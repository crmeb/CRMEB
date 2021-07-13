<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
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
use think\Model;
use think\model\Relation;

/**
 * 一对多关联类
 */
class HasMany extends Relation
{
    /**
     * 架构函数
     * @access public
     * @param  Model  $parent     上级模型对象
     * @param  string $model      模型名
     * @param  string $foreignKey 关联外键
     * @param  string $localKey   当前模型主键
     */
    public function __construct(Model $parent, string $model, string $foreignKey, string $localKey)
    {
        $this->parent     = $parent;
        $this->model      = $model;
        $this->foreignKey = $foreignKey;
        $this->localKey   = $localKey;
        $this->query      = (new $model)->db();

        if (get_class($parent) == $model) {
            $this->selfRelation = true;
        }
    }

    /**
     * 延迟获取关联数据
     * @access public
     * @param  array   $subRelation 子关联名
     * @param  Closure $closure     闭包查询条件
     * @return Collection
     */
    public function getRelation(array $subRelation = [], Closure $closure = null): Collection
    {
        if ($closure) {
            $closure($this->getClosureType($closure));
        }

        if ($this->withLimit) {
            $this->query->limit($this->withLimit);
        }

        return $this->query
            ->where($this->foreignKey, $this->parent->{$this->localKey})
            ->relation($subRelation)
            ->select()
            ->setParent(clone $this->parent);
    }

    /**
     * 预载入关联查询
     * @access public
     * @param  array   $resultSet   数据集
     * @param  string  $relation    当前关联名
     * @param  array   $subRelation 子关联名
     * @param  Closure $closure     闭包
     * @param  array   $cache       关联缓存
     * @return void
     */
    public function eagerlyResultSet(array &$resultSet, string $relation, array $subRelation, Closure $closure = null, array $cache = []): void
    {
        $localKey = $this->localKey;
        $range    = [];

        foreach ($resultSet as $result) {
            // 获取关联外键列表
            if (isset($result->$localKey)) {
                $range[] = $result->$localKey;
            }
        }

        if (!empty($range)) {
            $data = $this->eagerlyOneToMany([
                [$this->foreignKey, 'in', $range],
            ], $subRelation, $closure, $cache);

            // 关联数据封装
            foreach ($resultSet as $result) {
                $pk = $result->$localKey;
                if (!isset($data[$pk])) {
                    $data[$pk] = [];
                }

                $result->setRelation($relation, $this->resultSetBuild($data[$pk], clone $this->parent));
            }
        }
    }

    /**
     * 预载入关联查询
     * @access public
     * @param  Model   $result      数据对象
     * @param  string  $relation    当前关联名
     * @param  array   $subRelation 子关联名
     * @param  Closure $closure     闭包
     * @param  array   $cache       关联缓存
     * @return void
     */
    public function eagerlyResult(Model $result, string $relation, array $subRelation = [], Closure $closure = null, array $cache = []): void
    {
        $localKey = $this->localKey;

        if (isset($result->$localKey)) {
            $pk   = $result->$localKey;
            $data = $this->eagerlyOneToMany([
                [$this->foreignKey, '=', $pk],
            ], $subRelation, $closure, $cache);

            // 关联数据封装
            if (!isset($data[$pk])) {
                $data[$pk] = [];
            }

            $result->setRelation($relation, $this->resultSetBuild($data[$pk], clone $this->parent));
        }
    }

    /**
     * 关联统计
     * @access public
     * @param  Model   $result  数据对象
     * @param  Closure $closure 闭包
     * @param  string  $aggregate 聚合查询方法
     * @param  string  $field 字段
     * @param  string  $name 统计字段别名
     * @return integer
     */
    public function relationCount(Model $result, Closure $closure = null, string $aggregate = 'count', string $field = '*', string &$name = null)
    {
        $localKey = $this->localKey;

        if (!isset($result->$localKey)) {
            return 0;
        }

        if ($closure) {
            $closure($this->getClosureType($closure), $name);
        }

        return $this->query
            ->where($this->foreignKey, '=', $result->$localKey)
            ->$aggregate($field);
    }

    /**
     * 创建关联统计子查询
     * @access public
     * @param  Closure $closure 闭包
     * @param  string  $aggregate 聚合查询方法
     * @param  string  $field 字段
     * @param  string  $name 统计字段别名
     * @return string
     */
    public function getRelationCountQuery(Closure $closure = null, string $aggregate = 'count', string $field = '*', string &$name = null): string
    {
        if ($closure) {
            $closure($this->getClosureType($closure), $name);
        }

        return $this->query->alias($aggregate . '_table')
            ->whereExp($aggregate . '_table.' . $this->foreignKey, '=' . $this->parent->getTable() . '.' . $this->localKey)
            ->fetchSql()
            ->$aggregate($field);
    }

