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

namespace app\model\driver\pay;

use crmeb\basic\BaseModel;

/**
 * 支付方式字典模型
 * Class PayChannel
 * @package app\model\driver\pay
 * @property string $channel_type 渠道类型：payment=支付方式 feature=支付功能
 */
class PayChannel extends BaseModel
{
    /**
     * 表名
     * @var string
     */
    protected $name = 'pay_channel';

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
     * 删除状态搜索器
     * @param $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('is_del', $value);
        }
    }

    /**
     * 平台搜索器
     * @param $query
     * @param $value
     */
    public function searchPlatformAttr($query, $value)
    {
        if ($value) {
            $query->where('platform', $value);
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
