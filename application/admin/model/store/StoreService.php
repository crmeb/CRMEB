<?php
namespace app\admin\model\store;

use app\admin\model\store\StoreServiceLog as ServiceLogModel;
use app\admin\model\wechat\WechatUser;
use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 客服管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class StoreService extends ModelBasic
{
    use ModelTrait;
    /**
     * @return array
     */
    public static function getList($mer_id){
        $model = new self;
        $model->alias('a');
        $model->join('__USER__ b ','b.uid = a.uid');
        $model->field('a.*,b.nickname as wx_name');
        $model->where("mer_id",$mer_id);
        $model->order('a.id desc');
        return self::page($model,function($item,$key){
        });
    }

    /**
     * @return array
     */
    public static function getChatUser($now_service,$mer_id){
        $where = 'mer_id = '.$mer_id.' AND (uid = '.$now_service["uid"].' OR to_uid='.$now_service["uid"].')';
        $chat_list = ServiceLogModel::field("uid,to_uid")->where($where)->group("uid,to_uid")->select();
        if(count($chat_list) > 0){
            $arr_user = $arr_to_user = [];
            foreach ($chat_list as $key => $value) {
                array_push($arr_user,$value["uid"]);
                array_push($arr_to_user,$value["to_uid"]);
            }
            $uids = array_merge($arr_user,$arr_to_user);

            $list = WechatUser::field("uid,nickname,headimgurl")->where(array("uid"=>array(array("in",$uids),array("neq",$now_service["uid"]))))->select();
            foreach ($list as $index => $user) {
                $service = self::field("uid,nickname,avatar as headimgurl")->where(array("uid"=>$user["uid"]))->find();
                if($service)$list[$index] = $service;
            }
        }else{
            $list = null;
        }
        return $list;
    }
}