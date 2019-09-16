<?php
namespace app\admin\controller\sms;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder;
use think\facade\Route;
use app\admin\model\system\SystemConfig as ConfigModel;
/**
 * 短信配置
 * Class SmsConfig
 * @package app\admin\controller\sms
 */
class SmsConfig extends AuthController
{
    /**
     * 展示配置
     * @return string
     * @throws \FormBuilder\exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $type = input('type')!=0?input('type'):0;
        $tab_id = input('tab_id');
        if(!$tab_id) $tab_id = 1;
        $this->assign('tab_id',$tab_id);
        $list = ConfigModel::getAll($tab_id);
        if($type==3){//其它分类
            $config_tab = null;
        }else{
            $config_tab = ConfigModel::getConfigTabAll($type);
            foreach ($config_tab as $kk=>$vv){
                $arr = ConfigModel::getAll($vv['value'])->toArray();
                if(empty($arr)){
                    unset($config_tab[$kk]);
                }
            }
        }
        $formBuilder = [];
        foreach ($list as $data){
            switch ($data['type']){
                case 'text'://文本框
                    switch ($data['input_type']){
                        case 'input':
                            $data['value'] = json_decode($data['value'],true)?:'';
                            $formBuilder[] = FormBuilder::input($data['menu_name'],$data['info'],$data['value'])->info($data['desc'])->placeholder($data['desc'])->col(13);
                            break;
                        case 'number':
                            $data['value'] = json_decode($data['value'],true)?:0;
                            $formBuilder[] = FormBuilder::number($data['menu_name'],$data['info'],$data['value'])->info($data['desc']);
                            break;
                        case 'dateTime':
                            $formBuilder[] = FormBuilder::dateTime($data['menu_name'],$data['info'],$data['value'])->info($data['desc']);
                            break;
                        case 'color':
                            $data['value'] = json_decode($data['value'],true)?:'';
                            $formBuilder[] = FormBuilder::color($data['menu_name'],$data['info'],$data['value'])->info($data['desc']);
                            break;
                    }
                    break;
                case 'textarea'://多行文本框
                    $data['value'] = json_decode($data['value'],true)?:'';
                    $formBuilder[] = FormBuilder::textarea($data['menu_name'],$data['info'],$data['value'])->placeholder($data['desc'])->info($data['desc']);
                    break;
                case 'radio'://单选框
                    $data['value'] = json_decode($data['value'],true)?:'0';
                    $parameter = explode("\n",$data['parameter']);
                    $options = [];
                    if($parameter) {
                        foreach ($parameter as $v) {
                            $pdata = explode("=>", $v);
                            $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                        }
                        $formBuilder[] = FormBuilder::radio($data['menu_name'],$data['info'],$data['value'])->options($options)->info($data['desc']);
                    }
                    break;
                case 'upload'://文件上传
                    switch ($data['upload_type']){
                        case 1:
                            $data['value'] = json_decode($data['value'],true)?:'';
                            $formBuilder[] = FormBuilder::frameImageOne($data['menu_name'],$data['info'],Url::buildUrl('admin/widget.images/index',array('fodder'=>$data['menu_name'])),$data['value'])->icon('image')->width('100%')->height('500px')->info($data['desc']);
                            break;
                        case 2:
                            $data['value'] = json_decode($data['value'],true)?:[];
                            $formBuilder[] = FormBuilder::frameImages($data['menu_name'],$data['info'],Url::buildUrl('admin/widget.images/index',array('fodder'=>$data['menu_name'])),$data['value'])->maxLength(5)->icon('image')->width('100%')->height('500px')->info($data['desc']);
                            break;
                        case 3:
                            $data['value'] = json_decode($data['value'],true);
                            $formBuilder[] = FormBuilder::uploadFileOne($data['menu_name'],$data['info'],Url::buildUrl('file_upload'),$data['value'])->name('file')->info($data['desc']);
                            break;
                    }

                    break;
                case 'checkbox'://多选框
                    $data['value'] = json_decode($data['value'],true)?:[];
                    $parameter = explode("\n",$data['parameter']);
                    $options = [];
                    if($parameter) {
                        foreach ($parameter as $v) {
                            $pdata = explode("=>", $v);
                            $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                        }
                        $formBuilder[] = FormBuilder::checkbox($data['menu_name'],$data['info'],$data['value'])->options($options)->info($data['desc']);
                    }
                    break;
                case 'select'://多选框
                    $data['value'] = json_decode($data['value'],true)?:[];
                    $parameter = explode("\n",$data['parameter']);
                    $options = [];
                    if($parameter) {
                        foreach ($parameter as $v) {
                            $pdata = explode("=>", $v);
                            $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                        }
                        $formBuilder[] = FormBuilder::select($data['menu_name'],$data['info'],$data['value'])->options($options)->info($data['desc']);
                    }
                    break;
            }
        }

        $form = FormBuilder::make_post_form('编辑配置',$formBuilder,Route::buildUrl('save_basics'));
        $this->assign(compact('form'));
        $this->assign('config_tab',$config_tab);
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 保存配置
     */
    public function save_basics()
    {
        $request = app('request');
        if($request->isPost()){
            $post = $request->post();
            foreach ($post as $k=>$v){
                if(is_array($v)){
                    $res = ConfigModel::where('menu_name',$k)->column('upload_type','type');
                    foreach ($res as $kk=>$vv){
                        if($kk == 'upload'){
                            if($vv == 1 || $vv == 3){
                                $post[$k] = $v[0];
                            }
                        }
                    }
                }
            }
            foreach ($post as $k=>$v){
                ConfigModel::edit(['value' => json_encode($v)],$k,'menu_name');
            }
            return $this->successful('修改成功');
        }
    }
}