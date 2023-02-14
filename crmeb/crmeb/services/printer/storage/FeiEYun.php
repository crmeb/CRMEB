<?php

namespace crmeb\services\printer\storage;

use crmeb\services\printer\BasePrinter;

class FeiEYun extends BasePrinter
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
        $time = time();
        $request = $this->accessToken->postRequest('http://api.feieyun.cn/Api/Open/', [
            'user' => $this->accessToken->feyUser,
            'stime' => $time,
            'sig' => sha1($this->accessToken->feyUser . $this->accessToken->feyUkey . $time),
            'apiname' => 'Open_printMsg',
            'sn' => $this->accessToken->feySn,
            'content' => $this->printerContent,
            'times' => 1
        ]);
        $res = json_decode($request, true);
        if ($res['msg'] == 'ok') {
            return $res;
        } else {
            return $this->setError($res['msg']);
        }
    }

    /**
     * 设置打印内容
     * @param array $config
     * @return YiLianYun
     */
    public function setPrinterContent(array $config): self
    {
        $printTime = date('Y-m-d H:i:s', time());
        $product = $config['product'];
        $orderInfo = $config['orderInfo'];
        $orderTime = date('Y-m-d H:i:s', $orderInfo['pay_time']);
        $this->printerContent = '<CB>**' . $config['name'] . '**</CB><BR>';
        $this->printerContent .= '--------------------------------<BR>';
        $this->printerContent .= '订单编号：' . $orderInfo['order_id'] . '<BR>';
        $this->printerContent .= '打印时间: ' . $printTime . '<BR>';
        $this->printerContent .= '付款时间: ' . $orderTime . '<BR>';
        $this->printerContent .= '姓   名: ' . $orderInfo['real_name'] . '<BR>';
        $this->printerContent .= '电   话: ' . $orderInfo['user_phone'] . '<BR>';
        $this->printerContent .= '地   址: ' . $orderInfo['user_address'] . '<BR>';
        $this->printerContent .= '赠送积分: ' . $orderInfo['gain_integral'] . '<BR>';
        $this->printerContent .= '订单备注：' . $orderInfo['mark'] . '<BR>';
        $this->printerContent .= '**************商品**************<BR>';
        $this->printerContent .= '名称           单价  数量 金额<BR>';
        $this->printerContent .= '--------------------------------<BR>';
        foreach ($product as $item) {
            $name = $item['productInfo']['store_name'];
            $price = $item['truePrice'];
            $num = $item['cart_num'];
            $prices = bcmul((string)$item['cart_num'], (string)$item['truePrice'], 2);
            $kw3 = '';
            $kw1 = '';
            $kw2 = '';
            $kw4 = '';
            $str = $name;
            $blankNum = 14;//名称控制为14个字节
            $lan = mb_strlen($str, 'utf-8');
            $m = 0;
            $j = 1;
            $blankNum++;
            $result = array();
            if (strlen($price) < 6) {
                $k1 = 6 - strlen($price);
                for ($q = 0; $q < $k1; $q++) {
                    $kw1 .= ' ';
                }
                $price = $price . $kw1;
            }
            if (strlen($num) < 3) {
                $k2 = 3 - strlen($num);
                for ($q = 0; $q < $k2; $q++) {
                    $kw2 .= ' ';
                }
                $num = $num . $kw2;
            }
            if (strlen($prices) < 6) {
                $k3 = 6 - strlen($prices);
                for ($q = 0; $q < $k3; $q++) {
                    $kw4 .= ' ';
                }
                $prices = $prices . $kw4;
            }
            for ($i = 0; $i < $lan; $i++) {
                $new = mb_substr($str, $m, $j, 'utf-8');
                $j++;
                if (mb_strwidth($new, 'utf-8') < $blankNum) {
                    if ($m + $j > $lan) {
                        $m = $m + $j;
                        $tail = $new;
                        $lenght = iconv("UTF-8", "GBK//IGNORE", $new);
                        $k = 14 - strlen($lenght);
                        for ($q = 0; $q < $k; $q++) {
                            $kw3 .= ' ';
                        }
                        if ($m == $j) {
                            $tail .= $kw3 . ' ' . $price . ' ' . $num . ' ' . $prices;
                        } else {
                            $tail .= $kw3 . '<BR>';
                        }
                        break;
                    } else {
                        $next_new = mb_substr($str, $m, $j, 'utf-8');
                        if (mb_strwidth($next_new, 'utf-8') < $blankNum) {
                            continue;
                        } else {
                            $m = $i + 1;
                            $result[] = $new;
                            $j = 1;
                        }
                    }
                }
            }
            $head = '';
            foreach ($result as $key => $value) {
                if ($key < 1) {
                    $v_lenght = iconv("UTF-8", "GBK//IGNORE", $value);
                    $v_lenght = strlen($v_lenght);
                    if ($v_lenght == 13) $value = $value . " ";
                    $head .= $value . ' ' . $price . ' ' . $num . ' ' . $prices;
                } else {
                    $head .= $value . '<BR>';
                }
            }
            $this->printerContent .= $head . $tail;
            unset($price);
        }
        $this->printerContent .= '--------------------------------<BR>';
        $this->printerContent .= '合计：' . number_format($orderInfo['total_price'], 1) . '元<BR>';
        $this->printerContent .= '邮费：' . number_format($orderInfo['pay_postage'], 1) . '元<BR>';
        $this->printerContent .= '优惠：' . number_format($orderInfo['coupon_price'], 1) . '元<BR>';
        $this->printerContent .= '抵扣：' . number_format($orderInfo['deduction_price'], 1) . '元<BR>';
        $this->printerContent .= '实际支付：' . number_format($orderInfo['pay_price'], 1) . '元<BR>';
        $this->printerContent .= '<QR>' . $config['url'] . '</QR>';//把解析后的二维码生成的字符串用标签套上即可自动生成二维码
        return $this;
    }
}
