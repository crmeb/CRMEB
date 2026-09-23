<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\model\user;


use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 会员卡批次模型类
 * 对应数据表 eb_member_card_batch，存储会员卡批次信息
 */
class MemberCardBatch extends BaseModel
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
    protected $name = 'member_card_batch';

    /**
     * 新增时自动填充的字段
     * @var array
     */
    protected $insert = ['add_time'];

    /**
     * 隐藏的字段（JSON序列化时不输出）
     * @var array
     */
    protected $hidden = ['update_time'];

    /**
     * 禁用自动更新时间戳
     * @var bool
     */
    protected $updateTime = false;

    /**
     * 卡批次名称搜索器
     * @param Model $query
     * @param $value
     */
    public function searchTitleAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('title', 'like', '%' . $value . '%');
        }
    }

    /**
     * 格式化数据
     * @param $value
     * @return array|mixed
     */
    public function getQrcodeAttr($value)
    {
        $value = $value ? json_decode($value, true) : [];
        return is_array($value) ? $value : [];
    }
}
