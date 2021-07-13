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


use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader as Render;
use FormBuilder\Contract\AnnotationInterface;
use FormBuilder\FormHandle;
use FormBuilder\Util;

class AnnotationReader
{
    protected static $isInit = false;

    /**
     * @var Render
     */
    protected $annotationReader;

    /**
     * @var FormHandle
     */
    protected $handle;

    public function __construct(FormHandle $handle)
    {
        if (!self::$isInit) {
            AnnotationRegistry::registerLoader('class_exists');
            self::$isInit = true;
        }

        $this->annotationReader = new Render();
        $this->handle = $handle;
    }

    public function getRender()
    {
        return $this->annotationReader;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function render()
    {
        $reflectionClass = new \ReflectionClass($this->handle);
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        $rule = [];
        $except = $this->handle->getExcept();
        foreach ($methods as $method) {
            $field = preg_replace('/^(.+)(Field|_field)$/', '$1', $method->name);
            $value = null;
            if ($field != $method->name && !in_array($field, $except)) {
                $params = $method->getParameters();
                if (isset($params[0]) && ($dep = $params[0]->getClass())) {
                    if (in_array('FormBuilder\\Contract\\FormComponentInterface', $dep->getInterfaceNames())) {
                        $componentClass = $dep->getName();
                        $value = $method->invokeArgs($this->handle, [new $componentClass($field, $this->handle->getFieldTitle($field))]);
                    }
                }
                if (is_null($value)) $value = $method->invoke($this->handle);
                if (!is_null($value) && (($isArray = is_array($value)) || Util::isComponent($value))) {
                    $rule[] = compact('value', 'method', 'isArray');
                }
            }
        }
        return $this->parse($rule);
    }

    /**
     * @param $rules
     * @return array
     */
    protected function parse($rules)
    {
        $formRule = [];
        $groupList = [];
        foreach ($rules as $rule) {
            $annotations = $this->annotationReader->getMethodAnnotations($rule['method']);
            $value = $rule['value'];
            $group = null;
            foreach ($annotations as $annotation) {
                if (!$annotation instanceof AnnotationInterface) continue;
                if ($annotation instanceof Group) {
                    $group = $annotation;
                } else {
                    $value = $rule['isArray'] ? $annotation->parseRule($value) : $annotation->parseComponent($value);
                }
            }

            if (!is_null($group)) {
                if (!isset($groupList[$group->id])) {
                    $groupList[$group->id] = $group;
                    $formRule[] = $group;
                }
                $groupList[$group->id]->appendChildren($value);
            } else {
                $formRule[] = $value;
            }
        }

        foreach ($formRule as $k => $v) {
            if ($v instanceof Group) {
                $formRule[$k] = $v->parse($this->handle->ui());
            }
        }

        return $formRule;
    }
}