<?php

use GuzzleHttp\Client;
use Mockery as m;
use Overtrue\Socialite\Providers\Base;
use Overtrue\Socialite\User;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class OAuthTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_it_can_get_auth_url_without_redirect()
    {
        $config = [
            'client_id' => 'fake_client_id',
            'client_secret' => 'fake_client_secret',
        ];
        $provider = new OAuthTestProviderStub($config);

        $this->assertSame('http://auth.url?client_id=fake_client_id&scope=info&response_type=code', $provider->redirect());
    }

    public function test_it_can_get_auth_url_with_redirect()
    {
        // 手动配置
        $config = [
            'client_id' => 'fake_client_id',
            'client_secret' => 'fake_client_secret',
        ];
        $provider = new OAuthTestProviderStub($config);

        $this->assertSame('http://auth.url?client_id=fake_client_id&redirect_uri=fake_redirect&scope=info&response_type=code', $provider->redirect('fake_redirect'));

        // 用配置属性配置
        $config += ['redirect_url' => 'fake_redirect'];
        $provider = new OAuthTestProviderStub($config);

        $this->assertSame('http://auth.url?client_id=fake_client_id&redirect_uri=fake_redirect&scope=info&response_type=code', $provider->redirect());
    }

    public function test_it_can_get_auth_url_with_redirect_uri_config_alias()
    {
        $config = [
            'client_id' => 'fake_client_id',
            'client_secret' => 'fake_client_secret',
            'redirect_uri' => 'fake_redirect',
        ];
        $provider = new OAuthTestProviderStub($config);

        $this->assertSame('http://auth.url?client_id=fake_client_id&redirect_uri=fake_redirect&scope=info&response_type=code', $provider->redirect());
    }

    public function test_it_can_get_auth_url_with_redirect_config_alias()
    {
        $config = [
            'client_id' => 'fake_client_id',
            'client_secret' => 'fake_client_secret',
            'redirect' => 'fake_redirect',
        ];
        $provider = new OAuthTestProviderStub($config);

        $this->assertSame('http://auth.url?client_id=fake_client_id&redirect_uri=fake_redirect&scope=info&response_type=code', $provider->redirect());
    }

    public function test_it_can_get_auth_url_with_scopes()
    {
        $config = [
            'client_id' => 'fake_client_id',
            'client_secret' => 'fake_client_secret',
        ];
        $provider = new OAuthTestProviderStub($config);
        $url = $provider->scopes(['test_info', 'test_email'])->redirect();

        $this->assertSame('http://auth.url?client_id=fake_client_id&scope=test_info%2Ctest_email&response_type=code', $url);

        // 切换scope分割符
        $url = $provider->scopes(['test_info', 'test_email'])->withScopeSeparator(' ')->redirect();
        $this->assertSame('http://auth.url?client_id=fake_client_id&scope=test_info%20test_email&response_type=code', $url);
    }

    public function test_it_can_get_auth_url_with_state()
    {
        $config = [
            'client_id' => 'fake_client_id',
            'client_secret' => 'fake_client_secret',
        ];
        $provider = new OAuthTestProviderStub($config);
        $url = $provider->withState(123456)->redirect();

        $this->assertSame('http://auth.url?client_id=fake_client_id&scope=info&response_type=code&state=123456', $url);
    }

    public function test_it_can_get_token()
    {
        $config = [
            'client_id' => 'fake_client_id',
            'client_secret' => 'fake_client_secret',
        ];
        $provider = new OAuthTestProviderStub($config);
        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $stream->shouldReceive('__toString')->andReturn(\json_encode([
            'access_token' => 'fake_access_token',
            'refresh_token' => 'fake_refresh_token',
            'expires_in' => 123456,
        ]));
        $response->shouldReceive('getBody')->andReturn($stream);

        $provider->getHttpClient()->shouldReceive('post')->with('http://token.url', [
            'form_params' => [
                'client_id' => 'fake_client_id',
                'client_secret' => 'fake_client_secret',
                'code' => 'fake_code',
                'redirect_uri' => null,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])->andReturn($response);

        $this->assertSame([
            'access_token' => 'fake_access_token',
            'refresh_token' => 'fake_refresh_token',
            'expires_in' => 123456,
        ], $provider->tokenFromCode('fake_code'));
    }

    public function test_it_can_get_user_by_token()
    {
        $config = [
            'client_id' => 'fake_client_id',
            'client_secret' => 'fake_client_secret',
        ];
        $provider = new OAuthTestProviderStub($config);

        $user = $provider->userFromToken('fake_access_token');

        $this->assertSame('foo', $user->getId());
        $this->assertSame(['id' => 'foo'], $user->getRaw());
        $this->assertSame('fake_access_token', $user->getAccessToken());
    }

    public function test_it_can_get_user_by_code()
    {
        $config = [
            'client_id' => 'fake_client_id',
            'client_secret' => 'fake_client_secret',
        ];
        $provider = new OAuthTestProviderStub($config);

        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $stream->shouldReceive('__toString')->andReturn(\json_encode([
            'access_token' => 'fake_access_token',
            'refresh_token' => 'fake_refresh_token',
            'expires_in' => 123456,
        ]));
        $response->shouldReceive('getBody')->andReturn($stream);

        $provider->getHttpClient()->shouldReceive('post')->with('http://token.url', [
            'form_params' => [
                'client_id' => 'fake_client_id',
                'client_secret' => 'fake_client_secret',
                'code' => 'fake_code',
                'redirect_uri' => null,
            ],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ])->andReturn($response);

        $this->assertSame([
            'access_token' => 'fake_access_token',
            'refresh_token' => 'fake_refresh_token',
            'expires_in' => 123456,
        ], $provider->tokenFromCode('fake_code'));

        $user = $provider->userFromCode('fake_code');
        $tokenResponse = [
            'access_token' => 'fake_access_token',
            'refresh_token' => 'fake_refresh_token',
            'expires_in' => 123456,
        ];

        $this->assertSame('foo', $user->getId());
        $this->assertSame($tokenResponse, $user->getTokenResponse());
        $this->assertSame('fake_access_token', $user->getAccessToken());
        $this->assertSame('fake_refresh_token', $user->getRefreshToken());
    }
}

class OAuthTestProviderStub extends Base
{
    public $http;

    protected array $scopes = ['info'];

    protected int $encodingType = PHP_QUERY_RFC3986;

    protected function getAuthUrl(): string
    {
        $url = 'http://auth.url';

        return $this->buildAuthUrlFromBase($url);
    }

    protected function getTokenUrl(): string
    {
        return 'http://token.url';
    }

    protected function getUserByToken(string $token): array
    {
        return ['id' => 'foo'];
    }

    protected function mapUserToObject(array $user): User
    {
        return new User(['id' => $user['id']]);
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     */
    public function getHttpClient(): Client
    {
        if ($this->http) {
            return $this->http;
        }

        return $this->http = m::mock(Client::class);
    }
}
