<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\services\system;

use think\facade\Config;
use think\facade\Db;
use think\facade\Log;
use app\jobs\UpgradeJob;
use app\services\BaseServices;
use crmeb\services\FileService;
use crmeb\services\HttpService;
use crmeb\services\CacheService;
use crmeb\utils\fileVerification;
use crmeb\exceptions\AdminException;
use app\dao\system\upgrade\UpgradeLogDao;

/**
 * 在线升级
 * Class UpgradeServices
 * @package app\services\system
 */
class UpgradeServices extends BaseServices
{
    const LOGIN_URL = 'http://upgrade.crmeb.net/api/login';
    const UPGRADE_URL = 'http://upgrade.crmeb.net/api/upgrade/list';
    const UPGRADE_CURRENT_URL = 'http://upgrade.crmeb.net/api/upgrade/current_list';
    const AGREEMENT_URL = 'http://upgrade.crmeb.net/api/upgrade/agreement';
    const PACKAGE_DOWNLOAD_URL = 'http://upgrade.crmeb.net/api/upgrade/download';
    const UPGRADE_STATUS_URL = 'http://upgrade.crmeb.net/api/upgrade/status';
    const UPGRADE_LOG_URL = 'http://upgrade.crmeb.net/api/upgrade/log';

    /**
     * @var array $requestData
     */
    private $requestData = [];

    /**
     * @var int $timeStamp
     */
    private $timeStamp;

    /**
     * UpgradeServices constructor.
     * @param UpgradeLogDao $dao
     */
    public function __construct(UpgradeLogDao $dao)
    {
        $versionData = $this->getVersion();
        if ($versionData['version_code'] < 450) return true;
        if (empty($versionData)) {
            throw new AdminException('授权信息丢失');
        }

        $this->timeStamp = time();
        $recVersion = $this->recombinationVersion($versionData['version'] ?? '');
        $this->dao = $dao;

        $this->requestData = [
            'nonce' => mt_rand(111, 999),
            'host' => app()->request->host(),
            'timestamp' => $this->timeStamp,
            'app_id' => trim($versionData['app_id'] ?? ''),
            'app_key' => trim($versionData['app_key'] ?? ''),
            'version' => implode('.', $recVersion)
        ];

        if (!CacheService::get('upgrade_auth_token')) {
            $this->getAuth();
        }
    }

    /**
     * 获取版本信息
     * @return void
     */
    /**
     * 获取文件配置信息
     * @param string $name
     * @param string $path
     * @return array|string
     */
    public function getVersion(string $name = '', string $path = '')
    {
        $file = '.version';
        $arr = [];
        $list = @file($path ?: app()->getRootPath() . $file);

        foreach ($list as $val) {
            list($k, $v) = explode('=', str_replace(PHP_EOL, '', $val));
            $arr[$k] = $v;
        }
        return !empty($name) ? $arr[$name] ?? '' : $arr;
    }

    /**
     * 获取版本号
     * @param $input
     * @return array
     */
    public function recombinationVersion($input): array
    {
        $version = substr($input, strpos($input, ' v') + 1);
        return array_map(function ($item) {
            if (preg_match('/\d+/', $item, $arr)) {
                $item = $arr[0];
            }
            return (int)$item;
        }, explode('.', $version));
    }

    /**
     * 获取Token
     * @return void
     */
    public function getAuth()
    {
        $this->getSign($this->timeStamp);
        $result = HttpService::postRequest(self::LOGIN_URL, $this->requestData);
        if (!$result) {
            throw new AdminException('授权失败');
        }

        $authData = json_decode($result, true);
        if (!isset($authData['status']) || $authData['status'] != 200) {
            Log::error(['msg' => $authData['msg'] ?? '', 'error' => $authData['data'] ?? []]);
            throw new AdminException($authData['msg'] ?? '授权失败');
        }
        CacheService::set('upgrade_auth_token', $authData['data']['access_token'], 7200);
    }

