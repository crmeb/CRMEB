<?php

namespace app\routine\controller;

use app\admin\model\system\SystemConfig;
use app\routine\model\routine\RoutineServer;
use app\routine\model\user\RoutineUser;
use service\JsonService;
use service\UtilService;
use think\Controller;
use think\Request;

class Login extends Controller{



    /**
     * 获取用户信息
     * @param Request $request
     * @return \think\response\Json
     */

    public function index(Request $request){
        $data = UtilService::postMore([['info',[]]],$request);//获取前台传的code
        $data = $data['info'];
        unset($data['info']);
        $res = $this->setCode($data['code']);
        if(!isset($res['openid'])) return JsonService::fail('openid获取失败');
        if(isset($res['unionid'])) $data['unionid'] = $res['unionid'];
        else $data['unionid'] = '';
        $data['routine_openid'] = $res['openid'];
        $data['session_key'] = $res['session_key'];
        $dataOauthInfo = RoutineUser::routineOauth($data);
        $data['uid'] = $dataOauthInfo['uid'];
        $data['page'] = $dataOauthInfo['page'];
        $data['status'] = RoutineUser::isUserStatus($data['uid']);
        return JsonService::successful($data);
    }

    /**
     * 根据前台传code  获取 openid 和  session_key //会话密匙
     * @param string $code
     * @return array|mixed
     */
    public function setCode($code = ''){
        if($code == '') return [];
        $routineAppId = SystemConfig::getValue('routine_appId');//小程序appID
        $routineAppSecret = SystemConfig::getValue('routine_appsecret');//小程序AppSecret
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$routineAppId.'&secret='.$routineAppSecret.'&js_code='.$code.'&grant_type=authorization_code';
        return json_decode(RoutineServer::curlGet($url),true);
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