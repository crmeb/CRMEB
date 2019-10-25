<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16 0016
 * Time: 10:39
 */

namespace app\admin\controller\record;

use app\admin\controller\AuthController;
use crmeb\services\UtilService as Util;
use app\admin\model\record\StoreStatistics as StatisticsModel;

/**
 * Class StoreStatistics
 * @package app\admin\controller\record
 */
class StoreStatistics extends AuthController
{
    /**
     * 显示列表
     */
    public function index()
    {
        $where = Util::getMore([
            ['date',''],
            ['export',''],
            ['data','']
        ],$this->request);
        $where['date']=$this->request->param('date');
        $where['data']=$this->request->param('data');
        $where['export']=$this->request->param('export');
        $trans=StatisticsModel::trans();//最近交易
        $seckill=StatisticsModel::getSeckill($where);//秒杀商品
        $ordinary=StatisticsModel::getOrdinary($where);//普通商品
        $pink=StatisticsModel::getPink($where);//拼团商品
        $recharge=StatisticsModel::getRecharge($where);//充值
        $extension=StatisticsModel::getExtension($where);//推广金
        $orderCount = [
            urlencode('微信支付')=>StatisticsModel::getTimeWhere($where,StatisticsModel::statusByWhere('weixin'))->count(),
            urlencode('余额支付')=>StatisticsModel::getTimeWhere($where,StatisticsModel::statusByWhere('yue'))->count(),
            urlencode('线下支付')=>StatisticsModel::getTimeWhere($where,StatisticsModel::statusByWhere('offline'))->count(),
        ];
        $Statistic = [
            ['name'=>'营业额','type'=>'line','data'=>[]],
            ['name'=>'支出','type'=>'line','data'=>[]],
            ['name'=>'盈利','type'=>'line','data'=>[]],
        ];
        $orderinfos=StatisticsModel::getOrderInfo($where);
        $orderinfo=$orderinfos['orderinfo'];
        $orderDays=[];
        if (empty($orderinfo)){
            $orderDays[]=date('Y-m-d',time());
            $Statistic[0]['data'][] = 0;
            $Statistic[1]['data'][] = 0;
            $Statistic[2]['data'][] = 0;
        }
        foreach($orderinfo as $info){
            $orderDays[]=$info['pay_time'];
            $Statistic[0]['data'][] = $info['total_price']+$info['pay_postage'];
            $Statistic[1]['data'][] = $info['coupon_price']+$info['deduction_price']+$info['cost'];
            $Statistic[2]['data'][] = ($info['total_price']+$info['pay_postage'])-($info['coupon_price']+$info['deduction_price']+$info['cost']);
        }
        $price=$orderinfos['price']+$orderinfos['postage'];
        $cost=$orderinfos['deduction']+$orderinfos['coupon']+$orderinfos['cost'];
        $Consumption=StatisticsModel::getConsumption($where)['number'];
        $header=[
            ['name'=>'总营业额', 'class'=>'fa-line-chart', 'value'=>'￥'.$price, 'color'=>'red'],
            ['name'=>'总支出', 'class'=>'fa-area-chart', 'value'=>'￥'.($cost+$extension), 'color'=>'lazur'],
            ['name'=>'总盈利', 'class'=>'fa-bar-chart', 'value'=>'￥'.bcsub($price,$cost,0), 'color'=>'navy'],
            ['name'=>'新增消费', 'class'=>'fa-pie-chart', 'value'=>'￥'.($Consumption==0?0:$Consumption), 'color'=>'yellow']
        ];
        $data=[
            ['value'=>$orderinfos['cost'], 'name'=>'商品成本'],
            ['value'=>$orderinfos['coupon'], 'name'=>'优惠券抵扣'],
            ['value'=>$orderinfos['deduction'], 'name'=>'积分抵扣'],
            ['value'=>$extension, 'name'=>'推广人佣金']
        ];

        $this->assign(StatisticsModel::systemTable($where));
        $this->assign(compact('where','trans','orderCount','orderDays','header','Statistic','ordinary','pink','recharge','data','seckill'));
        $this->assign('price',StatisticsModel::getOrderPrice($where));

        return $this->fetch();
    }
}