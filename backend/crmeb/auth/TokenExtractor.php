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

namespace crmeb\auth;

use think\facade\Config;
use think\Request;

/**
 * 请求令牌提取器
 * 统一四端（api/adminapi/kefuapi/outapi）认证中间件的 token 提取口径：
 * 自定义认证头（cookie.token_name 配置，默认 Authori-zation）→ 标准 Authorization 头 → GET/POST 明文 token 历史兼容回退
 * Class TokenExtractor
 * @package crmeb\auth
 */
class TokenExtractor
{
    /**
     * 从请求中提取认证令牌（多级回退）
     * @param Request $request 请求对象
     * @return string 提取到的 token，无则返回空字符串
     */
    public static function extract(Request $request): string
    {
        // 优先统一配置的自定义认证头（个别服务器/网关无法透传 Authorization，故历史采用自定义头名）
        $token = self::trimBearer((string)$request->header(self::headerName(), ''));
        if (!$token) {
            // 兼容标准 Authorization 头
            $token = self::trimBearer((string)$request->header('Authorization', ''));
        }
        // 历史兼容：部分客户端（表单直传/第三方对接 outapi）仍通过明文参数传递 token，保留回退
        if (!$token) {
            $token = trim((string)$request->get('token', ''));
        }
        if (!$token) {
            $token = trim((string)$request->post('token', ''));
        }
        return $token;
    }

    /**
     * 获取统一的自定义认证头名
     * @return string
     */
    public static function headerName(): string
    {
        return (string)Config::get('cookie.token_name', 'Authori-zation');
    }

    /**
     * 去除 Bearer 前缀并修剪空白
     * 仅在 token 以 "Bearer " 前缀开头时才移除该前缀，避免 ltrim 按字符集裁剪误删 JWT 首字符（如 eyJ... 开头的 e）
     * @param string $token 原始头值
     * @return string 处理后的 token
     */
    protected static function trimBearer(string $token): string
    {
        if (stripos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
        return trim($token);
    }
}
