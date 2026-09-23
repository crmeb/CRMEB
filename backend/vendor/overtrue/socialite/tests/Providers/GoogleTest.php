<?php

namespace Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Overtrue\Socialite\Providers\Google;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class GoogleTest extends TestCase
{
    public function test_google_user_from_code_uses_redirect_uri_config_key()
    {
        $provider = new Google([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_uri' => 'https://example.com/callback',
        ]);

        $history = [];
        $mock = new MockHandler([
            new Response(200, [], \json_encode([
                'access_token' => 'test-access-token',
                'refresh_token' => 'test-refresh-token',
                'expires_in' => 3600,
            ])),
            new Response(200, [], \json_encode([
                'id' => 'google-user-id',
                'name' => 'Google User',
                'email' => 'google@example.com',
                'picture' => 'https://example.com/avatar.png',
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $handler->push(Middleware::history($history));
        $client = new Client(['handler' => $handler]);

        $reflection = new ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        $user = $provider->userFromCode('test-code');

        $this->assertSame('google-user-id', $user->getId());
        $this->assertCount(2, $history);

        \parse_str((string) $history[0]['request']->getBody(), $formParams);
        $this->assertSame('test-code', $formParams['code'] ?? null);
        $this->assertSame('https://example.com/callback', $formParams['redirect_uri'] ?? null);
    }
}
