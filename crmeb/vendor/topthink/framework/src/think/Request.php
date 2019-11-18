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

namespace think;

use think\file\UploadedFile;
use think\route\Rule;

/**
 * 请求管理类
 * @package think
 */
class Request
{
    /**
     * 兼容PATH_INFO获取
     * @var array
     */
    protected $pathinfoFetch = ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'];

    /**
     * PATHINFO变量名 用于兼容模式
     * @var string
     */
    protected $varPathinfo = 's';

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
     * 域名根
     * @var string
     */
    protected $rootDomain = '';

    /**
     * HTTPS代理标识
     * @var string
     */
    protected $httpsAgentName = '';

    /**
     * 前端代理服务器IP
     * @var array
     */
    protected $proxyServerIp = [];

    /**
     * 前端代理服务器真实IP头
     * @var array
     */
    protected $proxyServerIpHeader = ['HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP'];

    /**
     * 请求类型
     * @var string
     */
    protected $method;

    /**
     * 域名（含协议及端口）
     * @var string
     */
    protected $domain;

    /**
     * HOST（含端口）
     * @var string
     */
    protected $host;

    /**
     * 子域名
     * @var string
     */
    protected $subDomain;

    /**
     * 泛域名
     * @var string
     */
    protected $panDomain;

    /**
     * 当前URL地址
     * @var string
     */
    protected $url;

    /**
     * 基础URL
     * @var string
     */
    protected $baseUrl;

    /**
     * 当前执行的文件
     * @var string
     */
    protected $baseFile;

    /**
     * 访问的ROOT地址
     * @var string
     */
    protected $root;

    /**
     * pathinfo
     * @var string
     */
    protected $pathinfo;

    /**
     * pathinfo（不含后缀）
     * @var string
     */
    protected $path;

    /**
     * 当前请求的IP地址
     * @var string
     */
    protected $realIP;

    /**
     * 当前控制器名
     * @var string
     */
    protected $controller;

    /**
     * 当前操作名
     * @var string
     */
    protected $action;

    /**
     * 当前请求参数
     * @var array
     */
    protected $param = [];

    /**
     * 当前GET参数
     * @var array
     */
    protected $get = [];

    /**
     * 当前POST参数
     * @var array
     */
    protected $post = [];

    /**
     * 当前REQUEST参数
     * @var array
     */
    protected $request = [];

    /**
     * 当前路由对象
     * @var Rule
     */
    protected $rule;

    /**
     * 当前ROUTE参数
     * @var array
     */
    protected $route = [];

    /**
     * 中间件传递的参数
     * @var array
     */
    protected $middleware = [];

    /**
     * 当前PUT参数
     * @var array
     */
    protected $put;

    /**
     * SESSION对象
     * @var Session
     */
    protected $session;

    /**
     * COOKIE数据
     * @var array
     */
    protected $cookie = [];

    /**
     * ENV对象
     * @var Env
     */
    protected $env;

    /**
     * 当前SERVER参数
     * @var array
     */
    protected $server = [];

    /**
     * 当前FILE参数
     * @var array
     */
    protected $file = [];

    /**
     * 当前HEADER参数
     * @var array
     */
    protected $header = [];

    /**
     * 资源类型定义
     * @var array
     */
    protected $mimeType = [
        'xml'   => 'application/xml,text/xml,application/x-xml',
        'json'  => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js'    => 'text/javascript,application/javascript,application/x-javascript',
        'css'   => 'text/css',
        'rss'   => 'application/rss+xml',
        'yaml'  => 'application/x-yaml,text/yaml',
        'atom'  => 'application/atom+xml',
        'pdf'   => 'application/pdf',
        'text'  => 'text/plain',
        'image' => 'image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*',
        'csv'   => 'text/csv',
        'html'  => 'text/html,application/xhtml+xml,*/*',
    ];

    /**
     * 当前请求内容
     * @var string
     */
    protected $content;

    /**
     * 全局过滤规则
     * @var array
     */
    protected $filter;

    /**
     * php://input内容
     * @var string
     */
    // php://input
    protected $input;

    /**
     * 请求安全Key
     * @var string
     */
    protected $secureKey;

