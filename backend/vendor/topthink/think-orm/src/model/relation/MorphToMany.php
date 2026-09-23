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

namespace think\model\relation;

use Closure;
use Exception;
use think\db\BaseQuery as Query;
use think\db\Raw;
use think\model\contract\Modelable as Model;
use think\model\Pivot;

/**
 * 多态多对多关联.
 */
class MorphToMany extends BelongsToMany
{
    /**
     * 多态关系的模型名映射别名的数组.
     *
     * @var array
     */
    protected static $morphMap = [];

    /**
     * 多态字段名.
     *
     * @var string
     */
    protected $morphType;

    /**
     * 多态模型名.
     *
     * @var string
     */
    protected $morphClass;

    /**
     * 是否反向关联.
     *
     * @var bool
     */
    protected $inverse;

    /**
     * 架构函数.
     *
     * @param Model  $parent    上级模型对象
     * @param string $model     模型名
     * @param string $middle    中间表名/模型名
     * @param string $morphKey  关联外键
     * @param string $morphType 多态字段名
     * @param string $localKey  当前模型关联键
     * @param bool   $inverse   反向关联
     */
    public function __construct(Model $parent, string $model, string $middle, string $morphType, string $morphKey, string $localKey, bool $inverse = false)
    {
        $this->morphType    = $morphType;
        $this->inverse      = $inverse;
        $this->morphClass   = $inverse ? $model : get_class($parent);

        if (isset(static::$morphMap[$this->morphClass])) {
            $this->morphClass = static::$morphMap[$this->morphClass];
        }

        $foreignKey = $inverse ? $morphKey : $localKey;
        $localKey   = $inverse ? $localKey : $morphKey;

        parent::__construct($parent, $model, $middle, $foreignKey, $localKey);
    }

    /**
     * 预载入关联查询（数据集）.
     *
     * @param array   $resultSet   数据集
     * @param string  $relation    当前关联名
     * @param array   $subRelation 子关联名
     * @param Closure $closure     闭包
     * @param array   $cache       关联缓存
     *
     * @return void
     */
    public function eagerlyResultSet(array &$resultSet, string $relation, array $subRelation, ?Closure $closure = null, array $cache = []): void
    {
        $pk     = $resultSet[0]->getPk();
        $range  = [];

        foreach ($resultSet as $result) {
            // 获取关联外键列表
            if (isset($result->$pk)) {
                $range[] = $result->$pk;
            }
        }

        if (!empty($range)) {
            // 查询关联数据
            $data = $this->eagerlyManyToMany([
                ['pivot.' . $this->localKey, 'in', array_unique($range)],
                ['pivot.' . $this->morphType, '=', $this->morphClass],
            ], $subRelation, $closure, $cache, true);

            // 关联数据封装
            foreach ($resultSet as $result) {
                if (!isset($data[$result->$pk])) {
                    $data[$result->$pk] = [];
                }

                $result->setRelation($relation, $this->resultSetBuild($data[$result->$pk]));
            }
        }
    }

    /**
     * 预载入关联查询（单个数据）.
     *
     * @param Model   $result      数据对象
     * @param string  $relation    当前关联名
     * @param array   $subRelation 子关联名
     * @param Closure $closure     闭包
     * @param array   $cache       关联缓存
     *
     * @return void
     */
    public function eagerlyResult(Model $result, string $relation, array $subRelation, ?Closure $closure = null, array $cache = []): void
    {
        $pk = $result->getPk();

        if (isset($result->$pk)) {
            $pk = $result->$pk;
            // 查询管理数据
            $data = $this->eagerlyManyToMany([
                ['pivot.' . $this->localKey, '=', $pk],
                ['pivot.' . $this->morphType, '=', $this->morphClass],
            ], $subRelation, $closure, $cache);

            // 关联数据封装
            if (!isset($data[$pk])) {
                $data[$pk] = [];
            }

            $result->setRelation($relation, $this->resultSetBuild($data[$pk]));
        }
    }

