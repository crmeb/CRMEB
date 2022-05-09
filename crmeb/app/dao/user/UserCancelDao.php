<?php

namespace app\dao\user;

use app\dao\BaseDao;
use app\model\user\UserCancel;

class UserCancelDao extends BaseDao
{
    /**
     * @return string
     */
    protected function setModel(): string
    {
        return UserCancel::class;
    }

    /**
     * 获取列表
     * @param $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($where, $page = 0, $limit = 0)
    {
        return $this->search($where)->with(['user'])
            ->when($page && $limit, function ($query) use ($page, $limit) {
                $query->page($page, $limit);
            })->select()->toArray();
    }
}