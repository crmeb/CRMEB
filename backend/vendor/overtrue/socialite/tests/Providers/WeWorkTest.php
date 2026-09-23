<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\Exceptions\InvalidArgumentException;
use Overtrue\Socialite\Exceptions\MethodDoesNotSupportException;
use Overtrue\Socialite\Providers\WeWork;
use PHPUnit\Framework\TestCase;

class WeWorkTest extends TestCase
{
    public function test_we_work_provider_o_auth_url()
    {
        $response = (new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]))
            ->scopes(['snsapi_base'])
            ->redirect();

        $this->assertSame('https://open.weixin.qq.com/connect/oauth2/authorize?appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=snsapi_base#wechat_redirect', $response);
    }

    public function test_we_work_provider_qrcode_url()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
            'agent_id' => 1000,
        ]);

        $response = $provider->withAgentId(1000)->asQrcode()->redirect();

        $this->assertStringStartsWith('https://login.work.weixin.qq.com/wwlogin/sso/login', $response);
        $this->assertStringContainsString('appid=CORPID', $response);
        $this->assertStringContainsString('agentid=1000', $response);
    }

    public function test_we_work_provider_configuration()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
            'base_url' => 'https://custom.base.url',
            'agent_id' => 1000,
        ]);

        $this->assertSame('https://custom.base.url', $provider->getBaseUrl());
    }

    public function test_with_agent_id_method()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]);

        $result = $provider->withAgentId(1000);
        $this->assertInstanceOf(WeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_detailed_method()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]);

        $result = $provider->detailed();
        $this->assertInstanceOf(WeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_as_qrcode_method()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]);

        $result = $provider->asQrcode();
        $this->assertInstanceOf(WeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_with_api_access_token_method()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]);

        $result = $provider->withApiAccessToken('test_token');
        $this->assertInstanceOf(WeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_throws_exception_when_agent_id_required_for_qrcode()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("agent_id is require when qrcode mode or scopes is 'snsapi_privateinfo'");

        $provider->asQrcode()->getAuthUrl();
    }

    public function test_throws_exception_when_agent_id_required_for_private_info()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("agent_id is require when qrcode mode or scopes is 'snsapi_privateinfo'");

        $provider->scopes(['snsapi_privateinfo'])->getAuthUrl();
    }

    public function test_user_from_code_success()
    {
        $mockProvider = $this->getMockBuilder(WeWork::class)
            ->setConstructorArgs([[
                'client_id' => 'CORPID',
                'client_secret' => 'client_secret',
                'redirect' => 'REDIRECT_URI',
            ]])
            ->onlyMethods(['getApiAccessToken', 'getUser'])
            ->getMock();

        $mockProvider->method('getApiAccessToken')->willReturn('api_token');
        $mockProvider->method('getUser')->willReturn([
            'UserId' => 'user123',
            'OpenId' => 'openid123',
        ]);

        $user = $mockProvider->userFromCode('test_code');

        $this->assertSame('user123', $user->getId());
    }

    public function test_user_from_code_with_detailed_success()
    {
        $mockProvider = $this->getMockBuilder(WeWork::class)
            ->setConstructorArgs([[
                'client_id' => 'CORPID',
                'client_secret' => 'client_secret',
                'redirect' => 'REDIRECT_URI',
            ]])
            ->onlyMethods(['getApiAccessToken', 'getUser', 'getUserById'])
            ->getMock();

        $mockProvider->method('getApiAccessToken')->willReturn('api_token');
        $mockProvider->method('getUser')->willReturn([
            'UserId' => 'user123',
        ]);
        $mockProvider->method('getUserById')->willReturn([
            'userid' => 'user123',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = $mockProvider->detailed()->userFromCode('test_code');

        $this->assertSame('user123', $user->getId());
    }

    public function test_user_from_code_with_detailed_and_user_ticket()
    {
        $mockProvider = $this->getMockBuilder(WeWork::class)
            ->setConstructorArgs([[
                'client_id' => 'CORPID',
                'client_secret' => 'client_secret',
                'redirect' => 'REDIRECT_URI',
            ]])
            ->onlyMethods(['getApiAccessToken', 'getUser', 'getUserById', 'getUserDetail'])
            ->getMock();

        $mockProvider->method('getApiAccessToken')->willReturn('api_token');
        $mockProvider->method('getUser')->willReturn([
            'UserId' => 'user123',
            'user_ticket' => 'ticket_abc',
        ]);
        $mockProvider->method('getUserById')->willReturn([
            'userid' => 'user123',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $mockProvider->method('getUserDetail')->with('ticket_abc')->willReturn([
            'gender' => '1',
            'mobile' => '13800138000',
        ]);

        $user = $mockProvider->detailed()->userFromCode('test_code');

        $this->assertSame('user123', $user->getId());
        $this->assertSame('13800138000', $user->getRaw()['mobile']);
    }

    public function test_throws_exception_when_user_id_missing()
    {
        // Mock the methods
        $mockProvider = $this->getMockBuilder(WeWork::class)
            ->setConstructorArgs([[
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url' => 'http://localhost/callback',
                'corp_id' => 'corp_id',
                'corp_secret' => 'corp_secret',
            ]])
            ->onlyMethods(['getApiAccessToken', 'getUser'])
            ->getMock();

        // Set detailed to true to trigger the UserId validation
        $detailedProperty = new ReflectionProperty(WeWork::class, 'detailed');
        $detailedProperty->setAccessible(true);
        $detailedProperty->setValue($mockProvider, true);

        $mockProvider->method('getApiAccessToken')->willReturn('api_token');
        $mockProvider->method('getUser')->willReturn([
            'Name' => 'Test User',
            // Missing UserId
        ]);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Authorization failed: missing UserId in user response');

        $mockProvider->userFromCode('test_code');
    }

    public function test_throws_exception_when_access_token_missing()
    {
        $provider = new WeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
            'corp_id' => 'corp_id',
            'corp_secret' => 'corp_secret',
        ]);

        // Mock HTTP response without access_token
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'errcode' => 0,  // Success error code
                'some_other_field' => 'value',
                // Missing access_token
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        // Use reflection to set the HTTP client
        $reflection = new ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Authorization failed: missing access_token in response');

        // Use reflection to test protected method
        $requestApiAccessToken = new ReflectionMethod(WeWork::class, 'requestApiAccessToken');
        $requestApiAccessToken->setAccessible(true);
        $requestApiAccessToken->invoke($provider);
    }

    public function test_get_user_by_token_throws_exception()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]);

        $this->expectException(MethodDoesNotSupportException::class);
        $this->expectExceptionMessage('WeWork doesn\'t support access_token mode');

        $getUserByToken = new ReflectionMethod(WeWork::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $getUserByToken->invoke($provider, 'test_token');
    }

    public function test_map_user_to_object_detailed()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]);

        // Set detailed to true
        $detailedProperty = new ReflectionProperty(WeWork::class, 'detailed');
        $detailedProperty->setAccessible(true);
        $detailedProperty->setValue($provider, true);

        $mapUserToObject = new ReflectionMethod(WeWork::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $user = [
            'userid' => 'user123',
            'name' => 'Test User',
            'avatar' => 'http://avatar.url',
            'email' => 'test@example.com',
        ];

        $result = $mapUserToObject->invoke($provider, $user);

        $this->assertSame('user123', $result->getId());
        $this->assertSame('Test User', $result->getName());
        $this->assertSame('http://avatar.url', $result->getAvatar());
        $this->assertSame('test@example.com', $result->getEmail());
    }

    public function test_map_user_to_object_simple()
    {
        $provider = new WeWork([
            'client_id' => 'CORPID',
            'client_secret' => 'client_secret',
            'redirect' => 'REDIRECT_URI',
        ]);

        $mapUserToObject = new ReflectionMethod(WeWork::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $user = [
            'UserId' => 'user123',
            'OpenId' => 'openid123',
        ];

        $result = $mapUserToObject->invoke($provider, $user);

        $this->assertSame('user123', $result->getId());
    }
}
