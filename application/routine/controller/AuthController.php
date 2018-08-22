<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/11
 */

namespace app\routine\controller;

use app\routine\model\user\User;
use service\JsonService;
use think\Controller;
use think\Request;
use think\Session;

class AuthController extends Controller
{
    public $userInfo = [];
    protected function _initialize()
    {
        parent::_initialize();
        $uid = Request::instance()->get('uid',0);
        $userInfo = User::get($uid);
        if($userInfo) $userInfo->toArray();
        else return JsonService::fail('没有获取用户UID');
        $this->userInfo = $userInfo;//根据uid获取用户信息
    }
}