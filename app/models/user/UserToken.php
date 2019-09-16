<?php


namespace app\models\user;


use think\Model;

class UserToken extends Model
{
    protected $name = 'user_token';

    protected $type = [
        'create_time' => 'datetime',
        'login_ip' => 'string'
    ];

    protected $autoWriteTimestamp = true;

    protected $updateTime = false;

    public static function onBeforeInsert(UserToken $token)
    {
        if (!isset($token['login_ip']))
            $token['login_ip'] = app()->request->ip();
    }

    public static function createToken(User $user, $type): self
    {
        $tokenInfo = $user->getToken($type);
        return self::create([
            'uid' => $user->uid,
            'token' => $tokenInfo['token'],
            'expires_time' => date('Y-m-d H:i:s', $tokenInfo['params']['exp'])
        ]);
    }

    /**
     * 删除一天前的过期token
     * @return bool
     * @throws \Exception
     */
    public static function delToken()
    {
        return self::where('expires_time', '<', date('Y-m-d H:i:s',strtotime('-1 day')))->delete();
    }
}