<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace crmeb\utils;

use think\facade\Config;
use think\facade\Env;

class Cors
{
    public static function allowedOrigin($origin): string
    {
        if (!$origin) {
            return "";
        }

        $origin = rtrim(trim((string) $origin), "/");
        if ($origin === "") {
            return "";
        }

        $scheme = parse_url($origin, PHP_URL_SCHEME);
        if (!in_array($scheme, ["http", "https"], true)) {
            return "";
        }

        $allowed = Config::get("cookie.cors_allowed_origins", []);
        if (is_string($allowed)) {
            $allowed = array_filter(array_map("trim", explode(",", $allowed)));
        }
        if (is_array($allowed)) {
            $allowed = array_values(
                array_filter(
                    array_map(static function ($value) {
                        $value = rtrim(trim((string) $value), "/");
                        return $value !== "" ? $value : "";
                    }, $allowed),
                ),
            );
        }

        if (is_array($allowed) && count($allowed)) {
            return in_array($origin, $allowed, true) ? $origin : "";
        }

        $cookieDomain = (string) Config::get("cookie.domain", "");
        $cookieDomain = trim($cookieDomain);
        // 兼容：开发环境（APP_DEBUG=true）无白名单时默认放行；生产默认收紧
        if ($cookieDomain === "") {
            return Env::get("app_debug", false) ? $origin : "";
        }

        $originHost = parse_url($origin, PHP_URL_HOST);
        if (!$originHost) {
            return "";
        }

        $cookieDomain = ltrim($cookieDomain, ".");
        $originHost = strtolower($originHost);
        $cookieDomain = strtolower($cookieDomain);

        if ($originHost === $cookieDomain) {
            return $origin;
        }

        $suffix = "." . $cookieDomain;
        if (
            strlen($originHost) > strlen($suffix) &&
            substr($originHost, -strlen($suffix)) === $suffix
        ) {
            return $origin;
        }

        return "";
    }

    public static function buildHeaders($origin): array
    {
        $header = Config::get("cookie.header", []);
        if (!is_array($header)) {
            $header = [];
        }

        unset(
            $header["Access-Control-Allow-Origin"],
            $header["Access-Control-Allow-Credentials"],
        );

        $allowedOrigin = self::allowedOrigin($origin);
        if ($allowedOrigin !== "") {
            $header["Access-Control-Allow-Origin"] = $allowedOrigin;
            $header["Access-Control-Allow-Credentials"] = "true";
            $header["Vary"] = "Origin";
        }

        return $header;
    }
}
