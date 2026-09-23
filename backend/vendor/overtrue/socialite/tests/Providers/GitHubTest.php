<?php

namespace Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Overtrue\Socialite\Providers\GitHub;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionObject;

class GitHubTest extends TestCase
{
    /**
     * Build a GitHub provider with a mocked Guzzle client and return
     * both the provider instance and the queued mock handler.
     */
    private function makeProvider(array $config, array $queuedResponses): GitHub
    {
        $provider = new GitHub(\array_merge([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'https://example.com/callback',
        ], $config));

        $handler = HandlerStack::create(new MockHandler($queuedResponses));
        $client = new Client(['handler' => $handler]);

        $reflection = new ReflectionObject($provider);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($provider, $client);

        return $provider;
    }

    private function invokeGetUserByToken(GitHub $provider, string $token): array
    {
        $method = new ReflectionMethod(GitHub::class, 'getUserByToken');
        $method->setAccessible(true);

        return $method->invoke($provider, $token);
    }

    public function test_redirect_url_contains_default_user_email_scope()
    {
        $provider = new GitHub([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'https://example.com/callback',
        ]);

        $url = $provider->redirect();

        $this->assertStringStartsWith('https://github.com/login/oauth/authorize', $url);
        $this->assertStringContainsString('client_id=client_id', $url);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $url);
        $this->assertStringContainsString('scope=user%3Aemail', $url);
    }

    public function test_default_scopes_fetch_email_when_not_in_user_response()
    {
        $provider = $this->makeProvider([], [
            // /user response — no email field
            new Response(200, [], \json_encode([
                'id' => 1,
                'login' => 'octocat',
                'name' => 'The Octocat',
                'avatar_url' => 'https://example.com/octocat.png',
            ])),
            // /user/emails response — primary verified email
            new Response(200, [], \json_encode([
                ['email' => 'secondary@example.com', 'primary' => false, 'verified' => true],
                ['email' => 'octocat@example.com', 'primary' => true, 'verified' => true],
            ])),
        ]);

        $user = $this->invokeGetUserByToken($provider, 'test-token');

        $this->assertSame('octocat@example.com', $user['email']);
        $this->assertSame('octocat', $user['login']);
    }

    /**
     * Regression test for the str_contains() fallback: when scopes are
     * supplied as a single space-separated string (e.g. via the `scope`
     * config key), $this->scopes ends up as ['read:user user:email'].
     * The old `in_array('user:email', $this->scopes)` check missed this
     * case. The fallback ensures the email lookup still happens.
     */
    public function test_email_lookup_runs_when_scope_passed_as_space_separated_string()
    {
        $provider = $this->makeProvider([
            'scope' => 'read:user user:email',
        ], [
            new Response(200, [], \json_encode([
                'id' => 2,
                'login' => 'monalisa',
                'name' => 'Mona Lisa',
                'avatar_url' => 'https://example.com/mona.png',
            ])),
            new Response(200, [], \json_encode([
                ['email' => 'mona@example.com', 'primary' => true, 'verified' => true],
            ])),
        ]);

        $user = $this->invokeGetUserByToken($provider, 'test-token');

        $this->assertSame('mona@example.com', $user['email']);
    }

    public function test_email_lookup_runs_when_scopes_array_includes_user_email()
    {
        $provider = $this->makeProvider([
            'scopes' => ['read:user', 'user:email'],
        ], [
            new Response(200, [], \json_encode([
                'id' => 3,
                'login' => 'arrayuser',
            ])),
            new Response(200, [], \json_encode([
                ['email' => 'array@example.com', 'primary' => true, 'verified' => true],
            ])),
        ]);

        $user = $this->invokeGetUserByToken($provider, 'test-token');

        $this->assertSame('array@example.com', $user['email']);
    }

    public function test_email_lookup_skipped_when_scope_does_not_request_email()
    {
        // Only one queued response: the /user call. If the provider tried
        // to also call /user/emails the MockHandler would throw.
        $provider = $this->makeProvider([
            'scope' => 'read:user',
        ], [
            // Include a non-null email value so we can prove the value was
            // NOT overwritten by a /user/emails lookup.
            new Response(200, [], \json_encode([
                'id' => 4,
                'login' => 'noemailuser',
                'email' => 'from-user-endpoint@example.com',
            ])),
        ]);

        $user = $this->invokeGetUserByToken($provider, 'test-token');

        // If the /user/emails call had run, the MockHandler would have thrown
        // (no second response queued). The email value must be the one returned
        // by the /user endpoint, untouched.
        $this->assertSame('from-user-endpoint@example.com', $user['email']);
        $this->assertSame('noemailuser', $user['login']);
    }

    public function test_get_email_by_token_returns_empty_string_when_no_primary_verified_email()
    {
        $provider = $this->makeProvider([], [
            new Response(200, [], \json_encode([
                'id' => 5,
                'login' => 'nouser',
            ])),
            new Response(200, [], \json_encode([
                ['email' => 'unverified@example.com', 'primary' => true, 'verified' => false],
                ['email' => 'secondary@example.com', 'primary' => false, 'verified' => true],
            ])),
        ]);

        $user = $this->invokeGetUserByToken($provider, 'test-token');

        $this->assertSame('', $user['email']);
    }

    public function test_get_email_by_token_returns_empty_string_when_request_fails()
    {
        $provider = $this->makeProvider([], [
            new Response(200, [], \json_encode([
                'id' => 6,
                'login' => 'failuser',
            ])),
            // /user/emails → server error, swallowed by getEmailByToken
            new Response(500, [], 'server error'),
        ]);

        $user = $this->invokeGetUserByToken($provider, 'test-token');

        $this->assertSame('', $user['email']);
    }

    public function test_map_user_to_object_uses_expected_fields()
    {
        $provider = new GitHub([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'https://example.com/callback',
        ]);

        $mapUserToObject = new ReflectionMethod(GitHub::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $user = $mapUserToObject->invoke($provider, [
            'id' => 7,
            'login' => 'octocat',
            'name' => 'The Octocat',
            'email' => 'octocat@example.com',
            'avatar_url' => 'https://example.com/octocat.png',
        ]);

        $this->assertSame(7, $user->getId());
        $this->assertSame('octocat', $user->getNickname());
        $this->assertSame('The Octocat', $user->getName());
        $this->assertSame('octocat@example.com', $user->getEmail());
        $this->assertSame('https://example.com/octocat.png', $user->getAvatar());
    }
}
