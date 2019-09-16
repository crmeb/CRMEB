<?php
namespace app\admin\controller\sms;

use crmeb\services\SMSService;
use think\facade\Route;
use app\admin\controller\AuthController;
use crmeb\services\FormBuilder;
use crmeb\services\JsonService;
use crmeb\services\UtilService;

/**
 * 短信购买
 * Class SmsPay
 * @package app\admin\controller\sms
 */
class SmsPay extends AuthController
{
    /**
     * 显示资源列表
     * @return string
     */
    public function index()
    {
        $sms = new SMSService();
        if(!$sms::$status) return $this->failed('请先填写短信配置');
        return $this->fetch();
    }

    /**
     *  获取账号信息
     */
    public function number()
    {
        $countInfo = SMSService::count();
        if($countInfo['status'] == 400) return JsonService::fail($countInfo['msg']);
        return JsonService::success($countInfo['data']);
    }

    /**
     *  获取支付套餐
     */
    public function price()
    {
        list($page, $limit) = UtilService::getMore([
            ['page',1],
            ['limit',20],
        ], null, true);
        $mealInfo = SMSService::meal($page, $limit);
        if($mealInfo['status'] == 400) return JsonService::fail($mealInfo['msg']);
        return JsonService::success($mealInfo['data']['data']);
    }
    /**
     * 获取支付码
     */
    public function pay()
    {
        list($payType, $mealId, $price)= UtilService::postMore([
            ['payType', 'weixin'],
            ['mealId', 0],
            ['price', 0],
        ], null, true);
        $payInfo = SMSService::pay($payType, $mealId, $price, $this->adminId);
        if($payInfo['status'] == 400) return JsonService::fail($payInfo['msg']);
        return JsonService::success($payInfo['data']);
    }

    public function meal()
    {
        $this->assign('badge',SmsMeal::reckonNumber());
        return $this->fetch();
    }
    /*
     *  异步获取分类列表
     *  @return json
     */
    public function meal_list(){
        $where = UtilService::getMore([
            ['is_show',''],
            ['title',''],
            ['page',1],
            ['limit',20],
            ['order','']
        ]);
        return JsonService::successlayui(SmsMeal::lst($where));
    }
    /**
     * 显示创建资源表单页.
     *
     * @return string
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function create()
    {
        $field = [
            FormBuilder::input('title','套餐名称'),
            FormBuilder::number('price','套餐价格',0)->min(0),
            FormBuilder::number('num','短信条数',0)->min(0),
            FormBuilder::datePicker('end_time','使用期限',0),
            FormBuilder::radio('is_show','是否可用',1)->options([['label'=>'可用','value'=>1],['label'=>'不可用','value'=>0]])
        ];
        $form = FormBuilder::make_post_form('添加短信套餐',$field,Route::buildUrl('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 保存新建的资源
     */
    public function save()
    {
        $data = UtilService::postMore([
            ['title',''],
            ['price',''],
            ['num',''],
            ['end_time',''],
            ['is_show',1],
        ]);
        if(!strlen(trim($data['title']))) return JsonService::fail('请输入套餐名称');
        if(!strlen(trim($data['price']))) return JsonService::fail('请输入套餐价格');
        if(!strlen(trim($data['num']))) return JsonService::fail('请输入短信条数');
        $data['end_time'] = strtotime($data['end_time']);
        $data['add_time'] = time();
        $res = SmsMeal::create($data);
        if($res)
            return JsonService::successful('添加成功');
        return JsonService::fail('添加失败');
    }
    /**
     * 设置单个产品上架|下架
     * @param string $is_show
     * @param string $id
     */
    public function set_show($is_show='',$id=''){
        ($is_show=='' || $id=='') && JsonService::fail('缺少参数');
        $res=SmsMeal::where('id', $id)->update(['is_show'=>(int)$is_show]);
        if($res){
            return JsonService::successful($is_show==1 ? '显示成功':'隐藏成功');
        }else{
            return JsonService::fail($is_show==1 ? '显示失败':'隐藏失败');
        }
    }
    /**
     * 显示编辑资源表单页.
     * @param $id
     * @return string|void
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function edit($id)
    {
        $adminInfo = SmsMeal::get($id);
        if(!$adminInfo) return $this->failed('数据不存在!');
        $field = [
            FormBuilder::input('title','账号', $adminInfo->getData('title'))->disabled(true),
            FormBuilder::number('price','短信条数', $adminInfo->getData('price'))->min(0),
            FormBuilder::number('num','短信条数', $adminInfo->getData('num'))->min(0),
            FormBuilder::datePicker('end_time','使用期限',date('Y-m-d H:i',$adminInfo->getData('end_time'))),
            FormBuilder::radio('is_show','是否可用', $adminInfo->getData('is_show'))->options([['label'=>'可用','value'=>1],['label'=>'不可用','value'=>0]])
        ];
        $form = FormBuilder::make_post_form('编辑短信套餐',$field,Route::buildUrl('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存更新的资源
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update($id)
    {
        $adminInfo = SmsAdminModel::get($id);
        if(!$adminInfo) return $this->failed('数据不存在!');
        $data = Util::postMore([
            ['title',''],
            ['price',''],
            ['num',''],
            ['end_time',''],
            ['is_show',1],
        ]);
        if(!strlen(trim($data['title']))) return JsonService::fail('请输入套餐名称');
        if(!strlen(trim($data['price']))) return JsonService::fail('请输入套餐价格');
        if(!strlen(trim($data['num']))) return JsonService::fail('请输入短信条数');
        $data['end_time'] = strtotime($data['end_time']);
        $data['add_time'] = time();
        $res = SmsMeal::edit($data, $id);
        if($res)
            return JsonService::successful('修改成功');
        return JsonService::fail('修改失败');
    }

    /**
     * 删除指定资源
     * @param $id
     */
    public function delete($id)
    {
        $smsAdmin = SmsMeal::get($id);
        if(!$smsAdmin) return JsonService::fail('数据不存在!');
        if($smsAdmin['is_del']) return JsonService::fail('已删除');
        if(SmsMeal::del($id)){
            return JsonService::successful('删除成功');
        }
        return JsonService::fail('删除失败');
    }

}