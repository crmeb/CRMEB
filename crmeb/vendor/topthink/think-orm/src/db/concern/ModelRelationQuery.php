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

namespace think\db\concern;

use Closure;
use think\helper\Str;
use think\Model;
use think\model\Collection as ModelCollection;

/**
 * 模型及关联查询
 */
trait ModelRelationQuery
{

    /**
     * 当前模型对象
     * @var Model
     */
    protected $model;

    /**
     * 指定模型
     * @access public
     * @param Model $model 模型对象实例
     * @return $this
     */
    public function model(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * 获取当前的模型对象
     * @access public
     * @return Model|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * 设置需要隐藏的输出属性
     * @access public
     * @param array $hidden 需要隐藏的字段名
     * @return $this
     */
    public function hidden(array $hidden)
    {
        $this->options['hidden'] = $hidden;
        return $this;
    }

    /**
     * 设置需要输出的属性
     * @access public
     * @param array $visible 需要输出的属性
     * @return $this
     */
    public function visible(array $visible)
    {
        $this->options['visible'] = $visible;
        return $this;
    }

    /**
     * 设置需要追加输出的属性
     * @access public
     * @param array $append 需要追加的属性
     * @return $this
     */
    public function append(array $append)
    {
        $this->options['append'] = $append;
        return $this;
    }

    /**
     * 添加查询范围
     * @access public
     * @param array|string|Closure $scope 查询范围定义
     * @param array                $args  参数
     * @return $this
     */
    public function scope($scope, ...$args)
    {
        // 查询范围的第一个参数始终是当前查询对象
        array_unshift($args, $this);

        if ($scope instanceof Closure) {
            call_user_func_array($scope, $args);
            return $this;
        }

        if (is_string($scope)) {
            $scope = explode(',', $scope);
        }

        if ($this->model) {
            // 检查模型类的查询范围方法
            foreach ($scope as $name) {
                $method = 'scope' . trim($name);

                if (method_exists($this->model, $method)) {
                    call_user_func_array([$this->model, $method], $args);
                }
            }
        }

        return $this;
    }

    /**
     * 设置关联查询
     * @access public
     * @param array $relation 关联名称
     * @return $this
     */
    public function relation(array $relation)
    {
        if (!empty($relation)) {
            $this->options['relation'] = $relation;
        }

        return $this;
    }

    /**
     * 使用搜索器条件搜索字段
     * @access public
     * @param string|array  $fields 搜索字段
     * @param mixed         $data   搜索数据
     * @param string        $prefix 字段前缀标识
     * @return $this
     */
    public function withSearch($fields, $data = [], string $prefix = '')
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        $likeFields = $this->getConfig('match_like_fields') ?: [];

        foreach ($fields as $key => $field) {
            if ($field instanceof Closure) {
                $field($this, $data[$key] ?? null, $data, $prefix);
            } elseif ($this->model) {
                // 检测搜索器
                $fieldName = is_numeric($key) ? $field : $key;
                $method    = 'search' . Str::studly($fieldName) . 'Attr';

                if (method_exists($this->model, $method)) {
                    $this->model->$method($this, $data[$field] ?? null, $data, $prefix);
                } elseif (isset($data[$field])) {
                    $this->where($fieldName, in_array($fieldName, $likeFields) ? 'like' : '=', in_array($fieldName, $likeFields) ? '%' . $data[$field] . '%' : $data[$field]);
                }
            }
        }

        return $this;
    }

    /**
     * 设置数据字段获取器
     * @access public
     * @param string|array $name     字段名
     * @param callable     $callback 闭包获取器
     * @return $this
     */
    public function withAttr($name, callable $callback = null)
    {
        if (is_array($name)) {
            $this->options['with_attr'] = $name;
        } else {
            $this->options['with_attr'][$name] = $callback;
        }

        return $this;
    }

    /**
     * 关联预载入 In方式
     * @access public
     * @param array|string $with 关联方法名称
     * @return $this
     */
    public function with($with)
    {
        if (!empty($with)) {
            $this->options['with'] = (array) $with;
        }

        return $this;
    }

