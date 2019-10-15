<?php
namespace app\admin\model\wechat;

use app\models\user\User;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 客服管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class StoreServiceLog extends BaseModel
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
    protected $name = 'store_service_log';

    use ModelTrait;

    /**
     * @param $where
     * @return array
     */
    public static function getChatList($where, $mer_id){
        $model = new self;
        $model = $model->where('mer_id', $mer_id);
        $model = $model->whereIn('uid', [$where['uid'], $where['to_uid']]);
        $model = $model->whereIn('to_uid', [$where['uid'], $where['to_uid']]);
        $model->order("add_time desc");
        return self::page($model,function($item) use ($mer_id){
            $user = StoreService::field("nickname,avatar")->where('mer_id',$mer_id)->where(array("uid"=>$item["uid"]))->find();
            if(!$user)$user = User::field("nickname,avatar")->where(array("uid"=>$item["uid"]))->find();
            $item["nickname"] = $user["nickname"];
            $item["avatar"] = $user["avatar"];
        }, $where);
    }
}