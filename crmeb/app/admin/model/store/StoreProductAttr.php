<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/08
 */

namespace app\admin\model\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class StoreProductAttr extends BaseModel
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_attr';

    use ModelTrait;

    protected function setAttrValuesAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function getAttrValuesAttr($value)
    {
        return explode(',', $value);
    }


    public static function createProductAttr($attrList, $valueList, $productId, $type=0)
    {
        $result = ['attr' => $attrList, 'value' => $valueList];
        $attrValueList = [];
        $attrNameList = [];
        foreach ($attrList as $index => $attr) {
            if (!isset($attr['value'])) return self::setErrorInfo('请输入规则名称!');
            $attr['value'] = trim($attr['value']);
            if (!isset($attr['value'])) return self::setErrorInfo('请输入规则名称!!');
            if (!isset($attr['detail']) || !count($attr['detail'])) return self::setErrorInfo('请输入属性名称!');
            foreach ($attr['detail'] as $k => $attrValue) {
                $attrValue = trim($attrValue);
                if (empty($attrValue)) return self::setErrorInfo('请输入正确的属性');
                $attr['detail'][$k] = $attrValue;
                $attrValueList[] = $attrValue;
                $attr['detail'][$k] = $attrValue;
            }
            $attrNameList[] = $attr['value'];
            $attrList[$index] = $attr;
        }
        $attrCount = count($attrList);
        foreach ($valueList as $index => $value) {
            if (!isset($value['detail']) || count($value['detail']) != $attrCount) return self::setErrorInfo('请填写正确的商品信息');
            if (!isset($value['price']) || !is_numeric($value['price']) || floatval($value['price']) != $value['price'])
                return self::setErrorInfo('请填写正确的商品价格');
            if (!isset($value['stock']) || !is_numeric($value['stock']) || intval($value['stock']) != $value['stock'])
                return self::setErrorInfo('请填写正确的商品库存');
            if (!isset($value['cost']) || !is_numeric($value['cost']) || floatval($value['cost']) != $value['cost'])
                return self::setErrorInfo('请填写正确的商品成本价格');
            if (!isset($value['pic']) || empty($value['pic']))
                return self::setErrorInfo('请上传商品图片');
            foreach ($value['detail'] as $attrName => $attrValue) {
                $attrName = trim($attrName);
                $attrValue = trim($attrValue);
                if (!in_array($attrName, $attrNameList, true)) return self::setErrorInfo($attrName . '规则不存在');
                if (!in_array($attrValue, $attrValueList, true)) return self::setErrorInfo($attrName . '属性不存在');
                if (empty($attrName)) return self::setErrorInfo('请输入正确的属性');
                $value['detail'][$attrName] = $attrValue;
            }
            $valueList[$index] = $value;
        }
        $attrGroup = [];
        $valueGroup = [];
        foreach ($attrList as $k => $value) {
            $attrGroup[] = [
                'product_id' => $productId,
                'attr_name' => $value['value'],
                'attr_values' => $value['detail'],
                'type' => $type
            ];
        }
        foreach ($valueList as $k => $value) {
            sort($value['detail'], SORT_STRING);
            $suk = implode(',', $value['detail']);
            $valueGroup[$suk] = [
                'product_id' => $productId,
                'suk' => $suk,
                'price' => $value['price'],
                'cost' => $value['cost'],
                'ot_price' => $value['ot_price'],
                'stock' => $value['stock'],
                'unique' => StoreProductAttrValue::where(['product_id'=>$productId,'suk'=>$suk,'type'=>$type])->value('unique') ? : '',
                'image' => $value['pic'],
                'bar_code' => $value['bar_code'] ?? '',
                'weight' => $value['weight'] ?? 0,
                'volume' => $value['volume'] ?? 0,
                'brokerage' => $value['brokerage'] ?? 0,
                'brokerage_two' => $value['brokerage_two'] ?? 0,
                'type' => $type,
                'quota' => $value['quota'] ?? 0,
                'quota_show' => $value['quota'] ?? 0,
            ];
        }
        if (!count($attrGroup) || !count($valueGroup)) return self::setErrorInfo('请设置至少一个属性!');
        $attrModel = new self;
        $attrValueModel = new StoreProductAttrValue;
        if (!self::clearProductAttr($productId,$type)) return false;
        $res = false !== $attrModel->saveAll($attrGroup)
            && false !== $attrValueModel->saveAll($valueGroup)
            && false !== StoreProductAttrResult::setResult($result, $productId, $type);
        if ($res)
            return true;
        else
            return self::setErrorInfo('编辑商品属性失败!');
    }

    public static function clearProductAttr($productId,$type=0)
    {
        if (empty($productId) && $productId != 0) return self::setErrorInfo('商品不存在!');
        $res = false !== self::where('product_id', $productId)->where('type', $type)->delete()
            && false !== StoreProductAttrValue::clearProductAttrValue($productId,$type);
        if (!$res)
            return self::setErrorInfo('编辑属性失败,清除旧属性失败!');
        else
            return true;
    }

    /**
     * 获取产品属性
     * @param $productId
     * @return array|bool|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getProductAttr($productId)
    {
        if (empty($productId) && $productId != 0) return self::setErrorInfo('商品不存在!');
        $count = self::where('product_id', $productId)->count();
        if (!$count) return self::setErrorInfo('商品不存在!');
        return self::where('product_id', $productId)->select()->toArray();
    }

}