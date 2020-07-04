<?php
/**
 * Created by PhpStorm.
 * User: 吴昊天
 * Date: 2020-03-16
 * Time: 12:35
 */

namespace app\admin\model\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreDescription extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_description';

    use ModelTrait;

    /**
     * 获取详情
     * @param $product_id
     * @param int $type
     * @return mixed
     */
    public static function getDescription($product_id, $type = 0)
    {
        return self::where('product_id', $product_id)->where('type', $type)->value('description');
    }

    /**
     * 添加或者修改详情
     * @param string $description
     * @param int $product_id
     * @param int $type
     * @return bool|\think\Model|static
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function saveDescription(string $description = '', int $product_id = 0, int $type = 0)
    {
        $description = htmlspecialchars($description);
        if ($product_id) {
            $info = self::where(['product_id' => $product_id, 'type' => $type])->find();
            if ($info) {
                $info->description = $description;
                return $info->save();
            }
        }
        return self::create(compact('description', 'product_id', 'type'));
    }
}