<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\system\config;


use app\dao\system\config\SystemStorageDao;
use app\services\BaseServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder;
use app\services\other\UploadService;

/**
 * Class SystemStorageServices
 * @package app\services\system\config
 */
class SystemStorageServices extends BaseServices
{

    /**
     * SystemStorageServices constructor.
     * @param SystemStorageDao $dao
     */
    public function __construct(SystemStorageDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param array $where
     * @return array
     */
    public function getList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $config = $this->getStorageConfig((int)$where['type']);
        $where['access_key'] = $config['accessKey'];
        $list = $this->dao->getList($where, ['*'], $page, $limit, 'add_time');
        foreach ($list as &$item) {
            $item['cname'] = str_replace('https://', '', $item['domain']);
            $item['_add_time'] = date('Y-m-d H:i:s', $item['add_time']);
            $item['_update_time'] = date('Y-m-d H:i:s', $item['update_time']);
            $service = UploadService::init($item['type']);
            $region = $service->getRegion();
            foreach ($region as $value) {
                if (strstr($item['region'], $value['value'])) {
                    $item['_region'] = $value['label'];
                }
            }
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * @param int $type
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getFormStorage(int $type)
    {
        $upload = UploadService::init($type);

        $config = $this->getStorageConfig($type);
        $ruleConfig = [];
        if (!$config['accessKey']) {
            $ruleConfig = [
                FormBuilder::input('accessKey', 'AccessKeyId', $config['accessKey'] ?? '')->required(),
                FormBuilder::input('secretKey', 'AccessKeySecret', $config['secretKey'] ?? '')->required(),
            ];
        }

        if ($type === 4 && isset($config['appid']) && !$config['appid']) {
            $ruleConfig[] = FormBuilder::input('appid', 'APPID', $config['appid'] ?? '')->required();
        }

        $rule = [
            FormBuilder::input('name', '空间名称')->required(),
            FormBuilder::select('region', '空间区域')->options($upload->getRegion())->required(),
            FormBuilder::radio('acl', '读写权限', 'public-read')->options([
                ['label' => '公共读(推荐)', 'value' => 'public-read'],
                ['label' => '公共读写', 'value' => 'public-read-write'],
            ])->required(),
        ];

        $rule = array_merge($ruleConfig, $rule);
        return create_form('添加云空间', $rule, '/system/config/storage/' . $type);
    }

    /**
     * @param int $type
     * @return array
     */
    public function getStorageConfig(int $type)
    {
        $config = [
            'accessKey' => '',
            'secretKey' => ''
        ];
        switch ($type) {
            case 2://七牛
                $config = [
                    'accessKey' => sys_config('qiniu_accessKey', ''),
                    'secretKey' => sys_config('qiniu_secretKey', ''),
                ];
                break;
            case 3:// oss 阿里云
                $config = [
                    'accessKey' => sys_config('accessKey', ''),
                    'secretKey' => sys_config('secretKey', ''),
                ];
                break;
            case 4:// cos 腾讯云
                $config = [
                    'accessKey' => sys_config('tengxun_accessKey', ''),
                    'secretKey' => sys_config('tengxun_secretKey', ''),
                    'appid' => sys_config('tengxun_appid', ''),
                ];
                break;
            case 5:// cos 京东云
                $config = [
                    'accessKey' => sys_config('jd_accessKey', ''),
                    'secretKey' => sys_config('jd_secretKey', ''),
                    'storageRegion' => sys_config('jd_storageRegion', ''),

                ];
                break;
            case 6:// cos 华为云
                $config = [
                    'accessKey' => sys_config('hw_accessKey', ''),
                    'secretKey' => sys_config('hw_secretKey', ''),
                ];
                break;
            case 7:// cos 天翼云
                $config = [
                    'accessKey' => sys_config('ty_accessKey', ''),
                    'secretKey' => sys_config('ty_secretKey', ''),
                ];
                break;
        }
        return $config;
    }

    /**
     * @param int $type
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getFormStorageConfig(int $type)
    {
        $config = $this->getStorageConfig($type);
        $rule = [
            FormBuilder::hidden('type', $type),
            FormBuilder::input('accessKey', 'AccessKeyId', $config['accessKey'] ?? '')->required(),
            FormBuilder::input('secretKey', 'AccessKeySecret', $config['secretKey'] ?? '')->required(),
        ];

        if ($type === 4) {
            $rule[] = FormBuilder::input('appid', 'APPID', $config['appid'] ?? '')->required();
        }

        if ($type === 5) {
            $rule[] = FormBuilder::input('storageRegion', 'storageRegion', $config['storageRegion'] ?? '')->required();
        }


        return create_form('配置信息', $rule, '/system/config/storage/config');
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
        $storageInfo = $this->dao->get(['is_delete' => 0, 'id' => $id]);
        if (!$storageInfo) {
            throw new AdminException(400608);
        }
        if ($storageInfo->status) {
            throw new AdminException(400609);
        }

        try {
            $upload = UploadService::init($storageInfo->type);
            $res = $upload->deleteBucket($storageInfo->name, $storageInfo->region);
            if (false === $res) {
                throw new AdminException($upload->getError());
            }
        } catch (\Throwable $e) {
            throw new AdminException($e->getMessage());
        }
        $storageInfo->is_delete = 1;
        $storageInfo->save();

        CacheService::clear();

        return true;
    }

    public function saveConfig(int $type, array $data)
    {
        //保存配置信息
        if (1 !== $type) {
            $accessKey = $secretKey = $appid = $storageRegion = '';
            if (isset($data['accessKey']) && isset($data['secretKey']) && $data['accessKey'] && $data['secretKey']) {
                $accessKey = $data['accessKey'];
                $secretKey = $data['secretKey'];
                unset($data['accessKey'], $data['secretKey']);
            }
            if (isset($data['appid']) && $data['appid']) {
                $appid = $data['appid'];
                unset($data['appid']);
            }
            if (isset($data['storageRegion']) && $data['storageRegion']) {
                $storageRegion = $data['storageRegion'];
                unset($data['storageRegion']);
            }
            if (!$accessKey || !$secretKey) {
                return true;
            }
            /** @var SystemConfigServices $make */
            $make = app()->make(SystemConfigServices::class);
            switch ($type) {
                case 2://七牛
                    $make->update('qiniu_accessKey', ['value' => json_encode($accessKey)], 'menu_name');
                    $make->update('qiniu_secretKey', ['value' => json_encode($secretKey)], 'menu_name');
                    break;
                case 3:// oss 阿里云
                    $make->update('accessKey', ['value' => json_encode($accessKey)], 'menu_name');
                    $make->update('secretKey', ['value' => json_encode($secretKey)], 'menu_name');
                    break;
                case 4:// cos 腾讯云
                    $make->update('tengxun_accessKey', ['value' => json_encode($accessKey)], 'menu_name');
                    $make->update('tengxun_secretKey', ['value' => json_encode($secretKey)], 'menu_name');
                    $make->update('tengxun_appid', ['value' => json_encode($appid)], 'menu_name');
                    break;
                case 5:// oss 京东云
                    $make->update('jd_accessKey', ['value' => json_encode($accessKey)], 'menu_name');
                    $make->update('jd_secretKey', ['value' => json_encode($secretKey)], 'menu_name');
                    $make->update('jd_storageRegion', ['value' => json_encode($storageRegion)], 'menu_name');
                    break;
                case 6:// oss 华为云
                    $make->update('hw_accessKey', ['value' => json_encode($accessKey)], 'menu_name');
                    $make->update('hw_secretKey', ['value' => json_encode($secretKey)], 'menu_name');
                    break;
                case 7:// oss 天翼云
                    $make->update('ty_accessKey', ['value' => json_encode($accessKey)], 'menu_name');
                    $make->update('ty_secretKey', ['value' => json_encode($secretKey)], 'menu_name');
                    break;
            }
            CacheService::clear();
        }
    }

