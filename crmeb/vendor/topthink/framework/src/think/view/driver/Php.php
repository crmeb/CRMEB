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

use RuntimeException;
use think\App;
use think\contract\TemplateHandlerInterface;
use think\helper\Str;

/**
 * PHP原生模板驱动
 */
class Php implements TemplateHandlerInterface
{
    protected $template;
    protected $content;
    protected $app;

    // 模板引擎参数
    protected $config = [
        // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
        'auto_rule'     => 1,
        // 视图目录名
        'view_dir_name' => 'view',
        // 应用模板路径
        'view_path'     => '',
        // 模板文件后缀
        'view_suffix'   => 'php',
        // 模板文件名分隔符
        'view_depr'     => DIRECTORY_SEPARATOR,
    ];

    public function __construct(App $app, array $config = [])
    {
        $this->app    = $app;
        $this->config = array_merge($this->config, (array) $config);
    }

    /**
     * 检测是否存在模板文件
     * @access public
     * @param string $template 模板文件或者模板规则
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
     * @param string $template 模板文件
     * @param array  $data     模板变量
     * @return void
     */
    public function fetch(string $template, array $data = []): void
    {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }

        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new RuntimeException('template not exists:' . $template);
        }

        $this->template = $template;

        extract($data, EXTR_OVERWRITE);

        include $this->template;
    }

    /**
     * 渲染模板内容
     * @access public
     * @param string $content 模板内容
     * @param array  $data    模板变量
     * @return void
     */
    public function display(string $content, array $data = []): void
    {
        $this->content = $content;

        extract($data, EXTR_OVERWRITE);
        eval('?>' . $this->content);
    }

    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    private function parseTemplate(string $template): string
    {
        $request = $this->app->request;

        // 获取视图根目录
        if (strpos($template, '@')) {
            // 跨应用调用
            [$app, $template] = explode('@', $template);
        }

        if ($this->config['view_path'] && !isset($app)) {
            $path = $this->config['view_path'];
        } else {
            $appName = isset($app) ? $app : $this->app->http->getName();
            $view    = $this->config['view_dir_name'];

            if (is_dir($this->app->getAppPath() . $view)) {
                $path = isset($app) ? $this->app->getBasePath() . ($appName ? $appName . DIRECTORY_SEPARATOR : '') . $view . DIRECTORY_SEPARATOR : $this->app->getAppPath() . $view . DIRECTORY_SEPARATOR;
            } else {
                $path = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . ($appName ? $appName . DIRECTORY_SEPARATOR : '');
            }
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
                    // 如果模板文件名为空 按照默认规则定位
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
     * @param array $config 参数
     * @return void
     */
    public function config(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 获取模板引擎配置
     * @access public
     * @param string $name 参数名
     * @return mixed
     */
    public function getConfig(string $name)
    {
        return $this->config[$name] ?? null;
    }
}
