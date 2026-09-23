<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\message\channel;

use app\dao\message\NoticeChannelDao;
use app\dao\message\NoticeTemplateDao;
use app\services\driver\sms\SmsDriverServices;
use app\services\message\admin\NoticeConfigServices;
use app\services\user\UserServices;
use crmeb\exceptions\NoticeSendException;
use crmeb\services\notice\AbstractNoticeChannel;
use crmeb\services\notice\NoticeMessage;
use crmeb\services\sms\Sms;
use think\facade\Log;

/**
 * 短信渠道
 *
 * 依赖 app 层 SmsDriverServices 读取启用的短信驱动配置，故置于 app/services/message/channel/ 下。
 * pushSms() 方法同时供验证码场景直接调用（LoginServices::verify）。
 */
class SmsChannel extends AbstractNoticeChannel
{
    public static function channelKey(): string
    {
        return 'is_sms';
    }

    public static function channelName(): string
    {
        return '短信';
    }

    /**
     * 本渠道额外配置字段：短信模板ID
     * @return array
     */
    public static function getExtraFields(): array
    {
        return ['sms_id' => ''];
    }

    /**
     * @param array|null $global 短信渠道定义
     * @param array|null $template 验证码通知模板
     * @return bool
     */
    public static function verificationAvailable(?array $global, ?array $template): bool
    {
        return $global !== null && (int)$global['status'] === 1 && (int)$global['support_user'] === 1
            && $template !== null && (int)$template['status'] === 1;
    }

    /**
     * 发送短信通知（通知管线入口）
     *
     * @param NoticeMessage $message 待投递消息（sms_id / sms_driver_key 在 extra，模板内容为 content）
     * @throws NoticeSendException 手机号缺失、驱动未配置、模板未就绪或网关拒绝
     */
    public function send(NoticeMessage $message): void
    {
        $phone = $this->resolvePhone($message);
        if ($phone === '') {
            throw new NoticeSendException('短信发送缺少手机号（变量 user_phone/phone 及用户资料均未提供）');
        }

        // 通知路径的模板上下文来自消息本体：模板ID/驱动标识在 extra，内容即 content
        $this->doSend($phone, $message->variables, $message->mark, [
            'sms_id'          => (string)($message->extra['sms_id'] ?? ''),
            'template_content' => $message->content,
            'sms_driver_key'  => (string)($message->extra['sms_driver_key'] ?? ''),
        ]);
    }

    /**
     * 发送短信（验证码直发入口）
     *
     * 验证码场景不携带模板上下文，按 mark 自动查询配置；失败返回 false 不抛出，
     * 由调用方（LoginServices::verify）转为业务异常。
     *
     * @param string $phone 收件手机号
     * @param array  $data  短信参数（如 ['code' => '123456', 'time' => '5']）
     * @param string $mark  消息标识（如 verify_code）
     * @return bool
     */
    public function pushSms(string $phone, array $data, string $mark): bool
    {
        try {
            $template = [];
            if ($mark === 'verify_code') {
                // 验证码直发路径按 mark 从通知配置读取模板ID与内容
                $template = $this->getSmsConfigFromChannel($mark);
            }
            $this->doSend($phone, $data, $mark, $template);
            return true;
        } catch (\Throwable $e) {
            Log::error('发送短信失败,失败原因:' . $e->getMessage());
            return false;
        }
    }