    /**
     * 保存云存储
     * @param int $type
     * @param array $data
     * @return mixed
     */
    public function saveStorage(int $type, array $data)
    {
        //保存配置信息
        $this->saveConfig($type, $data);
        if ($this->dao->count(['name' => $data['name']])) {
            throw new AdminException(400610);
        }
        //保存云存储
        $data['type'] = $type;
        $upload = UploadService::init($type);
        $res = $upload->createBucket($data['name'], $data['region'], $data['acl']);
        if (false === $res) {
            throw new AdminException($upload->getError());
        }
        if (3 === $type) {
            $data['region'] = $this->getReagionHost($type, $data['region']);
        }
        $data['domain'] = $this->getDomain($type, $data['name'], $data['region'], sys_config('tengxun_appid'));
        if (2 === $type) {
            $domianList = $upload->getDomian($data['name']);
            $data['domain'] = $domianList[count($domianList) - 1];
        } else {
            $data['cname'] = $data['domain'];
        }
        $data['add_time'] = time();
        $data['update_time'] = time();
        $config = $this->getStorageConfig($type);
        $data['access_key'] = $config['accessKey'];

        CacheService::clear();

        return $this->dao->save($data);
    }

    /**
     * 同步云储存桶
     * @param int $type
     * @return bool
     */
    public function synchronization(int $type)
    {
        $data = [];
        switch ($type) {
            case 2://七牛
                $config = $this->getStorageConfig($type);
                $upload = UploadService::init($type);
                $list = $upload->listbuckets();
                foreach ($list as $item) {
                    if (!$this->dao->count(['name' => $item['id'], 'access_key' => $config['accessKey']])) {
                        $data[] = [
                            'type' => $type,
                            'access_key' => $config['accessKey'],
                            'name' => $item['id'],
                            'region' => $item['region'],
                            'acl' => $item['private'] == 0 ? 'public-read' : 'private',
                            'status' => 0,
                            'is_delete' => 0,
                            'add_time' => time(),
                            'update_time' => time()
                        ];
                    }
                }
                break;
            case 3:// oss 阿里云
                $upload = UploadService::init($type);
                $list = $upload->listbuckets();
                $config = $this->getStorageConfig($type);
                foreach ($list as $item) {
                    if (!$this->dao->count(['name' => $item['name'], 'access_key' => $config['accessKey']])) {
                        $region = $this->getReagionHost($type, $item['location']);
                        $data[] = [
                            'type' => $type,
                            'access_key' => $config['accessKey'],
                            'name' => $item['name'],
                            'region' => $region,
                            'acl' => 'public-read',
                            'domain' => $this->getDomain($type, $item['name'], $region),
                            'status' => 0,
                            'is_delete' => 0,
                            'add_time' => strtotime($item['createTime']),
                            'update_time' => time()
                        ];
                    }
                }
                break;
            case 4:// cos 腾讯云
                $upload = UploadService::init($type);
                $list = $upload->listbuckets();
                if (!empty($list['Name'])) {
                    $newList = $list;
                    $list = [];
                    $list[] = $newList;
                }
                $config = $this->getStorageConfig($type);
                foreach ($list as $item) {
                    if (!$this->dao->count(['name' => $item['Name'], 'access_key' => $config['accessKey']])) {
                        $data[] = [
                            'type' => $type,
                            'access_key' => $config['accessKey'],
                            'name' => $item['Name'],
                            'region' => $item['Location'],
                            'acl' => 'public-read',
                            'status' => 0,
                            'domain' => sys_config('tengxun_appid') ? $this->getDomain($type, $item['Name'], $item['Location']) : '',
                            'is_delete' => 0,
                            'add_time' => strtotime($item['CreationDate']),
                            'update_time' => time()
                        ];
                    }
                }
                break;
            case 5:// cos 京东云
                $upload = UploadService::init($type);
                $res = $upload->listbuckets(sys_config('jd_storageRegion'));
                $list = $res['Buckets'];
                $location = explode('.', $res['@metadata']['effectiveUri'])[1] ?? 'cn-north-1';
                $config = $this->getStorageConfig($type);
                foreach ($list as $item) {
                    if (!$this->dao->count(['name' => $item['Name'], 'access_key' => $config['accessKey']])) {
                        $data[] = [
                            'type' => $type,
                            'access_key' => $config['accessKey'],
                            'name' => $item['Name'],
                            'region' => $location,
                            'acl' => 'public-read',
                            'status' => 0,
                            'domain' => $this->getDomain($type, $item['Name'], $location),
                            'is_delete' => 0,
                            'add_time' => time(),
                            'update_time' => time()
                        ];
                    }
                }
                break;
            case 6:// cos 华为云
            case 7:// cos 天翼云
                $upload = UploadService::init($type);
                $list = $upload->listbuckets();
                if (!empty($list['Name'])) {
                    $newList = $list;
                    $list = [];
                    $list[] = $newList;
                }
                $config = $this->getStorageConfig($type);
                foreach ($list as $item) {
                    if (!$this->dao->count(['name' => $item['Name'], 'access_key' => $config['accessKey']])) {
                        $data[] = [
                            'type' => $type,
                            'access_key' => $config['accessKey'],
                            'name' => $item['Name'],
                            'region' => $item['Location'],
                            'acl' => 'public-read',
                            'status' => 0,
                            'domain' => $this->getDomain($type, $item['Name'], $item['Location']),
                            'is_delete' => 0,
                            'add_time' => strtotime($item['CreationDate']),
                            'update_time' => time()
                        ];
                    }
                }
                break;
        }
        if ($data) {
            $this->dao->saveAll($data);
        }

        CacheService::clear();

        return true;
    }

