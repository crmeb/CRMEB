<?php
/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2018/6/14 下午5:25
 */

namespace app\admin\controller\record;

use app\admin\controller\AuthController;
use app\admin\model\store\StoreProduct;
use app\admin\model\order\StoreOrder;
use app\admin\model\ump\StoreBargain;
use app\admin\model\ump\StoreSeckill;
use app\admin\model\ump\StoreCombination;
use crmeb\services\JsonService;
use crmeb\services\UtilService as Util;
use app\admin\model\user\User;
use app\admin\model\user\UserBill;
use app\admin\model\user\UserExtract;
use app\admin\model\store\StoreCouponUser;
/**
 * 微信充值记录
 * Class UserRecharge
 * @package app\admin\controller\user
 */
class Record extends AuthController

{

    /**
     * 显示操作记录
     */
    public function index(){


    }
    /**
     * 显示订单记录
     */
    public function chart_order(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    public function get_echarts_order(){
        $where=Util::getMore([
            ['type',''],
            ['status',''],
            ['data',''],
        ]);
        return JsonService::successful(StoreOrder::getEchartsOrder($where));
    }
    /**
     * 显示产品记录
     */
    public function chart_product(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    /**
     * 获取产品曲线图数据
     */
    public function get_echarts_product($type='',$data=''){
        return JsonService::successful(StoreProduct::getChatrdata($type,$data));

    }

    /**
     * 获取销量
     */
    public function get_echarts_maxlist($data=''){
        return JsonService::successful(StoreProduct::getMaxList(compact('data')));
    }
    /**
     * 获取利润
     */
    public function get_echarts_profity($data=''){
        return JsonService::successful(StoreProduct::ProfityTop10(compact('data')));
    }
    /**
     * 获取缺货列表
     */
    public function getLackList(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(StoreProduct::getLackList($where));
    }
    /**
     * 表单快速修改
     */
    public function editField($id=''){
        $post=$this->request->post();
        StoreProduct::beginTrans();
        try{
            StoreProduct::edit($post,$id);
            StoreProduct::commitTrans();
            return JsonService::successful('修改成功');
        }catch (\Exception $e){
            StoreProduct::rollbackTrans();
            return JsonService::fail($e->getMessage());
        }
    }
    //获取差评
    public function getnegativelist(){
        $where=Util::getMore([
            ['page',1],
            ['limit',10],
        ]);
        return JsonService::successful(StoreProduct::getnegativelist($where));
    }
    /**
     * 获取退货
     */
    public function getTuiPriesList(){
        return JsonService::successful(StoreProduct::TuiProductList());
    }
    //营销统计
    /**
     * 显示积分统计
     */
    public function chart_score(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    /**
     * 获取积分头部信息
     */
    public function getScoreBadgeList($data=''){
        return JsonService::successful(UserBill::getScoreBadgeList(compact('data')));
    }
    /**
     * 获取积分曲线图和柱状图
     */
    public function getScoreCurve($data='',$limit=20){
        return JsonService::successful(UserBill::getScoreCurve(compact('data','limit')));
    }
    /**
     * 显示优惠券统计
     */
    public function chart_coupon(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    /**
     * 获取优惠劵头部信息
     */
    public function getCouponBadgeList($data=''){
        return JsonService::successful(StoreCouponUser::getCouponBadgeList(compact('data')));
    }
    /**
     * 获取优惠劵数据图表
     */
    public function getConponCurve($data=''){
        return JsonService::successful(StoreCouponUser::getConponCurve(compact('data')));
    }
    /**
     * 显示拼团统计
     */
    public function chart_combination(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    /**
     * 显示砍价统计
     */
    public function chart_bargain(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    /**
     * 显示秒杀统计
     */
    public function chart_seckill(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    //财务统计
    /**
     * 显示反佣统计
     */
    public function chart_rebate(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    //获取用户返佣柱状图
    public function getUserBillBrokerage($data=''){
        return JsonService::successful(UserBill::getUserBillChart(compact('data')));
    }
    //获取用户返佣头部信息
    public function getRebateBadge($data=''){
        return JsonService::successful(UserBill::getRebateBadge(compact('data')));
    }
    //获得 返佣列表,带分页
    public function getFanList($page=1,$limit=20){
        return JsonService::successful(UserBill::getFanList(compact('page','limit')));
    }
    //获得 返佣总次数
    public function getFanCount(){
        return JsonService::successful(UserBill::getFanCount());
    }
    /**
     * 显示充值统计
     */
    public function chart_recharge(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    /**
     * 获取用户充值柱状图和曲线图
     */
    public function getEchartsRecharge($data=''){
        return JsonService::successful(UserBill::getEchartsRecharge(compact('data')));
    }
    /**
     * 显示提现统计
     */
    public function chart_cash(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    //获取提现头部信息
    public function getExtractHead($data=''){
        return JsonService::successful(UserExtract::getExtractHead(compact('data')));
    }
    //会员统计
    /**
     * 显示用户统计
     */
    public function user_chart(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    /**
     * 获取头部信息
     *
     * 人数 增长 分销人数 分销增长
     */
    public function getBadgeList($data='',$is_promoter='',$status=''){
        return JsonService::successful(User::getBadgeList(compact('data','is_promoter','status')));
    }
    /*
     * 获取用户增长曲线图
     *
     */
    public function getUserChartList($data='',$is_promoter='',$status=''){
        return JsonService::successful(User::getUserChartList(compact('data','is_promoter','status')));
    }
    /*
     * 获取提现分布图和提现人数金额曲线图
     *
     */
    public function getExtractData($data=''){
        return JsonService::successful(UserExtract::getExtractList(compact('data')));
    }
    /*
     * 分销会员统计
     *
     */
    public function user_distribution_chart(){
        $limit=10;
        $top10list=User::getUserDistributionTop10List($limit);
        $this->assign([
            'is_layui'=>true,
            'limit'=>$limit,
            'year'=>getMonth(),
            'commissionList'=>$top10list['commission'],
            'extractList'=>$top10list['extract'],
        ]);
        return $this->fetch();
    }
    /*
     * 获取分销会员统计会员头部详情
     *
     */
    public function getDistributionBadgeList($data=''){
        return JsonService::successful(User::getDistributionBadgeList(compact('data')));
    }
    /*
     * 获取分销会员统计图表数据
     *
     * $data 时间范围
     *
     */
    public function getUserDistributionChart($data=''){
        return JsonService::successful(User::getUserDistributionChart(compact('data')));
    }
    /**
     * 会员业务
     */
    public function user_business_chart(){
        $limit=10;
        $top10list=User::getUserTop10List($limit);
        $this->assign([
            'is_layui'=>true,
            'limit'=>$limit,
            'year'=>getMonth(),
            'integralList'=>$top10list['integral'],
            'moneyList'=>$top10list['now_money'],
            'shopcountList'=>$top10list['shopcount'],
            'orderList'=>$top10list['order'],
            'lastorderList'=>$top10list['lastorder']
        ]);
        return $this->fetch();
    }
    /*
     * 获取 会员业务的
     * 购物会员统计
     * 分销商业务人数和提现人数统计
     * 分销商业务佣金和提现金额统计
     * 曲线图
     * $data 时间
     */
    public function getUserBusinessChart($data=''){
        return JsonService::successful(User::getUserBusinessChart(compact('data')));
    }
    /*
    * 获取 会员业务
    * 会员总余额 分销商总佣金 分销商总佣金余额 分销商总提现佣金 本月分销商业务佣金 本月分销商佣金提现金额
    * 上月分销商业务佣金 上月分销商佣金提现金额
    * $where 查询条件
    *
    * return array
    */
    public function getUserBusinesHeade($data){
        return JsonService::successful(User::getUserBusinesHeade(compact('data')));
    }
    /**
     * 显示用户属性统计
     */
    public function user_attr(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    /**
     * 获取用户属性统计
     */
    public function getEchartsData($data=''){
        return JsonService::successful(User::getEchartsData(compact('data')));
    }
    //排行榜
    /**
     * 显示产品排行榜
     */
    public function ranking_saleslists(){
        $this->assign([
            'is_layui'=>true,
        ]);
        return $this->fetch();
    }
    /*
     *获取产品排行 带分页
     */
    public function getSaleslists($start_time='',$end_time='',$title='',$page=1,$limit=20){
        return JsonService::successlayui(StoreProduct::getSaleslists(compact('start_time','end_time','title','page','limit')));
    }
    /*
     *生成表格,并下载
     */
    public function save_product_export($start_time='',$end_time='',$title=''){
        return JsonService::successlayui(StoreProduct::SaveProductExport(compact('start_time','end_time','title')));
    }
    /*
     *获取单个商品的详情
     */
    public function product_info($id=''){
        if($id=='') $this->failed('缺少商品id');
        if(!StoreProduct::be(['id'=>$id])) return $this->failed('商品不存在!');
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth(),
            'id'=>$id,
        ]);
        return $this->fetch();
    }
    /*
     *获取单个商品的详情头部信息
     */
    public function getProductBadgeList($id='',$data=''){
        return JsonService::successful(StoreProduct::getProductBadgeList($id,$data));
    }
    /*
     *获取单个商品的销售曲线图
     */
    public function getProductCurve($id='',$data='',$limit=20){
        return JsonService::successful(StoreProduct::getProductCurve(compact('id','data','limit')));
    }
    /*
     *获取单个商品的销售总条数
     */
    public function getProductCount($id,$data=''){
        return JsonService::successful(StoreProduct::setWhere(compact('data'))
            ->where('a.product_id',$id)
            ->join('user c','c.uid=a.uid')
            ->where('a.is_pay',1)
            ->count());
    }
    /*
     *获取单个商品的销售列表
     */
    public function getSalelList($data='',$id=0,$page=1,$limit=20){
        return JsonService::successful(StoreProduct::getSalelList(compact('data','id','page','limit')));
    }
    /**
     * 显示反佣排行榜
     */
    public function ranking_commission(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    public function getcommissionlist($page=1,$limit=20){
        return JsonService::successful(UserExtract::where('status',1)
            ->field(['real_name','extract_price','balance'])
            ->order('extract_price desc')
            ->page($page,$limit)
            ->select());
    }
    public function getmonthcommissionlist($page=1,$limit=20){
        return JsonService::successful(UserExtract::where('status',1)
            ->whereTime('add_time','month')
            ->field(['real_name','extract_price','balance'])
            ->order('extract_price desc')
            ->page($page,$limit)
            ->select());
    }
    //获取佣金返现总条数
    public function getCommissonCount(){
        return JsonService::successful(UserExtract::where('status',1)->count());
    }
    //获取本月佣金返现条数
    public function getMonthCommissonCount(){
        return JsonService::successful(UserExtract::where('status',1)->whereTime('add_time','month')->count());
    }
    /**
     * 显示积分排行榜
     */
    public function ranking_point(){
        $this->assign([
            'is_layui'=>true,
            'year'=>getMonth()
        ]);
        return $this->fetch();
    }
    //获取所有积分排行总人数
    public function getPountCount(){
        return JsonService::successful(User::where('status', 1)->where('integral','<>',0)->count());
    }
    //获取积分排行列表
    public function getpointList($page=1,$limit=20){
        return JsonService::successful(($list=User::where('status', 1)
            ->where('integral','<>',0)
            ->field('nickname,integral')
            ->order('integral desc')
            ->page($page,$limit)
            ->select()) && count($list) ? $list->toArray():[]);
    }
    //获取本月积分排行别表
    public function getMonthpountList($page=1,$limit=20){
        return JsonService::successful(($list=User::where('status',1)
            ->where('integral','<>',0)
            ->whereTime('add_time','month')
            ->order('integral desc')
            ->field('nickname,integral')
            ->page($page,$limit)
            ->select()) && count($list) ? $list->toArray():[]);
    }
    public function getMonthPountCount(){
        return JsonService::successful(User::where('status',1)->where('integral','<>',0)->whereTime('add_time','month')->count());
    }
    /**
     *
     * 显示下级会员排行榜
     */
    public function ranking_lower(){
        echo " 复购率 复购增长率 活跃度 活跃率 分销总金额 增长率 消费会员 非消费会员 消费排行榜 积分排行榜 余额排行榜 分销总金额排行榜 分销人数排行榜 分销余额排行榜 购物金额排行榜 购物次数排行榜 提现排行榜 ";
    }
    /**
     * 获取砍价产品曲线图数据
     */
    public function get_mark_echarts_product($type='',$data='',$model = 0){
        if(!$model) return JsonService::successful(StoreBargain::getChatrdata($type,$data));
        if($model) return JsonService::successful(StoreSeckill::getChatrdata($type,$data));
    }
    /**
     * 获取拼团产品曲线图数据
     */
    public function get_combination_echarts_product($type='',$data=''){
        return JsonService::successful(StoreCombination::getChatrdata($type,$data));
    }
    /*
     * 获取拼团销量
    */
    public function get_combination_maxlist($data=''){
        return JsonService::successful(StoreCombination::getMaxList(compact('data')));
    }
    /*
     *  拼团盈利
     */
    public function get_combination_profity($data=''){
        return JsonService::successful(StoreCombination::ProfityTop10(compact('data')));
    }
    /*
     *  拼团退货
     */
    public function get_combination_refund_list(){
        $where = Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        return JsonService::successlayui(StoreCombination::getBargainRefundList($where));
    }
    /**
     * 获取销量
     */
    public function get_mark_echarts_maxlist($data='',$model = 0){
        if(!$model) return JsonService::successful(StoreBargain::getMaxList(compact('data')));
        if($model) return JsonService::successful(StoreSeckill::getMaxList(compact('data')));
    }

    /**
     * 获取利润
     */
    public function get_mark_echarts_profity($data='',$model = 0){
        if(!$model) return JsonService::successful(StoreBargain::ProfityTop10(compact('data')));
        if($model) return JsonService::successful(StoreSeckill::ProfityTop10(compact('data')));
    }
    /**
     * 获取补货的砍价产品
     */
    public function get_mark_lack_list($model = 0){
        $where = Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        if(!$model) return JsonService::successlayui(StoreBargain::getLackList($where));
        if($model) return JsonService::successlayui(StoreSeckill::getLackList($where));
    }
    /**
     * 获取砍价产品的评论
     */
    public function get_mark_negative_list($model = 0){
        $where = Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        if(!$model) return JsonService::successlayui(StoreBargain::getNegativeList($where));
        if($model) return JsonService::successlayui(StoreSeckill::getNegativeList($where));
    }
    /**
     * 获取砍价产品的退货
     */
    public function get_mark_bargain_refund_list($model = 0){
        $where = Util::getMore([
            ['page',1],
            ['limit',20],
        ]);
        if(!$model) return JsonService::successlayui(StoreBargain::getBargainRefundList($where));
        if($model) return JsonService::successlayui(StoreSeckill::getBargainRefundList($where));
    }



}

