<?php
declare(strict_types=1);

namespace modules\aiagent\crontab;

use crmeb\crontab\CrontabTaskInterface;
use modules\aiagent\service\AgentTaskServices;

/**
 * AI 自动化任务调度触发器
 *
 * 由系统 timer 常驻进程按 eb_system_crontab（mark=aiAgentTask，每分钟）触发，
 * 仅做「到期扫描 + 入队」轻量动作，LLM 多轮调用全部在队列容器执行，
 * 避免阻塞 timer 单进程事件循环内的其他系统定时任务。
 */
class AiAgentTaskDispatcherTask implements CrontabTaskInterface
{
    /** @return string 调度标识（与 eb_system_crontab.mark 一致） */
    public function getMark(): string
    {
        return 'aiAgentTask';
    }

    /** @return string 任务名称 */
    public function getName(): string
    {
        return 'AI 自动化任务调度';
    }

    /** @return string 任务说明 */
    public function getDescription(): string
    {
        return '扫描到期的 AI 自动化任务并入队执行（aiagent 模块）';
    }

    /** @return void */
    public function handle(): void
    {
        app()->make(AgentTaskServices::class)->dispatchDueTasks();
    }
}