    /**
     * @param int $type
     * @param string $reagion
     * @return mixed|string
     */
    public function getReagionHost(int $type, string $reagion)
    {
        $upload = UploadService::init($type);
        $reagionList = $upload->getRegion();
        foreach ($reagionList as $item) {
            if (strstr($item['value'], $reagion) !== false) {
                return $item['value'];
            }
        }
        return '';
    }

    /**
     * 获取域名
     * @param int $type
     * @param string $name
     * @param string $reagion
     * @param string $appid
     * @return string
     */
    public function getDomain(int $type, string $name, string $reagion, string $appid = '')
    {
        $domainName = '';
        switch ($type) {
            case 3:// oss 阿里云
                $domainName = 'https://' . $name . '.' . $reagion;
                break;
            case 4:// cos 腾讯云
                $domainName = 'https://' . $name . ($appid ? '-' . $appid : '') . '.cos.' . $reagion . '.myqcloud.com';
                break;
            case 5:// cos 京东云
                $domainName = 'https://' . $name . '.s3.' . $reagion . '.jdcloud-oss.com';
                break;
            case 6:// cos 华为云
                $domainName = 'https://' . $name . '.obs.' . $reagion . '.myhuaweicloud.com';
                break;
            case 7:// cos 天翼云
                $domainName = 'https://' . $name . '.obs.' . $reagion . '.ctyun.cn';
                break;
        }
        return $domainName;
    }


