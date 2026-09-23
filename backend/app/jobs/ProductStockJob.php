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

namespace app\jobs;

use app\services\product\product\OutCenterProductServices;
use crmeb\basic\BaseJobs;
use think\facade\Log;

class ProductStockJob extends BaseJobs
{

    /**
     * 拆分计算
     * @param array $data
     * @return bool
     */
    public function distribute(array $data): bool
    {
        try {
            foreach ($data as $key => $item) {
                ProductStockJob::dispatch('calcValueStock', [$key]);
            }
        } catch (\Exception $e) {
            Log::error(['msg' => '拆分计算失败,错误原因:' . $e->getMessage(), 'data' => $data]);
        }
        return true;
    }

    /**
     * 计算库存
     * @param int $id
     * @return bool
     */
    public function calcValueStock(int $id): bool
    {
        try {
            /** @var OutCenterProductServices $services */
            $services = app()->make(OutCenterProductServices::class);
            $services->calcStockByAttrValue($id);
        } catch (\Exception $e) {
            Log::error(['msg' => '计算商品库存失败,错误原因:' . $e->getMessage(), 'data' => $id]);
        }
        return true;
    }
}