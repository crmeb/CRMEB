<?php
/**
 *  +----------------------------------------------------------------------
 *  | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
 *  +----------------------------------------------------------------------
 *  | Author: CRMEB Team <admin@crmeb.com>
 *  +----------------------------------------------------------------------
 */

namespace crmeb\utils;

use think\Response;
use think\console\Output;

/**
 * 执行命令
 * Class Terminal
 * @author 等风来
 * @email 136327134@qq.com
 * @date 2023/4/13
 * @package crmeb\utils
 */
class Terminal
{
    /**
     * 命令
     * @var \string[][]
     */
    protected $command = [
        'npm-build' => [
            'run_root' => '',
            'command' => 'npm run build',
        ],
        'npm-install' => [
            'run_root' => '',
            'command' => 'npm run install',
        ],
    ];

    /**
     * 执行内容保存地址
     * @var string
     */
    protected $outputFile;

    /**
     * 执行状态
     * @var integer
     */
    protected $procStatus;

    /**
     * 响应内容
     * @var string
     */
    protected $outputContent;

    /**
     * @var
     */
    protected $output;

    /**
     * Terminal constructor.
     */
    public function __construct()
    {
        $this->command['npm-build']['run_root'] = str_replace('DS', DS, config('app.admin_template_path'));
        $this->command['npm-install']['run_root'] = str_replace('DS', DS, config('app.admin_template_path'));

        $outputDir = root_path() . 'runtime' . DIRECTORY_SEPARATOR . 'terminal';
        $this->outputFile = $outputDir . DIRECTORY_SEPARATOR . 'exec.log';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        file_put_contents($this->outputFile, '');
    }

    /**
     * @param Output $output
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/13
     */
    public function setOutput(Output $output)
    {
        $this->output = $output;
    }

    /**
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/13
     */
    public function adminTemplatePath()
    {
        return $this->command['npm-install']['run_root'];
    }

    /**
     * 执行
     * @param string $name
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/13
     */
    public function run(string $name)
    {
        if (!function_exists('proc_open')) {
            throw new \RuntimeException('缺少proc_open函数无法运行');
        }

        if (!isset($this->command[$name])) {
            throw new \RuntimeException('运行的命令不存在');
        }

        $command = $this->command[$name];

        $descriptorspec = [0 => ['pipe', 'r'], 1 => ['file', $this->outputFile, 'w'], 2 => ['file', $this->outputFile, 'w']];
        $process = proc_open($command['command'], $descriptorspec, $pipes, $command['run_root'], null, ['suppress_errors' => true]);
        if (is_resource($process)) {
            while ($this->getProcStatus($process)) {
                $contents = file_get_contents($this->outputFile);
                if (strlen($contents) && $this->outputContent != $contents) {
                    $newOutput = str_replace($this->outputContent, '', $contents);
                    if (preg_match('/\r\n|\r|\n/', $newOutput)) {
                        $this->echoOutputFlag($newOutput);
                        $this->outputContent = $contents;
                    }
                }
                usleep(500000);
            }
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            proc_close($process);
        }

        return $this->output('run success');
    }

    /**
     * 判断状态
     * @param $process
     * @return bool
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/13
     */
    public function getProcStatus($process): bool
    {
        $status = proc_get_status($process);
        if ($status['running']) {
            $this->procStatus = 1;
            return true;
        } elseif ($this->procStatus === 1) {
            $this->procStatus = 0;
            $this->output('exit: ' . $status['exitcode']);
            if ($status['exitcode'] === 0) {
                $this->echoOutputFlag('success');
            } else {
                $this->echoOutputFlag('error');
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 直接输入响应
     * @param string $message
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/13
     */
    public function echoOutputFlag(string $message)
    {
        if ($this->output && $this->output instanceof Output) {
            $this->output->info($message);
        } else {
            echo $this->output($message);
            @ob_flush();
        }
    }

    /**
     * 返回响应内容
     * @param string $data
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/4/13
     */
    private function output($data)
    {
        $data = [
            'message' => $data,
        ];
        return Response::create($data, 'json')->getContent();
    }
}
