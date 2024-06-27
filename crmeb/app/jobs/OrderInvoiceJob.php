<?php

namespace app\jobs;

use app\services\order\StoreOrderInvoiceServices;
use crmeb\basic\BaseJobs;
use crmeb\traits\QueueTrait;

class OrderInvoiceJob extends BaseJobs
{
    use QueueTrait;

    /**
     * 自动开票队列
     * @param $id
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/16
     */
    public function autoInvoice($id)
    {
        try {
            if (sys_config('elec_invoice', 1) != 1) {
                return true;
            }
            /** @var StoreOrderInvoiceServices $services */
            $services = app()->make(StoreOrderInvoiceServices::class);
            $services->invoiceIssuance($id);
        } catch (\Exception $e) {
        }
        return true;
    }

    /**
     * 自动冲红队列
     * @param $id
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/16
     */
    public function autoInvoiceRed($id)
    {
        try {
            if (sys_config('elec_invoice', 1) != 1) {
                return true;
            }
            /** @var StoreOrderInvoiceServices $services */
            $services = app()->make(StoreOrderInvoiceServices::class);
            $services->redInvoiceIssuance($id);
        } catch (\Exception $e) {
        }
        return true;
    }
}