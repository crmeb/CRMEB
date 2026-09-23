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

namespace app\services\driver\logistics;

use app\dao\driver\logistics\LogisticsDriverDao;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\driver\DriverRegistry;

/**
 * 物流查询驱动管理服务
 * Class LogisticsDriverServices
 * @package app\services\driver\logistics
 */
class LogisticsDriverServices extends BaseService
{
    /**
     * @var LogisticsDriverDao
     */
    protected $dao;

    /**
     * LogisticsDriverServices constructor.
     * @param LogisticsDriverDao $dao
     */
    public function __construct(LogisticsDriverDao $dao)
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
     * 获取当前启用的物流查询驱动
     * @return array|null
     */
    public function getEnabledDriver(): ?array
    {
        return $this->dao->getEnabledDriver();
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
     */
    public function getById(int $id)
    {
        return $this->dao->get($id);
    }

    /**
     * 获取驱动详情（含配置字段）
     * @param int $id
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
            $info = DriverRegistry::get('logistics', 'yihaotong');
            if ($info) {
                $configFields = $info['config_fields'] ?? [];
                foreach ($configFields as &$field) {
                    if ($field['field'] === 'yihaotong_appid') {
                        $field['readonly'] = true;
                        $field['value'] = sys_config('yihaotong_appid', '');
                    } elseif ($field['field'] === 'yihaotong_appsecret') {
                        $field['readonly'] = true;
                        $field['value'] = sys_config('yihaotong_appsecret', '');
                    }
                }
                $data['config_fields'] = $configFields;
            }
        } elseif (!empty($data['driver_key'])) {
            // 非一号通驱动：DB 中没有 config_fields 时从注册中心读取
            if (empty($data['config_fields'])) {
                $info = DriverRegistry::get('logistics', $data['driver_key']);
                if ($info) {
                    $data['config_fields'] = $info['config_fields'] ?? [];
                }
            }
        }

        return $data;
    }

    /**
     * 扫描物流驱动目录并同步到数据库
     * 扫描 driver/logistics/（第三方扩展 + 内置驱动 + 插件安装的驱动）
     * @return array 返回扫描到的驱动列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function scanDrivers()
    {
        $drivers = [];

        // 统一走驱动注册中心（读取 plugin.json 元数据），输出按原契约键白名单构造
        foreach (DriverRegistry::scan('logistics') as $info) {
            $configFields = $info['config_fields'];

            // 一号通驱动：从系统配置读取 appid/appsecret
            if ($info['driver_key'] === 'yihaotong') {
                foreach ($configFields as &$field) {
                    if ($field['field'] === 'yihaotong_appid') {
                        $field['value'] = sys_config('yihaotong_appid', '');
                    } elseif ($field['field'] === 'yihaotong_appsecret') {
                        $field['value'] = sys_config('yihaotong_appsecret', '');
                    }
                }
                unset($field);
            }

            $drivers[] = [
                'driver_key' => $info['driver_key'],
                'driver_name' => $info['name'],
                'class_name' => $info['class_name'],
                'icon' => $info['icon'],
                'capabilities' => json_encode($info['capabilities'], JSON_UNESCAPED_UNICODE),
                'config_fields' => json_encode($configFields, JSON_UNESCAPED_UNICODE),
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
     * 同步物流驱动信息到数据库
     * @return array 返回同步结果
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
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
            $isBuiltin = $driver['is_builtin'];
            unset($driver['is_builtin']);

            if (in_array($driverKey, $existingKeys)) {
                // 更新现有驱动（不覆盖 status）
                $this->dao->update($driverKey, $driver, 'driver_key');
                $updated++;
            } else {
                // 新增驱动
                $driver['status'] = $isBuiltin ? 1 : 0; // 内置驱动默认启用
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
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        // 启用时，先禁用其他所有驱动（同一时间只能启用一个物流查询驱动）
        if ($status == 1) {
            $this->dao->disableAllExcept($driver->id);
        }

        $driver->status = $status;
        $driver->update_time = time();
        $driver->save();

        CacheService::clear();

        return true;
    }

    /**
     * 获取驱动的配置字段定义
     * @param string $driverKey
     * @return array
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getConfigFields(string $driverKey)
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            return [];
        }

        return $driver->config_fields ?? [];
    }

    /**
     * 保存驱动配置
     * @param string $driverKey
     * @param array $configValues
     * @return bool
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveDriverConfig(string $driverKey, array $configValues)
    {
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        // 一号通物流驱动：账号/密钥由系统配置管理，不存储到 driver 表
        $yihaotongFields = ['yihaotong_appid', 'yihaotong_appsecret'];

        // 读取现有 config_fields，将每个字段的 default 更新为用户填写的值
        $configFields = is_array($driver->config_fields) ? $driver->config_fields : [];
        foreach ($configFields as &$field) {
            $fieldName = $field['field'];
            if ($driver->driver_key === 'yihaotong' && in_array($fieldName, $yihaotongFields)) {
                continue;
            }
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

    /**
     * 更新驱动信息
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update(int $id, array $data)
    {
        $driver = $this->dao->get($id);
        if (!$driver) {
            throw new AdminException('驱动不存在');
        }

        // 启用时，先禁用其他所有驱动（只能同时启用一个）
        if (isset($data['status']) && $data['status'] == 1) {
            $this->dao->disableAllExcept($id);
        }

        foreach ($data as $key => $value) {
            $driver->$key = $value;
        }
        $driver->update_time = time();
        $driver->save();

        // 清除缓存
        CacheService::clear();

        return true;
    }
}
