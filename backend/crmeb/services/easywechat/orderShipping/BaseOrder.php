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
use crmeb\services\easywechat\AbstractApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 小程序发货信息管理基础客户端(EasyWeChat 6.x)
 *
 * Class BaseOrder
 * @package crmeb\services\easywechat\orderShipping
 */
class BaseOrder extends AbstractApiClient
{
    /**
     * 小程序 appid
     */
    protected string $appId;

    /**
     * 商户号
     */
    protected string $merchantId;

    const ORDER = 'wxa/sec/order/';
    const EXPRESS = 'cgi-bin/express/delivery/open_msg/';

    const PATH = '/pages/goods/order_details/index';

    public function __construct(HttpClientInterface $api, string $appId = '', string $merchantId = '')
    {
        parent::__construct($api);
        $this->appId = $appId;
        $this->merchantId = $merchantId;
    }

    protected function resultHandle(array $result): array
    {
        if (empty($result)) {
            throw new AdminException('微信接口返回异常');
        }
        if (($result['errcode'] ?? -1) == 0) {
            return $result;
        } else {
            throw new AdminException("微信接口异常：code = {$result['errcode']} msg = {$result['errmsg']}");
        }
    }

    /**
     * 发货
     * @param array $params
     * @return array
     */
    public function shipping($params)
    {
        return $this->resultHandle($this->postJson(self::ORDER . 'upload_shipping_info', $params));
    }

    /**
     * 订单列表查询
     * @param $params
     * @return array
     */
    public function orderList($params)
    {
        return $this->resultHandle($this->postJson(self::ORDER . 'get_order_list', $params));
    }

    /**
     * 合单
     * @param $params
     * @return array
     */
    public function combinedShipping($params)
    {
        return $this->resultHandle($this->postJson(self::ORDER . 'upload_combined_shipping_info', $params));
    }


    /**
     * 签收消息提醒
     * @param $params
     * @return array
     */
    public function notifyConfirm($params)
    {
        return $this->resultHandle($this->postJson(self::ORDER . 'notify_confirm_receive', $params));
    }


    /**
     * 查询小程序是否已开通发货信息管理服务
     * @return array
     */
    public function isManaged()
    {
        return $this->resultHandle($this->postJson(self::ORDER . 'is_trade_managed', [
            'appid' => $this->appId,
        ]));
    }

    /**
     * 设置跳转连接
     * @param $path
     * @return array
     */
    public function setMesJumpPath($path)
    {
        return $this->resultHandle($this->postJson(self::ORDER . 'set_msg_jump_path', [
            'path' => $path,
        ]));
    }

    /**
     * 获取运力id列表
     * @return array
     */
    public function getDeliveryList()
    {
        return $this->resultHandle($this->postJson(self::EXPRESS . 'get_delivery_list', []));
    }
}
