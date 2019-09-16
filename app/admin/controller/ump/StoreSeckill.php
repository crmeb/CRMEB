<?php

namespace app\admin\controller\ump;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use crmeb\services\UploadService as Upload;
use app\admin\model\store\StoreProduct as ProductModel;
use think\facade\Route as Url;
use app\admin\model\ump\StoreSeckillAttr;
use app\admin\model\ump\StoreSeckillAttrResult;
use app\admin\model\ump\StoreSeckill as StoreSeckillModel;
use app\admin\model\system\SystemAttachment;

/**
 * 限时秒杀  控制器
 * Class StoreSeckill
 * @package app\admin\controller\store
 */
class StoreSeckill extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = StoreSeckillModel::class;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->assign('countSeckill',StoreSeckillModel::getSeckillCount());
        $this->assign('seckillId',StoreSeckillModel::getSeckillIdAll());
        return $this->fetch();
    }
    public function save_excel(){
        $where=Util::getMore([
            ['status',''],
            ['store_name','']
        ]);
        StoreSeckillModel::SaveExcel($where);
    }
    /**
     * 异步获取砍价数据
     */
    public function get_seckill_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['status',''],
            ['store_name','']
        ]);
        $seckillList = StoreSeckillModel::systemPage($where);
        if(is_object($seckillList['list'])) $seckillList['list'] = $seckillList['list']->toArray();
        $data = $seckillList['list']['data'];
        foreach ($data as $k=>$v){
            $data[$k]['_stop_time'] =$v['stop_time'] ?  date('Y/m/d H:i:s',$v['stop_time']) : '';
        }
        return Json::successlayui(['count'=>$seckillList['list']['total'],'data'=>$data]);
    }

    public function get_seckill_id(){
        return Json::successlayui(StoreSeckillModel::getSeckillIdAll());
    }
    /**
     * 添加秒杀产品
     * @return form-builder
     */
    public function create()
    {
        $f = array();
        $f[] = Form::input('title','产品标题');
        $f[] = Form::input('info','秒杀活动简介')->type('textarea');
        $f[] = Form::input('unit_name','单位')->placeholder('个、位');
        $f[] = Form::dateTimeRange('section_time','活动时间');
        $f[] = Form::frameImageOne('image','产品主图片(305*305px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'image')))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images','产品轮播图(640*640px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price','秒杀价')->min(0)->col(12);
        $f[] = Form::number('ot_price','原价')->min(0)->col(12);
        $f[] = Form::number('cost','成本价')->min(0)->col(12);
        $f[] = Form::number('stock','库存')->min(0)->precision(0)->col(12);
        $f[] = Form::number('sales','销量')->min(0)->precision(0)->col(12);
        $f[] = Form::number('sort','排序')->col(12);
        $f[] = Form::number('num','单次购买产品个数')->precision(0)->col(12);
        $f[] = Form::number('give_integral','赠送积分')->min(0)->precision(0)->col(12);
        $f[] = Form::number('postage','邮费')->min(0)->col(12);
        $f[] = Form::radio('is_postage','是否包邮',1)->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(12);
        $f[] = Form::radio('is_hot','热门推荐',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $f[] = Form::radio('status','活动状态',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $form = Form::make_post_form('添加用户通知',$f,Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存秒杀产品
     * @param int $id
     */
    public function save($id = 0)
    {
        $data = Util::postMore([
            'title',
            'product_id',
            'info',
            'unit_name',
            ['image',''],
            ['images',[]],
            'price',
            'ot_price',
            'cost',
            'sales',
            'stock',
            'sort',
            'give_integral',
            'postage',
            ['section_time',[]],
            ['is_postage',0],
            ['cost',0],
            ['is_hot',0],
            ['status',0],
            ['num',0]
        ]);
        if(!$data['title']) return Json::fail('请输入产品标题');
        if(!$data['unit_name']) return Json::fail('请输入产品单位');
        if(!$data['product_id']) return Json::fail('产品ID不能为空');
        if(count($data['section_time'])<1) return Json::fail('请选择活动时间');
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        unset($data['section_time']);
        if(!$data['image']) return Json::fail('请选择推荐图');
        if(count($data['images'])<1) return Json::fail('请选择轮播图');
        $data['images'] = json_encode($data['images']);
        if($data['price'] == '' || $data['price'] < 0) return Json::fail('请输入产品秒杀售价');
        if($data['ot_price'] == '' || $data['ot_price'] < 0) return Json::fail('请输入产品原售价');
        if($data['cost'] == '' || $data['cost'] < 0) return Json::fail('请输入产品成本价');
        if($data['stock'] == '' || $data['stock'] < 0) return Json::fail('请输入库存');
        if($data['num']<1) return Json::fail('请输入单次秒杀个数');
        if($id){
            $product = StoreSeckillModel::get($id);
            if(!$product) return Json::fail('数据不存在!');
            StoreSeckillModel::edit($data,$id);
            return Json::successful('编辑成功!');
        }else{
            $data['add_time'] = time();
            StoreSeckillModel::create($data);
            return Json::successful('添加成功!');
        }

    }
    /** 开启秒杀
     * @param $id
     * @return mixed|void
     */
    public function seckill($id){
        if(!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('title','产品标题',$product->getData('store_name'));
        $f[] = Form::hidden('product_id',$id);
        $f[] = Form::input('info','秒杀活动简介',$product->getData('store_info'))->type('textarea');
        $f[] = Form::input('unit_name','单位',$product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::dateTimeRange('section_time','活动时间');
        $f[] = Form::frameImageOne('image','产品主图片(305*305px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'image')),$product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images','产品轮播图(640*640px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'images')),json_decode($product->getData('slider_image')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price','秒杀价')->min(0)->col(12);
        $f[] = Form::number('ot_price','原价',$product->getData('price'))->min(0)->col(12);
        $f[] = Form::number('cost','成本价',$product->getData('cost'))->min(0)->col(12);
        $f[] = Form::number('stock','库存',$product->getData('stock'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sales','销量',$product->getData('sales'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sort','排序',$product->getData('sort'))->col(12);
        $f[] = Form::number('num','单次购买产品个数',1)->precision(0)->col(12);
        $f[] = Form::number('give_integral','赠送积分',$product->getData('give_integral'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('postage','邮费',$product->getData('postage'))->min(0)->col(12);
        $f[] = Form::radio('is_postage','是否包邮',$product->getData('is_postage'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(12);
        $f[] = Form::radio('is_hot','热门推荐',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $f[] = Form::radio('status','活动状态',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $form = Form::make_post_form('添加用户通知',$f,Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $res = Upload::image('file','store/product/'.date('Ymd'));
        if(is_array($res)){
            SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],4,$res['image_type'],$res['time']);
            return Json::successful('图片上传成功!',['name'=>$res['name'],'url'=>Upload::pathToUrl($res['thumb_path'])]);
        }else
            return Json::fail($res);
    }


    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $product = StoreSeckillModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::hidden('product_id',$product->getData('product_id'));
        $f[] = Form::input('title','产品标题',$product->getData('title'));
        $f[] = Form::input('info','秒杀活动简介',$product->getData('info'))->type('textarea');
        $f[] = Form::input('unit_name','单位',$product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::dateTimeRange('section_time','活动时间',date('Y-m-d H:i:s', (int)$product->getData('start_time')),date('Y-m-d H:i:s', (int)$product->getData('stop_time')));
        $f[] = Form::frameImageOne('image','产品主图片(305*305px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'image')),$product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images','产品轮播图(640*640px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'images')),json_decode($product->getData('images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price','秒杀价',$product->getData('price'))->min(0)->col(12);
        $f[] = Form::number('ot_price','原价',$product->getData('ot_price'))->min(0)->col(12);
        $f[] = Form::number('cost','成本价',$product->getData('cost'))->min(0)->col(12);
        $f[] = Form::number('stock','库存',$product->getData('stock'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sales','销量',$product->getData('sales'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sort','排序',$product->getData('sort'))->col(12);
        $f[] = Form::number('num','单次购买产品个数',$product->getData('num'))->precision(0)->col(12);
        $f[] = Form::number('give_integral','赠送积分',$product->getData('give_integral'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('postage','邮费',$product->getData('postage'))->min(0)->col(12);
        $f[] = Form::radio('is_postage','是否包邮',$product->getData('is_postage'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(12);
        $f[] = Form::radio('is_hot','热门推荐',$product->getData('is_hot'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $f[] = Form::radio('status','活动状态',$product->getData('status'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $form = Form::make_post_form('添加用户通知',$f,Url::buildUrl('save',compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(!$id) return $this->failed('数据不存在');
        $product = StoreSeckillModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['is_del']) return Json::fail('已删除!');
        $data['is_del'] = 1;
        if(!StoreSeckillModel::edit($data,$id))
            return Json::fail(StoreSeckillModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    public function edit_content($id){
        if(!$id) return $this->failed('数据不存在');
        $seckill = StoreSeckillModel::get($id);
        if(!$seckill) return Json::fail('数据不存在!');
        $this->assign([
            'content'=>StoreSeckillModel::where('id',$id)->value('description'),
            'field'=>'description',
            'action'=>Url::buildUrl('change_field',['id'=>$id,'field'=>'description'])
        ]);
        return $this->fetch('public/edit_content');
    }

    public function change_field($id){
        if(!$id) return $this->failed('数据不存在');
        $seckill = StoreSeckillModel::get($id);
        if(!$seckill) return Json::fail('数据不存在!');
        $data['description'] = request()->post('description');
        $res = StoreSeckillModel::edit($data,$id);
        if($res)
            return Json::successful('添加成功');
        else
            return Json::fail('添加失败');
    }
    /**
     * 属性页面
     * @param $id
     * @return mixed|void
     */
    public function attr($id)
    {
        if(!$id) return $this->failed('数据不存在!');
        $result = StoreSeckillAttrResult::getResult($id);
        $image = StoreSeckillModel::where('id',$id)->value('image');
        $this->assign(compact('id','result','product','image'));
        return $this->fetch();
    }

    /**
     * 生成属性
     * @param int $id
     */
    public function is_format_attr($id = 0){
        if(!$id) return Json::fail('产品不存在');
        list($attr,$detail) = Util::postMore([
            ['items',[]],
            ['attrs',[]]
        ],$this->request,true);
        $product = StoreSeckillModel::get($id);
        if(!$product) return Json::fail('产品不存在');
        $attrFormat = attrFormat($attr)[1];
        if(count($detail)){
            foreach ($attrFormat as $k=>$v){
                foreach ($detail as $kk=>$vv){
                    if($v['detail'] == $vv['detail']){
                        $attrFormat[$k]['price'] = $vv['price'];
                        $attrFormat[$k]['sales'] = $vv['sales'];
                        $attrFormat[$k]['pic'] = $vv['pic'];
                        $attrFormat[$k]['check'] = false;
                        break;
                    }else{
                        $attrFormat[$k]['price'] = '';
                        $attrFormat[$k]['sales'] = '';
                        $attrFormat[$k]['pic'] = $product['image'];
                        $attrFormat[$k]['check'] = true;
                    }
                }
            }
        }else{
            foreach ($attrFormat as $k=>$v){
                $attrFormat[$k]['price'] = $product['price'];
                $attrFormat[$k]['sales'] = $product['stock'];
                $attrFormat[$k]['pic'] = $product['image'];
                $attrFormat[$k]['check'] = false;
            }
        }
        return Json::successful($attrFormat);
    }
    /**
     * 添加 修改属性
     * @param $id
     */
    public function set_attr($id)
    {
        if(!$id) return $this->failed('产品不存在!');
        list($attr,$detail) = Util::postMore([
            ['items',[]],
            ['attrs',[]]
        ],$this->request,true);
        $res = StoreSeckillAttr::createProductAttr($attr,$detail,$id);
        if($res)
            return $this->successful('编辑属性成功!');
        else
            return $this->failed(StoreSeckillAttr::getErrorInfo());
    }

    /**
     * 清除属性
     * @param $id
     */
    public function clear_attr($id)
    {
        if(!$id) return $this->failed('产品不存在!');
        if(false !== StoreSeckillAttr::clearProductAttr($id) && false !== StoreSeckillAttrResult::clearResult($id))
            return $this->successful('清空产品属性成功!');
        else
            return $this->failed(StoreSeckillAttr::getErrorInfo('清空产品属性失败!'));
    }

    /**
     * 修改秒杀产品状态
     * @param $status
     * @param int $id
     */
    public function set_seckill_status($status,$id = 0){
        if(!$id) return Json::fail('参数错误');
        $res = StoreSeckillModel::edit(['status'=>$status],$id);
        if($res) return Json::successful('修改成功');
        else return Json::fail('修改失败');
    }
}