    /**
     * 获取签名
     * @param int $timeStamp
     * @return void
     */
    public function getSign(int $timeStamp)
    {
        $data = $this->requestData;
        if ((!isset($data['host']) || !$data['host']) ||
            (!isset($data['nonce']) || !$data['nonce']) ||
            (!isset($data['app_id']) || !$data['app_id']) ||
            (!isset($data['version']) || !$data['version']) ||
            (!isset($data['app_key']) || !$data['app_key'])) {
            throw new AdminException('验证失效，请重新请求');
        }

        $host = $data['host'];
        $nonce = $data['nonce'];
        $appId = $data['app_id'];
        $appKey = $data['app_key'];
        $version = $data['version'];
        unset($data['sign'], $data['nonce'], $data['host'], $data['version'], $data['app_id'], $data['app_key']);

        $params = json_encode($data);
        $shaiAtt = [
            'host' => $host,
            'nonce' => $nonce,
            'app_id' => $appId,
            'params' => $params,
            'app_key' => $appKey,
            'version' => $version,
            'time_stamp' => $timeStamp
        ];

        sort($shaiAtt, SORT_STRING);
        $shaiStr = implode(',', $shaiAtt);
        $this->requestData['sign'] = hash("SHA256", $shaiStr);
    }

    /**
     * 升级列表
     * @return mixed
     */
    public function getUpgradeList()
    {
        [$page, $limit] = $this->getPageValue();
        $this->requestData['page'] = (string)($page ?: 1);
        $this->requestData['limit'] = (string)($limit ?: 10);
        $this->getSign($this->timeStamp);
        $result = HttpService::getRequest(self::UPGRADE_URL, $this->requestData);
        if (!$result) {
            throw new AdminException('升级列表获取失败');
        }

        $data = json_decode($result, true);
        if (!$this->checkAuth($data)) {
            throw new AdminException($data['msg'] ?? '升级列表获取失败');
        }
        return $data['data'] ?? [];
    }

