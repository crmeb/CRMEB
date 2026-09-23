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
 * 远程一对多关联类.
 */
class HasManyThrough extends Relation
{
    /**
     * 中间关联表外键.
     *
     * @var string
     */
    protected $throughKey;

    /**
     * 中间主键.
     *
     * @var string
     */
    protected $throughPk;

    /**
     * 中间表查询对象
     *
     * @var Query
     */
    protected $through;

    /**
     * 架构函数.
     *
     * @param Model  $parent     上级模型对象
     * @param string $model      关联模型名
     * @param string $through    中间模型名
     * @param string $foreignKey 关联外键
     * @param string $throughKey 中间关联外键
     * @param string $localKey   当前模型主键
     * @param string $throughPk  中间模型主键
     */
    public function __construct(Model $parent, string $model, string $through, string $foreignKey, string $throughKey, string $localKey, string $throughPk)
    {
        $this->parent     = $parent;
        $this->model      = $model;
        $this->through    = (new $through())->db();
        $this->foreignKey = $foreignKey;
        $this->throughKey = $throughKey;
        $this->localKey   = $localKey;
        $this->throughPk  = $throughPk;
        $this->query      = (new $model())->db();
    }

    /**
     * 延迟获取关联数据.
     *
     * @param array   $subRelation 子关联名
     * @param Closure $closure     闭包查询条件
     *
     * @return Collection
     */
    public function getRelation(array $subRelation = [], ?Closure $closure = null)
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
     * @return Query
     */
    public function has(string $operator = '>=', int $count = 1, string $id = '*', string $joinType = 'INNER', ?Query $query = null): Query
    {
        // 子查询构建
        $model          = Str::snake(class_basename($this->parent));
        $table          = $this->through->getTable();
        $relation       = Str::snake(class_basename($this->model));
        $relationTable  = (new $this->model())->getTable();
        $query          = $query ?: $this->parent->db();
        $alias          = $query->getAlias() ?: $model;

        // 统计子查询
        $subQuery = $this->through
            ->field('COUNT(' . $id . ')')
            ->table($table)
            ->join([$relationTable => $relation], $relation . '.' . $this->throughKey . '=' . $table . '.' . $this->throughPk, $joinType)
            ->whereColumn($table . '.' . $this->throughPk, $model . '.' . $this->localKey);

        $this->getRelationSoftDelete($subQuery, $relation);
        return $query->alias($alias)->where('(' . $subQuery->buildSql() . ') ' . $operator . ' ' . $count);
    }

