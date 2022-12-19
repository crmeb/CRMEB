<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\jobs\notice;


use crmeb\basic\BaseJobs;
use crmeb\services\printer\Printer;
use crmeb\traits\QueueTrait;
use think\facade\Log;


/**
 * 小票打印
 * Class PrintJob
 * @package app\jobs\notice
 */
class PrintJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 小票打印
     * @param $type
     * @param $configData
     * @param $order
     * @param $product
     * @return bool|void
     */
    public function doJob($type, $configData, $order, $product)
    {

        try {
            $printer = new Printer($type, $configData);
            $printer->setPrinterContent([
                'name' => sys_config('site_name'),
                'url' => sys_config('site_url'),
                'orderInfo' => is_object($order) ? $order->toArray() : $order,
                'product' => $product
            ])->startPrinter();
            return true;
        } catch (\Throwable $e) {
            Log::error('小票打印失败失败,失败原因:' . $e->getMessage());
        }

    }

}
