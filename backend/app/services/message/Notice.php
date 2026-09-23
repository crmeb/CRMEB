<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\message;

use app\services\message\admin\NoticeLogServices;
use app\services\message\pipeline\SendNoticeService;
use crmeb\exceptions\NoticeSendException;
use crmeb\plugin\ModuleStateChecker;
use crmeb\services\notice\NoticeChannelInterface;
use crmeb\services\notice\NoticeMessage;
use think\facade\Log;

/**
 * 消息通知发送门面类
 *
 * 提供链式 API，业务代码中直接发送消息通知；
 * 渠道选择、适配器实例化、投递与日志记录全部在本类完成，不再经过独立管线。
 * 接收者即类型：to($uid>0) 为用户通知（type=1），to(0) 为平台/客服提醒（type=2），
 * 两种配置分别由核心安装 SQL 或所属插件 Installer 按 (mark,type) 登记。
 * 双侧通知（同 mark 既发用户又发客服）：监听器内 to($uid) 与 to(0) 各调用一次，
 * 本类按本次调用类型分别投递，互不重复。
 *
 * 使用示例：
 * ```php
 * // 用户通知：支付成功后发给下单用户
 * Notice::to($uid)->sendMsg('order_pay_success', '支付成功', [
 *     'order_id' => $orderId,
 * ]);
 *
 * // 平台客服提醒：同一事件通知平台方（注意 to(0)）
 * Notice::to(0)->sendMsg('order_pay_success', '用户下单客服提醒', [
 *     'order_id' => $orderId,
 * ]);
 *
 * // 指定渠道发送（不指定则投递该通知已配置的全部启用渠道）
 * Notice::to($uid)->channels(['is_system', 'is_sms'])
 *     ->sendMsg('pink_complete', '拼团成功', [
 *         '活动名称' => $title,
 *     ]);
 * ```
 */
class Notice
{
    /**
     * 接收用户 ID（>0 用户通知 type=1；0 平台/客服通知 type=2）
     * @var int
     */
    protected int $uid = 0;

    /**
     * 指定发送的渠道标识列表（空数组 = 投递该通知全部启用渠道）
     * @var array
     */
    protected array $channelKeys = [];

    /**
     * 指定消息接收用户
     *
     * @param int $uid 用户ID，0 表示平台/客服端
     * @return static
     */
    public static function to(int $uid): self
    {
        $instance = new static();
        $instance->uid = $uid;
        return $instance;
    }

    /**
     * 指定发送渠道（多个）
     *
     * @param array $channels 渠道标识数组，如 ['is_system', 'is_sms', 'is_wechat']
     * @return $this
     */
    public function channels(array $channels): self
    {
        $this->channelKeys = $channels;
        return $this;
    }

    /**
     * 指定发送渠道（单个）
     *
     * @param string $channel 渠道标识，如 'is_wechat'
     * @return $this
     */
    public function channel(string $channel): self
    {
        return $this->channels([$channel]);
    }

