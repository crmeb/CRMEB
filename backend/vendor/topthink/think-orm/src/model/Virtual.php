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

namespace think\model;

use think\db\exception\DbException as Exception;
use think\Model;
use think\model\contract\Modelable;

/**
 * Class Virtual.
 * 虚拟模型
 */
abstract class Virtual extends Model
{
    /**
     * 创建数据.
     *
     * @param array|object  $data 数据
     * @param array  $allowField  允许字段
     * @param bool   $replace     使用Replace
     * @param string $suffix      数据表后缀
     * @return Modelable
     */
    public static function create(array | object $data, array $allowField = [], bool $replace = false, string $suffix = ''): Modelable
    {
        $model = new static();

        if (!empty($data)) {
            // 初始化模型数据
            $model->data($data);
        }
        return $model;
    }

    /**
     * 获取Db对象实例.
     * @return Query
     */
    public function getQuery()
    {
        throw new Exception('virtual model not support db query');
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
        $schema = array_merge($this->getOption('schema', []), $this->getOption('type', []));

        if ($field) {
            return $schema[$field] ?? null;
        }

        return $schema;
    }    
}
