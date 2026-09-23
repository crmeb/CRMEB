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

namespace think\db;

use Closure;
use Psr\SimpleCache\CacheInterface;
use ReflectionFunction;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException as Exception;
use think\db\exception\ModelNotFoundException;
use think\helper\Str;
use think\Model;
use think\Paginator;

/**
 * 数据查询基础类.
 */
abstract class BaseQuery
{
    use concern\TimeFieldQuery;
    use concern\AggregateQuery;
    use concern\ModelRelationQuery;
    use concern\ParamsBind;
    use concern\ResultOperation;
    use concern\WhereQuery;

    /**
     * 当前数据库连接对象
     *
     * @var Connection
     */
    protected $connection;

    /**
     * 当前数据表名称（不含前缀）.
     *
     * @var string
     */
    protected $name = '';

    /**
     * 当前数据表主键.
     *
     * @var string|array
     */
    protected $pk;

    /**
     * 当前数据表自增主键.
     *
     * @var string
     */
    protected $autoinc;

    /**
     * 当前数据表前缀
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * 当前数据表后缀
     *
     * @var string
     */
    protected $suffix = '';

    /**
     * 当前查询参数.
     *
     * @var array
     */
    protected $options = [];

    /**
     * 架构函数.
     *
     * @param ConnectionInterface $connection 数据库连接对象
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->prefix     = $this->getConfig('prefix');
        $timeRule         = $this->getConfig('time_query_rule');
        if (!empty($timeRule)) {
            $this->timeRule($timeRule);
        }
    }

    /**
     * 利用__call方法实现一些特殊的Model方法.
     *
     * @param string $method 方法名称
     * @param array  $args   调用参数
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        if (strtolower(substr($method, 0, 5)) == 'getby') {
            // 根据某个字段获取记录
            $field = Str::snake(substr($method, 5));

            return $this->where($field, '=', $args[0])->find();
        }

        if (strtolower(substr($method, 0, 10)) == 'getfieldby') {
            // 根据某个字段获取记录的某个值
            $name = Str::snake(substr($method, 10));

            return $this->where($name, '=', $args[0])->value($args[1]);
        }

        if (strtolower(substr($method, 0, 7)) == 'whereor') {
            $name = Str::snake(substr($method, 7));
            array_unshift($args, $name);

            return call_user_func_array([$this, 'whereOr'], $args);
        }

        if (strtolower(substr($method, 0, 5)) == 'where') {
            $name = Str::snake(substr($method, 5));
            array_unshift($args, $name);

            return call_user_func_array([$this, 'where'], $args);
        }

        if ($this->model && method_exists($this->model, 'scope' . $method)) {
            // 动态调用命名范围
            array_unshift($args, $this);
            $this->options['scope'][$method] = [[$this->model, 'scope' . $method], $args];

            return $this;
        }

        throw new Exception('method not exist:' . static::class . '->' . $method);
    }

    /**
     * 创建一个新的查询对象
     *
     * @param string|null $class 查询对象类名
     * @return BaseQuery
     */
    public function newQuery(?string $class = null): BaseQuery
    {
        if (null === $class) {
            $query = new static($this->connection);
        } else {
            $query = new $class($this->connection);
        }

        if ($this->model) {
            $query->model($this->model);
        }

        if (isset($this->options['table'])) {
            $query->table($this->options['table']);
        } else {
            $query->name($this->name)->suffix($this->suffix);
        }

        if (!empty($this->options['json'])) {
            $query->json($this->options['json'], $this->options['json_assoc']);
        }

        if (isset($this->options['field_type'])) {
            $query->schema($this->options['field_type']);
        }

        if (isset($this->options['lazy_fields'])) {
            $query->lazyFields($this->options['lazy_fields']);
        }

        return $query;
    }

    /**
     * 获取当前的数据库Connection对象
     *
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * 指定当前数据表名（不含前缀）.
     *
     * @param string $name 不含前缀的数据表名字
     *
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * 指定当前数据表后缀.
     *
     * @param string $suffix 后缀
     *
     * @return $this
     */
    public function suffix(string $suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * 获取当前的数据表名称.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?: $this->model->getName();
    }

    /**
     * 设置主键值.
     *
     * @param mixed $key 主键值
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->options['key'] = $key;

        return $this;
    }

    /**
     * 获取主键值.
     * @param array $data 数据
     *
     * @return mixed
     */
    public function getKey(array $data = [])
    {
        if (!empty($data)) {
            $pk = $this->getPk();
            if (is_string($pk) && isset($data[$pk])) {
                $id = $data[$pk];
            } else {
                $id = null;
            }
            return $id;
        }

        return $this->getOption('key');
    }

    /**
     * 获取数据库的配置参数.
     *
     * @param string $name 参数名称
     *
     * @return mixed
     */
    public function getConfig(string $name = '')
    {
        return $this->connection->getConfig($name);
    }

