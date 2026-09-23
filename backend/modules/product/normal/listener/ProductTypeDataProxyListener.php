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

namespace modules\product\normal\listener;

use crmeb\interfaces\ListenerInterface;
use modules\product\normal\dao\NormalProductSkuDao;
use think\facade\Log;

/**
 * 实物商品产品类型数据代理监听器
 * 响应 app 层 ProductTypeDataProxy 事件，代理调用 NormalProductSkuDao 方法
 * 避免 app 层（CenterProductServices / OutCenterProductServices 等）直接引用 modules 层 DAO
 * 事件结构：
 *   ['product_type' => 'normal', 'action' => string, 'args' => array]
 */
class ProductTypeDataProxyListener implements ListenerInterface
{
    /**
     * 允许调用的方法白名单
     * 涵盖 DAO 自定义方法以及 BaseDao 继承的通用读写方法
     */
    private const ALLOWED_ACTIONS = [
        // DAO 自定义方法
        'deleteByProductId',      // 按商品ID删除全部 SKU
        'decStock',               // 扣减 SKU 库存
        'incStock',               // 回滚 SKU 库存
        'getShowListByProductId', // 查询商品启用的 SKU 列表
        'updateImage',            // 更新 SKU 图片
        'getColumnByProductId',   // 按商品ID获取字段映射
        // BaseDao 继承方法
        'getColumn',              // 通用字段查询
        'update',                  // 通用更新
        'save',                    // 通用新增
        'delete',                  // 通用删除
    ];

    /**
     * 代理调用 NormalProductSkuDao 方法
     * @param mixed $event 事件数据，结构：
     *   ['product_type' => 'normal', 'action' => string, 'args' => array]
     * @return mixed 查询结果，不匹配返回 null
     */
    public function handle($event)
    {
        $productType = $event['product_type'] ?? '';
        // 非实物商品类型不处理
        if ($productType !== 'normal') {
            return null;
        }

        $action = $event['action'] ?? '';
        $args = $event['args'] ?? [];

        // 方法白名单校验
        if (!in_array($action, self::ALLOWED_ACTIONS)) {
            return null;
        }

        try {
            /** @var NormalProductSkuDao $dao */
            $dao = app()->make(NormalProductSkuDao::class);
            return $dao->$action(...$args);
        } catch (\Throwable $e) {
            Log::warning('[Normal] ProductTypeDataProxy failed: ' . $e->getMessage());
            return null;
        }
    }
}
