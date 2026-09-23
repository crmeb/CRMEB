<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\adminapi\middleware;

use think\Request;
use app\services\system\admin\SystemRoleServices;
use crmeb\exceptions\AuthException;
use crmeb\interfaces\MiddlewareInterface;

/**
 * 权限规则验证
 *
 * 在原有角色权限校验之前增加「接口方法限制」：
 * 除超级管理员（level=0）外，其他后台用户默认禁止调用写方法（POST/PUT/DELETE），
 * 命中白名单的路由放行；读方法（GET 等）默认放行，命中黑名单的路由拦截。
 * 规则直接维护在下方类常量中，路由支持精确匹配或以 * 结尾的前缀通配（如 crud/*）。
 *
 * Class AdminCheckRoleMiddleware
 * @package app\http\middleware
 */
class AdminCheckRoleMiddleware implements MiddlewareInterface
{
    /**
     * 是否启用接口方法限制（false 时仅按原有角色权限校验，不拦截任何方法）
     * @var bool
     */
    public const METHOD_LIMIT_ENABLE = false;

    /**
     * 默认禁止非超级管理员调用的请求方法（大写）
     * @var array
     */
    public const METHOD_LIMIT_METHODS = ['POST', 'PUT', 'DELETE'];

    /**
     * 白名单：被禁方法中允许非超级管理员调用的路由，支持 * 前缀通配
     * 填写路由规则（与路由定义一致，参数写 :id），如 order/remark/:id、crud/*
     * @var array
     */
    public const METHOD_LIMIT_ALLOW = [
        //'crud/*',
        'aiagent/chat_stream',
        'menusSearch',
    ];

    /**
     * 黑名单：读方法（GET 等）中禁止非超级管理员调用的路由，支持 * 前缀通配
     * @var array
     */
    public const METHOD_LIMIT_DENY = [
        //'system/admin/index',
        'system/upgrade_status',
        'theme/use_data/<id>',
        'theme/restore/<id>',
        'theme/export/<id>',
        'theme/use/<id>',
        'system/clear/<type>',
    ];

    /**
     * 权限规则验证
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \throwable
     */
    public function handle(Request $request, \Closure $next)
    {
        if ($request->method(true) === 'OPTIONS') {
            return $next($request);
        }

        if (!$request->adminId() || !$request->adminInfo())
            throw new AuthException('参数错误');

        // 超级管理员不受方法限制，其他后台用户先按方法黑白名单拦截，再做角色权限校验
        $this->verifyMethodLimit($request);

        if ($request->adminInfo()['level'] && !static::METHOD_LIMIT_ENABLE) {
            /** @var SystemRoleServices $systemRoleService */
            $systemRoleService = app()->make(SystemRoleServices::class);
            $systemRoleService->verifyAuth($request);
        }

        return $next($request);
    }

    /**
     * 接口方法限制：被禁方法默认拒绝、白名单放行；读方法默认放行、黑名单拒绝
     * @param Request $request
     * @return void
     * @throws AuthException
     */
    protected function verifyMethodLimit(Request $request): void
    {
        if (!static::METHOD_LIMIT_ENABLE) {
            return;
        }

        // 超级管理员（level=0）不受限制
        if (!(int)($request->adminInfo()['level'] ?? 0)) {
            return;
        }

        $rule = $this->getRequestRule($request);

        if (in_array(strtoupper($request->method()), static::METHOD_LIMIT_METHODS, true)) {
            // 被禁方法：未命中白名单一律拒绝
            if (!$this->matchRuleList(static::METHOD_LIMIT_ALLOW, $rule)) {
                throw new AuthException('暂无权限');
            }
            return;
        }

        // 读方法：命中黑名单拒绝
        if ($this->matchRuleList(static::METHOD_LIMIT_DENY, $rule)) {
            throw new AuthException('暂无权限');
        }
    }

    /**
     * 获取当前请求的路由规则（小写），路由对象为空时回退 pathinfo
     * @param Request $request
     * @return string
     */
    protected function getRequestRule(Request $request): string
    {
        $rule = '';
        if ($request->rule()) {
            $rule = $request->rule()->getRule();
        }

        return trim(strtolower($rule ?: $request->pathinfo()));
    }

    /**
     * 判断路由是否命中规则列表：精确匹配或以 * 结尾的前缀通配，不区分大小写
     * @param array $rules
     * @param string $rule
     * @return bool
     */
    protected function matchRuleList(array $rules, string $rule): bool
    {
        foreach ($rules as $item) {
            $item = trim(strtolower($item));
            if ($item === '') {
                continue;
            }
            // 通配规则：去掉结尾 * 后按前缀匹配
            if (str_ends_with($item, '*')) {
                if (str_starts_with($rule, rtrim($item, '*'))) {
                    return true;
                }
            } elseif ($item === $rule) {
                return true;
            }
        }
        return false;
    }
}
