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

namespace think\db\concern;

use Closure;
use think\helper\Str;
use think\model\Collection as ModelCollection;
use think\model\contract\Modelable as Model;

/**
 * 模型及关联查询.
 */
trait ModelRelationQuery
{
    /**
     * 当前模型对象
     *
     * @var Model
     */
    protected $model;

    /**
     * 指定模型.
     *
     * @param Model $model 模型对象实例
     *
     * @return $this
     */
    public function model(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * 获取当前的模型对象
     *
     * @return Model|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * 设置需要隐藏的输出属性.
     *
     * @param array $hidden 属性列表
     * @param bool $merge 是否合并
     *
     * @return $this
     */
    public function hidden(array $hidden, bool $merge = false)
    {
        $this->options['hidden'] = [$hidden, $merge];

        return $this;
    }

    /**
     * 设置需要输出的属性.
     *
     * @param array $visible 属性列表
     * @param bool  $merge 是否合并
     *
     * @return $this
     */
    public function visible(array $visible, bool $merge = false)
    {
        $this->options['visible'] = [$visible, $merge];

        return $this;
    }

    /**
     * 设置需要附加的输出属性.
     *
     * @param array $append 属性列表
     * @param bool  $merge  是否合并
     *
     * @return $this
     */
    public function append(array $append, bool $merge = false)
    {
        $this->options['append'] = [$append, $merge];

        return $this;
    }

    /**
     * 设置模型的输出映射.
     *
     * @param array $mapping 映射列表
     *
     * @return $this
     */
    public function mapping(array $mapping)
    {
        $this->options['mapping'] = $mapping;

        return $this;
    }

    /**
     * 添加查询范围.
     *
     * @param array|string|Closure $scope 查询范围定义
     * @param array                $args  参数
     *
     * @return $this
     */
    public function scope($scope, ...$args)
    {
        // 查询范围的第一个参数始终是当前查询对象
        array_unshift($args, $this);

        if ($scope instanceof Closure) {
            $this->options['scope'][] = [$scope, $args];
            return $this;
        }

        if ($this->model) {
            if (is_string($scope)) {
                $scope = explode(',', $scope);
            }

            // 检查模型类的查询范围方法
            $entity = $this->model->getEntity();
            foreach ($scope as $name) {
                $method = 'scope' . trim($name);
                if ($entity && method_exists($entity, $method)) {
                    $this->options['scope'][$name] = [[$entity, $method], $args];
                } elseif (method_exists($this->model, $method)) {
                    $this->options['scope'][$name] = [[$this->model, $method], $args];
                }
            }
        }

        return $this;
    }

    /**
     * 执行查询范围查询.
     *
     * @return $this
     */
    protected function scopeQuery()
    {
        if (!empty($this->options['scope'])) {
            foreach ($this->options['scope'] as $val) {
                [$call, $args] = $val;
                call_user_func_array($call, $args);
            }
        }

        return $this;
    }

    /**
     * 指定不使用的查询范围.
     *
     * @param array $scope 查询范围
     *
     * @return $this
     */
    public function withoutScope(array $scope = [])
    {
        if (empty($scope)) {
            $this->options['scope'] = [];
            return $this;
        }

        foreach ($scope as $name) {
            if (isset($this->options['scope'][$name])) {
                unset($this->options['scope'][$name]);
            }
        }

        return $this;
    }

    /**
     * 设置关联查询.
     *
     * @param array $relation 关联名称
     *
     * @return $this
     */
    public function relation(array $relation)
    {
        if (empty($this->model) || empty($relation)) {
            return $this;
        }

        $this->options['relation'] = $relation;

        return $this;
    }

    /**
     * 使用搜索器条件搜索字段.
     *
     * @param string|array $fields 搜索字段
     * @param mixed        $data   搜索数据
     * @param bool         $strict 是否严格检查数据
     *
     * @return $this
     */
    public function withSearch(string | array $fields, $data = [], bool $strict = false)
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        $likeFields = $this->getConfig('match_like_fields') ?: [];

        foreach ($fields as $key => $field) {
            if ($field instanceof Closure) {
                $field($this, $data[$key] ?? null, $data);
            } elseif ($this->model) {
                // 检查字段是否有数据
                $fieldName = is_numeric($key) ? $field : $key;
                if ($strict && (!isset($data[$fieldName]) || (empty($data[$fieldName]) && !in_array($data[$fieldName], ['0', 0])))) {
                    continue;
                }

                if (is_string($key) && isset($data[$key])) {
                    // 默认搜索规则
                    $this->where($key, $field, 'like' == $field ? '%' . $data[$key] . '%' : $data[$key]);
                    continue;
                }
                
                $method = 'search' . Str::studly($fieldName) . 'Attr';
                $entity = $this->model->getEntity();
                if ($entity && method_exists($entity, $method)) {
                    $entity->$method($this, $data[$fieldName] ?? null, $data);
                } elseif (method_exists($this->model, $method)) {
                    $this->model->$method($this, $data[$fieldName] ?? null, $data);
                } elseif (isset($data[$field])) {
                    $this->where($fieldName, in_array($fieldName, $likeFields) ? 'like' : '=', in_array($fieldName, $likeFields) ? '%' . $data[$field] . '%' : $data[$field]);
                }
            }
        }

        return $this;
    }

