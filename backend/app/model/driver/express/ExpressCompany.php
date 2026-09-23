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

namespace app\model\driver\express;

use crmeb\basic\BaseModel;
use think\Model;

/**
 * 电子面单物流公司管理模型
 * Class ExpressCompany
 * @package app\model\driver\express
 */
class ExpressCompany extends BaseModel
{
    /**
     * 表名
     * @var string
     */
    protected $name = 'express_company';

    /**
     * 主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 自动写入时间戳
     * @var bool
     */
    protected $autoWriteTimestamp = false;

    /**
     * 状态搜索器
     * @param Model $query
     * @param mixed $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * 驱动标识搜索器
     * @param Model $query
     * @param mixed $value
     */
    public function searchDriverKeyAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('driver_key', $value);
        }
    }

    /**
     * 快递公司编码搜索器
     * @param Model $query
     * @param mixed $value
     */
    public function searchCodeAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('code', $value);
        }
    }

    /**
     * 是否展示搜索器
     * @param Model $query
     * @param mixed $value
     */
    public function searchIsShowAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('is_show', $value);
        }
    }
}