    /**
     * 得到当前数据表名称.
     * @param bool $alias 是否返回数据表别名
     *
     * @return string
     */
    public function getTable(bool $alias = false)
    {
        if (isset($this->options['table'])) {
            $table = $this->options['table'];
            if ($alias && is_string($table) && !empty($this->options['alias'][$table])) {
                return $this->options['alias'][$table];
            }
            return $table;
        }

        return $this->prefix . Str::snake($this->name) . $this->suffix;
    }

    /**
     * 设置字段类型信息.
     *
     * @param string $field 字段
     * @param string $type 字段类型
     *
     * @return $this
     */
    public function setFieldType(string $field, string $type)
    {
        $this->options['field_type'][$field] = $type;

        return $this;
    }

    /**
     * 设置字段类型信息.
     *
     * @param array $schema 字段类型信息
     *
     * @return $this
     */
    public function schema(array $schema)
    {
        $this->options['field_type'] = $schema;

        return $this;
    }

    /**
     * 设置只读字段.
     *
     * @param array $fields 只读字段
     *
     * @return $this
     */
    public function readonly(array $fields)
    {
        $this->options['readonly_fields'] = $fields;

        return $this;
    }

    /**
     * 获取最近一次查询的sql语句.
     *
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->connection->getLastSql();
    }

    /**
     * 获取返回或者影响的记录数.
     *
     * @return int
     */
    public function getNumRows(): int
    {
        return $this->connection->getNumRows();
    }

    /**
     * 获取最近插入的ID.
     *
     * @param string|null $sequence 自增序列名
     *
     * @return mixed
     */
    public function getLastInsID(?string $sequence = null)
    {
        return $this->connection->getLastInsID($this, $sequence);
    }

    /**
     * 设置字段映射信息.
     *
     * @param array $map 字段映射信息
     *
     * @return $this
     */
    public function fieldMap(array $map)
    {
        $this->options['field_map'] = $map;

        return $this;
    }

    /**
     * 获取字段映射名
     *
     * @param string $name 字段名
     *
     * @return mixed
     */
    public function getFieldMap(string $name)
    {
        $map = $this->getOption('field_map', []);
        return $map[$name] ?? null;
    }

    /**
     * 得到某个字段的值
     *
     * @param string $field   字段名
     * @param mixed  $default 默认值
     * @param bool   $useModelAttr 是否使用模型获取器
     *
     * @return mixed
     */
    public function value(string $field, $default = null, bool $useModelAttr = false)
    {
        $result = $this->connection->value($this, $field, $default);

        $array[$field] = $result;
        if ($this->model && $useModelAttr) {
            // JSON数据处理
            if (!empty($this->options['json'])) {
                $this->jsonModelResult($array);
            }
            return $this->model->newInstance($array)->get($field);
        }

        if (!empty($this->options['json'])) {
            $this->jsonResult($array);
        }
        return $array[$field];
    }

    /**
     * 得到某个字段的值 并且经过模型的获取器处理
     *
     * @param string $field   字段名
     * @param mixed  $default 默认值
     *
     * @return mixed
     */
    public function valueWithAttr(string $field, $default = null)
    {
        return $this->value($field, $default, true);
    }

    /**
     * 得到某个列的数组.
     *
     * @param string|array $field 字段名 多个字段用逗号分隔
     * @param string       $key   索引
     * @param bool         $useModelAttr 是否使用模型获取器
     *
     * @return array
     */
    public function column(string | array $field, string $key = '', bool $useModelAttr = false): array
    {
        $result = $this->connection->column($this, $field, $key);
        return array_map(function ($item) use ($field, $useModelAttr) {
            if (is_array($item)) {
                if ($this->model && $useModelAttr) {
                    // JSON数据处理
                    if (!empty($this->options['json'])) {
                        $this->jsonModelResult($item);
                    }
                    return $this->model->newInstance($item)->toArray();
                }
                if (!empty($this->options['json'])) {
                    $this->jsonResult($item);
                }
                return $item;
            }

            if (is_array($field) && 1 === count($field)) {
                $field = current($field);
            }

            $array[$field] = $item;
            if ($this->model && $useModelAttr) {
                if (!empty($this->options['json'])) {
                    $this->jsonModelResult($array);
                }
                return $this->model->newInstance($array)->get($field);
            }
            if (!empty($this->options['json'])) {
                $this->jsonResult($array);
            }
            return $array[$field];
        }, $result);
    }

    /**
     * 得到某个列的数组 并且经过模型的获取器处理.
     *
     * @param string|array $field 字段名 多个字段用逗号分隔
     * @param string       $key   索引
     *
     * @return array
     */
    public function columnWithAttr(string | array $field, string $key = '')
    {
        return $this->column($field, $key, true);
    }

