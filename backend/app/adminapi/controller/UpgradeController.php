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

namespace app\adminapi\controller;

use app\Request;
use app\services\system\log\SystemFileMd5Services;
use app\services\system\UpgradeServices;
use think\facade\Db;
use think\facade\Env;


class UpgradeController
{
    /**
     * @var UpgradeServices
     */
    private $services;

    /**
     * UpgradeController constructor.
     * @param UpgradeServices $services
     */
    public function __construct(UpgradeServices $services)
    {
        $this->services = $services;
    }

    /**
     * 升级程序页面
     * @return \think\response\View
     */
    public function index()
    {
        $data = $this->upData();
        $Title = "CRMEB升级程序";
        $Powered = "Powered by CRMEB";

        //获取当前版本号
        $version_now = $this->getversion('.version')['version'];
        $version_new = $data['new_version'];
        $isUpgrade = true;
        $executeIng = false;

        return view('/upgrade/step1', [
            'title' => $Title,
            'powered' => $Powered,
            'version_now' => $version_now,
            'version_new' => $version_new,
            'isUpgrade' => json_encode($isUpgrade),
            'executeIng' => json_encode($executeIng),
            'next' => 1,
            'action' => 'upgrade'
        ]);
    }

    /**
     * 获取当前版本号
     * @return array
     */
    public function getversion($str)
    {
        $version_arr = [];
        $curent_version = @file(app()->getRootPath() . $str);

        foreach ($curent_version as $val) {
            list($k, $v) = explode('=', $val);
            $version_arr[$k] = $v;
        }
        return $version_arr;
    }

    /**
     * 写入升级过程
     * @param string $field
     * @param int $n
     * @return bool
     */
    public function setIsUpgrade(string $field, int $n = 0)
    {
        $upgrade = parse_ini_file(public_path('upgrade') . '.upgrade');
        if ($n) {
            if (!is_array($upgrade)) {
                $upgrade = [];
            }
            $string = '';
            foreach ($upgrade as $key => $item) {
                $string .= $key . '=' . $item . "\r\n";
            }
            $string .= $field . '=' . $n . "\r\n";
            file_put_contents(public_path('upgrade') . '.upgrade', $string);
            return true;
        } else {
            if (!is_array($upgrade)) {
                return false;
            }
            return isset($upgrade[$field]);
        }
    }

