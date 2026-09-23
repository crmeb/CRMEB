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

    public function getList(array $filter = [], int $page = 1, int $limit = 20): array
    {
        $where = [];
        if (!empty($filter['mark'])) {
            $where['mark'] = $filter['mark'];
        }
        if (!empty($filter['uid'])) {
            $where['uid'] = $filter['uid'];
        }
        if (!empty($filter['channel_key'])) {
            $where['channel_key'] = $filter['channel_key'];
        }
        if (!empty($filter['status'])) {
            $where['status'] = $filter['status'];
        }
        if (!empty($filter['start_time']) && !empty($filter['end_time'])) {
            $where['send_time'] = [$filter['start_time'], $filter['end_time']];
        }

        return $this->dao->getList($where, '*', $page, $limit);
    }

    public function getDetail(int $id): array
    {
        $info = $this->dao->get($id);
        return $info ? $info->toArray() : [];
    }

    public function getStatistics(array $filter = []): array
    {
        $where = [];
        if (!empty($filter['channel_key'])) {
            $where['channel_key'] = $filter['channel_key'];
        }
        if (!empty($filter['start_time']) && !empty($filter['end_time'])) {
            $where['send_time'] = [$filter['start_time'], $filter['end_time']];
        }
        return $this->dao->getStatistics($where);
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
