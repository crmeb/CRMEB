<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\db\builder;

use think\db\Builder;
use think\db\exception\DbException as Exception;
use think\db\BaseQuery as Query;
use think\db\Raw;

/**
 * Oracle数据库驱动.
 */
class Oracle extends Builder
{
    protected $selectSql = 'SELECT * FROM (SELECT thinkphp.*, rownum AS numrow FROM (SELECT  %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%) thinkphp ) %LIMIT%%COMMENT%';

    /**
     * limit分析.
     *
     * @param Query $query 查询对象
     * @param mixed $limit
     *
     * @return string
     */
    protected function parseLimit(Query $query, string $limit): string
    {
        $limitStr = '';

        if (!empty($limit)) {
            $limit = explode(',', $limit);

            if (count($limit) > 1) {
                $limitStr = '(numrow>' . $limit[0] . ') AND (numrow<=' . ($limit[0] + $limit[1]) . ')';
            } else {
                $limitStr = '(numrow>0 AND numrow<=' . $limit[0] . ')';
            }
        }

        return $limitStr ? ' WHERE ' . $limitStr : '';
    }

    /**
     * 设置锁机制.
     *
     * @param Query      $query 查询对象
     * @param bool|string $lock
     *
     * @return string
     */
    protected function parseLock(Query $query, bool|string $lock = false): string
    {
        if (!$lock) {
            return '';
        }

        return ' FOR UPDATE NOWAIT ';
    }

    /**
     * 字段和表名处理.
     *
     * @param Query  $query  查询对象
     * @param string|int|Raw $key
     * @param bool   $strict
     *
     * @throws Exception
     *
     * @return string
     */
    public function parseKey(Query $query, string|int|Raw $key, bool $strict = false): string
    {
        if (is_int($key)) {
            return (string) $key;
        } elseif ($key instanceof Raw) {
            return $this->parseRaw($query, $key);
        }

        $key = trim($key);

        if (str_contains($key, '->') && !str_contains($key, '(')) {
            // JSON字段支持
            [$field, $name] = explode($key, '->');
            $key = $field . '."' . $name . '"';
        } elseif (str_contains($key, '.') && !preg_match('/[,\'\"\(\)\[\s]/', $key)) {
            [$table, $key] = explode('.', $key, 2);

            $alias = $query->getOption('alias');

            if ('__TABLE__' == $table) {
                $table = $query->getOption('table');
                $table = is_array($table) ? array_shift($table) : $table;
            }

            if (isset($alias[$table])) {
                $table = $alias[$table];
            }
        }

        if ($strict && !preg_match('/^[\w\.\*]+$/', $key)) {
            throw new Exception('not support data:' . $key);
        }

        if ('*' != $key && !preg_match('/[,\'\"\*\(\)\[.\s]/', $key)) {
            $key = '"' . $key . '"';
        }

        if (isset($table)) {
            $key = '"' . $table . '".' . $key;
        }

        return $key;
    }

    /**
     * 随机排序.
     *
     * @param Query $query 查询对象
     *
     * @return string
     */
    protected function parseRand(Query $query): string
    {
        return 'DBMS_RANDOM.value';
    }
}
