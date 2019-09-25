<?php


namespace app\api\controller\wechat;


use app\models\user\WechatUser;
use app\Request;
use crmeb\services\MiniProgramService;
use crmeb\services\UtilService;
use app\models\user\UserToken;
use crmeb\services\SystemConfigService;
use app\models\user\User;
use app\models\routine\RoutineFormId;
use think\facade\Cache;

class AuthController
{
    /*
     * 小程序授权登录
     * @param json
     *
     * */
    public function mp_auth(Request $request)
    {
        $cache_key = '';
        list($code,$post_cache_key,$login_type) = UtilService::postMore([
            ['code',''],
            ['cache_key',''],
            ['login_type','']
        ],$request,true);
        $session_key = Cache::get('eb_api_code_'.$post_cache_key);
        if (!$code && !$session_key)
            return app('json')->fail('授权失败,参数有误');
        if($code && !$session_key){
            try {
                $userInfoCong = MiniProgramService::getUserInfo($code);
                $session_key = $userInfoCong['session_key'];
                $cache_key = md5(time().$code);
                Cache::set('eb_api_code_'.$cache_key,$session_key,86400);
            } catch (\Exception $e) {
                return app('json')->fail('获取session_key失败，请检查您的配置！', ['line' => $e->getLine(), 'message' => $e->getMessage()]);
            }
        }

        $data = UtilService::postMore([
            ['spread_spid', 0],
            ['spread_code', ''],
            ['iv', ''],
            ['encryptedData', ''],
        ]);//获取前台传的code
        try {
            //解密获取用户信息
            $userInfo = MiniProgramService::encryptor($session_key, $data['iv'], $data['encryptedData']);
        } catch (\Exception $e) {
            if ($e->getCode() == '-41003') return app('json')->fail('获取会话密匙失败');
        }
        if (!isset($userInfo['openId'])) return app('json')->fail('openid获取失败');
        if (!isset($userInfo['unionId'])) $userInfo['unionId'] = '';
        $userInfo['spid'] = $data['spread_spid'];
        $userInfo['code'] = $data['spread_code'];
        $userInfo['session_key'] = $session_key;
        $userInfo['login_type'] = $login_type;
        $dataOauthInfo = WechatUser::routineOauth($userInfo);
        $userInfo['uid'] = $dataOauthInfo['uid'];
        $userInfo['page'] = $dataOauthInfo['page'];
        $userInfo = User::where('uid',$dataOauthInfo['uid'])->find();
        if($userInfo->login_type == 'h5' && ($h5UserInfo = User::where(['account'=>$userInfo->phone,'phone'=>$userInfo->phone,'user_type'=>'h5'])->find()))
            $token = UserToken::createToken($userInfo, 'routine');
        else
            $token = UserToken::createToken($userInfo, 'routine');
        if($token) {
            event('UserLogin', [$userInfo, $token]);
            return app('json')->successful('登陆成功！', [
                'token' => $token->token,
                'userInfo' => $userInfo,
                'expires_time' => strtotime($token->expires_time),
                'cache_key' => $cache_key
            ]);
        }else
            return app('json')->fail('获取用户访问token失败!');
    }

    /*
     * 获取授权logo
     * */
    public function get_logo(Request $request)
    {
        $logoType = $request->get('type',1);
        switch ((int)$logoType){
            case 1:
                $logo=SystemConfigService::get('routine_logo');
                break;
            case 2:
                $logo=SystemConfigService::get('wechat_avatar');
                break;
            default:
                $logo = '';
                break;
        }
        if(strstr($logo,'http')===false && $logo) $logo = SystemConfigService::get('site_url').$logo;
        return app('json')->successful(['logo_url'=>str_replace('\\','/',$logo)]);
    }

    /*
     * 保存form id
     *
     * */
    public function set_form_id(Request $request)
    {
        $formId = $request->post('formId','');
        if(!$formId) return app('json')->fail('缺少form id');
        RoutineFormId::SetFormId($formId,$request->uid);
        return app('json')->successful('保存form id 成功！',['uid'=>$request->uid]);
    }

    /*
     * 小程序支付回调
     * */
    public function notify()
    {
        MiniProgramService::handleNotify();
    }
}