    /**
     * 获取云存储配置
     * @param int $type
     * @return array|string[]
     */
    public function getConfig(int $type)
    {
        $res = ['name' => '', 'region' => '', 'domain' => '', 'cdn' => ''];
        try {
            $config = $this->dao->get(['type' => $type, 'status' => 1, 'is_delete' => 0]);
            if ($config) {
                return ['name' => $config->name, 'region' => $config->region, 'domain' => $config->domain, 'cdn' => $config->cdn];
            }
        } catch (\Throwable $e) {
        }
        return $res;

    }

    /**
     * 获取修改域名表单
     * @param int $id
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function getUpdateDomainForm(int $id)
    {
        $storage = $this->dao->get(['id' => $id], ['domain', 'cdn']);
        $rule = [
            FormBuilder::input('domain', '空间域名', $storage['domain']),
            FormBuilder::input('cdn', 'cdn域名', $storage['cdn']),
        ];
        return create_form('修改空间域名', $rule, '/system/config/storage/domain/' . $id);
    }

    /**
     * 修改域名并绑定
     * @param int $id
     * @param string $domain
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function updateDomain(int $id, string $domain, array $data = [])
    {
        $info = $this->dao->get($id);
        if (!$info) {
            throw new AdminException(100026);
        }
        if ($info->domain != $domain) {
            $info->domain = $domain;
            $upload = UploadService::init($info->type);
            //是否添加过域名不存在需要绑定域名
            $domainList = $upload->getDomian($info->name, $info->region);
            $domainParse = parse_url($domain);
            if (false === $domainParse) {
                throw new AdminException('域名输入有误');
            }
            if (!in_array($domainParse['host'], $domainList)) {
                //绑定域名到云储存桶
                $res = $upload->bindDomian($info->name, $domain, $info->region);
                if (false === $res) {
                    throw new AdminException($upload->getError());
                }
            }
            //七牛云需要通过接口获取cname
            if (2 === ((int)$info->type)) {
                $resDomain = $upload->getDomianInfo($domain);
                $info->cname = $resDomain['cname'] ?? '';
            }
            $info->save();
        }
        if ($info->cdn != $data['cdn']) {
            $info->cdn = $data['cdn'];
            $info->save();
        }

        CacheService::clear();

        return true;
    }
}
