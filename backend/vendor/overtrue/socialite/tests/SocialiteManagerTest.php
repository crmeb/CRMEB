<?php

use Overtrue\Socialite\Providers\Base;
use Overtrue\Socialite\Providers\GitHub;
use Overtrue\Socialite\SocialiteManager;
use Overtrue\Socialite\User;
use PHPUnit\Framework\TestCase;

class SocialiteManagerTest extends TestCase
{
    public function test_it_can_create_from_config()
    {
        $config = [
            'foo' => [
                'provider' => 'github',
                'client_id' => 'foo-app-id',
                'client_secret' => 'your-app-secret',
                'redirect' => 'http://localhost/socialite/callback.php',
            ],
            'bar' => [
                'provider' => 'github',
                'client_id' => 'bar-app-id',
                'client_secret' => 'your-app-secret',
                'redirect' => 'http://localhost/socialite/callback.php',
            ],
        ];

        $manager = new SocialiteManager($config);

        $this->assertInstanceOf(GitHub::class, $manager->create('foo'));
        $this->assertSame('foo-app-id', $manager->create('foo')->getClientId());

        $this->assertInstanceOf(GitHub::class, $manager->create('bar'));
        $this->assertSame('bar-app-id', $manager->create('bar')->getClientId());

        // from name
        $config = [
            'github' => [
                'client_id' => 'your-app-id',
                'client_secret' => 'your-app-secret',
                'redirect' => 'http://localhost/socialite/callback.php',
            ],
        ];

        $manager = new SocialiteManager($config);

        $this->assertInstanceOf(GitHub::class, $manager->create('github'));
        $this->assertSame('your-app-id', $manager->create('github')->getClientId());
    }

    public function test_it_can_create_from_custom_creator()
    {
        $config = [
            'foo' => [
                'provider' => 'myprovider',
                'client_id' => 'your-app-id',
                'client_secret' => 'your-app-secret',
                'redirect' => 'http://localhost/socialite/callback.php',
            ],
        ];

        $manager = new SocialiteManager($config);

        $manager->extend('myprovider', function ($config) {
            return new DummyProviderForCustomProviderTest($config);
        });

        $this->assertInstanceOf(DummyProviderForCustomProviderTest::class, $manager->create('foo'));
    }

    public function test_it_can_create_from_custom_provider_class()
    {
        $config = [
            'foo' => [
                'provider' => DummyProviderForCustomProviderTest::class,
                'client_id' => 'your-app-id',
                'client_secret' => 'your-app-secret',
                'redirect' => 'http://localhost/socialite/callback.php',
            ],
        ];

        $manager = new SocialiteManager($config);

        $this->assertInstanceOf(DummyProviderForCustomProviderTest::class, $manager->create('foo'));
    }
}

class DummyProviderForCustomProviderTest extends Base
{
    protected function getAuthUrl(): string
    {
        return '';
    }

    protected function getTokenUrl(): string
    {
        return '';
    }

    protected function getUserByToken(string $token): array
    {
        return [];
    }

    protected function mapUserToObject(array $user): User
    {
        return new User([]);
    }
}
