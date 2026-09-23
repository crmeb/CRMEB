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

namespace app\model\driver;

use crmeb\basic\BaseModel;

/**
 * 通用驱动字典模型（跨类型操作 eb_driver 表）
 *
 * 与 sms/email 等按类型拆分的驱动模型不同，本模型不绑定 driver_type
 * 全局作用域，供需要跨驱动类型统一读写的场景使用（如 MCP 驱动管理工具）。
 *
 * Class Driver
 * @package app\model\driver
 */
class Driver extends BaseModel
{
    /**
     * @var string
     */
    protected $name = 'driver';

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
