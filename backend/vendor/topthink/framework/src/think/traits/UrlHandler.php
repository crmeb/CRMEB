<?php

declare (strict_types=1);

namespace think\traits;

/**
 * URL处理 trait
 */
trait UrlHandler
{
    /**
     * 当前URL地址
     * @var string
     */
    protected $url;

    /**
     * 当前URL地址 不含QUERY_STRING
     * @var string
     */
    protected $baseUrl;

    /**
     * 当前执行的文件 SCRIPT_NAME
     * @var string
     */
    protected $baseFile;

    /**
     * URL访问根地址
     * @var string
     */
    protected $root;

    /**
     * 设置当前请求的URL
     * @access public
     * @param  string $url URL地址
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * 获取当前请求URL
     * @access public
     * @param  bool $complete 是否包含完整域名
     * @return string
     */
    public function url(bool $complete = false): string
    {
        if ($this->url) {
            $url = $this->url;
        } elseif ($this->server('HTTP_X_REWRITE_URL')) {
            $url = $this->server('HTTP_X_REWRITE_URL');
        } elseif ($this->server('REQUEST_URI')) {
            $url = $this->server('REQUEST_URI');
        } elseif ($this->server('ORIG_PATH_INFO')) {
            $url = $this->server('ORIG_PATH_INFO') . (!empty($this->server('QUERY_STRING')) ? '?' . $this->server('QUERY_STRING') : '');
        } elseif (isset($_SERVER['argv'][1])) {
            $url = $_SERVER['argv'][1];
        } else {
            $url = '';
        }

        return $complete ? $this->domain() . $url : $url;
    }

    /**
     * 设置当前URL 不含QUERY_STRING
     * @access public
     * @param  string $url URL地址
     * @return $this
     */
    public function setBaseUrl(string $url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * 获取当前URL 不含QUERY_STRING
     * @access public
     * @param  bool $complete 是否包含完整域名
     * @return string
     */
    public function baseUrl(bool $complete = false): string
    {
        if (!$this->baseUrl) {
            $str           = $this->url();
            $this->baseUrl = str_contains($str, '?') ? strstr($str, '?', true) : $str;
        }

        return $complete ? $this->domain() . $this->baseUrl : $this->baseUrl;
    }

    /**
     * 获取当前执行的文件 SCRIPT_NAME
     * @access public
     * @param  bool $complete 是否包含完整域名
     * @return string
     */
    public function baseFile(bool $complete = false): string
    {
        if (!$this->baseFile) {
            $url = '';
            if (!$this->isCli()) {
                $script_name = basename($this->server('SCRIPT_FILENAME'));
                if (basename($this->server('SCRIPT_NAME')) === $script_name) {
                    $url = $this->server('SCRIPT_NAME');
                } elseif (basename($this->server('PHP_SELF')) === $script_name) {
                    $url = $this->server('PHP_SELF');
                } elseif (basename($this->server('ORIG_SCRIPT_NAME')) === $script_name) {
                    $url = $this->server('ORIG_SCRIPT_NAME');
                } elseif (($pos = strpos($this->server('PHP_SELF'), '/' . $script_name)) !== false) {
                    $url = substr($this->server('SCRIPT_NAME'), 0, $pos) . '/' . $script_name;
                } elseif ($this->server('DOCUMENT_ROOT') && str_starts_with($this->server('SCRIPT_FILENAME'), $this->server('DOCUMENT_ROOT'))) {
                    $url = str_replace('\\', '/', str_replace($this->server('DOCUMENT_ROOT'), '', $this->server('SCRIPT_FILENAME')));
                }
            }
            $this->baseFile = $url;
        }

        return $complete ? $this->domain() . $this->baseFile : $this->baseFile;
    }

    /**
     * 设置URL访问根地址
     * @access public
     * @param  string $url URL地址
     * @return $this
     */
    public function setRoot(string $url)
    {
        $this->root = $url;
        return $this;
    }

    /**
     * 获取URL访问根地址
     * @access public
     * @param  bool $complete 是否包含完整域名
     * @return string
     */
    public function root(bool $complete = false): string
    {
        if (!$this->root) {
            $file = $this->baseFile();
            if ($file && !str_starts_with($this->url(), $file)) {
                $file = str_replace('\\', '/', dirname($file));
            }
            $this->root = rtrim($file, '/');
        }

        return $complete ? $this->domain() . $this->root : $this->root;
    }

    /**
     * 获取URL访问根地址
     * @access public
     * @return string
     */
    public function rootUrl(): string
    {
        $base = $this->root();
        $root = '' === $base ? dirname($this->baseUrl()) : $base;

        return $root;
    }

    /**
     * 获取当前请求URL的pathinfo信息（含URL后缀）
     * @access public
     * @return string
     */
    public function pathinfo(): string
    {
        if (isset($_SERVER['PATH_INFO'])) {
            return ltrim($_SERVER['PATH_INFO'], '/');
        }

        $url = $this->url();
        $base = $this->rootUrl();

        if ($base && str_starts_with($url, $base)) {
            $url = substr($url, strlen($base));
        }

        return ltrim($url, '/');
    }

    /**
     * 获取当前请求URL的pathinfo信息(不含URL后缀)
     * @access public
     * @return string
     */
    public function path(): string
    {
        $pathinfo = $this->pathinfo();
        $suffix   = $this->config('url_html_suffix');

        if (false === $suffix) {
            return $pathinfo;
        }

        return preg_replace('/\.(' . $suffix . ')$/i', '', $pathinfo);
    }

    /**
     * 获取URL后缀
     * @access public
     * @return string
     */
    public function ext(): string
    {
        return pathinfo($this->pathinfo(), PATHINFO_EXTENSION);
    }

    /**
     * 获取当前请求的时间
     * @access public
     * @param  bool $float 是否使用浮点数
     * @return float|int
     */
    public function time(bool $float = false)
    {
        return $float ? $_SERVER['REQUEST_TIME_FLOAT'] : $_SERVER['REQUEST_TIME'];
    }
}