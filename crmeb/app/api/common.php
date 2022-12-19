<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
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
function set_view($uid, $product_id = 0, $cate = 0, $type = '', $content = '', $min = 20)
{
    //TODO 待优化
    $Db = new \app\models\store\StoreVisit;
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
