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
use think\db\BaseQuery as Query;
use think\helper\Str;
use think\Model;

/**
 * BelongsTo关联类
 */
class BelongsTo extends OneToOne
{
    /**
     * 架构函数
     * @access public
     * @param  Model  $parent 上级模型对象
     * @param  string $model 模型名
     * @param  string $foreignKey 关联外键
     * @param  string $localKey 关联主键
     * @param  string $relation  关联名
     */
    public function __construct(Model $parent, string $model, string $foreignKey, string $localKey, string $relation = null)
    {
        $this->parent     = $parent;
        $this->model      = $model;
        $this->foreignKey = $foreignKey;
        $this->localKey   = $localKey;
        $this->query      = (new $model)->db();
        $this->relation   = $relation;

        if (get_class($parent) == $model) {
            $this->selfRelation = true;
        }
    }

    /**
     * 延迟获取关联数据
     * @access public
     * @param  array   $subRelation 子关联名
     * @param  Closure $closure     闭包查询条件
     * @return Model
     */
    public function getRelation(array $subRelation = [], Closure $closure = null)
    {
        if ($closure) {
            $closure($this->getClosureType($closure));
        }

        $foreignKey = $this->foreignKey;

        $relationModel = $this->query
            ->removeWhereField($this->localKey)
            ->where($this->localKey, $this->parent->$foreignKey)
            ->relation($subRelation)
            ->find();

        if ($relationModel) {
            if (!empty($this->bindAttr)) {
                // 绑定关联属性
                $this->bindAttr($this->parent, $relationModel);
            }

            $relationModel->setParent(clone $this->parent);
        }

        return $relationModel;
    }

