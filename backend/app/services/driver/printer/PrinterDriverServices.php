<?php
declare(strict_types=1);

namespace app\services\driver\printer;

use app\dao\driver\printer\PrinterDriverDao;
use app\services\driver\email\EmailDriverServices;
use crmeb\exceptions\AdminException;
use crmeb\plugin\PluginManifestRepository;
use crmeb\services\driver\DriverRegistry;
use think\Validate;

/**
 * 打印机驱动管理服务，设备配置和服务商凭据分开存储。
 */
class PrinterDriverServices extends EmailDriverServices
{
    /** @var PrinterDriverDao 打印驱动数据访问层 */
    protected $dao;

    /** 配置表单中已保存敏感值的占位符。 */
    public const CONFIG_MASK = '********';

    /**
     * 初始化打印机驱动服务。
     * @param PrinterDriverDao $dao
     */
    public function __construct(PrinterDriverDao $dao)
    {
        parent::__construct($dao);
    }

    /**
     * 修改单个驱动状态，打印驱动允许并行启用。
     * @param int $id
     * @param mixed $status
     * @return bool
     */
    public function updateStatus(int $id, $status): bool
    {
        return $this->saveConfig($id, ['status' => $status]);
    }

    /**
     * 校验配置后一次性保存，掩码保留原值，不透传任意字段。
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function saveConfig(int $id, array $data): bool
    {
        $driver = $this->requireInstalledDriver($id);
        $fields = $this->driverConfigFields($driver->toArray());
        $allowed = array_fill_keys(array_merge(['status'], array_column($fields, 'field')), true);
        if (array_diff_key($data, $allowed)) {
            throw new AdminException('存在不允许修改的打印机驱动字段');
        }
        $status = array_key_exists('status', $data) ? $this->validStatus($data['status']) : (int)$driver->status;
        foreach ($fields as &$field) {
            $name = $field['field'];
            $value = array_key_exists($name, $data) ? $data[$name] : ($field['value'] ?? $field['default'] ?? '');
            if (!is_string($value) && !is_int($value)) {
                throw new AdminException('打印机驱动配置值必须是文本');
            }
            if ($value === self::CONFIG_MASK && $this->sensitiveField($field)) {
                $value = $field['value'] ?? $field['default'] ?? '';
                if ($value === '') {
                    throw new AdminException('尚未配置的密钥不能使用占位符');
                }
            }
            $value = trim((string)$value);
            $required = ($field['required'] ?? true) !== false && ($field['type'] ?? 'text') !== 'switch';
            if ((($status === 1 || array_key_exists($name, $data)) && $required && $value === '') || mb_strlen($value) > 2000) {
                throw new AdminException('请完整填写打印机驱动配置');
            }
            $field['value'] = $value;
        }
        unset($field);
        if ($status === 1 && !DriverRegistry::get('printer', (string)$driver->driver_key)) {
            throw new AdminException('打印机驱动插件未启用');
        }
        $update = ['config_fields' => $fields];
        if (array_key_exists('status', $data)) {
            $update['status'] = $status;
        }
        if ($this->dao->update($id, $update) === false) {
            throw new \RuntimeException('保存打印机驱动配置失败');
        }
        DriverRegistry::resetCache();
        return true;
    }

    /**
     * 获取已安装驱动列表，界面不可读取真实密钥。
     * @param array $where
     * @return array
     */
    public function getList(array $where = []): array
    {
        $where = array_key_exists('status', $where) ? ['printer.status' => $this->validStatus($where['status'])] : [];
        $manifests = (new PluginManifestRepository(root_path()))->drivers('printer');
        $list = $this->dao->getInstalledList($where);
        foreach ($list as &$item) {
            $manifest = $manifests[(string)$item['owner_plugin_id']] ?? null;
            $item['driver_exists'] = $manifest !== null;
            $item['driver_info'] = $manifest ? array_merge($manifest->meta['driver'] ?? [], [
                'version' => $manifest->version,
                'author' => (string)($manifest->meta['author'] ?? ''),
                'description' => (string)($manifest->meta['description'] ?? ''),
            ]) : null;
            $item = $this->publicDriver($item);
        }
        unset($item);
        return $list;
    }

    /**
     * 获取脱敏的驱动详情和配置字段定义。
     * @param int $id
     * @return array
     */
    public function getDetail(int $id): array
    {
        $data = $this->requireInstalledDriver($id)->toArray();
        $data['config_fields'] = $this->driverConfigFields($data);
        $manifest = (new PluginManifestRepository(root_path()))->drivers('printer')[(string)$data['owner_plugin_id']] ?? null;
        $data['driver_info'] = $manifest ? ($manifest->meta['driver'] ?? []) : null;
        return $this->publicDriver($data);
    }

