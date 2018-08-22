<?php

namespace app\routine\controller;

use app\routine\model\user\RoutineUser;
use behavior\routine\RoutineBehavior;
use service\JsonService;
use service\UtilService;
use think\Controller;
use think\Request;
use think\Session;

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
        $data['openid'] = $res['openid'];
        $data['session_key'] = $res['session_key'];
        $data['uid'] = RoutineUser::routineOauth($data);
        return JsonService::successful($data);
    }

    /**
     * 根据前台传code  获取 openid 和  session_key //会话密匙
     * @param string $code
     * @return array|mixed
     */
    public function setCode($code = ''){
        if($code == '') return [];
        $routineAppId = 'wx7bc36cccc15e4be2';//小程序appID
        $routineAppSecret = 'a13757487f35b0ad88c03455b1903c4d';//小程序AppSecret
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$routineAppId.'&secret='.$routineAppSecret.'&js_code='.$code.'&grant_type=authorization_code';
        return json_decode(RoutineBehavior::curlGet($url),true);
    }
}