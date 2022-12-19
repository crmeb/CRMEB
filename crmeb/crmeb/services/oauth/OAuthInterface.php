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

namespace crmeb\services\oauth;

/**
 * 第三方登录
 * Interface OAuthInterface
 * @package crmeb\services\oauth
 */
interface OAuthInterface
{

    /**
     * 获取用户信息
     * @param string $openid
     * @return mixed
     */
    public function getUserInfo(string $openid);

    /**
     * 授权
     * @param string|null $code
     * @param array $options
     * @return mixed
     */
    public function oauth(string $code = null, array $options = []);

}
