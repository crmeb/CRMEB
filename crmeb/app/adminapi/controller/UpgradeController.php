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
        $data['new_version'] = 'CRMEB-BZ v5.2.0';
        $data['new_code'] = 520;
        $data['update_sql'] = [
            [
                'code' => 520,
                'type' => 3,
                'table' => "store_product",
                'field' => "min_qty",
                'findSql' => "show columns from `@table` like 'min_qty'",
                'sql' => "ALTER TABLE `@table` ADD `min_qty` int(11) NOT NULL DEFAULT '1' COMMENT '起购数量'"
            ],
            [
                'code' => 520,
                'type' => -1,
                'table' => "system_config_tab",
                'sql' => "truncate table `@table`"
            ],
            [
                'code' => 520,
                'type' => -1,
                'table' => "system_config_tab",
                'sql' => <<<SQL
INSERT INTO `@table` (`id`, `pid`, `title`, `eng_title`, `status`, `info`, `icon`, `type`, `sort`) VALUES
(1, 129, '基础配置', 'basics', 1, 0, 'ios-settings', 0, 0),
(2, 78, '公众号配置(H5)', 'wechat', 1, 0, 'ios-chatbubbles', 3, 0),
(4, 23, '微信支付配置', 'pay', 1, 0, 'ios-chatbubbles', 3, 0),
(7, 78, '小程序配置', 'routine', 1, 0, 'logo-android', 3, 0),
(9, 0, '分销配置', 'fenxiao', 1, 0, 'md-contacts', 3, 0),
(11, 100, '用户积分配置', 'point', 0, 0, 'logo-euro', 3, 0),
(18, 65, '一号通', 'system_sms', 1, 0, 'ios-chatboxes', 3, 99),
(21, 65, '小票打印配置', 'printing_deploy', 1, 0, 'logo-buffer', 3, 0),
(23, 65, '商城支付配置', 'pay_config', 1, 0, 'logo-usd', 3, 70),
(28, 100, '用户充值配置', 'recharge_site', 0, 0, '', 3, 2),
(31, 79, '基础配置', 'base_config', 0, 0, '', 0, 0),
(41, 65, '采集商品配置', 'copy_product', 1, 0, '', 3, 0),
(45, 100, '用户等级配置', 'store_member', 1, 0, '', 3, 3),
(50, 113, '发票功能配置', 'store_invoice', 1, 0, '', 3, 0),
(63, 23, '支付宝支付配置', 'ali_pay', 1, 0, '', 3, 0),
(64, 65, '物流查询配置', 'logistics_select', 1, 0, '', 3, 0),
(65, 0, '接口设置', 'system_serve', 1, 0, 'md-briefcase', 3, 0),
(66, 65, '电子面单配置', 'electronic_sheet', 1, 0, '', 3, 0),
(67, 100, '付费会员配置', 'member_card', 0, 0, '', 3, 2),
(69, 0, '客服配置', 'kefu_config', 1, 0, '', 3, 0),
(70, 129, '分享配置', 'share_index_config', 1, 0, '', 0, 0),
(71, 113, '售后退款配置', 'refund_config', 1, 0, '', 3, 0),
(72, 9, '分销模式', 'brokerage_type', 1, 0, '', 3, 0),
(73, 9, '返佣设置', 'brokerage_set', 1, 0, '', 3, 0),
(74, 9, '提现设置', 'extract_set', 1, 0, '', 3, 0),
(75, 78, 'PC站点配置', 'system_pc', 1, 0, '', 3, 0),
(77, 78, 'APP配置', 'app', 1, 0, '', 3, 0),
(78, 0, '应用配置', 'sys_app', 1, 0, '', 3, 0),
(79, 65, '系统存储配置', 'storage_config', 1, 0, '', 3, 0),
(80, 79, '七牛云配置', 'qiniu_config', 0, 0, '', 0, 0),
(81, 79, '阿里云配置', 'oss_config', 0, 0, '', 0, 0),
(82, 79, '腾讯云配置', 'cos_config', 0, 0, '', 0, 0),
(86, 21, '基础配置', 'print_basic', 1, 0, '', 3, 0),
(87, 21, '易联云配置', 'yly_config', 1, 0, '', 3, 0),
(89, 41, '基础配置', 'copy_basic', 1, 0, '', 3, 0),
(90, 41, '99api配置', '99api_config', 1, 0, '', 3, 0),
(91, 64, '基础配置', 'logistics_basic', 1, 0, '', 3, 0),
(92, 64, '阿里云配置', 'logistics_aliyun', 1, 0, '', 3, 0),
(93, 66, '基础配置', 'electronic_basic', 1, 0, '', 3, 0),
(94, 66, '一号通配置', 'system_electronic_config', 1, 0, '', 3, 0),
(96, 65, '短信接口配置', 'sms_config', 1, 0, '', 3, 0),
(97, 96, '基础配置', 'sms_config_basic', 1, 0, '', 3, 0),
(98, 96, '阿里云配置', 'sms_aliyun', 1, 0, '', 3, 0),
(99, 96, '腾讯云配置', 'tencent_sms', 1, 0, '', 3, 0),
(100, 0, '用户配置', 'system_user_config', 1, 0, 'md-contact', 3, 0),
(102, 65, '对外接口配置', 'out_config', 1, 0, '', 3, 0),
(103, 102, '基础配置', 'out_basic', 1, 0, '', 3, 0),
(104, 102, '推送配置', 'out_push', 1, 0, '', 3, 0),
(105, 100, '新用户设置', 'new_user_setting', 1, 0, '', 3, 0),
(106, 129, '翻译配置', 'online_translation', 0, 0, '', 3, 0),
(107, 21, '飞鹅云配置', 'fey_config', 1, 0, '', 3, 0),
(108, 23, '通联支付', 'allinpay', 1, 0, '', 3, 0),
(109, 23, '基础配置', 'pay_basic', 1, 0, '', 3, 100),
(110, 79, '京东云配置', 'jd_oss_config', 0, 0, '', 0, 0),
(111, 79, '华为云配置', 'obs_config', 0, 0, '', 0, 0),
(112, 79, '天翼云配置', 'ty_oss_config', 0, 0, '', 0, 0),
(113, 0, '订单配置', 'order_config', 1, 0, '', 3, 0),
(114, 113, '包邮设置', 'free_shipping_config', 1, 0, '', 3, 10),
(115, 113, '订单取消配置', 'order_cancel_config', 1, 0, '', 3, 0),
(116, 113, '自动收货配置', 'auto_take_config', 1, 0, '', 3, 0),
(117, 113, '自动评价配置', 'auto_reviews_config', 1, 0, '', 3, 0),
(119, 113, '到店自提配置', 'self_mention_config', 1, 0, '', 3, 0),
(120, 113, '警戒库存配置', 'store_stock_config', 1, 0, '', 3, 0),
(122, 129, 'LOGO配置', 'site_logo_config', 1, 0, '', 0, 0),
(123, 129, '统计配置', 'statistics_config', 1, 0, '', 0, 0),
(124, 129, '地图配置', 'map_config', 1, 0, '', 0, 0),
(125, 129, '备案配置', 'beian_config', 1, 0, '', 0, 0),
(126, 100, '用户签到配置', 'user_sign_config', 0, 0, '', 3, 0),
(129, 0, '系统配置', 'system_config', 1, 0, '', 0, 0);
SQL
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'site_name'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='basics'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'site_name'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'site_url'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='basics'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'site_url'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'site_logo'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='site_logo_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'site_logo'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'store_free_postage'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='free_shipping_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'store_free_postage'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'offline_postage'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='free_shipping_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'offline_postage'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'routine_appId'",
                'sql' => "UPDATE `@table` SET `sort` = 100 WHERE `menu_name` = 'routine_appId'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'routine_appsecret'",
                'sql' => "UPDATE `@table` SET `sort` = 99 WHERE `menu_name` = 'routine_appsecret'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'routine_name'",
                'sql' => "UPDATE `@table` SET `sort` = 98 WHERE `menu_name` = 'routine_name'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'store_stock'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='store_stock_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'store_stock'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'order_cancel_time'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='order_cancel_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'order_cancel_time'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'order_activity_time'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='order_cancel_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'order_activity_time'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'order_bargain_time'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='order_cancel_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'order_bargain_time'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'order_seckill_time'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='order_cancel_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'order_seckill_time'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'order_pink_time'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='order_cancel_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'order_pink_time'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'system_delivery_time'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='auto_take_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'system_delivery_time'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'h5_avatar'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='new_user_setting'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId,`sort` = 100 WHERE `menu_name` = 'h5_avatar'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'tengxun_map_key'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='map_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'tengxun_map_key'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'store_self_mention'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='self_mention_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'store_self_mention'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'site_logo_square'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='site_logo_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'site_logo_square'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'login_logo'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='site_logo_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'login_logo'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'store_user_mobile'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='new_user_setting'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId,`sort` = 95 WHERE `menu_name` = 'store_user_mobile'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'sign_give_exp'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='user_sign_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'sign_give_exp'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'wap_login_logo'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='site_logo_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'wap_login_logo'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'record_No'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='beian_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'record_No'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'statistic_script'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='statistics_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'statistic_script'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'ico_path'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='site_logo_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'ico_path'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'system_comment_time'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='auto_reviews_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'system_comment_time'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'comment_content'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='auto_reviews_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'comment_content'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'queue_open'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='basics'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'queue_open'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'network_security'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='beian_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'network_security'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'icp_url'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='beian_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'icp_url'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'network_security_url'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='beian_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'network_security_url'"
            ],
            [
                'code' => 520,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'coupon_return_open'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='refund_config'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId WHERE `menu_name` = 'coupon_return_open'"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'jd_storageRegion'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='jd_oss_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'jd_storageRegion', 'text', 'input', @tabId, '', 1, '', 100, 0, '\"\"', '京东云storageRegion', '京东云storageRegion', 0, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'sign_status'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='user_sign_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'sign_status', 'radio', 'input', @tabId, '1=>开启\n0=>关闭', 1, '', 0, 0, '1', '签到开关', '签到开关，商城是否开启签到功能，关闭后隐藏签到入口', 100, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'sign_mode'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='user_sign_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'sign_status', 'radio', 'input', @tabId, '1=>开启\n0=>关闭', 1, '', 0, 0, '1', '签到开关', '签到开关，商城是否开启签到功能，关闭后隐藏签到入口', 100, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'sign_remind'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='user_sign_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'sign_remind', 'radio', 'input', @tabId, '1=>开启\n0=>关闭', 1, '', 0, 0, '0', '签到提醒', '是否开启签到提醒，提醒方式为短信以及站内信', 90, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'sign_remind_time'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='user_sign_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'sign_remind_time', 'text', 'number', @tabId, '', 1, '', 0, 0, '9', '提醒时间', '每天的签到提示时间，只能设置整点', 85, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'sign_remind_type'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='user_sign_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'sign_remind_type', 'checkbox', 'input', @tabId, '1=>短信\n2=>站内信', 1, '', 0, 0, '[\"1\",\"2\"]', '提醒方式', '签到每日提醒的提醒方式，支持短信和站内信', 80, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'sign_give_point'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='user_sign_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'sign_give_point', 'text', 'number', @tabId, '', 1, '', 0, 0, '10', '签到赠送积分', '签到赠送积分，每日签到赠送的积分值', 75, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'mer_type'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'mer_type', 'radio', 'input', @tabId, '0=>微信支付商户模式\n1=>微信支付服务商模式', 1, '', 0, 0, '0', '商户类型', '商户类型，目前支持普通微信商户模式和普通微信服务商模式；', 0, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'pay_sub_merchant_id'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'pay_sub_merchant_id', 'text', 'input', @tabId, '', 1, '', 0, 0, '\"\"', '子商户商户号', '微信支付服务商子商户商户号', 0, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'withdrawal_fee'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='extract_set'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'withdrawal_fee', 'text', 'number', @tabId, '', 1, '', 100, 0, '\"0\"', '提现手续费', '提现手续费百分比，范围0-100，0为无提现手续费，例：设置10%提现，提现100，到账90，10手续费', 0, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'routine_auth_type'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'routine_auth_type', 'checkbox', 'input', @tabId, '1=>微信授权\n2=>手机号登录', 1, '', 0, 0, '[\"1\",\"2\"]', '手机号获取方式', '小程序获取手机号的方式，微信授权和手机号验证码', 90, 1)"
            ],
            [
                'code' => 520,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'wechat_template_to_miniprogram'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'wechat_template_to_miniprogram', 'radio', 'input', @tabId, '1=>开启\n0=>关闭', 1, '', 0, 0, '\"0\"', '模版消息跳小程序', '开启之后，公众号发送的模版消息，点击之后跳转到小程序的对应页面，需要在公众号中关联小程序', 0, 1)"
            ],
            [
                'code' => 520,
                'type' => 1,
                'table' => "system_crud_data",
                'findSql' => "select * from information_schema.tables where table_name ='@table'",
                'sql' => "CREATE TABLE IF NOT EXISTS `@table` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `value` text NOT NULL COMMENT '数据内容JSON',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='crud数据字典';"
            ],
            [
                'code' => 520,
                'type' => -1,
                'table' => "system_menus",
                'sql' => "truncate table `@table`"
            ],
            [
                'code' => 520,
                'type' => -1,
                'table' => "system_menus",
                'sql' => <<<SQL
INSERT INTO `@table` (`id`, `pid`, `icon`, `menu_name`, `module`, `controller`, `action`, `api_url`, `methods`, `params`, `sort`, `is_show`, `is_show_path`, `access`, `menu_path`, `path`, `auth_type`, `header`, `is_header`, `unique_auth`, `is_del`, `mark`) VALUES
(1, 0, 's-shop', '商品', 'admin', 'product', 'index', '', '', '[]', 115, 1, 1, 1, '/product', '', 1, '0', 1, 'admin-product', 0, '商品'),
(2, 1, '', '商品管理', 'admin', 'product.product', 'index', '', '', '[]', 1, 1, 1, 1, '/product/product_list', '', 1, '', 0, 'admin-store-storeProuduct-index', 0, '商品管理'),
(3, 1, '', '商品分类', 'admin', 'product.storeCategory', 'index', '', '', '[]', 1, 1, 1, 1, '/product/product_classify', '', 1, 'product', 0, 'admin-store-storeCategory-index', 0, '商品分类'),
(4, 0, 's-order', '订单', 'admin', 'order', 'index', '', '', '[]', 120, 1, 1, 1, '/order', '', 1, 'home', 1, 'admin-order', 0, '订单'),
(5, 4, '', '订单管理', 'admin', 'order.store_order', 'index', '', '', '[]', 10, 1, 1, 1, '/order/list', '4', 1, 'order', 0, 'admin-order-storeOrder-index', 0, '订单管理'),
(6, 1, '', '商品评论', 'admin', 'store.store_product_reply', 'index', '', '', '[]', 0, 1, 1, 1, '/product/product_reply', '', 1, 'product', 0, 'product-product-reply', 0, '商品评论'),
(7, 0, 's-home', '主页', 'admin', 'index', '', '', '', '[]', 127, 1, 1, 1, '/index', '', 1, 'home', 1, 'admin-home', 0, '主页'),
(9, 0, 'user-solid', '用户', 'admin', 'user.user', '', '', '', '[]', 125, 1, 1, 1, '/user', '', 1, 'user', 1, 'admin-user', 0, '用户'),
(10, 9, '', '用户管理', 'admin', 'user.user', 'index', '', '', '[]', 10, 1, 1, 1, '/user/list', '', 1, 'user', 0, 'admin-user-user-index', 0, '用户管理'),
(11, 9, '', '用户等级', 'admin', 'user.user_level', 'index', '', '', '[]', 7, 1, 1, 1, '/user/level', '', 1, 'user', 0, 'user-user-level', 0, '用户等级'),
(12, 0, 's-tools', '设置', 'admin', 'setting.system_config', 'index', '', '', '[]', 1, 1, 1, 1, '/setting', '', 1, 'setting', 1, 'admin-setting', 0, '设置'),
(14, 12, '', '管理权限', 'admin', 'setting.system_admin', '', '', '', '[]', 0, 1, 1, 1, '/setting/auth/list', '', 1, 'setting', 0, 'setting-system-admin', 0, '管理权限'),
(19, 14, '', '角色管理', 'admin', 'setting.system_role', 'index', '', '', '[]', 1, 1, 1, 1, '/setting/system_role/index', '', 1, 'setting', 0, 'setting-system-role', 0, '角色管理'),
(20, 14, '', '管理员列表', 'admin', 'setting.system_admin', 'index', '', '', '[]', 1, 1, 1, 1, '/setting/system_admin/index', '', 1, 'setting', 0, 'setting-system-list', 0, '管理员列表'),
(21, 14, '', '权限设置', 'admin', 'setting.system_menus', 'index', '', '', '[]', 1, 1, 1, 1, '/setting/system_menus/index', '12/14', 1, 'setting', 1, 'setting-system-menus', 0, '菜单管理'),
(23, 12, '', '系统设置', 'admin', 'setting.system_config', 'index', '', '', '[]', 10, 1, 1, 1, '/setting/system_config/2/129', '12', 1, 'setting', 1, 'setting-system-config', 0, '系统设置'),
(25, 0, 'cpu', '维护', 'admin', 'system', '', '', '', '[]', 0, 1, 1, 1, '/system', '', 1, 'setting', 1, 'admin-system', 0, '维护'),
(26, 0, 's-promotion', '分销', 'admin', 'agent', '', '', '', '[]', 104, 1, 1, 1, '/agent', '', 1, 'user', 1, 'admin-agent', 0, '分销'),
(27, 0, 's-marketing', '营销', 'admin', 'marketing', '', '', '', '[]', 110, 1, 1, 1, '/marketing', '', 1, 'home', 1, 'admin-marketing', 0, '营销'),
(28, 26, '', '分销设置', 'admin', 'setting.system_config', '', '', '', '[]', 1, 1, 1, 1, '/setting/system_config_retail/2/9', '', 1, 'setting', 0, 'setting-system-config', 0, '分销设置'),
(29, 26, '', '分销员管理', 'admin', 'agent.agent_manage', 'index', '', '', '[]', 99, 1, 1, 1, '/agent/agent_manage/index', '', 1, 'user', 0, 'agent-agent-manage', 0, '分销员管理'),
(30, 27, '', '优惠券', 'admin', 'marketing.store_coupon', '', '', '', '[]', 100, 1, 1, 1, '/marketing/store_coupon', '27', 1, 'marketing', 0, 'marketing-store_coupon-index', 0, '优惠券'),
(31, 27, '', '砍价管理', 'admin', 'marketing.store_bargain', '', '', '', '[]', 85, 1, 1, 1, '/marketing/store_bargain', '27', 1, 'marketing', 0, 'marketing-store_bargain-index', 0, '砍价管理'),
(32, 27, '', '拼团管理', 'admin', 'marketing.store_combination', '', '', '', '[]', 80, 1, 1, 1, '/marketing/store_combination', '27', 1, 'marketing', 0, 'marketing-store_combination-index', 0, '拼团管理'),
(33, 27, '', '秒杀管理', 'admin', 'marketing.store_seckill', '', '', '', '[]', 75, 1, 1, 1, '/marketing/store_seckill', '27', 1, 'marketing', 0, 'marketing-store_seckill-index', 0, '秒杀管理'),
(34, 27, '', '积分管理', 'admin', 'marketing.user_point', '', '', '', '[]', 95, 1, 1, 1, '/marketing/user_point', '27', 1, 'marketing', 0, 'marketing-user_point-index', 0, '积分管理'),
(35, 0, 's-finance', '财务', 'admin', 'finance', '', '', '', '[]', 90, 1, 1, 1, '/finance', '', 1, 'home', 1, 'admin-finance', 0, '财务'),
(36, 35, '', '财务操作', 'admin', 'finance', '', '', '', '[]', 1, 1, 1, 1, '/finance/user_extract', '', 1, 'finance', 0, 'finance-user_extract-index', 0, '财务操作'),
(37, 35, '', '财务记录', 'admin', 'finance', '', '', '', '[]', 1, 1, 1, 1, '/finance/user_recharge', '', 1, 'finance', 0, 'finance-user-recharge-index', 0, '财务记录'),
(38, 35, '', '佣金记录', 'admin', 'finance', '', '', '', '[]', 1, 1, 1, 1, '/finance/finance', '', 1, 'finance', 0, 'finance-finance-index', 0, '佣金记录'),
(39, 36, '', '提现申请', 'admin', 'finance.user_extract', '', '', '', '[]', 1, 1, 1, 1, '/finance/user_extract/index', '', 1, 'finance', 0, 'finance-user_extract', 0, '提现申请'),
(40, 37, '', '充值记录', 'admin', 'finance.user_recharge', '', '', '', '[]', 1, 1, 1, 1, '/finance/user_recharge/index', '', 1, 'finance', 0, 'finance-user-recharge', 0, '充值记录'),
(42, 38, '', '佣金记录', 'admin', 'finance.finance', '', '', '', '[]', 1, 1, 1, 1, '/finance/finance/commission', '', 1, 'finance', 0, 'finance-finance-commission', 0, '佣金记录'),
(43, 0, 's-management', '内容', 'admin', 'cms', '', '', '', '[]', 85, 1, 1, 1, '/cms', '', 1, 'home', 1, 'admin-cms', 0, '内容'),
(44, 43, '', '文章管理', 'admin', 'cms.article', 'index', '', '', '[]', 1, 1, 1, 1, '/cms/article/index', '', 1, 'cms', 0, 'cms-article-index', 0, '文章管理'),
(45, 43, '', '文章分类', 'admin', 'cms.article_category', 'index', '', '', '[]', 1, 1, 1, 1, '/cms/article_category/index', '', 1, 'cms', 0, 'cms-article-category', 0, '文章分类'),
(47, 65, '', '系统日志', 'admin', 'system.system_log', 'index', '', '', '[]', 0, 1, 1, 1, '/system/maintain/system_log/index', '', 1, 'system', 0, 'system-maintain-system-log', 0, '系统日志'),
(56, 25, '', '开发配置', 'admin', 'system', '', '', '', '[]', 10, 1, 1, 1, '/system/config', '', 1, 'system', 0, 'system-config-index', 0, '开发配置'),
(57, 65, '', '刷新缓存', 'admin', 'system', 'clear', '', '', '[]', 1, 1, 1, 1, '/system/maintain/clear/index', '', 1, 'system', 0, 'system-clear', 0, '刷新缓存'),
(65, 25, '', '安全维护', 'admin', 'system', '', '', '', '[]', 7, 1, 1, 1, '/system/maintain', '', 1, 'system', 0, 'system-maintain-index', 0, '安全维护'),
(66, 1073, '', '清除数据', 'admin', 'system.system_cleardata', 'index', '', '', '[]', 0, 1, 1, 1, '/system/maintain/system_cleardata/index', '25/1073', 1, 'system', 0, 'system-maintain-system-cleardata', 0, '清除数据'),
(67, 1695, '', '数据库管理', 'admin', 'system.system_databackup', 'index', '', '', '[]', 0, 1, 1, 1, '/system/maintain/system_databackup/index', '25/1695', 1, 'system', 1, 'system-maintain-system-databackup', 0, '数据库管理'),
(69, 135, '', '公众号', 'admin', 'wechat', '', '', '', '[]', 4, 1, 1, 1, '/app/wechat', '135', 1, 'app', 0, 'admin-wechat', 0, '公众号'),
(71, 30, '', '优惠券列表', 'admin', 'marketing.store_coupon_issue', 'index', '', '', '[]', 0, 1, 1, 1, '/marketing/store_coupon_issue/index', '', 1, 'marketing', 0, 'marketing-store_coupon_issue', 0, '优惠券列表'),
(72, 30, '', '用户领取记录', 'admin', 'marketing.store_coupon_user', 'index', '', '', '[]', 0, 1, 1, 1, '/marketing/store_coupon_user/index', '', 1, 'marketing', 0, 'marketing-store_coupon_user', 0, '用户领取记录'),
(74, 31, '', '砍价商品', 'admin', 'marketing.store_bargain', 'index', '', '', '[]', 0, 1, 1, 1, '/marketing/store_bargain/index', '', 1, 'marketing', 0, 'marketing-store_bargain', 0, '砍价商品'),
(75, 32, '', '拼团商品', 'admin', 'marketing.store_combination', 'index', '', '', '[]', 0, 1, 1, 1, '/marketing/store_combination/index', '', 1, 'marketing', 0, 'marketing-store_combination', 0, '拼团商品'),
(76, 32, '', '拼团列表', 'admin', 'marketing.store_combination', 'combina_list', '', '', '[]', 0, 1, 1, 1, '/marketing/store_combination/combina_list', '', 1, 'marketing', 0, 'marketing-store_combination-combina_list', 0, '拼团列表'),
(77, 33, '', '秒杀商品', 'admin', 'marketing.store_seckill', 'index', '', '', '[]', 0, 1, 1, 1, '/marketing/store_seckill/index', '', 1, 'marketing', 0, 'marketing-store_seckill', 0, '秒杀商品'),
(78, 33, '', '秒杀配置', 'admin', 'marketing.store_seckill', 'index', '', '', '[]', 0, 1, 1, 1, '/marketing/store_seckill_data/index/49', '', 1, 'marketing', 0, 'marketing-store_seckill-data', 0, '秒杀配置'),
(79, 34, '', '积分配置', 'admin', 'setting.system_config/index.html', 'index', '', '', '[]', 0, 1, 1, 1, '/marketing/integral/system_config/2/11', '27/34', 1, 'marketing', 1, 'marketing-integral-system_config', 0, '积分配置'),
(92, 69, '', '微信菜单', 'admin', 'application.wechat_menus', 'index', '', '', '[]', 0, 1, 1, 1, '/app/wechat/setting/menus/index', '', 1, 'app', 0, 'application-wechat-menus', 0, '微信菜单'),
(94, 3417, '', '一号通页面', 'admin', 'setting.sms_config', '', '', '', '[]', 8, 1, 1, 1, '/setting/sms/sms_config/index', '12/1056/3417', 1, 'setting', 1, 'setting-sms', 0, '一号通'),
(99, 1, '', '商品规格', 'admin', 'store.store_product', 'index', '', '', '[]', 1, 1, 1, 1, '/product/product_attr', '', 1, 'product', 0, 'product-product-attr', 0, '商品规格'),
(109, 69, '', '图文管理', 'admin', 'wechat.wechat_news_category', 'index', '', '', '[]', 0, 1, 1, 1, '/app/wechat/news_category/index', '', 1, 'app', 0, 'wechat-wechat-news-category-index', 0, '图文管理'),
(111, 56, '', '配置分类', 'admin', 'setting.system_config_tab', 'index', '', '', '[]', 99, 1, 1, 1, '/system/config/system_config_tab/index', '25/56', 1, 'system', 0, 'system-config-system_config-tab', 0, '配置分类'),
(112, 56, '', '组合数据', 'admin', 'setting.system_group', 'index', '', '', '[]', 98, 1, 1, 1, '/system/config/system_group/index', '25/56', 1, 'system', 0, 'system-config-system_config-group', 0, '组合数据'),
(113, 114, '', '关注回复', 'admin', 'wechat.reply', 'index', '', '', '[]', 0, 1, 1, 1, '/app/wechat/reply/follow/subscribe', '135/69/114', 1, 'app', 0, 'wechat-wechat-reply-subscribe', 0, '关注回复'),
(114, 69, '', '自动回复', 'admin', 'wechat.reply', '', '', '', '[]', 0, 1, 1, 1, '/app/wechat/reply', '', 1, 'app', 0, 'wechat-wechat-reply-index', 0, '自动回复'),
(115, 114, '', '关键字回复', 'admin', 'wechat.reply', 'keyword', '', '', '[]', 0, 1, 1, 1, '/app/wechat/reply/keyword', '', 1, 'app', 0, 'wechat-wechat-reply-keyword', 0, '关键字回复'),
(116, 114, '', '无效词回复', 'admin', 'wechat.reply', 'index', '', '', '[]', 0, 1, 1, 1, '/app/wechat/reply/index/default', '135/69/114', 1, 'app', 0, 'wechat-wechat-reply-default', 0, '无效词回复'),
(128, 656, '', '数据配置', 'admin', 'setting.system_group_data', 'index', '', '', '[]', 2, 1, 1, 1, '/setting/system_visualization_data', '12/656', 1, 'system', 0, 'admin-setting-system_visualization_data', 0, '数据配置'),
(135, 0, 'menu', '应用', 'admin', 'app', 'index', '', '', '[]', 70, 1, 1, 1, '/app', '', 1, 'app', 1, 'admin-app', 0, '应用'),
(144, 303, '', '提货点设置', 'admin', 'merchant.system_store', 'index', '', '', '[]', 5, 1, 1, 1, '/setting/merchant/system_store/index', '', 1, '', 0, 'setting-system-config-merchant', 0, '提货点设置'),
(145, 1073, '', '物流公司', 'admin', 'freight.express', 'index', '', '', '[]', 4, 1, 1, 1, '/setting/freight/express/index', '25/1073', 1, '', 0, 'setting-freight-express', 0, '物流公司'),
(165, 0, 'message-solid', '客服', 'admin', 'setting.storeService', 'index', '', '', '[]', 104, 1, 1, 1, '/kefu', '', 1, '', 0, 'setting-store-service', 0, '客服'),
(227, 9, '', '用户分组', 'admin', 'user.user_group', 'index', '', '', '[]', 9, 1, 1, 1, '/user/group', '', 1, 'user', 0, 'user-user-group', 0, '用户分组'),
(229, 1073, '', '城市数据', 'admin', 'setting.system_city', '', '', '', '[]', 1, 1, 1, 1, '/setting/freight/city/list', '25/1073', 1, 'setting', 0, 'setting-system-city', 0, '城市数据'),
(230, 303, '', '运费模板', 'admin', 'setting.shipping_templates', '', '', '', '[]', 0, 1, 1, 1, '/setting/freight/shipping_templates/list', '', 1, 'setting', 0, 'setting-shipping-templates', 0, '运费模板'),
(300, 144, '', '提货点', 'admin', 'merchant.system_store', 'index', '', '', '[]', 0, 1, 1, 1, '/setting/merchant/system_store/list', '', 1, 'setting', 0, 'setting-merchant-system-store', 0, '提货点'),
(301, 144, '', '核销员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/merchant/system_store_staff/index', '', 1, 'setting', 0, 'setting-merchant-system-store-staff', 0, '核销员'),
(302, 4, '', '核销记录', 'admin', '', 'index', '', '', '[]', 0, 1, 1, 1, '/setting/merchant/system_verify_order/index', '4', 1, 'setting', 1, 'setting-merchant-system-verify-order', 0, '核销订单'),
(303, 12, '', '发货设置', 'admin', 'setting', 'index', '', '', '[]', 0, 1, 1, 1, '/setting/freight', '12', 1, '', 0, 'admin-setting-freight', 0, '发货设置'),
(566, 656, '', '素材管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/file', '12/656', 1, '', 0, 'system-file', 0, '素材管理'),
(589, 9, '', '用户标签', 'admin', 'user.user_label', 'index', '', '', '[]', 8, 1, 1, 1, '/user/label', '', 1, 'user', 0, 'user-user-label', 0, '用户标签'),
(605, 25, '', '商业授权', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/maintain/auth', '', 1, '', 0, 'system-maintain-auth', 0, '商业授权'),
(655, 65, '', '在线升级', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/onlineUpgrade/index', '25/65', 1, '', 0, 'system-onlineUpgrade-index', 0, '在线升级'),
(656, 0, 's-open', '装修', 'admin', '', '', '', '', '[]', 80, 1, 1, 1, '/setting/pages', '', 1, '', 0, 'admin-setting-pages', 0, '装修'),
(657, 656, '', '页面设计', 'admin', '', '', '', '', '[]', 3, 1, 1, 1, '/setting/pages/devise', '12/656', 1, '', 0, 'admin-setting-pages-devise', 0, '页面设计'),
(678, 165, '', '客服列表', 'admin', '', '', '', '', '[]', 10, 1, 1, 1, '/setting/store_service/index', '165', 1, '', 0, 'admin-setting-store_service-index', 0, '客服列表'),
(679, 165, '', '客服话术', 'admin', '', '', '', '', '[]', 9, 1, 1, 1, '/setting/store_service/speechcraft', '165', 1, '', 0, 'admin-setting-store_service-speechcraft', 0, '客服话术'),
(686, 27, '', '直播管理', 'admin', '', '', '', '', '[]', 65, 1, 1, 1, '/marketing/live', '27', 1, '', 0, 'admin-marketing-live', 0, '直播管理'),
(687, 686, '', '直播间管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/live/live_room', '', 1, '', 0, 'admin-marketing-live-live_room', 0, '直播间管理'),
(688, 686, '', '直播商品管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/live/live_goods', '', 1, '', 0, 'admin-marketing-live-live_goods', 0, '直播商品管理'),
(689, 686, '', '主播管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/live/anchor', '', 1, '', 0, 'admin-marketing-live-anchor', 0, '主播管理'),
(717, 1, '', '商品统计', 'admin', '', '', '', '', '[]', 100, 1, 1, 1, '/statistic/product', '1', 1, '', 0, 'admin-statistic', 0, '商品统计'),
(718, 9, '', '用户统计', 'admin', '', '', '', '', '[]', 100, 1, 1, 1, '/statistic/user', '9', 1, '', 0, 'admin-statistic', 0, '用户统计'),
(720, 303, '', '配送员管理', 'admin', '', '', '', '', '[]', 10, 1, 1, 1, '/setting/delivery_service/index', '', 1, '', 0, 'setting-delivery-service', 0, '配送员管理'),
(731, 27, '', '付费会员', 'admin', '', '', '', '', '[]', 70, 1, 1, 1, '/user/grade', '27', 1, '', 0, 'user-user-grade', 0, '付费会员'),
(738, 165, '', '用户留言', 'admin', '', '', '', '', '[]', 8, 1, 1, 1, '/setting/store_service/feedback', '165', 1, '', 0, 'admin-setting-store_service-feedback', 0, '用户留言'),
(751, 731, '', '会员类型', 'admin', '', '', '', '', '[]', 5, 1, 1, 1, '/user/grade/type', '', 1, '', 0, 'admin-user-member-type', 0, '会员类型'),
(755, 31, '', '砍价列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/store_bargain/bargain_list', '', 1, '', 0, 'marketing-store_bargain-bargain_list', 0, '砍价列表'),
(760, 4, '', '收银订单', 'admin', '', '', '', '', '[]', 8, 1, 1, 1, '/order/offline', '4', 1, '', 0, 'admin-order-offline', 0, '收银订单'),
(762, 731, '', '卡密会员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/user/grade/card', '', 1, '', 0, 'admin-user-grade-card', 0, '卡密会员'),
(763, 731, '', '会员记录', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/user/grade/record', '', 1, '', 0, 'admin-user-grade-record', 0, '会员记录'),
(765, 731, '', '会员权益', 'admin', '', '', '', '', '[]', 4, 1, 1, 1, '/user/grade/right', '', 1, '', 0, 'admin-user-grade-right', 0, '会员权益'),
(766, 35, '', '交易统计', 'admin', '', '', '', '', '[]', 100, 1, 1, 1, '/statistic/transaction', '35', 1, '', 0, 'admin-statistic', 0, '交易统计'),
(767, 36, '', '发票管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/order/invoice/list', '', 1, '', 0, 'admin-order-startOrderInvoice-index', 0, '发票管理'),
(896, 26, '', '分销等级', 'admin', '', '', '', '', '[]', 95, 1, 1, 1, '/setting/membership_level/index', '26', 1, '', 0, 'admin-setting-membership_level-index', 0, '分销等级'),
(897, 4, '', '售后订单', 'admin', '', '', '', '', '[]', 9, 1, 1, 1, '/order/refund', '4', 1, '', 0, 'admin-order-refund', 0, '售后订单'),
(898, 12, '', '消息管理', 'admin', '', '', '', '', '[]', 9, 1, 1, 1, '/setting/notification/index', '12', 1, '', 0, 'setting-notification', 0, '消息管理'),
(902, 656, '', '主题风格', 'admin', '', '', '', '', '[]', 2, 1, 1, 1, '/setting/theme_style', '12/656', 1, '', 0, 'admin-setting-theme_style', 0, '主题风格'),
(903, 656, '', 'PC商城', 'admin', '', '', '', '', '[]', 2, 1, 1, 1, '/setting/pc_group_data', '12/656', 1, '', 0, 'setting-system-pc_data', 0, 'PC商城'),
(905, 34, '', '积分商品', 'admin', '', '', '', '', '[]', 95, 1, 1, 1, '/marketing/store_integral/index', '27/34', 1, '', 0, 'marketing-store_integral', 0, '积分商品'),
(909, 27, '', '抽奖管理', 'admin', '', '', '', '', '[]', 90, 1, 1, 1, '/marketing/lottery/index', '27', 1, '', 0, 'marketing-lottery-index', 0, '抽奖管理'),
(912, 34, '', '积分订单', 'admin', '', '', '', '', '[]', 90, 1, 1, 1, '/marketing/store_integral/order_list', '27/34', 1, '', 0, 'marketing-store_integral-order', 0, '积分订单'),
(993, 135, '', '小程序', 'admin', '', '', '', '', '[]', 3, 1, 1, 1, '/app/routine', '135', 1, '', 0, 'admin-routine', 0, '小程序'),
(994, 993, '', '小程序下载', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/app/routine/download', '135/993', 1, '', 0, 'routine-download', 0, '小程序下载'),
(997, 4, '', '订单统计', 'admin', '', '', '', '', '[]', 100, 1, 1, 1, '/statistic/order', '4', 1, '', 0, 'admin-statistic', 0, '订单统计'),
(998, 37, '', '资金流水', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/finance/capital_flow/index', '35/37', 1, '', 0, 'finance-capital_flow-index', 0, '资金流水'),
(999, 37, '', '账单记录', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/finance/billing_records/index', '35/37', 1, '', 0, 'finance-billing_records-index', 0, '账单记录'),
(1001, 34, '', '积分记录', 'admin', '', '', '', '', '[]', 85, 1, 1, 1, '/marketing/point_record', '27/34', 1, '', 0, 'marketing-point_record-index', 0, '积分记录'),
(1002, 34, '', '积分统计', 'admin', '', '', '', '', '[]', 100, 1, 1, 1, '/marketing/point_statistic', '27/34', 1, '', 0, 'marketing-point_statistic-index', 0, '积分统计'),
(1003, 35, '', '余额记录', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/finance/balance', '35', 1, '', 0, 'finance-balance-index', 0, '余额记录'),
(1004, 1003, '', '余额记录', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/finance/balance/balance', '35/1003', 1, '', 0, 'finance-user-balance', 0, '余额记录'),
(1005, 1003, '', '余额统计', 'admin', '', '', '', '', '[]', 100, 1, 1, 1, '/statistic/balance', '35/1003', 1, '', 0, 'admin-statistic', 0, '余额统计'),
(1006, 69, '', '公众号配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/wechat_config/2/2', '135/69', 1, '', 0, 'setting-system-config', 0, '公众号配置'),
(1007, 993, '', '小程序配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/routine_config/2/7', '135/993', 1, '', 0, 'setting-system-config', 0, '小程序配置'),
(1008, 135, '', 'PC端', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/app/pc', '135', 1, '', 0, 'admin-pc', 0, 'PC端'),
(1009, 135, '', 'APP', 'admin', '', '', '', '', '[]', 2, 1, 1, 1, '/app/app', '135', 1, '', 0, 'admin-app', 0, 'APP'),
(1010, 1008, '', 'PC端配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/pc_config/2/75', '135/1008', 1, '', 0, 'setting-system-config', 0, 'PC端配置'),
(1011, 1009, '', 'APP配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/app_config/2/77', '135/1009', 1, '', 0, 'setting-system-config', 0, 'APP配置'),
(1012, 1056, '', '系统存储配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/storage', '12', 1, '', 0, 'setting-storage', 0, '系统存储配置'),
(1013, 26, '', '事业部', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/agent/division', '26', 1, '', 0, 'agent-division', 0, '事业部'),
(1014, 1013, '', '事业部列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/division/index', '26/1013', 1, '', 0, 'agent-division-index', 0, '事业部列表'),
(1015, 1013, '', '代理商列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/division/agent/index', '26/1013', 1, '', 0, 'agent-division-agent-index', 0, '代理商列表'),
(1016, 1013, '', '代理商申请', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/division/agent/applyList', '26/1013', 1, '', 0, 'agent-division-agent-applyList', 0, '代理商申请'),
(1018, 909, '', '抽奖配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/lottery/create', '27/909', 1, '', 0, 'admin-marketing-lottery-create', 0, '抽奖配置'),
(1019, 909, '', '中奖记录', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/lottery/recording_list', '27/909', 1, '', 0, 'admin-marketing-lottery-recording_list-id', 0, '中奖记录'),
(1023, 27, '', '渠道码', 'admin', '', '', '', '', '[]', 55, 1, 1, 1, '/marketing/channel_code/channelCodeIndex', '27', 1, '', 0, 'marketing-channel_code-index', 0, '渠道码'),
(1053, 3420, '', '金额设置', 'admin', '', '', '', '', '[]', 60, 1, 1, 1, '/marketing/recharge', '27/3420', 1, '', 0, 'marketing-recharge-index', 0, '充值配置'),
(1055, 1009, '', '版本管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/app/app/version', '135/1009', 1, '', 0, 'admin-app-version', 0, '版本管理'),
(1056, 12, '', '接口配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/other_config', '12', 1, '', 0, 'setting-other', 0, '接口配置'),
(1057, 1056, '', '小票打印配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/other_config/print/2/21', '12/1056', 1, '', 0, 'setting-other-print', 0, '小票打印配置'),
(1058, 1056, '', '商品采集配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/other_config/copy/2/41', '12/1056', 1, '', 0, 'setting-other-copy', 0, '商品采集配置'),
(1059, 1056, '', '物流查询配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/other_config/logistics/2/64', '12/1056', 1, '', 0, 'setting-other-logistics', 0, '物流查询配置'),
(1060, 1056, '', '电子面单配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/other_config/electronic/2/66', '12/1056', 1, '', 0, 'setting-other-electronic', 0, '电子面单配置'),
(1061, 12, '', '协议设置', 'admin', '', '', '', '', '[]', 9, 1, 1, 1, '/setting/agreement', '12', 1, '', 0, 'setting-agreement', 0, '协议设置'),
(1062, 1056, '', '短信接口配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/other_config/sms/2/96', '12/1056', 1, '', 0, 'setting-other-sms', 0, '短信接口配置'),
(1063, 1056, '', '商城支付配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/other_config/pay/2/23', '12/1056', 1, '', 0, 'setting-other-pay', 0, '商城支付配置'),
(1064, 25, '', '对外接口', 'admin', '', '', '', '', '[]', 6, 1, 1, 1, '/setting/other_out_config', '25', 1, '', 0, 'setting-other-out', 0, '对外接口'),
(1066, 1064, '', '账号管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/system_out_account/index', '25/1064', 1, '', 0, 'setting-system-out-account-index', 0, '账号管理'),
(1067, 25, '', '语言设置', 'admin', '', '', '', '', '[]', 5, 1, 1, 1, '/setting/lang', '25', 1, '', 0, 'admin-lang', 0, '语言设置'),
(1068, 1067, '', '语言列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/lang/list', '12/1067', 1, '', 0, 'admin-lang-list', 0, '语言列表'),
(1069, 1067, '', '语言详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/lang/info', '12/1067', 1, '', 0, 'admin-lang-info', 0, '语言详情'),
(1070, 1067, '', '地区列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/lang/country', '25/1067', 1, '', 0, 'admin-lang-country', 0, '地区列表'),
(1071, 1695, '', '文件管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/maintain/system_file/opendir', '25/1695', 1, '', 0, 'system-maintain-system-file', 0, '文件管理'),
(1073, 25, '', '数据维护', 'admin', '', '', '', '', '[]', 7, 1, 1, 1, 'system/database/index', '25', 1, '', 0, 'system-database-index', 0, '数据维护'),
(1075, 731, '', '会员配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/member_config/2/67', '27/731', 1, '', 0, 'setting-member-config', 0, '会员配置'),
(1076, 56, '', '定时任务', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/crontab', '25/56', 1, '', 0, 'system-crontab-index', 0, '定时任务'),
(1078, 1695, '', '接口管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/backend_routing', '25/1695', 1, '', 0, 'system-config-backend-routing', 0, '接口管理'),
(1101, 1695, '', '代码生成', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/code_generation_list', '25/1695', 1, '', 0, 'system-config-code-generation-list', 0, '代码生成'),
(1695, 25, '', '开发工具', 'admin', '', '', '', '', '[]', 1, 1, 1, 1, '/tool', '25', 1, '', 0, 'admin-tool', 0, '开发工具'),
(2450, 69, '', '添加图文消息', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/app/wechat/news_category/save', '135/69', 3, '', 0, 'wechat-wechat-news-category-save', 0, '添加图文消息'),
(2451, 43, '', '文章添加', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/cms/article/add_article', '43', 3, '', 0, 'cms-article-creat', 0, '文章添加'),
(2452, 32, '', '拼团添加', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/store_combination/create', '27/32', 3, '', 0, 'marketing-store_combination-create', 0, '拼团添加'),
(2453, 30, '', '添加优惠券', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/store_coupon_issue/create', '27/30', 3, '', 0, 'marketing-store_coupon_issue-create', 0, '添加优惠券'),
(2454, 1, '', '商品添加', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/product/add_product', '1', 3, '', 0, 'admin-store-storeProuduct-index', 0, '商品添加'),
(2455, 31, '', '砍价添加', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/store_bargain/create', '27/31', 3, '', 0, 'marketing-store_bargain-create', 0, '砍价添加'),
(2456, 33, '', '秒杀添加', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/store_seckill/create', '27/33', 3, '', 0, 'marketing-store_seckill-create', 0, '秒杀添加'),
(2457, 34, '', '积分商品添加', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/store_integral/create', '27/34', 3, '', 0, 'marketing-store_integral-create', 0, '积分商品添加'),
(2458, 686, '', '直播间添加', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/live/add_live_room', '27/686', 3, '', 0, 'admin-marketing-live-add_live_room', 0, '直播间添加'),
(2459, 686, '', '直播商品管理', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/live/add_live_goods', '27/686', 3, '', 0, 'admin-marketing-live-add_live_goods', 0, '直播商品管理'),
(2460, 27, '', '渠道码添加', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/channel_code/create', '27', 3, '', 0, 'marketing-channel_code-create', 0, '渠道码添加'),
(2461, 656, '', '装修页面', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/pages/diy', '656', 3, '', 0, 'admin-setting-pages-diy', 0, '装修页面'),
(2462, 1695, '', '代码生成', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/code_generation', '25/1695', 3, '', 0, 'system-config-code-generation', 0, '代码生成'),
(2463, 1695, '', '文件管理入口', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/maintain/system_file/login', '25/1695', 3, '', 0, 'system-maintain-system-file', 0, '文件管理入口'),
(2472, 56, '', '权限维护', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/system_menus/index', '25/56', 1, '', 0, 'system_menus-index', 0, '权限规则'),
(2475, 10, '', '添加用户', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-create', 0, '添加用户'),
(2476, 2475, '', '添加编辑用户信息时候的信息', '', '', '', 'user/user/user_save_info/<uid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user-user_save_info', 0, '添加编辑用户信息时候的信息'),
(2477, 2475, '', '添加或修改用户标签表单', '', '', '', 'user/user_label/add/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label-add', 0, '添加或修改用户标签表单'),
(2478, 2475, '', '添加或修改用户标签', '', '', '', 'user/user_label/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label-save', 0, '添加或修改用户标签'),
(2479, 2475, '', '保存用户', '', '', '', 'user/user', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user', 0, '保存用户'),
(2480, 2475, '', '获取修改用户表单', '', '', '', 'user/user/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user-edit', 0, '获取修改用户表单'),
(2481, 2475, '', '修改用户', '', '', '', 'user/user/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user', 0, '修改用户'),
(2482, 2475, '', '添加用户', '', '', '', 'user/user/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user-save', 0, '添加用户'),
(2483, 10, '', '发送优惠券', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-send-coupon', 0, '发送优惠券'),
(2484, 2483, '', '发送优惠券列表', '', '', '', 'marketing/coupon/grant', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-coupon-grant', 0, '发送优惠券列表'),
(2485, 2483, '', '发送优惠券', '', '', '', 'marketing/coupon/user/grant', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-coupon-user-grant', 0, '发送优惠券'),
(2486, 10, '', '批量设置分组', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-batch-set-group', 0, '批量设置分组'),
(2487, 2486, '', '用户分组表单', '', '', '', 'user/set_group', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-set_group', 0, '用户分组表单'),
(2488, 2486, '', '设置用户分组', '', '', '', 'user/save_set_group', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-save_set_group', 0, '设置用户分组'),
(2489, 10, '', '批量设置标签', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-batch-set-label', 0, '批量设置标签'),
(2490, 2489, '', '获取用户标签', '', '', '', 'user/label/<uid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-label', 0, '获取用户标签'),
(2491, 2489, '', '保存用户标签', '', '', '', 'user/save_set_label', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-save_set_label', 0, '保存用户标签'),
(2492, 10, '', '用户导出', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-export', 0, '用户导出'),
(2493, 2492, '', '用户列表导出', '', '', '', 'export/user_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'export-user_list', 0, '用户列表导出'),
(2494, 10, '', '用户详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-info', 0, '用户详情'),
(2495, 2494, '', '获取用户详情', '', '', '', 'user/user/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user-6465801810568', 0, '获取用户详情'),
(2496, 2494, '', '修改用户', '', '', '', 'user/user/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user-646580181056f', 0, '修改用户'),
(2497, 2494, '', '添加编辑用户信息时候的信息', '', '', '', 'user/user/user_save_info/<uid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user-user_save_info-6465801810573', 0, '添加编辑用户信息时候的信息'),
(2498, 2494, '', '获取指定用户的信息', '', '', '', 'user/one_info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-one_info', 0, '获取指定用户的信息'),
(2499, 2494, '', '添加或修改用户标签表单', '', '', '', 'user/user_label/add/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label-add-6465802de8e2c', 0, '添加或修改用户标签表单'),
(2500, 2494, '', '添加或修改用户标签', '', '', '', 'user/user_label/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label-save-6465802de8e30', 0, '添加或修改用户标签'),
(2501, 10, '', '积分余额', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-set-balance', 0, '积分余额'),
(2502, 2501, '', '修改积分余额表单', '', '', '', 'user/edit_other/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-edit_other', 0, '修改积分余额表单'),
(2503, 2501, '', '修改积分余额', '', '', '', 'user/update_other/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-update_other', 0, '修改积分余额'),
(2504, 10, '', '赠送会员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-set-level-time', 0, '赠送会员'),
(2505, 2504, '', '赠送付费会员时长', '', '', '', 'user/give_level_time/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-give_level_time', 0, '赠送付费会员时长'),
(2506, 2504, '', '执行赠送付费会员时长', '', '', '', 'user/save_give_level_time/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-save_give_level_time', 0, '执行赠送付费会员时长'),
(2507, 10, '', '设置分组', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-set-group', 0, '设置分组'),
(2508, 2507, '', '用户分组表单', '', '', '', 'user/set_group', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-set_group-646585b911c0b', 0, '用户分组表单'),
(2509, 2507, '', '设置用户分组', '', '', '', 'user/save_set_group', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-save_set_group-646585b911c11', 0, '设置用户分组'),
(2510, 10, '', '设置标签', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-set-label', 0, '设置标签'),
(2511, 2510, '', '获取用户标签', '', '', '', 'user/label/<uid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-label-646585fd46ff0', 0, '获取用户标签'),
(2512, 2510, '', '设置和取消用户标签', '', '', '', 'user/label/<uid>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-label-646585fd46ff6', 0, '设置和取消用户标签'),
(2513, 10, '', '修改上级推广人', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/10', 3, '', 0, 'user-set-spread', 0, '修改上级推广人'),
(2514, 2513, '', '新增客服选择用户列表', '', '', '', 'app/wechat/kefu/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu-create', 0, '新增客服选择用户列表'),
(2515, 2513, '', '修改上级推广人', '', '', '', 'agent/spread', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-spread', 0, '修改上级推广人'),
(2516, 227, '', '添加分组', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/227', 3, '', 0, 'user-group-add', 0, '添加分组'),
(2517, 227, '', '修改分组', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/227', 3, '', 0, 'user-group-update', 0, '修改分组'),
(2518, 227, '', '删除分组', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/227', 3, '', 0, 'user-group-delete', 0, '删除分组'),
(2519, 2516, '', '添加修改分组表单', '', '', '', 'user/user_group/add/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_group-add', 0, '添加修改分组表单'),
(2520, 2516, '', '保存分组表单数据', '', '', '', 'user/user_group/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_group-save', 0, '保存分组表单数据'),
(2521, 2517, '', '保存分组表单数据', '', '', '', 'user/user_group/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_group-save-646586e1d4557', 0, '保存分组表单数据'),
(2522, 2517, '', '添加修改分组表单', '', '', '', 'user/user_group/add/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_group-add-646586e1d455e', 0, '添加修改分组表单'),
(2523, 2518, '', '删除用户分组数据', '', '', '', 'user/user_group/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_group-del', 0, '删除用户分组数据'),
(2525, 589, '', '标签分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/589', 3, '', 0, 'user-label-cate', 0, '标签分类'),
(2526, 589, '', '添加标签', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/589', 3, '', 0, 'user-label-add', 0, '添加标签'),
(2527, 589, '', '修改标签', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/589', 3, '', 0, 'user-label-update', 0, '修改标签'),
(2528, 589, '', '删除标签', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/589', 3, '', 0, 'user-label-delete', 0, '删除标签'),
(2529, 2525, '', '获取标签分类表单', '', '', '', 'user/user_label_cate/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label_cate-create', 0, '获取标签分类表单'),
(2530, 2525, '', '保存标签分类', '', '', '', 'user/user_label_cate', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label_cate', 0, '保存标签分类'),
(2531, 2525, '', '获取修改标签分类表单', '', '', '', 'user/user_label_cate/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label_cate-edit', 0, '获取修改标签分类表单'),
(2532, 2525, '', '修改标签分类', '', '', '', 'user/user_label_cate/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label_cate', 0, '修改标签分类'),
(2533, 2525, '', '删除标签分类', '', '', '', 'user/user_label_cate/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label_cate', 0, '删除标签分类'),
(2534, 2526, '', '添加或修改用户标签', '', '', '', 'user/user_label/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label-save-6465897098557', 0, '添加或修改用户标签'),
(2535, 2526, '', '添加或修改用户标签表单', '', '', '', 'user/user_label/add/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label-add-646589709855d', 0, '添加或修改用户标签表单'),
(2536, 2527, '', '添加或修改用户标签', '', '', '', 'user/user_label/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label-save-6465897903b20', 0, '添加或修改用户标签'),
(2537, 2527, '', '添加或修改用户标签表单', '', '', '', 'user/user_label/add/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label-add-6465897903b2c', 0, '添加或修改用户标签表单'),
(2538, 2528, '', '删除用户标签', '', '', '', 'user/user_label/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_label-del', 0, '删除用户标签'),
(2539, 11, '', '添加等级', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/11', 3, '', 0, 'user-level-add', 0, '添加等级'),
(2540, 11, '', '修改等级', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/11', 3, '', 0, 'user-level-update', 0, '修改等级'),
(2541, 11, '', '删除等级', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/11', 3, '', 0, 'user-level-delete', 0, '删除等级'),
(2542, 11, '', '设置等级状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '9/11', 3, '', 0, 'user-level-status', 0, '设置等级状态'),
(2543, 2539, '', '添加或修改用户等级', '', '', '', 'user/user_level', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_level', 0, '添加或修改用户等级'),
(2544, 2539, '', '添加用户等级表单', '', '', '', 'user/user_level/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_level-create', 0, '添加用户等级表单'),
(2545, 2540, '', '添加用户等级表单', '', '', '', 'user/user_level/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_level-create-64658a1262574', 0, '添加用户等级表单'),
(2546, 2540, '', '添加或修改用户等级', '', '', '', 'user/user_level', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_level-64658a126257b', 0, '添加或修改用户等级'),
(2547, 2541, '', '删除用户等级', '', '', '', 'user/user_level/delete/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_level-delete', 0, '删除用户等级'),
(2548, 2542, '', '设置用户等级上下架', '', '', '', 'user/user_level/set_show/<id>/<is_show>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-user_level-set_show', 0, '设置用户等级上下架'),
(2549, 5, '', '删除订单', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-delete', 0, '删除订单'),
(2550, 5, '', '订单核销', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-write-off', 0, '订单核销'),
(2551, 5, '', '订单导出', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-export', 0, '订单导出'),
(2554, 2549, '', '删除订单单个', '', '', '', 'order/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-del', 0, '删除订单单个'),
(2555, 2549, '', '批量删除订单', '', '', '', 'order/dels', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-dels', 0, '批量删除订单'),
(2556, 2550, '', '订单核销', '', '', '', 'order/write', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-write', 0, '订单核销'),
(2557, 2550, '', '订单号核销', '', '', '', 'order/write_update/<order_id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-write_update', 0, '订单号核销'),
(2558, 2551, '', '订单列表导出', '', '', '', 'export/order_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'export-order_list', 0, '订单列表导出'),
(2559, 5, '', '订单编辑', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-edit', 0, '订单编辑'),
(2560, 5, '', '订单发货', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-send', 0, '订单发货'),
(2561, 5, '', '线下确认付款', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-offline-confirm', 0, '线下确认付款'),
(2562, 5, '', '订单详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-info', 0, '订单详情'),
(2563, 5, '', '订单记录', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-record', 0, '订单记录'),
(2564, 5, '', '电子面单打印', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-electron', 0, '电子面单打印'),
(2565, 5, '', '小票打印', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-tips', 0, '小票打印'),
(2566, 5, '', '订单备注', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-mark', 0, '订单备注'),
(2567, 5, '', '确认收货', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-take', 0, '确认收货'),
(2568, 5, '', '删除订单', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-delete', 0, '删除订单'),
(2569, 5, '', '快递面单打印', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/5', 3, '', 0, 'order-express', 0, '快递面单打印'),
(2570, 2559, '', '获取订单编辑表单', '', '', '', 'order/edit/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-edit-6465a3050171f', 0, '获取订单编辑表单'),
(2571, 2559, '', '修改订单', '', '', '', 'order/update/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-update', 0, '修改订单'),
(2572, 2560, '', '订单发送货', '', '', '', 'order/delivery/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-delivery', 0, '订单发送货'),
(2573, 2560, '', '获取订单可拆分商品列表', '', '', '', 'order/split_cart_info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-split_cart_info', 0, '获取订单可拆分商品列表'),
(2574, 2560, '', '拆单发送货', '', '', '', 'order/split_delivery/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-split_delivery', 0, '拆单发送货'),
(2575, 2560, '', '获取订单拆分子订单列表', '', '', '', 'order/split_order/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-split_order', 0, '获取订单拆分子订单列表'),
(2576, 2560, '', '获取配送信息表单', '', '', '', 'order/distribution/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-distribution', 0, '获取配送信息表单'),
(2577, 2560, '', '获取物流公司', '', '', '', 'order/express_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-express_list', 0, '获取物流公司'),
(2578, 2560, '', '获取物流信息', '', '', '', 'order/express/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-express-6465a3438b950', 0, '获取物流信息'),
(2579, 2560, '', '面单默认配置信息', '', '', '', 'order/sheet_info', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-sheet_info', 0, '面单默认配置信息'),
(2580, 2560, '', '订单列表获取配送员', '', '', '', 'order/delivery/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-delivery-list', 0, '订单列表获取配送员'),
(2581, 2560, '', '电子面单模板列表', '', '', '', 'order/expr/temp', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-expr-temp', 0, '电子面单模板列表'),
(2582, 2560, '', '快递公司电子面单模版', '', '', '', 'order/express/temp', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-express-temp', 0, '快递公司电子面单模版'),
(2583, 2560, '', '更多操作打印电子面单', '', '', '', 'order/order_dump/<order_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-order_dump', 0, '更多操作打印电子面单'),
(2584, 2561, '', '线下支付', '', '', '', 'order/pay_offline/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-pay_offline', 0, '线下支付'),
(2585, 2562, '', '订单详情', '', '', '', 'order/info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-info-6465a369a2c3b', 0, '订单详情'),
(2586, 2563, '', '获取订单状态', '', '', '', 'order/status/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-status', 0, '获取订单状态'),
(2587, 2564, '', '面单默认配置信息', '', '', '', 'order/sheet_info', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-sheet_info-6465a3eb77bb4', 0, '面单默认配置信息'),
(2588, 2564, '', '快递公司电子面单模版', '', '', '', 'order/express/temp', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-express-temp-6465a3eb77bbf', 0, '快递公司电子面单模版'),
(2589, 2564, '', '更多操作打印电子面单', '', '', '', 'order/order_dump/<order_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-order_dump-6465a3eb77bc6', 0, '更多操作打印电子面单'),
(2590, 2564, '', '电子面单模板列表', '', '', '', 'order/expr/temp', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-expr-temp-6465a3eb77bcd', 0, '电子面单模板列表'),
(2591, 2565, '', '打印订单', '', '', '', 'order/print/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-print', 0, '打印订单'),
(2592, 2566, '', '修改备注信息', '', '', '', 'order/remark/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-remark', 0, '修改备注信息'),
(2593, 2567, '', '确认收货', '', '', '', 'order/take/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-take-6465a61b2ebdb', 0, '确认收货'),
(2594, 2568, '', '删除订单单个', '', '', '', 'order/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-del-6465a66347928', 0, '删除订单单个'),
(2595, 2568, '', '批量删除订单', '', '', '', 'order/dels', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-dels-6465a66347931', 0, '批量删除订单'),
(2596, 2569, '', '更多操作打印电子面单', '', '', '', 'order/order_dump/<order_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-order_dump-6465a69038c44', 0, '更多操作打印电子面单'),
(2597, 2569, '', '快递公司电子面单模版', '', '', '', 'order/express/temp', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-express-temp-6465a69038c4c', 0, '快递公司电子面单模版'),
(2598, 2569, '', '电子面单模板列表', '', '', '', 'order/expr/temp', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-expr-temp-6465a69038c51', 0, '电子面单模板列表'),
(2599, 897, '', '订单详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/897', 3, '', 0, 'refund-info', 0, '订单详情'),
(2600, 897, '', '售后备注', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/897', 3, '', 0, 'refund-mark', 0, '售后备注'),
(2601, 897, '', '退款', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/897', 3, '', 0, 'refund-yes', 0, '退款'),
(2602, 897, '', '不退款', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/897', 3, '', 0, 'refund-no', 0, '不退款'),
(2603, 2599, '', '获取退款单详情', '', '', '', 'refund/info/<uni>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'refund-info-6465c125e62a0', 0, '获取退款单详情'),
(2604, 2600, '', '售后订单备注', '', '', '', 'refund/remark/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'refund-remark', 0, '售后订单备注'),
(2605, 2601, '', '商家同意退款，等待用户退货', '', '', '', 'refund/agree/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'refund-agree', 0, '商家同意退款，等待用户退货'),
(2606, 2601, '', '售后订单退款表单', '', '', '', 'refund/refund/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'refund-refund', 0, '售后订单退款表单'),
(2607, 2601, '', '售后订单退款', '', '', '', 'refund/refund/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'refund-refund', 0, '售后订单退款'),
(2608, 2602, '', '修改不退款理由', '', '', '', 'refund/no_refund/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'refund-no_refund', 0, '修改不退款理由'),
(2609, 2602, '', '获取不退款表单', '', '', '', 'refund/no_refund/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'refund-no_refund', 0, '获取不退款表单'),
(2610, 760, '', '收款二维码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '4/760', 3, '', 0, 'order-offline-qrcode', 0, '收款二维码'),
(2611, 2610, '', '获取线下付款二维码', '', '', '', 'order/offline_scan', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-offline_scan', 0, '获取线下付款二维码'),
(2612, 2, '', '添加商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/2', 3, '', 0, 'product-add', 0, '添加商品'),
(2613, 2, '', '商品采集', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/2', 3, '', 0, 'product-copy', 0, '商品采集'),
(2614, 2, '', '批量修改', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/2', 3, '', 0, 'product-batch-edit', 0, '批量修改'),
(2615, 2, '', '商品上下架', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/2', 3, '', 0, 'product-batch-status', 0, '商品上下架'),
(2616, 2, '', '商品导出', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/2', 3, '', 0, 'product-export', 0, '商品导出'),
(2617, 2, '', '查看商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/2', 3, '', 0, 'product-info', 0, '查看商品'),
(2618, 2, '', '编辑商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/2', 3, '', 0, 'product-edit', 0, '编辑商品'),
(2619, 2, '', '商品评论', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/2', 3, '', 0, 'product-reply', 0, '商品评论'),
(2620, 2, '', '商品回收站', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/2', 3, '', 0, 'product-recycle', 0, '商品回收站'),
(2621, 2612, '', '获取退出未保存的数据', '', '', '', 'product/cache', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-cache', 0, '获取退出未保存的数据'),
(2622, 2612, '', '保存还未提交数据', '', '', '', 'product/cache', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-cache', 0, '保存还未提交数据'),
(2623, 2612, '', '获取商品规格', '', '', '', 'product/product/attrs/<id>/<type>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-attrs', 0, '获取商品规格'),
(2624, 2612, '', '新建或修改商品', '', '', '', 'product/product/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product', 0, '新建或修改商品'),
(2625, 2612, '', '商品快速编辑', '', '', '', 'product/product/set_product/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-set_product', 0, '商品快速编辑'),
(2626, 2612, '', '商品规则列表', '', '', '', 'product/product/rule', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule', 0, '商品规则列表'),
(2627, 2612, '', '新建或编辑商品规则', '', '', '', 'product/product/rule/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule', 0, '新建或编辑商品规则'),
(2628, 2612, '', '生成商品规格列表', '', '', '', 'product/generate_attr/<id>/<type>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-generate_attr', 0, '生成商品规格列表'),
(2629, 2612, '', '获取商品规则属性模板', '', '', '', 'product/product/get_rule', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-get_rule', 0, '获取商品规则属性模板'),
(2630, 2612, '', '获取运费模板', '', '', '', 'product/product/get_template', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-get_template', 0, '获取运费模板'),
(2631, 2612, '', '上传视频密钥接口', '', '', '', 'product/product/get_temp_keys', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-get_temp_keys', 0, '上传视频密钥接口'),
(2632, 2612, '', '导入虚拟商品卡密', '', '', '', 'product/product/import_card', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-import_card', 0, '导入虚拟商品卡密'),
(2633, 2613, '', '获取采集商品数据', '', '', '', 'product/crawl', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-crawl', 0, '获取采集商品数据'),
(2634, 2613, '', '获取复制商品配置', '', '', '', 'product/copy_config', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-copy_config', 0, '获取复制商品配置'),
(2635, 2613, '', '保存采集商品数据', '', '', '', 'product/crawl/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-crawl-save', 0, '保存采集商品数据'),
(2636, 2613, '', '复制其他平台商品', '', '', '', 'product/copy', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-copy-6465c40d4430f', 0, '复制其他平台商品'),
(2637, 2613, '', '保存还未提交数据', '', '', '', 'product/cache', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-cache-6465c40d44313', 0, '保存还未提交数据'),
(2638, 2613, '', '获取退出未保存的数据', '', '', '', 'product/cache', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-cache-6465c40d44317', 0, '获取退出未保存的数据'),
(2639, 2613, '', '获取商品规格', '', '', '', 'product/product/attrs/<id>/<type>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-attrs-6465c40d4431b', 0, '获取商品规格'),
(2640, 2613, '', '新建或修改商品', '', '', '', 'product/product/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-6465c40d44320', 0, '新建或修改商品'),
(2641, 2613, '', '商品快速编辑', '', '', '', 'product/product/set_product/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-set_product-6465c40d44324', 0, '商品快速编辑'),
(2642, 2613, '', '商品规则列表', '', '', '', 'product/product/rule', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465c40d44328', 0, '商品规则列表'),
(2643, 2613, '', '新建或编辑商品规则', '', '', '', 'product/product/rule/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465c40d4432c', 0, '新建或编辑商品规则'),
(2644, 2613, '', '商品规则详情', '', '', '', 'product/product/rule/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465c40d4432f', 0, '商品规则详情'),
(2645, 2613, '', '生成商品规格列表', '', '', '', 'product/generate_attr/<id>/<type>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-generate_attr-6465c40d44333', 0, '生成商品规格列表'),
(2646, 2613, '', '获取商品规则属性模板', '', '', '', 'product/product/get_rule', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-get_rule-6465c40d44337', 0, '获取商品规则属性模板'),
(2647, 2613, '', '获取运费模板', '', '', '', 'product/product/get_template', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-get_template-6465c40d4433b', 0, '获取运费模板'),
(2648, 2613, '', '上传视频密钥接口', '', '', '', 'product/product/get_temp_keys', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-get_temp_keys-6465c40d4433f', 0, '上传视频密钥接口'),
(2649, 2614, '', '商品批量设置', '', '', '', 'product/batch/setting', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-batch-setting', 0, '商品批量设置'),
(2650, 2615, '', '设置批量商品下架', '', '', '', 'product/product/product_unshow', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-product_unshow', 0, '设置批量商品下架'),
(2651, 2615, '', '设置批量商品上架', '', '', '', 'product/product/product_show', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-product_show', 0, '设置批量商品上架'),
(2652, 2615, '', '修改商品状态', '', '', '', 'product/product/set_show/<id>/<is_show>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-set_show', 0, '修改商品状态'),
(2653, 2616, '', '商品列表导出', '', '', '', 'export/product_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'export-product_list', 0, '商品列表导出'),
(2654, 2617, '', '商品详情', '', '', '', 'product/product/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-6465c46cedb2c', 0, '商品详情'),
(2655, 2618, '', '获取退出未保存的数据', '', '', '', 'product/cache', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-cache-6465c48f616d4', 0, '获取退出未保存的数据'),
(2656, 2618, '', '保存还未提交数据', '', '', '', 'product/cache', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-cache-6465c48f616dd', 0, '保存还未提交数据'),
(2657, 2618, '', '获取商品规格', '', '', '', 'product/product/attrs/<id>/<type>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-attrs-6465c48f616e5', 0, '获取商品规格'),
(2658, 2618, '', '新建或修改商品', '', '', '', 'product/product/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-6465c48f616eb', 0, '新建或修改商品'),
(2659, 2618, '', '商品详情', '', '', '', 'product/product/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-6465c48f616f0', 0, '商品详情'),
(2660, 2618, '', '商品快速编辑', '', '', '', 'product/product/set_product/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-set_product-6465c48f616f5', 0, '商品快速编辑'),
(2661, 2618, '', '商品规则列表', '', '', '', 'product/product/rule', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465c48f616fb', 0, '商品规则列表'),
(2662, 2618, '', '新建或编辑商品规则', '', '', '', 'product/product/rule/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465c48f61700', 0, '新建或编辑商品规则'),
(2663, 2618, '', '商品规则详情', '', '', '', 'product/product/rule/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465c48f61705', 0, '商品规则详情'),
(2664, 2618, '', '生成商品规格列表', '', '', '', 'product/generate_attr/<id>/<type>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-generate_attr-6465c48f6170b', 0, '生成商品规格列表'),
(2665, 2618, '', '获取商品规则属性模板', '', '', '', 'product/product/get_rule', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-get_rule-6465c48f61710', 0, '获取商品规则属性模板'),
(2666, 2618, '', '获取运费模板', '', '', '', 'product/product/get_template', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-get_template-6465c48f61715', 0, '获取运费模板'),
(2667, 2618, '', '上传视频密钥接口', '', '', '', 'product/product/get_temp_keys', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-get_temp_keys-6465c48f6171a', 0, '上传视频密钥接口'),
(2668, 2618, '', '导入虚拟商品卡密', '', '', '', 'product/product/import_card', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-import_card-6465c48f6171f', 0, '导入虚拟商品卡密'),
(2669, 2620, '', '商品放入回收站', '', '', '', 'product/product/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-6465c51a7665e', 0, '商品放入回收站'),
(2670, 3, '', '添加分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/3', 3, '', 0, 'product-cate-add', 0, '添加分类'),
(2671, 3, '', '修改分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/3', 3, '', 0, 'product-cate-edit', 0, '修改分类'),
(2672, 3, '', '删除分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/3', 3, '', 0, 'product-cate-delete', 0, '删除分类'),
(2673, 3, '', '分类状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/3', 3, '', 0, 'product-cate-status', 0, '分类状态'),
(2674, 2670, '', '商品分类新增', '', '', '', 'product/category', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-category', 0, '商品分类新增'),
(2675, 2670, '', '商品分类新增表单', '', '', '', 'product/category/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-category-create', 0, '商品分类新增表单'),
(2676, 2671, '', '商品分类编辑表单', '', '', '', 'product/category/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-category-6465c612c552f', 0, '商品分类编辑表单'),
(2677, 2671, '', '商品分类编辑', '', '', '', 'product/category/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-category-6465c612c5536', 0, '商品分类编辑'),
(2678, 2672, '', '删除商品分类', '', '', '', 'product/category/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-category-6465c638c6d16', 0, '删除商品分类'),
(2679, 2673, '', '商品分类修改状态', '', '', '', 'product/category/set_show/<id>/<is_show>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-category-set_show', 0, '商品分类修改状态'),
(2680, 99, '', '添加规格', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/99', 3, '', 0, 'product-rule-add', 0, '添加规格'),
(2681, 99, '', '编辑规格', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/99', 3, '', 0, 'product-rule-edit', 0, '编辑规格'),
(2682, 99, '', '删除规格', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/99', 3, '', 0, 'product-rule-delete', 0, '删除规格'),
(2683, 2680, '', '商品规则详情', '', '', '', 'product/product/rule/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465d8779407b', 0, '商品规则详情'),
(2684, 2680, '', '新建或编辑商品规则', '', '', '', 'product/product/rule/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465d87794082', 0, '新建或编辑商品规则'),
(2685, 2681, '', '新建或编辑商品规则', '', '', '', 'product/product/rule/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465d88b800ca', 0, '新建或编辑商品规则'),
(2686, 2681, '', '商品规则详情', '', '', '', 'product/product/rule/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-6465d88b800d4', 0, '商品规则详情'),
(2687, 2682, '', '删除商品规则', '', '', '', 'product/product/rule/delete', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-product-rule-delete', 0, '删除商品规则'),
(2688, 6, '', '添加自评', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/6', 3, '', 0, 'product-reply-add', 0, '添加自评'),
(2689, 6, '', '回复评论', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/6', 3, '', 0, 'product-reply-reply', 0, '回复评论'),
(2690, 6, '', '删除评论', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '1/6', 3, '', 0, 'product-reply-delete', 0, '删除评论'),
(2691, 2688, '', '虚拟评论表单', '', '', '', 'product/reply/fictitious_reply/<product_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-reply-fictitious_reply', 0, '虚拟评论表单'),
(2692, 2688, '', '保存虚拟评论', '', '', '', 'product/reply/save_fictitious_reply', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-reply-save_fictitious_reply', 0, '保存虚拟评论'),
(2693, 2689, '', '商品回复评论', '', '', '', 'product/reply/set_reply/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-reply-set_reply', 0, '商品回复评论'),
(2694, 2690, '', '删除商品评论', '', '', '', 'product/reply/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'product-reply-6465d92d1ba2d', 0, '删除商品评论'),
(2695, 71, '', '添加复制优惠券', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/30/71', 3, '', 0, 'coupon-add', 0, '添加复制优惠券'),
(2696, 71, '', '删除优惠券', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/30/71', 3, '', 0, 'coupon-delete', 0, '删除优惠券'),
(2697, 71, '', '领取记录', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/30/71', 3, '', 0, 'coupon-receive', 0, '领取记录'),
(2698, 2695, '', '添加优惠券', '', '', '', 'marketing/coupon/save_coupon', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-coupon-save_coupon', 0, '添加优惠券'),
(2699, 2695, '', '一键复制优惠券', '', '', '', 'marketing/coupon/copy/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-coupon-copy', 0, '一键复制优惠券'),
(2700, 2696, '', '已发布优惠券删除', '', '', '', 'marketing/coupon/released/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-coupon-released', 0, '已发布优惠券删除'),
(2701, 2697, '', '已发布优惠券领取记录', '', '', '', 'marketing/coupon/released/issue_log/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-coupon-released-issue_log', 0, '已发布优惠券领取记录'),
(2702, 2697, '', '会员领取记录', '', '', '', 'marketing/coupon/user', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-coupon-user', 0, '会员领取记录'),
(2713, 905, '', '添加积分商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/905', 3, '', 0, 'point-product-add', 0, '添加积分商品'),
(2714, 905, '', '编辑积分商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/905', 3, '', 0, 'point-product-edit', 0, '编辑积分商品'),
(2715, 905, '', '删除积分商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/905', 3, '', 0, 'point-product-delete', 0, '删除积分商品'),
(2716, 905, '', '积分商品状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/905', 3, '', 0, 'point-product-status', 0, '积分商品状态'),
(2717, 905, '', '兑换记录', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/905', 3, '', 0, 'point-product-record', 0, '兑换记录'),
(2718, 2713, '', '积分商品新增或编辑', '', '', '', 'marketing/integral/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral', 0, '积分商品新增或编辑'),
(2719, 2714, '', '积分商品新增或编辑', '', '', '', 'marketing/integral/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-6465e598dac7e', 0, '积分商品新增或编辑'),
(2720, 2714, '', '积分商品详情', '', '', '', 'marketing/integral/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-6465e598dac85', 0, '积分商品详情'),
(2721, 2715, '', '积分商品删除', '', '', '', 'marketing/integral/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-6465e5a7d7e0b', 0, '积分商品删除'),
(2722, 2716, '', '修改积分商品状态', '', '', '', 'marketing/integral/set_show/<id>/<is_show>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-set_show', 0, '修改积分商品状态'),
(2723, 912, '', '订单发货', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/912', 3, '', 0, 'point-order-send', 0, '订单发货'),
(2724, 912, '', '订单详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/912', 3, '', 0, 'point-order-info', 0, '订单详情'),
(2725, 912, '', '订单记录', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/912', 3, '', 0, 'point-order-record', 0, '订单记录'),
(2726, 912, '', '小票打印', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/912', 3, '', 0, 'point-order-print', 0, '小票打印'),
(2727, 912, '', '订单备注', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/912', 3, '', 0, 'point-order-mark', 0, '订单备注'),
(2728, 912, '', '确认收货', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/912', 3, '', 0, 'point-order-take', 0, '确认收货'),
(2729, 2723, '', '积分订单发送货', '', '', '', 'marketing/integral/order/delivery/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-delivery', 0, '积分订单发送货'),
(2730, 2723, '', '获取积分订单配送信息表单', '', '', '', 'marketing/integral/order/distribution/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-distribution', 0, '获取积分订单配送信息表单'),
(2731, 2723, '', '修改积分订单配送信息', '', '', '', 'marketing/integral/order/distribution/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-distribution', 0, '修改积分订单配送信息'),
(2732, 2723, '', '积分订单获取物流公司', '', '', '', 'marketing/integral/order/express_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-express_list', 0, '积分订单获取物流公司'),
(2733, 2723, '', '积分订单快递公司电子面单模版', '', '', '', 'marketing/integral/order/express/temp', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-express-temp', 0, '积分订单快递公司电子面单模版'),
(2734, 2723, '', '积分订单列表获取配送员', '', '', '', 'marketing/integral/order/delivery/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-delivery-list', 0, '积分订单列表获取配送员'),
(2735, 2723, '', '积分订单获取面单默认配置信息', '', '', '', 'marketing/integral/order/sheet_info', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-sheet_info', 0, '积分订单获取面单默认配置信息'),
(2736, 2723, '', '积分订单获取物流信息', '', '', '', 'marketing/integral/order/express/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-express', 0, '积分订单获取物流信息'),
(2737, 2724, '', '积分商城订单详情数据', '', '', '', 'marketing/integral/order/info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-info', 0, '积分商城订单详情数据'),
(2738, 2725, '', '获取积分订单状态', '', '', '', 'marketing/integral/order/status/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-status', 0, '获取积分订单状态'),
(2739, 2726, '', '打印积分订单', '', '', '', 'marketing/integral/order/print/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-print', 0, '打印积分订单'),
(2740, 2727, '', '积分记录列表备注', '', '', '', 'marketing/point_record/remark/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-point_record-remark', 0, '积分记录列表备注'),
(2741, 2728, '', '积分订单确认收货', '', '', '', 'marketing/integral/order/take/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-integral-order-take', 0, '积分订单确认收货'),
(2742, 1001, '', '积分记录备注', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/34/1001', 3, '', 0, 'point-record', 0, '积分记录备注'),
(2743, 2742, '', '积分记录列表备注', '', '', '', 'marketing/point_record/remark/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-point_record-remark-6465fd1a75f76', 0, '积分记录列表备注'),
(2744, 1018, '', '抽奖保存', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/909/1018', 3, '', 0, 'lottery-save', 0, '抽奖保存'),
(2745, 2744, '', '修改抽奖活动数据', '', '', '', 'marketing/lottery/edit/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-lottery-edit', 0, '修改抽奖活动数据'),
(2746, 1019, '', '中奖发货', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/909/1019', 3, '', 0, 'lottery-send', 0, '中奖发货'),
(2747, 1019, '', '中奖备注', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/909/1019', 3, '', 0, 'lottery-mark', 0, '中奖备注'),
(2748, 2746, '', '抽奖中奖发货、备注处理', '', '', '', 'marketing/lottery/record/deliver', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-lottery-record-deliver', 0, '抽奖中奖发货、备注处理'),
(2749, 2747, '', '抽奖中奖发货、备注处理', '', '', '', 'marketing/lottery/record/deliver', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-lottery-record-deliver-6465fe30cf185', 0, '抽奖中奖发货、备注处理'),
(2750, 74, '', '添加砍价', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/31/74', 3, '', 0, 'bargain-add', 0, '添加砍价'),
(2751, 74, '', '导出砍价', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/31/74', 3, '', 0, 'bargain-export', 0, '导出砍价'),
(2752, 74, '', '编辑砍价', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/31/74', 3, '', 0, 'bargain-edit', 0, '编辑砍价'),
(2753, 74, '', '删除砍价', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/31/74', 3, '', 0, 'bargain-delete', 0, '删除砍价'),
(2754, 74, '', '砍价统计', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/31/74', 3, '', 0, 'bargain-statistics', 0, '砍价统计'),
(2755, 2750, '', '新增或编辑砍价商品', '', '', '', 'marketing/bargain/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-bargain', 0, '新增或编辑砍价商品'),
(2756, 2750, '', '砍价商品详情', '', '', '', 'marketing/bargain/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-bargain', 0, '砍价商品详情'),
(2757, 2750, '', '修改砍价商品状态', '', '', '', 'marketing/bargain/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-bargain-set_status', 0, '修改砍价商品状态'),
(2758, 2751, '', '砍价商品列表导出', '', '', '', 'export/bargain_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'export-bargain_list', 0, '砍价商品列表导出'),
(2759, 2752, '', '砍价商品详情', '', '', '', 'marketing/bargain/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-bargain-6465ff00a7c2b', 0, '砍价商品详情'),
(2760, 2752, '', '新增或编辑砍价商品', '', '', '', 'marketing/bargain/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-bargain-6465ff00a7c33', 0, '新增或编辑砍价商品'),
(2761, 2752, '', '修改砍价商品状态', '', '', '', 'marketing/bargain/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-bargain-set_status-6465ff00a7c39', 0, '修改砍价商品状态'),
(2762, 2753, '', '删除砍价商品', '', '', '', 'marketing/bargain/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-bargain-6465ff10afa4e', 0, '删除砍价商品'),
(2763, 755, '', '查看详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/31/755', 3, '', 0, 'bargain-list-info', 0, '查看详情'),
(2764, 2763, '', '参与砍价列表', '', '', '', 'marketing/bargain_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-bargain_list', 0, '参与砍价列表'),
(2765, 2763, '', '砍价人列表', '', '', '', 'marketing/bargain_list_info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-bargain_list_info', 0, '砍价人列表'),
(2766, 75, '', '添加拼团', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/32/75', 3, '', 0, 'combination-add', 0, '添加拼团'),
(2767, 75, '', '导出拼团', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/32/75', 3, '', 0, 'combination-export', 0, '导出拼团'),
(2768, 75, '', '编辑拼团', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/32/75', 3, '', 0, 'combination-edit', 0, '编辑拼团'),
(2769, 75, '', '删除拼团', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/32/75', 3, '', 0, 'combination-delete', 0, '删除拼团'),
(2770, 75, '', '拼团统计', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/32/75', 3, '', 0, 'combination-statistics', 0, '拼团统计'),
(2771, 2766, '', '拼团商品详情', '', '', '', 'marketing/combination/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-combination', 0, '拼团商品详情'),
(2772, 2766, '', '新增或编辑拼团商品', '', '', '', 'marketing/combination/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-combination', 0, '新增或编辑拼团商品'),
(2773, 2766, '', '修改拼团商品状态', '', '', '', 'marketing/combination/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-combination-set_status', 0, '修改拼团商品状态'),
(2774, 2767, '', '拼团商品列表导出', '', '', '', 'export/combination_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'export-combination_list', 0, '拼团商品列表导出'),
(2775, 2768, '', '新增或编辑拼团商品', '', '', '', 'marketing/combination/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-combination-6466cb2165ea1', 0, '新增或编辑拼团商品'),
(2776, 2768, '', '修改拼团商品状态', '', '', '', 'marketing/combination/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-combination-set_status-6466cb2165ea7', 0, '修改拼团商品状态'),
(2777, 2768, '', '拼团商品详情', '', '', '', 'marketing/combination/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-combination-6466cb2165eab', 0, '拼团商品详情'),
(2778, 2769, '', '删除拼团商品', '', '', '', 'marketing/combination/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-combination-6466cb2e9b9a8', 0, '删除拼团商品'),
(2779, 76, '', '拼团详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/32/76', 3, '', 0, 'combination-list-info', 0, '拼团详情'),
(2780, 2779, '', '参与拼团列表', '', '', '', 'marketing/combination/combine/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-combination-combine-list', 0, '参与拼团列表'),
(2781, 2779, '', '拼团人列表', '', '', '', 'marketing/combination/order_pink/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-combination-order_pink', 0, '拼团人列表'),
(2782, 77, '', '添加秒杀', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/33/77', 3, '', 0, 'seckill-add', 0, '添加秒杀'),
(2783, 77, '', '导出秒杀', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/33/77', 3, '', 0, 'seckill-export', 0, '导出秒杀'),
(2784, 77, '', '编辑秒杀', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/33/77', 3, '', 0, 'seckill-edit', 0, '编辑秒杀'),
(2785, 77, '', '删除秒杀', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/33/77', 3, '', 0, 'seckill-delete', 0, '删除秒杀'),
(2786, 77, '', '秒杀统计', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/33/77', 3, '', 0, 'seckill-statistics', 0, '秒杀统计'),
(2787, 2782, '', '秒杀商品详情', '', '', '', 'marketing/seckill/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill', 0, '秒杀商品详情'),
(2788, 2782, '', '新增或编辑秒杀商品', '', '', '', 'marketing/seckill/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill', 0, '新增或编辑秒杀商品'),
(2789, 2782, '', '修改秒杀商品状态', '', '', '', 'marketing/seckill/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill-set_status', 0, '修改秒杀商品状态'),
(2790, 2783, '', '秒杀商品列表导出', '', '', '', 'export/seckill_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'export-seckill_list', 0, '秒杀商品列表导出'),
(2791, 2784, '', '秒杀商品详情', '', '', '', 'marketing/seckill/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill-6466d624dd816', 0, '秒杀商品详情'),
(2792, 2784, '', '新增或编辑秒杀商品', '', '', '', 'marketing/seckill/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill-6466d624dd820', 0, '新增或编辑秒杀商品'),
(2793, 2784, '', '修改秒杀商品状态', '', '', '', 'marketing/seckill/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill-set_status-6466d624dd826', 0, '修改秒杀商品状态'),
(2794, 2785, '', '删除秒杀商品', '', '', '', 'marketing/seckill/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill-6466d630e8c12', 0, '删除秒杀商品'),
(2795, 2786, '', '秒杀参与人', '', '', '', 'marketing/seckill/statistics/order/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill-statistics-order', 0, '秒杀参与人'),
(2796, 2786, '', '秒杀统计', '', '', '', 'marketing/seckill/statistics/head/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill-statistics-head', 0, '秒杀统计'),
(2797, 2786, '', '秒杀参与人', '', '', '', 'marketing/seckill/statistics/people/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'marketing-seckill-statistics-people', 0, '秒杀参与人'),
(2798, 751, '', '添加会员类型', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/751', 3, '', 0, 'member-add', 0, '添加会员类型'),
(2799, 751, '', '编辑会员类型', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/751', 3, '', 0, 'member-edit', 0, '编辑会员类型'),
(2800, 751, '', '删除会员类型', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/751', 3, '', 0, 'member-delete', 0, '删除会员类型'),
(2801, 751, '', '会员类型状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/751', 3, '', 0, 'member-status', 0, '会员类型状态'),
(2802, 2798, '', '会员类型列表', '', '', '', 'user/member/ship', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member-ship', 0, '会员类型列表'),
(2803, 2798, '', '会员类型修改状态', '', '', '', 'user/member_ship/set_ship_status', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_ship-set_ship_status', 0, '会员类型修改状态'),
(2804, 2798, '', '会员卡类型编辑', '', '', '', 'user/member_ship/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_ship-save', 0, '会员卡类型编辑'),
(2805, 2799, '', '会员类型列表', '', '', '', 'user/member/ship', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member-ship-6466db17503ca', 0, '会员类型列表'),
(2806, 2799, '', '会员类型修改状态', '', '', '', 'user/member_ship/set_ship_status', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_ship-set_ship_status-6466db17503d2', 0, '会员类型修改状态'),
(2807, 2799, '', '会员类型删除', '', '', '', 'user/member_ship/delete/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_ship-delete', 0, '会员类型删除'),
(2808, 2799, '', '会员卡类型编辑', '', '', '', 'user/member_ship/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_ship-save-6466db17503dc', 0, '会员卡类型编辑'),
(2809, 2800, '', '会员类型删除', '', '', '', 'user/member_ship/delete/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_ship-delete-6466db246a1e5', 0, '会员类型删除'),
(2810, 2801, '', '会员类型修改状态', '', '', '', 'user/member_ship/set_ship_status', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_ship-set_ship_status-6466db33cfbdf', 0, '会员类型修改状态'),
(2811, 765, '', '编辑会员权益', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/765', 3, '', 0, 'member-right-edit', 0, '编辑会员权益'),
(2812, 765, '', '会员权益状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/765', 3, '', 0, 'member-right-status', 0, '会员权益状态'),
(2813, 2811, '', '会员权益修改', '', '', '', 'user/member_right/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_right-save', 0, '会员权益修改'),
(2814, 2812, '', '会员权益修改', '', '', '', 'user/member_right/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_right-save-6466e10aa77e5', 0, '会员权益修改'),
(2815, 762, '', '添加批次', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/762', 3, '', 0, 'member-card-add', 0, '添加批次'),
(2816, 762, '', '下载二维码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/762', 3, '', 0, 'member-card-down-qrcode', 0, '下载二维码'),
(2817, 762, '', '编辑批次名', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/762', 3, '', 0, 'member-card-edit', 0, '编辑批次名'),
(2818, 762, '', '查看卡列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/762', 3, '', 0, 'member-card-scan', 0, '查看卡列表'),
(2819, 762, '', '导出会员卡密', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/731/762', 3, '', 0, 'member-card-export', 0, '导出会员卡密'),
(2820, 2815, '', '添加会员卡批次', '', '', '', 'user/member_batch/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_batch-save', 0, '添加会员卡批次'),
(2821, 2815, '', '会员卡列表', '', '', '', 'user/member_card/index/<card_batch_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_card-index', 0, '会员卡列表'),
(2822, 2815, '', '会员卡批次快速修改', '', '', '', 'user/member_batch/set_value/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_batch-set_value', 0, '会员卡批次快速修改'),
(2823, 2815, '', '会员卡修改状态', '', '', '', 'user/member_card/set_status', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_card-set_status', 0, '会员卡修改状态'),
(2824, 2816, '', '兑换会员卡二维码', '', '', '', 'user/member_scan', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_scan', 0, '兑换会员卡二维码'),
(2825, 2817, '', '会员卡批次快速修改', '', '', '', 'user/member_batch/set_value/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_batch-set_value-6466f21a4c295', 0, '会员卡批次快速修改'),
(2826, 2817, '', '会员卡修改状态', '', '', '', 'user/member_card/set_status', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_card-set_status-6466f21a4c29e', 0, '会员卡修改状态'),
(2827, 2818, '', '会员卡列表', '', '', '', 'user/member_card/index/<card_batch_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'user-member_card-index-6466f23950641', 0, '会员卡列表'),
(2828, 2819, '', '会员卡导出', '', '', '', 'export/member_card/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'export-member_card', 0, '会员卡导出'),
(2829, 687, '', '添加直播间', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/687', 3, '', 0, 'live-room-add', 0, '添加直播间'),
(2830, 687, '', '同步直播间', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/687', 3, '', 0, 'live-room-sync', 0, '同步直播间'),
(2831, 687, '', '直播间详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/687', 3, '', 0, 'live-room-info', 0, '直播间详情'),
(2832, 687, '', '删除直播间', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/687', 3, '', 0, 'live-room-delete', 0, '删除直播间'),
(2833, 687, '', '直播间状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/687', 3, '', 0, 'live-room-status', 0, '直播间状态'),
(2834, 2829, '', '直播间添加', '', '', '', 'live/room/add', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-room-add-64671181de1c7', 0, '直播间添加'),
(2835, 2829, '', '直播间详情', '', '', '', 'live/room/detail/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-room-detail', 0, '直播间详情'),
(2836, 2830, '', '同步直播间状态', '', '', '', 'live/room/syncRoom', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-room-syncRoom', 0, '同步直播间状态'),
(2837, 2831, '', '直播间详情', '', '', '', 'live/room/detail/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-room-detail-646711a2b8bd3', 0, '直播间详情'),
(2838, 2832, '', '删除直播间', '', '', '', 'live/room/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-room-del', 0, '删除直播间'),
(2839, 2833, '', '设置直播间是否显示', '', '', '', 'live/room/set_show/<id>/<is_show>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-room-set_show', 0, '设置直播间是否显示'),
(2840, 687, '', '直播间添加商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/687', 3, '', 0, 'live-room-add-product', 0, '直播间添加商品'),
(2841, 2840, '', '直播间添加商品', '', '', '', 'live/room/add_goods', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-room-add_goods', 0, '直播间添加商品'),
(2842, 688, '', '添加直播商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/688', 3, '', 0, 'live-product-add', 0, '添加直播商品'),
(2843, 688, '', '直播商品详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/688', 3, '', 0, 'live-product-info', 0, '直播商品详情'),
(2844, 688, '', '删除直播商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/688', 3, '', 0, 'live-product-delete', 0, '删除直播商品'),
(2845, 2842, '', '生成直播商品', '', '', '', 'live/goods/create', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-goods-create', 0, '生成直播商品'),
(2846, 2842, '', '添加修改直播商品', '', '', '', 'live/goods/add', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-goods-add', 0, '添加修改直播商品'),
(2847, 2843, '', '直播商品详情', '', '', '', 'live/goods/detail/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-goods-detail', 0, '直播商品详情'),
(2848, 2844, '', '删除直播商品', '', '', '', 'live/goods/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-goods-del', 0, '删除直播商品'),
(2849, 689, '', '添加主播', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/689', 3, '', 0, 'live-anchor-add', 0, '添加主播'),
(2850, 689, '', '修改主播', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/689', 3, '', 0, 'live-anchor-edit', 0, '修改主播'),
(2851, 689, '', '删除主播', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/686/689', 3, '', 0, 'live-anchor-delete', 0, '删除主播'),
(2852, 2849, '', '保存主播数据', '', '', '', 'live/anchor/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-anchor-save', 0, '保存主播数据'),
(2853, 2849, '', '添加修改主播表单', '', '', '', 'live/anchor/add/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-anchor-add-64671b7155864', 0, '添加修改主播表单'),
(2854, 2850, '', '保存主播数据', '', '', '', 'live/anchor/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-anchor-save-64671b7b408c3', 0, '保存主播数据'),
(2855, 2850, '', '添加修改主播表单', '', '', '', 'live/anchor/add/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-anchor-add-64671b7b408ca', 0, '添加修改主播表单'),
(2856, 2851, '', '删除主播', '', '', '', 'live/anchor/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'live-anchor-del', 0, '删除主播'),
(2857, 1023, '', '添加分组', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/1023', 3, '', 0, 'channel-code-add', 0, '添加分组'),
(2858, 1023, '', '新建二维码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/1023', 3, '', 0, 'channel-qrcode-add', 0, '新建二维码'),
(2859, 1023, '', '编辑二维码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/1023', 3, '', 0, 'channel-qrcode-edit', 0, '编辑二维码'),
(2860, 1023, '', '删除二维码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/1023', 3, '', 0, 'channel-qrcode-delete', 0, '删除二维码'),
(2861, 1023, '', '下载二维码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/1023', 3, '', 0, 'channel-qrcode-down', 0, '下载二维码'),
(2862, 1023, '', '二维码统计', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/1023', 3, '', 0, 'channel-qrcode-statistics', 0, '二维码统计'),
(2863, 1023, '', '扫码用户列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '27/1023', 3, '', 0, 'channel-qrcode-user', 0, '扫码用户列表'),
(2864, 2857, '', '渠道码分类列表', '', '', '', 'app/wechat_qrcode/cate/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-cate-list', 0, '渠道码分类列表'),
(2865, 2857, '', '渠道码分类添加编辑表单', '', '', '', 'app/wechat_qrcode/cate/create/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-cate-create', 0, '渠道码分类添加编辑表单'),
(2866, 2857, '', '渠道码分类保存', '', '', '', 'app/wechat_qrcode/cate/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-cate-save', 0, '渠道码分类保存'),
(2867, 2857, '', '渠道码分类删除', '', '', '', 'app/wechat_qrcode/cate/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-cate-del', 0, '渠道码分类删除'),
(2868, 2858, '', '保存渠道码', '', '', '', 'app/wechat_qrcode/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-save', 0, '保存渠道码'),
(2869, 2858, '', '渠道码详情', '', '', '', 'app/wechat_qrcode/info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-info', 0, '渠道码详情'),
(2870, 2859, '', '渠道码列表', '', '', '', 'app/wechat_qrcode/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-list', 0, '渠道码列表'),
(2871, 2859, '', '渠道码详情', '', '', '', 'app/wechat_qrcode/info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-info-64671d713f4e3', 0, '渠道码详情'),
(2872, 2859, '', '保存渠道码', '', '', '', 'app/wechat_qrcode/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-save-64671d713f4e9', 0, '保存渠道码'),
(2873, 2860, '', '删除渠道码', '', '', '', 'app/wechat_qrcode/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-del', 0, '删除渠道码'),
(2874, 2862, '', '渠道码统计', '', '', '', 'app/wechat_qrcode/statistic/<qid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-statistic', 0, '渠道码统计'),
(2875, 2863, '', '渠道码用户列表', '', '', '', 'app/wechat_qrcode/user_list/<qid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat_qrcode-user_list', 0, '渠道码用户列表'),
(2876, 29, '', '推广人列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/29', 3, '', 0, 'spread-user-list', 0, '推广人列表'),
(2877, 29, '', '推广订单', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/29', 3, '', 0, 'spread-order-list', 0, '推广订单'),
(2878, 29, '', '推广二维码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/29', 3, '', 0, 'spread-qrcode', 0, '推广二维码'),
(2879, 29, '', '修改上级推广人', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/29', 3, '', 0, 'spread-edit-spread', 0, '修改上级推广人'),
(2880, 29, '', '删除上级推广人', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/29', 3, '', 0, 'spread-delete-spread', 0, '删除上级推广人'),
(2881, 29, '', '取消推广资格', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/29', 3, '', 0, 'spread-cancel', 0, '取消推广资格'),
(2882, 29, '', '修改分销等级', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/29', 3, '', 0, 'spread-edit-level', 0, '修改分销等级'),
(2883, 2876, '', '推广人列表', '', '', '', 'agent/stair', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-stair', 0, '推广人列表'),
(2884, 2877, '', '推广订单列表', '', '', '', 'agent/stair/order', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-stair-order', 0, '推广订单列表'),
(2885, 2878, '', '查看小程序推广二维码', '', '', '', 'agent/look_xcx_code', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-look_xcx_code', 0, '查看小程序推广二维码'),
(2886, 2878, '', '查看H5推广二维码', '', '', '', 'agent/look_h5_code', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-look_h5_code', 0, '查看H5推广二维码'),
(2887, 2878, '', '查看公众号推广二维码', '', '', '', 'agent/look_code', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-look_code', 0, '查看公众号推广二维码'),
(2888, 2879, '', '修改上级推广人', '', '', '', 'agent/spread', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-spread-64671e9e67c6d', 0, '修改上级推广人'),
(2889, 2880, '', '清除上级推广人', '', '', '', 'agent/stair/delete_spread/<uid>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-stair-delete_spread', 0, '清除上级推广人'),
(2890, 896, '', '添加分销等级', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/896', 3, '', 0, 'spread-level-add', 0, '添加分销等级'),
(2891, 896, '', '修改分销等级', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/896', 3, '', 0, 'spread-level-edit', 0, '修改分销等级'),
(2892, 896, '', '删除分销等级', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/896', 3, '', 0, 'spread-level-delete', 0, '删除分销等级'),
(2893, 896, '', '分销等级任务', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/896', 3, '', 0, 'spread-level-task', 0, '分销等级任务'),
(2894, 2890, '', '获取分销员等级表单', '', '', '', 'agent/level/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level-create', 0, '获取分销员等级表单'),
(2895, 2890, '', '保存分销员等级', '', '', '', 'agent/level', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level', 0, '保存分销员等级'),
(2896, 2891, '', '修改分销员等级', '', '', '', 'agent/level/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level-64671f51e1c5d', 0, '修改分销员等级'),
(2897, 2891, '', '获取修改分销员等级表单', '', '', '', 'agent/level/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level-edit', 0, '获取修改分销员等级表单'),
(2898, 2891, '', '修改分销等级状态', '', '', '', 'agent/level/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level-set_status', 0, '修改分销等级状态'),
(2899, 2892, '', '删除分销员等级', '', '', '', 'agent/level/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level-64671f5ca12b7', 0, '删除分销员等级'),
(2900, 2893, '', '获取分销员等级任务列表', '', '', '', 'agent/level_task', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level_task', 0, '获取分销员等级任务列表'),
(2901, 2893, '', '保存分销员等级任务', '', '', '', 'agent/level_task', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level_task', 0, '保存分销员等级任务'),
(2902, 2893, '', '获取分销员等级任务表单', '', '', '', 'agent/level_task/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level_task-create', 0, '获取分销员等级任务表单'),
(2903, 2893, '', '获取修改分销员等级任务表单', '', '', '', 'agent/level_task/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level_task-edit', 0, '获取修改分销员等级任务表单'),
(2904, 2893, '', '修改分销员等级任务', '', '', '', 'agent/level_task/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level_task', 0, '修改分销员等级任务'),
(2905, 2893, '', '删除分销员等级任务', '', '', '', 'agent/level_task/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level_task', 0, '删除分销员等级任务'),
(2906, 2893, '', '修改分销等级任务状态', '', '', '', 'agent/level_task/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-level_task-set_status', 0, '修改分销等级任务状态'),
(2907, 1014, '', '添加事业部', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/1013/1014', 3, '', 0, 'division-add', 0, '添加事业部'),
(2908, 1014, '', '修改事业部', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/1013/1014', 3, '', 0, 'division-edit', 0, '修改事业部'),
(2909, 1014, '', '删除事业部', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/1013/1014', 3, '', 0, 'division-delete', 0, '删除事业部'),
(2910, 1014, '', '查看代理商', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/1013/1014', 3, '', 0, 'division-scan-agent', 0, '查看代理商'),
(2911, 2907, '', '添加事业部', '', '', '', 'agent/division/create/<uid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-create', 0, '添加事业部'),
(2912, 2907, '', '事业部保存', '', '', '', 'agent/division/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-save', 0, '事业部保存'),
(2913, 2908, '', '添加事业部', '', '', '', 'agent/division/create/<uid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-create-646720ac60991', 0, '添加事业部'),
(2914, 2908, '', '事业部保存', '', '', '', 'agent/division/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-save-646720ac6099a', 0, '事业部保存'),
(2915, 2909, '', '删除代理商', '', '', '', 'agent/division/del/<type>/<uid>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-del', 0, '删除代理商'),
(2916, 2910, '', '下级列表', '', '', '', 'agent/division/down_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-down_list', 0, '下级列表'),
(2917, 1015, '', '添加代理商', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/1013/1015', 3, '', 0, 'division-agent-add', 0, '添加代理商'),
(2918, 1015, '', '修改代理商', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/1013/1015', 3, '', 0, 'division-agent-edit', 0, '修改代理商'),
(2919, 1015, '', '删除代理商', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/1013/1015', 3, '', 0, 'division-agent-delete', 0, '删除代理商'),
(2920, 1015, '', '查看员工', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/1013/1015', 3, '', 0, 'division-agent-staff', 0, '查看员工'),
(2921, 2917, '', '添加事业部', '', '', '', 'agent/division/agent/create/<uid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-agent-create', 0, '添加事业部'),
(2922, 2917, '', '事业部保存', '', '', '', 'agent/division/agent/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-agent-save', 0, '事业部保存'),
(2923, 2918, '', '添加事业部', '', '', '', 'agent/division/agent/create/<uid>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-agent-create-64672134e5497', 0, '添加事业部'),
(2924, 2918, '', '事业部保存', '', '', '', 'agent/division/agent/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-agent-save-64672134e54a1', 0, '事业部保存'),
(2925, 2919, '', '删除代理商', '', '', '', 'agent/division/del/<type>/<uid>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-del-64672140f09bd', 0, '删除代理商'),
(2926, 2920, '', '下级列表', '', '', '', 'agent/division/down_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-down_list-6467214aa534c', 0, '下级列表'),
(2927, 1016, '', '审核代理商', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '26/1013/1016', 3, '', 0, 'division-agent-apply', 0, '审核代理商'),
(2928, 2927, '', '代理商申请列表', '', '', '', 'agent/division/agent_apply/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-agent_apply-list', 0, '代理商申请列表'),
(2929, 2927, '', '审核表单', '', '', '', 'agent/division/examine_apply/<id>/<type>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-examine_apply', 0, '审核表单'),
(2930, 2927, '', '提交审核', '', '', '', 'agent/division/apply_agent/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-apply_agent-save', 0, '提交审核'),
(2931, 2927, '', '删除审核', '', '', '', 'agent/division/del_apply/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'agent-division-del_apply', 0, '删除审核'),
(2932, 678, '', '添加客服', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/678', 3, '', 0, 'service-add', 0, '添加客服'),
(2933, 678, '', '编辑客服', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/678', 3, '', 0, 'service-edit', 0, '编辑客服'),
(2934, 678, '', '删除客服', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/678', 3, '', 0, 'service-delete', 0, '删除客服'),
(2935, 678, '', '进入工作台', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/678', 3, '', 0, 'service-in', 0, '进入工作台'),
(2936, 2932, '', '添加客服表单', '', '', '', 'app/wechat/kefu/add', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu-add', 0, '添加客服表单'),
(2937, 2932, '', '修改客服表单', '', '', '', 'app/wechat/kefu/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu-edit', 0, '修改客服表单'),
(2938, 2932, '', '添加客服', '', '', '', 'app/wechat/kefu', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu', 0, '添加客服'),
(2939, 2932, '', '修改客服', '', '', '', 'app/wechat/kefu/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu', 0, '修改客服'),
(2940, 2932, '', '修改客服状态', '', '', '', 'app/wechat/kefu/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu-set_status', 0, '修改客服状态'),
(2941, 2933, '', '修改客服表单', '', '', '', 'app/wechat/kefu/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu-edit-646723c8147fd', 0, '修改客服表单'),
(2942, 2933, '', '修改客服', '', '', '', 'app/wechat/kefu/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu-646723c814806', 0, '修改客服'),
(2943, 2933, '', '修改客服状态', '', '', '', 'app/wechat/kefu/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu-set_status-646723c81480d', 0, '修改客服状态'),
(2944, 2934, '', '删除客服', '', '', '', 'app/wechat/kefu/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu-646723d59c121', 0, '删除客服'),
(2945, 2935, '', '客服登录', '', '', '', 'app/wechat/kefu/login/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-kefu-login', 0, '客服登录'),
(2946, 679, '', '添加分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/679', 3, '', 0, 'service-speechcraft-cate-add', 0, '添加分类'),
(2947, 679, '', '添加话术', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/679', 3, '', 0, 'service-speechcraft-add', 0, '添加话术'),
(2948, 679, '', '编辑话术', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/679', 3, '', 0, 'service-speechcraft-edit', 0, '编辑话术'),
(2949, 679, '', '删除话术', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/679', 3, '', 0, 'service-speechcraft-delete', 0, '删除话术'),
(2950, 2946, '', '获取客服话术分类列表', '', '', '', 'app/wechat/speechcraftcate', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraftcate', 0, '获取客服话术分类列表'),
(2951, 2946, '', '保存客服话术分类', '', '', '', 'app/wechat/speechcraftcate', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraftcate', 0, '保存客服话术分类'),
(2952, 2946, '', '获取客服话术分类表单', '', '', '', 'app/wechat/speechcraftcate/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraftcate-create', 0, '获取客服话术分类表单'),
(2953, 2946, '', '获取修改客服话术分类表单', '', '', '', 'app/wechat/speechcraftcate/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraftcate-edit', 0, '获取修改客服话术分类表单'),
(2954, 2946, '', '修改客服话术分类', '', '', '', 'app/wechat/speechcraftcate/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraftcate', 0, '修改客服话术分类'),
(2955, 2947, '', '保存客服话术', '', '', '', 'app/wechat/speechcraft', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraft', 0, '保存客服话术'),
(2956, 2947, '', '获取客服话术表单', '', '', '', 'app/wechat/speechcraft/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraft-create', 0, '获取客服话术表单'),
(2957, 2948, '', '获取修改客服话术表单', '', '', '', 'app/wechat/speechcraft/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraft-edit', 0, '获取修改客服话术表单'),
(2958, 2948, '', '修改客服话术', '', '', '', 'app/wechat/speechcraft/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraft-646726ba5bc91', 0, '修改客服话术'),
(2959, 2949, '', '删除客服话术', '', '', '', 'app/wechat/speechcraft/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-speechcraft-6467272d0c6c4', 0, '删除客服话术'),
(2960, 738, '', '回复留言', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/738', 3, '', 0, 'service-feedback-reply', 0, '回复留言'),
(2961, 738, '', '删除留言', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '165/738', 3, '', 0, 'service-feedback-delete', 0, '删除留言'),
(2962, 2960, '', '获取修改用户反馈表单', '', '', '', 'app/feedback/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-feedback-edit', 0, '获取修改用户反馈表单'),
(2963, 2960, '', '修改用户反馈', '', '', '', 'app/feedback/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-feedback', 0, '修改用户反馈'),
(2964, 2961, '', '删除用户反馈', '', '', '', 'app/feedback/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-feedback-64672949545ff', 0, '删除用户反馈'),
(2965, 39, '', '审核提现', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '35/36/39', 3, '', 0, 'extract-status', 0, '审核提现'),
(2966, 39, '', '编辑提现', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '35/36/39', 3, '', 0, 'extract-edit', 0, '编辑提现'),
(2967, 2965, '', '拒绝提现申请', '', '', '', 'finance/extract/refuse/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'finance-extract-refuse', 0, '拒绝提现申请'),
(2968, 2965, '', '通过提现申请', '', '', '', 'finance/extract/adopt/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'finance-extract-adopt', 0, '通过提现申请'),
(2969, 2966, '', '提现记录修改', '', '', '', 'finance/extract/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'finance-extract', 0, '提现记录修改'),
(2970, 2966, '', '提现记录修改表单', '', '', '', 'finance/extract/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'finance-extract-edit', 0, '提现记录修改表单'),
(2971, 767, '', '编辑发票', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '35/36/767', 3, '', 0, 'invoice-edit', 0, '编辑发票'),
(2972, 767, '', '订单信息', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '35/36/767', 3, '', 0, 'invoice-order-info', 0, '订单信息'),
(2973, 2971, '', '申请发票列表', '', '', '', 'order/invoice/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-invoice-list', 0, '申请发票列表'),
(2974, 2971, '', '设置发票状态', '', '', '', 'order/invoice/set/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-invoice-set', 0, '设置发票状态'),
(2975, 2972, '', '开票订单详情', '', '', '', 'order/invoice_order_info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-invoice_order_info', 0, '开票订单详情'),
(2976, 40, '', '充值删除', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '35/37/40', 3, '', 0, 'recharge-delete', 0, '充值删除'),
(2977, 40, '', '充值退款', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '35/37/40', 3, '', 0, 'recharge-refund', 0, '充值退款'),
(2978, 2976, '', '删除充值记录', '', '', '', 'finance/recharge/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'finance-recharge', 0, '删除充值记录'),
(2979, 2976, '', '充值记录列表', '', '', '', 'finance/recharge', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'finance-recharge', 0, '充值记录列表'),
(2980, 2977, '', '充值退款', '', '', '', 'finance/recharge/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'finance-recharge-64672c72c2273', 0, '充值退款'),
(2981, 2977, '', '充值退款表单', '', '', '', 'finance/recharge/<id>/refund_edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'finance-recharge-refund_edit', 0, '充值退款表单'),
(2982, 998, '', '资金流水备注', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '35/37/998', 3, '', 0, 'capital-flow-mark', 0, '资金流水备注'),
(2983, 2982, '', '设置备注', '', '', '', 'statistic/flow/set_mark/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'statistic-flow-set_mark', 0, '设置备注'),
(2984, 999, '', '账单详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '35/37/999', 3, '', 0, 'billing-info', 0, '账单详情'),
(2985, 999, '', '下载账单', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '35/37/999', 3, '', 0, 'billing-down', 0, '下载账单'),
(2986, 2984, '', '账单记录', '', '', '', 'statistic/flow/get_record', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'statistic-flow-get_record', 0, '账单记录'),
(2987, 2985, '', '资金流水', '', '', '', 'statistic/flow/get_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'statistic-flow-get_list', 0, '资金流水'),
(2988, 44, '', '添加文章', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '43/44', 3, '', 0, 'cms-add', 0, '添加文章'),
(2989, 44, '', '编辑文章', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '43/44', 3, '', 0, 'cms-edit', 0, '编辑文章'),
(2990, 44, '', '删除文章', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '43/44', 3, '', 0, 'cms-delete', 0, '删除文章'),
(2991, 44, '', '关联商品', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '43/44', 3, '', 0, 'cms-product', 0, '关联商品'),
(2992, 2988, '', '保存文章', '', '', '', 'cms/cms', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-cms', 0, '保存文章'),
(2993, 2988, '', '获取文章表单', '', '', '', 'cms/cms/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-cms-create', 0, '获取文章表单'),
(2994, 2989, '', '获取修改文章表单', '', '', '', 'cms/cms/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-cms-edit', 0, '获取修改文章表单'),
(2995, 2989, '', '获取文章详细信息', '', '', '', 'cms/cms/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-cms-64673315dd264', 0, '获取文章详细信息'),
(2996, 2989, '', '修改文章', '', '', '', 'cms/cms/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-cms-64673315dd287', 0, '修改文章'),
(2997, 2990, '', '删除文章', '', '', '', 'cms/cms/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-cms-6467331dc244e', 0, '删除文章'),
(2998, 2991, '', '取消文章关联商品', '', '', '', 'cms/cms/unrelation/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-cms-unrelation', 0, '取消文章关联商品'),
(2999, 2991, '', '文章关联商品', '', '', '', 'cms/cms/relation/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-cms-relation', 0, '文章关联商品'),
(3000, 45, '', '添加分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '43/45', 3, '', 0, 'cms-cate-add', 0, '添加分类'),
(3001, 45, '', '修改分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '43/45', 3, '', 0, 'cms-cate-edit', 0, '修改分类'),
(3002, 45, '', '删除分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '43/45', 3, '', 0, 'cms-cate-delete', 0, '删除分类'),
(3003, 45, '', '查看文章', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '43/45', 3, '', 0, 'cms-cate-cms', 0, '查看文章'),
(3004, 3000, '', '保存文章分类', '', '', '', 'cms/category', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-category', 0, '保存文章分类'),
(3005, 3000, '', '获取文章分类表单', '', '', '', 'cms/category/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-category-create', 0, '获取文章分类表单'),
(3006, 3001, '', '修改文章分类', '', '', '', 'cms/category/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-category-646733862d224', 0, '修改文章分类'),
(3007, 3001, '', '获取修改文章分类表单', '', '', '', 'cms/category/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-category-edit', 0, '获取修改文章分类表单'),
(3008, 3002, '', '删除文章分类', '', '', '', 'cms/category/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'cms-category-646733a5e8994', 0, '删除文章分类'),
(3009, 657, '', '商城首页', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/657', 3, '', 0, 'pages-diy-index', 0, '商城首页'),
(3010, 657, '', '商品分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/657', 3, '', 0, 'pages-diy-cate', 0, '商品分类'),
(3011, 657, '', '个人中心', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/657', 3, '', 0, 'pages-diy-user', 0, '个人中心'),
(3012, 3009, '', '添加专题页', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/657/3009', 3, '', 0, 'pages-diy-add', 0, '添加专题页'),
(3013, 3009, '', '编辑页面', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/657/3009', 3, '', 0, 'pages-diy-edit', 0, '编辑页面'),
(3014, 3009, '', '删除页面', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/657/3009', 3, '', 0, 'pages-diy-delete', 0, '删除页面'),
(3015, 3009, '', '设为首页', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/657/3009', 3, '', 0, 'pages-diy-status', 0, '设为首页'),
(3016, 3012, '', '添加DIY模板', '', '', '', 'diy/save/<id?>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-save', 0, '添加DIY模板'),
(3017, 3012, '', '添加DIY模板', '', '', '', 'diy/diy_save/<id?>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-diy_save', 0, '添加DIY模板'),
(3018, 3012, '', '获取前端页面路径', '', '', '', 'diy/get_url', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_url', 0, '获取前端页面路径'),
(3019, 3012, '', '获取商品分类', '', '', '', 'diy/get_category', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_category', 0, '获取商品分类'),
(3020, 3012, '', '获取商品列表', '', '', '', 'diy/get_product', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_product', 0, '获取商品列表'),
(3021, 3012, '', '获取门店自提开启状态', '', '', '', 'diy/get_store_status', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_store_status', 0, '获取门店自提开启状态'),
(3022, 3012, '', '还原Diy默认数据', '', '', '', 'diy/recovery/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-recovery', 0, '还原Diy默认数据'),
(3023, 3012, '', '获取所有二级分类', '', '', '', 'diy/get_by_category', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_by_category', 0, '获取所有二级分类'),
(3024, 3012, '', '设置Diy默认数据', '', '', '', 'diy/set_recovery/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-set_recovery', 0, '设置Diy默认数据'),
(3025, 3012, '', '获取商品列表', '', '', '', 'diy/get_product_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_product_list', 0, '获取商品列表'),
(3026, 3012, '', '获取页面链接', '', '', '', 'diy/get_page_link/<cate_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_page_link', 0, '获取页面链接'),
(3027, 3012, '', '获取页面链接分类', '', '', '', 'diy/get_page_category', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_page_category', 0, '获取页面链接分类'),
(3028, 3012, '', '添加DIY', '', '', '', 'diy/create', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-create', 0, '添加DIY'),
(3029, 3012, '', '添加表单', '', '', '', 'diy/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-create', 0, '添加表单'),
(3030, 3012, '', 'Diy模板数据详情', '', '', '', 'diy/get_diy_info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_diy_info', 0, 'Diy模板数据详情'),
(3031, 3012, '', '删除DIY模板', '', '', '', 'diy/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-del', 0, '删除DIY模板'),
(3032, 3012, '', 'Diy模板数据详情', '', '', '', 'diy/get_info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_info', 0, 'Diy模板数据详情'),
(3033, 3013, '', 'Diy模板数据详情', '', '', '', 'diy/get_info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_info-646a1a8c6b2bf', 0, 'Diy模板数据详情'),
(3034, 3013, '', 'Diy模板数据详情', '', '', '', 'diy/get_diy_info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_diy_info-646a1a8c6b2c6', 0, 'Diy模板数据详情'),
(3035, 3013, '', '使用DIY模板', '', '', '', 'diy/set_status/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-set_status', 0, '使用DIY模板'),
(3036, 3013, '', '添加表单', '', '', '', 'diy/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-create-646a1a8c6b2cf', 0, '添加表单'),
(3037, 3013, '', '添加DIY', '', '', '', 'diy/create', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-create-646a1a8c6b2d2', 0, '添加DIY'),
(3038, 3013, '', '添加DIY模板', '', '', '', 'diy/save/<id?>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-save-646a1a8c6b2d6', 0, '添加DIY模板'),
(3039, 3013, '', '获取前端页面路径', '', '', '', 'diy/get_url', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_url-646a1a8c6b2da', 0, '获取前端页面路径'),
(3040, 3013, '', '添加DIY模板', '', '', '', 'diy/diy_save/<id?>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-diy_save-646a1a8c6b2de', 0, '添加DIY模板'),
(3041, 3013, '', '获取商品分类', '', '', '', 'diy/get_category', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_category-646a1a8c6b2e2', 0, '获取商品分类'),
(3042, 3013, '', '获取商品列表', '', '', '', 'diy/get_product', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_product-646a1a8c6b2e6', 0, '获取商品列表'),
(3043, 3013, '', '还原Diy默认数据', '', '', '', 'diy/recovery/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-recovery-646a1a8c6b2ea', 0, '还原Diy默认数据'),
(3044, 3013, '', '获取门店自提开启状态', '', '', '', 'diy/get_store_status', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_store_status-646a1a8c6b2ed', 0, '获取门店自提开启状态'),
(3045, 3013, '', '获取所有二级分类', '', '', '', 'diy/get_by_category', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_by_category-646a1a8c6b2f1', 0, '获取所有二级分类'),
(3046, 3013, '', '设置Diy默认数据', '', '', '', 'diy/set_recovery/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-set_recovery-646a1a8c6b2f5', 0, '设置Diy默认数据'),
(3047, 3013, '', '获取商品列表', '', '', '', 'diy/get_product_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_product_list-646a1a8c6b2f9', 0, '获取商品列表'),
(3048, 3013, '', '获取页面链接分类', '', '', '', 'diy/get_page_category', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_page_category-646a1a8c6b2fd', 0, '获取页面链接分类'),
(3049, 3013, '', '获取页面链接', '', '', '', 'diy/get_page_link/<cate_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_page_link-646a1a8c6b301', 0, '获取页面链接'),
(3050, 3014, '', '删除DIY模板', '', '', '', 'diy/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-del-646a1a97c43d4', 0, '删除DIY模板'),
(3051, 3015, '', '使用DIY模板', '', '', '', 'diy/set_status/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-set_status-646a1aa862dae', 0, '使用DIY模板'),
(3052, 3010, '', '切换分类页面', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/657/3010', 3, '', 0, 'pages-diy-cate-status', 0, '切换分类页面'),
(3053, 3052, '', '获取风格设置', '', '', '', 'diy/get_color_change/<type>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_color_change', 0, '获取风格设置'),
(3054, 3052, '', '换色和分类保存', '', '', '', 'diy/color_change/<status>/<type>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-color_change', 0, '换色和分类保存'),
(3055, 3011, '', '保存个人中心', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/657/3011', 3, '', 0, 'pages-diy-member-save', 0, '保存个人中心'),
(3056, 3055, '', '个人中心保存', '', '', '', 'diy/member_save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-member_save', 0, '个人中心保存'),
(3057, 3055, '', '获取页面链接', '', '', '', 'diy/get_page_link/<cate_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_page_link-646a1b603f626', 0, '获取页面链接'),
(3058, 3055, '', '获取页面链接分类', '', '', '', 'diy/get_page_category', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_page_category-646a1b603f62f', 0, '获取页面链接分类'),
(3059, 3055, '', '个人中心详情', '', '', '', 'diy/get_member', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_member', 0, '个人中心详情'),
(3060, 902, '', '切换主题', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/902', 3, '', 0, 'pages-theme-status', 0, '切换主题'),
(3061, 3060, '', '换色和分类保存', '', '', '', 'diy/color_change/<status>/<type>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-color_change-646a1bdde013b', 0, '换色和分类保存'),
(3062, 3060, '', '获取风格设置', '', '', '', 'diy/get_color_change/<type>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'diy-get_color_change-646a1bdde0144', 0, '获取风格设置'),
(3063, 566, '', '添加素材分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/566', 3, '', 0, 'system-file-cate-add', 0, '添加素材分类'),
(3064, 566, '', '删除素材分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/566', 3, '', 0, 'system-file-cate-delete', 0, '删除素材分类'),
(3065, 566, '', '上传素材', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/566', 3, '', 0, 'system-file-add', 0, '上传素材'),
(3066, 566, '', '删除素材', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/656/566', 3, '', 0, 'system-file-delete', 0, '删除素材'),
(3067, 3063, '', '获取附件分类管理列表', '', '', '', 'file/category', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-category', 0, '获取附件分类管理列表'),
(3068, 3063, '', '保存附件分类管理', '', '', '', 'file/category', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-category', 0, '保存附件分类管理'),
(3069, 3063, '', '获取附件分类管理表单', '', '', '', 'file/category/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-category-create', 0, '获取附件分类管理表单'),
(3070, 3063, '', '获取修改附件分类管理表单', '', '', '', 'file/category/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-category-edit', 0, '获取修改附件分类管理表单'),
(3071, 3063, '', '修改附件分类管理', '', '', '', 'file/category/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-category', 0, '修改附件分类管理'),
(3072, 3064, '', '删除附件分类管理', '', '', '', 'file/category/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-category-646a1ca8e74c5', 0, '删除附件分类管理'),
(3073, 3065, '', '图片附件列表', '', '', '', 'file/file', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-file', 0, '图片附件列表'),
(3074, 3065, '', '移动图片分类表单', '', '', '', 'file/file/move', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-file-move', 0, '移动图片分类表单'),
(3075, 3065, '', '移动图片分类', '', '', '', 'file/file/do_move', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-file-do_move', 0, '移动图片分类'),
(3076, 3065, '', '修改图片名称', '', '', '', 'file/file/update/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-file-update', 0, '修改图片名称'),
(3077, 3065, '', '上传图片', '', '', '', 'file/upload/<upload_type?>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-upload', 0, '上传图片'),
(3078, 3065, '', '上传类型', '', '', '', 'file/upload_type', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-upload_type', 0, '上传类型'),
(3079, 3065, '', '分片上传本地视频', '', '', '', 'file/video_upload', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-video_upload', 0, '分片上传本地视频'),
(3080, 3066, '', '删除图片', '', '', '', 'file/file/delete', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'file-file-delete', 0, '删除图片'),
(3081, 92, '', '保存并发布', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '135/69/92', 3, '', 0, 'wechat-menu-save', 0, '保存并发布'),
(3082, 3081, '', '微信公众号菜单列表', '', '', '', 'app/wechat/menu', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-menu', 0, '微信公众号菜单列表'),
(3083, 3081, '', '保存微信公众号菜单', '', '', '', 'app/wechat/menu', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-menu', 0, '保存微信公众号菜单'),
(3084, 109, '', '添加图文', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '135/69/109', 3, '', 0, 'wechat-news-add', 0, '添加图文'),
(3085, 109, '', '编辑图文', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '135/69/109', 3, '', 0, 'wechat-news-edit', 0, '编辑图文'),
(3086, 109, '', '删除图文', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '135/69/109', 3, '', 0, 'wechat-news-delete', 0, '删除图文'),
(3087, 3084, '', '图文列表', '', '', '', 'app/wechat/news', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-news', 0, '图文列表'),
(3088, 3084, '', '保存图文', '', '', '', 'app/wechat/news', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-news', 0, '保存图文'),
(3089, 3084, '', '图文详情', '', '', '', 'app/wechat/news/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-news', 0, '图文详情'),
(3090, 3085, '', '保存图文', '', '', '', 'app/wechat/news', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-news-646a1d9c1d008', 0, '保存图文'),
(3091, 3085, '', '图文列表', '', '', '', 'app/wechat/news', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-news-646a1d9c1d011', 0, '图文列表'),
(3092, 3085, '', '图文详情', '', '', '', 'app/wechat/news/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-news-646a1d9c1d017', 0, '图文详情'),
(3093, 3086, '', '删除图文', '', '', '', 'app/wechat/news/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-news-646a1da7936d6', 0, '删除图文'),
(3094, 113, '', '保存并发布', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '135/69/114/113', 3, '', 0, 'wechat-follow-save', 0, '保存并发布'),
(3095, 115, '', '保存并发布', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '135/69/114/115', 3, '', 0, 'wechat-keyword-save', 0, '保存并发布'),
(3096, 116, '', '保存并回复', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '135/69/114/116', 3, '', 0, 'wechat-default-save', 0, '保存并回复'),
(3097, 3094, '', '关键字回复列表', '', '', '', 'app/wechat/keyword', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword', 0, '关键字回复列表'),
(3098, 3094, '', '保存关键字回复', '', '', '', 'app/wechat/keyword/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword', 0, '保存关键字回复'),
(3099, 3094, '', '关键字回复详情', '', '', '', 'app/wechat/keyword/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword', 0, '关键字回复详情'),
(3100, 3094, '', '删除关键字回复', '', '', '', 'app/wechat/keyword/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword', 0, '删除关键字回复'),
(3101, 3094, '', '修改关键字回复状态', '', '', '', 'app/wechat/keyword/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-set_status', 0, '修改关键字回复状态'),
(3102, 3095, '', '关键字回复列表', '', '', '', 'app/wechat/keyword', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-646a1e33e50c2', 0, '关键字回复列表'),
(3103, 3095, '', '保存关键字回复', '', '', '', 'app/wechat/keyword/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-646a1e33e50cc', 0, '保存关键字回复'),
(3104, 3095, '', '关键字回复详情', '', '', '', 'app/wechat/keyword/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-646a1e33e50d2', 0, '关键字回复详情'),
(3105, 3095, '', '删除关键字回复', '', '', '', 'app/wechat/keyword/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-646a1e33e50da', 0, '删除关键字回复'),
(3106, 3095, '', '修改关键字回复状态', '', '', '', 'app/wechat/keyword/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-set_status-646a1e33e50e0', 0, '修改关键字回复状态'),
(3107, 3096, '', '关键字回复列表', '', '', '', 'app/wechat/keyword', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-646a1e43aba07', 0, '关键字回复列表'),
(3108, 3096, '', '保存关键字回复', '', '', '', 'app/wechat/keyword/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-646a1e43aba11', 0, '保存关键字回复'),
(3109, 3096, '', '修改关键字回复状态', '', '', '', 'app/wechat/keyword/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-set_status-646a1e43aba18', 0, '修改关键字回复状态'),
(3110, 3096, '', '删除关键字回复', '', '', '', 'app/wechat/keyword/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-646a1e43aba1e', 0, '删除关键字回复'),
(3111, 3096, '', '关键字回复详情', '', '', '', 'app/wechat/keyword/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-keyword-646a1e43aba24', 0, '关键字回复详情'),
(3112, 994, '', '下载小程序码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '135/993/994', 3, '', 0, 'routine-down-qrcode', 0, '下载小程序码'),
(3113, 994, '', '下载小程序包', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '135/993/994', 3, '', 0, 'routine-down-file', 0, '下载小程序包'),
(3114, 3112, '', '下载小程序页面数据', '', '', '', 'app/routine/info', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-routine-info', 0, '下载小程序页面数据'),
(3115, 3113, '', '下载小程序页面数据', '', '', '', 'app/routine/info', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-routine-info-646a1ed2ee6e0', 0, '下载小程序页面数据'),
(3116, 3113, '', '下载小程序模版', '', '', '', 'app/routine/download', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-routine-download', 0, '下载小程序模版'),
(3118, 898, '', '同步消息', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/898', 3, '', 0, 'notification-sync', 0, '同步消息'),
(3119, 898, '', '设置消息', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/898', 3, '', 0, 'notification-setting', 0, '设置消息'),
(3120, 898, '', '消息状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/898', 3, '', 0, 'notification-status', 0, '消息状态'),
(3121, 3118, '', '一键同步订阅消息', '', '', '', 'app/routine/syncSubscribe', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-routine-syncSubscribe', 0, '一键同步订阅消息'),
(3122, 3118, '', '一键同步模版消息', '', '', '', 'app/wechat/syncSubscribe', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'app-wechat-syncSubscribe', 0, '一键同步模版消息'),
(3123, 3119, '', '系统通知列表', '', '', '', 'setting/notification/index', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-notification-index', 0, '系统通知列表'),
(3124, 3119, '', '保存通知设置', '', '', '', 'setting/notification/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-notification-save', 0, '保存通知设置'),
(3125, 3119, '', '获取单条通知数据', '', '', '', 'setting/notification/info', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-notification-info', 0, '获取单条通知数据'),
(3126, 3120, '', '修改消息状态', '', '', '', 'setting/notification/set_status/<type>/<status>/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-notification-set_status', 0, '修改消息状态'),
(3127, 1061, '', '保存协议', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/1061', 3, '', 0, 'agreement-save', 0, '保存协议'),
(3128, 3127, '', '获取协议内容', '', '', '', 'setting/get_agreement/<type>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-get_agreement', 0, '获取协议内容'),
(3129, 3127, '', '获取版权信息', '', '', '', 'setting/get_version', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-get_version', 0, '获取版权信息'),
(3130, 3127, '', '设置协议内容', '', '', '', 'setting/save_agreement', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-save_agreement', 0, '设置协议内容'),
(3131, 19, '', '添加角色', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/14/19', 3, '', 0, 'system-role-add', 0, '添加角色'),
(3132, 19, '', '编辑角色', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/14/19', 3, '', 0, 'system-role-edit', 0, '编辑角色'),
(3133, 19, '', '删除角色', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/14/19', 3, '', 0, 'system-role-delete', 0, '删除角色'),
(3134, 19, '', '角色状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/14/19', 3, '', 0, 'system-role-status', 0, '角色状态'),
(3135, 3131, '', '管理员身份列表', '', '', '', 'setting/role', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role', 0, '管理员身份列表'),
(3136, 3131, '', '管理员身份权限列表', '', '', '', 'setting/role/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role-create', 0, '管理员身份权限列表'),
(3137, 3131, '', '新建或编辑管理员', '', '', '', 'setting/role/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role', 0, '新建或编辑管理员'),
(3138, 3132, '', '管理员身份列表', '', '', '', 'setting/role', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role-646a206a0dd01', 0, '管理员身份列表'),
(3139, 3132, '', '编辑管理员详情', '', '', '', 'setting/role/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role-edit', 0, '编辑管理员详情'),
(3140, 3132, '', '新建或编辑管理员', '', '', '', 'setting/role/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role-646a206a0dd15', 0, '新建或编辑管理员'),
(3141, 3133, '', '管理员身份列表', '', '', '', 'setting/role', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role-646a2078367e6', 0, '管理员身份列表'),
(3142, 3133, '', '删除管理员身份', '', '', '', 'setting/role/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role-646a2078367ef', 0, '删除管理员身份'),
(3143, 3134, '', '管理员身份列表', '', '', '', 'setting/role', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role-646a2083c8f0b', 0, '管理员身份列表'),
(3144, 3134, '', '修改管理员身份状态', '', '', '', 'setting/role/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-role-set_status', 0, '修改管理员身份状态'),
(3145, 20, '', '添加管理员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/14/20', 3, '', 0, 'system-admin-add', 0, '添加管理员'),
(3146, 20, '', '修改管理员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/14/20', 3, '', 0, 'system-admin-edit', 0, '修改管理员'),
(3147, 20, '', '删除管理员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/14/20', 3, '', 0, 'system-admin-delete', 0, '删除管理员'),
(3148, 20, '', '管理员状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/14/20', 3, '', 0, 'system-admin-status', 0, '管理员状态'),
(3149, 3145, '', '获取管理员列表', '', '', '', 'setting/admin', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-admin', 0, '获取管理员列表'),
(3150, 3145, '', '保存管理员', '', '', '', 'setting/admin', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-admin', 0, '保存管理员'),
(3151, 3145, '', '获取管理员表单', '', '', '', 'setting/admin/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-admin-create', 0, '获取管理员表单'),
(3152, 3146, '', '获取管理员列表', '', '', '', 'setting/admin', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-admin-646a213532f4c', 0, '获取管理员列表'),
(3153, 3146, '', '获取修改管理员表单', '', '', '', 'setting/admin/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-admin-edit', 0, '获取修改管理员表单'),
(3154, 3146, '', '修改管理员', '', '', '', 'setting/admin/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-admin-646a213532f5f', 0, '修改管理员'),
(3155, 3147, '', '删除管理员', '', '', '', 'setting/admin/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-admin-646a2141d9a56', 0, '删除管理员'),
(3156, 3147, '', '获取管理员列表', '', '', '', 'setting/admin', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-admin-646a2141d9a61', 0, '获取管理员列表'),
(3157, 3148, '', '获取管理员列表', '', '', '', 'setting/admin', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-admin-646a214f14bed', 0, '获取管理员列表'),
(3158, 3148, '', '修改管理员状态', '', '', '', 'setting/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-set_status', 0, '修改管理员状态'),
(3159, 21, '', '编辑菜单', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/14/21', 3, '', 0, 'system-menu-edit', 0, '编辑菜单'),
(3160, 3159, '', '获取修改权限菜单表单', '', '', '', 'setting/menus/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-edit', 0, '获取修改权限菜单表单'),
(3161, 3159, '', '修改权限菜单', '', '', '', 'setting/menus/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus', 0, '修改权限菜单'),
(3162, 3159, '', '查看权限菜单信息', '', '', '', 'setting/menus/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus', 0, '查看权限菜单信息'),
(3163, 720, '', '添加配送员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/720', 3, '', 0, 'delivery-service-add', 0, '添加配送员'),
(3164, 720, '', '编辑配送员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/720', 3, '', 0, 'delivery-service-edit', 0, '编辑配送员'),
(3165, 720, '', '删除配送员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/720', 3, '', 0, 'delivery-service-delete', 0, '删除配送员'),
(3166, 720, '', '配送员状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/720', 3, '', 0, 'delivery-service-status', 0, '配送员状态'),
(3167, 3163, '', '配送员列表', '', '', '', 'order/delivery/index', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-delivery-index', 0, '配送员列表'),
(3168, 3163, '', '新增配送表单', '', '', '', 'order/delivery/add', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-delivery-add', 0, '新增配送表单'),
(3169, 3163, '', '保存新建的配送员', '', '', '', 'order/delivery/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-delivery-save', 0, '保存新建的配送员'),
(3170, 3164, '', '编辑配送员表单', '', '', '', 'order/delivery/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-delivery-edit', 0, '编辑配送员表单'),
(3171, 3164, '', '修改配送员', '', '', '', 'order/delivery/update/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-delivery-update', 0, '修改配送员'),
(3172, 3165, '', '删除配送员', '', '', '', 'order/delivery/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-delivery-del', 0, '删除配送员'),
(3173, 3166, '', '修改配送员状态', '', '', '', 'order/delivery/set_status/<id>/<status>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'order-delivery-set_status', 0, '修改配送员状态'),
(3174, 300, '', '添加提货点', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/144/300', 3, '', 0, 'merchant-add', 0, '添加提货点'),
(3175, 300, '', '修改提货点', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/144/300', 3, '', 0, 'merchant-edit', 0, '修改提货点'),
(3176, 300, '', '删除提货点', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/144/300', 3, '', 0, 'merchant-delete', 0, '删除提货点'),
(3177, 300, '', '提货点状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/144/300', 3, '', 0, 'merchant-status', 0, '提货点状态'),
(3178, 3174, '', '门店列表', '', '', '', 'merchant/store', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store', 0, '门店列表'),
(3179, 3174, '', '保存修改门店信息', '', '', '', 'merchant/store/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store', 0, '保存修改门店信息'),
(3180, 3174, '', '门店位置选择', '', '', '', 'merchant/store/address', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store-address', 0, '门店位置选择'),
(3181, 3175, '', '门店列表', '', '', '', 'merchant/store', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store-646a231547cf8', 0, '门店列表'),
(3182, 3175, '', '保存修改门店信息', '', '', '', 'merchant/store/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store-646a231547d03', 0, '保存修改门店信息'),
(3183, 3175, '', '门店详情', '', '', '', 'merchant/store/get_info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store-get_info', 0, '门店详情'),
(3184, 3175, '', '门店位置选择', '', '', '', 'merchant/store/address', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store-address-646a231547d13', 0, '门店位置选择'),
(3185, 3176, '', '门店列表', '', '', '', 'merchant/store', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store-646a23281a6ee', 0, '门店列表'),
(3186, 3176, '', '门店删除', '', '', '', 'merchant/store/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store-del', 0, '门店删除'),
(3187, 3177, '', '门店列表', '', '', '', 'merchant/store', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store-646a2337c993f', 0, '门店列表'),
(3188, 3177, '', '门店上下架', '', '', '', 'merchant/store/set_show/<id>/<is_show>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store-set_show', 0, '门店上下架'),
(3189, 301, '', '添加核销员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/144/301', 3, '', 0, 'merchant-staff-add', 0, '添加核销员'),
(3190, 301, '', '修改核销员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/144/301', 3, '', 0, 'merchant-staff-edit', 0, '修改核销员'),
(3191, 301, '', '删除核销员', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/144/301', 3, '', 0, 'merchant-staff-delete', 0, '删除核销员'),
(3192, 301, '', '核销员状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/144/301', 3, '', 0, 'merchant-staff-status', 0, '核销员状态'),
(3193, 3189, '', '获取门店店员列表', '', '', '', 'merchant/store_staff', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff', 0, '获取门店店员列表'),
(3194, 3189, '', '添加门店店员表单', '', '', '', 'merchant/store_staff/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff-create', 0, '添加门店店员表单'),
(3195, 3189, '', '保存店员', '', '', '', 'merchant/store_staff/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff-save', 0, '保存店员'),
(3196, 3189, '', '门店搜索列表', '', '', '', 'merchant/store_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_list', 0, '门店搜索列表'),
(3197, 3190, '', '获取门店店员列表', '', '', '', 'merchant/store_staff', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff-646a23be65c88', 0, '获取门店店员列表'),
(3198, 3190, '', '修改店员表单', '', '', '', 'merchant/store_staff/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff-edit', 0, '修改店员表单'),
(3199, 3190, '', '保存店员', '', '', '', 'merchant/store_staff/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff-save-646a23be65c98', 0, '保存店员'),
(3200, 3190, '', '门店搜索列表', '', '', '', 'merchant/store_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_list-646a23be65c9e', 0, '门店搜索列表'),
(3201, 3191, '', '获取门店店员列表', '', '', '', 'merchant/store_staff', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff-646a23cc0a061', 0, '获取门店店员列表'),
(3202, 3191, '', '删除店员', '', '', '', 'merchant/store_staff/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff-del', 0, '删除店员'),
(3203, 3192, '', '修改店员状态', '', '', '', 'merchant/store_staff/set_show/<id>/<is_show>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff-set_show', 0, '修改店员状态'),
(3204, 3192, '', '获取门店店员列表', '', '', '', 'merchant/store_staff', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'merchant-store_staff-646a23d9d7e44', 0, '获取门店店员列表'),
(3205, 230, '', '添加运费模版', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/230', 3, '', 0, 'shipping-temp-add', 0, '添加运费模版'),
(3206, 230, '', '修改运费模版', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/230', 3, '', 0, 'shipping-temp-edit', 0, '修改运费模版'),
(3207, 230, '', '删除运费模版', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/303/230', 3, '', 0, 'shipping-temp-delete', 0, '删除运费模版'),
(3208, 3205, '', '运费模板列表', '', '', '', 'setting/shipping_templates/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-shipping_templates-list', 0, '运费模板列表'),
(3209, 3205, '', '新增或修改运费模版', '', '', '', 'setting/shipping_templates/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-shipping_templates-save', 0, '新增或修改运费模版'),
(3210, 3205, '', '城市数据接口', '', '', '', 'setting/shipping_templates/city_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-shipping_templates-city_list', 0, '城市数据接口'),
(3211, 3206, '', '运费模板列表', '', '', '', 'setting/shipping_templates/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-shipping_templates-list-646a24602a752', 0, '运费模板列表'),
(3212, 3206, '', '修改运费模板数据', '', '', '', 'setting/shipping_templates/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-shipping_templates-edit', 0, '修改运费模板数据'),
(3213, 3206, '', '新增或修改运费模版', '', '', '', 'setting/shipping_templates/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-shipping_templates-save-646a24602a763', 0, '新增或修改运费模版'),
(3214, 3206, '', '城市数据接口', '', '', '', 'setting/shipping_templates/city_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-shipping_templates-city_list-646a24602a769', 0, '城市数据接口'),
(3215, 3207, '', '删除运费模板', '', '', '', 'setting/shipping_templates/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-shipping_templates-del', 0, '删除运费模板'),
(3216, 3207, '', '运费模板列表', '', '', '', 'setting/shipping_templates/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-shipping_templates-list-646a246c229e8', 0, '运费模板列表'),
(3217, 111, '', '添加配置分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/111', 3, '', 0, 'config-tab-add', 0, '添加配置分类'),
(3218, 111, '', '修改配置分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/111', 3, '', 0, 'config-tab-edit', 0, '修改配置分类'),
(3219, 111, '', '删除配置分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/111', 3, '', 0, 'config-tab-delete', 0, '删除配置分类'),
(3220, 111, '', '查看配置列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/111', 3, '', 0, 'config-list', 0, '查看配置列表'),
(3221, 111, '', '添加配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/111', 3, '', 0, 'config-add', 0, '添加配置'),
(3222, 111, '', '修改配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/111', 3, '', 0, 'config-edit', 0, '修改配置'),
(3223, 111, '', '删除配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/111', 3, '', 0, 'config-delete', 0, '删除配置'),
(3224, 3217, '', '获取系统配置分类列表', '', '', '', 'setting/config_class', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config_class', 0, '获取系统配置分类列表'),
(3225, 3217, '', '获取系统配置分类表单', '', '', '', 'setting/config_class/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config_class-create', 0, '获取系统配置分类表单'),
(3226, 3217, '', '保存系统配置分类', '', '', '', 'setting/config_class', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config_class', 0, '保存系统配置分类'),
(3227, 3218, '', '获取系统配置分类列表', '', '', '', 'setting/config_class', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config_class-646a2648cbb75', 0, '获取系统配置分类列表'),
(3228, 3218, '', '获取修改系统配置分类表单', '', '', '', 'setting/config_class/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config_class-edit', 0, '获取修改系统配置分类表单'),
(3229, 3218, '', '修改系统配置分类', '', '', '', 'setting/config_class/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config_class-646a2648cbb97', 0, '修改系统配置分类'),
(3230, 3219, '', '获取系统配置分类列表', '', '', '', 'setting/config_class', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config_class-646a26613d6bb', 0, '获取系统配置分类列表'),
(3231, 3219, '', '删除系统配置分类', '', '', '', 'setting/config_class/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config_class-646a26613d6c6', 0, '删除系统配置分类'),
(3232, 3220, '', '获取系统配置分类列表', '', '', '', 'setting/config_class', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config_class-646a267255df4', 0, '获取系统配置分类列表'),
(3233, 3220, '', '获取系统配置列表', '', '', '', 'setting/config', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config', 0, '获取系统配置列表'),
(3234, 3221, '', '获取系统配置列表', '', '', '', 'setting/config', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config-646a26874c851', 0, '获取系统配置列表'),
(3235, 3221, '', '保存系统配置', '', '', '', 'setting/config', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config-646a26874c85b', 0, '保存系统配置'),
(3236, 3221, '', '获取系统配置表单', '', '', '', 'setting/config/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config-create', 0, '获取系统配置表单'),
(3237, 3222, '', '获取系统配置列表', '', '', '', 'setting/config', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config-646a269697340', 0, '获取系统配置列表'),
(3238, 3222, '', '获取修改系统配置表单', '', '', '', 'setting/config/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config-edit', 0, '获取修改系统配置表单'),
(3239, 3222, '', '修改系统配置', '', '', '', 'setting/config/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config-646a269697353', 0, '修改系统配置'),
(3240, 3223, '', '获取系统配置列表', '', '', '', 'setting/config', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config-646a26a48db02', 0, '获取系统配置列表'),
(3241, 3223, '', '删除系统配置', '', '', '', 'setting/config/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config-646a26a48db0f', 0, '删除系统配置'),
(3242, 3223, '', '修改配置状态', '', '', '', 'setting/config/set_status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-config-set_status', 0, '修改配置状态'),
(3243, 112, '', '添加数据组', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/112', 3, '', 0, 'system-group-add', 0, '添加数据组'),
(3244, 112, '', '修改数据组', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/112', 3, '', 0, 'system-group-edit', 0, '修改数据组'),
(3245, 112, '', '删除数据组', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/112', 3, '', 0, 'system-group-delete', 0, '删除数据组'),
(3246, 112, '', '查看数据列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/112', 3, '', 0, 'system-group-data-list', 0, '查看数据列表'),
(3247, 112, '', '添加组合数据', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/112', 3, '', 0, 'system-group-data-add', 0, '添加组合数据'),
(3248, 112, '', '修改组合数据', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/112', 3, '', 0, 'system-group-data-edit', 0, '修改组合数据'),
(3249, 112, '', '删除组合数据', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/112', 3, '', 0, 'system-group-data-delete', 0, '删除组合数据'),
(3250, 3243, '', '获取组合数据列表', '', '', '', 'setting/group', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group', 0, '获取组合数据列表'),
(3251, 3243, '', '保存组合数据', '', '', '', 'setting/group', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group', 0, '保存组合数据'),
(3252, 3244, '', '获取组合数据列表', '', '', '', 'setting/group', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group-646a2784797f3', 0, '获取组合数据列表'),
(3253, 3244, '', '获取修改组合数据表单', '', '', '', 'setting/group/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group-edit', 0, '获取修改组合数据表单'),
(3254, 3244, '', '修改组合数据', '', '', '', 'setting/group/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group-646a278479805', 0, '修改组合数据'),
(3255, 3245, '', '获取组合数据列表', '', '', '', 'setting/group', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group-646a27906b21a', 0, '获取组合数据列表'),
(3256, 3245, '', '删除组合数据', '', '', '', 'setting/group/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group-646a27906b223', 0, '删除组合数据'),
(3257, 3246, '', '获取组合数据列表', '', '', '', 'setting/group', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group-646a27b813076', 0, '获取组合数据列表'),
(3258, 3246, '', '获取组合数据子数据列表', '', '', '', 'setting/group_data', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group_data', 0, '获取组合数据子数据列表'),
(3259, 3247, '', '获取组合数据子数据列表', '', '', '', 'setting/group_data', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group_data-646a27c6e19ae', 0, '获取组合数据子数据列表'),
(3260, 3247, '', '保存组合数据子数据', '', '', '', 'setting/group_data', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group_data-646a27c6e19b8', 0, '保存组合数据子数据'),
(3261, 3247, '', '获取组合数据子数据表单', '', '', '', 'setting/group_data/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group_data-create', 0, '获取组合数据子数据表单'),
(3262, 3248, '', '获取组合数据子数据列表', '', '', '', 'setting/group_data', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group_data-646a27d6c6cbf', 0, '获取组合数据子数据列表'),
(3263, 3248, '', '获取修改组合数据子数据表单', '', '', '', 'setting/group_data/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group_data-edit', 0, '获取修改组合数据子数据表单'),
(3264, 3248, '', '修改组合数据子数据', '', '', '', 'setting/group_data/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group_data-646a27d6c6cd2', 0, '修改组合数据子数据'),
(3265, 3249, '', '获取组合数据子数据列表', '', '', '', 'setting/group_data', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group_data-646a27e50d1c1', 0, '获取组合数据子数据列表'),
(3266, 3249, '', '删除组合数据子数据', '', '', '', 'setting/group_data/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-group_data-646a27e50d1ca', 0, '删除组合数据子数据'),
(3267, 1076, '', '添加定时任务', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/1076', 3, '', 0, 'crontab-add', 0, '添加定时任务'),
(3268, 1076, '', '编辑定时任务', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/1076', 3, '', 0, 'crontab-edit', 0, '编辑定时任务'),
(3269, 1076, '', '删除定时任务', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/1076', 3, '', 0, 'crontab-delete', 0, '删除定时任务'),
(3270, 1076, '', '定时任务状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/1076', 3, '', 0, 'crontab-status', 0, '定时任务状态'),
(3271, 3267, '', '定时任务列表', '', '', '', 'system/crontab/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-list', 0, '定时任务列表'),
(3272, 3267, '', '定时任务类型', '', '', '', 'system/crontab/mark', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-mark', 0, '定时任务类型'),
(3273, 3267, '', '定时任务添加编辑', '', '', '', 'system/crontab/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-save', 0, '定时任务添加编辑'),
(3274, 3268, '', '定时任务列表', '', '', '', 'system/crontab/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-list-646a2860d3394', 0, '定时任务列表'),
(3275, 3268, '', '定时任务类型', '', '', '', 'system/crontab/mark', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-mark-646a2860d339f', 0, '定时任务类型'),
(3276, 3268, '', '定时任务详情', '', '', '', 'system/crontab/info/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-info', 0, '定时任务详情'),
(3277, 3268, '', '定时任务添加编辑', '', '', '', 'system/crontab/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-save-646a2860d33b0', 0, '定时任务添加编辑'),
(3278, 3269, '', '定时任务列表', '', '', '', 'system/crontab/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-list-646a286e08853', 0, '定时任务列表'),
(3279, 3269, '', '删除定时任务', '', '', '', 'system/crontab/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-del', 0, '删除定时任务'),
(3280, 3270, '', '定时任务列表', '', '', '', 'system/crontab/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-list-646a287b727dc', 0, '定时任务列表'),
(3281, 3270, '', '定时任务是否开启开关', '', '', '', 'system/crontab/set_open/<id>/<is_open>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crontab-set_open', 0, '定时任务是否开启开关'),
(3282, 2472, '', '添加权限', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/2472', 3, '', 0, 'system-admin-role-add', 0, '添加权限'),
(3283, 2472, '', '编辑权限', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/2472', 3, '', 0, 'system-admin-role-edit', 0, '编辑权限'),
(3284, 2472, '', '删除权限', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/2472', 3, '', 0, 'system-admin-role-delete', 0, '删除权限'),
(3285, 2472, '', '添加子菜单', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/2472', 3, '', 0, 'system-admin-role-add-menus', 0, '添加子菜单'),
(3286, 2472, '', '选择接口', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/56/2472', 3, '', 0, 'system-admin-role-select', 0, '选择接口'),
(3287, 3282, '', '获取权限菜单列表', '', '', '', 'setting/menus', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a296711f5a', 0, '获取权限菜单列表'),
(3288, 3282, '', '获取权限菜单表单', '', '', '', 'setting/menus/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-create', 0, '获取权限菜单表单'),
(3289, 3282, '', '保存权限菜单', '', '', '', 'setting/menus', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a296711f72', 0, '保存权限菜单'),
(3290, 3283, '', '获取权限菜单列表', '', '', '', 'setting/menus', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a297963e41', 0, '获取权限菜单列表'),
(3291, 3283, '', '获取修改权限菜单表单', '', '', '', 'setting/menus/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-edit-646a297963e4d', 0, '获取修改权限菜单表单'),
(3292, 3283, '', '查看权限菜单信息', '', '', '', 'setting/menus/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a297963e56', 0, '查看权限菜单信息'),
(3293, 3283, '', '修改权限菜单', '', '', '', 'setting/menus/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a297963e5e', 0, '修改权限菜单'),
(3294, 3284, '', '获取权限菜单列表', '', '', '', 'setting/menus', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a29871df51', 0, '获取权限菜单列表'),
(3295, 3284, '', '删除权限菜单', '', '', '', 'setting/menus/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a29871df5c', 0, '删除权限菜单'),
(3296, 3285, '', '查看权限菜单信息', '', '', '', 'setting/menus/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a29b28b70e', 0, '查看权限菜单信息'),
(3297, 3285, '', '保存权限菜单', '', '', '', 'setting/menus', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a29b28b719', 0, '保存权限菜单'),
(3298, 3285, '', '获取权限菜单列表', '', '', '', 'setting/menus', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-menus-646a29b28b720', 0, '获取权限菜单列表'),
(3299, 1101, '', '添加功能', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1695/1101', 3, '', 0, 'code-generation-add', 0, '添加功能'),
(3300, 1101, '', '查看代码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1695/1101', 3, '', 0, 'code-generation-scan', 0, '查看代码'),
(3301, 1101, '', '下载代码', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1695/1101', 3, '', 0, 'code-generation-down', 0, '下载代码'),
(3302, 1101, '', '编辑功能', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1695/1101', 3, '', 0, 'code-generation-edit', 0, '编辑功能'),
(3303, 1101, '', '删除功能', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1695/1101', 3, '', 0, 'code-generation-delete', 0, '删除功能'),
(3304, 3299, '', '保存生成CRUD', '', '', '', 'system/crud', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud', 0, '保存生成CRUD'),
(3305, 3299, '', '获取菜单TREE形数据', '', '', '', 'system/crud/menus', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-menus', 0, '获取菜单TREE形数据'),
(3306, 3299, '', '获取CRUD文件存放', '', '', '', 'system/crud/file_path', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-file_path', 0, '获取CRUD文件存放'),
(3307, 3299, '', '获取CRUD列表', '', '', '', 'system/crud/column_type', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-column_type', 0, '获取CRUD列表'),
(3308, 3299, '', '获取CRUD配置', '', '', '', 'system/crud/config/<tableName>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-config', 0, '获取CRUD配置'),
(3309, 3299, '', '保存CRUD修改的文件', '', '', '', 'system/crud/save_file/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-save_file', 0, '保存CRUD修改的文件'),
(3310, 3300, '', '查看CRUD', '', '', '', 'system/crud/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-646a34d60329f', 0, '查看CRUD'),
(3311, 3300, '', '保存CRUD修改的文件', '', '', '', 'system/crud/save_file/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-save_file-646a34d6032aa', 0, '保存CRUD修改的文件'),
(3312, 3301, '', '下载生成的文件', '', '', '', 'system/crud/download/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-download', 0, '下载生成的文件'),
(3313, 3301, '', '获取CRUD列表', '', '', '', 'system/crud', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-646a34e764096', 0, '获取CRUD列表'),
(3314, 3302, '', '保存CRUD修改的文件', '', '', '', 'system/crud/save_file/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-save_file-646a34fe884b9', 0, '保存CRUD修改的文件'),
(3315, 3302, '', '获取CRUD配置', '', '', '', 'system/crud/config/<tableName>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-config-646a34fe884c2', 0, '获取CRUD配置'),
(3316, 3302, '', '获取CRUD列表', '', '', '', 'system/crud/column_type', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-column_type-646a34fe884c9', 0, '获取CRUD列表'),
(3317, 3302, '', '获取CRUD文件存放', '', '', '', 'system/crud/file_path', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-file_path-646a34fe884d0', 0, '获取CRUD文件存放'),
(3318, 3302, '', '查看CRUD', '', '', '', 'system/crud/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-646a34fe884d6', 0, '查看CRUD'),
(3319, 3302, '', '保存生成CRUD', '', '', '', 'system/crud', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-646a34fe884dc', 0, '保存生成CRUD'),
(3320, 3302, '', '获取菜单TREE形数据', '', '', '', 'system/crud/menus', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-menus-646a34fe884e3', 0, '获取菜单TREE形数据'),
(3321, 3302, '', '获取CRUD列表', '', '', '', 'system/crud', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-646a34fe884e9', 0, '获取CRUD列表'),
(3322, 3303, '', '获取CRUD列表', '', '', '', 'system/crud', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-646a350c5c14e', 0, '获取CRUD列表'),
(3323, 3303, '', '删除CRUD', '', '', '', 'system/crud/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-646a350c5c15b', 0, '删除CRUD'),
(3324, 1078, '', '接口分类', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1695/1078', 3, '', 0, 'route-cate', 0, '接口分类'),
(3325, 1078, '', '同步接口', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1695/1078', 3, '', 0, 'route-sync', 0, '同步接口'),
(3326, 1078, '', '调试接口', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1695/1078', 3, '', 0, 'route-test', 0, '调试接口'),
(3327, 1078, '', '编辑接口', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1695/1078', 3, '', 0, 'route-edit', 0, '编辑接口'),
(3328, 3324, '', '获取路由分类列表', '', '', '', 'system/route_cate', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route_cate', 0, '获取路由分类列表'),
(3329, 3324, '', '保存路由分类', '', '', '', 'system/route_cate', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route_cate', 0, '保存路由分类'),
(3330, 3324, '', '获取创建路由分类表单', '', '', '', 'system/route_cate/create', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route_cate-create', 0, '获取创建路由分类表单'),
(3331, 3324, '', '获取修改路由分类表单', '', '', '', 'system/route_cate/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route_cate-edit', 0, '获取修改路由分类表单'),
(3332, 3324, '', '修改路由分类', '', '', '', 'system/route_cate/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route_cate', 0, '修改路由分类'),
(3333, 3324, '', '删除路由分类', '', '', '', 'system/route_cate/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route_cate', 0, '删除路由分类'),
(3334, 3325, '', '同步路由', '', '', '', 'system/route/sync_route/<appName?>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route-sync_route', 0, '同步路由'),
(3335, 3325, '', '获取路由tree', '', '', '', 'system/route/tree', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route-tree', 0, '获取路由tree'),
(3336, 3327, '', '获取路由tree', '', '', '', 'system/route/tree', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route-tree-646a35e9baaef', 0, '获取路由tree'),
(3337, 3327, '', '查看路由权限', '', '', '', 'system/route/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route', 0, '查看路由权限'),
(3338, 3327, '', '保存路由权限', '', '', '', 'system/route/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-route', 0, '保存路由权限'),
(3339, 1068, '', '添加语言列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/1067/1068', 3, '', 0, 'lang-list-add', 0, '添加语言列表'),
(3340, 1068, '', '修改语言列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/1067/1068', 3, '', 0, 'lang-list-edit', 0, '修改语言列表'),
(3341, 1068, '', '删除语言列表', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/1067/1068', 3, '', 0, 'lang-list-delete', 0, '删除语言列表'),
(3342, 1068, '', '语言列表状态', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/1067/1068', 3, '', 0, 'lang-list-status', 0, '语言列表状态'),
(3343, 3339, '', '语言类型列表', '', '', '', 'setting/lang_type/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-list', 0, '语言类型列表'),
(3344, 3339, '', '新增修改语言类型表单', '', '', '', 'setting/lang_type/form/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-form', 0, '新增修改语言类型表单'),
(3345, 3339, '', '保存新增修改语言', '', '', '', 'setting/lang_type/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-save', 0, '保存新增修改语言'),
(3346, 3340, '', '语言类型列表', '', '', '', 'setting/lang_type/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-list-646a36876cfbe', 0, '语言类型列表'),
(3347, 3340, '', '新增修改语言类型表单', '', '', '', 'setting/lang_type/form/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-form-646a36876cfd9', 0, '新增修改语言类型表单'),
(3348, 3340, '', '保存新增修改语言', '', '', '', 'setting/lang_type/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-save-646a36876cfe3', 0, '保存新增修改语言'),
(3349, 3341, '', '语言类型列表', '', '', '', 'setting/lang_type/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-list-646a369c9741e', 0, '语言类型列表'),
(3350, 3341, '', '删除语言', '', '', '', 'setting/lang_type/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-del', 0, '删除语言'),
(3351, 3342, '', '语言类型列表', '', '', '', 'setting/lang_type/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-list-646a36a88444d', 0, '语言类型列表'),
(3352, 3342, '', '修改语言类型状态', '', '', '', 'setting/lang_type/status/<id>/<status>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_type-status', 0, '修改语言类型状态'),
(3353, 1069, '', '添加语言详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/1067/1069', 3, '', 0, 'lang-info-add', 0, '添加语言详情'),
(3354, 1069, '', '编辑语言详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/1067/1069', 3, '', 0, 'lang-info-edit', 0, '编辑语言详情'),
(3355, 1069, '', '删除语言详情', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '12/1067/1069', 3, '', 0, 'lang-info-delete', 0, '删除语言详情'),
(3356, 3353, '', '语言列表', '', '', '', 'setting/lang_code/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-list', 0, '语言列表'),
(3357, 3353, '', '语言详情', '', '', '', 'setting/lang_code/info', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-info', 0, '语言详情'),
(3358, 3353, '', '保存修改语言', '', '', '', 'setting/lang_code/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-save', 0, '保存修改语言'),
(3359, 3353, '', '机器翻译', '', '', '', 'setting/lang_code/translate', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-translate', 0, '机器翻译'),
(3360, 3354, '', '语言列表', '', '', '', 'setting/lang_code/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-list-646a3717e64bb', 0, '语言列表'),
(3361, 3354, '', '语言详情', '', '', '', 'setting/lang_code/info', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-info-646a3717e64c8', 0, '语言详情'),
(3362, 3354, '', '保存修改语言', '', '', '', 'setting/lang_code/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-save-646a3717e64d1', 0, '保存修改语言'),
(3363, 3354, '', '机器翻译', '', '', '', 'setting/lang_code/translate', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-translate-646a3717e64db', 0, '机器翻译'),
(3364, 3355, '', '语言列表', '', '', '', 'setting/lang_code/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-list-646a372511b5e', 0, '语言列表'),
(3365, 3355, '', '删除语言', '', '', '', 'setting/lang_code/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_code-del', 0, '删除语言'),
(3366, 1070, '', '添加语言地区', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1067/1070', 3, '', 0, 'lang-country-add', 0, '添加语言地区'),
(3367, 1070, '', '编辑语言地区', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1067/1070', 3, '', 0, 'lang-country-edit', 0, '编辑语言地区'),
(3368, 1070, '', '删除语言地区', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1067/1070', 3, '', 0, 'lang-country-delete', 0, '删除语言地区'),
(3369, 3366, '', '语言国家列表', '', '', '', 'setting/lang_country/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_country-list', 0, '语言国家列表'),
(3370, 3366, '', '添加语言地区表单', '', '', '', 'setting/lang_country/form/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_country-form', 0, '添加语言地区表单'),
(3371, 3366, '', '保存语言地区', '', '', '', 'setting/lang_country/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_country-save', 0, '保存语言地区'),
(3372, 3367, '', '语言国家列表', '', '', '', 'setting/lang_country/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_country-list-646a377fb08e5', 0, '语言国家列表'),
(3373, 3367, '', '添加语言地区表单', '', '', '', 'setting/lang_country/form/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_country-form-646a377fb08f2', 0, '添加语言地区表单'),
(3374, 3367, '', '保存语言地区', '', '', '', 'setting/lang_country/save/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_country-save-646a377fb08fc', 0, '保存语言地区'),
(3375, 3368, '', '语言国家列表', '', '', '', 'setting/lang_country/list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_country-list-646a378a8e1b7', 0, '语言国家列表'),
(3376, 3368, '', '删除语言地区', '', '', '', 'setting/lang_country/del/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-lang_country-del', 0, '删除语言地区'),
(3377, 1066, '', '添加对外账号', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1064/1066', 3, '', 0, 'out-account-add', 0, '添加对外账号'),
(3378, 1066, '', '编辑对外账号', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1064/1066', 3, '', 0, 'out-account-edit', 0, '编辑对外账号'),
(3379, 1066, '', '删除对外账号', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1064/1066', 3, '', 0, 'out-account-delete', 0, '删除对外账号'),
(3380, 1066, '', '设置对外账号', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1064/1066', 3, '', 0, 'out-account-setting', 0, '设置对外账号'),
(3381, 3377, '', '对外接口账号信息', '', '', '', 'setting/system_out_account/index', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-system_out_account-index', 0, '对外接口账号信息'),
(3382, 3377, '', '对外接口账号添加', '', '', '', 'setting/system_out_account/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-system_out_account-save', 0, '对外接口账号添加'),
(3383, 3378, '', '对外接口账号信息', '', '', '', 'setting/system_out_account/index', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-system_out_account-index-646a385546279', 0, '对外接口账号信息'),
(3384, 3378, '', '对外接口账号添加', '', '', '', 'setting/system_out_account/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-system_out_account-save-646a385546286', 0, '对外接口账号添加'),
(3385, 3378, '', '对外接口账号修改', '', '', '', 'setting/system_out_account/update/<id>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-system_out_account-update', 0, '对外接口账号修改'),
(3386, 3379, '', '对外接口账号信息', '', '', '', 'setting/system_out_account/index', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-system_out_account-index-646a386512a5e', 0, '对外接口账号信息'),
(3387, 3379, '', '删除账号', '', '', '', 'setting/system_out_account/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-system_out_account', 0, '删除账号'),
(3388, 3380, '', '对外接口账号信息', '', '', '', 'setting/system_out_account/index', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-system_out_account-index-646a38719e553', 0, '对外接口账号信息'),
(3389, 3380, '', '设置账号推送接口', '', '', '', 'setting/system_out_account/set_up/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-system_out_account-set_up', 0, '设置账号推送接口'),
(3390, 145, '', '同步物流公司', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1073/145', 3, '', 0, 'express-sync', 0, '同步物流公司'),
(3391, 145, '', '编辑物流公司', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1073/145', 3, '', 0, 'express-edit', 0, '编辑物流公司'),
(3392, 3390, '', '获取物流公司列表', '', '', '', 'freight/express', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'freight-express', 0, '获取物流公司列表'),
(3393, 3390, '', '同步物流公司', '', '', '', 'freight/express/sync_express', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'freight-express-sync_express', 0, '同步物流公司'),
(3394, 3391, '', '获取物流公司列表', '', '', '', 'freight/express', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'freight-express-646a38ef9dbdd', 0, '获取物流公司列表'),
(3395, 3391, '', '获取修改物流公司表单', '', '', '', 'freight/express/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'freight-express-edit', 0, '获取修改物流公司表单'),
(3396, 3391, '', '修改物流公司', '', '', '', 'freight/express/<id>', 'PUT', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'freight-express-646a38ef9dbf3', 0, '修改物流公司'),
(3398, 229, '', '添加城市数据', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1073/229', 3, '', 0, 'system-city-add', 0, '添加城市数据'),
(3399, 229, '', '编辑城市数据', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1073/229', 3, '', 0, 'system-city-edit', 0, '编辑城市数据'),
(3400, 229, '', '删除城市数据', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1073/229', 3, '', 0, 'system-city-delete', 0, '删除城市数据'),
(3401, 229, '', '清除城市数据缓存', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/', '25/1073/229', 3, '', 0, 'system-city-clear-cache', 0, '清除城市数据缓存'),
(3402, 3398, '', '获取城市数据完整列表', '', '', '', 'setting/city/full_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-full_list', 0, '获取城市数据完整列表'),
(3403, 3398, '', '获取城市数据列表', '', '', '', 'setting/city/list/<parent_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-list', 0, '获取城市数据列表'),
(3404, 3398, '', '添加城市数据表单', '', '', '', 'setting/city/add/<parent_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-add', 0, '添加城市数据表单'),
(3405, 3398, '', '新增/修改城市数据', '', '', '', 'setting/city/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-save', 0, '新增/修改城市数据'),
(3406, 3399, '', '获取城市数据完整列表', '', '', '', 'setting/city/full_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-full_list-646a39aa87ef0', 0, '获取城市数据完整列表'),
(3407, 3399, '', '获取城市数据列表', '', '', '', 'setting/city/list/<parent_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-list-646a39aa87efe', 0, '获取城市数据列表'),
(3408, 3399, '', '修改城市数据表单', '', '', '', 'setting/city/<id>/edit', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-edit', 0, '修改城市数据表单'),
(3409, 3399, '', '新增/修改城市数据', '', '', '', 'setting/city/save', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-save-646a39aa87f12', 0, '新增/修改城市数据'),
(3410, 3400, '', '获取城市数据完整列表', '', '', '', 'setting/city/full_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-full_list-646a39c191645', 0, '获取城市数据完整列表'),
(3411, 3400, '', '获取城市数据列表', '', '', '', 'setting/city/list/<parent_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-list-646a39c191651', 0, '获取城市数据列表'),
(3412, 3400, '', '删除城市数据', '', '', '', 'setting/city/del/<city_id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-del', 0, '删除城市数据'),
(3413, 3401, '', '获取城市数据完整列表', '', '', '', 'setting/city/full_list', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-full_list-646a39ceb8c4c', 0, '获取城市数据完整列表'),
(3414, 3401, '', '获取城市数据列表', '', '', '', 'setting/city/list/<parent_id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-list-646a39ceb8c57', 0, '获取城市数据列表'),
(3415, 3401, '', '清除城市数据缓存', '', '', '', 'setting/city/clean_cache', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'setting-city-clean_cache', 0, '清除城市数据缓存'),
(3417, 1056, '', '一号通', 'admin', '', '', '', '', '[]', 10, 1, 1, 1, '/yihaotong', '12/1056', 1, '', 0, 'setting-yihaotong', 0, ''),
(3418, 3417, '', '一号通配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/yihaotong_config/3/18', '12/1056/3417', 1, '', 0, 'setting-yihaotong-config', 0, ''),
(3419, 1067, '', '翻译配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/lang_config/3/106', '25/1067', 1, '', 0, 'setting-lang-config', 0, ''),
(3420, 27, '', '用户充值', 'admin', '', '', '', '', '[]', 60, 1, 1, 1, '/marketing/recharge', '27', 1, '', 0, 'admin-marketing-recharge', 0, ''),
(3421, 165, '', '客服配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/kefu_config/2/69', '165', 1, '', 0, 'setting-kefu-config', 0, ''),
(3422, 3420, '', '充值配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/recharge_config/2/28', '27/3420', 1, '', 0, 'setting-recharge-config', 0, ''),
(3423, 9, '', '用户配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/user_config/2/100', '9', 1, '', 0, '', 0, ''),
(3424, 4, '', '订单配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/order_config/2/113', '4', 1, '', 0, '', 0, ''),
(3425, 27, '', '每日签到', 'admin', '', '', '', '', '[]', 57, 1, 1, 1, '/markering/sign', '27', 1, '', 0, 'admin-marketing-sign', 0, ''),
(3426, 3425, '', '签到配置', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/setting/sign_config/2/126', '27/3425', 1, '', 0, '', 0, ''),
(3427, 3425, '', '签到奖励', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/marketing/sign_rewards', '27/3425', 1, '', 0, '', 0, ''),
(3429, 165, '', '自动回复', 'admin', '', '', '', '', '[]', 7, 1, 1, 1, '/setting/store_service/auto_reply', '165', 1, '', 0, '', 0, ''),
(3430, 1695, '', '数据字典', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/code_data_dictionary', '25/1695', 1, '', 0, 'system-code-data_dictionary', 0, ''),
(3431, 3430, '', '查看数据字典', '', '', '', 'system/crud/data_dictionary/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-data_dictionary', 0, ''),
(3432, 3430, '', '删除数据字典', '', '', '', 'system/crud/data_dictionary/<id>', 'DELETE', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-data_dictionary', 0, ''),
(3433, 3430, '', '修改或者保存字典数据', '', '', '', 'system/crud/data_dictionary/<id?>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-data_dictionary', 0, ''),
(3434, 3430, '', '获取数据字典列表', '', '', '', 'system/crud/data_dictionary', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-data_dictionary', 0, ''),
(3435, 3299, '', '获取数据字典列表', '', '', '', 'system/crud/data_dictionary', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-data_dictionary-64d491f3da358', 0, ''),
(3436, 3299, '', '查看数据字典', '', '', '', 'system/crud/data_dictionary/<id>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-data_dictionary-64d491f3da391', 0, ''),
(3437, 3299, '', '修改或者保存字典数据', '', '', '', 'system/crud/data_dictionary/<id?>', 'POST', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-data_dictionary-64d491f3da3b8', 0, ''),
(3438, 3299, '', '获取可以进行关联的表名', '', '', '', 'system/crud/association_table', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-association_table', 0, ''),
(3439, 3299, '', '获取表的详细信息', '', '', '', 'system/crud/association_table/<tableName>', 'GET', '[]', 1, 1, 1, 1, '', '', 2, '', 0, 'system-crud-association_table', 0, '');
SQL
            ],
            [
                'code' => 520,
                'type' => -1,
                'table' => "system_notification",
                'sql' => "DROP TABLE `@table`"
            ],
            [
                'code' => 520,
                'type' => 1,
                'table' => "system_notification",
                'findSql' => "select * from information_schema.tables where table_name ='@table'",
                'sql' => "CREATE TABLE IF NOT EXISTS `@table` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `mark` varchar(50) NOT NULL DEFAULT '' COMMENT '标识',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '通知类型',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '通知场景说明',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '站内信（0：不存在，1：开启，2：关闭）',
  `system_title` varchar(256) NOT NULL DEFAULT '' COMMENT '站内信标题',
  `system_text` varchar(512) NOT NULL DEFAULT '' COMMENT '系统消息id',
  `is_wechat` tinyint(1) NOT NULL DEFAULT '0' COMMENT '公众号模板消息（0：不存在，1：开启，2：关闭）',
  `wechat_tempkey` varchar(255) NOT NULL DEFAULT '' COMMENT '模版消息tempkey',
  `wechat_content` varchar(255) NOT NULL DEFAULT '' COMMENT '模版消息内容',
  `wechat_kid` varchar(255) NOT NULL DEFAULT '' COMMENT '模版消息kid',
  `wechat_tempid` varchar(255) NOT NULL DEFAULT '' COMMENT '模版消息tempid',
  `is_routine` tinyint(1) NOT NULL DEFAULT '0' COMMENT '小程序订阅消息（0：不存在，1：开启，2：关闭）',
  `routine_tempkey` varchar(255) NOT NULL DEFAULT '' COMMENT '订阅消息id',
  `routine_content` varchar(255) NOT NULL DEFAULT '' COMMENT '订阅消息内容',
  `routine_kid` varchar(255) NOT NULL DEFAULT '' COMMENT '订阅消息kid',
  `routine_tempid` varchar(255) NOT NULL DEFAULT '' COMMENT '订阅消息tempid',
  `is_sms` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发送短信（0：不存在，1：开启，2：关闭）',
  `sms_id` varchar(32) NOT NULL DEFAULT '' COMMENT '短信id',
  `sms_text` varchar(255) NOT NULL DEFAULT '' COMMENT '短信模版内容',
  `is_ent_wechat` tinyint(1) NOT NULL DEFAULT '0' COMMENT '企业微信群通知（0：不存在，1：开启，2：关闭）',
  `ent_wechat_text` varchar(512) NOT NULL DEFAULT '' COMMENT '企业微信消息',
  `url` varchar(512) NOT NULL DEFAULT '' COMMENT '群机器人链接',
  `is_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'APP推送（0：不存在，1：开启，2：关闭）',
  `app_id` int(11) NOT NULL DEFAULT '0' COMMENT 'app推送id',
  `variable` varchar(256) NOT NULL DEFAULT '' COMMENT '变量',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型（1：用户，2：管理员）',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='通知设置'"
            ],
            [
                'code' => 520,
                'type' => -1,
                'table' => "system_notification",
                'sql' => <<<SQL
INSERT INTO `@table` (`id`, `mark`, `name`, `title`, `is_system`, `system_title`, `system_text`, `is_wechat`, `wechat_tempkey`, `wechat_content`, `wechat_kid`, `wechat_tempid`, `is_routine`, `routine_tempkey`, `routine_content`, `routine_kid`, `routine_tempid`, `is_sms`, `sms_id`, `sms_text`, `is_ent_wechat`, `ent_wechat_text`, `url`, `is_app`, `app_id`, `variable`, `type`, `add_time`) VALUES
(1, 'verify_code', '短信验证码', '短信验证码', 0, '短信验证码', '您的验证码是：{code}，有效期为{time}分钟。如非本人操作，可不予理会。', 0, '', '', '', '', 0, '', '', '', '', 1, '538393', '您的验证码是：{$code}，有效期为{$time}分钟。如非本人操作，可不予理会。', 0, '', '', 0, 0, '', 1, 0),
(2, 'bind_spread_uid', '绑定推广关系消息', '绑定推广关系消息', 1, '绑定下级通知', '恭喜，又一员猛将将永久绑定到您的团队，用户{nickname}加入您的队伍！', 0, '', '', '', '', 0, '', '', '', '', 0, '', '', 0, '', '', 0, 0, '{nikename}用户名', 1, 0),
(3, 'order_pay_success', '支付成功提醒消息', '支付成功提醒消息', 1, '购买成功通知', '您购买的商品已支付成功，支付金额{pay_price}元，订单号{order_id},感谢您的光临！', 1, '43216', '订单号{{character_string2.DATA}}\n下单时间{{time4.DATA}}\n商品名称{{thing3.DATA}}\n支付金额{{amount5.DATA}}', '', '', 1, '1927', '付款单号{{character_string1.DATA}}\n付款金额{{amount2.DATA}}\n付款时间{{date3.DATA}}', '', '', 1, '520268', '您购买的商品已支付成功，支付金额{$pay_price}元，订单号{$order_id},感谢您的光临！', 0, '', '', 0, 0, '{order_id}订单号,{total_num}商品总数,{pay_price}支付金额', 1, 0),
(4, 'order_postage_success', '发货提醒消息', '发货提醒消息', 1, '发货通知', '亲爱的用户{nickname}您的商品{store_name}，订单号{order_id}已发货，请注意查收', 1, '42984', '订单编号{{character_string2.DATA}}\n发货时间{{time12.DATA}}\n商品名称{{thing4.DATA}}\n快递公司{{thing13.DATA}}\n快递单号{{character_string14.DATA}}', '', '', 1, '1458', '快递单号{{character_string2.DATA}}\n快递公司{{thing1.DATA}}\n发货时间{{time3.DATA}}\n订单商品{{thing5.DATA}}', '', '', 1, '520269', '亲爱的用户{$nickname}您的商品{$store_name}，订单号{$order_id}已发货，请注意查收', 0, '', '', 0, 0, '{nickname}用户昵称,{store_name}商品名称,{order_id}订单号,{delivery_name}快递名称,{delivery_id}快递单号,{user_address}收货地址', 1, 0),
(5, 'order_deliver_success', '送货提醒消息', '送货提醒消息', 1, '发货通知', '亲爱的用户{nickname}您的商品{store_name}，订单号{order_id}已发货，请注意查收', 1, '50079', '订单号{{character_string1.DATA}}\n配送时间{{time8.DATA}}\n商品名称{{thing5.DATA}}\n配送员{{thing9.DATA}}\n配送员电话{{phone_number10.DATA}}', '', '', 1, '1128', '商品信息{{thing8.DATA}}\r\n订单编号{{character_string1.DATA}}\r\n配送人{{name4.DATA}}\r\n配送员电话{{phone_number10.DATA}}', '', '', 1, '520269', '亲爱的用户{$nickname}您的商品{$store_name}，订单号{$order_id}已发货，请注意查收', 0, '', '', 0, 0, '{nickname}用户昵称,{store_name}商品名称,{order_id}订单号,{delivery_name}配送员姓名,{delivery_id}配送员电话,{user_address}收货地址', 1, 0),
(6, 'order_take', '确认收货提醒消息', '确认收货提醒消息', 1, '确认收货通知', '亲，您的订单{order_id},商品{store_name}已确认收货,感谢您的光临！', 1, '42985', '订单编号{{character_string2.DATA}}\n签收时间{{character_string7.DATA}}\n商品名称{{thing4.DATA}}\n订单金额{{amount9.DATA}}', '', '', 1, '1481', '订单类型{{thing1.DATA}}\n订单商品{{thing2.DATA}}\n收货时间{{date5.DATA}}', '', '', 1, '520271', '亲，您的订单{$order_id},商品{$store_name}已确认收货,感谢您的光临！', 0, '', '', 0, 0, '{order_id}订单号,{store_name}商品名称', 1, 0),
(7, 'order_refund', '退款成功提醒消息', '退款成功提醒消息', 1, '退款成功通知', '您的订单{order_id}已同意退款,退款金额{refund_price}元。', 1, '46622', '订单编号{{character_string1.DATA}}\n退款时间{{time5.DATA}}\n商品名称{{thing2.DATA}}\n退款金额{{amount3.DATA}}', '', '', 1, '1451', '退款状态{{thing1.DATA}}\n退款商品{{thing2.DATA}}\n退款金额{{amount3.DATA}}\n退款单号{{character_string6.DATA}}', '', '', 1, '979010', '您的订单{$order_id}已同意退款,退款金额{$refund_price}元。', 0, '', '', 0, 0, '{order_id}订单号,{refund_price}退款金额,{pay_price}订单金额', 1, 0),
(8, 'send_order_refund_no_status', '退款失败提醒消息', '退款失败提醒消息', 1, '退款申请拒绝通知', '您好！您的订单{order_id}已拒绝退款。', 1, '46623', '订单编号{{character_string1.DATA}}\n商品名称{{thing2.DATA}}\n退款金额{{amount3.DATA}}\n驳回原因{{thing4.DATA}}', '', '', 1, '1451', '退款状态{{thing1.DATA}}\n退款商品{{thing2.DATA}}\n退款金额{{amount3.DATA}}\n退款单号{{character_string6.DATA}}', '', '', 0, '979001', '亲，您的订单{$order_id}退款申请已被拒绝，有疑问可咨询商城客服哈～', 0, '', '', 0, 0, '{order_id}订单号,{store_name}商品名称,{pay_price}订单金额', 1, 0),
(9, 'price_revision', '改价提醒消息', '改价提醒消息', 1, '改价通知', '您的订单{order_id}，实际支付金额已被修改为{pay_price}', 0, '', '', '', '', 0, '', '', '', '', 1, '528288', '您的订单{$order_id}，实际支付金额已被修改为{$pay_price}', 0, '', '', 0, 0, '{order_id}订单号,{pay_price}订单金额', 1, 0),
(10, 'order_pay_false', '未付款提醒消息', '未付款提醒消息', 1, '提醒付款通知', '您有未付款订单,订单号为:{order_id}，商品数量有限，请及时付款。', 0, '', '', '', '', 0, '', '', '', '', 1, '528116', '您有未付款订单,订单号为:{$order_id}，商品数量有限，请及时付款。', 0, '', '', 0, 0, '{order_id}订单号', 1, 0),
(11, 'recharge_success', '充值成功提醒消息', '充值成功提醒消息', 1, '充值成功通知', '您成功充值￥{price}，现剩余余额￥{now_money}元', 1, '52552', '充值时间{{time1.DATA}}\n充值金额{{amount3.DATA}}\n赠送金额{{amount4.DATA}}\n当前余额{{amount5.DATA}}', '', '', 1, '755', '交易单号{{character_string1.DATA}}\n充值金额{{amount3.DATA}}\n账户余额{{amount4.DATA}}\n充值时间{{date5.DATA}}', '', '', 0, '811355', '您成功充值￥{$price}，现剩余余额￥{$now_money}元', 0, '', '', 0, 0, '{order_id}充值订单,{price}充值金额,{now_money}现有余额', 1, 0),
(12, 'recharge_order_refund_status', '充值退款提醒消息', '充值退款提醒消息', 1, '充值退款通知', '亲，您充值的金额已退款,本次退款{refund_price}元', 1, '46622', '订单编号{{character_string1.DATA}}\n退款时间{{time5.DATA}}\n商品名称{{thing2.DATA}}\n退款金额{{amount3.DATA}}', '', '', 0, '', '', '', '', 0, '811356', '亲，您充值的金额已退款,本次退款￥{$refund_price}元', 0, '', '', 0, 0, '{refund_price}退款金额,{order_id}充值订单,{price}充值金额', 1, 0),
(13, 'user_extract', '提现成功提醒消息', '提现成功提醒消息', 1, '提现成功通知', '亲，您成功提现佣金{extract_number}元', 1, '52381', '提现时间{{time3.DATA}}\n提现金额{{amount2.DATA}}', '', '', 1, '1470', '提现状态{{thing1.DATA}}\n提现金额{{amount2.DATA}}\n提现账号{{thing3.DATA}}\n提现时间{{date4.DATA}}', '', '', 0, '980195', '亲，您的提现申请已经通过，{$price}元已经到账，请及时查收。', 0, '', '', 0, 0, '{extract_number}提现金额,{nickname}用户昵称,{date}提现时间', 1, 0),
(14, 'user_balance_change', '提现失败提醒消息', '提现失败提醒消息', 1, '提现失败通知', '亲，您发起的提现被驳回，返回佣金{extract_number}元', 0, '', '', '', '', 1, '1470', '提现状态{{thing1.DATA}}\n提现金额{{amount2.DATA}}\n提现账号{{thing3.DATA}}\n提现时间{{date4.DATA}}', '', '', 0, '979002', '亲，您的提现申请被驳回，返回佣金{$extract_number}元，有疑问可咨询商城客服哈～', 0, '', '', 0, 0, '{extract_number}提现金额,{nickname}用户昵称,{date}提现时间,{message}失败原因', 1, 0),
(15, 'order_brokerage', '佣金到账提醒消息', '佣金到账提醒消息', 1, '佣金到账通知', '亲，恭喜您成功获得佣金{brokerage_price}元', 0, '', '', '', '', 0, '', '', '', '', 0, '979008', '亲，恭喜您成功获得佣金{$brokerage_price}元。', 0, '', '', 0, 0, '{goods_name}商品名称,{goods_price}商品金额,{brokerage_price}分佣金额', 1, 0),
(16, 'integral_accout', '积分到账提醒消息', '积分到账提醒消息', 1, '积分到账通知', '亲，您成功获得积分{gain_integral}，现有积分{integral}', 0, '', '', '', '', 1, '335', '订单编号{{character_string2.DATA}}\n商品名称{{thing3.DATA}}\n支付金额{{amount4.DATA}}\n获得积分{{number5.DATA}}\n累计积分{{number6.DATA}}', '', '', 0, '979009', '亲，您成功获得积分{$gain_integral}，现有积分{$integral}。', 0, '', '', 0, 0, '{order_id}订单号,{store_name}商品名称,{pay_price}支付金额,{gain_integral}获取积分,{integral}现有积分', 1, 0),
(17, 'bargain_success', '砍价成功提醒消息', '砍价成功提醒消息', 1, '砍价成功通知', '亲，好腻害！你的朋友们已经帮你砍到底价了，商品名称{title}，底价{min_price}', 0, '', '', '', '', 1, '2727', '商品名称{{thing1.DATA}}\n底价{{amount2.DATA}}\n备注{{thing3.DATA}}', '', '', 0, '979007', '亲，好腻害！你的朋友们已经帮你砍到底价了，商品名称{$title}，底价{$min_price}。', 0, '', '', 0, 0, '{title}活动名称{min_price}最低价', 1, 0),
(18, 'open_pink_success', '开团成功提醒消息', '开团成功提醒消息', 1, '开团成功通知', '亲，您已成功参与拼团，活动名称{title}', 0, '', '', '', '', 1, '3353', '商品名称{{thing2.DATA}}\n拼团人数{{thing1.DATA}}\n备注{{thing3.DATA}}', '', '', 0, '979005', '亲，您已成功参与拼团，拼团名称{$title}。', 0, '', '', 0, 0, '{title}活动名称,{nickname}团长,{count}拼团人数,{pink_time}开团时间', 1, 0),
(19, 'can_pink_success', '参团成功提醒消息', '参团成功提醒消息', 1, '参团成功通知', '亲，您已成功参与拼团，活动名称{title}', 0, '', '', '', '', 1, '3353', '商品名称{{thing2.DATA}}\n拼团人数{{thing1.DATA}}\n备注{{thing3.DATA}}', '', '', 0, '978999', '亲，您已成功参与拼团，订单号是{$order_id}，商品名称{$title}。', 0, '', '', 0, 0, '{title}活动名称,{nickname}团长,{count}拼团人数,{pink_time}开团时间', 1, 0),
(20, 'order_user_groups_success', '拼团成功提醒消息', '拼团成功提醒消息', 1, '拼团成功通知', '亲，您的拼团已经完成了，拼团名称{title}，团长{nickname}', 0, '', '', '', '', 1, '3098', '活动名称{{thing1.DATA}}\n团长昵称{{thing12.DATA}}\n开团时间{{date5.DATA}}\n成团人数{{number2.DATA}}', '', '', 0, '979006', '亲，您已经成功拼团，拼团名称{$title}，团长{$nickname}，快去支付吧～', 0, '', '', 0, 0, '{title}活动名称,{nickname}团长,{count}拼团人数,{pink_time}开团时间', 1, 0),
(21, 'send_order_pink_fial', '拼团失败提醒消息', '拼团失败提醒消息', 1, '拼团失败通知', '亲，您的拼团失败，活动名称{title}', 0, '', '', '', '', 1, '3353', '商品名称{{thing2.DATA}}\n拼团人数{{thing1.DATA}}\n备注{{thing3.DATA}}', '', '', 0, '980197', '亲，您的拼团已经失败，拼团名称{$title}。', 0, '', '', 0, 0, '{title}活动名称{count}拼团人数', 1, 0),
(22, 'send_order_pink_clone', '取消拼团提醒消息', '取消拼团提醒消息', 1, '取消拼团通知', '亲，您的拼团取消，活动名称{title}', 0, '', '', '', '', 1, '3353', '商品名称{{thing2.DATA}}\n拼团人数{{thing1.DATA}}\n备注{{thing3.DATA}}', '', '', 0, '979000', '亲，您的拼团已取消，拼团名称{$title}。', 0, '', '', 0, 0, '{title}活动名称{count}拼团人数', 1, 0),
(23, 'sign_remind', '签到提醒', '签到提醒', 1, '签到提醒', '亲，{$site_name}提醒您，今日记得签到并领取奖励哦～', 0, '', '', '', '', 0, '', '', '', '', 1, '980181', '亲，{$site_name}提醒您，今日记得签到并领取奖励哦～', 0, '', '', 0, 0, '{site_name}商品名称', 1, 0),
(24, 'admin_pay_success_code', '用户下单客服提醒消息', '用户下单客服提醒消息', 1, '您有新的订单待处理', '您有一笔支付成功的订单待处理，订单号{order_id}!', 1, '48089', '订单类型{{short_thing6.DATA}}\n订单编号{{character_string1.DATA}}\n订单时间{{time2.DATA}}', '', '', 0, '', '', '', '', 1, '520273', '{$admin_name}管理员,您有一笔支付成功的订单待处理，订单号{$order_id}!', 1, '您有个新订单请注意查收\\n订单号：{order_id}', '', 0, 0, '{admin_name}管理员,{order_id}订单号', 2, 0),
(25, 'kefu_send_extract_application', '用户提现申请客服提醒消息', '用户提现申请客服提醒消息', 1, '你有个新的提现申请待处理', '您有一笔提现申请待处理，提现金额{money}!', 1, '48089', '订单类型{{short_thing6.DATA}}\n订单编号{{character_string1.DATA}}\n订单时间{{time2.DATA}}', '', '', 0, '', '', '', '', 1, '980196', '{$admin_name}管理员,您有一笔提现申请待处理，订单号{$order_id}!', 1, '您有个提现申请请注意查收\\n>提现金额{money}', '', 0, 0, '{nickname}用户昵称,{money}提现金额', 2, 0),
(26, 'send_admin_confirm_take_over', '用户收货客服提醒消息', '用户收货客服提醒消息', 1, '你有个新的用户收货待处理', '您有一笔订单已经确认收货，订单号{order_id}!', 1, '48089', '订单类型{{short_thing6.DATA}}\n订单编号{{character_string1.DATA}}\n订单时间{{time2.DATA}}', '', '', 0, '', '', '', '', 1, '520422', '{$admin_name}管理员,您有一笔订单已经确认收货，订单号{$order_id}!', 1, '您有个订单确认收货\\n>订单号{order_id}', '', 0, 0, '{storeTitle}商品名称,{order_id}订单号', 2, 0),
(27, 'send_order_apply_refund', '用户申请退款客服提醒消息', '用户申请退款客服提醒消息', 1, '您有新的退款待处理', '您有一笔退款订单待处理，订单号{order_id}!', 1, '48089', '订单类型{{short_thing6.DATA}}\n订单编号{{character_string1.DATA}}\n订单时间{{time2.DATA}}', '', '', 0, '', '', '', '', 1, '520274', '{$admin_name}管理员,您有一笔退款订单待处理，订单号{$order_id}!', 1, '您有个订单退款请注意查收\\n订单号：{order_id}', '', 0, 0, '{admin_name}管理员,{order_id}订单号', 2, 0);
SQL
            ],
            [
                'code' => 520,
                'type' => -1,
                'table' => "system_route",
                'sql' => "DROP TABLE `@table`"
            ],
            [
                'code' => 520,
                'type' => 1,
                'table' => "system_route",
                'findSql' => "select * from information_schema.tables where table_name ='@table'",
                'sql' => "CREATE TABLE IF NOT EXISTS `@table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类',
  `app_name` varchar(20) NOT NULL DEFAULT 'api' COMMENT '应用名',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '路由名称',
  `describe` text NOT NULL COMMENT '功能描述',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '路由路径',
  `method` enum('POST','GET','DELETE','PUT','*') NOT NULL DEFAULT 'GET' COMMENT '路由请求方式',
  `file_path` varchar(255) NOT NULL DEFAULT '' COMMENT '文件路径',
  `action` varchar(255) NOT NULL DEFAULT '' COMMENT '方法名称',
  `query` longtext NOT NULL COMMENT 'get请求参数',
  `header` longtext NOT NULL COMMENT 'header',
  `request` longtext NOT NULL COMMENT '请求数据',
  `request_type` varchar(100) NOT NULL DEFAULT '' COMMENT '请求类型',
  `response` longtext NOT NULL COMMENT '返回数据',
  `request_example` longtext NOT NULL COMMENT '请求示例',
  `response_example` longtext NOT NULL COMMENT '返回示例',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型',
  `add_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `delete_time` timestamp NULL DEFAULT NULL COMMENT '删除字段',
  PRIMARY KEY (`id`),
  KEY `path` (`path`)
) ENGINE=InnoDB AUTO_INCREMENT=1695 DEFAULT CHARSET=utf8 COMMENT='路由规则表'"
            ],
            [
                'code' => 520,
                'type' => 1,
                'table' => "system_sign_reward",
                'findSql' => "select * from information_schema.tables where table_name ='@table'",
                'sql' => "CREATE TABLE IF NOT EXISTS `@table` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型，0连续签到奖励，1累计签到奖励',
  `days` int(11) NOT NULL DEFAULT '0' COMMENT '天数',
  `point` int(11) NOT NULL DEFAULT '0' COMMENT '赠送积分',
  `exp` int(11) NOT NULL DEFAULT '0' COMMENT '赠送经验',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='系统签到奖励表'"
            ],
            [
                'code' => 520,
                'type' => 3,
                'table' => "system_storage",
                'field' => "cdn",
                'findSql' => "show columns from `@table` like 'cdn'",
                'sql' => "ALTER TABLE `@table` ADD `cdn` varchar(255) NOT NULL DEFAULT '' COMMENT 'CDN加速域名'"
            ],
            [
                'code' => 520,
                'type' => 3,
                'table' => "user",
                'field' => "sign_remind",
                'findSql' => "show columns from `@table` like 'sign_remind'",
                'sql' => "ALTER TABLE `@table` ADD `sign_remind` tinyint(1) NOT NULL DEFAULT '0' COMMENT '签到提醒状态'"
            ],
            [
                'code' => 520,
                'type' => 3,
                'table' => "user",
                'field' => "user_extract",
                'findSql' => "show columns from `@table` like 'extract_fee'",
                'sql' => "ALTER TABLE `@table` ADD `extract_fee` decimal(12,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '提现手续费'"
            ],
            [
                'code' => 520,
                'type' => 3,
                'table' => "wechat_key",
                'field' => "key_type",
                'findSql' => "show columns from `@table` like 'key_type'",
                'sql' => "ALTER TABLE `@table` ADD `key_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '回复类型，0公众号自动回复，1客服自动回复'"
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
