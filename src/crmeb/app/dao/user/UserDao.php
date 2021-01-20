<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\dao\user;

use app\dao\BaseDao;
use app\model\user\User;

/**
 * 用户
 * Class UserDao
 * @package app\dao\user
 */
class UserDao extends BaseDao
{

    protected function setModel(): string
    {
        return User::class;
    }

    /**
     * 获取用户列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', int $page, int $limit): array
    {
        return $this->search($where)->field($field)->with(['label'])->page($page, $limit)->select()->toArray();
    }

    /**
     * 获取特定条件的总数
     * @param array $where
     * @param bool $is_list
     * @return array|int
     */
    public function getCount(array $where, bool $is_list = false)
    {
        if ($is_list)
            return $this->getModel()->where($where)->group('uid')->fetchSql(true)->column('count(*) as user_count', 'uid');
        else
            return $this->getModel()->where($where)->count();
    }

    /**
     * 某个字段累加某个数值
     * @param string $field
     * @param int $num
     */
    public function incField(int $uid, string $field, int $num = 1)
    {
        return $this->getModel()->where('uid', $uid)->inc($field, $num)->update();
    }

    /**
     * @param $uid
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserLabel($uid, $field = '*')
    {
        return $this->search(['uid' => $uid])->field($field)->with(['label'])->select()->toArray();
    }

    /**
     * 某个条件 用户某个字段总和
     * @param array $where
     * @param string $filed
     * @return float
     */
    public function getWhereSumField(array $where, string $filed)
    {
        return $this->search($where)->sum($filed);
    }

    /**
     * 根据条件查询对应的用户信息以数组形式返回
     * @param array $where
     * @param string $field
     * @param string $key
     * @return array
     */
    public function getUserInfoArray(array $where, string $field, string $key)
    {
        return $this->search($where)->column($field, $key);
    }

    /**
     * 获取特定时间用户访问量
     * @param $time
     * @param $week
     * @return int
     */
    public function todayLastVisit($time, $week)
    {
        switch ($week) {
            case 1:
                return $this->search(['time' => $time ?: 'today', 'timeKey' => 'last_time'])->count();
            case 2:
                return $this->search(['time' => $time ?: 'week', 'timeKey' => 'last_time'])->count();
        }
    }

    /**
     * 获取特定时间用户访问量
     * @param $time
     * @param $week
     * @return int
     */
    public function todayAddVisit($time, $week)
    {
        switch ($week) {
            case 1:
                return $this->search(['time' => $time ?: 'today', 'timeKey' => 'add_time'])->count();
            case 2:
                return $this->search(['time' => $time ?: 'week', 'timeKey' => 'add_time'])->count();
        }
    }

    /**
     * 获取特定时间内用户列表
     * @param $starday
     * @param $yesterday
     * @return mixed
     */
    public function userList($starday, $yesterday)
    {
        return $this->getModel()->where('add_time', 'between time', [$starday, $yesterday])
            ->field("FROM_UNIXTIME(add_time,'%m-%e') as day,count(*) as count")
            ->group("FROM_UNIXTIME(add_time, '%Y%m%e')")
            ->order('add_time asc')
            ->select()->toArray();
    }

    /**
     * 购买量范围的用户数量
     * @param $status
     * @return int
     */
    public function userCount($status)
    {
        switch ($status) {
            case 1:
                return $this->getModel()->where('pay_count', '>', 0)->where('pay_count', '<', 4)->count();
            case 2:
                return $this->getModel()->where('pay_count', '>', 4)->count();
        }
    }

    /**
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserInfoList(array $where, $field = "*"): array
    {
        return $this->search($where)->field($field)->select()->toArray();
    }
}
