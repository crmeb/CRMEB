<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16 0016
 * Time: 11:07
 */

namespace app\admin\model\finance;
use traits\ModelTrait;
use basic\ModelBasic;
use service\ExportService;
use app\wap\model\user\UserBill;
use app\admin\model\user\User;
use service\PHPExcelService;
/*数据统计处理*/
class FinanceModel extends ModelBasic
{
    protected $name = 'user_bill';
    use ModelTrait;

    /**
     * 处理金额
     * @param $where
     * @return array
     */
    public static function systemPage($where)
    {
        $model = new self;
        //翻页
        $limit = $where['limit'];
        $offset= $where['offset'];
        $limit = $offset.','.$limit;
        //排序
        $order = '';
        if(!empty($where['sort'])&&!empty($where['sortOrder'])){
            $order = $where['sort'].' '.$where['sortOrder'];
        }
        unset($where['limit']);unset($where['offset']);
        unset($where['sort']);unset($where['sortOrder']);
        if(!empty($where['add_time'])){
            list($startTime,$endTime) = explode(' - ',$where['add_time']);
            $where['add_time'] = array('between',[strtotime($startTime),strtotime($endTime)]);
        }else{
            $where['add_time'] = array('between',[strtotime(date('Y/m').'/01'),strtotime(date('Y/m').'/'.date('t'))]);
        }
        if(empty($where['title'])){
            unset($where['title']);
        }

        $total = $model->where($where)->count();
        $rows = $model->where($where)->order($order)->limit($limit)->select()->each(function($e){
            return $e['add_time'] = date('Y-m-d H:i:s',$e['add_time']);
        })->toArray();
        return compact('total','rows');
    }
    public static function getBillList($where){
//        \think\Db::listen(function($sql, $time, $explain){
//            // 记录SQL
//            echo $sql. ' ['.$time.'s]';
//            // 查看性能分析结果
////            dump($explain);
//        });
        $data=($data=self::setWhereList($where)->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray():[];
        $count=self::setWhereList($where)->count();
        return compact('data','count');
    }
    public static function SaveExport($where){
        $data=($data=self::setWhereList($where)->select()) && count($data) ? $data->toArray():[];
        $export = [];
        foreach ($data as $value){
            $export[]=[
                $value['uid'],
                $value['nickname'],
                $value['pm']==0 ? '-'.$value['number']:$value['number'],
                $value['title'],
                $value['mark'],
                $value['add_time'],
            ];
        }
        PHPExcelService::setExcelHeader(['会员ID','昵称','金额/积分','类型','备注','创建时间'])
            ->setExcelTile('资金监控', '资金监控',date('Y-m-d H:i:s',time()))
            ->setExcelContent($export)
            ->ExcelSave();
    }
    public static function setWhereList($where){
        $time['data']='';
        if($where['start_time']!='' && $where['end_time']!=''){
            $time['data']=$where['start_time'].' - '.$where['end_time'];
        }
        $model=self::getModelTime($time,self::alias('A')
            ->join('user B','B.uid=A.uid')
            ->where('A.category','not in','integral')
            ->order('A.add_time desc'),'A.add_time');
        if(trim($where['type'])!=''){
            $model=$model->where('A.type',$where['type']);
        }else{
            $model=$model->where('A.type','not in','gain,system_sub,deduction,sign');
        }
        if($where['nickname']!=''){
            $model=$model->where('B.nickname|B.uid','like',"%$where[nickname]%");
        }
        return $model->field(['A.*','FROM_UNIXTIME(A.add_time,"%Y-%m-%d %H:%i:%s") as add_time','B.uid','B.nickname']);
    }
    /**
     * 获取营业数据
     */
    public static function getOrderInfo($where)
    {
        $orderinfo = self::getTimeWhere($where)
            ->field('sum(total_price) total_price,sum(cost) cost,sum(pay_postage) pay_postage,sum(pay_price) pay_price,sum(coupon_price) coupon_price,sum(deduction_price) deduction_price,from_unixtime(pay_time,\'%Y-%m-%d\') pay_time')->order('pay_time')->group('from_unixtime(pay_time,\'%Y-%m-%d\')')->select()->toArray();
        $price = 0;
        $postage = 0;
        $deduction = 0;
        $coupon = 0;
        $cost = 0;
        foreach ($orderinfo as $info) {
            $price = bcadd($price, $info['total_price'], 2);//应支付
            $postage = bcadd($postage, $info['pay_postage'], 2);//邮费
            $deduction = bcadd($deduction, $info['deduction_price'], 2);//抵扣
            $coupon = bcadd($coupon, $info['coupon_price'], 2);//优惠券
            $cost = bcadd($cost, $info['cost'], 2);//成本
        }
        return compact('orderinfo', 'price', 'postage', 'deduction', 'coupon', 'cost');
    }

    /**
     * 处理where条件
     */
    public static function statusByWhere($status, $model = null)
    {
        if ($model == null) $model = new self;
        if ('' === $status)
            return $model;
        else if ($status == 'weixin')//微信支付
            return $model->where('pay_type', 'weixin');
        else if ($status == 'yue')//余额支付
            return $model->where('pay_type', 'yue');
        else if ($status == 'offline')//线下支付
            return $model->where('pay_type', 'offline');
        else
            return $model;
    }

    public static function getTimeWhere($where, $model = null)
    {
        return self::getTime($where)->where('paid', 1)->where('refund_status', 0);
    }
    /**
     * 获取时间区间
     */
    public static function getTime($where,$model=null,$prefix='add_time'){
        if ($model == null) $model = new self;
        if ($where['data'] == '') {
            switch ($where['date']){
                case 'today':case 'week':case 'month':case 'year':
                    $model=$model->whereTime($prefix,$where['date']);
                    break;
                case 'quarter':
                    list($startTime,$endTime)=User::getMonth('n');
                    $model = $model->where($prefix, '>', strtotime($startTime));
                    $model = $model->where($prefix, '<', strtotime($endTime));
                    break;
            }
        }else{
            list($startTime, $endTime) = explode(' - ', $where['data']);
            $model = $model->where($prefix, '>', strtotime($startTime));
            $model = $model->where($prefix, '<', strtotime($endTime));
        }
        return $model;
    }
    /**
     * 获取新增消费
     */
    public static function getConsumption($where)
    {
        $consumption=self::getTime($where,new UserBill,'b.add_time')->alias('a')->join('user b','a.uid = b.uid')
            ->field('sum(a.number) number')
        ->where('a.type','pay_product')->find()->toArray();
        return $consumption;
    }
    /**
     * 获取拼团商品
     */
    public static function getPink($where)
    {
        $pink = self::getTimeWhere($where)->where('pink_id', 'neq', 0)->sum('pay_price');
        return $pink;
    }
    /**
     * 获取秒杀商品
     */
    public static function getSeckill($where){
        $seckill=self::getTimeWhere($where)->where('seckill_id', 'neq', 0)->sum('pay_price');
        return $seckill;
    }
    /**
     * 获取普通商品数
     */
    public static function getOrdinary($where)
    {
        $ordinary = self::getTimeWhere($where)->where('pink_id', 'eq', 0)->where('seckill_id','eq','0')->sum('pay_price');
        return $ordinary;
    }

    /**
     * 获取用户充值
     */
    public static function getRecharge($where)
    {
            $Recharge = self::getTime($where,new UserBill)->where('type', 'system_add')->where('category','now_money')->sum('number');
            return $Recharge;
    }
    /**
     * 获取推广金
     */
    public static function getExtension($where)
    {
        $extension = self::getTime($where,new UserBill)->where('type', 'brokerage')->where('category','now_money')->sum('number');
        return $extension;
    }

    /**
     * 最近交易
     */
    public static function trans()
    {
        $trans = self::alias('a')
            ->join('user b', 'a.uid=b.uid')
            ->join('__STORE_ORDER_CART_INFO__ c', 'a.id=c.oid')
            ->join('__STORE_PRODUCT__ d', 'c.product_id=d.id')
            ->field('b.nickname,a.pay_price,d.store_name')
            ->order('a.add_time DESC')
            ->limit('6')
            ->select()->toArray();
        return $trans;
    }

    /**
     * 导出表格
     */
    public static function systemTable($where){
        $orderinfos=self::getOrderInfo($where);
        if($where['export'] == 1){
            $export = [];
            $orderinfo=$orderinfos['orderinfo'];
            foreach($orderinfo as $info){
                $time=$info['pay_time'];
                $price = $info['total_price']+$info['pay_postage'];
                $zhichu = $info['coupon_price']+$info['deduction_price']+$info['cost'];
                $profit = ($info['total_price']+$info['pay_postage'])-($info['coupon_price']+$info['deduction_price']+$info['cost']);
                $deduction=$info['deduction_price'];//积分抵扣
                $coupon=$info['coupon_price'];//优惠
                $cost=$info['cost'];//成本
                $export[] = [$time,$price,$zhichu,$cost,$coupon,$deduction,$profit];
            }
//            ExportService::exportCsv($export,'统计'.time(),['时间','营业额(元)','支出(元)','成本','优惠','积分抵扣','盈利(元)']);
            dump($export);
            PHPExcelService::setExcelHeader(['时间','营业额(元)','支出(元)','成本','优惠','积分抵扣','盈利(元)'])->setExcelTile('财务统计', '财务统计',date('Y-m-d H:i:s',time()))->setExcelContent($export)->ExcelSave();
        }
    }
}