    /**
     * 查询SQL组装 union.
     *
     * @param string|array|Closure $union UNION
     * @param bool                 $all   是否适用UNION ALL
     *
     * @return $this
     */
    public function union(string | array | Closure $union, bool $all = false)
    {
        $this->options['union']['type'] = $all ? 'UNION ALL' : 'UNION';

        if (is_array($union)) {
            $this->options['union'] = array_merge($this->options['union'], $union);
        } else {
            $this->options['union'][] = $union;
        }

        return $this;
    }

    /**
     * 查询SQL组装 union all.
     *
     * @param mixed $union UNION数据
     *
     * @return $this
     */
    public function unionAll(string | array | Closure $union)
    {
        return $this->union($union, true);
    }

    /**
     * 指定查询字段.
     *
     * @param string|array|Raw|true $field 字段信息
     *
     * @return $this
     */
    public function field(string | array | Raw | bool $field)
    {
        if (empty($field)) {
            return $this;
        } elseif ($field instanceof Raw) {
            $this->options['field'][] = $field;

            return $this;
        }

        if (is_string($field)) {
            if (preg_match('/[\<\'\"\(]/', $field)) {
                return $this->fieldRaw($field);
            }

            $field = array_map('trim', explode(',', $field));
        }

        if (true === $field) {
            // 获取全部字段
            $fields = $this->getTableFields();
            $field  = $fields ?: ['*'];
        }

        if (isset($this->options['field'])) {
            $field = array_merge((array) $this->options['field'], $field);
        }

        $this->options['field'] = array_unique($field, SORT_REGULAR);

        return $this;
    }

    /**
     * 指定要排除的查询字段.
     *
     * @param array|string $field 要排除的字段
     *
     * @return $this
     */
    public function withoutField(array | string $field)
    {
        if (empty($field)) {
            return $this;
        }

        if (is_string($field)) {
            $field = array_map('trim', explode(',', $field));
        }

        // 字段排除
        $fields = $this->getTableFields();
        $field  = $fields ? array_diff($fields, $field) : $field;

        if (isset($this->options['field'])) {
            $field = array_merge((array) $this->options['field'], $field);
        }

        $this->options['field'] = array_unique($field, SORT_REGULAR);

        return $this;
    }

    /**
     * 指定其它数据表的查询字段.
     *
     * @param string|array|true  $field     字段信息
     * @param string $tableName 数据表名
     * @param string $prefix    字段前缀
     * @param string $alias     别名前缀
     *
     * @return $this
     */
    public function tableField(string | array | bool $field, string $tableName, string $prefix = '', string $alias = '')
    {
        if (empty($field)) {
            return $this;
        }

        if (is_string($field)) {
            $field = array_map('trim', explode(',', $field));
        }

        if (true === $field) {
            // 获取全部字段
            $fields = $this->getTableFields($tableName);
            $field  = $fields ?: ['*'];
        }

        // 添加统一的前缀
        $prefix = $prefix ?: $tableName;
        foreach ($field as $key => &$val) {
            if (strpos($val, '.')) {
                continue;
            }

            if (is_numeric($key) && $alias) {
                $field[$prefix . '.' . $val] = $alias . $val;
                unset($field[$key]);
            } elseif (is_numeric($key)) {
                $val = $prefix . '.' . $val;
            }
        }

        if (isset($this->options['field'])) {
            $field = array_merge((array) $this->options['field'], $field);
        }

        $this->options['field'] = array_unique($field, SORT_REGULAR);

        return $this;
    }

    /**
     * 设置数据.
     *
     * @param array $data 数据
     *
     * @return $this
     */
    public function data(array $data)
    {
        $this->options['data'] = $data;

        return $this;
    }

    /**
     * 指定查询数量.
     *
     * @param int      $offset 起始位置
     * @param int|null $length 查询数量
     *
     * @return $this
     */
    public function limit(int $offset, ?int $length = null)
    {
        $this->options['limit'] = $offset . ($length ? ',' . $length : '');

        return $this;
    }

    /**
     * 指定分页.
     *
     * @param int      $page     页数
     * @param int|null $listRows 每页数量
     *
     * @return $this
     */
    public function page(int $page, ?int $listRows = null)
    {
        $this->options['page'] = [$page, $listRows];

        return $this;
    }

    /**
     * 指定当前操作的数据表.
     *
     * @param string|array|Raw $table 表名
     *
     * @return $this
     */
    public function table(string | array | Raw $table)
    {
        if (is_string($table) && !str_contains($table, ')')) {
            $table = $this->tableStr($table);
        } elseif (is_array($table)) {
            $table = $this->tableArr($table);
        }

        $this->options['table'] = $table;
        return $this;
    }