    /**
     * 关联预载入 JOIN方式
     * @access protected
     * @param array|string $with     关联方法名
     * @param string       $joinType JOIN方式
     * @return $this
     */
    public function withJoin($with, string $joinType = '')
    {
        if (empty($with)) {
            return $this;
        }

        $with  = (array) $with;
        $first = true;

        foreach ($with as $key => $relation) {
            $closure = null;
            $field   = true;

            if ($relation instanceof Closure) {
                // 支持闭包查询过滤关联条件
                $closure  = $relation;
                $relation = $key;
            } elseif (is_array($relation)) {
                $field    = $relation;
                $relation = $key;
            } elseif (is_string($relation) && strpos($relation, '.')) {
                $relation = strstr($relation, '.', true);
            }

            $result = $this->model->eagerly($this, $relation, $field, $joinType, $closure, $first);

            if (!$result) {
                unset($with[$key]);
            } else {
                $first = false;
            }
        }

        $this->via();

        $this->options['with_join'] = $with;

        return $this;
    }

    /**
     * 关联统计
     * @access protected
     * @param array|string $relations 关联方法名
     * @param string       $aggregate 聚合查询方法
     * @param string       $field     字段
     * @param bool         $subQuery  是否使用子查询
     * @return $this
     */
    protected function withAggregate($relations, string $aggregate = 'count', $field = '*', bool $subQuery = true)
    {
        if (!$subQuery) {
            $this->options['with_count'][] = [$relations, $aggregate, $field];
        } else {
            if (!isset($this->options['field'])) {
                $this->field('*');
            }

            $this->model->relationCount($this, (array) $relations, $aggregate, $field, true);
        }

        return $this;
    }

    /**
     * 关联缓存
     * @access public
     * @param string|array|bool $relation 关联方法名
     * @param mixed             $key    缓存key
     * @param integer|\DateTime $expire 缓存有效期
     * @param string            $tag    缓存标签
     * @return $this
     */
    public function withCache($relation = true, $key = true, $expire = null, string $tag = null)
    {
        if (false === $relation || false === $key || !$this->getConnection()->getCache()) {
            return $this;
        }

        if ($key instanceof \DateTimeInterface || $key instanceof \DateInterval || (is_int($key) && is_null($expire))) {
            $expire = $key;
            $key    = true;
        }

        if (true === $relation || is_numeric($relation)) {
            $this->options['with_cache'] = $relation;
            return $this;
        }

        $relations = (array) $relation;
        foreach ($relations as $name => $relation) {
            if (!is_numeric($name)) {
                $this->options['with_cache'][$name] = is_array($relation) ? $relation : [$key, $relation, $tag];
            } else {
                $this->options['with_cache'][$relation] = [$key, $expire, $tag];
            }
        }

        return $this;
    }

    /**
     * 关联统计
     * @access public
     * @param string|array $relation 关联方法名
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withCount($relation, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'count', '*', $subQuery);
    }

    /**
     * 关联统计Sum
     * @access public
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withSum($relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'sum', $field, $subQuery);
    }

    /**
     * 关联统计Max
     * @access public
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withMax($relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'max', $field, $subQuery);
    }

    /**
     * 关联统计Min
     * @access public
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withMin($relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'min', $field, $subQuery);
    }

    /**
     * 关联统计Avg
     * @access public
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     * @return $this
     */
    public function withAvg($relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'avg', $field, $subQuery);
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param  string  $relation 关联方法名
     * @param  mixed   $operator 比较操作符
     * @param  integer $count    个数
     * @param  string  $id       关联表的统计字段
     * @param  string  $joinType JOIN类型
     * @return $this
     */
    public function has(string $relation, string $operator = '>=', int $count = 1, string $id = '*', string $joinType = '')
    {
        return $this->model->has($relation, $operator, $count, $id, $joinType, $this);
    }

