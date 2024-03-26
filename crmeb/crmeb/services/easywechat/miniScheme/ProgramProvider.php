<?php

namespace crmeb\services\easywechat\miniScheme;

use EasyWeChat\MiniProgram\AccessToken;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ProgramProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['mini_program.access_token'] = function ($pimple) {
            return new AccessToken(
                $pimple['config']['mini_program']['app_id'],
                $pimple['config']['mini_program']['secret'],
                $pimple['cache']
            );
        };

        $pimple['mini_program.mini_scheme'] = function ($pimple) {
            return new ProgramScheme($pimple['mini_program.access_token']);
        };
    }
}