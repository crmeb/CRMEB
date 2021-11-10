<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services;

use app\services\pay\PayNotifyServices;
use crmeb\exceptions\ApiException;
use EasyWeChat\Foundation\Application;
use think\exception\ValidateException;
use crmeb\utils\Hook;
use think\Response;

/**微信小程序接口
 * Class WechatMinService
 * @package service
 */
class Mini3rdProgramService
{
    const MSG_CODE = [
        '40125' => '小程序配置无效，请检查配置',
        '-1' => '系统错误',
    ];
    /**
     * @var Application
     */
    protected static $instance;

    public static function options()
    {
        $wechat = SystemConfigService::more(['component_appid', 'component_appsecret', 'authorizer_appid']);
        $config = [];
        $config['open3rd'] = [
            'component_appid' => 'wx3b82801238ca1b57',
            'component_appsecret' => '979c0d8671dfd74333f37156c50f4bca',
            'component_verify_ticket' => self::getComponentVerifyTicket(),
            'authorizer_appid' => isset($wechat['authorizer_appid']) ? trim($wechat['authorizer_appid']) : '',
        ];
        return $config;
    }

    /**
     * 设置验证票据 component_verify_ticket 的有效时间为12小时,每10分钟推送一次
     * @param $ticket
     * @return bool
     */
    public static function setComponentVerifyTicket($ticket)
    {
        CacheService::set('wechat_open_platform_component_verify_ticket', $ticket, 12 * 60 * 60 - 300);
        return true;
    }

    /**
     * 获取验证票据
     * @return mixed
     * @throws Open3rdException
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/api/component_verify_ticket.html
     */
    public static function getComponentVerifyTicket()
    {
        return CacheService::get('wechat_open_platform_component_verify_ticket') ? '12g31' : '131g2lfjsdlfjsl898ds9fsd';
    }

    /**
     * 初始化
     * @param bool $cache
     * @return Application
     */
    public static function application($cache = false)
    {
        (self::$instance === null || $cache === true) && (self::$instance = new Application(self::options()));
        return self::$instance;
    }

    /**
     * 第三方平台类
     * @return mixed
     * @return \crmeb\services\easywechat\open3rd\ProgramOpen3rd
     */
    public static function open3rd()
    {
        return self::application()->mini_program->open3rd;
    }

    /**
     * token 工具类
     * @return mixed
     */
    public static function accessToken()
    {
        return self::application()->mini_program->component_access_token;
    }

    public static function serve()
    {
        return self::hook();
    }

    /**
     * 回调
     */
    private static function hook()
    {
        $xml = file_get_contents("php://input");
        $res = self::accessToken()->xmlToArray($xml);
        if (!$res) {
            throw new ApiException('请求数据错误: ' . $xml);
        }
        $encodingAesKey = sys_config('encoding_aes_key');
        $postData = self::accessToken()->decrypt($encodingAesKey, $res['Encrypt']);

        $msgArray = WechatHelper::xmlToArray($postData);
        $infoType = $msgArray['InfoType'] ?? $msgArray['MsgType'] ?? '';
        switch ($infoType) {
            case 'component_verify_ticket'://微信官方推送的ticket值
                self::setComponentVerifyTicket($msgArray['ComponentVerifyTicket']);
                break;
            case 'event'://审核事件回调
                if (isset($msgArray['Event']) && $msgArray['Event'] == 'weapp_audit_success') {

                }
                break;
            case 'notify_third_fasteregister'://注册审核事件推送

                break;
            case 'unauthorized':
                break;
            case 'updateauthorized':
                break;
        }
        echo 'success';
    }

