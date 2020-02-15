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

use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\helper\Str;

/**
 * 查询数据处理
 */
trait ResultOperation
{
    /**
     * 是否允许返回空数据（或空模型）
     * @access public
     * @param bool $allowEmpty 是否允许为空
     * @return $this
     */
    public function allowEmpty(bool $allowEmpty = true)
    {
        $this->options['allow_empty'] = $allowEmpty;
        return $this;
    }

    /**
     * 设置查询数据不存在是否抛出异常
     * @access public
     * @param bool $fail 数据不存在是否抛出异常
     * @return $this
     */
    public function failException(bool $fail = true)
    {
        $this->options['fail'] = $fail;
        return $this;
    }

    /**
     * 处理数据
     * @access protected
     * @param array $result 查询数据
     * @return void
     */
    protected function result(array &$result): void
    {
        if (!empty($this->options['json'])) {
            $this->jsonResult($result, $this->options['json'], true);
        }

        if (!empty($this->options['with_attr'])) {
            $this->getResultAttr($result, $this->options['with_attr']);
        }

        $this->filterResult($result);
    }

    /**
     * 处理数据集
     * @access public
     * @param array $resultSet 数据集
     * @return void
     */
    protected function resultSet(array &$resultSet): void
    {
        if (!empty($this->options['json'])) {
            foreach ($resultSet as &$result) {
                $this->jsonResult($result, $this->options['json'], true);
            }
        }

        if (!empty($this->options['with_attr'])) {
            foreach ($resultSet as &$result) {
                $this->getResultAttr($result, $this->options['with_attr']);
            }
        }

        if (!empty($this->options['visible']) || !empty($this->options['hidden'])) {
            foreach ($resultSet as &$result) {
                $this->filterResult($result);
            }
        }

        // 返回Collection对象
        $resultSet = new Collection($resultSet);
    }

    /**
     * 处理数据的可见和隐藏
     * @access protected
     * @param array $result 查询数据
     * @return void
     */
    protected function filterResult(&$result): void
    {
        if (!empty($this->options['visible'])) {
            foreach ($this->options['visible'] as $key) {
                $array[] = $key;
            }
            $result = array_intersect_key($result, array_flip($array));
        } elseif (!empty($this->options['hidden'])) {
            foreach ($this->options['hidden'] as $key) {
                $array[] = $key;
            }
            $result = array_diff_key($result, array_flip($array));
        }
    }

    /**
     * 使用获取器处理数据
     * @access protected
     * @param array $result   查询数据
     * @param array $withAttr 字段获取器
     * @return void
     */
    protected function getResultAttr(array &$result, array $withAttr = []): void
    {
        foreach ($withAttr as $name => $closure) {
            $name = Str::snake($name);

            if (strpos($name, '.')) {
                // 支持JSON字段 获取器定义
                list($key, $field) = explode('.', $name);

                if (isset($result[$key])) {
                    $result[$key][$field] = $closure($result[$key][$field] ?? null, $result[$key]);
                }
            } else {
                $result[$name] = $closure($result[$name] ?? null, $result);
            }
        }
    }

    /**
     * 处理空数据
     * @access protected
     * @return array|Model|null
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    protected function resultToEmpty()
    {
        if (!empty($this->options['fail'])) {
            $this->throwNotFound();
        } elseif (!empty($this->options['allow_empty'])) {
            return !empty($this->model) ? $this->model->newInstance() : [];
        }
    }

    /**
     * 查找单条记录 不存在返回空数据（或者空模型）
     * @access public
     * @param mixed $data 数据
     * @return array|Model
     */
    public function findOrEmpty($data = null)
    {
        return $this->allowEmpty(true)->find($data);
    }

    /**
     * JSON字段数据转换
     * @access protected
     * @param array $result           查询数据
     * @param array $json             JSON字段
     * @param bool  $assoc            是否转换为数组
     * @param array $withRelationAttr 关联获取器
     * @return void
     */
    protected function jsonResult(array &$result, array $json = [], bool $assoc = false, array $withRelationAttr = []): void
    {
        foreach ($json as $name) {
            if (!isset($result[$name])) {
                continue;
            }

            $result[$name] = json_decode($result[$name], true);

            if (isset($withRelationAttr[$name])) {
                foreach ($withRelationAttr[$name] as $key => $closure) {
                    $result[$name][$key] = $closure($result[$name][$key] ?? null, $result[$name]);
                }
            }

            if (!$assoc) {
                $result[$name] = (object) $result[$name];
            }
        }
    }

    /**
     * 查询失败 抛出异常
     * @access protected
     * @return void
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    protected function throwNotFound(): void
    {
        if (!empty($this->model)) {
            $class = get_class($this->model);
            throw new ModelNotFoundException('model data Not Found:' . $class, $class, $this->options);
        }

        $table = $this->getTable();
        throw new DataNotFoundException('table data not Found:' . $table, $table, $this->options);
    }

    /**
     * 查找多条记录 如果不存在则抛出异常
     * @access public
     * @param array|string|Query|Closure $data 数据
     * @return array|Model
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function selectOrFail($data = null)
    {
        return $this->failException(true)->select($data);
    }

    /**
     * 查找单条记录 如果不存在则抛出异常
     * @access public
     * @param array|string|Query|Closure $data 数据
     * @return array|Model
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function findOrFail($data = null)
    {
        return $this->failException(true)->find($data);
    }

}
