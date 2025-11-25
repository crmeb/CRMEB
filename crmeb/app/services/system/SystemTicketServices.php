<?php

namespace app\services\system;

use app\dao\system\SystemTicketDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\FormBuilder as Form;
use crmeb\services\printer\Printer;

class SystemTicketServices extends BaseServices
{
    public function __construct(SystemTicketDao $dao)
    {
        $this->dao = $dao;
    }

    public function ticketList($where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->ticketList($where, $page, $limit);
        foreach ($list as &$item) {
            $item['add_time'] = date('Y-m-d H:i:s', $item['add_time']);
        }
        $count = $this->dao->ticketCount($where);
        return compact('list', 'count');
    }

    public function ticketForm($id)
    {
        $info = $this->dao->get($id) ?? [];
        if ($info) $info = $info->toArray();
        $field[] = Form::input('print_name', '打印机名称', $info['print_name'] ?? '')->required('请输入打印机名称')->placeholder('打印机名称');
        $field[] = Form::radio('type', '平台选择', $info['type'] ?? 1)
            ->options([['label' => '易联云', 'value' => 1], ['label' => '飞鹅云', 'value' => 2]])
            ->appendControl(1, [
                    Form::input('yly_user_id', '用户ID：', $info['yly_user_id'] ?? '')->required('请输入用户ID')->placeholder('易联云开发者ID'),
                    Form::input('yly_app_id', '应用ID：', $info['yly_app_id'] ?? '')->required('请输入应用ID')->placeholder('易联应用ID'),
                    Form::input('yly_app_secret', '应用密钥：', $info['yly_app_secret'] ?? '')->required('请输入应用密钥')->placeholder('易联应用密钥'),
                    Form::input('yly_sn', '终端号：', $info['yly_sn'] ?? '')->required('请输入终端号')->placeholder('易联云打印机终端号，打印机型号：易联云打印机 K4无线版'),
                ]
            )->appendControl(2, [
                    Form::input('fey_user', '飞鹅云USER：', $info['fey_user'] ?? '')->required('请输入飞鹅云USER')->placeholder('飞鹅云后台注册账号'),
                    Form::input('fey_ukey', '飞鹅云UYEK：', $info['fey_ukey'] ?? '')->required('请输入飞鹅云UYEK')->placeholder('飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】'),
                    Form::input('fey_sn', '飞鹅云SN：', $info['fey_sn'] ?? '')->required('请输入飞鹅云SN')->placeholder('打印机标签上的编号，必须要在管理后台里添加打印机或调用API接口'),
                ]
            );
        $field[] = Form::number('times', '打印联数', $info['times'] ?? 1)->min(1)->required('请输入打印联数')->placeholder('打印机单次打印张数');
        $field[] = Form::radio('print_type', '打印时机', $info['print_type'] ?? 1)->options([['label' => '支付后打印', 'value' => 1], ['label' => '下单后打印', 'value' => 2]]);
        $field[] = Form::radio('status', '打印开关', $info['status'] ?? 1)->options([['label' => '开启', 'value' => 1], ['label' => '关闭', 'value' => 0]]);
        return create_form('小票打印', $field, $this->url('/system/ticket/save/' . $id), 'POST');
    }

    public function ticketSave($id, $data)
    {
        if ($id) {
            $this->dao->update($id, $data);
        } else {
            $data['add_time'] = time();
            $this->dao->save($data);
        }
        return true;
    }

    public function ticketSetStatus($id, $status)
    {
        $this->dao->update($id, ['status' => $status]);
        return true;
    }

    public function ticketDel($id)
    {
        $this->dao->update($id, ['is_del' => 1]);
        return true;
    }

    public function ticketContent($id)
    {
        $print_content = $this->dao->value(['id' => $id], 'print_content');
        return $print_content ? json_decode($print_content, true) : [];
    }

    public function ticketContentSave($id, $data)
    {
        $print_content = json_encode($data);
        $this->dao->update($id, ['print_content' => $print_content]);
        return true;
    }

