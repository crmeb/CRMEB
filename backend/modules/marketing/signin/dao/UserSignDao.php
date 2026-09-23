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
declare (strict_types=1);

namespace modules\marketing\signin\dao;

use crmeb\basic\BaseDao;
use modules\marketing\signin\model\UserSign;

/**
 *
 * Class UserSignDao
 * @package modules\marketing\signin\dao
 */
class UserSignDao extends BaseDao
{

    /**
     * 设置模型
     * @return string
     */
    protected function setModel(): string
    {
        return UserSign::class;
    }

    /**
     * 查询指定自然日是否签到，写入场景须先获取用户行锁。
     * @param int $uid
     * @param int $timestamp
     * @return bool
     */
    public function hasSignOnDate(int $uid, int $timestamp): bool
    {
        $start = strtotime('today', $timestamp);
        $end = strtotime('tomorrow', $timestamp);
        return (bool)$this->getDb()->where('uid', $uid)->where('add_time', '>=', $start)
            ->where('add_time', '<', $end)->count();
    }

    /**
     * 获取列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field, int $page, int $limit)
    {
        return $this->search($where)->field($field)->order('id desc')->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->select()->toArray();
    }

    /**
     * 获取列表
     * @param array $where
     * @param string $field
     * @param int $page
     * @param int $limit
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getListGroup(array $where, string $field, int $page, int $limit, string $group)
    {
        return $this->search($where)->field($field)->order('id desc')->group($group)->page($page, $limit)->select()->toArray();
    }

    /**
     * 获取周或者月的累积签到次数
     * @param $type
     * @param $uid
     * @param int|null $timestamp 统计所属周期的时间
     * @return int
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/1
     */
    public function getCumulativeDays($type, $uid, ?int $timestamp = null)
    {
        $query = $this->getDb()->where('uid', $uid);
        $timestamp ??= time();
        if ($type == 1 || $type == 0) {
            $start = strtotime($type == 1 ? 'monday this week midnight' : 'first day of this month midnight', $timestamp);
            $end = strtotime($type == 1 ? '+1 week' : '+1 month', $start);
            $query->where('add_time', '>=', $start)->where('add_time', '<', $end);
        }
        return $query->count();
    }

    /**
     * 获取本周或者本月的签到列表
     * @param $type
     * @param $uid
     * @param int|null $timestamp 显示所属周期的时间
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/8
     */
    public function getUserSignList($type, $uid, ?int $timestamp = null): array
    {
        $timestamp ??= time();
        $start = strtotime($type == 1 ? 'monday this week midnight' : 'first day of this month midnight', $timestamp);
        $end = strtotime($type == 1 ? '+1 week' : '+1 month', $start);
        return $this->getDb()->where('uid', $uid)->where('add_time', '>=', $start)
            ->where('add_time', '<', $end)->field('id,uid,add_time,number')->order('id asc')->select()->toArray();
    }
}
