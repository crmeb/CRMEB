<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/23
 */

namespace app\models\store;

use app\models\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * TODO 客服信息Model
 * Class StoreServiceLog
 * @package app\models\store
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
     * 客服聊天记录
     * @param $uid
     * @param $toUid
     * @param $page
     * @param $limit
     * @return array
     */
    public static function lst($uid, $toUid, $page, $limit)
    {
        if(!$limit || !$page) return [];
        $model = new self;
        $model = $model->whereIn('uid', [$uid, $toUid]);
        $model = $model->whereIn('to_uid', [$uid, $toUid]);
        $model = $model->order('id DESC');
        $model = $model->page($page, $limit);
        return $model->select()->each(function ($item){
            $userInfo = User::getUserInfo($item['uid'], 'nickname,avatar');
            $item['nickname'] = $userInfo['nickname'];
            $item['avatar'] = $userInfo['avatar'];
        });
    }
}