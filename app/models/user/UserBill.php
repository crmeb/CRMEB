<?php
/**
 * Created by CRMEB.
 * Copyright (c) 2017~2019 http://www.crmeb.com All rights reserved.
 * Author: liaofei <136327134@qq.com>
 * Date: 2019/3/27 21:44
 */
namespace app\models\user;

use think\facade\Cache;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * TODO 用户消费新增金额明细 model
 * Class UserBill
 * @package app\models\user
 */
class UserBill extends BaseModel
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
    protected $name = 'user_bill';

    use ModelTrait;

    public static function income($title,$uid,$category,$type,$number,$link_id = 0,$balance = 0,$mark = '',$status = 1)
    {
        $pm = 1;
        $add_time = time();
        return self::create(compact('title','uid','link_id','category','type','number','balance','mark','status','pm','add_time'));
    }

    public static function expend($title,$uid,$category,$type,$number,$link_id = 0,$balance = 0,$mark = '',$status = 1)
    {
        $pm = 0;
        $add_time = time();
        return self::create(compact('title','uid','link_id','category','type','number','balance','mark','status','pm','add_time'));
    }

    /**
     * 积分/佣金 使用记录
     * @param $uid
     * @param $page
     * @param $limit
     * @param string $category
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function userBillList($uid, $page, $limit, $category='integral')
    {
        if($page){
            $list = self::where('uid',$uid)
                ->where('category',$category)
                ->field('mark,pm,number,add_time')
                ->where('status',1)
                ->order('add_time DESC')
                ->page((int)$page,(int)$limit)
                ->select();
        }else{
            $list = self::where('uid',$uid)
                ->where('category',$category)
                ->field('mark,pm,number,add_time')
                ->where('status',1)
                ->order('add_time DESC')
                ->select();
        }
        $list = count($list) ? $list->toArray() : [];
        foreach ($list as &$v){
            $v['add_time'] = date('Y/m/d H:i',$v['add_time']);
            $v['number'] = floatval($v['number']);
        }
        return $list;
    }

    /**
     * 获取昨日佣金
     * @param $uid
     * @return float
     */
    public static function yesterdayCommissionSum($uid)
    {
        return self::where('uid',$uid)->where('category','now_money')->where('type','brokerage')->where('pm',1)
            ->where('status',1)->whereTime('add_time', 'yesterday')->sum('number');
    }

    /**
     * 获取总佣金
     * @param $uid
     * @return float
     */
    public static function getBrokerage($uid)
    {
        return self::where('uid',$uid)->where('category','now_money')->where('type','brokerage')->where('pm',1)
            ->where('status',1)->sum('number');
    }

    /**
     * 获取后台添加的余额
     * @param $uid
     * @return float
     */
    public static function getSystemAdd($uid)
    {
        return self::where('uid',$uid)->where('category','now_money')->where('type','system_add')->where('pm',1)
            ->where('status',1)->sum('number');
    }


    /**
     * 累计充值
     * @param $uid
     * @return float
     */
    public static function getRecharge($uid)
    {
        return self::where('uid', $uid)
            ->where('category', 'now_money')
            ->where('type', 'recharge')
            ->where('pm', 1)
            ->where('status', 1)
            ->sum('number');
    }

    /*
     * 获取用户账单明细
     * @param int $uid 用户uid
     * @param int $page 页码
     * @param int $limit 展示多少条
     * @param int $type 展示类型
     * @return array
     * */
    public static function getUserBillList($uid,$page,$limit,$type)
    {
        if(!$limit) return [];
        $model=self::where('uid',$uid)->where('category','now_money')->order('add_time desc')
            ->field('FROM_UNIXTIME(add_time,"%Y-%m") as time,group_concat(id SEPARATOR ",") ids')->group('time');
        switch ((int)$type){
            case 0:
                $model=$model->where('type','in','recharge,brokerage,pay_product,system_add,pay_product_refund,system_sub');
                break;
            case 1:
                $model=$model->where('type','pay_product');
                break;
            case 2:
                $model=$model->where('type','recharge');
                break;
            case 3:
                $model=$model->where('type','brokerage');
                break;
            case 4:
                $model=$model->where('type','extract');
                break;
        }
        if($page) $model = $model->page((int)$page,(int)$limit);
        $list = ($list = $model->select()) ? $list->toArray() : [];
        $data = [];
        foreach ($list as $item){
            $value['time'] = $item['time'];
            $value['list'] = self::where('id','in',$item['ids'])->field('FROM_UNIXTIME(add_time,"%Y-%m-%d %H:%i") as add_time,title,number,pm')->order('add_time DESC')->select();
            array_push($data,$value);
        }
        return $data;
    }

    /**
     * TODO 获取用户记录 按月查找
     * @param $uid $uid  用户编号
     * @param int $page $page 分页起始值
     * @param int $limit $limit 查询条数
     * @param string $category $category 记录类型
     * @param string $type $type 记录分类
     * @return mixed
     */
    public static function getRecordList($uid, $page = 1, $limit = 8, $category = 'now_money', $type = ''){
        $model = new self;
        $model = $model->field("FROM_UNIXTIME(add_time, '%Y-%m') as time");
        $model = $model->where('uid', $uid);
        if(strlen(trim($type))) $model = $model->whereIn('type', $type);
        $model = $model->where('category', $category);
        $model = $model->group("FROM_UNIXTIME(add_time, '%Y-%m')");
        $model = $model->order('time desc');
        $model = $model->page($page, $limit);
        return $model->select();
    }

    /**
     * TODO  按月份查找用户记录
     * @param $uid $uid  用户编号
     * @param int $addTime $addTime 月份
     * @param string $category $category 记录类型
     * @param string $type $type 记录分类
     * @return mixed
     */
    public static function getRecordListDraw($uid, $addTime = 0,$category = 'now_money',$type = ''){
        if(!$uid) [];
        $model = new self;
        $model = $model->field("title,FROM_UNIXTIME(add_time, '%Y-%m-%d %H:%i') as time,number,pm");
        $model = $model->where('uid',$uid);
        $model = $model->where("FROM_UNIXTIME(add_time, '%Y-%m')= '{$addTime}'");
        $model = $model->where('category',$category);
        if(strlen(trim($type))) $model = $model->where('type','in',$type);
        $model = $model->order('add_time desc');
        $list = $model->select();
        if($list) return $list->toArray();
        else [];
    }

    /**
     * TODO 获取订单返佣记录
     * @param $uid
     * @param int $addTime
     * @param string $category
     * @param string $type
     * @return mixed
     */
    public static function getRecordOrderListDraw($uid, $addTime = 0,$category = 'now_money',$type = 'brokerage'){
        if(!strlen(trim($uid))) [];
        $model = new self;
        $model = $model->field("o.order_id,FROM_UNIXTIME(b.add_time, '%Y-%m-%d %H:%i') as time,b.number,u.avatar,u.nickname");
        $model = $model->alias('b');
        $model = $model->join('StoreOrder o','o.id=b.link_id');
        $model = $model->join('User u','u.uid=o.uid','right');
        $model = $model->where('b.uid', $uid);
        $model = $model->where("FROM_UNIXTIME(b.add_time, '%Y-%m')= '{$addTime}'");
        $model = $model->where('b.category',$category);
        $model = $model->whereIn('b.type', $type);
        $model = $model->order('time desc');
//        dump($model);exit();
        $list = $model->select();
        if($list) return $list->toArray();
        else [];
    }

    /**
     * TODO 获取用户记录总和
     * @param $uid
     * @param string $category
     * @param string $type
     * @return mixed
     */
    public static function getRecordCount($uid, $category = 'now_money', $type = '',$time=''){
        $model = new self;
        $model = $model->where('uid', $uid);
        $model = $model->where('category',$category);
        $model = $model->where('status',1);
        if(strlen(trim($type))) $model = $model->where('type','in',$type);
        if($time) $model=$model->whereTime('add_time',$time);
        return $model->sum('number');
    }

    /**
     * TODO 获取订单返佣记录总数
     * @param $uid
     * @param string $category
     * @param string $type
     * @return mixed
     */
    public static function getRecordOrderCount($uid, $category = 'now_money', $type = 'brokerage'){
        $model = new self;
        $model = $model->where('uid', $uid);
        $model = $model->where('category',$category);
        if(strlen(trim($type))) $model = $model->whereIn('type', $type);
        return $model->count();
    }

    /*
     * 记录分享次数
     * @param int $uid 用户uid
     * @param int $cd 冷却时间
     * @return Boolean
     * */
    public static function setUserShare($uid,$cd=300){
        $user=User::where('uid',$uid)->find();
        if(!$user) return self::setErrorInfo('用户不存在！');
        $cachename='Share_'.$uid;
        if(Cache::has($cachename)) return false;
        self::income('用户分享记录',$uid,'share','share',1,0,0,date('Y-m-d H:i:s',time()).':用户分享');
        Cache::set($cachename,1,$cd);
        event('UserLevelAfter',[$user]);
        return true;
    }

}
