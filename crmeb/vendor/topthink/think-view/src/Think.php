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

namespace think\view\driver;

use think\App;
use think\helper\Str;
use think\Template;
use think\template\exception\TemplateNotFoundException;

class Think
{
    // 模板引擎实例
    private $template;
    private $app;

    // 模板引擎参数
    protected $config = [
        // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
        'auto_rule'     => 1,
        // 视图目录名
        'view_dir_name' => 'view',
        // 模板起始路径
        'view_path'     => '',
        // 模板文件后缀
        'view_suffix'   => 'html',
        // 模板文件名分隔符
        'view_depr'     => DIRECTORY_SEPARATOR,
        // 是否开启模板编译缓存,设为false则每次都会重新编译
        'tpl_cache'     => true,
    ];

    public function __construct(App $app, array $config = [])
    {
        $this->app = $app;

        $this->config = array_merge($this->config, (array) $config);

        if (empty($this->config['cache_path'])) {
            $this->config['cache_path'] = $app->getRuntimePath() . 'temp' . DIRECTORY_SEPARATOR;
        }

        $this->template = new Template($this->config);
        $this->template->setCache($app->cache);
        $this->template->extend('$Think', function (array $vars) {
            $type  = strtoupper(trim(array_shift($vars)));
            $param = implode('.', $vars);

            switch ($type) {
                case 'CONST':
                    $parseStr = strtoupper($param);
                    break;
                case 'CONFIG':
                    $parseStr = 'config(\'' . $param . '\')';
                    break;
                case 'LANG':
                    $parseStr = 'lang(\'' . $param . '\')';
                    break;
                case 'NOW':
                    $parseStr = "date('Y-m-d g:i a',time())";
                    break;
                case 'LDELIM':
                    $parseStr = '\'' . ltrim($this->getConfig('tpl_begin'), '\\') . '\'';
                    break;
                case 'RDELIM':
                    $parseStr = '\'' . ltrim($this->getConfig('tpl_end'), '\\') . '\'';
                    break;
                default:
                    $parseStr = defined($type) ? $type : '\'\'';
            }

            return $parseStr;
        });

        $this->template->extend('$Request', function (array $vars) {
            // 获取Request请求对象参数
            $method = array_shift($vars);
            if (!empty($vars)) {
                $params = implode('.', $vars);
                if ('true' != $params) {
                    $params = '\'' . $params . '\'';
                }
            } else {
                $params = '';
            }

            return 'app(\'request\')->' . $method . '(' . $params . ')';
        });
    }

    /**
     * 检测是否存在模板文件
     * @access public
     * @param  string $template 模板文件或者模板规则
     * @return bool
     */
    public function exists(string $template): bool
    {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }

        return is_file($template);
    }

    /**
     * 渲染模板文件
     * @access public
     * @param  string $template 模板文件
     * @param  array  $data 模板变量
     * @return void
     */
    public function fetch(string $template, array $data = []): void
    {
        if (empty($this->config['view_path'])) {
            $view = $this->config['view_dir_name'];

            if (is_dir($this->app->getAppPath() . $view)) {
                $path = $this->app->getAppPath() . $view . DIRECTORY_SEPARATOR;
            } else {
                $appName = $this->app->http->getName();
                $path    = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . ($appName ? $appName . DIRECTORY_SEPARATOR : '');
            }

            $this->config['view_path'] = $path;
            $this->template->view_path = $path;
        }

        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }

        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new TemplateNotFoundException('template not exists:' . $template, $template);
        }

        // 记录视图信息
        $this->app['log']
            ->record('[ VIEW ] ' . $template . ' [ ' . var_export(array_keys($data), true) . ' ]');

        $this->template->fetch($template, $data);
    }

    /**
     * 渲染模板内容
     * @access public
     * @param  string $template 模板内容
     * @param  array  $data 模板变量
     * @return void
     */
    public function display(string $template, array $data = []): void
    {
        $this->template->display($template, $data);
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param  string $template 模板文件规则
     * @return string
     */
    private function parseTemplate(string $template): string
    {
        // 分析模板文件规则
        $request = $this->app['request'];

        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨模块调用
            list($app, $template) = explode('@', $template);
        }

        if (isset($app)) {
            $view     = $this->config['view_dir_name'];
            $viewPath = $this->app->getBasePath() . $app . DIRECTORY_SEPARATOR . $view . DIRECTORY_SEPARATOR;

            if (is_dir($viewPath)) {
                $path = $viewPath;
            } else {
                $path = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR;
            }

            $this->template->view_path = $path;
        } else {
            $path = $this->config['view_path'];
        }

        $depr = $this->config['view_depr'];

        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = $request->controller();

            if (strpos($controller, '.')) {
                $pos        = strrpos($controller, '.');
                $controller = substr($controller, 0, $pos) . '.' . Str::snake(substr($controller, $pos + 1));
            } else {
                $controller = Str::snake($controller);
            }

            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认模板渲染规则定位
                    if (2 == $this->config['auto_rule']) {
                        $template = $request->action(true);
                    } elseif (3 == $this->config['auto_rule']) {
                        $template = $request->action();
                    } else {
                        $template = Str::snake($request->action());
                    }

                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }

        return $path . ltrim($template, '/') . '.' . ltrim($this->config['view_suffix'], '.');
    }

    /**
     * 配置模板引擎
     * @access private
     * @param  array  $config 参数
     * @return void
     */
    public function config(array $config): void
    {
        $this->template->config($config);
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取模板引擎配置
     * @access public
     * @param  string  $name 参数名
     * @return void
     */
    public function getConfig(string $name)
    {
        return $this->template->getConfig($name);
    }

    public function __call($method, $params)
    {
        return call_user_func_array([$this->template, $method], $params);
    }
}
