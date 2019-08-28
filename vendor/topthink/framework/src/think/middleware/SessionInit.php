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
use think\Request;
use think\response\Redirect as RedirectResponse;
use think\Session;

/**
 * Session初始化
 */
class SessionInit
{

    /**
     * Session初始化
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param App     $app
     * @param Session $session
     * @return void
     */
    public function handle($request, Closure $next, App $app, Session $session)
    {
        // Session初始化
        $varSessionId = $app->config->get('session.var_session_id');
        $cookieName   = $app->config->get('session.name') ?: 'PHPSESSID';

        if ($varSessionId && $request->request($varSessionId)) {
            $sessionId = $request->request($varSessionId);
        } else {
            $sessionId = $request->cookie($cookieName) ?: '';
        }

        $session->setId($sessionId);

        $request->withSession($session);

        $response = $next($request)->setSession($session);

        $app->cookie->set($cookieName, $session->getId());

        // 清空当次请求有效的数据
        if (!($response instanceof RedirectResponse)) {
            $session->flush();
        }

        return $response;
    }
}
