<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\virtual\controller\user;

use modules\product\virtual\service\ProductServices;
use modules\product\virtual\Handler;

/**
 * 网盘商品用户端控制器
 */
class ProductController
{
    /**
     * 商品详情
     * 过渡期：调用 app 现有商品详情 + 扩展表数据
     */
    public function read(int $id, ProductServices $service, Handler $handler)
    {
        $extendInfo = $service->getExtendInfo($id);
        if (empty($extendInfo) || ($extendInfo['product_type'] ?? '') !== 'virtual' || !empty($extendInfo['is_del']) || empty($extendInfo['is_show'])) {
            return app('json')->fail('商品不存在或已下架');
        }
        $specs = $handler->getDetailSpecs($id);
        foreach ($specs['productValue'] ?? [] as &$value) {
            unset($value['download_url'], $value['download_password']);
        }
        unset($value);
        return app('json')->success([
            'product_id'      => $id,
            'storeInfo'       => $this->sanitizeProductInfo($extendInfo),
            'productAttr'     => $specs['productAttr'] ?? [],
            'productValue'    => $specs['productValue'] ?? [],
            'extend_info'     => $this->sanitizeProductInfo($extendInfo),
            'selection_spec'  => $handler->getSelectionSpec(),
            'cart_strategy'   => $handler->getCartStrategy(),
        ]);
    }

    /**
     * 清理用户端商品详情中的付费资源。
     * @param array $product 商品信息
     * @return array
     */
    protected function sanitizeProductInfo(array $product): array
    {
        unset($product['download_url'], $product['download_password']);
        unset($product['attrs'], $product['attr']);
        return $product;
    }
}
