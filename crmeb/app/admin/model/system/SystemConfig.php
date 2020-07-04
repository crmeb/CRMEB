<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */

namespace app\admin\model\system;

use crmeb\basic\BaseModel;
use crmeb\services\FormBuilder as Form;
use crmeb\traits\ModelTrait;
use think\facade\Route as Url;

class SystemConfig extends BaseModel
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
    protected $name = 'system_config';

    use ModelTrait;

    /**
     * 修改单个配置
     * @param $menu
     * @param $value
     * @return bool
     */
    public static function setValue($menu, $value)
    {
        if (empty($menu) || !($config_one = self::get(['menu_name' => $menu]))) return self::setErrorInfo('字段名称错误');
        if ($config_one['type'] == 'radio' || $config_one['type'] == 'checkbox') {
            $parameter = [];
            $option = [];
            $parameter = explode(',', $config_one['parameter']);
            foreach ($parameter as $k => $v) {
                if (isset($v) && !empty($v)) {
                    $option[$k] = explode('-', $v);
                }
            }
            $value_arr = [];//选项的值
            foreach ($option as $k => $v) {
                foreach ($v as $kk => $vv)
                    if (!$kk) {
                        $value_arr[$k] = $vv;
                    }
            }
            $i = 0;//
            if (is_array($value)) {
                foreach ($value as $value_v) {
                    if (in_array($value_v, $value_arr)) {
                        $i++;
                    }
                }
                if (count($value) != $i) return self::setErrorInfo('输入的值不属于选项中的参数');
            } else {
                if (in_array($value, $value_arr)) {
                    $i++;
                }
                if (!$i) return self::setErrorInfo('输入的值不属于选项中的参数');
            }
            if ($config_one['type'] == 'radio' && is_array($value)) return self::setErrorInfo('单选按钮的值是字符串不是数组');
        }
        $bool = self::edit(['value' => json_encode($value)], $menu, 'menu_name');
        return $bool;
    }

    /**
     * 获取单个参数配置
     * @param $menu
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getConfigValue($menu)
    {
        if (empty($menu) || !($config_one = self::where('menu_name', $menu)->find())) return false;
        return json_decode($config_one['value'], true);
    }

    /**
     * 获得多个参数
     * @param $menus
     * @return array
     */
    public static function getMore($menus)
    {
        $menus = is_array($menus) ? implode(',', $menus) : $menus;
        $list = self::where('menu_name', 'IN', $menus)->column('value', 'menu_name') ?: [];
        foreach ($list as $menu => $value) {
            $list[$menu] = json_decode($value, true);
        }
        return $list;
    }

    /**
     * @return array
     */
    public static function getAllConfig()
    {
        $list = self::column('value', 'menu_name') ?: [];
        foreach ($list as $menu => $value) {
            $list[$menu] = json_decode($value, true);
        }
        return $list;
    }

    /**
     * text  判断
     * @param $data
     * @return bool
     */
    public static function valiDateTextRole($data)
    {
        if (!$data['width']) return self::setErrorInfo('请输入文本框的宽度');
        if ($data['width'] <= 0) return self::setErrorInfo('请输入正确的文本框的宽度');
        return true;
    }

    /**
     * radio 和 checkbox规则的判断
     * @param $data
     * @return bool
     */
    public static function valiDateRadioAndCheckbox($data)
    {
        $parameter = [];
        $option = [];
        $option_new = [];
        $data['parameter'] = str_replace("\r\n", "\n", $data['parameter']);//防止不兼容
        $parameter = explode("\n", $data['parameter']);
        if (count($parameter) < 2) return self::setErrorInfo('请输入正确格式的配置参数');
        foreach ($parameter as $k => $v) {
            if (isset($v) && !empty($v)) {
                $option[$k] = explode('=>', $v);
            }
        }
        if (count($option) < 2) return self::setErrorInfo('请输入正确格式的配置参数');
        $bool = 1;
        foreach ($option as $k => $v) {
            $option_new[$k] = $option[$k][0];
            foreach ($v as $kk => $vv) {
                $vv_num = strlen($vv);
                if (!$vv_num) {
                    $bool = 0;
                }
            }
        }
        if (!$bool) return self::setErrorInfo('请输入正确格式的配置参数');
        $num1 = count($option_new);//提取该数组的数目
        $arr2 = array_unique($option_new);//合并相同的元素
        $num2 = count($arr2);//提取合并后数组个数
        if ($num1 > $num2) return self::setErrorInfo('请输入正确格式的配置参数');
        return true;
    }

    /**
     * textarea  判断
     * @param $data
     * @return bool
     */
    public static function valiDateTextareaRole($data)
    {
        if (!$data['width']) return self::setErrorInfo('请输入多行文本框的宽度');
        if (!$data['high']) return self::setErrorInfo('请输入多行文本框的高度');
        if ($data['width'] < 0) return self::setErrorInfo('请输入正确的多行文本框的宽度');
        if ($data['high'] < 0) return self::setErrorInfo('请输入正确的多行文本框的宽度');
        return true;
    }

    /**
     * 获取一数据
     * @param $filed
     * @param $value
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOneConfig($filed, $value)
    {
        $where[$filed] = $value;
        return self::where($where)->find();
    }

    /**
     * 获取配置分类
     * @param $id
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAll($id, int $status = 1)
    {
        $where['config_tab_id'] = $id;
        if ($status == 1) $where['status'] = $status;
        return self::where($where)->order('sort desc,id asc')->select();
    }

    /**
     * 获取所有配置分类
     * @param int $type
     * @return array
     */
    public static function getConfigTabAll($type = 0)
    {
        $configAll = SystemConfigTab::getAll($type);
        $config_tab = [];
        foreach ($configAll as $k => $v) {
            if (!$v['info']) {
                $config_tab[$k]['id'] = $v['id'];
                $config_tab[$k]['label'] = $v['title'];
                $config_tab[$k]['icon'] = $v['icon'];
                $config_tab[$k]['type'] = $v['type'];
                $config_tab[$k]['pid'] = $v['pid'];

            }
        }
        return $config_tab;
    }

    /**
     * 获取所有配置分类
     * @param int $type
     * @return array
     */
    public static function getConfigChildrenTabAll($pid = 0)
    {
        $configAll = SystemConfigTab::getChildrenTab($pid);
        $config_tab = [];
        foreach ($configAll as $k => $v) {
            if (!$v['info']) {
                $config_tab[$k]['id'] = $v['id'];
                $config_tab[$k]['label'] = $v['title'];
                $config_tab[$k]['icon'] = $v['icon'];
                $config_tab[$k]['type'] = $v['type'];
                $config_tab[$k]['pid'] = $v['pid'];
            }
        }
        return $config_tab;
    }

    /**
     * 选择类型
     * @param string $type
     * @return array
     */
    public static function radiotype($type = 'text')
    {
        return [
            ['value' => 'text', 'label' => '文本框', 'disabled' => 1]
            , ['value' => 'textarea', 'label' => '多行文本框', 'disabled' => 1]
            , ['value' => 'radio', 'label' => '单选按钮', 'disabled' => 1]
            , ['value' => 'upload', 'label' => '文件上传', 'disabled' => 1]
            , ['value' => 'checkbox', 'label' => '多选按钮', 'disabled' => 1]
        ];
    }

    /**
     * 选择文本框类型
     * @param string $type
     * @return array
     */
    public static function texttype($type = 'text')
    {
        return [
            ['value' => 'input', 'label' => '文本框']
            , ['value' => 'dateTime', 'label' => '时间']
            , ['value' => 'color', 'label' => '颜色']
            , ['value' => 'number', 'label' => '数字']
        ];
    }

    /**
     * 选择文文件类型
     * @param string $type
     * @return array
     */
    public static function uploadtype($type = 'text')
    {
        return [['value' => 1, 'label' => '单图']
            , ['value' => 2, 'label' => '多图']
            , ['value' => 3, 'label' => '文件']];
    }

    /**
     * 字段状态
     * @param string $type
     * @return array
     */
    public static function formstatus($type = 'text')
    {
        return [['value' => 1, 'label' => '显示'], ['value' => 2, 'label' => '隐藏']];
    }

    /**
     * 文本框
     * @param $tab_id
     * @return array
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function createInputRule($tab_id)
    {
        $formbuider = [];
        $formbuider[] = Form::hidden('type', 'text');
//        $formbuider[] = Form::select('config_tab_id','分类',$tab_id)->setOptions(SystemConfig::getConfigTabAll(-1));
        $formbuider[] = Form::select('input_type', '类型')->setOptions(self::texttype());
//        $formbuider[] = Form::input('info','配置名称')->autofocus(1);
//        $formbuider[] = Form::input('menu_name','字段变量')->placeholder('例如：site_url');
//        $formbuider[] = Form::input('desc','配置简介');
        $formbuider[] = Form::input('value', '默认值');
        $formbuider[] = Form::number('width', '文本框宽(%)', 100);
        $formbuider[] = Form::input('required', '验证规则')->placeholder('多个请用,隔开例如：required:true,url:true');
//        $formbuider[] = Form::number('sort','排序');
//        $formbuider[] = Form::radio('status','状态',1)->options(self::formstatus());
        return $formbuider;
    }

    /**
     * 多行文本框
     * @param $tab_id
     * @return array
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function createTextAreaRule($tab_id)
    {
        $formbuider = [];
        $formbuider[] = Form::hidden('type', 'textarea');
//        $formbuider[] = Form::select('config_tab_id','分类',$tab_id)->setOptions(SystemConfig::getConfigTabAll(-1));
//        $formbuider[] = Form::input('info','配置名称')->autofocus(1);
//        $formbuider[] = Form::input('menu_name','字段变量')->placeholder('例如：site_url');
//        $formbuider[] = Form::input('desc','配置简介');
        $formbuider[] = Form::textarea('value', '默认值');
        $formbuider[] = Form::number('width', '文本框宽(%)', 100);
        $formbuider[] = Form::number('high', '多行文本框高(%)', 5);
//        $formbuider[] = Form::number('sort','排序');
//        $formbuider[] = Form::radio('status','状态',1)->options(self::formstatus());
        return $formbuider;
    }

    /**
     * 单选按钮
     * @param $tab_id
     * @return array
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function createRadioRule($tab_id)
    {
        $formbuider = [];
        $formbuider[] = Form::hidden('type', 'radio');
//        $formbuider[] = Form::select('config_tab_id','分类',$tab_id)->setOptions(SystemConfig::getConfigTabAll(-1));
//        $formbuider[] = Form::input('info','配置名称')->autofocus(1);
//        $formbuider[] = Form::input('menu_name','字段变量')->placeholder('例如：site_url');
//        $formbuider[] = Form::input('desc','配置简介');
        $formbuider[] = Form::textarea('parameter', '配置参数')->placeholder("参数方式例如:\n1=>男\n2=>女\n3=>保密");
        $formbuider[] = Form::input('value', '默认值');
//        $formbuider[] = Form::number('sort','排序');
//        $formbuider[] = Form::radio('status','状态',1)->options(self::formstatus());
        return $formbuider;
    }

    /**
     * 文件上传
     * @param $tab_id
     * @return array
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function createUploadRule($tab_id)
    {
        $formbuider = [];
        $formbuider[] = Form::hidden('type', 'upload');
//        $formbuider[] = Form::select('config_tab_id','分类',$tab_id)->setOptions(SystemConfig::getConfigTabAll(-1));
//        $formbuider[] = Form::input('info','配置名称')->autofocus(1);
//        $formbuider[] = Form::input('menu_name','字段变量')->placeholder('例如：site_url');
//        $formbuider[] = Form::input('desc','配置简介');
        $formbuider[] = Form::radio('upload_type', '上传类型', 1)->options(self::uploadtype());
//        $formbuider[] = Form::number('sort','排序');
//        $formbuider[] = Form::radio('status','状态',1)->options(self::formstatus());
        return $formbuider;

    }

    /**
     * 多选框
     * @param $tab_id
     * @return array
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function createCheckboxRule($tab_id)
    {
        $formbuider = [];
        $formbuider[] = Form::hidden('type', 'checkbox');
//        $formbuider[] = Form::select('config_tab_id','分类',$tab_id)->setOptions(SystemConfig::getConfigTabAll(-1));
//        $formbuider[] = Form::input('info','配置名称')->autofocus(1);
//        $formbuider[] = Form::input('menu_name','字段变量')->placeholder('例如：site_url');
//        $formbuider[] = Form::input('desc','配置简介');
        $formbuider[] = Form::textarea('parameter', '配置参数')->placeholder("参数方式例如:\n1=>白色\n2=>红色\n3=>黑色");
//        $formbuider[] = Form::input('value','默认值');
//        $formbuider[] = Form::number('sort','排序');
//        $formbuider[] = Form::radio('status','状态',1)->options(self::formstatus());
        return $formbuider;
    }

    /**
     * 下拉框
     * @param $tab_id
     * @return array
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function createSelectRule($tab_id)
    {
        $formbuider = [];
        $formbuider[] = Form::hidden('type', 'select');
//        $formbuider[] = Form::select('config_tab_id','分类',$tab_id)->setOptions(SystemConfig::getConfigTabAll(-1));
//        $formbuider[] = Form::input('info','配置名称')->autofocus(1);
//        $formbuider[] = Form::input('menu_name','字段变量')->placeholder('例如：site_url');
//        $formbuider[] = Form::input('desc','配置简介');
        $formbuider[] = Form::textarea('parameter', '配置参数')->placeholder("参数方式例如:\n1=>白色\n2=>红色\n3=>黑色");
//        $formbuider[] = Form::input('value','默认值');
//        $formbuider[] = Form::number('sort','排序');
//        $formbuider[] = Form::radio('status','状态',1)->options(self::formstatus());
        return $formbuider;
    }
    /** 生成配置表单
     * @param $list
     * @return $this
     */
    public static function builder_config_from_data($list)
    {
        $formbuider = [];
        foreach ($list as $data) {
            switch ($data['type']) {
                case 'text'://文本框
                    switch ($data['input_type']) {
                        case 'input':
                            $data['value'] = json_decode($data['value'], true) ?: '';
                            $formbuider[] = Form::input($data['menu_name'], $data['info'], $data['value'])->info($data['desc'])->placeholder($data['desc'])->col(13);
                            break;
                        case 'number':
                            $data['value'] = json_decode($data['value'], true) ?: 0;
                            $formbuider[] = Form::number($data['menu_name'], $data['info'], $data['value'])->info($data['desc']);
                            break;
                        case 'dateTime':
                            $formbuider[] = Form::dateTime($data['menu_name'], $data['info'], $data['value'])->info($data['desc']);
                            break;
                        case 'color':
                            $data['value'] = json_decode($data['value'], true) ?: '';
                            $formbuider[] = Form::color($data['menu_name'], $data['info'], $data['value'])->info($data['desc']);
                            break;
                        default:
                            $data['value'] = json_decode($data['value'], true) ?: '';
                            $formbuider[] = Form::input($data['menu_name'], $data['info'], $data['value'])->info($data['desc'])->placeholder($data['desc'])->col(13);
                            break;
                    }
                    break;
                case 'textarea'://多行文本框
                    $data['value'] = json_decode($data['value'], true) ?: '';
                    $formbuider[] = Form::textarea($data['menu_name'], $data['info'], $data['value'])->placeholder($data['desc'])->info($data['desc'])->rows(6)->col(13);
                    break;
                case 'radio'://单选框
                    $data['value'] = json_decode($data['value'], true) ?: '0';
                    $parameter = explode("\n", $data['parameter']);
                    $options = [];
                    if ($parameter) {
                        foreach ($parameter as $v) {
                            $pdata = explode("=>", $v);
                            $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                        }
                        $formbuider[] = Form::radio($data['menu_name'], $data['info'], $data['value'])->options($options)->info($data['desc'])->col(13);
                    }
                    break;
                case 'upload'://文件上传
                    switch ($data['upload_type']) {
                        case 1:
                            $data['value'] = json_decode($data['value'], true) ?: '';
                            $formbuider[] = Form::frameImageOne($data['menu_name'], $data['info'], Url::buildUrl('admin/widget.images/index', array('fodder' => $data['menu_name'])), $data['value'])->icon('image')->width('70%')->height('500px')->info($data['desc'])->col(13);
                            break;
                        case 2:
                            $data['value'] = json_decode($data['value'], true) ?: [];
                            $formbuider[] = Form::frameImages($data['menu_name'], $data['info'], Url::buildUrl('admin/widget.images/index', array('fodder' => $data['menu_name'])), $data['value'])->maxLength(5)->icon('image')->width('70%')->height('500px')->info($data['desc'])->col(13);
                            break;
                        case 3:
                            $data['value'] = json_decode($data['value'], true);
                            $formbuider[] = Form::uploadFileOne($data['menu_name'], $data['info'], Url::buildUrl('file_upload'), $data['value'])->name('file')->info($data['desc'])->col(13);
                            break;
                    }

                    break;
                case 'checkbox'://多选框
                    $data['value'] = json_decode($data['value'], true) ?: [];
                    $parameter = explode("\n", $data['parameter']);
                    $options = [];
                    if ($parameter) {
                        foreach ($parameter as $v) {
                            $pdata = explode("=>", $v);
                            $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                        }
                        $formbuider[] = Form::checkbox($data['menu_name'], $data['info'], $data['value'])->options($options)->info($data['desc'])->col(13);
                    }
                    break;
                case 'select'://多选框
                    $data['value'] = json_decode($data['value'], true) ?: [];
                    $parameter = explode("\n", $data['parameter']);
                    $options = [];
                    if ($parameter) {
                        foreach ($parameter as $v) {
                            $pdata = explode("=>", $v);
                            $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                        }
                        $formbuider[] = Form::select($data['menu_name'], $data['info'], $data['value'])->options($options)->info($data['desc'])->col(13);
                    }
                    break;
            }
        }
        return $formbuider;
    }

    /**
     * 配置短信信息
     * @param $account
     * @param $token
     * @return bool
     */
    public static function setConfigSmsInfo($account, $token)
    {
        self::beginTrans();
        $res1 = self::where('menu_name', 'sms_account')->where('value', '"' . $account . '"')->count();
        if (!$res1) $res1 = self::where('menu_name', 'sms_account')->update(['value' => '"' . $account . '"']);
        $res2 = self::where('menu_name', 'sms_token')->where('value', '"' . $token . '"')->count();
        if (!$res2) $res2 = self::where('menu_name', 'sms_token')->update(['value' => '"' . $token . '"']);
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }
}