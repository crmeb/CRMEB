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

namespace think\facade;

use think\Facade;
use think\file\UploadedFile;
use think\route\Rule;

/**
 * @see \think\Request
 * @package think\facade
 * @mixin \think\Request
 * @method static \think\Request setDomain(string $domain) 设置当前包含协议的域名
 * @method static string domain(bool $port = false) 获取当前包含协议的域名
 * @method static string rootDomain() 获取当前根域名
 * @method static \think\Request setSubDomain(string $domain) 设置当前泛域名的值
 * @method static string subDomain() 获取当前子域名
 * @method static \think\Request setPanDomain(string $domain) 设置当前泛域名的值
 * @method static string panDomain() 获取当前泛域名的值
 * @method static \think\Request setUrl(string $url) 设置当前完整URL 包括QUERY_STRING
 * @method static string url(bool $complete = false) 获取当前完整URL 包括QUERY_STRING
 * @method static \think\Request setBaseUrl(string $url) 设置当前URL 不含QUERY_STRING
 * @method static string baseUrl(bool $complete = false) 获取当前URL 不含QUERY_STRING
 * @method static string baseFile(bool $complete = false) 获取当前执行的文件 SCRIPT_NAME
 * @method static \think\Request setRoot(string $url) 设置URL访问根地址
 * @method static string root(bool $complete = false) 获取URL访问根地址
 * @method static string rootUrl() 获取URL访问根目录
 * @method static \think\Request setPathinfo(string $pathinfo) 设置当前请求的pathinfo
 * @method static string pathinfo() 获取当前请求URL的pathinfo信息（含URL后缀）
 * @method static string ext() 当前URL的访问后缀
 * @method static integer|float time(bool $float = false) 获取当前请求的时间
 * @method static string type() 当前请求的资源类型
 * @method static void mimeType(string|array $type, string $val = '') 设置资源类型
 * @method static \think\Request setMethod(string $method) 设置请求类型
 * @method static string method(bool $origin = false) 当前的请求类型
 * @method static bool isGet() 是否为GET请求
 * @method static bool isPost() 是否为POST请求
 * @method static bool isPut() 是否为PUT请求
 * @method static bool isDelete() 是否为DELTE请求
 * @method static bool isHead() 是否为HEAD请求
 * @method static bool isPatch() 是否为PATCH请求
 * @method static bool isOptions() 是否为OPTIONS请求
 * @method static bool isCli() 是否为cli
 * @method static bool isCgi() 是否为cgi
 * @method static mixed param(string|array $name = '', mixed $default = null, string|array $filter = '') 获取当前请求的参数
 * @method static \think\Request setRule(Rule $rule) 设置路由变量
 * @method static Rule|null rule() 获取当前路由对象
 * @method static \think\Request setRoute(array $route) 设置路由变量
 * @method static mixed route(string|array $name = '', mixed $default = null, string|array $filter = '') 获取路由参数
 * @method static mixed get(string|array $name = '', mixed $default = null, string|array $filter = '') 获取GET参数
 * @method static mixed middleware(mixed $name, mixed $default = null) 获取中间件传递的参数
 * @method static mixed post(string|array $name = '', mixed $default = null, string|array $filter = '') 获取POST参数
 * @method static mixed put(string|array $name = '', mixed $default = null, string|array $filter = '') 获取PUT参数
 * @method static mixed delete(mixed $name = '', mixed $default = null, string|array $filter = '') 设置获取DELETE参数
 * @method static mixed patch(mixed $name = '', mixed $default = null, string|array $filter = '') 设置获取PATCH参数
 * @method static mixed request(string|array $name = '', mixed $default = null, string|array $filter = '') 获取request变量
 * @method static mixed env(string $name = '', string $default = null) 获取环境变量
 * @method static mixed session(string $name = '', string $default = null) 获取session数据
 * @method static mixed cookie(mixed $name = '', string $default = null, string|array $filter = '') 获取cookie参数
 * @method static mixed server(string $name = '', string $default = '') 获取server参数
 * @method static null|array|UploadedFile file(string $name = '') 获取上传的文件信息
 * @method static string|array header(string $name = '', string $default = null) 设置或者获取当前的Header
 * @method static mixed input(array $data = [], string|false $name = '', mixed $default = null, string|array $filter = '') 获取变量 支持过滤和默认值
 * @method static mixed filter(mixed $filter = null) 设置或获取当前的过滤规则
 * @method static mixed filterValue(mixed &$value, mixed $key, array $filters) 递归过滤给定的值
 * @method static bool has(string $name, string $type = 'param', bool $checkEmpty = false) 是否存在某个请求参数
 * @method static array only(array $name, mixed $data = 'param', string|array $filter = '') 获取指定的参数
 * @method static mixed except(array $name, string $type = 'param') 排除指定参数获取
 * @method static bool isSsl() 当前是否ssl
 * @method static bool isJson() 当前是否JSON请求
 * @method static bool isAjax(bool $ajax = false) 当前是否Ajax请求
 * @method static bool isPjax(bool $pjax = false) 当前是否Pjax请求
 * @method static string ip() 获取客户端IP地址
 * @method static boolean isValidIP(string $ip, string $type = '') 检测是否是合法的IP地址
 * @method static string ip2bin(string $ip) 将IP地址转换为二进制字符串
 * @method static bool isMobile() 检测是否使用手机访问
 * @method static string scheme() 当前URL地址中的scheme参数
 * @method static string query() 当前请求URL地址中的query参数
 * @method static \think\Request setHost(string $host) 设置当前请求的host（包含端口）
 * @method static string host(bool $strict = false) 当前请求的host
 * @method static int port() 当前请求URL地址中的port参数
 * @method static string protocol() 当前请求 SERVER_PROTOCOL
 * @method static int remotePort() 当前请求 REMOTE_PORT
 * @method static string contentType() 当前请求 HTTP_CONTENT_TYPE
 * @method static string secureKey() 获取当前请求的安全Key
 * @method static \think\Request setController(string $controller) 设置当前的控制器名
 * @method static \think\Request setAction(string $action) 设置当前的操作名
 * @method static string controller(bool $convert = false) 获取当前的控制器名
 * @method static string action(bool $convert = false) 获取当前的操作名
 * @method static string getContent() 设置或者获取当前请求的content
 * @method static string getInput() 获取当前请求的php://input
 * @method static string buildToken(string $name = '__token__', mixed $type = 'md5') 生成请求令牌
 * @method static bool checkToken(string $token = '__token__', array $data = []) 检查请求令牌
 * @method static \think\Request withMiddleware(array $middleware) 设置在中间件传递的数据
 * @method static \think\Request withGet(array $get) 设置GET数据
 * @method static \think\Request withPost(array $post) 设置POST数据
 * @method static \think\Request withCookie(array $cookie) 设置COOKIE数据
 * @method static \think\Request withSession(Session $session) 设置SESSION数据
 * @method static \think\Request withServer(array $server) 设置SERVER数据
 * @method static \think\Request withHeader(array $header) 设置HEADER数据
 * @method static \think\Request withEnv(Env $env) 设置ENV数据
 * @method static \think\Request withInput(string $input) 设置php://input数据
 * @method static \think\Request withFiles(array $files) 设置文件上传数据
 * @method static \think\Request withRoute(array $route) 设置ROUTE变量
 * @method static mixed __set(string $name, mixed $value) 设置中间传递数据
 * @method static mixed __get(string $name) 获取中间传递数据的值
 * @method static boolean __isset(string $name) 检测中间传递数据的值
 * @method static bool offsetExists($name)
 * @method static mixed offsetGet($name)
 * @method static mixed offsetSet($name, $value)
 * @method static mixed offsetUnset($name)
 */
class Request extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'request';
    }
}
