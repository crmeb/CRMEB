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

/**
 * 电子面单模板
 * Class ExpressTemplate
 * @package app\model\driver\express
 */
class ExpressTemplate extends BaseModel
{
    /**
     * @var string
     */
    protected $name = 'express_template';

    /**
     * @var string
     */
    protected $pk = 'id';

    /**
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 快递公司编码搜索器
     * @param $query
     * @param $value
     */
    public function searchComAttr($query, $value)
    {
        if ($value) $query->where('com', $value);
    }

    /**
     * 驱动标识搜索器
     * @param $query
     * @param $value
     */
    public function searchDriverKeyAttr($query, $value)
    {
        if ($value) $query->where('driver_key', $value);
    }

    /**
     * 状态搜索器
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') $query->where('status', $value);
    }

    /**
     * 模板名称搜索器
     * @param $query
     * @param $value
     */
    public function searchTempNameAttr($query, $value)
    {
        if ($value) $query->where('temp_name', 'like', "%{$value}%");
    }

    /**
     * extra_config 字段自动序列化
     * @param $value
     * @return mixed
     */
    public function getExtraConfigAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * extra_config 字段自动序列化存储
     * @param $value
     * @return string
     */
    public function setExtraConfigAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    /**
     * 创建时间获取器
     * @param $value
     * @return string
     */
    public function getAddTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', (int)$value) : '';
    }

    /**
     * 更新时间获取器
     * @param $value
     * @return string
     */
    public function getUpdateTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', (int)$value) : '';
    }
}
