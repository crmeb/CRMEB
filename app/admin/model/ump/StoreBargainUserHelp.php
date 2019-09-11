<?php
namespace app\admin\model\ump;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 砍价帮砍Model
 * Class StoreBargainUserHelp
 * @package app\admin\model\ump
 */
class StoreBargainUserHelp extends BaseModel
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
    protected $name = 'store_bargain_user_help';

    use ModelTrait;

    /**
     * 获取砍价昌平帮忙砍价人数
     * @param int $bargainId
     * @return int|string
     */
    public static function getCountPeopleHelp($bargainId = 0){
        if(!$bargainId) return 0;
        return self::where('bargain_id',$bargainId)->count();
    }

}

