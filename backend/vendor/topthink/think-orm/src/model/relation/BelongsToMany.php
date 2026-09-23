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
use think\Collection;
use think\db\BaseQuery as Query;
use think\db\exception\DbException as Exception;
use think\db\Raw;
use think\helper\Str;
use think\model\contract\Modelable as Model;
use think\model\Pivot;
use think\model\Relation;

/**
 * 多对多关联类.
 */
class BelongsToMany extends Relation
{
    /**
     * 中间表表名.
     *
     * @var string
     */
    protected $middle;

    /**
     * 中间表模型名称.
     *
     * @var string
     */
    protected $pivotName;

    /**
     * 中间表模型对象
     *
     * @var Pivot
     */
    protected $pivot;

    /**
     * 中间表数据名称.
     *
     * @var string
     */
    protected $pivotDataName = 'pivot';

    /**
     * 绑定的关联属性.
     *
     * @var array
     */
    protected $bindAttr = [];

    /**
     * 架构函数.
     *
     * @param Model  $parent     上级模型对象
     * @param string $model      模型名
     * @param string $middle     中间表/模型名
     * @param string $foreignKey 关联模型外键
     * @param string $localKey   当前模型关联键
     */
    public function __construct(Model $parent, string $model, string $middle, string $foreignKey, string $localKey)
    {
        $this->parent     = $parent;
        $this->model      = $model;
        $this->foreignKey = $foreignKey;
        $this->localKey   = $localKey;

        if (str_contains($middle, '\\')) {
            $this->pivotName = $middle;
            $this->middle    = Str::snake(class_basename($middle));
        } else {
            $this->middle = $middle;
        }

        $this->query = (new $model())->db();
        $this->pivot = $this->newPivot();
    }

    /**
     * 设置中间表模型.
     *
     * @param $pivot
     *
     * @return $this
     */
    public function pivot(string $pivot)
    {
        $this->pivotName = $pivot;

        return $this;
    }

    /**
     * 设置中间表数据名称.
     *
     * @param string $name
     *
     * @return $this
     */
    public function name(string $name)
    {
        $this->pivotDataName = $name;

        return $this;
    }

    /**
     * 绑定关联表的属性到父模型属性.
     *
     * @param array $attr 要绑定的属性列表
     *
     * @return $this
     */
    public function bind(array $attr)
    {
        $this->bindAttr = $attr;

        return $this;
    }

    /**
     * 实例化中间表模型.
     *
     * @param $data
     *
     * @throws Exception
     *
     * @return Pivot
     */
    protected function newPivot(array $data = []): Pivot
    {
        $class = $this->pivotName ?: Pivot::class;
        $pivot = new $class($data, $this->parent, $this->middle);

        if ($pivot instanceof Pivot) {
            return $pivot;
        } else {
            throw new Exception('pivot model must extends: \think\model\Pivot');
        }
    }

    /**
     * 延迟获取关联数据.
     *
     * @param array   $subRelation 子关联名
     * @param Closure $closure     闭包查询条件
     *
     * @return Collection
     */
    public function getRelation(array $subRelation = [], ?Closure $closure = null): Collection
    {
        if ($closure) {
            $closure($this->query);
        }

        return $this->relation($subRelation)->select();
    }

