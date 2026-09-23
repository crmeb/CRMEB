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

namespace crmeb\interfaces;

/**
 * 授权接口
 * 各端授权类实现此接口，保证方法签名一致
 * @package crmeb\interfaces
 */
interface OAuthInterface
{
    /**
     * 授权获取用户信息
     * @param mixed ...$args
     * @return array
     */
    public function oauth(...$args): array;

    /**
     * 获取用户信息
     * @param string $openid
     * @return array
     */
    public function getUserInfo(string $openid): array;
}
