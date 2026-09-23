<?php

namespace Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\Providers\QQ;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class QQTest extends TestCase
{
    public function test_qq_provider_has_correct_redirect_response()
    {
        $provider = new QQ([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $response = $provider->redirect();

        $this->assertStringStartsWith('https://graph.qq.com/oauth2.0/authorize', $response);
        $this->assertStringContainsString('redirect_uri=http%3A%2F%2Flocalhost%2Fcallback', $response);
        $this->assertStringContainsString('client_id=client_id', $response);
        $this->assertStringContainsString('response_type=code', $response);
        $this->assertStringContainsString('scope=get_user_info', $response);
    }

    public function test_qq_provider_token_url_and_request_fields()
    {
        $provider = new QQ([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $getTokenUrl = new ReflectionMethod(QQ::class, 'getTokenUrl');
        $getTokenUrl->setAccessible(true);

        $getTokenFields = new ReflectionMethod(QQ::class, 'getTokenFields');
        $getTokenFields->setAccessible(true);

        $getCodeFields = new ReflectionMethod(QQ::class, 'getCodeFields');
        $getCodeFields->setAccessible(true);

        $this->assertSame('https://graph.qq.com/oauth2.0/token', $getTokenUrl->invoke($provider));

        $this->assertSame([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'code' => 'test_code',
            'redirect_uri' => 'http://localhost/callback',
            'grant_type' => 'authorization_code',
        ], $getTokenFields->invoke($provider, 'test_code'));

        $this->assertSame([
            'client_id' => 'client_id',
            'redirect_uri' => 'http://localhost/callback',
            'scope' => 'get_user_info',
            'response_type' => 'code',
            'state' => 'qq-state',
        ], $getCodeFields->invoke($provider->withState('qq-state')));
    }

    public function test_with_union_id_method()
    {
        $provider = new QQ([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $result = $provider->withUnionId();
        $this->assertInstanceOf(QQ::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_token_from_code_method()
    {
        $provider = new QQ([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            new Response(200, [], 'access_token=test_token&refresh_token=refresh_token&expires_in=7200'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $token = $provider->tokenFromCode('test_code');

        $this->assertArrayHasKey('access_token', $token);
        $this->assertSame('test_token', $token['access_token']);
    }

    public function test_get_user_by_token_success()
    {
        $provider = new QQ([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            // First call to get openid
            new Response(200, [], '{"openid": "test_openid", "unionid": "test_unionid"}'),
            // Second call to get user info
            new Response(200, [], '{"ret": 0, "nickname": "Test User", "figureurl_qq_2": "http://avatar.url"}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        // Use reflection to test protected method
        $getUserByToken = new ReflectionMethod(QQ::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $result = $getUserByToken->invoke($provider, 'test_token');

        $this->assertArrayHasKey('openid', $result);
        $this->assertSame('test_openid', $result['openid']);
        $this->assertArrayHasKey('unionid', $result);
        $this->assertSame('test_unionid', $result['unionid']);
    }

    public function test_throws_exception_when_openid_missing()
    {
        $provider = new QQ([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            // First call to get openid - return response missing openid
            new Response(200, [], '{"error": "invalid_request"}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Authorization failed: missing openid in token response');

        // Use reflection to test protected method
        $getUserByToken = new ReflectionMethod(QQ::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $getUserByToken->invoke($provider, 'test_token');
    }

    public function test_throws_exception_when_user_info_returns_fails()
    {
        $provider = new QQ([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mock = new MockHandler([
            // First call to get openid - success
            new Response(200, [], '{"openid": "test_openid"}'),
            // Second call to get user info - failure
            new Response(200, [], '{"ret": 1, "msg": "parameter error"}'),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(AuthorizeFailedException::class);

        // Use reflection to test protected method
        $getUserByToken = new ReflectionMethod(QQ::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $getUserByToken->invoke($provider, 'test_token');
    }

    public function test_map_user_to_object()
    {
        $provider = new QQ([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mapUserToObject = new ReflectionMethod(QQ::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $user = [
            'openid' => 'test_openid',
            'nickname' => 'Test User',
            'email' => 'test@example.com',
            'figureurl_qq_2' => 'http://avatar.url',
        ];

        $result = $mapUserToObject->invoke($provider, $user);

        $this->assertSame('test_openid', $result->getId());
        $this->assertSame('Test User', $result->getName());
        $this->assertSame('Test User', $result->getNickname());
        $this->assertSame('test@example.com', $result->getEmail());
        $this->assertSame('http://avatar.url', $result->getAvatar());
    }
}