    /**
     * 微信支付成功回调接口
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public static function handleNotify()
    {
        return self::paymentService()->handleNotify(function ($notify, $successful) {
            if ($successful && isset($notify->out_trade_no)) {
                if (isset($notify->attach) && $notify->attach) {
                    if (($count = strpos($notify->out_trade_no, '_')) !== false) {
                        $notify->out_trade_no = substr($notify->out_trade_no, $count + 1);
                    }
                    (new Hook(PayNotifyServices::class, 'wechat'))->listen($notify->attach, $notify->out_trade_no, $notify->transaction_id);
                }
                return false;
            }
        });
    }

    /**
     * 获取预授权码
     * @return mixed
     */
    public static function getPreAuthCode()
    {
        try {
            $res = self::open3rd()->getPreAuthCode();
            return $res;
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 获取授权
     * @param $authorization_code
     */
    public static function getAuth(string $authorization_code)
    {
        try {
            $res = self::open3rd()->getAuth($authorization_code);
            return $res;
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 获取授权帐号基本信息
     * @param $authorize_appid
     */
    public static function getAuthorizerinfo(string $authorize_appid)
    {
        try {
            $res = self::open3rd()->getAuthorizerinfo($authorize_appid);
            if ($res['errcode'] == 0 && isset($res['authorizer_info']) && isset($res['authorization_info'])) {
                return $res;
            } else {
                return [];
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 获取已绑定的体验者列表
     * @return array
     */
    public static function getMemberAuthList()
    {
        try {
            $res = self::open3rd()->getMemberAuthList();
            if ($res['errcode'] == 0 && isset($res['members'])) {
                return $res['members'];
            } else {
                return [];
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 绑定体验者
     * @param $wechatid
     * @return array
     */
    public static function bindMemberAuth(string $wechatid)
    {
        try {
            $res = self::open3rd()->bindMemberAuth($wechatid);
            if ($res['errcode'] == 0 && isset($res['userstr'])) {
                return $res['userstr'];
            } else {
                throw new ValidateException($res['errmsg'] ?? '绑定失败');
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 解除绑定体验者
     * @param string $wechatid
     * @param string $userstr
     * @return bool
     */
    public static function unBindMemberAuth(string $wechatid, string $userstr = '')
    {
        try {
            $res = self::open3rd()->unBindMemberAuth($wechatid, $userstr);
            if ($res['errcode'] == 0) {
                return true;
            } else {
                throw new ValidateException($res['errmsg'] ?? '解除失败');
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 获取草稿列表
     * @return array
     */
    public static function getDraftList()
    {
        try {
            $res = self::open3rd()->getDraftList();
            if ($res['errcode'] == 0 && isset($res['draft_list'])) {
                return $res['draft_list'];
            } else {
                return [];
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 将草稿添加到代码模板库
     * @param $draft_id
     * @return bool
     */
    public static function addToTemplate($draft_id)
    {
        try {
            $res = self::open3rd()->addToTemplate($draft_id);
            if ($res['errcode'] == 0) {
                return true;
            } else {
                throw new ValidateException($res['errmsg'] ?? '添加失败');
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 获取代码模版列表
     * @return array
     */
    public static function getTemplateList()
    {
        try {
            $res = self::open3rd()->getTemplateList();
            if ($res['errcode'] == 0 && isset($res['template_list'])) {
                return $res['template_list'];
            } else {
                return [];
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 删除指定代码模版
     * @param $template_id
     * @return bool
     */
    public static function delTemplate($template_id)
    {
        try {
            $res = self::open3rd()->delTemplate($template_id);
            if ($res['errcode'] == 0) {
                return true;
            } else {
                throw new ValidateException($res['errmsg'] ?? '删除代码模版失败');
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 上传代码
     * @param $template_id
     * @param string $ext_json
     * @param string $user_version
     * @param string $user_desc
     * @return bool
     */
    public static function commit($template_id, string $ext_json, string $user_version, string $user_desc = '')
    {
        try {
            $res = self::open3rd()->commit($template_id, $ext_json, $user_version, $user_desc);
            if ($res['errcode'] == 0) {
                return true;
            } else {
                throw new ValidateException($res['errmsg'] ?? '上传代码失败');
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 获取已经上传的代码列表
     * @return array
     */
    public static function getPage()
    {
        try {
            $res = self::open3rd()->getPage();
            if ($res['errcode'] == 0 && isset($res['page_list'])) {
                return $res['page_list'];
            } else {
                return [];
            }
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    /**
     * 获取体验二维码
     * @param string $path
     * @return mixed
     */
    public static function getQrcode(string $path = '')
    {
        try {
            return self::open3rd()->getQrcode($path);
        } catch (\Throwable $e) {
            throw new ValidateException(self::getValidMessgae($e));
        }
    }

    public static function getValidMessgae(\Throwable $e)
    {
        return self::MSG_CODE[$e->getCode()] ?? $e->getMessage();
    }
}
