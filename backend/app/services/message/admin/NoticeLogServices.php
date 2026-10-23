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

use crmeb\basic\BaseService;
use app\dao\message\NoticeLogDao;
use app\model\message\NoticeLog;

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

    public function createLog(string $mark, int $uid, string $channelKey, string $channelName, string $title = '', string $content = ''): int
    {
        $data = [
            'mark' => $mark,
            'uid' => $uid,
            'channel_key' => $channelKey,
            'channel_name' => $channelName,
            'title' => $title,
            'content' => $content,
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

    public function cleanLogs(int $days = 30): int
    {
        $time = time() - $days * 86400;
        return $this->dao->deleteByTime($time);
    }
}