    public function upgrade(Request $request)
    {
        list($sleep, $page, $prefix) = $request->getMore([
            ['sleep', 0],
            ['page', 1],
            ['prefix', 'eb_'],
        ], true);
        $data = $this->upData();
        $code_now = $this->getversion('.version')['version_code'];
        if ($data['new_code'] == $code_now) {
            return app('json')->success(['sleep' => -1]);
        }
        $sql_arr = [];
        foreach ($data['update_sql'] as $items) {
            if ($items['code'] > $code_now) {
                $sql_arr[] = $items;
            }
        }
        //sql 执行完成，开始执行修改数据
        if (!isset($sql_arr[$sleep])) {
            file_put_contents(app()->getRootPath() . '.version', "version=" . $data['new_version'] . "\nversion_code=" . $data['new_code'] . "\nplatform=CRMEB\napp_id=ze7x9rxsv09l6pvsyo" . "\napp_key=fuF7U9zaybLa5gageVQzxtxQMFnvU2OI");
            return app('json')->success(['sleep' => -1]);
        }
        $sql = $sql_arr[$sleep];
        Db::startTrans();
        try {
            if ($sql['type'] == 1) {
                if (isset($sql['findSql']) && $sql['findSql']) {
                    $table = $prefix . $sql['table'];
                    $findSql = str_replace('@table', $table, $sql['findSql']);
                    if (!empty(Db::query($findSql))) {
                        $item['table'] = $table;
                        $item['status'] = 1;
                        $item['error'] = $table . '表已存在';
                        $item['sleep'] = $sleep + 1;
                        $item['add_time'] = date('Y-m-d H:i:s', time());
                        Db::commit();
                        return app('json')->success($item);
                    }
                }
                if (isset($sql['sql']) && $sql['sql']) {
                    $upSql = $sql['sql'];
                    $upSql = str_replace('@table', $table, $upSql);
                    Db::execute($upSql);
                    $item['table'] = $table;
                    $item['status'] = 1;
                    $item['error'] = $table . '表添加成功';
                    $item['sleep'] = $sleep + 1;
                    $item['add_time'] = date('Y-m-d H:i:s', time());
                    Db::commit();
                    return app('json')->success($item);
                }
            } elseif ($sql['type'] == 2) {
                if (isset($sql['findSql']) && $sql['findSql']) {
                    $table = $prefix . $sql['table'];
                    $findSql = str_replace('@table', $table, $sql['findSql']);
                    if (empty(Db::query($findSql))) {
                        $item['table'] = $table;
                        $item['status'] = 1;
                        $item['error'] = $table . '表不存在';
                        $item['sleep'] = $sleep + 1;
                        $item['add_time'] = date('Y-m-d H:i:s', time());
                        Db::commit();
                        return app('json')->success($item);
                    }
                }
                if (isset($sql['sql']) && $sql['sql']) {
                    $upSql = $sql['sql'];
                    $upSql = str_replace('@table', $table, $upSql);
                    Db::execute($upSql);
                    $item['table'] = $table;
                    $item['status'] = 1;
                    $item['error'] = $table . '表删除成功';
                    $item['sleep'] = $sleep + 1;
                    $item['add_time'] = date('Y-m-d H:i:s', time());
                    Db::commit();
                    return app('json')->success($item);
                }
            } elseif ($sql['type'] == 3) {
                if (isset($sql['findSql']) && $sql['findSql']) {
                    $table = $prefix . $sql['table'];
                    $findSql = str_replace('@table', $table, $sql['findSql']);
                    if (!empty(Db::query($findSql))) {
                        $item['table'] = $table;
                        $item['status'] = 1;
                        $item['error'] = $table . '表中' . $sql['field'] . '已存在';
                        $item['sleep'] = $sleep + 1;
                        $item['add_time'] = date('Y-m-d H:i:s', time());
                        Db::commit();
                        return app('json')->success($item);
                    }
                }
                if (isset($sql['sql']) && $sql['sql']) {
                    $upSql = $sql['sql'];
                    $upSql = str_replace('@table', $table, $upSql);
                    Db::execute($upSql);
                    $item['table'] = $table;
                    $item['status'] = 1;
                    $item['error'] = $table . '表中' . $sql['field'] . '字段添加成功';
                    $item['sleep'] = $sleep + 1;
                    $item['add_time'] = date('Y-m-d H:i:s', time());
                    Db::commit();
                    return app('json')->success($item);
                }
            } elseif ($sql['type'] == 4) {
                if (isset($sql['findSql']) && $sql['findSql']) {
                    $table = $prefix . $sql['table'];
                    $findSql = str_replace('@table', $table, $sql['findSql']);
                    if (empty(Db::query($findSql))) {
                        $item['table'] = $table;
                        $item['status'] = 1;
                        $item['error'] = $table . '表中' . $sql['field'] . '不存在';
                        $item['sleep'] = $sleep + 1;
                        $item['add_time'] = date('Y-m-d H:i:s', time());
                        Db::commit();
                        return app('json')->success($item);
                    }
                }
                if (isset($sql['sql']) && $sql['sql']) {
                    $upSql = $sql['sql'];
                    $upSql = str_replace('@table', $table, $upSql);
                    Db::execute($upSql);
                    $item['table'] = $table;
                    $item['status'] = 1;
                    $item['error'] = $table . '表中' . $sql['field'] . '字段修改成功';
                    $item['sleep'] = $sleep + 1;
                    $item['add_time'] = date('Y-m-d H:i:s', time());
                    Db::commit();
                    return app('json')->success($item);
                }
            } elseif ($sql['type'] == 5) {
                if (isset($sql['findSql']) && $sql['findSql']) {
                    $table = $prefix . $sql['table'];
                    $findSql = str_replace('@table', $table, $sql['findSql']);
                    if (empty(Db::query($findSql))) {
                        $item['table'] = $table;
                        $item['status'] = 1;
                        $item['error'] = $table . '表中' . $sql['field'] . '不存在';
                        $item['sleep'] = $sleep + 1;
                        $item['add_time'] = date('Y-m-d H:i:s', time());
                        Db::commit();
                        return app('json')->success($item);
                    }
                }
                if (isset($sql['sql']) && $sql['sql']) {
                    $upSql = $sql['sql'];
                    $upSql = str_replace('@table', $table, $upSql);
                    Db::execute($upSql);
                    $item['table'] = $table;
                    $item['status'] = 1;
                    $item['error'] = $table . '表中' . $sql['field'] . '字段删除成功';
                    $item['sleep'] = $sleep + 1;
                    $item['add_time'] = date('Y-m-d H:i:s', time());
                    Db::commit();
                    return app('json')->success($item);
                }
            } elseif ($sql['type'] == 6) {
                $table = $prefix . $sql['table'] ?? '';
                if (isset($sql['findSql']) && $sql['findSql']) {
                    $findSql = str_replace('@table', $table, $sql['findSql']);
                    if (!empty(Db::query($findSql))) {
                        $item['table'] = $prefix . $sql['table'];
                        $item['status'] = 1;
                        $item['error'] = $table . '表中此数据已存在';
                        $item['sleep'] = $sleep + 1;
                        $item['add_time'] = date('Y-m-d H:i:s', time());
                        Db::commit();
                        return app('json')->success($item);
                    }
                }
                if (isset($sql['sql']) && $sql['sql']) {
                    $upSql = $sql['sql'];
                    $upSql = str_replace('@table', $table, $upSql);
                    if (isset($sql['whereSql']) && $sql['whereSql']) {
                        $whereTable = $prefix . $sql['whereTable'] ?? '';
                        $whereSql = str_replace('@whereTable', $whereTable, $sql['whereSql']);
                        $tabId = Db::query($whereSql)[0]['tabId'] ?? 0;
                        if (!$tabId) {
                            $item['table'] = $whereTable;
                            $item['status'] = 1;
                            $item['error'] = '查询父类ID不存在';
                            $item['sleep'] = $sleep + 1;
                            $item['add_time'] = date('Y-m-d H:i:s', time());
                            Db::commit();
                            return app('json')->success($item);
                        }
                        $upSql = str_replace('@tabId', $tabId, $upSql);
                    }
                    if (Db::execute($upSql)) {
                        $item['table'] = $table;
                        $item['status'] = 1;
                        $item['error'] = '数据添加成功';
                        $item['sleep'] = $sleep + 1;
                        $item['add_time'] = date('Y-m-d H:i:s', time());
                        Db::commit();
                        return app('json')->success($item);
                    }
                }
            } elseif ($sql['type'] == 7) {
                $table = $prefix . $sql['table'] ?? '';
                if (isset($sql['findSql']) && $sql['findSql']) {
                    $findSql = str_replace('@table', $table, $sql['findSql']);
                    if (empty(Db::query($findSql))) {
                        $item['table'] = $prefix . $sql['table'];
                        $item['status'] = 1;
                        $item['error'] = $table . '表中此数据不存在';
                        $item['sleep'] = $sleep + 1;
                        $item['add_time'] = date('Y-m-d H:i:s', time());
                        Db::commit();
                        return app('json')->success($item);
                    }
                }
                if (isset($sql['sql']) && $sql['sql']) {
                    $upSql = $sql['sql'];
                    $upSql = str_replace('@table', $table, $upSql);
                    if (isset($sql['whereSql']) && $sql['whereSql']) {
                        $whereTable = $prefix . $sql['whereTable'] ?? '';
                        $whereSql = str_replace('@whereTable', $whereTable, $sql['whereSql']);
                        $tabId = Db::query($whereSql)[0]['tabId'] ?? 0;
                        if (!$tabId) {
                            $item['table'] = $whereTable;
                            $item['status'] = 1;
                            $item['error'] = '查询父类ID不存在';
                            $item['sleep'] = $sleep + 1;
                            $item['add_time'] = date('Y-m-d H:i:s', time());
                            Db::commit();
                            return app('json')->success($item);
                        }
                        $upSql = str_replace('@tabId', $tabId, $upSql);
                    }
                    if (Db::execute($upSql)) {
                        $item['table'] = $table;
                        $item['status'] = 1;
                        $item['error'] = '数据修改成功';
                        $item['sleep'] = $sleep + 1;
                        $item['add_time'] = date('Y-m-d H:i:s', time());
                        Db::commit();
                        return app('json')->success($item);
                    }
                }
            } elseif ($sql['type'] == 8) {
            } elseif ($sql['type'] == -1) {
                $table = $prefix . $sql['table'];
                if (isset($sql['sql']) && $sql['sql']) {
                    $upSql = $sql['sql'];
                    $upSql = str_replace('@table', $table, $upSql);
                    if (isset($sql['new_table']) && $sql['new_table']) {
                        $new_table = $prefix . $sql['new_table'];
                        $upSql = str_replace('@new_table', $new_table, $upSql);
                    }
                    Db::execute($upSql);
                    $item['table'] = $table;
                    $item['status'] = 1;
                    $item['error'] = $table . ' sql执行成功';
                    $item['sleep'] = $sleep + 1;
                    $item['add_time'] = date('Y-m-d H:i:s', time());
                    Db::commit();
                    return app('json')->success($item);
                }
            }
        } catch (\Throwable $e) {
            $item['table'] = $prefix . $sql['table'];
            $item['status'] = 0;
            $item['sleep'] = $sleep + 1;
            $item['add_time'] = date('Y-m-d H:i:s', time());
            $item['error'] = $e->getMessage();
            Db::rollBack();
            return app('json')->success($item);
        }
    }