    public function startPrint($order, $product, $print_type = 1)
    {
        $list = $this->dao->ticketList(['status' => 1, 'print_type' => $print_type]);
        foreach ($list as $item) {
            if ($item['type'] == 1) { //易联云
                $name = 'yi_lian_yun';
                $configData = [
                    'partner' => $item['yly_user_id'],
                    'clientId' => $item['yly_app_id'],
                    'apiKey' => $item['yly_app_secret'],
                    'terminal' => $item['yly_sn']
                ];
                $print_content = json_decode($item['print_content'], true);
                if (is_null($print_content) || !count($print_content)) throw new AdminException('请先配置打印内容');
                $content = $this->ylyContent($print_content, $order, $product, $item['times'], $print_type);
            } else { //飞鹅云
                $name = 'fei_e_yun';
                $configData = [
                    'feyUser' => $item['fey_user'],
                    'feyUkey' => $item['fey_ukey'],
                    'feySn' => $item['fey_sn']
                ];
                $print_content = json_decode($item['print_content'], true);
                if (is_null($print_content) || !count($print_content)) throw new AdminException('请先配置打印内容');
                $content = $this->feyContent($print_content, $order, $product, $print_type);
            }
            $printer = new Printer($name, $configData);
            $printer->setPrinterContent($content, $item['times'])->startPrinter();
        }
    }

