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

/**
 * 多语言加载
 */
class LoadLangPack
{

    /**
     * 路由初始化（路由规则注册）
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param Lang    $lang
     * @param App     $app
     * @return Response
     */
    public function handle($request, Closure $next, Lang $lang, App $app)
    {
        // 自动侦测当前语言
        $langset = $lang->detect();

        if ($lang->defaultLangSet() != $langset) {
            // 加载系统语言包
            $lang->load([
                $app->getThinkPath() . 'lang' . DIRECTORY_SEPARATOR . $langset . '.php',
            ]);

            $app->LoadLangPack($langset);
        }

        $lang->saveToCookie($app->cookie);

        return $next($request);
    }
}