    /**
     * 重写.env文件
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/04
     */
    public function setEnv()
    {
        $unique = uniqid('', true);
        //读取配置文件，并替换真实配置数据1
        $strConfig = file_get_contents(root_path() . 'public/install/.env');
        $strConfig = str_replace('#APP_KEY#', 'app_key_' . $unique, $strConfig);
        $strConfig = str_replace('#DB_HOST#', Env::get('DATABASE.HOSTNAME', ''), $strConfig);
        $strConfig = str_replace('#DB_NAME#', Env::get('DATABASE.DATABASE', ''), $strConfig);
        $strConfig = str_replace('#DB_USER#', Env::get('DATABASE.USERNAME', ''), $strConfig);
        $strConfig = str_replace('#DB_PWD#', Env::get('DATABASE.PASSWORD', ''), $strConfig);
        $strConfig = str_replace('#DB_PORT#', Env::get('DATABASE.HOSTPORT', ''), $strConfig);
        $strConfig = str_replace('#DB_PREFIX#', Env::get('DATABASE.PREFIX', ''), $strConfig);
        $strConfig = str_replace('#DB_CHARSET#', 'utf8', $strConfig);
        $strConfig = str_replace('#CACHE_TYPE#', 'redis', $strConfig);
        $strConfig = str_replace('#CACHE_PREFIX#', 'cache_' . $unique . ':', $strConfig);
        $strConfig = str_replace('#CACHE_TAG_PREFIX#', 'cache_tag_' . $unique . ':', $strConfig);
        $strConfig = str_replace('#RB_HOST#', Env::get('REDIS.REDIS_HOSTNAME', ''), $strConfig);
        $strConfig = str_replace('#RB_PORT#', Env::get('REDIS.PORT', ''), $strConfig);
        $strConfig = str_replace('#RB_PWD#', Env::get('REDIS.REDIS_PASSWORD', ''), $strConfig);
        $strConfig = str_replace('#RB_SELECT#', Env::get('REDIS.SELECT', ''), $strConfig);
        $strConfig = str_replace('#QUEUE_NAME#', $unique, $strConfig);
        @chmod(root_path() . '/.env', 0777); //数据库配置文件的地址
        @file_put_contents(root_path() . '/.env', $strConfig); //数据库配置文件的地址
    }

