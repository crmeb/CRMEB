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

namespace think\model;

use Closure;
use think\db\BaseQuery as Query;
use think\db\exception\DbException as Exception;
use think\model\Collection;
use think\model\contract\Modelable as Model;

/**
 * 模型关联基础类.
 *
 * @mixin Query
 */
abstract class Relation
{
    /**
     * 父模型对象
     *
     * @var Model
     */
    protected $parent;

    /**
     * 当前关联的模型类名.
     *
     * @var string
     */
    protected $model;

    /**
     * 关联模型查询对象
     *
     * @var Query
     */
    protected $query;

    /**
     * 关联表外键.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * 关联表主键.
     *
     * @var string
     */
    protected $localKey;

    /**
     * 是否执行关联基础查询.
     *
     * @var bool
     */
    protected $baseQuery;

    /**
     * 是否为自关联.
     *
     * @var bool
     */
    protected $selfRelation = false;

    /**
     * 关联数据字段限制.
     *
     * @var array
     */
    protected $withField;

    /**
     * 排除关联数据字段.
     *
     * @var array
     */
    protected $withoutField;

    /**
     * 默认数据.
     *
     * @var mixed
     */
    protected $default;

    /**
     * 获取一条关联数据.
     *
     * @var bool
     */
    protected $isOneofMany = false;

    /**
     * 获取关联的所属模型.
     *
     * @return Model
     */
    public function getParent(): Model
    {
        return $this->parent;
    }

    /**
     * 获取当前的关联模型类的Query实例.
     *
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * 获取关联表外键.
     *
     * @return string
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * 获取关联表主键.
     *
     * @return string
     */
    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    /**
     * 获取当前的关联模型类的实例.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->query->getModel();
    }

    /**
     * 当前关联是否为自关联.
     *
     * @return bool
     */
    public function isSelfRelation(): bool
    {
        return $this->selfRelation;
    }

    /**
     * 封装关联数据集.
     *
     * @param array $resultSet 数据集
     *
     * @param array $resultSet 关联数据结果集
     * @return Collection 返回模型集合对象
     */
    protected function resultSetBuild(array $resultSet)
    {
        return (new $this->model())->toCollection($resultSet);
    }

    /**
     * 获取关联查询的字段
     *
     * 根据模型名称处理查询字段
     *
     * @param string $model 模型名称
     * @return mixed 返回处理后的查询字段
     */
    protected function getQueryFields(string $model)
    {
        $fields = $this->query->getOption('field');
        $this->query->removeOption('field');

        return $this->getRelationQueryFields($fields, $model);
    }

    /**
     * 获取关联查询的字段
     *
     * 处理关联查询的字段，添加表名前缀
     *
     * @param mixed $fields 字段定义
     * @param string $model 模型名称
     * @return mixed 返回处理后的查询字段
     */
    protected function getRelationQueryFields($fields, string $model)
    {
        if (empty($fields) || '*' == $fields) {
            return $model . '.*';
        }

        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        foreach ($fields as &$field) {
            if (!str_contains($field, '.')) {
                $field = $model . '.' . $field;
            }
        }

        return $fields;
    }

    /**
     * 处理关联查询条件
     *
     * 为查询条件添加关联表前缀
     *
     * @param array &$where 查询条件
     * @param string $relation 关联表名
     * @return void
     */
    protected function getQueryWhere(array &$where, string $relation): void
    {
        if (array_is_list($where) && isset($where[0]) && is_string($where[0])) {
            $where = [ $where ];
        }
        foreach ($where as $key => &$val) {
            if (is_string($key)) {
                $where[] = [!str_contains($key, '.') ? $relation . '.' . $key : $key, '=', $val];
                unset($where[$key]);
            } elseif (is_array($val) && isset($val[0]) && !str_contains($val[0], '.')) {
                $val[0] = $relation . '.' . $val[0];
            }
        }
    }

    /**
     * 获取关联数据默认值
     *
     * @param mixed $data 模型数据
     *
     * @return mixed
     */
    protected function getDefaultModel($data)
    {
        if (is_array($data)) {
            $model = new $this->model($data);
        } elseif ($data instanceof Closure) {
            $model = new $this->model();
            $data($model);
        } else {
            $model = $data;
        }

        return $model;
    }

    /**
     * 处理关联查询及软删除的关联查询
     *
     * @param Query  $query 查询对象
     * @param string $relation 关联名
     * @param mixed  $where 查询条件
     * @param string $logic 查询逻辑
     * @return Query 返回查询对象
     */
    protected function getRelationSoftDelete(Query $query, $relation, $where = null, $logic = '')
    {
        if ($where) {
            if (is_array($where)) {
                $this->getQueryWhere($where, $relation);
            } elseif ($where instanceof Query) {
                $where->via($relation);
            } elseif ($where instanceof Closure) {
                $where($this->query->via($relation));
                $where = $this->query;
            }

            $whereLogic = 'OR' == $logic ? 'whereOr' : 'where'; 
            $query->$whereLogic(function ($query) use ($where) {
                $query->where($where);
            });
        }

        // 启用软删除则增加软删除条件
        $softDelete = $this->query->getOption('soft_delete');
        return $query->when($softDelete, function ($query) use ($softDelete, $relation) {
            $query->where($relation . strstr($softDelete[0], '.'), '=' == $softDelete[1][0] ? $softDelete[1][1] : null);
        });
    }

    /**
     * 获取关联的最新一条数据.
     *
     * @param string $field 排序字段
     *
     * @return $this
     */
    public function first(string $field = '') 
    {
        $field = $field ?: $this->query->getPk();
        $this->query->order($field, 'desc');
        $this->isOneofMany = true;
        return $this;
    }

    /**
     * 获取关联的最旧一条数据.
     *
     * @param string $field 排序字段
     *
     * @return $this
     */
    public function last(string $field = '')
    {
        $field = $field ?: $this->query->getPk();
        $this->query->order($field, 'asc');
        $this->isOneofMany = true;
        return $this;
    }

    /**
     * 执行基础查询（仅执行一次）.
     *
     * @return void
     */
    protected function baseQuery(): void
    {
    }

    public function __call($method, $args)
    {
        if ($this->query) {
            // 执行基础查询
            $this->baseQuery();

            $result = call_user_func_array([$this->query, $method], $args);

            return $result === $this->query ? $this : $result;
        }

        throw new Exception('method not exists:' . __CLASS__ . '->' . $method);
    }
}
