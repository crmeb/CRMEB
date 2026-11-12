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

namespace app\services\message\admin;

use app\dao\message\NoticeLogDao;
use app\model\message\NoticeLog;
use app\services\message\Notice;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;

class NoticeLogServices extends BaseService
{
    protected $dao;

    public function __construct(NoticeLogDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 组装查询条件
     * @param array $filter 筛选条件
     * @param bool $withStatus 是否附加状态条件（统计口径不按状态过滤，否则成功/失败恒为 0）
     * @return array
     */
    protected function buildWhere(array $filter, bool $withStatus = true): array
    {
        $where = [];
        if (!empty($filter['mark'])) {
            $where['mark'] = trim((string)$filter['mark']);
        }
        if (!empty($filter['uid'])) {
            $where['uid'] = (int)$filter['uid'];
        }
        if (!empty($filter['channel_key'])) {
            $where['channel_key'] = $filter['channel_key'];
        }
        // 状态 0（待发送）是合法筛选值，不能用 empty 判断，否则会被丢弃
        if ($withStatus && isset($filter['status']) && $filter['status'] !== '' && $filter['status'] !== null) {
            $where['status'] = (int)$filter['status'];
        }
        if (!empty($filter['start_time']) && !empty($filter['end_time'])) {
            $where['send_time'] = [(int)$filter['start_time'], (int)$filter['end_time']];
        }
        return $where;
    }

    /**
     * 发送记录列表
     * @param array $filter
     * @param int $page
     * @param int $limit
     * @return array{list: array, count: int}
     */
    public function getList(array $filter = [], int $page = 1, int $limit = 20): array
    {
        $where = $this->buildWhere($filter);
        return [
            'list' => $this->dao->getList($where, '*', $page, $limit),
            'count' => $this->dao->getCount($where),
        ];
    }

    public function getDetail(int $id): array
    {
        $info = $this->dao->get($id);
        return $info ? $info->toArray() : [];
    }

    /**
     * 发送统计（与列表使用同一套筛选条件，状态筛选除外）
     * @param array $filter
     * @return array
     */
    public function getStatistics(array $filter = []): array
    {
        return $this->dao->getStatistics($this->buildWhere($filter, false));
    }

    /**
     * 写入发送日志（含原始业务变量，失败后可安全重试）
     * @param string $mark 消息标识
     * @param int $uid 接收用户
     * @param string $channelKey 渠道标识
     * @param string $channelName 渠道名称
     * @param string $title 渲染后的标题
     * @param string $content 渲染后的内容
     * @param array $variables 原始业务变量
     * @return int 日志 ID
     */
    public function createLog(string $mark, int $uid, string $channelKey, string $channelName, string $title = '', string $content = '', array $variables = []): int
    {
        $data = [
            'mark' => $mark,
            'uid' => $uid,
            'channel_key' => $channelKey,
            'channel_name' => $channelName,
            'title' => $title,
            'content' => $content,
            'variables' => $variables === [] ? null : json_encode($variables, JSON_UNESCAPED_UNICODE),
            'status' => NoticeLog::STATUS_PENDING,
        ];
        return $this->dao->createLog($data);
    }

    public function logSuccess(int $id): bool
    {
        return $this->dao->updateStatus($id, NoticeLog::STATUS_SUCCESS);
    }

    public function logFailed(int $id, string $errorMsg): bool
    {
        return $this->dao->updateStatus($id, NoticeLog::STATUS_FAILED, $errorMsg);
    }

    /**
     * 按日志保存的原始变量，仅重试失败记录对应的单一渠道
     * @param int $id 日志 ID
     * @return bool
     * @throws AdminException 记录不存在、非失败态、缺变量、渠道未执行或重试仍失败
     */
    public function retry(int $id): bool
    {
        $info = $this->getDetail($id);
        if (!$info) {
            throw new AdminException('记录不存在');
        }
        if ((int)($info['status'] ?? -1) !== NoticeLog::STATUS_FAILED) {
            throw new AdminException('仅失败记录可重试');
        }

        $variables = $this->decodeVariables($info['variables'] ?? null);
        if ($variables === null) {
            throw new AdminException('该日志未保存原始变量，无法安全重试');
        }

        $mark = (string)($info['mark'] ?? '');
        $channelKey = (string)($info['channel_key'] ?? '');
        if ($mark === '' || $channelKey === '') {
            throw new AdminException('日志缺少消息标识或渠道，无法重试');
        }

        $results = Notice::to((int)($info['uid'] ?? 0))
            ->channel($channelKey)
            ->sendMsg($mark, (string)($info['title'] ?: $mark), $variables);

        $result = $results[$channelKey] ?? null;
        if ($result === null) {
            throw new AdminException('重试未执行：渠道未启用或通知未配置');
        }
        if (empty($result['success'])) {
            throw new AdminException('重试失败：' . ((string)($result['error'] ?? '') ?: '未知错误'));
        }
        return true;
    }

    /**
     * 解析日志中的原始变量
     * @param mixed $raw JSON 字符串或已解码数组
     * @return array|null 有效关联数组；空/非法返回 null（不可重试）
     */
    protected function decodeVariables($raw): ?array
    {
        if (is_array($raw)) {
            return $raw === [] ? null : $raw;
        }
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || $decoded === []) {
            return null;
        }
        return $decoded;
    }

    public function cleanLogs(int $days = 30): int
    {
        $time = time() - $days * 86400;
        return $this->dao->deleteByTime($time);
    }
}
