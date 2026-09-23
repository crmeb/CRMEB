<?php

use GuzzleHttp\Client;
use Overtrue\Socialite\Contracts\FactoryInterface;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\Providers\WeChat;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

// here we need loaded the symbols first.
\class_exists(FactoryInterface::class);

class WechatTest extends TestCase
{
    public function test_we_chat_provider_has_correctly_redirect_response()
    {
        $response = (new WeChat([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/socialite/callback.php',
        ]))->redirect();

        $this->assertStringStartsWith('https://open.weixin.qq.com/connect/qrconnect', $response);
        $this->assertMatchesRegularExpression('/redirect_uri=http%3A%2F%2Flocalhost%2Fsocialite%2Fcallback.php/', $response);
    }

    public function test_we_chat_provider_token_url_and_request_fields()
    {
        $provider = new WeChat([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/socialite/callback.php',
        ]);

        $getTokenUrl = new ReflectionMethod(WeChat::class, 'getTokenUrl');
        $getTokenUrl->setAccessible(true);

        $getTokenFields = new ReflectionMethod(WeChat::class, 'getTokenFields');
        $getTokenFields->setAccessible(true);

        $getCodeFields = new ReflectionMethod(WeChat::class, 'getCodeFields');
        $getCodeFields->setAccessible(true);

        $this->assertSame('https://api.weixin.qq.com/sns/oauth2/access_token', $getTokenUrl->invoke($provider));
        $this->assertSame([
            'appid' => 'client_id',
            'secret' => 'client_secret',
            'code' => 'iloveyou',
            'grant_type' => 'authorization_code',
        ], $getTokenFields->invoke($provider, 'iloveyou'));

        $this->assertSame([
            'appid' => 'client_id',
            'redirect_uri' => 'http://localhost/socialite/callback.php',
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => 'wechat-state',
            'connect_redirect' => 1,
        ], $getCodeFields->invoke($provider->withState('wechat-state')));
    }

    public function test_open_platform_component()
    {
        $provider = new WeChat([
            'client_id' => 'client_id',
            'client_secret' => null,
            'redirect' => 'redirect-url',
            'component' => [
                'id' => 'component-app-id',
                'token' => 'token',
            ],
        ]);
        $getTokenUrl = new ReflectionMethod(WeChat::class, 'getTokenUrl');
        $getTokenUrl->setAccessible(true);

        $getTokenFields = new ReflectionMethod(WeChat::class, 'getTokenFields');
        $getTokenFields->setAccessible(true);

        $getCodeFields = new ReflectionMethod(WeChat::class, 'getCodeFields');
        $getCodeFields->setAccessible(true);

        $this->assertSame([
            'appid' => 'client_id',
            'redirect_uri' => 'redirect-url',
            'response_type' => 'code',
            'scope' => 'snsapi_base',
            'state' => 'state',
            'connect_redirect' => 1,
            'component_appid' => 'component-app-id',
        ], $getCodeFields->invoke($provider->withState('state')));

        $this->assertSame([
            'appid' => 'client_id',
            'component_appid' => 'component-app-id',
            'component_access_token' => 'token',
            'code' => 'simcode',
            'grant_type' => 'authorization_code',
        ], $getTokenFields->invoke($provider, 'simcode'));

        $this->assertSame('https://api.weixin.qq.com/sns/oauth2/component/access_token', $getTokenUrl->invoke($provider));
    }

    public function test_open_platform_component_with_custom_parameters()
    {
        $provider = new WeChat([
            'client_id' => 'client_id',
            'client_secret' => null,
            'redirect' => 'redirect-url',
            'component' => [
                'id' => 'component-app-id',
                'token' => 'token',
            ],
        ]);

        $getCodeFields = new ReflectionMethod(WeChat::class, 'getCodeFields');
        $getCodeFields->setAccessible(true);

        $provider->with(['foo' => 'bar']);

        $fields = $getCodeFields->invoke($provider->withState('wechat-state'));
        $this->assertArrayHasKey('foo', $fields);
        $this->assertSame('bar', $fields['foo']);
    }

    public function test_throws_exception_when_openid_missing()
    {
        $provider = new WeChat([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/socialite/callback.php',
        ]);

        // Test that getSnsapiBaseUserFromCode throws exception when openid is missing
        $getSnsapiBaseUserFromCode = new ReflectionMethod(WeChat::class, 'getSnsapiBaseUserFromCode');
        $getSnsapiBaseUserFromCode->setAccessible(true);

        // Mock the getTokenFromCode method to return response without openid
        $mockProvider = $this->getMockBuilder(WeChat::class)
            ->setConstructorArgs([[
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url' => 'http://localhost/socialite/callback.php',
            ]])
            ->onlyMethods(['getTokenFromCode', 'fromJsonBody'])
            ->getMock();

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockProvider->method('getTokenFromCode')->willReturn($mockResponse);

        // Test missing openid
        $mockProvider->method('fromJsonBody')->willReturn(['access_token' => 'token123']);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Authorization failed: missing openid in token response');

        $getSnsapiBaseUserFromCode->invoke($mockProvider, 'test_code');
    }

    public function test_throws_exception_when_access_token_missing()
    {
        $provider = new WeChat([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/socialite/callback.php',
        ]);

        // Test that getSnsapiBaseUserFromCode throws exception when access_token is missing
        $getSnsapiBaseUserFromCode = new ReflectionMethod(WeChat::class, 'getSnsapiBaseUserFromCode');
        $getSnsapiBaseUserFromCode->setAccessible(true);

        // Mock the getTokenFromCode method to return response without access_token
        $mockProvider = $this->getMockBuilder(WeChat::class)
            ->setConstructorArgs([[
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url' => 'http://localhost/socialite/callback.php',
            ]])
            ->onlyMethods(['getTokenFromCode', 'fromJsonBody'])
            ->getMock();

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockProvider->method('getTokenFromCode')->willReturn($mockResponse);

        // Test missing access_token
        $mockProvider->method('fromJsonBody')->willReturn(['openid' => 'openid123']);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Authorization failed: missing access_token in token response');

        $getSnsapiBaseUserFromCode->invoke($mockProvider, 'test_code');
    }

    public function test_throws_exception_when_get_user_by_token_fails()
    {
        $mockProvider = $this->getMockBuilder(WeChat::class)
            ->setConstructorArgs([[
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url' => 'http://localhost/socialite/callback.php',
            ]])
            ->onlyMethods(['getHttpClient', 'fromJsonBody'])
            ->getMock();

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockHttpClient = $this->createMock(Client::class);
        $mockHttpClient->method('get')->willReturn($mockResponse);

        $mockProvider->method('getHttpClient')->willReturn($mockHttpClient);
        $mockProvider->method('fromJsonBody')->willReturn([
            'errcode' => 40003,
            'errmsg' => 'invalid openid',
        ]);

        $getUserByToken = new ReflectionMethod(WeChat::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('invalid openid');

        $getUserByToken->invoke($mockProvider, 'test_token');
    }
}
