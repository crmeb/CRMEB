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

use think\db\Raw;
use think\helper\Str;

/**
 * JOIN和VIEW查询
 */
trait JoinAndViewQuery
{

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed  $join      关联的表名
     * @param mixed  $condition 条件
     * @param string $type      JOIN类型
     * @param array  $bind      参数绑定
     * @return $this
     */
    public function join($join, string $condition = null, string $type = 'INNER', array $bind = [])
    {
        $table = $this->getJoinTable($join);

        if (!empty($bind) && $condition) {
            $this->bindParams($condition, $bind);
        }

        $this->options['join'][] = [$table, strtoupper($type), $condition];

        return $this;
    }

    /**
     * LEFT JOIN
     * @access public
     * @param mixed $join      关联的表名
     * @param mixed $condition 条件
     * @param array $bind      参数绑定
     * @return $this
     */
    public function leftJoin($join, string $condition = null, array $bind = [])
    {
        return $this->join($join, $condition, 'LEFT', $bind);
    }

    /**
     * RIGHT JOIN
     * @access public
     * @param mixed $join      关联的表名
     * @param mixed $condition 条件
     * @param array $bind      参数绑定
     * @return $this
     */
    public function rightJoin($join, string $condition = null, array $bind = [])
    {
        return $this->join($join, $condition, 'RIGHT', $bind);
    }

    /**
     * FULL JOIN
     * @access public
     * @param mixed $join      关联的表名
     * @param mixed $condition 条件
     * @param array $bind      参数绑定
     * @return $this
     */
    public function fullJoin($join, string $condition = null, array $bind = [])
    {
        return $this->join($join, $condition, 'FULL');
    }

    /**
     * 获取Join表名及别名 支持
     * ['prefix_table或者子查询'=>'alias'] 'table alias'
     * @access protected
     * @param array|string|Raw $join  JION表名
     * @param string           $alias 别名
     * @return string|array
     */
    protected function getJoinTable($join, &$alias = null)
    {
        if (is_array($join)) {
            $table = $join;
            $alias = array_shift($join);
            return $table;
        } elseif ($join instanceof Raw) {
            return $join;
        }

        $join = trim($join);

        if (false !== strpos($join, '(')) {
            // 使用子查询
            $table = $join;
        } else {
            // 使用别名
            if (strpos($join, ' ')) {
                // 使用别名
                list($table, $alias) = explode(' ', $join);
            } else {
                $table = $join;
                if (false === strpos($join, '.')) {
                    $alias = $join;
                }
            }

            if ($this->prefix && false === strpos($table, '.') && 0 !== strpos($table, $this->prefix)) {
                $table = $this->getTable($table);
            }
        }

        if (!empty($alias) && $table != $alias) {
            $table = [$table => $alias];
        }

        return $table;
    }

    /**
     * 指定JOIN查询字段
     * @access public
     * @param string|array $join  数据表
     * @param string|array $field 查询字段
     * @param string       $on    JOIN条件
     * @param string       $type  JOIN类型
     * @param array        $bind  参数绑定
     * @return $this
     */
    public function view($join, $field = true, $on = null, string $type = 'INNER', array $bind = [])
    {
        $this->options['view'] = true;

        $fields = [];
        $table  = $this->getJoinTable($join, $alias);

        if (true === $field) {
            $fields = $alias . '.*';
        } else {
            if (is_string($field)) {
                $field = explode(',', $field);
            }

            foreach ($field as $key => $val) {
                if (is_numeric($key)) {
                    $fields[] = $alias . '.' . $val;

                    $this->options['map'][$val] = $alias . '.' . $val;
                } else {
                    if (preg_match('/[,=\.\'\"\(\s]/', $key)) {
                        $name = $key;
                    } else {
                        $name = $alias . '.' . $key;
                    }

                    $fields[] = $name . ' AS ' . $val;

                    $this->options['map'][$val] = $name;
                }
            }
        }

        $this->field($fields);

        if ($on) {
            $this->join($table, $on, $type, $bind);
        } else {
            $this->table($table);
        }

        return $this;
    }

    /**
     * 视图查询处理
     * @access protected
     * @param array $options 查询参数
     * @return void
     */
    protected function parseView(array &$options): void
    {
        foreach (['AND', 'OR'] as $logic) {
            if (isset($options['where'][$logic])) {
                foreach ($options['where'][$logic] as $key => $val) {
                    if (array_key_exists($key, $options['map'])) {
                        array_shift($val);
                        array_unshift($val, $options['map'][$key]);
                        $options['where'][$logic][$options['map'][$key]] = $val;
                        unset($options['where'][$logic][$key]);
                    }
                }
            }
        }

        if (isset($options['order'])) {
            // 视图查询排序处理
            foreach ($options['order'] as $key => $val) {
                if (is_numeric($key) && is_string($val)) {
                    if (strpos($val, ' ')) {
                        list($field, $sort) = explode(' ', $val);
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

}
