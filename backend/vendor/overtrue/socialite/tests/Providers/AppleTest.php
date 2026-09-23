<?php

namespace Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\Exceptions\InvalidArgumentException;
use Overtrue\Socialite\Exceptions\InvalidTokenException;
use Overtrue\Socialite\Providers\Apple;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionObject;

class AppleTest extends TestCase
{
    private function makeProvider(array $extra = []): Apple
    {
        return new Apple(\array_merge([
            'client_id' => 'com.example.app',
            'client_secret' => 'test-client-secret',
            'redirect_url' => 'https://example.com/callback',
        ], $extra));
    }

    /**
     * Create a real RS256-signed id_token JWT for testing.
     */
    private function makeSignedIdToken(array $claimsOverride = [], ?\OpenSSLAsymmetricKey $privateKey = null): array
    {
        $keyPair = $privateKey ? null : \openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $key = $privateKey ?? $keyPair;

        $kid = 'test-kid-123';
        $header = $this->base64UrlEncode(\json_encode(['kid' => $kid, 'alg' => 'RS256']));
        $claims = \array_merge([
            'sub' => 'apple-user-id',
            'email' => 'user@privaterelay.appleid.com',
            'iss' => 'https://appleid.apple.com',
            'aud' => 'com.example.app',
            'exp' => \time() + 3600,
            'iat' => \time(),
        ], $claimsOverride);
        $payload = $this->base64UrlEncode(\json_encode($claims));

        $data = $header.'.'.$payload;
        \openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256);
        $token = $data.'.'.$this->base64UrlEncode($signature);

        // Get public key details to build JWKS
        $details = \openssl_pkey_get_details($key);

        $jwks = [
            'keys' => [[
                'kty' => 'RSA',
                'kid' => $kid,
                'use' => 'sig',
                'alg' => 'RS256',
                'n' => $this->base64UrlEncode($details['rsa']['n']),
                'e' => $this->base64UrlEncode($details['rsa']['e']),
            ]],
        ];

