<?php


namespace app\admin\model\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 门店自提 model
 * Class SystemVip
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


    public function getLatlngAttr($value,$data)
    {
        return $data['latitude'].','.$data['longitude'];
    }

    /*
     *
     *
     * */
    public static function getStoreDispose()
    {

    }

}