    /**
     * 发送消息
     *
     * $data 统一为扁平业务变量（如 ['订单编号' => 'wx123', '支付金额' => '99.00']），
     * 各渠道按自身模板配置渲染：站内信/短信/机器人直接替换 {key} 占位符，
     * 公众号/小程序经后台配置的 key_list 把业务变量映射为微信模板字段。
     *
     * @param string $mark  消息标识（如 pink_complete、order_pay_success），须已由安装 SQL/Installer 登记
     * @param string $name  消息名称（如 拼团成功提醒），仅描述用途
     * @param array  $data  扁平业务变量
     * @return array [channel_key => ['success' => bool, 'error' => string]]
     *               空数组 = 没有任何渠道实际执行发送（mark 未登记/未启用/被过滤）
     */
    public function sendMsg(string $mark, string $name, array $data): array
    {
        try {
            // 通知由核心安装 SQL 或插件 Installer 登记；未知 mark 无配置可投
            $configs = app()->make(SendNoticeService::class)->getNoticeInfo($mark);
            if (empty($configs) || !$this->ownerActive($configs)) {
                return [];
            }

            // 选渠道：纯过滤，产出待投递清单（无 IO、无异常）
            $targets = $this->selectTargets($configs, $this->channelKeys);

            // 逐渠道投递：单渠道失败只影响自身，靠返回值汇总成败
            $results = [];
            foreach ($targets as $channelKey => $target) {
                $results[$channelKey] = $this->deliver($target, $mark, $data);
            }
            return $results;
        } catch (\Throwable $e) {
            Log::error('通知发送异常 ' . json_encode([
                'mark' => $mark,
                'uid' => $this->uid,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return [];
        }
    }

    /**
     * 取本次调用对应接收类型的配置行（uk_mark_type 唯一键保证同 mark 同 type 仅一行）
     *
     * @param array $configs getNoticeInfo 返回的同 mark 配置行列表
     * @return array|null
     */
    protected function typeConfig(array $configs): ?array
    {
        $wantType = $this->uid > 0 ? 1 : 2;
        foreach ($configs as $config) {
            if ((int)($config['type'] ?? 1) === $wantType) {
                return $config;
            }
        }
        return null;
    }

    /**
     * 校验通知归属插件的启用状态
     *
     * 归属为空 = 核心安装 SQL 登记的核心通知，直接放行；
     * 显式插件归属（module:xxx）校验模块启用状态，未启用不投递。
     *
     * @param array $configs 同 mark 配置行列表
     * @return bool
     */
    protected function ownerActive(array $configs): bool
    {
        $config = $this->typeConfig($configs);
        if ($config === null) {
            return false;
        }
        return ModuleStateChecker::isOwnerActive((string)($config['owner_plugin_id'] ?? ''));
    }

    /**
     * 选渠道：筛出本次调用应投递的渠道清单
     *
     * 过滤条件：模板行存在 + 渠道启用（含全局开关）+ 支持本次接收端
     * （用户通知只走 support_user=1 的渠道，平台通知只走 support_platform=1 的渠道，
     * 防止钉钉/企业微信群机器人被误用于向具体用户发消息）+
     * 渠道归属模块已启用（防止已禁用插件登记的适配器类名仍被调度）。
     *
     * @param array $configs      getNoticeInfo 返回的同 mark 配置行列表
     * @param array $onlyChannels 显式指定的渠道标识，空数组 = 遍历该通知已配置的全部渠道
     * @return array [channel_key => ['channel_key','channel_name','adapter','message'=>NoticeMessage]]
     */
    protected function selectTargets(array $configs, array $onlyChannels): array
    {
        $config = $this->typeConfig($configs);
        if ($config === null) {
            return [];
        }

        // 渠道支持范围按接收端选列
        $scopeKey = $this->uid > 0 ? 'support_user' : 'support_platform';

        $targets = [];
        foreach ($onlyChannels ?: array_keys($config['channels'] ?? []) as $channelKey) {
            $channel = $config['channels'][$channelKey] ?? null;

            if (!$channel
                || (int)($channel['status'] ?? 0) !== 1
                || (int)($channel['global_status'] ?? 1) !== 1
                || (int)($channel[$scopeKey] ?? 1) !== 1
                // 渠道归属模块已禁用/卸载时不投递（渠道表 adapter_class 是静态登记，需运行时校验归属状态）
                || !ModuleStateChecker::isOwnerActive((string)($channel['owner_plugin_id'] ?? ''))
            ) {
                continue;
            }

            $extra = [];
            if (!empty($channel['extra'])) {
                $extra = is_string($channel['extra'])
                    ? (json_decode($channel['extra'], true) ?: [])
                    : (array)$channel['extra'];
            }

            $targets[$channelKey] = [
                'channel_key'  => $channelKey,
                'channel_name' => (string)($channel['channel_name'] ?? $channelKey),
                'adapter'      => (string)($channel['adapter_class'] ?? ''),
                'message'      => new NoticeMessage(
                    mark: (string)$config['mark'],
                    uid: $this->uid,
                    title: (string)($channel['template_title'] ?? ''),
                    content: (string)($channel['template_content'] ?? ''),
                    extra: $extra,
                ),
            ];
        }
        return $targets;
    }

    /**
     * 投递单个渠道：实例化适配器 → 发送 → 写日志 → 返回该渠道结果
     *
     * 任何一步失败都只终结本渠道，绝不向上抛——调用方在循环里投递多个渠道，
     * 靠返回值汇总成败，这就是"单渠道异常隔离"的全部内容。
     *
     * @param array  $target    selectTargets 产出的待投递目标
     * @param string $mark      消息标识（写日志用）
     * @param array  $variables 扁平业务变量
     * @return array ['success' => bool, 'error' => string]
     */
    protected function deliver(array $target, string $mark, array $variables): array
    {
        // 变量在投递时才装配进消息：selectTargets 阶段保持纯过滤
        $target['message'] = new NoticeMessage(
            mark: $mark,
            uid: $target['message']->uid,
            title: $target['message']->title,
            content: $target['message']->content,
            variables: $variables,
            extra: $target['message']->extra,
        );

        // 适配器类名来自渠道表字段，属于可配置数据：空值/类不存在/没实现接口都算配置错误，
        // 归为该渠道失败并留日志，而不是让整批发送崩掉
        $adapter = $target['adapter'];
        if ($adapter === '' || !class_exists($adapter)) {
            return $this->recordFailure($target, '渠道适配器类不存在：' . $adapter);
        }
        if (!is_subclass_of($adapter, NoticeChannelInterface::class)) {
            return $this->recordFailure($target, '适配器未实现 NoticeChannelInterface：' . $adapter);
        }

        try {
            $this->makeChannel($adapter)->send($target['message']);
            $ok = true;
            $error = '';
        } catch (NoticeSendException $e) {
            // 渠道业务失败：消息即原因，直接记录
            $ok = false;
            $error = $e->getMessage();
        } catch (\Throwable $e) {
            // 渠道未预期的系统异常：带上异常类便于定位
            $ok = false;
            $error = get_class($e) . '：' . $e->getMessage();
        }

        $this->writeLog($target, $ok, $error);

        return ['success' => $ok, 'error' => $error];
    }

    /**
     * 适配器本身就是坏的场景：发送不可能发生，但仍要留下一条失败日志
     *
     * @param array  $target 待投递目标
     * @param string $error  失败原因
     * @return array ['success' => bool, 'error' => string]
     */
    protected function recordFailure(array $target, string $error): array
    {
        $this->writeLog($target, false, $error);
        return ['success' => false, 'error' => $error];
    }

    /**
     * 写通知日志。日志表故障不能反过来决定投递结果，但不允许空 catch——降级到框架日志留痕
     *
     * @param array  $target 待投递目标
     * @param bool   $ok     发送是否成功
     * @param string $error  失败原因（成功时为空）
     * @return void
     */
    protected function writeLog(array $target, bool $ok, string $error): void
    {
        try {
            $message = $target['message'];
            $logId = $this->logServices()->createLog(
                $message->mark,
                $message->uid,
                $target['channel_key'],
                $target['channel_name'],
                $message->title,
                $message->content
            );
            if ($ok) {
                $this->logServices()->logSuccess($logId);
            } else {
                $this->logServices()->logFailed($logId, $error);
            }
        } catch (\Throwable $e) {
            Log::error('通知日志写入失败 ' . json_encode([
                'mark' => $target['message']->mark,
                'uid'  => $target['message']->uid,
                'chan' => $target['channel_key'],
                'reason' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 实例化渠道适配器（独立方法便于测试替身注入）
     *
     * @param string $adapter 适配器类名
     * @return NoticeChannelInterface
     */
    protected function makeChannel(string $adapter): NoticeChannelInterface
    {
        return app()->make($adapter);
    }

    /**
     * 通知日志服务（独立方法便于测试替身注入）
     *
     * @return NoticeLogServices
     */
    protected function logServices(): NoticeLogServices
    {
        return app()->make(NoticeLogServices::class);
    }
}
