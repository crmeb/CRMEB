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

namespace app\adminapi\controller;

use app\Request;
use app\services\activity\coupon\StoreCouponIssueServices;
use app\services\activity\coupon\StoreCouponProductServices;
use app\services\order\StoreOrderCartInfoServices;
use app\services\order\StoreOrderCreateServices;
use app\services\order\StoreOrderRefundServices;
use app\services\order\StoreOrderServices;
use app\services\system\UpgradeServices;
use app\services\user\UserBillServices;
use app\services\user\UserBrokerageServices;
use app\services\user\UserMoneyServices;
use app\services\user\UserBrokerageFrozenServices;
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
     * @param Request $request
     * @return \think\response\View
     */
    public function index(Request $request)
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
//            $limit = 100;
//            if (!$this->setIsUpgrade('money')) {
//                $res = $this->handleMoney((int)$sleep, (int)$page, (int)$limit);
//                return app('json')->success($res);
//            } elseif (!$this->setIsUpgrade('brokerage')) {
//                $res = $this->handleBrokerage((int)$sleep, (int)$page, (int)$limit);
//                return app('json')->success($res);
//            } elseif (!$this->setIsUpgrade('orderRefund')) {
//                $res = $this->handleOrderRefund((int)$sleep, (int)$page, (int)$limit);
//                return app('json')->success($res);
//            } else {
//                file_put_contents(app()->getRootPath() . '.version', "version=" . $data['new_version'] . "\nversion_code=" . $data['new_code']);
//                return app('json')->success(['sleep' => -1]);
//            }
//            $limit = 100;
//            if (!$this->setIsUpgrade('cartInfo')) {
//                $res = $this->handleCartInfo((int)$sleep, (int)$page, (int)$limit);
//                return app('json')->success($res);
//            } else {
//                file_put_contents(app()->getRootPath() . '.version', "version=" . $data['new_version'] . "\nversion_code=" . $data['new_code'] . "\napp_id=ze7x9rxsv09l6pvsyo" . "\napp_key=fuF7U9zaybLa5gageVQzxtxQMFnvU2OI");
//                $this->services->generateSignature();
//                return app('json')->success(['sleep' => -1]);
//            }
//            $limit = 100;
//            if (!$this->setIsUpgrade('coupon')) {
//                $res = $this->handleCoupon((int)$sleep, (int)$page, (int)$limit);
//                return app('json')->success($res);
//            } else {
//                $this->setEnv();
//                file_put_contents(app()->getRootPath() . '.version', "version=" . $data['new_version'] . "\nversion_code=" . $data['new_code'] . "\nplatform=CRMEB\napp_id=ze7x9rxsv09l6pvsyo" . "\napp_key=fuF7U9zaybLa5gageVQzxtxQMFnvU2OI");
//                $this->services->generateSignature();
//                return app('json')->success(['sleep' => -1]);
//            }
            file_put_contents(app()->getRootPath() . '.version', "version=" . $data['new_version'] . "\nversion_code=" . $data['new_code'] . "\nplatform=CRMEB\napp_id=ze7x9rxsv09l6pvsyo" . "\napp_key=fuF7U9zaybLa5gageVQzxtxQMFnvU2OI");
            $this->services->generateSignature();
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
        $unique = uniqid();
        //读取配置文件，并替换真实配置数据1
        $strConfig = file_get_contents(root_path() . 'public/install/.env');
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
     * 更新分类券
     * @param int $sleep
     * @param int $page
     * @param int $limit
     * @return array
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/03/04
     */
    public function handleCoupon(int $sleep = 1, int $page = 1, int $limit = 100)
    {
        $list = app()->make(StoreCouponIssueServices::class)->selectList([['category_id', '>', 0]], 'id,category_id', $page, $limit)->toArray();
        if (count($list)) {
            $allData = [];
            foreach ($list as $item) {
                $data = [
                    'coupon_id' => $item['id'],
                    'product_id' => 0,
                    'category_id' => $item['category_id']
                ];
                $allData[] = $data;
            }
            if ($allData) {
                app()->make(StoreCouponProductServices::class)->saveAll($allData);
            }
            $info['table'] = 'store_coupon_product';
            $info['status'] = 1;
            $info['error'] = '分类券数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = $page + 1;
            $info['add_time'] = date('Y-m-d H:i:s', time());
            return $info;
        } else {
            $this->setIsUpgrade('coupon', 1);
            $info['table'] = 'store_coupon_product';
            $info['status'] = 1;
            $info['error'] = '分类券数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = 1;
            $info['add_time'] = date('Y-m-d H:i:s', time());
            return $info;
        }
    }

    /**
     * 处理历史余额数据
     * @param int $sleep
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function handleMoney(int $sleep = 1, int $page = 1, int $limit = 100)
    {
        /** @var UserBillServices $userBillServics */
        $userBillServics = app()->make(UserBillServices::class);
        $where = ['category' => 'now_money', 'type' => ['pay_product', 'pay_product_refund', 'system_add', 'system_sub', 'recharge', 'lottery_use', 'lottery_add']];
        $list = $userBillServics->getList($where, '*', $page, $limit, [], 'id asc');
        if ($list) {
            $allData = $data = [];
            foreach ($list as $item) {
                $data = [
                    'uid' => $item['uid'],
                    'link_id' => $item['link_id'],
                    'pm' => $item['pm'],
                    'title' => $item['title'],
                    'type' => $item['type'],
                    'number' => $item['number'],
                    'balance' => $item['balance'],
                    'mark' => $item['mark'],
                    'add_time' => strtotime($item['add_time']),
                ];
                $allData[] = $data;
            }
            if ($allData) {
                /** @var UserMoneyServices $userMoneyServices */
                $userMoneyServices = app()->make(UserMoneyServices::class);
                $userMoneyServices->saveAll($allData);
            }
            $info['table'] = 'user_money';
            $info['status'] = 1;
            $info['error'] = '余额数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = $page + 1;
            $info['add_time'] = date('Y-m-d H:i:s', time());
            return $info;
        } else {
            $this->setIsUpgrade('money', 1);
            $info['table'] = 'user_money';
            $info['status'] = 1;
            $info['error'] = '余额数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = 1;
            $info['add_time'] = date('Y-m-d H:i:s', time());
            return $info;
        }
    }

    /**
     * 处理历史佣金数据
     * @param int $sleep
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function handleBrokerage(int $sleep = 1, int $page = 1, int $limit = 100)
    {
        /** @var UserBillServices $userBillServics */
        $userBillServics = app()->make(UserBillServices::class);
        $where = ['category' => ['', 'now_money'], 'type' => ['brokerage', 'brokerage_user', 'extract', 'refund', 'extract_fail']];
        $list = $userBillServics->getList($where, '*', $page, $limit, [], 'id asc');
        if ($list) {
            $allData = $data = [];
            /** @var  $brokerageFrozenServices */
            $brokerageFrozenServices = app()->make(UserBrokerageFrozenServices::class);
            $frozenList = $brokerageFrozenServices->getColumn([['uill_id', 'in', array_column($list, 'id')], ['frozen_time', '>', time()]], 'uill_id,frozen_time', 'uill_id');
            foreach ($list as $item) {
                if (in_array($item['type'], ['brokerage_user', 'extract', 'refund', 'extract_fail'])) {
                    $type = $item['type'];
                } else {
                    if (strpos($item['mark'], '二级')) {
                        $type = 'two_brokerage';
                    } else {
                        $type = 'one_brokerage';
                    }
                }
                $data = [
                    'uid' => $item['uid'],
                    'link_id' => $item['link_id'],
                    'pm' => $item['pm'],
                    'title' => $item['title'],
                    'type' => $type,
                    'number' => $item['number'],
                    'balance' => $item['balance'],
                    'mark' => $item['mark'],
                    'frozen_time' => $frozenList[$item['id']]['frozen_time'] ?? 0,
                    'add_time' => strtotime($item['add_time']),
                ];
                $allData[] = $data;
            }
            if ($allData) {
                /** @var UserBrokerageServices $userBrokerageServices */
                $userBrokerageServices = app()->make(UserBrokerageServices::class);
                $userBrokerageServices->saveAll($allData);
            }
            $info['table'] = 'user_brokerage';
            $info['status'] = 1;
            $info['error'] = '佣金数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = $page + 1;
            $info['add_time'] = date('Y-m-d H:i:s', time());
            return $info;
        } else {
            $this->setIsUpgrade('brokerage', 1);
            $info['table'] = 'user_brokerage';
            $info['status'] = 1;
            $info['error'] = '佣金数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = 1;
            $info['add_time'] = date('Y-m-d H:i:s', time());
            return $info;
        }
    }

    /**
     * 处理历史退款数据
     * @param int $sleep
     * @param int $page
     * @param int $limit
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function handleOrderRefund(int $sleep = 1, int $page = 1, int $limit = 100)
    {
        /** @var StoreOrderServices $storeOrderServices */
        $storeOrderServices = app()->make(StoreOrderServices::class);
        $list = $storeOrderServices->getSplitOrderList(['refund_status' => [1, 2], ['refund_type' => [1, 2, 4, 5, 6]]], ['*'], [], $page, $limit, 'id asc');
        $allData = $refundOrderData = [];
        if ($list) {
            /** @var StoreOrderCreateServices $storeOrderCreateServices */
            $storeOrderCreateServices = app()->make(StoreOrderCreateServices::class);
            /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
            $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
            $time = time();
            foreach ($list as $order) {
                //生成退款订单
                $refundOrderData['uid'] = $order['uid'];
                $refundOrderData['store_id'] = $order['store_id'];
                $refundOrderData['store_order_id'] = $order['id'];
                $refundOrderData['order_id'] = $storeOrderCreateServices->getNewOrderId('');
                $refundOrderData['refund_num'] = $order['total_num'];
                $refundOrderData['refund_type'] = $order['refund_type'];
                $refundOrderData['refund_price'] = $order['pay_price'];
                $refundOrderData['refunded_price'] = 0;
                $refundOrderData['refund_explain'] = $order['refund_reason_wap_explain'];
                $refundOrderData['refund_img'] = $order['refund_reason_wap_img'];
                $refundOrderData['refund_reason'] = $order['refund_reason_wap'];
                $refundOrderData['refund_express'] = $order['refund_express'];
                $refundOrderData['refunded_time'] = $order['refund_type'] == 6 ? $order['refund_reason_time'] : 0;
                $refundOrderData['add_time'] = $order['refund_reason_time'];
                $cartInfos = $storeOrderCartInfoServices->getCartColunm(['oid' => $order['id']], 'id,cart_id,cart_num,cart_info');
                foreach ($cartInfos as &$cartInfo) {
                    $cartInfo['cart_info'] = is_string($cartInfo['cart_info']) ? json_decode($cartInfo['cart_info'], true) : $cartInfo['cart_info'];
                }
                $refundOrderData['cart_info'] = json_encode(array_column($cartInfos, 'cart_info'));
                $allData[] = $refundOrderData;
            }
            if ($allData) {
                /** @var StoreOrderRefundServices $storeOrderRefundServices */
                $storeOrderRefundServices = app()->make(StoreOrderRefundServices::class);
                $storeOrderRefundServices->saveAll($allData);
            }
            $info['table'] = 'store_order_refund';
            $info['status'] = 1;
            $info['error'] = '退款数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = $page + 1;
            $info['add_time'] = date('Y-m-d H:i:s', time());
            return $info;
        } else {
            $this->setIsUpgrade('orderRefund', 1);
            $info['table'] = 'store_order_refund';
            $info['status'] = 1;
            $info['error'] = '退款数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = 1;
            $info['add_time'] = date('Y-m-d H:i:s', time());
            return $info;
        }
    }

    /**
     * 更新订单商品表
     * @param int $sleep
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function handleCartInfo(int $sleep = 1, int $page = 1, int $limit = 100)
    {
        /** @var StoreOrderCartInfoServices $storeOrderCartInfoServices */
        $storeOrderCartInfoServices = app()->make(StoreOrderCartInfoServices::class);
        $list = $storeOrderCartInfoServices->selectList(['uid' => 0], 'id,oid', $page, $limit)->toArray();
        $allData = $cartData = [];
        if ($list) {
            /** @var StoreOrderServices $storeOrderServices */
            $storeOrderServices = app()->make(StoreOrderServices::class);
            $uids = $storeOrderServices->getColumn([['id', 'in', array_column($list, 'oid')]], 'uid', 'id');
            foreach ($list as $cart) {
                $cartData['id'] = $cart['id'];
                $cartData['uid'] = $uids[$cart['oid']] ?? 0;
                $allData[] = $cartData;
            }
            if ($allData) {
                $storeOrderCartInfoServices->saveAll($allData);
            }
            $info['table'] = 'store_order_cart_info';
            $info['status'] = 1;
            $info['error'] = '订单商品数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = $page + 1;
        } else {
            $this->setIsUpgrade('cartInfo', 1);
            $info['table'] = 'store_order_cart_info';
            $info['status'] = 1;
            $info['error'] = '订单商品数据更新成功';
            $info['sleep'] = $sleep + 1;
            $info['page'] = 1;
        }
        $info['add_time'] = date('Y-m-d H:i:s', time());
        return $info;
    }


    /**
     * 升级数据
     * @return mixed
     */
    public function upData()
    {
        $data['new_version'] = 'CRMEB-BZ v5.3.0';
        $data['new_code'] = 530;
        $data['update_sql'] = [
            [
                'code' => 530,
                'type' => -1,
                'table' => "system_config_tab",
                'sql' => "truncate table `@table`"
            ],
            [
                'code' => 530,
                'type' => 3,
                'table' => "system_config_tab",
                'field' => "menus_id",
                'findSql' => "show columns from `@table` like 'menus_id'",
                'sql' => "ALTER TABLE `@table` ADD `menus_id` int(11) NOT NULL DEFAULT '0' COMMENT '菜单ID'"
            ],
            [
                'code' => 530,
                'type' => -1,
                'table' => "system_config_tab",
                'sql' => <<<SQL
INSERT INTO `@table` (`id`, `pid`, `title`, `eng_title`, `status`, `info`, `icon`, `type`, `sort`, `menus_id`) VALUES
(1, 129, '基础配置', 'basics', 1, 0, 'ios-settings', 0, 0, 23),
(2, 78, '公众号配置(H5)', 'wechat', 1, 0, 'ios-chatbubbles', 3, 0, 1006),
(4, 23, '微信支付配置', 'pay', 1, 0, 'ios-chatbubbles', 3, 0, 1063),
(7, 78, '小程序配置', 'routine', 1, 0, 'logo-android', 3, 0, 1007),
(9, 0, '分销配置', 'fenxiao', 1, 0, 'md-contacts', 3, 0, 28),
(11, 100, '用户积分配置', 'point', 0, 0, 'logo-euro', 3, 0, 3423),
(18, 65, '一号通', 'system_sms', 1, 0, 'ios-chatboxes', 3, 99, 3418),
(21, 65, '小票打印配置', 'printing_deploy', 1, 0, 'logo-buffer', 3, 0, 1057),
(23, 65, '商城支付配置', 'pay_config', 1, 0, 'logo-usd', 3, 70, 1063),
(28, 100, '用户充值配置', 'recharge_site', 0, 0, '', 3, 2, 3423),
(31, 79, '基础配置', 'base_config', 0, 0, '', 0, 0, 1012),
(41, 65, '采集商品配置', 'copy_product', 1, 0, '', 3, 0, 1058),
(45, 100, '用户等级配置', 'store_member', 1, 0, '', 3, 3, 3423),
(50, 113, '发票功能配置', 'store_invoice', 1, 0, '', 3, 0, 3424),
(63, 23, '支付宝支付配置', 'ali_pay', 1, 0, '', 3, 0, 1063),
(64, 65, '物流查询配置', 'logistics_select', 1, 0, '', 3, 0, 1059),
(65, 0, '接口设置', 'system_serve', 1, 0, 'md-briefcase', 3, 0, 1056),
(66, 65, '电子面单配置', 'electronic_sheet', 1, 0, '', 3, 0, 1060),
(67, 100, '付费会员配置', 'member_card', 0, 0, '', 3, 2, 3423),
(69, 0, '客服配置', 'kefu_config', 1, 0, '', 3, 0, 3421),
(70, 129, '分享配置', 'share_index_config', 1, 0, '', 0, 0, 23),
(71, 113, '售后退款配置', 'refund_config', 1, 0, '', 3, 0, 3424),
(72, 9, '分销模式', 'brokerage_type', 1, 0, '', 3, 0, 28),
(73, 9, '返佣设置', 'brokerage_set', 1, 0, '', 3, 0, 28),
(74, 9, '提现设置', 'extract_set', 1, 0, '', 3, 0, 28),
(75, 78, 'PC站点配置', 'system_pc', 1, 0, '', 3, 0, 1010),
(77, 78, 'APP配置', 'app', 1, 0, '', 3, 0, 1011),
(78, 0, '应用配置', 'sys_app', 1, 0, '', 3, 0, 135),
(79, 65, '系统存储配置', 'storage_config', 1, 0, '', 3, 0, 1012),
(80, 79, '七牛云配置', 'qiniu_config', 0, 0, '', 0, 0, 1012),
(81, 79, '阿里云配置', 'oss_config', 0, 0, '', 0, 0, 1012),
(82, 79, '腾讯云配置', 'cos_config', 0, 0, '', 0, 0, 1012),
(86, 21, '基础配置', 'print_basic', 1, 0, '', 3, 0, 1057),
(87, 21, '易联云配置', 'yly_config', 1, 0, '', 3, 0, 1057),
(89, 41, '基础配置', 'copy_basic', 1, 0, '', 3, 0, 1058),
(90, 41, '99api配置', '99api_config', 1, 0, '', 3, 0, 1058),
(91, 64, '基础配置', 'logistics_basic', 1, 0, '', 3, 0, 1059),
(92, 64, '阿里云配置', 'logistics_aliyun', 1, 0, '', 3, 0, 1059),
(93, 66, '基础配置', 'electronic_basic', 1, 0, '', 3, 0, 1060),
(94, 66, '一号通配置', 'system_electronic_config', 1, 0, '', 3, 0, 1060),
(96, 65, '短信接口配置', 'sms_config', 1, 0, '', 3, 0, 1062),
(97, 96, '基础配置', 'sms_config_basic', 1, 0, '', 3, 0, 1062),
(98, 96, '阿里云配置', 'sms_aliyun', 1, 0, '', 3, 0, 1062),
(99, 96, '腾讯云配置', 'tencent_sms', 1, 0, '', 3, 0, 1062),
(100, 0, '用户配置', 'system_user_config', 1, 0, 'md-contact', 3, 0, 3423),
(102, 65, '对外接口配置', 'out_config', 1, 0, '', 3, 0, 0),
(103, 102, '基础配置', 'out_basic', 1, 0, '', 3, 0, 0),
(104, 102, '推送配置', 'out_push', 1, 0, '', 3, 0, 0),
(105, 100, '新用户设置', 'new_user_setting', 1, 0, '', 3, 0, 3423),
(106, 129, '翻译配置', 'online_translation', 0, 0, '', 3, 0, 23),
(107, 21, '飞鹅云配置', 'fey_config', 1, 0, '', 3, 0, 1057),
(108, 23, '通联支付', 'allinpay', 1, 0, '', 3, 0, 1063),
(109, 23, '基础配置', 'pay_basic', 1, 0, '', 3, 100, 1063),
(110, 79, '京东云配置', 'jd_oss_config', 0, 0, '', 0, 0, 1012),
(111, 79, '华为云配置', 'obs_config', 0, 0, '', 0, 0, 1012),
(112, 79, '天翼云配置', 'ty_oss_config', 0, 0, '', 0, 0, 1012),
(113, 0, '订单配置', 'order_config', 1, 0, '', 3, 0, 3424),
(114, 113, '包邮设置', 'free_shipping_config', 1, 0, '', 3, 10, 3424),
(115, 113, '订单取消配置', 'order_cancel_config', 1, 0, '', 3, 0, 3424),
(116, 113, '自动收货配置', 'auto_take_config', 1, 0, '', 3, 0, 3424),
(117, 113, '自动评价配置', 'auto_reviews_config', 1, 0, '', 3, 0, 3424),
(119, 113, '到店自提配置', 'self_mention_config', 1, 0, '', 3, 0, 3424),
(120, 113, '警戒库存配置', 'store_stock_config', 1, 0, '', 3, 0, 3424),
(122, 129, 'LOGO配置', 'site_logo_config', 1, 0, '', 0, 0, 23),
(123, 129, '统计配置', 'statistics_config', 1, 0, '', 0, 0, 23),
(124, 129, '地图配置', 'map_config', 1, 0, '', 0, 0, 23),
(125, 129, '备案配置', 'beian_config', 1, 0, '', 0, 0, 23),
(126, 100, '用户签到配置', 'user_sign_config', 0, 0, '', 3, 0, 3423),
(129, 0, '系统配置', 'system_config', 1, 0, '', 0, 0, 23),
(130, 2, '公众号配置', 'wechat_config', 1, 0, '', 3, 0, 1006),
(131, 2, '服务器域名配置', 'wechat_encoding', 1, 0, '', 3, 0, 1006),
(132, 7, '小程序配置', 'routine_config', 1, 0, '', 3, 0, 1007),
(133, 7, '消息推送配置', 'routine_encoding', 1, 0, '', 3, 0, 1007),
(134, 129, '模块配置', 'model_config', 1, 0, '', 0, 0, 23);
SQL
            ],
            [
                'code' => 530,
                'type' => -1,
                'table' => "system_config",
                'sql' => "UPDATE `@table` SET `parameter` = '0=>银行卡\n1=>微信\n2=>支付宝\n3=>余额' WHERE `menu_name` = 'store_free_postage'"
            ],
            [
                'code' => 530,
                'type' => -1,
                'table' => "system_config",
                'sql' => "DELETE FROM `@table` WHERE `id` = 459"
            ],
            [
                'code' => 530,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'model_checkbox'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='model_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'model_checkbox', 'checkbox', 'input', @tabId, 'seckill=>秒杀\nbargain=>砍价\ncombination=>拼团', 1, '', 0, 0, '[\"seckill\",\"bargain\",\"combination\"]', '模块配置', '模块配置，选中展示对应的模块，取消选中则前后端不展示模块相关内容', 0, 1)"
            ],
            [
                'code' => 530,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'sp_appid'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'sp_appid', 'text', 'input', @tabId, '', 1, '', 100, 0, '\"\"', '主商户APPID', '开启服务商支付，需要配置主商户申请的时候开通的公众号服务号的APPID', 0, 1)"
            ],
            [
                'code' => 530,
                'type' => 3,
                'table' => "system_notification",
                'field' => "wechat_data",
                'findSql' => "show columns from `@table` like 'wechat_data'",
                'sql' => "ALTER TABLE `@table` ADD `wechat_data` varchar(255) NOT NULL DEFAULT '' COMMENT '模版消息参数' AFTER `wechat_tempid`"
            ],
            [
                'code' => 530,
                'type' => 3,
                'table' => "system_notification",
                'field' => "wechat_link",
                'findSql' => "show columns from `@table` like 'wechat_link'",
                'sql' => "ALTER TABLE `@table` ADD `wechat_link` varchar(255) NOT NULL DEFAULT '' COMMENT '模版消息链接' AFTER `wechat_data`"
            ],
            [
                'code' => 530,
                'type' => 3,
                'table' => "system_notification",
                'field' => "wechat_to_routine",
                'findSql' => "show columns from `@table` like 'wechat_to_routine'",
                'sql' => "ALTER TABLE `@table` ADD `wechat_to_routine` int(1) NOT NULL DEFAULT '0' COMMENT '模版消息跳转小程序' AFTER `wechat_link`"
            ],
            [
                'code' => 530,
                'type' => 3,
                'table' => "system_notification",
                'field' => "routine_data",
                'findSql' => "show columns from `@table` like 'routine_data'",
                'sql' => "ALTER TABLE `@table` ADD `routine_data` varchar(255) NOT NULL DEFAULT '' COMMENT '订阅消息参数' AFTER `routine_tempid`"
            ],
            [
                'code' => 530,
                'type' => 3,
                'table' => "system_notification",
                'field' => "routine_link",
                'findSql' => "show columns from `@table` like 'routine_link'",
                'sql' => "ALTER TABLE `@table` ADD `routine_link` varchar(255) NOT NULL DEFAULT '' COMMENT '订阅消息链接' AFTER `routine_data`"
            ],
            [
                'code' => 530,
                'type' => 3,
                'table' => "system_notification",
                'field' => "custom_trigger",
                'findSql' => "show columns from `@table` like 'custom_trigger'",
                'sql' => "ALTER TABLE `@table` ADD `custom_trigger` varchar(255) NOT NULL DEFAULT '' COMMENT '自定义消息触发位置' AFTER `add_time`"
            ],
            [
                'code' => 530,
                'type' => 3,
                'table' => "system_notification",
                'field' => "custom_variable",
                'findSql' => "show columns from `@table` like 'custom_variable'",
                'sql' => "ALTER TABLE `@table` ADD `custom_variable` varchar(1000) NOT NULL DEFAULT '' COMMENT '自定义消息变量' AFTER `custom_trigger`"
            ],
            [
                'code' => 530,
                'type' => -1,
                'table' => "system_menus",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 993, '', '小程序链接', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/app/routine/link', '135/993', 1, '', 0, '', 0, '')"
            ],
            [
                'code' => 530,
                'type' => -1,
                'table' => "system_menus",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 56, '', '模块配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/integral/system_config/2/134', '25/56', 1, '', 0, 'system-model-system_config', 0, '')"
            ],
            [
                'code' => 530,
                'type' => 1,
                'table' => "routine_scheme",
                'findSql' => "select * from information_schema.tables where table_name ='@table'",
                'sql' => "CREATE TABLE IF NOT EXISTS `@table` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '小程序页面地址',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '生成链接地址',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `expire_type` int(11) NOT NULL DEFAULT '-1' COMMENT '到期类型',
  `expire_interval` int(11) NOT NULL DEFAULT '0' COMMENT '到期天数',
  `expire_time` int(11) NOT NULL DEFAULT '0' COMMENT '到期时间',
  `is_del` int(11) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='小程序外链'"
            ],
        ];
        return $data;
    }

    /**
     * 升级列表
     * @return mixed
     */
    public function upgradeList()
    {
        return app('json')->success([]);
        return app('json')->success($this->services->getUpgradeList());
    }

    /**
     * 可升级列表
     * @return mixed
     */
    public function upgradeableList()
    {
        return app('json')->success([]);
        return app('json')->success($this->services->getUpgradeableList());
    }

    /**
     * 可升级列表
     * @return mixed
     */
    public function agreement()
    {
        return app('json')->success($this->services->getAgreement());
    }

    /**
     * 下载升级包
     * @param $packageKey
     * @return mixed
     */
    public function download($packageKey)
    {
        if (empty($packageKey)) {
            return app('json')->fail(100100);
        }

        $this->services->packageDownload($packageKey);
        return app('json')->success();
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
     * 获取升级状态
     * @return mixed
     */
    public function upgradeStatus()
    {
        return app('json')->success([]);
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
        return app('json')->success([]);
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
            return app('json')->fail(100026);
        }
        return app('json')->success($this->services->export((int)$id, $type));
    }
}
