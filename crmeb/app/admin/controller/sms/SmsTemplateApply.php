<?php
namespace app\admin\controller\sms;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder;
use crmeb\services\JsonService;
use crmeb\services\SMSService;
use crmeb\services\UtilService;
use think\facade\Route;

/**
 * 短信模板申请
 * Class SmsTemplateApply
 * @package app\admin\controller\sms
 */
class SmsTemplateApply extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return string
     */
    public function index()
    {
        $sms = new SMSService();
        if(!$sms::$status) return $this->failed('请先填写短信配置');
        return $this->fetch();
    }

    /**
     * 异步获取模板列表
     */
    public function lst()
    {
        $where = UtilService::getMore([
            ['status',''],
            ['title',''],
            ['page',1],
            ['limit',20],
        ]);
        $templateList = SMSService::template($where);
        if($templateList['status'] == 400) return JsonService::fail($templateList['msg']);
        return JsonService::successlayui($templateList['data']);
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
            FormBuilder::input('title','模板名称'),
            FormBuilder::input('content','模板内容')->type('textarea'),
            FormBuilder::radio('type','模板类型',1)->options([['label'=>'验证码','value'=>1],['label'=>'通知','value'=>2],['label'=>'推广','value'=>3]])
        ];
        $form = FormBuilder::make_post_form('申请短信模板',$field,Route::buildUrl('save'),2);
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
            ['content',''],
            ['type',0]
        ]);
        if(!strlen(trim($data['title']))) return JsonService::fail('请输入模板名称');
        if(!strlen(trim($data['content']))) return JsonService::fail('请输入模板内容');
        $applyStatus = SMSService::apply($data['title'], $data['content'], $data['type']);
        if($applyStatus['status'] == 400) return JsonService::fail($applyStatus['msg']);
        return JsonService::success('申请成功');
    }
}