    /**
     * 创建关联统计子查询
     * @access public
     * @param  Closure $closure 闭包
     * @param  string  $aggregate 聚合查询方法
     * @param  string  $field 字段
     * @param  string  $name 聚合字段别名
     * @return string
     */
    public function getRelationCountQuery(Closure $closure = null, string $aggregate = 'count', string $field = '*', &$name = ''): string
    {
        if ($closure) {
            $closure($this->getClosureType($closure), $name);
        }

        return $this->query
            ->whereExp($this->localKey, '=' . $this->parent->getTable() . '.' . $this->foreignKey)
            ->fetchSql()
            ->$aggregate($field);
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
        $foreignKey = $this->foreignKey;

        if (!isset($result->$foreignKey)) {
            return 0;
        }

        if ($closure) {
            $closure($this->getClosureType($closure), $name);
        }

        return $this->query
            ->where($this->localKey, '=', $result->$foreignKey)
            ->$aggregate($field);
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
    public function has(string $operator = '>=', int $count = 1, string $id = '*', string $joinType = '', Query $query = null): Query
    {
        $table      = $this->query->getTable();
        $model      = class_basename($this->parent);
        $relation   = class_basename($this->model);
        $localKey   = $this->localKey;
        $foreignKey = $this->foreignKey;
        $softDelete = $this->query->getOptions('soft_delete');
        $query      = $query ?: $this->parent->db()->alias($model);

        return $query->whereExists(function ($query) use ($table, $model, $relation, $localKey, $foreignKey, $softDelete) {
            $query->table([$table => $relation])
                ->field($relation . '.' . $localKey)
                ->whereExp($model . '.' . $foreignKey, '=' . $relation . '.' . $localKey)
                ->when($softDelete, function ($query) use ($softDelete, $relation) {
                    $query->where($relation . strstr($softDelete[0], '.'), '=' == $softDelete[1][0] ? $softDelete[1][1] : null);
                });
        });
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param  mixed  $where  查询条件（数组或者闭包）
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

        return $query->field($fields)
            ->join([$table => $relation], $model . '.' . $this->foreignKey . '=' . $relation . '.' . $this->localKey, $joinType ?: $this->joinType)
            ->when($softDelete, function ($query) use ($softDelete, $relation) {
                $query->where($relation . strstr($softDelete[0], '.'), '=' == $softDelete[1][0] ? $softDelete[1][1] : null);
            })
            ->where($where);
    }

    /**
     * 预载入关联查询（数据集）
     * @access protected
     * @param  array   $resultSet 数据集
     * @param  string  $relation 当前关联名
     * @param  array   $subRelation 子关联名
     * @param  Closure $closure 闭包
     * @param  array   $cache       关联缓存
     * @return void
     */
    protected function eagerlySet(array &$resultSet, string $relation, array $subRelation = [], Closure $closure = null, array $cache = []): void
    {
        $localKey   = $this->localKey;
        $foreignKey = $this->foreignKey;

        $range = [];
        foreach ($resultSet as $result) {
            // 获取关联外键列表
            if (isset($result->$foreignKey)) {
                $range[] = $result->$foreignKey;
            }
        }

        if (!empty($range)) {
            $this->query->removeWhereField($localKey);

            $data = $this->eagerlyWhere([
                [$localKey, 'in', $range],
            ], $localKey, $subRelation, $closure, $cache);

            // 关联数据封装
            foreach ($resultSet as $result) {
                // 关联模型
                if (!isset($data[$result->$foreignKey])) {
                    $relationModel = null;
                } else {
                    $relationModel = $data[$result->$foreignKey];
                    $relationModel->setParent(clone $result);
                    $relationModel->exists(true);
                }

                if (!empty($this->bindAttr)) {
                    // 绑定关联属性
                    $this->bindAttr($result, $relationModel);
                } else {
                    // 设置关联属性
                    $result->setRelation($relation, $relationModel);
                }
            }
        }
    }

    /**
     * 预载入关联查询（数据）
     * @access protected
     * @param  Model   $result 数据对象
     * @param  string  $relation 当前关联名
     * @param  array   $subRelation 子关联名
     * @param  Closure $closure 闭包
     * @param  array   $cache       关联缓存
     * @return void
     */
    protected function eagerlyOne(Model $result, string $relation, array $subRelation = [], Closure $closure = null, array $cache = []): void
    {
        $localKey   = $this->localKey;
        $foreignKey = $this->foreignKey;

        $this->query->removeWhereField($localKey);

        $data = $this->eagerlyWhere([
            [$localKey, '=', $result->$foreignKey],
        ], $localKey, $subRelation, $closure, $cache);

        // 关联模型
        if (!isset($data[$result->$foreignKey])) {
            $relationModel = null;
        } else {
            $relationModel = $data[$result->$foreignKey];
            $relationModel->setParent(clone $result);
            $relationModel->exists(true);
        }

        if (!empty($this->bindAttr)) {
            // 绑定关联属性
            $this->bindAttr($result, $relationModel);
        } else {
            // 设置关联属性
            $result->setRelation($relation, $relationModel);
        }
    }

    /**
     * 添加关联数据
     * @access public
     * @param  Model $model关联模型对象
     * @return Model
     */
    public function associate(Model $model): Model
    {
        $this->parent->setAttr($this->foreignKey, $model->getKey());
        $this->parent->save();

        return $this->parent->setRelation($this->relation, $model);
    }

    /**
     * 注销关联数据
     * @access public
     * @return Model
     */
    public function dissociate(): Model
    {
        $foreignKey = $this->foreignKey;

        $this->parent->setAttr($foreignKey, null);
        $this->parent->save();

        return $this->parent->setRelation($this->relation, null);
    }

    /**
     * 执行基础查询（仅执行一次）
     * @access protected
     * @return void
     */
    protected function baseQuery(): void
    {
        if (empty($this->baseQuery)) {
            if (isset($this->parent->{$this->foreignKey})) {
                // 关联查询带入关联条件
                $this->query->where($this->localKey, '=', $this->parent->{$this->foreignKey});
            }

            $this->baseQuery = true;
        }
    }
}
