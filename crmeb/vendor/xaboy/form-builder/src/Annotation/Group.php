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

namespace FormBuilder\Annotation;

use FormBuilder\Contract\AnnotationInterface;

/**
 * @Annotation
 */
final class Group implements AnnotationInterface
{
    /**
     * @var int
     */
    public $id = 1;

    /**
     * @var string
     */
    public $tag;

    /**
     * @var string
     */
    public $className = '';

    public $span = 24;

    protected $children = [];

    public function parseRule($rule)
    {
        return $rule;
    }

    public function parseComponent($component)
    {
        return $component;
    }

    public function appendChildren($rule)
    {
        $this->children[] = $rule;
    }

    /**
     * @param string $UI
     * @return array
     */
    public function parse($UI)
    {
        $col = 'i-col';
        $row = 'row';
        if ($UI == 'elm') {
            $row = 'el-row';
            $col = 'el-col';
        }

        return [
            'type' => $col,
            'props' => [
                'span' => $this->span
            ],
            'children' => [
                [
                    'type' => is_null($this->tag) ? $row : $this->tag,
                    'children' => $this->children,
                    'attrs' => [
                        'class' => $this->className
                    ]
                ]
            ],
            'native' => true
        ];
    }
}