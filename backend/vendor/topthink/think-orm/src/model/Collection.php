<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: zhangyajun <448901948@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\model;

use think\Collection as BaseCollection;
use think\model\contract\Modelable as Model;
use think\Paginator;

/**
 * 模型数据集类.
 *
 * @template TKey of array-key
 * @template TModel of \think\Model
 *
 * @extends BaseCollection<TKey, TModel>
 */
class Collection extends BaseCollection
{
    /**
     * 延迟预载入关联查询.
     *
     * @param array $relation 关联
     * @param mixed $cache    关联缓存
     * @param bol   $withJoin 是否JOIN
     *
     * @return $this
     */
    public function load(array $relation, $cache = false, bool $withJoin = false)
    {
        if (!$this->isEmpty()) {
            $item = current($this->items);
            $item->eagerlyResultSet($this->items, $relation, [], $withJoin, $cache);
        }

        return $this;
    }

    /**
     * 转换为视图模型
     *
     * @param string $view 视图类名
     *
     * @return $this
     */
    public function toView(string $view)
    {
        return $this->map(function (Model $model) use($view) {
            return $model->toView($view);
        });
    }

    /**
     * 删除数据集的数据.
     *
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
     * 更新数据.
     *
     * @param array $data       数据数组
     * @param array $allowField 允许字段
     *
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
     * 设置需要隐藏的输出属性.
     *
     * @param array $hidden 属性列表
     * @param bool  $merge  是否合并
     *
     * @return $this
     */
    public function hidden(array $hidden, bool $merge = false)
    {
        $this->each(function (Model $model) use ($hidden, $merge) {
            $model->hidden($hidden, $merge);
        });

        return $this;
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
        $this->each(function (Model $model) use ($visible, $merge) {
            $model->visible($visible, $merge);
        });

        return $this;
    }

    /**
     * 设置需要追加的输出属性.
     *
     * @param array $append 属性列表
     * @param bool  $merge  是否合并
     *
     * @return $this
     */
    public function append(array $append, bool $merge = false)
    {
        $this->each(function (Model $model) use ($append, $merge) {
            $model->append($append, $merge);
        });

        return $this;
    }

    /**
     * 设置属性映射.
     *
     * @param array $mapping 属性映射
     *
     * @return $this
     */
    public function mapping(array $mapping)
    {
        $this->each(function (Model $model) use ($mapping) {
            $model->mapping($mapping);
        });

        return $this;
    }

    /**
     * 设置模型输出场景.
     *
     * @param string $scene 场景名称
     *
     * @return $this
     */
    public function scene(string $scene)
    {
        $this->each(function (Model $model) use ($scene) {
            $model->scene($scene);
        });

        return $this;
    }

    /**
     * 设置数据字段获取器.
     *
     * @param string|array $name     字段名
     * @param callable     $callback 闭包获取器
     *
     * @return $this
     */
    public function withAttr(string|array $name, ?callable $callback = null)
    {
        $this->each(function (Model $model) use ($name, $callback) {
            $model->withFieldAttr($name, $callback);
        });

        return $this;
    }

    /**
     * 绑定（一对一）关联属性到当前模型.
     *
     * @param string $relation 关联名称
     * @param array  $attrs    绑定属性
     *
     * @throws Exception
     *
     * @return $this
     */
    public function bindAttr(string $relation, array $attrs = [])
    {
        $this->each(function (Model $model) use ($relation, $attrs) {
            $model->bindAttr($relation, $attrs);
        });

        return $this;
    }

    /**
     * 按指定键整理数据.
     *
     * @param mixed       $items    数据
     * @param string|null $indexKey 键名
     *
     * @return array
     */
    public function dictionary($items = null, ?string &$indexKey = null)
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
     * 比较数据集，返回差集.
     *
     * @param mixed       $items    数据
     * @param string|null $indexKey 指定比较的键名
     *
     * @return static
     */
    public function diff($items, ?string $indexKey = null)
    {
        if ($this->isEmpty()) {
            return new static($items);
        }

        $diff = [];
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
     * 比较数据集，返回交集.
     *
     * @param mixed       $items    数据
     * @param string|null $indexKey 指定比较的键名
     *
     * @return static
     */
    public function intersect($items, ?string $indexKey = null)
    {
        if ($this->isEmpty()) {
            return new static([]);
        }

        $intersect = [];
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
