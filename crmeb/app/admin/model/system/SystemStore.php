<?php


namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 门店自提 model
 * Class SystemStore
 * @package app\admin\model\system
 */
class SystemStore extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'system_store';


    public static function getLatlngAttr($value,$data)
    {
        return $data['latitude'].','.$data['longitude'];
    }

    public static function verificWhere()
    {
        return self::where('is_show',1)->where('is_del',0);
    }
    /*
     * 获取门店信息
     * @param int $id
     * */
    public static function getStoreDispose($id = 0)
    {
        if($id)
            $storeInfo = self::verificWhere()->where('id',$id)->find();
        else
            $storeInfo = self::verificWhere()->find();
        if($storeInfo) {
            $storeInfo['latlng'] = self::getLatlngAttr(null, $storeInfo);
            $storeInfo['valid_time'] = $storeInfo['valid_time'] ? explode(' - ', $storeInfo['valid_time']) : [];
            $storeInfo['day_time'] = $storeInfo['day_time'] ? explode(' - ', $storeInfo['day_time']) : [];
            $storeInfo['address'] = $storeInfo['address'] ? explode(',', $storeInfo['address']) : [];
        }else{
            $storeInfo['latlng'] =[];
            $storeInfo['valid_time']=[];
            $storeInfo['valid_time']=[];
            $storeInfo['day_time']=[];
            $storeInfo['address']=[];
            $storeInfo['id']=0;
        }
        return $storeInfo;
    }

}