    /**
     * 升级数据
     * @return mixed
     */
    public function upData()
    {
        $data['new_version'] = 'CRMEB-BZ v6.0.1';
        $data['new_code'] = 601;
        $data['update_sql'] = [
            [
                'code' => 601,
                'type' => -1,
                'table' => "order_center",
                'sql' => "ALTER TABLE `@table` MODIFY COLUMN `trade_no` varchar(100) NOT NULL DEFAULT '' COMMENT '支付订单号';"
            ],
        ];
        return $data;
    }


    /**
     * 所有的版本列表
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/2/25
     */
    public function upgradeList()
    {
        return app('json')->success($this->services->getUpgradeList());
    }

    /**
     * 可升级的版本列表
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/2/25
     */
    public function upgradeableList()
    {
        return app('json')->success($this->services->getUpgradeableList());
    }

    /**
     * 升级协议
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/2/25
     */
    public function agreement()
    {
        return app('json')->success($this->services->getAgreement());
    }

    /**
     * 检查文件
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/2/25
     */
    public function checkFile()
    {
        $data = app()->make(SystemFileMd5Services::class)->checkFile();
        return app('json')->success($data);
    }

    /**
     * 重新执行
     * @param Request $request
     * @return \think\Response
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2026/2/27
     */
    public function reExecute(Request $request)
    {
        $type = $request->param('type', '');
        return app('json')->success($this->services->reExecute($type));
    }

