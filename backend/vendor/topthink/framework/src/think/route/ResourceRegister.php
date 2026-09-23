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

namespace think\route;

/**
 * 资源路由注册类
 */
class ResourceRegister
{
    /**
     * 资源路由
     * @var Resource
     */
    protected $resource;

    /**
     * 是否注册过
     * @var bool
     */
    protected $registered = false;

    /**
     * 架构函数
     * @access public
     * @param  Resource   $resource     资源路由
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * 注册资源路由
     * @access protected
     * @return void
     */
    protected function register()
    {
        $this->registered = true;
        
        $this->resource->parseGroupRule($this->resource->getRule());
    }

    /**
     * 动态方法
     * @access public
     * @param string $method 方法名
     * @param array  $args   调用参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->resource, $method], $args);
    }

    public function __destruct()
    {
        if (!$this->registered) {
            $this->register();
        }
    }
}
