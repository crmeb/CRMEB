<?php

declare (strict_types=1);

namespace think\traits;

/**
 * 域名处理 trait
 */
trait DomainHandler
{
    /**
     * 域名（含协议及端口）
     * @var string
     */
    protected $domain;

    /**
     * 域名根
     * @var string
     */
    protected $rootDomain = '';

    /**
     * 子域名
     * @var string
     */
    protected $subDomain = '';

    /**
     * 泛域名
     * @var string
     */
    protected $panDomain = '';

    /**
     * 特殊域名根标识 用于识别com.cn org.cn 这种
     * @var array
     */
    protected $domainSpecialSuffix = ['com', 'net', 'org', 'edu', 'gov', 'mil', 'co', 'info'];

    /**
     * 设置当前域名
     * @access public
     * @param  string $domain 域名
     * @return $this
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * 获取当前域名
     * @access public
     * @param  bool $port 是否需要包含端口
     * @return string
     */
    public function domain(bool $port = false): string
    {
        if (!$this->domain) {
            $this->domain = $this->scheme() . '://' . $this->host();
        }

        return $port ? $this->domain : rtrim($this->domain, ':');
    }

    /**
     * 设置域名根
     * @access public
     * @param  string $domain 域名根
     * @return $this
     */
    public function setRootDomain(string $domain)
    {
        $this->rootDomain = $domain;
        return $this;
    }

    /**
     * 获取域名根
     * @access public
     * @return string
     */
    public function rootDomain(): string
    {
        if (!$this->rootDomain) {
            $item   = explode('.', $this->host());
            $count  = count($item);
            $suffix = $this->config('app.domain_suffix');

            if ($suffix && in_array($item[$count - 2], $this->domainSpecialSuffix)) {
                $this->rootDomain = $item[$count - 3] . '.' . $item[$count - 2] . '.' . $item[$count - 1];
            } elseif ($suffix) {
                $this->rootDomain = $item[$count - 2] . '.' . $item[$count - 1];
            } else {
                $this->rootDomain = $item[$count - 2] . '.' . $item[$count - 1];
            }
        }

        return $this->rootDomain;
    }

    /**
     * 设置子域名
     * @access public
     * @param  string $domain 子域名
     * @return $this
     */
    public function setSubDomain(string $domain)
    {
        $this->subDomain = $domain;
        return $this;
    }

    /**
     * 获取子域名
     * @access public
     * @return string
     */
    public function subDomain(): string
    {
        if (!$this->subDomain) {
            if ($this->isCli()) {
                return '';
            }

            $rootDomain = $this->rootDomain();
            if ($rootDomain) {
                $this->subDomain = rtrim(strstr($this->host(), $rootDomain, true), '.');
            } else {
                $this->subDomain = '';
            }
        }

        return $this->subDomain;
    }

    /**
     * 设置泛域名
     * @access public
     * @param  string $domain 泛域名
     * @return $this
     */
    public function setPanDomain(string $domain)
    {
        $this->panDomain = $domain;
        return $this;
    }

    /**
     * 获取泛域名
     * @access public
     * @return string
     */
    public function panDomain(): string
    {
        if (!$this->panDomain) {
            if ($this->isCli()) {
                return '';
            }

            $rootDomain = $this->rootDomain();
            if ($rootDomain) {
                $this->panDomain = '*' . $rootDomain;
            } else {
                $this->panDomain = '';
            }
        }

        return $this->panDomain;
    }

    /**
     * 获取当前HOST
     * @access public
     * @param  bool $strict 是否严格模式
     * @return string
     */
    public function host(bool $strict = true): string
    {
        if ($this->server('HTTP_X_FORWARDED_HOST')) {
            $host = $this->server('HTTP_X_FORWARDED_HOST');
        } elseif ($this->server('HTTP_HOST')) {
            $host = $this->server('HTTP_HOST');
        } else {
            $host = $this->server('SERVER_NAME') . ($this->server('SERVER_PORT') == '80' ? '' : ':' . $this->server('SERVER_PORT'));
        }

        return true === $strict && strpos($host, ':') ? strstr($host, ':', true) : $host;
    }

    /**
     * 获取当前请求的协议
     * @access public
     * @return string
     */
    public function scheme(): string
    {
        return $this->isSsl() ? 'https' : 'http';
    }

    /**
     * 当前是否SSL
     * @access public
     * @return bool
     */
    public function isSsl(): bool
    {
        if ($this->server('HTTPS') && ('1' == $this->server('HTTPS') || 'on' == strtolower($this->server('HTTPS')))) {
            return true;
        } elseif ('https' == $this->server('REQUEST_SCHEME')) {
            return true;
        } elseif ('443' == $this->server('SERVER_PORT')) {
            return true;
        } elseif ('https' == $this->server('HTTP_X_FORWARDED_PROTO')) {
            return true;
        }

        return false;
    }

    /**
     * 获取当前请求的端口
     * @access public
     * @return int
     */
    public function port(): int
    {
        return $this->server('SERVER_PORT') ?? 80;
    }

    /**
     * 获取当前请求的远程端口
     * @access public
     * @return int
     */
    public function remotePort(): int
    {
        return $this->server('REMOTE_PORT') ?? 0;
    }
}