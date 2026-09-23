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

use think\db\Raw;
use think\helper\Str;

/**
 * JOIN和VIEW查询.
 */
trait JoinAndViewQuery
{
    /**
     * 查询SQL组装 join.
     *
     * @param array|string|Raw   $join      关联的表名
     * @param mixed  $condition 条件
     * @param string $type      JOIN类型
     * @param array  $bind      参数绑定
     *
     * @return $this
     */
    public function join(array | string | Raw $join, ?string $condition = null, string $type = 'INNER', array $bind = [])
    {
        $table = $this->getJoinTable($join, $alias);

        if (!empty($bind) && $condition) {
            $this->bindParams($condition, $bind);
        }

        if ($alias) {
            $this->options['join'][$alias] = [$table, strtoupper($type), $condition];
        } else {
            $this->options['join'][] = [$table, strtoupper($type), $condition];
        }

        return $this;
    }

    /**
     * LEFT JOIN.
     *
     * @param array|string|Raw  $join      关联的表名
     * @param mixed $condition 条件
     * @param array $bind      参数绑定
     *
     * @return $this
     */
    public function leftJoin(array | string | Raw $join, ?string $condition = null, array $bind = [])
    {
        return $this->join($join, $condition, 'LEFT', $bind);
    }

    /**
     * RIGHT JOIN.
     *
     * @param array|string|Raw  $join      关联的表名
     * @param mixed $condition 条件
     * @param array $bind      参数绑定
     *
     * @return $this
     */
    public function rightJoin(array | string | Raw $join, ?string $condition = null, array $bind = [])
    {
        return $this->join($join, $condition, 'RIGHT', $bind);
    }

    /**
     * FULL JOIN.
     *
     * @param array|string|Raw  $join      关联的表名
     * @param mixed $condition 条件
     * @param array $bind      参数绑定
     *
     * @return $this
     */
    public function fullJoin(array | string | Raw $join, ?string $condition = null, array $bind = [])
    {
        return $this->join($join, $condition, 'FULL', $bind);
    }

    /**
     * 获取Join表名及别名 支持
     * ['prefix_table或者子查询'=>'alias'] 'table alias'.
     *
     * @param array|string|Raw $join  JION表名
     * @param string           $alias 别名
     *
     * @return string|array
     */
    protected function getJoinTable(array | string | Raw $join, ?string &$alias = null)
    {
        if (is_array($join)) {
            $table = $join;
            $alias = array_shift($join);
            return $table;
        }

        if ($join instanceof Raw || str_contains($join, '(')) {
            return $join;
        }

        $join = trim($join);

        // 使用别名
        if (str_contains($join, ' ')) {
            // 使用别名
            [$table, $alias] = explode(' ', $join);
        } else {
            $table = $join;
            if (!str_contains($join, '.')) {
                $alias = $join;
            }
        }

        if ($this->prefix && !str_contains($table, '.') && !str_starts_with($table, $this->prefix)) {
            $table = $this->prefix . Str::snake($table) . $this->suffix;
        }

        if (!empty($alias) && $table != $alias) {
            $table = [$table => $alias];
        }

        return $table;
    }

    /**
     * 指定JOIN查询字段.
     *
     * @param array|string|Raw  $join  数据表
     * @param string|array|bool $field 查询字段
     * @param string       $on    JOIN条件
     * @param string       $type  JOIN类型
     * @param array        $bind  参数绑定
     *
     * @return $this
     */
    public function view(array | string | Raw $join, string | array | bool $field = true, ?string $on = null, string $type = 'INNER', array $bind = []): self
    {
        $this->options['view'] = true;

        $fields = [];
        $table  = $this->getJoinTable($join, $alias);

        // 处理字段
        $fields = $this->processFields($field, $alias);

        $this->field($fields);

        // 处理连接
        if ($on) {
            $this->join($table, $on, $type, $bind);
        } else {
            $this->table($table);
        }

        return $this;
    }

    protected function processFields(string | array | bool $field, string $alias): array
    {
        $fields = [];

        if (true === $field) {
            $fields[] = $alias . '.*'; // 选取所有字段
        } else {
            if (is_string($field)) {
                $field = explode(',', $field);
            }

            foreach ($field as $key => $val) {
                $name     = is_numeric($key) ? $alias . '.' . $val : (preg_match('/[,=\.\'\"\(\s]/', $key) ? $key : $alias . '.' . $key);
                $fields[] = $name . (is_numeric($key) ? '' : ' AS ' . $val);

                $this->options['map'][$val] = $name;
            }
        }

        return $fields;
    }

    /**
     * 视图查询处理.
     *
     * @param array $options 查询参数
     *
     * @return void
     */
    protected function parseView(array &$options): void
    {
        foreach (['AND', 'OR'] as $logic) {
            if (!isset($options['where'][$logic])) {
                continue;
            }

            // 视图查询条件处理
            foreach ($options['where'][$logic] as $key => $val) {
                if (array_key_exists($key, $options['map'])) {
                    array_shift($val);
                    array_unshift($val, $options['map'][$key]);
                    $options['where'][$logic][$options['map'][$key]] = $val;
                    unset($options['where'][$logic][$key]);
                }
            }
        }

        if (empty($options['order'])) {
            return;
        }
        
        // 视图查询排序处理
        foreach ($options['order'] as $key => $val) {
            if (is_numeric($key) && is_string($val)) {
                if (str_contains($val, ' ')) {
                    [$field, $sort] = explode(' ', $val);
                    if (array_key_exists($field, $options['map'])) {
                        $options['order'][$options['map'][$field]] = $sort;
                        unset($options['order'][$key]);
                    }
                } elseif (array_key_exists($val, $options['map'])) {
                    $options['order'][$options['map'][$val]] = 'asc';
                    unset($options['order'][$key]);
                }
            } elseif (array_key_exists($key, $options['map'])) {
                $options['order'][$options['map'][$key]] = $val;
                unset($options['order'][$key]);
            }
        }
    }
}
