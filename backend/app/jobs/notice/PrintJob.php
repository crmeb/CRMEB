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

namespace app\jobs\notice;

use app\dao\order\OrderCenterDao;
use app\services\driver\printer\SystemTicketServices;
use crmeb\basic\BaseJobs;
use crmeb\utils\Str;
use think\facade\Log;
use think\facade\Env;

/**
 * 小票打印
 * Class PrintJob
 * @package app\jobs\notice
 */
class PrintJob extends BaseJobs
{

    /**
     * 小票打印
     * @param int $id 订单中台ID
     * @param int $print_type 打印场景：1=支付后 2=下单时
     * @return bool
     */
    public function doJob($id, $print_type)
    {
        $dao = app()->make(OrderCenterDao::class);
        $centerOrder = $dao->get(['id' => (int)$id, 'is_del' => 0], [
            'order_no', 'total_price', 'pay_postage', 'deduction_price', 'pay_price', 'pay_type',
            'shipping_type', 'mark', 'add_time', 'pay_time', 'extra_info',
        ]);
        if (!$centerOrder) {
            Log::warning('[PrintJob] 订单中台记录不存在，跳过打印: id=' . (int)$id);
            return true;
        }
        $order = is_object($centerOrder) ? $centerOrder->toArray() : (array)$centerOrder;
        $extra = $order['extra_info'] ?? null;
        $extra = is_string($extra) ? json_decode($extra, true) : $extra;
        $extra = is_array($extra) ? $extra : [];

        // 商品明细从中台快照构建，不按源订单ID进行模糊兜底查询。
        $product = [];
        foreach ($extra['items'] ?? [] as $item) {
            $attrInfo = is_array($item['attr_info'] ?? null) ? $item['attr_info'] : [];
            $product[] = [
                'sum_price' => (string)($item['true_price'] ?? ($item['price'] ?? '0.00')),
                'cart_num' => (int)($item['cart_num'] ?? 1),
                'productInfo' => [
                    'store_name' => Str::substrUTf8((string)($item['store_name'] ?? ''), 10, 'UTF-8', ''),
                    'attrInfo' => ['suk' => $item['sku'] ?? ($attrInfo['suk'] ?? ''), 'bar_code' => $attrInfo['bar_code'] ?? ''],
                ],
            ];
        }
        if (!$product) return true;

        $contact = is_array($extra['contact'] ?? null) ? $extra['contact'] : [];
        $printOrder = [
            'order_id' => $order['order_no'] ?? '',
            'total_price' => (string)($order['total_price'] ?? '0.00'),
            'pay_postage' => (string)($order['pay_postage'] ?? '0.00'),
            'deduction_price' => (string)($order['deduction_price'] ?? '0.00'),
            'pay_price' => (string)($order['pay_price'] ?? '0.00'),
            'pay_type' => $order['pay_type'] ?? '',
            'shipping_type' => (int)($order['shipping_type'] ?? 1),
            'real_name' => $contact['name'] ?? '', 'user_phone' => $contact['phone'] ?? '',
            'user_address' => SystemTicketServices::formatAddress($contact['address'] ?? ''),
            'mark' => $order['mark'] ?? '',
            'add_time' => (int)($order['add_time'] ?? 0), 'pay_time' => (int)($order['pay_time'] ?? 0),
        ];
        app()->make(SystemTicketServices::class)->startPrint($printOrder, $product, (int)$print_type);
        return true;
    }

    /**
     * 同步降级的打印失败不回滚下单，设备提交结果已由共享服务保留
     * @param string|array $action 任务方法或参数
     * @param array $data 任务参数
     * @return mixed
     */
    protected static function executeSync($action, array $data = [])
    {
        try {
            return parent::executeSync($action, $data);
        } catch (\Throwable $e) {
            Log::error('[PrintJob] 同步小票提交失败，订单主流程继续；请检查设备提交记录');
            return false;
        }
    }

    /**
     * 打印使用独立队列重试，不阻塞支付后置通知和商品日志
     * @param string|array $action 任务方法或参数
     * @param array $data 任务参数
     * @return mixed
     */
    public static function runPaymentTask($action, array $data = [])
    {
        $method = is_array($action) ? 'doJob' : $action;
        if (!is_string($method) || !method_exists(static::class, $method)) throw new \RuntimeException('打印任务方法不存在');
        if (sys_config('queue_open', 0) == 1 && Env::get('cache.driver', 'file') === 'redis') {
            $result = static::buildQueue($action, $data);
            if (!$result) throw new \RuntimeException('打印任务入队失败');
            return $result;
        }
        // 未开启队列时只尝试一次，失败留痕但不能卡住支付任务链。
        static::executeSync($action, $data);
        return true;
    }
}
