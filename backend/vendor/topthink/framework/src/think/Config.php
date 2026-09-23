<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace think;

use Closure;

/**
 * 配置管理类
 * @package think
 */
class Config
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * 注册配置获取器
     * @var Closure[]
     */
    protected $hook = [];

    /**
     * 构造方法
     * @access public
     */
    public function __construct(protected string $path = '', protected string $ext = '.php')
    {
    }

    public static function __make(App $app)
    {
        $path = $app->getConfigPath();
        $ext  = $app->getConfigExt();

        return new static($path, $ext);
    }

    /**
     * 加载配置文件（多种格式）
     * @access public
     * @param  string $file 配置文件名
     * @param  string $name 一级配置名
     * @return array
     */
    public function load(string $file, string $name = ''): array
    {
        if (is_file($file)) {
            $filename = $file;
        } elseif (is_file($this->path . $file . $this->ext)) {
            $filename = $this->path . $file . $this->ext;
        }

        if (isset($filename)) {
            return $this->parse($filename, $name);
        }

        return $this->config;
    }

    /**
     * 解析配置文件
     * @access public
     * @param  string $file 配置文件名
     * @param  string $name 一级配置名
     * @return array
     */
    protected function parse(string $file, string $name): array
    {
        $type   = pathinfo($file, PATHINFO_EXTENSION);
        $config = [];
        $config = match ($type) {
            'php' => include $file,
            'yml', 'yaml' => function_exists('yaml_parse_file') ? yaml_parse_file($file) : [],
            'ini'         => parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [],
            'json'        => json_decode(file_get_contents($file), true),
            default       => [],
        };

        return is_array($config) ? $this->set($config, strtolower($name)) : [];
    }

    /**
     * 检测配置是否存在
     * @access public
     * @param  string $name 配置参数名（支持多级配置 .号分割）
     * @return bool
     */
    public function has(string $name): bool
    {
        if (!str_contains($name, '.') && !isset($this->config[strtolower($name)])) {
            return false;
        }

        return !is_null($this->get($name));
    }

    /**
     * 获取一级配置
     * @access protected
     * @param  string $name 一级配置名
     * @return array
     */
    protected function pull(string $name): array
    {
        return $this->config[$name] ?? [];
    }

    /**
     * 注册配置获取器
     * @access public
     * @param  Closure $callback
     * @param  string|null $key
     * @return void
     */
    public function hook(Closure $callback, ?string $key = null)
    {
        $this->hook[$key ?? 'global'] = $callback;
    }

    /**
     * 获取配置参数 为空则获取所有配置
     * @access public
     * @param  string $name    配置参数名（支持多级配置 .号分割）
     * @param  mixed  $default 默认值
     * @return mixed
     */
    public function get(?string $name = null, $default = null)
    {
        // 无参数时获取所有
        if (empty($name)) {
            return $this->config;
        }

        if (!str_contains($name, '.')) {
            $name   = strtolower($name);
            $result = $this->pull($name);
            return $this->hook ? $this->lazy($name, $result, []) : $result;
        }

        $item    = explode('.', $name);
        $item[0] = strtolower($item[0]);
        $config  = $this->config;

        foreach ($item as $val) {
            if (isset($config[$val])) {
                $config = $config[$val];
            } else {
                return $this->hook ? $this->lazy($name, null, $default) : $default;
            }
        }

        return $this->hook ? $this->lazy($name, $config, $default) : $config;
    }

    /**
     * 通过获取器加载配置
     * @access public
     * @param  string  $name 配置参数
     * @param  mixed   $value 配置值
     * @param  mixed   $default 默认值
     * @return mixed
     */
    protected function lazy(string $name, $value = null, $default = null)
    {
        // 通过获取器返回
        $key = strpos($name, '.') ? strstr($name, '.', true) : $name;
        if (isset($this->hook[$key])) {
            $call = $this->hook[$key];
        } elseif (isset($this->hook['global'])) {
            $call = $this->hook['global'];
        }
        if (isset($call)) {
            $result = call_user_func_array($call, [$name, $value]);
            if (is_null($result)) {
                return $default;
            }
        }
        return $result ?? ($value ?: $default);
    }

    /**
     * 设置配置参数 name为数组则为批量设置
     * @access public
     * @param  array  $config 配置参数
     * @param  string $name 配置名
     * @return array
     */
    public function set(array $config, ?string $name = null): array
    {
        if (empty($name)) {
            $this->config = array_merge($this->config, array_change_key_case($config));
            return $this->config;
        }

        if (isset($this->config[$name])) {
            $result = array_merge($this->config[$name], $config);
        } else {
            $result = $config;
        }

        $this->config[$name] = $result;

        return $result;
    }
}
