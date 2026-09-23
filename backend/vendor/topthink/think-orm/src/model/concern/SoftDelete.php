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

use Closure;
use think\db\BaseQuery as Query;
use think\Model;
use think\model\Collection;

/**
 * 数据软删除
 *
 * @mixin Model
 *
 * @method $this withTrashed()
 * @method $this onlyTrashed()
 */
trait SoftDelete
{
    /**
     * 获取查询对象
     *
     * @param array|null $scope 设置不使用的全局查询范围
     * @return Query
     */
    public function db(array | null $scope = []): Query
    {
        $query = parent::db($scope);
        $this->withNoTrashed($query);

        return $query;
    }

    /**
     * 判断当前实例是否被软删除.
     *
     * @return bool
     */
    public function trashed(): bool
    {
        $field = $this->getDeleteTimeField();

        if ($field && !empty($this->getOrigin($field))) {
            return true;
        }

        return false;
    }

    public function scopeWithTrashed(Query $query): void
    {
        $query->removeOption('soft_delete');
    }

    public function scopeOnlyTrashed(Query $query): void
    {
        $field = $this->getDeleteTimeField(true);

        if ($field) {
            $query->useSoftDelete($field, $this->getWithTrashedExp());
        }
    }

    /**
     * 获取软删除数据的查询条件.
     *
     * @return array
     */
    protected function getWithTrashedExp(): array
    {
        return is_null($this->getOption('defaultSoftDelete')) ? ['notnull', ''] : ['<>', $this->getOption('defaultSoftDelete')];
    }

    /**
     * 删除当前的记录.
     *
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->isEmpty() || false === $this->trigger('BeforeDelete')) {
            return false;
        }

        $name  = $this->getDeleteTimeField();
        $force = $this->isForce();

        if ($name && !$force) {
            foreach ($this->getData() as $key => $val) {
                if ($val instanceof Model || $val instanceof Collection) {
                    $relations[$key] = $val;
                }
            }
            // 软删除
            $this->exists()->withEvent(false)->save([$name => $this->getDateTime($name)]);

            $this->withEvent(true);
            if (!empty($relations)) {
                // 删除关联数据
                $this->relationDelete($relations);
            }
            $this->trigger('AfterDelete');
            $this->exists(false);
            $this->clear();
            return true;
        } 
        return parent::delete();
    }

    /**
     * 删除记录.
     *
     * @param mixed $data  主键列表 支持闭包查询条件
     * @param bool  $force 是否强制删除
     * @param array $together 关联删除
     *
     * @return bool
     */
    public static function destroy($data, bool $force = false, array $together = []): bool
    {
        // 传入空值（包括空字符串和空数组）的时候不会做任何的数据删除操作，但传入0则是有效的
        if (empty($data) && 0 !== $data) {
            return false;
        }
        $query = (new static())->db();

        if ($force) {
            $query->removeOption('soft_delete');
        }

        if (is_array($data) && key($data) !== 0) {
            $query->where($data);
            $data = [];
        } elseif ($data instanceof Closure) {
            $data($query);
            $data = [];
        }

        $resultSet = $query->select((array) $data);

        foreach ($resultSet as $result) {
            /** @var Model $result */
            $result->force($force)->together($together)->delete();
        }

        return true;
    }

    /**
     * 恢复被软删除的记录.
     *
     * @param array $where 更新条件
     *
     * @return bool
     */
    public function restore(array $where = []): bool
    {
        $name = $this->getDeleteTimeField();

        if (!$name || false === $this->trigger('BeforeRestore')) {
            return false;
        }

        $db = $this->getDbWhere($where);

        // 恢复删除
        $db->useSoftDelete($name, $this->getWithTrashedExp())
            ->update([$name => $this->getOption('defaultSoftDelete')]);

        $this->trigger('AfterRestore');

        return true;
    }

    /**
     * 获取软删除字段.
     *
     * @param bool $read 是否查询操作 写操作的时候会自动去掉表别名
     *
     * @return string|false
     */
    public function getDeleteTimeField(bool $read = false): bool | string
    {
        $field = $this->getOption('deleteTime', 'delete_time');

        if (false === $field) {
            return false;
        }

        if (!str_contains($field, '.')) {
            $field = '__TABLE__.' . $field;
        }

        if (!$read && str_contains($field, '.')) {
            $array = explode('.', $field);
            $field = array_pop($array);
        }

        return $field;
    }

    /**
     * 查询的时候默认排除软删除数据.
     *
     * @param Query $query
     *
     * @return void
     */
    protected function withNoTrashed(Query $query): void
    {
        $field = $this->getDeleteTimeField(true);

        if ($field) {
            $condition = is_null($this->getOption('defaultSoftDelete')) ? ['null', ''] : ['=', $this->getOption('defaultSoftDelete')];
            $query->useSoftDelete($field, $condition);
        }
    }
}
