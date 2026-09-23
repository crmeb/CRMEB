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

namespace think\model\relation;

use Closure;
use think\db\BaseQuery as Query;
use think\db\exception\DbException as Exception;
use think\db\exception\InvalidArgumentException;
use think\helper\Str;
use think\model\contract\Modelable as Model;
use think\model\Relation;

/**
 * 一对一关联基础类.
 */
abstract class OneToOne extends Relation
{
    /**
     * JOIN类型.
     *
     * @var string
     */
    protected $joinType = 'INNER';

    /**
     * 绑定的关联属性.
     *
     * @var array
     */
    protected $bindAttr = [];

    /**
     * 关联名.
     *
     * @var string
     */
    protected $relation;

    /**
     * 获取一对多关联的最新一条数据.
     *
     * @param string $field 排序字段
     *
     * @return $this
     */
    public function firstOfMany(string $field = '') 
    {
        return $this->first($field);
    }

    /**
     * 获取一对多关联的最旧一条数据.
     *
     * @param string $field 排序字段
     *
     * @return $this
     */
    public function lastOfMany(string $field = '')
    {
         return $this->last($field);
    }

    /**
     * 设置join类型.
     *
     * @param string $type JOIN类型
     *
     * @return $this
     */
    public function joinType(string $type)
    {
        $this->joinType = $type;
        return $this;
    }

    /**
     * 预载入关联查询（JOIN方式）.
     *
     * @param Query   $query    查询对象
     * @param string  $relation 关联名
     * @param mixed   $field    关联字段
     * @param string  $joinType JOIN方式
     * @param Closure $closure  闭包条件
     * @param bool    $first
     *
     * @return void
     */
    public function eagerly(Query $query, string $relation, $field = true, string $joinType = '', ?Closure $closure = null, bool $first = false): void
    {
        $name = Str::snake(class_basename($this->parent));

        if ($first) {
            $table = $query->getTable();
            $query->table([$table => $name]);

            if ($query->getOption('field')) {
                $masterField = $query->getOption('field');
                $query->removeOption('field');
            } else {
                $masterField = true;
            }

            $query->tableField($masterField, $table, $name);
        }

        // 预载入封装
        $joinTable = $this->query->getTable();
        $joinAlias = Str::snake($relation);
        $joinType  = $joinType ?: $this->joinType;
        if (true !== $field) {
            $joinField = $field;
        } elseif ($this->query->getOption('field')) {
            $joinField = $this->query->getOption('field');
        } else {
            $joinField = $field;
        }

        $query->via($joinAlias);

        if ($this instanceof BelongsTo) {
            $foreignKeyExp = $this->foreignKey;

            if (!str_contains($foreignKeyExp, '.')) {
                $foreignKeyExp = $name . '.' . $this->foreignKey;
            }

            $joinOn = $foreignKeyExp . '=' . $joinAlias . '.' . $this->localKey;
        } else {
            $foreignKeyExp = $this->foreignKey;

            if (!str_contains($foreignKeyExp, '.')) {
                $foreignKeyExp = $joinAlias . '.' . $this->foreignKey;
            }

            $joinOn = $name . '.' . $this->localKey . '=' . $foreignKeyExp;
        }

        if ($closure) {
            // 执行闭包查询
            $closure($query);

            // 使用field指定获取关联的字段
            $withField = $query->getOption('field');
            if ($withField) {
                $joinField = $withField;
            }
            $query->removeOption('field');
        }

        $query->join([$joinTable => $joinAlias], $joinOn, $joinType)
            ->tableField($joinField, $joinTable, $joinAlias, $joinAlias . '__');
    }

    /**
     *  预载入关联查询（数据集）.
     *
     * @param array   $resultSet
     * @param string  $relation
     * @param array   $subRelation
     * @param Closure $closure
     *
     * @return mixed
     */
    abstract protected function eagerlySet(array &$resultSet, string $relation, array $subRelation = [], ?Closure $closure = null);

    /**
     * 预载入关联查询（数据）.
     *
     * @param Model   $result
     * @param string  $relation
     * @param array   $subRelation
     * @param Closure $closure
     *
     * @return mixed
     */
    abstract protected function eagerlyOne(Model $result, string $relation, array $subRelation = [], ?Closure $closure = null);

    /**
     * 预载入关联查询（数据集）.
     *
     * @param array   $resultSet   数据集
     * @param string  $relation    当前关联名
     * @param array   $subRelation 子关联名
     * @param Closure $closure     闭包
     * @param array   $cache       关联缓存
     * @param bool    $join        是否为JOIN方式
     *
     * @return void
     */
    public function eagerlyResultSet(array &$resultSet, string $relation, array $subRelation = [], ?Closure $closure = null, array $cache = [], bool $join = false): void
    {
        if ($join) {
            // 模型JOIN关联组装
            foreach ($resultSet as $result) {
                $this->match($this->model, $relation, $result);
            }
        } else {
            // IN查询
            $this->eagerlySet($resultSet, $relation, $subRelation, $closure, $cache);
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
     * @param bool    $join        是否为JOIN方式
     *
     * @return void
     */
    public function eagerlyResult(Model $result, string $relation, array $subRelation = [], ?Closure $closure = null, array $cache = [], bool $join = false): void
    {
        if ($join) {
            // 模型JOIN关联组装
            $this->match($this->model, $relation, $result);
        } else {
            // IN查询
            $this->eagerlyOne($result, $relation, $subRelation, $closure, $cache);
        }
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
    public function make(array | Model $data = []): Model
    {
        if ($data instanceof Model) {
            $data = $data->getData();
        }

        // 保存关联表数据
        $data[$this->foreignKey] = $this->parent->{$this->localKey};

        return (new $this->model($data))->setSuffix($this->getModel()->getSuffix());
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
     * 一对一 关联模型预查询拼装.
     *
     * @param string $model    模型名称
     * @param string $relation 关联名
     * @param Model  $result   模型对象实例
     *
     * @return void
     */
    protected function match(string $model, string $relation, Model $result): void
    {
        $data = $result->getRelation($relation);
        if (!empty($data)) {
            if ($this->bindAttr) {
                $result->bindRelationAttr($data, $this->bindAttr);
            } else {
                $relationModel = new $model($data);
                $result->setRelation($relation, $relationModel);
            }
        }
    }

    /**
     * 一对一 关联模型预查询（IN方式）.
     *
     * @param array   $where       关联预查询条件
     * @param string  $key         关联键名
     * @param array   $subRelation 子关联
     * @param Closure $closure
     * @param array   $cache       关联缓存
     * @param bool    $collection  是否数据集查询
     * @return array
     */
    protected function eagerlyWhere(array $where, string $key, array $subRelation = [], ?Closure $closure = null, array $cache = [], bool $collection = false)
    {
        // 预载入关联查询 支持嵌套预载入
        if ($closure) {
            $this->baseQuery = true;
            $closure($this->query);
        }

        if ($collection) {
            $this->query->removeOption('limit');
        } else {
            $this->query->limit(1);
        }

        $list = $this->query
            ->where($where)
            ->with($subRelation)
            ->cache($cache[0] ?? false, $cache[1] ?? null, $cache[2] ?? null)
            ->lazy();

        // 组装模型数据
        $data = [];
        foreach ($list as $set) {
            if (!isset($data[$set->$key])) {
                $data[$set->$key] = $set;
            }
        }

        return $data;
    }
}
