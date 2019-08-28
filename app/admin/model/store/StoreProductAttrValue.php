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
        return is_array($value) ? implode(',',$value) : $value;
    }

    protected function setUniqueAttr($value,$data)
    {

        if(is_array($data['suk'])) $data['suk'] = $this->setSukAttr($data['suk']);
        return self::uniqueId($data['product_id'].$data['suk'].uniqid(true));
    }

    /*
     * 减少销量增加库存
     * */
    public static function incProductAttrStock($productId,$unique,$num)
    {
        $productAttr=self::where('unique',$unique)->where('product_id',$productId)->field('stock,sales')->find();
        if(!$productAttr) return true;
        if($productAttr->sales > 0) $productAttr->sales=bcsub($productAttr->sales,$num,0);
        if($productAttr->sales < 0) $productAttr->sales=0;
        $productAttr->stock = bcadd($productAttr->stock, $num,0);
        return $productAttr->save();
    }

    public static function decProductAttrStock($productId,$unique,$num)
    {
        $res = self::where('product_id',$productId)->where('unique',$unique)
            ->dec('stock',$num)->inc('sales',$num)->update();
        if($res){
            $stock = self::where('product_id',$productId)->where('unique',$unique)->value('stock');
            $replenishment_num = SystemConfigService::get('store_stock') ?? 0;//库存预警界限
            if($replenishment_num >= $stock){
                ChannelService::instance()->send('STORE_STOCK', ['id'=>$productId]);
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
        if(!$productAttr) return [];
        $attr = [];
        foreach ($productAttr as $key=>$value){
            $attr[$key]['value'] = $value['attr_name'];
            $attr[$key]['detailValue'] = '';
            $attr[$key]['attrHidden'] = true;
            $attr[$key]['detail'] = $value['attr_values'];
        }
        $value = attrFormat($attr)[1];
        $valueNew = [];
        $count = 0;
        foreach ($value as $key=>$item){
            $detail = $item['detail'];
            sort($item['detail'],SORT_STRING);
            $suk =  implode(',', $item['detail']);
            $sukValue = self::where('product_id', $productId)->where('suk', $suk)->column('cost,price,stock as sales,image as pic','suk');
            if(!count($sukValue)){
                unset($value[$key]);
            }else{
                $valueNew[$count]['detail'] = $detail;
                $valueNew[$count]['cost'] = $sukValue[$suk]['cost'];
                $valueNew[$count]['price'] = $sukValue[$suk]['price'];
                $valueNew[$count]['sales'] = $sukValue[$suk]['sales'];
                $valueNew[$count]['pic'] = $sukValue[$suk]['pic'];
                $valueNew[$count]['check'] = false;
                $count++;
            }
        }
        return ['attr'=>$attr, 'value'=>$valueNew];
    }


    public static function uniqueId($key)
    {
        return substr(md5($key),12,8);
    }

    public static function clearProductAttrValue($productId)
    {
        return self::where('product_id',$productId)->delete();
    }


}