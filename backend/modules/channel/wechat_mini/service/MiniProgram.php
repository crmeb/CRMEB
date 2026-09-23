<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

declare(strict_types=1);

namespace modules\channel\wechat_mini\service;

use modules\channel\wechat_mini\sdk\MiniProgramService;

/**
 * 小程序授权登录
 * 负责小程序 code 换取 session_key/openid，并解密用户信息
 */
class MiniProgram
{
    /**
     * 授权登录
     * @param string|null $code 小程序登录 code
     * @param array $options 可选参数：silence 静默授权；iv/encryptedData 解密用户信息
     * @return array|mixed
     * @throws OAuthException
     */
    public function oauth(?string $code = null, array $options = [])
    {
        if (!$code) {
            throw new OAuthException('缺少code');
        }

        try {
            $userInfoCong = MiniProgramService::getUserInfo($code);
            $session_key = $userInfoCong['session_key'];
        } catch (\Exception $e) {
            throw new OAuthException($e->getMessage());
        }

        if (!isset($userInfoCong['openid'])) {
            throw new OAuthException('openid获取失败');
        }

        // 是否静默授权
        if (isset($options['silence']) && $options['silence'] === true) {
            return $userInfoCong;
        }

        if (empty($options['iv']) || empty($options['encryptedData'])) {
            throw new OAuthException('参数错误');
        }

        try {
            // 解密获取用户信息
            $userInfo = MiniProgramService::encryptor($session_key, $options['iv'], $options['encryptedData']);
        } catch (\Exception $e) {
            if ($e->getCode() == '-41003') {
                throw new OAuthException('获取会话密匙失败');
            }
        }

        return [$userInfoCong, $userInfo];
    }
}
