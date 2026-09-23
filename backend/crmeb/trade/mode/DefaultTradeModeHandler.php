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

namespace crmeb\trade\mode;

/**
 * 默认交易模式处理器（普通购买）
 * 作为默认处理器，不参与任何价格覆盖和规则限制
 * 当查询未注册的交易模式时，注册中心会自动降级为此处理器，保证默认流程可用
 */
class DefaultTradeModeHandler extends BaseTradeModeHandler
{
    /**
     * 获取交易模式标识
     * @return string
     */
    public function getType(): string
    {
        return 'normal';
    }

    /**
     * 获取交易模式中文标签
     * @return string
     */
    public function getLabel(): string
    {
        return '普通购买';
    }

    /**
     * 普通购买不判断商品是否参与活动
     * @param array $productIds 商品ID数组
     * @return array
     */
    public function getProductExist(array $productIds): array
    {
        $result = [];
        foreach ($productIds as $productId) {
            $result[$productId] = true;
        }
        return $result;
    }

    /**
     * 普通购买始终返回true
     * @param int $productId 商品ID
     * @return bool
     */
    public function isProductInActivity(int $productId): bool
    {
        return true;
    }

    /**
     * 普通购买无活动信息
     * @param int $productId 商品ID
     * @return array|null
     */
    public function getProductActivity(int $productId): ?array
    {
        return null;
    }

    /**
     * 普通购买无活动ID列表
     * @param array $productIds 商品ID数组
     * @param array $field 需要返回的字段
     * @return array
     */
    public function getActivityIdsList(array $productIds = [], array $field = []): array
    {
        return [];
    }
}
