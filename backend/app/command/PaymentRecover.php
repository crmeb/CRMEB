<?php
declare(strict_types=1);

namespace app\command;

use app\services\pay\PayRecordServices;
use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 恢复既有支付合同、退款和后置任务，不创建新的支付订单。
 */
class PaymentRecover extends Command
{
    /**
     * 配置命令。
     * @return void
     */
    protected function configure()
    {
        $this->setName('pay:recover')->setDescription('分批查询微信漏单并恢复退款及支付后置任务');
    }

    /**
     * 执行恢复并输出脱敏汇总。
     * @param Input $input 输入
     * @param Output $output 输出
     * @return int 退出码
     */
    protected function execute(Input $input, Output $output)
    {
        try {
            $result = app()->make(PayRecordServices::class)->recover();
            $output->writeln(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            return $result['failed'] ? 1 : 0;
        } catch (\Throwable $e) {
            $output->writeln('支付恢复失败，请确认支付记录迁移已执行。异常类型：' . get_class($e));
            return 1;
        }
    }
}
