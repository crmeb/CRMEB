<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace behavior\wechat;


use think\Db;

/**
 * 素材消息行为
 * Class MaterialBehavior
 * @package behavior\wechat
 */
class MaterialBehavior
{
    public static function db()
    {
        return Db::name('WechatMedia');
    }

    public static function wechatMaterialAfter($data,$type)
    {
        $data['type'] = $type;
        $data['add_time'] = time();
        $data['temporary'] = 0;
        self::db()->insert($data);
    }

    public static function wechatMaterialTemporaryAfter($data,$type)
    {
        $data['type'] = $type;
        $data['add_time'] = time();
        $data['temporary'] = 1;
        self::db()->insert($data);
    }
}