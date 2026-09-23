<?php

namespace app\model\driver\copyproduct;

use crmeb\basic\BaseModel;

class CopyDriver extends BaseModel
{
    /**
     * 驱动类型标识（对应 eb_driver.driver_type）
     */
    const DRIVER_TYPE = 'copyproduct';

    /**
     * @var string
     */
    protected $name = 'driver';

    /**
     * 全局查询作用域：读取时自动过滤 driver_type
     * @var array
     */
    protected $globalScope = ['driverType'];

    /**
     * 插入时自动填充 driver_type
     * @var array
     */
    protected $insert = ['driver_type' => self::DRIVER_TYPE];

    /**
     * 更新时自动填充 driver_type
     * @var array
     */
    protected $update = ['driver_type' => self::DRIVER_TYPE];

    /**
     * 驱动类型全局作用域
     * @param $query
     */
    public function scopeDriverType($query)
    {
        $query->where('driver_type', self::DRIVER_TYPE)->where('owner_plugin_id', '<>', '');
    }

    protected $pk = 'id';

    protected $autoWriteTimestamp = true;

    public function searchDriverNameAttr($query, $value)
    {
        if ($value) $query->where('driver_name', 'like', "%{$value}%");
    }

    public function searchDriverKeyAttr($query, $value)
    {
        if ($value) $query->where('driver_key', $value);
    }

    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') $query->where('status', $value);
    }

    public function getConfigFieldsAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function getCapabilitiesAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function getPlatformsAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setConfigFieldsAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    public function setCapabilitiesAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    public function setPlatformsAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }
}
