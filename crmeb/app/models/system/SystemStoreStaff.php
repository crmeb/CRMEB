<?php


namespace app\models\system;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 店员 model
 * Class SystemStore
 * @package app\admin\model\system
 */
class SystemStoreStaff extends BaseModel
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
    protected $name = 'system_store_staff';

    /**
     * 判断是否是有权限核销的店员
     * @param $uid
     * @return int
     */
    public static function verifyStatus($uid)
    {
        return self::where('uid', $uid)->where('status', 1)->where('verify_status', 1)->count();
    }

}