    /**
     * 关联统计
     *
     * @param Model   $result    数据对象
     * @param Closure $closure   闭包
     * @param string  $aggregate 聚合查询方法
     * @param string  $field     字段
     * @param string  $name      统计字段别名
     *
     * @return int
     */
    public function relationCount(Model $result, ?Closure $closure = null, string $aggregate = 'count', string $field = '*', ?string &$name = null)
    {
        $pk = $result->getPk();

        if (!isset($result->$pk)) {
            return 0;
        }

        if ($closure) {
            $closure($this->query, $name);
        }

        return $this->belongsToManyQuery($this->foreignKey, $this->localKey, [
            ['pivot.' . $this->localKey, '=', $result->$pk],
            ['pivot.' . $this->morphType, '=', $this->morphClass],
        ])->$aggregate($field);
    }

    /**
     * 获取关联统计子查询.
     *
     * @param Closure $closure   闭包
     * @param string  $aggregate 聚合查询方法
     * @param string  $field     字段
     * @param string  $name      统计字段别名
     *
     * @return string
     */
    public function getRelationCountQuery(?Closure $closure = null, string $aggregate = 'count', string $field = '*', ?string &$name = null): string
    {
        if ($closure) {
            $closure($this->query, $name);
        }

        return $this->belongsToManyQuery($this->foreignKey, $this->localKey, [
            ['pivot.' . $this->localKey, 'exp', new Raw('=' . $this->parent->getTable(true) . '.' . $this->parent->getPk())],
            ['pivot.' . $this->morphType, '=', $this->morphClass],
        ])->fetchSql()->$aggregate($field);
    }

    /**
     * BELONGS TO MANY 关联查询.
     *
     * @param string $foreignKey 关联模型关联键
     * @param string $localKey   当前模型关联键
     * @param array  $condition  关联查询条件
     *
     * @return Query
     */
    protected function belongsToManyQuery(string $foreignKey, string $localKey, array $condition = []): Query
    {
        // 关联查询封装
        $tableName  = $this->query->getTable();
        $table      = $this->pivot->db()->getTable();
        $fields     = $this->getQueryFields($tableName);

        $query = $this->query
            ->field($fields)
            ->tableField(true, $table, 'pivot', 'pivot__');

        if (empty($this->baseQuery)) {
            $relationFk = $this->query->getPk();
            $query->join([$table => 'pivot'], 'pivot.' . $foreignKey . '=' . $tableName . '.' . $relationFk)
                ->where($condition);
        }

        return $query;
    }

    /**
     * 多对多 关联模型预查询.
     *
     * @param array   $where       关联预查询条件
     * @param array   $subRelation 子关联
     * @param Closure $closure     闭包
     * @param array   $cache       关联缓存
     * @param bool    $collection  是否数据集查询
     *
     * @return array
     */
    protected function eagerlyManyToMany(array $where, array $subRelation = [], ?Closure $closure = null, array $cache = [], bool $collection = false): array
    {
        if ($closure) {
            $closure($this->query);
        }

        $withLimit = $this->query->getOption('limit');
        if ($withLimit && $collection) {
            $this->query->removeOption('limit');
        }

        if ($this->isOneofMany) {
            // 仅获取一条关联数据
            if (!$collection) {
                $this->query->limit(1);
            } else {
                $withLimit = 1;
            }
        }

        // 预载入关联查询 支持嵌套预载入
        $list = $this->belongsToManyQuery($this->foreignKey, $this->localKey, $where)
            ->with($subRelation)
            ->cache($cache[0] ?? false, $cache[1] ?? null, $cache[2] ?? null)
            ->lazy();

        // 组装模型数据
        $data = [];
        foreach ($list as $set) {
            $pivot = $set->getRelation('pivot');
            $key   = $pivot[$this->localKey];

            if ($withLimit && isset($data[$key]) && count($data[$key]) >= $withLimit) {
                continue;
            }

            $set->setRelation($this->pivotDataName, $this->newPivot($pivot));

            $data[$key][] = $set;
        }

        return $data;
    }

    /**
     * 附加关联的一个中间表数据.
     *
     * @param mixed $data  数据 可以使用数组、关联模型对象 或者 关联对象的主键
     * @param array $pivot 中间表额外数据
     *
     * @return array|Pivot
     */
    public function attach($data, array $pivot = [])
    {
        if (is_array($data)) {
            if (key($data) === 0) {
                $id = $data;
            } else {
                // 保存关联表数据
                $model = new $this->model();
                $id = $model->insertGetId($data);
            }
        } elseif (is_numeric($data) || is_string($data)) {
            // 根据关联表主键直接写入中间表
            $id = $data;
        } elseif ($data instanceof Model) {
            // 根据关联表主键直接写入中间表
            $id = $data->getKey();
        }

        if (!empty($id)) {
            // 保存中间表数据
            $pivot[$this->localKey]  = $this->parent->getKey();
            $pivot[$this->morphType] = $this->morphClass;

            $result = [];
            foreach ((array) $ids as $id) {
                $pivot[$this->foreignKey] = $id;
                $object = $this->newPivot();
                $object->replace()->save($pivot);

                $result[] = $object;
            }

            if (count($result) == 1) {
                // 返回中间表模型对象
                $result = $result[0];
            }

            return $result;
        } else {
            throw new Exception('miss relation data');
        }
    }