    /**
     * 指定数据表（字符串）.
     *
     * @param string $table 表名
     *
     * @return array|string
     */
    protected function tableStr(string $table): array | string
    {
        if (!str_contains($table, ',')) {
            // 单表
            if (str_contains($table, ' ')) {
                [$item, $alias] = explode(' ', $table);
                $table          = [];
                $this->alias([$item => $alias]);
                $table[$item] = $alias;
            }
        } else {
            // 多表
            $tables = explode(',', $table);
            $table  = [];

            foreach ($tables as $item) {
                $item = trim($item);
                if (str_contains($item, ' ')) {
                    [$item, $alias] = explode(' ', $item);
                    $this->alias([$item => $alias]);
                    $table[$item] = $alias;
                } else {
                    $table[] = $item;
                }
            }
        }
        return $table;
    }

    /**
     * 指定多个数据表（数组格式）.
     *
     * @param array $tables 表名列表
     *
     * @return array
     */
    protected function tableArr(array $tables): array
    {
        $table = [];
        foreach ($tables as $key => $val) {
            if (is_numeric($key)) {
                $table[] = $val;
            } else {
                $this->alias([$key => $val]);
                $table[$key] = $val;
            }
        }

        return $table;
    }

    /**
     * 指定排序 order('id','desc') 或者 order(['id'=>'desc','create_time'=>'desc']).
     *
     * @param string|array|Raw $field 排序字段
     * @param string           $order 排序
     *
     * @return $this
     */
    public function order(string | array | Raw $field, string $order = '')
    {
        if (empty($field)) {
            return $this;
        } elseif ($field instanceof Raw) {
            $this->options['order'][] = $field;
            return $this;
        }

        if (is_string($field)) {
            $field = $this->parseOrderField($this->getFieldMap($field) ?: $field);
            if (is_string($field) && strpos($field, '->')) {
                [$alias, $attr] = explode('->', $field, 2);

                $type = $this->getFieldType($alias);
                if (is_null($type)) {
                    $this->hasWhere($alias);
                    $field = $alias . '.' . $attr;
                }
            }

            if (str_contains($field, ',')) {
                $field = array_map('trim', explode(',', $field));
            } else {
                $field = empty($order) ? $field : [$field => $order];
            }
        } elseif (!empty($this->options['via'])) {
            foreach ($field as $key => $val) {
                if (is_numeric($key)) {
                    $field[$key] = $this->parseOrderField($val);
                } else {
                    $key1 = $this->parseOrderField($key);
                    if ($key != $key1) {
                        $field[$key1] = $val;
                        unset($field[$key]);
                    }
                }
            }
        }

        if (!isset($this->options['order'])) {
            $this->options['order'] = [];
        }

        if (is_array($field)) {
            $this->options['order'] = array_merge($this->options['order'], $field);
        } else {
            $this->options['order'][] = $field;
        }

        return $this;
    }

    protected function parseOrderField(string $field): string
    {
        if (!empty($this->options['via']) && !str_contains($field, '.') && !str_contains($field, '->')) {
            $field = $this->options['via'] . '.' . $field;
        }
        return $field;        
    }
    /**
     * 分页查询.
     *
     * @param int|array|null $listRows 每页数量 数组表示配置参数
     * @param int|bool       $simple   是否简洁模式或者总记录数
     *
     * @return Paginator
     *
     * @throws Exception
     */
    public function paginate(int | array | null $listRows = null, int | bool $simple = false): Paginator
    {
        if (is_int($simple)) {
            $total  = $simple;
            $simple = false;
        }

        $defaultConfig = [
            'query' => [], //url额外参数
            'fragment' => '', //url锚点
            'var_page' => 'page', //分页变量
            'list_rows' => 15, //每页数量
        ];

        if (is_array($listRows)) {
            $config   = array_merge($defaultConfig, $listRows);
            $listRows = intval($config['list_rows']);
        } else {
            $config   = $defaultConfig;
            $listRows = intval($listRows ?: $config['list_rows']);
        }

        $page           = isset($config['page']) ? (int) $config['page'] : Paginator::getCurrentPage($config['var_page']);
        $page           = max($page, 1);
        $config['path'] = $config['path'] ?? Paginator::getCurrentPath();

        if (!isset($total) && !$simple) {
            $options = $this->getOptions();

            unset($this->options['order'], $this->options['cache'], $this->options['limit'], $this->options['page'], $this->options['field']);

            $bind  = $this->bind;
            $total = $this->count();
            if ($total > 0) {
                $results = $this->options($options)->bind($bind)->page($page, $listRows)->select();
            } else {
                if (!empty($this->model)) {
                    $results = new \think\model\Collection([]);
                } else {
                    $results = new \think\Collection([]);
                }
            }
        } elseif ($simple) {
            $results = $this->limit(($page - 1) * $listRows, $listRows + 1)->select();
            $total   = null;
        } else {
            $results = $this->page($page, $listRows)->select();
        }

        $this->removeOption('limit');
        $this->removeOption('page');

        return Paginator::make($results, $listRows, $page, $total, $simple, $config);
    }

