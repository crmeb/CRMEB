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

namespace app\services\driver\upload;

use app\dao\driver\upload\UploadDriverDao;
use app\dao\driver\upload\UploadStorageDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\driver\DriverRegistry;

/**
 * 存储驱动管理服务
 * Class UploadDriverServices
 * @package app\services\driver\upload
 */
class UploadDriverServices extends BaseService
{
    /** 配置界面保留已保存敏感值的固定占位符。 */
    public const CONFIG_MASK = '********';

    /**
     * @var UploadDriverDao
     */
    protected $dao;

    /**
     * UploadDriverServices constructor.
     * @param UploadDriverDao $dao
     */
    public function __construct(UploadDriverDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取驱动列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        [$page, $limit, $defaultLimit] = $this->getPageValue();
        $page = $page ?: 1;
        $limit = $limit ?: $defaultLimit;
        $list = $this->dao->getList($where, ['*'], $page, $limit, 'sort desc');
        $list = array_map([$this, 'redactDriver'], $list);
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 获取所有启用的驱动
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getEnabledDrivers()
    {
        return $this->dao->getEnabledDrivers();
    }

    /**
     * 根据 driver_key 获取驱动信息
     * @param string $driverKey
     * @return array|\think\Model|null
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getByDriverKey(string $driverKey)
    {
        return $this->dao->getByDriverKey($driverKey);
    }

    /**
     * 根据ID获取驱动信息
     * @param int $id
     * @return array|\think\Model|null
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getById(int $id)
    {
        return $this->dao->get($id);
    }

    /**
     * 扫描驱动目录并同步到数据库
     * 扫描 driver/upload/（第三方扩展 + 内置驱动 + 插件安装的驱动）
     * @return array 返回扫描到的驱动列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function scanDrivers()
    {
        $drivers = [];

        // 统一走驱动注册中心（读取 plugin.json 元数据），输出按原契约键白名单构造
        foreach (DriverRegistry::scan('upload') as $info) {
            $drivers[] = [
                'driver_key' => $info['driver_key'],
                'driver_name' => $info['name'],
                'class_name' => $info['class_name'],
                'domain_template' => $info['domain_template'] ?? '',
                'config_fields' => json_encode($info['config_fields'], JSON_UNESCAPED_UNICODE),
                'bucket_fields' => json_encode($info['bucket_fields'] ?? [], JSON_UNESCAPED_UNICODE),
                'help_doc' => $info['help_doc'],
                'sort' => $info['sort'],
                'is_builtin' => $info['is_builtin'],
            ];
        }

        // 按 sort 降序排序
        usort($drivers, function ($a, $b) {
            return $b['sort'] - $a['sort'];
        });

        return $drivers;
    }

    /**
     * 同步驱动信息到数据库
     * @return array 返回同步结果
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function syncDrivers()
    {
        $scanned = $this->scanDrivers();
        $updated = $this->transaction(function () use ($scanned) {
            $updated = 0;
            foreach ($scanned as $driver) {
                $current = $this->dao->getByDriverKey($driver['driver_key']);
                // Registry 只提供已安装且启用的插件；新增/删除字典行必须由插件生命周期管理。
                if (!$current) {
                    continue;
                }
                $savedFields = array_column($current->config_fields ?: [], null, 'field');
                $fields = json_decode($driver['config_fields'], true, 512, JSON_THROW_ON_ERROR);
                foreach ($fields as &$field) {
                    $saved = $savedFields[$field['field']] ?? [];
                    if (array_key_exists('value', $saved)) {
                        $field['value'] = $saved['value'];
                    }
                }
                unset($field);
                $driver['config_fields'] = $fields;
                unset($driver['is_builtin']);
                if ($this->dao->update($driver['driver_key'], $driver, 'driver_key') === false) {
                    throw new \RuntimeException('同步存储驱动失败');
                }
                ++$updated;
            }
            return $updated;
        });
        $this->invalidateUploadCache();

        return [
            'scanned' => count($scanned),
            'added' => 0,
            'updated' => $updated,
            'deleted' => 0,
        ];
    }

    /**
     * 更新驱动状态
     * @param string $driverKey
     * @param int $status
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateStatus(string $driverKey, int $status)
    {
        if (!in_array($status, [0, 1], true)) {
            throw new AdminException('无效的驱动状态');
        }
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        $this->update((int)$driver->id, ['status' => $status]);

        return true;
    }

    /**
     * 获取驱动的配置字段定义
     * @param string $driverKey
     * @param bool $redact 是否脱敏界面返回值
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfigFields(string $driverKey, bool $redact = false)
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            return [];
        }

        $fields = $driver->config_fields ?? [];
        return $redact ? $this->redactConfigFields($fields) : $fields;
    }

    /**
     * 获取供配置界面使用的脱敏字段
     * @param string $driverKey 驱动标识
     * @return array
     */
    public function getPublicConfigFields(string $driverKey): array
    {
        return $this->getConfigFields($driverKey, true);
    }

    /**
     * 获取已安装且插件可用的驱动能力声明
     * @param string $driverKey 驱动标识
     * @return array
     */
    public function getCapabilities(string $driverKey): array
    {
        $info = DriverRegistry::get('upload', $driverKey);
        return is_array($info['capabilities'] ?? null) ? array_values($info['capabilities']) : [];
    }

    /**
     * 获取驱动的存储空间配置字段
     * @param string $driverKey
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getBucketFields(string $driverKey)
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            return [];
        }

        return $driver->bucket_fields ?? [];
    }

    /**
     * 获取 type 到 driver_key 的映射
     * 从数据库动态读取，不硬编码
     * @return array
     */
    public function getTypeDriverKeyMap()
    {
        $map = [];
        $drivers = $this->dao->getList([], ['id', 'driver_key'], 0, 0, 'id asc');

        foreach ($drivers as $index => $driver) {
            $map[$index + 1] = $driver['driver_key'];
        }

        return $map;
    }

    /**
     * 根据 type 获取 driver_key
     * @param int $type
     * @return string
     */
    public function getDriverKeyByType(int $type)
    {
        $map = $this->getTypeDriverKeyMap();
        return $map[$type] ?? 'local';
    }

    /**
     * 根据 driver_key 获取 type
     * @param string $driverKey
     * @return int
     */
    public function getTypeByDriverKey(string $driverKey)
    {
        $map = $this->getTypeDriverKeyMap();
        return array_search($driverKey, $map) ?: 1;
    }

    /**
     * 获取上传公共配置（缩略图和水印）
     * @param string $driverKey
     * @return array
     */
    public function getCommonConfig(string $driverKey)
    {
        $defaultConfig = $this->getDefaultCommonConfig();
        $driver = $this->dao->getByDriverKey($driverKey);
        return array_merge($defaultConfig, array_intersect_key($driver->thumb_watermark_config ?? [], $defaultConfig));
    }

    /**
     * 上传公共配置的固定字段与默认值
     * @return array
     */
    protected function getDefaultCommonConfig(): array
    {
        return [
            'image_thumb_status' => 1,
            'thumb_big_width' => 800,
            'thumb_big_height' => 800,
            'thumb_mid_width' => 300,
            'thumb_mid_height' => 300,
            'thumb_small_width' => 150,
            'thumb_small_height' => 150,
            'image_watermark_status' => 1,
            'watermark_type' => 2,
            'watermark_opacity' => '',
            'watermark_rotate' => '',
            'watermark_position' => 1,
            'watermark_x' => '',
            'watermark_y' => '',
            'watermark_image' => '',
            'watermark_text' => 'CRMEB',
            'watermark_text_size' => '28',
            'watermark_text_color' => '#ffffff',
            'watermark_text_angle' => '7',
        ];
    }

    /**
     * 保存上传公共配置（缩略图和水印）到 eb_driver 表
     * @param string $driverKey
     * @param array $config
     * @return bool
     */
    public function saveCommonConfig(string $driverKey, array $config)
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        $driver->thumb_watermark_config = $this->prepareCommonConfig($driver->thumb_watermark_config ?: [], $config);
        $driver->update_time = time();
        if ($driver->save() === false) {
            throw new \RuntimeException('保存上传公共配置失败');
        }
        $this->invalidateUploadCache();

        return true;
    }

