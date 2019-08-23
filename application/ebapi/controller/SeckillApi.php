<?php
namespace app\ebapi\controller;

use app\core\util\SystemConfigService;
use app\ebapi\model\store\StoreProductRelation;
use app\ebapi\model\store\StoreProductReply;
use app\ebapi\model\store\StoreSeckill;
use app\core\util\GroupDataService;
use service\JsonService;
use service\UtilService;


/**
 * 小程序秒杀api接口
 * Class SeckillApi
 * @package app\ebapi\controller
 *
 */
class SeckillApi extends AuthController
{
    /**
     * 秒杀列表页
     * @return \think\response\Json
     */
    public function seckill_index(){
        $seckillTime = GroupDataService::getData('routine_seckill_time')?:[];//秒杀时间段
        $seckillTimeIndex = 0;
        if(count($seckillTime)){
            foreach($seckillTime as $key=>&$value){
                $currentHour = date('H');
                $activityEndHour = bcadd((int)$value['time'],(int)$value['continued'],0);
                if($activityEndHour > 24){
                    $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'].':00' : '0'.(int)$value['time'].':00';
                    $value['state'] = '即将开始';
                    $value['status'] = 2;
                    $value['stop'] = bcadd(strtotime(date('Y-m-d')),bcmul($activityEndHour,3600,0));
                }else{
                    if($currentHour >= (int)$value['time'] && $currentHour < $activityEndHour){
                        $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'].':00' : '0'.(int)$value['time'].':00';
                        $value['state'] = '抢购中';
                        $value['stop'] = bcadd(strtotime(date('Y-m-d')),bcmul($activityEndHour,3600,0));
                        $value['status'] = 1;
                        if(!$seckillTimeIndex) $seckillTimeIndex = $key;
                    }else if($currentHour < (int)$value['time']){
                        $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'].':00' : '0'.(int)$value['time'].':00';
                        $value['state'] = '即将开始';
                        $value['status'] = 2;
                        $value['stop'] = bcadd(strtotime(date('Y-m-d')),bcmul($activityEndHour,3600,0));
                    }else if($currentHour >= $activityEndHour){
                        $value['time'] = strlen((int)$value['time']) == 2 ? (int)$value['time'].':00' : '0'.(int)$value['time'].':00';
                        $value['state'] = '已结束';
                        $value['status'] = 0;
                        $value['stop'] = bcadd(strtotime(date('Y-m-d')),bcmul($activityEndHour,3600,0));
                    }
                }
            }
        }
        $data['lovely'] = SystemConfigService::get('seckill_header_banner');
        if(strstr($data['lovely'],'http')===false) $data['lovely']=SystemConfigService::get('site_url').$data['lovely'];
        $data['lovely'] = str_replace('\\','/',$data['lovely']);
        $data['seckillTime'] = $seckillTime;
        $data['seckillTimeIndex'] = $seckillTimeIndex;
        return JsonService::successful($data);
    }

    public function seckill_list(){
        $data = UtilService::postMore([['time',0],['offset',0],['limit',20]]);
        if(!$data['time']) return JsonService::fail('参数错误');
        $timeInfo = GroupDataService::getDataNumber($data['time']);
        $activityEndHour = bcadd((int)$timeInfo['time'],(int)$timeInfo['continued'],0);
        $startTime = bcadd(strtotime(date('Y-m-d')),bcmul($timeInfo['time'],3600,0));
        $stopTime = bcadd(strtotime(date('Y-m-d')),bcmul($activityEndHour,3600,0));
        $seckillInfo = StoreSeckill::seckillList($startTime,$stopTime,$data['offset'],$data['limit']);
        if(count($seckillInfo)){
            foreach ($seckillInfo as $key=>&$item){
                $percent = (int)bcmul(bcdiv($item['sales'],bcadd($item['stock'],$item['sales'],0),2),100,0);
                $item['percent'] = $percent ? $percent : 10;
            }
        }
        return JsonService::successful($seckillInfo);
    }
    /**
     * 秒杀详情页
     * @param Request $request
     * @return \think\response\Json
     */
    public function seckill_detail(){
        $data = UtilService::postMore(['id']);
        $id = $data['id'];
        if(!$id || !($storeInfo = StoreSeckill::getValidProduct($id))) return JsonService::fail('商品不存在或已下架!');
        $storeInfo['userLike'] = StoreProductRelation::isProductRelation($storeInfo['product_id'],$this->userInfo['uid'],'like','product_seckill');
        $storeInfo['like_num'] = StoreProductRelation::productRelationNum($storeInfo['product_id'],'like','product_seckill');
        $storeInfo['userCollect'] = StoreProductRelation::isProductRelation($storeInfo['product_id'],$this->userInfo['uid'],'collect','product_seckill');
        $storeInfo['uid'] = $this->userInfo['uid'];
        $data['storeInfo'] = $storeInfo;
        setView($this->userInfo['uid'],$id,$storeInfo['product_id'],'viwe');
        $data['reply'] = StoreProductReply::getRecProductReply($storeInfo['product_id']);
        $data['replyCount'] = StoreProductReply::productValidWhere()->where('product_id',$storeInfo['id'])->count();
        return JsonService::successful($data);
    }
}