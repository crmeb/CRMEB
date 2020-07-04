<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/08
 */

namespace app\admin\model\store;

use crmeb\basic\BaseModel;
use crmeb\services\SystemConfigService;
use crmeb\services\workerman\ChannelService;
use crmeb\traits\ModelTrait;

class StoreProductAttrValue extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr_value';

    use ModelTrait;

    protected $insert = ['unique'];

    protected function setSukAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setUniqueAttr($value, $data)
    {

        if (is_array($data['suk'])) $data['suk'] = $this->setSukAttr($data['suk']);
        return $data['unique'] ?: self::uniqueId($data['product_id'] . $data['suk'] . uniqid(true));
    }

    /*
     * 减少销量增加库存
     * */
    public static function incProductAttrStock($productId, $unique, $num, $type = 0)
    {
        $productAttr = self::where('unique', $unique)->where('product_id', $productId)->where('type', $type)->field('stock,sales')->find();
        if (!$productAttr) return true;
        if ($productAttr->sales > 0) $productAttr->sales = bcsub($productAttr->sales, $num, 0);
        if ($productAttr->sales < 0) $productAttr->sales = 0;
        $productAttr->stock = bcadd($productAttr->stock, $num, 0);
        return $productAttr->save();
    }

    public static function decProductAttrStock($productId, $unique, $num, $type = 0)
    {
        if ($type == 0) {
            $res = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)
                ->dec('stock', $num)->inc('sales', $num)->update();
        } else {
            $res = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)
                ->dec('stock', $num)->dec('quota', $num)->inc('sales', $num)->update();
        }

        if ($res) {
            $stock = self::where('product_id', $productId)->where('unique', $unique)->where('type', $type)->value('stock');
            $replenishment_num = sys_config('store_stock') ?? 0;//库存预警界限
            if ($replenishment_num >= $stock) {
                try {
                    ChannelService::instance()->send('STORE_STOCK', ['id' => $productId]);
                } catch (\Exception $e) {
                }
            }
        }
        return $res;
    }

    /**
     * 获取属性参数
     * @param $productId
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getStoreProductAttrResult($productId)
    {
        $productAttr = StoreProductAttr::getProductAttr($productId);
        if (!$productAttr) return [];
        $attr = [];
        foreach ($productAttr as $key => $value) {
            $attr[$key]['value'] = $value['attr_name'];
            $attr[$key]['detailValue'] = '';
            $attr[$key]['attrHidden'] = true;
            $attr[$key]['detail'] = $value['attr_values'];
        }
        $value = attr_format($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key => $item) {
            $detail = $item['detail'];
            sort($item['detail'], SORT_STRING);
            $suk = implode(',', $item['detail']);
            $sukValue = self::where('product_id', $productId)->where('type', 0)->where('suk', $suk)->column('bar_code,cost,price,stock as sales,image as pic', 'suk');
            if (!count($sukValue)) {
                unset($value[$key]);
            } else {
                $valueNew[$count]['detail'] = $detail;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'];
                $valueNew[$count]['price'] = $sukValue[$suk]['price'];
                $valueNew[$count]['sales'] = $sukValue[$suk]['sales'];
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'];
                $valueNew[$count]['bar_code'] = $sukValue[$suk]['bar_code'] ?? '';
                $valueNew[$count]['check'] = false;
                $count++;
            }
        }
        return ['attr' => $attr, 'value' => $valueNew];
    }


    public static function uniqueId($key)
    {
        return substr(md5($key), 12, 8);
    }

    public static function clearProductAttrValue($productId, $type = 0)
    {
        return self::where('product_id', $productId)->where('type', $type)->delete();
    }


}
