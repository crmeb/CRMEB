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

namespace think\model\relation;

use Closure;
use think\Collection;
use think\Container;
use think\db\BaseQuery as Query;
use think\Model;
use think\model\Relation;

/**
 * 远程一对多关联类
 */
class HasManyThrough extends Relation
{
    /**
     * 中间关联表外键
     * @var string
     */
    protected $throughKey;

    /**
     * 中间主键
     * @var string
     */
    protected $throughPk;

    /**
     * 中间表查询对象
     * @var Query
     */
    protected $through;

    /**
     * 架构函数
     * @access public
     * @param  Model  $parent     上级模型对象
     * @param  string $model      关联模型名
     * @param  string $through    中间模型名
     * @param  string $foreignKey 关联外键
     * @param  string $throughKey 中间关联外键
     * @param  string $localKey   当前模型主键
     * @param  string $throughPk  中间模型主键
     */
    public function __construct(Model $parent, string $model, string $through, string $foreignKey, string $throughKey, string $localKey, string $throughPk)
    {
        $this->parent     = $parent;
        $this->model      = $model;
        $this->through    = (new $through)->db();
        $this->foreignKey = $foreignKey;
        $this->throughKey = $throughKey;
        $this->localKey   = $localKey;
        $this->throughPk  = $throughPk;
        $this->query      = (new $model)->db();
    }

    /**
     * 延迟获取关联数据
     * @access public
     * @param  array   $subRelation 子关联名
     * @param  Closure $closure     闭包查询条件
     * @return Collection
     */
    public function getRelation(array $subRelation = [], Closure $closure = null)
    {
        if ($closure) {
            $closure($this);
        }

        $this->baseQuery();

        if ($this->withLimit) {
            $this->query->limit($this->withLimit);
        }

        return $this->query->relation($subRelation)
            ->select()
            ->setParent(clone $this->parent);
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param  string  $operator 比较操作符
     * @param  integer $count    个数
     * @param  string  $id       关联表的统计字段
     * @param  string  $joinType JOIN类型
     * @return Query
     */
    public function has(string $operator = '>=', int $count = 1, string $id = '*', string $joinType = ''): Query
    {
        $model         = Container::parseName(Container::classBaseName($this->parent));
        $throughTable  = $this->through->getTable();
        $pk            = $this->throughPk;
        $throughKey    = $this->throughKey;
        $relation      = new $this->model;
        $relationTable = $relation->getTable();

        if ('*' != $id) {
            $id = $relationTable . '.' . $relation->getPk();
        }

        return $this->parent->db()
            ->alias($model)
            ->field($model . '.*')
            ->join($throughTable, $throughTable . '.' . $this->foreignKey . '=' . $model . '.' . $this->localKey)
            ->join($relationTable, $relationTable . '.' . $throughKey . '=' . $throughTable . '.' . $this->throughPk)
            ->group($relationTable . '.' . $this->throughKey)
            ->having('count(' . $id . ')' . $operator . $count);
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param  mixed  $where 查询条件（数组或者闭包）
     * @param  mixed  $fields 字段
     * @param  string $joinType JOIN类型
     * @return Query
     */
    public function hasWhere($where = [], $fields = null, $joinType = ''): Query
    {
        $model        = Container::parseName(Container::classBaseName($this->parent));
        $throughTable = $this->through->getTable();
        $pk           = $this->throughPk;
        $throughKey   = $this->throughKey;
        $modelTable   = (new $this->model)->getTable();

        if (is_array($where)) {
            $this->getQueryWhere($where, $modelTable);
        } elseif ($where instanceof Query) {
            $where->via($modelTable);
        } elseif ($where instanceof Closure) {
            $where($this->query->via($modelTable));
            $where = $this->query;
        }

        $fields = $this->getRelationQueryFields($fields, $model);

        return $this->parent->db()
            ->alias($model)
            ->join($throughTable, $throughTable . '.' . $this->foreignKey . '=' . $model . '.' . $this->localKey)
            ->join($modelTable, $modelTable . '.' . $throughKey . '=' . $throughTable . '.' . $this->throughPk)
            ->group($modelTable . '.' . $this->throughKey)
            ->where($where)
            ->field($fields);
    }

    /**
     * 预载入关联查询（数据集）
     * @access protected
     * @param  array   $resultSet   数据集
     * @param  string  $relation    当前关联名
     * @param  array   $subRelation 子关联名
     * @param  Closure $closure     闭包
     * @return void
     */
    public function eagerlyResultSet(array &$resultSet, string $relation, array $subRelation = [], Closure $closure = null): void
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

            $data = $this->eagerlyWhere([
                [$this->foreignKey, 'in', $range],
            ], $foreignKey, $relation, $subRelation, $closure);

            // 关联属性名
            $attr = Container::parseName($relation);

            // 关联数据封装
            foreach ($resultSet as $result) {
                $pk = $result->$localKey;
                if (!isset($data[$pk])) {
                    $data[$pk] = [];
                }

                // 设置关联属性
                $result->setRelation($attr, $this->resultSetBuild($data[$pk], clone $this->parent));
            }
        }
    }