    /**
     * 下载升级包
     * @param $packageKey
     * @return mixed
     */
    public function packageDownload($packageKey)
    {
        if (sys_config('queue_open', 0) != 1 || Env::get('cache.driver', 'file') != 'redis') {
            return app('json')->fail('请先开启消息队列');
        }
        if (empty($packageKey)) {
            return app('json')->fail('参数错误');
        }
        $this->services->packageDownload($packageKey);
        return app('json')->success('下载中...');
    }

    /**
     * 升级进度
     * @return mixed
     */
    public function progress()
    {
        $result = $this->services->getProgress();
        return app('json')->success($result);
    }

    /**
     * 获取下载进度
     * 返回下载包、备份、解压的进度状态
     * @return mixed
     */
    public function downloadProgress(Request $request)
    {
        $type = $request->param('type', '');
        $result = $this->services->getDownloadProgress($type);
        return app('json')->success($result);
    }

    /**
     * 获取升级状态
     * @return mixed
     */
    public function upgradeStatus()
    {
        $data = $this->services->getUpgradeStatus();
        return app('json')->success($data);
    }

    /**
     * 升级记录
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     */
    public function upgradeLogList()
    {
        $data = $this->services->getUpgradeLogList();
        return app('json')->success($data);
    }

    /**
     * 导出备份
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     */
    public function export($id, $type)
    {
        if (!$id || !$type) {
            return app('json')->fail('数据不存在');
        }
        return app('json')->success($this->services->export((int)$id, $type));
    }

    // ==================== 跨版本升级接口 ====================

    /**
     * 获取跨版本升级概览
     * 返回当前版本、最新版本、版本差距、待升级版本列表
     * @return mixed
     */
    public function crossVersionOverview()
    {
        $data = $this->services->getCrossVersionUpgradeOverview();
        return app('json')->success($data);
    }

    /**
     * 获取待升级版本列表
     * @return mixed
     */
    public function pendingVersions()
    {
        $data = $this->services->getPendingVersions();
        return app('json')->success($data);
    }