    /**
     * 判断是否存在关联数据.
     *
     * @param mixed $data 数据 可以使用关联模型对象 或者 关联对象的主键
     *
     * @return Pivot|false
     */
    public function attached($data)
    {
        if ($data instanceof Model) {
            $id = $data->getKey();
        } else {
            $id = $data;
        }

        $pivot = $this->pivot
            ->where($this->localKey, $this->parent->getKey())
            ->where($this->morphType, $this->morphClass)
            ->where($this->foreignKey, $id)
            ->find();

        return $pivot ?: false;
    }

    /**
     * 解除关联的一个中间表数据.
     *
     * @param int|array $data        数据 可以使用关联对象的主键
     * @param bool      $relationDel 是否同时删除关联表数据
     *
     * @return int
     */
    public function detach($data = null, bool $relationDel = false): int
    {
        if (is_array($data)) {
            $id = $data;
        } elseif (is_numeric($data) || is_string($data)) {
            // 根据关联表主键直接写入中间表
            $id = $data;
        } elseif ($data instanceof Model) {
            // 根据关联表主键直接写入中间表
            $id = $data->getKey();
        }

        // 删除中间表数据
        $pivot = [
            [$this->localKey, '=', $this->parent->getKey()],
            [$this->morphType, '=', $this->morphClass],
        ];

        if (isset($id)) {
            $pivot[] = [$this->foreignKey, is_array($id) ? 'in' : '=', $id];
        }

        $result = $this->newPivot()->where($pivot)->delete();

        // 删除关联表数据
        if (isset($id) && $relationDel) {
            $model = $this->model;
            $model::destroy($id);
        }

        return $result;
    }

    /**
     * 数据同步.
     *
     * @param array $ids
     * @param bool  $detaching
     *
     * @return array
     */
    public function sync(array $ids, bool $detaching = true): array
    {
        $changes = [
            'attached' => [],
            'detached' => [],
            'updated'  => [],
        ];

        $current = $this->pivot
            ->where($this->localKey, $this->parent->getKey())
            ->where($this->morphType, $this->morphClass)
            ->column($this->foreignKey);

        $records = [];

        foreach ($ids as $key => $value) {
            if (!is_array($value)) {
                $records[$value] = [];
            } else {
                $records[$key] = $value;
            }
        }

        $detach = array_diff($current, array_keys($records));

        if ($detaching && count($detach) > 0) {
            $this->detach($detach);
            $changes['detached'] = $detach;
        }

        foreach ($records as $id => $attributes) {
            if (!in_array($id, $current)) {
                $this->attach($id, $attributes);
                $changes['attached'][] = $id;
            } elseif (count($attributes) > 0) {
                $this->detach($id);
                $this->attach($id, $attributes);
                $changes['updated'][] = $id;
            }
        }

        return $changes;
    }

    /**
     * 执行基础查询（仅执行一次）.
     *
     * @return void
     */
    protected function baseQuery(): void
    {
        if (empty($this->baseQuery)) {
            $foreignKey = $this->foreignKey;
            $localKey = $this->localKey;

            // 关联查询
            $this->belongsToManyQuery($foreignKey, $localKey, [
                ['pivot.' . $localKey, '=', $this->parent->getKey()],
                ['pivot.' . $this->morphType, '=', $this->morphClass],
            ]);

            $this->baseQuery = true;
        }
    }

    /**
     * 设置或获取多态关系的模型名映射别名的数组.
     *
     * @param array|null $map
     * @param bool       $merge
     *
     * @return array
     */
    public static function morphMap(?array $map = null, $merge = true): array
    {
        if (is_array($map)) {
            static::$morphMap = $merge && static::$morphMap
                ? $map + static::$morphMap : $map;
        }

        return static::$morphMap;
    }
}
