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

declare(strict_types=1);

namespace crmeb\services\pay;

use think\facade\Db;

/**
 * 支付渠道配置读取工具
 *
 * 支付驱动插件化后，支付配置统一存储于 eb_pay_channel.config_fields，
 * 各层读取支付配置一律经由本类，禁止再从 system_config 读取支付配置。
 * 本类位于 crmeb 核心层，供 crmeb / modules / app 各层复用。
 *
 * 架构豁免说明：本类位于 crmeb 核心层，依赖铁律禁止调用 app 层服务
 * （如 app\services\driver\pay\PayChannelServices），核心层无 DAO 体系，
 * 故此处 Db 直查为合规做法（同 crmeb\services\AliPayService::getPemPath
 * 与 driver\pay\WechatPay\WechatPaySdk::channelConfig 模式），禁止"修复"为 app 层 DAO。
 *
 * Class PayChannelConfig
 * @package crmeb\services\pay
 */
class PayChannelConfig
{
    /**
     * 按渠道标识读取支付渠道配置值映射（config_fields 的 field => value）
     * config_fields 为空时，从 driver/pay/{driver}/plugin.json 兜底字段定义
     * @param string $channelKey 渠道标识：weixin / weixin_v3 / alipay / allinpay / yue / offline / friend
     * @return array<string,string>
     */
    public static function get(string $channelKey): array
    {
        try {
            $channel = Db::name('pay_channel')
                ->where('channel_key', $channelKey)
                ->where('is_del', 0)
                ->find();
        } catch (\Throwable $e) {
            return [];
        }
        if (!$channel) {
            return [];
        }

        // Db 直查返回 text 列原始 JSON 字符串，需解码（模型 cast 在 crmeb 层不生效）
        $configFields = $channel['config_fields'] ?? '';
        if (is_string($configFields)) {
            $configFields = json_decode($configFields, true) ?: [];
        }
        if (!is_array($configFields)) {
            $configFields = [];
        }

        // config_fields 为空时从驱动 plugin.json 兜底字段定义
        if (empty($configFields) && !empty($channel['driver'])) {
            $configFields = self::pluginConfigFields((string)$channel['driver']);
        }

        $config = [];
        foreach ($configFields as $field) {
            if (isset($field['field'])) {
                $config[$field['field']] = $field['value'] ?? '';
            }
        }
        return $config;
    }

    /**
     * 读取支付驱动 plugin.json 中的 driver 元数据。
     *
     * 统一驱动表只保存通用字段，支付渠道的 channel_key、支付类型映射等扩展字段
     * 仍由驱动清单声明。调用方传入的目录名来自已安装驱动记录，禁止拼接任意路径。
     * @param string $dirName 驱动目录名（如 AliPay / WechatPay）
     * @return array
     */
    public static function getDriverMetadata(string $dirName): array
    {
        if ($dirName === '' || basename($dirName) !== $dirName
            || !preg_match('/^[A-Za-z0-9_.-]+$/', $dirName)) {
            return [];
        }

        $file = root_path('driver/pay/' . $dirName) . 'plugin.json';
        if (!is_file($file)) {
            return [];
        }

        try {
            $json = json_decode((string)file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return [];
        }

        $driver = $json['driver'] ?? [];
        return is_array($driver) ? $driver : [];
    }

    /**
     * 读取驱动 plugin.json 的 config_fields 字段定义
     * @param string $dirName 驱动目录名（如 AliPay / WechatPay / AllinPay）
     * @return array
     */
    protected static function pluginConfigFields(string $dirName): array
    {
        // plugin.json 约定：config_fields 位于 driver 子对象下（同 DriverRegistry::scan）
        $driver = self::getDriverMetadata($dirName);
        return is_array($driver['config_fields'] ?? null) ? $driver['config_fields'] : [];
    }
}
