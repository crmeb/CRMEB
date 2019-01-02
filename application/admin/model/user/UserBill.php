<?php

namespace app\admin\model\user;

use traits\ModelTrait;
use basic\ModelBasic;
use app\admin\model\wechat\WechatUser;
use think\Db;
/**
 * 用户消费新增金额明细 model
 * Class User
 * @package app\admin\model\user
 */

class UserBill extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }
    
    public static function income($title,$uid,$category,$type,$number,$link_id = 0,$balance = 0,$mark = '',$status = 1){
        $pm = 1;
        return self::set(compact('title','uid','link_id','category','type','number','balance','mark','status','pm'));
    }
    //获取柱状图和饼状图数据
    public static function getUserBillChart($where,$category='now_money',$type='brokerage',$pm=1,$zoom=15){
        $model=self::getModelTime($where,new self());
        $list=$model->field(['FROM_UNIXTIME(add_time,"%Y-%c-%d") as un_time','sum(number) as sum_number'])
            ->order('un_time asc')
            ->where(['category'=>$category,'type'=>$type,'pm'=>$pm])
            ->group('un_time')
            ->select();
        if(count($list)) $list=$list->toArray();
        $legdata=[];
        $listdata=[];
        $dataZoom='';
        foreach ($list as $item){
            $legdata[]=$item['un_time'];
            $listdata[]=$item['sum_number'];
        }
        if(count($legdata)>=$zoom) $dataZoom=$legdata[$zoom-1];
        //获取用户分布钱数
        $fenbulist=self::getModelTime($where,new self(),'a.add_time')
            ->alias('a')
            ->join('user r','a.uid=r.uid')
            ->field(['a.uid','sum(a.number) as sum_number','r.nickname'])
            ->where(['a.category'=>$category,'a.type'=>$type,'a.pm'=>$pm])
            ->order('sum_number desc')
            ->group('a.uid')
            ->limit(8)
            ->select();
        //获取用户当前时间段总钱数
        $sum_number=self::getModelTime($where,new self())
            ->where(['category'=>$category,'type'=>$type,'pm'=>$pm])
            ->sum('number');
        if(count($fenbulist)) $fenbulist=$fenbulist->toArray();
        $fenbudate=[];
        $fenbu_legend=[];
        $color=['#ffcccc','#99cc00','#fd99cc','#669966','#66CDAA','#ADFF2F','#00BFFF','#00CED1','#66cccc','#ff9900','#ffcc00','#336699','#cccc00','#99ccff','#990066'];
        foreach ($fenbulist as $key=>$value){
            $fenbu_legend[]=$value['nickname'];
            $items['name']=$value['nickname'];
            $items['value']=bcdiv($value['sum_number'],$sum_number,2)*100;
            $items['itemStyle']['color']=$color[$key];
            $fenbudate[]=$items;
        }
        return compact('legdata','listdata','fenbudate','fenbu_legend','dataZoom');
    }
    //获取头部信息
    public static function getRebateBadge($where){
        $datawhere=['category'=>'now_money','type'=>'brokerage','pm'=>1];
        return [
            [
                'name'=>'返利数(笔)',
                'field'=>'个',
                'count'=>self::getModelTime($where,new self())->where($datawhere)->count(),
                'content'=>'返利总笔数',
                'background_color'=>'layui-bg-blue',
                'sum'=>self::where($datawhere)->count(),
                'class'=>'fa fa-bar-chart',
            ],
            [
                'name'=>'返利金额（元）',
                'field'=>'个',
                'count'=>self::getModelTime($where,new self())->where($datawhere)->sum('number'),
                'content'=>'返利总金额',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::where($datawhere)->sum('number'),
                'class'=>'fa fa-line-chart',
            ],
        ];
    }
    //获取返佣用户信息列表
    public static function getFanList($where){
        $datawhere=['a.category'=>'now_money','a.type'=>'brokerage','a.pm'=>1];
        $list=self::alias('a')->join('user r','a.uid=r.uid')
            ->where($datawhere)
            ->order('a.number desc')
            ->join('__STORE_ORDER__ o','o.id=a.link_id')
            ->field(['o.order_id','FROM_UNIXTIME(a.add_time,"%Y-%c-%d") as add_time','a.uid','o.uid as down_uid','r.nickname','r.avatar','r.spread_uid','r.level','a.number'])
            ->page((int)$where['page'],(int)$where['limit'])
            ->select();
        if(count($list)) $list=$list->toArray();
        return $list;
    }
    //获取返佣用户总人数
    public static function getFanCount(){
        $datawhere=['a.category'=>'now_money','a.type'=>'brokerage','a.pm'=>1];
        return self::alias('a')->join('user r','a.uid=r.uid')->join('__STORE_ORDER__ o','o.id=a.link_id')->where($datawhere)->count();
    }
    //获取用户充值数据
    public static function getEchartsRecharge($where,$limit=15){
        $datawhere=['category'=>'now_money','pm'=>1];
        $list=self::getModelTime($where,self::where($datawhere)->where('type','in',['recharge','system_add']))
            ->field(['sum(number) as sum_money','FROM_UNIXTIME(add_time,"%Y-%c-%d") as un_time','count(id) as count'])
            ->group('un_time')
            ->order('un_time asc')
            ->select();
        if(count($list)) $list=$list->toArray();
        $sum_count=self::getModelTime($where,self::where($datawhere)->where('type','in',['recharge','system_add']))->count();
        $xdata=[];
        $seriesdata=[];
        $data=[];
        $zoom='';
        foreach ($list as $value){
            $xdata[]=$value['un_time'];
            $seriesdata[]=$value['sum_money'];
            $data[]=$value['count'];
        }
        if(count($xdata)>$limit){
            $zoom=$xdata[$limit-5];
        }
        return compact('xdata','seriesdata','data','zoom');
    }
    //获取佣金提现列表
    public static function getExtrctOneList($where,$uid){
        $list=self::setOneWhere($where,$uid)
            ->field(['number','link_id','mark','FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i:%s") as _add_time','status'])
            ->select();
        count($list) && $list=$list->toArray();
        $count=self::setOneWhere($where,$uid)->count();
        foreach ($list as &$value){
            $value['order_id']=Db::name('store_order')->where(['order_id'=>$value['link_id']])->value('order_id');
        }
        return ['data'=>$list,'count'=>$count];
    }
    //设置单个用户查询
    public static function setOneWhere($where,$uid){
        $model=self::where(['uid'=>$uid,'category'=>'now_money','type'=>'brokerage']);
        $time['data']='';
        if($where['start_time']!='' && $where['end_time']!=''){
            $time['data']=$where['start_time'].' - '.$where['end_time'];
            $model=self::getModelTime($time,$model);
        }
        if($where['nickname']!=''){
            $model=$model->where('link_id|mark','like',"%$where[nickname]%");
        }
        return $model;
    }
    //查询积分个人明细
    public static function getOneIntegralList($where){
        return self::setWhereList(
            $where,
            ['deduction','system_add'],
            ['title','number','balance','mark','FROM_UNIXTIME(add_time,"%Y-%m-%d") as add_time']
        );
    }
    //查询个人签到明细
    public static function getOneSignList($where){
        return self::setWhereList(
            $where,'sign',
            ['title','number','mark','FROM_UNIXTIME(add_time,"%Y-%m-%d") as add_time']
            );
    }
    //查询个人余额变动记录
    public static function getOneBalanceChangList($where){
         $list=self::setWhereList(
            $where,
            ['system_add','pay_product','extract','pay_product_refund','system_sub'],
            ['FROM_UNIXTIME(add_time,"%Y-%m-%d") as add_time','title','type','mark','number','balance','pm','status'],
            'now_money'
        );
         foreach ($list as &$item){
            switch ($item['type']){
                case 'system_add':
                    $item['_type']='系统添加';
                    break;
                case 'pay_product':
                    $item['_type']='商品购买';
                    break;
                case 'extract':
                    $item['_type']='提现';
                    break;
                case 'pay_product_refund':
                    $item['_type']='退款';
                    break;
                case 'system_sub':
                    $item['_type']='系统减少';
                    break;
            }
            $item['_pm']=$item['pm']==1 ? '获得': '支出';
         }
         return $list;
    }
    //设置where条件分页.返回数据
    public static function setWhereList($where,$type='',$field=[],$category='integral'){
        $models=self::where('uid',$where['uid'])
            ->where('category',$category)
            ->page((int)$where['page'],(int)$where['limit'])
            ->field($field);
        if(is_array($type)){
            $models=$models->where('type','in',$type);
        }else{
            $models=$models->where('type',$type);
        }
        return ($list=$models->select()) && count($list) ? $list->toArray():[];
    }
    //获取积分统计头部信息
    public static function getScoreBadgeList($where){
        return [
            [
                'name'=>'总积分',
                'field'=>'个',
                'count'=>self::getModelTime($where,new self())->where('category','integral')->where('type','in',['gain','system_sub','deduction','sign'])->sum('number'),
                'background_color'=>'layui-bg-blue',
                'col'=>4,
            ],
            [
                'name'=>'已使用积分',
                'field'=>'个',
                'count'=>self::getModelTime($where,new self())->where('category','integral')->where('type','deduction')->sum('number'),
                'background_color'=>'layui-bg-cyan',
                'col'=>4,
            ],
            [
                'name'=>'未使用积分',
                'field'=>'个',
                'count'=>self::getModelTime($where,db('user'))->sum('integral'),
                'background_color'=>'layui-bg-cyan',
                'col'=>4,
            ],
        ];
    }
    //获取积分统计曲线图和柱状图
    public static function getScoreCurve($where){
        //发放积分趋势图
         $list=self::getModelTime($where,self::where('category','integral')
            ->field(['FROM_UNIXTIME(add_time,"%Y-%m-%d") as _add_time','sum(number) as sum_number'])
            ->group('_add_time')->order('_add_time asc'))->select()->toArray();
         $date=[];
         $zoom='';
         $seriesdata=[];
         foreach ($list as $item){
             $date[]=$item['_add_time'];
             $seriesdata[]=$item['sum_number'];
         }
         unset($item);
         if(count($date)>$where['limit']){
             $zoom=$date[$where['limit']-5];
         }
        //使用积分趋势图
        $deductionlist=self::getModelTime($where,self::where('category','integral')->where('type','deduction')
            ->field(['FROM_UNIXTIME(add_time,"%Y-%m-%d") as _add_time','sum(number) as sum_number'])
            ->group('_add_time')->order('_add_time asc'))->select()->toArray();
         $deduction_date=[];
         $deduction_zoom='';
         $deduction_seriesdata=[];
         foreach ($deductionlist as $item){
             $deduction_date[]=$item['_add_time'];
             $deduction_seriesdata[]=$item['sum_number'];
         }
         if(count($deductionlist)>$where['limit']){
             $deduction_zoom=$deductionlist[$where['limit']-5];
         }
         return compact('date','seriesdata','zoom','deduction_date','deduction_zoom','deduction_seriesdata');
    }
}