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
declare (strict_types = 1);

namespace think\middleware;

use Closure;
use think\App;
use think\Lang;
use think\Request;
use think\Response;

/**
 * 多语言加载
 */
class LoadLangPack
{
    protected $app;

    protected $lang;

    public function __construct(App $app, Lang $lang)
    {
        $this->app  = $app;
        $this->lang = $lang;
    }

    /**
     * 路由初始化（路由规则注册）
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        // 自动侦测当前语言
        $langset = $this->lang->detect($request);

        if ($this->lang->defaultLangSet() != $langset) {
            // 加载系统语言包
            $this->lang->load([
                $this->app->getThinkPath() . 'lang' . DIRECTORY_SEPARATOR . $langset . '.php',
            ]);

            $this->app->LoadLangPack($langset);
        }

        $this->lang->saveToCookie($this->app->cookie);

        return $next($request);
    }
}
