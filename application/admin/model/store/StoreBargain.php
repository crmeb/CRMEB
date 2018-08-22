<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16 0016
 * Time: 11:07
 */

namespace app\admin\model\store;

use traits\ModelTrait;
use basic\ModelBasic;
use service\PHPExcelService;

/**
 * 砍价Model
 * Class StoreBargain
 * @package app\admin\model\store
 */
class StoreBargain extends ModelBasic
{
    use ModelTrait;

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where){
        $model = new self;
        if($where['status'] != '')  $model = $model->where('status',$where['status']);
        if($where['store_name'] != ''){
            $model = $model->where('title','LIKE',"%$where[store_name]%");
            $model = $model->whereOr('store_name','LIKE',"%$where[store_name]%");
        }
        if($where['data'] != '') $model = $model->whereTime('add_time', 'between', explode('-',$where['data']));
        $model = $model->order('id desc');
        $model = $model->where('is_del',0);
        if($where['export'] == 1){
            $list = $model->select()->toArray();
            $export = [];
            foreach ($list as $index=>$item){
                $export[] = [
                    $item['title'],
                    $item['info'],
                    $item['store_name'],
                    '￥'.$item['price'],
                    '￥'.$item['cost'],
                    $item['num'],
                    '￥'.$item['bargain_max_price'],
                    '￥'.$item['bargain_min_price'],
                    $item['bargain_num'],
                    $item['status'] ? '开启' : '关闭',
                    date('Y-m-d H:i:s',$item['start_time']),
                    date('Y-m-d H:i:s',$item['stop_time']),
                    $item['sales'],
                    $item['stock'],
                    $item['give_integral'],
                    date('Y-m-d H:i:s',$item['add_time']),
                ];
                $list[$index] = $item;
            }

            PHPExcelService::setExcelHeader(['砍价活动名称','砍价活动简介','砍价产品名称','砍价金额','成本价','每次购买的砍价产品数量','用户每次砍价的最大金额','用户每次砍价的最小金额',
                '用户每次砍价的次数','砍价状态','砍价开启时间','砍价结束时间','销量','库存','返多少积分','添加时间'])
                ->setExcelTile('砍价产品导出','产品信息'.time(),' 生成时间：'.date('Y-m-d H:i:s',time()))
                ->setExcelContent($export)
                ->ExcelSave();
        }
        return self::page($model,function($item){
            if($item['status']){
                if($item['start_time'] > time())
                    $item['start_name'] = '活动未开始';
                else if($item['stop_time'] < time())
                    $item['start_name'] = '活动已结束';
                else if($item['stop_time'] > time() && $item['start_time'] < time())
                    $item['start_name'] = '正在进行中';
            }

        },$where);
    }
}