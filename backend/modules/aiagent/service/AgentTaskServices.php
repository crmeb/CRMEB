<?php
declare(strict_types=1);

namespace modules\aiagent\service;

use crmeb\exceptions\AdminException;
use modules\aiagent\dao\AiAgentTaskDao;
use modules\aiagent\dao\AiAgentTaskRunDao;
use modules\aiagent\jobs\AgentTaskJob;
use think\facade\Db;
use think\facade\Log;

/**
 * AI 自动化任务服务
 *
 * 承担任务定义（eb_ai_agent_task）与运行记录（eb_ai_agent_task_run）的数据管理：
 * 任务 CRUD/启停、调度时间推导、到期扫描入队、立即执行、运行历史查询。
 * 无人值守的执行编排见 AgentTaskRunnerService。
 */
class AgentTaskServices
{
    /** 调度类型：每隔 N 分钟（1-43200，即最多 30 天） */
    public const SCHEDULE_MINUTES = 1;

    /** 调度类型：每天固定时刻（HH:MM） */
    public const SCHEDULE_DAILY = 2;

    /** 调度类型：每周固定周几固定时刻（周几|HH:MM，周几 1=周一...7=周日） */
    public const SCHEDULE_WEEKLY = 3;

    /** 调度类型：每月固定几日固定时刻（几日|HH:MM，几日 1-28 避开月末歧义） */
    public const SCHEDULE_MONTHLY = 4;

    /** 调度类型：每年固定月日固定时刻（月|日|HH:MM，月 1-12、日 1-28 避开月末歧义） */
    public const SCHEDULE_YEARLY = 5;

    /** 自主级别：只读（默认，需确认写工具不可见） */
    public const AUTONOMY_READONLY = 1;

    /** 自主级别：需审批（一期按只读降级执行，审批链路后续版本实现） */
    public const AUTONOMY_CONFIRM = 2;

    /** 自主级别：全自动（保留全部工具并豁免确认，仅超管可创建） */
    public const AUTONOMY_AUTO = 3;

    /** @var AiAgentTaskDao 任务数据 Dao */
    protected AiAgentTaskDao $taskDao;

    /** @var AiAgentTaskRunDao 运行记录数据 Dao */
    protected AiAgentTaskRunDao $runDao;

    /**
     * 构造函数（容器注入）
     * @param AiAgentTaskDao $taskDao
     * @param AiAgentTaskRunDao $runDao
     */
    public function __construct(AiAgentTaskDao $taskDao, AiAgentTaskRunDao $runDao)
    {
        $this->taskDao = $taskDao;
        $this->runDao = $runDao;
    }

    /**
     * 任务列表（当前管理员，按创建时间倒序，附最近一次运行摘要）
     * @param int $adminId 管理员 ID
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return array{list:array,count:int}
     */
    public function index(int $adminId, int $page = 1, int $limit = 20): array
    {
        $result = $this->taskDao->getPageByAdmin($adminId, $page, $limit);
        foreach ($result['list'] as &$item) {
            $item = $this->formatTask($item);
        }
        return $result;
    }

    /**
     * 任务详情
     * @param int $id 任务 ID
     * @param int $adminId 管理员 ID（归属校验）
     * @return array
     */
    public function read(int $id, int $adminId): array
    {
        return $this->formatTask($this->getTask($id, $adminId));
    }

