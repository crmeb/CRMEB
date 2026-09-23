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
declare(strict_types=1);

namespace crmeb\utils;

/**
 * 出站 URL 安全防护（SSRF）
 *
 * 管理员可配置的出站地址（AI 模型接口地址、远程 MCP Server 地址等）由服务端
 * 直接发起请求，必须限制可触达的网络范围：仅允许 http/https 协议，拒绝解析到
 * 回环、内网、链路本地（含云厂商元数据 169.254.169.254）等非公网地址，并拒绝
 * 数据库/缓存等非 HTTP 服务端口。DNS 解析失败按不安全处理（fail-closed）。
 *
 * selfcall 模式供「系统自调用」场景使用（如内置 MCP 经 site_url 回环访问本系统）：
 * 放行回环与内网地址，但仍拒绝链路本地与未指定地址。
 */
class SsrfGuard
{
    /** 严格模式：仅允许公网地址（默认） */
    public const MODE_STRICT = 'strict';

    /** 自调用模式：放行回环与内网（系统经 site_url 回环自调用），仍拒绝链路本地 */
    public const MODE_SELFCALL = 'selfcall';

    /** 允许的协议 */
    private const ALLOWED_SCHEMES = ['http', 'https'];

    /** 拒绝出站的非 HTTP 服务端口（数据库/缓存/消息队列/远程管理等） */
    private const BLOCKED_PORTS = [
        22, 23, 25, 110, 135, 139, 143, 445, 873, 1080, 1433, 1521, 2049,
        2181, 2375, 2376, 3306, 3389, 5432, 5672, 5900, 6379, 9200, 9300,
        11211, 27017, 27018, 50070,
    ];

    /**
     * 校验出站地址安全性，不安全时抛出异常
     * @param string $url 待校验地址
     * @param string $mode MODE_STRICT 仅公网；MODE_SELFCALL 放行回环与内网
     * @throws \InvalidArgumentException 地址缺失、协议/端口不允许或解析到受限地址
     */
    public static function assertSafeUrl(string $url, string $mode = self::MODE_STRICT): void
    {
        $parts = parse_url(trim($url));
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            throw new \InvalidArgumentException('接口地址格式不正确');
        }
        $scheme = strtolower((string)$parts['scheme']);
        if (!in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            throw new \InvalidArgumentException('接口地址仅支持 http:// 或 https:// 协议');
        }
        $port = (int)($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
        if ($port < 1 || $port > 65535 || in_array($port, self::BLOCKED_PORTS, true)) {
            throw new \InvalidArgumentException('接口地址使用了不允许的端口');
        }
        $host = (string)$parts['host'];
        // IPv6 字面量在 parse_url 中保留方括号，统一去掉后再解析
        $host = trim($host, '[]');
        foreach (self::resolveIps($host) as $ip) {
            if (!self::isIpAllowed($ip, $mode)) {
                throw new \InvalidArgumentException('接口地址不允许指向内网或保留地址（含云服务器元数据地址）');
            }
        }
    }

    /**
     * 解析主机名为 IP 列表（供请求时固定解析结果、防 DNS 重绑定）
     * @param string $host 主机名或 IP 字面量
     * @return array<int,string> 解析出的全部 A/AAAA 记录
     * @throws \InvalidArgumentException 主机名无法解析时抛出
     */
    public static function resolveIps(string $host): array
    {
        // IP 字面量无需解析
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return [$host];
        }
        $ips = [];
        $records = @dns_get_record($host, DNS_A | DNS_AAAA);
        if (is_array($records)) {
            foreach ($records as $record) {
                if (!empty($record['ip'])) {
                    $ips[] = (string)$record['ip'];
                }
                if (!empty($record['ipv6'])) {
                    $ips[] = (string)$record['ipv6'];
                }
            }
        }
        if ($ips === []) {
            // dns_get_record 不可用或无记录时退回 gethostbynamel（IPv4）
            $ipv4 = @gethostbynamel($host);
            if (is_array($ipv4)) {
                $ips = $ipv4;
            }
        }
        $ips = array_values(array_unique(array_filter($ips)));
        if ($ips === []) {
            throw new \InvalidArgumentException('接口地址的域名无法解析，请检查是否填写正确');
        }
        return $ips;
    }

    /**
     * 生成 curl CURLOPT_RESOLVE 固定解析条目（把域名钉到校验时解析出的 IP，
     * 防止校验与真实请求之间 DNS 重绑定到内网）
     * @param string $url 请求地址
     * @return array<int,string> CURLOPT_RESOLVE 条目，域名为主机时非空
     */
    public static function resolveEntries(string $url): array
    {
        $parts = parse_url(trim($url));
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return [];
        }
        $host = (string)$parts['host'];
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return [];
        }
        $scheme = strtolower((string)$parts['scheme']);
        $port = (int)($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
        $entries = [];
        foreach (self::resolveIps(trim($host, '[]')) as $ip) {
            $entries[] = "{$host}:{$port}:{$ip}";
        }
        return $entries;
    }

    /**
     * 判断单个 IP 是否允许出站访问
     * @param string $ip IPv4/IPv6 地址（支持 IPv4-mapped IPv6）
     * @param string $mode MODE_STRICT / MODE_SELFCALL
     * @return bool
     */
    private static function isIpAllowed(string $ip, string $mode): bool
    {
        // IPv4-mapped IPv6（::ffff:10.0.0.1 等）按内嵌 IPv4 判定
        if (stripos($ip, '::ffff:') === 0) {
            return self::isIpAllowed(substr($ip, 7), $mode);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // 所有模式都拒绝：链路本地（云元数据 169.254.169.254）与未指定地址
            if (self::inCidr4($ip, '169.254.0.0/16') || self::inCidr4($ip, '0.0.0.0/8')) {
                return false;
            }
            if ($mode === self::MODE_SELFCALL) {
                return true;
            }
            return !self::inCidr4($ip, '127.0.0.0/8')
                && !self::inCidr4($ip, '10.0.0.0/8')
                && !self::inCidr4($ip, '172.16.0.0/12')
                && !self::inCidr4($ip, '192.168.0.0/16')
                && !self::inCidr4($ip, '100.64.0.0/10');
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return false;
        }
        $lower = strtolower($ip);
        // 所有模式都拒绝：链路本地与未指定地址
        if (str_starts_with($lower, 'fe80:') || $lower === '::') {
            return false;
        }
        if ($mode === self::MODE_SELFCALL) {
            return true;
        }
        // 严格模式拒绝：回环、唯一本地地址（ULA fc00::/7）
        return $lower !== '::1' && !str_starts_with($lower, 'fc') && !str_starts_with($lower, 'fd');
    }

    /**
     * IPv4 是否落在 CIDR 网段内
     * @param string $ip IPv4 地址
     * @param string $cidr a.b.c.d/nn 形式网段
     * @return bool
     */
    private static function inCidr4(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $netLong = ip2long($subnet);
        if ($ipLong === false || $netLong === false) {
            return false;
        }
        $mask = -1 << (32 - (int)$bits);
        return ($ipLong & $mask) === ($netLong & $mask);
    }
}
