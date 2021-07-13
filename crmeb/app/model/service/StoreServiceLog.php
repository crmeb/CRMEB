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
 * 客服聊天记录
 * Class StoreServiceLog
 * @package app\model\service
 */
class StoreServiceLog extends BaseModel
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
    protected $name = 'store_service_log';

    public function getAddTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 一对一关联
     * @return mixed
     */
    public function service()
    {
        return $this->hasOne(StoreService::class, 'uid', 'uid')->field(['uid', 'nickname', 'avatar'])->bind([
            'nickname' => 'nickname',
            'avatar' => 'avatar'
        ]);
    }

    /**
     * 一对一关联
     * @return mixed
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->field(['uid', 'nickname', 'avatar'])->bind([
            'nickname' => 'nickname',
            'avatar' => 'avatar'
        ]);
    }

    /**
     * uid搜索器
     * @param Model $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        $query->where('uid|to_uid', $value);
    }

    /**
     * 聊天记录搜索器
     * @param Model $query
     * @param $value
     */
    public function searchChatAttr($query, $value)
    {
        $query->whereIn('uid', $value)->whereIn('to_uid', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        $query->where('type', $value);
    }

    /**
     * @param Model $query
     * @param $value
     */
    public function searchIsTouristAttr($query, $value)
    {
        $query->where('is_tourist', $value);
    }
}
