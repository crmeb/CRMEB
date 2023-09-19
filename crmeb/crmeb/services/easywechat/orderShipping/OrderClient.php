<?php

namespace crmeb\services\easywechat\orderShipping;

use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use EasyWeChat\Core\Exceptions\HttpException;


class OrderClient extends BaseOrder
{
    const cache_prefix = 'mini_order';

    const express_company = 'ZTO';   // 默认发货快递公司为（中通快递）

    /**
     * @var
     */
    protected $cache;

    /**
     * 处理联系人
     * @param array $contact
     * @return array
     *
     * @date 2023/05/10
     * @author yyw
     */
    protected function handleContact(array $contact = []): array
    {
        if (isset($contact)) {
            if (isset($contact['consignor_contact']) && $contact['consignor_contact']) {
                $contact['consignor_contact'] = Utility::encryptTel($contact['consignor_contact']);
            }
            if (isset($contact['receiver_contact']) && $contact['receiver_contact']) {
                $contact['receiver_contact'] = Utility::encryptTel($contact['receiver_contact']);
            }
        }
        return $contact;
    }

    /**
     * 发货
     * @param string $out_trade_no
     * @param int $logistics_type
     * @param array $shipping_list
     * @param string $payer_openid
     * @param int $delivery_mode
     * @param bool $is_all_delivered
     * @return array
     * @throws HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public function shippingByTradeNo(string $out_trade_no, int $logistics_type, array $shipping_list, string $payer_openid, $path, int $delivery_mode = 1, bool $is_all_delivered = true)
    {
        if (!$this->checkManaged()) {
            throw new AdminException('开通小程序订单管理服务后重试');
        }
        $params = [
            'order_key' => [
                'order_number_type' => 1,
                'mchid' => $this->config['config']['mini_program']['merchant_id'],
                'out_trade_no' => $out_trade_no,
            ],
            'logistics_type' => $logistics_type,
            'delivery_mode' => $delivery_mode,
            'upload_time' => date(DATE_RFC3339),
            'payer' => [
                'openid' => $payer_openid
            ]
        ];
        if ($delivery_mode == 2) {
            $params['is_all_delivered'] = $is_all_delivered;
        }

        foreach ($shipping_list as $shipping) {
            $contact = $this->handleContact($shipping['contact'] ?? []);
            $params['shipping_list'][] = [
                'tracking_no' => $shipping['tracking_no'] ?? '',
                'express_company' => isset($shipping['express_company']) ? $this->getDelivery($shipping['express_company']) : '',
                'item_desc' => $shipping['item_desc'],
                'contact' => $contact
            ];
        }
        // 跳转路径
        $this->setMesJumpPath($path);
        return $this->shipping($params);
    }


    /**
     * 合单
     * @param string $out_trade_no
     * @param int $logistics_type
     * @param array $sub_orders
     * @param string $payer_openid
     * @param int $delivery_mode
     * @param bool $is_all_delivered
     * @return array
     * @throws HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public function combinedShippingByTradeNo(string $out_trade_no, int $logistics_type, array $sub_orders, string $payer_openid, int $delivery_mode = 2, bool $is_all_delivered = false)
    {
        if (!$this->checkManaged()) {
            throw new AdminException('开通小程序订单管理服务后重试');
        }
        $params = [
            'order_key' => [
                'order_number_type' => 1,
                'mchid' => $this->config['config']['mini_program']['merchant_id'],
                'out_trade_no' => $out_trade_no,
            ],
            'upload_time' => date(DATE_RFC3339),
            'payer' => [
                'openid' => $payer_openid
            ]
        ];

        foreach ($sub_orders as $order) {
            $sub_order = [
                'order_key' => [
                    'order_number_type' => 1,
                    'mchid' => $this->config['config']['mini_program']['merchant_id'],
                    'out_trade_no' => $order['out_trade_no'],
                    'logistics_type' => $logistics_type,
                ],
                'delivery_mode' => $delivery_mode,
                'is_all_delivered' => $is_all_delivered
            ];
            foreach ($sub_orders['shipping_list'] as $shipping) {
                $contact = $this->handleContact($shipping['contact'] ?? []);
                $sub_order['shipping_list'][] = [
                    'tracking_no' => $shipping['tracking_no'] ?? '',
                    'express_company' => isset($shipping['express_company']) ? $this->getDelivery($shipping['express_company']) : '',
                    'item_desc' => $shipping['item_desc'],
                    'contact' => $contact
                ];
            }
            $params['sub_orders'][] = $sub_order;
        }

        return $this->combinedShipping($params);
    }


    /**
     * 签收通知
     * @param string $merchant_trade_no
     * @param string $received_time
     * @return array
     * @throws HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public function notifyConfirmByTradeNo(string $merchant_trade_no, string $received_time)
    {
        $params = [
            'merchant_id' => $this->config['config']['mini_program']['merchant_id'],
            'merchant_trade_no' => $merchant_trade_no,
            'received_time' => $received_time
        ];
        return $this->notifyConfirm($params);
    }

    /**
     * 设置跳转连接
     * @param $path
     * @return array
     * @throws \EasyWeChat\Core\Exceptions\HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public function setMesJumpPathAndCheck($path)
    {
        if (!$this->checkManaged()) {
            throw new AdminException('开通小程序订单管理服务后重试');
        }
        return $this->setMesJumpPath($path);
    }

    /**
     * 设置小程序管理服务开通状态
     * @return bool
     * @throws HttpException
     *
     * @date 2023/05/09
     * @author yyw
     */
    public function setManaged()
    {
        $res = $this->isManaged();
        if ($res['is_trade_managed']) {
            $key = self::cache_prefix . '_is_trade_managed';
            CacheService::set($key, $res['is_trade_managed']);
            return true;
        } else {
            return false;
        }

    }

    /**
     * @return bool
     * @throws HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public function checkManaged()
    {
        $key = self::cache_prefix . '_is_trade_managed';
        if (CacheService::get($key)) {
            return true;
        } else {
            return $this->setManaged();
        }
    }

    /**
     * 同步去微信物流列表
     * @return array
     * @throws HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public function setDeliveryList()
    {
        $list = $this->getDeliveryList();
        if ($list) {
            $key = self::cache_prefix . '_delivery_list';
            $date = array_column($list['delivery_list'], 'delivery_id', 'delivery_name');
            // 创建缓存
            CacheService::set($key, json_encode($date));

            return $date;
        } else {
            throw new AdminException('物流公司列表异常');
        }
    }

    /**
     * 获取物流公司编码
     * @param $company_name
     * @return array|mixed
     * @throws HttpException
     *
     * @date 2023/05/10
     * @author yyw
     */
    public function getDelivery($company_name)
    {
        $key = self::cache_prefix . '_delivery_list';
        if (!CacheService::get($key)) {
            $date = $this->setDeliveryList();
            $express_company = $date[$company_name] ?? '';
        } else {
            $express_company = json_decode(CacheService::get($key), true)[$company_name] ?? '';
        }
        if (empty($express_company)) {
            $express_company = self::express_company;
        }

        return $express_company;
    }
}
