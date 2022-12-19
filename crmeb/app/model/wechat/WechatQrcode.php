<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\wechat;


use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 渠道码model
 * Class WechatKey
 * @package app\model\wechat
 */
class WechatQrcode extends BaseModel
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
    protected $name = 'wechat_qrcode';

    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid')->bind(['nickname', 'avatar']);
    }

    public function record()
    {
        return $this->hasOne(WechatQrcodeRecord::class, 'qid', 'id');
    }

    public function searchUidAttr($query, $value)
    {
        if ($value != '') $query->where('uid', $value);
    }

    public function searchCateIdAttr($query, $value)
    {
        if ($value != '') $query->where('cate_id', $value);
    }

    public function searchNameAttr($query, $value)
    {
        if ($value != '') $query->where('name', $value);
    }

    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '') $query->where('is_del', $value);
    }
}