    /**
     * 获取所有待执行的升级SQL
     * @return mixed
     */
    public function pendingUpgradeSql()
    {
        $data = $this->services->getAllPendingUpgradeSql();
        return app('json')->success([
            'total' => count($data),
            'list' => $data
        ]);
    }

    /**
     * 执行跨版本升级(单步执行)
     * 前端可以循环调用此接口，每次执行一步SQL
     * @param Request $request
     * @return mixed
     */
    public function executeCrossVersionUpgrade(Request $request)
    {
        // 检查是否满足最低版本要求
        if (!$this->services->meetsMinVersionRequirement()) {
            $availability = $this->services->checkCrossVersionUpgradeAvailability();
            return app('json')->fail($availability['message']);
        }

        $step = $request->param('step', 0);
        $result = $this->services->executeCrossVersionUpgrade((int)$step);
        return app('json')->success($result);
    }

    /**
     * 一键执行全部跨版本升级
     * 一次性执行所有待升级版本的SQL
     * @return mixed
     */
    public function executeAllCrossVersionUpgrade()
    {
        // 检查是否满足最低版本要求
        if (!$this->services->meetsMinVersionRequirement()) {
            $availability = $this->services->checkCrossVersionUpgradeAvailability();
            return app('json')->fail($availability['message']);
        }

        $result = $this->services->executeAllCrossVersionUpgrade();
        return app('json')->success($result);
    }

    /**
     * 检查是否需要跨版本升级
     * @return mixed
     */
    public function checkCrossVersionUpgrade()
    {
        // 先检查是否满足最低版本要求
        $availability = $this->services->checkCrossVersionUpgradeAvailability();

        if (!$availability['available']) {
            return app('json')->success([
                'available' => false,
                'need_upgrade' => false,
                'version_gap' => 0,
                'message' => $availability['message'],
                'current_version' => $availability['current_version'],
                // 截取当前版本号，只保留主版本号（如：CRMEB-BZ v6.0.0截取为 v6.0.0）
                'current_version_main' => str_replace('CRMEB-BZ ', '', $availability['current_version']),
                'current_code' => $availability['current_code'],
                'min_version' => $availability['min_version'],
                'min_code' => $availability['min_code']
            ]);
        }

        $needUpgrade = $this->services->needCrossVersionUpgrade();
        $versionGap = $this->services->getVersionGap();
        return app('json')->success([
            'available' => true,
            'need_upgrade' => $needUpgrade,
            'version_gap' => $versionGap,
            'message' => $needUpgrade ? "有{$versionGap}个版本待升级" : '当前已是最新版本',
            'current_version' => $availability['current_version'],
            // 截取当前版本号，只保留主版本号（如：CRMEB-BZ v6.0.0截取为 v6.0.0）
            'current_version_main' => str_replace('CRMEB-BZ ', '', $availability['current_version']),
            'current_min_version' => $availability['current_version'],
            'current_code' => $availability['current_code'],
            'min_version' => $availability['min_version'],
            'min_code' => $availability['min_code']
        ]);
    }

    /**
     * 获取备份状态
     * @return mixed
     */
    public function backupStatus()
    {
        $data = $this->services->getBackupStatus();
        return app('json')->success($data);
    }

    /**
     * 获取升级进度
     * @return mixed
     */
    public function upgradeProgress()
    {
        $data = $this->services->getUpgradeProgressDetail();
        return app('json')->success($data);
    }

    // ==================== 版本回退接口 ====================

    /**
     * 获取可回退的版本列表
     * @return mixed
     */
    public function rollbackVersions()
    {
        $data = $this->services->getRollbackVersions();
        return app('json')->success($data);
    }

    /**
     * 执行版本回退
     * @param Request $request
     * @return mixed
     */
    public function executeRollback(Request $request)
    {
        $logId = $request->param('log_id', 0);
        if (!$logId) {
            return app('json')->fail('请选择要回退的版本');
        }

        $result = $this->services->rollbackToVersion((int)$logId);
        return app('json')->success($result);
    }
}
