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

namespace think\facade;

use think\Facade;

/**
 * @see \think\Db
 * @package think\facade
 * @mixin \think\Db
 * @method \think\db\Query connect(string $name = null, bool $force = false) static 连接/切换数据库连接
 * @method \think\db\Connection getConnection() static 获取数据库连接对象
 * @method \think\db\Query master() static 从主服务器读取数据
 * @method \think\db\Query table(string $table) static 指定数据表（含前缀）
 * @method \think\db\Query name(string $name) static 指定数据表（不含前缀）
 * @method \think\db\Raw raw(string $value) static 使用表达式设置数据
 * @method \think\db\Query where(mixed $field, string $op = null, mixed $condition = null) static 查询条件
 * @method \think\db\Query whereRaw(string $where, array $bind = []) static 表达式查询
 * @method \think\db\Query whereExp(string $field, string $condition, array $bind = []) static 字段表达式查询
 * @method \think\db\Query when(mixed $condition, mixed $query, mixed $otherwise = null) static 条件查询
 * @method \think\db\Query join(mixed $join, mixed $condition = null, string $type = 'INNER') static JOIN查询
 * @method \think\db\Query view(mixed $join, mixed $field = null, mixed $on = null, string $type = 'INNER') static 视图查询
 * @method \think\db\Query field(mixed $field, boolean $except = false) static 指定查询字段
 * @method \think\db\Query fieldRaw(string $field, array $bind = []) static 指定查询字段
 * @method \think\db\Query union(mixed $union, boolean $all = false) static UNION查询
 * @method \think\db\Query limit(mixed $offset, integer $length = null) static 查询LIMIT
 * @method \think\db\Query order(mixed $field, string $order = null) static 查询ORDER
 * @method \think\db\Query orderRaw(string $field, array $bind = []) static 查询ORDER
 * @method \think\db\Query cache(mixed $key = null , integer $expire = null) static 设置查询缓存
 * @method \think\db\Query withAttr(string $name, callable $callback = null) static 使用获取器获取数据
 * @method mixed value(string $field) static 获取某个字段的值
 * @method array column(string $field, string $key = '') static 获取某个列的值
 * @method mixed find(mixed $data = null) static 查询单个记录
 * @method mixed select(mixed $data = null) static 查询多个记录
 * @method integer save(array $data = [], bool $forceInsert = false) static 保存记录 自动判断insert或者update
 * @method integer insert(array $data, bool $getLastInsID = false, string $sequence = null) static 插入一条记录
 * @method integer insertGetId(array $data, string $sequence = null) static 插入一条记录并返回自增ID
 * @method integer insertAll(array $dataSet) static 插入多条记录
 * @method integer update(array $data) static 更新记录
 * @method integer delete(mixed $data = null) static 删除记录
 * @method boolean chunk(integer $count, callable $callback, string $column = null) static 分块获取数据
 * @method \Generator cursor(mixed $data = null) static 使用游标查找记录
 * @method mixed query(string $sql, array $bind = [], bool $master = false, bool $pdo = false) static SQL查询
 * @method integer execute(string $sql, array $bind = [], boolean $fetch = false, bool $getLastInsID = false, string $sequence = null) static SQL执行
 * @method \think\Paginator paginate(mixed $listRows = 15, mixed $simple = null) static 分页查询
 * @method \think\Paginator pageSelect(mixed $listRows = null, string $key = null, string $sort = null, mixed $lastId = null) static 分页查询（适用于大数据）
 * @method mixed transaction(callable $callback) static 执行数据库事务
 * @method void startTrans() static 启动事务
 * @method void commit() static 用于非自动提交状态下面的查询提交
 * @method void rollback() static 事务回滚
 * @method bool batchQuery(array $sqlArray) static 批处理执行SQL语句
 * @method mixed getLastInsID(string $sequence = null) static 获取最近插入的ID
 * @method mixed getConfig(string $name = '') static 获取数据库的配置参数
 */
class Db extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'db';
    }
}
