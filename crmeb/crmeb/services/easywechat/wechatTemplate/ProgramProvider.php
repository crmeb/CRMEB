<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\services\easywechat\wechatTemplate;

use EasyWeChat\Core\AccessToken;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ProgramProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['wechat.access_token'] = function ($pimple) {
            return new AccessToken(
                $pimple['config']['app_id'],
                $pimple['config']['secret'],
                $pimple['cache']
            );
        };

        $pimple['new_notice'] = function ($pimple) {
            return new ProgramTemplate($pimple['wechat.access_token']);
        };
    }
}