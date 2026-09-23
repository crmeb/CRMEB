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

namespace think\model\concern;

use think\db\BaseQuery as Query;
use think\facade\Db;

/**
 * 数据库连接.
 */
trait DbConnect
{
    /**
     * 设置Db对象实例.（用于兼容）
     */
    public static function setDb($db)
    {}

    /**
     * 获取Db对象实例.
     * @return Query
     */
    public function getQuery()
    {
        $db = $this->initDb()->newQuery($this->getOption('query'));

        if ($this->getOption('cache')) {
            [$key, $expire, $tag] = $this->getOption('cache');
            $db->cache($key, $expire, $tag);
        }

        return $db->schema($this->getOption('schema'))
            ->pk($this->getPk())
            ->suffix($this->getOption('suffix'))
            ->setKey($this->getKey())
            ->replace($this->getOption('replace', false))
            ->model($this);
    }

    /**
     * 初始化数据库连接对象.
     * @return Query
     */
    private function initDb()
    {
        $connection = $this->getOption('connection');
        if ($this->getOption('db')) {
            $db = $this->getOption('db')->connect($connection);
        } else {
            $db = Db::connect($connection);
        }

        $db = $db->name($this->getName());
        if ($this->getOption('table')) {
            $db->table($this->getOption('table'));
        } else {
            $db->suffix($this->getOption('suffix'));
        }

        return $db;
    }

    /**
     * 获取数据表字段类型列表（或某个字段的类型）.
     *
     * @param string|null $field 字段名
     *
     * @return array|string
     */
    protected function getFields(?string $field = null)
    {
        $schema = $this->getOption('schema');
        if (empty($schema)) {
            // 获取数据表信息
            $db     = $this->initDb();
            $fields = $db->getFieldsType();
            $schema = array_merge($fields, $this->getOption('type', []));
            // 获取主键
            if (!$this->getOption('pk')) {
                $this->setOption('pk', $db->getPk());
            }

            $this->setOption('schema', $schema);
        }

        if ($field) {
            return $schema[$field] ?? null;
        }

        return $schema;
    }

    /**
     * 新增数据是否使用Replace.
     *
     * @param bool $replace
     *
     * @return $this
     */
    public function replace(bool $replace = true)
    {
        return $this->setOption('replace', $replace);
    }

    /**
     * 获取当前模型的数据表后缀
     *
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->getOption('suffix', '');
    }

    /**
     * 设置当前模型数据表的后缀
     *
     * @param string $suffix 数据表后缀
     *
     * @return $this
     */
    public function setSuffix(string $suffix)
    {
        $this->setOption('suffix', $suffix);

        return $this;
    }

    /**
     * 构建实体模型查询.
     *
     * @param Query $query 查询对象
     * @return void
     */
    protected function query(Query $query) {}

    /**
     * 获取查询对象
     *
     * @param array|null $scope 设置不使用的全局查询范围
     * @return Query
     */
    public function db(array | null $scope = []): Query
    {
        $query = $this->getQuery();
        // 全局查询范围
        if (is_array($scope)) {
            $globalScope = array_diff($this->getOption('globalScope', []), $scope);
            $query->scope($globalScope);
        }
        // 执行扩展查询
        $this->query($query);
        return $query;
    }

    /**
     * 设置不使用的全局查询范围.
     *
     * @param array $scope 不启用的全局查询范围
     *
     * @return Query
     */
    public static function withoutGlobalScope(?array $scope = null): Query
    {
        $model = new static();

        return $model->db($scope);
    }

    public static function __callStatic($method, $args)
    {
        $model = new static();

        if ('suffix' == $method) {
            $model->setSuffix($args[0]);
        }

        $db    = $model->db();

        if (!empty(self::$weakMap[$model]['autoRelation'])) {
            // 自动获取关联数据
            $db->with(self::$weakMap[$model]['autoRelation']);
        }

        return call_user_func_array([$db, $method], $args);
    }

    public function __call($method, $args)
    {
        if ($this->isExists() && strtolower($method) == 'withattr') {
            return call_user_func_array([$this, 'withFieldAttr'], $args);
        }

        return call_user_func_array([$this->db(), $method], $args);
    }
}
