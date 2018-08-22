<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\user;

use app\admin\model\wechat\WechatUser;
use app\admin\model\user\UserNoticeSee;
use traits\ModelTrait;
use basic\ModelBasic;

/**
 * 用户通知 model
 * Class UserNotice
 * @package app\admin\model\user
 */
class UserNotice extends ModelBasic
{
    use ModelTrait;

    /**
     * @return array
     */
    public static function getList(){
        $model = new self;
        $model->order('id desc');
        return self::page($model,function($item,$key){
            if($item["uid"] != ''){
                $uids = explode(",",$item["uid"]);
                array_splice($uids,0,1);
                array_splice($uids,count($uids)-1,1);
                $item["uid"] = $uids;
            }
        });
    }
}