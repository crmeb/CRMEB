<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\wechat;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 关键词
 * Class WechatReply
 * @package app\model\wechat
 */
class WechatReply extends BaseModel
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
    protected $name = 'wechat_reply';

    /**
     * 消息类型
     * @var string[]
     */
    public static $replyType = ['text', 'image', 'news', 'voice'];

    /**
     * 公众号自动回复关联
     * @return \think\model\relation\HasMany
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/3
     */
    public function wechatKeys()
    {
        return $this->hasMany(WechatKey::class, 'reply_id', 'id');
    }

    /**
     * 客服自动回复关联
     * @return \think\model\relation\HasOne
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/8/3
     */
    public function kefuKey()
    {
        return $this->hasOne(WechatKey::class, 'reply_id', 'id')->bind(['keys']);
    }
}
