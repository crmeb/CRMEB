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
use think\db\exception\DbException as Exception;
use think\helper\Str;
use think\model\contract\Modelable as Model;
use think\model\Relation;

/**
 * 多态一对一关联类.
 */
class MorphOne extends Relation
{
    /**
     * 多态关联外键.
     *
     * @var string
     */
    protected $morphKey;

    /**
     * 多态字段.
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
     * 绑定的关联属性.
     *
     * @var array
     */
    protected $bindAttr = [];

    /**
     * 构造函数.
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
     * @return Model
     */
    public function getRelation(array $subRelation = [], ?Closure $closure = null)
    {
        if ($closure) {
            $closure($this->query);
        }

        $this->baseQuery();

        $relationModel = $this->query->relation($subRelation)->find();

        if ($relationModel) {
            if (!empty($this->bindAttr)) {
                // 绑定关联属性
                $this->bindAttr($this->parent, $relationModel);
            }
        } else {
            $default       = $this->query->getOption('default_model');
            $relationModel = $this->getDefaultModel($default);
        }

        return $relationModel;
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
        $model    = Str::snake(class_basename($this->parent));
        $relation = Str::snake(class_basename($this->model));
        $query    = $query ?: $this->parent->db();
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
            $data = $this->eagerlyMorphToOne([
                [$morphKey, 'in', $range],
                [$morphType, '=', $type],
            ], $subRelation, $closure, $cache);

            $default      = $this->query->getOption('default_model');
            $defaultModel = $this->getDefaultModel($default);

            // 关联数据封装
            foreach ($resultSet as $result) {
                if (!isset($data[$result->$pk])) {
                    $relationModel = $defaultModel;
                } else {
                    $relationModel = $data[$result->$pk];
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
            $pk   = $result->$pk;
            $data = $this->eagerlyMorphToOne([
                [$this->morphKey, '=', $pk],
                [$this->morphType, '=', $this->type],
            ], $subRelation, $closure, $cache);

            if (isset($data[$pk])) {
                $relationModel = $data[$pk];
            } else {
                $default       = $this->query->getOption('default_model');
                $relationModel = $this->getDefaultModel($default);
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

    /**
     * 多态一对一 关联模型预查询.
     *
     * @param array   $where       关联预查询条件
     * @param array   $subRelation 子关联
     * @param Closure $closure     闭包
     * @param array   $cache       关联缓存
     *
     * @return array
     */
    protected function eagerlyMorphToOne(array $where, array $subRelation = [], ?Closure $closure = null, array $cache = []): array
    {
        // 预载入关联查询 支持嵌套预载入
        if ($closure) {
            $this->baseQuery = true;
            $closure($this->query);
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
            $data[$set->$morphKey] = $set;
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
    public function make(array | Model $data = []): Model
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
     * 执行基础查询（进执行一次）.
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
     * 获取绑定属性.
     *
     * @return array
     */
    public function getBindAttr(): array
    {
        return $this->bindAttr;
    }

    /**
     * 绑定关联属性到父模型.
     *
     * @param Model $result 父模型对象
     * @param Model $model  关联模型对象
     *
     * @throws Exception
     *
     * @return void
     */
    protected function bindAttr(Model $result, ?Model $model = null): void
    {
        foreach ($this->bindAttr as $key => $attr) {
            $key   = is_numeric($key) ? $attr : $key;
            $value = $result->getOrigin($key);

            if (!is_null($value)) {
                throw new Exception('bind attr has exists:' . $key);
            }

            $result->set($key, $model?->get($attr));
        }
    }
}
