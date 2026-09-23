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

namespace app\model\driver\printer;

use crmeb\basic\BaseModel;

/**
 * 打印机渠道模型
 * Class PrinterChannel
 * @package app\model\driver\printer
 */
class PrinterChannel extends BaseModel
{
    /**
     * 表名
     * @var string
     */
    protected $name = 'printer_channel';

    /**
     * 时间字段自动写入
     * @var bool
     */
    protected $autoWriteTimestamp = true;

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
     * 驱动搜索器
     * @param $query
     * @param $value
     */
    public function searchDriverAttr($query, $value)
    {
        if ($value) {
            $query->where('driver', $value);
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
