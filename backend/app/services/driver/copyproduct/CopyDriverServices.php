<?php

namespace app\services\driver\copyproduct;

use app\dao\driver\copyproduct\CopyDriverDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\driver\DriverRegistry;

class CopyDriverServices extends BaseService
{
    protected $dao;

    public function __construct(CopyDriverDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where = [])
    {
        [$page, $limit] = $this->getPageValue();
        $list = $this->dao->getList($where, ['*'], $page, $limit, 'sort desc');
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    public function getEnabledDrivers()
    {
        return $this->dao->getEnabledDrivers();
    }

    public function getByDriverKey(string $driverKey)
    {
        return $this->dao->getByDriverKey($driverKey);
    }

    public function getById(int $id)
    {
        return $this->dao->get($id);
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

        // 如果数据库中没有 config_fields，从 plugin.json 读取（兼容未同步的情况）
        if (empty($data['config_fields']) && $data['driver_key']) {
            $scannedDrivers = $this->scanDrivers();
            foreach ($scannedDrivers as $scanned) {
                if ($scanned['driver_key'] === $data['driver_key']) {
                    $data['config_fields'] = $scanned['config_fields'];
                    break;
                }
            }
        }

        // 一号通采集驱动：账号/密钥从系统配置自动读取，标记为只读
        if ($data['driver_key'] === 'yihaotong') {
            foreach ($data['config_fields'] as &$field) {
                if (in_array($field['field'], ['yihaotong_appid', 'yihaotong_appsecret'])) {
                    $field['readonly'] = true;
                    if ($field['field'] === 'yihaotong_appid') {
                        $field['value'] = sys_config('yihaotong_appid', '');
                    } else {
                        $field['value'] = sys_config('yihaotong_appsecret', '');
                    }
                }
            }
        }

        // 附加驱动扫描信息（供前端展示驱动描述、帮助文档等）
        $scannedDrivers = $this->scanDrivers();
        $data['driver_info'] = null;
        foreach ($scannedDrivers as $scanned) {
            if ($scanned['driver_key'] === $data['driver_key']) {
                $data['driver_info'] = $scanned;
                break;
            }
        }

        return $data;
    }

    /**
     * 扫描 driver/copyproduct/（第三方扩展驱动 + 内置驱动 + 插件安装的驱动）
     * @return array
     */
    public function scanDrivers()
    {
        $drivers = [];

        // 统一走驱动注册中心（读取 plugin.json 元数据，带缓存与标准键归一化）
        // 输出按原契约键白名单构造，避免注册中心的额外键（dir_name/namespace 等）被 syncDrivers 落库
        foreach (DriverRegistry::scan('copyproduct') as $info) {
            $drivers[] = [
                'driver_key' => $info['driver_key'],
                'driver_name' => $info['name'],
                'class_name' => $info['class_name'],
                'icon' => $info['icon'],
                'description' => $info['description'] ?? '',
                'is_builtin' => (int)($info['is_builtin'] ?? 0),
                'capabilities' => $info['capabilities'],
                'platforms' => $info['platforms'] ?? [],
                'config_fields' => $info['config_fields'],
                'sort' => $info['sort'],
            ];
        }

        usort($drivers, function ($a, $b) {
            return $b['sort'] - $a['sort'];
        });

        return $drivers;
    }

    public function syncDrivers()
    {
        $scanned = $this->scanDrivers();
        $existing = $this->dao->getList([], ['driver_key', 'id', 'status']);
        $existingKeys = array_column($existing, 'driver_key');

        $added = 0;
        $updated = 0;
        $deleted = 0;

        foreach ($scanned as $driver) {
            $driverKey = $driver['driver_key'];

            if (in_array($driverKey, $existingKeys)) {
                // 已存在：更新基本信息但保留 config_fields 中的配置值
                $existingDriver = $this->dao->getByDriverKey($driverKey);
                $configFields = is_array($existingDriver->config_fields) ? $existingDriver->config_fields : [];
                $newConfigFields = $driver['config_fields'];
                foreach ($newConfigFields as &$newField) {
                    $fieldName = $newField['field'];
                    foreach ($configFields as $oldField) {
                        if (($oldField['field'] ?? '') === $fieldName && isset($oldField['value'])) {
                            $newField['value'] = $oldField['value'];
                            break;
                        }
                    }
                }

                $this->dao->update($driverKey, [
                    'driver_name'   => $driver['driver_name'],
                    'class_name'    => $driver['class_name'],
                    'icon'          => $driver['icon'],
                    'description'   => $driver['description'],
                    'is_builtin'    => (int)($driver['is_builtin'] ?? 0),
                    'capabilities'  => $driver['capabilities'],
                    'platforms'     => $driver['platforms'],
                    'config_fields' => $newConfigFields,
                    'sort'          => $driver['sort'],
                ], 'driver_key');
                $updated++;
            } else {
                // 与驱动插件安装路径（installDriverRecord）同规则：内置驱动注册后默认开启
                $driver['status'] = !empty($driver['is_builtin']) ? 1 : 0;
                $driver['add_time'] = time();
                $driver['update_time'] = time();
                $this->dao->save($driver);
                $added++;
            }
        }

        // 删除已不存在的驱动（目录已被移除）
        $scannedKeys = array_column($scanned, 'driver_key');
        foreach ($existingKeys as $key) {
            if (!in_array($key, $scannedKeys)) {
                $this->dao->delete(['driver_key' => $key]);
                $deleted++;
            }
        }

        CacheService::clear();

        return [
            'scanned' => count($scanned),
            'added' => $added,
            'updated' => $updated,
            'deleted' => $deleted,
        ];
    }

    public function updateStatus(string $driverKey, int $status)
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        if ($status == 1) {
            $this->dao->disableAllExcept($driver->id);
        }

        $driver->status = $status;
        $driver->update_time = time();
        $driver->save();

        CacheService::clear();

        return true;
    }

    public function saveDriverConfig(string $driverKey, array $configValues)
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        $configFields = is_array($driver->config_fields) ? $driver->config_fields : [];
        foreach ($configFields as &$field) {
            $fieldName = $field['field'];
            if (isset($configValues[$fieldName])) {
                $field['value'] = $configValues[$fieldName];
            }
        }
        $driver->config_fields = $configFields;
        $driver->update_time = time();
        $driver->save();

        CacheService::clear();

        return true;
    }

    public function update(int $id, array $data)
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        if (isset($data['status']) && $data['status'] == 1) {
            $this->dao->disableAllExcept($id);
        }

        foreach ($data as $key => $value) {
            $driver->$key = $value;
        }
        $driver->update_time = time();
        $driver->save();

        CacheService::clear();

        return true;
    }
}