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

use app\services\order\OrderInvoiceServices;
use crmeb\basic\BaseJobs;

/**
 * 订单开票消息队列
 * Class OrderInvoiceJob
 * @package app\jobs
 */
class OrderInvoiceJob extends BaseJobs
{

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
            /** @var OrderInvoiceServices $services */
            $services = app()->make(OrderInvoiceServices::class);
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
            /** @var OrderInvoiceServices $services */
            $services = app()->make(OrderInvoiceServices::class);
            $services->redInvoiceIssuance($id);
        } catch (\Exception $e) {
        }
        return true;
    }
}