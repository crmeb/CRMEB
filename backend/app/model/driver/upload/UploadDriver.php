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

namespace app\model\driver\upload;

use crmeb\basic\BaseModel;

/**
 * 存储驱动字典
 * Class UploadDriver
 * @package app\model\driver\upload
 */
class UploadDriver extends BaseModel
{

    /**
     * 驱动类型标识（对应 eb_driver.driver_type）
     */
    const DRIVER_TYPE = 'upload';

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

    /**
     * @var string
     */
    protected $pk = 'id';

    /**
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 驱动名称搜索器
     * @param $query
     * @param $value
     */
    public function searchDriverNameAttr($query, $value)
    {
        if ($value) $query->where('driver_name', 'like', "%{$value}%");
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
     * config_fields 字段自动序列化
     * @param $value
     * @return mixed
     */
    public function getConfigFieldsAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * bucket_fields 字段自动序列化
     * @param $value
     * @return mixed
     */
    public function getBucketFieldsAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * help_doc 字段自动序列化
     * @param $value
     * @return mixed
     */
    public function getHelpDocAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * config_fields 字段自动序列化存储
     * @param $value
     * @return string
     */
    public function setConfigFieldsAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    /**
     * bucket_fields 字段自动序列化存储
     * @param $value
     * @return string
     */
    public function setBucketFieldsAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    /**
     * help_doc 字段自动序列化存储
     * @param $value
     * @return string
     */
    public function setHelpDocAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    /**
     * thumb_watermark_config 字段自动序列化
     * @param $value
     * @return mixed
     */
    public function getThumbWatermarkConfigAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * thumb_watermark_config 字段自动序列化存储
     * @param $value
     * @return string
     */
    public function setThumbWatermarkConfigAttr($value)
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    /**
     * 关联存储空间表
     * @return \think\model\relation\HasMany
     */
    public function systemStorage()
    {
        return $this->hasMany(UploadStorage::class, 'driver_key', 'driver_key');
    }
}