    /**
     * 短信发送核心流程
     *
     * @param string $phone     收件手机号
     * @param array  $data      短信参数
     * @param string $mark      消息标识
     * @param array  $template  模板上下文：sms_id / template_content / sms_driver_key
     * @throws NoticeSendException 任一环节失败
     */
    protected function doSend(string $phone, array $data, string $mark, array $template): void
    {
        Log::info('[短信诊断] 进入短信发送 ' . json_encode([
            'mark' => $mark,
            'phone_tail' => $phone !== '' ? substr($phone, -4) : '',
            'data_keys' => array_keys($data),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($phone === '') {
            throw new NoticeSendException('手机号为空');
        }

        /** @var SmsDriverServices $driverServices */
        $driverServices = app()->make(SmsDriverServices::class);
        $enabledDriver = $driverServices->getEnabledDriver();
        if (!$enabledDriver) {
            throw new NoticeSendException('未配置启用的短信驱动');
        }

        $type = $enabledDriver['driver_key'];
        $configStatus = [];
        foreach ((array)($enabledDriver['config_fields'] ?? []) as $field) {
            if (!empty($field['field'])) {
                $configStatus[$field['field']] = (string)($field['value'] ?? '') !== '';
            }
        }
        Log::info('[短信诊断] 短信驱动已选定 ' . json_encode([
            'mark' => $mark,
            'driver' => $type,
            'config_present' => $configStatus,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $smsId = (string)($template['sms_id'] ?? '');
        if ($smsId === '' || (!empty($template['sms_driver_key']) && $template['sms_driver_key'] !== $type)) {
            throw new NoticeSendException("短信模板未就绪或不属于当前驱动: {$mark}");
        }

        // 短信平台要求参数与模板变量严格匹配，只传模板内容中声明的占位符
        $data = $this->parseData((string)($template['template_content'] ?? ''), $data);
        if ($type == 'tencent') {
            $data = $this->handleTencent($data);
        }

        $smsMake = app()->make(Sms::class, [$type, $this->getDriverConfig($enabledDriver)]);
        Log::info('[短信诊断] 调用短信驱动 ' . json_encode([
            'mark' => $mark,
            'driver' => $type,
            'template_id' => $smsId,
            'param_keys' => array_keys($data),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $res = $smsMake->send($phone, $smsId, $data);
        if ($res === false) {
            throw new NoticeSendException((string)$smsMake->getError() ?: '短信网关拒绝');
        }
        Log::info('[短信诊断] 短信驱动返回成功 ' . json_encode([
            'mark' => $mark,
            'driver' => $type,
            'template_id' => $smsId,
            'response_type' => get_debug_type($res),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * 解析收件手机号：优先业务变量（user_phone/phone），缺省时按 uid 读用户资料
     *
     * @param NoticeMessage $message 待投递消息
     * @return string 空字符串表示无法解析
     */
    protected function resolvePhone(NoticeMessage $message): string
    {
        $phone = (string)($message->variables['user_phone'] ?? $message->variables['phone'] ?? '');
        if ($phone !== '' || $message->uid <= 0) {
            return $phone;
        }
        try {
            $userInfo = app()->make(UserServices::class)->getUserInfo($message->uid, 'phone');
            if (is_object($userInfo) && method_exists($userInfo, 'toArray')) {
                $userInfo = $userInfo->toArray();
            }
            return is_array($userInfo) ? (string)($userInfo['phone'] ?? '') : '';
        } catch (\Throwable $e) {
            Log::warning('[短信诊断] 按用户ID读取手机号失败 ' . json_encode([
                'mark' => $message->mark,
                'uid' => $message->uid,
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return '';
        }
    }

    /**
     * 从驱动配置中提取配置数组
     * @param array $driver
     * @return array
     */
    protected function getDriverConfig(array $driver): array
    {
        $config = [];
        $configFields = is_array($driver['config_fields']) ? $driver['config_fields'] : [];
        foreach ($configFields as $field) {
            if (isset($field['field']) && isset($field['value'])) {
                $config[$field['field']] = $field['value'];
            }
        }
        return $config;
    }

    /**
     * 验证码直发路径读取开关及短信配置。
     * @param string $mark 消息标识
     * @return array
     */
    protected function getSmsConfigFromChannel(string $mark): array
    {
        try {
            $global = app()->make(NoticeChannelDao::class)->getByChannelKey('is_sms');
            if (!$global) return [];
            $sysNotiServices = app()->make(NoticeConfigServices::class);
            $notification = $sysNotiServices->getOneNotce(['mark' => $mark, 'type' => 1]);
            if (!$notification) return [];

            $templateDao = app()->make(NoticeTemplateDao::class);
            $channel = $templateDao->getByNotificationAndChannel($notification['id'], 'is_sms');
            if (self::verificationAvailable($global, $channel ? $channel->toArray() : null)) {
                $extra = !empty($channel['extra']) ? (is_string($channel['extra']) ? json_decode($channel['extra'], true) : $channel['extra']) : [];
                $extra['template_content'] = (string)($channel['template_content'] ?? '');
                return $extra;
            }
        } catch (\Throwable $e) {
            Log::error('获取短信模板ID失败: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * 解析短信参数
     *
     * 以模板内容为准：按 {变量} 占位符出现顺序提取变量，
     * 只保留 $data 中存在的同名标量值。多余或缺失的参数都会被短信平台拒绝。
     * 阿里云/一号通使用命名参数（如 ['code'=>'123456']），
     * 腾讯云使用位置参数（由 handleTencent 转换）。
     * 模板内容为空时退回原始数据，兜底无模板的直发场景。
     *
     * @param string $templateContent 模板内容（含 {变量} 占位符）
     * @param array  $data            短信参数
     * @return array
     */
    protected function parseData(string $templateContent, array $data): array
    {
        if ($templateContent === '') {
            return $data;
        }
        if (!preg_match_all('/\{([^{}]+)\}/', $templateContent, $matches)) {
            return [];
        }

        $params = [];
        foreach (array_unique($matches[1]) as $key) {
            $key = trim((string)$key);
            if ($key === '' || !array_key_exists($key, $data) || !is_scalar($data[$key])) {
                continue;
            }
            $params[$key] = (string)$data[$key];
        }
        return $params;
    }

    /**
     * 处理腾讯云参数
     *
     * 将按模板内容顺序过滤后的命名参数转为位置参数数组，
     * 顺序与模板内容中占位符出现顺序一致，需与腾讯云后台模板变量顺序保持一致。
     *
     * @param array $data 命名参数
     * @return array 位置参数
     */
    public function handleTencent(array $data): array
    {
        return array_values($data);
    }
}
