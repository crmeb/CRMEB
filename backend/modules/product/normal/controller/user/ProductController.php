<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace modules\product\normal\controller\user;

use app\services\product\CenterProductServices;

/**
 * 实物商品用户端控制器
 */
class ProductController
{
    /**
     * 商品详情
     * 过渡期：调用 app 现有商品详情 + 扩展表数据
     */
    public function read(int $id, CenterProductServices $service)
    {
        try {
            return app('json')->success($service->getDetail($id, (int)request()->uid()));
        } catch (\Throwable $e) {
            return app('json')->fail($e->getMessage());
        }
    }
}
