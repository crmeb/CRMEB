<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\ump;

use think\facade\Db;
use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;
use app\admin\model\store\StoreProductRelation;
use app\admin\model\order\StoreOrder;
use app\admin\model\system\SystemConfig;
use crmeb\services\PHPExcelService;

/**
 * 拼团model
 * Class StoreCombination
 * @package app\admin\model\store
 */
class StoreCombination extends BaseModel
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
    protected $name = 'store_combination';

    use ModelTrait;

    /**
     * 设置拼团 where 条件
     * @param $where
     * @param null $model
     * @return mixed
     */
    public static function setWhere($where,$model=null){
        $model=$model===null? new self():$model;
        $model = $model->alias('c');
        $model = $model->field('c.*,p.store_name,p.price as ot_price');
        $model = $model->join('StoreProduct p','p.id=c.product_id','LEFT');
        if(isset($where['is_show']) && $where['is_show'] != '')  $model = $model->where('c.is_show',$where['is_show']);
        if(isset($where['is_host']) && $where['is_host'] != '')  $model = $model->where('c.is_host',$where['is_host']);
        if(isset($where['store_name']) && $where['store_name'] != '') $model = $model->where('p.store_name|p.id|c.id|c.title','LIKE',"%$where[store_name]%");
        return $model->order('c.id desc')->where('c.is_del',0);
    }
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where){
        $model = self::setWhere($where)->limit(bcmul($where['page'],$where['limit'],0),$where['limit']);
        return self::page($model,function ($item){
            $item['count_people_all'] = StorePink::getCountPeopleAll($item['id']);//参与人数
            $item['count_people_pink'] = StorePink::getCountPeoplePink($item['id']);//成团人数
            $item['count_people_browse'] = self::getVisitPeople($item['id']);//访问人数
        },$where,$where['limit']);
    }

    /**
     * 导出EXCEL表格,并下载
     * @param $where
     */
    public static function SaveExcel($where){
        $list = self::setWhere($where)->select();
        count($list) && $list=$list->toArray();
        $excel=[];
        foreach ($list as $item){
            $item['count_people_all'] = StorePink::getCountPeopleAll($item['id']);//参与人数
            $item['count_people_pink'] = StorePink::getCountPeoplePink($item['id']);//成团人数
            $item['count_people_browse'] = self::getVisitPeople($item['id']);//访问人数
            $item['_stop_time'] = date('Y/m/d H:i:s',$item['stop_time']);
            $excel[]=[
                $item['id'],
                $item['title'],
                $item['ot_price'],
                $item['price'],
                $item['stock'],
                $item['people'],
                $item['count_people_browse'],
                $item['browse'],
                $item['count_people_all'],
                $item['count_people_pink'],
                $item['browse'],
                $item['is_show'],
                $item['_stop_time']
            ];
        }
        PHPExcelService::setExcelHeader(['编号','拼团名称','原价','拼团价','库存','拼团人数','访客人数','展现量','参与人数','成团数量','浏览量','产品状态','结束时间'])
            ->setExcelTile('拼团产品导出',' ',' 生成时间：'.date('Y-m-d H:i:s',time()))
            ->setExcelContent($excel)
            ->ExcelSave();
    }
    /**
     * 获取查看拼团产品人数
     * @param int $combinationId
     * @param string $productType
     * @return mixed
     */
    public static function getVisitPeople($combinationId = 0,$productType = 'combination'){
        $model = Db::name('store_visit');
        $model = $model->where('product_id',$combinationId);
        $model = $model->where('product_type',$productType);
        return $model->count();
    }


    /**
     * 获取查看拼团统计
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getStatistics(){
        $statistics = array();
        $statistics['browseCount'] = self::value('sum(browse) as browse');//总展现量
        $statistics['browseCount'] = $statistics['browseCount'] ? $statistics['browseCount'] : 0;
        $statistics['visitCount'] = Db::name('store_visit')->where('product_type','combination')->count();//访客人数
        $statistics['partakeCount'] = StorePink::getCountPeopleAll();//参与人数
        $statistics['pinkCount'] = StorePink::getCountPeoplePink();//成团数量
        return compact('statistics');
    }

    /**
     * 获取拼团总数
     * @return int|string
     */
    public static function getCombinationCount(){
        return self::where('is_del',0)->count();
    }

    /**
     * 获取拼团产品ID
     * @return array
     */
    public static function getCombinationIdAll(){
        return self::where('is_del',0)->column('id','id');
    }

    /**
     * 获取所有拼团数据
     * @param int $limit
     * @param int $length
     * @return array
     */
    public static function getAll($limit = 0,$length = 0){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price');
        $model = $model->order('c.sort desc,c.id desc');
        $model = $model->where('c.is_show',1);
        $model = $model->where('c.is_del',0);
        $model = $model->where('c.start_time','<',time());
        $model = $model->where('c.stop_time','>',time());
        if($limit && $length) $model = $model->limit($limit,$length);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }
    /**
     * 获取一条拼团数据
     * @param $id
     * @return mixed
     */
    public static function getCombinationOne($id){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.*,s.price as product_price');
        $model = $model->where('c.is_show',1);
        $model = $model->where('c.is_del',0);
        $model = $model->where('c.id',$id);
        $model = $model->where('c.start_time','<',time());
        $model = $model->where('c.stop_time','>',time()-86400);
        $list = $model->find();
        if($list) return $list->toArray();
        else return [];
    }

    /**
     * 获取推荐的拼团产品 移动到公众号
     * @return mixed
     */
    public static function getCombinationHost($limit = 0){
        $model = new self();
        $model = $model->alias('c');
        $model = $model->join('StoreProduct s','s.id=c.product_id');
        $model = $model->field('c.id,c.image,c.price,c.sales,c.title,c.people,s.price as product_price');
        $model = $model->where('c.is_del',0);
        $model = $model->where('c.is_host',1);
        $model = $model->where('c.is_host',1);
        $model = $model->where('c.start_time','<',time());
        $model = $model->where('c.stop_time','>',time());
        if($limit) $model = $model->limit($limit);
        $list = $model->select();
        if($list) return $list->toArray();
        else return [];
    }

    /**
     * 判断库存是否足够 移动到小程序
     * @param $id
     * @param $cart_num
     * @return int|mixed
     */
    public static function getCombinationStock($id,$cart_num){
           $stock = self::where('id',$id)->value('stock');
           return $stock > $cart_num ? $stock : 0;
    }

    /**
     * 获取产品状态 移动到小程序 移动到公众号
     * @param $id
     * @return mixed
     */
    public static function isValidCombination($id){
        $model = new self();
        $model = $model->where('id',$id);
        $model = $model->where('is_del',0);
        $model = $model->where('is_show',1);
        return $model->count();
    }

    /**
     * 修改销量和库存 移动到小程序 移动到公众号
     * @param $num
     * @param $CombinationId
     * @return bool
     */
    public static function decCombinationStock($num,$CombinationId)
    {
        $res = false !== self::where('id',$CombinationId)->dec('stock',$num)->inc('sales',$num)->update();
        return $res;
    }
    /**
     * 拼团产品过滤条件
     * @param $model
     * @param $type
     * @return mixed
     */
    public static function setWhereType($model,$type,$alt=''){
        switch ($type){
            case 1:
                if($alt)
                    $data = [$alt.'.is_del' => 1];
                else
                    $data = ['is_del' => 1];
                break;
            case 2:
                if($alt)
                    $data = [$alt.'.is_host'=>1];
                else
                    $data = ['is_host'=>1];
                break;
            case 3:
                if($alt)
                    $data = [$alt.'.is_show'=>1];
                else
                    $data = ['is_show'=>1];
                break;
            default:
                if($alt)
                    $data=[$alt.'.is_show'=>1,$alt.'.is_del'=>0];
                else
                    $data=['is_show'=>1,'is_del'=>0];
                break;
        }
        if(isset($data)) $model = $model->where($data);
        return $model;
    }
    /**
     * 拼团产品数量
     * @param $where
     * @param $type
     * @return array
     */
    public static function getbadge($where,$type){
        $StoreOrderModel = new StoreOrder();
        $replenishment_num = (int)SystemConfig::getConfigValue('replenishment_num');
        $replenishment_num = $replenishment_num > 0 ? $replenishment_num : 20;
        $stock1 = self::getModelTime($where,new self())->where('stock','<',$replenishment_num)->column('stock','id');
        $sum_stock = self::where('stock','<',$replenishment_num)->column('stock','id');
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
                'name'=>'拼团商品种类',
                'field'=>'件',
                'count'=>self::setWhereType(new self(),$type)->where('add_time','<',mktime(0,0,0,date('m'),date('d'),date('Y')))->count(),
                'content'=>'拼团商品种类总数',
                'background_color'=>'layui-bg-blue',
                'sum'=>self::where('is_show', 1)->where('is_del', 0)->count(),
                'class'=>'fa fa fa-ioxhost',
            ],
            [
                'name'=>'正在拼团商品',
                'field'=>'个',
                'count'=>self::setWhereType(self::getModelTime($where,self::alias('a')->join('StoreProduct t','t.id=a.product_id'),'a.add_time'),$type,'a')
                    ->where('a.start_time','<',time())
                    ->where('a.stop_time','>',time())
                    ->count(),
                'content'=>'正在拼团商品总库存',
                'background_color'=>'layui-bg-cyan',
                'sum'=>self::where('a.start_time','<',time())->alias('a')
                    ->join('StoreProduct t','t.id=a.product_id')
                    ->where('a.stop_time','>',time())->sum('a.stock'),
                'class'=>'fa fa-line-chart',
            ],
            [
                'name'=>'拼团成功订单',
                'field'=>'件',
                'count'=>self::getModelTime($where,$StoreOrderModel)->where('combination_id','<>',0)->sum('total_num'),
                'content'=>'活动商品总数',
                'background_color'=>'layui-bg-green',
                'sum'=>$StoreOrderModel->where('combination_id','<>',0)->sum('total_num'),
                'class'=>'fa fa-bar-chart',
            ],
            [
                'name'=>'拼团缺货商品',
                'field'=>'件',
                'count'=>$lack,
                'content'=>'总商品数量',
                'background_color'=>'layui-bg-orange',
                'sum'=>array_sum($sum),
                'class'=>'fa fa-cube',
            ],
        ];
    }

    public static function getChatrdata($type,$data){
        $legdata = ['销量','数量','点赞','收藏'];
        $model=self::order('id desc');
        $list= self::getModelTime(compact('data'),$model)
            ->field('FROM_UNIXTIME(add_time,"%Y-%c-%d") as un_time,count(id) as count,sum(sales) as sales')
            ->group('un_time')
            ->distinct(true)
            ->select()
            ->each(function($item) use($data){
                $item['collect']=self::getModelTime(compact('data'),new StoreProductRelation)->where('type', 'collect')->count();
                $item['like']=self::getModelTime(compact('data'),new StoreProductRelation())->where('type', 'like')->count();
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
     * 获取拼团利润
     * @param $where
     * @return array
     */
    public static function ProfityTop10($where){
        $classs=['layui-bg-red','layui-bg-orange','layui-bg-green','layui-bg-blue','layui-bg-cyan'];
        $model = StoreOrder::alias('a')->join('__store_combination__ b','b.id = a.combination_id')->where('a.paid',1);
        $list=self::getModelTime($where,$model,'a.add_time')->group('a.seckill_id')->order('profity desc')->limit(10)
            ->field('count(a.combination_id) as p_count,b.title as store_name,sum(b.price) as sum_price,(b.price-b.cost) as profity')
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
    public static function getMaxList($where){
        $classs=['layui-bg-red','layui-bg-orange','layui-bg-green','layui-bg-blue','layui-bg-cyan'];
        $model=StoreOrder::alias('a')->join('__store_combination__ b','b.id=a.combination_id')->where('a.paid',1);
        $list=self::getModelTime($where,$model,'a.add_time')->group('a.combination_id')->order('p_count desc')->limit(10)
            ->field('count(a.combination_id) as p_count,b.title as store_name,sum(b.price) as sum_price')->select();
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
     * 拼团产品退货
     * @param array $where
     * @return mixed
     */
    public static function getBargainRefundList($where = array()){
        $model = StoreOrder::alias('a')->join('__store_combination__ b','b.id=a.combination_id');
        $list = self::getModelTime($where,$model,'a.add_time')->where('a.refund_status','<>',0)->group('a.combination_id')
            ->order('count desc')->page((int)$where['page'],(int)$where['limit'])
            ->field('count(a.combination_id) as count,b.title as store_name,sum(b.price) as sum_price')
            ->select();
        if(count($list)) $list=$list->toArray();
        return $list;
    }

    /**
     * TODO 获取某个字段值
     * @param $id
     * @param string $field
     * @return mixed
     */
    public static function getCombinationField($id,$field = 'title'){
        return self::where('id',$id)->value($field);
    }

}