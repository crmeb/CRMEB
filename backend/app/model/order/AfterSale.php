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

namespace app\model\order;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;

/**
 * 售后单模型
 * 表：eb_after_sale
 * 管理售后申请、审核、退款、退货、换货、补发、维修的全流程
 */
class AfterSale extends BaseModel
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
    protected $name = 'after_sale';

    /**
     * 售后商品快照获取器
     * @param $value
     * @return array
     */
    public function getProductSnapshotAttr($value)
    {
        return $this->decodeJsonField($value);
    }

    /**
     * 售后关联的购物车明细ID列表获取器
     * cart_ids 为 JSON 数组（如 [3,5]），统一解码为原生数组
     * @param $value
     * @return array
     */
    public function getCartIdsAttr($value)
    {
        return $this->decodeJsonField($value);
    }

    /**
     * cart_ids 修改器：统一编码为 JSON 字符串存储
     * 支持数组/逗号分隔字符串/JSON 字符串三种输入
     * @param mixed $value
     * @return string
     */
    public function setCartIdsAttr($value)
    {
        if (empty($value)) {
            return '[]';
        }
        if (is_string($value)) {
            // 兼容 JSON 字符串或逗号分隔字符串
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $value)));
        }
        return json_encode(array_values(array_map('intval', $value)));
    }

    /**
     * 凭证图片获取器
     * @param $value
     * @return array
     */
    public function getEvidenceImagesAttr($value)
    {
        return $this->decodeJsonField($value);
    }

    /**
     * 退货快递凭证获取器
     * @param $value
     * @return array
     */
    public function getReturnDeliveryImagesAttr($value)
    {
        return $this->decodeJsonField($value);
    }

    /**
     * 统一解码 JSON 字段
     * 兼容三种来源：
     * 1. MySQL JSON 列被新版 ORM 自动包装为 \think\model\type\Json 对象（调用 value() 取已解码数组）
     * 2. 手动 json_encode 写入的 JSON 字符串（旧数据或非 JSON 类型列）
     * 3. 已解码的数组（直接返回）
     * @param mixed $value 原始字段值
     * @return array
     */
    protected function decodeJsonField($value): array
    {
        // ORM 包装的 JSON 类型对象（如 \think\model\type\Json），调用 value() 取已解码数据
        if (is_object($value) && method_exists($value, 'value')) {
            $value = $value->value();
        }
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * 售后单号搜索器
     * @param $query
     * @param $value
     */
    public function searchAfterSaleNoAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('after_sale_no', $value);
        }
    }

    /**
     * 关联订单号搜索器
     * @param $query
     * @param $value
     */
    public function searchOrderNoAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('order_no', $value);
        }
    }

    /**
     * 用户ID搜索器
     * @param $query
     * @param $value
     */
    public function searchUidAttr($query, $value)
    {
        if ($value !== '') {
            if (is_array($value)) {
                $query->whereIn('uid', $value);
            } else {
                $query->where('uid', $value);
            }
        }
    }

    /**
     * 售后类型搜索器
     * @param $query
     * @param $value
     */
    public function searchTypeAttr($query, $value)
    {
        if ($value !== '') {
            if (is_array($value)) {
                $query->whereIn('type', $value);
            } else {
                $query->where('type', $value);
            }
        }
    }

    /**
     * 售后状态搜索器
     * @param $query
     * @param $value
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            if (is_array($value)) {
                $query->whereIn('status', $value);
            } else {
                $query->where('status', $value);
            }
        }
    }

    /**
     * 产品类型搜索器
     * @param $query
     * @param $value
     */
    public function searchProductTypeAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('product_type', $value);
        }
    }

    /**
     * 活动类型搜索器
     * @param $query
     * @param $value
     */
    public function searchActivityTypeAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('activity_type', $value);
        }
    }

    /**
     * 软删除搜索器
     * @param $query
     * @param $value
     */
    public function searchIsDelAttr($query, $value)
    {
        if ($value !== '' && !is_null($value)) {
            $query->where('is_del', $value);
        }
    }

    /**
     * 一对一关联中台订单
     * @return \think\model\relation\HasOne
     */
    public function orderCenter()
    {
        return $this->hasOne(OrderCenter::class, 'id', 'order_center_id');
    }
}
