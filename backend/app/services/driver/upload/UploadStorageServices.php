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


use app\dao\driver\upload\UploadStorageDao;
use app\services\driver\upload\UploadDriverServices;
use crmeb\basic\BaseService;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\driver\DriverRegistry;
use crmeb\services\upload\Upload;
use crmeb\utils\FormBuilder;

/**
 * 存储空间管理服务
 *
 * 注意：此服务不直接依赖扩展，配置字段从数据库读取
 * 扩展通过 UploadDriverServices 注册配置到数据库
 *
 * Class UploadStorageServices
 * @package app\services\driver\upload
 */
class UploadStorageServices extends BaseService
{
    /**
     * @var UploadStorageDao
     */
    protected $dao;

    /**
     * @var UploadDriverServices
     */
    protected $driverServices;

    /**
     * UploadStorageServices constructor.
     * @param UploadStorageDao $dao
     * @param UploadDriverServices $driverServices
     */
    public function __construct(UploadStorageDao $dao, UploadDriverServices $driverServices)
    {
        $this->dao = $dao;
        $this->driverServices = $driverServices;
    }

    /**
     * 将动态类型编号或驱动 key 解析为已注册的上传驱动
     * @param int|string $typeOrKey
     * @return string
     */
    protected function resolveDriverKey($typeOrKey): string
    {
        if (is_int($typeOrKey) || (is_string($typeOrKey) && ctype_digit($typeOrKey))) {
            $driverKey = $this->driverServices->getTypeDriverKeyMap()[(int)$typeOrKey] ?? '';
        } else {
            $driverKey = is_string($typeOrKey) ? $typeOrKey : '';
        }
        if ($driverKey === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $driverKey) || !$this->driverServices->getByDriverKey($driverKey)) {
            throw new AdminException('上传驱动不存在');
        }
        return $driverKey;
    }

    /**
     * 校验访问地址，允许路径前缀但不允许凭据、查询或目录穿越
     * @param mixed $value
     * @param int $maxLength
     * @param bool $optional
     * @return string
     */
    protected function validateStorageUrl($value, int $maxLength = 255, bool $optional = true): string
    {
        if (!is_string($value)) throw new AdminException('空间域名格式错误');
        $value = trim($value);
        if ($value === '' && $optional) return '';
        $url = parse_url($value);
        if (strlen($value) > $maxLength || !filter_var($value, FILTER_VALIDATE_URL) || !$url
            || !in_array(strtolower($url['scheme'] ?? ''), ['http', 'https'], true) || empty($url['host'])
            || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment'])) {
            throw new AdminException('空间域名格式错误，请输入 HTTP 或 HTTPS 地址');
        }
        foreach (explode('/', rawurldecode($url['path'] ?? '')) as $part) {
            if ($part === '..' || preg_match('/[\x00-\x1f\x7f\\\\]/', $part)) throw new AdminException('空间域名路径不合法');
        }
        return rtrim($value, '/');
    }

    /**
     * 校验 CNAME 目标，空字符串可清除可选配置
     * @param mixed $value
     * @return string
     */
    protected function validateStorageCname($value): string
    {
        if (!is_string($value)) throw new AdminException('CNAME 格式错误');
        $value = trim($value);
        if ($value !== '' && (strlen($value) > 253 || !filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))) {
            throw new AdminException('CNAME 格式错误');
        }
        return $value;
    }

    /**
     * 从声明字段构造配置并检查必填项
     * @param string $driverKey
     * @param array $data
     * @return array
     */
    protected function prepareConfigFields(string $driverKey, array $data): array
    {
        $fields = $this->driverServices->getConfigFields($driverKey);
        foreach ($fields as &$field) {
            $name = $field['field'];
            if (array_key_exists($name, $data)) {
                if (!is_scalar($data[$name]) && $data[$name] !== null) throw new AdminException('驱动配置格式错误');
                $field['value'] = $data[$name] === null ? '' : $data[$name];
            }
            if (!empty($field['required']) && trim((string)($field['value'] ?? '')) === '') {
                throw new AdminException('请填写必填驱动配置');
            }
        }
        unset($field);
        return $fields;
    }

    /**
     * 使用核心上传管理器创建指定空间客户端，避免 app 直接实例化驱动
     * @param string $driverKey
     * @param array|\ArrayAccess $storage
     * @return Upload
     */
    protected function storageUpload(string $driverKey, $storage = []): Upload
    {
        $config = [];
        foreach ($this->prepareConfigFields($driverKey, []) as $field) $config[$field['field']] = $field['value'] ?? '';
        foreach ($config as $name => $value) {
            if (str_starts_with($name, $driverKey . '_')) {
                $shortName = substr($name, strlen($driverKey) + 1);
                if (!array_key_exists($shortName, $config)) $config[$shortName] = $value;
            }
        }
        $config['storageName'] = $storage['name'] ?? '';
        $config['storageRegion'] = $storage['region'] ?? ($config['storageRegion'] ?? '');
        $config['uploadUrl'] = $storage['domain'] ?? '';
        $config['cdn'] = $storage['cdn'] ?? '';
        return new Upload($driverKey, $config);
    }

    /**
     * 根据 driver_key 获取驱动配置
     *
     * 配置字段的值保存在 eb_driver 表的 config_fields JSON 字段中
     * （与 saveConfigByDriverKey 写入逻辑对应）
     *
     * @param string $driverKey
     * @return array
     */
    public function getStorageConfigByDriverKey(string $driverKey)
    {
        $config = [];
        $configFields = $this->driverServices->getConfigFields($driverKey);

        if (empty($configFields)) {
            return $config;
        }

        foreach ($configFields as $field) {
            $fieldName = $field['field'];
            $config[$fieldName] = $field['value'] ?? '';
        }

        return $config;
    }

    /**
     * 获取存储空间配置
     * @param string $driverKey
     * @return array
     */
    public function getConfigByDriverKey(string $driverKey)
    {
        $res = ['name' => '', 'region' => '', 'domain' => '', 'cdn' => ''];
        $config = $this->dao->get(['driver_key' => $driverKey, 'status' => 1, 'is_delete' => 0], ['name', 'region', 'domain', 'cdn']);
        if ($config) return ['name' => $config->name, 'region' => $config->region, 'domain' => $config->domain, 'cdn' => $config->cdn];
        return $res;
    }

    /**
     * 根据 type 获取配置表单
     * @param int $type
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getFormStorageConfig($type)
    {
        $driverKey = $this->resolveDriverKey($type);
        return $this->getFormStorageConfigByDriverKey($driverKey);
    }

    /**
     * 根据 type 获取添加存储空间表单
     * @param int $type
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getFormStorage($type)
    {
        $driverKey = $this->resolveDriverKey($type);
        return $this->getFormStorageByDriverKey($driverKey);
    }

    /**
     * 获取配置表单
     * @param string $driverKey
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getFormStorageConfigByDriverKey(string $driverKey)
    {
        $driverKey = $this->resolveDriverKey($driverKey);
        $configFields = $this->driverServices->getPublicConfigFields($driverKey);

        $rule = [
            FormBuilder::hidden('driver_key', $driverKey),
        ];

        foreach ($configFields as $field) {
            $fieldName = $field['field'];
            $fieldLabel = $field['label'] ?? $fieldName;
            $fieldType = $field['type'] ?? 'text';
            $required = $field['required'] ?? false;
            $value = $field['value'] ?? '';

            switch ($fieldType) {
                case 'password':
                    $input = FormBuilder::input($fieldName, $fieldLabel, $value)->type('password');
                    break;
                case 'select':
                    $options = $field['options'] ?? [];
                    $input = FormBuilder::select($fieldName, $fieldLabel, $value)->options($options);
                    break;
                default:
                    $input = FormBuilder::input($fieldName, $fieldLabel, $value);
                    break;
            }
            if ($required) $input->required();
            $rule[] = $input;
        }

        return create_form('配置信息', $rule, '/system/config/storage/config');
    }

    /**
     * 根据 type 保存配置
     * @param int|string $type
     * @param array $data
     * @return bool
     */
    public function saveConfig($type, array $data)
    {
        return $this->saveConfigByDriverKey($this->resolveDriverKey($type), $data);
    }

    /**
     * 保存配置
     *
     * 将配置字段的值写入 eb_driver 表的 config_fields JSON 字段中，
     * 与 getStorageConfigByDriverKey 的读取逻辑对应
     *
     * @param string $driverKey
     * @param array $data
     * @return bool
     */
    public function saveConfigByDriverKey(string $driverKey, array $data)
    {
        $driverKey = $this->resolveDriverKey($driverKey);
        if ($this->driverServices->saveDriverConfig($driverKey, $data) === false) throw new \Exception(getLang('保存上传驱动配置失败'));
        CacheService::delete('storage_list');
        return true;
    }

    /**
     * 设置空间状态，云端 CORS 成功后才切换同驱动空间
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function setStorageStatus(int $id, int $status = 1): bool
    {
        if ($id <= 0 || !in_array($status, [0, 1], true)) throw new AdminException('参数错误');
        $info = $this->dao->get(['id' => $id, 'is_delete' => 0]);
        if (!$info) throw new AdminException('云存储不存在');
        $driverKey = $this->resolveDriverKey($info['driver_key']);
        if ($status === 0) {
            if ((int)$info['status'] === 1 && in_array($driverKey, array_column($this->driverServices->getEnabledDrivers(), 'driver_key'), true)) {
                throw new AdminException('请先切换本地存储，再停用默认云存储空间');
            }
        } else {
            $this->validateStorageUrl($info['domain'], 100, false);
            $upload = $this->storageUpload($driverKey, $info);
            if ($upload->setBucketCors($info['name'], $info['region']) === false) throw new \Exception(getLang('设置云空间跨域规则失败'));
        }
        $this->transaction(function () use ($info, $driverKey, $status) {
            if ($status === 1 && $this->dao->disableByDriverKey($driverKey) === false) throw new \Exception(getLang('切换云空间失败'));
            $info->status = $status;
            $info->update_time = time();
            if ($info->save() === false) throw new \Exception(getLang('保存云空间状态失败'));
        });
        CacheService::delete('storage_list');
        return true;
    }

    /**
     * 切换默认上传驱动，云存储必须先选定可用空间
     * @param int|string $typeOrKey
     * @return bool
     */
    public function switchDriver($typeOrKey): bool
    {
        $driverKey = $this->resolveDriverKey($typeOrKey);
        if ($driverKey !== 'local' && !$this->dao->count(['driver_key' => $driverKey, 'status' => 1, 'is_delete' => 0])) {
            throw new AdminException('未有正在使用的存储空间');
        }
        if ($this->driverServices->updateStatus($driverKey, 1) === false) throw new \Exception(getLang('切换上传驱动失败'));
        CacheService::delete('storage_list');
        return true;
    }

    /**
     * 获取列表
     * @param array $where
     * @return array
     */
    public function getList(array $where)
    {
        [$page, $limit, $defaultLimit] = $this->getPageValue();
        $page = max(1, $page);
        $limit = $limit > 0 ? $limit : max(1, $defaultLimit);

        // 如果传入的是 type，转换为 driver_key
        if (isset($where['type'])) {
            $key = $this->resolveDriverKey($where['type']);
            if (!empty($where['driver_key']) && $where['driver_key'] !== $key) throw new AdminException('上传驱动参数不一致');
            $where['driver_key'] = $key;
            unset($where['type']);
        }

        $driverKey = $where['driver_key'] ?? '';
        if ($driverKey !== '') $where['driver_key'] = $this->resolveDriverKey($driverKey);

        $list = $this->dao->getList($where, ['id', 'type', 'driver_key', 'name', 'region', 'domain', 'cdn', 'cname', 'acl', 'is_ssl', 'status', 'add_time', 'update_time'], $page, $limit, 'add_time');
        foreach ($list as &$item) {
            // 优先使用数据库中实际的 cname 值，为空时从 domain 推导
            if (empty($item['cname'])) {
                $item['cname'] = parse_url($item['domain'], PHP_URL_HOST) ?: '';
            }
            $item['_region'] = $this->getRegionName($item['region'], $driverKey ?: $item['driver_key']);
            $item['_add_time'] = date('Y-m-d H:i:s', (int)$item['add_time']);
            $item['_update_time'] = date('Y-m-d H:i:s', (int)$item['update_time']);
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 从驱动注册中心读取 region_map 并转换区域代码为中文名
     * @param string $region
     * @param string $driverKey
     * @return string
     */
    protected function getRegionName(string $region, string $driverKey): string
    {
        if (!$region) return '';

        // 统一走驱动注册中心（plugin.json 的 region_map 经全量透传）
        $info = DriverRegistry::get('upload', $driverKey);
        $map = $info['region_map'] ?? [];
        return $map[$region] ?? $region;
    }

    /**
     * 获取添加存储空间表单
     * @param string $driverKey
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getFormStorageByDriverKey(string $driverKey)
    {
        $driverKey = $this->resolveDriverKey($driverKey);
        $bucketFields = $this->driverServices->getBucketFields($driverKey);

        $ruleConfig = [];

        // 根据 config_fields 动态生成配置字段
        $configFields = $this->driverServices->getPublicConfigFields($driverKey);
        foreach ($configFields as $field) {
            $fieldName = $field['field'];
            $fieldLabel = $field['label'] ?? $fieldName;
            $required = $field['required'] ?? false;
            $value = $field['value'] ?? '';

            if ($value === '') {
                $input = FormBuilder::input($fieldName, $fieldLabel, $value);
                if (($field['type'] ?? '') === 'password') $input->type('password');
                if ($required) $input->required();
                $ruleConfig[] = $input;
            }
        }

        // 根据 bucket_fields 动态生成存储空间字段
        $rule = [];
        foreach ($bucketFields as $field) {
            $fieldName = $field['field'];
            $fieldLabel = $field['label'] ?? $fieldName;
            $fieldType = $field['type'] ?? 'text';
            $required = $field['required'] ?? false;
            $default = $field['value'] ?? '';

            switch ($fieldType) {
                case 'select':
                    $options = $field['options'] ?? [];
                    $input = FormBuilder::select($fieldName, $fieldLabel, $default)->options($options);
                    break;
                case 'radio':
                    $options = $field['options'] ?? [];
                    $input = FormBuilder::radio($fieldName, $fieldLabel, $default)->options($options);
                    break;
                default:
                    $input = FormBuilder::input($fieldName, $fieldLabel, $default);
                    break;
            }
            if ($required) $input->required();
            $rule[] = $input;
        }

        $rule = array_merge($ruleConfig, $rule);
        return create_form('添加云空间', $rule, '/system/config/storage/' . $driverKey);
    }

    /**
     * 删除空间
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteStorage(int $id)
    {
        if ($id <= 0) throw new AdminException('参数错误');
        $storageInfo = $this->dao->get(['is_delete' => 0, 'id' => $id]);
        if (!$storageInfo) {
            throw new AdminException('删除的云存储不存在');
        }
        if ($storageInfo->status) {
            throw new AdminException('云存储正在使用中,需要启动其他空间才能删除');
        }

        $storageInfo->is_delete = 1;
        $storageInfo->update_time = time();
        if ($storageInfo->save() === false) throw new \Exception(getLang('删除云存储配置失败'));
        CacheService::delete('storage_list');

        return true;
    }

    /**
     * 同步云存储空间列表
     *
     * 根据 type 或 driver_key 获取已保存的配置，
     * 实例化对应的上传驱动，调用 listbuckets() 拉取远程存储空间列表
     *
     * @param string|int $type 驱动类型（数字）或 driver_key（字符串）
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function synchronization($type)
    {
        $driverKey = $this->resolveDriverKey($type);
        if ($driverKey === 'local') return [];
        $storage = $this->dao->get(['driver_key' => $driverKey, 'status' => 1, 'is_delete' => 0]);
        $driver = $this->storageUpload($driverKey, $storage ?: []);
        $buckets = $driver->listbuckets($storage['region'] ?? '');
        if ($buckets === false) throw new \Exception(getLang('同步云空间失败'));
        if (!is_array($buckets)) throw new \Exception(getLang('云空间列表格式错误'));
        $existingNames = $this->dao->getColumn(['driver_key' => $driverKey, 'is_delete' => 0], 'name');
        $type = $this->driverServices->getTypeByDriverKey($driverKey);
        $driverInfo = $this->driverServices->getByDriverKey($driverKey);
        $domainTemplate = $driverInfo['domain_template'] ?? '';
        $appid = '';
        if (str_contains($domainTemplate, '{appid}')) {
            $config = $this->getStorageConfigByDriverKey($driverKey);
            $appid = (string)($config['appid'] ?? $config[$driverKey . '_appid'] ?? '');
        }
        $records = [];
        foreach ($buckets as $bucket) {
            if (is_array($bucket) || is_object($bucket)) {
                $bucketArr = (array)$bucket;
                $name = $bucketArr['name'] ?? $bucketArr['Name'] ?? $bucketArr['id'] ?? '';
                $region = $bucketArr['region'] ?? $bucketArr['Region'] ?? $bucketArr['location'] ?? $bucketArr['Location'] ?? '';
            } elseif (is_string($bucket)) {
                $name = $bucket;
                $region = '';
            } else {
                continue;
            }

            if (!is_string($name) || !is_string($region) || $name === '' || in_array($name, $existingNames, true)) continue;
            if (!preg_match('/^[a-z0-9][a-z0-9_.-]{0,99}$/i', $name)) throw new AdminException('云空间名称格式错误');
            if (strlen($region) > 100 || ($region !== '' && !preg_match('/^[a-z0-9-]+$/i', $region))) throw new AdminException('云空间区域格式错误');
            $domain = $this->formatStorageDomain($domainTemplate, $name, $region, $appid);
            $records[] = [
                'name' => $name,
                'region' => $region,
                'domain' => $domain,
                'type' => $type,
                'driver_key' => $driverKey,
                'is_delete' => 0,
                'status' => 0,
                'is_ssl' => str_starts_with($domain, 'https://') ? 1 : 0,
                'add_time' => time(),
                'update_time' => time(),
            ];
            $existingNames[] = $name;
        }
        if ($records) {
            // 远程请求已完成，事务中只执行批量元数据写入。
            $this->transaction(function () use ($records) {
                if ($this->dao->saveAll($records) === false) throw new \Exception(getLang('保存同步云空间失败'));
            });
            CacheService::delete('storage_list');
        }
        return $buckets;
    }

    /**
     * 根据 type 保存云存储
     * @param int|string $type
     * @param array $data
     * @return mixed
     */
    public function saveStorage($type, array $data)
    {
        return $this->saveStorageByDriverKey($this->resolveDriverKey($type), $data);
    }

    /**
     * 保存云存储
     * @param string $driverKey
     * @param array $data
     * @return mixed
     */
    public function saveStorageByDriverKey(string $driverKey, array $data)
    {
        $driverKey = $this->resolveDriverKey($driverKey);
        $metadata = ['name' => '', 'region' => '', 'acl' => 'public-read', 'domain' => '', 'cdn' => '', 'cname' => ''];
        foreach ($this->driverServices->getBucketFields($driverKey) as $field) {
            $name = $field['field'];
            if (array_key_exists($name, $metadata) && isset($field['value'])) $metadata[$name] = $field['value'];
        }
        $metadata = array_replace($metadata, array_intersect_key($data, $metadata));
        if (!is_string($metadata['name']) || !preg_match('/^[a-z0-9][a-z0-9_.-]{0,99}$/i', $metadata['name'])) throw new AdminException('云空间名称格式错误');
        if (!is_string($metadata['region']) || strlen($metadata['region']) > 100 || ($metadata['region'] !== '' && !preg_match('/^[a-z0-9-]+$/i', $metadata['region']))) throw new AdminException('云空间区域格式错误');
        if (!in_array($metadata['acl'], ['private', 'public-read', 'public-read-write'], true)) throw new AdminException('云空间权限格式错误');
        foreach ($this->driverServices->getBucketFields($driverKey) as $field) {
            $value = $metadata[$field['field']] ?? ($data[$field['field']] ?? '');
            if (!empty($field['required']) && (!is_scalar($value) || trim((string)$value) === '')) throw new AdminException('请填写必填云空间信息');
            $options = array_column($field['options'] ?? [], 'value');
            if ($options && $value !== '' && !in_array($value, $options, true)) throw new AdminException('云空间选项不合法');
        }
        if ($this->dao->count(['name' => $metadata['name'], 'driver_key' => $driverKey, 'is_delete' => 0])) throw new AdminException('云空间名称不能重复');
        if ($metadata['domain'] === '') $metadata['domain'] = $this->getDomain($driverKey, $metadata['name'], $metadata['region']);
        $metadata['domain'] = $this->validateStorageUrl($metadata['domain'], 100);
        $metadata['cdn'] = $this->validateStorageUrl($metadata['cdn']);
        $metadata['cname'] = $this->validateStorageCname($metadata['cname']);
        $metadata += ['driver_key' => $driverKey, 'type' => $this->driverServices->getTypeByDriverKey($driverKey), 'status' => 0, 'is_delete' => 0,
            'is_ssl' => str_starts_with($metadata['domain'], 'https://') ? 1 : 0, 'add_time' => time(), 'update_time' => time()];
        $configValues = array_intersect_key($data, array_column($this->driverServices->getConfigFields($driverKey), null, 'field'));
        // 此入口只登记已有云空间；驱动配置和空间记录必须共同提交，不创建真实存储桶。
        $result = $this->transaction(function () use ($driverKey, $configValues, $metadata) {
            $this->saveConfigByDriverKey($driverKey, $configValues);
            $result = $this->dao->save($metadata);
            if (!$result) throw new \Exception(getLang('保存云空间失败'));
            return $result;
        });
        CacheService::delete('storage_list');
        return $result;
    }

    /**
     * 获取云存储配置
     * @param int $type
     * @return array|string[]
     */
    public function getConfig($type)
    {
        $driverKey = $this->resolveDriverKey($type);
        return $this->getConfigByDriverKey($driverKey);
    }

    /**
     * 获取修改域名表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getUpdateDomainForm(int $id)
    {
        $storage = $this->dao->get(['id' => $id, 'is_delete' => 0], ['domain', 'cdn', 'cname']);
        if (!$storage) throw new AdminException('云存储不存在');
        $rule = [
            FormBuilder::input('domain', '空间域名', $storage['domain']),
            FormBuilder::input('cdn', 'cdn域名', $storage['cdn']),
            FormBuilder::input('cname', 'CNAME记录值', $storage['cname'])->placeholder('请先在云存储平台绑定域名后，将提供的CNAME目标地址粘贴至此'),
        ];
        return create_form('修改空间域名', $rule, '/system/config/storage/domain/' . $id);
    }

    /**
     * 修改域名并绑定
     * @param int $id
     * @param string $domain
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateDomain(int $id, string $domain, array $data = [])
    {
        $info = $this->dao->get(['id' => $id, 'is_delete' => 0]);
        if (!$info) {
            throw new AdminException('数据不存在');
        }

        $domain = $this->validateStorageUrl($domain, 100, false);
        $changes = ['domain' => $domain, 'is_ssl' => str_starts_with($domain, 'https://') ? 1 : 0, 'update_time' => time()];
        if (array_key_exists('cdn', $data)) $changes['cdn'] = $this->validateStorageUrl($data['cdn']);
        if (array_key_exists('cname', $data)) $changes['cname'] = $this->validateStorageCname($data['cname']);
        if ($info->save($changes) === false) throw new \Exception(getLang('保存云空间域名失败'));
        CacheService::delete('storage_list');

        return true;
    }

    /**
     * 获取域名
     * 从数据库读取域名模板，不硬编码
     * @param string $driverKey
     * @param string $name
     * @param string $region
     * @param string $appid
     * @return string
     */
    public function getDomain(string $driverKey, string $name, string $region, string $appid = '')
    {
        $driver = $this->driverServices->getByDriverKey($driverKey);
        $domainTemplate = $driver['domain_template'] ?? '';
        if ($appid === '' && str_contains($domainTemplate, '{appid}')) {
            $config = $this->getStorageConfigByDriverKey($driverKey);
            $appid = (string)($config['appid'] ?? $config[$driverKey . '_appid'] ?? '');
        }
        return $this->formatStorageDomain($domainTemplate, $name, $region, $appid);
    }

    /**
     * 纯模板转换，批量同步无需逐个空间查询驱动配置
     * @param string $template
     * @param string $name
     * @param string $region
     * @param string $appid
     * @return string
     */
    protected function formatStorageDomain(string $template, string $name, string $region, string $appid): string
    {
        if ($template === '') return '';
        $values = ['{name}' => $name, '{region}' => $region, '{appid}' => $appid];
        foreach ($values as $placeholder => $value) if ($value === '' && str_contains($template, $placeholder)) return '';
        return $this->validateStorageUrl(strtr($template, $values), 100, false);
    }
}