    /**
     * 根据数字类型字段进行分页查询（大数据）.
     *
     * @param int|array|null $listRows 每页数量或者分页配置
     * @param string|null    $key      分页索引键
     * @param string|null    $sort     索引键排序 asc|desc
     *
     * @return Paginator
     *
     * @throws Exception
     */
    public function paginateX(int | array | null $listRows = null, ?string $key = null, ?string $sort = null): Paginator
    {
        $defaultConfig = [
            'query' => [], //url额外参数
            'fragment' => '', //url锚点
            'var_page' => 'page', //分页变量
            'list_rows' => 15, //每页数量
        ];

        $config   = is_array($listRows) ? array_merge($defaultConfig, $listRows) : $defaultConfig;
        $listRows = is_int($listRows) ? $listRows : (int) $config['list_rows'];
        $page     = isset($config['page']) ? (int) $config['page'] : Paginator::getCurrentPage($config['var_page']);
        $page     = max($page, 1);

        $config['path'] = $config['path'] ?? Paginator::getCurrentPath();

        $key     = $key ?: $this->getPk();
        $options = $this->getOptions();

        if (is_null($sort)) {
            $order = $options['order'] ?? '';
            if (!empty($order)) {
                $sort = $order[$key] ?? 'desc';
            } else {
                $this->order($key, 'desc');
                $sort = 'desc';
            }
        } else {
            $this->order($key, $sort);
        }

        $newOption = $options;
        unset($newOption['field'], $newOption['page']);

        $data = $this->newQuery()
            ->options($newOption)
            ->field($key)
            ->where(true)
            ->order($key, $sort)
            ->limit(1)
            ->find();

        $result = $data[$key] ?? 0;

        if (is_numeric($result)) {
            $lastId = 'asc' == $sort ? ($result - 1) + ($page - 1) * $listRows : ($result + 1) - ($page - 1) * $listRows;
        } else {
            throw new Exception('not support type');
        }

        $results = $this->when($lastId, function ($query) use ($key, $sort, $lastId) {
            $query->where($key, 'asc' == $sort ? '>' : '<', $lastId);
        })
            ->limit($listRows)
            ->select();

        $this->options($options);

        return Paginator::make($results, $listRows, $page, null, true, $config);
    }

    /**
     * 根据最后ID查询更多N个数据.
     *
     * @param int             $limit  数量
     * @param int|string|null $lastId 最后ID
     * @param string|null     $key    分页索引键 默认为主键
     * @param string|null     $sort   索引键排序 asc|desc
     *
     * @return array
     *
     * @throws Exception
     */
    public function more(int $limit, int | string | null $lastId = null, ?string $key = null, ?string $sort = null): array
    {
        $key = $key ?: $this->getPk();

        if (is_null($sort)) {
            $order = $this->getOption('order');
            if (!empty($order)) {
                $sort = $order[$key] ?? 'desc';
            } else {
                $this->order($key, 'desc');
                $sort = 'desc';
            }
        } else {
            $this->order($key, $sort);
        }

        $result = $this->when($lastId, function ($query) use ($key, $sort, $lastId) {
            $query->where($key, 'asc' == $sort ? '>' : '<', $lastId);
        })->limit($limit)->select();

        $last = $result->last();

        $result->first();

        return [
            'data'   => $result,
            'lastId' => $last ? $last[$key] : null,
        ];
    }

    /**
     * 获取当前的缓存对象
     *
     * @return CacheInterface|null
     */
    public function getCache()
    {
        return $this->getConnection()->getCache();
    }

    /**
     * 查询缓存 数据为空不缓存.
     *
     * @param mixed         $key    缓存key
     * @param int|\DateTime $expire 缓存有效期
     * @param string|array  $tag    缓存标签
     *
     * @return $this
     */
    public function cache($key = true, $expire = null, $tag = null)
    {
        if (false === $key || !$this->getCache()) {
            return $this;
        }

        if ($key instanceof \DateTimeInterface  || $key instanceof \DateInterval  || (is_int($key) && is_null($expire))) {
            $expire = $key;
            $key    = true;
        }

        $this->options['cache'] = [$key, $expire, $tag ?: $this->getTable()];
        return $this;
    }

    /**
     * 查询缓存 允许缓存空数据.
     *
     * @param mixed         $key    缓存key
     * @param int|\DateTime $expire 缓存有效期
     * @param string|array  $tag    缓存标签
     *
     * @return $this
     */
    public function cacheAlways($key = true, $expire = null, $tag = null)
    {
        $this->options['cache_always'] = true;
        return $this->cache($key, $expire, $tag);
    }

