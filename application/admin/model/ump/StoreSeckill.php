<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\ump;

use app\admin\model\order\StoreOrder;
use app\admin\model\store\StoreProductRelation;
use app\admin\model\system\SystemConfig;
use traits\ModelTrait;
use basic\ModelBasic;
use app\admin\model\store\StoreProduct;
use service\PHPExcelService;

/**
 * Class StoreSeckill
 * @package app\admin\model\store
 */
class StoreSeckill extends ModelBasic
{
    use ModelTrait;

   /**
     * 秒杀产品过滤条件
     * @param $model
     * @param $type
     * @return mixed
     */
    public static function setWhereType($model,$type){
        switch ($type){
            case 1:
                $data = ['status'=>0,'is_del'=>0];
                break;
            case 2:
                $data = ['status'=>1,'is_del'=>0];
                break;
            case 3:
                $data = ['status'=>1,'is_del'=>0,'stock'=>0];
                break;
            case 4:
                $data = ['status'=>1,'is_del'=>0,'stock'=>['elt',1]];
                break;
            case 5:
                $data = ['is_del'=>1];
                break;
        }
        if(isset($data)) $model = $model->where($data);
        return $model;
    }

    /**
     * 秒杀产品数量 图标展示
     * @param $type
     * @param $data
     * @return array
     */
    public static function getChatrdata($type,$data){
        $legdata = ['销量','数量','点赞','收藏'];
        $model = self::setWhereType(self::order('id desc'),$type);
        $list = self::getModelTime(compact('data'),$model)
            ->field('FROM_UNIXTIME(add_time,"%Y-%c-%d") as un_time,count(id) as count,sum(sales) as sales')
            ->group('un_time')
            ->distinct(true)
            ->select()
            ->each(function($item) use($data){
                $item['collect']=self::getModelTime(compact('data'),new StoreProductRelation)->where(['type'=>'collect'])->count();
                $item['like']=self::getModelTime(compact('data'),new StoreProductRelation())->where(['type'=>'like'])->count();
            })->toArray();
        $chatrList=[];
        $datetime=[];
        $data_item=[];
        $itemList=[0=>[],1=>[],2=>[],3=>[]];
        foreach ($list as $item){
            $itemList[0][]=$item['sales'];
            $itemList[1][]=$item['count'];
            $itemList[2][]=$item['like'];
            $itemList[3][]=$item['collect'];
            array_push($datetime,$item['un_time']);
        }
        foreach ($legdata as $key=>$leg){
            $data_item['name']=$leg;
            $data_item['type']='line';
            $data_item['data']=$itemList[$key];
            $chatrList[]=$data_item;
            unset($data_item);
        }
        unset($leg);
        $badge = self::getbadge(compact('data'),$type);
        $count = self::setWhereType(self::getModelTime(compact('data'),new self()),$type)->count();
        return compact('datetime','chatrList','legdata','badge','count');

    }

    /**
     * 秒杀产品数量
     * @param $where
     * @param $type
     * @return array
     */
    public static function getbadge($where,$type){
        $StoreOrderModel = new StoreOrder();
        $replenishment_num = SystemConfig::getValue('replenishment_num');
        $replenishment_num = $replenishment_num > 0 ? $replenishment_num : 20;
        $stock1 = self::getModelTime($where,new self())->where('stock','<',$replenishment_num)->column('stock');
        $sum_stock = self::where('stock','<',$replenishment_num)->column('stock');
        $stk=[];
        foreach ($stock1 as $item){
            $stk[]=$replenishment_num-$item;
        }
        $lack=array_sum($stk);
        $sum=[];
        foreach ($sum_stock as $val){
            $sum[]=$replenishment_num-$val;
        }
        return [
            [
                'name'=>'商品数量',
                'field'=>'件',
                'count'=>self::setWhereType(new self(),$type)->where('add_time','<',mktime(0,0,0,date('m'),date('d'),date('Y')))->sum('stock'),
                'content'=>'商品数量总数',
                'background_color'=>'layui-bg-blue',
                'sum'=>self::sum('stock'),
                'class'=>'fa fa fa-ioxhost',
            ],
            [
                'name'=>'新增商品',
                'field'=>'件',
                'count'=>self::setWhereType(self::getModelTime($where,new self),$type)->sum('stock'),
                'content'=>'新增商品总数',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::where('status',1)->sum('stock'),
                'class'=>'fa fa-line-chart',
            ],
            [
                'name'=>'活动商品',
                'field'=>'件',
                'count'=>self::getModelTime($where,$StoreOrderModel)->where('seckill_id','NEQ',0)->sum('total_num'),
                'content'=>'活动商品总数',
                'background_color'=>'layui-bg-green',
                'sum'=>$StoreOrderModel->sum('total_num'),
                'class'=>'fa fa-bar-chart',
            ],
            [
                'name'=>'缺货商品',
                'field'=>'件',
                'count'=>$lack,
                'content'=>'总商品数量',
                'background_color'=>'layui-bg-orange',
                'sum'=>array_sum($sum),
                'class'=>'fa fa-cube',
            ],
        ];
    }

