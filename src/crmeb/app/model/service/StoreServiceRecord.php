<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\service;


use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Model;

/**
 * 客服聊天用户记录
 * Class StoreServiceRecord
 * @package app\model\service
 */
class StoreServiceRecord extends BaseModel
{
    use ModelTrait;

    protected $name = 'store_service_record';

    protected $pk = 'id';

    /**
     * 更新时间
     * @var bool | string | int
     */
    protected $updateTime = false;

    /**
     * 用户关联
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'to_uid')->field(['nickname', 'uid', 'avatar'])->bind([
            'wx_nickname' => 'nickname',
            'wx_avatar' => 'avatar',
        ]);
    }

    /**
     * 客服用户
     * @return \think\model\relation\HasOne
     */
    public function service()
    {
        return $this->hasOne(StoreService::class, 'uid', 'to_uid')->field(['nickname', 'uid', 'avatar'])->bind([
            'kefu_nickname' => 'nickname',
            'kefu_avatar' => 'avatar',
        ]);
    }

    /**
     * 发送者id搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUserIdAttr($query, $value)
    {
        $query->where('user_id', $value);
    }

    /**
     * 送达人uid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchToUidAttr($query, $value)
    {
        $query->where('to_uid', $value);
    }

    /**
     * 用户昵称搜索器
     * @param Model $query
     * @param $value
     */
    public function searchTitleAttr($query, $value)
    {
        if ($value) {
            $query->whereIn('to_uid', function ($query) use ($value) {
                $query->name('user')->whereLike('nickname|uid', '%' . $value . '%')->field('uid');
            });
        }
    }

    /**
     * 是否游客
     * @param Model $query
     * @param $value
     */
    public function searchIsTouristAttr($query, $value)
    {
        $query->where('is_tourist', $value);
    }
}
