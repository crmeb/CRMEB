<?php

namespace app\admin\controller\setting;

use think\facade\Route as Url;
use app\admin\controller\AuthController;
use app\admin\model\system\{
    SystemConfig as ConfigModel, SystemConfigTab as ConfigTabModel
};
use crmeb\services\{
    CacheService,
    FormBuilder as Form,
    UtilService as Util,
    JsonService as Json
};
use crmeb\services\upload\Upload;


/**
 *  配置列表控制器
 * Class SystemConfig
 * @package app\admin\controller\system
 */
class SystemConfig extends AuthController
{
    /**
     * 基础配置
     * */
    public function index()
    {
        [$type, $tab_id, $children_tab_id] = Util::getMore([
            ['type', 0],//配置类型
            ['tab_id', 1],//当前分类ID
            ['children_tab_id', null],//当前子集分类ID
        ], null, true);

        $config_tab = null;//顶级分类
        $children_config_tab = null;//二级分类

        if ($type == 3) {//其它分类
            $config_tab = null;
        } else {
            $config_tab = ConfigModel::getConfigTabAll($type);//获取一级tab
        }
        $children_config_tab = ConfigModel::getConfigChildrenTabAll($tab_id);//获取二级tab

        if (!$children_tab_id && $children_config_tab) {
            $children_tab_id = $children_config_tab[0]['id'];
        }

        if ($children_tab_id) {
            $tid = $children_tab_id;
        } else {
            $tid = $tab_id;
        }

        //获取分类配置参数
        $list = ConfigModel::getAll($tid);
        $formbuider = ConfigModel::builder_config_from_data($list);//生产表单json
        $form = Form::make_post_form('编辑配置', $formbuider, Url::buildUrl('save_basics'));

        $this->assign('tab_id', $tab_id);
        $this->assign('children_tab_id', $children_tab_id);
        $this->assign('config_tab', $config_tab);
        $this->assign('children_config_tab', $children_config_tab);

        $this->assign(compact('form'));
        $this->assign('list', $list);
        return $this->fetch();
    }



