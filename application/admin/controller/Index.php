<?php

namespace app\admin\controller;

use app\admin\model\store\StoreProduct;
use app\admin\model\system\SystemConfig;
use app\admin\model\system\SystemMenus;
use app\admin\model\system\SystemNotice as NoticeModel;
use app\admin\model\system\SystemNotice;
use app\admin\model\system\SystemRole;
use app\admin\model\order\StoreOrder;
use app\admin\model\user\UserExtract;
use service\CacheService;
use service\UpgradeService;
use service\UpgradeApi;
use think\DB;

/**
 * 首页控制器
 * Class Index
 * @package app\admin\controller
 *
 */
class Index extends AuthController
{
    public function index()
    {
        //获取当前登录后台的管理员信息
        $adminInfo = $this->adminInfo->toArray();
        $roles  = explode(',',$adminInfo['roles']);
        $site_logo = SystemConfig::getOneConfig('menu_name','site_logo')->toArray();
//        dump(SystemMenus::menuList());
//        exit();
        $this->assign([
            'menuList'=>SystemMenus::menuList(),
            'site_logo'=>json_decode($site_logo['value'],true),
            'role_name'=>SystemRole::where('id',$roles[0])->field('role_name')->find()
        ]);
        return $this->fetch();
    }
    //后台首页内容
    public function main()
    {
        /*首页第一行统计*/
        $now_month = strtotime(date('Y-m'));
        $pre_month = strtotime(date('Y-m',strtotime('-1 month')));
        $now_day = strtotime(date('Y-m-d'));
        $pre_day = strtotime(date('Y-m-d',strtotime('-1 day')));//昨天时间戳
        $day = strtotime(date('Y-m-d',strtotime('0 day')));//今天时间戳
        //昨天待发货数量
        $topData['orderDeliveryNum'] = StoreOrder::isMainYesterdayCount($pre_day,$day)
            ->where('status',0)
            ->where('paid',1)
            ->where('refund_status',0)
            ->count();
        //昨天退换货订单数
        $topData['orderRefundNum'] = StoreOrder::isMainYesterdayCount($pre_day,$day)
            ->where('paid',1)
            ->where('refund_status','IN','1,2')
            ->count();
        //库存预警
        $replenishment_num = SystemConfig::getValue('store_stock') > 0 ? SystemConfig::getValue('store_stock') : 20;//库存预警界限
        $topData['stockProduct'] = StoreProduct::where('stock','<=',$replenishment_num)->where('is_del',0)->count();
        //待处理提现
        $topData['treatedExtract'] = UserExtract::where('status',0)->count();
        //昨日订单数
        $topData['orderNum'] = StoreOrder::isMainYesterdayCount($pre_day,$day)->count();
        //昨日交易额
        $orderPriceNum = StoreOrder::isMainYesterdayCount($pre_day,$day)->field('sum(pay_price) as pay_price')->find()['pay_price'];
        $topData['orderPriceNum'] = $orderPriceNum ? $orderPriceNum : 0;
        //总收入->日
        $now_day_order_p = StoreOrder::where('paid',1)->where('pay_time','gt',$now_day)->value('sum(pay_price)');
        $pre_day_order_p = StoreOrder::where('paid',1)->where('pay_time','gt',$pre_day)->where('pay_time','lt',$now_day)->value('sum(pay_price)');
        $first_line['d_price'] = [
            'data' => $now_day_order_p ? $now_day_order_p : 0,
            'percent' => abs($now_day_order_p - $pre_day_order_p),
            'is_plus' => $now_day_order_p - $pre_day_order_p > 0 ? 1 : ($now_day_order_p - $pre_day_order_p == 0 ? -1 : 0)
        ];

        //总收入->月
        $now_month_order_p = StoreOrder::where('paid',1)->where('pay_time','gt',$now_month)->value('sum(pay_price)');
        $pre_month_order_p = StoreOrder::where('paid',1)->where('pay_time','gt',$pre_month)->where('pay_time','lt',$now_month)->value('sum(pay_price)');
        $first_line['m_price'] = [
            'data' => $now_month_order_p > 0 ? $now_month_order_p : 0,
            'percent' => abs($now_month_order_p - $pre_month_order_p),
            'is_plus' => $now_month_order_p - $pre_month_order_p > 0 ? 1 : ($now_month_order_p - $pre_month_order_p == 0 ? -1 : 0)
        ];

        //新粉丝->日
        $now_day_user = DB::name('User')->where('add_time','gt',$now_day)->count();
        $pre_day_user = DB::name('User')->where('add_time','gt',$pre_day)->where('add_time','lt',$now_day)->count();
        $pre_day_user = $pre_day_user ? $pre_day_user : 0;
        $first_line['day'] = [
            'data' => $now_day_user ? $now_day_user : 0,
            'percent' => abs($now_day_user - $pre_day_user),
            'is_plus' => $now_day_user - $pre_day_user > 0 ? 1 : ($now_day_user - $pre_day_user == 0 ? -1 : 0)
        ];

        //新粉丝->月
        $now_month_user = DB::name('User')->where('add_time','gt',$now_month)->count();
        $pre_month_user = DB::name('User')->where('add_time','gt',$pre_month)->where('add_time','lt',$now_month)->count();
        $first_line['month'] = [
            'data' => $now_month_user ? $now_month_user : 0,
            'percent' => abs($now_month_user - $pre_month_user),
            'is_plus' => $now_month_user - $pre_month_user > 0 ? 1 : ($now_month_user - $pre_month_user == 0 ? -1 : 0)
        ];

        /*首页第二行统计*/
        $second_line['order_count_max'] = 50; //max最小为100
        for ($i=0; $i < 7; $i++) {
            $time = strtotime('-'.$i.' day');
            $now_day_info = strtotime(date('Y-m-d',strtotime('-'.($i-1).' day')));
            $pre_day_info = strtotime(date('Y-m-d',strtotime('-'.$i.' day')));
            $order_count[$i]['y'] = date('Y',$time);
            $order_count[$i]['m'] = date('m',$time);
            $order_count[$i]['d'] = date('d',$time);
            $order_count[$i]['count'] = StoreOrder::where('add_time','gt',$pre_day_info)->where('add_time','lt',$now_day_info)->count();
            $second_line['order_count_max'] = $second_line['order_count_max'] > $order_count[$i]['count'] ? $second_line['order_count_max'] : $order_count[$i]['count'];
        }
        $second_line['order_count'] = $order_count;

        //本月订单总数
        $now_order_info_c = StoreOrder::where('add_time','gt',$now_month)->count();
        $pre_order_info_c = StoreOrder::where('add_time','gt',$pre_month)->where('add_time','lt',$now_month)->count();
        $order_info['first'] = [
            'data' => $now_order_info_c ? $now_order_info_c : 0,
            'percent' => abs($now_order_info_c - $pre_order_info_c),
            'is_plus' => $now_order_info_c - $pre_order_info_c > 0 ? 1 : ($now_order_info_c - $pre_order_info_c == 0 ? -1 : 0)
        ];

        //上月订单总数
        $second_now_month = strtotime(date('Y-m',strtotime('-1 month')));
        $second_pre_month = strtotime(date('Y-m',strtotime('-2 month')));
        $now_order_info_c = StoreOrder::where('add_time','gt',$pre_month)->where('add_time','lt',$now_month)->count();
        $pre_order_info_c = StoreOrder::where('add_time','gt',$second_pre_month)->where('add_time','lt',$second_now_month)->count();
        $order_info["second"] = [
            'data' => $now_order_info_c ? $now_order_info_c : 0,
            'percent' => abs($now_order_info_c - $pre_order_info_c),
            'is_plus' => $now_order_info_c - $pre_order_info_c > 0 ? 1 : ($now_order_info_c - $pre_order_info_c == 0 ? -1 : 0)
        ];
        $second_line['order_info'] = $order_info;

        /*首页第三行统计*/
        $third_line['order_count_max'] = 100; //max最小为100
        for ($x=0; $x < 30; $x++) {
            $time = strtotime('-'.$x.' day');
            $now_day_info = strtotime(date('Y-m-d',strtotime('-'.($x-1).' day')));
            $pre_day_info = strtotime(date('Y-m-d',strtotime('-'.$x.' day')));
            $price_count[$x]['y'] = date('Y',$time);
            $price_count[$x]['m'] = date('m',$time);
            $price_count[$x]['d'] = date('d',$time);
            $price_count[$x]['count'] = StoreOrder::where('paid',1)->where('pay_time','gt',$pre_day_info)->where('pay_time','lt',$now_day_info)->value('sum(pay_price)');
            $third_line['order_count_max'] = $third_line['order_count_max'] > $price_count[$x]['count'] ? $third_line['order_count_max'] : $price_count[$x]['count'];
        }
        $third_line['price_count'] = $price_count;
        $this->assign([
            'first_line' => $first_line,
            'second_line' => $second_line,
            'third_line' => $third_line,
            'topData' => $topData,
        ]);
        return $this->fetch();
    }
    public function test(){
        UpgradeService::start();
    }
}