    /**
     * 强制更新缓存
     *
     * @param mixed         $key    缓存key
     * @param int|\DateTime $expire 缓存有效期
     * @param string|array  $tag    缓存标签
     *
     * @return $this
     */
    public function cacheForce($key = true, $expire = null, $tag = null)
    {
        $this->options['force_cache'] = true;

        return $this->cache($key, $expire, $tag);
    }

    /**
     * 指定查询lock.
     *
     * @param bool|string $lock 是否lock
     *
     * @return $this
     */
    public function lock(bool | string $lock = false)
    {
        $this->options['lock'] = $lock;

        if ($lock) {
            $this->options['master'] = true;
        }

        return $this;
    }

    /**
     * 指定数据表别名.
     *
     * @param array|string $alias 数据表别名
     *
     * @return $this
     */
    public function alias(array | string $alias)
    {
        if (is_array($alias)) {
            $this->options['alias'] = $alias;
        } else {
            $table = $this->getTable();

            $this->options['alias'][$table] = $alias;
        }

        return $this;
    }

    /**
     * 获取数据表别名.
     *
     * @param string $table 数据表（留空取当前表）
     *
     * @return string
     */
    public function getAlias(string $table = '')
    {
        if ('' === $table) {
            $table = $this->getTable();
        }

        return $this->options['alias'][$table] ?? '';
    }

    /**
     * 设置从主服务器读取数据.
     *
     * @param bool $readMaster 是否从主服务器读取
     *
     * @return $this
     */
    public function master(bool $readMaster = true)
    {
        $this->options['master'] = $readMaster;

        return $this;
    }

    /**
     * 设置是否严格检查字段名.
     *
     * @param bool $strict 是否严格检查字段
     *
     * @return $this
     */
    public function strict(bool $strict = true)
    {
        $this->options['strict'] = $strict;

        return $this;
    }

    /**
     * 设置自增序列名.
     *
     * @param string|null $sequence 自增序列名
     *
     * @return $this
     */
    public function sequence(?string $sequence = null)
    {
        $this->options['sequence'] = $sequence;

        return $this;
    }

    /**
     * 设置JSON字段信息.
     *
     * @param array $json  JSON字段
     * @param bool  $assoc 是否取出数组
     *
     * @return $this
     */
    public function json(array $json = [], bool $assoc = false)
    {
        $this->options['json']       = $json;
        $this->options['json_assoc'] = $assoc;

        return $this;
    }

    /**
     * 设置延迟写入字段 用于实时获取缓存数据
     *
     * @param array $fields 延迟写入字段
     *
     * @return $this
     */
    public function lazyFields(array $fields)
    {
        $this->options['lazy_fields'] = $fields;

        return $this;
    }

    /**
     * 指定数据表主键.
     *
     * @param string|array|bool|null $pk 主键
     *
     * @return $this
     */
    public function pk(string | array | bool | null $pk)
    {
        $this->pk = $pk;

        return $this;
    }

