<?php
declare(strict_types=1);

namespace app\services\driver\express;

use app\dao\driver\express\ExpressSettingDao;
use crmeb\basic\BaseService;
use crmeb\services\CacheService;
use think\facade\Db;

/** 电子面单业务设置，与驱动服务商配置分开存储。 */
class ExpressConfigServices extends BaseService
{
    private const SETTING_ID = 1;
    private static ?array $configCache = null;

    public function __construct(ExpressSettingDao $dao)
    {
        $this->dao = $dao;
    }

    public function getConfig(): array
    {
        if (self::$configCache !== null) {
            return self::$configCache;
        }

        $setting = $this->dao->get(self::SETTING_ID);
        if (!$setting) {
            $data = $this->legacyConfig();
            $this->dao->save($this->toStorage($data) + [
                'id' => self::SETTING_ID,
                'update_time' => time(),
            ]);
            $this->stripLegacyBusinessFields();
            return self::$configCache = $data;
        }

        $data = $setting->toArray();
        $this->stripLegacyBusinessFields();
        return self::$configCache = [
            'open' => (int)($data['open'] ?? 0),
            'siid' => (string)($data['siid'] ?? ''),
            'id' => (string)($data['company_id'] ?? ''),
            'temp_id' => (string)($data['temp_id'] ?? ''),
            'com' => (string)($data['com'] ?? ''),
            'to_name' => (string)($data['to_name'] ?? ''),
            'to_tel' => (string)($data['to_tel'] ?? ''),
            'to_address' => (string)($data['to_address'] ?? ''),
        ];
    }

    public function saveConfig(array $data): bool
    {
        $config = array_merge($this->getConfig(), array_intersect_key($data, $this->defaults()));
        $storage = $this->toStorage($config) + ['update_time' => time()];
        if ($this->dao->get(self::SETTING_ID)) {
            $this->dao->update(self::SETTING_ID, $storage);
        } else {
            $this->dao->save($storage + ['id' => self::SETTING_ID]);
        }
        $this->clearCache();
        CacheService::clear();
        return true;
    }

    public function getItem(string $key, $default = '')
    {
        return $this->getConfig()[$key] ?? $default;
    }

    public function checkAvailable(): array
    {
        $config = $this->getConfig();
        $missing = [];
        if (empty($config['siid'])) $missing[] = '云打印机编号';
        if (empty($config['com'])) $missing[] = '快递公司编码';
        if (empty($config['to_name'])) $missing[] = '发货人姓名';
        if (empty($config['to_tel'])) $missing[] = '发货人电话';
        if (empty($config['to_address'])) $missing[] = '发货人详细地址';
        return [
            'available' => empty($missing),
            'open' => (int)$config['open'] === 1,
            'missing' => $missing,
        ];
    }

    public function clearCache(): void
    {
        self::$configCache = null;
    }

    public function getDeliveryInfo(): array
    {
        $config = $this->getConfig();
        return [
            'express_temp_id' => $config['temp_id'],
            'to_name' => $config['to_name'],
            'id' => $config['id'],
            'to_tel' => $config['to_tel'],
            'to_add' => $config['to_address'],
            'export_open' => (bool)$config['open'],
        ];
    }

    private function defaults(): array
    {
        return [
            'open' => 0,
            'siid' => '',
            'id' => '',
            'temp_id' => '',
            'com' => '',
            'to_name' => '',
            'to_tel' => '',
            'to_address' => '',
        ];
    }

    /** 首次读取时迁移旧版驱动 config_fields 中的业务配置。 */
    private function legacyConfig(): array
    {
        $result = $this->defaults();
        // express 旧表作兜底，logistics 后读取以保持旧服务的优先级。
        foreach (['express', 'logistics'] as $type) {
            $raw = Db::name('driver')
                ->where('driver_type', $type)
                ->where('driver_key', 'yihaotong')
                ->value('config_fields');
            $fields = is_string($raw) ? (json_decode($raw, true) ?: []) : (array)$raw;
            foreach ($fields as $field) {
                $key = $field['field'] ?? '';
                if (array_key_exists($key, $result) && array_key_exists('value', $field)) {
                    $result[$key] = $field['value'];
                }
            }
        }
        $result['open'] = (int)$result['open'];
        return $result;
    }

    private function toStorage(array $config): array
    {
        return [
            'open' => (int)($config['open'] ?? 0),
            'siid' => (string)($config['siid'] ?? ''),
            'company_id' => (string)($config['id'] ?? ''),
            'temp_id' => (string)($config['temp_id'] ?? ''),
            'com' => (string)($config['com'] ?? ''),
            'to_name' => (string)($config['to_name'] ?? ''),
            'to_tel' => (string)($config['to_tel'] ?? ''),
            'to_address' => (string)($config['to_address'] ?? ''),
        ];
    }

    /** 驱动配置只保留服务商接口字段，清理旧版混入的业务设置。 */
    private function stripLegacyBusinessFields(): void
    {
        $businessKeys = array_keys($this->defaults());
        foreach (['logistics', 'express'] as $type) {
            $row = Db::name('driver')
                ->where('driver_type', $type)
                ->where('driver_key', 'yihaotong')
                ->field('id,config_fields')
                ->find();
            if (!$row) {
                continue;
            }
            $fields = is_string($row['config_fields'])
                ? (json_decode($row['config_fields'], true) ?: [])
                : (array)$row['config_fields'];
            $filtered = array_values(array_filter($fields, static function (array $field) use ($businessKeys): bool {
                return !in_array((string)($field['field'] ?? ''), $businessKeys, true);
            }));
            if (count($filtered) !== count($fields)) {
                Db::name('driver')
                    ->where('id', $row['id'])
                    ->where('driver_type', $type)
                    ->update([
                        'config_fields' => json_encode($filtered, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ]);
            }
        }
    }
}
