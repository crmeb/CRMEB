<?php

declare (strict_types=1);

namespace think\traits;

/**
 * HTTP方法处理 trait
 */
trait HttpMethodHandler
{
    /**
     * 请求类型
     * @var string
     */
    protected $varMethod = '_method';

    /**
     * 表单ajax伪装变量
     * @var string
     */
    protected $varAjax = '_ajax';

    /**
     * 表单pjax伪装变量
     * @var string
     */
    protected $varPjax = '_pjax';

    /**
     * 请求类型
     * @var string
     */
    protected $method;

    /**
     * 获取请求类型
     * @access public
     * @param  bool $origin 是否获取原始请求类型
     * @return string
     */
    public function method(bool $origin = false): string
    {
        if ($origin) {
            return $this->server('REQUEST_METHOD') ?: 'GET';
        } elseif (!$this->method) {
            if (isset($_POST[$this->varMethod])) {
                $this->method = strtoupper($_POST[$this->varMethod]);
                $this->{$this->method}($_POST);
            } elseif ($this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $this->method = $this->server('HTTP_X_HTTP_METHOD_OVERRIDE');
            } else {
                $this->method = $this->server('REQUEST_METHOD') ?: 'GET';
            }
        }

        return $this->method;
    }

    /**
     * 是否为GET请求
     * @access public
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method() == 'GET';
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method() == 'POST';
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @access public
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->method() == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @access public
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @access public
     * @return bool
     */
    public function isOptions(): bool
    {
        return $this->method() == 'OPTIONS';
    }

    /**
     * 是否为CLI
     * @access public
     * @return bool
     */
    public function isCli(): bool
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为CGI
     * @access public
     * @return bool
     */
    public function isCgi(): bool
    {
        return str_starts_with(PHP_SAPI, 'cgi');
    }

    /**
     * 是否为ajax请求
     * @access public
     * @param  bool $ajax true 获取原始ajax请求
     * @return bool
     */
    public function isAjax(bool $ajax = false): bool
    {
        $result = $this->server('HTTP_X_REQUESTED_WITH', '', 'strtolower') === 'xmlhttprequest';

        if (true === $ajax) {
            return $result;
        }

        return $this->param($this->varAjax) ? true : $result;
    }

    /**
     * 是否为pjax请求
     * @access public
     * @param  bool $pjax true 获取原始pjax请求
     * @return bool
     */
    public function isPjax(bool $pjax = false): bool
    {
        $result = !empty($this->server('HTTP_X_PJAX'));

        if (true === $pjax) {
            return $result;
        }

        return $this->param($this->varPjax) ? true : $result;
    }

    /**
     * 是否为JSON请求
     * @access public
     * @return bool
     */
    public function isJson(): bool
    {
        return false !== strpos($this->type(), 'json');
    }

    /**
     * 是否为手机访问
     * @access public
     * @return bool
     */
    public function isMobile(): bool
    {
        if ($this->server('HTTP_VIA') && stristr($this->server('HTTP_VIA'), 'wap')) {
            return true;
        } elseif ($this->server('HTTP_ACCEPT') && strpos(strtoupper($this->server('HTTP_ACCEPT')), 'VND.WAP.WML')) {
            return true;
        } elseif ($this->server('HTTP_X_WAP_PROFILE') || $this->server('HTTP_PROFILE')) {
            return true;
        } elseif ($this->server('HTTP_USER_AGENT') && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc-|htc_|htc-|iemobile|kindle|midp|mmp|mobile|novarra|o2 |opera mini|opera mobi|palm|palmos|pocket|portalmmm|proxynet|sharp-|sharp t-mobile|sonyericsson |sonyericsson|symbian|symbianos|up.browser|up.link|vodafone|wap |webos|windows ce|windows phone|xda |xda_)/i', $this->server('HTTP_USER_AGENT'))) {
            return true;
        }

        return false;
    }
}