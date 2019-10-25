<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\db\builder;

use think\db\Builder;
use think\db\Query;
use think\db\Raw;
use think\Exception;

/**
 * Sqlsrv数据库驱动
 */
class Sqlsrv extends Builder
{
    /**
     * SELECT SQL表达式
     * @var string
     */
    protected $selectSql = 'SELECT T1.* FROM (SELECT thinkphp.*, ROW_NUMBER() OVER (%ORDER%) AS ROW_NUMBER FROM (SELECT %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%) AS thinkphp) AS T1 %LIMIT%%COMMENT%';
    /**
     * SELECT INSERT SQL表达式
     * @var string
     */
    protected $selectInsertSql = 'SELECT %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%';

    /**
     * UPDATE SQL表达式
     * @var string
     */
    protected $updateSql = 'UPDATE %TABLE% SET %SET% FROM %TABLE% %JOIN% %WHERE% %LIMIT% %LOCK%%COMMENT%';

    /**
     * DELETE SQL表达式
     * @var string
     */
    protected $deleteSql = 'DELETE FROM %TABLE% %USING% FROM %TABLE% %JOIN% %WHERE% %LIMIT% %LOCK%%COMMENT%';

    /**
     * INSERT SQL表达式
     * @var string
     */
    protected $insertSql = 'INSERT INTO %TABLE% (%FIELD%) VALUES (%DATA%) %COMMENT%';

    /**
     * INSERT ALL SQL表达式
     * @var string
     */
    protected $insertAllSql = 'INSERT INTO %TABLE% (%FIELD%) %DATA% %COMMENT%';

    /**
     * order分析
     * @access protected
     * @param  Query     $query        查询对象
     * @param  mixed     $order
     * @return string
     */
    protected function parseOrder(Query $query, array $order): string
    {
        if (empty($order)) {
            return ' ORDER BY rand()';
        }

        $array = [];

        foreach ($order as $key => $val) {
            if ($val instanceof Raw) {
                $array[] = $val->getValue();
            } elseif ('[rand]' == $val) {
                $array[] = $this->parseRand($query);
            } else {
                if (is_numeric($key)) {
                    list($key, $sort) = explode(' ', strpos($val, ' ') ? $val : $val . ' ');
                } else {
                    $sort = $val;
                }

                $sort    = in_array(strtolower($sort), ['asc', 'desc'], true) ? ' ' . $sort : '';
                $array[] = $this->parseKey($query, $key, true) . $sort;
            }
        }

        return ' ORDER BY ' . implode(',', $array);
    }

    /**
     * 随机排序
     * @access protected
     * @param  Query     $query        查询对象
     * @return string
     */
    protected function parseRand(Query $query): string
    {
        return 'rand()';
    }

    /**
     * 字段和表名处理
     * @access public
     * @param  Query     $query     查询对象
     * @param  mixed     $key       字段名
     * @param  bool      $strict   严格检测
     * @return string
     */
    public function parseKey(Query $query, $key, bool $strict = false): string
    {
        if (is_int($key)) {
            return (string) $key;
        } elseif ($key instanceof Raw) {
            return $key->getValue();
        }

        $key = trim($key);

        if (strpos($key, '.') && !preg_match('/[,\'\"\(\)\[\s]/', $key)) {
            list($table, $key) = explode('.', $key, 2);

            $alias = $query->getOptions('alias');

            if ('__TABLE__' == $table) {
                $table = $query->getOptions('table');
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
            $key = '[' . $key . ']';
        }

        if (isset($table)) {
            $key = '[' . $table . '].' . $key;
        }

        return $key;
    }

    /**
     * limit
     * @access protected
     * @param  Query     $query        查询对象
     * @param  mixed     $limit
     * @return string
     */
    protected function parseLimit(Query $query, string $limit): string
    {
        if (empty($limit)) {
            return '';
        }

        $limit = explode(',', $limit);

        if (count($limit) > 1) {
            $limitStr = '(T1.ROW_NUMBER BETWEEN ' . $limit[0] . ' + 1 AND ' . $limit[0] . ' + ' . $limit[1] . ')';
        } else {
            $limitStr = '(T1.ROW_NUMBER BETWEEN 1 AND ' . $limit[0] . ")";
        }

        return 'WHERE ' . $limitStr;
    }

    public function selectInsert(Query $query, array $fields, string $table): string
    {
        $this->selectSql = $this->selectInsertSql;

        return parent::selectInsert($query, $fields, $table);
    }

}
