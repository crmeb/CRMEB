<?php

namespace Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\Exceptions\BadRequestException;
use Overtrue\Socialite\Exceptions\InvalidArgumentException;
use Overtrue\Socialite\Providers\Alipay;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AlipayTest extends TestCase
{
    public function test_alipay_provider_has_correct_redirect_response()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $response = $provider->redirect();

        $this->assertStringStartsWith('https://openauth.alipay.com/oauth2/publicAppAuthorize.htm', $response);
        $this->assertStringContainsString('redirect_uri=http%3A%2F%2Flocalhost%2Fcallback', $response);
        $this->assertStringContainsString('app_id=client_id', $response);
        $this->assertStringContainsString('scope=auth_user', $response);
    }

    public function test_alipay_provider_sandbox_mode()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
            'sandbox' => true,
        ]);

        $response = $provider->redirect();

        $this->assertStringStartsWith('https://openauth.alipaydev.com/oauth2/publicAppAuthorize.htm', $response);
    }

    public function test_alipay_provider_urls_and_fields()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $getTokenUrl = new ReflectionMethod(Alipay::class, 'getTokenUrl');
        $getTokenUrl->setAccessible(true);

        $this->assertSame('https://openapi.alipay.com/gateway.do', $getTokenUrl->invoke($provider));
    }

    public function test_get_public_fields()
    {
        $provider = new Alipay([
            'client_id' => 'test_app_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $fields = $provider->getPublicFields('test.method');

        $this->assertArrayHasKey('app_id', $fields);
        $this->assertSame('test_app_id', $fields['app_id']);
        $this->assertArrayHasKey('method', $fields);
        $this->assertSame('test.method', $fields['method']);
        $this->assertArrayHasKey('format', $fields);
        $this->assertSame('json', $fields['format']);
        $this->assertArrayHasKey('charset', $fields);
        $this->assertSame('UTF-8', $fields['charset']);
        $this->assertArrayHasKey('sign_type', $fields);
        $this->assertSame('RSA2', $fields['sign_type']);
        $this->assertArrayHasKey('timestamp', $fields);
        $this->assertArrayHasKey('version', $fields);
        $this->assertSame('1.0', $fields['version']);
    }

    public function test_get_code_fields_throws_exception_when_no_redirect_url()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please set the correct redirect URL refer which was on the Alipay Official Admin pannel.');

        $getCodeFields = new ReflectionMethod(Alipay::class, 'getCodeFields');
        $getCodeFields->setAccessible(true);
        $getCodeFields->invoke($provider);
    }

    public function test_get_code_fields()
    {
        $provider = new Alipay([
            'app_id' => 'test_app_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $getCodeFields = new ReflectionMethod(Alipay::class, 'getCodeFields');
        $getCodeFields->setAccessible(true);
        $fields = $getCodeFields->invoke($provider);

        $this->assertArrayHasKey('app_id', $fields);
        $this->assertSame('test_app_id', $fields['app_id']);
        $this->assertArrayHasKey('scope', $fields);
        $this->assertSame('auth_user', $fields['scope']);
        $this->assertArrayHasKey('redirect_uri', $fields);
        $this->assertSame('http://localhost/callback', $fields['redirect_uri']);
    }

    public function test_sign_with_sh_a256_rsa_throws_exception_when_no_private_key()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no RSA private key set.');

        $signWithSHA256RSA = new ReflectionMethod(Alipay::class, 'signWithSHA256RSA');
        $signWithSHA256RSA->setAccessible(true);
        $signWithSHA256RSA->invoke($provider, 'test_content', '');
    }

    public function test_build_params()
    {
        $params = [
            'app_id' => 'test_app_id',
            'method' => 'test.method',
            'sign' => 'signature',
            'timestamp' => '2024-01-01 12:00:00',
        ];

        $result = Alipay::buildParams($params);
        $this->assertSame('app_id=test_app_id&method=test.method&timestamp=2024-01-01 12:00:00', $result);

        $resultWithUrlencode = Alipay::buildParams($params, true);
        $this->assertStringContainsString('timestamp=2024-01-01%2012%3A00%3A00', $resultWithUrlencode);
    }

    public function test_token_from_code_success()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        // Mock HTTP response
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'alipay_system_oauth_token_response' => [
                    'access_token' => 'token123',
                    'user_id' => 'user123',
                ],
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        // Use reflection to set the HTTP client
        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $token = $provider->tokenFromCode('test_code');

        $this->assertArrayHasKey('access_token', $token);
        $this->assertSame('token123', $token['access_token']);
    }

    public function test_throws_exception_when_token_response_missing()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        // Mock HTTP response without alipay_system_oauth_token_response
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'some_other_field' => 'value',
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        // Use reflection to set the HTTP client
        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Authorization failed: missing alipay_system_oauth_token_response in response');

        $provider->tokenFromCode('test_code');
    }

    public function test_throws_exception_when_error_response()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        // Mock HTTP response with error
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'error_response' => [
                    'code' => '20001',
                    'msg' => 'Invalid parameters',
                ],
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        // Use reflection to set the HTTP client
        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(BadRequestException::class);

        $provider->tokenFromCode('test_code');
    }

    public function test_get_user_by_token_success()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        // Mock HTTP response with user data
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'alipay_user_info_share_response' => [
                    'user_id' => 'user123',
                    'nick_name' => 'Test User',
                ],
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        // Use reflection to set the HTTP client
        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $getUserByToken = new ReflectionMethod(Alipay::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $result = $getUserByToken->invoke($provider, 'test_token');

        $this->assertArrayHasKey('user_id', $result);
        $this->assertSame('user123', $result['user_id']);
        $this->assertArrayHasKey('nick_name', $result);
        $this->assertSame('Test User', $result['nick_name']);
    }

    public function test_get_user_by_token_throws_exception_when_error_response()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        // Mock HTTP response with error
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'error_response' => [
                    'code' => '20001',
                    'msg' => 'Invalid token',
                ],
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        // Use reflection to set the HTTP client
        $reflection = new \ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $this->expectException(BadRequestException::class);

        $getUserByToken = new ReflectionMethod(Alipay::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $getUserByToken->invoke($provider, 'test_token');
    }

    public function test_map_user_to_object()
    {
        $provider = new Alipay([
            'client_id' => 'client_id',
            'rsa_private_key' => 'private_key',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mapUserToObject = new ReflectionMethod(Alipay::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $user = [
            'user_id' => 'user123',
            'nick_name' => 'Test User',
            'avatar' => 'http://avatar.url',
            'email' => 'test@example.com',
        ];

        $result = $mapUserToObject->invoke($provider, $user);

        $this->assertSame('user123', $result->getId());
        $this->assertSame('Test User', $result->getName());
        $this->assertSame('http://avatar.url', $result->getAvatar());
        $this->assertSame('test@example.com', $result->getEmail());
    }
}
