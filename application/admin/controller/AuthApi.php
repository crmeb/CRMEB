<?php

namespace app\admin\controller;

use app\admin\model\ump\StoreBargain;
use app\admin\model\ump\StoreCombination;
use app\admin\model\ump\StoreSeckill;
use basic\SystemBasic;
use app\admin\model\store\StoreProduct;
use service\JsonService;
use service\UtilService;

/**
 * 异步请求控制器
 * Class AuthApi
 * @package app\admin\controller
 */
class AuthApi extends SystemBasic
{
    /**
     * 获取砍价产品曲线图数据
     */
    public function get_echarts_product($type='',$data='',$model = 0){
        if(!$model) return JsonService::successful(StoreBargain::getChatrdata($type,$data));
        if($model) return JsonService::successful(StoreSeckill::getChatrdata($type,$data));
    }
    /**
     * 获取销量
     */
    public function get_echarts_maxlist($data='',$model = 0){
        if(!$model) return JsonService::successful(StoreBargain::getMaxList(compact('data')));
        if($model) return JsonService::successful(StoreSeckill::getMaxList(compact('data')));
    }
    /**
     * 获取利润
     */
    public function get_echarts_profity($data='',$model = 0){
        if(!$model) return JsonService::successful(StoreBargain::ProfityTop10(compact('data')));
        if($model) return JsonService::successful(StoreSeckill::ProfityTop10(compact('data')));
    }
    /**
     * 获取补货的砍价产品
     */
    public function getLackList($model = 0){
        $where = UtilService::getMore([
            ['page',1],
            ['limit',20],
        ]);
        if(!$model) return JsonService::successlayui(StoreBargain::getLackList($where));
        if($model) return JsonService::successlayui(StoreSeckill::getLackList($where));
    }
    /**
     * 获取砍价产品的评论
     */
    public function getnegativelist($model = 0){
        $where = UtilService::getMore([
            ['page',1],
            ['limit',20],
        ]);
        if(!$model) return JsonService::successlayui(StoreBargain::getNegativeList($where));
        if($model) return JsonService::successlayui(StoreSeckill::getNegativeList($where));
    }
    /**
     * 获取砍价产品的退货
     */
    public function get_bargain_refund_list($model = 0){
        $where = UtilService::getMore([
            ['page',1],
            ['limit',20],
        ]);
        if(!$model) return JsonService::successlayui(StoreBargain::getBargainRefundList($where));
        if($model) return JsonService::successlayui(StoreSeckill::getBargainRefundList($where));
    }

    /**
     * 修改拼团状态
     * @param $status
     * @param int $idd
     */
    public function set_combination_status($status,$id = 0){
        if(!$id) return JsonService::fail('参数错误');
        $res = StoreCombination::edit(['is_show'=>$status],$id);
        if($res) return JsonService::successful('修改成功');
        else return JsonService::fail('修改失败');
    }

    /**
     * 修改砍价状态
     * @param $status
     * @param int $id
     */
    public function set_bargain_status($status,$id = 0){
        if(!$id) return JsonService::fail('参数错误');
        $res = StoreBargain::edit(['status'=>$status],$id);
        if($res) return JsonService::successful('修改成功');
        else return JsonService::fail('修改失败');
    }
    /**
     * 修改秒杀产品状态
     * @param $status
     * @param int $id
     */
    public function set_seckill_status($status,$id = 0){
        if(!$id) return JsonService::fail('参数错误');
        $res = StoreSeckill::edit(['status'=>$status],$id);
        if($res) return JsonService::successful('修改成功');
        else return JsonService::fail('修改失败');
    }
}