    /**
     * 可升级列表
     * @return mixed
     */
    public function getUpgradeableList()
    {
        $this->getSign($this->timeStamp);
        $result = HttpService::getRequest(self::UPGRADE_CURRENT_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
        if (!$result) {
            throw new AdminException('可升级列表获取失败');
        }

        $data = json_decode($result, true);
        if (!$this->checkAuth($data)) {
            throw new AdminException($data['msg'] ?? '升级列表获取失败');
        }
        return $data['data'] ?? [];
    }

    /**
     * 升级协议
     * @return mixed
     */
    public function getAgreement()
    {
        $this->getSign($this->timeStamp);
        $result = HttpService::getRequest(self::AGREEMENT_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
        if (!$result) {
            throw new AdminException('升级协议获取失败');
        }

        $data = json_decode($result, true);
        if (!$this->checkAuth($data)) {
            throw new AdminException($data['msg'] ?? '升级协议获取失败');
        }
        return $data['data'] ?? [];
    }

    /**
     * 下载
     * @param string $packageKey
     * @return bool
     */
    public function packageDownload(string $packageKey): bool
    {
        $token = md5(time());

        //检查数据库大小
        $this->checkDatabaseSize();


        //核对项目签名
        $this->checkSignature();

        $this->requestData['package_key'] = $packageKey;
        $this->getSign($this->timeStamp);
        $result = HttpService::getRequest(self::PACKAGE_DOWNLOAD_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
        if (!$result) {
            throw new AdminException('升级包获取失败');
        }
        $data = json_decode($result, true);
        if (!$this->checkAuth($data)) {
            throw new AdminException($data['msg'] ?? '授权失败');
        }

        if (empty($data['data']['server_package_link']) && empty($data['data']['client_package_link']) && empty($data['data']['pc_package_link'])) {
            CacheService::set($token . 'upgrade_status', 2, 86400);
            return true;
        }

        if (!empty($data['data']['server_package_link'])) {
            $this->downloadFile($data['data']['server_package_link'], $token . '_server_package');
        } else {
            CacheService::set($token . '_server_package', 2, 86400);
        }

        if (!empty($data['data']['client_package_link'])) {
            $this->downloadFile($data['data']['client_package_link'], $token . '_client_package');
        } else {
            CacheService::set($token . '_client_package', 2, 86400);
        }

        if (!empty($data['data']['pc_package_link'])) {
            $this->downloadFile($data['data']['pc_package_link'], $token . '_pc_package');
        } else {
            CacheService::set($token . '_pc_package', 2, 86400);
        }

        CacheService::set($token . '_database_backup', 1, 86400);
        UpgradeJob::dispatch('databaseBackup', [$token]);

        CacheService::set($token . '_project_backup', 1, 86400);
        UpgradeJob::dispatch('projectBackup', [$token]);

        CacheService::set('upgrade_token', $token, 86400);
        CacheService::set($token . '_upgrade_data', $data, 86400);
        return true;
    }

    /**
     * 执行下载
     * @param string $seq
     * @param string $url
     * @param string $downloadPath
     * @param string $fileName
     * @param int $timeout
     * @return void
     */
    public function download(string $seq, string $url, string $downloadPath, string $fileName, int $timeout = 300)
    {
        ini_set('memory_limit', '-1');
        $fp_output = fopen($downloadPath . DS . $fileName, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FILE, $fp_output);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (stripos($url, "https://") !== FALSE) curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_exec($ch);
        curl_close($ch);

        if (strpos($fileName, 'zip') === false) {
            throw new AdminException('安装包格式错误');
        }

        /** @var FileService $fileService */
        $fileService = app()->make(FileService::class);
        $downloadFilePath = $downloadPath . DS . substr($fileName, 0, strpos($fileName, 'zip') - 1);
        if (!$fileService->extractFile($downloadPath . DS . $fileName, $downloadFilePath)) {
            throw new AdminException('升级包解压失败');
        }

        CacheService::set($seq . '_path', $downloadFilePath, 86400);
        CacheService::set($seq . '_name', $downloadPath . DS . $fileName, 86400);
        CacheService::set($seq, 2, 86400);
    }

    /**
     * 开始下载
     * @param string $packageLink
     * @param string $seq
     * @return void
     */
    private function downloadFile(string $packageLink, string $seq)
    {
        $fileName = substr($packageLink, strrpos($packageLink, '/') + 1);
        $filePath = app()->getRootPath() . 'upgrade' . DS . date('Y-m-d');;
        if (!is_dir($filePath)) mkdir($filePath, 0755, true);
        UpgradeJob::dispatch('download', [$seq, $packageLink, $filePath, $fileName, 300]);
        CacheService::set($seq, 1, 86400);
    }

    /**
     * 升级进度
     * @return array
     */
    public function getProgress(): array
    {
        $token = CacheService::get('upgrade_token');
        if (empty($token)) {
            throw new AdminException('请重新升级');
        }

        $serverProgress = CacheService::get($token . '_server_package'); // 服务端包下载进度
        $clientProgress = CacheService::get($token . '_client_package'); // 客户端包下载进度
        $pcProgress = CacheService::get($token . '_pc_package'); // PC端包下载进度
        $databaseBackupProgress = CacheService::get($token . '_database_backup'); // 数据库备份进度
        $projectBackupProgress = CacheService::get($token . '_project_backup'); // 项目备份备份进度

        $databaseUpgradeProgress = CacheService::get($token . '_database_upgrade'); // 数据库升级进度
        $coverageProjectProgress = CacheService::get($token . '_coverage_project'); // 项目覆盖进度

        $stepNum = 1;
        $tip = '开始升级';
        if ($serverProgress == $clientProgress && $clientProgress == $pcProgress) {
            $tip = $serverProgress == 1 ? '开始下载安装包' : '安装包下载完成';
            if ($serverProgress == 2) {
                $stepNum += 1;
            }
        } else {
            $tip = '正在下载安装包';
        }

        if ($databaseBackupProgress == 2) {
            $tip = '数据库备份完成';
            $stepNum += 1;
        }

        if ($projectBackupProgress == 2) {
            $tip = '项目备份完成';
            $stepNum += 1;
        }

        if ((int)$databaseUpgradeProgress == 2) {
            $tip = '数据库升级完成';
            $stepNum += 1;
        }

        if ((int)$coverageProjectProgress == 2) {
            $tip = '项目升级完成';
            $stepNum += 1;
        }

        $upgradeStatus = (int)CacheService::get($token . 'upgrade_status');
        if ($upgradeStatus == 2) {
            $stepNum = 6;
            $tip = '升级完成';
        } elseif ($upgradeStatus < 0) {
            $this->saveLog($token);
            throw new AdminException(CacheService::get($token . 'upgrade_status_tip', '升级失败'));
        } elseif ($serverProgress == 2 && $clientProgress == 2 && $pcProgress == 2 && $databaseBackupProgress == 2 && $projectBackupProgress == 2) {
            try {
                $this->overwriteProject();
            } catch (\Exception $e) {
                $this->sendUpgradeLog($token);
            }
        }

        $speed = sprintf("%.1f", $stepNum / 6 * 100);
        return compact('speed', 'tip');
    }

    /**
     * 数据库备份
     * @param $token
     * @return bool
     * @throws \think\db\exception\BindParamException
     */
    public function databaseBackup($token): bool
    {
        try {
            //备份表数据
            /** @var SystemDatabackupServices $backServices */
            $backServices = app()->make(SystemDatabackupServices::class);
            $tables = $backServices->getDataList();
            if (count($tables['list']) < 1) {
                throw new AdminException('数据表获取失败');
            }

            $version = str_replace('.', '', $this->requestData['version']);
            $backServices->getDbBackup()->setFile(['name' => date("YmdHis") . '_' . $version, 'part' => 1]);
            $tables = implode(',', array_column($tables['list'], 'name'));
            $result = $backServices->backup($tables);
            if (!empty($result)) {
                throw new AdminException('数据库备份失败 ' . $result);
            }

            $fileData = $backServices->getDbBackup()->getFile();
            $fileName = $fileData['filename'] . '.gz';
            if (!is_file($fileData['filepath'] . $fileName)) {
                throw new AdminException('数据库备份失败');
            }
            CacheService::set($token . '_database_backup', 2, 86400);
            CacheService::set($token . '_database_backup_name', $fileName, 86400);
            return true;
        } catch (\Exception $e) {
            Log::error('升级失败,失败原因:' . $e->getMessage());
            CacheService::set($token . 'upgrade_status', -1, 86400);
            CacheService::set($token . 'upgrade_status_tip', '升级失败,失败原因:' . $e->getMessage(), 86400);
        }
        return false;
    }

    /**
     * 项目备份
     * @param string $token
     * @return bool
     */
    public function projectBackup(string $token): bool
    {
        try {
            ini_set('memory_limit', '-1');
            $appPath = app()->getRootPath();
            /** @var FileService $fileService */
            $fileService = app()->make(FileService::class);

            $dir = 'backup' . DS . date('Ymd') . DS . $token;
            $backupDir = $appPath . $dir;

            $projectPath = $this->getProjectDir($appPath);
            if (empty($projectPath)) {
                throw new AdminException('项目目录获取异常');
            }

            foreach ($projectPath as $key => $path) {
                foreach ($path as $item) {
                    if ($key == 'file') {
                        $fileService->handleFile($appPath . $item, $backupDir . DS . $item, 'copy', false, ['zip']);
                    } else {
                        $fileService->handleDir($appPath . $item, $backupDir . DS . $item, 'copy', false, ['uploads']);
                    }
                }
            }

            $version = str_replace('.', '', $this->requestData['version']);
            $fileName = date("YmdHis") . '_' . $version . '_project' . '.zip';
            $filePath = $appPath . 'backup' . DS . $fileName;

            /** @var FileService $fileService */
            $fileService = app()->make(FileService::class);
            $result = $fileService->addZip($backupDir, $filePath, $backupDir);
            if (!$result) {
                throw new AdminException('项目备份失败');
            }

            CacheService::set($token . '_project_backup', 2, 86400);
            CacheService::set($token . '_project_backup_name', $fileName, 86400);

            //检测项目备份
            if (!is_file($filePath)) {
                throw new AdminException('项目备份检测失败');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('升级失败,失败原因:' . $e->getMessage());
            CacheService::set($token . 'upgrade_status', -1, 86400);
            CacheService::set($token . 'upgrade_status_tip', '升级失败,失败原因:' . $e->getMessage(), 86400);
        }
        return false;
    }

    /**
     * 获取项目目录
     * @param $path
     * @return array
     */
    public function getProjectDir($path): array
    {
        /** @var FileService $fileService */
        $fileService = app()->make(FileService::class);
        $list = $fileService->getDirs($path);
        $ignore = ['.', '..', '.git', '.idea', 'runtime', 'backup', 'upgrade'];
        foreach ($list as $key => $path) {
            if (empty($key)) {
                unset($list[$key]);
                continue;
            }
            if (is_array($path)) {
                foreach ($path as $key2 => $item) {
                    if (in_array($item, $ignore) && $item) {
                        unset($list[$key][$key2]);
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 升级
     * @return bool
     * @throws \Exception
     */
    public function overwriteProject(): bool
    {
        try {
            if (!$token = CacheService::get('upgrade_token')) {
                throw new AdminException('请重新下载升级包');
            }

            if (CacheService::get($token . 'is_execute') == 2) {
                return true;
            }
            CacheService::set($token . 'is_execute', 2, 86400);

            $dataBackupName = CacheService::get($token . '_database_backup_name');
            if (!$dataBackupName || !is_file(app()->getRootPath() . 'backup' . DS . $dataBackupName)) {
                throw new AdminException('数据库备份失败');
            }

            $serverPackageFilePath = CacheService::get($token . '_server_package_path');
            if (!is_dir($serverPackageFilePath)) {
                throw new AdminException('项目文件获取异常');
            }

            // 执行sql文件
            if (!$this->databaseUpgrade($token, $serverPackageFilePath)) {
                throw new AdminException('数据库升级失败');
            }

            // 替换文件目录
            $this->coverageProject($token);

            // 发送升级日志
            $this->sendUpgradeLog($token);
            $this->saveLog($token);
            CacheService::set($token . 'upgrade_status', 2, 86400);
            return true;
        } catch (\Exception $e) {
            Log::error('升级失败,失败原因:' . $e->getMessage());
            CacheService::set($token . 'upgrade_status', -1, 86400);
            CacheService::set($token . 'upgrade_status_tip', '升级失败,失败原因:' . $e->getMessage(), 86400);
        }
        return false;
    }

    /**
     * 写入日志
     * @param $token
     * @return void
     */
    public function saveLog($token)
    {
        if (CacheService::get($token . 'is_save') == 2) {
            return true;
        }
        CacheService::set($token . 'is_save', 2, 86400);

        $upgradeData = CacheService::get($token . '_upgrade_data');

        $this->dao->save([
            'title' => $upgradeData['data']['title'] ?? '',
            'content' => $upgradeData['data']['content'] ?? '',
            'first_version' => $upgradeData['data']['first_version'] ?? '',
            'second_version' => $upgradeData['data']['second_version'] ?? '',
            'third_version' => $upgradeData['data']['third_version'] ?? '',
            'fourth_version' => $upgradeData['data']['fourth_version'] ?? '',
            'upgrade_time' => time(),
            'error_data' => CacheService::get($token . 'upgrade_status_tip', ''),
            'package_link' => CacheService::get($token . '_project_backup_name', ''),
            'data_link' => CacheService::get($token . '_database_backup_name', '')
        ]);
    }

    /**
     * 发送日志
     * @param string $token
     * @return bool
     */
    public function sendUpgradeLog(string $token): bool
    {
        try {
            $versionBefore = CacheService::get('version_before', '');
            $versionData = $this->getVersion();
            if (empty($versionData)) {
                throw new AdminException('授权信息丢失');
            }
            $versionAfter = $this->recombinationVersion($versionData['version'] ?? '');

            $this->requestData['version_before'] = implode('.', $versionBefore);
            $this->requestData['version_after'] = implode('.', $versionAfter);
            $this->requestData['error_data'] = CacheService::get($token . 'upgrade_status_tip', '');

            $this->getSign($this->timeStamp);
            $result = HttpService::postRequest(self::UPGRADE_LOG_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
            if (!$result) {
                throw new AdminException('升级日志推送失败');
            }

            $data = json_decode($result, true);
            $this->checkAuth($data);
        } catch (\Exception $e) {
            Log::error(['msg' => '升级日志发送失败:,失败原因' . ($data['msg'] ?? '') . $e->getMessage(), 'data' => $data]);
        }
        return true;
    }

    /**
     * 核对签名
     * @return void
     * @throws \Exception
     */
    public function checkSignature()
    {
        $projectSignature = rtrim($this->getVersion('project_signature'));
        if (!$projectSignature) {
            throw new AdminException('项目签名获取异常');
        }

        /** @var fileVerification $verification */
        $verification = app()->make(fileVerification::class);
        $newSignature = $verification->getSignature(app()->getRootPath());
        if ($projectSignature != $newSignature) {
            throw new AdminException('项目签名核对异常');
        }
    }

    /**
     * 生成签名
     * @return void
     * @throws \Exception
     */
    public function generateSignature()
    {
        $file = app()->getRootPath() . '.version';
        if (!$data = @file($file)) {
            throw new AdminException('.version读取失败');
        }
        $list = [];
        if (!empty($data)) {
            foreach ($data as $datum) {
                list($name, $value) = explode('=', $datum);
                $list[$name] = rtrim($value);
            }
        }

        if (!isset($list['project_signature'])) {
            $list['project_signature'] = '';
        }

        /** @var fileVerification $verification */
        $verification = app()->make(fileVerification::class);
        $list['project_signature'] = $verification->getSignature(app()->getRootPath());

        $str = "";
        foreach ($list as $key => $item) {
            $str .= "{$key}={$item}\n";
        }

        file_put_contents($file, $str);
    }

    /**
     * 数据库升级
     * @param string $token
     * @param string $serverPackageFilePath
     * @return bool
     */
    public function databaseUpgrade(string $token, string $serverPackageFilePath): bool
    {
        $databaseFilePath = $serverPackageFilePath . DS . "upgrade" . DS . "database.php";
        if (!is_file($databaseFilePath)) {
            CacheService::set($token . '_database_upgrade', 2, 86400);
            return true;
        }
        CacheService::set($token . '_database_upgrade', 1, 86400);

        $sqlData = include $databaseFilePath;
        $nowCode = $this->getVersion('version_code');
        if ($sqlData['new_code'] <= $nowCode) {
            CacheService::set($token . '_database_upgrade', 2, 86400);
            return true;
        }

        $updateSql = $upgradeSql = [];
        foreach ($sqlData['update_sql'] as $items) {
            if ($items['code'] > $nowCode) {
                $upgradeSql[] = $items;
            }
        }

        if (empty($upgradeSql)) {
            CacheService::set($token . '_database_upgrade', 2, 86400);
            return true;
        }

        $prefix = config('database.connections.' . config('database.default'))['prefix'];
        Db::startTrans();
        try {
            foreach ($upgradeSql as $item) {
                $tip = [
                    '1' => '表已存在',
                    '2' => '表不存在',
                    '3' => '表中' . ($item['field'] ?? '') . '字段已存在',
                    '4' => '表中' . ($item['field'] ?? '') . '字段不存在',
                    '5' => '表中删除字段' . ($item['field'] ?? '') . '不存在',
                    '6' => '表中数据已存在',
                    '6_2' => '表中查询父类ID不存在',
                    '7' => '表中数据已存在',
                    '8' => '表中数据不存在',
                ];
                if (!isset($item['table']) || !$item['table']) {
                    throw new AdminException('请核对升级数据结构:table');
                }

                if (!isset($item['sql']) || !$item['sql']) {
                    throw new AdminException('请核对升级数据结构:sql');
                }

                $whereTable = '';
                $table = $prefix . $item['table'];
                if (isset($item['whereTable']) && $item['whereTable']) {
                    $whereTable = $prefix . $item['whereTable'];
                }

                if (isset($item['findSql']) && $item['findSql']) {
                    $findSql = str_replace('@table', $table, $item['findSql']);
                    if (!empty(Db::query($findSql))) {
                        // 1建表 2删表 3添加字段 4修改字段 5删除字段 6添加数据 7修改数据 8删数据 -1直接执行
                        if (in_array($item['type'], [1, 3, 6])) {
                            throw new AdminException($table . $tip[$item['type']] ?? '未知异常');
                        }
                    } else {
                        if (in_array($item['type'], [4, 5, 7])) {
                            throw new AdminException($table . $tip[$item['type']] ?? '未知异常');
                        }

                        if ($item['type'] == 8) {
                            continue;
                        }
                    }
                }

                if ($item['type'] == 4) {
                    if (!isset($item['rollback_sql']) || !$item['rollback_sql']) {
                        throw new AdminException('请核对升级数据结构:rollback_sql');
                    }
                    $updateSql[] = $item;
                }

                $upSql = str_replace('@table', $table, $item['sql']);
                if ($item['type'] == 6 || $item['type'] == 7) {
                    if (isset($item['whereSql']) && $item['whereSql']) {
                        $whereSql = str_replace('@whereTable', $whereTable, $item['whereSql']);
                        $tabId = Db::query($whereSql)[0]['tabId'] ?? 0;
                        if (!$tabId) {
                            throw new AdminException($table . $tip[$item['type']] ?? '未知异常');
                        }
                        $upSql = str_replace('@tabId', $tabId, $upSql);
                    }
                } elseif ($item['type'] == 8) {
                    $upSql = str_replace(['@table', '@field', '@value'], [$table, $item['field'], $item['value']], $item['sql']);
                } elseif ($item['type'] == -1) {
                    if (isset($item['new_table']) && $item['new_table']) {
                        $new_table = $prefix . $item['new_table'];
                        $upSql = str_replace('@new_table', $new_table, $upSql);
                    }
                }

                if ($upSql) {
                    Db::execute($upSql);
                }
                Log::write(['type' => 'database_upgrade', '`item' => json_encode($item), 'upSql' => $upSql], 'notice');
            }

            Db::commit();
            CacheService::set($token . '_database_upgrade', 2, 86400);
        } catch (\Throwable $e) {
            Db::rollback();
            Log::error(['msg' => '数据库升级失败,失败原因:' . $e->getMessage(), 'data' => json_encode($upgradeSql)]);
            CacheService::set($token . 'upgrade_status', -1, 86400);
            CacheService::set($token . 'upgrade_status_tip', '数据库升级失败,失败原因:' . $e->getMessage(), 86400);
            if (!empty($updateSql)) {
                $this->rollbackStructure($prefix, $updateSql);
            }
            return false;
        }
        return true;
    }

    /**
     * 覆盖项目
     * @param string $token
     * @return bool
     */
    public function coverageProject(string $token): bool
    {
        $versionData = $this->getVersion();
        if (empty($versionData)) {
            throw new AdminException('授权信息异常');
        }
        CacheService::set('version_before', $this->recombinationVersion($versionData['version'] ?? ''), 86400);

        /** @var FileService $fileService */
        $fileService = app()->make(FileService::class);

        // 服务端项目
        $serverPackageName = CacheService::get($token . '_server_package_name');

        // 客户端项目
        $clientPackageName = CacheService::get($token . '_client_package_name');

        // PC端项目
        $pcPackageName = CacheService::get($token . '_pc_package_name');

        if (!is_file($serverPackageName) && !is_file($clientPackageName) && !is_file($pcPackageName)) {
            throw new AdminException('升级文件异常,请重新下载');
        }

        if (is_file($serverPackageName) && !$fileService->extractFile($serverPackageName, app()->getRootPath())) {
            throw new AdminException('服务端解压失败');
        }

        if (is_file($clientPackageName) && !$fileService->extractFile($clientPackageName, app()->getRootPath())) {
            throw new AdminException('客户端解压失败');
        }

        if (is_file($pcPackageName) && !$fileService->extractFile($pcPackageName, app()->getRootPath())) {
            throw new AdminException('PC端解压失败');
        }

        //生成项目签名
        $this->generateSignature();

        CacheService::set($token . '_coverage_project', 2, 86400);
        return true;
    }

    /**
     * 回滚表结构
     * @param string $prefix
     * @param array $updateSql
     * @return void
     */
    public function rollbackStructure(string $prefix, array $updateSql): void
    {
        try {
            foreach ($updateSql as $item) {
                Db::execute(str_replace('@table', $prefix . $item['table'], $item['rollback_sql']));
            }
        } catch (\Exception $e) {
            Log::error(['msg' => '数据库结构回滚失败', 'error' => $e->getFile() . '__' . $e->getLine() . '__' . $e->getMessage(), 'data' => $updateSql]);
        }
    }

    /**
     * 检查访问权限
     * @param array $data
     * @return bool
     */
    public function checkAuth(array $data): bool
    {
        if (!isset($data['status']) || $data['status'] != 200) {
            if ($data['status'] == 410000) {
                $this->getAuth();
            }
            Log::error(['msg' => $data['msg'] ?? '', 'error' => $data]);
            return false;
        }
        return true;
    }

    /**
     * 升级状态
     * @return array
     */
    public function getUpgradeStatus(): array
    {
        $this->getSign($this->timeStamp);
        $result = HttpService::getRequest(self::UPGRADE_STATUS_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
        if (!$result) {
            throw new AdminException('升级状态获取失败');
        }

        $data = json_decode($result, true);
        $this->checkAuth($data);

        $upgradeData['status'] = $data['data']['status'] ?? 0;
        $upgradeData['force_reminder'] = $data['data']['force_reminder'] ?? 0;
        $upgradeData['title'] = $upgradeData['status'] < 1 ? "您已升级至最新版本，无需更新" : "系统有新版本可更新";
        return $upgradeData;
    }

    /**
     * 升级日志
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUpgradeLogList(): array
    {
        [$page, $limit] = $this->getPageValue();
        $count = $this->dao->count();
        $list = $this->dao->getList(['id', 'title', 'content', 'first_version', 'second_version', 'third_version', 'fourth_version', 'upgrade_time', 'package_link', 'data_link'], $page, $limit);

        $rootPath = app()->getRootPath();
        foreach ($list as &$item) {
            $item['file_status'] = 1;
            $item['data_status'] = 1;
            if (!$item['package_link'] || !is_file($rootPath . 'backup' . DS . $item['package_link'])) {
                $item['file_status'] = 0;
            }

            if (!$item['data_link'] || !is_file($rootPath . 'backup' . DS . $item['data_link'])) {
                $item['data_status'] = 0;
            }
            unset($item['package_link'], $item['data_link']);
            $item['upgrade_time'] = date('Y-m-d H:i:s', $item['upgrade_time']);
        }
        return compact('list', 'count');
    }

    /**
     * 导出
     * @param int $id
     * @param string $type
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function export(int $id, string $type)
    {
        $data = $this->dao->getOne(['id' => $id], 'package_link, data_link');
        if (!$data || !$data['package_link']) {
            throw new AdminException('备份文件不存在');
        }

        $fileName = $type == 'file' ? $data['package_link'] : $data['data_link'];
        $filePath = app()->getRootPath() . 'backup' . DS . $fileName;
        if (!is_file($filePath)) {
            throw new AdminException('备份文件不存在');
        }

        //下载文件
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        ob_clean();
        flush();
        readfile($filePath); //输出文件
    }

    /**
     * 检查数据库大小
     * @return bool
     */
    public function checkDatabaseSize(): bool
    {
        if (!$database = Config::get('database.connections.' . Config::get('database.default') . '.database')) {
            throw new AdminException('数据库信息获取失败');
        }

        $result = Db::query("select concat(round(sum(data_length/1024/1024))) as size from information_schema.tables where table_schema='{$database}';");
        if ((int)($result[0]['size'] ?? '') > 500) {
            throw new AdminException('数据库文件过大, 不能升级');
        }
        return true;
    }
}
