<?php

namespace Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\Exceptions\BadRequestException;
use Overtrue\Socialite\Exceptions\InvalidArgumentException;
use Overtrue\Socialite\Providers\DingTalk;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DingTalkTest extends TestCase
{
    public function test_ding_talk_provider_has_correct_redirect_response()
    {
        $provider = new DingTalk([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $response = $provider->redirect();

        $this->assertStringStartsWith('https://oapi.dingtalk.com/connect/qrconnect', $response);
        $this->assertStringContainsString('redirect_uri=http%3A%2F%2Flocalhost%2Fcallback', $response);
        $this->assertStringContainsString('appid=client_id', $response);
    }

    public function test_ding_talk_provider_configuration()
    {
        // Test with app_id configuration
        $provider = new DingTalk([
            'app_id' => 'test_app_id',
            'app_secret' => 'test_app_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $this->assertSame('test_app_id', $provider->getClientId());
        $this->assertSame('test_app_secret', $provider->getClientSecret());

        // Test with appid configuration
        $provider = new DingTalk([
            'appid' => 'test_appid',
            'appSecret' => 'test_appsecret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $this->assertSame('test_appid', $provider->getClientId());
        $this->assertSame('test_appsecret', $provider->getClientSecret());

        // Test with appId configuration
        $provider = new DingTalk([
            'appId' => 'test_appId',
            'client_secret' => 'test_client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $this->assertSame('test_appId', $provider->getClientId());
        $this->assertSame('test_client_secret', $provider->getClientSecret());
    }

    public function test_get_code_fields()
    {
        $provider = new DingTalk([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $getCodeFields = new ReflectionMethod(DingTalk::class, 'getCodeFields');
        $getCodeFields->setAccessible(true);

        $fields = $getCodeFields->invoke($provider->withState('test-state'));

        $this->assertSame([
            'appid' => 'client_id',
            'grant_type' => 'authorization_code',
            'code' => 'snsapi_login',
            'redirect_uri' => 'http://localhost/callback',
        ], $fields);
    }

    public function test_create_signature()
    {
        $provider = new DingTalk([
            'client_id' => 'client_id',
            'client_secret' => 'test_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $createSignature = new ReflectionMethod(DingTalk::class, 'createSignature');
        $createSignature->setAccessible(true);

        $time = 1234567890000;
        $signature = $createSignature->invoke($provider, $time);

        $this->assertIsString($signature);
        $this->assertNotEmpty($signature);
    }

    public function test_user_from_code_success()
    {
        $provider = new DingTalk([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            new Response(200, [], '{"errcode": 0, "user_info": {"nick": "Test User", "open_id": "test_openid"}}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $user = $provider->userFromCode('test_code');

        $this->assertSame('Test User', $user->getName());
        $this->assertSame('Test User', $user->getNickname());
        $this->assertSame('test_openid', $user->getId());
    }

    public function test_throws_exception_when_user_info_missing()
    {
        $provider = new DingTalk([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            new Response(200, [], '{"errcode": 0}'), // Missing user_info
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Authorization failed: missing user_info in response');

        $provider->userFromCode('test_code');
    }

    public function test_throws_exception_when_error_code_non_zero()
    {
        $provider = new DingTalk([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            new Response(200, [], '{"errcode": 1, "errmsg": "Error message"}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(BadRequestException::class);

        $provider->userFromCode('test_code');
    }

    public function test_get_token_url_throws_exception()
    {
        $provider = new DingTalk([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not supported to get access token.');

        $getTokenUrl = new ReflectionMethod(DingTalk::class, 'getTokenUrl');
        $getTokenUrl->setAccessible(true);
        $getTokenUrl->invoke($provider);
    }

    public function test_get_user_by_token_throws_exception()
    {
        $provider = new DingTalk([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to use token get User.');

        $getUserByToken = new ReflectionMethod(DingTalk::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $getUserByToken->invoke($provider, 'test_token');
    }

    public function test_map_user_to_object()
    {
        $provider = new DingTalk([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mapUserToObject = new ReflectionMethod(DingTalk::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $user = [
            'nick' => 'Test User',
            'open_id' => 'test_openid',
        ];

        $result = $mapUserToObject->invoke($provider, $user);

        $this->assertSame('test_openid', $result->getId());
        $this->assertSame('Test User', $result->getName());
        $this->assertSame('Test User', $result->getNickname());
        $this->assertNull($result->getEmail());
        $this->assertNull($result->getAvatar());
    }
}
