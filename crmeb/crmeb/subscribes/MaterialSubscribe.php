<?php

namespace crmeb\subscribes;

use think\facade\Db;

/**
 * 素材消息事件
 * Class MaterialSubscribe
 * @package crmeb\subscribes
 */
class MaterialSubscribe
{

    public function handle()
    {

    }

    /**
     * 图片/声音  转media  存入数据库
     * @param $event
     */
    public function onWechatMaterialAfter($event)
    {
        list($data, $type) = $event;
        $data['type'] = $type;
        $data['add_time'] = time();
        $data['temporary'] = 0;
        Db::name('WechatMedia')->insert($data);
    }
}