    /**
     * 预载入关联查询（数据）
     * @access protected
     * @param  Model   $result      数据对象
     * @param  string  $relation    当前关联名
     * @param  array   $subRelation 子关联名
     * @param  Closure $closure     闭包
     * @return void
     */
    public function eagerlyResult(Model $result, string $relation, array $subRelation = [], Closure $closure = null): void
    {
        $localKey   = $this->localKey;
        $foreignKey = $this->foreignKey;
        $pk         = $result->$localKey;

        $this->query->removeWhereField($foreignKey);

        $data = $this->eagerlyWhere([
            [$foreignKey, '=', $pk],
        ], $foreignKey, $relation, $subRelation, $closure);

        // 关联数据封装
        if (!isset($data[$pk])) {
            $data[$pk] = [];
        }

        $result->setRelation(Container::parseName($relation), $this->resultSetBuild($data[$pk], clone $this->parent));
    }

    /**
     * 关联模型预查询
     * @access public
     * @param  array   $where       关联预查询条件
     * @param  string  $key         关联键名
     * @param  string  $relation    关联名
     * @param  array   $subRelation 子关联
     * @param  Closure $closure
     * @return array
     */
    protected function eagerlyWhere(array $where, string $key, string $relation, array $subRelation = [], Closure $closure = null): array
    {
        // 预载入关联查询 支持嵌套预载入
        $throughList = $this->through->where($where)->select();
        $keys        = $throughList->column($this->throughPk, $this->throughPk);

        if ($closure) {
            $this->baseQuery = true;
            $closure($this);
        }

        $list = $this->query->where($this->throughKey, 'in', $keys)->select();

        // 组装模型数据
        $data = [];
        $keys = $throughList->column($this->foreignKey, $this->throughPk);

        foreach ($list as $set) {
            $key = $keys[$set->{$this->throughKey}];

            if ($this->withLimit && isset($data[$key]) && count($data[$key]) >= $this->withLimit) {
                continue;
            }

            $data[$key][] = $set;
        }

        return $data;
    }

    /**
     * 关联统计
     * @access public
     * @param  Model   $result  数据对象
     * @param  Closure $closure 闭包
     * @param  string  $aggregate 聚合查询方法
     * @param  string  $field 字段
     * @param  string  $name 统计字段别名
     * @return mixed
     */
    public function relationCount(Model $result, Closure $closure = null, string $aggregate = 'count', string $field = '*', string &$name = null)
    {
        $localKey = $this->localKey;

        if (!isset($result->$localKey)) {
            return 0;
        }

        if ($closure) {
            $closure($this, $name);
        }

        $alias        = Container::parseName(Container::classBaseName($this->model));
        $throughTable = $this->through->getTable();
        $pk           = $this->throughPk;
        $throughKey   = $this->throughKey;
        $modelTable   = $this->parent->getTable();

        if (false === strpos($field, '.')) {
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
            $closure($this, $name);
        }

        $alias        = Container::parseName(Container::classBaseName($this->model));
        $throughTable = $this->through->getTable();
        $pk           = $this->throughPk;
        $throughKey   = $this->throughKey;
        $modelTable   = $this->parent->getTable();

        if (false === strpos($field, '.')) {
            $field = $alias . '.' . $field;
        }

        return $this->query
            ->alias($alias)
            ->join($throughTable, $throughTable . '.' . $pk . '=' . $alias . '.' . $throughKey)
            ->join($modelTable, $modelTable . '.' . $this->localKey . '=' . $throughTable . '.' . $this->foreignKey)
            ->whereExp($throughTable . '.' . $this->foreignKey, '=' . $this->parent->getTable() . '.' . $this->localKey)
            ->fetchSql()
            ->$aggregate($field);
    }

    /**
     * 执行基础查询（仅执行一次）
     * @access protected
     * @return void
     */
    protected function baseQuery(): void
    {
        if (empty($this->baseQuery) && $this->parent->getData()) {
            $alias        = Container::parseName(Container::classBaseName($this->model));
            $throughTable = $this->through->getTable();
            $pk           = $this->throughPk;
            $throughKey   = $this->throughKey;
            $modelTable   = $this->parent->getTable();
            $fields       = $this->getQueryFields($alias);

            $this->query
                ->field($fields)
                ->alias($alias)
                ->join($throughTable, $throughTable . '.' . $pk . '=' . $alias . '.' . $throughKey)
                ->join($modelTable, $modelTable . '.' . $this->localKey . '=' . $throughTable . '.' . $this->foreignKey)
                ->where($throughTable . '.' . $this->foreignKey, $this->parent->{$this->localKey});

            $this->baseQuery = true;
        }
    }

}