    /**
     * 创建/编辑任务（编辑时按 id > 0 判定，仅限本人任务）
     * @param int $adminId 管理员 ID
     * @param array $data 表单数据（name/instruction/model_id/server_ids/skill_keys/autonomy/schedule_type/schedule_value/max_steps/timeout/notify_channels/notify_email/status/id）
     * @param bool $isSuperAdmin 是否超级管理员（level=0）：全自动自主级别仅超管可设置
     * @return int 任务 ID
     */
    public function save(int $adminId, array $data, bool $isSuperAdmin = false): int
    {
        $id = (int)($data['id'] ?? 0);
        $name = trim((string)($data['name'] ?? ''));
        $instruction = trim((string)($data['instruction'] ?? ''));
        if ($name === '') {
            throw new AdminException('请填写任务名称');
        }
        if (mb_strlen($name) > 100) {
            throw new AdminException('任务名称不能超过 100 字');
        }
        if ($instruction === '') {
            throw new AdminException('请填写任务指令');
        }
        $scheduleType = (int)($data['schedule_type'] ?? self::SCHEDULE_DAILY);
        $scheduleValue = trim((string)($data['schedule_value'] ?? ''));
        // 调度值合法性校验（同时规整格式，供时间推导使用）
        if ($scheduleType === self::SCHEDULE_MINUTES) {
            if (!ctype_digit($scheduleValue) || (int)$scheduleValue < 1 || (int)$scheduleValue > 43200) {
                throw new AdminException('间隔分钟数必须为 1-43200 的整数（最多 30 天）');
            }
            $scheduleValue = (string)(int)$scheduleValue;
        } elseif ($scheduleType === self::SCHEDULE_DAILY) {
            if (!preg_match('~^([01]?\d|2[0-3]):([0-5]\d)$~', $scheduleValue)) {
                throw new AdminException('每天调度的时间格式必须为 HH:MM，如 09:00');
            }
        } elseif ($scheduleType === self::SCHEDULE_WEEKLY) {
            if (!preg_match('~^([1-7])\|([01]?\d|2[0-3]):([0-5]\d)$~', $scheduleValue)) {
                throw new AdminException('每周调度的格式必须为 周几|HH:MM，如 1|09:00（1=周一，7=周日）');
            }
        } elseif ($scheduleType === self::SCHEDULE_MONTHLY) {
            if (!preg_match('~^([1-9]|1\d|2[0-8])\|([01]?\d|2[0-3]):([0-5]\d)$~', $scheduleValue)) {
                throw new AdminException('每月调度的格式必须为 几日|HH:MM，如 1|09:00（仅支持 1-28 日）');
            }
        } elseif ($scheduleType === self::SCHEDULE_YEARLY) {
            if (!preg_match('~^([1-9]|1[0-2])\|([1-9]|1\d|2[0-8])\|([01]?\d|2[0-3]):([0-5]\d)$~', $scheduleValue)) {
                throw new AdminException('每年调度的格式必须为 月|日|HH:MM，如 1|1|09:00（仅支持 1-28 日）');
            }
        } else {
            throw new AdminException('不支持的调度类型');
        }
        $autonomy = (int)($data['autonomy'] ?? self::AUTONOMY_READONLY);
        // 需审批模式（autonomy=2）链路后续版本实现，暂不接受该取值，避免「看似会审批、实际只读执行」的语义错位
        if (!in_array($autonomy, [self::AUTONOMY_READONLY, self::AUTONOMY_AUTO], true)) {
            throw new AdminException($autonomy === self::AUTONOMY_CONFIRM
                ? '需审批模式即将上线，请先选择「只读」或「全自动」'
                : '不支持的自主级别');
        }
        // 全自动豁免确认并保留全部工具（含写操作），服务端强制仅超级管理员可设置
        if ($autonomy === self::AUTONOMY_AUTO && !$isSuperAdmin) {
            throw new AdminException('「全自动」自主级别仅超级管理员可使用');
        }
        $channels = array_values(array_unique(array_filter(array_map(
            'trim',
            (array)($data['notify_channels'] ?? [])
        ), fn(string $v) => in_array($v, ['is_email', 'is_ent_wechat'], true))));
        $email = trim((string)($data['notify_email'] ?? ''));
        if (in_array('is_email', $channels, true)) {
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new AdminException('通知渠道含邮件时必须填写合法的收件邮箱');
            }
        }
        $record = [
            'name' => $name,
            'instruction' => $instruction,
            'model_id' => max(0, (int)($data['model_id'] ?? 0)),
            'server_ids' => json_encode(array_values(array_unique(array_map('intval', (array)($data['server_ids'] ?? [])))), JSON_UNESCAPED_UNICODE),
            'skill_keys' => json_encode(array_values(array_unique(array_filter(array_map('strval', (array)($data['skill_keys'] ?? []))))), JSON_UNESCAPED_UNICODE),
            'autonomy' => $autonomy,
            'schedule_type' => $scheduleType,
            'schedule_value' => $scheduleValue,
            'max_steps' => min(30, max(3, (int)($data['max_steps'] ?? 10))),
            'timeout' => min(1800, max(60, (int)($data['timeout'] ?? 300))),
            'notify_channels' => json_encode($channels, JSON_UNESCAPED_UNICODE),
            'notify_email' => $email,
            'status' => (int)($data['status'] ?? 1) === 1 ? 1 : 0,
        ];
        $now = time();
        if ($id > 0) {
            $task = $this->getTask($id, $adminId);
            $record['update_time'] = $now;
            $this->taskDao->updateById((int)$task['id'], $record);
            // 调度或启停变化：重推下次运行时间（停用保持 0，启用后从当前时间起算）
            $nextRunTime = (int)$record['status'] === 1
                ? $this->computeNextRunTime($scheduleType, $scheduleValue, $now)
                : 0;
            $this->taskDao->updateById((int)$task['id'], ['next_run_time' => $nextRunTime]);
            return (int)$task['id'];
        }
        $record['admin_id'] = $adminId;
        $record['next_run_time'] = (int)$record['status'] === 1
            ? $this->computeNextRunTime($scheduleType, $scheduleValue, $now)
            : 0;
        $record['create_time'] = $now;
        $record['update_time'] = $now;
        return $this->taskDao->insertTask($record);
    }

    /**
     * 删除任务（仅限本人；运行中记录保留但不阻止删除，历史运行随任务一并清理）
     * @param int $id 任务 ID
     * @param int $adminId 管理员 ID
     */
    public function delete(int $id, int $adminId): void
    {
        $task = $this->getTask($id, $adminId);
        Db::transaction(function () use ($task) {
            $this->runDao->deleteByTaskId((int)$task['id']);
            $this->taskDao->deleteById((int)$task['id']);
        });
    }

    /**
     * 启停任务（启用时重推下次运行时间，停用清零以排除出扫描）
     * @param int $id 任务 ID
     * @param int $adminId 管理员 ID
     * @param int $status 0=停用 1=启用
     */
    public function setStatus(int $id, int $adminId, int $status): void
    {
        $task = $this->getTask($id, $adminId);
        $status = $status === 1 ? 1 : 0;
        $nextRunTime = $status === 1
            ? $this->computeNextRunTime((int)$task['schedule_type'], (string)$task['schedule_value'], time())
            : 0;
        $this->taskDao->updateById((int)$task['id'], [
            'status' => $status,
            'next_run_time' => $nextRunTime,
            'update_time' => time(),
        ]);
    }

    /**
     * 立即执行（创建运行记录并入队，与调度执行完全同构，也是任务的测试入口）
     * @param int $id 任务 ID
     * @param int $adminId 管理员 ID
     * @return int 运行记录 ID
     */
    public function trigger(int $id, int $adminId): int
    {
        $task = $this->getTask($id, $adminId);
        if ((int)$task['status'] !== 1) {
            throw new AdminException('任务已停用，请先启用后再执行');
        }
        $runId = $this->createRun((int)$task['id'], 2);
        if ($runId === 0) {
            // 同一秒内重复触发被唯一索引拦截：视为已提交，提示而非静默
            throw new AdminException('该任务刚已提交执行，请稍后在运行历史中查看结果');
        }
        return $runId;
    }

    /**
     * 运行记录列表（仅限本人任务）
     * @param int $taskId 任务 ID
     * @param int $adminId 管理员 ID
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return array{list:array,count:int}
     */
    public function runs(int $taskId, int $adminId, int $page = 1, int $limit = 20): array
    {
        $this->getTask($taskId, $adminId);
        $result = $this->runDao->getPageByTask($taskId, $page, $limit);
        foreach ($result['list'] as &$item) {
            $item = $this->formatRun($item);
        }
        return $result;
    }

    /**
     * 全量运行记录列表（当前管理员的全部任务，倒序，附任务名称）
     * @param int $adminId 管理员 ID
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return array{list:array,count:int}
     */
    public function runIndex(int $adminId, int $page = 1, int $limit = 20): array
    {
        $result = $this->runDao->getPageWithTaskByAdmin($adminId, $page, $limit);
        foreach ($result['list'] as &$item) {
            $item = $this->formatRun($item);
            $item['task_name'] = (string)($item['task_name'] ?? '');
        }
        return $result;
    }

    /**
     * 运行详情（含关联会话消息，供前端回放执行全过程）
     * @param int $runId 运行记录 ID
     * @param int $adminId 管理员 ID（归属校验）
     * @return array
     */
    public function runDetail(int $runId, int $adminId): array
    {
        $run = $this->runDao->getById($runId);
        if (!$run) {
            throw new AdminException('运行记录不存在');
        }
        $this->getTask((int)$run['task_id'], $adminId);
        $run = $this->formatRun($run);
        $run['conversation_id'] = (int)$run['conversation_id'];
        $run['messages'] = (int)$run['conversation_id'] > 0
            ? app()->make(AiAgentServices::class)->messages((int)$run['conversation_id'], $adminId)
            : [];
        return $run;
    }

    /**
     * 扫描到期任务并入队（timer 每分钟触发，毫秒级返回，重活全部出队执行）
     *
     * 流程：孤儿运行回收 → 到期任务逐条「创建 run + 先推进 next_run_time + 入队」；
     * next_run_time 在入队前推进保证扫描幂等（下一轮扫描不会重复命中），
     * 并发场景由 run 状态 CAS（仅 running 可被领取）兜底。
     *
     * @return int 本次入队的运行数
     */
    public function dispatchDueTasks(): int
    {
        $now = time();
        $this->recycleOrphanRuns($now);
        $tasks = $this->taskDao->getDueTasks($now);
        $dispatched = 0;
        foreach ($tasks as $task) {
            try {
                // 先推进时间指针再入队：无论入队成功与否都不会被下一轮扫描重复命中
                $this->taskDao->updateById((int)$task['id'], [
                    'next_run_time' => $this->computeNextRunTime(
                        (int)$task['schedule_type'],
                        (string)$task['schedule_value'],
                        max($now, (int)$task['next_run_time'])
                    ),
                    'last_run_time' => $now,
                    'update_time' => $now,
                ]);
                $runId = $this->createRun((int)$task['id'], 1, $now);
                if ($runId > 0) {
                    AgentTaskJob::dispatch('doJob', [$runId]);
                    $dispatched++;
                }
            } catch (\Throwable $e) {
                // 单个任务异常不中断本轮扫描
                Log::error('[AgentTask] 任务入队失败: ' . $e->getMessage(), ['task_id' => $task['id'] ?? 0]);
            }
        }
        return $dispatched;
    }

    /**
     * 读取任务（归属校验）
     * @param int $id 任务 ID
     * @param int $adminId 管理员 ID
     * @param bool $withAdmin false=按归属校验；true=免归属（执行器场景，仅要求存在）
     * @return array
     */
    public function getTask(int $id, int $adminId = 0, bool $withAdmin = true): array
    {
        $task = $this->taskDao->getById($id, $adminId, $withAdmin);
        if (!$task) {
            throw new AdminException('任务不存在或无权访问');
        }
        return $task;
    }

    /**
     * 读取运行记录
     * @param int $runId 运行记录 ID
     * @return array
     */
    public function getRun(int $runId): array
    {
        $run = $this->runDao->getById($runId);
        if (!$run) {
            throw new AdminException('运行记录不存在');
        }
        return $run;
    }

    /**
     * 更新运行记录
     * @param int $runId 运行记录 ID
     * @param array $data 更新数据
     */
    public function updateRun(int $runId, array $data): void
    {
        $this->runDao->updateById($runId, $data);
    }

    /**
     * 创建运行记录（running 态，等待队列领取）
     *
     * 同一任务 + 同一触发方式 + 同一触发时间点由唯一索引（uk_task_trigger）兜底：
     * 并发重复创建（双 timer 扫描、连点手动执行）只落一条记录，重复方返回 0，
     * 由调用方跳过入队，避免同一调度点重复执行（重复发邮件/重复写操作）。
     *
     * @param int $taskId 任务 ID
     * @param int $triggerType 触发方式：1=调度 2=手动立即执行
     * @param int $triggerTime 调度触发时间点（手动执行传当前时间）
     * @return int 运行记录 ID（0=该触发点已有运行记录，属重复投递）
     */
    public function createRun(int $taskId, int $triggerType, int $triggerTime = 0): int
    {
        $now = time();
        try {
            return $this->runDao->insertRun([
                'task_id' => $taskId,
                'conversation_id' => 0,
                'status' => 'running',
                'trigger_type' => $triggerType === 2 ? 2 : 1,
                'trigger_time' => $triggerTime ?: $now,
                'confirm_message_id' => 0,
                'summary' => '',
                'error' => '',
                'tool_calls' => 0,
                'usage' => '',
                'started_at' => 0,
                'finished_at' => 0,
            ]);
        } catch (\Throwable $e) {
            // 唯一索引冲突 = 该触发点已有运行记录，按重复投递静默返回；
            // 其他数据库异常原样抛出
            if (!$this->runDao->hasRunAt($taskId, $triggerType === 2 ? 2 : 1, $triggerTime ?: $now)) {
                throw $e;
            }
            return 0;
        }
    }

    /**
     * 回收孤儿运行：running 超过合理时长仍未终态（进程被杀/容器重启），标记 failed
     * @param int $now 当前时间戳
     */
    private function recycleOrphanRuns(int $now): void
    {
        $runIds = $this->runDao->findOrphanRunIds($now);
        if ($runIds === []) {
            return;
        }
        $this->runDao->updateByIds($runIds, [
            'status' => 'failed',
            'error' => '执行超时未结束，已被调度器回收（可能因进程被终止）',
            'finished_at' => $now,
        ]);
    }

    /**
     * 推导下次运行时间
     * @param int $type 调度类型：1=每隔N分钟 2=每天HH:MM 3=每周周几|HH:MM 4=每月几日|HH:MM
     * @param string $value 调度值：分钟数 / HH:MM / 周几|HH:MM / 几日|HH:MM
     * @param int $from 起算时间戳
     * @return int 下次运行时间戳（无法解析时返回 0）
     */
    public function computeNextRunTime(int $type, string $value, int $from): int
    {
        if ($type === self::SCHEDULE_MINUTES) {
            $minutes = (int)$value;
            if ($minutes < 1) {
                return 0;
            }
            return $from + $minutes * 60;
        }
        if ($type === self::SCHEDULE_DAILY && preg_match('~^([01]?\d|2[0-3]):([0-5]\d)$~', $value, $m)) {
            $hour = (int)$m[1];
            $minute = (int)$m[2];
            $today = strtotime(date('Y-m-d ', $from) . sprintf('%02d:%02d:00', $hour, $minute));
            // 今天的时刻已过（或恰为当前秒）则排到明天
            return $today > $from ? $today : $today + 86400;
        }
        if ($type === self::SCHEDULE_WEEKLY && preg_match('~^([1-7])\|([01]?\d|2[0-3]):([0-5]\d)$~', $value, $m)) {
            $week = (int)$m[1];
            $hour = (int)$m[2];
            $minute = (int)$m[3];
            $time = strtotime(date('Y-m-d ', $from) . sprintf('%02d:%02d:00', $hour, $minute));
            // date('N') 返回 1=周一...7=周日，与配置口径一致；本周该日时刻已过则顺延到下一个该周几
            $days = ($week - (int)date('N', $time) + 7) % 7;
            if ($days === 0 && $time <= $from) {
                $days = 7;
            }
            return $time + $days * 86400;
        }
        if ($type === self::SCHEDULE_MONTHLY && preg_match('~^([1-9]|1\d|2[0-8])\|([01]?\d|2[0-3]):([0-5]\d)$~', $value, $m)) {
            $day = (int)$m[1];
            $hour = (int)$m[2];
            $minute = (int)$m[3];
            $time = mktime($hour, $minute, 0, (int)date('n', $from), $day, (int)date('Y', $from));
            // 本月该日时刻已过（或恰为当前秒）则排到下月同日（day<=28 恒存在，无月末进位歧义）
            if ($time <= $from) {
                $time = mktime($hour, $minute, 0, (int)date('n', $from) + 1, $day, (int)date('Y', $from));
            }
            return $time;
        }
        if ($type === self::SCHEDULE_YEARLY && preg_match('~^([1-9]|1[0-2])\|([1-9]|1\d|2[0-8])\|([01]?\d|2[0-3]):([0-5]\d)$~', $value, $m)) {
            $month = (int)$m[1];
            $day = (int)$m[2];
            $hour = (int)$m[3];
            $minute = (int)$m[4];
            $time = mktime($hour, $minute, 0, $month, $day, (int)date('Y', $from));
            // 今年该日时刻已过（或恰为当前秒）则排到明年同日（day<=28 恒存在，无月末进位歧义）
            if ($time <= $from) {
                $time = mktime($hour, $minute, 0, $month, $day, (int)date('Y', $from) + 1);
            }
            return $time;
        }
        return 0;
    }

    /**
     * 任务记录出参格式化（JSON 字段解码 + 类型规整）
     * @param array $task 任务记录
     * @return array
     */
    private function formatTask(array $task): array
    {
        $task['id'] = (int)$task['id'];
        $task['server_ids'] = $this->decodeJson((string)($task['server_ids'] ?? ''));
        $task['skill_keys'] = $this->decodeJson((string)($task['skill_keys'] ?? ''));
        $task['notify_channels'] = $this->decodeJson((string)($task['notify_channels'] ?? ''));
        $task['autonomy'] = (int)$task['autonomy'];
        $task['schedule_type'] = (int)$task['schedule_type'];
        $task['max_steps'] = (int)$task['max_steps'];
        $task['timeout'] = (int)$task['timeout'];
        $task['model_id'] = (int)$task['model_id'];
        $task['status'] = (int)$task['status'];
        $task['last_run_time'] = (int)$task['last_run_time'];
        $task['next_run_time'] = (int)$task['next_run_time'];
        return $task;
    }

    /**
     * 运行记录出参格式化
     * @param array $run 运行记录
     * @return array
     */
    private function formatRun(array $run): array
    {
        $run['id'] = (int)$run['id'];
        $run['task_id'] = (int)$run['task_id'];
        $run['conversation_id'] = (int)$run['conversation_id'];
        $run['trigger_type'] = (int)$run['trigger_type'];
        $run['tool_calls'] = (int)$run['tool_calls'];
        $run['started_at'] = (int)$run['started_at'];
        $run['finished_at'] = (int)$run['finished_at'];
        $run['duration'] = $run['finished_at'] > 0 && $run['started_at'] > 0
            ? $run['finished_at'] - $run['started_at']
            : 0;
        return $run;
    }

    /**
     * JSON 字符串安全解码为数组
     * @param string $value JSON 字符串
     * @return array
     */
    private function decodeJson(string $value): array
    {
        $decoded = json_decode($value, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }
}