    /**
     * 仅刷新已安装驱动元数据，创建和清理由插件生命周期管理。
     * @return array
     */
    public function syncDrivers(): array
    {
        $repository = new PluginManifestRepository(root_path());
        $manifests = $repository->drivers('printer');
        $updated = 0;
        foreach ($this->dao->getInstalledList() as $existing) {
            $manifest = $manifests[(string)$existing['owner_plugin_id']] ?? null;
            if (!$manifest) {
                continue;
            }
            $meta = $manifest->meta['driver'];
            $oldFields = $existing['config_fields'] ?? [];
            $oldFields = is_string($oldFields) ? (json_decode($oldFields, true) ?: []) : $oldFields;
            $values = array_column($oldFields, 'value', 'field');
            $fields = $meta['config_fields'] ?? [];
            foreach ($fields as &$field) {
                if (array_key_exists($field['field'] ?? '', $values)) {
                    $field['value'] = $values[$field['field']];
                }
            }
            unset($field);
            $row = [
                'driver_name' => (string)($meta['name'] ?? $manifest->name),
                'class_name' => (string)($meta['class_name'] ?? 'Driver'),
                'namespace' => 'driver\\printer\\' . basename($manifest->basePath),
                'icon' => (string)($meta['icon'] ?? ''),
                'description' => (string)($meta['description'] ?? $manifest->meta['description'] ?? ''),
                'is_builtin' => !empty($meta['is_builtin']) ? 1 : 0,
                'capabilities' => json_encode($meta['capabilities'] ?? [], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'config_fields' => $fields,
                'help_doc' => json_encode($meta['help_doc'] ?? [], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'sort' => (int)($meta['sort'] ?? 0),
                'update_time' => time(),
            ];
            if ($this->dao->update((int)$existing['id'], $row) === false) {
                throw new \RuntimeException('同步打印机驱动失败');
            }
            ++$updated;
        }
        DriverRegistry::resetCache();
        return ['added' => 0, 'updated' => $updated, 'deleted' => 0];
    }

    /**
     * 获取注册中心的运行时打印驱动。
     * @return array
     */
    protected function scanDrivers(): array
    {
        return DriverRegistry::scan('printer');
    }

    /**
     * 获取插件和驱动开关同时启用的原始配置，仅供内部打印使用。
     * @param string $driverKey
     * @return array|null
     */
    public function getEnabledDriverByKey(string $driverKey): ?array
    {
        if (!DriverRegistry::get('printer', $driverKey)) {
            return null;
        }
        $driver = $this->dao->getByDriverKey($driverKey);
        if (!$driver || !$driver->status) {
            return null;
        }
        return $driver->toArray();
    }

    /**
     * 查找安装日志确认的驱动，不能操作其他类型或已卸载的字典记录。
     * @param int $id
     * @return \app\model\driver\printer\PrinterDriver
     */
    protected function requireInstalledDriver(int $id)
    {
        $driver = $id > 0 ? $this->dao->getInstalledDriver($id) : null;
        if (!$driver) {
            throw new AdminException('打印机驱动不存在');
        }
        return $driver;
    }

    /**
     * 读取配置字段定义，尚未配置时从首次安装清单读取。
     * @param array $driver
     * @return array
     */
    protected function driverConfigFields(array $driver): array
    {
        $fields = $driver['config_fields'] ?? [];
        if (is_string($fields)) {
            $fields = json_decode($fields, true);
        }
        if (!$fields) {
            $manifest = (new PluginManifestRepository(root_path()))->drivers('printer')[(string)$driver['owner_plugin_id']] ?? null;
            $fields = $manifest ? ($manifest->meta['driver']['config_fields'] ?? []) : [];
        }
        if (!is_array($fields) || !$fields) {
            throw new AdminException('打印机驱动配置定义不存在');
        }
        return $fields;
    }

    /**
     * 严格校验开关，防止非法文本被强制转换为关闭。
     * @param mixed $status
     * @return int
     */
    protected function validStatus($status): int
    {
        (new Validate())->rule(['status' => [static fn($value) => in_array($value, [0, 1, '0', '1'], true) ? true : '打印机驱动状态无效']])
            ->failException(true)->check(['status' => $status]);
        return (int)$status;
    }

    /**
     * 判断配置字段是否包含敏感凭据。
     * @param array $field
     * @return bool
     */
    protected function sensitiveField(array $field): bool
    {
        return ($field['type'] ?? '') === 'password' || (bool)preg_match('/secret|password|token|ukey|api.?key/i', (string)($field['field'] ?? ''));
    }

    /**
     * 脱敏全部公开配置副本，包括嵌套驱动元数据中的默认值。
     * @param array $driver
     * @return array
     */
    protected function publicDriver(array $driver): array
    {
        $fields = $driver['config_fields'] ?? [];
        $fields = is_string($fields) ? (json_decode($fields, true) ?: []) : $fields;
        foreach ($fields as &$field) {
            if ($this->sensitiveField($field)) {
                foreach (['value', 'default'] as $name) {
                    if (isset($field[$name]) && $field[$name] !== '') {
                        $field[$name] = self::CONFIG_MASK;
                    }
                }
            }
        }
        unset($field);
        $driver['config_fields'] = $fields;
        if (is_array($driver['driver_info'] ?? null)) {
            $driver['driver_info'] = $this->publicDriver($driver['driver_info']);
        }
        return $driver;
    }
}