    /**
     * 组装Pivot模型.
     *
     * @param Model $result 模型对象
     *
     * @return array
     */
    protected function matchPivot(Model $result): array
    {
        $pivot    = $result->getRelation('pivot');
        $bindAttr = $this->query->getOption('bind_attr');
        if (empty($bindAttr)) {
            $bindAttr = $this->bindAttr;
        }

        foreach ($pivot as $attr => $val) {
            $pos = array_search($attr, $bindAttr);
            if (false !== $pos) {
                // 中间表属性绑定
                $key = !is_numeric($pos) ? $pos : $attr;
                if (null !== $result->getOrigin($key)) {
                    throw new Exception('bind attr has exists:' . $attr);
                }
                $result->set($key, $val);
            }
        }

        $result->setRelation($this->pivotDataName, $this->newPivot($pivot));

        return $pivot;
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param string     $operator 比较操作符
     * @param integer    $count 个数
     * @param string     $id 关联表的统计字段
     * @param string     $joinType JOIN类型
     * @param Query|null $query Query对象
     * @return Query
     */
    public function has(string $operator = '>=', int $count = 1, string $id = '*', string $joinType = '', ?Query $query = null): Query
    {
        $table      = $this->query->getTable();
        $pivot      = $this->pivot->getTable();
        $model      = Str::snake(class_basename($this->parent));
        $relation   = Str::snake(class_basename($this->model));
        $query      = $query ?: $this->parent->db();
        $alias      = $query->getAlias() ?: $model;

        if ('=' === $operator && 0 === $count) {
            return $query->alias($alias)
                ->whereNotExists(function ($query) use ($pivot, $alias, $relation, $table) {
                    $query->table([$pivot => 'pivot'])
                        ->field('pivot.' . $this->foreignKey)
                        ->join($table . ' ' . $relation, $relation . '.' . $this->query->getPk() . '= pivot.' . $this->foreignKey)
                        ->whereColumn($alias . '.' . $this->parent->getPk(), 'pivot.' . $this->localKey);

                    $this->getRelationSoftDelete($query, $relation);
                });
        }

        $query->alias($alias)
            ->field($model . '.*')
            ->join([$pivot => 'pivot'], 'pivot.' . $this->localKey . '=' . $alias . '.' . $this->parent->getPk(), $joinType)
            ->join($table . ' ' . $relation, $relation . '.' . $this->query->getPk() . '= pivot.' . $this->foreignKey, $joinType)
            ->group($alias . '.' . $this->parent->getPk())
            ->having('count(' . $id . ')' . $operator . $count);
        return $this->getRelationSoftDelete($query, $relation);
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param array|Closure  $where 查询条件（数组或者闭包）
     * @param mixed          $fields 字段
     * @param string         $joinType JOIN类型
     * @param Query|null     $query Query对象
     * @return Query
     */
    public function hasWhere($where = [], $fields = null, string $joinType = '', ?Query $query = null, string $logic = '', string $relationAlias = ''): Query
    {
        $table    = $this->query->getTable();
        $pivot    = $this->pivot->getTable();
        $model    = Str::snake(class_basename($this->parent));
        $relation = Str::snake(class_basename($this->model));
        $query    = $query ?: $this->parent->db();
        $alias    = $query->getAlias() ?: $model;
        $fields   = $this->getRelationQueryFields($fields, $alias);
        $relAlias = $relationAlias ?: $relation;

        $query->alias($alias)
            ->join([$pivot => 'pivot'], 'pivot.' . $this->localKey . '=' . $alias . '.' . $this->parent->getPk(), $joinType)
            ->join([$table => $relAlias], $relAlias . '.' . $this->query->getPk() . '= pivot.' . $this->foreignKey, $joinType)
            ->group($alias . '.' . $this->parent->getPk())
            ->field($fields);

        return $this->getRelationSoftDelete($query, $relAlias, $where, $logic);            
    }

    /**
     * 设置中间表的查询条件.
     *
     * @param string $field
     * @param string $op
     * @param mixed  $condition
     *
     * @return $this
     */
    public function wherePivot($field, $op = null, $condition = null)
    {
        $this->query->where('pivot.' . $field, $op, $condition);

        return $this;
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
        $localKey = $this->localKey;
        $pk       = $resultSet[0]->getPk();
        $range    = [];

        foreach ($resultSet as $result) {
            // 获取关联外键列表
            if (isset($result->$pk)) {
                $range[] = $result->$pk;
            }
        }

        if (!empty($range)) {
            // 查询关联数据
            $range = array_unique($range);
            $data  = $this->eagerlyManyToMany([
                ['pivot.' . $localKey, 'in', $range],
            ], $subRelation, $closure, $cache, count($range) > 1 ? true : false);

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

        if (is_string($pk) && isset($result->$pk)) {
            $pk = $result->$pk;
            // 查询管理数据
            $data = $this->eagerlyManyToMany([
                ['pivot.' . $this->localKey, '=', $pk],
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
    public function relationCount(Model $result, ?Closure $closure = null, string $aggregate = 'count', string $field = 'id',  ? string &$name = null)
    {
        $pk = $result->getPk();

        if (!isset($result->$pk)) {
            return 0;
        }

        $pk = $result->$pk;

        if ($closure) {
            $closure($this->query, $name);
        }

        return $this->belongsToManyQuery($this->foreignKey, $this->localKey, [
            ['pivot.' . $this->localKey, '=', $pk],
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
    public function getRelationCountQuery(?Closure $closure = null, string $aggregate = 'count', string $field = 'id',  ? string &$name = null) : string
    {
        if ($closure) {
            $closure($this->query, $name);
        }

        $alias = Str::snake(class_basename($this->model));
        $alias = $this->query->getAlias() ?: $alias . '_' . $aggregate;
        if (!str_contains($field, '.')) {
            $field = $alias . '.' . $field;
        }

        $this->query->alias($alias);
        return $this->belongsToManyQuery($this->foreignKey, $this->localKey, [
            [
                'pivot.' . $this->localKey, 'exp', new Raw('=' . $this->parent->getTable(true) . '.' . $this->parent->getPk()),
            ],
        ])->fetchSql()->$aggregate($field);
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
    protected function eagerlyManyToMany(array $where, array $subRelation = [], ?Closure $closure = null, array $cache = [], bool $collection = false) : array
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
            $pivot = $this->matchPivot($set);
            $key   = $pivot[$this->localKey];

            if ($withLimit && isset($data[$key]) && count($data[$key]) >= $withLimit) {
                continue;
            }

            $data[$key][] = $set;
        }

        return $data;
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
        if (empty($this->baseQuery)) {
            $tableName = $this->query->getTable(true);
            $table     = $this->pivot->db()->getTable();
            $fields    = $this->getQueryFields($tableName);

            $this->query
                ->field($fields)
                ->tableField(true, $table, 'pivot', 'pivot__')
                ->join([$table => 'pivot'], 'pivot.' . $foreignKey . '=' . $tableName . '.' . $this->query->getPk())
                ->where($condition);
        }

        return $this->query;
    }

    /**
     * 保存（新增）当前关联数据对象
     *
     * @param mixed $data  数据 可以使用数组 关联模型对象 和 关联对象的主键
     * @param array $pivot 中间表额外数据
     *
     * @return array|Pivot
     */
    public function save($data, array $pivot = [])
    {
        // 保存关联表/中间表数据
        return $this->attach($data, $pivot);
    }

    /**
     * 批量保存当前关联数据对象
     *
     * @param iterable $dataSet   数据集
     * @param array    $pivot     中间表额外数据
     * @param bool     $samePivot 额外数据是否相同
     *
     * @return array|false
     */
    public function saveAll(iterable $dataSet, array $pivot = [], bool $samePivot = false)
    {
        $result = [];

        foreach ($dataSet as $key => $data) {
            if (!$samePivot) {
                $pivotData = $pivot[$key] ?? [];
            } else {
                $pivotData = $pivot;
            }

            $result[] = $this->attach($data, $pivotData);
        }

        return empty($result) ? false : $result;
    }

    /**
     * 附加关联的一个中间表数据.
     *
     * @param mixed $data  数据 可以使用数组、关联模型对象 或者 关联对象的主键
     * @param array $pivot 中间表额外数据
     *
     * @throws Exception
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
                $id    = $model->insertGetId($data);
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
            $pivot[$this->localKey] = $this->parent->getKey();

            $ids = (array) $id;
            foreach ($ids as $id) {
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
        $pivot   = [];
        $pivot[] = [$this->localKey, '=', $this->parent->getKey()];

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
            $localKey   = $this->localKey;

            $this->query->filter(function ($result, $options) {
                $this->matchPivot($result);
            });

            // 关联查询
            if (null === $this->parent->getKey()) {
                $condition = ['pivot.' . $localKey, 'exp', new Raw('=' . $this->parent->getTable(true) . '.' . $this->parent->getPk())];
            } else {
                $condition = ['pivot.' . $localKey, '=', $this->parent->getKey()];
            }

            $this->belongsToManyQuery($foreignKey, $localKey, [$condition]);

            $this->baseQuery = true;
        }
    }
}
