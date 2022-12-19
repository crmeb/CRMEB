<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\oauth\storage;


use crmeb\basic\BaseStorage;
use crmeb\services\app\WechatOpenService;
use crmeb\services\app\WechatService;
use crmeb\services\oauth\OAuthException;
use crmeb\services\oauth\OAuthInterface;

/**
 * 微信公众号登录
 * Class Wechat
 * @package crmeb\services\oauth\storage
 */
class Wechat extends BaseStorage implements OAuthInterface
{

    protected function initialize(array $config)
    {
        // TODO: Implement initialize() method.
    }


    /**
     * 获取用户信息
     * @param string $openid
     * @return mixed
     */
    public function getUserInfo(string $openid)
    {
        return WechatService::oauth2Service()->getUserInfo($openid)->toArray();
    }

    /**
     * 授权
     * @param string|null $code
     * @param array $options
     * @return \EasyWeChat\Support\Collection|mixed
     */
    public function oauth(string $code = null, array $options = [])
    {
        $open = false;
        if (!empty($options['open'])) {
            $open = true;
        }

        if (!$open) {
            try {
                $wechatInfo = WechatService::oauth2Service()->oauth();
            } catch (\Throwable $e) {
                throw new OAuthException($e->getMessage());
            }
        } else {
            /** @var WechatOpenService $service */
            $service = app()->make(WechatOpenService::class);
            $wechatInfo = $service->getAuthorizationInfo();
            if (!$wechatInfo) {
                throw new OAuthException(410131);
            }
        }

        return $wechatInfo;
    }
}
