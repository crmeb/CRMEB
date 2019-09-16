<?php
namespace app\admin\model\wechat;

use app\admin\model\wechat\StoreServiceLog as ServiceLogModel;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 客服管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class StoreService extends BaseModel
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
    protected $name = 'store_service';

    use ModelTrait;

    /**
     * @param $mer_id
     * @return array
     */
    public static function getList($mer_id){
        return self::page(self::where('mer_id',$mer_id)->order('id desc'),function($item){
            $item['wx_name']=WechatUser::where(['uid'=>$item['uid']])->value('nickname');
        });
    }

    /**
     * 获取聊天记录用户
     * @param $now_service
     * @param $mer_id
     * @return array|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getChatUser($now_service,$mer_id){
        $chat_list = ServiceLogModel::field("uid,to_uid")->where('mer_id', $mer_id)->where('to_uid|uid', $now_service["uid"])->group("uid,to_uid")->select();
        if(count($chat_list) > 0){
            $chat_list = $chat_list->toArray();
            $arr_user = $arr_to_user = [];
            foreach ($chat_list as $key => $value) {
                array_push($arr_user,$value["uid"]);
                array_push($arr_to_user,$value["to_uid"]);
            }
            $uids = array_merge($arr_user,$arr_to_user);
            $uids = array_flip(array_flip($uids));
            $uids = array_flip($uids);
            unset($uids[$now_service["uid"]]);
            $uids = array_flip($uids);
            if(!count($uids)) return null;
            return WechatUser::field("uid,nickname,headimgurl")->whereIn('uid', $uids)->select()->toArray();
        }
        return null;
    }
}