<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\system\config;


use app\dao\system\config\SystemConfigDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\utils\File;
use crmeb\utils\FormBuilder;
use think\facade\Log;

/**
 * 系统配置
 * Class SystemConfigServices
 * @package app\services\system\config
 * @method count(array $where = []) 获取指定条件下的count
 * @method save(array $data) 保存数据
 * @method get(int $id, ?array $field = []) 获取一条数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 * @method delete(int $id, ?string $key = null) 删除数据
 * @method getUploadTypeList(string $configName) 获取上传配置中的上传类型
 */
class SystemConfigServices extends BaseService
{
    /**
     * 系统配置数据访问层
     * @var SystemConfigDao
     */
    protected $dao;

    /**
     * form表单句柄
     * @var FormBuilder
     */
    protected $builder;

    /**
     * 表单数据切割符号
     * @var string
     */
    protected $cuttingStr = '=>';

    /**
     * 获取配置表单提交URL
     * 根据当前请求URL自动推导：将 edit_basics 替换为 save_basics
     * @param string $url 当前请求的 baseUrl
     * @return string
     */
    protected function getSaveUrl(string $url): string
    {
        if (!$url) {
            return '/setting/config/save_basics';
        }
        // 替换 edit_basics → save_basics
        $saveUrl = str_replace('edit_basics', 'save_basics', $url);
        // 去掉多应用URL前缀（如 /admin/、/adminapi/ 等），得到路由路径
        $adminPrefix = config('app.admin_prefix', 'admin');
        // 多应用模式下，URL 可能包含应用名前缀（如 /adminapi/）
        $prefixes = array_unique([
            '/' . $adminPrefix . '/',
            '/adminapi/',
        ]);
        foreach ($prefixes as $prefix) {
            if (strpos($saveUrl, $prefix) === 0) {
                $saveUrl = substr($saveUrl, strlen($prefix) - 1);
                break;
            }
        }
        return $saveUrl;
    }


    /**
     * SystemConfigServices constructor.
     * @param SystemConfigDao $dao
     * @param FormBuilder $builder
     */
    public function __construct(SystemConfigDao $dao, FormBuilder $builder)
    {
        $this->dao = $dao;
        $this->builder = $builder;
    }

 

    /**
     * 获取单个系统配置
     * @param string $configName
     * @param null $default
     * @return mixed|null
     * @throws \ReflectionException
     */
    public function getConfigValue(string $configName, $default = null)
    {
        $value = $this->dao->getConfigValue($configName);
        return is_null($value) ? $default : json_decode($value, true);
    }

    /**
     * 写入单个系统配置（按 menu_name 定位已有配置行）
     * @param string $configName 配置项 menu_name
     * @param mixed $value 配置值
     * @return bool
     */
    public function setConfig(string $configName, $value): bool
    {
        if (!$this->dao->getOne(['menu_name' => $configName])) {
            return false;
        }
        $this->dao->update($configName, ['value' => json_encode($value)], 'menu_name');
        CacheService::delete('system_config_' . $configName);
        return true;
    }

    /**
     * 获取全部配置
     * @param array $configName
     * @return array
     * @throws \ReflectionException
     */
    public function getConfigAll(array $configName = [])
    {
        return array_map(function ($item) {
            return json_decode($item, true);
        }, $this->dao->getConfigAll($configName));
    }

    /**
     * 获取配置列表搜索并分页
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfigList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getConfigList($where, $page, $limit);
        $count = $this->dao->count($where);
        $tidy_srr = [];
        $configTabList = app()->make(SystemConfigTabServices::class)->getColumn([], 'title', 'id');
        foreach ($list as &$item) {
            $item['value'] = $item['value'] ? (json_decode($item['value'], true) ?: '') : '';
            if ($item['type'] == 'radio' || $item['type'] == 'checkbox') {
                $item['value'] = $this->getRadioOrCheckboxValueInfo($item['menu_name'], $item['value']);
            }
            if ($item['type'] == 'upload' && !empty($item['value'])) {
                if ($item['upload_type'] == 1 || $item['upload_type'] == 3) {
                    $item['value'] = [set_file_url($item['value'])];
                } elseif ($item['upload_type'] == 2) {
                    $tempValue = set_file_url($item['value']);
                    $item['value'] = is_array($tempValue) ? $tempValue : [];
                }
                if (is_array($item['value']) && !empty($item['value'])) {
                    foreach ($item['value'] as $key => $value) {
                        $tidy_srr[$key]['filepath'] = $value;
                        $tidy_srr[$key]['filename'] = basename($value);
                    }
                    $item['value'] = $tidy_srr;
                } else {
                    $item['value'] = [];
                }
            }
            if ($item['level'] == 1) {
                $item['link_data'] = $this->getLinkData($item['link_id'], $item['link_value']);
            }
            $item['config_tab_name'] = $configTabList[$item['config_tab_id']] ?? '';
        }
        return compact('count', 'list');
    }

    /**
     * 配置列表页获取关联的值，展示：关联配置名称/关联值
     * @param $id
     * @param $value
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/31
     */
    private function getLinkData($id, $value)
    {
        $info = $this->dao->get($id);
        if (!$info) return '';
        $parameter = explode("\n", $info['parameter']);
        $result = [];
        foreach ($parameter as $item) {
            $parts = explode('=>', $item);
            $result[$parts[0]] = $parts[1];
        }
        return $info['info'] . ':' . $result[$value].'（显示）';
    }

