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
use think\model\contract\Modelable as Model;

/**
 * 远程一对一关联类.
 */
class HasOneThrough extends HasManyThrough
{
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
        } else {
            $default = $this->query->getOption('default_model');
            $relationModel = $this->getDefaultModel($default);
        }

        return $relationModel;
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
    public function eagerlyResultSet(array &$resultSet, string $relation, array $subRelation = [], ?Closure $closure = null, array $cache = []): void
    {
        $localKey   = $this->localKey;
        $foreignKey = $this->foreignKey;

        $range      = [];
        foreach ($resultSet as $result) {
            // 获取关联外键列表
            if (isset($result->$localKey)) {
                $range[] = $result->$localKey;
            }
        }

        if (!empty($range)) {
            $this->query->removeWhereField($foreignKey);
            $default = $this->query->getOption('default_model');
            $defaultModel = $this->getDefaultModel($default);

            $data = $this->eagerlyWhere([
                [$this->foreignKey, 'in', $range],
            ], $foreignKey, $subRelation, $closure, $cache);

            // 关联数据封装
            foreach ($resultSet as $result) {
                // 关联模型
                if (!isset($data[$result->$localKey])) {
                    $relationModel = $defaultModel;
                } else {
                    $relationModel = $data[$result->$localKey];
                }

                // 设置关联属性
                $result->setRelation($relation, $relationModel);
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
    public function eagerlyResult(Model $result, string $relation, array $subRelation = [], ?Closure $closure = null, array $cache = []): void
    {
        $localKey   = $this->localKey;
        $foreignKey = $this->foreignKey;

        $this->query->removeWhereField($foreignKey);

        $data = $this->eagerlyWhere([
            [$foreignKey, '=', $result->$localKey],
        ], $foreignKey, $subRelation, $closure, $cache);

        // 关联模型
        if (!isset($data[$result->$localKey])) {
            $default = $this->query->getOption('default_model');
            $relationModel = $this->getDefaultModel($default);
        } else {
            $relationModel = $data[$result->$localKey];
        }

        $result->setRelation($relation, $relationModel);
    }

    /**
     * 关联模型预查询.
     *
     * @param array   $where       关联预查询条件
     * @param string  $key         关联键名
     * @param array   $subRelation 子关联
     * @param Closure $closure
     * @param array   $cache       关联缓存
     * @param bool    $collection  是否数据集查询
     *
     * @return array
     */
    protected function eagerlyWhere(array $where, string $key, array $subRelation = [], ?Closure $closure = null, array $cache = [], bool $collection = false): array
    {
        // 预载入关联查询 支持嵌套预载入
        $keys = $this->through->where($where)->column($this->throughPk, $this->foreignKey);

        if ($closure) {
            $closure($this->query);
        }

        $list = $this->query
            ->where($this->throughKey, 'in', $keys)
            ->cache($cache[0] ?? false, $cache[1] ?? null, $cache[2] ?? null)
            ->select();

        // 组装模型数据
        return array_map(function ($key) use ($list) {
            $set = $list->where($this->throughKey, '=', $key)->first();
            return $set ?: null;
        }, $keys);
    }
}