    /**
     * 是否合并Param
     * @var bool
     */
    protected $mergeParam = false;

    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        // 保存 php://input
        $this->input = file_get_contents('php://input');
    }

    public static function __make(App $app)
    {
        $request = new static();

        $request->server  = $_SERVER;
        $request->env     = $app->env;
        $request->get     = $_GET;
        $request->post    = $_POST ?: $request->getInputData($request->input);
        $request->put     = $request->getInputData($request->input);
        $request->request = $_REQUEST;
        $request->cookie  = $_COOKIE;
        $request->file    = $_FILES ?? [];

        if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
            $header = $result;
        } else {
            $header = [];
            $server = $_SERVER;
            foreach ($server as $key => $val) {
                if (0 === strpos($key, 'HTTP_')) {
                    $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                    $header[$key] = $val;
                }
            }
            if (isset($server['CONTENT_TYPE'])) {
                $header['content-type'] = $server['CONTENT_TYPE'];
            }
            if (isset($server['CONTENT_LENGTH'])) {
                $header['content-length'] = $server['CONTENT_LENGTH'];
            }
        }

        $request->header = array_change_key_case($header);

        return $request;
    }

    /**
     * 设置当前包含协议的域名
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
     * 获取当前包含协议的域名
     * @access public
     * @param  bool $port 是否需要去除端口号
     * @return string
     */
    public function domain(bool $port = false): string
    {
        return $this->scheme() . '://' . $this->host($port);
    }

    /**
     * 获取当前根域名
     * @access public
     * @return string
     */
    public function rootDomain(): string
    {
        $root = $this->rootDomain;

        if (!$root) {
            $item  = explode('.', $this->host());
            $count = count($item);
            $root  = $count > 1 ? $item[$count - 2] . '.' . $item[$count - 1] : $item[0];
        }

        return $root;
    }

    /**
     * 设置当前泛域名的值
     * @access public
     * @param  string $domain 域名
     * @return $this
     */
    public function setSubDomain(string $domain)
    {
        $this->subDomain = $domain;
        return $this;
    }

    /**
     * 获取当前子域名
     * @access public
     * @return string
     */
    public function subDomain(): string
    {
        if (is_null($this->subDomain)) {
            // 获取当前主域名
            $rootDomain = $this->rootDomain();

            if ($rootDomain) {
                $this->subDomain = rtrim(stristr($this->host(), $rootDomain, true), '.');
            } else {
                $this->subDomain = '';
            }
        }

        return $this->subDomain;
    }

    /**
     * 设置当前泛域名的值
     * @access public
     * @param  string $domain 域名
     * @return $this
     */
    public function setPanDomain(string $domain)
    {
        $this->panDomain = $domain;
        return $this;
    }

    /**
     * 获取当前泛域名的值
     * @access public
     * @return string
     */
    public function panDomain(): string
    {
        return $this->panDomain ?: '';
    }

    /**
     * 设置当前完整URL 包括QUERY_STRING
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
     * 获取当前完整URL 包括QUERY_STRING
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
            $this->baseUrl = strpos($str, '?') ? strstr($str, '?', true) : $str;
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
                } elseif ($this->server('DOCUMENT_ROOT') && strpos($this->server('SCRIPT_FILENAME'), $this->server('DOCUMENT_ROOT')) === 0) {
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
            if ($file && 0 !== strpos($this->url(), $file)) {
                $file = str_replace('\\', '/', dirname($file));
            }
            $this->root = rtrim($file, '/');
        }

        return $complete ? $this->domain() . $this->root : $this->root;
    }

    /**
     * 获取URL访问根目录
     * @access public
     * @return string
     */
    public function rootUrl(): string
    {
        $base = $this->root();
        $root = strpos($base, '.') ? ltrim(dirname($base), DIRECTORY_SEPARATOR) : $base;

        if ('' != $root) {
            $root = '/' . ltrim($root, '/');
        }

        return $root;
    }

    /**
     * 设置当前请求的pathinfo
     * @access public
     * @param  string $pathinfo
     * @return $this
     */
    public function setPathinfo(string $pathinfo)
    {
        $this->pathinfo = $pathinfo;
        return $this;
    }

    /**
     * 获取当前请求URL的pathinfo信息（含URL后缀）
     * @access public
     * @return string
     */
    public function pathinfo(): string
    {
        if (is_null($this->pathinfo)) {
            if (isset($_GET[$this->varPathinfo])) {
                // 判断URL里面是否有兼容模式参数
                $pathinfo = $_GET[$this->varPathinfo];
                unset($_GET[$this->varPathinfo]);
                unset($this->get[$this->varPathinfo]);
            } elseif ($this->server('PATH_INFO')) {
                $pathinfo = $this->server('PATH_INFO');
            } elseif (false !== strpos(PHP_SAPI, 'cli')) {
                $pathinfo = strpos($this->server('REQUEST_URI'), '?') ? strstr($this->server('REQUEST_URI'), '?', true) : $this->server('REQUEST_URI');
            }

            // 分析PATHINFO信息
            if (!isset($pathinfo)) {
                foreach ($this->pathinfoFetch as $type) {
                    if ($this->server($type)) {
                        $pathinfo = (0 === strpos($this->server($type), $this->server('SCRIPT_NAME'))) ?
                        substr($this->server($type), strlen($this->server('SCRIPT_NAME'))) : $this->server($type);
                        break;
                    }
                }
            }

            if (!empty($pathinfo)) {
                unset($this->get[$pathinfo], $this->request[$pathinfo]);
            }

            $this->pathinfo = empty($pathinfo) || '/' == $pathinfo ? '' : ltrim($pathinfo, '/');
        }

        return $this->pathinfo;
    }

    /**
     * 当前URL的访问后缀
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
     * @param  bool $float 是否使用浮点类型
     * @return integer|float
     */
    public function time(bool $float = false)
    {
        return $float ? $this->server('REQUEST_TIME_FLOAT') : $this->server('REQUEST_TIME');
    }

    /**
     * 当前请求的资源类型
     * @access public
     * @return string
     */
    public function type(): string
    {
        $accept = $this->server('HTTP_ACCEPT');

        if (empty($accept)) {
            return '';
        }

        foreach ($this->mimeType as $key => $val) {
            $array = explode(',', $val);
            foreach ($array as $k => $v) {
                if (stristr($accept, $v)) {
                    return $key;
                }
            }
        }

        return '';
    }

    /**
     * 设置资源类型
     * @access public
     * @param  string|array $type 资源类型名
     * @param  string       $val 资源类型
     * @return void
     */
    public function mimeType($type, $val = ''): void
    {
        if (is_array($type)) {
            $this->mimeType = array_merge($this->mimeType, $type);
        } else {
            $this->mimeType[$type] = $val;
        }
    }

    /**
     * 设置请求类型
     * @access public
     * @param  string $method 请求类型
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * 当前的请求类型
     * @access public
     * @param  bool $origin 是否获取原始请求类型
     * @return string
     */
    public function method(bool $origin = false): string
    {
        if ($origin) {
            // 获取原始请求类型
            return $this->server('REQUEST_METHOD') ?: 'GET';
        } elseif (!$this->method) {
            if (isset($this->post[$this->varMethod])) {
                $method = strtolower($this->post[$this->varMethod]);
                if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                    $this->method    = strtoupper($method);
                    $this->{$method} = $this->post;
                } else {
                    $this->method = 'POST';
                }
                unset($this->post[$this->varMethod]);
            } elseif ($this->server('HTTP_X_HTTP_METHOD_OVERRIDE')) {
                $this->method = strtoupper($this->server('HTTP_X_HTTP_METHOD_OVERRIDE'));
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
     * 是否为cli
     * @access public
     * @return bool
     */
    public function isCli(): bool
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为cgi
     * @access public
     * @return bool
     */
    public function isCgi(): bool
    {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    /**
     * 获取当前请求的参数
     * @access public
     * @param  string|array $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function param($name = '', $default = null, $filter = '')
    {
        if (empty($this->mergeParam)) {
            $method = $this->method(true);

            // 自动获取请求变量
            switch ($method) {
                case 'POST':
                    $vars = $this->post(false);
                    break;
                case 'PUT':
                case 'DELETE':
                case 'PATCH':
                    $vars = $this->put(false);
                    break;
                default:
                    $vars = [];
            }

            // 当前请求参数和URL地址中的参数合并
            $this->param = array_merge($this->param, $this->get(false), $vars, $this->route(false));

            $this->mergeParam = true;
        }

        if (is_array($name)) {
            return $this->only($name, $this->param, $filter);
        }

        return $this->input($this->param, $name, $default, $filter);
    }

    /**
     * 设置路由变量
     * @access public
     * @param  Rule $rule 路由对象
     * @return $this
     */
    public function setRule(Rule $rule)
    {
        $this->rule = $rule;
        return $this;
    }

    /**
     * 获取当前路由对象
     * @access public
     * @return Rule|null
     */
    public function rule()
    {
        return $this->rule;
    }

    /**
     * 设置路由变量
     * @access public
     * @param  array $route 路由变量
     * @return $this
     */
    public function setRoute(array $route)
    {
        $this->route = array_merge($this->route, $route);
        return $this;
    }

    /**
     * 获取路由参数
     * @access public
     * @param  string|array $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function route($name = '', $default = null, $filter = '')
    {
        if (is_array($name)) {
            return $this->only($name, $this->route, $filter);
        }

        return $this->input($this->route, $name, $default, $filter);
    }

    /**
     * 获取GET参数
     * @access public
     * @param  string|array $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '')
    {
        if (is_array($name)) {
            return $this->only($name, $this->get, $filter);
        }

        return $this->input($this->get, $name, $default, $filter);
    }

    /**
     * 获取中间件传递的参数
     * @access public
     * @param  mixed $name 变量名
     * @param  mixed $default 默认值
     * @return mixed
     */
    public function middleware($name, $default = null)
    {
        return $this->middleware[$name] ?? $default;
    }

    /**
     * 获取POST参数
     * @access public
     * @param  string|array $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        if (is_array($name)) {
            return $this->only($name, $this->post, $filter);
        }

        return $this->input($this->post, $name, $default, $filter);
    }

    /**
     * 获取PUT参数
     * @access public
     * @param  string|array $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function put($name = '', $default = null, $filter = '')
    {
        if (is_array($name)) {
            return $this->only($name, $this->put, $filter);
        }

        return $this->input($this->put, $name, $default, $filter);
    }

    protected function getInputData($content): array
    {
        if (false !== strpos($this->contentType(), 'json')) {
            return (array) json_decode($content, true);
        } elseif (strpos($content, '=')) {
            parse_str($content, $data);
            return $data;
        }

        return [];
    }

    /**
     * 设置获取DELETE参数
     * @access public
     * @param  mixed        $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function delete($name = '', $default = null, $filter = '')
    {
        return $this->put($name, $default, $filter);
    }

    /**
     * 设置获取PATCH参数
     * @access public
     * @param  mixed        $name 变量名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function patch($name = '', $default = null, $filter = '')
    {
        return $this->put($name, $default, $filter);
    }

    /**
     * 获取request变量
     * @access public
     * @param  string|array $name 数据名称
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function request($name = '', $default = null, $filter = '')
    {
        if (is_array($name)) {
            return $this->only($name, $this->request, $filter);
        }

        return $this->input($this->request, $name, $default, $filter);
    }

    /**
     * 获取环境变量
     * @access public
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    public function env(string $name = '', string $default = null)
    {
        if (empty($name)) {
            return $this->env->get();
        } else {
            $name = strtoupper($name);
        }

        return $this->env->get($name, $default);
    }

    /**
     * 获取session数据
     * @access public
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    public function session(string $name = '', $default = null)
    {
        if ('' === $name) {
            return $this->session->all();
        }
        return $this->session->get($name, $default);
    }

    /**
     * 获取cookie参数
     * @access public
     * @param  mixed        $name 数据名称
     * @param  string       $default 默认值
     * @param  string|array $filter 过滤方法
     * @return mixed
     */
    public function cookie(string $name = '', $default = null, $filter = '')
    {
        if (!empty($name)) {
            $data = $this->getData($this->cookie, $name, $default);
        } else {
            $data = $this->cookie;
        }

        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
        } else {
            $this->filterValue($data, $name, $filter);
        }

        return $data;
    }

    /**
     * 获取server参数
     * @access public
     * @param  string $name 数据名称
     * @param  string $default 默认值
     * @return mixed
     */
    public function server(string $name = '', string $default = '')
    {
        if (empty($name)) {
            return $this->server;
        } else {
            $name = strtoupper($name);
        }

        return $this->server[$name] ?? $default;
    }

    /**
     * 获取上传的文件信息
     * @access public
     * @param  string $name 名称
     * @return null|array|UploadedFile
     */
    public function file(string $name = '')
    {
        $files = $this->file;
        if (!empty($files)) {

            if (strpos($name, '.')) {
                list($name, $sub) = explode('.', $name);
            }

            // 处理上传文件
            $array = $this->dealUploadFile($files, $name);

            if ('' === $name) {
                // 获取全部文件
                return $array;
            } elseif (isset($sub) && isset($array[$name][$sub])) {
                return $array[$name][$sub];
            } elseif (isset($array[$name])) {
                return $array[$name];
            }
        }
    }

    protected function dealUploadFile(array $files, string $name): array
    {
        $array = [];
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $item  = [];
                $keys  = array_keys($file);
                $count = count($file['name']);

                for ($i = 0; $i < $count; $i++) {
                    if ($file['error'][$i] > 0) {
                        if ($name == $key) {
                            $this->throwUploadFileError($file['error'][$i]);
                        } else {
                            continue;
                        }
                    }

                    $temp['key'] = $key;

                    foreach ($keys as $_key) {
                        $temp[$_key] = $file[$_key][$i];
                    }

                    $item[] = new UploadedFile($temp['tmp_name'], $temp['name'], $temp['type'], $temp['error']);
                }

                $array[$key] = $item;
            } else {
                if ($file instanceof File) {
                    $array[$key] = $file;
                } else {
                    if ($file['error'] > 0) {
                        if ($key == $name) {
                            $this->throwUploadFileError($file['error']);
                        } else {
                            continue;
                        }
                    }

                    $array[$key] = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);
                }
            }
        }

        return $array;
    }

    protected function throwUploadFileError($error)
    {
        static $fileUploadErrors = [
            1 => 'upload File size exceeds the maximum value',
            2 => 'upload File size exceeds the maximum value',
            3 => 'only the portion of file is uploaded',
            4 => 'no file to uploaded',
            6 => 'upload temp dir not found',
            7 => 'file write error',
        ];

        $msg = $fileUploadErrors[$error];
        throw new Exception($msg, $error);
    }

    /**
     * 设置或者获取当前的Header
     * @access public
     * @param  string $name header名称
     * @param  string $default 默认值
     * @return string|array
     */
    public function header(string $name = '', string $default = null)
    {
        if ('' === $name) {
            return $this->header;
        }

        $name = str_replace('_', '-', strtolower($name));

        return $this->header[$name] ?? $default;
    }

    /**
     * 获取变量 支持过滤和默认值
     * @access public
     * @param  array        $data 数据源
     * @param  string|false $name 字段名
     * @param  mixed        $default 默认值
     * @param  string|array $filter 过滤函数
     * @return mixed
     */
    public function input(array $data = [], $name = '', $default = null, $filter = '')
    {
        if (false === $name) {
            // 获取原始数据
            return $data;
        }

        $name = (string) $name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                list($name, $type) = explode('/', $name);
            }

            $data = $this->getData($data, $name);

            if (is_null($data)) {
                return $default;
            }

            if (is_object($data)) {
                return $data;
            }
        }

        $data = $this->filterData($data, $filter, $name, $default);

        if (isset($type) && $data !== $default) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }

        return $data;
    }

    protected function filterData($data, $filter, $name, $default)
    {
        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
        } else {
            $this->filterValue($data, $name, $filter);
        }

        return $data;
    }

    /**
     * 强制类型转换
     * @access public
     * @param  mixed  $data
     * @param  string $type
     * @return mixed
     */
    private function typeCast(&$data, string $type)
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array) $data;
                break;
            // 数字
            case 'd':
                $data = (int) $data;
                break;
            // 浮点
            case 'f':
                $data = (float) $data;
                break;
            // 布尔
            case 'b':
                $data = (boolean) $data;
                break;
            // 字符串
            case 's':
                if (is_scalar($data)) {
                    $data = (string) $data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
                break;
        }
    }

    /**
     * 获取数据
     * @access public
     * @param  array  $data 数据源
     * @param  string $name 字段名
     * @param  mixed  $default 默认值
     * @return mixed
     */
    protected function getData(array $data, string $name, $default = null)
    {
        foreach (explode('.', $name) as $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                return $default;
            }
        }

        return $data;
    }

    /**
     * 设置或获取当前的过滤规则
     * @access public
     * @param  mixed $filter 过滤规则
     * @return mixed
     */
    public function filter($filter = null)
    {
        if (is_null($filter)) {
            return $this->filter;
        }

        $this->filter = $filter;

        return $this;
    }

    protected function getFilter($filter, $default): array
    {
        if (is_null($filter)) {
            $filter = [];
        } else {
            $filter = $filter ?: $this->filter;
            if (is_string($filter) && false === strpos($filter, '/')) {
                $filter = explode(',', $filter);
            } else {
                $filter = (array) $filter;
            }
        }

        $filter[] = $default;

        return $filter;
    }

    /**
     * 递归过滤给定的值
     * @access public
     * @param  mixed $value 键值
     * @param  mixed $key 键名
     * @param  array $filters 过滤方法+默认值
     * @return mixed
     */
    public function filterValue(&$value, $key, $filters)
    {
        $default = array_pop($filters);

        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            } elseif (is_scalar($value)) {
                if (is_string($filter) && false !== strpos($filter, '/')) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } elseif (!empty($filter)) {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        $value = $default;
                        break;
                    }
                }
            }
        }

        return $value;
    }

    /**
     * 是否存在某个请求参数
     * @access public
     * @param  string $name 变量名
     * @param  string $type 变量类型
     * @param  bool   $checkEmpty 是否检测空值
     * @return bool
     */
    public function has(string $name, string $type = 'param', bool $checkEmpty = false): bool
    {
        if (!in_array($type, ['param', 'get', 'post', 'put', 'patch', 'route', 'delete', 'cookie', 'session', 'env', 'request', 'server', 'header', 'file'])) {
            return false;
        }

        $param = empty($this->$type) ? $this->$type() : $this->$type;

        if (is_object($param)) {
            return $param->has($name);
        }

        // 按.拆分成多维数组进行判断
        foreach (explode('.', $name) as $val) {
            if (isset($param[$val])) {
                $param = $param[$val];
            } else {
                return false;
            }
        }

        return ($checkEmpty && '' === $param) ? false : true;
    }

    /**
     * 获取指定的参数
     * @access public
     * @param  array        $name 变量名
     * @param  mixed        $data 数据或者变量类型
     * @param  string|array $filter 过滤方法
     * @return array
     */
    public function only(array $name, $data = 'param', $filter = ''): array
    {
        $data = is_array($data) ? $data : $this->$data();

        $item = [];
        foreach ($name as $key => $val) {

            if (is_int($key)) {
                $default = null;
                $key     = $val;
                if (!isset($data[$key])) {
                    continue;
                }
            } else {
                $default = $val;
            }

            $item[$key] = $this->filterData($data[$key] ?? $default, $filter, $key, $default);
        }

        return $item;
    }

    /**
     * 排除指定参数获取
     * @access public
     * @param  array  $name 变量名
     * @param  string $type 变量类型
     * @return mixed
     */
    public function except(array $name, string $type = 'param'): array
    {
        $param = $this->$type();

        foreach ($name as $key) {
            if (isset($param[$key])) {
                unset($param[$key]);
            }
        }

        return $param;
    }

    /**
     * 当前是否ssl
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
        } elseif ($this->httpsAgentName && $this->server($this->httpsAgentName)) {
            return true;
        }

        return false;
    }

    /**
     * 当前是否JSON请求
     * @access public
     * @return bool
     */
    public function isJson(): bool
    {
        $acceptType = $this->type();

        return false !== strpos($acceptType, 'json');
    }

    /**
     * 当前是否Ajax请求
     * @access public
     * @param  bool $ajax true 获取原始ajax请求
     * @return bool
     */
    public function isAjax(bool $ajax = false): bool
    {
        $value  = $this->server('HTTP_X_REQUESTED_WITH');
        $result = $value && 'xmlhttprequest' == strtolower($value) ? true : false;

        if (true === $ajax) {
            return $result;
        }

        return $this->param($this->varAjax) ? true : $result;
    }

    /**
     * 当前是否Pjax请求
     * @access public
     * @param  bool $pjax true 获取原始pjax请求
     * @return bool
     */
    public function isPjax(bool $pjax = false): bool
    {
        $result = !empty($this->server('HTTP_X_PJAX')) ? true : false;

        if (true === $pjax) {
            return $result;
        }

        return $this->param($this->varPjax) ? true : $result;
    }

    /**
     * 获取客户端IP地址
     * @access public
     * @return string
     */
    public function ip(): string
    {
        if (!empty($this->realIP)) {
            return $this->realIP;
        }

        $this->realIP = $this->server('REMOTE_ADDR', '');

        // 如果指定了前端代理服务器IP以及其会发送的IP头
        // 则尝试获取前端代理服务器发送过来的真实IP
        $proxyIp       = $this->proxyServerIp;
        $proxyIpHeader = $this->proxyServerIpHeader;

        if (count($proxyIp) > 0 && count($proxyIpHeader) > 0) {
            // 从指定的HTTP头中依次尝试获取IP地址
            // 直到获取到一个合法的IP地址
            foreach ($proxyIpHeader as $header) {
                $tempIP = $this->server($header);

                if (empty($tempIP)) {
                    continue;
                }

                $tempIP = trim(explode(',', $tempIP)[0]);

                if (!$this->isValidIP($tempIP)) {
                    $tempIP = null;
                } else {
                    break;
                }
            }

            // tempIP不为空，说明获取到了一个IP地址
            // 这时我们检查 REMOTE_ADDR 是不是指定的前端代理服务器之一
            // 如果是的话说明该 IP头 是由前端代理服务器设置的
            // 否则则是伪装的
            if (!empty($tempIP)) {
                $realIPBin = $this->ip2bin($this->realIP);

                foreach ($proxyIp as $ip) {
                    $serverIPElements = explode('/', $ip);
                    $serverIP         = $serverIPElements[0];
                    $serverIPPrefix   = $serverIPElements[1] ?? 128;
                    $serverIPBin      = $this->ip2bin($serverIP);

                    // IP类型不符
                    if (strlen($realIPBin) !== strlen($serverIPBin)) {
                        continue;
                    }

                    if (strncmp($realIPBin, $serverIPBin, (int) $serverIPPrefix) === 0) {
                        $this->realIP = $tempIP;
                        break;
                    }
                }
            }
        }

        if (!$this->isValidIP($this->realIP)) {
            $this->realIP = '0.0.0.0';
        }

        return $this->realIP;
    }

    /**
     * 检测是否是合法的IP地址
     *
     * @param string $ip   IP地址
     * @param string $type IP地址类型 (ipv4, ipv6)
     *
     * @return boolean
     */
    public function isValidIP(string $ip, string $type = ''): bool
    {
        switch (strtolower($type)) {
            case 'ipv4':
                $flag = FILTER_FLAG_IPV4;
                break;
            case 'ipv6':
                $flag = FILTER_FLAG_IPV6;
                break;
            default:
                $flag = null;
                break;
        }

        return boolval(filter_var($ip, FILTER_VALIDATE_IP, $flag));
    }

    /**
     * 将IP地址转换为二进制字符串
     *
     * @param string $ip
     *
     * @return string
     */
    public function ip2bin(string $ip): string
    {
        if ($this->isValidIP($ip, 'ipv6')) {
            $IPHex = str_split(bin2hex(inet_pton($ip)), 4);
            foreach ($IPHex as $key => $value) {
                $IPHex[$key] = intval($value, 16);
            }
            $IPBin = vsprintf('%016b%016b%016b%016b%016b%016b%016b%016b', $IPHex);
        } else {
            $IPHex = str_split(bin2hex(inet_pton($ip)), 2);
            foreach ($IPHex as $key => $value) {
                $IPHex[$key] = intval($value, 16);
            }
            $IPBin = vsprintf('%08b%08b%08b%08b', $IPHex);
        }

        return $IPBin;
    }

    /**
     * 检测是否使用手机访问
     * @access public
     * @return bool
     */
    public function isMobile(): bool
    {
        if ($this->server('HTTP_VIA') && stristr($this->server('HTTP_VIA'), "wap")) {
            return true;
        } elseif ($this->server('HTTP_ACCEPT') && strpos(strtoupper($this->server('HTTP_ACCEPT')), "VND.WAP.WML")) {
            return true;
        } elseif ($this->server('HTTP_X_WAP_PROFILE') || $this->server('HTTP_PROFILE')) {
            return true;
        } elseif ($this->server('HTTP_USER_AGENT') && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $this->server('HTTP_USER_AGENT'))) {
            return true;
        }

        return false;
    }

    /**
     * 当前URL地址中的scheme参数
     * @access public
     * @return string
     */
    public function scheme(): string
    {
        return $this->isSsl() ? 'https' : 'http';
    }

    /**
     * 当前请求URL地址中的query参数
     * @access public
     * @return string
     */
    public function query(): string
    {
        return $this->server('QUERY_STRING', '');
    }

    /**
     * 设置当前请求的host（包含端口）
     * @access public
     * @param  string $host 主机名（含端口）
     * @return $this
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * 当前请求的host
     * @access public
     * @param bool $strict  true 仅仅获取HOST
     * @return string
     */
    public function host(bool $strict = false): string
    {
        if ($this->host) {
            $host = $this->host;
        } else {
            $host = strval($this->server('HTTP_X_REAL_HOST') ?: $this->server('HTTP_HOST'));
        }

        return true === $strict && strpos($host, ':') ? strstr($host, ':', true) : $host;
    }

    /**
     * 当前请求URL地址中的port参数
     * @access public
     * @return int
     */
    public function port(): int
    {
        return (int) $this->server('SERVER_PORT', '');
    }

    /**
     * 当前请求 SERVER_PROTOCOL
     * @access public
     * @return string
     */
    public function protocol(): string
    {
        return $this->server('SERVER_PROTOCOL', '');
    }

    /**
     * 当前请求 REMOTE_PORT
     * @access public
     * @return int
     */
    public function remotePort(): int
    {
        return (int) $this->server('REMOTE_PORT', '');
    }

    /**
     * 当前请求 HTTP_CONTENT_TYPE
     * @access public
     * @return string
     */
    public function contentType(): string
    {
        $contentType = $this->server('CONTENT_TYPE');

        if ($contentType) {
            if (strpos($contentType, ';')) {
                list($type) = explode(';', $contentType);
            } else {
                $type = $contentType;
            }
            return trim($type);
        }

        return '';
    }

    /**
     * 获取当前请求的安全Key
     * @access public
     * @return string
     */
    public function secureKey(): string
    {
        if (is_null($this->secureKey)) {
            $this->secureKey = uniqid('', true);
        }

        return $this->secureKey;
    }

    /**
     * 设置当前的控制器名
     * @access public
     * @param  string $controller 控制器名
     * @return $this
     */
    public function setController(string $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * 设置当前的操作名
     * @access public
     * @param  string $action 操作名
     * @return $this
     */
    public function setAction(string $action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * 获取当前的控制器名
     * @access public
     * @param  bool $convert 转换为小写
     * @return string
     */
    public function controller(bool $convert = false): string
    {
        $name = $this->controller ?: '';
        return $convert ? strtolower($name) : $name;
    }

    /**
     * 获取当前的操作名
     * @access public
     * @param  bool $convert 转换为小写
     * @return string
     */
    public function action(bool $convert = false): string
    {
        $name = $this->action ?: '';
        return $convert ? strtolower($name) : $name;
    }

    /**
     * 设置或者获取当前请求的content
     * @access public
     * @return string
     */
    public function getContent(): string
    {
        if (is_null($this->content)) {
            $this->content = $this->input;
        }

        return $this->content;
    }

    /**
     * 获取当前请求的php://input
     * @access public
     * @return string
     */
    public function getInput(): string
    {
        return $this->input;
    }

    /**
     * 生成请求令牌
     * @access public
     * @param  string $name 令牌名称
     * @param  mixed  $type 令牌生成方法
     * @return string
     */
    public function buildToken(string $name = '__token__', $type = 'md5'): string
    {
        $type  = is_callable($type) ? $type : 'md5';
        $token = call_user_func($type, $this->server('REQUEST_TIME_FLOAT'));

        $this->session->set($name, $token);

        return $token;
    }

    /**
     * 检查请求令牌
     * @access public
     * @param  string $token 令牌名称
     * @param  array  $data  表单数据
     * @return bool
     */
    public function checkToken(string $token = '__token__', array $data = []): bool
    {
        if (in_array($this->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        if (!$this->session->has($token)) {
            // 令牌数据无效
            return false;
        }

        // Header验证
        if ($this->header('X-CSRF-TOKEN') && $this->session->get($token) === $this->header('X-CSRF-TOKEN')) {
            // 防止重复提交
            $this->session->delete($token); // 验证完成销毁session
            return true;
        }

        if (empty($data)) {
            $data = $this->post();
        }

        // 令牌验证
        if (isset($data[$token]) && $this->session->get($token) === $data[$token]) {
            // 防止重复提交
            $this->session->delete($token); // 验证完成销毁session
            return true;
        }

        // 开启TOKEN重置
        $this->session->delete($token);
        return false;
    }

    /**
     * 设置在中间件传递的数据
     * @access public
     * @param  array $middleware 数据
     * @return $this
     */
    public function withMiddleware(array $middleware)
    {
        $this->middleware = array_merge($this->middleware, $middleware);
        return $this;
    }

    /**
     * 设置GET数据
     * @access public
     * @param  array $get 数据
     * @return $this
     */
    public function withGet(array $get)
    {
        $this->get = $get;
        return $this;
    }

    /**
     * 设置POST数据
     * @access public
     * @param  array $post 数据
     * @return $this
     */
    public function withPost(array $post)
    {
        $this->post = $post;
        return $this;
    }

    /**
     * 设置COOKIE数据
     * @access public
     * @param array $cookie 数据
     * @return $this
     */
    public function withCookie(array $cookie)
    {
        $this->cookie = $cookie;
        return $this;
    }

    /**
     * 设置SESSION数据
     * @access public
     * @param Session $session 数据
     * @return $this
     */
    public function withSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * 设置SERVER数据
     * @access public
     * @param  array $server 数据
     * @return $this
     */
    public function withServer(array $server)
    {
        $this->server = array_change_key_case($server, CASE_UPPER);
        return $this;
    }

    /**
     * 设置HEADER数据
     * @access public
     * @param  array $header 数据
     * @return $this
     */
    public function withHeader(array $header)
    {
        $this->header = array_change_key_case($header);
        return $this;
    }

    /**
     * 设置ENV数据
     * @access public
     * @param Env $env 数据
     * @return $this
     */
    public function withEnv(Env $env)
    {
        $this->env = $env;
        return $this;
    }

    /**
     * 设置php://input数据
     * @access public
     * @param  string $input RAW数据
     * @return $this
     */
    public function withInput(string $input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * 设置文件上传数据
     * @access public
     * @param  array $files 上传信息
     * @return $this
     */
    public function withFiles(array $files)
    {
        $this->file = $files;
        return $this;
    }

    /**
     * 设置ROUTE变量
     * @access public
     * @param  array $route 数据
     * @return $this
     */
    public function withRoute(array $route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * 设置中间传递数据
     * @access public
     * @param  string    $name  参数名
     * @param  mixed     $value 值
     */
    public function __set(string $name, $value)
    {
        $this->middleware[$name] = $value;
    }

    /**
     * 获取中间传递数据的值
     * @access public
     * @param  string $name 名称
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->middleware($name);
    }

    /**
     * 检测中间传递数据的值
     * @access public
     * @param  string $name 名称
     * @return boolean
     */
    public function __isset(string $name): bool
    {
        return isset($this->middleware[$name]);
    }
}
