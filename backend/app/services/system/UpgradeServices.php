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

namespace app\services\system;

use app\services\system\log\SystemFileMd5Services;
use SplFileInfo;
use think\facade\Config;
use think\facade\Db;
use think\facade\Log;
use app\jobs\UpgradeJob;
use crmeb\basic\BaseService;
use crmeb\utils\File;
use crmeb\utils\Http;
use crmeb\services\CacheService;
use crmeb\utils\fileVerification;
use crmeb\exceptions\AdminException;
use app\dao\system\upgrade\UpgradeLogDao;

/**
 * 在线升级
 * Class UpgradeServices
 * 本类为系统在线升级服务，Db::query/Db::execute/Db::transaction 用于执行升级脚本、
 * 查询 information_schema 及事务控制，不属于日常业务数据操作，保留直接调用。
 * @package app\services\system
 */
class UpgradeServices extends BaseService
{
    /**
     * @var UpgradeLogDao
     */
    protected $dao;

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
    private $timeStamp = 0;

    /**
     * UpgradeServices constructor.
     * @param UpgradeLogDao $dao
     */
    public function __construct(UpgradeLogDao $dao)
    {
        $this->dao = $dao;
        $versionData = $this->getVersion();
        //        if ($versionData['version_code'] < 450) return true;
        if (empty($versionData)) {
            throw new AdminException('授权信息丢失');
        }

        $this->timeStamp = time();
        $recVersion = $this->recombinationVersion($versionData['version'] ?? '');

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
        $result = Http::postRequest(self::LOGIN_URL, $this->requestData);
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
            (!isset($data['app_key']) || !$data['app_key'])
        ) {
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
        $result = Http::getRequest(self::UPGRADE_URL, $this->requestData);
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
        $result = Http::getRequest(self::UPGRADE_CURRENT_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
        if (!$result) {
            throw new AdminException('可升级列表获取失败');
        }

        $data = json_decode($result, true);
        if (!$this->checkAuth($data)) {
            throw new AdminException($data['msg'] ?? '升级列表获取失败');
        }

        if ($data['data']) {
            $routineData = [
                'version' => $data['data'][0]['first_version'] . '.' . $data['data'][0]['second_version'] . '.' . $data['data'][0]['third_version'],
                'desc' => $data['data'][0]['content'],
                'is_live' => 0
            ];
            CacheService::set('routine_upload_data', $routineData, 86400);
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
        $result = Http::getRequest(self::AGREEMENT_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
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

        $this->requestData['package_key'] = $packageKey;
        $this->getSign($this->timeStamp);
        $result = Http::getRequest(self::PACKAGE_DOWNLOAD_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
        if (!$result) {
            throw new AdminException('升级包获取失败');
        }
        $data = json_decode($result, true);

        if (!$this->checkAuth($data)) {
            throw new AdminException($data['msg'] ?? '授权失败');
        }

        if (empty($data['data']['server_package_link']) && empty($data['data']['client_package_link'])) {
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

        $filePath = $downloadPath . DS . $fileName;
        $fp_output = fopen($filePath, 'w');
        if (!$fp_output) {
            throw new AdminException('无法创建下载文件');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);        // 连接超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);         // 总超时时间
        curl_setopt($ch, CURLOPT_FILE, $fp_output);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);      // 跟随重定向
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_REFERER, 'https://www.crmeb.com');
        if (stripos($url, "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp_output);  // 关闭文件句柄

        // 检查下载结果
        if ($result === false || !empty($error)) {
            @unlink($filePath);
            throw new AdminException('下载失败: ' . $error);
        }

        if ($httpCode !== 200) {
            @unlink($filePath);
            throw new AdminException('下载失败，HTTP状态码: ' . $httpCode);
        }

        // 检查文件是否存在且有内容
        if (!is_file($filePath) || filesize($filePath) < 100) {
            @unlink($filePath);
            throw new AdminException('下载的文件无效');
        }

        if (pathinfo($fileName, PATHINFO_EXTENSION) !== 'zip') {
            throw new AdminException('安装包格式错误');
        }

        /** @var File $fileService */
        $fileService = app()->make(File::class);
        $downloadFilePath = $downloadPath . DS . pathinfo($fileName, PATHINFO_FILENAME);
        if (!$fileService->extractFile($filePath, $downloadFilePath)) {
            throw new AdminException('升级包解压失败');
        }

        CacheService::set($seq . '_path', $downloadFilePath, 86400);
        CacheService::set($seq . '_name', $filePath, 86400);
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
        $filePath = app()->getRootPath() . 'upgrade' . DS . date('Y-m-d');
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
        $databaseBackupProgress = CacheService::get($token . '_database_backup'); // 数据库备份进度
        $projectBackupProgress = CacheService::get($token . '_project_backup'); // 项目备份备份进度

        $databaseUpgradeProgress = CacheService::get($token . '_database_upgrade'); // 数据库升级进度
        $coverageProjectProgress = CacheService::get($token . '_coverage_project'); // 项目覆盖进度

        $stepNum = 1;
        $tip = '开始升级';
        if ($serverProgress == $clientProgress) {
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
        } elseif ($serverProgress == 2 && $clientProgress == 2 && $databaseBackupProgress == 2 && $projectBackupProgress == 2) {
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

            // 从.version文件获取版本号
            $versionData = $this->getVersion();
            $backServices->getDbBackup()->setFile(['name' => $versionData['version_code'], 'part' => 1]);
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
            /** @var File $fileService */
            $fileService = app()->make(File::class);

            $dir = 'backup' . DS . date('Ymd') . DS . $token;
            $backupDir = $appPath . $dir;
            $fileService->handleDir($appPath . 'app', $backupDir . DS . 'app');
            $fileService->handleDir($appPath . 'config', $backupDir . DS . 'config');
            $fileService->handleDir($appPath . 'crmeb', $backupDir . DS . 'crmeb');

            // 从.version文件获取版本号
            $versionData = $this->getVersion();
            $fileName = $versionData['version_code'] . '-1.project.zip';
            $filePath = $appPath . 'backup' . DS . $fileName;

            /** @var File $fileService */
            $fileService = app()->make(File::class);
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

            // 压缩完成，删除移动的文件
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($appPath . 'backup' . DS . date('Ymd'), \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var SplFileInfo $fileInfo */
            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isDir()) {
                    @rmdir($fileInfo->getRealPath());
                } else {
                    @unlink($fileInfo->getRealPath());
                }
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
            $result = Http::postRequest(self::UPGRADE_LOG_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
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
                        // 表/字段/数据已存在时跳过，不中断升级
                        if (in_array($item['type'], [1, 3, 6])) {
                            Log::notice(['type' => 'database_upgrade_skip', 'reason' => $table . ($tip[$item['type']] ?? ''), 'item' => json_encode($item)]);
                            continue;
                        }
                    } else {
                        // 表/字段/数据不存在时跳过修改和删除操作
                        if (in_array($item['type'], [4, 5, 7])) {
                            Log::notice(['type' => 'database_upgrade_skip', 'reason' => $table . ($tip[$item['type']] ?? ''), 'item' => json_encode($item)]);
                            continue;
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
                            // 关联数据不存在时跳过，不中断升级
                            Log::notice(['type' => 'database_upgrade_skip', 'reason' => $table . ' 关联数据不存在', 'item' => json_encode($item)]);
                            continue;
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

        /** @var File $fileService */
        $fileService = app()->make(File::class);

        // 服务端项目
        $serverPackageName = CacheService::get($token . '_server_package_name');

        // 客户端项目
        $clientPackageName = CacheService::get($token . '_client_package_name');

        if (!is_file($serverPackageName) && !is_file($clientPackageName)) {
            throw new AdminException('升级文件异常,请重新下载');
        }

        // 服务端解压前，清空前端相关目录，避免旧文件残留
        if (is_file($serverPackageName)) {
            $clearDirs = [
                app()->getRootPath() . 'public' . DS . 'admin',
                app()->getRootPath() . 'public' . DS . 'statics' . DS . 'mp_view',
                app()->getRootPath() . 'public' . DS . 'statics' . DS . 'download',
            ];
            foreach ($clearDirs as $clearDir) {
                if (is_dir($clearDir)) {
                    $this->clearDirectory($clearDir);
                }
            }
            if (!$fileService->extractFile($serverPackageName, app()->getRootPath())) {
                throw new AdminException('服务端解压失败');
            }
        }

        if (is_file($clientPackageName) && !$fileService->extractFile($clientPackageName, app()->getRootPath())) {
            throw new AdminException('客户端解压失败');
        }

        CacheService::set($token . '_coverage_project', 2, 86400);
        return true;
    }

    /**
     * 清空目录下所有文件和子目录（保留目录本身）
     * @param string $dir 目录路径
     * @return void
     */
    public function clearDirectory(string $dir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
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
     * 恢复数据库备份
     * @param string $backupFileName 备份文件名
     * @return bool
     */
    public function restoreDatabase(string $backupFileName): bool
    {
        try {
            $backupPath = app()->getRootPath() . 'backup' . DS . $backupFileName;
            if (!is_file($backupPath)) {
                throw new AdminException('数据库备份文件不存在');
            }

            // 检测是否是 gz 压缩文件
            $isGz = (pathinfo($backupFileName, PATHINFO_EXTENSION) === 'gz');

            // 直接读取并执行 SQL 文件
            $db = \think\facade\Db::connect();

            if ($isGz) {
                $gz = gzopen($backupPath, 'r');
                if (!$gz) {
                    throw new AdminException('无法打开备份文件');
                }

                $sql = '';
                while (!gzeof($gz)) {
                    $line = gzgets($gz);
                    // 跳过注释和空行
                    if (empty(trim($line)) || strpos(trim($line), '--') === 0) {
                        continue;
                    }
                    $sql .= $line;
                    // 检测是否是完整的 SQL 语句
                    if (preg_match('/;\s*$/', trim($sql))) {
                        try {
                            $db->execute($sql);
                        } catch (\Exception $e) {
                            // 跳过 SET 和某些特殊语句的错误
                            if (strpos($sql, 'SET FOREIGN_KEY_CHECKS') === false) {
                                Log::warning('执行SQL失败: ' . $e->getMessage());
                            }
                        }
                        $sql = '';
                    }
                }
                gzclose($gz);
            } else {
                $handle = fopen($backupPath, 'r');
                if (!$handle) {
                    throw new AdminException('无法打开备份文件');
                }

                $sql = '';
                while (!feof($handle)) {
                    $line = fgets($handle);
                    // 跳过注释和空行
                    if (empty(trim($line)) || strpos(trim($line), '--') === 0) {
                        continue;
                    }
                    $sql .= $line;
                    // 检测是否是完整的 SQL 语句
                    if (preg_match('/;\s*$/', trim($sql))) {
                        try {
                            $db->execute($sql);
                        } catch (\Exception $e) {
                            if (strpos($sql, 'SET FOREIGN_KEY_CHECKS') === false) {
                                Log::warning('执行SQL失败: ' . $e->getMessage());
                            }
                        }
                        $sql = '';
                    }
                }
                fclose($handle);
            }

            Log::notice(['type' => 'database_restore', 'file' => $backupFileName]);
            return true;
        } catch (\Exception $e) {
            Log::error('数据库恢复失败: ' . $e->getMessage());
            throw new AdminException('数据库恢复失败: ' . $e->getMessage());
        }
    }

    /**
     * 恢复项目文件备份
     * @param string $backupFileName 备份文件名
     * @return bool
     */
    public function restoreProject(string $backupFileName): bool
    {
        try {
            $backupPath = app()->getRootPath() . 'backup' . DS . $backupFileName;
            if (!is_file($backupPath)) {
                throw new AdminException('项目备份文件不存在');
            }

            /** @var File $fileService */
            $fileService = app()->make(File::class);

            // 解压zip文件到项目根目录
            $result = $fileService->extractFile($backupPath, app()->getRootPath());
            if (!$result) {
                throw new AdminException('项目文件恢复失败');
            }

            Log::notice(['type' => 'project_restore', 'file' => $backupFileName]);
            return true;
        } catch (\Exception $e) {
            Log::error('项目文件恢复失败: ' . $e->getMessage());
            throw new AdminException('项目文件恢复失败: ' . $e->getMessage());
        }
    }

    /**
     * 完整回退到指定版本
     * @param int $logId 升级日志ID
     * @return array
     */
    public function rollbackToVersion(int $logId): array
    {
        try {
            // 获取升级记录
            $logData = $this->dao->getOne(['id' => $logId]);
            if (!$logData) {
                throw new AdminException('升级记录不存在');
            }

            $result = [
                'database_restored' => false,
                'project_restored' => false,
                'version_restored' => false,
                'message' => ''
            ];

            // 1. 恢复数据库
            if (!empty($logData['data_link'])) {
                $this->restoreDatabase($logData['data_link']);
                $result['database_restored'] = true;
            }

            // 2. 恢复项目文件
            if (!empty($logData['package_link'])) {
                $this->restoreProject($logData['package_link']);
                $result['project_restored'] = true;
            }

            // 3. 恢复版本号
            $versionStr = sprintf(
                '%s.%s.%s.%s',
                $logData['first_version'] ?? '5',
                $logData['second_version'] ?? '5',
                $logData['third_version'] ?? '0',
                $logData['fourth_version'] ?? '0'
            );
            $versionCode = (int)($logData['first_version'] . $logData['second_version'] . $logData['third_version']);

            $versionManager = $this->getVersionManager();
            $versionManager->updateVersionFile('CRMEB-BZ v' . $versionStr, $versionCode);
            $result['version_restored'] = true;

            $result['message'] = '回退成功';
            Log::notice(['type' => 'version_rollback', 'log_id' => $logId, 'version' => $versionStr]);

            return $result;
        } catch (\Exception $e) {
            Log::error('版本回退失败: ' . $e->getMessage());
            throw new AdminException('版本回退失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取可回退的版本列表
     * @return array
     */
    public function getRollbackVersions(): array
    {
        $list = $this->dao->getList(['id', 'title', 'first_version', 'second_version', 'third_version', 'fourth_version', 'upgrade_time', 'package_link', 'data_link'], 1, 20);

        $rootPath = app()->getRootPath();
        $rollbackList = [];

        foreach ($list as $item) {
            $canRollback = true;
            $reason = [];

            // 检查备份文件是否存在
            if (empty($item['package_link']) || !is_file($rootPath . 'backup' . DS . $item['package_link'])) {
                $canRollback = false;
                $reason[] = '项目备份文件不存在';
            }

            if (empty($item['data_link']) || !is_file($rootPath . 'backup' . DS . $item['data_link'])) {
                $canRollback = false;
                $reason[] = '数据库备份文件不存在';
            }

            $rollbackList[] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'version' => sprintf('v%s.%s.%s', $item['first_version'], $item['second_version'], $item['third_version']),
                'upgrade_time' => date('Y-m-d H:i:s', $item['upgrade_time']),
                'can_rollback' => $canRollback,
                'reason' => implode(', ', $reason)
            ];
        }

        return $rollbackList;
    }

    /**
     * 检查访问权限
     * @param array $data
     * @return bool
     */
    public function checkAuth(array $data): bool
    {
        if (!isset($data['status']) || $data['status'] != 200) {
            if ($data['status'] == '请输入账号和密码') {
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
        $result = Http::getRequest(self::UPGRADE_STATUS_URL, $this->requestData, ['Access-Token: Bearer ' . CacheService::get('upgrade_auth_token')]);
        if (!$result) {
            throw new AdminException('升级状态获取失败');
        }

        $data = json_decode($result, true);
        $this->checkAuth($data);

        if (!isset($data['data']['auth']) || !$data['data']['auth']) {
            throw new AdminException('您的域名未授权，请先授权');
        }

        $upgradeData['status'] = $data['data']['status'] ?? 0;
        $upgradeData['force_reminder'] = $data['data']['force_reminder'] ?? 0;
        $upgradeData['title'] = $upgradeData['status'] < 1 ? "您已升级至最新版本，无需更新" : "系统有新版本可更新";
        return $upgradeData;
    }

    /**
     * 重新执行升级
     * @param $type
     * @return bool
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/2/27
     */
    public function reExecute($type)
    {
        $token = CacheService::get('upgrade_token');
        switch ($type) {
            case 0:
                CacheService::set($token . '_check_md5_status', 0, 86400);
                break;
            case 1:
                CacheService::set($token . '_database_backup', 0, 86400);
                CacheService::set($token . '_project_backup', 0, 86400);
                break;
            case 2:
                CacheService::set($token . '_server_package', 0, 86400);
                CacheService::set($token . '_client_package', 0, 86400);
                break;
            case 3:
                CacheService::set($token . '_coverage_project', 0, 86400);
                break;
            default:
                throw new AdminException('参数错误');
        }
        return true;
    }

    /**
     * 获取下载进度
     * 返回下载包、备份、解压的进度状态
     * @param $type
     */
    public function getDownloadProgress($type)
    {
        // 如果type不在0,1,2,3,4中，则返回错误
        if (!in_array($type, [0, 1, 2, 3, 4])) {
            throw new AdminException('参数错误');
        }

        // 获取升级token
        $token = CacheService::get('upgrade_token');
        if (empty($token)) {
            return [
                'stage' => 'idle',
                'progress' => 0,
                'message' => '未开始下载',
                'completed' => false,
                'can_upgrade' => false
            ];
        }

        // 阶段1：检测文件是否有改动
        if ($type == 0) {
            // 检查MD5状态，0未检查，1检查中，2检查成功，3忽略
            $checkMd5Status = (int)CacheService::get($token . '_check_md5_status', 0);
            // 检查MD5文件差异数据，空为无差异，否则为差异文件
            $checkMd5File = (array)CacheService::get($token . '_check_md5_file', []);
            // 检测文件是否有改动
            if ($checkMd5Status < 2) {
                // 检测文件状态为0时，开始检测
                if ($checkMd5Status == 0) {
                    // 设置检测状态为1，开始检测
                    CacheService::set($token . '_check_md5_status', 1, 86400);
                    // 触发检测任务
                    UpgradeJob::dispatch('checkFileMd5', [$token]);
                }
                // 检测文件状态为1时，等待检测完成
                if (empty($checkMd5File)) {
                    return [
                        'stage' => 'loading',
                        'type' => 0,
                        'progress' => 0,
                        'message' => '正在检测文件是否有改动...',
                        'completed' => false,
                        'can_upgrade' => false
                    ];
                } else {
                    return [
                        'stage' => 'error',
                        'type' => 0,
                        'progress' => 0,
                        'message' => '文件有改动，请确认是否继续升级...',
                        'completed' => false,
                        'can_upgrade' => false,
                        'data' => $checkMd5File
                    ];
                }
            } else {
                return [
                    'stage' => 'success',
                    'type' => 0,
                    'progress' => 0,
                    'message' => '文件检测完成',
                    'completed' => false,
                    'can_upgrade' => false
                ];
            }
        }

        // 阶段2: 备份
        if ($type == 1) {
            // 检测数据库备份状态，0未备份，1备份中，2备份成功，3忽略
            $databaseBackup = (int)CacheService::get($token . '_database_backup', 0);
            // 检测项目备份状态，0未备份，1备份中，2备份成功，3忽略
            $projectBackup = (int)CacheService::get($token . '_project_backup', 0);
            // 检测备份
            if ($databaseBackup < 2 || $projectBackup < 2) {
                // 检测数据库备份状态为0时，开始备份
                if ($databaseBackup == 0) {
                    // 设置数据库备份状态为1，开始备份
                    CacheService::set($token . '_database_backup', 1, 86400);
                    // 触发备份任务
                    UpgradeJob::dispatch('databaseBackup', [$token]);
                }
                // 检测项目备份状态为0时，开始备份
                if ($projectBackup == 0) {
                    // 设置项目备份状态为1，开始备份
                    CacheService::set($token . '_project_backup', 1, 86400);
                    // 触发备份任务
                    UpgradeJob::dispatch('projectBackup', [$token]);
                }
                return [
                    'stage' => 'loading',
                    'type' => 1,
                    'progress' => 33,
                    'message' => '正在备份文件和数据库...',
                    'completed' => false,
                    'can_upgrade' => false
                ];
            } else {
                // 检测文件状态为2时，检查文件是否备份成功，备份路径为 backup/600-1.project.zip和 backup/600-1.sql.gz
                $backupPath = app()->getRootPath() . 'backup' . DS;
                $versionData = $this->getVersion();
                $projectBackupFile = $backupPath . $versionData['version_code'] . '-1.project.zip';
                $databaseBackupFile = $backupPath . $versionData['version_code'] . '-1.sql.gz';
                if (!is_file($projectBackupFile) || !is_file($databaseBackupFile)) {
                    return [
                        'stage' => 'error',
                        'type' => 1,
                        'progress' => 33,
                        'message' => '系统备份失败！',
                        'completed' => false,
                        'can_upgrade' => false
                    ];
                } else {
                    return [
                        'stage' => 'success',
                        'type' => 1,
                        'progress' => 33,
                        'message' => '系统备份成功！',
                        'completed' => false,
                        'can_upgrade' => false
                    ];
                }
            }
        }

        // 阶段3: 下载更新包
        if ($type == 2) {
            // 检测服务端升级包状态，0未下载，1下载中，2下载成功，3忽略
            $serverPackage = (int)CacheService::get($token . '_server_package', 0);
            // 检测客户端升级包状态，0未下载，1下载中，2下载成功，3忽略
            $clientPackage = (int)CacheService::get($token . '_client_package', 0);
            // 检测升级包下载
            if ($serverPackage < 2 || $clientPackage < 2) {
                return [
                    'stage' => 'loading',
                    'type' => 2,
                    'progress' => 66,
                    'message' => '正在下载升级包...',
                    'completed' => false,
                    'can_upgrade' => false,
                    'detail' => [
                        'server' => $serverPackage,
                        'client' => $clientPackage
                    ]
                ];
            } else {
                return [
                    'stage' => 'success',
                    'type' => 2,
                    'progress' => 66,
                    'message' => '升级包下载完成！',
                    'completed' => false,
                    'can_upgrade' => false
                ];
            }
        }

        // 阶段4: 覆盖数据库升级文件，进行升级
        if ($type == 3) {
            $coverageProject = (int)CacheService::get($token . '_coverage_project', 0);
            if ($coverageProject < 2) {
                // 解压覆盖数据库更新文件
                if ($coverageProject == 0) {
                    // 设置解压覆盖项目状态为1，开始解压覆盖
                    CacheService::set($token . '_coverage_project', 1, 86400);
                    // 获取下载解压的文件目录
                    $serverPackagePath = CacheService::get($token . '_server_package_path');
                    // 判断目录$downloadFilePath目录下面是否存在config/和upgrade/versions/目录，如果存在，将这两个文件夹覆盖到项目根目录
                    $configPath = $serverPackagePath . DS . 'config';
                    $versionsPath = $serverPackagePath . DS . 'upgrade' . DS . 'versions';
                    if (is_dir($configPath) && is_dir($versionsPath)) {
                        /** @var File $fileService */
                        $fileService = app()->make(File::class);
                        // 复制config目录
                        $res = $fileService->copyDir($configPath, app()->getRootPath() . 'config');
                        // 复制upgrade/versions目录
                        $res = $res && $fileService->copyDir($versionsPath, app()->getRootPath() . 'upgrade' . DS . 'versions');
                        // 覆盖成功
                        if ($res) {
                            // 设置解压覆盖项目状态为2，覆盖成功
                            CacheService::set($token . '_coverage_project', 2, 86400);
                            return [
                                'stage' => 'loading',
                                'type' => 3,
                                'progress' => 100,
                                'message' => '覆盖数据库升级文件完成，开始执行升级...',
                                'completed' => true,
                                'can_upgrade' => true,
                            ];
                        }
                    }
                }
            } else {
                // 执行所有跨版本升级
                $data = $this->executeAllCrossVersionUpgrade();
                // 执行成功
                return [
                    'stage' => 'success',
                    'type' => 3,
                    'progress' => 100,
                    'message' => '数据库更新完成',
                    'completed' => true,
                    'can_upgrade' => true,
                    'data' => $data
                ];
            }
        }

        // 阶段5: 覆盖项目文件
        if ($type == 4) {
            // 覆盖项目文件
            $this->coverageProject($token);
            return [
                'stage' => 'complete',
                'type' => 4,
                'progress' => 100,
                'message' => '全部更新完成',
                'completed' => true,
                'can_upgrade' => true,
                'routine_upload_data' => CacheService::get('routine_upload_data', [])
            ];
        }

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
            $item['file_status'] = 0;
            $item['data_status'] = 0;
            if ($item['package_link'] && is_file($rootPath . 'backup' . DS . $item['package_link'])) {
                $item['package_link'] = 'backup/' . $item['package_link'];
                $item['file_status'] = 1;
            }
            if ($item['data_link'] && is_file($rootPath . 'backup' . DS . $item['data_link'])) {
                $item['data_link'] = 'backup/' . $item['data_link'];
                $item['data_status'] = 1;
            }
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

    // ==================== 跨版本升级相关方法 ====================

    /**
     * 获取版本管理器实例
     * @return \upgrade\VersionManager
     */
    protected function getVersionManager()
    {
        // 手动加载 VersionManager 类（避免修改 composer.json）
        $file = app()->getRootPath() . 'upgrade' . DIRECTORY_SEPARATOR . 'VersionManager.php';
        if (!class_exists('\\upgrade\\VersionManager') && file_exists($file)) {
            require_once $file;
        }
        return new \upgrade\VersionManager();
    }

    /**
     * 获取跨版本升级概览
     * @return array
     */
    public function getCrossVersionUpgradeOverview(): array
    {
        $versionManager = $this->getVersionManager();
        return $versionManager->getUpgradeOverview();
    }

    /**
     * 获取待升级版本列表
     * @return array
     */
    public function getPendingVersions(): array
    {
        $versionManager = $this->getVersionManager();
        return $versionManager->getPendingVersions();
    }

    /**
     * 获取所有待执行的升级SQL
     * @return array
     */
    public function getAllPendingUpgradeSql(): array
    {
        $versionManager = $this->getVersionManager();
        return $versionManager->getAllPendingUpgradeSql();
    }

    /**
     * 执行跨版本升级
     * @param int $step 当前执行到第几步
     * @return array ['success' => bool, 'step' => int, 'total' => int, 'message' => string, 'completed' => bool]
     */
    public function executeCrossVersionUpgrade(int $step = 0): array
    {
        $versionManager = $this->getVersionManager();
        $allSql = $versionManager->getAllPendingUpgradeSql();
        $total = count($allSql);

        // 检查是否已完成所有升级
        if ($step >= $total) {
            // 获取升级前的版本信息
            $beforeVersion = CacheService::get('cross_version_before_version', []);
            $pendingVersions = $versionManager->getPendingVersions();

            // 更新版本文件为最新版本
            $latestVersion = $versionManager->getLatestVersion();
            if (!empty($latestVersion)) {
                $versionManager->updateVersionFile($latestVersion['version'], $latestVersion['code']);
            }

            // 保存升级日志
            $token = CacheService::get('cross_version_upgrade_token', '');
            if ($token) {
                $this->saveCrossVersionUpgradeLog($token, $beforeVersion, $latestVersion, $pendingVersions);
            }

            return [
                'success' => true,
                'step' => $step,
                'total' => $total,
                'message' => '升级完成',
                'completed' => true,
                'current_version' => $latestVersion['version'] ?? ''
            ];
        }

        // 对于第一步，执行备份
        if ($step == 0) {
            // 记录升级前的版本信息
            $beforeVersion = $versionManager->getCurrentVersion();
            CacheService::set('cross_version_before_version', $beforeVersion, 86400);

            $token = md5(time() . uniqid('', true));
            CacheService::set('cross_version_upgrade_token', $token, 86400);

            $backupResult = $this->performBackup($token);
            if (!$backupResult) {
                return [
                    'success' => false,
                    'step' => $step,
                    'total' => $total,
                    'message' => '备份失败，无法继续升级',
                    'completed' => false
                ];
            }
        }

        // 执行当前步骤的SQL
        $sqlItem = $allSql[$step];
        $result = $versionManager->executeSqlItem($sqlItem);

        $message = $result['message'] ?? '';
        if (isset($sqlItem['version'])) {
            $message = "[{$sqlItem['version']}] " . $message;
        }

        return [
            'success' => $result['success'],
            'step' => $step + 1,
            'total' => $total,
            'message' => $message,
            'completed' => false,
            'skipped' => $result['skipped'] ?? false,
            'sql_info' => [
                'table' => $sqlItem['table'] ?? '',
                'field' => $sqlItem['field'] ?? '',
                'type' => $sqlItem['type'] ?? 0,
                'version' => $sqlItem['version'] ?? ''
            ]
        ];
    }

    /**
     * 一键执行全部跨版本升级
     * @return array
     */
    public function executeAllCrossVersionUpgrade(): array
    {
        $versionManager = $this->getVersionManager();
        $pendingVersions = $versionManager->getPendingVersions();

        if (empty($pendingVersions)) {
            return [
                'success' => true,
                'message' => '当前已是最新版本，无需升级',
                'executed' => 0,
                'skipped' => 0,
                'failed' => 0,
                'sql_logs' => []
            ];
        }

        // 记录升级前的版本信息
        $beforeVersion = $versionManager->getCurrentVersion();

        // 在执行跨版本升级前进行备份
        $token = CacheService::get('upgrade_token');
        CacheService::set('cross_version_upgrade_token', $token, 86400);

        // 初始化进度状态
        CacheService::set($token . '_sql_progress', ['current' => 0, 'total' => 0], 86400);
        CacheService::set($token . '_sql_logs', [], 86400);
        CacheService::set($token . '_upgrade_complete', 0, 86400);

        $executed = 0;
        $skipped = 0;
        $failed = 0;
        $failedMessages = [];
        $migrationResults = [];
        $sqlLogs = [];

        // 统计总SQL数量
        $totalSql = 0;
        foreach ($pendingVersions as $version) {
            $upgradeData = $versionManager->getVersionUpgradeData($version);
            if (!empty($upgradeData['update_sql'])) {
                $totalSql += count($upgradeData['update_sql']);
            }
        }
        $this->updateSqlProgress($token, 0, $totalSql);

        $currentSql = 0;

        // 遍历每个版本
        foreach ($pendingVersions as $version) {
            $upgradeData = $versionManager->getVersionUpgradeData($version);

            // 执行SQL升级
            if (!empty($upgradeData['update_sql'])) {
                foreach ($upgradeData['update_sql'] as $sqlItem) {
                    $currentSql++;
                    $result = $versionManager->executeSqlItem($sqlItem);

                    // 记录SQL执行日志
                    $logEntry = [
                        'version' => $version['version'],
                        'table' => $sqlItem['table'] ?? '-',
                        'field' => $sqlItem['field'] ?? '',
                        'type' => $sqlItem['type'] ?? 0,
                        'status' => $result['success'] ? (isset($result['skipped']) && $result['skipped'] ? 'skipped' : 'success') : 'failed',
                        'message' => $result['message'] ?? ''
                    ];
                    $sqlLogs[] = $logEntry;
                    $this->addSqlLog($token, $logEntry);

                    if ($result['success']) {
                        if (isset($result['skipped']) && $result['skipped']) {
                            $skipped++;
                        } else {
                            $executed++;
                        }
                    } else {
                        $failed++;
                        $failedMessages[] = "[{$version['version']}] " . $result['message'];
                    }

                    // 更新进度
                    $this->updateSqlProgress($token, $currentSql, $totalSql);
                }
            }

            // 执行数据迁移处理器
            if (!empty($upgradeData['data_handlers']) && $failed == 0) {
                /** @var DataMigrationServices $migrationServices */
                $migrationServices = app()->make(DataMigrationServices::class);
                $migrationResult = $migrationServices->executeAllHandlers($upgradeData['data_handlers']);
                $migrationResults[$version['version']] = $migrationResult;

                if (!$migrationResult['success']) {
                    $failed++;
                    $failedMessages[] = "[{$version['version']}] 数据迁移失败";
                }
            }

            // 每个版本升级完成后更新版本文件
            $versionManager->updateVersionFile($version['version'], $version['code']);
            Log::notice(['type' => 'cross_version_upgrade', 'version' => $version['version'], 'code' => $version['code']]);
        }

        // 标记升级完成
        $this->markUpgradeComplete($token);

        // 最终获取最新版本信息
        $latestVersion = $versionManager->getLatestVersion();

        // 升级成功后保存升级日志
        if ($failed == 0) {
            $this->saveCrossVersionUpgradeLog($token, $beforeVersion, $latestVersion, $pendingVersions);
        }

        return [
            'success' => $failed == 0,
            'message' => $failed == 0 ? '升级成功' : '部分SQL执行失败',
            'executed' => $executed,
            'skipped' => $skipped,
            'failed' => $failed,
            'total' => $totalSql,
            'failed_messages' => $failedMessages,
            'migration_results' => $migrationResults,
            'current_version' => $latestVersion['version'] ?? '',
            'sql_logs' => $sqlLogs
        ];
    }

    /**
     * 执行备份
     * @param string $token
     * @return bool
     */
    protected function performBackup(string $token): bool
    {
        try {
            // 执行数据库备份
            CacheService::set($token . '_database_backup', 1, 86400);
            $this->databaseBackup($token);

            // 等待数据库备份完成
            $maxWait = 30; // 最大等待30秒
            $waited = 0;
            while ($waited < $maxWait) {
                if (CacheService::get($token . '_database_backup') == 2) {
                    break;
                }
                sleep(1);
                $waited++;
            }

            if (CacheService::get($token . '_database_backup') != 2) {
                throw new AdminException('数据库备份超时');
            }

            // 执行项目备份
            CacheService::set($token . '_project_backup', 1, 86400);
            $this->projectBackup($token);

            // 等待项目备份完成
            $waited = 0;
            while ($waited < $maxWait) {
                if (CacheService::get($token . '_project_backup') == 2) {
                    break;
                }
                sleep(1);
                $waited++;
            }

            if (CacheService::get($token . '_project_backup') != 2) {
                throw new AdminException('项目备份超时');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('执行备份失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取备份状态
     * @return array
     */
    public function getBackupStatus(): array
    {
        $token = CacheService::get('upgrade_token');
        if (empty($token)) {
            return [
                'database_backup' => 0,
                'project_backup' => 0,
                'message' => '未开始备份'
            ];
        }

        $databaseBackup = CacheService::get($token . '_database_backup');
        $projectBackup = CacheService::get($token . '_project_backup');

        return [
            'database_backup' => $databaseBackup ?? 0,
            'project_backup' => $projectBackup ?? 0,
            'message' => $this->getBackupMessage($databaseBackup, $projectBackup)
        ];
    }

    /**
     * 获取备份状态信息
     * @param int $databaseBackup
     * @param int $projectBackup
     * @return string
     */
    private function getBackupMessage(int $databaseBackup, int $projectBackup): string
    {
        if ($databaseBackup == 2 && $projectBackup == 2) {
            return '备份完成';
        } elseif ($databaseBackup == 1 || $projectBackup == 1) {
            return '正在备份';
        } elseif ($databaseBackup == 0 && $projectBackup == 0) {
            return '未开始备份';
        } else {
            return '备份异常';
        }
    }

    /**
     * 获取升级进度详情
     * @return array
     */
    public function getUpgradeProgressDetail(): array
    {
        $token = CacheService::get('cross_version_upgrade_token') ?: CacheService::get('upgrade_token');

        if (empty($token)) {
            return [
                'step' => 0,
                'progress' => 0,
                'step_details' => [
                    'database' => '未开始',
                    'project' => '未开始',
                    'sql' => '未开始',
                    'complete' => '未开始',
                ],
                'sql_logs' => [],
            ];
        }

        // 获取各步骤状态
        $databaseBackup = CacheService::get($token . '_database_backup', 0);
        $projectBackup = CacheService::get($token . '_project_backup', 0);
        $sqlProgress = CacheService::get($token . '_sql_progress', ['current' => 0, 'total' => 0]);
        $sqlLogs = CacheService::get($token . '_sql_logs', []);
        $upgradeComplete = CacheService::get($token . '_upgrade_complete', 0);

        // 计算当前步骤和进度
        $step = 0;
        $progress = 0;
        $stepDetails = [
            'database' => '等待中...',
            'project' => '等待中...',
            'sql' => '等待中...',
            'complete' => '等待中...',
        ];

        // 步骤1: 数据库备份
        if ($databaseBackup == 1) {
            $step = 0;
            $progress = 10;
            $stepDetails['database'] = '正在备份数据库...';
        } elseif ($databaseBackup == 2) {
            $step = 1;
            $progress = 25;
            $stepDetails['database'] = '数据库备份成功 ✓';
        }

        // 步骤2: 项目文件备份
        if ($databaseBackup == 2) {
            if ($projectBackup == 1) {
                $step = 1;
                $progress = 35;
                $stepDetails['project'] = '正在备份项目文件...';
            } elseif ($projectBackup == 2) {
                $step = 2;
                $progress = 50;
                $stepDetails['project'] = '项目文件备份成功 ✓';
            }
        }

        // 步骤3: SQL执行
        if ($databaseBackup == 2 && $projectBackup == 2) {
            $current = $sqlProgress['current'] ?? 0;
            $total = $sqlProgress['total'] ?? 0;

            if ($total > 0) {
                $sqlPercent = ($current / $total) * 100;
                $progress = 50 + ($sqlPercent * 0.4); // 50-90
                $stepDetails['sql'] = "执行中: {$current}/{$total}";

                if ($current >= $total) {
                    $step = 3;
                    $progress = 90;
                    $failedCount = count(array_filter($sqlLogs, function ($log) { return $log['status'] === 'failed'; }));
                    $stepDetails['sql'] = $failedCount > 0
                        ? "SQL执行完成({$failedCount}项失败)"
                        : 'SQL执行完成 ✓';
                }
            } else {
                $stepDetails['sql'] = '等待执行...';
            }
        }

        // 步骤4: 完成
        if ($upgradeComplete == 2) {
            $step = 4;
            $progress = 100;
            $stepDetails['complete'] = '升级完成 ✓';
        }

        return [
            'step' => $step,
            'progress' => (int)$progress,
            'step_details' => $stepDetails,
            'sql_logs' => array_slice($sqlLogs, -50), // 最多返回50条
        ];
    }

    /**
     * 更新SQL执行进度
     * @param string $token
     * @param int $current
     * @param int $total
     * @return void
     */
    protected function updateSqlProgress(string $token, int $current, int $total): void
    {
        CacheService::set($token . '_sql_progress', ['current' => $current, 'total' => $total], 86400);
    }

    /**
     * 添加SQL执行日志
     * @param string $token
     * @param array $log
     * @return void
     */
    protected function addSqlLog(string $token, array $log): void
    {
        $logs = CacheService::get($token . '_sql_logs', []);
        $logs[] = $log;
        CacheService::set($token . '_sql_logs', $logs, 86400);
    }

    /**
     * 标记升级完成
     * @param string $token
     * @return void
     */
    protected function markUpgradeComplete(string $token): void
    {
        CacheService::set($token . '_upgrade_complete', 2, 86400);
    }

    /**
     * 判断是否需要跨版本升级
     * @return bool
     */
    public function needCrossVersionUpgrade(): bool
    {
        $versionManager = $this->getVersionManager();
        return $versionManager->needUpgrade();
    }

    /**
     * 获取版本差距
     * @return int
     */
    public function getVersionGap(): int
    {
        $versionManager = $this->getVersionManager();
        return $versionManager->getVersionGap();
    }

    /**
     * 检查跨版本升级可用性
     * 检查当前版本是否满足最低版本要求
     * @return array
     */
    public function checkCrossVersionUpgradeAvailability(): array
    {
        $versionManager = $this->getVersionManager();
        return $versionManager->checkUpgradeAvailability();
    }

    /**
     * 当前版本是否满足最低版本要求
     * @return bool
     */
    public function meetsMinVersionRequirement(): bool
    {
        $versionManager = $this->getVersionManager();
        return $versionManager->meetsMinVersionRequirement();
    }

    /**
     * 保存跨版本升级日志
     * @param string $token 升级token
     * @param array $beforeVersion 升级前版本信息
     * @param array $latestVersion 升级后版本信息
     * @param array $pendingVersions 升级的版本列表
     * @return bool
     */
    protected function saveCrossVersionUpgradeLog(string $token, array $beforeVersion, array $latestVersion, array $pendingVersions): bool
    {
        try {
            // 解析升级前的版本号
            $beforeVersionStr = $beforeVersion['version'] ?? '';
            // 解析升级后的版本号
            $afterVersionStr = $latestVersion['version'] ?? '';
            $afterVersionParts = $this->parseVersionString($afterVersionStr);

            // 获取备份文件名
            $packageLink = CacheService::get($token . '_project_backup_name', '');
            $dataLink = CacheService::get($token . '_database_backup_name', '');
            $updateContent = CacheService::get('routine_upload_data', [])['desc'] ?? '暂无';
            // 保存到数据库
            $this->dao->save([
                'title' => '升级 ' . $afterVersionStr . ' 完成',
                'content' => '版本升级: ' . $beforeVersionStr . ' -> ' . $afterVersionStr . '；更新内容：' . $updateContent . '；',
                'first_version' => $afterVersionParts['first'] ?? '6',
                'second_version' => $afterVersionParts['second'] ?? '0',
                'third_version' => $afterVersionParts['third'] ?? '0',
                'fourth_version' => $afterVersionParts['fourth'] ?? '0',
                'upgrade_time' => time(),
                'error_data' => '',
                'package_link' => $packageLink,
                'data_link' => $dataLink
            ]);

            Log::notice(['type' => 'cross_version_upgrade_log', 'before' => $beforeVersionStr, 'after' => $afterVersionStr, 'token' => $token]);
            return true;
        } catch (\Exception $e) {
            Log::error('保存跨版本升级日志失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 解析版本字符串
     * @param string $versionStr 例如 "CRMEB-BZ v5.6.4"
     * @return array
     */
    protected function parseVersionString(string $versionStr): array
    {
        $result = ['first' => '5', 'second' => '5', 'third' => '0', 'fourth' => '0'];

        // 匹配版本号 如 v5.6.4 或 5.6.4
        if (preg_match('/v?(\d+)\.(\d+)\.(\d+)(?:\.(\d+))?/i', $versionStr, $matches)) {
            $result['first'] = $matches[1] ?? '5';
            $result['second'] = $matches[2] ?? '5';
            $result['third'] = $matches[3] ?? '0';
            $result['fourth'] = $matches[4] ?? '0';
        }

        return $result;
    }
}