    /**
     * 销量排行 top 10
     * layui-bg-red 红 layui-bg-orange 黄 layui-bg-green 绿 layui-bg-blue 蓝 layui-bg-cyan 黑
     */
    public static function getMaxList($where){
        $classs=['layui-bg-red','layui-bg-orange','layui-bg-green','layui-bg-blue','layui-bg-cyan'];
        $model=StoreOrder::alias('a')->join('__STORE_SECKILL__ b','b.id=a.seckill_id')->where('a.paid',1);
        $list=self::getModelTime($where,$model,'a.add_time')->group('a.seckill_id')->order('p_count desc')->limit(10)
            ->field(['count(a.seckill_id) as p_count','b.title as store_name','sum(b.price) as sum_price'])->select();
        if(count($list)) $list=$list->toArray();
        $maxList=[];
        $sum_count=0;
        $sum_price=0;
        foreach ($list as $item){
            $sum_count+=$item['p_count'];
            $sum_price=bcadd($sum_price,$item['sum_price'],2);
        }
        unset($item);
        foreach ($list as $key=>&$item){
            $item['w']=bcdiv($item['p_count'],$sum_count,2)*100;
            $item['class']=isset($classs[$key]) ?$classs[$key]:( isset($classs[$key-count($classs)]) ? $classs[$key-count($classs)]:'');
            $item['store_name']=self::getSubstrUTf8($item['store_name']);
        }
        $maxList['sum_count']=$sum_count;
        $maxList['sum_price']=$sum_price;
        $maxList['list']=$list;
        return $maxList;
    }

    /**
     * 获取秒杀利润
     * @param $where
     * @return array
     */
    public static function ProfityTop10($where){
        $classs=['layui-bg-red','layui-bg-orange','layui-bg-green','layui-bg-blue','layui-bg-cyan'];
        $model = StoreOrder::alias('a')->join('__STORE_SECKILL__ b','b.id = a.seckill_id')->where('a.paid',1);
        $list=self::getModelTime($where,$model,'a.add_time')->group('a.seckill_id')->order('profity desc')->limit(10)
            ->field(['count(a.seckill_id) as p_count','b.title as store_name','sum(b.price) as sum_price','(b.price-b.cost) as profity'])
            ->select();
        if(count($list)) $list=$list->toArray();
        $maxList=[];
        $sum_count=0;
        $sum_price=0;
        foreach ($list as $item){
            $sum_count+=$item['p_count'];
            $sum_price=bcadd($sum_price,$item['sum_price'],2);
        }
        foreach ($list as $key=>&$item){
            $item['w']=bcdiv($item['sum_price'],$sum_price,2)*100;
            $item['class']=isset($classs[$key]) ?$classs[$key]:( isset($classs[$key-count($classs)]) ? $classs[$key-count($classs)]:'');
            $item['store_name']=self::getSubstrUTf8($item['store_name'],30);
        }
        $maxList['sum_count']=$sum_count;
        $maxList['sum_price']=$sum_price;
        $maxList['list']=$list;
        return $maxList;
    }

