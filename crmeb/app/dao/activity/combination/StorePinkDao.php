<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\dao\activity\combination;

use app\dao\BaseDao;
use app\model\activity\combination\StorePink;

/**
 *
 * Class StorePinkDao
 * @package app\dao\activity
 */
class StorePinkDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return StorePink::class;
    }

    /**
     * 获取拼团数量集合
     * @param array $where
     * @return array
     */
    public function getPinkCount(array $where = [])
    {
        return $this->getModel()->where($where)->group('cid')->column('count(*)', 'cid');
    }

    /**
     * 获取列表
     * @param array $where
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, int $page = 0, int $limit = 0)
    {
        return $this->search($where)->when($where['k_id'] != 0, function ($query) use ($where) {
            $query->whereOr('id', $where['k_id']);
        })->with('getProduct')->when($page != 0, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->order('add_time desc')->select()->toArray();
    }

    /**
     * 获取正在拼团中的人,取最早写入的一条
     * @param array $where
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPinking(array $where)
    {
        return $this->search($where)->order('add_time asc')->find();
    }

    /**
     * 获取拼团列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function pinkList(array $where)
    {
        return $this->search($where)
            ->where('stop_time', '>', time())
            ->order('add_time desc')
            ->select()->toArray();
    }

    /**
     * 获取正在拼团的人数
     * @param int $kid
     * @return int
     */
    public function getPinkPeople(int $kid)
    {
        return $this->count(['k_id' => $kid, 'is_refund' => 0]) + 1;
    }

    /**
     * 获取正在拼团的人数
     * @param array $kids
     * @return int
     */
    public function getPinkPeopleCount(array $kids)
    {
        $count = $this->getModel()->whereIn('k_id', $kids)->where('is_refund', 0)->group('k_id')->column('COUNT(id) as count', 'k_id');
        $counts = [];
        foreach ($kids as &$item) {
            if (isset($count[$item])) {
                $counts[$item] = $count[$item] + 1;
            } else {
                $counts[$item] = 1;
            }
        }
        return $counts;
    }

    /**
     * 获取拼团成功的列表
     * @param int $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function successList(int $uid)
    {
        return $this->search(['status' => 2, 'is_refund' => 0])
            ->where('uid', '<>', $uid)
            ->select()->toArray();
    }


    /**
     * 获取拼团完成的个数
     * @return float
     */
    public function getPinkOkSumTotalNum()
    {
        return $this->sum(['status' => 2, 'is_refund' => 0], 'total_num');
    }

    /**
     * 是否能继续拼团
     * @param int $id
     * @param int $uid
     * @return int
     */
    public function isPink(int $id, int $uid)
    {
        return $this->getModel()->where('k_id|id', $id)->where('uid', $uid)->where('is_refund', 0)->count();
    }

    /**
     * 获取一条拼团信息
     * @param int $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPinkUserOne(int $id)
    {
        return $this->search()->with('getProduct')->find($id);
    }

    /**
     * 获取拼团信息
     * @param array $where
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPinkUserList(array $where)
    {
        return $this->getModel()->where($where)->with('getProduct')->select()->toArray();
    }

    /**
     * 获取拼团结束的列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function pinkListEnd()
    {
        return $this->getModel()->where('stop_time', '<=', time())
            ->where('status', 1)
            ->where('k_id', 0)
            ->where('is_refund', 0)
            ->field('id,people,k_id,uid,stop_time')->select()->toArray();
    }
}
