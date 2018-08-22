<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/08
 */

namespace app\admin\model\ump;


use basic\ModelBasic;
use traits\ModelTrait;

class StoreSeckillAttr extends ModelBasic
{
    use ModelTrait;

    protected function setAttrValuesAttr($value)
    {
        return is_array($value) ? implode(',',$value) : $value;
    }

    protected function getAttrValuesAttr($value)
    {
        return explode(',',$value);
    }


    public static function createProductAttr($attrList,$valueList,$productId)
    {
        $result = ['attr'=>$attrList,'value'=>$valueList];
        $attrValueList = [];
        $attrNameList = [];
        foreach ($attrList as $index=>$attr){
            if(!isset($attr['value'])) return self::setErrorInfo('请输入规则名称!');
            $attr['value'] = trim($attr['value']);
            if(!isset($attr['value'])) return self::setErrorInfo('请输入规则名称!!');
            if(!isset($attr['detail']) || !count($attr['detail'])) return self::setErrorInfo('请输入属性名称!');
            foreach ($attr['detail'] as $k=>$attrValue){
                $attrValue = trim($attrValue);
                if(empty($attrValue)) return self::setErrorInfo('请输入正确的属性');
                $attr['detail'][$k] = $attrValue;
                $attrValueList[] = $attrValue;
                $attr['detail'][$k] = $attrValue;
            }
            $attrNameList[] = $attr['value'];
            $attrList[$index] = $attr;
        }
        $attrCount = count($attrList);
        foreach ($valueList as $index=>$value){
            if(!isset($value['detail']) || count($value['detail']) != $attrCount) return self::setErrorInfo('请填写正确的商品信息');
            if(!isset($value['price']) || !is_numeric($value['price']) || floatval($value['price']) != $value['price'])
                return self::setErrorInfo('请填写正确的商品价格');
            if(!isset($value['sales']) || !is_numeric($value['sales']) || intval($value['sales']) != $value['sales'])
                return self::setErrorInfo('请填写正确的商品库存');
            if(!isset($value['pic']) || empty($value['pic']))
                return self::setErrorInfo('请上传商品图片');
            foreach ($value['detail'] as $attrName=>$attrValue){
                $attrName = trim($attrName);
                $attrValue = trim($attrValue);
                if(!in_array($attrName,$attrNameList,true)) return self::setErrorInfo($attrName.'规则不存在');
                if(!in_array($attrValue,$attrValueList,true)) return self::setErrorInfo($attrName.'属性不存在');
                if(empty($attrName)) return self::setErrorInfo('请输入正确的属性');
                $value['detail'][$attrName] = $attrValue;
            }
            $valueList[$index] = $value;
        }
        $attrGroup = [];
        $valueGroup = [];
        foreach ($attrList as $k=>$value){
            $attrGroup[] = [
                'product_id'=>$productId,
                'attr_name'=>$value['value'],
                'attr_values'=>$value['detail']
            ];
        }
        foreach ($valueList as $k=>$value){
            ksort($value['detail'],SORT_STRING);
            $suk = implode(',',$value['detail']);
            $valueGroup[$suk] = [
                'product_id'=>$productId,
                'suk'=>$suk,
                'price'=>$value['price'],
                'stock'=>$value['sales'],
                'image'=>$value['pic']
            ];
        }
        if(!count($attrGroup) || !count($valueGroup)) return self::setErrorInfo('请设置至少一个属性!');
        $attrModel = new self;
        $attrValueModel = new StoreSeckillAttrValue;
        self::beginTrans();
        if(!self::clearProductAttr($productId)) return false;
        $res = false !== $attrModel->saveAll($attrGroup)
            && false !== $attrValueModel->saveAll($valueGroup)
        && false !== StoreSeckillAttrResult::setResult($result,$productId);
        self::checkTrans($res);
        if($res)
            return true;
        else
            return self::setErrorInfo('编辑商品属性失败!');
    }

    public static function clearProductAttr($productId)
    {
        if (empty($productId) && $productId != 0) return self::setErrorInfo('商品不存在!');
        $res = false !== self::where('product_id',$productId)->delete()
            && false !== StoreSeckillAttrValue::clearProductAttrValue($productId);
        if(!$res)
            return self::setErrorInfo('编辑属性失败,清除旧属性失败!');
        else
            return true;
    }

}