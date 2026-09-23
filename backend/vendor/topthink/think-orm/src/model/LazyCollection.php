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
declare(strict_types=1);

namespace think\model;

use think\db\LazyCollection as DbCollection;
use think\model\contract\Modelable as Model;

/**
 * 模型惰性集合类 - 用于高效处理大数据集.
 */
class LazyCollection extends DbCollection
{
    /**
     * 延迟预载入关联查询
     * @param array $relation 关联
     * @param mixed $cache    关联缓存
     * @param bol   $withJoin 是否JOIN
     * @return static
     */
    public function load(array $relation, $cache = false, $withJoin = false)
    {
        if (empty($relation)) {
            return $this;
        }

        return new static(function () use ($relation, $cache, $withJoin) {
            $items = [];
            foreach ($this->getIterator() as $key => $item) {
                $items[$key] = $item;
            }

            if (!empty($items)) {
                $first = reset($items);
                $first->eagerlyResultSet($items, $relation, [], $withJoin, $cache);
            }

            foreach ($items as $key => $item) {
                yield $key => $item;
            }
        });
    }

    /**
     * 按键值对集合进行分组
     * @param callable|string|int $groupBy 分组依据
     * @return static
     */
    public function group($groupBy)
    {
        return new static(function () use ($groupBy) {
            $groups = [];
            foreach ($this->getIterator() as $key => $value) {
                if (is_callable($groupBy)) {
                    $groupKey = $groupBy($value, $key);
                } else {
                    $groupKey = data_get($value, $groupBy);
                }

                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [];
                }
                $groups[$groupKey][$key] = $value;
            }

            foreach ($groups as $key => $group) {
                yield $key => new Collection($group);
            }
        });
    }

    /**
     * 对集合进行排序
     * @param callable|null $callback 排序回调
     * @return Collection
     */
    public function sort(?callable $callback = null): Collection
    {
        $items = $this->toArray();

        if (is_null($callback)) {
            asort($items);
        } else {
            uasort($items, $callback);
        }
        return new Collection($items);
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
     * @return static
     */
    public function hidden(array $hidden, bool $merge = false)
    {
        return new static(function () use ($hidden, $merge) {
            foreach ($this->getIterator() as $key => $model) {
                $model->hidden($hidden, $merge);
                yield $key => $model;
            }
        });
    }

    /**
     * 设置需要输出的属性.
     *
     * @param array $visible
     * @param bool  $merge   是否合并
     *
     * @return static
     */
    public function visible(array $visible, bool $merge = false)
    {
        return new static(function () use ($visible, $merge) {
            foreach ($this->getIterator() as $key => $model) {
                $model->visible($visible, $merge);
                yield $key => $model;
            }
        });
    }

    /**
     * 设置需要追加的输出属性.
     *
     * @param array $append 属性列表
     * @param bool  $merge  是否合并
     *
     * @return static
     */
    public function append(array $append, bool $merge = false)
    {
        return new static(function () use ($append, $merge) {
            foreach ($this->getIterator() as $key => $model) {
                $model->append($append, $merge);
                yield $key => $model;
            }
        });
    }

    /**
     * 设置属性映射.
     *
     * @param array $mapping 属性映射
     *
     * @return static
     */
    public function mapping(array $mapping)
    {
        return new static(function () use ($mapping) {
            foreach ($this->getIterator() as $key => $model) {
                $model->mapping($mapping);
                yield $key => $model;
            }
        });
    }

    /**
     * 设置模型输出场景.
     *
     * @param string $scene 场景名称
     *
     * @return static
     */
    public function scene(string $scene)
    {
        return new static(function () use ($scene) {
            foreach ($this->getIterator() as $key => $model) {
                $model->scene($scene);
                yield $key => $model;
            }
        });
    }

    /**
     * 设置数据字段获取器.
     *
     * @param string|array $name     字段名
     * @param callable     $callback 闭包获取器
     *
     * @return static
     */
    public function withAttr(string|array $name, ?callable $callback = null)
    {
        return new static(function () use ($name, $callback) {
            foreach ($this->getIterator() as $key => $model) {
                $model->withFieldAttr($name, $callback);
                yield $key => $model;
            }
        });
    }

    /**
     * 绑定（一对一）关联属性到当前模型.
     *
     * @param string $relation 关联名称
     * @param array  $attrs    绑定属性
     *
     * @return static
     */
    public function bindAttr(string $relation, array $attrs = [])
    {
        return new static(function () use ($relation, $attrs) {
            foreach ($this->getIterator() as $key => $model) {
                $model->bindAttr($relation, $attrs);
                yield $key => $model;
            }
        });
    }     
}