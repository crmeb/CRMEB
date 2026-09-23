<?php

namespace Qiniu\Http;

use Qiniu\Http\Middleware\Middleware;

final class RequestOptions
{

    /**
     * @var int|null
     * http 请求的超时时间，单位：秒，默认：0，不超时
     */
    public $connection_timeout;

    /**
     * @var int|null
     * http 请求的超时时间，单位：毫秒，默认：0，不超时
     */
    public $connection_timeout_ms;

    /**
     * @var int|null
     * http 请求的超时时间，单位：秒，默认：0，不超时
     */
    public $timeout;


    /**
     * @var int|null
     * http 请求的超时时间，单位：毫秒，默认：0，不超时
     */
    public $timeout_ms;

    /**
     * @var string|null
     * 代理URL，默认：空
     */
    public $proxy;

    /**
     * @var int|null
     * 代理鉴权方式，默认：空
     */
    public $proxy_auth;

    /**
     * @var string|null
     * 代理鉴权参数，默认：空
     */
    public $proxy_user_password;

    /**
     * @var array<Middleware>
     */
    public $middlewares;

    public function __construct(
        $connection_timeout = null,
        $connection_timeout_ms = null,
        $timeout = null,
        $timeout_ms = null,
        $middlewares = array(),
        $proxy = null,
        $proxy_auth = null,
        $proxy_user_password = null
    ) {
        $this->connection_timeout = $connection_timeout;
        $this->connection_timeout_ms = $connection_timeout_ms;
        $this->timeout = $timeout;
        $this->timeout_ms = $timeout_ms;
        $this->proxy = $proxy;
        $this->proxy_auth = $proxy_auth;
        $this->proxy_user_password = $proxy_user_password;
        $this->middlewares = $middlewares;
    }

    public function getCurlOpt()
    {
        $result = array();
        if ($this->connection_timeout != null) {
            $result[CURLOPT_CONNECTTIMEOUT] = $this->connection_timeout;
        }
        if ($this->connection_timeout_ms != null) {
            $result[CURLOPT_CONNECTTIMEOUT_MS] = $this->connection_timeout_ms;
        }
        if ($this->timeout != null) {
            $result[CURLOPT_TIMEOUT] = $this->timeout;
        }
        if ($this->timeout_ms != null) {
            $result[CURLOPT_TIMEOUT_MS] = $this->timeout_ms;
        }
        if ($this->proxy != null) {
            $result[CURLOPT_PROXY] = $this->proxy;
        }
        if ($this->proxy_auth != null) {
            $result[CURLOPT_PROXYAUTH] = $this->proxy_auth;
        }
        if ($this->proxy_user_password != null) {
            $result[CURLOPT_PROXYUSERPWD] = $this->proxy_user_password;
        }
        return $result;
    }
}
