<?php

namespace Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\Exceptions\InvalidArgumentException;
use Overtrue\Socialite\Providers\DouYin;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DouYinTest extends TestCase
{
    public function test_dou_yin_provider_has_correct_redirect_response()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $response = $provider->redirect();

        $this->assertStringStartsWith('https://open.douyin.com/platform/oauth/connect/', $response);
        $this->assertStringContainsString('redirect_uri=http%3A%2F%2Flocalhost%2Fcallback', $response);
        $this->assertStringContainsString('client_key=client_id', $response);
        $this->assertStringContainsString('response_type=code', $response);
        $this->assertStringContainsString('scope=user_info', $response);
    }

    public function test_dou_yin_provider_urls_and_fields()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $getTokenUrl = new ReflectionMethod(DouYin::class, 'getTokenUrl');
        $getTokenUrl->setAccessible(true);

        $getTokenFields = new ReflectionMethod(DouYin::class, 'getTokenFields');
        $getTokenFields->setAccessible(true);

        $this->assertSame('https://open.douyin.com/oauth/access_token/', $getTokenUrl->invoke($provider));

        $this->assertSame([
            'client_key' => 'client_id',
            'client_secret' => 'client_secret',
            'code' => 'test_code',
            'grant_type' => 'authorization_code',
        ], $getTokenFields->invoke($provider, 'test_code'));

        $this->assertSame([
            'client_key' => 'client_id',
            'redirect_uri' => 'http://localhost/callback',
            'scope' => 'user_info',
            'response_type' => 'code',
        ], $provider->getCodeFields());
    }

    public function test_with_open_id_method()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $result = $provider->withOpenId('test_openid');
        $this->assertInstanceOf(DouYin::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_token_from_code_success()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            new Response(200, [], '{"data": {"error_code": 0, "access_token": "token123", "open_id": "openid123"}}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $token = $provider->tokenFromCode('test_code');

        $this->assertArrayHasKey('access_token', $token);
        $this->assertSame('token123', $token['access_token']);
    }

    public function test_throws_exception_when_open_id_missing()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            new Response(200, [], '{"data": {"error_code": 0, "access_token": "token123"}}'), // Missing open_id
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Authorization failed: missing open_id in token response');

        $provider->tokenFromCode('test_code');
    }

    public function test_throws_exception_when_data_missing()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            new Response(200, [], '{"error": "missing data"}'), // Missing data
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Invalid token response');

        $provider->tokenFromCode('test_code');
    }

    public function test_throws_exception_when_error_code_non_zero()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            new Response(200, [], '{"data": {"error_code": 1, "description": "error occurred"}}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Invalid token response');

        $provider->tokenFromCode('test_code');
    }

    public function test_get_user_by_token_success()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            new Response(200, [], '{"data": {"nickname": "Test User", "avatar": "http://avatar.url", "open_id": "test_openid"}}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        // Set openId first
        $provider->withOpenId('test_openid');

        $getUserByToken = new ReflectionMethod(DouYin::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $result = $getUserByToken->invoke($provider, 'test_token');

        $this->assertArrayHasKey('open_id', $result);
        $this->assertSame('test_openid', $result['open_id']);
        $this->assertArrayHasKey('nickname', $result);
        $this->assertSame('Test User', $result['nickname']);
    }

    public function test_get_user_by_token_throws_exception_when_open_id_empty()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('please set the `open_id` before issue the API request.');

        $getUserByToken = new ReflectionMethod(DouYin::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $getUserByToken->invoke($provider, 'test_token');
    }

    public function test_map_user_to_object()
    {
        $provider = new DouYin([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mapUserToObject = new ReflectionMethod(DouYin::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $user = [
            'open_id' => 'test_openid',
            'nickname' => 'Test User',
            'avatar' => 'http://avatar.url',
            'email' => 'test@example.com',
        ];

        $result = $mapUserToObject->invoke($provider, $user);

        $this->assertSame('test_openid', $result->getId());
        $this->assertSame('Test User', $result->getName());
        $this->assertSame('Test User', $result->getNickname());
        $this->assertSame('http://avatar.url', $result->getAvatar());
        $this->assertSame('test@example.com', $result->getEmail());
    }
}
