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

declare(strict_types=1);

namespace app\model\driver\sms;

use crmeb\basic\BaseModel;

/**
 * 短信驱动字典模型
 * Class SmsDriver
 * @package app\model\driver\sms
 */
class SmsDriver extends BaseModel
{
    /**
     * 驱动类型标识（对应 eb_driver.driver_type）
     */
    const DRIVER_TYPE = 'sms';

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
     * 主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 时间字段自动写入
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 创建时间字段名
     * @var string
     */
    protected $createTime = 'add_time';

    /**
     * 更新时间字段名
     * @var string
     */
    protected $updateTime = 'update_time';

    /**
     * 状态搜索器
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('status', $value);
        }
    }

    /**
     * 获取 config_fields 的 JSON 数组
     * @param $value
     * @return array
     */
    public function getConfigFieldsAttr($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 设置 config_fields 为 JSON
     * @param $value
     * @return string
     */
    public function setConfigFieldsAttr($value): string
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }
}
