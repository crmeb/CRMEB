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

namespace app\services\driver\express;

use app\dao\driver\express\ExpressDriverDao;
use app\dao\driver\express\ExpressCompanyDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\driver\DriverRegistry;
use crmeb\services\logistics\Express;
use think\facade\Log;

/**
 * 电子面单驱动管理服务
 * Class ExpressDriverServices
 * @package app\services\driver\express
 */
class ExpressDriverServices extends BaseService
{
    public function __construct(ExpressDriverDao $dao)
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
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, ['*'], $page, $limit, 'sort desc');
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
     * 获取电子面单类型驱动的中文名称映射（driver_key => driver_name）
     * @return array
     */
    public function getExpressDriverNames(): array
    {
        $names = [];
        $list = $this->getList([])['list'] ?? [];
        foreach ($list as $driver) {
            if (($driver['driver_type'] ?? '') === 'express') {
                $names[$driver['driver_key']] = $driver['driver_name'] ?? '';
            }
        }
        return $names;
    }

    /**
     * 根据 driver_key 获取驱动信息
     * @param string $driverKey
     * @return array|\think\Model|null
     */
    public function getByDriverKey(string $driverKey)
    {
        return $this->dao->getByDriverKey($driverKey);
    }

    /**
     * 获取已入库的 driver_key 列表
     * @return array
     */
    public function getStoredDriverKeys(): array
    {
        return $this->dao->getColumn([], 'driver_key', 'driver_key') ?: [];
    }

    /**
     * 扫描驱动目录，同步驱动信息到数据库
     * @return array
     */
    public function syncDrivers()
    {
        $drivers = $this->scanDrivers();
        $result = ['created' => 0, 'updated' => 0, 'errors' => [], 'deleted' => 0];

        // 收集扫描到的 driver_key
        $scannedKeys = [];
        foreach ($drivers as $driver) {
            $scannedKeys[] = $driver['driver_key'];
        }

        foreach ($drivers as $driver) {
            try {
                $exist = $this->dao->getByDriverKey($driver['driver_key']);
                $data = [
                    'driver_key' => $driver['driver_key'],
                    'driver_name' => $driver['name'],
                    'icon' => $driver['icon'] ?? '',
                    'is_builtin' => $driver['is_builtin'] ? 1 : 0,
                    'config_fields' => $driver['config_fields'] ?? [],
                    'sort' => $driver['sort'] ?? 0,
                    'update_time' => time(),
                ];
                if ($exist) {
                    // 已存在，保留原有 status
                    $data['status'] = $exist->status;
                    $this->dao->update($exist->id, $data);
                    $result['updated']++;
                } else {
                    $data['status'] = 1;
                    $data['add_time'] = time();
                    $this->dao->save($data);
                    $result['created']++;
                }
            } catch (\Throwable $e) {
                $result['errors'][] = $driver['driver_key'] . ': ' . $e->getMessage();
                Log::error('同步电子面单驱动失败: ' . $e->getMessage());
            }
        }

        // 删除已不存在的驱动（目录已被移除）
        $dbKeys = $this->dao->getColumn([], 'driver_key');
        foreach ($dbKeys as $key) {
            if (!in_array($key, $scannedKeys)) {
                $this->dao->delete(['driver_key' => $key]);
                $result['deleted']++;
            }
        }

        CacheService::clear();
        return $result;
    }

    /**
     * 扫描驱动目录
     * 扫描 driver/express/（第三方扩展 + 内置驱动 + 插件安装的驱动）
     * @return array
     */
    public function scanDrivers(): array
    {
        $drivers = [];

        // 统一走驱动注册中心（读取 plugin.json 元数据，带缓存与标准键归一化）
        foreach (DriverRegistry::scan('express') as $info) {
            // 一号通驱动：自动填充账号/密钥
            if ($info['driver_key'] === 'yihaotong' && !empty($info['config_fields'])) {
                $sysConfigMap = [
                    'yihaotong_appid' => 'yihaotong_appid',
                    'yihaotong_appsecret' => 'yihaotong_appsecret',
                ];
                foreach ($info['config_fields'] as &$field) {
                    $configKey = $sysConfigMap[$field['field']] ?? null;
                    if ($configKey) {
                        $field['value'] = sys_config($configKey, '');
                    }
                }
                unset($field);
            }

            // 补充原扫描契约的附加键（完整类名/文件路径/存在性标记）
            $info['namespace'] = $info['namespace'] . '\\' . $info['class_name'];
            $info['file_path'] = root_path() . 'driver/express/' . $info['dir_name'] . '/Driver.php';
            $info['exists'] = file_exists($info['file_path']);
            $info['has_info'] = true;

            $drivers[$info['driver_key']] = $info;
        }

        return $drivers;
    }

    /**
     * 获取驱动详情，一号通驱动自动填充账号/密钥
     * @param int $id
     * @return array
     */
    public function getDetail(int $id): array
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        $data = $driver->toArray();

        // 一号通驱动：账号/密钥从系统配置自动读取
        if ($data['driver_key'] === 'yihaotong') {
            $sysConfigMap = [
                'yihaotong_appid' => 'yihaotong_appid',
                'yihaotong_appsecret' => 'yihaotong_appsecret',
            ];
            foreach ($data['config_fields'] as &$field) {
                $configKey = $sysConfigMap[$field['field']] ?? null;
                if ($configKey) {
                    $field['readonly'] = true;
                    $field['value'] = sys_config($configKey, '');
                }
            }
        }

        // 如果 config_fields 缺少 value 索引，从 plugin.json 补充默认值
        $info = $this->getInfoByDriverKey($data['driver_key']);
        if ($info && !empty($info['config_fields'])) {
            $infoIndex = [];
            foreach ($info['config_fields'] as $f) {
                $infoIndex[$f['field']] = $f;
            }
            foreach ($data['config_fields'] as &$field) {
                if (!isset($field['value']) || $field['value'] === null || $field['value'] === '') {
                    $src = $infoIndex[$field['field']] ?? [];
                    if (isset($src['value'])) {
                        $field['value'] = $src['value'];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 获取驱动 plugin.json 配置
     * @param string $driverKey
     * @return array|null
     */
    public function getInfoByDriverKey(string $driverKey): ?array
    {
        $drivers = $this->scanDrivers();
        return $drivers[$driverKey] ?? null;
    }

    /**
     * 修改驱动状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function setStatus(int $id, int $status)
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }
        $driver->status = $status;
        $driver->update_time = time();
        $driver->save();
        CacheService::clear();
        return true;
    }

    /**
     * 保存驱动配置
     * @param int $id
     * @param array $configValues
     * @return bool
     */
    public function saveConfig(int $id, array $configValues)
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        // 更新基本字段
        $updateData = [];
        if (isset($configValues['driver_name'])) {
            $updateData['driver_name'] = $configValues['driver_name'];
        }
        if (isset($configValues['sort'])) {
            $updateData['sort'] = (int)$configValues['sort'];
        }
        if (isset($configValues['status'])) {
            $updateData['status'] = (int)$configValues['status'];
        }

        // 一号通驱动：账号/密钥由系统配置管理，不存储到 driver 表
        $yihaotongFields = ['yihaotong_appid', 'yihaotong_appsecret'];

        $configFields = is_array($driver->config_fields) ? $driver->config_fields : [];
        foreach ($configFields as &$field) {
            $fieldName = $field['field'];
            if ($driver->driver_key === 'yihaotong' && in_array($fieldName, $yihaotongFields)) {
                continue;
            }
            if (array_key_exists($fieldName, $configValues)) {
                $field['value'] = $configValues[$fieldName];
            }
        }

        $updateData['config_fields'] = $configFields;
        $updateData['update_time'] = time();
        $this->dao->update($id, $updateData);

        CacheService::clear();
        return true;
    }

    /**
     * 排序
     * @param array $data
     * @return bool
     */
    public function sort(array $data)
    {
        foreach ($data as $item) {
            if (isset($item['id']) && isset($item['sort'])) {
                $this->dao->update((int)$item['id'], ['sort' => (int)$item['sort'], 'update_time' => time()]);
            }
        }
        CacheService::clear();
        return true;
    }

    /**
     * 获取启用的驱动标识列表
     * @return array
     */
    public function getEnabledDriverKeys(): array
    {
        $drivers = $this->dao->getEnabledDrivers();
        return array_column($drivers, 'driver_key');
    }

    /**
     * 获取指定驱动的配置字段值
     * @param string $driverKey
     * @param string $fieldName
     * @param mixed $default
     * @return mixed
     */
    public function getConfigValue(string $driverKey, string $fieldName, $default = '')
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver || empty($driver->config_fields)) {
            return $default;
        }
        $configFields = is_array($driver->config_fields) ? $driver->config_fields : [];
        foreach ($configFields as $field) {
            if (isset($field['field']) && $field['field'] === $fieldName) {
                return $field['value'] ?? $default;
            }
        }
        return $default;
    }

    /**
     * 根据快递公司编码获取绑定的驱动标识
     * 优先从 eb_express_company 查找，未找到则回退默认 yihaotong
     * @param string $deliveryCode 快递公司编码（eb_express.code）
     * @return string 驱动标识
     */
    public function getDriverKeyByCode(string $deliveryCode): string
    {
        if (empty($deliveryCode)) {
            return 'yihaotong';
        }
        /** @var ExpressCompanyDao $companyDao */
        $companyDao = app()->make(ExpressCompanyDao::class);
        $company = $companyDao->getByCode($deliveryCode);
        if ($company && !empty($company['driver_key'])) {
            return $company['driver_key'];
        }
        return 'yihaotong';
    }

    /**
     * 根据驱动标识创建 Express 物流服务实例
     * @param string $driverKey 驱动标识
     * @return Express
     */
    public function createExpressService(string $driverKey = 'yihaotong'): Express
    {
        $config = $this->buildDriverConfig($driverKey);
        return app()->make(Express::class, [$driverKey, $config]);
    }

    /**
     * 根据快递公司编码创建 Express 物流服务实例
     * @param string $deliveryCode 快递公司编码
     * @return Express
     */
    public function createExpressServiceByCode(string $deliveryCode): Express
    {
        $driverKey = $this->getDriverKeyByCode($deliveryCode);
        return $this->createExpressService($driverKey);
    }

    /**
     * 获取指定快递公司的云打印机编号
     * 优先取公司级配置，回退到全局设置
     * @param string $deliveryCode 快递公司编码
     * @return string
     */
    public function getCompanySiid(string $deliveryCode): string
    {
        if (!empty($deliveryCode)) {
            /** @var ExpressCompanyDao $companyDao */
            $companyDao = app()->make(ExpressCompanyDao::class);
            $company = $companyDao->getByCode($deliveryCode);
            if ($company && !empty($company['siid'])) {
                return (string)$company['siid'];
            }
        }
        return (string)app()->make(ExpressConfigServices::class)->getItem('siid');
    }

    /**
     * 从 eb_driver 表构建驱动配置数组
     * @param string $driverKey
     * @return array
     */
    private function buildDriverConfig(string $driverKey): array
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver || empty($driver->config_fields)) {
            return [];
        }
        $config = [];
        $fields = is_array($driver->config_fields) ? $driver->config_fields : [];
        foreach ($fields as $field) {
            if (isset($field['field'], $field['value'])) {
                $config[$field['field']] = $field['value'];
            }
        }
        return $config;
    }
}
