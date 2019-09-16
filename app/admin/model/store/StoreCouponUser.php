<?php
namespace app\admin\model\store;

use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\facade\Db;

class StoreCouponUser extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_coupon_user';

    use ModelTrait;

    public static function tidyCouponList($couponList)
    {
        $time = time();
        foreach ($couponList as &$coupon){
            $coupon['_add_time'] = date('Y/m/d',$coupon['add_time']);
            $coupon['_end_time'] = date('Y/m/d',$coupon['end_time']);
            $coupon['use_min_price'] = floatval($coupon['use_min_price']);
            $coupon['coupon_price'] = floatval($coupon['coupon_price']);
            if($coupon['is_fail']){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已失效';
            }else if ($coupon['status'] == 1){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已使用';
            }else if ($coupon['status'] == 2){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
            }else if($coupon['add_time'] > $time || $coupon['end_time'] < $time){
                $coupon['_type'] = 0;
                $coupon['_msg'] = '已过期';
            }else{
                if($coupon['add_time']+ 3600*24 > $time){
                    $coupon['_type'] = 2;
                    $coupon['_msg'] = '可使用';
                }else{
                    $coupon['_type'] = 1;
                    $coupon['_msg'] = '可使用';
                }
            }
            $coupon['integral']= Db::name('store_coupon')->where(['id'=>$coupon['cid']])->value('integral');
        }
        return $couponList;
    }
    //获取个人优惠券列表
    public static function getOneCouponsList($where){
        $list=self::where(['uid'=>$where['uid']])->page((int)$where['page'],(int)$where['limit'])->select();
        return self::tidyCouponList($list);
    }
    //获取优惠劵头部信息
    public static function getCouponBadgeList($where){
        return [
            [
                'name'=>'总发放优惠券',
                'field'=>'张',
                'count'=>self::getModelTime($where, Db::name('store_coupon_issue'))->where('status',1)->sum('total_count'),
                'background_color'=>'layui-bg-blue',
                'col'=>6,
            ],
            [
                'name'=>'总使用优惠券',
                'field'=>'张',
                'count'=>self::getModelTime($where,new self())->where('status',1)->count(),
                'background_color'=>'layui-bg-blue',
                'col'=>6,
            ]
        ];
    }
    //获取优惠劵图表
    public static function getConponCurve($where,$limit=20){
        //优惠劵发放记录
        $list=self::getModelTime($where, Db::name('store_coupon_issue')
            ->where('status',1)
            ->field(['FROM_UNIXTIME(add_time,"%Y-%m-%d") as _add_time','sum(total_count) as total_count'])->group('_add_time')->order('_add_time asc'))->select();
        $date=[];
        $seriesdata=[];
        $zoom='';
        foreach ($list as $item){
            $date[]=$item['_add_time'];
            $seriesdata[]=$item['total_count'];
        }
        unset($item);
        if(count($date)>$limit){
            $zoom=$date[$limit-5];
        }
        //优惠劵使用记录
        $componList=self::getModelTime($where,self::where('status',1)->field(['FROM_UNIXTIME(add_time,"%Y-%m-%d") as _add_time','sum(coupon_price) as coupon_price'])
            ->group('_add_time')->order('_add_time asc'))->select();
        count($componList) && $componList=$componList->toArray();
        $compon_date=[];
        $compon_data=[];
        $compon_zoom='';
        foreach($componList as $item){
            $compon_date[]=$item['_add_time'];
            $compon_data[]=$item['coupon_price'];
        }
        if(count($compon_date)>$limit){
            $compon_zoom=$compon_date[$limit-5];
        }
        return compact('date','seriesdata','zoom','compon_date','compon_data','compon_zoom');
    }

}