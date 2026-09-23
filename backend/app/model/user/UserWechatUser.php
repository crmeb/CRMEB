<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------

namespace app\model\user;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 微信渠道身份映射。
 *
 * 该表同时保存公众号和小程序身份，属于用户认证基础数据，不能依赖某一个渠道模块。
 */
class UserWechatUser extends BaseModel
{
    use ModelTrait;

    protected $pk = 'id';

    protected $name = 'wechat_user';

    protected $insert = ['add_time'];

    public static function setAddTimeAttr()
    {
        return time();
    }

    protected function getAddTimeAttr($value)
    {
        if ($value) return date('Y-m-d H:i', (int)$value);
        return $value;
    }

    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }

    public function searchUnionidAttr($query, $value)
    {
        return $query->where('unionid', $value);
    }

    public function searchOpenidAttr($query, $value)
    {
        return $query->where('openid', $value);
    }
}
