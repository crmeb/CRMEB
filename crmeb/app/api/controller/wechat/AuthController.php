<?php


namespace app\api\controller\wechat;


use app\models\user\WechatUser;
use app\Request;
use crmeb\services\CacheService;
use crmeb\services\MiniProgramService;
use crmeb\services\UtilService;
use app\models\user\UserToken;
use crmeb\services\SystemConfigService;
use app\models\user\User;
use app\models\routine\RoutineFormId;
use think\facade\Cache;
use crmeb\services\SubscribeTemplateService;

/**
 * 小程序相关
 * Class AuthController
 * @package app\api\controller\wechat
 */
class AuthController
{

    /**
     * 小程序授权登录
     * @param Request $request
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function mp_auth(Request $request)
    {
        $cache_key = '';
        list($code, $post_cache_key, $login_type) = UtilService::postMore([
            ['code', ''],
            ['cache_key', ''],
            ['login_type', '']
        ], $request, true);
        $session_key = Cache::get('eb_api_code_' . $post_cache_key);
        if (!$code && !$session_key)
            return app('json')->fail('授权失败,参数有误');
        if ($code && !$session_key) {
            try {
                $userInfoCong = MiniProgramService::getUserInfo($code);
                $session_key = $userInfoCong['session_key'];
                $cache_key = md5(time() . $code);
                Cache::set('eb_api_code_' . $cache_key, $session_key, 86400);
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
        $uid = WechatUser::routineOauth($userInfo);
        $userInfo = User::where('uid', $uid)->find();
        if ($userInfo->login_type == 'h5' && ($h5UserInfo = User::where(['account' => $userInfo->phone, 'phone' => $userInfo->phone, 'user_type' => 'h5'])->find()))
            $token = UserToken::createToken($userInfo, 'routine');
        else
            $token = UserToken::createToken($userInfo, 'routine');
        if ($token) {
            event('UserLogin', [$userInfo, $token]);
            return app('json')->successful('登陆成功！', [
                'token' => $token->token,
                'userInfo' => $userInfo,
                'expires_time' => strtotime($token->expires_time),
                'cache_key' => $cache_key
            ]);
        } else
            return app('json')->fail('获取用户访问token失败!');
    }

    /**
     * 获取授权logo
     * @param Request $request
     * @return mixed
     */
    public function get_logo(Request $request)
    {
        $logoType = $request->get('type', 1);
        switch ((int)$logoType) {
            case 1:
                $logo = sys_config('routine_logo');
                break;
            case 2:
                $logo = sys_config('wechat_avatar');
                break;
            default:
                $logo = '';
                break;
        }
        if (strstr($logo, 'http') === false && $logo) $logo = sys_config('site_url') . $logo;
        return app('json')->successful(['logo_url' => str_replace('\\', '/', $logo)]);
    }

    /**
     * 保存form id
     * @param Request $request
     * @return mixed
     */
    public function set_form_id(Request $request)
    {
        $formId = $request->post('formId', '');
        if (!$formId) return app('json')->fail('缺少form id');
        return app('json')->successful('保存form id 成功！', ['uid' => $request->uid()]);
    }

    /**
     * 小程序支付回调
     */
    public function notify()
    {
        MiniProgramService::handleNotify();
    }

    /**
     * 获取小程序订阅消息id
     * @return mixed
     */
    public function teml_ids()
    {
        $temlIdsName = SubscribeTemplateService::getConstants();
        $temlIdsList = CacheService::get('TEML_IDS_LIST', function () use ($temlIdsName) {
            $temlId = [];
            foreach ($temlIdsName as $key => $item) {
                $temlId[strtolower($key)] = SubscribeTemplateService::setTemplateId($item);
            }
            return $temlId;
        });
        return app('json')->success($temlIdsList);
    }

    /**
     * 获取小程序直播列表
     * @param Request $request
     * @return mixed
     */
    public function live(Request $request)
    {
        [$page, $limit] = UtilService::getMore([
            ['page', 1],
            ['limit', 10],
        ], $request, true);
        $list = CacheService::get('WECHAT_LIVE_LIST_' . $page . '_' . $limit, function () use ($page, $limit) {
            $list = MiniProgramService::getLiveInfo($page, $limit);
            foreach ($list as &$item) {
                $item['_start_time'] = date('m-d H:i', $item['start_time']);
            }
            return $list;
        }, 600);
        return app('json')->success($list);
    }
}