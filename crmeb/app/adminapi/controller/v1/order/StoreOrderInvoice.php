<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\order;

use app\adminapi\controller\AuthController;
use app\services\order\StoreOrderInvoiceServices;
use app\services\order\StoreOrderServices;
use app\services\product\product\StoreProductServices;
use app\services\system\store\SystemStoreServices;
use app\services\user\UserServices;
use think\facade\App;

/**
 * 发票管理
 * Class StoreOrderInvoice
 * @package app\adminapi\controller\v1\order
 */
class StoreOrderInvoice extends AuthController
{

    /**
     * StoreOrderInvoice constructor.
     * @param App $app
     * @param StoreOrderInvoiceServices $services
     */
    public function __construct(App $app, StoreOrderInvoiceServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 获取订单类型数量
     * @return mixed
     */
    public function chart()
    {
        $where = $this->request->getMore([
            ['data', '', '', 'time'],
            ['real_name', ''],
            ['field_key', ''],
            [['type', 'd'], 0],
        ]);
        $data = $this->services->chart($where);
        return app('json')->success($data);
    }

    /**
     * 查询发票列表
     * @return mixed
     */
    public function list()
    {
        $where = $this->request->getMore([
            ['status', 0],
            ['real_name', ''],
            ['header_type', ''],
            ['type', ''],
            ['data', '', '', 'time'],
            ['field_key', ''],
        ]);
        return app('json')->success($this->services->getList($where));
    }


    /**
     * 设置发票状态
     * @param string $id
     * @return mixed
     */
    public function set_invoice($id = '')
    {
        if ($id == '') return app('json')->fail(100100);
        $data = $this->request->postMore([
            ['is_invoice', 0],
            ['invoice_number', 0],
            ['remark', '']
        ]);
        if ($data['is_invoice'] == 1 && !$data['invoice_number']) {
            return app('json')->fail(400166);
        }
        if ($data['invoice_number'] && !preg_match('/^\d{8,10}$/', $data['invoice_number'])) {
            return app('json')->fail(400167);
        }
        $this->services->setInvoice((int)$id, $data);
        return app('json')->success(100014);
    }

    /**
     * 订单详情
     * @param $id 订单id
     * @return mixed
     */
    public function orderInfo(StoreProductServices $productServices, StoreOrderServices $orderServices, $id)
    {
        if (!$id || !($orderInfo = $orderServices->get($id))) {
            return app('json')->fail(400118);
        }
        /** @var UserServices $services */
        $services = app()->make(UserServices::class);
        $userInfo = $services->get($orderInfo['uid']);
        if (!$userInfo) {
            return app('json')->fail(400119);
        }
        $userInfo = $userInfo->hidden(['pwd', 'add_ip', 'last_ip', 'login_type']);
        $userInfo['spread_name'] = '';
        if ($userInfo['spread_uid'])
            $userInfo['spread_name'] = $services->value(['uid' => $userInfo['spread_uid']], 'nickname');
        $orderInfo = $orderServices->tidyOrder($orderInfo->toArray(), true, true);
        //核算优惠金额
        $vipTruePrice = array_column($orderInfo['cartInfo'], 'vip_sum_truePrice');
        $vipTruePrice = array_sum($vipTruePrice);
        $orderInfo['vip_true_price'] = $vipTruePrice ?: 0;

        $orderInfo['add_time'] = $orderInfo['_add_time'] ?? '';
        $productId = array_column($orderInfo['cartInfo'], 'product_id');
        $cateData = $productServices->productIdByProductCateName($productId);
        foreach ($orderInfo['cartInfo'] as &$item) {
            $item['class_name'] = $cateData[$item['product_id']] ?? '';
        }
        if ($orderInfo['store_id'] && $orderInfo['shipping_type'] == 2) {
            /** @var  $storeServices */
            $storeServices = app()->make(SystemStoreServices::class);
            $orderInfo['_store_name'] = $storeServices->value(['id' => $orderInfo['store_id']], 'name');
        } else {
            $orderInfo['_store_name'] = '';
        }
        $userInfo = $userInfo->toArray();
        $invoice = $this->services->getOne(['order_id' => $id]);
        return app('json')->success(compact('orderInfo', 'userInfo', 'invoice'));
    }
}
