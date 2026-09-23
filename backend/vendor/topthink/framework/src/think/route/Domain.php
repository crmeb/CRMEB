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

use Closure;
use think\Route;
use think\Container;

/**
 * 域名路由
 */
class Domain extends RuleGroup
{
    /**
     * 架构函数
     * @access public
     * @param  Route       $router   路由对象
     * @param  string      $name     路由域名
     * @param  mixed       $rule     域名路由
     * @param  bool        $lazy   延迟解析
     */
    public function __construct(Route $router, ?string $name = null, $rule = null, bool $lazy = false)
    {
        $this->router = $router;
        $this->domain = $name;
        $this->rule   = $rule;

        if (!$lazy && !is_null($rule)) {
            $this->parseGroupRule($rule);
        }
    }

    /**
     * 解析分组和域名的路由规则及绑定
     * @access public
     * @param  mixed $rule 路由规则
     * @return void
     */
    public function parseGroupRule($rule): void
    {
        $origin = $this->router->getGroup();
        $this->router->setGroup($this);

        if ($rule instanceof Closure) {
            Container::getInstance()->invokeFunction($rule);
        } elseif ($this->config('route_auto_group')) {
            $this->loadGroupRoutes();
        }

        $this->router->setGroup($origin);
        $this->hasParsed = true;
    }

    /**
     * 自动加载分组（子目录）路由
     * @access protected
     * @param  string  $dir 目录名
     * @return void
     */
    protected function loadGroupRoutes(): void
    {
        $routePath = root_path('route');
        if (is_dir($routePath)) {
            $dirs = glob($routePath . '*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                // 自动检查分组子目录
                $groupName = str_replace('\\', '/', substr_replace($dir, '', 0, strlen($routePath)));
                if (!$this->router->getRuleName()->hasGroup($groupName)) {
                    $this->router->group($groupName);
                }
            }
        }
    }
}
