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

namespace app\services\other\export;


use app\services\BaseServices;
use crmeb\services\SpreadsheetExcelService;

class ExportServices extends BaseServices
{
    /**
     * 真实请求导出
     * @param $header excel表头
     * @param $title 标题
     * @param array $export 填充数据
     * @param string $filename 保存文件名称
     * @param string $suffix 保存文件后缀
     * @param bool $is_save true|false 是否保存到本地
     * @return mixed
     */
    public function export($header, $title_arr, $export = [], $filename = '', $suffix = 'xlsx', $is_save = false)
    {
        $title = isset($title_arr[0]) && !empty($title_arr[0]) ? $title_arr[0] : '导出数据';
        $name = isset($title_arr[1]) && !empty($title_arr[1]) ? $title_arr[1] : '导出数据';
        $info = isset($title_arr[2]) && !empty($title_arr[2]) ? $title_arr[2] : date('Y-m-d H:i:s', time());

        $path = SpreadsheetExcelService::instance()->setExcelHeader($header)
            ->setExcelTile($title, $name, $info)
            ->setExcelContent($export)
            ->excelSave($filename, $suffix, $is_save);
        $path = $this->siteUrl() . $path;
        return [$path];
    }

    /**
     * 获取系统接口域名
     * @return string
     */
    public function siteUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol . $domainName;
    }

    /**
     * 用户资金导出
     * @param $data 导出数据
     */
    public function userFinance($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                $export[] = [
                    $value['uid'],
                    $value['nickname'],
                    $value['pm'] == 0 ? '-' . $value['number'] : $value['number'],
                    $value['title'],
                    $value['mark'],
                    $value['add_time'],
                ];
            }
        }
        $header = ['会员ID', '昵称', '金额/积分', '类型', '备注', '创建时间'];
        $title = ['资金监控', '资金监控', date('Y-m-d H:i:s', time())];
        $filename = '资金监控_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 用户佣金导出
     * @param $data 导出数据
     */
    public function userCommission($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as &$value) {
                $export[] = [
                    $value['nickname'],
                    $value['sum_number'],
                    $value['now_money'],
                    $value['brokerage_price'],
                    $value['extract_price'],
                ];
            }
        }
        $header = ['昵称/姓名', '总佣金金额', '账户余额', '账户佣金', '提现到账佣金'];
        $title = ['拥金记录', '拥金记录' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '拥金记录_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 用户积分导出
     * @param $data 导出数据
     */
    public function userPoint($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $key => $item) {
                $export[] = [
                    $item['id'],
                    $item['title'],
                    $item['balance'],
                    $item['number'],
                    $item['mark'],
                    $item['nickname'],
                    $item['add_time'],
                ];
            }
        }
        $header = ['编号', '标题', '变动前积分', '积分变动', '备注', '用户微信昵称', '添加时间'];
        $title = ['积分日志', '积分日志' . time(), '生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '积分日志_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 用户充值导出
     * @param $data 导出数据
     */
    public function userRecharge($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                switch ($item['recharge_type']) {
                    case 'routine':
                        $item['_recharge_type'] = '小程序充值';
                        break;
                    case 'weixin':
                        $item['_recharge_type'] = '公众号充值';
                        break;
                    default:
                        $item['_recharge_type'] = '其他充值';
                        break;
                }
                $item['_pay_time'] = $item['pay_time'] ? date('Y-m-d H:i:s', $item['pay_time']) : '暂无';
                $item['_add_time'] = $item['add_time'] ? date('Y-m-d H:i:s', $item['add_time']) : '暂无';
                $item['paid_type'] = $item['paid'] ? '已支付' : '未支付';

                $export[] = [
                    $item['nickname'],
                    $item['price'],
                    $item['paid_type'],
                    $item['_recharge_type'],
                    $item['_pay_time'],
                    $item['paid'] == 1 && $item['refund_price'] == $item['price'] ? '已退款' : '未退款',
                    $item['_add_time']
                ];
            }
        }
        $header = ['昵称/姓名', '充值金额', '是否支付', '充值类型', '支付时间', '是否退款', '添加时间'];
        $title = ['充值记录', '充值记录' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '充值记录_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 用户推广导出
     * @param $data 导出数据
     */
    public function userAgent($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $index => $item) {
                $export[] = [
                    $item['uid'],
                    $item['nickname'],
                    $item['phone'],
                    $item['spread_count'],
                    $item['order_count'],
                    $item['order_price'],
                    $item['brokerage_money'],
                    $item['extract_count_price'],
                    $item['extract_count_num'],
                    $item['brokerage_price'],
                    $item['spread_name'],
                ];
            }
        }
        $header = ['用户编号', '昵称', '电话号码', '推广用户数量', '订单数量', '推广订单金额', '佣金金额', '已提现金额', '提现次数', '未提现金额', '上级推广人'];
        $title = ['推广用户', '推广用户导出' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '推广用户_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 微信用户导出
     * @param $data 导出数据
     */
    public function wechatUser($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $index => $item) {
                $export[] = [
                    $item['nickname'],
                    $item['sex'],
                    $item['country'] . $item['province'] . $item['city'],
                    $item['subscribe'] == 1 ? '关注' : '未关注',
                ];
            }
        }
        $header = ['名称', '性别', '地区', '是否关注公众号'];
        $title = ['微信用户导出', '微信用户导出' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '微信用户导出_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 订单资金导出
     * @param $data 导出数据
     */
    public function orderFinance($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $info) {
                $time = $info['pay_time'];
                $price = $info['total_price'] + $info['pay_postage'];
                $zhichu = $info['coupon_price'] + $info['deduction_price'] + $info['cost'];
                $profit = ($info['total_price'] + $info['pay_postage']) - ($info['coupon_price'] + $info['deduction_price'] + $info['cost']);
                $deduction = $info['deduction_price'];//积分抵扣
                $coupon = $info['coupon_price'];//优惠
                $cost = $info['cost'];//成本
                $export[] = [$time, $price, $zhichu, $cost, $coupon, $deduction, $profit];
            }
        }
        $header = ['时间', '营业额(元)', '支出(元)', '成本', '优惠', '积分抵扣', '盈利(元)'];
        $title = ['财务统计', '财务统计', date('Y-m-d H:i:s', time())];
        $filename = '财务统计_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 商铺砍价活动导出
     * @param $data 导出数据
     */
    public function storeBargain($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $index => $item) {
                $export[] = [
                    $item['title'],
                    $item['info'],
                    '￥' . $item['price'],
                    $item['bargain_num'],
                    $item['status'] ? '开启' : '关闭',
                    empty($item['start_time']) ? '' : date('Y-m-d H:i:s', (int)$item['start_time']),
                    empty($item['stop_time']) ? '' : date('Y-m-d H:i:s', (int)$item['stop_time']),
                    $item['sales'],
                    $item['quota'],
                    empty($item['add_time']) ? '' : $item['add_time'],
                ];
            }
        }
        $header = ['砍价活动名称', '砍价活动简介', '砍价金额', '用户每次砍价的次数', '砍价状态', '砍价开启时间', '砍价结束时间', '销量', '限量', '添加时间'];
        $title = ['砍价商品导出', '商品信息' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '砍价商品导出_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 商铺拼团导出
     * @param $data 导出数据
     */
    public function storeCombination($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                $export[] = [
                    $item['id'],
                    $item['title'],
                    $item['ot_price'],
                    $item['price'],
                    $item['quota'],
                    $item['count_people'],
                    $item['count_people_all'],
                    $item['count_people_pink'],
                    $item['sales'] ?? 0,
                    $item['is_show'] ? '开启' : '关闭',
                    empty($item['stop_time']) ? '' : date('Y/m/d H:i:s', (int)$item['stop_time'])
                ];
            }
        }
        $header = ['编号', '拼团名称', '原价', '拼团价', '限量', '拼团人数', '参与人数', '成团数量', '销量', '商品状态', '结束时间'];
        $title = ['拼团商品导出', '商品信息' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '拼团商品导出_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 商铺秒杀活动导出
     * @param $data 导出数据
     */
    public function storeSeckill($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                if ($item['status']) {
                    if ($item['start_time'] > time())
                        $item['start_name'] = '活动未开始';
                    else if ($item['stop_time'] < time())
                        $item['start_name'] = '活动已结束';
                    else if ($item['stop_time'] > time() && $item['start_time'] < time())
                        $item['start_name'] = '正在进行中';
                } else {
                    $item['start_name'] = '活动已结束';
                }
                $export[] = [
                    $item['id'],
                    $item['title'],
                    $item['info'],
                    $item['ot_price'],
                    $item['price'],
                    $item['quota'],
                    $item['sales'],
                    $item['start_name'],
                    $item['stop_time'] ? date('Y-m-d H:i:s', $item['stop_time']) : '/',
                    $item['status'] ? '开启' : '关闭',
                ];
            }
        }
        $header = ['编号', '活动标题', '活动简介', '原价', '秒杀价', '限量', '销量', '秒杀状态', '结束时间', '状态'];
        $title = ['秒杀商品导出', ' ', ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '秒杀商品导出_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 商铺商品导出
     * @param $data 导出数据
     */
    public function storeProduct($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $index => $item) {
                $export[] = [
                    $item['store_name'],
                    $item['store_info'],
                    $item['cate_name'],
                    '￥' . $item['price'],
                    $item['stock'],
                    $item['sales'],
                    $item['visitor'],
                ];
            }
        }
        $header = ['商品名称', '商品简介', '商品分类', '价格', '库存', '销量', '浏览量'];
        $title = ['商品导出', '商品信息' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '商品导出_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 商铺订单导出
     * @param $data 导出数据
     */
    public function storeOrder($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                if ($item['paid'] == 1) {
                    switch ($item['pay_type']) {
                        case 'weixin':
                            $item['pay_type_name'] = '微信支付';
                            break;
                        case 'yue':
                            $item['pay_type_name'] = '余额支付';
                            break;
                        case 'offline':
                            $item['pay_type_name'] = '线下支付';
                            break;
                        default:
                            $item['pay_type_name'] = '其他支付';
                            break;
                    }
                } else {
                    switch ($item['pay_type']) {
                        default:
                            $item['pay_type_name'] = '未支付';
                            break;
                        case 'offline':
                            $item['pay_type_name'] = '线下支付';
                            $item['pay_type_info'] = 1;
                            break;
                    }
                }
                if ($item['paid'] == 0 && $item['status'] == 0) {
                    $item['status_name'] = '未支付';
                } else if ($item['paid'] == 1 && $item['status'] == 0 && $item['shipping_type'] == 1 && $item['refund_status'] == 0) {
                    $item['status_name'] = '未发货';
                } else if ($item['paid'] == 1 && $item['status'] == 0 && $item['shipping_type'] == 2 && $item['refund_status'] == 0) {
                    $item['status_name'] = '未核销';
                } else if ($item['paid'] == 1 && $item['status'] == 1 && $item['shipping_type'] == 1 && $item['refund_status'] == 0) {
                    $item['status_name'] = '待收货';
                } else if ($item['paid'] == 1 && $item['status'] == 1 && $item['shipping_type'] == 2 && $item['refund_status'] == 0) {
                    $item['status_name'] = '未核销';
                } else if ($item['paid'] == 1 && $item['status'] == 2 && $item['refund_status'] == 0) {
                    $item['status_name'] = '待评价';
                } else if ($item['paid'] == 1 && $item['status'] == 3 && $item['refund_status'] == 0) {
                    $item['status_name'] = '已完成';
                } else if ($item['paid'] == 1 && $item['refund_status'] == 1) {
                    $refundReasonTime = date('Y-m-d H:i', $item['refund_reason_time']);
                    $refundReasonWapImg = json_decode($item['refund_reason_wap_img'], true);
                    $refundReasonWapImg = $refundReasonWapImg ?: [];
                    $img = '';
                    if (count($refundReasonWapImg)) {
                        foreach ($refundReasonWapImg as $itemImg) {
                            if (strlen(trim($itemImg)))
                                $img .= '<img style="height:50px;" src="' . $itemImg . '" />';
                        }
                    }
                    if (!strlen(trim($img))) $img = '无';
                    $item['status_name'] = <<<HTML
<b style="color:#f124c7">申请退款</b><br/>
<span>退款原因：{$item['refund_reason_wap']}</span><br/>
<span>备注说明：{$item['refund_reason_wap_explain']}</span><br/>
<span>退款时间：{$refundReasonTime}</span><br/>
<span>退款凭证：{$img}</span>
HTML;
                } else if ($item['paid'] == 1 && $item['refund_status'] == 2) {
                    $item['status_name'] = '已退款';
                }

                $goodsName = [];
                foreach ($item['_info'] as $k => $v) {

                    $suk = '';
                    if (isset($v['productInfo']['attrInfo'])) {
                        if (isset($v['productInfo']['attrInfo']['suk'])) {
                            $suk = '(' . $v['productInfo']['attrInfo']['suk'] . ')';
                        }
                    }
                    if (isset($v['productInfo']['store_name'])) {
                        $goodsName[] = implode(' ',
                            [$v['productInfo']['store_name'],
                                $suk,
                                "[{$v['cart_num']} * {$v['truePrice']}]"
                            ]);
                    }
                }
                if ($item['sex'] == 1) $sex_name = '男';
                else if ($item['sex'] == 2) $sex_name = '女';
                else $sex_name = '未知';
                $export[] = [
                    $item['order_id'],
                    $sex_name,
                    $item['user_phone'],
                    $item['real_name'],
                    $item['user_phone'],
                    $item['user_address'],
                    $goodsName ? implode("\n", $goodsName) : '',
                    $item['total_price'],
                    $item['pay_price'],
                    $item['pay_postage'],
                    $item['coupon_price'],
                    $item['pay_type_name'],
                    $item['pay_time'] > 0 ? date('Y/m-d H:i', (int)$item['pay_time']) : '暂无',
                    $item['status_name'] ?? '未知状态',
                    empty($item['add_time']) ? 0 : date('Y-m-d H:i:s', (int)$item['add_time']),
                    $item['mark']
                ];
            }
        }
        $header = ['订单号', '性别', '电话', '收货人姓名', '收货人电话', '收货地址', '商品信息',
            '总价格', '实际支付', '邮费', '优惠金额', '支付状态', '支付时间', '订单状态', '下单时间', '用户备注'];
        $title = ['订单导出', '订单信息' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '订单导出_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    /**
     * 商铺自提点导出
     * @param $data 导出数据
     */
    public function storeMerchant($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as $index => $item) {
                $export[] = [
                    $item['name'],
                    $item['phone'],
                    $item['address'] . '' . $item['detailed_address'],
                    $item['day_time'],
                    $item['is_show'] ? '开启' : '关闭'
                ];
            }
        }
        $header = ['提货点名称', '提货点', '地址', '营业时间', '状态'];
        $title = ['提货点导出', '提货点信息' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '提货点导出_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    public function memberCard($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data['data'] as $index => $item) {
                $export[] = [
                    $item['card_number'],
                    $item['card_password'],
                    $item['user_name'],
                    $item['user_phone'],
                    $item['use_time'],
                    $item['use_uid'] ? '已领取' : '未领取'
                ];
            }
        }
        $header = ['会员卡号', '密码', '领取人', '领取人手机号', '领取时间', '是否使用'];
        $title = ['会员卡导出', '会员卡导出' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = $data['title'] ? ("卡密会员_" . trim(str_replace(["\r\n", "\r", "\\", "\n", "/", "<", ">", "=", " "], '', $data['title']))) : "";
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    public function tradeData($data = [], $tradeTitle = "交易统计")
    {
        $export = $header = [];
        if (!empty($data)) {
            $header = ['时间'];
            $headerArray = array_column($data['series'], 'name');
            $header = array_merge($header, $headerArray);
            $export = [];
            foreach ($data['series'] as $index => $item) {
                foreach ($data['x'] as $k => $v) {
                    $export[$v]['time'] = $v;
                    $export[$v][] = $item['value'][$k];
                }
            }
        }
        $title = [$tradeTitle, $tradeTitle, ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = $tradeTitle;
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }


    /**
     * 商品统计
     * @param $data 导出数据
     */
    public function productTrade($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as &$value) {
                $export[] = [
                    $value['time'],
                    $value['browse'],
                    $value['user'],
                    $value['cart'],
                    $value['order'],
                    $value['payNum'],
                    $value['pay'],
                    $value['cost'],
                    $value['refund'],
                    $value['refundNum'],
                    $value['changes'] . '%'
                ];
            }
        }
        $header = ['日期/时间', '商品浏览量', '商品访客数', '加购件数', '下单件数', '支付件数', '支付金额', '成本金额', '退款金额', '退款件数', '访客-支付转化率'];
        $title = ['商品统计', '商品统计' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '商品统计_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }

    public function userTrade($data = [])
    {
        $export = [];
        if (!empty($data)) {
            foreach ($data as &$value) {
                $export[] = [
                    $value['time'],
                    $value['user'],
                    $value['browse'],
                    $value['new'],
                    $value['paid'],
                    $value['changes'] . '%',
                    $value['vip'],
                    $value['recharge'],
                    $value['payPrice'],
                ];
            }
        }
        $header = ['日期/时间', '访客数', '浏览量', '新增用户数', '成交用户数', '访客-支付转化率', '付费会员数', '充值用户数', '客单价'];
        $title = ['用户统计', '用户统计' . time(), ' 生成时间：' . date('Y-m-d H:i:s', time())];
        $filename = '用户统计_' . date('YmdHis', time());
        $suffix = 'xlsx';
        $is_save = true;
        return $this->export($header, $title, $export, $filename, $suffix, $is_save);
    }
}
