<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think\model;

use think\Collection as BaseCollection;
use think\Model;
use think\Paginator;

/**
 * 模型数据集类
 */
class Collection extends BaseCollection
{
    /**
     * 延迟预载入关联查询
     * @access public
     * @param  array|string $relation 关联
     * @return $this
     */
    public function load($relation)
    {
        if (!$this->isEmpty()) {
            $item = current($this->items);
            $item->eagerlyResultSet($this->items, (array) $relation);
        }

        return $this;
    }

    /**
     * 删除数据集的数据
     * @access public
     * @return bool
     */
    public function delete(): bool
    {
        $this->each(function (Model $model) {
            $model->delete();
        });

        return true;
    }

    /**
     * 更新数据
     * @access public
     * @param array $data       数据数组
     * @param array $allowField 允许字段
     * @return bool
     */
    public function update(array $data, array $allowField = []): bool
    {
        $this->each(function (Model $model) use ($data, $allowField) {
            if (!empty($allowField)) {
                $model->allowField($allowField);
            }

            $model->save($data);
        });

        return true;
    }

    /**
     * 设置需要隐藏的输出属性
     * @access public
     * @param  array $hidden 属性列表
     * @return $this
     */
    public function hidden(array $hidden)
    {
        $this->each(function (Model $model) use ($hidden) {
            $model->hidden($hidden);
        });

        return $this;
    }

    /**
     * 设置需要输出的属性
     * @access public
     * @param  array $visible
     * @return $this
     */
    public function visible(array $visible)
    {
        $this->each(function (Model $model) use ($visible) {
            $model->visible($visible);
        });

        return $this;
    }

    /**
     * 设置需要追加的输出属性
     * @access public
     * @param  array $append 属性列表
     * @return $this
     */
    public function append(array $append)
    {
        $this->each(function (Model $model) use ($append) {
            $model->append($append);
        });

        return $this;
    }

    /**
     * 设置父模型
     * @access public
     * @param  Model $parent 父模型
     * @return $this
     */
    public function setParent(Model $parent)
    {
        $this->each(function (Model $model) use ($parent) {
            $model->setParent($parent);
        });

        return $this;
    }

    /**
     * 设置数据字段获取器
     * @access public
     * @param  string|array $name       字段名
     * @param  callable     $callback   闭包获取器
     * @return $this
     */
    public function withAttr($name, $callback = null)
    {
        $this->each(function (Model $model) use ($name, $callback) {
            $model->withAttribute($name, $callback);
        });

        return $this;
    }

    /**
     * 绑定（一对一）关联属性到当前模型
     * @access protected
     * @param  string $relation 关联名称
     * @param  array  $attrs    绑定属性
     * @return $this
     * @throws Exception
     */
    public function bindAttr(string $relation, array $attrs = [])
    {
        $this->each(function (Model $model) use ($relation, $attrs) {
            $model->bindAttr($relation, $attrs);
        });

        return $this;
    }

    /**
     * 按指定键整理数据
     *
     * @access public
     * @param  mixed  $items    数据
     * @param  string $indexKey 键名
     * @return array
     */
    public function dictionary($items = null, string &$indexKey = null)
    {
        if ($items instanceof self || $items instanceof Paginator) {
            $items = $items->all();
        }

        $items = is_null($items) ? $this->items : $items;

        if ($items && empty($indexKey)) {
            $indexKey = $items[0]->getPk();
        }

        if (isset($indexKey) && is_string($indexKey)) {
            return array_column($items, null, $indexKey);
        }

        return $items;
    }

    /**
     * 比较数据集，返回差集
     *
     * @access public
     * @param  mixed  $items    数据
     * @param  string $indexKey 指定比较的键名
     * @return static
     */
    public function diff($items, string $indexKey = null)
    {
        if ($this->isEmpty()) {
            return new static($items);
        }

        $diff       = [];
        $dictionary = $this->dictionary($items, $indexKey);

        if (is_string($indexKey)) {
            foreach ($this->items as $item) {
                if (!isset($dictionary[$item[$indexKey]])) {
                    $diff[] = $item;
                }
            }
        }

        return new static($diff);
    }

    /**
     * 比较数据集，返回交集
     *
     * @access public
     * @param  mixed  $items    数据
     * @param  string $indexKey 指定比较的键名
     * @return static
     */
    public function intersect($items, string $indexKey = null)
    {
        if ($this->isEmpty()) {
            return new static([]);
        }

        $intersect  = [];
        $dictionary = $this->dictionary($items, $indexKey);

        if (is_string($indexKey)) {
            foreach ($this->items as $item) {
                if (isset($dictionary[$item[$indexKey]])) {
                    $intersect[] = $item;
                }
            }
        }

        return new static($intersect);
    }
}
