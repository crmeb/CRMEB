<?php
declare(strict_types=1);

namespace crmeb\trade\product;

/**
 * 商品查询契约
 *
 * crmeb 核心层定义此接口，由 app 层 CenterProductDao 实现，
 * 实现 crmeb → app 的依赖反转，避免核心层反向依赖应用层。
 *
 * 用途：产品类型处理器基类（BaseProductTypeHandler）获取中台商品字段时，
 * 通过此接口查询，而非直接依赖 app\dao\product\CenterProductDao。
 */
interface ProductQueryInterface
{
    /**
     * 获取商品指定字段
     * @param int $id 商品ID
     * @param array $field 字段列表
     * @return mixed 模型实例或 null
     */
    public function getProductField(int $id, array $field = ['*']);
}
