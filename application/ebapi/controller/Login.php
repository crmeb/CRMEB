<?php

namespace app\ebapi\controller;


use think\Cache;
use think\Controller;
use think\Request;
use service\JsonService;
use service\UtilService;
use app\core\util\MiniProgramService;
use app\core\util\TokenService;
use app\ebapi\model\user\WechatUser;
use app\core\logic\Login as CoreLogin;

class Login extends Controller
{

    /*
     * 执行登录
     * */
    public function _empty($name)
    {
        CoreLogin::login_ing($name);
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return \think\response\Json
     */
    public function index(Request $request){
        //待完善
        $data = UtilService::postMore([
            ['spid',0],
            ['code',''],
            ['iv',''],
            ['encryptedData',''],
            ['cache_key',''],
        ],$request);//获取前台传的code
        if(!Cache::has('eb_api_code_'.$data['cache_key'])) return JsonService::status('410','获取会话密匙失败');
        $data['session_key']=Cache::get('eb_api_code_'.$data['cache_key']);
        try{
            //解密获取用户信息
            $userInfo = $this->decryptCode($data['session_key'], $data['iv'], $data['encryptedData']);
        }catch (\Exception $e){
            if($e->getCode()=='-41003') return JsonService::status('410','获取会话密匙失败');
        }
        if(!isset($userInfo['openId'])) return JsonService::fail('openid获取失败');
        if(!isset($userInfo['unionId']))  $userInfo['unionId'] = '';
        $userInfo['session_key'] = $data['session_key'];
        $userInfo['spid'] = $data['spid'];
        $userInfo['code'] = $data['code'];
        $dataOauthInfo = WechatUser::routineOauth($userInfo);
        $userInfo['uid'] = $dataOauthInfo['uid'];
        $userInfo['page'] = $dataOauthInfo['page'];
        $userInfo['token'] = TokenService::getToken($userInfo['uid'],$userInfo['openId']);
        if($userInfo['token']===false) return JsonService::fail('获取用户访问token失败!');
        $userInfo['status'] = WechatUser::isUserStatus($userInfo['uid']);
        return JsonService::successful($userInfo);
    }

    /**
     * 根据前台传code  获取 openid 和  session_key //会话密匙
     * @param string $code
     * @return array|mixed
     */
    public function setCode(Request $request){
        list($code) = UtilService::postMore([['code', '']], $request, true);//获取前台传的code
        if ($code == '') return JsonService::fail('');
        try{
            $userInfo = MiniProgramService::getUserInfo($code);
        }catch (\Exception $e){
            return JsonService::fail('获取session_key失败，请检查您的配置！',['line'=>$e->getLine(),'message'=>$e->getMessage()]);
        }
        $cache_key = md5(time().$code);
        if (isset($userInfo['session_key'])){
            Cache::set('eb_api_code_'.$cache_key, $userInfo['session_key'], 86400);
            return JsonService::successful(['cache_key'=>$cache_key]);
        }else
            return JsonService::fail('获取会话密匙失败');
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

}