<?php


namespace app\model\wechat;


use app\model\user\User;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

class WechatQrcodeRecord extends BaseModel
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
    protected $name = 'wechat_qrcode_record';

    /**
     * 关联user
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'uid', 'uid');
    }

    public function searchQidAttr($query, $value)
    {
        if ($value) $query->where('qid', $value);
    }

    public function searchIsFollowAttr($query, $value)
    {
        if ($value !== '') $query->where('is_follow', $value);
    }
}