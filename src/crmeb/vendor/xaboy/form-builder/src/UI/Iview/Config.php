<?php
/**
 * PHP表单生成器
 *
 * @package  FormBuilder
 * @author   xaboy <xaboy2005@qq.com>
 * @version  2.0
 * @license  MIT
 * @link     https://github.com/xaboy/form-builder
 * @document http://php.form-create.com
 */

namespace FormBuilder\UI\Iview;


use FormBuilder\Contract\ConfigInterface;
use FormBuilder\UI\Iview\Components\Button;
use FormBuilder\UI\Iview\Components\Poptip;
use FormBuilder\UI\Iview\Components\Tooltip;
use FormBuilder\UI\Iview\Style\FormStyle;
use FormBuilder\UI\Iview\Style\Row;

class Config implements ConfigInterface
{
    const INFO_TYPE_POPTIP = 'poptip';

    const INFO_TYPE_TOOLTIP = 'tooltip';

    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function info($type)
    {
        if (is_array($type) || $type instanceof Poptip || $type instanceof Tooltip)
            $this->config['info'] = $type;

        return $this;
    }

    /**
     * @param string $type tooltip,poptip
     * @return Poptip|Tooltip
     */
    public function createInfo($type = self::INFO_TYPE_POPTIP)
    {
        if (strtolower($type) === self::INFO_TYPE_TOOLTIP)
            $info = new Tooltip();
        else
            $info = new Poptip();
        $this->info($info);
        return $info;
    }

    /**
     * 表单整体显示规则配置
     *
     * @param FormStyle|array $formStyle
     * @return $this
     */
    public function formStyle($formStyle)
    {
        $this->config['form'] = $formStyle;
        return $this;
    }

    public function createFormStyle(array $rule = [])
    {
        $formStyle = new FormStyle($rule);
        $this->formStyle($formStyle);
        return $formStyle;
    }

    /**
     * 表单组件布局配置
     *
     * @param Row|array $row
     * @return $this
     */
    public function row($row)
    {
        $this->config['row'] = $row;
        return $this;
    }

    public function createRow(array $rule = [])
    {
        $row = new Row($rule);
        $this->row($row);
        return $row;
    }

    /**
     * 提交按钮样式和布局配置
     *
     * @param Button|array|bool $btn
     * @return $this
     */
    public function submitBtn($btn)
    {
        $this->config['submitBtn'] = $btn;
        return $this;
    }

    public function createSubmitBtn()
    {
        $submitBtn = new Button();
        $this->submitBtn($submitBtn);
        return $submitBtn;
    }

    /**
     * 开启事件注入
     *
     * @param bool $bool
     * @return $this
     */
    public function injectEvent($bool)
    {
        $this->config['injectEvent'] = !!$bool;
        return $this;
    }

    /**
     * 重置按钮样式和布局配置
     *
     * @param Button|array|bool $btn
     * @return $this
     */
    public function resetBtn($btn)
    {
        $this->config['resetBtn'] = $btn;
        return $this;
    }

    /**
     * @return Button
     */
    public function createResetBtn()
    {
        $resetBtn = new Button();
        $this->resetBtn($resetBtn);
        return $resetBtn;
    }

    /**
     * @param Button $btn
     * @return mixed
     */
    protected function parseButton(Button $btn)
    {
        $rule = $btn->build();
        if (isset($rule['col']))
            $rule['props']->col = $rule['col'];
        return $rule['props'];
    }

    /**
     * @param $info
     * @return mixed
     */
    protected function parseInfo($info)
    {
        if ($info instanceof Poptip || $info instanceof Tooltip) {
            $rule = $info->build();
            $info = $rule['props'];
            $info->type = $rule['type'];
        }

        return $info;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = $this->config;
        if (isset($config['form']) && ($form = $config['form']) instanceof FormStyle)
            $config['form'] = $form->getStyle();
        if (isset($config['row']) && ($row = $config['row']) instanceof Row)
            $config['row'] = $row->getStyle();
        if (isset($config['submitBtn']) && ($submitBtn = $config['submitBtn']) instanceof Button)
            $config['submitBtn'] = $this->parseButton($submitBtn);
        if (isset($config['resetBtn']) && ($resetBtn = $config['resetBtn']) instanceof Button)
            $config['resetBtn'] = $this->parseButton($resetBtn);
        if (isset($config['info']))
            $config['info'] = $this->parseInfo($config['info']);

        return $config;
    }


    /**
     * @param string $componentName
     * @param array $config
     * @return mixed
     */
    public function componentGlobalConfig($componentName, array $config)
    {
        if (!isset($this->config['global'])) $this->config['global'] = [];
        $this->config['global'][$componentName] = $config;
        return $this;
    }


    /**
     * @param array $config
     * @return $this
     */
    public function componentGlobalCommonConfig(array $config)
    {
        return $this->componentGlobalConfig('*', $config);
    }
}