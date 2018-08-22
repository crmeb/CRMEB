<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\routine\model\user;

use basic\ModelBasic;
use traits\ModelTrait;

/**
 * 微信用户model
 * Class WechatUser
 * @package app\routine\model\user
 */
class WechatUser extends ModelBasic
{
    use ModelTrait;

    public static function getOpenId($uid = ''){
        if($uid == '') return false;
        return self::where('uid',$uid)->value('openid');
    }


}