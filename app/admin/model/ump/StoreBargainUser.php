<?php
namespace app\admin\model\ump;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 参与砍价Model
 * Class StoreBargainUser
 * @package app\admin\model\ump
 */
class StoreBargainUser extends BaseModel
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
    protected $name = 'store_bargain_user';

    use ModelTrait;

    /**
     * 获取参与人数
     * @param int $bargainId $bargainId 砍价产品ID
     * @param int $status   $status 状态
     * @return int|string
     */
    public static function getCountPeopleAll($bargainId = 0,$status = 0){
        if(!$bargainId) return 0;
        $model = new self();
        $model = $model->where('bargain_id',$bargainId);
        if($status) $model = $model->where('status',$status);
        return $model->count();
    }

}