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
        $data['new_version'] = 'CRMEB-BZ v5.2.1';
        $data['new_code'] = 521;
        $data['update_sql'] = [
            [
                'code' => 521,
                'type' => 3,
                'table' => "agent_level",
                'field' => "one_brokerage_percent",
                'findSql' => "show columns from `@table` like 'one_brokerage_percent'",
                'sql' => "ALTER TABLE `@table` ADD `one_brokerage_percent` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '一级分佣比例' AFTER `one_brokerage`"
            ],
            [
                'code' => 521,
                'type' => 3,
                'table' => "agent_level",
                'field' => "two_brokerage_percent",
                'findSql' => "show columns from `@table` like 'two_brokerage_percent'",
                'sql' => "ALTER TABLE `@table` ADD `two_brokerage_percent` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '二级分佣比例' AFTER `two_brokerage`"
            ],
            [
                'code' => 521,
                'type' => 3,
                'table' => "store_bargain",
                'field' => "is_commission",
                'findSql' => "show columns from `@table` like 'is_commission'",
                'sql' => "ALTER TABLE `@table` ADD `is_commission` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否返佣'"
            ],
            [
                'code' => 521,
                'type' => 3,
                'table' => "store_seckill",
                'field' => "is_commission",
                'findSql' => "show columns from `@table` like 'is_commission'",
                'sql' => "ALTER TABLE `@table` ADD `is_commission` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否返佣'"
            ],
            [
                'code' => 521,
                'type' => 6,
                'table' => "system_config_tab",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'wechat_config'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, @tabId, '公众号配置', 'wechat_config', 1, 0, '', 3, 0)"
            ],
            [
                'code' => 521,
                'type' => 6,
                'table' => "system_config_tab",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'wechat_encoding'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, @tabId, '服务器域名配置', 'wechat_encoding', 1, 0, '', 3, 0)"
            ],
            [
                'code' => 521,
                'type' => 6,
                'table' => "system_config_tab",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'routine_config'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, @tabId, '小程序配置', 'routine_config', 1, 0, '', 3, 0)"
            ],
            [
                'code' => 521,
                'type' => 6,
                'table' => "system_config_tab",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'routine_encoding'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, @tabId, '消息推送配置', 'routine_encoding', 1, 0, '', 3, 0)"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'wechat_appid'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'wechat_appid'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'wechat_appsecret'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'wechat_appsecret'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'wechat_token'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_encoding'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'wechat_token'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'wechat_encode'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_encoding'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'wechat_encode'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'wechat_encodingaeskey'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_encoding'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'wechat_encodingaeskey'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'wechat_qrcode'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'wechat_qrcode'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_appId'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'routine_appId'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_appsecret'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'routine_appsecret'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'api'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_encoding'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'api'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_name'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'routine_name'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_contact_type'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'routine_contact_type'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'weixin_ckeck_file'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'weixin_ckeck_file'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'create_wechat_user'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'create_wechat_user'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'get_avatar'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'get_avatar'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'share_qrcode'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'share_qrcode'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'order_shipping_open'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'order_shipping_open'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_auth_type'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'routine_auth_type'"
            ],
            [
                'code' => 521,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'wechat_template_to_miniprogram'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'wechat_template_to_miniprogram'"
            ],
            [
                'code' => 521,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'refund_time_available'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='refund_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'refund_time_available', 'text', 'input', @tabId, '', 1, '', 100, 0, '0', '售后期限', '订单收货之后，在多少天内可以进行退款，超出天数前端不显示退款按钮，设置0则永远显示', 0, 1)"
            ],
            [
                'code' => 521,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_api'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_encoding'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'routine_api', 'text', 'input', @tabId, '', 1, '', 100, 0, '\"\\/api\\/wechat\\/miniServe\"', '接口地址', '配置小程序消息推送使用的接口地址，直接复制输入框内容（此项系统生成，无法修改）', 0, 1)"
            ],
            [
                'code' => 521,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_token'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_encoding'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'routine_token', 'text', 'input', @tabId, '', 1, '', 100, 0, '\"\"', '小程序验证TOKEN', '小程序验证TOKEN', 0, 1)"
            ],
            [
                'code' => 521,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_encode'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_encoding'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'routine_encode', 'radio', 'input', @tabId, '0=>明文模式\n1=>兼容模式\n2=>安全模式', 1, '', 0, 0, '0', '消息加解密方式', '小程序消息推送中的消息加密方式，暂时仅支持明文模式', 0, 1)"
            ],
            [
                'code' => 521,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_encodingaeskey'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine_encoding'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'routine_encodingaeskey', 'text', 'input', @tabId, '', 1, '', 0, 0, '\"\"', 'EncodingAESKey', '消息加密密钥由43位字符组成，字符范围为A-Z,a-z,0-9', 0, 1)"
            ],
            [
                'code' => 521,
                'type' => -1,
                'table' => "system_menus",
                'sql' => "DELETE FROM `@table` WHERE `id` = 657"
            ],
            [
                'code' => 521,
                'type' => -1,
                'table' => "system_menus",
                'sql' => "INSERT INTO `@table` VALUES (657, 656, '', '首页装修', 'admin', '', '', '', '', '[]', 100, 1, 1, 1, '/setting/pages/devise/0', '12/656', 1, '', 0, 'admin-setting-pages-devise', 0, '页面设计')"
            ],
            [
                'code' => 521,
                'type' => -1,
                'table' => "system_menus",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 656, '', '商品分类', 'admin', '', '', '', '', '[]', 95, 1, 1, 1, '/setting/pages/cate_page/1', '656', 1, '', 0, '', 0, '')"
            ],
            [
                'code' => 521,
                'type' => -1,
                'table' => "system_menus",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 656, '', '个人中心', 'admin', '', '', '', '', '[]', 90, 1, 1, 1, '/setting/pages/user_page/2', '656', 1, '', 0, '', 0, '')"
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
        return app('json')->success($this->services->getUpgradeList());
    }

    /**
     * 可升级列表
     * @return mixed
     */
    public function upgradeableList()
    {
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
            return app('json')->fail(100026);
        }
        return app('json')->success($this->services->export((int)$id, $type));
    }
}
