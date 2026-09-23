<?php

declare(strict_types=1);

namespace app\services\driver\bot;

use app\dao\driver\bot\BotDriverDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\driver\DriverRegistry;

class BotDriverServices extends BaseService
{
    public function __construct(BotDriverDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where = []): array
    {
        $list = $this->dao->selectList($where, '*', 0, 0, 'sort desc,id asc')->toArray();
        $scannedDrivers = $this->scanDrivers();

        foreach ($list as &$item) {
            $driverKey = $item['driver_key'] ?? '';
            $item['driver_exists'] = isset($scannedDrivers[$driverKey]);
            $item['driver_info'] = $scannedDrivers[$driverKey] ?? null;
        }

        $existingKeys = array_column($list, 'driver_key');
        foreach ($scannedDrivers as $key => $driver) {
            if (!in_array($key, $existingKeys)) {
                $list[] = [
                    'id'            => 0,
                    'driver_key'    => $key,
                    'driver_name'   => $driver['name'],
                    'icon'          => $driver['icon'],
                    'is_builtin'    => $driver['is_builtin'],
                    'config_fields' => $driver['config_fields'],
                    'sort'          => $driver['sort'],
                    'status'        => 0,
                    'driver_exists' => true,
                    'driver_info'   => $driver,
                    'not_synced'    => true,
                ];
            }
        }

        return $list;
    }

    public function getDetail(int $id): array
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('机器人驱动不存在');
        }

        $data = $driver->toArray();

        if (empty($data['config_fields']) && $data['driver_key']) {
            $scannedDrivers = $this->scanDrivers();
            if (isset($scannedDrivers[$data['driver_key']])) {
                $data['config_fields'] = $scannedDrivers[$data['driver_key']]['config_fields'];
            }
        }

        $scannedDrivers = $this->scanDrivers();
        $data['driver_info'] = $scannedDrivers[$data['driver_key']] ?? null;

        return $data;
    }

    public function syncDrivers(): array
    {
        $scannedDrivers = $this->scanDrivers();
        $added = 0;
        $updated = 0;
        $deleted = 0;

        foreach ($scannedDrivers as $driverKey => $info) {
            $existing = $this->dao->getByDriverKey($driverKey);

            if ($existing) {
                $configFields = is_array($existing->config_fields) ? $existing->config_fields : [];

                $newConfigFields = $info['config_fields'];
                foreach ($newConfigFields as &$newField) {
                    $fieldName = $newField['field'];
                    foreach ($configFields as $oldField) {
                        if (($oldField['field'] ?? '') === $fieldName && isset($oldField['value'])) {
                            $newField['value'] = $oldField['value'];
                            break;
                        }
                    }
                }

                $this->dao->update($existing->id, [
                    'driver_name'   => $info['name'],
                    'icon'          => $info['icon'],
                    'is_builtin'    => $info['is_builtin'] ? 1 : 0,
                    'sort'          => $info['sort'],
                    'config_fields' => $newConfigFields,
                ]);
                $updated++;
            } else {
                $this->dao->save([
                    'driver_key'    => $driverKey,
                    'driver_name'   => $info['name'],
                    'icon'          => $info['icon'],
                    'is_builtin'    => $info['is_builtin'] ? 1 : 0,
                    'sort'          => $info['sort'],
                    'config_fields' => $info['config_fields'],
                    'status'        => 0,
                ]);
                $added++;
            }
        }

        // 删除已不存在的驱动（目录已被移除）
        $dbKeys = $this->dao->getColumn([], 'driver_key');
        foreach ($dbKeys as $key) {
            if (!isset($scannedDrivers[$key])) {
                $this->dao->delete(['driver_key' => $key]);
                $deleted++;
            }
        }

        return ['added' => $added, 'updated' => $updated, 'deleted' => $deleted];
    }

    public function updateStatus(int $id, int $status): bool
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('机器人驱动不存在');
        }

        $this->dao->update($id, ['status' => $status]);
        return true;
    }

    public function saveConfig(int $id, array $data): bool
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('机器人驱动不存在');
        }

        $configFields = is_array($driver->config_fields) ? $driver->config_fields : [];
        if (empty($configFields)) {
            $scannedDrivers = $this->scanDrivers();
            $configFields = $scannedDrivers[$driver->driver_key]['config_fields'] ?? [];
        }

        foreach ($configFields as &$field) {
            $fieldName = $field['field'];
            if (isset($data[$fieldName])) {
                $field['value'] = $data[$fieldName];
            }
        }

        $this->dao->update($id, [
            'config_fields' => $configFields,
        ]);

        return true;
    }

    public function setSort(array $sortData): bool
    {
        foreach ($sortData as $item) {
            if (isset($item['id']) && isset($item['sort'])) {
                $this->dao->update((int)$item['id'], ['sort' => (int)$item['sort']]);
            }
        }
        return true;
    }

    public function getDrivers(): array
    {
        return array_values($this->scanDrivers());
    }

    public function getConfigFields(string $driverKey): array
    {
        $scannedDrivers = $this->scanDrivers();
        return $scannedDrivers[$driverKey]['config_fields'] ?? [];
    }

    public function getEnabledDriver(): ?array
    {
        $driver = $this->dao->getActiveDriver();
        return $driver ? $driver->toArray() : null;
    }

    public function getAvailableDrivers(): array
    {
        $list = $this->dao->getAllEnabled();
        $result = [];
        foreach ($list as $driver) {
            $data = $driver->toArray();
            $result[] = [
                'driver_key' => $data['driver_key'],
                'name'       => $data['driver_name'],
                'icon'       => $data['icon'],
            ];
        }
        return $result;
    }

    /**
     * 扫描 driver/bot/（第三方扩展 + 内置驱动 + 插件安装的驱动）
     * @return array
     */
    protected function scanDrivers(): array
    {
        // 统一走驱动注册中心（读取 plugin.json 元数据，带缓存与标准键归一化）
        return DriverRegistry::scan('bot');
    }
}