    public function ylyContent($printContent, $orderInfo, $product, $times, $print_type)
    {
        $goodsStr = '<table><tr><td>名称</td><td>单价</td><td>数量</td><td>金额</td></tr>';
        foreach ($product as $item) {
            $goodsStr .= '<tr><td><FH2><FW2>----------------</FW2></FH2></td></tr>';
            $goodsStr .= '<tr>';
            $price = $item['sum_price'];
            $num = $item['cart_num'];
            $prices = bcmul((string)$item['cart_num'], (string)$item['sum_price'], 2);
            $goodsStr .= "<td>{$item['productInfo']['store_name']} | {$item['productInfo']['attrInfo']['suk']}</td><td>{$price}</td><td>{$num}</td><td>{$prices}</td>";
            $goodsStr .= '</tr>';
            if (in_array(1, $printContent['goods'])) {
                $goodsStr .= '<tr>';
                $goodsStr .= "<td>规格编码：{$item['productInfo']['attrInfo']['bar_code']}</td>";
                $goodsStr .= '</tr>';
            }
            unset($price, $num, $prices);
        }
        $goodsStr .= '</table>';
        $total_price = bcadd($orderInfo['total_price'], $orderInfo['pay_postage'], 2);
        $addTime = date('Y-m-d H:i:s', $orderInfo['add_time']);
        $payTime = isset($orderInfo['pay_time']) ? date('Y-m-d H:i:s', $orderInfo['pay_time']) : '';
        $printTime = date('Y-m-d H:i:s', time());

        $content = '';
        $content .= '<MN>' . $times . '</MN>';
        if ($printContent['header']) {
            $content .= '<FS2><center>' . sys_config('site_name') . '</center></FS2>';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if ($printContent['delivery']) {
            if ($orderInfo['shipping_type'] == 1) {
                $content .= '配送方式：商家配送 \r';
            } else {
                $content .= '配送方式：门店自提 \r';
            }
            $content .= '客户姓名: ' . $orderInfo['real_name'] . ' \r';
            $content .= '客户电话: ' . $orderInfo['user_phone'] . ' \r';
            if ($orderInfo['shipping_type'] == 1) $content .= '收货地址: ' . $orderInfo['user_address'] . ' \r';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if ($printContent['buyer_remarks']) {
            $content .= '买家备注: ' . $orderInfo['mark'] . ' \r';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if (in_array(0, $printContent['goods'])) {
            $content .= '*************商品***************';
            $content .= '      \r';
            $content .= $goodsStr;
            $content .= '********************************\r';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
            $content .= '<RA>合计：' . $total_price . '元</RA>';
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if ($printContent['preferential'] || $printContent['freight']) {
            if ($printContent['freight']) {
                $content .= '<RA>邮费：' . $orderInfo['pay_postage'] . '元</RA>';
            }
            if ($printContent['preferential']) {
                $discount_price = bcsub(bcadd($orderInfo['total_price'], $orderInfo['pay_postage'], 2), bcadd($orderInfo['deduction_price'], $orderInfo['pay_price'], 2), 2);
                $content .= '<RA>优惠：-' . $discount_price . '元</RA>';
                $content .= '<RA>抵扣：-' . $orderInfo['deduction_price'] . '元</RA>';
            }
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if (in_array(0, $printContent['pay'])) {
            if ($print_type == 1) {
                switch ($orderInfo['pay_type']) {
                    case 'weixin':
                        $content .= '<RA>支付方式：微信支付</RA>';
                        break;
                    case 'alipay':
                        $content .= '<RA>支付方式：支付宝支付</RA>';
                        break;
                    case 'yue':
                        $content .= '<RA>支付方式：余额支付</RA>';
                        break;
                    case 'offline':
                        $content .= '<RA>支付方式：线下支付</RA>';
                        break;
                    default:
                        $content .= '<RA>支付方式：暂无</RA>';
                        break;
                }
            } else {
                $content .= '<RA>支付方式：暂无</RA>';
            }
        }
        if (in_array(1, $printContent['pay'])) {
            $content .= '<RA>实际支付：' . $orderInfo['pay_price'] . '元</RA>';
        }
        if (count($printContent['pay'])) {
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if (in_array(0, $printContent['order'])) {
            $content .= '订单编号：' . $orderInfo['order_id'] . '\r';
        }
        if (in_array(1, $printContent['order'])) {
            $content .= '下单时间：' . $addTime . '\r';
        }
        if (in_array(2, $printContent['order'])) {
            $content .= '支付时间：' . $payTime . '\r';
        }
        if (in_array(3, $printContent['order'])) {
            $content .= '打印时间：' . $printTime . '\r';
        }
        if (count($printContent['order'])) {
            $content .= '<FH2><FW2>----------------</FW2></FH2>';
        }
        if ($printContent['code'] && $printContent['code_url']) {
            $content .= '<QR>' . sys_config('site_url') . $printContent['code_url'] . '</QR>';
            $content .= '      \r';
        }
        if ($printContent['show_notice']) {
            $content .= '<center>' . $printContent['notice_content'] . '</center>';
            $content .= '      \r';
        }
        return $content;
    }

    public function feyContent($printContent, $orderInfo, $product, $print_type)
    {
        $printTime = date('Y-m-d H:i:s', time());
        $addTime = date('Y-m-d H:i:s', $orderInfo['add_time']);
        $payTime = isset($orderInfo['pay_time']) ? date('Y-m-d H:i:s', $orderInfo['pay_time']) : '';
        $content = '';
        if ($printContent['header']) {
            $content .= '<CB>' . sys_config('site_name') . '</CB><BR>';
            $content .= '--------------------------------<BR>';
        }
        if ($printContent['delivery']) {
            if ($orderInfo['shipping_type'] == 1) {
                $content .= '配送方式：商家配送<BR>';
            } else {
                $content .= '配送方式：门店自提<BR>';
            }
            $content .= '客户姓名: ' . $orderInfo['real_name'] . '<BR>';
            $content .= '客户电话: ' . $orderInfo['user_phone'] . '<BR>';
            if ($orderInfo['shipping_type'] == 1) $content .= '收货地址：' . $orderInfo['user_address'] . '<BR>';
            $content .= '--------------------------------<BR>';
        }
        if ($printContent['buyer_remarks']) {
            $content .= '买家备注：' . $orderInfo['mark'] . '<BR>';
            $content .= '--------------------------------<BR>';
        }
        if (in_array(0, $printContent['goods'])) {
            $content .= '<BR>';
            $content .= '**************商品**************<BR>';
            $content .= '<BR>';
            $content .= '名称           单价  数量 金额<BR>';
            foreach ($product as $item) {
                $content .= '--------------------------------<BR>';
                $name = $item['productInfo']['store_name'] . " | " . $item['productInfo']['attrInfo']['suk'];
                $price = $item['sum_price'];
                $num = $item['cart_num'];
                $prices = bcmul((string)$item['cart_num'], (string)$item['sum_price'], 2);
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
                $content .= $head . $tail;
                if (in_array(1, $printContent['goods'])) {
                    $content .= '规格编码：' . $item['productInfo']['attrInfo']['bar_code'] . '<BR>';
                }
                unset($price);
            }
            $content .= '<BR>';
            $content .= '********************************<BR>';
            $content .= '<BR>';
            $content .= '--------------------------------<BR>';
            $total_price = bcadd($orderInfo['total_price'], $orderInfo['pay_postage'], 2);
            $content .= '<RIGHT>合计：' . number_format($total_price, 2) . '元</RIGHT>';
            $content .= '--------------------------------<BR>';
        }
        if ($printContent['preferential'] || $printContent['freight']) {
            if ($printContent['freight']) {
                $content .= '<RIGHT>邮费：' . number_format($orderInfo['pay_postage'], 2) . '元</RIGHT><BR>';
            }
            if ($printContent['preferential']) {
                $discount_price = bcsub(bcadd($orderInfo['total_price'], $orderInfo['pay_postage'], 2), bcadd($orderInfo['deduction_price'], $orderInfo['pay_price'], 2), 2);
                $content .= '<RIGHT>优惠：-' . number_format($discount_price, 2) . '元</RIGHT><BR>';
                $content .= '<RIGHT>抵扣：-' . number_format($orderInfo['deduction_price'], 2) . '元</RIGHT>';
            }
            $content .= '--------------------------------<BR>';
        }
        if (in_array(0, $printContent['pay'])) {
            if ($print_type == 1) {
                switch ($orderInfo['pay_type']) {
                    case 'weixin':
                        $content .= '<RIGHT>支付方式：微信支付</RIGHT><BR>';
                        break;
                    case 'alipay':
                        $content .= '<RIGHT>支付方式：支付宝支付</RIGHT><BR>';
                        break;
                    case 'yue':
                        $content .= '<RIGHT>支付方式：余额支付</RIGHT><BR>';
                        break;
                    case 'offline':
                        $content .= '<RIGHT>支付方式：线下支付</RIGHT><BR>';
                        break;
                    default:
                        $content .= '<RIGHT>支付方式：暂无</RIGHT><BR>';
                        break;
                }
            } else {
                $content .= '<RIGHT>支付方式：暂无</RIGHT><BR>';
            }
        }
        if (in_array(1, $printContent['pay'])) {
            $content .= '<RIGHT>实际支付：' . number_format($orderInfo['pay_price'], 2) . '元</RIGHT>';
        }
        if (count($printContent['pay'])) {
            $content .= '--------------------------------<BR>';
        }
        if (in_array(0, $printContent['order'])) {
            $content .= '订单编号：' . $orderInfo['order_id'] . '<BR>';
        }
        if (in_array(1, $printContent['order'])) {
            $content .= '下单时间: ' . $addTime . '<BR>';
        }
        if (in_array(2, $printContent['order'])) {
            $content .= '付款时间: ' . $payTime . '<BR>';
        }
        if (in_array(3, $printContent['order'])) {
            $content .= '打印时间: ' . $printTime . '<BR>';
        }
        if (count($printContent['order'])) {
            $content .= '--------------------------------<BR>';
            $content .= '<BR>';
        }
        if ($printContent['code'] && $printContent['code_url']) {
            $content .= '<QR>' . sys_config('site_url') . $printContent['code_url'] . '</QR>';
        }
        if ($printContent['show_notice']) {
            $content .= '<C>' . $printContent['notice_content'] . '</C>';
        }
        return $content;
    }
}
