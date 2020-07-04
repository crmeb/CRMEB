<?php

namespace crmeb\utils;

use crmeb\traits\LogicTrait;
use think\facade\Queue as QueueJob;

/**
 * Class Queue
 * @package crmeb\utils
 * @method $this setJobClassName(string $jobClassName); 设置任务类名
 * @method $this setQueue(string $queue); 设置任务名
 * @method $this setDelay(int $delay); 设置延迟时间
 * @method $this setJobData(array $jobData); 设置任务参数
 */
class Queue
{

    use LogicTrait;

    /**
     * 任务类名
     * @var string
     */
    protected $jobClassName = \crmeb\jobs\OrderJob::class;

    /**
     * 多任务
     * @var null
     */
    protected $task = null;

    /**
     * 任务参数
     * @var array
     */
    protected $jobData = [
        'beforeMethod' => 'doBeforeMethod', //默认任务执行前执行的方法
        'method' => 'doDefaultJod', //默认任务执行方法
        'data' => null, //执行任务需要的参数
        'errorTimes' => 3, //任务执行错误最大次数
        'release' => 0,//延迟执行秒数
    ];

    /**
     * 任务名称
     * @var string
     */
    protected $queue = 'CRMEB';

    /**
     * 延迟执行秒数
     * @var int
     */
    protected $delay = 0;

    /**
     * 规则
     * @var array
     */
    protected $propsRule = [
        'jobClassName' => null,
        'queue' => null,
        'delay' => null,
        'jobData' => null,
    ];

    /**
     * 创建定时执行任务
     * @param $data
     * @return mixed
     */
    public function push($data = null)
    {
        $this->merge($data);
        return QueueJob::push($this->jobClassName, $this->jobData, $this->queue);
    }

    /**
     * 创建延迟执行任务
     * @param null $data
     * @return mixed
     */
    public function later($data = null)
    {
        $this->merge($data);
        return QueueJob::later($this->delay, $this->jobClassName, $this->jobData, $this->queue);
    }

    /**
     * 合并处理参数
     * @param $data
     */
    protected function merge($data)
    {
        if ($data) {
            $this->jobData['data'] = $data;
        }
        if ($this->delay && !$this->jobData['release']) {
            $this->jobData['release'] = $this->delay;
        }
        $this->jobClassName = $this->task ? $this->jobClassName . '@' . $this->task : $this->jobClassName;
    }

    /**
     * 创建任务
     * @param null $data
     * @return mixed
     */
    public static function create($data = null)
    {
        $instance = self::instance();
        if ($instance->delay) {
            return $instance->later($data);
        } else {
            return $instance->push($data);
        }
    }
}