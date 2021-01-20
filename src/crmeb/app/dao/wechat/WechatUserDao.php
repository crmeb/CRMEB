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

namespace app\dao\wechat;

use app\dao\BaseDao;
use app\model\wechat\WechatUser;

/**
 *
 * Class UserWechatUserDao
 * @package app\dao\user
 */
class WechatUserDao extends BaseDao
{
    /**
     * @return string
     */
    protected function setModel(): string
    {
        return WechatUser::class;
    }

    /**
     * 获取wechat_user表数据
     * @param array $where
     * @param string $field
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList(array $where, string $field = '*', string $order = 'id desc', int $page = 0, int $limit = 0)
    {
        return $this->getModel()->where($where)->field($field)->order($order)->when($page && $limit, function ($query) use ($page, $limit) {
            $query->page($page, $limit);
        })->select()->toArray();
    }

    /**
     * 获取微信用户统计数据
     * @param $time
     * @param $where
     * @param $timeType
     * @param $key
     * @return mixed
     */
    public function getWechatTrendData($time, $where, $timeType, $key)
    {
        return $this->getModel()->where('user_type', 'wechat')
            ->where($where)
            ->where(function ($query) use ($time) {
                if ($time[0] == $time[1]) {
                    $query->whereDay('add_time', $time[0]);
                } else {
                    $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                    $query->whereTime('add_time', 'between', $time);
                }
            })->field("FROM_UNIXTIME(add_time,'$timeType') as days,count(uid) as $key")
            ->group('days')->select()->toArray();
    }

    /**
     * 地域全部用户
     * @param $time
     * @param $userType
     * @return mixed
     */
    public function getRegionAll($time, $userType)
    {
        return $this->getModel()->when($userType != '', function ($query) use ($userType) {
            $query->where('user_type', $userType);
        })->whereTime('add_time', '<', strtotime($time[1]) + 86400)->field('count(uid) as allNum,province')
            ->group('province')->select()->toArray();
    }

    /**
     * 地域新增用户
     * @param $time
     * @param $userType
     * @return mixed
     */
    public function getRegionNew($time, $userType)
    {
        return $this->getModel()->when($userType != '', function ($query) use ($userType) {
            $query->where('user_type', $userType);
        })->where(function ($query) use ($time) {
                if ($time[0] == $time[1]) {
                    $query->whereDay('add_time', $time[0]);
                } else {
                    $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                    $query->whereTime('add_time', 'between', $time);
                }
            })->field('count(uid) as newNum,province')
            ->group('province')->select()->toArray();
    }

    /**
     * 获取用户性别
     * @param $time
     * @param $userType
     * @return mixed
     */
    public function getSex($time, $userType)
    {
        return $this->getModel()->when($userType != '', function ($query) use ($userType) {
            $query->where('user_type', $userType);
        })->where(function ($query) use ($time) {
                if ($time[0] == $time[1]) {
                    $query->whereDay('add_time', $time[0]);
                } else {
                    $time[1] = date('Y/m/d', strtotime($time[1]) + 86400);
                    $query->whereTime('add_time', 'between', $time);
                }
            })->field('count(uid) as value,sex as name')
            ->group('sex')->select()->toArray();
    }

    /**
     * 获取公众号或者小程序的openid
     * @param int $uid
     * @return mixed
     */
    public function getWechatOpenid(int $uid)
    {
        return $this->getModel()->where('uid', $uid)->where(function ($qurey) {
            $qurey->where('user_type', 'wechat');//->whereOr('user_type', 'routine');
        })->value('openid');
    }
}