    /**
     * 限制关联数据的字段 已废弃直接使用field或withoutfield替代.
     *
     * @deprecated
     *
     * @param array|string $field 关联字段限制
     *
     * @return $this
     */
    public function withField($field)
    {
        return $this->field($field);
    }

    /**
     * 限制关联数据的数量 已废弃直接使用limit替代.
     *
     * @deprecated
     *
     * @param int $limit 关联数量限制
     *
     * @return $this
     */
    public function withLimit(int $limit)
    {
        return $this->limit($limit);
    }

    /**
     * 设置关联数据不存在的时候默认值
     *
     * @param mixed $data 默认值
     *
     * @return $this
     */
    public function withDefault($data = null)
    {
        $this->options['default_model'] = $data;

        return $this;
    }

    /**
     * 设置关联模型的动态绑定
     *
     * @param array $attr 绑定数据
     *
     * @return $this
     */
    public function withBind(array $attr)
    {
        $this->options['bind_attr'] = $attr;
        return $this;
    }

    /**
     * 设置数据字段获取器.
     *
     * @param string|array $name     字段名
     * @param callable     $callback 闭包获取器
     *
     * @return $this
     */
    public function withAttr(string | array $name,  ? callable $callback = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $this->withAttr($key, $val);
            }

            return $this;
        }

        $this->options['with_attr'][$name] = $callback;

        if (str_contains($name, '.')) {
            [$relation, $field] = explode('.', $name);

            if (!empty($this->options['json']) && in_array($relation, $this->options['json'])) {
            } else {
                $this->options['with_relation_attr'][$relation][$field] = $callback;
                unset($this->options['with_attr'][$name]);
            }
        }

        return $this;
    }

    /**
     * 关联预载入 In方式.
     *
     * @param array|string $with 关联方法名称
     *
     * @return $this
     */
    public function with(array | string $with)
    {
        if (empty($this->model) || empty($with)) {
            return $this;
        }

        $this->options['with'] = array_merge($this->options['with'] ?? [], (array) $with);

        return $this;
    }

    /**
     * 关联预载入 JOIN方式.
     *
     * @param array|string $with     关联方法名
     * @param string       $joinType JOIN方式
     *
     * @return $this
     */
    public function withJoin(array | string $with, string $joinType = '')
    {
        if (empty($this->model) || empty($with)) {
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
            } elseif (is_string($relation) && str_contains($relation, '.')) {
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
     *
     * @param array|string $relations 关联方法名
     * @param string       $aggregate 聚合查询方法
     * @param string       $field     字段
     * @param bool         $subQuery  是否使用子查询
     *
     * @return $this
     */
    protected function withAggregate(string | array $relations, string $aggregate = 'count', $field = 'id', bool $subQuery = true)
    {
        if (empty($this->model)) {
            return $this;
        }

        if (!$subQuery) {
            $this->options['with_aggregate'][] = [(array) $relations, $aggregate, $field];

            return $this;
        }

        if (!isset($this->options['field'])) {
            $this->field('*');
        }

        $this->model->relationCount($this, (array) $relations, $aggregate, $field, true);

        return $this;
    }

    /**
     * 关联缓存.
     *
     * @param string|array|bool $relation 关联方法名
     * @param mixed             $key      缓存key
     * @param int|\DateTime     $expire   缓存有效期
     * @param string            $tag      缓存标签
     *
     * @return $this
     */
    public function withCache(string | array | bool $relation = true, $key = true, $expire = null, ?string $tag = null)
    {
        if (empty($this->model)) {
            return $this;
        }

        if (false === $relation || false === $key || !$this->getConnection()->getCache()) {
            return $this;
        }

        if ($key instanceof \DateTimeInterface  || $key instanceof \DateInterval  || (is_int($key) && is_null($expire))) {
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
     *
     * @param string|array $relation 关联方法名
     * @param string       $field    字段(默认为id)
     * @param bool         $subQuery 是否使用子查询
     *
     * @return $this
     */
    public function withCount(string | array $relation, string $field = 'id', bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'count', $field, $subQuery);
    }

    /**
     * 关联统计Sum.
     *
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     *
     * @return $this
     */
    public function withSum(string | array $relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'sum', $field, $subQuery);
    }

    /**
     * 关联统计Max.
     *
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     *
     * @return $this
     */
    public function withMax(string | array $relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'max', $field, $subQuery);
    }

    /**
     * 关联统计Min.
     *
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     *
     * @return $this
     */
    public function withMin(string | array $relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'min', $field, $subQuery);
    }

    /**
     * 关联统计Avg.
     *
     * @param string|array $relation 关联方法名
     * @param string       $field    字段
     * @param bool         $subQuery 是否使用子查询
     *
     * @return $this
     */
    public function withAvg(string | array $relation, string $field, bool $subQuery = true)
    {
        return $this->withAggregate($relation, 'avg', $field, $subQuery);
    }

    /**
     * 查询关联数据存在（或超过多少条）的模型数据.
     *
     * @param string $relation 关联方法名
     * @param mixed  $operator 比较操作符
     * @param int    $count    个数
     * @param string $id       关联表的统计字段
     * @param string $joinType JOIN类型
     *
     * @return $this
     */
    public function has(string $relation, string $operator = '>=', int $count = 1, string $id = '*', string $joinType = '')
    {
        return $this->model->has($relation, $operator, $count, $id, $joinType, $this);
    }

    /**
     * 查询关联数据不存在的模型数据.
     *
     * @param string $relation 关联方法名
     * @param string $id       关联表的统计字段
     * @param string $joinType JOIN类型
     *
     * @return $this
     */
    public function hasNot(string $relation, string $id = '*', string $joinType = '')
    {
        return $this->model->has($relation, '=', 0, $id, $joinType, $this);
    }

    /**
     * 根据关联条件查询当前模型.
     *
     * @param string|array $relation 关联方法名 或 ['关联方法名', '关联表别名']
     * @param mixed  $where    查询条件（数组或者闭包）
     * @param mixed  $fields   字段
     * @param string $joinType JOIN类型
     *
     * @return $this
     */
    public function hasWhere(string|array $relation, $where = [], string $fields = '*', string $joinType = '')
    {
        return $this->model->hasWhere($relation, $where, $fields, $joinType, $this);
    }

    /**
     * 根据关联条件查询当前模型.
     *
     * @param string|array $relation 关联方法名 或 ['关联方法名', '关联表别名']
     * @param mixed  $where    查询条件（数组或者闭包）
     * @param mixed  $fields   字段
     * @param string $joinType JOIN类型
     *
     * @return $this
     */
    public function hasWhereOr(string|array $relation, $where = [], string $fields = '*', string $joinType = '')
    {
        return $this->model->hasWhereOr($relation, $where, $fields, $joinType, $this);
    }

    /**
     * 查询数据转换为模型数据集对象
     *
     * @param array $resultSet 数据集
     *
     * @return ModelCollection
     */
    protected function resultSetToModelCollection(array $resultSet): ModelCollection
    {
        if (empty($resultSet)) {
            return $this->model->toCollection();
        }

        $this->options['is_resultSet'] = true;

        foreach ($resultSet as $key => &$result) {
            // 数据转换为模型对象
            $this->resultToModel($result);
        }

        foreach (['with', 'with_join'] as $with) {
            // 关联预载入
            if (!empty($this->options[$with])) {
                $result->eagerlyResultSet(
                    $resultSet,
                    $this->options[$with],
                    $this->options['with_relation_attr'],
                    'with_join' == $with,
                    $this->options['with_cache'] ?? false
                );
            }
        }

        // 模型数据集转换
        return $this->model->toCollection($resultSet);
    }

    /**
     * 查询数据转换为模型对象
     *
     * @param array $result 查询数据
     *
     * @return void
     */
    protected function resultToModel(array &$result): void
    {
        // 实时读取延迟数据
        if (!empty($this->options['lazy_fields'])) {
            $id = $this->getKey($result);
            foreach ($this->options['lazy_fields'] as $field) {
                if (isset($result[$field])) {
                    $result[$field] += $this->getLazyFieldValue($field, $id);
                }
            }
        }

        $result = $this->model->newInstance($result, $this->options);

        if ($this->suffix) {
            $result->setSuffix($this->suffix);
        }

        // 模型数据处理
        foreach ($this->options['filter'] as $filter) {
            call_user_func_array($filter, [$result, $this->options]);
        }

        // 关联查询
        if (!empty($this->options['relation'])) {
            $result->relationQuery($this->options['relation'], $this->options['with_relation_attr']);
        }

        // 关联预载入查询
        if (empty($this->options['is_resultSet'])) {
            foreach (['with', 'with_join'] as $with) {
                if (!empty($this->options[$with])) {
                    $result->eagerlyResult(
                        $result,
                        $this->options[$with],
                        $this->options['with_relation_attr'],
                        'with_join' == $with,
                        $this->options['with_cache'] ?? false
                    );
                }
            }
        }

        // 关联统计查询
        if (!empty($this->options['with_aggregate'])) {
            foreach ($this->options['with_aggregate'] as $val) {
                $result->relationCount($this, $val[0], $val[1], $val[2], false);
            }
        }

        // 动态获取器
        if (!empty($this->options['with_attr'])) {
            $result->withFieldAttr($this->options['with_attr']);
        }

        // 模型输出设置
        foreach (['hidden', 'visible', 'append'] as $name) {
            if (!empty($this->options[$name])) {
                [$value, $merge] = $this->options[$name];
                $result->$name($value, $merge);
            }
        }

        // 字段映射
        if (!empty($this->options['mapping'])) {
            $result->mapping($this->options['mapping']);
        }
    }
}
