<?php
namespace Qiniu\Http\Middleware;

use Qiniu\Http\Request;
use Qiniu\Http\Response;

interface Middleware
{
    /**
     * @param Request $request
     * @param callable(Request): Response $next
     * @return Response
     */
    public function send($request, $next);
}

/**
 * @param array<Middleware> $middlewares
 * @param callable(Request): Response $handler
 * @return callable(Request): Response
 */
function compose($middlewares, $handler)
{
    $next = $handler;
    foreach (array_reverse($middlewares) as $middleware) {
        $next = function ($request) use ($middleware, $next) {
            return $middleware->send($request, $next);
        };
    }
    return $next;
}
