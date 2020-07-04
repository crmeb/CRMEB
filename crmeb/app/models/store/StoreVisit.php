<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\models\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\admin\model\user\User;

/**
 * 商品浏览分析
 * Class StoreVisit
 * @package app\admin\model\store
 */
class StoreVisit extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_visit';

    use ModelTrait;

    /**
     *  设置浏览信息
     * @param $uid
     * @param int $product_id
     * @param int $product_type
     * @param int $cate
     * @param string $type
     * @param string $content
     * @param int $min
     */
    public static function setView($uid, $product_id = 0, $product_type = 'product', $cate = 0, $type = '', $content = '', $min = 20)
    {
        $model = new self();
        $view = $model->where('uid', $uid)->where('product_id', $product_id)->where('product_type', $product_type)->field('count,add_time,id')->find();
        if ($view && $type != 'search') {
            $time = time();
            if (($view['add_time'] + $min) < $time) {
                $model->where(['id' => $view['id']])->update(['count' => $view['count'] + 1, 'add_time' => time()]);
            }
        } else {
            $cate = explode(',', $cate)[0];
            $model->insert([
                'add_time' => time(),
                'count' => 1,
                'product_id' => $product_id,
                'product_type' => $product_type,
                'cate_id' => $cate,
                'type' => $type,
                'uid' => $uid,
                'content' => $content
            ]);
        }
    }

}