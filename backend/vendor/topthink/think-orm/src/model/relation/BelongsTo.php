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
use think\db\BaseQuery as Query;
use think\helper\Str;
use think\model\contract\Modelable as Model;

/**
 * BelongsTo关联类.
 */
class BelongsTo extends OneToOne
{
    /**
     * 架构函数.
     *
     * @param Model  $parent     上级模型对象
     * @param string $model      模型名
     * @param string $foreignKey 关联外键
     * @param string $localKey   关联主键
     * @param string $relation   关联名
     */
    public function __construct(Model $parent, string $model, string $foreignKey, string $localKey, ?string $relation = null)
    {
        $this->parent     = $parent;
        $this->model      = $model;
        $this->foreignKey = $foreignKey;
        $this->localKey   = $localKey;
        $this->query      = (new $model())->db();
        $this->relation   = $relation;

        if (get_class($parent) == $model) {
            $this->selfRelation = true;
        }
    }

    /**
     * 延迟获取关联数据.
     *
     * @param array   $subRelation 子关联名
     * @param Closure $closure     闭包查询条件
     *
     * @return Model
     */
    public function getRelation(array $subRelation = [], ?Closure $closure = null)
    {
        if ($closure) {
            $closure($this->query);
        }

        $foreignKey    = $this->foreignKey;
        $relationModel = $this->query
            ->removeWhereField($this->localKey)
            ->where($this->localKey, $this->parent->$foreignKey)
            ->relation($subRelation)
            ->find();

        if ($relationModel) {
            if (!empty($this->bindAttr)) {
                // 绑定关联属性
                $this->parent->bindRelationAttr($relationModel, $this->bindAttr);
            }
        } else {
            $default       = $this->query->getOption('default_model');
            $relationModel = $this->getDefaultModel($default);
        }

        return $relationModel;
    }

    /**
     * 创建关联统计子查询.
     *
     * @param Closure $closure   闭包
     * @param string  $aggregate 聚合查询方法
     * @param string  $field     字段
     * @param string  $name      聚合字段别名
     *
     * @return string
     */
    public function getRelationCountQuery(?Closure $closure = null, string $aggregate = 'count', string $field = 'id', &$name = ''): string
    {
        if ($closure) {
            $closure($this->query, $name);
        }

        $alias = Str::snake(class_basename($this->model));
        $alias = $this->query->getAlias() ?: $alias . '_' . $aggregate;
        return $this->query
            ->alias($alias)
            ->whereExp($alias . '.' . $this->localKey, '=' . $this->parent->getTable(true) . '.' . $this->foreignKey)
            ->fetchSql()
            ->$aggregate($field);
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
        $foreignKey = $this->foreignKey;

        if (!isset($result->$foreignKey)) {
            return 0;
        }

        if ($closure) {
            $closure($this->query, $name);
        }

        return $this->query
            ->where($this->localKey, '=', $result->$foreignKey)
            ->$aggregate($field);
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
    public function has(string $operator = '>=', int $count = 1, string $id = '*', string $joinType = '', ?Query $query = null) : Query
    {
        $table      = $this->query->getTable();
        $model      = Str::snake(class_basename($this->parent));
        $relation   = Str::snake(class_basename($this->model));
        $query      = $query ?: $this->parent->db();
        $alias      = $query->getAlias() ?: $model;

        if ($this->isSelfRelation() && $alias == $relation) {
            $relation .= '_'; 
        }

        return $query->alias($alias)
            ->whereExists(function ($query) use ($table, $alias, $relation) {
                $query->table([$table => $relation])
                ->field($relation . '.' . $this->localKey)
                ->whereColumn($alias . '.' . $this->foreignKey, $relation . '.' . $this->localKey);

            $this->getRelationSoftDelete($query, $relation);
        });
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
    public function hasWhere($where = [], $fields = null, string $joinType = '', ?Query $query = null, string $logic = '', string $relationAlias = ''): Query
    {
        $model    = Str::snake(class_basename($this->parent));
        $relation = Str::snake(class_basename($this->model));
        $table    = $this->query->getTable();
        $query    = $query ?: $this->parent->db();
        $alias    = $query->getAlias() ?: $model;
        $fields   = $this->getRelationQueryFields($fields, $alias);
        $relAlias = $relationAlias ?: $relation;

        if ($this->isSelfRelation() && $alias == $relAlias) {
            $relAlias .= '_';
        }

        $query->alias($alias)
            ->via($alias)
            ->field($fields)
            ->join([$table => $relAlias], $alias . '.' . $this->foreignKey . '=' . $relAlias . '.' . $this->localKey, $joinType);

        return $this->getRelationSoftDelete($query, $relAlias, $where, $logic);
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
    protected function eagerlySet(array &$resultSet, string $relation, array $subRelation = [], ?Closure $closure = null, array $cache = []): void
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
            $default      = $this->query->getOption('default_model');
            $defaultModel = $this->getDefaultModel($default);

            $range = array_unique($range);
            $data  = $this->eagerlyWhere([
                [$localKey, 'in', $range],
            ], $localKey, $subRelation, $closure, $cache, count($range) > 1 ? true : false);

            // 动态绑定参数
            $bindAttr = $this->query->getOption('bind_attr');
            if ($bindAttr) {
                $this->bind($bindAttr);
            }

            // 关联数据封装
            foreach ($resultSet as $result) {
                // 关联模型
                if (!isset($data[$result->$foreignKey])) {
                    $relationModel = $defaultModel;
                } else {
                    $relationModel = $data[$result->$foreignKey];
                }

                // 设置关联属性
                if (!empty($this->bindAttr) && $relationModel) {
                    $result->bindRelationAttr($relationModel, $this->bindAttr, $relation);
                } else {
                    $result->setRelation($relation, $relationModel);
                }
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
    protected function eagerlyOne(Model $result, string $relation, array $subRelation = [], ?Closure $closure = null, array $cache = []): void
    {
        $localKey   = $this->localKey;
        $foreignKey = $this->foreignKey;

        $this->query->removeWhereField($localKey);

        $data = $this->eagerlyWhere([
            [$localKey, '=', $result->$foreignKey],
        ], $localKey, $subRelation, $closure, $cache);

        // 关联模型
        if (!isset($data[$result->$foreignKey])) {
            $default       = $this->query->getOption('default_model');
            $relationModel = $this->getDefaultModel($default);
        } else {
            $relationModel = $data[$result->$foreignKey];
        }

        // 动态绑定参数
        $bindAttr = $this->query->getOption('bind_attr');
        if ($bindAttr) {
            $this->bind($bindAttr);
        }

        // 设置关联属性
        if (!empty($this->bindAttr) && $relationModel) {
            $result->bindRelationAttr($relationModel, $this->bindAttr, $relation);
        } else {
            $result->setRelation($relation, $relationModel);
        }
    }

    /**
     * 添加关联数据.
     *
     * @param Model $model关联模型对象
     *
     * @return Model
     */
    public function associate(Model $model): Model
    {
        $this->parent->set($this->foreignKey, $model->getKey());
        $this->parent->save();

        return $this->parent->setRelation($this->relation, $model);
    }

    /**
     * 注销关联数据.
     *
     * @return Model
     */
    public function dissociate(): Model
    {
        $foreignKey = $this->foreignKey;

        $this->parent->set($foreignKey, null);
        $this->parent->save();

        return $this->parent->setRelation($this->relation, null);
    }

    /**
     * 执行基础查询（仅执行一次）.
     *
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
