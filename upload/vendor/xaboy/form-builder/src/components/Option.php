<?php
/**
 * FormBuilder表单生成器
 * Author: xaboy
 * Github: https://github.com/xaboy/form-builder
 */

namespace FormBuilder\components;


use FormBuilder\interfaces\FormComponentInterFace;
use FormBuilder\Helper;

/**
 * Class Option
 *
 * @package FormBuilder\components
 */
class Option implements FormComponentInterFace
{

    /**
     * @var array
     */
    protected $props;

    /**
     * Option constructor.
     *
     * @param        $value
     * @param string $label
     * @param bool   $disabled
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function __construct($value, $label = '', $disabled = false)
    {
        self::verify($value, $value);
        $value = (string)$value;
        $this->props = compact('label', 'value');
        $this->disabled($disabled);
    }

    /**
     * @param $value
     * @param $label
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public static function verify($value, $label)
    {
        Helper::verifyType($value, ['numeric', 'string', 'null'], 'options.value');
        Helper::verifyType($label, ['numeric', 'string', 'null'], 'options.label');
    }


    /**
     * @param bool $disabled
     * @return $this
     */
    public function disabled($disabled = true)
    {
        $disabled = Helper::toType($disabled, 'boolean');
        $this->props['disabled'] = $disabled;
        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        $props = $this->props;
        if ($props['disabled'] != true)
            unset($props['disabled']);
        return $props;
    }
}