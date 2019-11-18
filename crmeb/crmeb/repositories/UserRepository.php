<?php

namespace crmeb\repositories;

use app\admin\model\system\SystemAttachment;
use app\models\user\User;
use app\models\user\UserAddress;
use app\models\user\UserToken;
use crmeb\exceptions\AuthException;
use think\db\exception\ModelNotFoundException;
use think\db\exception\DataNotFoundException;
use think\exception\DbException;

/**
 * Class UserRepository
 * @package crmeb\repositories
 */
class UserRepository
{
    /**
     * 管理员后台给用户添加金额
     * @param $user
     * $user 用户信息
     * @param $money
     * $money 添加的金额
     */
    public static function adminAddMoney($user, $money)
    {

    }

    /**
     * 管理员后台给用户减少金额
     * @param $user
     * $user 用户信息
     * @param $money
     * $money 减少的金额
     */
    public static function adminSubMoney($user, $money)
    {

    }

    /**
     * 管理员后台给用户增加的积分
     * @param $user
     * $user 用户信息
     * @param $integral
     * $integral 增加的积分
     */
    public static function adminAddIntegral($user, $integral)
    {

    }

    /**
     * 管理员后台给用户减少的积分
     * @param $user
     * $user 用户信息
     * @param $integral
     * $integral 减少的积分
     */
    public static function adminSubIntegral($user, $integral)
    {

    }

    /**
     * 获取授权信息
     * @param string $token
     * @return array
     * @throws AuthException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function parseToken($token): array
    {
        if (!$token || !$tokenData = UserToken::where('token', $token)->find())
            throw new AuthException('请登录', 410000);
        try {
            [$user, $type] = User::parseToken($token);
        } catch (\Throwable $e) {
            $tokenData->delete();
            throw new AuthException('登录已过期,请重新登录', 410001);
        }

        if (!$user || $user->uid != $tokenData->uid) {
            $tokenData->delete();
            throw new AuthException('登录状态有误,请重新登录', 410002);
        }
        $tokenData->type = $type;
        return compact('user', 'tokenData');
    }
    /**
     * 订单创建成功后
     * @param $order
     * @param $group
     */
    public static function storeProductOrderCreateEbApi($order,$group)
    {
        if(!UserAddress::be(['is_default'=>1,'uid'=>$order['uid']])) UserAddress::setDefaultAddress($group['addressId'],$order['uid']);
    }
}