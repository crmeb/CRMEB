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
namespace crmeb\services\easywechat\Open3rd;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * 注册第三方平台
 * Class ProgramProvider
 * @package crmeb\utils
 */
class ProgramProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['mini_program.component_access_token'] = function ($pimple) {
            return new AccessToken(
                $pimple['config']['open3rd']['component_appid'],
                $pimple['config']['open3rd']['component_appsecret'],
                $pimple['config']['open3rd']['component_verify_ticket'],
                $pimple['config']['open3rd']['authorizer_appid']
            );
        };

        $pimple['mini_program.open3rd'] = function ($pimple) {
            return new ProgramOpen3rd($pimple['mini_program.component_access_token']);
        };
    }
}
