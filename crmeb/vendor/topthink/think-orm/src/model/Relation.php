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

namespace think\model;

use think\db\Query;
use think\Exception;
use think\Model;

/**
 * 模型关联基础类
 * @package think\model
 *
 * @mixin Query
 */
abstract class Relation
{
    /**
     * 父模型对象
     * @var Model
     */
    protected $parent;

    /**
     * 当前关联的模型类名
     * @var string
     */
    protected $model;

    /**
     * 关联模型查询对象
     * @var Query
     */
    protected $query;

    /**
     * 关联表外键
     * @var string
     */
    protected $foreignKey;

    /**
     * 关联表主键
     * @var string
     */
    protected $localKey;

    /**
     * 是否执行关联基础查询
     * @var bool
     */
    protected $baseQuery;

    /**
     * 是否为自关联
     * @var bool
     */
    protected $selfRelation = false;

    /**
     * 关联数据数量限制
     * @var int
     */
    protected $withLimit;

    /**
     * 关联数据字段限制
     * @var array
     */
    protected $withField;

    /**
     * 获取关联的所属模型
     * @access public
     * @return Model
     */
    public function getParent(): Model
    {
        return $this->parent;
    }

    /**
     * 获取当前的关联模型类的Query实例
     * @access public
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * 获取当前的关联模型类的实例
     * @access public
     * @param bool $clear 是否需要清空查询条件
     * @return Model
     */
    public function getModel(bool $clear = true): Model
    {
        return $this->query->getModel($clear);
    }

    /**
     * 当前关联是否为自关联
     * @access public
     * @return bool
     */
    public function isSelfRelation(): bool
    {
        return $this->selfRelation;
    }

    /**
     * 封装关联数据集
     * @access public
     * @param  array $resultSet 数据集
     * @param  Model $parent 父模型
     * @return mixed
     */
    protected function resultSetBuild(array $resultSet, Model $parent = null)
    {
        return (new $this->model)->toCollection($resultSet)->setParent($parent);
    }

    protected function getQueryFields(string $model)
    {
        $fields = $this->query->getOptions('field');
        return $this->getRelationQueryFields($fields, $model);
    }

    protected function getRelationQueryFields($fields, string $model)
    {
        if (empty($fields) || '*' == $fields) {
            return $model . '.*';
        }

        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        foreach ($fields as &$field) {
            if (false === strpos($field, '.')) {
                $field = $model . '.' . $field;
            }
        }

        return $fields;
    }

    protected function getQueryWhere(array &$where, string $relation): void
    {
        foreach ($where as $key => &$val) {
            if (is_string($key)) {
                $where[] = [false === strpos($key, '.') ? $relation . '.' . $key : $key, '=', $val];
                unset($where[$key]);
            } elseif (isset($val[0]) && false === strpos($val[0], '.')) {
                $val[0] = $relation . '.' . $val[0];
            }
        }
    }

    /**
     * 更新数据
     * @access public
     * @param  array $data 更新数据
     * @return integer
     */
    public function update(array $data = []): int
    {
        return $this->query->update($data);
    }

    /**
     * 删除记录
     * @access public
     * @param  mixed $data 表达式 true 表示强制删除
     * @return int
     * @throws Exception
     * @throws PDOException
     */
    public function delete($data = null): int
    {
        return $this->query->delete($data);
    }

    /**
     * 限制关联数据的数量
     * @access public
     * @param  int $limit 关联数量限制
     * @return $this
     */
    public function withLimit(int $limit)
    {
        $this->withLimit = $limit;
        return $this;
    }

    /**
     * 限制关联数据的字段
     * @access public
     * @param  array $field 关联字段限制
     * @return $this
     */
    public function withField(array $field)
    {
        $this->withField = $field;
        return $this;
    }

    /**
     * 执行基础查询（仅执行一次）
     * @access protected
     * @return void
     */
    protected function baseQuery(): void
    {}

    public function __call($method, $args)
    {
        if ($this->query) {
            // 执行基础查询
            $this->baseQuery();

            $model  = $this->query->getModel(false);
            $result = call_user_func_array([$model, $method], $args);

            $this->query = $model->getQuery();
            return $result === $this->query ? $this : $result;
        }

        throw new Exception('method not exists:' . __CLASS__ . '->' . $method);
    }
}
