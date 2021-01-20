<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\basic;

use crmeb\interfaces\JobInterface;
use think\facade\Log;
use think\queue\Job;

/**
 * 消息队列基类
 * Class BaseJob
 * @package crmeb\basic
 */
class BaseJob implements JobInterface
{

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        $this->fire(...$arguments);
    }

    /**
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data): void
    {
        try {
            $action = $data['do'] ?? 'doJob';//任务名
            $infoData = $data['data'] ?? [];//执行数据
            $errorCount = $data['errorCount'] ?? 0;//最大错误次数
            $log = $data['log'] ?? null;
            if (method_exists($this, $action)) {
                if ($this->{$action}(...$infoData)) {
                    //删除任务
                    $job->delete();
                    //记录日志
                    $this->info($log);
                } else {
                    if ($job->attempts() >= $errorCount && $errorCount) {
                        //删除任务
                        $job->delete();
                        //记录日志
                        $this->info($log);
                    } else {
                        //从新放入队列
                        $job->release();
                    }
                }
            } else {
                $job->delete();
            }
        } catch (\Throwable $e) {
            $job->delete();
            Log::error('执行消息队列发成错误,错误原因:' . $e->getMessage());
        }
    }

    /**
     * 打印出成功提示
     * @param $log
     * @return bool
     */
    protected function info($log)
    {
        try {
            if (is_callable($log)) {
                print_r($log() . "\r\n");
            } else if (is_string($log) || is_array($log)) {
                print_r($log . "\r\n");
            }
        } catch (\Throwable $e) {
            print_r($e->getMessage());
        }
    }

    /**
     * 任务失败执行方法
     * @param $data
     * @param $e
     */
    public function failed($data, $e)
    {

    }
}