    /**
     * 基础配置  单个
     * @return mixed|void
     */
    public function index_alone()
    {
        $tab_id = input('tab_id');
        if (!$tab_id) return $this->failed('参数错误，请重新打开');
        $this->assign('tab_id', $tab_id);
        $list = ConfigModel::getAll($tab_id);
        foreach ($list as $k => $v) {
            if (!is_null(json_decode($v['value'])))
                $list[$k]['value'] = json_decode($v['value'], true);
            if ($v['type'] == 'upload' && !empty($v['value'])) {
                if ($v['upload_type'] == 1 || $v['upload_type'] == 3) $list[$k]['value'] = explode(',', $v['value']);
            }
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * 添加字段
     * @return string
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function create()
    {
        $data = Util::getMore(['type',]);//接收参数
        $tab_id = !empty(request()->param('tab_id')) ? request()->param('tab_id') : 1;
        //前面通用字段
        $formbuiderheader = array();
        $formbuiderheader[] = Form::select('config_tab_id', '分类', 0)->setOptions(function () {
            $menuList = ConfigTabModel::field(['id', 'pid', 'title'])->select()->toArray();
            $list = sort_list_tier($menuList, '顶级', 'pid', 'id');
            $options = [['value' => 0, 'label' => '顶级按钮']];
            foreach ($list as $option) {
                $options[] = ['value' => $option['id'], 'label' => $option['html'] . $option['title']];
            }
            return $options;
        })->filterable(1);
        $formbuiderheader[] = Form::select('input_type', '类型')->setOptions(ConfigModel::texttype());
        $formbuiderheader[] = Form::input('info', '配置名称')->autofocus(1);
        $formbuiderheader[] = Form::input('menu_name', '字段变量')->placeholder('例如：site_url');
        $formbuiderheader[] = Form::input('desc', '配置简介');
        //不同类型不同字段
        $formbuider = array();
        switch ($data['type']) {
            case 0://文本框
                $formbuider = ConfigModel::createInputRule($tab_id);
                break;
            case 1://多行文本框
                $formbuider = ConfigModel::createTextAreaRule($tab_id);
                break;
            case 2://单选框
                $formbuider = ConfigModel::createRadioRule($tab_id);
                break;
            case 3://文件上传
                $formbuider = ConfigModel::createUploadRule($tab_id);
                break;
            case 4://多选框
                $formbuider = ConfigModel::createCheckboxRule($tab_id);
                break;
            case 5://下拉框
                $formbuider = ConfigModel::createSelectRule($tab_id);
                break;
        }
        //后面通用字段
        $formbuiderfoot = array();
        $formbuiderfoot[] = Form::number('sort', '排序');
        $formbuiderfoot[] = Form::radio('status', '状态', 1)->options(ConfigModel::formstatus());
        $formbuiders = array_merge($formbuiderheader, $formbuider, $formbuiderfoot);
        $form = Form::make_post_form('添加字段', $formbuiders, Url::buildUrl('save'));
        $this->assign(compact('form'));
        $this->assign('get', request()->param());
        return $this->fetch();
    }

    /**
     * 保存字段
     * */
    public function save()
    {
        $data = Util::postMore([
            'menu_name',
            'type',
            'input_type',
            'config_tab_id',
            'parameter',
            'upload_type',
            'required',
            'width',
            'high',
            'value',
            'info',
            'desc',
            'sort',
            'status',]);
        if (!$data['info']) return Json::fail('请输入配置名称');
        if (!$data['menu_name']) return Json::fail('请输入字段名称');
        if ($data['menu_name']) {
            $oneConfig = ConfigModel::getOneConfig('menu_name', $data['menu_name']);
            if (!empty($oneConfig)) return Json::fail('请重新输入字段名称,之前的已经使用过了');
        }
        if (!$data['desc']) return Json::fail('请输入配置简介');
        if ($data['sort'] < 0) {
            $data['sort'] = 0;
        }
        if ($data['type'] == 'text') {
            if (!ConfigModel::valiDateTextRole($data)) return Json::fail(ConfigModel::getErrorInfo());
        }
        if ($data['type'] == 'textarea') {
            if (!ConfigModel::valiDateTextareaRole($data)) return Json::fail(ConfigModel::getErrorInfo());
        }
        $data['parameter'] = htmlspecialchars_decode($data['parameter']);
        if ($data['type'] == 'radio' || $data['type'] == 'checkbox') {
            if (!$data['parameter']) return Json::fail('请输入配置参数');
            if (!ConfigModel::valiDateRadioAndCheckbox($data)) return Json::fail(ConfigModel::getErrorInfo());
            $data['value'] = json_encode($data['value']);
        }
        ConfigModel::create($data);
        CacheService::clear();
        return Json::successful('添加菜单成功!');
    }

    /**
     * @param $id
     */
    public function update_config($id)
    {
        $type = request()->post('type');
        if ($type == 'text' || $type == 'textarea' || $type == 'radio' || ($type == 'upload' && (request()->post('upload_type') == 1 || request()->post('upload_type') == 3))) {
            $value = request()->post('value');
        } else {
            $value = request()->post('value/a');
        }
        $data = Util::postMore(['status', 'info', 'desc', 'sort', 'config_tab_id', 'required', 'parameter', ['value', $value], 'upload_type', 'input_type']);
        $data['value'] = htmlspecialchars_decode(json_encode($data['value']));
        $data['parameter'] = htmlspecialchars_decode($data['parameter']);
        if (!ConfigModel::get($id)) return Json::fail('编辑的记录不存在!');
        ConfigModel::edit($data, $id);
        return Json::successful('修改成功!');
    }

    /**
     * 修改是否显示子子段
     * @param $id
     * @return mixed
     */
    public function edit_config($id)
    {
        $menu = ConfigModel::get($id)->getData();
        if (!$menu) return Json::fail('数据不存在!');
        $formbuider = array();
        $formbuider[] = Form::input('menu_name', '字段变量', $menu['menu_name'])->disabled(1);
        $formbuider[] = Form::hidden('type', $menu['type']);
//        $formbuider[] = Form::select('config_tab_id', '分类', (string)$menu['config_tab_id'])->setOptions(ConfigModel::getConfigTabAll(-1));
        $formbuider[] = Form::select('config_tab_id', '分类', (string)$menu['config_tab_id'])->setOptions(function () {
            $menuList = ConfigTabModel::field(['id', 'pid', 'title'])->select()->toArray();
            $list = sort_list_tier($menuList, '顶级', 'pid', 'id');
            $options = [['value' => 0, 'label' => '顶级按钮']];
            foreach ($list as $option) {
                $options[] = ['value' => $option['id'], 'label' => $option['html'] . $option['title']];
            }
            return $options;
        })->filterable(1);
        $formbuider[] = Form::input('info', '配置名称', $menu['info'])->autofocus(1);
        $formbuider[] = Form::input('desc', '配置简介', $menu['desc']);
        switch ($menu['type']) {
            case 'text':
                $menu['value'] = json_decode($menu['value'], true);
                $formbuider[] = Form::select('input_type', '类型', $menu['input_type'])->setOptions(ConfigModel::texttype());
                //输入框验证规则
                $formbuider[] = Form::input('value', '默认值', $menu['value']);
                if (!empty($menu['required'])) {
                    $formbuider[] = Form::number('width', '文本框宽(%)', $menu['width']);
                    $formbuider[] = Form::input('required', '验证规则', $menu['required'])->placeholder('多个请用,隔开例如：required:true,url:true');
                }
                break;
            case 'textarea':
                $menu['value'] = json_decode($menu['value'], true);
                //多行文本
                if (!empty($menu['high'])) {
                    $formbuider[] = Form::textarea('value', '默认值', $menu['value'])->rows(5);
                    $formbuider[] = Form::number('width', '文本框宽(%)', $menu['width']);
                    $formbuider[] = Form::number('high', '多行文本框高(%)', $menu['high']);
                } else {
                    $formbuider[] = Form::input('value', '默认值', $menu['value']);
                }
                break;
            case 'radio':
                $menu['value'] = json_decode($menu['value'], true);
                $parameter = explode("\n", htmlspecialchars_decode($menu['parameter']));
                $options = [];
                if ($parameter) {
                    foreach ($parameter as $v) {
                        $data = explode("=>", $v);
                        $options[] = ['label' => $data[1], 'value' => $data[0]];
                    }
                    $formbuider[] = Form::radio('value', '默认值', $menu['value'])->options($options);
                }
                //单选和多选参数配置
                if (!empty($menu['parameter'])) {
                    $formbuider[] = Form::textarea('parameter', '配置参数', $menu['parameter'])->placeholder("参数方式例如:\n1=白色\n2=红色\n3=黑色");
                }
                break;
            case 'checkbox':
                $menu['value'] = json_decode($menu['value'], true) ?: [];
                $parameter = explode("\n", htmlspecialchars_decode($menu['parameter']));
                $options = [];
                if ($parameter) {
                    foreach ($parameter as $v) {
                        $data = explode("=>", $v);
                        $options[] = ['label' => $data[1], 'value' => $data[0]];
                    }
                    $formbuider[] = Form::checkbox('value', '默认值', $menu['value'])->options($options);
                }
                //单选和多选参数配置
                if (!empty($menu['parameter'])) {
                    $formbuider[] = Form::textarea('parameter', '配置参数', $menu['parameter'])->placeholder("参数方式例如:\n1=白色\n2=红色\n3=黑色");
                }
                break;
            case 'upload':
                if ($menu['upload_type'] == 1) {
                    $menu['value'] = json_decode($menu['value'], true);
                    $formbuider[] = Form::frameImageOne('value', '图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'value')), (string)$menu['value'])->icon('image')->width('100%')->height('550px');
                } elseif ($menu['upload_type'] == 2) {
                    $menu['value'] = json_decode($menu['value'], true) ?: [];
                    $formbuider[] = Form::frameImages('value', '多图片', Url::buildUrl('admin/widget.images/index', array('fodder' => 'value')), $menu['value'])->maxLength(5)->icon('images')->width('100%')->height('550px')->spin(0);
                } else {
                    $menu['value'] = json_decode($menu['value'], true);
                    $formbuider[] = Form::uploadFileOne('value', '文件', Url::buildUrl('file_upload'), $menu['value'])->name('file');
                }
                //上传类型选择
                if (!empty($menu['upload_type'])) {
                    $formbuider[] = Form::radio('upload_type', '上传类型', $menu['upload_type'])->options([['value' => 1, 'label' => '单图'], ['value' => 2, 'label' => '多图'], ['value' => 3, 'label' => '文件']]);
                }
                break;

        }
        $formbuider[] = Form::number('sort', '排序', $menu['sort']);
        $formbuider[] = Form::radio('status', '状态', $menu['status'])->options([['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']]);

        $form = Form::make_post_form('编辑字段', $formbuider, Url::buildUrl('update_config', array('id' => $id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 删除子字段
     * @return \think\response\Json
     */
    public function delete_config()
    {
        $id = input('id');
        if (!ConfigModel::del($id)) {
            return Json::fail(ConfigModel::getErrorInfo('删除失败,请稍候再试!'));
        } else {
            CacheService::clear();
            return Json::successful('删除成功!');
        }
    }

    /**
     * 保存数据    true
     * */
    public function save_basics()
    {
        $request = app('request');
        if ($request->isPost()) {
            $post = $request->post();
            foreach ($post as $k => $v) {
                if (is_array($v)) {
                    $res = ConfigModel::where('menu_name', $k)->column('upload_type', 'type');
                    foreach ($res as $kk => $vv) {
                        if ($kk == 'upload') {
                            if ($vv == 1 || $vv == 3) {
                                $post[$k] = $v[0];
                            }
                        }
                    }
                }
            }
            foreach ($post as $k => $v) {
                ConfigModel::edit(['value' => json_encode($v)], $k, 'menu_name');
            }
            CacheService::clear();
            return $this->successful('修改成功');
        }
    }

    /**
     * 文件上传
     * */
    public function file_upload()
    {
        $upload = new Upload('local');
        $res = $upload->to('config/file')->validate()->move($this->request->param('file', 'file'));
        if ($res === false) return Json::fail($upload->getError());
        return Json::successful('上传成功!', ['filePath' => $res->filePath]);
    }

}
