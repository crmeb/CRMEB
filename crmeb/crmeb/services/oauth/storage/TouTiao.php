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
use crmeb\services\oauth\OAuthInterface;

/**
 * 头条小程序登录
 * Class TouTiao
 * @package crmeb\services\oauth\storage
 */
class TouTiao extends BaseStorage implements OAuthInterface
{

    protected function initialize(array $config)
    {
        // TODO: Implement initialize() method.
    }

    public function getUserInfo(string $openid)
    {
        // TODO: Implement getUserInfo() method.
    }

    public function oauth(string $code = null, array $options = [])
    {
        // TODO: Implement oauth() method.
    }
}