    /**
     * 获取秒杀缺货
     * @param $where
     * @return array
     */
    public static function getLackList($where){
        $replenishment_num = SystemConfig::getValue('replenishment_num');
        $replenishment_num = $replenishment_num > 0 ? $replenishment_num : 20;
        $list=self::where('stock','<',$replenishment_num)->field(['id','title as store_name','stock','price'])->page((int)$where['page'],(int)$where['limit'])->order('stock asc')->select();
        if(count($list)) $list=$list->toArray();
        $count=self::where('stock','<',$replenishment_num)->count();
        return ['count'=>$count,'data'=>$list];
    }

    /**
     * 秒杀产品评价
     * @param array $where
     * @return array
     */
    public static function getNegativeList($where = array()){
        $replenishment_num = 3;
        return [];
    }

    /**
     * 秒杀产品退货
     * @param array $where
     * @return mixed
     */
    public static function getBargainRefundList($where = array()){
        $model = StoreOrder::alias('a')->join('__STORE_SECKILL__ b','b.id=a.seckill_id');
        $list = self::getModelTime($where,$model,'a.add_time')->where('a.refund_status','NEQ',0)->group('a.seckill_id')->order('count desc')->page((int)$where['page'],(int)$where['limit'])
            ->field(['count(a.seckill_id) as count','b.title as store_name','sum(b.price) as sum_price'])->select();
        if(count($list)) $list=$list->toArray();
        return $list;
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where){
        $model = new self;
        $model = $model->alias('s');
//        $model = $model->join('StoreProduct p','p.id=s.product_id');
        if($where['status'] != '')  $model = $model->where('s.status',$where['status']);
        if($where['store_name'] != '') $model = $model->where('s.title|s.id','LIKE',"%$where[store_name]%");
        $model = $model->page(bcmul($where['page'],$where['limit'],0),$where['limit']);
        $model = $model->order('s.id desc');
        $model = $model->where('s.is_del',0);
        return self::page($model,function($item){
            $item['store_name'] = StoreProduct::where('id',$item['product_id'])->value('store_name');
            if($item['status']){
                if($item['start_time'] > time())
                    $item['start_name'] = '活动未开始';
                else if($item['stop_time'] < time())
                    $item['start_name'] = '活动已结束';
                else if($item['stop_time'] > time() && $item['start_time'] < time())
                    $item['start_name'] = '正在进行中';
            }else $item['start_name'] = '关闭';

        },$where,$where['limit']);
    }
    public static function SaveExcel($where){
        $model = new self;
        if($where['status'] != '')  $model = $model->where('status',$where['status']);
        if($where['store_name'] != '') $model = $model->where('title|id','LIKE',"%$where[store_name]%");
        $list = $model->order('id desc')->where('is_del',0)->select();
        count($list) && $list=$list->toArray();
        $excel=[];
        foreach ($list as $item){
            $item['store_name'] = StoreProduct::where('id',$item['product_id'])->value('store_name');
            if($item['status']){
                if($item['start_time'] > time())
                    $item['start_name'] = '活动未开始';
                else if($item['stop_time'] < time())
                    $item['start_name'] = '活动已结束';
                else if($item['stop_time'] > time() && $item['start_time'] < time())
                    $item['start_name'] = '正在进行中';
            }else $item['start_name'] = '关闭';
            $excel[]=[
                $item['id'],
                $item['title'],
                $item['info'],
                $item['ot_price'],
                $item['price'],
                $item['stock'],
                $item['start_name'],
                $item['stop_time'],
                $item['stop_time'],
                $item['status']? '开启':'关闭',
            ];
        }
        PHPExcelService::setExcelHeader(['编号','活动标题','活动简介','原价','秒杀价','库存','秒杀状态','结束时间','状态'])
            ->setExcelTile('秒杀产品导出',' ',' 生成时间：'.date('Y-m-d H:i:s',time()))
            ->setExcelContent($excel)
            ->ExcelSave();
    }

    /**
     * 获取秒杀产品id
     * @return array
     */
    public static function getSeckillIdAll(){
        return self::where('is_del',0)->column('id','id');
    }

    /**
     * 获取秒杀的所有产品
     * @return int|string
     */
    public static function getSeckillCount(){
        return self::where('is_del',0)->count();
    }
}