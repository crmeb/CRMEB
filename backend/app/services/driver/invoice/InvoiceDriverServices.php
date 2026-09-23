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

namespace app\services\driver\invoice;

use app\dao\driver\invoice\InvoiceDriverDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\driver\DriverRegistry;

/**
 * 发票驱动管理服务
 *
 * 配置值存储在 config_fields JSON 的 value 属性中
 *
 * Class InvoiceDriverServices
 * @package app\services\driver\invoice
 * @method array|object|null get($id, ?array $field = []) 获取一条数据
 * @method update($id, array $data, ?string $key = null) 修改数据
 */
class InvoiceDriverServices extends BaseService
{
    /**
     * 初始化
     * @param InvoiceDriverDao $dao
     */
    public function __construct(InvoiceDriverDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取驱动列表（含扫描状态）
     * @param array $where
     * @return array
     */
    public function getList(array $where = []): array
    {
        $list = $this->dao->selectList($where, '*', 0, 0, 'sort desc,id asc')->toArray();
        $scannedDrivers = $this->scanDrivers();

        // 为每条记录附加驱动扫描状态
        foreach ($list as &$item) {
            $driverKey = $item['driver_key'] ?? '';
            $item['driver_exists'] = isset($scannedDrivers[$driverKey]);
            $item['driver_info'] = $scannedDrivers[$driverKey] ?? null;
        }

        // 附加数据库中不存在但目录中存在的驱动（未同步的）
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

    /**
     * 获取单条详情（含当前配置值）
     * @param int $id
     * @return array
     */
    public function getDetail(int $id): array
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('发票驱动不存在');
        }

        $data = $driver->toArray();

        // 如果数据库中没有 config_fields，从 plugin.json 读取
        if (empty($data['config_fields']) && $data['driver_key']) {
            $scannedDrivers = $this->scanDrivers();
            if (isset($scannedDrivers[$data['driver_key']])) {
                $data['config_fields'] = $scannedDrivers[$data['driver_key']]['config_fields'];
            }
        }

        // 一号通驱动：账号/密钥从系统配置自动读取，标记为只读
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

        // 附加驱动扫描信息
        $scannedDrivers = $this->scanDrivers();
        $data['driver_info'] = $scannedDrivers[$data['driver_key']] ?? null;

        return $data;
    }

    /**
     * 同步驱动信息：扫描 driver/invoice/ 目录，将 plugin.json 信息写入数据库
     * @return array ['added' => int, 'updated' => int]
     */
    public function syncDrivers(): array
    {
        $scannedDrivers = $this->scanDrivers();
        $added = 0;
        $updated = 0;
        $deleted = 0;

        foreach ($scannedDrivers as $driverKey => $info) {
            $existing = $this->dao->getByDriverKey($driverKey);

            if ($existing) {
                // 已存在：更新基本信息但保留 config_fields 中的配置值和状态
                $configFields = is_array($existing->config_fields) ? $existing->config_fields : [];

                // 用 plugin.json 的字段定义更新，但保留已有的 value
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
                // 新增：默认禁用
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

    /**
     * 修改驱动状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function updateStatus(int $id, int $status): bool
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('发票驱动不存在');
        }

        $this->dao->update($id, ['status' => $status]);
        return true;
    }

    /**
     * 保存驱动配置
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function saveConfig(int $id, array $data): bool
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('发票驱动不存在');
        }

        // 获取当前 config_fields 定义
        $configFields = is_array($driver->config_fields) ? $driver->config_fields : [];
        if (empty($configFields)) {
            $scannedDrivers = $this->scanDrivers();
            $configFields = $scannedDrivers[$driver->driver_key]['config_fields'] ?? [];
        }

        // 更新配置值到 config_fields 的 value 属性
        foreach ($configFields as &$field) {
            $fieldName = $field['field'];
            // 一号通驱动：账号/密钥由系统配置管理，不存储到 driver 表
            if ($driver->driver_key === 'yihaotong' && in_array($fieldName, ['yihaotong_appid', 'yihaotong_appsecret'])) {
                continue;
            }
            if (isset($data[$fieldName])) {
                $field['value'] = $data[$fieldName];
            }
        }

        // 配置值存储到 eb_driver 表的 config_fields JSON 中
        $this->dao->update($id, [
            'config_fields' => $configFields,
        ]);

        return true;
    }

    /**
     * 批量排序
     * @param array $sortData
     * @return bool
     */
    public function setSort(array $sortData): bool
    {
        foreach ($sortData as $item) {
            if (isset($item['id']) && isset($item['sort'])) {
                $this->dao->update((int)$item['id'], ['sort' => (int)$item['sort']]);
            }
        }
        return true;
    }

    /**
     * 获取已扫描的驱动列表
     * @return array
     */
    public function getDrivers(): array
    {
        return array_values($this->scanDrivers());
    }

    /**
     * 获取驱动配置字段定义
     * @param string $driverKey
     * @return array
     */
    public function getConfigFields(string $driverKey): array
    {
        $scannedDrivers = $this->scanDrivers();
        return $scannedDrivers[$driverKey]['config_fields'] ?? [];
    }

    /**
     * 获取当前启用的驱动
     * @return array|null
     */
    public function getEnabledDriver(): ?array
    {
        $driver = $this->dao->getActiveDriver();
        return $driver ? $driver->toArray() : null;
    }

    /**
     * 获取可用的驱动列表
     * @return array
     */
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
     * 扫描 driver/invoice/（第三方扩展 + 内置驱动 + 插件安装的驱动）
     * @return array
     */
    protected function scanDrivers(): array
    {
        // 统一走驱动注册中心（读取 plugin.json 元数据，带缓存与标准键归一化）
        return DriverRegistry::scan('invoice');
    }
}
