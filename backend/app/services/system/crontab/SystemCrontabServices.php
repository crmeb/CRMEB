<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\services\system\crontab;

use app\dao\system\crontab\SystemCrontabDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use think\facade\Log;
use think\helper\Str;
use Workerman\Crontab\Crontab;

/**
 * 系统定时任务服务
 */
class SystemCrontabServices extends BaseService
{
    /**
     * @var SystemCrontabDao
     */
    protected $dao;

    /**
     * 已注册的定时任务实例表（functionName => ['crontab' => Crontab, 'time' => update_time]）
     * @var array
     */
    protected $taskIds = [];

    /**
     * 进程内任务列表缓存（避免每秒调度查询数据库，30 秒刷新一次）
     * @var array|null
     */
    protected $taskListCache = null;

    /**
     * 任务列表缓存时间戳
     * @var int
     */
    protected $taskListCacheTime = 0;

    public function __construct(SystemCrontabDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 定时任务列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTimerList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->selectList($where, '*', $page, $limit, 'id desc', [], true);
        foreach ($list as &$item) {
            $item['next_execution_time'] = date('Y-m-d H:i:s', $item['next_execution_time']);
            $item['last_execution_time'] = $item['last_execution_time'] != 0 ? date('Y-m-d H:i:s', $item['last_execution_time']) : '暂未执行';
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 定时任务详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTimerInfo($id)
    {
        $info = $this->dao->get($id);
        if (!$info) throw new AdminException('数据不存在');
        $info['customCode'] = "<?php\n\n" . json_decode($info['customCode']);
        return $info->toArray();
    }

    /**
     * 定时任务类型
     * @return string[]
     */
    public function getMarkList(): array
    {
        return app()->make(CrontabRunServices::class)->getMarkList();
    }

    /**
     * 保存定时任务
     * @param array $data
     * @return bool
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveTimer(array $data = [])
    {
        if (!$data['id'] && $this->dao->getCount(['mark' => $data['mark'], 'is_del' => 0]) && $data['mark'] != 'customTimer') {
            throw new AdminException('该定时任务已存在，请勿重复添加');
        }
        if ($data['mark'] != 'customTimer') $data['name'] = $this->getMarkList()[$data['mark']];
        $data['customCode'] = json_encode(preg_replace('/<\?php\s*\n/', '', $data['customCode']));
        $data['timeStr'] = $this->getTimerStr([
            'type' => $data['type'],
            'month' => $data['month'],
            'week' => $data['week'],
            'day' => $data['day'],
            'hour' => $data['hour'],
            'minute' => $data['minute'],
            'second' => $data['second'],
        ]);
        if (!$data['id']) {
            unset($data['id']);
            $data['add_time'] = $data['update_time'] = time();
            $res = $this->dao->save($data);
        } else {
            $data['update_time'] = time();
            $res = $this->dao->update(['id' => $data['id']], $data);
        }
        if (!$res) throw new AdminException('保存失败');
        return true;
    }

    /**
     * 删除定时任务
     * @param $id
     * @return bool
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delTimer($id)
    {
        $data['update_time'] = time();
        $data['is_del'] = 1;
        $res = $this->dao->update(['id' => $id], $data);
        if (!$res) throw new AdminException('删除失败');
        return true;
    }

    /**
     * 设置定时任务状态
     * @param $id
     * @param $is_open
     * @return bool
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setTimerStatus($id, $is_open)
    {
        $data['update_time'] = time();
        $data['is_open'] = $is_open;
        $res = $this->dao->update(['id' => $id], $data);
        if (!$res) throw new AdminException('设置失败');
        return true;
    }

    /**
     * 计算定时任务下次执行时间
     * @param $data
     * @param int $time
     * @return false|float|int|mixed
     */
    public function getTimerCycleTime($data, $time = 0)
    {
        if (!$time) $time = time();
        switch ($data['type']) {
            case 1: // 每隔几秒
                $cycle_time = $time + $data['second'];
                break;
            case 2: // 每隔几分
                $cycle_time = $time + ($data['minute'] * 60);
                break;
            case 3: // 每隔几时
                $cycle_time = $time + ($data['hour'] * 3600) + ($data['minute'] * 60);
                break;
            case 4: // 每隔几日
                $cycle_time = $time + ($data['day'] * 86400) + ($data['hour'] * 3600) + ($data['minute'] * 60);
                break;
            case 5: // 每日几时几分几秒
                $cycle_time = strtotime(date('Y-m-d ' . $data['hour'] . ':' . $data['minute'] . ':' . $data['second'], time()));
                if ($time >= $cycle_time) {
                    $cycle_time = $cycle_time + 86400;
                }
                break;
            case 6: // 每周周几几时几分几秒
                $todayStart = strtotime(date('Y-m-d 00:00:00', time()));
                $w = date("w");
                if ($w > $data['week']) {
                    $cycle_time = $todayStart + ((7 - $w + $data['week']) * 86400) + ($data['hour'] * 3600) + ($data['minute'] * 60) + $data['second'];
                } else if ($w == $data['week']) {
                    $cycle_time = $todayStart + ($data['hour'] * 3600) + ($data['minute'] * 60) + $data['second'];
                    if ($time >= $cycle_time) {
                        $cycle_time = $cycle_time + (7 * 86400);
                    }
                } else {
                    $cycle_time = $todayStart + (($data['week'] - $w) * 86400) + ($data['hour'] * 3600) + ($data['minute'] * 60) + $data['second'];
                }
                break;
            case 7: // 每月几日几时几分几秒
                $currentMonth = date("n");
                $currentYear = date("Y");
                if ($currentMonth == 12) {
                    $nextMonth = 1;
                    $nextYear = $currentYear + 1;
                } else {
                    $nextMonth = $currentMonth + 1;
                    $nextYear = $currentYear;
                }
                $cycle_time = mktime($data['hour'], $data['minute'], $data['second'], $nextMonth, $data['day'], $nextYear);
                break;
            case 8: // 每年几月几日几时几分几秒
                $cycle_time = mktime($data['hour'], $data['minute'], $data['second'], $data['month'], $data['day'], date("Y") + 1);
                break;
            default:
                $cycle_time = 0;
                break;
        }
        return $cycle_time;
    }

    /**
     * 接口执行执行任务
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/02/17
     */
    public function crontabApiRun()
    {
        $crontabRunServices = app()->make(CrontabRunServices::class);
        $time = time();
        file_put_contents(root_path() . 'runtime/.timer', $time); //检测定时任务是否正常
        $list = $this->dao->selectList(['is_open' => 1, 'is_del' => 0])->toArray();
        foreach ($list as $item) {
            if ($item['next_execution_time'] < $time) {
                $mark = Str::camel($item['mark']);
                try {
                    if (strpos($mark, 'customTimer') === 0) {
                        $crontabRunServices->run((int)$item['id'], $mark, json_decode($item['customCode']));
                    } else {
                        $crontabRunServices->run((int)$item['id'], $mark);
                    }
                } catch (\Throwable $e) {
                    Log::error('定时任务执行失败,任务ID:' . $item['id'] . ',任务标识:' . $mark . ',失败原因:' . $e->getMessage());
                }
                $this->dao->update($item['id'], ['last_execution_time' => $time, 'next_execution_time' => $this->getTimerCycleTime($item)]);
            }
        }
    }

    /**
     * 运行定时任务
     *
     * 基于 Workerman Crontab 注册每个任务的触发器；任务执行体经 CrontabProcessServices
     * 派发到独立子进程执行，避免单进程事件循环被耗时任务阻塞。
     * @return void
     */
    public function crontabCommandRun()
    {
        file_put_contents(root_path() . 'runtime/.timer', time());
        // 获取 CrontabRunServices 实例
        $crontabRunServices = app()->make(CrontabRunServices::class);
        // 获取子进程派发服务实例
        $processServices = app()->make(CrontabProcessServices::class);
        // 创建一个每秒钟执行一次的定时任务
        new Crontab('*/1 * * * * *', function () use ($crontabRunServices, $processServices) {
            // 写入时间戳，用于检测定时任务是否正常执行
            $timerTime = file_get_contents(root_path() . 'runtime/.timer');
            if ($timerTime < (time() - 60)) {
                file_put_contents(root_path() . 'runtime/.timer', time());
            }
            // 获取定时任务列表（进程内 30 秒刷新，后台任务配置变更最多延迟 30 秒生效）
            $list = $this->getTaskList();
            // 遍历定时任务列表
            foreach ($list as &$item) {
                // 获取函数名
                $functionName = Str::camel($item['mark']);
                if ($functionName == 'customTimer') {
                    $functionName = 'customTimer_' . $item['id'];
                }
                // 如果更新时间不存在，则将其设置为添加时间
                $item['update_time'] = $item['update_time'] ?: $item['add_time'];
                // 如果任务已经被执行过，则跳过此次循环
                if (isset($this->taskIds[$functionName]) && $this->taskIds[$functionName]['time'] == $item['update_time']) {
                    continue;
                }
                // 获取定时器字符串
                $timeStr = $item['timeStr'] !== '' ? $item['timeStr'] : $this->getTimerStr($item);
                // 获取自定义代码
                $customCode = json_decode($item['customCode']);
                // 如果任务已经被执行过，并且当前时间和上次执行时间不同，则销毁之前的定时任务
                if (isset($this->taskIds[$functionName]) && $this->taskIds[$functionName]['time'] != $item['update_time'] && isset($this->taskIds[$functionName]['crontab']) && $this->taskIds[$functionName]['crontab'] instanceof Crontab) {
                    $this->taskIds[$functionName]['crontab']->destroy();
                    unset($this->taskIds[$functionName]);
                }
                // 如果任务是开启状态，则创建一个新的定时任务
                if ($item['is_open'] == 1) {
                    $crontab = new Crontab($timeStr, function () use ($crontabRunServices, $processServices, $functionName, $customCode, $item) {
                        // 任务执行体：派发到独立子进程运行，业务失败原因已在闭包内记录
                        $execute = function () use ($crontabRunServices, $functionName, $customCode, $item) {
                            try {
                                if (strpos($functionName, 'customTimer_') === 0) {
                                    $crontabRunServices->run((int)$item['id'], 'customTimer', $customCode);
                                } else {
                                    $crontabRunServices->run((int)$item['id'], $functionName);
                                }
                            } catch (\Throwable $e) {
                                Log::error('定时任务执行失败,任务ID:' . $item['id'] . ',任务标识:' . $functionName . ',失败原因:' . $e->getMessage());
                            }
                        };
                        $processServices->dispatch($functionName, $execute);
                    });
                    $this->taskIds[$functionName] = ['crontab' => $crontab, 'time' => $item['update_time']];
                }
            }
        });
    }

    /**
     * 获取已开启的定时任务列表（进程内缓存 30 秒）
     *
     * 说明：timer 进程不再读取 Cache 中的 crontabCache，避免子进程执行业务期间父进程
     * 与子进程共用缓存连接（redis 驱动场景）；后台对任务配置的增删改最多延迟 30 秒生效。
     * @return array
     */
    protected function getTaskList(): array
    {
        if ($this->taskListCache === null || (time() - $this->taskListCacheTime) >= 30) {
            $this->taskListCache = $this->dao->selectList(['is_open' => 1, 'is_del' => 0])->toArray();
            $this->taskListCacheTime = time();
        }
        return $this->taskListCache;
    }

    /**
     * 获取定时任务时间表达式
     * 0   1   2   3   4   5
     * |   |   |   |   |   |
     * |   |   |   |   |   +------ day of week (0 - 6) (Sunday=0)
     * |   |   |   |   +------ month (1 - 12)
     * |   |   |   +-------- day of month (1 - 31)
     * |   |   +---------- hour (0 - 23)
     * |   +------------ min (0 - 59)
     * +-------------- sec (0-59)[可省略，如果没有0位,则最小时间粒度是分钟]
     * @param $data
     * @return string
     */
    public function getTimerStr($data): string
    {
        $timeStr = '';
        switch ($data['type']) {
            case 1:// 每隔几秒
                $timeStr = '*/' . $data['second'] . ' * * * * *';
                break;
            case 2:// 每隔几分
                $timeStr = '0 */' . $data['minute'] . ' * * * *';
                break;
            case 3:// 每隔几时第几分钟执行
                $timeStr = '0 ' . $data['minute'] . ' */' . $data['hour'] . ' * * *';
                break;
            case 4:// 每隔几日第几小时第几分钟执行
                $timeStr = '0 ' . $data['minute'] . ' ' . $data['hour'] . ' */' . $data['day'] . ' * *';
                break;
            case 5:// 每日几时几分几秒
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' * * *';
                break;
            case 6:// 每周周几几时几分几秒
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' * * ' . ($data['week'] == 7 ? 0 : $data['week']);
                break;
            case 7:// 每月几日几时几分几秒
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' ' . $data['day'] . ' * *';
                break;
            case 8:// 每年几月几日几时几分几秒
                $timeStr = $data['second'] . ' ' . $data['minute'] . ' ' . $data['hour'] . ' ' . $data['day'] . ' ' . $data['month'] . ' *';
                break;
        }
        return $timeStr;
    }
}
