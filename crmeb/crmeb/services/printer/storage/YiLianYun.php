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
namespace crmeb\services\printer\storage;

use crmeb\services\printer\BasePrinter;

/**
 * Class YiLianYun
 * @package crmeb\services\printer\storage
 */
class YiLianYun extends BasePrinter
{

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize(array $config)
    {

    }

    /**
     * 开始打印
     * @return bool|mixed|string
     * @throws \Exception
     */
    public function startPrinter()
    {
        if (!$this->printerContent) {
            return $this->setError('Missing print');
        }
        $request = $this->accessToken->postRequest('https://open-api.10ss.net/print/index', [
            'client_id' => $this->accessToken->clientId,
            'access_token' => $this->accessToken->getAccessToken(),
            'machine_code' => $this->accessToken->machineCode,
            'content' => $this->printerContent,
            'origin_id' => 'crmeb' . time(),
            'sign' => strtolower(md5($this->accessToken->clientId . time() . $this->accessToken->apiKey)),
            'id' => $this->accessToken->createUuid(),
            'timestamp' => time()
        ]);
        if ($request === false) {
            return $this->setError('request was aborted');
        }
        $request = is_string($request) ? json_decode($request, true) : $request;
        if (isset($request['error']) && in_array($request['error'], [18, 14])) {
            return $this->setError('Accesstoken has expired');
        }
        return $request;
    }

    /**
     * 设置打印内容
     * @param array $config
     * @return YiLianYun
     */
    public function setPrinterContent(array $config): self
    {
        $printTime = date('Y-m-d H:i:s', time());
        $goodsStr = '<table><tr><td>商品名称</td><td>数量</td><td>单价</td><td>金额</td></tr>';
        $product = $config['product'];
        foreach ($product as $item) {
            $goodsStr .= '<tr>';
            $price = bcmul((string)$item['cart_num'], (string)$item['truePrice'], 2);
            $goodsStr .= "<td>{$item['productInfo']['store_name']}</td><td>{$item['cart_num']}</td><td>{$item['truePrice']}</td><td>{$price}</td>";
            $goodsStr .= '</tr>';
            unset($price);
        }
        $goodsStr .= '</table>';
        $orderInfo = $config['orderInfo'];
        $orderTime = date('Y-m-d H:i:s',$orderInfo['pay_time']);
        $name = $config['name'];
        $this->printerContent = <<<CONTENT
<FB><center> ** {$name} **</center></FB>
<FH2><FW2>----------------</FW2></FH2>
订单编号：{$orderInfo['order_id']}\r
打印时间: {$printTime} \r
付款时间: {$orderTime}\r
姓    名: {$orderInfo['real_name']}\r
电    话: {$orderInfo['user_phone']}\r
地    址: {$orderInfo['user_address']}\r
赠送积分: {$orderInfo['gain_integral']}\r
订单备注：{$orderInfo['mark']}\r
*************商品***************\r
{$goodsStr}
********************************\r
<FH>
<LR>合计：￥{$orderInfo['total_price']},优惠: ￥{$orderInfo['coupon_price']}</LR>
<LR>邮费：￥{$orderInfo['pay_postage']},抵扣：￥{$orderInfo['deduction_price']}</LR>
<right>实际支付：￥{$orderInfo['pay_price']}</right>           
</FH>
<FS><center> ** 完 **</center></FS>
CONTENT;
        return $this;
    }
}