    /**
     * 根据关联条件查询当前模型.
     *
     * @param mixed  $where    查询条件（数组或者闭包）
     * @param mixed  $fields   字段
     * @param string $joinType JOIN类型
     * @param Query  $query    Query对象
     * @return Query
     */
    public function hasWhere($where = [], $fields = null, $joinType = '', ?Query $query = null, string $logic = '', string $relationAlias = ''): Query
    {
        $model          = Str::snake(class_basename($this->parent));
        $relation       = Str::snake(class_basename($this->model));
        $table          = $this->through->getTable();
        $relationTable  = (new $this->model())->getTable();
        $query          = $query ?: $this->parent->db();
        $alias          = $query->getAlias() ?: $model;
        $relAlias       = $relationAlias ?: $relation;

        // EXISTS子查询
        $subQuery = $this->through
            ->table($table)
            ->join([$relationTable => $relAlias], $relAlias . '.' . $this->throughKey . '=' . $table . '.' . $this->throughPk, $joinType)
            ->whereColumn($table . '.' . $this->throughPk, $alias . '.' . $this->localKey);

        $this->getRelationSoftDelete($subQuery, $relAlias, $where, $logic);
        return $query->alias($alias)->whereExists($subQuery->buildSql());
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
    public function eagerlyResultSet(array &$resultSet, string $relation, array $subRelation = [], ?Closure $closure = null, array $cache = []): void
    {
        $localKey   = $this->localKey;
        $foreignKey = $this->foreignKey;

        $range = [];
        foreach ($resultSet as $result) {
            // 获取关联外键列表
            if (isset($result->$localKey)) {
                $range[] = $result->$localKey;
            }
        }

        if (!empty($range)) {
            $this->query->removeWhereField($foreignKey);
            $range = array_unique($range);
            $data  = $this->eagerlyWhere([
                [$this->foreignKey, 'in', $range],
            ], $foreignKey, $subRelation, $closure, $cache, count($range) > 1 ? true : false);

            // 关联数据封装
            foreach ($resultSet as $result) {
                $pk = $result->$localKey;
                if (!isset($data[$pk])) {
                    $data[$pk] = [];
                }

                // 设置关联属性
                $result->setRelation($relation, $this->resultSetBuild($data[$pk]));
            }
        }
    }

    /**
     * 预载入关联查询（数据）.
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
        $localKey   = $this->localKey;
        $foreignKey = $this->foreignKey;
        $pk         = $result->$localKey;

        $this->query->removeWhereField($foreignKey);

        $data = $this->eagerlyWhere([
            [$foreignKey, '=', $pk],
        ], $foreignKey, $subRelation, $closure, $cache);

        // 关联数据封装
        if (!isset($data[$pk])) {
            $data[$pk] = [];
        }

        $result->setRelation($relation, $this->resultSetBuild($data[$pk]));
    }

    /**
     * 关联模型预查询.
     *
     * @param array   $where       关联预查询条件
     * @param string  $key         关联键名
     * @param array   $subRelation 子关联
     * @param Closure $closure
     * @param array   $cache       关联缓存
     * @param bool    $collection  是否数据集查询
     *
     * @return array
     */
    protected function eagerlyWhere(array $where, string $key, array $subRelation = [], ?Closure $closure = null, array $cache = [], bool $collection = false): array
    {
        // 预载入关联查询 支持嵌套预载入
        $throughList = $this->through->where($where)->select();
        $keys        = $throughList->column($this->throughPk, $this->throughPk);

        if ($closure) {
            $this->baseQuery = true;
            $closure($this->query);
        }

        $throughKey = $this->throughKey;

        if ($this->baseQuery) {
            $throughKey = Str::snake(class_basename($this->model)) . '.' . $this->throughKey;
        }

        $withLimit = $this->query->getOption('limit');
        if ($withLimit && $collection) {
            $this->query->removeOption('limit');
        }

        if ($this->isOneofMany) {
            if (!$collection) {
                $this->query->limit(1);
            } else {
                $withLimit = 1;
            }
        }

        $list = $this->query
            ->where($throughKey, 'in', $keys)
            ->cache($cache[0] ?? false, $cache[1] ?? null, $cache[2] ?? null)
            ->lazy();

        // 组装模型数据
        $data = [];
        $keys = $throughList->column($this->foreignKey, $this->throughPk);

        foreach ($list as $set) {
            $key = $keys[$set->{$this->throughKey}];

            if ($withLimit && isset($data[$key]) && count($data[$key]) >= $withLimit) {
                continue;
            }

            $data[$key][] = $set;
        }

        return $data;
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
        $localKey = $this->localKey;

        if (!isset($result->$localKey)) {
            return 0;
        }

        if ($closure) {
            $closure($this->query, $name);
        }

        $alias        = Str::snake(class_basename($this->model));
        $alias        = $this->query->getAlias() ?: $alias;
        $throughTable = $this->through->getTable();
        $pk           = $this->throughPk;
        $throughKey   = $this->throughKey;
        $modelTable   = $this->parent->getTable();

        if (!str_contains($field, '.')) {
            $field = $alias . '.' . $field;
        }

        return $this->query
            ->alias($alias)
            ->join($throughTable, $throughTable . '.' . $pk . '=' . $alias . '.' . $throughKey)
            ->join($modelTable, $modelTable . '.' . $this->localKey . '=' . $throughTable . '.' . $this->foreignKey)
            ->where($throughTable . '.' . $this->foreignKey, $result->$localKey)
            ->$aggregate($field);
    }

    /**
     * 创建关联统计子查询.
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

        $alias        = Str::snake(class_basename($this->model));
        $alias        = $this->query->getAlias() ?: $alias . '_' . $aggregate;
        $throughTable = $this->through->getTable();
        $pk           = $this->throughPk;
        $throughKey   = $this->throughKey;
        $modelTable   = $this->parent->getTable();

        if (!str_contains($field, '.')) {
            $field = $alias . '.' . $field;
        }

        return $this->query
            ->alias($alias)
            ->join($throughTable, $throughTable . '.' . $pk . '=' . $alias . '.' . $throughKey)
            ->whereColumn($throughTable . '.' . $this->foreignKey, $this->parent->getTable() . '.' . $this->localKey)
            ->fetchSql()
            ->$aggregate($field);
    }

    /**
     * 执行基础查询（仅执行一次）.
     *
     * @return void
     */
    protected function baseQuery() : void
    {
        if (empty($this->baseQuery) && $this->parent->getData()) {
            $alias        = Str::snake(class_basename($this->model));
            $throughTable = $this->through->getTable();
            $pk           = $this->throughPk;
            $throughKey   = $this->throughKey;
            $modelTable   = $this->parent->getTable();
            $fields       = $this->getQueryFields($alias);

            $this->query
                ->field($fields)
                ->alias($alias)
                ->join($throughTable, $throughTable . '.' . $pk . '=' . $alias . '.' . $throughKey)
                ->where($throughTable . '.' . $this->foreignKey, $this->parent->{$this->localKey});

            $this->baseQuery = true;
        }
    }
}
