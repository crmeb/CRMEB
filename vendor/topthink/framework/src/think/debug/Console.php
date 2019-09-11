<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);
namespace think\debug;

use think\App;
use think\Response;

/**
 * 浏览器调试输出
 */
class Console
{
    protected $config = [
        'tabs' => ['base' => '基本', 'file' => '文件', 'info' => '流程', 'notice|error' => '错误', 'sql' => 'SQL', 'debug|log' => '调试'],
    ];

    // 实例化并传入参数
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 调试输出接口
     * @access public
     * @param  Response  $response Response对象
     * @param  array     $log 日志信息
     * @return string|bool
     */
    public function output(App $app, Response $response, array $log = [])
    {
        $request     = $app->request;
        $contentType = $response->getHeader('Content-Type');
        $accept      = $request->header('accept');
        if (strpos($accept, 'application/json') === 0 || $request->isAjax()) {
            return false;
        } elseif (!empty($contentType) && strpos($contentType, 'html') === false) {
            return false;
        }
        // 获取基本信息
        $runtime = number_format(microtime(true) - $app->getBeginTime(), 10);
        $reqs    = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $mem     = number_format((memory_get_usage() - $app->getBeginMem()) / 1024, 2);

        if ($request->host()) {
            $uri = $request->protocol() . ' ' . $request->method() . ' : ' . $request->url(true);
        } else {
            $uri = 'cmd:' . implode(' ', $_SERVER['argv']);
        }

        // 页面Trace信息
        $base = [
            '请求信息' => date('Y-m-d H:i:s', $request->time()) . ' ' . $uri,
            '运行时间' => number_format((float) $runtime, 6) . 's [ 吞吐率：' . $reqs . 'req/s ] 内存消耗：' . $mem . 'kb 文件加载：' . count(get_included_files()),
            '查询信息' => $app->db->getQueryTimes() . ' queries',
            '缓存信息' => $app->cache->getReadTimes() . ' reads,' . $app->cache->getWriteTimes() . ' writes',
        ];

        if ($app->session->getId(false)) {
            $base['会话信息'] = 'SESSION_ID=' . $app->session->getId();
        }

        $info = $this->getFileInfo();

        // 页面Trace信息
        $trace = [];
        foreach ($this->config['tabs'] as $name => $title) {
            $name = strtolower($name);
            switch ($name) {
                case 'base': // 基本信息
                    $trace[$title] = $base;
                    break;
                case 'file': // 文件信息
                    $trace[$title] = $info;
                    break;
                default: // 调试信息
                    if (strpos($name, '|')) {
                        // 多组信息
                        $names  = explode('|', $name);
                        $result = [];
                        foreach ($names as $item) {
                            $result = array_merge($result, $log[$item] ?? []);
                        }
                        $trace[$title] = $result;
                    } else {
                        $trace[$title] = $log[$name] ?? '';
                    }
            }
        }

        //输出到控制台
        $lines = '';
        foreach ($trace as $type => $msg) {
            $lines .= $this->console($type, $msg);
        }
        $js = <<<JS

<script type='text/javascript'>
{$lines}
</script>
JS;
        return $js;
    }

    protected function console(string $type, $msg)
    {
        $type       = strtolower($type);
        $trace_tabs = array_values($this->config['tabs']);
        $line       = [];
        $line[]     = ($type == $trace_tabs[0] || '调试' == $type || '错误' == $type)
        ? "console.group('{$type}');"
        : "console.groupCollapsed('{$type}');";

        foreach ((array) $msg as $key => $m) {
            switch ($type) {
                case '调试':
                    $var_type = gettype($m);
                    if (in_array($var_type, ['array', 'string'])) {
                        $line[] = "console.log(" . json_encode($m) . ");";
                    } else {
                        $line[] = "console.log(" . json_encode(var_export($m, true)) . ");";
                    }
                    break;
                case '错误':
                    $msg    = str_replace("\n", '\n', addslashes(is_scalar($m) ? $m : json_encode($m)));
                    $style  = 'color:#F4006B;font-size:14px;';
                    $line[] = "console.error(\"%c{$msg}\", \"{$style}\");";
                    break;
                case 'sql':
                    $msg    = str_replace("\n", '\n', addslashes($m));
                    $style  = "color:#009bb4;";
                    $line[] = "console.log(\"%c{$msg}\", \"{$style}\");";
                    break;
                default:
                    $m      = is_string($key) ? $key . ' ' . $m : $key + 1 . ' ' . $m;
                    $msg    = json_encode($m);
                    $line[] = "console.log({$msg});";
                    break;
            }
        }
        $line[] = "console.groupEnd();";
        return implode(PHP_EOL, $line);
    }

    /**
     * 获取文件加载信息
     * @access protected
     * @return integer|array
     */
    protected function getFileInfo()
    {
        $files = get_included_files();
        $info  = [];

        foreach ($files as $key => $file) {
            $info[] = $file . ' ( ' . number_format(filesize($file) / 1024, 2) . ' KB )';
        }

        return $info;
    }
}
