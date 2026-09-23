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
 * 会员权益模型类
 * 对应数据表 eb_member_right，存储会员权益配置信息
 */
class MemberRight extends BaseModel
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
    protected $name = 'member_right';

    /**
     * 状态搜索器
     * 根据状态值筛选会员权益
     * @param \think\Model $query 查询对象
     * @param int $value 状态值
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value) {
            $query->where('status', $value);
        }
    }
}
