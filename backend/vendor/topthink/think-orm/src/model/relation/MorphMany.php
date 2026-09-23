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
use think\helper\Str;
use think\model\contract\Modelable as Model;
use think\model\Relation;

/**
 * 多态一对多关联.
 */
class MorphMany extends Relation
{
    /**
     * 多态关联外键.
     *
     * @var string
     */
    protected $morphKey;

    /**
     * 多态字段名.
     *
     * @var string
     */
    protected $morphType;

    /**
     * 多态类型.
     *
     * @var string
     */
    protected $type;

    /**
     * 架构函数.
     *
     * @param Model  $parent    上级模型对象
     * @param string $model     模型名
     * @param string $morphKey  关联外键
     * @param string $morphType 多态字段名
     * @param string $type      多态类型
     */
    public function __construct(Model $parent, string $model, string $morphKey, string $morphType, string $type)
    {
        $this->parent    = $parent;
        $this->model     = $model;
        $this->type      = $type;
        $this->morphKey  = $morphKey;
        $this->morphType = $morphType;
        $this->query     = (new $model())->db();
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

        $this->baseQuery();

        return $this->query->relation($subRelation)->select();
    }

    /**
     * 根据关联条件查询当前模型.
     *
     * @param string $operator 比较操作符
     * @param int    $count    个数
     * @param string $id       关联表的统计字段
     * @param string $joinType JOIN类型
     * @param Query  $query    Query对象
     *
     * @return Query
     */
    public function has(string $operator = '>=', int $count = 1, string $id = '*', string $joinType = '', ?Query $query = null)
    {
        $model    = Str::snake(class_basename($this->parent));
        $relation = Str::snake(class_basename($this->model));
        $table    = $this->query->getTable();
        $query    = $query ?: $this->parent->db();
        $alias    = $query->getAlias() ?: $model;

        $query->alias($alias)
            ->field($alias . '.*')
            ->join([$table => $relation], $alias . '.' . $this->parent->getPk() . '=' . $relation . '.' . $this->morphKey)
            ->where($relation . '.' . $this->morphType, '=', $this->type)
            ->group($relation . '.' . $this->morphKey)
            ->having('count(' . $id . ')' . $operator . $count);

        return $this->getRelationSoftDelete($query, $relation);
    }

    /**
     * 根据关联条件查询当前模型.
     *
     * @param mixed  $where    查询条件（数组或者闭包）
     * @param mixed  $fields   字段
     * @param string $joinType JOIN类型
     * @param Query  $query    Query对象
     *
     * @return Query
     */
    public function hasWhere($where = [], $fields = null, string $joinType = '', ?Query $query = null, string $logic = '', string $relationAlias = '')
    {
        $table    = $this->query->getTable();
        $query    = $query ?: $this->parent->db();
        $model    = Str::snake(class_basename($this->parent));
        $relation = Str::snake(class_basename($this->model));
        $alias    = $query->getAlias() ?: $model;
        $fields   = $this->getRelationQueryFields($fields, $alias);
        $relAlias = $relationAlias ?: $relation;

        $query->alias($alias)
            ->join([$table => $relAlias], $alias . '.' . $this->parent->getPk() . '=' . $relAlias . '.' . $this->morphKey, $joinType)
            ->where($relAlias . '.' . $this->morphType, '=', $this->type)
            ->group($relAlias . '.' . $this->morphKey)
            ->field($fields);

        return $this->getRelationSoftDelete($query, $relAlias, $where, $logic);
    }

