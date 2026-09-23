<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\model\concern;

use Closure;
use think\helper\Str;
use think\model\Collection;
use think\model\contract\Modelable;
use think\model\View;

/**
 * 模型数据转换处理.
 */
trait Conversion
{
    /**
     * 设置需要附加的输出属性.
     *
     * @param array $append 属性列表
     * @param bool  $merge  是否合并
     *
     * @return $this
     */
    public function append(array $append, bool $merge = false)
    {
        return $this->setOption('append', $merge ? array_merge($this->getOption('append'), $append) : $append);
    }

    /**
     * 设置需要隐藏的输出属性.
     *
     * @param array $hidden 属性列表
     * @param bool  $merge  是否合并
     *
     * @return $this
     */
    public function hidden(array $hidden, bool $merge = false)
    {
        return $this->setOption('hidden', $merge ? array_merge($this->getOption('hidden'), $hidden) : $hidden);
    }

    /**
     * 设置需要输出的属性.
     *
     * @param array $visible
     * @param bool  $merge   是否合并
     *
     * @return $this
     */
    public function visible(array $visible, bool $merge = false)
    {
        return $this->setOption('visible', $merge ? array_merge($this->getOption('visible'), $visible) : $visible);
    }

    /**
     * 设置属性的映射输出.
     *
     * @param array $map
     *
     * @return $this
     */
    public function mapping(array $map)
    {
        return $this->setOption('mapping', $map);
    }

    /**
     * 设置输出场景.
     *
     * @param string $scene
     *
     * @return $this
     */
    public function scene(string $scene)
    {
        $method = 'scene' . Str::studly($scene);
        if (method_exists($this, $method)) {
            call_user_func([$this, $method]);
        }
        return $this;
    }

    /**
     * 模型数据转数组.
     *
     * @return array
     */
    public function toArray(): array
    {
        $mapping = $this->getOption('mapping');
        foreach (['visible', 'hidden', 'append'] as $convert) {
            ${$convert} = $this->getOption($convert);
            foreach (${$convert} as $key => $val) {
                if (is_string($key)) {
                    $relation[$key][$convert] = $val;
                    unset(${$convert}[$key]);
                } elseif (str_contains($val, '.')) {
                    [$relName, $name]               = explode('.', $val);
                    $relation[$relName][$convert][] = $name;
                    unset(${$convert}[$key]);
                } elseif ($item = array_search($val, $mapping)) {
                    ${$convert}[$key] = $item;
                }
            }
        }
        $data  = $this->getData();
        $allow = array_diff($visible ?: array_keys($data), $hidden);

        $item = [];
        foreach ($data as $name => $val) {
            if ($val instanceof Modelable || $val instanceof Collection) {
                if (in_array($name, $hidden)) {
                    // 隐藏关联属性
                    unset($item[$name]);
                    continue;
                }

                if (!empty($relation[$name])) {
                    // 处理关联数据输出
                    foreach ($relation[$name] as $key => $attr) {
                        $val->$key($attr);
                    }
                }
                $item[$name] = $val->toArray();
            } elseif (empty($allow) || in_array($name, $allow)) {
                // 通过获取器输出
                $item[$name] = $this->getAttrOfWith($name, $val, $data);
            }

            if (array_key_exists($name, $item) && isset($mapping[$name])) {
                // 检查字段映射
                $item[$mapping[$name]] = $item[$name];
                unset($item[$name]);
            }
        }

        // 输出额外属性 必须定义获取器
        foreach ($this->getOption('append') as $key => $field) {
            if (is_numeric($key)) {
                if (strpos($field, '.')) {
                    [$name, $field] = explode('.', $field, 2);
                    $relation = $this->getRelationData($name, false);
                    $value    = $relation?->get($field);
                } else {
                    $value = $this->get($field);
                }
                $item[$field] = $value;
            } else {
                // 追加关联属性
                $relation = $this->getRelationData($key, false);
                foreach((array) $field as $key => $name) {
                    if (is_numeric($key)) {
                        $item[$name] = $relation?->get($name);
                    } else {
                        $item[$name] = $relation?->get($key);
                    }
                }
            } 
        }

        if ($this->getOption('convertNameToCamel')) {
            foreach ($item as $key => $val) {
                $name = Str::camel($key);
                if ($name !== $key) {
                    $item[$name] = $val;
                    unset($item[$key]);
                }
            }
        }

        return $item;
    }

    /**
     * 模型数据转Json.
     *
     * @param int $options json参数
     * @return string
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * 转换为数据集对象
     *
     * @param array|Collection $collection    数据集
     * @param string|null      $resultSetType 数据集类
     *
     * @return Collection
     */
    public function toCollection(iterable $collection = [], ?string $resultSetType = null): Collection
    {
        $resultSetType = $resultSetType ?: $this->getOption('resultSetType');

        if ($resultSetType && str_contains($resultSetType, '\\')) {
            $collection = new $resultSetType($collection);
        } else {
            $collection = new Collection($collection);
        }

        return $collection;
    }

    /**
     * 转换为视图模型
     *
     * @param string $class 视图模型类名
     * @return View
     */
    public function toView(string $class)
    {
        return is_subclass_of($class, View::class) ? new $class($this) : $this;
    }
}
