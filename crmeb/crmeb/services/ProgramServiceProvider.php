<?php

namespace crmeb\services;

use EasyWeChat\MiniProgram\AccessToken;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ProgramServiceProvider implements ServiceProviderInterface
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

        $pimple['mini_program.now_notice'] = function ($pimple) {
            return new ProgramSubscribeService($pimple['mini_program.access_token']);
        };
    }
}
