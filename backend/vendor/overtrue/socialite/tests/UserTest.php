<?php

use Overtrue\Socialite\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_jsonserialize()
    {
        $this->assertSame('[]', json_encode(new User([])));
        $this->assertSame('{"access_token":"mock-token"}', json_encode(new User(['access_token' => 'mock-token'])));
    }

    public function test_it_can_get_refresh_token()
    {
        $user = new User([
            'name' => 'fake_name',
            'access_token' => 'mock-token',
            'refresh_token' => 'fake_refresh',
        ]);

        // 能通过用 User 对象获取 refresh token
        $this->assertSame('fake_refresh', $user->getRefreshToken());
        $this->assertSame('{"name":"fake_name","access_token":"mock-token","refresh_token":"fake_refresh"}', json_encode($user));

        // 无 refresh_token 属性返回 null
        $user = new User([]);
        $this->assertSame(null, $user->getRefreshToken());
        // 能通过 setRefreshToken() 设置
        $user->setRefreshToken('fake_refreshToken');
        $this->assertSame('fake_refreshToken', $user->getRefreshToken());
        $this->assertSame('{"refresh_token":"fake_refreshToken"}', json_encode($user));
    }

    public function test_it_can_set_raw_data()
    {
        $user = new User([]);
        $data = ['data' => 'raw'];

        $user->setRaw($data);
        $this->assertSame($data, $user->getRaw());
        $this->assertSame(json_encode(['raw' => ['data' => 'raw']]), json_encode($user));
    }

    public function test_ie_can_get_attribute_by_magic_method()
    {
        $user = new User(['xx' => 'data']);

        $this->assertSame('data', $user->xx);
    }
}