    /**
     * 查询参数批量赋值
     *
     * @param array $options 表达式参数
     *
     * @return $this
     */
    protected function options(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * 获取所有查询参数.
     *
     * @return mixed
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * 获取查询参数.
     *
     * @param string $name 参数名
     * @param mixed  $default 默认值
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * 设置当前的查询参数.
     *
     * @param string $option 参数名
     * @param mixed  $value  参数值
     *
     * @return $this
     */
    public function setOption(string $option, $value)
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * 去除查询参数.
     *
     * @param string $option 参数名 留空去除所有参数
     *
     * @return $this
     */
    public function removeOption(string $option = '')
    {
        if ('' === $option) {
            $this->options = [];
            $this->bind    = [];
        } elseif (isset($this->options[$option])) {
            unset($this->options[$option]);
        }

        return $this;
    }

    /**
     * 设置当前字段添加的表别名.
     *
     * @param string $via 临时表别名
     *
     * @return $this
     */
    public function via(string $via = '')
    {
        $this->options['via'] = $via;

        return $this;
    }

    /**
     * 保存记录 自动判断insert或者update.
     *
     * @param array $data        数据
     * @param bool  $forceInsert 是否强制insert
     *
     * @return int
     */
    public function save(array $data = [], bool $forceInsert = false)
    {
        if ($forceInsert) {
            return $this->insert($data);
        }

        $this->options['data'] = array_merge($this->options['data'] ?? [], $data);

        if (!empty($this->options['where'])) {
            $isUpdate = true;
        } else {
            $isUpdate = $this->parseUpdateData($this->options['data']);
        }

        return $isUpdate ? $this->update() : $this->insert();
    }

    /**
     * 插入记录.
     *
     * @param array $data         数据
     * @param bool  $getLastInsID 返回自增主键
     *
     * @return int|string
     */
    public function insert(array $data = [], bool $getLastInsID = false)
    {
        if (!empty($data)) {
            $this->options['data'] = $data;
        }

        return $this->connection->insert($this, $getLastInsID);
    }

    /**
     * 插入记录并获取自增ID.
     *
     * @param array $data 数据
     *
     * @return int|string
     */
    public function insertGetId(array $data)
    {
        return $this->insert($data, true);
    }

    /**
     * 批量插入记录.
     *
     * @param array $dataSet 数据集
     * @param int   $limit   每次写入数据限制
     *
     * @return int
     */
    public function insertAll(array $dataSet = [], int $limit = 0): int
    {
        if (empty($dataSet)) {
            $dataSet = $this->options['data'] ?? [];
        }

        if ($limit) {
            $this->limit($limit);
        }

        return $this->connection->insertAll($this, $dataSet);
    }

    /**
     * 批量插入记录
     * @access public
     * @param array   $keys 键值
     * @param array   $values 数据
     * @param integer $limit   每次写入数据限制
     * @return integer
     */
    public function insertAllByKeys(array $keys, array $values, int $limit = 0): int
    {
        if ($limit) {
            $this->limit($limit);
        }

        return $this->connection->insertAllByKeys($this, $keys, $values);
    }

    /**
     * 通过Select方式插入记录.
     *
     * @param array  $fields 要插入的数据表字段名
     * @param string $table  要插入的数据表名
     *
     * @return int
     */
    public function selectInsert(array $fields, string $table): int
    {
        return $this->connection->selectInsert($this, $fields, $table);
    }

    /**
     * 更新记录.
     *
     * @param array $data 数据
     *
     * @throws Exception
     *
     * @return int
     */
    public function update(array $data = []): int
    {
        if (!empty($data)) {
            $this->options['data'] = array_merge($this->options['data'] ?? [], $data);
        }

        if (empty($this->options['where'])) {
            $this->parseUpdateData($this->options['data']);
        }

        if (empty($this->options['where']) && empty($this->options['scope'])) {
            // 如果没有任何更新条件则不执行
            throw new Exception('miss update condition');
        }

        // 检查只读字段
        if (!empty($this->options['readonly_fields'])) {
            foreach ($this->options['readonly_fields'] as $field) {
                if (array_key_exists($field, $this->options['data'])) {
                    unset($this->options['data'][$field]);
                }
            }
        }

        return $this->connection->update($this);
    }

    /**
     * 删除记录.
     *
     * @param mixed $data 表达式 true 表示强制删除
     *
     * @throws Exception
     *
     * @return int
     */
    public function delete($data = null): int
    {
        if (!is_null($data) && true !== $data) {
            // AR模式分析主键条件
            $this->parsePkWhere($data);
        }

        if (empty($this->options['where']) && !empty($this->options['key'])) {
            if (is_array($this->pk)) {
                $this->where($this->options['key']);
            } else {
                $this->where($this->pk, '=', $this->options['key']);
            }
        }

        if (true !== $data && empty($this->options['where']) && empty($this->options['scope'])) {
            // 如果条件为空 不进行删除操作 除非设置 1=1
            throw new Exception('delete without condition');
        }

        if (!empty($this->options['soft_delete'])) {
            // 软删除
            list($field, $condition) = $this->options['soft_delete'];
            if ($condition) {
                unset($this->options['soft_delete']);
                $this->options['data'] = [$field => $condition];

                return $this->connection->update($this);
            }
        }

        $this->options['data'] = $data;

        return $this->connection->delete($this);
    }

    /**
     * 查找记录.
     *
     * @param array $data 主键数据
     *
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     *
     * @return Collection<static>
     */
    public function select(array $data = []): Collection
    {
        if (!empty($data)) {
            // 主键条件分析
            $this->parsePkWhere($data);
        }

        $resultSet = $this->connection->select($this);

        // 返回结果处理
        if (!empty($this->options['fail']) && count($resultSet) == 0) {
            $this->throwNotFound();
        }

        // 数据列表读取后的处理
        if (!empty($this->model)) {
            // 生成模型对象
            $resultSet = $this->resultSetToModelCollection($resultSet);
        } else {
            $this->resultSet($resultSet);
        }

        return $resultSet;
    }

    /**
     * 查找单条记录.
     *
     * @param mixed   $data 主键数据
     * @param ?Closure $closure 闭包数据
     *
     * @throws Exception
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     *
     * @return mixed|static
     */
    public function find($data = null, ?Closure $closure = null)
    {
        if ($data instanceof Closure) {
            $closure = $data;
        } elseif (!is_null($data)) {
            // AR模式分析主键条件
            $this->parsePkWhere($data);
        }

        if (empty($this->options['where']) && empty($this->options['scope']) && empty($this->options['order']) && empty($this->options['sort'])) {
            $result = [];
        } else {
            $result = $this->connection->find($this);
        }

        // 数据处理
        if (empty($result)) {
            return $this->resultToEmpty($closure);
        }

        if (!empty($this->model)) {
            // 返回模型对象
            $this->resultToModel($result);
        } else {
            $this->result($result);
        }

        return $result;
    }

    /**
     * 分析表达式（可用于查询或者写入操作）.
     *
     * @return array
     */
    public function parseOptions(): array
    {
        // 执行全局查询范围
        $this->scopeQuery();

        $options = $this->getOptions();

        // 获取数据表
        if (empty($options['table'])) {
            $options['table'] = $this->getTable();
        }

        if (!isset($options['where'])) {
            $options['where'] = [];
        } elseif (isset($options['view'])) {
            // 视图查询条件处理
            $this->parseView($options);
        }

        foreach (['data', 'order', 'join', 'union', 'filter', 'json', 'with_attr', 'with_relation_attr'] as $name) {
            if (!isset($options[$name])) {
                $options[$name] = [];
            }
        }

        if (!isset($options['strict'])) {
            $options['strict'] = $this->getConfig('fields_strict');
        }

        foreach (['master', 'lock', 'fetch_sql', 'array', 'distinct', 'procedure', 'with_cache'] as $name) {
            if (!isset($options[$name])) {
                $options[$name] = false;
            }
        }

        foreach (['group', 'having', 'limit', 'force', 'comment', 'partition', 'duplicate', 'extra'] as $name) {
            if (!isset($options[$name])) {
                $options[$name] = '';
            }
        }

        if (isset($options['page'])) {
            // 根据页数计算limit
            [$page, $listRows] = $options['page'];

            $page     = $page > 0 ? $page : 1;
            $listRows = $listRows ?: (is_numeric($options['limit']) ? $options['limit'] : 20);
            $offset   = $listRows * ($page - 1);

            $options['limit'] = $offset . ',' . $listRows;
        }

        $this->options = $options;

        return $options;
    }

    /**
     * 分析数据是否存在更新条件.
     *
     * @param array $data 数据
     *
     * @throws Exception
     *
     * @return bool
     */
    public function parseUpdateData(array &$data): bool
    {
        $pk       = $this->getPk();
        $isUpdate = false;
        // 如果存在主键数据 则自动作为更新条件
        if (!empty($this->options['key'])) {
            if (is_array($pk)) {
                $this->where($this->options['key']);
            } else {
                $this->where($pk, '=', $this->options['key']);
            }
            $isUpdate = true;
        } elseif (is_string($pk) && isset($data[$pk])) {
            $this->where($pk, '=', $data[$pk]);
            $this->options['key'] = $data[$pk];
            unset($data[$pk]);
            $isUpdate = true;
        } elseif (is_array($pk)) {
            foreach ($pk as $field) {
                if (isset($data[$field])) {
                    $this->where($field, '=', $data[$field]);
                    $isUpdate = true;
                } else {
                    // 如果缺少复合主键数据则不执行
                    throw new Exception('miss complex primary data');
                }
                unset($data[$field]);
            }
        }

        return $isUpdate;
    }

    /**
     * 把主键值转换为查询条件 支持复合主键.
     *
     * @param mixed $data 主键数据
     *
     * @throws Exception
     *
     * @return void
     */
    public function parsePkWhere($data): void
    {
        $pk = $this->getPk();

        if (!is_string($pk)) {
            return;
        }

        // 获取数据表
        if (empty($this->options['table'])) {
            $this->options['table'] = $this->getTable();
        }

        $table = is_array($this->options['table']) ? key($this->options['table']) : $this->options['table'];

        if (!empty($this->options['alias'][$table])) {
            $alias = $this->options['alias'][$table];
        }

        $key = isset($alias) ? $alias . '.' . $pk : $pk;
        // 根据主键查询
        if (is_array($data)) {
            $this->where($key, 'in', $data);
        } else {
            $this->where($key, '=', $data);
            $this->options['key'] = $data;
        }
    }

    /**
     * 获取当前的查询标识.
     *
     * @return string
     */
    public function getQueryGuid(): string
    {
        $data  = $this->options;
        unset($data['scope'], $data['default_model']);
        $data['database'] = $this->getConfig('database');
        $data['table']    = $this->getTable();
        foreach (['AND', 'OR', 'XOR'] as $logic) {
            if (isset($data['where'][$logic])) {
                foreach ($data['where'][$logic] as $key => $val) {
                    if ($val instanceof Closure) {
                        $reflection = new ReflectionFunction($val);
                        $properties = $reflection->getStaticVariables();
                        if (empty($properties)) {
                            $name = $reflection->getName() . $reflection->getStartLine() . '-' . $reflection->getEndLine();
                        } else {
                            $name = var_export($properties, true);
                        }
                        $data['Closure'][] = $name;
                        unset($data['where'][$logic][$key]);
                    }
                }
            }
        }

        return md5(serialize(var_export($data, true)) . serialize($this->getBind(false)));
    }
}
