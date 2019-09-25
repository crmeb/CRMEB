<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16 0016
 * Time: 10:39
 */

namespace app\admin\controller\ump;

use app\admin\controller\AuthController;
use crmeb\services\JsonService;
use crmeb\services\UtilService as Util;
use crmeb\services\FormBuilder as Form;
use crmeb\services\UtilService;
use crmeb\traits\CurdControllerTrait;
use crmeb\services\JsonService as Json;
use crmeb\services\UploadService as Upload;
use think\facade\Route as Url;
use app\admin\model\store\StoreProduct as ProductModel;
use app\admin\model\ump\StoreBargain as StoreBargainModel;
use app\admin\model\system\SystemAttachment;

//砍价
class StoreBargain extends AuthController
{
    use CurdControllerTrait;

    protected $bindModel = StoreBargainModel::class;

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = Util::getMore([
            ['status',''],
            ['store_name',''],
            ['export',0],
            ['data',''],
        ],$this->request);
        $limitTimeList = [
            'today'=>implode(' - ',[date('Y/m/d'),date('Y/m/d',strtotime('+1 day'))]),
            'week'=>implode(' - ',[
                date('Y/m/d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),
                date('Y/m/d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))
            ]),
            'month'=>implode(' - ',[date('Y/m').'/01',date('Y/m').'/'.date('t')]),
            'quarter'=>implode(' - ',[
                date('Y').'/'.(ceil((date('n'))/3)*3-3+1).'/01',
                date('Y').'/'.(ceil((date('n'))/3)*3).'/'.date('t',mktime(0,0,0,(ceil((date('n'))/3)*3),1,date('Y')))
            ]),
            'year'=>implode(' - ',[
                date('Y').'/01/01',date('Y/m/d',strtotime(date('Y').'/01/01 + 1year -1 day'))
            ])
        ];
        $this->assign('where',$where);
        $this->assign('countBargain',StoreBargainModel::getCountBargain());
        $this->assign('limitTimeList',$limitTimeList);
        $this->assign(StoreBargainModel::systemPage($where));
        $this->assign('bargainId',StoreBargainModel::getBargainIdAll($where));
        return $this->fetch();
    }

    /**
     * 异步获取砍价数据
     */
    public function get_bargain_list(){
        $where=Util::getMore([
            ['page',1],
            ['limit',20],
            ['export',0],
            ['store_name',''],
            ['status',''],
            ['data','']
        ]);
        $bargainList = StoreBargainModel::systemPage($where);
        if(is_object($bargainList['list'])) $bargainList['list'] = $bargainList['list']->toArray();
        $data = $bargainList['list']['data'];
        foreach ($data as $k=>$v){
            $data[$k]['_stop_time'] = date('Y/m/d H:i:s',$v['stop_time']);
        }
        return Json::successlayui(['count'=>$bargainList['list']['total'],'data'=>$data]);
    }

    /**
     * 上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $res = Upload::image('file','store/bargain/'.date('Ymd'));
        if(is_array($res)){
            SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],3,$res['image_type'],$res['time']);
            return Json::successful('图片上传成功!',['name'=>$res['name'],'url'=>Upload::pathToUrl($res['thumb_path'])]);
        }else
            return Json::fail($res);
    }

    /**
     * 添加砍价
     * @param  int  $id
     * @return \think\Response
     */
    public function create()
    {
        $f = array();
        $f[] = Form::input('title','砍价活动名称');
        $f[] = Form::input('info','砍价活动简介')->type('textarea');
        $f[] = Form::input('store_name','砍价产品名称');
        $f[] = Form::input('unit_name','单位')->placeholder('个、位');
        $f[] = Form::dateTimeRange('section_time','活动时间');
        $f[] = Form::frameImageOne('image','产品主图片(305*305px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'image')))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images','产品轮播图(640*640px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'images')))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price','显示原价')->min(0)->col(12);
        $f[] = Form::number('min_price','最低购买价')->min(0);
        $f[] = Form::number('bargain_max_price','单次砍价的最大金额')->min(0)->col(12);
        $f[] = Form::number('bargain_min_price','单次砍价的最小金额')->min(0)->col(12);
        $f[] = Form::number('cost','成本价')->min(0)->col(12);
        $f[] = Form::number('bargain_num','单次砍价的次数')->min(0)->col(12);
        $f[] = Form::number('stock','库存')->min(0)->col(12);
        $f[] = Form::number('sales','销量')->min(0)->col(12);
        $f[] = Form::number('sort','排序')->col(12);
        $f[] = Form::number('num','单次允许购买数量')->col(12);
        $f[] = Form::number('give_integral','赠送积分')->min(0)->col(12);
        $f[] = Form::number('postage','邮费')->min(0)->col(12);
        $f[] = Form::radio('is_postage','是否包邮',1)->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(12);
        $f[] = Form::radio('is_hot','热门推荐',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $f[] = Form::radio('status','活动状态',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $form = Form::make_post_form('添加用户通知',$f,Url::buildUrl('update'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
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
        $product = StoreBargainModel::get($id);
        if(!$product) return $this->failed('数据不存在!');
        $f = array();
        $f[] = Form::input('title','砍价活动名称',$product->getData('title'));
        $f[] = Form::hidden('product_id',$product->getData('product_id'));
        $f[] = Form::input('info','砍价活动简介',$product->getData('info'))->type('textarea');
        $f[] = Form::input('store_name','砍价产品名称',$product->getData('store_name'));
        $f[] = Form::input('unit_name','单位',$product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::dateTimeRange('section_time','活动时间',date("Y-m-d H:i:s",$product->getData('start_time')),date("Y-m-d H:i:s",$product->getData('stop_time')));//->format("yyyy-MM-dd HH:mm:ss");
        $f[] = Form::frameImageOne('image','产品主图片(305*305px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'image')),$product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images','产品轮播图(640*640px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'images')),json_decode($product->getData('images'),1))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price','显示原价',$product->getData('price'))->min(0)->col(12);
        $f[] = Form::number('min_price','最低购买价',$product->getData('min_price'))->min(0)->col(12);
        $f[] = Form::number('bargain_max_price','单次砍价的最大金额',$product->getData('bargain_max_price'))->min(0)->col(12);
        $f[] = Form::number('bargain_min_price','单次砍价的最小金额',$product->getData('bargain_min_price'))->min(0)->col(12);
        $f[] = Form::number('cost','成本价',$product->getData('cost'))->min(0)->col(12);
        $f[] = Form::number('bargain_num','单次砍价的次数',$product->getData('bargain_num'))->min(0)->col(12);
        $f[] = Form::number('stock','库存',$product->getData('stock'))->min(0)->col(12);
        $f[] = Form::number('sales','销量',$product->getData('sales'))->min(0)->col(12);
        $f[] = Form::number('sort','排序',$product->getData('sort'))->col(12);
        $f[] = Form::number('num','单次允许购买数量',$product->getData('num'))->col(12);
        $f[] = Form::number('give_integral','赠送积分',$product->getData('give_integral'))->min(0)->col(12);
        $f[] = Form::number('postage','邮费',$product->getData('postage'))->min(0)->col(12);
        $f[] = Form::radio('is_postage','是否包邮',$product->getData('is_postage'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(12);
        $f[] = Form::radio('is_hot','热门推荐',$product->getData('is_hot'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $f[] = Form::radio('status','活动状态',$product->getData('status'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $form = Form::make_post_form('添加用户通知',$f,Url::buildUrl('update',array('id'=>$id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     * @param string $id
     */
    public function update($id='')
    {
        $data = UtilService::postMore([
            ['title',''],
            ['info',''],
            ['store_name',''],
            ['unit_name',''],
            ['section_time',[]],
            ['image',''],
            ['images',[]],
            ['price',0],
            ['min_price',0],
            ['bargain_max_price',0],
            ['bargain_min_price',0],
            ['cost',0],
            ['bargain_num',0],
            ['stock',0],
            ['sales',0],
            ['sort',0],
            ['num',0],
            ['give_integral',0],
            ['postage',0],
            ['is_postage',0],
            ['is_hot',0],
            ['status',0],
            ['product_id',0]
        ]);
        if($data['title'] == '') return JsonService::fail('请输入砍价活动名称');
        if($data['info'] == '') return JsonService::fail('请输入砍价活动简介');
        if($data['store_name'] == '') return JsonService::fail('请输入砍价产品名称');
        if($data['unit_name'] == '') return JsonService::fail('请输入产品单位');
        if(count($data['section_time'])<1) return JsonService::fail('请选择活动时间');
        if(!$data['section_time'][0]) return JsonService::fail('请选择活动时间');
        if(!$data['section_time'][1]) return JsonService::fail('请选择活动时间');
        $data['start_time'] = strtotime($data['section_time'][0]);
        $data['stop_time'] = strtotime($data['section_time'][1]);
        unset($data['section_time']);
        if(!($data['image'])) return JsonService::fail('请选择推荐图');
        if(count($data['images'])<1) return JsonService::fail('请选择轮播图');
        $data['images'] = json_encode($data['images']);
        if($data['price'] == '' || $data['price'] < 0) return JsonService::fail('请输入砍价金额');
        if($data['min_price'] == '' || $data['min_price'] < 0) return JsonService::fail('请输入砍价最低金额');
        if($data['bargain_max_price'] == '' || $data['bargain_max_price'] < 0) return JsonService::fail('请输入用户单次砍价的最大金额');
        if($data['bargain_min_price'] == '' || $data['bargain_min_price'] < 0) return JsonService::fail('请输入用户单次砍价的最小金额');
        if($data['cost'] == '' || $data['cost'] < 0) return JsonService::fail('请输入成本价');
        if($data['bargain_num'] == '' || $data['bargain_num'] < 0) return JsonService::fail('请输入用户单次砍价的次数');
        if($data['stock'] == '' || $data['stock'] < 0) return JsonService::fail('请输入库存');
        if($data['num'] == '' || $data['num'] < 0) return JsonService::fail('请输入单次购买的砍价产品数量');
        unset($data['img']);
        if($id){
            $product = StoreBargainModel::get($id);
            if(!$product) return Json::fail('数据不存在!');
            $res = StoreBargainModel::edit($data,$id);
            if($res) return JsonService::successful('修改成功');
            else return JsonService::fail('修改失败');
        }
        else{
            $data['add_time'] = time();
            $res = StoreBargainModel::create($data);
            if($res) return JsonService::successful('添加成功');
            else return JsonService::fail('添加成功');
        }


    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(!$id) return Json::fail('数据不存在');
        $product = StoreBargainModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        if($product['is_del']) return Json::fail('已删除!');
        $data['is_del'] = 1;
        if(StoreBargainModel::edit($data,$id))
            return Json::successful('删除成功!');
        else
            return Json::fail(StoreBargainModel::getErrorInfo('删除失败,请稍候再试!'));
    }

    /**
     * 显示内容窗口
     * @param $id
     * @return mixed|\think\response\Json|void
     */
    public function edit_content($id){
        if(!$id) return $this->failed('数据不存在');
        $seckill = StoreBargainModel::get($id);
        if(!$seckill) return $this->failed('数据不存在');
        $this->assign([
            'content'=>StoreBargainModel::where('id',$id)->value('description'),
            'field'=>'description',
            'action'=>Url::buildUrl('change_field',['id'=>$id,'field'=>'description'])
        ]);
        return $this->fetch('public/edit_content');
    }
    public function edit_rule($id){
        if(!$id) return $this->failed('数据不存在');
        $seckill = StoreBargainModel::get($id);
        if(!$seckill) return $this->failed('数据不存在');
        $this->assign([
            'content'=>StoreBargainModel::where('id',$id)->value('rule'),
            'field'=>'rule',
            'action'=>Url::buildUrl('change_field',['id'=>$id,'field'=>'rule'])
        ]);
        return $this->fetch('public/edit_content');
    }
    /**
     * 开启砍价产品
     * @param int $id
     * @return mixed|\think\response\Json|void
     */
    public function bargain($id = 0){
        if(!$id) return $this->failed('数据不存在');
        $product = ProductModel::get($id);
        if(!$product) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('title','砍价活动名称');
        $f[] = Form::input('info','砍价活动简介')->type('textarea');
        $f[] = Form::hidden('product_id',$product->getData('id'));
        $f[] = Form::input('store_name','砍价产品名称',$product->getData('store_name'));
        $f[] = Form::input('unit_name','单位',$product->getData('unit_name'))->placeholder('个、位');
        $f[] = Form::dateTimeRange('section_time','活动时间');//->format("yyyy-MM-dd HH:mm:ss");
        $f[] = Form::frameImageOne('image','产品主图片(305*305px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'image')),$product->getData('image'))->icon('image')->width('100%')->height('500px');
        $f[] = Form::frameImages('images','产品轮播图(640*640px)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'images')),json_decode($product->getData('slider_image'),1))->maxLength(5)->icon('images')->width('100%')->height('500px');
        $f[] = Form::number('price','砍价金额')->min(0)->col(12);
        $f[] = Form::number('min_price','砍价最低金额',0)->min(0)->col(12);
        $f[] = Form::number('bargain_max_price','单次砍价的最大金额',10)->min(0)->col(12);
        $f[] = Form::number('bargain_min_price','单次砍价的最小金额',0.01)->min(0)->precision(2)->col(12);
        $f[] = Form::number('cost','成本价',$product->getData('cost'))->min(0)->col(12);
        $f[] = Form::number('bargain_num','单次砍价的次数',1)->min(0)->col(12);
        $f[] = Form::number('stock','库存',$product->getData('stock'))->min(1)->col(12);
        $f[] = Form::number('sales','销量',$product->getData('sales'))->min(0)->col(12);
        $f[] = Form::number('sort','排序',$product->getData('sort'))->col(12);
        $f[] = Form::number('num','单次购买的砍价产品数量',1)->col(12);
        $f[] = Form::number('give_integral','赠送积分',$product->getData('give_integral'))->min(0)->col(12);
        $f[] = Form::number('postage','邮费',$product->getData('postage'))->min(0)->col(12);
        $f[] = Form::radio('is_postage','是否包邮',$product->getData('is_postage'))->options([['label'=>'是','value'=>1],['label'=>'否','value'=>0]])->col(12);
        $f[] = Form::radio('is_hot','热门推荐',$product->getData('is_hot'))->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $f[] = Form::radio('status','活动状态',1)->options([['label'=>'开启','value'=>1],['label'=>'关闭','value'=>0]])->col(12);
        $form = Form::make_post_form('开启砍价活动',$f,Url::buildUrl('update'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 修改砍价状态
     * @param $status
     * @param int $id
     */
    public function set_bargain_status($status,$id = 0){
        if(!$id) return JsonService::fail('参数错误');
        $res = StoreBargainModel::edit(['status'=>$status],$id);
        if($res) return JsonService::successful('修改成功');
        else return JsonService::fail('修改失败');
    }
}