<?php

namespace Providers;

use Overtrue\Socialite\Exceptions\AuthorizeFailedException;
use Overtrue\Socialite\Exceptions\MethodDoesNotSupportException;
use Overtrue\Socialite\Providers\OpenWeWork;
use PHPUnit\Framework\TestCase;

class OpenWeWorkTest extends TestCase
{
    public function test_open_we_work_provider_has_correct_redirect_response()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $response = $provider->redirect();

        $this->assertStringStartsWith('https://open.weixin.qq.com/connect/oauth2/authorize', $response);
        $this->assertStringContainsString('redirect_uri=http%3A%2F%2Flocalhost%2Fcallback', $response);
        $this->assertStringContainsString('appid=client_id', $response);
        $this->assertStringContainsString('response_type=code', $response);
    }

    public function test_open_we_work_provider_qrcode_redirect()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $response = $provider->asQrcode()->withUserType('admin')->withLang('en')->redirect();

        $this->assertStringStartsWith('https://open.work.weixin.qq.com/wwopen/sso/3rd_qrConnect', $response);
        $this->assertStringContainsString('appid=client_id', $response);
        $this->assertStringContainsString('usertype=admin', $response);
        $this->assertStringContainsString('lang=en', $response);
    }

    public function test_open_we_work_provider_configuration()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
            'base_url' => 'https://custom.base.url',
        ]);

        // Check the base URL is set via reflection
        $baseUrlProperty = new \ReflectionProperty(OpenWeWork::class, 'baseUrl');
        $baseUrlProperty->setAccessible(true);
        $this->assertSame('https://custom.base.url', $baseUrlProperty->getValue($provider));
    }

    public function test_with_agent_id_method()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $result = $provider->withAgentId(1000);
        $this->assertInstanceOf(OpenWeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_detailed_method()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $result = $provider->detailed();
        $this->assertInstanceOf(OpenWeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_as_qrcode_method()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $result = $provider->asQrcode();
        $this->assertInstanceOf(OpenWeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_with_user_type_method()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $result = $provider->withUserType('admin');
        $this->assertInstanceOf(OpenWeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_with_lang_method()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $result = $provider->withLang('en');
        $this->assertInstanceOf(OpenWeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_with_suite_ticket_method()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $result = $provider->withSuiteTicket('test_ticket');
        $this->assertInstanceOf(OpenWeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_with_suite_access_token_method()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $result = $provider->withSuiteAccessToken('test_token');
        $this->assertInstanceOf(OpenWeWork::class, $result);
        $this->assertSame($provider, $result);
    }

    public function test_user_from_code_success()
    {
        $mockProvider = $this->getMockBuilder(OpenWeWork::class)
            ->setConstructorArgs([[
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url' => 'http://localhost/callback',
            ]])
            ->onlyMethods(['getSuiteAccessToken', 'getUser'])
            ->getMock();

        $mockProvider->method('getSuiteAccessToken')->willReturn('suite_token');
        $mockProvider->method('getUser')->willReturn([
            'UserId' => 'user123',
            'openid' => 'openid123',
        ]);

        $user = $mockProvider->userFromCode('test_code');

        $this->assertSame('user123', $user->getId());
    }

    public function test_user_from_code_with_detailed_success()
    {
        $mockProvider = $this->getMockBuilder(OpenWeWork::class)
            ->setConstructorArgs([[
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url' => 'http://localhost/callback',
            ]])
            ->onlyMethods(['getSuiteAccessToken', 'getUser', 'getUserByTicket'])
            ->getMock();

        $mockProvider->method('getSuiteAccessToken')->willReturn('suite_token');
        $mockProvider->method('getUser')->willReturn([
            'UserId' => 'user123',
            'user_ticket' => 'user_ticket_123',
        ]);
        $mockProvider->method('getUserByTicket')->willReturn([
            'userid' => 'user123',
            'name' => 'Test User',
        ]);

        $user = $mockProvider->detailed()->userFromCode('test_code');

        $this->assertSame('user123', $user->getId());
    }

    public function test_throws_exception_when_user_ticket_missing()
    {
        // Mock the methods
        $mockProvider = $this->getMockBuilder(OpenWeWork::class)
            ->setConstructorArgs([[
                'client_id' => 'client_id',
                'client_secret' => 'client_secret',
                'redirect_url' => 'http://localhost/callback',
                'suite_id' => 'suite_id',
                'suite_secret' => 'suite_secret',
            ]])
            ->onlyMethods(['getSuiteAccessToken', 'getUser'])
            ->getMock();

        // Set detailed to true to trigger the user_ticket validation
        $detailedProperty = new \ReflectionProperty(OpenWeWork::class, 'detailed');
        $detailedProperty->setAccessible(true);
        $detailedProperty->setValue($mockProvider, true);

        $mockProvider->method('getSuiteAccessToken')->willReturn('suite_token');
        $mockProvider->method('getUser')->willReturn([
            'UserId' => 'user123',
            // Missing user_ticket
        ]);

        $this->expectException(AuthorizeFailedException::class);
        $this->expectExceptionMessage('Authorization failed: missing user_ticket in response');

        $mockProvider->userFromCode('test_code');
    }

    public function test_get_user_by_token_throws_exception()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $this->expectException(MethodDoesNotSupportException::class);
        $this->expectExceptionMessage('Open WeWork doesn\'t support access_token mode');

        $getUserByToken = new \ReflectionMethod(OpenWeWork::class, 'getUserByToken');
        $getUserByToken->setAccessible(true);
        $getUserByToken->invoke($provider, 'test_token');
    }

    public function test_map_user_to_object_detailed()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        // Set detailed to true
        $detailedProperty = new \ReflectionProperty(OpenWeWork::class, 'detailed');
        $detailedProperty->setAccessible(true);
        $detailedProperty->setValue($provider, true);

        $mapUserToObject = new \ReflectionMethod(OpenWeWork::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $user = [
            'userid' => 'user123',
            'name' => 'Test User',
            'avatar' => 'http://avatar.url',
            'gender' => '1',
            'corpid' => 'corp123',
            'open_userid' => 'open_user123',
            'qr_code' => 'qr_code_123',
        ];

        $result = $mapUserToObject->invoke($provider, $user);

        $this->assertSame('user123', $result->getId());
        $this->assertSame('Test User', $result->getName());
        $this->assertSame('http://avatar.url', $result->getAvatar());
    }

    public function test_map_user_to_object_simple()
    {
        $provider = new OpenWeWork([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect_url' => 'http://localhost/callback',
        ]);

        $mapUserToObject = new \ReflectionMethod(OpenWeWork::class, 'mapUserToObject');
        $mapUserToObject->setAccessible(true);

        $user = [
            'UserId' => 'user123',
            'OpenId' => 'openid123',
            'CorpId' => 'corp123',
            'open_userid' => 'open_user123',
        ];

        $result = $mapUserToObject->invoke($provider, $user);

        $this->assertSame('user123', $result->getId());
    }
}
