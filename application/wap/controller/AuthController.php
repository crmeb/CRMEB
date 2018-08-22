<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/11
 */

namespace app\wap\controller;


use app\wap\model\store\StoreOrder;
use app\wap\model\user\User;
use app\wap\model\user\WechatUser;
use basic\WapBasic;
use service\UtilService;
use think\Cookie;
use think\Session;
use think\Url;

class AuthController extends WapBasic
{
    /**
     * 用户ID
     * @var int
     */
    protected $uid;

    /**
     * 用户信息
     * @var
     */
    protected $userInfo;

    protected function _initialize()
    {
        parent::_initialize();
        try{
            $uid = User::getActiveUid();
        }catch (\Exception $e){
            Cookie::set('is_login',0);
            $url=$this->request->url(true);
            return $this->redirect(Url::build('Login/index',['ref'=>base64_encode(htmlspecialchars($url))]));
        }
        $this->userInfo = User::getUserInfo($uid);
        if(!$this->userInfo || !isset($this->userInfo['uid'])) return $this->failed('读取用户信息失败!');
        if(!$this->userInfo['status']) return $this->failed('已被禁止登陆!');
        $this->uid = $this->userInfo['uid'];
        $this->assign('userInfo',$this->userInfo);
    }

}