    /**
     * 保存驱动配置到 eb_driver 表的 config_fields 中
     * @param string $driverKey
     * @param array $configValues
     * @return bool
     */
    public function saveDriverConfig(string $driverKey, array $configValues)
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        $driver->config_fields = $this->validateDriverConfig($driver->config_fields ?: [], $configValues);
        $driver->update_time = time();
        if ($driver->save() === false) {
            throw new \RuntimeException('保存存储驱动配置失败');
        }
        $this->invalidateUploadCache();

        return true;
    }

    /**
     * 更新驱动信息
     * @param int $id
     * @param array $data
     * @param array|null $configValues 同时保存的配置字段
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update(int $id, array $data, ?array $configValues = null)
    {
        if (array_diff_key($data, array_flip(['driver_name', 'sort', 'status', 'thumb_watermark_config']))) {
            throw new AdminException('不支持的存储驱动字段');
        }
        if (array_key_exists('status', $data)) {
            $data['status'] = $this->configInteger($data['status'], 0, 1, '驱动状态');
        }
        if (array_key_exists('sort', $data)) {
            $data['sort'] = $this->configInteger($data['sort'], 0, 9999, '排序');
        }
        if (array_key_exists('driver_name', $data) && (!is_string($data['driver_name']) || trim($data['driver_name']) === '')) {
            throw new AdminException('请输入驱动名称');
        }
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        if (($data['status'] ?? 0) === 1 && !DriverRegistry::get('upload', (string)$driver->driver_key)) {
            throw new AdminException('请先启用对应的驱动插件');
        }
        if ($configValues !== null || ($data['status'] ?? 0) === 1) {
            $data['config_fields'] = $this->validateDriverConfig($driver->config_fields ?: [], $configValues ?? []);
        }
        if (array_key_exists('thumb_watermark_config', $data)) {
            if (!is_array($data['thumb_watermark_config'])) {
                throw new AdminException('上传公共配置类型错误');
            }
            $data['thumb_watermark_config'] = $this->prepareCommonConfig($driver->thumb_watermark_config ?: [], $data['thumb_watermark_config']);
        }
        $this->transaction(function () use ($id, $driver, $data) {
            // 状态切换与配置保存必须一起成功，失败时回滚其他驱动的停用。
            if (($data['status'] ?? 0) === 1 && $driver->driver_key !== 'local'
                && !app()->make(UploadStorageDao::class)->count(['driver_key' => $driver->driver_key, 'status' => 1, 'is_delete' => 0])) {
                throw new AdminException('未有正在使用的存储空间');
            }
            if (($data['status'] ?? 0) === 1 && $this->dao->disableAllExcept($id) === false) {
                throw new \RuntimeException('切换存储驱动失败');
            }
            foreach ($data as $key => $value) {
                $driver->$key = $value;
            }
            $driver->update_time = time();
            if ($driver->save() === false) {
                throw new \RuntimeException('保存存储驱动失败');
            }
        });
        $this->invalidateUploadCache();

        return true;
    }

    /**
     * 校验驱动配置字段定义中的白名单、类型和必填值
     * @param array $fields 字段定义及已保存值
     * @param array $values 本次配置值
     * @return array
     */
    protected function validateDriverConfig(array $fields, array $values): array
    {
        if (array_diff_key($values, array_column($fields, null, 'field'))) {
            throw new AdminException('不支持的驱动配置字段');
        }
        foreach ($fields as &$field) {
            $name = $field['field'];
            $value = array_key_exists($name, $values) ? $values[$name] : ($field['value'] ?? $field['default'] ?? '');
            // 固定占位符仅代表保留既有敏感值，绝不能保存为新凭据。
            if ($this->isSensitiveConfigField($field) && $value === self::CONFIG_MASK) {
                $value = $field['value'] ?? $field['default'] ?? '';
            }
            if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
                throw new AdminException('驱动配置字段类型错误');
            }
            if (($field['required'] ?? false) && (is_string($value) ? trim($value) === '' : $value === null)) {
                throw new AdminException('请填写必填驱动配置');
            }
            if (!($field['required'] ?? false) && $value === '') {
                $field['value'] = '';
                continue;
            }
            $type = $field['type'] ?? 'text';
            if (in_array($type, ['text', 'password', 'textarea'], true)) {
                if (!is_string($value) && !is_int($value)) throw new AdminException('驱动配置字段类型错误');
                $value = (string)$value;
            } elseif ($type === 'number') {
                if (!is_numeric($value) || !is_finite((float)$value)) throw new AdminException('驱动配置字段类型错误');
                $value = 0 + $value;
            } elseif (in_array($type, ['radio', 'select'], true)) {
                if (!in_array((string)$value, array_map('strval', array_column($field['options'] ?? [], 'value')), true)) {
                    throw new AdminException('驱动配置选项无效');
                }
            } elseif ($type === 'switch') {
                $value = $this->configInteger($value, 0, 1, '驱动配置');
            } else {
                throw new AdminException('不支持的驱动配置类型');
            }
            $field['value'] = $value;
        }
        unset($field);
        return $fields;
    }

    /**
     * 校验整数型配置
     * @param mixed $value 配置值
     * @param int $min 最小值
     * @param int $max 最大值
     * @param string $label 配置名称
     * @return int
     */
    protected function configInteger($value, int $min, int $max, string $label): int
    {
        $integer = is_bool($value) ? (int)$value : filter_var($value, FILTER_VALIDATE_INT);
        if ($integer === false || $integer < $min || $integer > $max) {
            throw new AdminException('上传配置 {name} 数值无效', ['name' => $label]);
        }
        return $integer;
    }

    /**
     * 合并公共配置并校验字段、类型和范围，不读取或修改持久化数据
     * @param array $current 已保存配置
     * @param array $config 本次配置
     * @return array
     */
    protected function prepareCommonConfig(array $current, array $config): array
    {
        $defaults = $this->getDefaultCommonConfig();
        if (array_diff_key($config, $defaults)) {
            throw new AdminException('不支持的上传公共配置字段');
        }
        return $this->validateCommonConfig(array_merge($defaults, array_intersect_key($current, $defaults), $config));
    }

    /**
     * 校验公共图片处理配置
     * @param array $config 合并后的配置
     * @return array
     */
    protected function validateCommonConfig(array $config): array
    {
        foreach (['image_thumb_status', 'image_watermark_status'] as $name) {
            $config[$name] = $this->configInteger($config[$name], 0, 1, $name);
        }
        $config['watermark_type'] = $this->configInteger($config['watermark_type'], 1, 2, '水印类型');
        $config['watermark_position'] = $this->configInteger($config['watermark_position'], 1, 9, '水印位置');
        foreach (['thumb_big_width' => 800, 'thumb_big_height' => 800, 'thumb_mid_width' => 300, 'thumb_mid_height' => 300,
            'thumb_small_width' => 150, 'thumb_small_height' => 150] as $name => $default) {
            if ($config[$name] === '' && !$config['image_thumb_status']) continue;
            if ($config[$name] === '') $config[$name] = $default;
            $config[$name] = $this->configInteger($config[$name], $config['image_thumb_status'] ? 1 : 0, 10000, $name);
        }
        foreach (['watermark_opacity' => [0, 100], 'watermark_rotate' => [-360, 360], 'watermark_text_angle' => [-360, 360],
            'watermark_text_size' => [1, 1000], 'watermark_x' => [0, 10000], 'watermark_y' => [0, 10000]] as $name => [$min, $max]) {
            if ($config[$name] !== '') $config[$name] = $this->configInteger($config[$name], $min, $max, $name);
        }
        foreach (['watermark_image', 'watermark_text', 'watermark_text_color'] as $name) {
            if (!is_string($config[$name])) throw new AdminException('水印配置类型错误');
        }
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $config['watermark_text_color'])) {
            throw new AdminException('水印文字颜色无效');
        }
        if ($config['image_watermark_status']) {
            $required = $config['watermark_type'] === 1 ? 'watermark_image' : 'watermark_text';
            if (trim($config[$required]) === '') throw new AdminException('请填写水印内容');
            if ($config['watermark_type'] === 2 && $config['watermark_text_size'] === '') throw new AdminException('请填写水印文字大小');
        }
        return $config;
    }

    /**
     * 判断凭据型配置字段
     * @param array $field 字段定义
     * @return bool
     */
    protected function isSensitiveConfigField(array $field): bool
    {
        return ($field['type'] ?? '') === 'password'
            || preg_match('/(?:access.?key|secret|token|password|private.?key)/i', $field['field'] ?? '') === 1;
    }

    /**
     * 脱敏配置界面数据，保持原有字段定义结构
     * @param array $fields 字段定义
     * @return array
     */
    protected function redactConfigFields(array $fields): array
    {
        foreach ($fields as &$field) {
            if ($this->isSensitiveConfigField($field)) {
                foreach (['value', 'default'] as $name) {
                    if (isset($field[$name]) && $field[$name] !== '') $field[$name] = self::CONFIG_MASK;
                }
            }
        }
        unset($field);
        return $fields;
    }

    /**
     * 脱敏管理端驱动详情及列表中的凭据
     * @param array $driver 驱动数据
     * @return array
     */
    public function redactDriver(array $driver): array
    {
        $driver['config_fields'] = $this->redactConfigFields(is_array($driver['config_fields'] ?? null) ? $driver['config_fields'] : []);
        return $driver;
    }

    /**
     * 仅失效上传空间缓存与进程内驱动字典
     * @return void
     */
    protected function invalidateUploadCache(): void
    {
        CacheService::delete('storage_list');
        DriverRegistry::resetCache();
    }
}
