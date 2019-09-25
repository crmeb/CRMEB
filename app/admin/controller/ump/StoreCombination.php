<?php

namespace app\admin\controller\ump;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use crmeb\services\UploadService as Upload;
use app\admin\model\store\StoreProduct as ProductModel;
use app\admin\model\ump\StoreCombinationAttr;
use app\admin\model\ump\StoreCombinationAttrResult;
use app\admin\model\ump\StoreCombination as StoreCombinationModel;
use think\facade\Route as Url;
use app\admin\model\system\SystemAttachment;
use app\admin\model\ump\StorePink;

/**
 * 拼团管理
 * Class StoreCombination
 * @package app\admin\controller\store
 */
class StoreCombination extends AuthController
{

    use CurdControllerTrait;

    protected $bindModel = StoreCombinationModel::class;

    /**
     * @return mixed
     */
    public function index()
    {
        $this->assign('countCombination',StoreCombinationModel::getCombinationCount());
        $this->assign(StoreCombinationModel::getStatistics());
        $this->assign('combinationId',StoreCombinationModel::getCombinationIdAll());
        return $this->fetch();
    }
    public function save_excel(){
        $where = Util::getMore([
            ['is_show',''],
            ['store_name',''],
        ]);
        StoreCombinationModel::SaveExcel($where);
    }
    /**
     * 异步获取拼团数据
     */
    public function get_combination_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['export',0],
            ['is_show',''],
            ['is_host',''],
            ['store_name','']
        ]);
        $combinationList = StoreCombinationModel::systemPage($where);
        if(is_object($combinationList['list'])) $combinationList['list'] = $combinationList['list']->toArray();
        $data = $combinationList['list']['data'];
        foreach ($data as $k=>$v){
            $data[$k]['_stop_time'] = date('Y/m/d H:i:s',$v['stop_time']);
        }
        return Json::successlayui(['count'=>$combinationList['list']['total'],'data'=>$data]);
    }

    public function combination($id = 0){
        if(!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::hidden('product_id',$id);
//        $f[] = Form::select('product_id','产品名称')->setOptions(function(){
//            $list = ProductModel::getTierList();
//            foreach ($list as $menu){
//                $menus[] = ['value'=>$menu['id'],'label'=>$menu['store_name'].'/'.$menu['id']];
//            }
//            return $menus;
//        })->filterable(1);
        $f[] = Form::input('title','拼团名称',$product->getData('store_name'));
        $f[] = Form::input('info','拼团简介',$product->getData('store_info'))->type('textarea');
        $f[] = Form::input('unit_name','单位',$product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::dateTimeRange('section_time','拼团时间');
        $f[] = Form::frameImageOne('image','产品主图片(305*305px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'image')),$product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images','产品轮播图(640*640px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'images')),json_decode($product->getData('slider_image')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price','拼团价')->min(0)->col(12);
        $f[] = Form::number('people','拼团人数')->min(2)->col(12);
        $f[] = Form::number('stock','库存',$product->getData('stock'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sales','销量',$product->getData('sales'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sort','排序')->col(12);
        $f[] = Form::number('postage','邮费',$product->getData('postage'))->min(0)->col(12);
        $f[] = Form::radio('is_postage','是否包邮',$product->getData('is_postage'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(12);
        $f[] = Form::radio('is_host','热门推荐',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $f[] = Form::radio('is_show','活动状态',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $form = Form::make_post_form('添加用户通知',$f,Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $f = array();
        $f[] = Form::select('product_id','产品名称')->setOptions(function(){
            $list = ProductModel::getTierList();
            foreach ($list as $menu){
                $menus[] = ['value'=>$menu['id'],'label'=>$menu['store_name'].'/'.$menu['id']];
            }
            return $menus;
        })->filterable(1);
        $f[] = Form::input('title','拼团名称');
        $f[] = Form::input('info','拼团简介')->type('textarea');
        $f[] = Form::input('unit_name','单位')->placeholder('个、位');
        $f[] = Form::dateTimeRange('section_time','拼团时间');
        $f[] = Form::frameImageOne('image','产品主图片(305*305px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'image')))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images','产品轮播图(640*640px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price','拼团价')->min(0)->col(12);
        $f[] = Form::number('people','拼团人数')->min(2)->col(12);
        $f[] = Form::number('stock','库存')->min(0)->precision(0)->col(12);
        $f[] = Form::number('sales','销量')->min(0)->precision(0)->col(12);
        $f[] = Form::number('sort','排序')->col(12);
        $f[] = Form::number('postage','邮费')->min(0)->col(12);
        $f[] = Form::radio('is_postage','是否包邮',1)->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(12);
        $f[] = Form::radio('is_host','热门推荐',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $f[] = Form::radio('is_show','活动状态',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $form = Form::make_post_form('添加用户通知',$f,Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     * @param int $id
     */
    public function save($id = 0)
    {
        $data = Util::postMore([
            'product_id',
            'title',
            'info',
            ['unit_name','个'],
            ['image',''],
            ['images',[]],
            ['section_time',[]],
            'postage',
            'price',
            'people',
            'sort',
            'stock',
            'sales',
            ['is_show',0],
            ['is_host',0],
            ['is_postage',0],
        ]);
        if(!$data['title']) return Json::fail('请输入拼团名称');
        if(!$data['info']) return Json::fail('请输入拼团简介');
        if(!$data['image']) return Json::fail('请上传产品图片');
        if(count($data['images'])<1) return Json::fail('请上传产品轮播图');
        if($data['price'] == '' || $data['price'] < 0) return Json::fail('请输入产品售价');
        if($data['people'] == '' || $data['people'] < 1) return Json::fail('请输入拼团人数');
        if(count($data['section_time'])<1) return Json::fail('请选择活动时间');
        if($data['stock'] == '' || $data['stock'] < 0) return Json::fail('请输入库存');
        $data['images'] = json_encode($data['images']);
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        unset($data['section_time']);
        if($id){
            $product = StoreCombinationModel::get($id);
            if(!$product) return Json::fail('数据不存在!');
            $data['product_id']=$product['product_id'];
            StoreCombinationModel::edit($data,$id);
            return Json::successful('编辑成功!');
        }else{
            $data['add_time'] = time();
            $data['description'] = '';
            StoreCombinationModel::create($data);
            return Json::successful('添加拼团成功!');
        }

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
        $product = StoreCombinationModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::hidden('product_id',$product->getData('product_id'));
        $f[] = Form::input('title','拼团名称',$product->getData('title'));
        $f[] = Form::input('info','拼团简介',$product->getData('info'))->type('textarea');
        $f[] = Form::input('unit_name','单位',$product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::dateTimeRange('section_time','拼团时间',date("Y-m-d H:i:s",$product->getData('start_time')),date("Y-m-d H:i:s",$product->getData('stop_time')));
        $f[] = Form::frameImageOne('image','产品主图片(305*305px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'image')),$product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images','产品轮播图(640*640px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'images')),json_decode($product->getData('images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price','拼团价',$product->getData('price'))->min(0)->col(12);
        $f[] = Form::number('people','拼团人数',$product->getData('people'))->min(2)->col(12);
        $f[] = Form::number('stock','库存',$product->getData('stock'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sales','销量',$product->getData('sales'))->min(0)->precision(0)->col(12);
        $f[] = Form::number('sort','排序',$product->getData('sort'))->col(12);
        $f[] = Form::number('postage','邮费',$product->getData('postage'))->min(0)->col(12);
        $f[] = Form::radio('is_postage','是否包邮',$product->getData('is_postage'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(12);
        $f[] = Form::radio('is_host','热门推荐',$product->getData('is_host'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $f[] = Form::radio('is_show','活动状态',$product->getData('is_show'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $form = Form::make_post_form('添加用户通知',$f,Url::buildUrl('save',compact('id')));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
//        $this->assign([
//            'title'=>'编辑产品','rules'=>$this->read($id)->getContent(),
//            'action'=>Url::buildUrl('update',array('id'=>$id))
//        ]);
//        return $this->fetch('public/common_form');
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
        $product = StoreCombinationModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['is_del']) return Json::fail('已删除!');
        $data['is_del'] = 1;
        if(!StoreCombinationModel::edit($data,$id))
            return Json::fail(StoreCombinationModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }

    /**
     * 属性页面
     * @param $id
     * @return mixed|void
     */
    public function attr($id)
    {
        if(!$id) return $this->failed('数据不存在!');
        $result = StoreCombinationAttrResult::getResult($id);
        $image = StoreCombinationModel::where('id',$id)->value('image');
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
        $product = StoreCombinationModel::get($id);
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
        $res = StoreCombinationAttr::createProductAttr($attr,$detail,$id);
        if($res)
            return $this->successful('编辑属性成功!');
        else
            return $this->failed(StoreCombinationAttr::getErrorInfo());
    }

    /**
     * 清除属性
     * @param $id
     */
    public function clear_attr($id)
    {
        if(!$id) return $this->failed('产品不存在!');
        if(false !== StoreCombinationAttr::clearProductAttr($id) && false !== StoreCombinationAttrResult::clearResult($id))
            return $this->successful('清空产品属性成功!');
        else
            return $this->failed(StoreCombinationAttr::getErrorInfo('清空产品属性失败!'));
    }

    public function edit_content($id){
        if(!$id) return $this->failed('数据不存在');
        $product = StoreCombinationModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $this->assign([
            'content'=>StoreCombinationModel::where('id',$id)->value('description'),
            'field'=>'description',
            'action'=>Url::buildUrl('change_field',['id'=>$id,'field'=>'description'])
        ]);
        return $this->fetch('public/edit_content');
    }

    /**
     * 上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $res = Upload::image('file','store/product/'.date('Ymd'));
        if(is_array($res)){
            SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],2,$res['image_type'],$res['time']);
            return Json::successful('图片上传成功!',['name'=>$res['name'],'url'=>Upload::pathToUrl($res['thumb_path'])]);
        }else
            return Json::fail($res);
    }

    /**拼团列表
     * @return mixed
     */
    public function combina_list()
    {
        $where = Util::getMore([
            ['status',''],
            ['data',''],
        ],$this->request);
        $this->assign('where',$where);
        $this->assign(StorePink::systemPage($where));

        return $this->fetch();
    }
    /**拼团人列表
     * @return mixed
     */
    public function order_pink($id){
        if(!$id) return $this->failed('数据不存在');
        $StorePink = StorePink::getPinkUserOne($id);
        if(!$StorePink) return $this->failed('数据不存在!');
        $list = StorePink::getPinkMember($id);
        $list[] = $StorePink;
        $this->assign('list',$list);
        return $this->fetch();
    }/**
 * 修改拼团状态
 * @param $status
 * @param int $idd
 */
    public function set_combination_status($status,$id = 0){
        if(!$id) return Json::fail('参数错误');
        $res = StoreCombinationModel::edit(['is_show'=>$status],$id);
        if($res) return Json::successful('修改成功');
        else return Json::fail('修改失败');
    }


}
