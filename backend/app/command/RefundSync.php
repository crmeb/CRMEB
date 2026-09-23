<?php
declare(strict_types=1);

namespace app\command;

use app\services\order\RefundOrderServices;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * 查询既有微信退款并恢复本地状态，不重新提交退款请求。
 */
class RefundSync extends Command
{
    /**
     * 配置单笔退款同步命令。
     * @return void
     */
    protected function configure()
    {
        $this->setName('refund:sync')
            ->addArgument('refund_no', Argument::REQUIRED, '系统退款单号（eb_refund_order.refund_no）')
            ->setDescription('查询既有微信 V3 退款并同步本地状态，不重新发起退款');
    }

    /**
     * 查询渠道结果，失败时输出具体错误供排查。
     * @param Input $input 命令输入
     * @param Output $output 命令输出
     * @return int 退出码
     */
    protected function execute(Input $input, Output $output)
    {
        try {
            $result = app()->make(RefundOrderServices::class)->syncWechatRefund(trim((string)$input->getArgument('refund_no')));
            // 仅输出排障字段，避免输出支付上下文与渠道原始响应。
            $output->writeln(json_encode(array_intersect_key($result, array_flip([
                'refund_no', 'order_no', 'state', 'attempts', 'last_error', 'query_status', 'query_message',
            ])), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            return ($result['state'] ?? '') === 'completed' ? 0 : 1;
        } catch (\Throwable $e) {
            $output->writeln('退款同步失败：' . $e->getMessage());
            return 1;
        }
    }
}