    /**
     * 根据关联条件查询当前模型
     * @access public
     * @param  string $relation 关联方法名
     * @param  mixed  $where    查询条件（数组或者闭包）
     * @param  mixed  $fields   字段
     * @param  string $joinType JOIN类型
     * @return $this
     */
    public function hasWhere(string $relation, $where = [], string $fields = '*', string $joinType = '')
    {
        return $this->model->hasWhere($relation, $where, $fields, $joinType, $this);
    }

    /**
     * 查询数据转换为模型数据集对象
     * @access protected
     * @param array $resultSet 数据集
     * @return ModelCollection
     */
    protected function resultSetToModelCollection(array $resultSet): ModelCollection
    {
        if (empty($resultSet)) {
            return $this->model->toCollection();
        }

        // 检查动态获取器
        if (!empty($this->options['with_attr'])) {
            foreach ($this->options['with_attr'] as $name => $val) {
                if (strpos($name, '.')) {
                    [$relation, $field] = explode('.', $name);

                    $withRelationAttr[$relation][$field] = $val;
                    unset($this->options['with_attr'][$name]);
                }
            }
        }

        $withRelationAttr = $withRelationAttr ?? [];

        foreach ($resultSet as $key => &$result) {
            // 数据转换为模型对象
            $this->resultToModel($result, $this->options, true, $withRelationAttr);
        }

        if (!empty($this->options['with'])) {
            // 预载入
            $result->eagerlyResultSet($resultSet, $this->options['with'], $withRelationAttr, false, $this->options['with_cache'] ?? false);
        }

        if (!empty($this->options['with_join'])) {
            // 预载入
            $result->eagerlyResultSet($resultSet, $this->options['with_join'], $withRelationAttr, true, $this->options['with_cache'] ?? false);
        }

        // 模型数据集转换
        return $this->model->toCollection($resultSet);
    }

    /**
     * 查询数据转换为模型对象
     * @access protected
     * @param array $result           查询数据
     * @param array $options          查询参数
     * @param bool  $resultSet        是否为数据集查询
     * @param array $withRelationAttr 关联字段获取器
     * @return void
     */
    protected function resultToModel(array &$result, array $options = [], bool $resultSet = false, array $withRelationAttr = []): void
    {
        // 动态获取器
        if (!empty($options['with_attr']) && empty($withRelationAttr)) {
            foreach ($options['with_attr'] as $name => $val) {
                if (strpos($name, '.')) {
                    [$relation, $field] = explode('.', $name);

                    $withRelationAttr[$relation][$field] = $val;
                    unset($options['with_attr'][$name]);
                }
            }
        }

        // JSON 数据处理
        if (!empty($options['json'])) {
            $this->jsonResult($result, $options['json'], $options['json_assoc'], $withRelationAttr);
        }

        $result = $this->model
            ->newInstance($result, $resultSet ? null : $this->getModelUpdateCondition($options));

        // 动态获取器
        if (!empty($options['with_attr'])) {
            $result->withAttribute($options['with_attr']);
        }

        // 输出属性控制
        if (!empty($options['visible'])) {
            $result->visible($options['visible']);
        } elseif (!empty($options['hidden'])) {
            $result->hidden($options['hidden']);
        }

        if (!empty($options['append'])) {
            $result->append($options['append']);
        }

        // 关联查询
        if (!empty($options['relation'])) {
            $result->relationQuery($options['relation'], $withRelationAttr);
        }

        // 预载入查询
        if (!$resultSet && !empty($options['with'])) {
            $result->eagerlyResult($result, $options['with'], $withRelationAttr, false, $options['with_cache'] ?? false);
        }

        // JOIN预载入查询
        if (!$resultSet && !empty($options['with_join'])) {
            $result->eagerlyResult($result, $options['with_join'], $withRelationAttr, true, $options['with_cache'] ?? false);
        }

        // 关联统计
        if (!empty($options['with_count'])) {
            foreach ($options['with_count'] as $val) {
                $result->relationCount($this, (array) $val[0], $val[1], $val[2], false);
            }
        }
    }

}
