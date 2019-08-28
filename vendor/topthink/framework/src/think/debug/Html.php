<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);
namespace think\debug;

use think\App;
use think\Response;

/**
 * 页面Trace调试
 */
class Html
{
    protected $config = [
        'file' => '',
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
     * @param  App      $app 应用实例
     * @param  Response $response Response对象
     * @param  array    $log 日志信息
     * @return bool|string
     */
    public function output(App $app, Response $response, array $log = [])
    {
        $request = $app->request;

        $contentType = $response->getHeader('Content-Type');
        $accept      = $request->header('accept');
        if (strpos($accept, 'application/json') === 0 || $request->isAjax()) {
            return false;
        } elseif (!empty($contentType) && strpos($contentType, 'html') === false) {
            return false;
        }

        // 获取基本信息
        $runtime = number_format(microtime(true) - $app->getBeginTime(), 10, '.', '');
        $reqs    = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $mem     = number_format((memory_get_usage() - $app->getBeginMem()) / 1024, 2);

        // 页面Trace信息
        if ($request->host()) {
            $uri = $request->protocol() . ' ' . $request->method() . ' : ' . $request->url(true);
        } else {
            $uri = 'cmd:' . implode(' ', $_SERVER['argv']);
        }

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
        // 调用Trace页面模板
        ob_start();
        include $this->config['file'] ?: __DIR__ . '/../../tpl/page_trace.tpl';
        return ob_get_clean();
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
