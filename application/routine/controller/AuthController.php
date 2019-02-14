<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/11
 */

namespace app\routine\controller;

use app\routine\model\user\User;
use app\routine\model\user\WechatUser;
use service\JsonService;
use think\Controller;
use think\Request;

class AuthController extends Controller
{
    protected $uid = 0;
    protected $userInfo = [];
    protected function _initialize()
    {
        parent::_initialize();

        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            if(!empty(input('openid'))){
                $uid = WechatUser::openidTouid(input('openid'));
                $userInfo = User::get($uid);
            }else{
                $uid = Request::instance()->get('uid',0);
                $userInfo = User::get($uid);
            }
            if($userInfo) $userInfo->toArray();
            else return JsonService::fail('没有获取用户UID');
            $this->userInfo = $userInfo;//根据uid获取用户信息
        } else {
            echo "非法访问";exit;
        }


    }
}