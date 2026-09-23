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
declare(strict_types=1);

namespace think\model;

use think\Model;

/**
 * 多对多中间表模型类.
 */
class Pivot extends Model
{
    /**
     * 父模型.
     *
     * @var Model
     */
    public $parent;
    protected $pivotName;

    /**
     * 是否时间自动写入.
     *
     * @var bool
     */
    protected $autoWriteTimestamp = false;

    /**
     * 架构函数.
     *
     * @param array      $data   数据
     * @param Model|null $parent 上级模型
     * @param string     $table  中间数据表名
     */
    public function __construct(array $data = [], ?Model $parent = null, string $table = '')
    {
        $this->pivotName   = $table;
        $this->parent      = $parent;
        parent::__construct($data);
    }

    /**
     *  初始化模型.
     *
     * @return void
     */
    protected function init() 
    {
        if (is_null($this->getOption('name'))) {
            $this->setOption('name', $this->pivotName);
        }
    }

    /**
     * 创建新的模型实例.
     *
     * @param array|object $data    数据
     * @param array        $options
     *
     * @return Model
     */
    public function newInstance(array | object $data = [], array $options = [])
    {
        $this->data($data);
        return $this->clone();
    }
}
