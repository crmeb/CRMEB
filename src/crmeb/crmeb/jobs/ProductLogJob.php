<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\jobs;


use app\services\product\product\StoreProductLogServices;
use crmeb\basic\BaseJob;
use think\facade\Log;

class ProductLogJob extends BaseJob
{
    /**
     * @param $type  'visit','cart','order','pay','collect','refund'
     * @param $data
     * @return bool
     */
    public function doJob($type,$data)
    {
        try {
            /** @var StoreProductLogServices $productLogServices */
            $productLogServices = app()->make(StoreProductLogServices::class);
            $productLogServices->createLog($type, $data);
        }catch (\Throwable $e){
            Log::error('写入商品记录发生错误,错误原因:' . $e->getMessage());
        }
        return true;
    }
}
