<?php

/*
 * This file is part of the overtrue/socialite.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Overtrue\Socialite\Providers\WeWorkProvider;
use Symfony\Component\HttpFoundation\Request;

class WeWorkProviderTest extends PHPUnit_Framework_TestCase
{
    public function testQrConnect()
    {
        $response = (new WeWorkProvider(Request::create('foo'), 'ww100000a5f2191', 'client_secret', 'http://www.oa.com'))
                    ->setAgentId('1000000')
                    ->stateless()
                    ->redirect();

        $this->assertSame('https://open.work.weixin.qq.com/wwopen/sso/qrConnect?appid=ww100000a5f2191&agentid=1000000&redirect_uri=http%3A%2F%2Fwww.oa.com', $response->getTargetUrl());
    }

    public function testOAuthWithAgentId()
    {
        $response = (new WeWorkProvider(Request::create('foo'), 'CORPID', 'client_secret', 'REDIRECT_URI'))
                    ->scopes(['snsapi_base'])
                    ->setAgentId('1000000')
                    ->stateless()
                    ->redirect();

        $this->assertSame('https://open.weixin.qq.com/connect/oauth2/authorize?appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=snsapi_base&agentid=1000000#wechat_redirect', $response->getTargetUrl());
    }

    public function testOAuthWithoutAgentId()
    {
        $response = (new WeWorkProvider(Request::create('foo'), 'CORPID', 'client_secret', 'REDIRECT_URI'))
                    ->scopes(['snsapi_base'])
                    ->stateless()
                    ->redirect();

        $this->assertSame('https://open.weixin.qq.com/connect/oauth2/authorize?appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=snsapi_base#wechat_redirect', $response->getTargetUrl());
    }
}
