<?php
namespace app\admin\controller\store;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use crmeb\services\JsonService;
use crmeb\services\UtilService as Util;
use crmeb\services\JsonService as Json;
use crmeb\services\UploadService as Upload;
use think\Request;
use app\admin\model\store\StoreCategory as CategoryModel;
use think\facade\Route as Url;
use app\admin\model\system\SystemAttachment;

/**
 * 产品分类控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class StoreCategory extends AuthController
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $this->assign('pid',$this->request->get('pid',0));
        $this->assign('cate',CategoryModel::getTierList(null, 0));
        return $this->fetch();
    }
    /*
     *  异步获取分类列表
     *  @return json
     */
    public function category_list(){
        $where = Util::getMore([
            ['is_show',''],
            ['pid',$this->request->param('pid','')],
            ['cate_name',''],
            ['page',1],
            ['limit',20],
            ['order','']
        ]);
        return JsonService::successlayui(CategoryModel::CategoryList($where));
    }

    /**
     * 设置产品分类上架|下架
     * @param string $is_show
     * @param string $id
     */
    public function set_show($is_show = '',$id = ''){
        ($is_show=='' || $id=='') && JsonService::fail('缺少参数');
        if(CategoryModel::setCategoryShow($id, (int)$is_show)){
            return JsonService::successful($is_show == 1 ? '显示成功':'隐藏成功');
        }else{
            return JsonService::fail(CategoryModel::getErrorInfo($is_show == 1 ?  '显示失败':'隐藏失败'));
        }
    }

    /**
     * 快速编辑
     * @param string $field
     * @param string $id
     * @param string $value
     */
    public function set_category($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(CategoryModel::where('id', $id)->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $field = [
            Form::select('pid','父级')->setOptions(function(){
                $list = CategoryModel::getTierList(null, 0);
                $menus = [['value'=>0,'label'=>'顶级菜单']];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['cate_name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('cate_name','分类名称'),
            Form::frameImageOne('pic','分类图标(180*180)',Url::buildUrl('admin/widget.images/index',array('fodder'=>'pic')))->icon('image')->width('100%')->height('500px'),
            Form::number('sort','排序'),
            Form::radio('is_show','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])
        ];
        $form = Form::make_post_form('添加分类',$field,Url::buildUrl('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }


    /**
     * 上传图片
     * @return \think\response\Json
     */
    public function upload()
    {
        $res = Upload::image('file','store/category'.date('Ymd'));
        if(is_array($res)){
            SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],1,$res['image_type'],$res['time']);
            return Json::successful('图片上传成功!',['name'=>$res['name'],'url'=>Upload::pathToUrl($res['thumb_path'])]);
        }else
            return Json::fail($res);
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'pid',
            'cate_name',
            ['pic',[]],
            'sort',
            ['is_show',0]
        ],$request);
        if($data['pid'] == '') return Json::fail('请选择父类');
        if(!$data['cate_name']) return Json::fail('请输入分类名称');
        if(count($data['pic'])<1) return Json::fail('请上传分类图标');
        if($data['sort'] <0 ) $data['sort'] = 0;
        $data['pic'] = $data['pic'][0];
        $data['add_time'] = time();
        CategoryModel::create($data);
        return Json::successful('添加分类成功!');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $c = CategoryModel::get($id);
        if(!$c) return Json::fail('数据不存在!');
        $field = [
            Form::select('pid','父级',(string)$c->getData('pid'))->setOptions(function() use($id){
                $list = CategoryModel::getTierList(CategoryModel::where('id','<>',$id), 0);
//                $list = (Util::sortListTier(CategoryModel::where('id','<>',$id)->select()->toArray(),'顶级','pid','cate_name'));
                $menus = [['value'=>0,'label'=>'顶级菜单']];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['cate_name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('cate_name','分类名称',$c->getData('cate_name')),
            Form::frameImageOne('pic','分类图标',Url::buildUrl('admin/widget.images/index',array('fodder'=>'pic')),$c->getData('pic'))->icon('image')->width('100%')->height('500px'),
            Form::number('sort','排序',$c->getData('sort')),
            Form::radio('is_show','状态',$c->getData('is_show'))->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])
        ];
        $form = Form::make_post_form('编辑分类',$field,Url::buildUrl('update',array('id'=>$id)),2);

        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'pid',
            'cate_name',
            ['pic',[]],
            'sort',
            ['is_show',0]
        ],$request);
        if($data['pid'] == '') return Json::fail('请选择父类');
        if(!$data['cate_name']) return Json::fail('请输入分类名称');
        if(count($data['pic'])<1) return Json::fail('请上传分类图标');
        if($data['sort'] <0 ) $data['sort'] = 0;
        $data['pic'] = $data['pic'][0];
        CategoryModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(!CategoryModel::delCategory($id))
            return Json::fail(CategoryModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
}