    /**
     * 一对多 关联模型预查询
     * @access public
     * @param  array   $where       关联预查询条件
     * @param  array   $subRelation 子关联
     * @param  Closure $closure
     * @param  array   $cache       关联缓存
     * @return array
     */
    protected function eagerlyOneToMany(array $where, array $subRelation = [], Closure $closure = null, array $cache = []): array
    {
        $foreignKey = $this->foreignKey;

        $this->query->removeWhereField($this->foreignKey);

        // 预载入关联查询 支持嵌套预载入
        if ($closure) {
            $this->baseQuery = true;
            $closure($this->getClosureType($closure));
        }

        $list = $this->query
            ->where($where)
            ->cache($cache[0] ?? false, $cache[1] ?? null, $cache[2] ?? null)
            ->with($subRelation)
            ->select();

        // 组装模型数据
        $data = [];

        foreach ($list as $set) {
            $key = $set->$foreignKey;

            if ($this->withLimit && isset($data[$key]) && count($data[$key]) >= $this->withLimit) {
                continue;
            }

            $data[$key][] = $set;
        }

        return $data;
    }

    /**
     * 保存（新增）当前关联数据对象
     * @access public
     * @param  mixed   $data 数据 可以使用数组 关联模型对象
     * @param  boolean $replace 是否自动识别更新和写入
     * @return Model|false
     */
    public function save($data, bool $replace = true)
    {
        $model = $this->make();

        return $model->replace($replace)->save($data) ? $model : false;
    }

    /**
     * 创建关联对象实例
     * @param array|Model $data
     * @return Model
     */
    public function make($data = []): Model
    {
        if ($data instanceof Model) {
            $data = $data->getData();
        }

        // 保存关联表数据
        $data[$this->foreignKey] = $this->parent->{$this->localKey};

        return new $this->model($data);
    }

    /**
     * 批量保存当前关联数据对象
     * @access public
     * @param  iterable $dataSet 数据集
     * @param  boolean  $replace 是否自动识别更新和写入
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
     * 根据关联条件查询当前模型
     * @access public
     * @param  string  $operator 比较操作符
     * @param  integer $count    个数
     * @param  string  $id       关联表的统计字段
     * @param  string  $joinType JOIN类型
     * @param  Query   $query    Query对象
     * @return Query
     */
    public function has(string $operator = '>=', int $count = 1, string $id = '*', string $joinType = 'INNER', Query $query = null): Query
    {
        $table = $this->query->getTable();

        $model    = class_basename($this->parent);
        $relation = class_basename($this->model);

        if ('*' != $id) {
            $id = $relation . '.' . (new $this->model)->getPk();
        }

        $softDelete = $this->query->getOptions('soft_delete');
        $query      = $query ?: $this->parent->db()->alias($model);

        return $query->field($model . '.*')
            ->join([$table => $relation], $model . '.' . $this->localKey . '=' . $relation . '.' . $this->foreignKey, $joinType)
            ->when($softDelete, function ($query) use ($softDelete, $relation) {
                $query->where($relation . strstr($softDelete[0], '.'), '=' == $softDelete[1][0] ? $softDelete[1][1] : null);
            })
            ->group($relation . '.' . $this->foreignKey)
            ->having('count(' . $id . ')' . $operator . $count);
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param  mixed  $where 查询条件（数组或者闭包）
     * @param  mixed  $fields 字段
     * @param  string $joinType JOIN类型
     * @param  Query  $query    Query对象
     * @return Query
     */
    public function hasWhere($where = [], $fields = null, string $joinType = '', Query $query = null): Query
    {
        $table    = $this->query->getTable();
        $model    = class_basename($this->parent);
        $relation = class_basename($this->model);

        if (is_array($where)) {
            $this->getQueryWhere($where, $relation);
        } elseif ($where instanceof Query) {
            $where->via($relation);
        } elseif ($where instanceof Closure) {
            $where($this->query->via($relation));
            $where = $this->query;
        }

        $fields     = $this->getRelationQueryFields($fields, $model);
        $softDelete = $this->query->getOptions('soft_delete');
        $query      = $query ?: $this->parent->db()->alias($model);

        return $query->group($model . '.' . $this->localKey)
            ->field($fields)
            ->join([$table => $relation], $model . '.' . $this->localKey . '=' . $relation . '.' . $this->foreignKey, $joinType)
            ->when($softDelete, function ($query) use ($softDelete, $relation) {
                $query->where($relation . strstr($softDelete[0], '.'), '=' == $softDelete[1][0] ? $softDelete[1][1] : null);
            })
            ->where($where);
    }

    /**
     * 执行基础查询（仅执行一次）
     * @access protected
     * @return void
     */
    protected function baseQuery(): void
    {
        if (empty($this->baseQuery)) {
            if (isset($this->parent->{$this->localKey})) {
                // 关联查询带入关联条件
                $this->query->where($this->foreignKey, '=', $this->parent->{$this->localKey});
            }

            $this->baseQuery = true;
        }
    }

}
