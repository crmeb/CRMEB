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
declare(strict_types=1);

namespace crmeb\services\easywechat\orderShipping;

use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OrderClient extends BaseOrder
{
    const cache_prefix = 'mini_order';

    const express_company = 'ZTO';   // 默认发货快递公司为（中通快递）

    /**
     * 处理联系人(电话脱敏)
     * @param array $contact
     * @return array
     */
    protected function handleContact(array $contact = []): array
    {
        if (isset($contact)) {
            if (isset($contact['consignor_contact']) && $contact['consignor_contact']) {
                $contact['consignor_contact'] = self::encryptTel($contact['consignor_contact']);
            }
            if (isset($contact['receiver_contact']) && $contact['receiver_contact']) {
                $contact['receiver_contact'] = self::encryptTel($contact['receiver_contact']);
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
     */
    public function shippingByTradeNo(string $out_trade_no, int $logistics_type, array $shipping_list, string $payer_openid, $path, int $delivery_mode = 1, bool $is_all_delivered = true)
    {
        if (!$this->checkManaged()) {
            throw new AdminException('开通小程序订单管理服务后重试');
        }
        $params = [
            'order_key' => [
                'order_number_type' => 1,
                'mchid' => $this->merchantId,
                'out_trade_no' => $out_trade_no,
            ],
            'logistics_type' => $logistics_type,
            'delivery_mode' => $delivery_mode,
            'upload_time' => date(DATE_RFC3339),
            'payer' => [
                'openid' => $payer_openid,
            ],
        ];
        if ($delivery_mode == 2) {
            $params['is_all_delivered'] = $is_all_delivered;
        }

        if ($logistics_type == 1) {
            foreach ($shipping_list as $shipping) {
                $contact = $this->handleContact($shipping['contact'] ?? []);
                $params['shipping_list'][] = [
                    'tracking_no' => $shipping['tracking_no'] ?? '',
                    'express_company' => $shipping['express_company'],
                    'item_desc' => $shipping['item_desc'],
                    'contact' => $contact,
                ];
            }
        } else {
            $params['shipping_list'] = $shipping_list;
        }

        // 跳转路径
        $this->setMesJumpPath($path);
        return $this->shipping($params);
    }

    /**
     * 订单列表查询
     * @param $params
     * @return array
     */
    public function shippingOrderList($params)
    {
        if (!$this->checkManaged()) {
            throw new AdminException('开通小程序订单管理服务后重试');
        }
        return $this->orderList($params);
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
     */
    public function combinedShippingByTradeNo(string $out_trade_no, int $logistics_type, array $sub_orders, string $payer_openid, int $delivery_mode = 2, bool $is_all_delivered = false)
    {
        if (!$this->checkManaged()) {
            throw new AdminException('开通小程序订单管理服务后重试');
        }
        $params = [
            'order_key' => [
                'order_number_type' => 1,
                'mchid' => $this->merchantId,
                'out_trade_no' => $out_trade_no,
            ],
            'upload_time' => date(DATE_RFC3339),
            'payer' => [
                'openid' => $payer_openid,
            ],
        ];

        foreach ($sub_orders as $order) {
            $sub_order = [
                'order_key' => [
                    'order_number_type' => 1,
                    'mchid' => $this->merchantId,
                    'out_trade_no' => $order['out_trade_no'],
                    'logistics_type' => $logistics_type,
                ],
                'delivery_mode' => $delivery_mode,
                'is_all_delivered' => $is_all_delivered,
            ];
            foreach ($sub_orders['shipping_list'] as $shipping) {
                $contact = $this->handleContact($shipping['contact'] ?? []);
                $sub_order['shipping_list'][] = [
                    'tracking_no' => $shipping['tracking_no'] ?? '',
                    'express_company' => isset($shipping['express_company']) ? $this->getDelivery($shipping['express_company']) : '',
                    'item_desc' => $shipping['item_desc'],
                    'contact' => $contact,
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
     */
    public function notifyConfirmByTradeNo(string $merchant_trade_no, string $received_time)
    {
        $params = [
            'merchant_id' => $this->merchantId,
            'merchant_trade_no' => $merchant_trade_no,
            'received_time' => $received_time,
        ];
        return $this->notifyConfirm($params);
    }

    /**
     * 设置跳转连接
     * @param $path
     * @return array
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
     */
    public function setManaged()
    {
        $res = $this->isManaged();
        if ($res['is_trade_managed']) {
            $key = self::cache_prefix . '_is_trade_managed_' . $this->appId;
            CacheService::set($key, $res['is_trade_managed']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function checkManaged()
    {
        $key = self::cache_prefix . '_is_trade_managed_' . $this->appId;
        if (CacheService::get($key)) {
            return true;
        } else {
            return $this->setManaged();
        }
    }

    /**
     * 同步去微信物流列表
     * @return array
     */
    public function setDeliveryList()
    {
        $list = $this->getDeliveryList();
        if ($list) {
            $key = self::cache_prefix . '_delivery_list';
            $data = array_column($list['delivery_list'], 'delivery_id', 'delivery_name');
            // 创建缓存
            CacheService::set($key, json_encode($data));

            return $data;
        } else {
            throw new AdminException('物流公司列表异常');
        }
    }

    /**
     * 获取物流公司编码
     * @param $company_name
     * @return array|mixed|string
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

    /**
     * 手机号脱敏
     * @param string $tel
     * @return string
     */
    public static function encryptTel(string $tel): string
    {
        return substr_replace($tel, '****', 3, 4);
    }
}
