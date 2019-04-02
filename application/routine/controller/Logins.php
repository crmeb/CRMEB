<?php

namespace app\routine\controller;

use app\admin\model\system\SystemConfig;
use app\routine\model\routine\RoutineServer;
use app\routine\model\user\RoutineUser;
use service\JsonService;
use service\UtilService;
use service\MiniProgramService;
use think\Controller;
use think\Request;

class Logins extends Controller{

    /**
     * 获取用户信息
     * @param Request $request
     * @return \think\response\Json
     */
    public function index(Request $request){
        $data = UtilService::postMore([['info',[]]],$request);//获取前台传的code
        $data = $data['info'];
        unset($data['info']);
        //解密获取用户信息
        $iv = urlencode($data['iv']);
        $data['iv'] = urldecode($iv);
        try{
            $userInfo = $this->decryptCode($data['session_key'], $data['iv'], $data['encryptedData']);
            if(!isset($userInfo['openId'])) return JsonService::fail('openid获取失败');
            if(!isset($userInfo['unionId']))  $userInfo['unionid'] = '';
            $userInfo['session_key'] = $data['session_key'];
            $userInfo['spid'] = $data['spid'];//推广人ID
            $userInfo['spreadid'] = (int)$data['spreadid'];//推广人ID 2.5.36
            $dataOauthInfo = RoutineUser::routineOauthnew($userInfo);
            $userInfo['uid'] = $dataOauthInfo['uid'];
            $userInfo['page'] = $dataOauthInfo['page'];
            $userInfo['status'] = RoutineUser::isUserStatus($userInfo['uid']);
            $userInfo['uidShare'] = RoutineUser::isUserShare($userInfo['uid']);//我的推广二维码ID
            return JsonService::successful($userInfo);
        }catch (\Exception $e){
            return JsonService::fail('error',$e->getMessage());
        }

    }


    /**
     * 根据前台传code  获取 openid 和  session_key //会话密匙
     * @param string $code
     * @return array|mixed
     */
    public function setCode(Request $request){
        $data = UtilService::postMore([['info', []]], $request);//获取前台传的code
//        var_dump($data);die;
        $code = '';
        if(isset($data['info']['code']))
            $code = $data['info']['code'];
        else
            JsonService::fail('未获取到code');
        if($code == '') return [];
        $info = MiniProgramService::getUserInfo($code);
        return $info;
    }

    /**
     * 解密数据
     * @param string $code
     * @return array|mixed
     */
    public function decryptCode($session = '', $iv = '', $encryptData = '')
    {
        if (!$session) return JsonService::fail('session参数错误');
        if (!$iv) return JsonService::fail('iv参数错误');
        if (!$encryptData) return JsonService::fail('encryptData参数错误');
        return MiniProgramService::encryptor($session, $iv, $encryptData);
    }

    /**
     * 获取网站logo
     */
    public function get_enter_logo(){
        $siteLogo = SystemConfig::getValue('routine_logo');
        $siteName = SystemConfig::getValue('routine_name');
        $data['site_logo'] = $siteLogo;
        $data['site_name'] = $siteName;
        return JsonService::successful($data);
    }
    /**
     * 获取网站顶部颜色
     */
    public function get_routine_config(){
        $routineConfig = SystemConfig::getMore('site_name,site_logo,site_url,site_close,site_service_phone,routine_logo,routine_name,routine_style');
        $data['routine_config'] = $routineConfig;
        return JsonService::successful($data);
    }
    /**
     * 获取网站顶部颜色
     */
    public function get_routine_style(){
        $routineStyle = SystemConfig::getValue('routine_style');
        $data['routine_style'] = $routineStyle;
        return JsonService::successful($data);
    }

    /**
     * 获取客服电话
     */
    public function get_site_service_phone(){
        $siteServicePhone = SystemConfig::getValue('site_service_phone');
        $data['site_service_phone'] = $siteServicePhone;
        return JsonService::successful($data);
    }
}