    /**
     * 配置列表页获取单选按钮或者多选按钮的显示值
     * @param $menu_name
     * @param $value
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getRadioOrCheckboxValueInfo(string $menu_name, $value): string
    {
        $option = [];
        $config_one = $this->dao->getOne(['menu_name' => $menu_name]);
        if (!$config_one) {
            return '';
        }
        $parameter = explode("\n", $config_one['parameter']);
        foreach ($parameter as $k => $v) {
            if (isset($v) && strlen($v) > 0) {
                $data = explode('=>', $v);
                $option[$data[0]] = $data[1];
            }
        }
        $str = '';
        if (is_array($value)) {
            foreach ($value as $v) {
                $str .= $option[$v] . ',';
            }
        } else {
            $str .= !empty($value) ? $option[$value] ?? '' : $option[0] ?? '';
        }
        return $str;
    }

    /**
     * 根据配置tab id获取系统配置信息
     * @param int $tabId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getReadList(int $tabId)
    {
        $info = $this->dao->getConfigTabAllList($tabId);
        foreach ($info as $k => $v) {
            if (!is_null(json_decode($v['value'])))
                $info[$k]['value'] = json_decode($v['value'], true);
            if ($v['type'] == 'upload' && !empty($v['value'])) {
                if ($v['upload_type'] == 1 || $v['upload_type'] == 3) $info[$k]['value'] = explode(',', $v['value']);
            }
        }
        return $info;
    }

    /**
     * 修改字段获取form表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editFieldForm(int $id)
    {
        $menu = $this->dao->get($id)->getData();
        if (!$menu) {
            throw new AdminException('数据不存在');
        }
        /** @var SystemConfigTabServices $service */
        $service = app()->make(SystemConfigTabServices::class);
        $formbuider = [];
        $linkData = $this->linkData($menu['config_tab_id']);
        $formbuider[] = $this->builder->radio('level', '联动显示', $menu['level'])->options([['value' => 0, 'label' => '否'], ['value' => 1, 'label' => '是']])->appendRule('suffix', [
            'type' => 'div',
            'class' => 'tips-info',
            'style' => ['width' => '100%'], 'props' => ['innerHTML' => '否：默认正常展示此配置；是：此配置默认隐藏，当选中下方对应配置的值时，此配置才会显示']
        ])->appendControl(1, [
            // 默认值需与 linkData() 中 options 的 value 类型保持一致，否则级联选择器无法自动回显
            $this->builder->cascader('link_data', '关联配置/值', [(int)$menu['link_id'], $this->normalizeOptionValue($menu['link_value'])])->options($linkData)->props(['props' => ['multiple' => false, 'checkStrictly' => false, 'emitPath' => true]])->style(['width' => '100%']),
        ])->requiredNum();
        $formbuider[] = $this->builder->input('menu_name', '字段变量', $menu['menu_name'])->disabled(1);
        $formbuider[] = $this->builder->hidden('type', $menu['type']);
        [$configTabList, $data] = $service->getConfigTabListForm((int)($menu['config_tab_id'] ?? 0));
        $formbuider[] = $this->builder->cascader('config_tab_id', '分类', $data)->options($configTabList)->filterable(true)->props(['props' => ['multiple' => false, 'checkStrictly' => true, 'emitPath' => true]])->style(['width' => '100%']);
        $formbuider[] = $this->builder->input('info', '配置名称', $menu['info'])->required('配置名称不能为空')->autofocus(1);
        $formbuider[] = $this->builder->input('desc', '配置简介', $menu['desc']);
        switch ($menu['type']) {
            case 'text':
                $menu['value'] = json_decode($menu['value'], true);
                $formbuider[] = $this->createTextInputTypeForm($menu, true);
                break;
            case 'textarea':
                $menu['value'] = json_decode($menu['value'], true);
                $formbuider[] = $this->builder->textarea('value', '默认值', $menu['value'])->rows(3);
                $formbuider[] = $this->builder->number('width', '文本框宽', (int)$menu['width'])->min(1)->max(24);
                $formbuider[] = $this->builder->number('high', '多行文本框高', (int)$menu['high'])->min(1);
                break;
            case 'radio':
                $formbuider = array_merge($formbuider, $this->createRadioForm($menu));
                $formbuider[] = $this->builder->textarea('parameter', '配置参数', $menu['parameter'] ?? '')->rows(3)->placeholder("参数方式例如:\n1=>白色\n2=>红色\n3=>黑色");
                break;
            
            case 'upload':
                $formbuider = array_merge($formbuider, $this->createUploadForm((int)$menu['upload_type'], $menu, true));
                break;
            case 'checkbox':
                $menu['label'] = '默认值';
                $formbuider = array_merge($formbuider, $this->createCheckboxForm($menu));
                $formbuider[] = $this->builder->textarea('parameter', '配置参数', $menu['parameter'] ?? '')->rows(3)->placeholder("参数方式例如:\n1=>白色\n2=>红色\n3=>黑色");
                break;
            case 'select':
                $formbuider = array_merge($formbuider, $this->createSelectForm($menu));
                $formbuider[] = $this->builder->textarea('parameter', '配置参数', $menu['parameter'] ?? '')->rows(3)->placeholder("参数方式例如:\n1=>白色\n2=>红色\n3=>黑色");
                break;
            case 'switch':
                $formbuider = array_merge($formbuider, $this->createSwitchForm($menu));
                break;
        }
        // 是否必填（支持JSON格式）
        $requiredData = json_decode($menu['required'], true);
        if ($requiredData && isset($requiredData['required'])) {
            $menu['required'] = $requiredData['required'] ? 1 : 0;
        } else {
            $menu['required'] = $menu['required'] ? 1 : 0;
        }
        // 非switch类型，添加是否必填字段
        if ($menu['type'] != 'switch') {
            $formbuider[] = $this->builder->radio('required', '是否必填', $menu['required'] ?? 0)->options([
                ['value' => 0, 'label' => '否'],
                ['value' => 1, 'label' => '是'],
            ])->requiredNum();
        }
        $formbuider[] = $this->builder->number('sort', '排序', (int)$menu['sort']);
        $formbuider[] = $this->builder->radio('status', '状态', $menu['status'] ?? 0)->options([
            ['value' => 1, 'label' => '显示'],
            ['value' => 0, 'label' => '隐藏'],
        ])->requiredNum();
        return create_form('编辑字段', $formbuider, $this->url('/setting/config/' . $id), 'PUT');
    }

    /**
     * 创建和编辑文本类型的输入类型表单
     * @param array $data 配置数据
     * @param bool $isEdit 是否为编辑模式
     * @return BaseForm|BaseForm[]
     */
    private function createTextInputTypeForm(array $data, bool $isEdit = false)
    {
        // 确保值为字符串或数值类型（兼容数据库中误存为JSON数组的情况）
        if (isset($data['value']) && is_array($data['value'])) {
            $data['value'] = implode(',', $data['value']);
        }
        
        // 拆分必填、格式规则和数字范围（支持JSON格式）
        $requiredRules = $data['required'] ?? '';
        $requiredData = [];
        
        // 尝试解析JSON格式的验证规则
        if ($requiredRules && $requiredData = json_decode($requiredRules, true)) {
            $data['required'] = isset($requiredData['required']) && $requiredData['required'] ? 'required:true' : '';
            $data['regex'] = $requiredData['regex'] ?? '';
            $data['min'] = $requiredData['min'] ?? '';
            $data['max'] = $requiredData['max'] ?? '';
        } else {
            // 兼容旧格式：逗号分隔的字符串格式
            $data['required'] = strpos($requiredRules, 'required:true') !== false ? 'required:true' : '';
            $data['regex'] = '';
            $data['min'] = '';
            $data['max'] = '';
            
            // 提取 min 和 max
            if (preg_match('/\bmin:(-?\d+)/', $requiredRules, $minMatch)) {
                $data['min'] = (int)$minMatch[1];
            }
            if (preg_match('/\bmax:(-?\d+)/', $requiredRules, $maxMatch)) {
                $data['max'] = (int)$maxMatch[1];
            }
            
            // 提取正则表达式
            if (preg_match('/regex:(\/.+\/[gimsuy]*)/', $requiredRules, $patternMatch)) {
                $data['regex'] = $patternMatch[1];
            }
        }
        
        // 创建输入类型选择器
        $inputTypeSelect = $this->builder->select('input_type', '类型', $data['input_type'] ?? 'input')->setOptions([
            ['value' => 'input', 'label' => '文本框'],
            ['value' => 'number', 'label' => '数字'],
            ['value' => 'dateTime', 'label' => '日期时间'],
            ['value' => 'date', 'label' => '日期'],
            ['value' => 'time', 'label' => '时间'],
            ['value' => 'color', 'label' => '颜色']
        ])->required();
        
        // 正则表达式（输入框）
        $regexField = $this->builder->input('regex', '表单验证', $data['regex'] ?? '')
            ->placeholder('输入正则表达式，如：/^\d+$/')
            ->appendRule('suffix', [
                'type' => 'div',
                'class' => 'tips-info',
                'style' => ['width' => '100%'], 'props' => ['innerHTML' => '邮箱：/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/<br>手机号：/^1[3-9]\d{9}$/<br>URL：/^https?:\/\/.+/i<br>电话：/^1[3-9]\d{9}$|^0\d{2,3}-\d{7,8}$|^400-\d{3}-\d{3}$/<br>长度：/^.{6,20}$/']
            ]);
        // 宽度字段
        $col = isset($data['width']) && $data['width'] != 0 && $data['width'] >= 4 && $data['width'] <= 24 ? (int)$data['width'] : 13;
        $widthField = $this->builder->number('width', '表单框宽', $col)->min(4)->max(24);
        
        // 不同类型的默认值
        $inputValue = $data['input_type'] == 'input' ? ($data['value'] ?? '') : '';
        $inputValueField = $this->builder->input('value', '默认值', $inputValue);
        
        $numberValue = $data['input_type'] == 'number' ? ($data['value'] ?? '0') : '';
        $numberValueField = $this->builder->input('value', '默认值', $numberValue);
        
        $dateTimeValue = $data['input_type'] == 'dateTime' ? ($data['value'] ?? date('Y-m-d H:i:s')) : '';
        $dateTimeValueField = $this->builder->dateTime('value', '默认值', $dateTimeValue);
        
        $dateValue = $data['input_type'] == 'date' ? ($data['value'] ?? date('Y-m-d')) : '';
        $dateValueField = $this->builder->date('value', '默认值', $dateValue);
        
        $timeValue = $data['input_type'] == 'time' ? ($data['value'] ?? date('H:i:s')) : '';
        $timeValueField = $this->builder->time('value', '默认值', $timeValue);
        
        $colorValue = $data['input_type'] == 'color' ? ($data['value'] ?? '#000000') : '#000000';
        $colorValueField = $this->builder->color('value', '默认值', $colorValue);
        
        // 数字类型的最小值和最大值
        $numberMinField = $this->builder->input('min', '最小值', $data['min'] ?? '')->type('number');
        $numberMaxField = $this->builder->input('max', '最大值', $data['max'] ?? '')->type('number');
        
        // 根据类型联动显示不同的控件
        $inputTypeSelect->appendControl('input', [$regexField, $inputValueField, $widthField]);
        $inputTypeSelect->appendControl('number', [$numberMinField, $numberMaxField, $numberValueField, $widthField]);
        $inputTypeSelect->appendControl('dateTime', [$dateTimeValueField]);
        $inputTypeSelect->appendControl('date', [$dateValueField]);
        $inputTypeSelect->appendControl('time', [$timeValueField]);
        $inputTypeSelect->appendControl('color', [$colorValueField]);
        
        return $inputTypeSelect;
    }

    /**
     * 创建单行表单
     * @param string $type
     * @param array $data
     * @return array
     */
    public function createTextForm(string $type, array $data)
    {
        $formbuider = [];
        $inputRule = '';
        switch ($type) {
            case 'number':
                // 解析验证规则（支持 JSON 格式）
                $requiredRules = $data['required'] ?? '';
                $requiredData = json_decode($requiredRules, true);
                
                if ($requiredData) {
                    // JSON 格式
                    $data['min'] = $requiredData['min'] ?? null;
                    $data['max'] = $requiredData['max'] ?? null;
                } else {
                    // 兼容旧格式：提取 min 和 max
                    $data['min'] = null;
                    $data['max'] = null;
                    if (preg_match('/\bmin:(-?\d+)/', $requiredRules, $minMatch)) {
                        $data['min'] = (int)$minMatch[1];
                    }
                    if (preg_match('/\bmax:(-?\d+)/', $requiredRules, $maxMatch)) {
                        $data['max'] = (int)$maxMatch[1];
                    }
                }
                
                $data['value'] = isset($data['value']) ? json_decode($data['value'], true) : 0;
                // 确保值为数值类型（兼容数据库中误存为JSON数组的情况）
                if (is_array($data['value'])) {
                    $data['value'] = 0;
                }
                // 最小值和最大值提示（有值才显示）
                $minMaxTip = '';
                if (isset($data['min']) && $data['min'] !== null) {
                    $minMaxTip = '<br>最小值：' . $data['min'];
                }
                if (isset($data['max']) && $data['max'] !== null) {
                    $minMaxTip .= ($minMaxTip ? '，' : '<br>') . '最大值：' . $data['max'];
                }
                // 宽度字段
                $col = isset($data['width']) && $data['width'] != 0 && $data['width'] >= 4 && $data['width'] <= 24 ? (int)$data['width'] : 13;
                $inputRule = $this->builder->number($data['menu_name'], $data['info'], (float)$data['value'])->controls(false)->appendRule('suffix', [
                    'type' => 'div',
                    'class' => 'tips-info',
                    'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc'] . $minMaxTip]
                ])->col($col);
                break;
            case 'dateTime':
                $inputRule = $this->builder->dateTime($data['menu_name'], $data['info'], $data['value'])->appendRule('suffix', [
                    'type' => 'div',
                    'class' => 'tips-info',
                    'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
                ]);
                break;
            case 'date':
                $data['value'] = json_decode($data['value'], true) ?: '';
                if (is_array($data['value'])) $data['value'] = '';
                $inputRule = $this->builder->date($data['menu_name'], $data['info'], $data['value'])->appendRule('suffix', [
                    'type' => 'div',
                    'class' => 'tips-info',
                    'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
                ]);
                break;
            case 'time':
                $data['value'] = json_decode($data['value'], true) ?: '';
                if (is_array($data['value'])) $data['value'] = '';
                $inputRule = $this->builder->time($data['menu_name'], $data['info'], $data['value'])->appendRule('suffix', [
                    'type' => 'div',
                    'class' => 'tips-info',
                    'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
                ]);
                break;
            case 'color':
                $data['value'] = isset($data['value']) ? json_decode($data['value'], true) : '';
                if (is_array($data['value'])) $data['value'] = '';
                $inputRule = $this->builder->color($data['menu_name'], $data['info'], $data['value'])->appendRule('suffix', [
                    'type' => 'div',
                    'class' => 'tips-info',
                    'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
                ]);
                break;
            default:
                $data['value'] = isset($data['value']) ? json_decode($data['value'], true) : '';
                // 确保值为字符串类型（兼容数据库中误存为JSON数组的情况）
                if (is_array($data['value'])) {
                    $data['value'] = implode(',', array_map(static function ($value) {
                        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
                    }, $data['value']));
                }
                // 系统生成的消息推送地址需要添加站点域名，并且设为只读。
                if (in_array($data['menu_name'], ['api', 'routine_api', 'wechat_api'], true)) {
                    $inputRule = $this->builder->input($data['menu_name'], $data['info'], strpos($data['value'], 'http') === false ? sys_config('site_url') . $data['value'] : $data['value'])->appendRule('suffix', [
                        'type' => 'div',
                        'class' => 'tips-info',
                        'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
                    ])->col($data['width'] ?? 13)->readonly(true);
                } else {
                    // 宽宽度设置
                    $col = isset($data['width']) && $data['width'] != 0 && $data['width'] >= 4 && $data['width'] <= 24 ? (int)$data['width'] : 13;
        
                    $inputRule = $this->builder->input($data['menu_name'], $data['info'], $data['value'])->appendRule('suffix', [
                        'type' => 'div',
                        'class' => 'tips-info',
                        'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
                    ])->col($col)->placeholder('请输入'.$data['info']);
                }
                break;
        }
        // 是否必填项
        $this->isRequired($data['required']) && $inputRule = $inputRule->required($data['info'].'不能为空');  
        $formbuider[] = $inputRule;
        return $formbuider;
    }
    
    /**
     * 创建多行文本框
     * @param array $data
     * @return mixed
     */
    private function createTextareaForm(array $data)
    {
        $formbuider = [];
        $textareaRule = '';
        $data['value'] = json_decode($data['value'], true) ?: '';
        if ($data['menu_name'] == 'param_filter_data') $data['value'] = base64_decode($data['value']);
        // 宽度设置
        $col = isset($data['width']) && $data['width'] < 24 && $data['width'] > 4  ? $data['width'] : 13;
        $textareaRule = $this->builder->textarea($data['menu_name'], $data['info'], $data['value'])->placeholder($data['desc'])->appendRule('suffix', [
            'type' => 'div',
            'class' => 'tips-info',
            'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
        ])->rows($data['high'] ?? 6)->col($col);
        // 是否必填项
        $this->isRequired($data['required']) && $textareaRule = $textareaRule->required($data['info'].'不能为空')->placeholder($data['desc']);
        $formbuider[] = $textareaRule;
        return $formbuider;
    }

    /**
     * 创建单选表单
     * @param array $data
     * @param array $control
     * @param array $control_two
     * @return array
     */
    private function createRadioForm(array $data, $control = [], $control_two = [], $control_three = [])
    {
        $formbuider = [];
        $value = json_decode($data['value'], true);
        $data['value'] = $this->normalizeOptionValue(($value === null || $value === '' || $value === false) ? '0' : $value);
        $parameter = explode("\n", $data['parameter']);
        $options = [];
        if ($parameter) {
            foreach ($parameter as $v) {
                if (strstr($v, $this->cuttingStr) !== false) {
                    $pdata = explode($this->cuttingStr, $v, 2);
                    $options[] = ['label' => trim($pdata[1]), 'value' => $this->normalizeOptionValue($pdata[0])];
                }
            }
            $formbuider[] = $radio = $this->builder->radio($data['menu_name'], $data['info'], $data['value'])->options($options)->appendRule('suffix', [
                'type' => 'div',
                'class' => 'tips-info',
                'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
            ])->appendValidate([
                'required' => true,
                'message' => $data['info'] . '必选项',
                'trigger' => 'change',
            ])->col(13);
            if ($control) {
                $radio->appendControl($data['show_value'] ?? 1, is_array($control) ? $control : [$control]);
            }
            if ($control_two && isset($data['show_value2'])) {
                $radio->appendControl($data['show_value2'] ?? 2, is_array($control_two) ? $control_two : [$control_two]);
            }
            if ($control_three && isset($data['show_value3'])) {
                $radio->appendControl($data['show_value3'] ?? 3, is_array($control_three) ? $control_three : [$control_three]);
            }
            return $formbuider;
        }
    }

    /**
     * 创建上传组件表单
     * @param int $type
     * @param array $data
     * @param bool $showTypeSelect 是否显示上传类型选择器
     * @return array
     */
    private function createUploadForm(int $type, array $data, bool $showTypeSelect = false)
    {
        $formbuider = [];
        // 单图控件
        if($type == 1){
            $singleDataValue = json_decode($data['value'], true) ?: '';
            if ($singleDataValue !== '') $singleDataValue = set_file_url($singleDataValue);
        } else{
            $singleDataValue = '';
        }
        $singleImageField = $this->builder->frameImage($data['menu_name'], $data['info'], $this->url(config('app.admin_prefix', 'admin') . '/widget.images/index', ['fodder' => $data['menu_name']], true), $singleDataValue)
            ->icon('el-icon-picture-outline')->width('950px')->height('560px')->Props(['footer' => false, 'modalTitle' => '预览'])->appendRule('suffix', [
                'type' => 'div',
                'class' => 'tips-info',
                'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc'] ?? '']
            ])->col(13);
        
        
        // 多图控件
        if($type == 2){
            $multiDataValue = json_decode($data['value'], true) ?: [];
            if (!empty($multiDataValue)) {
                $multiDataValue = set_file_url($multiDataValue);
            }
        } else{
            $multiDataValue = [];
        }
        
        $multiImageField = $this->builder->frameImages($data['menu_name'], $data['info'], $this->url(config('app.admin_prefix', 'admin') . '/widget.images/index', ['fodder' => $data['menu_name'], 'type' => 'many', 'maxLength' => 5], true), $multiDataValue)
            ->maxLength(5)->icon('el-icon-picture-outline')->width('950px')->height('560px')->Props(['footer' => false, 'modalTitle' => '预览'])
            ->appendRule('suffix', [
                'type' => 'div',
                'class' => 'tips-info',
                'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc'] ?? '']
            ])->col(13);
        
        // 文件控件
        if($type == 3){
            $fileDataValue = json_decode($data['value'], true) ?: '';
            if ($fileDataValue !== '') $fileDataValue = set_file_url($fileDataValue);
            $fileDataValue = is_string($fileDataValue) ? $fileDataValue : '';
        } else{
            $fileDataValue = '';
        }   
        
        // 文件上传使用前端 form-create 自定义组件 FileUpload：非图片文件显示文件图标，上传前后同为 80×80
        $fileField = $this->builder->fileUpload($data['menu_name'], $data['info'], $this->url('/adminapi/file/upload/1', ['type' => 1], false, true), $fileDataValue, [
            'data' => ['menu_name' => $data['menu_name']],
            'headers' => [
                'Authori-zation' => app()->request->header('Authori-zation'),
            ],
        ])->appendRule('suffix', [
            'type' => 'div',
            'class' => 'tips-info',
            'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc'] ?? '']
        ])->col(13);
        
        // 如果需要显示类型选择器（编辑模式）
        if ($showTypeSelect) {
            // 创建上传类型的radio选择器
            $uploadTypeSelect = $this->builder->radio('upload_type', '上传类型', $type)->options($this->uploadType());
            // 联动：切换类型时显示对应的上传控件
            $uploadTypeSelect->appendControl(1, [$singleImageField]);
            $uploadTypeSelect->appendControl(2, [$multiImageField]);
            $uploadTypeSelect->appendControl(3, [$fileField]);
            
            $uploadRule = $uploadTypeSelect;
        } else {
            // 新建模式：根据类型只显示对应的上传控件
            switch ($type) {
                case 1:
                    // 是否必填项   
                    $this->isRequired($data['required']) && $singleImageField = $singleImageField->appendValidate((new \FormBuilder\UI\Elm\Validate(\FormBuilder\UI\Elm\Validate::TYPE_STRING))->required()->message($data['info'].'请选择图片'));
                    $uploadRule = $singleImageField;
                    break;
                case 2:
                    // 是否必填项
                    $this->isRequired($data['required']) && $multiImageField = $multiImageField->appendValidate((new \FormBuilder\UI\Elm\Validate(\FormBuilder\UI\Elm\Validate::TYPE_ARRAY))->required()->message($data['info'].'请选择图片'));
                    $uploadRule = $multiImageField;
                    break;
                case 3:
                    // 是否必填项
                    $this->isRequired($data['required']) && $fileField = $fileField->appendValidate((new \FormBuilder\UI\Elm\Validate(\FormBuilder\UI\Elm\Validate::TYPE_STRING))->required()->message($data['info'].'请选择文件'));
                    $uploadRule = $fileField;
                    break;
            }
        }
        
        $formbuider[] = $uploadRule;
        
        return $formbuider;
    }

    /**
     * 创建单选框
     * @param array $data
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    private function createCheckboxForm(array $data)
    {
        $formbuider = [];
        $checkboxRule = null;
        $data['value'] = json_decode($data['value'], true) ?: [];
        $parameter = explode("\n", $data['parameter']);
        $options = [];
        if ($parameter) {
            foreach ($parameter as $v) {
                if (strstr($v, $this->cuttingStr) !== false) {
                    $pdata = explode($this->cuttingStr, $v);
                    $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                }
            }
            $checkboxRule = $this->builder->checkbox($data['menu_name'], $data['info'], $data['value'])->options($options)->appendRule('suffix', [
                'type' => 'div',
                'class' => 'tips-info',
                'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
            ])->col(13);
        }
        // 是否必填项
        $isRequired = $this->isRequired($data['required']);
        if ($isRequired) {
            $checkboxRule = $checkboxRule->required($data['info'].'至少选择一个');
        }
        $formbuider[] = $checkboxRule;
        return $formbuider;
    }

    /**
     * 创建选择框表单
     * @param array $data
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    private function createSelectForm(array $data)
    {
        $formbuider = [];
        $selectRule = null;
        $data['value'] = json_decode($data['value'], true) ?: [];
        $parameter = explode("\n", $data['parameter']);
        $options = [];
        if ($parameter) {
            foreach ($parameter as $v) {
                if (strstr($v, $this->cuttingStr) !== false) {
                    $pdata = explode($this->cuttingStr, $v);
                    $options[] = ['label' => $pdata[1], 'value' => $pdata[0]];
                }
            }
            $selectRule = $this->builder->select($data['menu_name'], $data['info'], $data['value'])->options($options)->appendRule('suffix', [
                'type' => 'div',
                'class' => 'tips-info',
                'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
            ])->col(13);
        }
        // 是否必填项
        $this->isRequired($data['required']) && $selectRule = $selectRule->required($data['info'].'请选择');
        $formbuider[] = $selectRule;
        return $formbuider;
    }

    /**
     * 开关选择
     * @param $data
     * @return array
     * @author: 吴汐
     * @email: 442384644@qq.com
     * @date: 2023/9/6
     */
    private function createSwitchForm($data)
    {
        $formbuider = [];
        $switchRule = null;
        $data['value'] = json_decode($data['value'], true) ?: '';
        $switchRule = $this->builder->switches($data['menu_name'], $data['info'], $data['value'])->appendRule('suffix', [   
            'type' => 'div',
            'class' => 'tips-info',
            'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
        ])->required($data['info'].'必选项')->col(13);
        $formbuider[] = $switchRule;
        return $formbuider;
    }

    /**
     * 创建颜色选择器
     * @param array $data
     * @return mixed
     */
    private function createColorForm(array $data)
    {
        $data['value'] = json_decode($data['value'], true) ?: '';
        $formbuider[] = $this->builder->color($data['menu_name'], $data['info'], $data['value'])->appendRule('suffix', [
            'type' => 'div',
            'class' => 'tips-info',
            'style' => ['width' => '100%'], 'props' => ['innerHTML' => $data['desc']]
        ])->col(13);
        return $formbuider;
    }

    /**
     * 统一配置项选项值类型：普通数字保持数字，字符串编码保持字符串。
     *
     * @param mixed $value
     * @return int|string
     */
    private function normalizeOptionValue($value)
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value)) {
            $value = trim($value);
            if (preg_match('/^-?(0|[1-9]\d*)$/', $value)) {
                return (int)$value;
            }
        }
        return $value;
    }

    /**
     * 上传类型
     * @return array
     */
    private function uploadType(): array
    {
        return [
            ['value' => 1, 'label' => '单图']
            , ['value' => 2, 'label' => '多图']
            , ['value' => 3, 'label' => '文件']
        ];
    }
    /**
     * 根据验证规则判断是否必填项
     * @param string $required
     * @return bool
     */
    private function isRequired(string $required)
    {
        // 支持 JSON 格式
        $requiredData = json_decode($required, true);
        if ($requiredData) {
            return isset($requiredData['required']) && $requiredData['required'] ? true : false;
        }
        // 兼容旧格式：逗号分隔
        return strpos($required, 'required:true') !== false;
    }


    /**
     * 获取系统配置表单
     * @param $data
     * @param bool $control
     * @param array $controle_two
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function formTypeShine($data, $control = false, $controle_two = [], $controle_three = [])
    {

        switch ($data['type']) {
            case 'text'://文本框
                return $this->createTextForm($data['input_type'], $data);
            case 'radio'://单选框
                return $this->createRadioForm($data, $control, $controle_two, $controle_three);
            case 'textarea'://多行文本框
                return $this->createTextareaForm($data);
            case 'upload'://文件上传
                return $this->createUploadForm((int)$data['upload_type'], $data,false);
            case 'checkbox'://多选框
                return $this->createCheckboxForm($data);
            case 'select'://多选框
                return $this->createSelectForm($data);
            // case 'color':
            //     return $this->createColorForm($data);
            case 'switch'://开关
                return $this->createSwitchForm($data);
        }
    }

    

    /**
     * 根据系统多个配置自动生成form表单页面
     * @param array $list
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function createForm(array $list)
    {
        if (!$list) return [];
        $list = array_combine(array_column($list, 'menu_name'), $list);
        $formbuider = [];
        $sonConfig = $this->dao->getColumn(['level' => 1], 'menu_name');
        foreach ($list as $key => $data) {
            if (in_array($key, $sonConfig)) {
                continue;
            }
            switch ($data['type']) {
                case 'text'://文本框
                    $formbuider = array_merge($formbuider, $this->createTextForm($data['input_type'], $data));
                    break;
                case 'radio'://单选框
                    $relateRule = $this->buildRelatedRuleFromDB($data);
                    $builder = [];
                    if (isset($relateRule[$key])) {
                        $role = $relateRule[$key];
                        $data['show_value'] = $role['show_value'];
                        foreach ($role['son_type'] as $sk => $sv) {
                            if (isset($list[$sk])) {
                                $son_data = $list[$sk];
                                $son_data['show_value'] = $role['show_value'];
                                $son_build = [];
                                if (isset($sv['son_type'])) {
                                    foreach ($sv['son_type'] as $ssk => $ssv) {
                                        $son_data['show_value'] = $sv['show_value'];
                                        $son_build[] = $this->formTypeShine($list[$ssk])[0];
                                        unset($list[$ssk]);
                                    }
                                }
                                $son_build_two = [];
                                if (isset($role['son_type'][$sk . '@'])) {
                                    $son_type_two = $role['son_type'][$sk . '@'];
                                    $son_data['show_value2'] = $son_type_two['show_value'];
                                    if (isset($son_type_two['son_type'])) {
                                        foreach ($son_type_two['son_type'] as $ssk => $ssv) {
                                            $son_build_two[] = $this->formTypeShine($list[$ssk])[0];
                                            unset($list[$ssk]);
                                        }
                                    }
                                }
                                $son_build_three = [];
                                if (isset($role['son_type'][$sk . '#'])) {
                                    $son_type_three = $role['son_type'][$sk . '#'];
                                    $son_data['show_value3'] = $son_type_three['show_value'];
                                    if (isset($son_type_three['son_type'])) {
                                        foreach ($son_type_three['son_type'] as $ssk => $ssv) {
                                            $son_build_three[] = $this->formTypeShine($list[$ssk])[0];
                                            unset($list[$ssk]);
                                        }
                                    }
                                }
                                $builder[] = $this->formTypeShine($son_data, $son_build, $son_build_two, $son_build_three)[0];
                                unset($list[$sk]);
                            }
                        }
                        $data['show_value'] = $role['show_value'];
                    }
                    $builder_two = [];
                    if (isset($relateRule[$key . '@'])) {
                        $role = $relateRule[$key . '@'];
                        $data['show_value2'] = $role['show_value'];
                        foreach ($role['son_type'] as $sk => $sv) {
                            $son_data = $list[$sk];
                            $son_data['show_value'] = $role['show_value'];
                            $builder_two[] = $this->formTypeShine($son_data)[0];
                        }
                    }
                    $builder_three = [];
                    if (isset($relateRule[$key . '#'])) {
                        $role = $relateRule[$key . '#'];
                        $data['show_value3'] = $role['show_value'];
                        foreach ($role['son_type'] as $sk => $sv) {
                            $son_data = $list[$sk];
                            $son_data['show_value'] = $role['show_value'];
                            $builder_three[] = $this->formTypeShine($son_data)[0];
                        }
                    }
                    $formbuider = array_merge($formbuider, $this->createRadioForm($data, $builder, $builder_two, $builder_three));
                    break;
                case 'textarea'://多行文本框
                    $formbuider = array_merge($formbuider, $this->createTextareaForm($data));
                    break;
                case 'upload'://文件上传
                    $formbuider = array_merge($formbuider, $this->createUploadForm((int)$data['upload_type'], $data,false));
                    break;
                case 'checkbox'://多选框
                    $formbuider = array_merge($formbuider, $this->createCheckboxForm($data));
                    break;
                case 'select'://多选框
                    $formbuider = array_merge($formbuider, $this->createSelectForm($data));
                    break;
                case 'switch'://开关
                    $formbuider = array_merge($formbuider, $this->createSwitchForm($data));
                    break;
            }
        }
        return $formbuider;
    }
    /**
     * 从数据库递归构建联动规则
     * 替代旧的 $relatedRule 硬编码，从 eb_system_config 表的 level/link_id/link_value 字段读取联动关系
     * @param array $parentConfig 父配置数据
     * @return array 联动规则数组，结构与 $relatedRule 一致
     */
    private function buildRelatedRuleFromDB(array $parentConfig): array
    {
        $relateRule = [];
        $sonData = $this->dao->getColumn(['level' => 1, 'link_id' => $parentConfig['id']], 'menu_name,link_value');
        if (!$sonData) return $relateRule;

        // 按 link_value 分组（处理多值联动：@/# 后缀场景）
        $grouped = [];
        foreach ($sonData as $sv) {
            $grouped[$sv['link_value']][] = $sv['menu_name'];
        }

        $i = 0;
        $suffixes = ['', '@', '#'];
        foreach ($grouped as $linkValue => $menuNames) {
            $label = $parentConfig['menu_name'] . ($suffixes[$i] ?? '');
            $relateRule[$label]['show_value'] = $this->normalizeOptionValue($linkValue);

            foreach ($menuNames as $menuName) {
                // 递归检查该子配置是否也有自己的子配置（二级联动）
                $childConfig = $this->dao->getOne(['menu_name' => $menuName]);
                if ($childConfig) {
                    $childConfig = $childConfig->toArray();
                }
                if ($childConfig && $this->hasRelatedChildren((int)$childConfig['id'])) {
                    // 递归结果中 key 为子配置 menu_name（可能带 @/# 后缀），需展平到父级 son_type
                    $nestedRule = $this->buildRelatedRuleFromDB($childConfig);
                    foreach ($nestedRule as $nestedKey => $nestedValue) {
                        $relateRule[$label]['son_type'][$nestedKey] = $nestedValue;
                    }
                } else {
                    $relateRule[$label]['son_type'][$menuName] = '';
                }
            }
            $i++;
        }

        return $relateRule;
    }

    /**
     * 检查指定配置是否有联动的子配置
     * @param int $configId 配置ID
     * @return bool
     */
    private function hasRelatedChildren(int $configId): bool
    {
        return $this->dao->count(['level' => 1, 'link_id' => $configId]) > 0;
    }

    /**
     * 根据tabid获取系统配置form表单创建
     * @param $url
     * @param int $tabId
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfigForm($url, int $tabId)
    {
        /** @var SystemConfigTabServices $service */
        $service = app()->make(SystemConfigTabServices::class);
        $title = $service->value(['id' => $tabId], 'title');
        // tab 不存在（如配置页面已被删除）时返回空表单，前端正常渲染不报错
        if (!$title) {
            return create_form('', [], $this->url($url ?? ''), 'POST');
        }
        $list = $this->dao->getConfigTabAllList($tabId);
        $formbuider = $this->createForm($list);
        $saveUrl = $this->getSaveUrl($url ?? '');
        return create_form($title, $formbuider, $this->url($saveUrl), 'POST');
    }

    /**
     * 新增路由增加设置项验证
     * 注：原先的硬编码路由验证已移除，该方法保留为兼容占位
     * @param $url
     * @param $post
     * @return bool
     */
    public function checkParam($url, $post)
    {
        return true;
    }

    
    /**
     * 添加配置字段
     * @param int $type
     * @param int $tab_id
    * @retuarr@throws \FormBuilder\Exception\FormBuilderException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function addFieldForm(int $type, int $tab_id): array
    {
        /** @var SystemConfigTabServices $service */
        $service = app()->make(SystemConfigTabServices::class);
        $formbuider = [];
        $form_type = '';
        $info_type = [];
        $parameter = [];
        switch ($type) {
            case 0://文本框
                $form_type = 'text';
                $parameter[] = $this->createTextInputTypeForm(['input_type' => 'input']);
                break;
            case 1://多行文本框
                $form_type = 'textarea';
                $parameter[] = $this->builder->textarea('value', '默认值');
                $parameter[] = $this->builder->number('width', '文本框宽', 100);
                $parameter[] = $this->builder->number('high', '多行文本框高', 5);
                break;
            case 2://单选框
                $form_type = 'radio';
                $parameter[] = $this->builder->textarea('parameter', '配置参数', "1=>男\n2=>女\n3=>保密")->rows(3)->placeholder("参数方式例如:\n1=>男\n2=>女\n3=>保密")->required('配置参数不能为空')->appendRule('suffix', [
                    'type' => 'div',
                    'class' => 'tips-info',
                    'style' => ['width' => '100%'], 'props' => ['innerHTML' => '参数说明：=>前面是参数值，后面是参数名称，每行一个参数，=>分割符不能换，例如：<br>1=>男<br>2=>女<br>3=>保密']
                ]); 
                $parameter[] = $this->builder->input('value', '默认值');
                break;
            case 3://文件上传
                $form_type = 'upload';
                $parameter = array_merge($parameter, $this->createUploadForm(1, ['menu_name' => 'value', 'info' => '默认值', 'value' => ''], true));
                break;
            case 4://多选框
                $form_type = 'checkbox';
                $parameter[] = $this->builder->textarea('parameter', '配置参数', "1=>白色\n2=>红色\n3=>黑色")->rows(3)->placeholder("参数方式例如:\n1=>白色\n2=>红色\n3=>黑色")->required('配置参数不能为空')->appendRule('suffix', [
                    'type' => 'div',
                    'class' => 'tips-info',
                    'style' => ['width' => '100%'], 'props' => ['innerHTML' => '参数说明：=>前面是参数值，后面是参数名称，每行一个参数，=>分割符不能换，例如：<br>1=>白色<br>2=>红色<br>3=>黑色']
                ]); 
                break;
            case 5://下拉框
                $form_type = 'select';
                $parameter[] = $this->builder->textarea('parameter', '配置参数', "1=>分类一\n2=>分类二\n3=>分类三")->rows(3)->placeholder("参数方式例如:\n1=>分类一\n2=>分类二\n3=>分类三")->required('配置参数不能为空')->appendRule('suffix', [
                    'type' => 'div',
                    'class' => 'tips-info',
                    'style' => ['width' => '100%'], 'props' => ['innerHTML' => '参数说明：=>前面是参数值，后面是参数名称，每行一个参数，=>分割符不能换，例如：<br>1=>分类一<br>2=>分类二<br>3=>分类三']
                ]); 
                break;
            case 6://开关
                $form_type = 'switch';
                $parameter[] = $this->builder->switches('value', '默认');
                break;
        }
        if ($form_type) {
            $formbuider[] = $this->builder->hidden('type', $form_type);
            [$configTabList, $data] = $service->getConfigTabListForm((int)($tab_id ?? 0));
            $linkData = $this->linkData($tab_id);
            $formbuider[] = $this->builder->radio('level', '联动显示', 0)->options([['value' => 0, 'label' => '否'], ['value' => 1, 'label' => '是']])->requiredNum()->appendRule('suffix', [
                'type' => 'div',
                'class' => 'tips-info',
                'style' => ['width' => '100%'], 'props' => ['innerHTML' => '否：默认正常展示此配置；是：此配置默认隐藏，当选中下方对应配置的值时，此配置才会显示']
            ])->appendControl(1, [
                $this->builder->cascader('link_data', '关联配置/值')->options($linkData)->props(['props' => ['multiple' => false, 'checkStrictly' => false, 'emitPath' => true]])->style(['width' => '100%']),
            ]);
            $formbuider[] = $this->builder->cascader('config_tab_id', '分类', $data)->options($configTabList)->filterable(true)->props(['props' => ['multiple' => false, 'checkStrictly' => true, 'emitPath' => false]])->style(['width' => '100%']);
            if ($info_type) {
                $formbuider[] = $info_type;
            }
            $formbuider[] = $this->builder->input('info', '配置名称')->required('配置名称不能为空')->autofocus(1);
            $formbuider[] = $this->builder->input('menu_name', '字段变量')->required('字段变量不能为空')->placeholder('例如：site_url');
            $formbuider[] = $this->builder->input('desc', '配置简介');
            $formbuider = array_merge($formbuider, $parameter);
            // 是否必填
            if ($form_type != 'switch') {
                $formbuider[] = $this->builder->radio('required', '是否必填', 0)->options([
                        ['value' => 0, 'label' => '否'],
                        ['value' => 1, 'label' => '是'],
                    ])->requiredNum();
            }
            $formbuider[] = $this->builder->number('sort', '排序', 0);

            $formbuider[] = $this->builder->radio('status', '状态', 1)->options([['value' => 1, 'label' => '显示'], ['value' => 0, 'label' => '隐藏']])->requiredNum(); 
        }
        return create_form('添加字段', $formbuider, $this->url('/setting/config'), 'POST');
    }

    /**
     * 联动选择关联配置/值
     * 根据指定的标签ID，链接数据并以特定格式返回。
     * @param $tab_id
     * @return array
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/30
     */
    public function linkData($tab_id)
    {
        $linkData = $this->selectList(['config_tab_id' => $tab_id, 'type' => 'radio', 'level' => 0], 'info as label,id as value,parameter,sort', 0, 0, 'sort DESC')->toArray();
        foreach ($linkData as &$item) {
            // 父级 value 也需归一化，与 children 的 value 类型保持一致，否则级联选择器无法匹配回显
            $item['value'] = $this->normalizeOptionValue($item['value']);
            $parameter = [];
            $parameter = explode("\n", $item['parameter']);
            foreach ($parameter as $pv) {
                $pvArr = explode('=>', $pv, 2);
                if (count($pvArr) < 2) {
                    continue;
                }
                $item['children'][] = [
                    'label' => trim($pvArr[1]),
                    'value' => $this->normalizeOptionValue($pvArr[0])
                ];
            }
        }
        return $linkData;
    }

    /**
     * radio 和 checkbox规则的判断
     * @param $data
     * @return bool
     */
    public function valiDateRadioAndCheckbox($data)
    {
        $option = [];
        $option_new = [];
        $data['parameter'] = str_replace("\r\n", "\n", $data['parameter']);//防止不兼容
        $parameter = explode("\n", $data['parameter']);
        if (count($parameter) < 2) {
            throw new AdminException('请输入正确格式的配置参数');
        }
        foreach ($parameter as $k => $v) {
            if (isset($v) && !empty($v)) {
                $option[$k] = explode('=>', $v);
            }
        }
        if (count($option) < 2) {
            throw new AdminException('请输入正确格式的配置参数');
        }
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
        if (!$bool) {
            throw new AdminException('请输入正确格式的配置参数');
        }
        $num1 = count($option_new);//提取该数组的数目
        $arr2 = array_unique($option_new);//合并相同的元素
        $num2 = count($arr2);//提取合并后数组个数
        if ($num1 > $num2) {
            throw new AdminException('请输入正确格式的配置参数');
        }
        return true;
    }

    /**
     * 验证参数
     * @param $data
     * @return bool
     */
    public function valiDateValue($data)
    {
        
        // 检查数据和验证规则是否存在
        if (!$data || !isset($data['required']) || !$data['required']) {
            return true;
        }
        
        $name = $data['info'] ?? '';
        $value = $data['value'] ?? '';
        
        // 支持JSON格式的验证规则
        $requiredData = json_decode($data['required'], true);
        if ($requiredData) {
            // JSON格式
            if (isset($requiredData['required']) && $requiredData['required']) {
                if ($value === '' || $value === null) {
                    throw new AdminException($name . '不能为空');
                }
            }
            if (isset($requiredData['regex']) && $requiredData['regex']) {
                $match = @preg_match($this->normalizeRegexPattern($requiredData['regex']), (string) $value);
                if ($match === false) {
                    throw new AdminException($name . '的验证规则配置错误');
                }
                if ($value && !$match) {
                    throw new AdminException($name . '请输入正确的格式');
                }
            }
            if (isset($requiredData['min']) && $value !== '' && $value !== null) {
                if ((float)$value < $requiredData['min']) {
                    throw new AdminException($name . '不能小于' . $requiredData['min']);
                }
            }
            if (isset($requiredData['gt']) && $value !== '' && $value !== null) {
                if ((float)$value <= $requiredData['gt']) {
                    throw new AdminException($name . '必须大于' . $requiredData['gt']);
                }
            }
            if (isset($requiredData['max']) && $value !== '' && $value !== null) {
                if ((float)$value > $requiredData['max']) {
                    throw new AdminException($name . '不能大于' . $requiredData['max']);
                }
            }
        } else {
            // 兼容旧格式：逗号分隔的字符串
            $valids = explode(',', $data['required']);
            foreach ($valids as $valid) {
                $valid = explode(':', $valid);
                if (isset($valid[0]) && isset($valid[1])) {
                    $k = strtolower(trim($valid[0]));
                    $v = strtolower(trim($valid[1]));
                    
                    if ($v != 'true') {
                        continue;
                    }
                    
                    switch ($k) {
                        case 'required':
                            if ($value === '' || $value === null) {
                                throw new AdminException($name . '不能为空');
                            }
                            break;
                            
                        case 'regex':
                            if ($value && !preg_match($data['regex'], $value)) {
                                throw new AdminException($name . '请输入正确的格式');
                            }
                            break;
                    }
                }
            }
        }
        
        return true;
    }

    /**
     * 将配置中未转义的正则分隔符规范化。
     *
     * 历史配置中的 URL 规则使用 /^https?://.+/i；其中协议后的斜杠在 PHP
     * PCRE 中需要转义。本方法仅补齐正则主体内未转义的分隔符，保持配置原值和
     * 后台表单展示不变。
     */
    private function normalizeRegexPattern(string $pattern): string
    {
        if (strlen($pattern) < 3) {
            return $pattern;
        }

        $delimiter = $pattern[0];
        if (ctype_alnum($delimiter) || $delimiter === '\\') {
            return $pattern;
        }

        $closingPosition = strrpos($pattern, $delimiter);
        if ($closingPosition === false || $closingPosition === 0) {
            return $pattern;
        }

        $body = substr($pattern, 1, $closingPosition - 1);
        $normalizedBody = '';
        $bodyLength = strlen($body);
        for ($index = 0; $index < $bodyLength; $index++) {
            $character = $body[$index];
            if ($character === $delimiter) {
                $backslashCount = 0;
                for ($cursor = $index - 1; $cursor >= 0 && $body[$cursor] === '\\'; $cursor--) {
                    $backslashCount++;
                }
                if ($backslashCount % 2 === 0) {
                    $normalizedBody .= '\\';
                }
            }
            $normalizedBody .= $character;
        }

        return $delimiter . $normalizedBody . substr($pattern, $closingPosition);
    }

    /**
     * 保存平台电子面单打印信息
     * @param array $data
     * @return bool
     */
    public function saveExpressInfo(array $data)
    {
        if (!is_array($data) || !$data) return false;
        foreach ($data as $key => $value) {
            $this->dao->update(['menu_name' => 'config_export_' . $key], ['value' => json_encode($value)]);
        }
        CacheService::clear();
        return true;
    }

    /**
     * 获取分享海报 兼容方法
     */
    public function getSpreadBanner()
    {
        //配置
        $banner = sys_config('spread_banner', []);
        if (!$banner) {
            //组合数据
            $banner = sys_data('routine_spread_banner');
            if ($banner) {
                $banner = array_column($banner, 'pic');
                $this->dao->update(['menu_name' => 'spread_banner'], ['value' => json_encode($banner)]);
                CacheService::clear();
            }
        }
        return $banner;
    }

    /**
     * 保存wss配置
     * @param int $wssOpen
     * @param string $wssLocalpk
     * @param string $wssLocalCert
     */
    public function saveSslFilePath(int $wssOpen, string $wssLocalpk, string $wssLocalCert)
    {
        $wssFile = root_path() . '.wss';
        $content = <<<WSS
wssOpen = $wssOpen
wssLocalpk = $wssLocalpk
wssLocalCert = $wssLocalCert
WSS;
        try {
            file_put_contents($wssFile, $content);
        } catch (\Throwable $e) {
            throw new AdminException('保存wss证书失败');
        }
    }

    /**
     * 获取wss配置
     * @param string $key
     * @return array|false|mixed
     */
    public function getSslFilePath(string $key = '')
    {
        $wssFile = root_path() . '.wss';
        try {
            $content = parse_ini_file($wssFile);
        } catch (\Throwable $e) {
            $content = [];
        }
        return $content[$key] ?? $content;
    }

    /**
     * 变更分销绑定关系模式
     * @param array $post
     * @return bool
     */
    public function checkBrokerageBinding(array $post)
    {
        try {
            $config_data = $post['store_brokerage_binding_status'];
            $config_one = $this->dao->getOne(['menu_name' => 'store_brokerage_binding_status']);
            $config_old = json_decode($config_one['value'], true);
            if ($config_old != 2 && $config_data == 2) {
                event('BrokerageBindingStatusChange', ['status' => $config_data]);
            }
        } catch (\Throwable $e) {
            Log::error('变更分销绑定模式重置绑定时间失败,失败原因:' . $e->getMessage());
            return false;
        }
        return true;
    }
}
