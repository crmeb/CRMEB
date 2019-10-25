<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 *  设置浏览信息
 * @param $uid
 * @param int $product_id
 * @param int $cate
 * @param string $type
 * @param string $content
 * @param int $min
 */
function setView($uid, $product_id = 0, $cate = 0, $type = '', $content = '', $min = 20)
{
    $Db = new \app\admin\model\store\StoreVisit;
    $view = $Db->where('uid', $uid)->where('product_id', $product_id)->field('count,add_time,id')->find();
    if ($view && $type != 'search') {
        $time = time();
        if (($view['add_time'] + $min) < $time) {
            $Db->where(['id' => $view['id']])->update(['count' => $view['count'] + 1, 'add_time' => time()]);
        }
    } else {
        $cate = explode(',', $cate)[0];
        $Db->insert([
            'add_time' => time(),
            'count' => 1,
            'product_id' => $product_id,
            'cate_id' => $cate,
            'type' => $type,
            'uid' => $uid,
            'content' => $content
        ]);
    }
}