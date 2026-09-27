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

namespace app\services\driver\pay;

use app\dao\driver\pay\PayChannelDao;
use app\model\driver\pay\PayChannelPlatform;
use app\services\pay\PayServices;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\driver\DriverRegistry;

/**
 * 支付方式管理服务
 * Class PayChannelServices
 * @package app\services\driver\pay
 * @method array|object|null get($id, ?array $field = []) 获取一条数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 */
class PayChannelServices extends BaseService
{
    protected const DEFAULT_ICON = 'icon-yuezhifu';

    /**
     * 按 channel_key 区分的默认字体图标（前端 iconfont 已内置对应字形，
     * 后台未上传图片时按支付方式展示差异化图标）
     */
    protected const DEFAULT_ICONS = [
        'weixin'    => 'icon-weixinzhifu',
        'weixin_v3' => 'icon-weixinzhifu',
        'alipay'    => 'icon-zhifubao',
        'yue'       => 'icon-yuezhifu',
        'offline'   => 'icon-xianxiazhifu',
        'friend'    => 'icon-haoyoudaizhifu',
        'allinpay'  => 'icon-tonglianzhifu1',
    ];

    /**
     * @var PayChannelDao
     */
    protected $dao;

    /**
     * 初始化
     * @param PayChannelDao $dao
     */
    public function __construct(PayChannelDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表（含驱动状态，过滤已软删除的记录）
     * @param array $where
     * @return array
     */
    public function getList(array $where = []): array
    {
        $where['is_del'] = 0;
        $list = $this->dao->selectList($where, '*', 0, 0, 'sort desc,id asc')->toArray();
        $drivers = $this->getDrivers();

        // 为每条记录附加驱动状态
        foreach ($list as &$item) {
            $item['icon'] = $this->getDisplayIcon($item['icon'] ?? '');
            $item['driver_status'] = false;
            $item['driver_info'] = null;
            $driverName = $item['driver'] ?? '';
            if ($driverName) {
                foreach ($drivers as $driver) {
                    if ($driver['name'] === $driverName) {
                        $item['driver_status'] = $driver['exists'];
                        $item['driver_info'] = $driver;
                        break;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 获取单条详情（含当前配置值）
     * @param int $id
     * @return array
     */
    public function getDetail(int $id): array
    {
        $channel = $this->dao->getById($id);
        if (!$channel) {
            throw new AdminException('支付方式不存在');
        }

        $data = $channel->toArray();
        $data['icon'] = $this->getDisplayIcon($data['icon'] ?? '');

        // 字段结构以已安装驱动的最新清单为准，数据库仅负责保存用户填写的 value。
        // 这样插件升级 show_if、options、tip 等定义后无需手工迁移 pay_channel 旧 JSON。
        $storedFields = is_array($data['config_fields'] ?? null) ? $data['config_fields'] : [];
        $schemaFields = $this->getDriverConfigSchema((string)($data['driver'] ?? ''));
        $data['config_fields'] = $this->mergeConfigFields($schemaFields ?: $storedFields, $storedFields);

        return $data;
    }

    /**
     * 更新支付方式
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateChannel(int $id, array $data): bool
    {
        $channel = $this->dao->getById($id);
        if (!$channel) {
            throw new AdminException('支付方式不存在');
        }

        // 更新基本信息
        $updateData = [];
        if (isset($data['channel_name'])) $updateData['channel_name'] = $data['channel_name'];
        if (isset($data['icon'])) $updateData['icon'] = $this->normalizeIcon($data['icon']);
        if (isset($data['sort'])) $updateData['sort'] = $data['sort'];
        if (isset($data['status'])) $updateData['status'] = $data['status'];
        if (isset($data['enabled_platforms'])) {
            $updateData['enabled_platforms'] = $data['enabled_platforms'];

            // 同步更新 eb_pay_channel_platform 表状态
            $enabledList = array_filter(array_map('trim', explode(',', $data['enabled_platforms'])));
            PayChannelPlatform::where('channel_key', $channel->channel_key)
                ->whereIn('platform', $enabledList)
                ->update(['status' => 1]);
            PayChannelPlatform::where('channel_key', $channel->channel_key)
                ->whereNotIn('platform', $enabledList)
                ->update(['status' => 0]);
        }

        // 前端按 config_fields 提交动态配置。只接收 field/value，字段定义仍以驱动清单为准，
        // 防止客户端篡改 show_if、type、options 等结构；同时兼容旧版顶层字段提交方式。
        $storedFields = is_array($channel->config_fields) ? $channel->config_fields : [];
        $schemaFields = $this->getDriverConfigSchema((string)$channel->driver);
        $configFields = $this->mergeConfigFields($schemaFields ?: $storedFields, $storedFields);
        $submittedValues = $this->extractSubmittedConfigValues($data['config_fields'] ?? []);

        foreach ($configFields as &$field) {
            $fieldName = (string)($field['field'] ?? '');
            if ($fieldName === '') {
                continue;
            }
            if (array_key_exists($fieldName, $submittedValues)) {
                $field['value'] = $submittedValues[$fieldName];
            } elseif (array_key_exists($fieldName, $data)) {
                $field['value'] = $data[$fieldName];
            }
            $field = $this->normalizeConfigFieldValue($field);
        }
        unset($field);
        $updateData['config_fields'] = $configFields;

        $this->dao->update($id, $updateData);

        $this->clearPayChannelCache();

        return true;
    }

    /**
     * 获取已安装支付驱动的最新配置字段结构。
     */
    protected function getDriverConfigSchema(string $driverName): array
    {
        if ($driverName === '') {
            return [];
        }

        foreach (DriverRegistry::scan('pay') as $info) {
            if (($info['dir_name'] ?? '') === $driverName) {
                return is_array($info['config_fields'] ?? null) ? $info['config_fields'] : [];
            }
        }

        return [];
    }

    /**
     * 用最新字段结构承载已保存值，避免旧结构阻断插件升级后的联动配置。
     */
    protected function mergeConfigFields(array $schemaFields, array $storedFields): array
    {
        $storedByName = [];
        foreach ($storedFields as $field) {
            if (is_array($field) && !empty($field['field'])) {
                $storedByName[(string)$field['field']] = $field;
            }
        }

        $result = [];
        foreach ($schemaFields as $field) {
            if (!is_array($field) || empty($field['field'])) {
                continue;
            }

            $fieldName = (string)$field['field'];
            $storedField = $storedByName[$fieldName] ?? [];
            if (array_key_exists('value', $storedField)) {
                $field['value'] = $storedField['value'];
            } elseif (array_key_exists('default', $field)) {
                $field['value'] = $field['default'];
            } elseif (in_array(($field['type'] ?? ''), ['radio', 'select'], true)
                && isset($field['options'][0]['value'])) {
                // 兼容旧驱动清单：未声明 default 时使用第一项，避免所有联动分组都被隐藏。
                $field['value'] = $field['options'][0]['value'];
            } elseif (($field['type'] ?? '') === 'switch') {
                $field['value'] = '0';
            } else {
                $field['value'] = '';
            }

            // 兼容历史安装数据中只写控制字段、遗漏比较值的 show_if。
            if (is_array($field['show_if'] ?? null)
                && !array_key_exists('value', $field['show_if'])
                && !array_key_exists('values', $field['show_if'])) {
                $field['show_if']['value'] = '1';
            }

            $result[] = $this->normalizeConfigFieldValue($field);
        }

        return $result;
    }

    /**
     * 从前端动态字段列表中提取可保存值。
     */
    protected function extractSubmittedConfigValues($configFields): array
    {
        if (!is_array($configFields)) {
            return [];
        }

        $values = [];
        foreach ($configFields as $field) {
            if (!is_array($field) || empty($field['field']) || !array_key_exists('value', $field)) {
                continue;
            }
            $values[(string)$field['field']] = $field['value'];
        }

        return $values;
    }

    /**
     * Element UI 的 radio/select/switch 使用字符串值，统一类型避免 0 与 "0" 联动失配。
     */
    protected function normalizeConfigFieldValue(array $field): array
    {
        $type = (string)($field['type'] ?? '');
        if (in_array($type, ['radio', 'select', 'switch'], true)
            && array_key_exists('value', $field)
            && $field['value'] !== null) {
            $field['value'] = (string)$field['value'];
        }

        return $field;
    }

    /**
     * 修改状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function setStatus(int $id, int $status): bool
    {
        $result = $this->dao->update($id, ['status' => $status]) !== false;
        
        if ($result) {
            $this->clearPayChannelCache();
        }
        
        return $result;
    }

    /**
     * 批量排序
     * @param array $sortData [{id: 1, sort: 100}, ...]
     * @return bool
     */
    public function setSort(array $sortData): bool
    {
        foreach ($sortData as $item) {
            if (isset($item['id']) && isset($item['sort'])) {
                $this->dao->update((int)$item['id'], ['sort' => (int)$item['sort']]);
            }
        }
        
        $this->clearPayChannelCache();
        
        return true;
    }

    /**
     * 扫描 driver/pay/（第三方扩展 + 内置驱动 + 插件安装的驱动）
     * 自动将无对应驱动文件的记录软删除（is_del=1），已存在的恢复（is_del=0）
     * @return array
     */
    public function getDrivers(): array
    {
        // 统一走驱动注册中心（读取 plugin.json 元数据），按目录名重组原契约结构
        $drivers = [];
        foreach (DriverRegistry::scan('pay') as $info) {
            $name = $info['dir_name'];
            $class = $info['namespace'] . '\\' . $info['class_name'];
            $drivers[] = [
                'name'        => $name,
                'namespace'   => $class,
                'class'       => $class,
                'file_path'   => root_path() . 'driver/pay/' . $name . '/Driver.php',
                'exists'      => true, // 已通过插件生命周期校验，当前可加载
                'has_info'    => true, // 登记即有 plugin.json 元数据
                'description' => $info['name'],
                'info'        => $info,
            ];
        }

        // 提取实际存在的驱动名称列表
        $existsNames = array_column($drivers, 'name');

        // 获取所有未删除的支付渠道记录
        $records = $this->dao->selectList([], 'id,driver', 0, 0, '')->toArray();

        foreach ($records as $record) {
            $driverName = $record['driver'] ?? '';
            if (!$driverName) {
                continue;
            }
            if (in_array($driverName, $existsNames)) {
                // 驱动文件存在 → 恢复（如果之前被软删除了）
                $this->dao->update($record['id'], ['is_del' => 0]);
            } else {
                // 驱动文件不存在 → 软删除
                $this->dao->update($record['id'], ['is_del' => 1]);
            }
        }

        return $drivers;
    }

    /**
     * 获取用户端可用的支付方式列表
     * @param string $scene 当前场景：routine/weixinh5/h5/pc/app/store
     * @param float|null $userBalance 用户余额
     * @return array
     */
    public function getAvailableChannels(string $scene = '', ?float $userBalance = null): array
    {
        $list = $this->dao->getAllEnabled();
        $result = [];

        foreach ($list as $channel) {
            $data = $channel->toArray();

            // 从 eb_pay_channel_platform 表判断当前端是否启用
            if ($scene) {
                $platformExists = \app\model\driver\pay\PayChannelPlatform::where('channel_key', $data['channel_key'])
                    ->where('platform', $scene)
                    ->where('status', 1)
                    ->find();
                if (!$platformExists) {
                    continue;
                }
            }

            // 支付管理中的渠道状态是用户端支付方式的唯一启停依据。
            $payStatus = $this->checkChannelStatus($data);

            $result[] = [
                // 优先使用支付管理中配置的图片，未配置时按 channel_key 使用差异化的默认字体图标。
                'icon'      => $this->getDisplayIcon($data['icon'] ?? '', (string)($data['channel_key'] ?? '')),
                'name'      => $data['channel_name'],
                'value'     => $data['channel_key'],
                'title'     => $this->getChannelTitle($data),
                'number'    => $data['channel_key'] === 'yue' ? $userBalance : null,
                'payStatus' => $payStatus,
            ];
        }

        return $result;
    }

    /**
     * 规范化支付图标值。
     * 历史安装数据中的 icon-* 是字体类名，不是后台上传的图片地址，按未配置处理。
     *
     * @param mixed $icon
     * @return string
     */
    protected function normalizeIcon($icon): string
    {
        $icon = trim((string)$icon);
        if ($icon === ''
            || preg_match('/^icon-[a-z0-9_-]+$/i', $icon)
            || (!preg_match('/^(https?:)?\/\//i', $icon)
                && !str_starts_with($icon, '/')
                && !preg_match('/\.(png|jpe?g|gif|webp|bmp|svg)(?:[?#].*)?$/i', $icon))) {
            return '';
        }

        return $icon;
    }

    /**
     * 获取展示图标。后台未配置图片时按 channel_key 使用对应的默认字体图标，不写回数据库。
     *
     * @param mixed $icon
     * @param string $channelKey 支付方式标识，用于匹配差异化默认图标
     * @return string
     */
    protected function getDisplayIcon($icon, string $channelKey = ''): string
    {
        return $this->normalizeIcon($icon)
            ?: self::DEFAULT_ICONS[$channelKey] ?? self::DEFAULT_ICON;
    }

    /**
     * 检查支付方式的开关状态（从 config_fields 自动解析）
     * @param array $channelData
     * @return bool
     */
    protected function checkChannelStatus(array $channelData): bool
    {
        // 从支付驱动表读取 status 字段，1=开启
        return !!(($channelData['status'] ?? 0) == 1);
    }

    /**
     * 根据 channel_key 获取支付方式的启用状态
     * @param string $channelKey
     * @return bool
     */
    public function getChannelStatus(string $channelKey): bool
    {
        try {
            $channel = $this->dao->getByChannelKey($channelKey);
            if (!$channel) {
                return false;
            }

            $data = $channel->toArray();

            // 检查基本状态
            if ((int)($data['status'] ?? 0) !== 1) {
                return false;
            }

            // 从 config_fields 检查开关状态
            return $this->checkChannelStatus($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取支付方式的标题描述
     * @param array $channelData
     * @return string
     */
    protected function getChannelTitle(array $channelData): string
    {
        // 优先从 config_fields 的第一个 switch 字段的 label 推导
        $configFields = $channelData['config_fields'] ?? [];
        foreach ($configFields as $field) {
            if (($field['type'] ?? '') === 'switch') {
                $label = $field['label'] ?? '';
                // 去掉"开关"后缀作为描述
                return str_replace('开关', '', $label) . '快捷支付';
            }
        }

        return $channelData['channel_name'] ?? '';
    }

    /**
     * 清除支付渠道信息缓存
     */
    protected function clearPayChannelCache(): void
    {
        try {
            /** @var PayServices $payServices */
            $payServices = app()->make(PayServices::class);
            $payServices->clearChannelInfoCache();
        } catch (\Exception $e) {
            // 缓存清除失败不影响主流程
        }
    }
}
