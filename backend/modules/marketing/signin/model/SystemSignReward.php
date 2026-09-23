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
namespace modules\marketing\signin\model;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * @author: 吴汐
 * @email: 442384644@qq.com
 * @date: 2023/7/28
 */
class SystemSignReward extends BaseModel
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
    protected $name = 'system_sign_reward';

    /**
     * 类型搜索器
     * @param $query
     * @param $value
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/7/28
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value !== '') $query->where('type', $value);
    }
}