    /**
     * 预载入关联查询.
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
        $morphType = $this->morphType;
        $morphKey  = $this->morphKey;
        $type      = $this->type;
        $range     = [];

        foreach ($resultSet as $result) {
            $pk = $result->getPk();
            // 获取关联外键列表
            if (isset($result->$pk)) {
                $range[] = $result->$pk;
            }
        }

        if (!empty($range)) {
            $where = [
                [$morphKey, 'in', array_unique($range)],
                [$morphType, '=', $type],
            ];
            $data = $this->eagerlyMorphToMany($where, $subRelation, $closure, $cache, true);

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
     * 预载入关联查询.
     *
     * @param Model   $result      数据对象
     * @param string  $relation    当前关联名
     * @param array   $subRelation 子关联名
     * @param Closure $closure     闭包
     * @param array   $cache       关联缓存
     *
     * @return void
     */
    public function eagerlyResult(Model $result, string $relation, array $subRelation = [], ?Closure $closure = null, array $cache = []): void
    {
        $pk = $result->getPk();

        if (isset($result->$pk)) {
            $key  = $result->$pk;
            $data = $this->eagerlyMorphToMany([
                [$this->morphKey, '=', $key],
                [$this->morphType, '=', $this->type],
            ], $subRelation, $closure, $cache);

            if (!isset($data[$key])) {
                $data[$key] = [];
            }

            $result->setRelation($relation, $this->resultSetBuild($data[$key]));
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
     * @return mixed
     */
    public function relationCount(Model $result, ?Closure $closure = null, string $aggregate = 'count', string $field = 'id',  ? string &$name = null)
    {
        $pk = $result->getPk();

        if (!isset($result->$pk)) {
            return 0;
        }

        if ($closure) {
            $closure($this->query, $name);
        }

        return $this->query
            ->where([
                [$this->morphKey, '=', $result->$pk],
                [$this->morphType, '=', $this->type],
            ])
            ->$aggregate($field);
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
        return $this->query
            ->alias($alias)
            ->whereColumn($alias . '.' . $this->morphKey, $this->parent->getTable(true) . '.' . $this->parent->getPk())
            ->where($alias . '.' . $this->morphType, '=', $this->type)
            ->fetchSql()
            ->$aggregate($field);
    }

    /**
     * 多态一对多 关联模型预查询.
     *
     * @param array   $where       关联预查询条件
     * @param array   $subRelation 子关联
     * @param Closure $closure     闭包
     * @param array   $cache       关联缓存
     * @param bool    $collection  是否数据集查询
     *
     * @return array
     */
    protected function eagerlyMorphToMany(array $where, array $subRelation = [], ?Closure $closure = null, array $cache = [], bool $collection = false) : array
    {
        // 预载入关联查询 支持嵌套预载入
        $this->query->removeOption('where');

        if ($closure) {
            $this->baseQuery = true;
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

        $list = $this->query
            ->where($where)
            ->with($subRelation)
            ->cache($cache[0] ?? false, $cache[1] ?? null, $cache[2] ?? null)
            ->lazy();

        // 组装模型数据
        $data     = [];
        $morphKey = $this->morphKey;
        foreach ($list as $set) {
            $key = $set->$morphKey;

            if ($withLimit && isset($data[$key]) && count($data[$key]) >= $withLimit) {
                continue;
            }

            $data[$key][] = $set;
        }

        return $data;
    }

    /**
     * 保存（新增）当前关联数据对象
     *
     * @param array|Model $data    数据 可以使用数组 关联模型对象
     * @param bool  $replace 是否自动识别更新和写入
     *
     * @return Model|false
     */
    public function save(array | Model $data, bool $replace = true)
    {
        $model = $this->make();

        return $model->replace($replace)->save($data) ? $model : false;
    }

    /**
     * 创建关联对象实例.
     *
     * @param array|Model $data
     *
     * @return Model
     */
    public function make($data = []): Model
    {
        if ($data instanceof Model) {
            $data = $data->getData();
        }

        // 保存关联表数据
        $pk = $this->parent->getPk();

        $data[$this->morphKey]  = $this->parent->$pk;
        $data[$this->morphType] = $this->type;

        return (new $this->model($data))->setSuffix($this->getModel()->getSuffix());
    }

    /**
     * 批量保存当前关联数据对象
     *
     * @param iterable $dataSet 数据集
     * @param bool     $replace 是否自动识别更新和写入
     *
     * @return array|false
     */
    public function saveAll(iterable $dataSet, bool $replace = true)
    {
        $result = [];

        foreach ($dataSet as $key => $data) {
            $result[] = $this->save($data, $replace);
        }

        return empty($result) ? false : $result;
    }

    /**
     * 获取多态关联外键.
     *
     * @return string
     */
    public function getMorphKey()
    {
        return $this->morphKey;
    }

    /**
     * 获取多态字段名.
     *
     * @return string
     */
    public function getMorphType()
    {
        return $this->morphType;
    }

    /**
     * 获取多态类型.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 执行基础查询（仅执行一次）.
     *
     * @return void
     */
    protected function baseQuery(): void
    {
        if (empty($this->baseQuery) && $this->parent->getData()) {
            $pk = $this->parent->getPk();

            $this->query->where([
                [$this->morphKey, '=', $this->parent->$pk],
                [$this->morphType, '=', $this->type],
            ]);

            $this->baseQuery = true;
        }
    }
}
