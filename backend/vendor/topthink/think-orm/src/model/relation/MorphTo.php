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

use BackedEnum;
use Closure;
use think\db\exception\DbException as Exception;
use think\db\Query;
use think\helper\Str;
use think\model\contract\Modelable as Model;
use think\model\Relation;

/**
 * 多态关联类.
 */
class MorphTo extends Relation
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
     * 多态别名.
     *
     * @var array
     */
    protected $alias = [];

    /**
     * 关联名.
     *
     * @var string
     */
    protected $relation;

    protected $queryCaller = [];

    /**
     * 架构函数.
     *
     * @param Model   $parent    上级模型对象
     * @param string  $morphType 多态字段名
     * @param string  $morphKey  外键名
     * @param array   $alias     多态别名定义
     * @param ?string $relation  关联名
     */
    public function __construct(Model $parent, string $morphType, string $morphKey, array $alias = [], ?string $relation = null)
    {
        $this->parent       = $parent;
        $this->morphType    = $morphType;
        $this->morphKey     = $morphKey;
        $this->alias        = $alias;
        $this->relation     = $relation;
    }

    /**
     * 获取当前的关联模型类的实例.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        $morphType = $this->morphType;
        $model = $this->parseModel($this->parent->$morphType);

        return new $model();
    }

    /**
     * 延迟获取关联数据.
     *
     * @param array    $subRelation 子关联名
     * @param ?Closure $closure     闭包查询条件
     *
     * @return Model
     */
    public function getRelation(array $subRelation = [], ?Closure $closure = null)
    {
        $morphKey   = $this->morphKey;
        $morphType  = $this->morphType;

        // 多态模型
        $model = $this->parseModel($this->parent->$morphType);

        // 主键数据
        $pk = $this->parent->$morphKey;

        return class_exists($model) ? $this->buildQuery((new $model())->relation($subRelation))->find($pk) : null;
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
        return $this->parent;
    }

    /**
     * 根据关联条件查询当前模型.
     *
     * @param mixed  $where    查询条件（数组或者闭包）
     * @param mixed  $fields   字段
     * @param string $joinType JOIN类型
     * @param ?Query $query    Query对象
     *
     * @return Query
     */
    public function hasWhere($where = [], $fields = null, string $joinType = '', ?Query $query = null, string $logic = '')
    {
        $model = Str::snake(class_basename($this->parent));
        $types = $this->parent->distinct()->column($this->morphType);
        $query = $query ?: $this->parent->db();
        $alias = $query->getAlias() ?: $model;

        return $query->alias($alias)
            ->where(function (Query $query) use ($types, $where, $alias, $logic) {
                foreach ($types as $type) {
                    if ($type) {
                        $query->whereExists(function (Query $query) use ($type, $where, $alias, $logic) {
                            $class = $this->parseModel($type);
                            /** @var Model $model */
                            $model = new $class();

                            $table = $model->getTable();
                            $logic = 'OR' == $logic ? 'whereOr' : 'where';
                            $query
                                ->table($table)
                                ->where($alias . '.' . $this->morphType, $type)
                                ->whereColumn($alias . '.' . $this->morphKey, $table . '.' . $model->getPk())
                                ->$logic($where);
                        }, 'OR');
                    }
                }
            });
    }

    /**
     * 解析模型的完整命名空间.
     *
     * @param string $model 模型名（或者完整类名）
     *
     * @return Model
     */
    protected function parseModel($model): string
    {
        if ($model instanceof BackedEnum) {
            $model = $model->value;
        }

        if (isset($this->alias[$model])) {
            $model = $this->alias[$model];
        }

        if (!str_contains($model, '\\')) {
            $path = explode('\\', get_class($this->parent));
            array_pop($path);
            array_push($path, Str::studly($model));
            $model = implode('\\', $path);
        }

        return $model;
    }

    /**
     * 设置多态别名.
     *
     * @param array $alias 别名定义
     *
     * @return $this
     */
    public function setAlias(array $alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * 移除关联查询参数.
     *
     * @return $this
     */
    public function removeOption(string $option = '')
    {
        return $this;
    }

    /**
     * 预载入关联查询.
     *
     * @param array    $resultSet   数据集
     * @param string   $relation    当前关联名
     * @param array    $subRelation 子关联名
     * @param ?Closure $closure     闭包
     * @param array    $cache       关联缓存
     *
     * @throws Exception
     *
     * @return void
     */
    public function eagerlyResultSet(array &$resultSet, string $relation, array $subRelation, ?Closure $closure = null, array $cache = []): void
    {
        $morphKey   = $this->morphKey;
        $morphType  = $this->morphType;
        $range      = [];

        foreach ($resultSet as $result) {
            // 获取关联外键列表
            if (!empty($result->$morphKey)) {
                $range[$result->$morphType][] = $result->$morphKey;
            }
        }

        if (!empty($range)) {
            foreach ($range as $key => $val) {
                // 多态类型映射
                $model = $this->parseModel($key);
                $data = [];
                if (class_exists($model)) {
                    $obj = new $model();
                    if (!is_null($closure)) {
                        $obj = $closure($obj);
                    }
                    $pk = $obj->getPk();
                    $list = $obj->with($subRelation)
                        ->cache($cache[0] ?? false, $cache[1] ?? null, $cache[2] ?? null)
                        ->select($val);

                    foreach ($list as $k => $vo) {
                        $data[$vo->$pk] = $vo;
                    }
                }

                foreach ($resultSet as $result) {
                    if ($key == $result->$morphType) {
                        // 关联模型
                        if (!isset($data[$result->$morphKey])) {
                            $relationModel = null;
                        } else {
                            $relationModel = $data[$result->$morphKey];
                        }

                        $result->setRelation($relation, $relationModel);
                    }
                }
            }
        }
    }

    /**
     * 预载入关联查询.
     *
     * @param Model    $result      数据对象
     * @param string   $relation    当前关联名
     * @param array    $subRelation 子关联名
     * @param ?Closure $closure     闭包
     * @param array    $cache       关联缓存
     *
     * @return void
     */
    public function eagerlyResult(Model $result, string $relation, array $subRelation = [], ?Closure $closure = null, array $cache = []): void
    {
        // 多态类型映射
        $model = $this->parseModel($result->{$this->morphType});

        $this->eagerlyMorphToOne($model, $relation, $result, $subRelation, $cache);
    }

    /**
     * 关联统计
     *
     * @param Model    $result    数据对象
     * @param ?Closure $closure   闭包
     * @param string   $aggregate 聚合查询方法
     * @param string   $field     字段
     *
     * @return int
     */
    public function relationCount(Model $result, ?Closure $closure = null, string $aggregate = 'count', string $field = '*')
    {
    }

    /**
     * 多态MorphTo 关联模型预查询.
     *
     * @param string $model       关联模型对象
     * @param string $relation    关联名
     * @param Model  $result
     * @param array  $subRelation 子关联
     * @param array  $cache       关联缓存
     *
     * @return void
     */
    protected function eagerlyMorphToOne(string $model, string $relation, Model $result, array $subRelation = [], array $cache = []): void
    {
        // 预载入关联查询 支持嵌套预载入
        $pk     = $this->parent->{$this->morphKey};
        $data   = null;

        if (class_exists($model)) {
            $data = (new $model())->with($subRelation)
                ->cache($cache[0] ?? false, $cache[1] ?? null, $cache[2] ?? null)
                ->find($pk);
        }

        $result->setRelation($relation, $data ?: null);
    }

    /**
     * 添加关联数据.
     *
     * @param Model  $model 关联模型对象
     * @param string $type  多态类型
     *
     * @return Model
     */
    public function associate(Model $model, string $type = ''): Model
    {
        $morphKey   = $this->morphKey;
        $morphType  = $this->morphType;
        $pk         = $model->getPk();

        $this->parent->set($morphKey, $model->$pk);
        $this->parent->set($morphType, $type ?: get_class($model));
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
        $morphKey   = $this->morphKey;
        $morphType  = $this->morphType;

        $this->parent->set($morphKey, null);
        $this->parent->set($morphType, null);
        $this->parent->save();

        return $this->parent->setRelation($this->relation, null);
    }

    protected function buildQuery(Query $query)
    {
        foreach ($this->queryCaller as $caller) {
            call_user_func_array([$query, $caller[0]], $caller[1]);
        }

        return $query;
    }

    public function __call($method, $args)
    {
        $this->queryCaller[] = [$method, $args];

        return $this;
    }
}