        return ['token' => $token, 'jwks' => $jwks, 'key' => $key, 'kid' => $kid];
    }

    private function base64UrlEncode(string $data): string
    {
        return \rtrim(\strtr(\base64_encode($data), '+/', '-_'), '=');
    }

    private function setHttpClient(Apple $provider, Client $client): void
    {
        $reflection = new ReflectionObject($provider);
        $httpClientProp = $reflection->getProperty('httpClient');
        $httpClientProp->setAccessible(true);
        $httpClientProp->setValue($provider, $client);
    }

    public function test_apple_provider_redirect_url()
    {
        $provider = $this->makeProvider();
        $url = $provider->redirect();

        $this->assertStringStartsWith('https://appleid.apple.com/auth/authorize', $url);
        $this->assertStringContainsString('client_id=com.example.app', $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('response_mode=form_post', $url);
        $this->assertStringContainsString('scope=name+email', $url);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $url);
    }

    public function test_apple_provider_token_url()
    {
        $provider = $this->makeProvider();
        $getTokenUrl = new ReflectionMethod(Apple::class, 'getTokenUrl');
        $getTokenUrl->setAccessible(true);

        $this->assertSame('https://appleid.apple.com/auth/token', $getTokenUrl->invoke($provider));
    }

    public function test_apple_provider_token_fields()
    {
        $provider = $this->makeProvider();
        $getTokenFields = new ReflectionMethod(Apple::class, 'getTokenFields');
        $getTokenFields->setAccessible(true);

        $fields = $getTokenFields->invoke($provider, 'test-code');

        $this->assertSame('com.example.app', $fields['client_id']);
        $this->assertSame('test-client-secret', $fields['client_secret']);
        $this->assertSame('test-code', $fields['code']);
        $this->assertSame('https://example.com/callback', $fields['redirect_uri']);
        $this->assertSame('authorization_code', $fields['grant_type']);
    }

    public function test_apple_provider_uses_provided_client_secret()
    {
        $provider = $this->makeProvider(['client_secret' => 'my-custom-secret']);
        $this->assertSame('my-custom-secret', $provider->getClientSecret());
    }

    public function test_apple_provider_map_user_to_object()
    {
        $provider = $this->makeProvider();
        $mapUserToObject = new ReflectionMethod(Apple::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $result = $mapUserToObject->invoke($provider, [
            'sub' => 'apple-user-id',
            'email' => 'user@example.com',
            'name' => 'Jane Doe',
        ]);

        $this->assertSame('apple-user-id', $result->getId());
        $this->assertSame('user@example.com', $result->getEmail());
        $this->assertSame('Jane Doe', $result->getName());
        $this->assertSame('Jane Doe', $result->getNickname()); // falls back to getName()
        $this->assertNull($result->getAvatar());
    }

    public function test_apple_provider_user_from_code_with_verified_token()
    {
        $provider = $this->makeProvider();
        $signed = $this->makeSignedIdToken();

        $tokenResponseBody = \json_encode([
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'id_token' => $signed['token'],
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]);

        $mock = new MockHandler([
            new Response(200, [], $tokenResponseBody),
            new Response(200, [], \json_encode($signed['jwks'])),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->setHttpClient($provider, $client);

        $user = $provider->userFromCode('test-code');

        $this->assertSame('apple-user-id', $user->getId());
        $this->assertSame('user@privaterelay.appleid.com', $user->getEmail());
        $this->assertSame('test-access-token', $user->getAccessToken());
        $this->assertSame('test-refresh-token', $user->getRefreshToken());
        $this->assertSame(3600, $user->getExpiresIn());
    }

    public function test_apple_provider_user_from_code_throws_when_id_token_missing()
    {
        $provider = $this->makeProvider();

        $tokenResponseBody = \json_encode([
            'access_token' => 'test-access-token',
            'expires_in' => 3600,
        ]);

        $mock = new MockHandler([
            new Response(200, [], $tokenResponseBody),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->setHttpClient($provider, $client);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Missing id_token in token response');

        $provider->userFromCode('test-code');
    }

    public function test_apple_provider_user_from_token_verifies_signature()
    {
        $provider = $this->makeProvider();
        $signed = $this->makeSignedIdToken(['name' => 'Alice']);

        $mock = new MockHandler([
            new Response(200, [], \json_encode($signed['jwks'])),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->setHttpClient($provider, $client);

        $user = $provider->userFromToken($signed['token']);

        $this->assertSame('apple-user-id', $user->getId());
        $this->assertSame('user@privaterelay.appleid.com', $user->getEmail());
        $this->assertSame('Alice', $user->getName());
    }

    public function test_apple_provider_rejects_invalid_signature()
    {
        $provider = $this->makeProvider();

        // Create a signed token but use a different key's JWKS
        $signed1 = $this->makeSignedIdToken();
        $signed2 = $this->makeSignedIdToken();

        // Use signed1's token but signed2's JWKS (wrong key)
        $parts = \explode('.', $signed1['token']);
        $wrongJwks = $signed2['jwks'];
        $wrongJwks['keys'][0]['kid'] = $signed1['kid'];

        $mock = new MockHandler([
            new Response(200, [], \json_encode($wrongJwks)),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->setHttpClient($provider, $client);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid id_token signature.');

        $provider->userFromToken($signed1['token']);
    }

    public function test_apple_provider_rejects_expired_token()
    {
        $provider = $this->makeProvider();
        $signed = $this->makeSignedIdToken(['exp' => \time() - 100]);

        $mock = new MockHandler([
            new Response(200, [], \json_encode($signed['jwks'])),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->setHttpClient($provider, $client);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('The id_token has expired.');

        $provider->userFromToken($signed['token']);
    }

    public function test_apple_provider_rejects_wrong_issuer()
    {
        $provider = $this->makeProvider();
        $signed = $this->makeSignedIdToken(['iss' => 'https://evil.com']);

        $mock = new MockHandler([
            new Response(200, [], \json_encode($signed['jwks'])),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->setHttpClient($provider, $client);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid id_token issuer.');

        $provider->userFromToken($signed['token']);
    }

    public function test_apple_provider_rejects_wrong_audience()
    {
        $provider = $this->makeProvider();
        $signed = $this->makeSignedIdToken(['aud' => 'com.wrong.app']);

        $mock = new MockHandler([
            new Response(200, [], \json_encode($signed['jwks'])),
        ]);
        $client = new Client(['handler' => HandlerStack::create($mock)]);
        $this->setHttpClient($provider, $client);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid id_token audience.');

        $provider->userFromToken($signed['token']);
    }

    public function test_apple_provider_rejects_malformed_jwt()
    {
        $provider = $this->makeProvider();

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid id_token format.');

        $provider->userFromToken('not-a-valid-jwt');
    }

    public function test_generate_client_secret_throws_when_config_missing()
    {
        $provider = new Apple([
            'client_id' => 'com.example.app',
            'redirect_url' => 'https://example.com/callback',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required Apple config');

        $provider->getClientSecret();
    }

    public function test_generate_client_secret_with_valid_key()
    {
        // Generate an EC P-256 key pair for testing
        $ecKey = \openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        \openssl_pkey_export($ecKey, $pemKey);

        $provider = new Apple([
            'client_id' => 'com.example.app',
            'redirect_url' => 'https://example.com/callback',
            'team_id' => 'TEAMID1234',
            'key_id' => 'KEYID12345',
            'private_key' => $pemKey,
        ]);

        $secret = $provider->getClientSecret();

        // Should be a valid JWT with 3 parts
        $parts = \explode('.', $secret);
        $this->assertCount(3, $parts);

        // Verify header
        $header = \json_decode(\base64_decode(\strtr($parts[0], '-_', '+/')), true);
        $this->assertSame('ES256', $header['alg']);
        $this->assertSame('KEYID12345', $header['kid']);

        // Verify payload
        $payload = \json_decode(\base64_decode(\strtr($parts[1], '-_', '+/')), true);
        $this->assertSame('TEAMID1234', $payload['iss']);
        $this->assertSame('https://appleid.apple.com', $payload['aud']);
        $this->assertSame('com.example.app', $payload['sub']);
    }
}
