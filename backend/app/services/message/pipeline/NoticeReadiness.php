<?php
declare(strict_types=1);

namespace app\services\message\pipeline;

/** 通知模板的配置就绪检查（不发送消息）。 */
class NoticeReadiness
{
    /**
     * @param string $key 渠道标识
     * @param int $type 通知类型
     * @param array|null $definition 已安装的渠道定义
     * @param bool $registered 渠道适配器是否已注册
     * @return array<string>
     */
    public static function channelReasons(string $key, int $type, ?array $definition, bool $registered): array
    {
        $reasons = [];
        if (!$registered) $reasons[] = '缺少渠道适配器';
        if (!$definition) {
            $reasons[] = '通知渠道未安装';
        } else {
            if ((int)$definition['status'] !== 1) $reasons[] = '渠道全局开关已关闭';
            if ($type === 1 && (int)$definition['support_user'] !== 1) $reasons[] = '渠道不支持用户通知';
            if ($type === 2 && (int)$definition['support_platform'] !== 1) $reasons[] = '渠道不支持平台通知';
        }
        return $reasons;
    }

    /**
     * @param string $key 渠道标识
     * @param array $template 渠道模板
     * @param string $variables 通知业务变量
     * @param int $type 1 用户、2 平台
     * @param bool $hasRecipient 平台客服是否存在
     * @param string $smsDriver 当前短信驱动
     * @param string $emailDriver 当前邮件驱动
     * @return array<string>
     */
    public static function reasons(string $key, array $template, string $variables, int $type = 1, bool $hasRecipient = true, string $smsDriver = '', string $emailDriver = ''): array
    {
        $reasons = [];
        $extra = $template['extra'] ?? [];
        if (is_string($extra)) {
            $extra = json_decode($extra, true) ?: [];
        }
        if ($key === 'is_system' && $type === 2 && !$hasRecipient) {
            $reasons[] = '缺少客服收件人';
        }
        if ($key === 'is_sms') {
            if (empty($extra['sms_id'])) {
                $reasons[] = '缺少短信模板ID';
            }
            if ($smsDriver === '') {
                $reasons[] = '缺少启用的短信驱动';
            } elseif (!empty($extra['sms_driver_key']) && $smsDriver !== $extra['sms_driver_key']) {
                $reasons[] = '短信编号不属于当前驱动';
            }
        }
        if ($key === 'is_email') {
            if (trim((string)($template['template_title'] ?? '')) === '') $reasons[] = '缺少邮件标题';
            if ($emailDriver === '') $reasons[] = '缺少启用的邮件驱动';
            // 用户通知(type=1)收件邮箱由渠道按 uid 从用户资料自动解析，无需业务声明；
            // 平台通知(type=2)无 uid，必须由业务数据显式携带收件邮箱变量
            if ($type === 2 && !preg_match('/\{(?:user_email|email)\}/', $variables)) $reasons[] = '缺少收件邮箱变量';
        }
        if (in_array($key, ['is_wechat', 'is_routine'], true)) {
            if (empty($extra['tempid'])) {
                $reasons[] = '缺少商户模板ID';
            }
            preg_match_all('/\{\{([a-zA-Z][a-zA-Z0-9_]*)\.DATA\}\}/i', (string)($template['template_content'] ?? ''), $matches);
            $keyList = $extra['key_list'] ?? [];
            preg_match_all('/\{([^}]+)\}/', $variables, $available);
            foreach (array_unique($matches[1]) as $field) {
                if (empty($keyList[$field]) || !in_array($keyList[$field], $available[1], true)) {
                    $reasons[] = '缺少模板字段映射';
                    break;
                }
            }
        }
        if ($key === 'is_dingtalk' && empty($extra['webhook'])) {
            $reasons[] = '缺少Webhook地址';
        }
        if ($key === 'is_ent_wechat' && empty($extra['url'])) {
            $reasons[] = '缺少机器人链接';
        }
        if ($key !== 'is_system' && trim((string)($template['template_content'] ?? '')) === '') {
            $reasons[] = '缺少通知内容';
        }
        return array_values(array_unique($reasons));
    }
}
