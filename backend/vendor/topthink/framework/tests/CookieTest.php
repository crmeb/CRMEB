<?php

namespace think\tests;

use DateTime;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use think\Config;
use think\Cookie;
use think\Request;

class CookieTest extends TestCase
{
    /** @var Cookie */
    protected $cookie;

    /** @var Request|MockInterface */
    protected $request;

    /** @var Config|MockInterface */
    protected $config;

    protected function setUp(): void
    {
        $this->request = m::mock(Request::class);
        $this->config = m::mock(Config::class);
        
        $this->cookie = new Cookie($this->request, [
            'expire' => 3600,
            'path' => '/',
            'domain' => 'test.com',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'lax'
        ]);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testMakeMethod()
    {
        $this->config->shouldReceive('get')
            ->with('cookie')
            ->andReturn(['expire' => 7200]);

        $cookie = Cookie::__make($this->request, $this->config);
        
        $this->assertInstanceOf(Cookie::class, $cookie);
    }

    public function testGet()
    {
        $this->request->shouldReceive('cookie')
            ->with('test_cookie', 'default')
            ->andReturn('cookie_value');

        $result = $this->cookie->get('test_cookie', 'default');
        
        $this->assertEquals('cookie_value', $result);
    }

    public function testGetAll()
    {
        $this->request->shouldReceive('cookie')
            ->with('', null)
            ->andReturn(['cookie1' => 'value1', 'cookie2' => 'value2']);

        $result = $this->cookie->get();
        
        $this->assertEquals(['cookie1' => 'value1', 'cookie2' => 'value2'], $result);
    }

    public function testHas()
    {
        $this->request->shouldReceive('has')
            ->with('test_cookie', 'cookie')
            ->andReturn(true);

        $result = $this->cookie->has('test_cookie');
        
        $this->assertTrue($result);
    }

    public function testHasReturnsFalse()
    {
        $this->request->shouldReceive('has')
            ->with('nonexistent_cookie', 'cookie')
            ->andReturn(false);

        $result = $this->cookie->has('nonexistent_cookie');
        
        $this->assertFalse($result);
    }

    public function testSetBasic()
    {
        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', 'test_value');

        $this->cookie->set('test_cookie', 'test_value');
        
        $cookies = $this->cookie->getCookie();
        $this->assertArrayHasKey('test_cookie', $cookies);
        $this->assertEquals('test_value', $cookies['test_cookie'][0]);
    }

    public function testSetWithNumericExpire()
    {
        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', 'test_value');

        $this->cookie->set('test_cookie', 'test_value', 7200);
        
        $cookies = $this->cookie->getCookie();
        $this->assertArrayHasKey('test_cookie', $cookies);
        $this->assertGreaterThan(time(), $cookies['test_cookie'][1]);
    }

    public function testSetWithDateTimeExpire()
    {
        $expire = new DateTime('+1 hour');
        
        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', 'test_value');

        $this->cookie->set('test_cookie', 'test_value', $expire);
        
        $cookies = $this->cookie->getCookie();
        $this->assertEquals($expire->getTimestamp(), $cookies['test_cookie'][1]);
    }

    public function testSetWithArrayOptions()
    {
        $options = [
            'expire' => 1800,
            'path' => '/test',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'strict'
        ];

        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', 'test_value');

        $this->cookie->set('test_cookie', 'test_value', $options);
        
        $cookies = $this->cookie->getCookie();
        $cookieData = $cookies['test_cookie'];
        
        $this->assertEquals('test_value', $cookieData[0]);
        $this->assertGreaterThan(time(), $cookieData[1]);
        $this->assertEquals('/test', $cookieData[2]['path']);
        $this->assertEquals('example.com', $cookieData[2]['domain']);
        $this->assertTrue($cookieData[2]['secure']);
        $this->assertFalse($cookieData[2]['httponly']);
        $this->assertEquals('strict', $cookieData[2]['samesite']);
    }

    public function testSetWithDateTimeInOptions()
    {
        $expire = new DateTime('+2 hours');
        $options = ['expire' => $expire];

        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', 'test_value');

        $this->cookie->set('test_cookie', 'test_value', $options);
        
        $cookies = $this->cookie->getCookie();
        $this->assertEquals($expire->getTimestamp(), $cookies['test_cookie'][1]);
    }

    public function testForever()
    {
        $this->request->shouldReceive('setCookie')
            ->with('forever_cookie', 'forever_value');

        $this->cookie->forever('forever_cookie', 'forever_value');
        
        $cookies = $this->cookie->getCookie();
        $this->assertArrayHasKey('forever_cookie', $cookies);
        $this->assertEquals('forever_value', $cookies['forever_cookie'][0]);
        $this->assertGreaterThan(time() + 315360000 - 10, $cookies['forever_cookie'][1]);
    }

    public function testForeverWithOptions()
    {
        $options = ['path' => '/forever', 'secure' => true];

        $this->request->shouldReceive('setCookie')
            ->with('forever_cookie', 'forever_value');

        $this->cookie->forever('forever_cookie', 'forever_value', $options);
        
        $cookies = $this->cookie->getCookie();
        $cookieData = $cookies['forever_cookie'];
        
        $this->assertEquals('/forever', $cookieData[2]['path']);
        $this->assertTrue($cookieData[2]['secure']);
        $this->assertGreaterThan(time() + 315360000 - 10, $cookieData[1]);
    }

    public function testForeverWithNullOptions()
    {
        $this->request->shouldReceive('setCookie')
            ->with('forever_cookie', 'forever_value');

        $this->cookie->forever('forever_cookie', 'forever_value', null);
        
        $cookies = $this->cookie->getCookie();
        $this->assertArrayHasKey('forever_cookie', $cookies);
    }

    public function testForeverWithNumericOptions()
    {
        $this->request->shouldReceive('setCookie')
            ->with('forever_cookie', 'forever_value');

        $this->cookie->forever('forever_cookie', 'forever_value', 123);
        
        $cookies = $this->cookie->getCookie();
        $this->assertArrayHasKey('forever_cookie', $cookies);
    }

    public function testDelete()
    {
        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', null);

        $this->cookie->delete('test_cookie');
        
        $cookies = $this->cookie->getCookie();
        $this->assertArrayHasKey('test_cookie', $cookies);
        $this->assertEquals('', $cookies['test_cookie'][0]);
        $this->assertLessThan(time(), $cookies['test_cookie'][1]);
    }

    public function testDeleteWithOptions()
    {
        $options = ['path' => '/test', 'domain' => 'example.com'];

        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', null);

        $this->cookie->delete('test_cookie', $options);
        
        $cookies = $this->cookie->getCookie();
        $cookieData = $cookies['test_cookie'];
        
        $this->assertEquals('', $cookieData[0]);
        $this->assertEquals('/test', $cookieData[2]['path']);
        $this->assertEquals('example.com', $cookieData[2]['domain']);
    }

    public function testGetCookie()
    {
        $this->request->shouldReceive('setCookie')
            ->with('cookie1', 'value1');
        $this->request->shouldReceive('setCookie')
            ->with('cookie2', 'value2');

        $this->cookie->set('cookie1', 'value1');
        $this->cookie->set('cookie2', 'value2');
        
        $cookies = $this->cookie->getCookie();
        
        $this->assertArrayHasKey('cookie1', $cookies);
        $this->assertArrayHasKey('cookie2', $cookies);
        $this->assertEquals('value1', $cookies['cookie1'][0]);
        $this->assertEquals('value2', $cookies['cookie2'][0]);
    }

    public function testSave()
    {
        // Mock the protected saveCookie method by extending the class
        $cookie = new class($this->request) extends Cookie {
            public $savedCookies = [];
            
            protected function saveCookie(string $name, string $value, int $expire, string $path, string $domain, bool $secure, bool $httponly, string $samesite): void
            {
                $this->savedCookies[] = [
                    'name' => $name,
                    'value' => $value,
                    'expire' => $expire,
                    'path' => $path,
                    'domain' => $domain,
                    'secure' => $secure,
                    'httponly' => $httponly,
                    'samesite' => $samesite,
                ];
            }
        };

        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', 'test_value');

        $cookie->set('test_cookie', 'test_value');
        $cookie->save();
        
        $this->assertCount(1, $cookie->savedCookies);
        $this->assertEquals('test_cookie', $cookie->savedCookies[0]['name']);
        $this->assertEquals('test_value', $cookie->savedCookies[0]['value']);
    }

    public function testCaseInsensitiveConfig()
    {
        $cookie = new Cookie($this->request, [
            'EXPIRE' => 1800,
            'PATH' => '/test',
            'DOMAIN' => 'TEST.COM'
        ]);

        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', 'test_value');

        $cookie->set('test_cookie', 'test_value');
        
        $cookies = $cookie->getCookie();
        $cookieData = $cookies['test_cookie'];
        
        $this->assertEquals('/test', $cookieData[2]['path']);
        $this->assertEquals('TEST.COM', $cookieData[2]['domain']);
    }

    public function testDefaultConfig()
    {
        $cookie = new Cookie($this->request);

        $this->request->shouldReceive('setCookie')
            ->with('test_cookie', 'test_value');

        $cookie->set('test_cookie', 'test_value');
        
        $cookies = $cookie->getCookie();
        $cookieData = $cookies['test_cookie'];
        
        $this->assertEquals('/', $cookieData[2]['path']);
        $this->assertEquals('', $cookieData[2]['domain']);
        $this->assertFalse($cookieData[2]['secure']);
        $this->assertFalse($cookieData[2]['httponly']);
    }
}