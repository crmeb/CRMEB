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
            $limit = 100;
            if (!$this->setIsUpgrade('coupon')) {
                $res = $this->handleCoupon((int)$sleep, (int)$page, (int)$limit);
                return app('json')->success($res);
            } else {
                $this->setEnv();
                file_put_contents(app()->getRootPath() . '.version', "version=" . $data['new_version'] . "\nversion_code=" . $data['new_code'] . "\nplatform=CRMEB\napp_id=ze7x9rxsv09l6pvsyo" . "\napp_key=fuF7U9zaybLa5gageVQzxtxQMFnvU2OI");
                $this->services->generateSignature();
                return app('json')->success(['sleep' => -1]);
            }
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
        $data['new_version'] = 'CRMEB-BZ v4.7.0';
        $data['new_code'] = 470;
        $data['update_sql'] = [
            [
                'code' => 470,
                'type' => -1,
                'table' => "store_coupon_issue_user",
                'sql' => "ALTER TABLE `@table` DROP INDEX `uid`"
            ],
            [
                'code' => 470,
                'type' => 3,
                'table' => "store_coupon_issue",
                'field' => "receive_limit",
                'findSql' => "show columns from `@table` like 'receive_limit'",
                'sql' => "ALTER TABLE `@table` ADD `receive_limit` int(10) NOT NULL DEFAULT '0' COMMENT '每个人个领取的优惠券数量'"
            ],
            [
                'code' => 470,
                'type' => 3,
                'table' => "store_coupon_product",
                'field' => "category_id",
                'findSql' => "show columns from `@table` like 'category_id'",
                'sql' => "ALTER TABLE `@table` ADD `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id'"
            ],
            [
                'code' => 470,
                'type' => -1,
                'table' => "store_order",
                'sql' => "ALTER TABLE `@table` CHANGE `cart_id` `cart_id` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '购物车id'"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config_tab",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'online_translation'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='store'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, @tabId, '机器翻译配置', 'online_translation', 1, 0, '', 0, 0)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config_tab",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'fey_config'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='printing_deploy'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, @tabId, '飞鹅云配置', 'fey_config', 1, 0, '', 3, 0)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config_tab",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'allinpay'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, @tabId, '通联支付', 'allinpay', 1, 0, '', 3, 0)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config_tab",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'pay_basic'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, @tabId, '基础配置', 'pay_basic', 1, 0, '', 3, 100)"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'offline_pay'",
                'sql' => "DELETE FROM `@table` WHERE `eng_title` = 'offline_pay'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'balance_pay'",
                'sql' => "DELETE FROM `@table` WHERE `eng_title` = 'balance_pay'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config_tab",
                'findSql' => "select id from @table where `eng_title` = 'friend_pay'",
                'sql' => "DELETE FROM `@table` WHERE `eng_title` = 'friend_pay'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'wechat_appid'",
                'sql' => "UPDATE `@table` SET `desc` = '微信公众号的AppID' WHERE `menu_name` = 'wechat_appid'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'wechat_appsecret'",
                'sql' => "UPDATE `@table` SET `desc` = '微信公众号的AppSecret' WHERE `menu_name` = 'wechat_appsecret'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'wechat_encodingaeskey'",
                'sql' => "UPDATE `@table` SET `desc` = '公众号消息加解密Key,在使用安全模式情况下要填写该值，请先在管理中心修改，然后填写该值，仅支持认证服务号' WHERE `menu_name` = 'wechat_encodingaeskey'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'wechat_qrcode'",
                'sql' => "UPDATE `@table` SET `desc` = '引导关注公众号显示的公众号关注二维码' WHERE `menu_name` = 'wechat_qrcode'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'pay_weixin_open'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay_basic'",
                'sql' => "UPDATE `@table` SET `config_tab_id` = @tabId,`parameter` = '0=>关闭\nweixin=>微信\nallinpay=>通联',`upload_type` = 1,`value` = '\"weixin\"',`info` = '微信支付',`desc` = '请选择微信支付通道，关闭用户端不展示',`sort` = 100 WHERE `menu_name` = 'pay_weixin_open'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'store_free_postage'",
                'sql' => "UPDATE `@table` SET `desc` = '商城商品满多少金额即可包邮，此项优先于其他的运费设置' WHERE `menu_name` = 'store_free_postage'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'api'",
                'sql' => "UPDATE `@table` SET `desc` = '配置服务器域名使用的接口地址，直接复制输入框内容（此项系统生成，无法修改）' WHERE `menu_name` = 'api'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'store_stock'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`desc` = '商品待补货数量低于多少时，提示库存不足' WHERE `menu_name` = 'store_stock'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'stor_reason'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='refund_config'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`config_tab_id` = @tabId,`upload_type` = 1 WHERE `menu_name` = 'stor_reason'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'h5_avatar'",
                'sql' => "UPDATE `@table` SET `info` = '用户默认头像',`desc` = '用户默认头像，后台添加用户以及用户登录的默认头像显示，尺寸(80*80)' WHERE `menu_name` = 'h5_avatar'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'offline_pay_status'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay_basic'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`config_tab_id` = @tabId,`upload_type` = 1,`info` = '线下支付',`desc` = '线下支付请选择开启或关闭',`sort` = 89 WHERE `menu_name` = 'offline_pay_status'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'recharge_switch'",
                'sql' => "UPDATE `@table` SET `desc` = '仅小程序端的充值开关，小程序提交审核前,需要关闭此功能' WHERE `menu_name` = 'recharge_switch'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'pay_success_printing_switch'",
                'sql' => "UPDATE `@table` SET `desc` = '支付成功自动小票打印功能，需要购买易联云K4或者K6无线打印机，或者购买飞鹅云V58系列' WHERE `menu_name` = 'pay_success_printing_switch'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'develop_id'",
                'sql' => "UPDATE `@table` SET `desc` = '易联云申请应用后页面开发者信息中的用户ID' WHERE `menu_name` = 'develop_id'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'printing_api_key'",
                'sql' => "UPDATE `@table` SET `desc` = '易联云申请应用后页面开发者信息中的应用密钥' WHERE `menu_name` = 'printing_api_key'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'printing_client_id'",
                'sql' => "UPDATE `@table` SET `desc` = '易联云申请应用后页面开发者信息中的应用ID' WHERE `menu_name` = 'printing_client_id'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'terminal_number'",
                'sql' => "UPDATE `@table` SET `desc` = '易联云打印机标签上的终端号' WHERE `menu_name` = 'terminal_number'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'yue_pay_status'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay_basic'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`config_tab_id` = @tabId,`upload_type` = 1,`info` = '余额支付',`sort` = 95 WHERE `menu_name` = 'yue_pay_status'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'copy_product_apikey'",
                'sql' => "UPDATE `@table` SET `desc` = '注册99api采集接口在个人中心复制key' WHERE `menu_name` = 'copy_product_apikey'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'member_price_status'",
                'sql' => "UPDATE `@table` SET `desc` = '商城会员折扣价格展示' WHERE `menu_name` = 'member_price_status'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'store_user_mobile'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`desc` = '用户在授权之后强制绑定手机号，可以实现用户多端统一' WHERE `menu_name` = 'store_user_mobile'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'ali_pay_status'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay_basic'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`config_tab_id` = @tabId,`parameter` = '0=>关闭\nalipay=>支付宝\nallinpay=>通联',`upload_type` = 1,`value` = '\"alipay\"',`info` = '支付宝支付',`desc` = '请选择支付宝通道，关闭用户端不显示',`sort` = 98 WHERE `menu_name` = 'ali_pay_status'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'alipay_public_key'",
                'sql' => "UPDATE `@table` SET `desc` = '支付宝加签完成后申城的支付宝公钥' WHERE `menu_name` = 'alipay_public_key'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'config_export_siid'",
                'sql' => "UPDATE `@table` SET `desc` = '请购买快递100二代云打印机(KX100L3)，官网：https://www.kuaidi100.com/cloud/print/cloudprinterSecond.shtml' WHERE `menu_name` = 'config_export_siid'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'service_feedback'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`desc` = '暂无客服在线是，联系客服跳转的客服反馈页面的显示文字' WHERE `menu_name` = 'service_feedback'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'tourist_avatar'",
                'sql' => "DELETE FROM `@table` WHERE `menu_name` = 'tourist_avatar'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'pc_logo'",
                'sql' => "UPDATE `@table` SET `status` = 0 WHERE `menu_name` = 'pc_logo'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'record_No'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = '网站的备案号，显示在H5和PC端底部' WHERE `menu_name` = 'record_No'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'routine_contact_type'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`parameter` = '0=>跟随系统\n1=>小程序客服',`upload_type` = 1,`desc` = '跟随系统：跟随系统使用默认客服、电话或者跳转链接；小程序客服：需要在小程序后台配置客服用户；' WHERE `menu_name` = 'routine_contact_type'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'station_open'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`upload_type` = 1,`desc` = '站点开始|关闭（用于升级等临时关闭），关闭后前端会弹窗显示站点升级中，请稍后访问' WHERE `menu_name` = 'station_open'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'uni_brokerage_price'",
                'sql' => "UPDATE `@table` SET `info` = '推广佣金单价' WHERE `menu_name` = 'uni_brokerage_price'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'is_self_brokerage'",
                'sql' => "UPDATE `@table` SET `sort` = 99 WHERE `menu_name` = 'is_self_brokerage'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'refund_name'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = '用户退货退款后台同意之后，显示在退货订单详情显示的接受退货的人员姓名' WHERE `menu_name` = 'refund_name'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'refund_phone'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = '用户退货退款后台同意之后，显示在退货订单详情显示的接受退货的人员电话' WHERE `menu_name` = 'refund_phone'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'refund_address'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = '用户退货退款后台同意之后，显示在退货订单详情显示的接受退货的地址信息' WHERE `menu_name` = 'refund_address'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'wechat_open_app_id'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`info` = '开放平台AppID',`desc` = '微信开放平台申请网页应用后给予的AppID' WHERE `menu_name` = 'wechat_open_app_id'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'wechat_open_app_secret'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`info` = '开放平台AppSecret',`desc` = '微信开放平台申请网页应用后给予的AppSecret' WHERE `menu_name` = 'wechat_open_app_secret'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'contact_number'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = 'PC底部显示的联系电话' WHERE `menu_name` = 'contact_number'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'company_address'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = 'PC底部显示的公司地址' WHERE `menu_name` = 'company_address'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'copyright'",
                'sql' => "DELETE FROM `@table` WHERE `menu_name` = 'copyright'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'site_keywords'",
                'sql' => "UPDATE `@table` SET `status` = 0 WHERE `menu_name` = 'site_keywords'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'site_description'",
                'sql' => "UPDATE `@table` SET `status` = 0 WHERE `menu_name` = 'site_description'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'product_phone_buy_url'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`upload_type` = 1,`desc` = '商品详情手机购买显示公众号码或者小程序码' WHERE `menu_name` = 'product_phone_buy_url'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'customer_type'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`upload_type` = 1,`desc` = '系统客服：点击联系客服使用系统的自带客服；拨打电话：点击联系客服拨打客服电话；跳转链接：跳转外部链接联系客服' WHERE `menu_name` = 'customer_type'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'customer_url'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = '客服类型选择跳转链接时，跳转的链接地址' WHERE `menu_name` = 'customer_url'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'customer_phone'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = '客服类型选择不打电话是，用户点击联系客服的联系电话' WHERE `menu_name` = 'customer_phone'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'wechat_app_appid'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = '微信开放平台申请移动应用后给予的APPID' WHERE `menu_name` = 'wechat_app_appid'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'wechat_app_appsecret'",
                'sql' => "UPDATE `@table` SET `upload_type` = 1,`width` = 0,`desc` = '微信开放平台申请移动应用后给予的AppSecret' WHERE `menu_name` = 'wechat_app_appsecret'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'statistic_script'",
                'sql' => "UPDATE `@table` SET `desc` = '程序访问统计代码，填写script标签内的内容' WHERE `menu_name` = 'statistic_script'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'weixin_ckeck_file'",
                'sql' => "UPDATE `@table` SET `desc` = '配置微信网页授权域名时候下载的微信校验文件，在此处上传' WHERE `menu_name` = 'weixin_ckeck_file'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'ico_path'",
                'sql' => "UPDATE `@table` SET `desc` = '程序ICO图标，更换后需要清除浏览器缓存' WHERE `menu_name` = 'ico_path'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'print_type'",
                'sql' => "UPDATE `@table` SET `parameter` = '1=>易联云\n2=>飞鹅云',`desc` = '打印平台选择' WHERE `menu_name` = 'print_type'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'customer_corpId'",
                'sql' => "UPDATE `@table` SET `width` = 0,`desc` = '如果客服链接填写企业微信客服，小程序需要跳转企业微信客服的话需要配置此项，并且在小程序客服中绑定企业ID' WHERE `menu_name` = 'customer_corpId'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'friend_pay_status'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='pay_basic'",
                'sql' => "UPDATE `@table` SET `input_type` = 'input',`config_tab_id` = @tabId,`info` = '好友代付开关' WHERE `menu_name` = 'friend_pay_status'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'aliyun_RegionId'",
                'sql' => "UPDATE `@table` SET `status` = 0 WHERE `menu_name` = 'aliyun_RegionId'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'hs_accesskey'",
                'sql' => "UPDATE `@table` SET `desc` = '' WHERE `menu_name` = 'hs_accesskey'"
            ],
            [
                'code' => 470,
                'type' => 7,
                'table' => "system_config",
                'findSql' => "select id from @table where `menu_name` = 'hs_secretkey'",
                'sql' => "UPDATE `@table` SET `desc` = '' WHERE `menu_name` = 'hs_secretkey'"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'fey_user'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='fey_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'fey_user', 'text', 'input', @tabId, '', 1, '', 0, 0, '\"\"', '飞鹅云USER', '飞鹅云后台注册账号', 10, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'fey_ukey'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='fey_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'fey_ukey', 'text', 'input', @tabId, '', 1, '', 0, 0, '\"\"', '飞鹅云UYEK', '飞鹅云后台注册账号后生成的UKEY 【备注：这不是填打印机的KEY】', 7, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'fey_sn'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='fey_config'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'fey_sn', 'text', 'input', @tabId, '', 1, '', 100, 0, '\"\"', '飞鹅云SN', '打印机标签上的编号，必须要在管理后台里添加打印机或调用API接口添加之后，才能调用API', 0, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'allin_private_key'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='allinpay'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'allin_private_key', 'text', 'input', @tabId, '', 1, '', 100, 5, '\"\"', '通联MD5私钥', '通联支付的MD5私钥，可以在商户后台设置中进行配置', 96, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'allin_cusid'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='allinpay'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'allin_cusid', 'text', '', @tabId, '', 1, '', 0, 0, '\"\"', '通联商户号', '通联支付商户号，由贵公司申请获得', 99, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'allin_appid'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='allinpay'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'allin_appid', 'text', '', @tabId, '', 1, '', 100, 0, '\"\"', '通联支付Appid', '通联商户后台的设置-》对接设置中查看', 98, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'queue_open'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='web_site'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'queue_open', 'radio', 'input', @tabId, '0=>关闭\n1=>开启', 1, '', 0, 0, '\"0\"', '消息队列', '是否启用消息队列，启用后提升程序运行速度，启用前必须配置Redis缓存，文档地址：https://doc.crmeb.com/single/crmeb_v4/7217', 0, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'get_avatar'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='routine'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'get_avatar', 'radio', 'input', @tabId, '0=>关闭\n1=>开启', 1, '', 0, 0, '\"0\"', '强制获取昵称头像', '是否在小程序用户授权之后，弹窗获取用户的昵称和头像', 0, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'share_qrcode'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='wechat'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'share_qrcode', 'radio', 'input', @tabId, '0=>商城\n1=>公众号', 1, '', 0, 0, '\"0\"', '公众号推广码类型', '公众号生成的推广码类型：商城：扫码直接进入商城，公众号：扫码进入公众号后推送商城的链接', 0, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'member_brokerage'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='brokerage_set'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'member_brokerage', 'radio', 'input', @tabId, '1=>开启\n0=>关闭', 1, '', 0, 0, '\"0\"', '购买付费会员返佣', '购买付费会员是否按照设置的佣金比例进行返佣', 98, 1)"
            ],
            [
                'code' => 470,
                'type' => 6,
                'table' => "system_config",
                'whereTable' => "system_config_tab",
                'findSql' => "select id from @table where `menu_name` = 'user_brokerage_type'",
                'whereSql' => "SELECT id as tabId FROM `@whereTable` WHERE `eng_title`='brokerage_set'",
                'sql' => "INSERT INTO `@table` VALUES (NULL, 'user_brokerage_type', 'radio', 'input', @tabId, '0=>按照商品价格返佣\n1=>按照实际支付价格返佣', 1, '', 0, 0, '\"0\"', '返佣类型', '选择返佣类型，按照商品价格返佣（按照商品售价计算返佣金额）以及按照实际支付价格返佣（按照商品的实际支付价格计算返佣 ）', 97, 1)"
            ],
            [
                'code' => 470,
                'type' => -1,
                'table' => "system_menus",
                'sql' => "truncate table `@table`"
            ],
            [
                'code' => 470,
                'type' => -1,
                'table' => "system_menus",
                'sql' => <<<SQL
INSERT INTO `@table` (`id`, `pid`, `icon`, `menu_name`, `module`, `controller`, `action`, `api_url`, `methods`, `params`, `sort`, `is_show`, `is_show_path`, `access`, `menu_path`, `path`, `auth_type`, `header`, `is_header`, `unique_auth`, `is_del`) VALUES
(1, 0, 'md-basket', '商品', 'admin', 'product', 'index', '', '', '[]', 115, 1, 0, 1, '/product', '', 1, '0', 1, 'admin-product', 0),
(2, 1, '', '商品管理', 'admin', 'product.product', 'index', '', '', '[]', 1, 1, 0, 1, '/product/product_list', '', 1, '', 0, 'admin-store-storeProuduct-index', 0),
(3, 1, '', '商品分类', 'admin', 'product.storeCategory', 'index', '', '', '[]', 1, 1, 0, 1, '/product/product_classify', '', 1, 'product', 0, 'admin-store-storeCategory-index', 0),
(4, 0, 'md-cart', '订单', 'admin', 'order', 'index', '', '', '[]', 120, 1, 0, 1, '/order', '', 1, 'home', 1, 'admin-order', 0),
(5, 4, '', '订单管理', 'admin', 'order.store_order', 'index', '', '', '[]', 10, 1, 0, 1, '/order/list', '4', 1, 'order', 0, 'admin-order-storeOrder-index', 0),
(6, 1, '', '商品评论', 'admin', 'store.store_product_reply', 'index', '', '', '[]', 0, 1, 0, 1, '/product/product_reply', '', 1, 'product', 0, 'product-product-reply', 0),
(7, 0, 'md-home', '主页', 'admin', 'index', '', '', '', '[]', 127, 1, 0, 1, '/home/', '', 1, 'home', 1, 'admin-index-index', 0),
(9, 0, 'md-person', '用户', 'admin', 'user.user', '', '', '', '[]', 125, 1, 0, 1, '/user', '', 1, 'user', 1, 'admin-user', 0),
(10, 9, '', '用户管理', 'admin', 'user.user', 'index', '', '', '[]', 10, 1, 0, 1, '/user/list', '', 1, 'user', 0, 'admin-user-user-index', 0),
(11, 9, '', '用户等级', 'admin', 'user.user_level', 'index', '', '', '[]', 7, 1, 0, 1, '/user/level', '', 1, 'user', 0, 'user-user-level', 0),
(12, 0, 'md-settings', '设置', 'admin', 'setting.system_config', 'index', '', '', '[]', 1, 1, 0, 1, '/setting', '', 1, 'setting', 1, 'admin-setting', 0),
(14, 12, '', '管理权限', 'admin', 'setting.system_admin', '', '', '', '[]', 0, 1, 0, 1, '/setting/auth/list', '', 1, 'setting', 0, 'setting-system-admin', 0),
(19, 14, '', '角色管理', 'admin', 'setting.system_role', 'index', '', '', '[]', 1, 1, 0, 1, '/setting/system_role/index', '', 1, 'setting', 0, 'setting-system-role', 0),
(20, 14, '', '管理员列表', 'admin', 'setting.system_admin', 'index', '', '', '[]', 1, 1, 0, 1, '/setting/system_admin/index', '', 1, 'setting', 0, 'setting-system-list', 0),
(21, 14, '', '权限规则', 'admin', 'setting.system_menus', 'index', '', '', '[]', 1, 1, 0, 1, '/setting/system_menus/index', '', 1, 'setting', 0, 'setting-system-menus', 0),
(22, 1, '', '产品添加', 'admin', 'store.store_product', 'save', '', '', '[]', 1, 1, 1, 1, '/product/add_product', '', 1, 'product', 0, 'product-product-save', 0),
(23, 12, '', '系统设置', 'admin', 'setting.system_config', 'index', '', '', '[]', 10, 1, 0, 1, '/setting/system_config', '', 1, 'setting', 0, 'setting-system-config', 0),
(25, 0, 'md-hammer', '维护', 'admin', 'system', '', '', '', '[]', 0, 1, 0, 1, '/system', '', 1, 'setting', 1, 'admin-system', 0),
(26, 0, 'ios-people', '分销', 'admin', 'agent', '', '', '', '[]', 104, 1, 0, 1, '/agent', '', 1, 'user', 1, 'admin-agent', 0),
(27, 0, 'ios-paper-plane', '营销', 'admin', 'marketing', '', '', '', '[]', 110, 1, 0, 1, '/marketing', '', 1, 'home', 1, 'admin-marketing', 0),
(28, 26, '', '分销设置', 'admin', 'setting.system_config', '', '', '', '[]', 1, 1, 0, 1, '/setting/system_config_retail/2/9', '', 1, 'setting', 0, 'setting-system-config', 0),
(29, 26, '', '分销员管理', 'admin', 'agent.agent_manage', 'index', '', '', '[]', 99, 1, 0, 1, '/agent/agent_manage/index', '', 1, 'user', 0, 'agent-agent-manage', 0),
(30, 27, '', '优惠券', 'admin', 'marketing.store_coupon', '', '', '', '[]', 100, 1, 0, 1, '/marketing/store_coupon', '27', 1, 'marketing', 0, 'marketing-store_coupon-index', 0),
(31, 27, '', '砍价管理', 'admin', 'marketing.store_bargain', '', '', '', '[]', 85, 1, 0, 1, '/marketing/store_bargain', '27', 1, 'marketing', 0, 'marketing-store_bargain-index', 0),
(32, 27, '', '拼团管理', 'admin', 'marketing.store_combination', '', '', '', '[]', 80, 1, 0, 1, '/marketing/store_combination', '27', 1, 'marketing', 0, 'marketing-store_combination-index', 0),
(33, 27, '', '秒杀管理', 'admin', 'marketing.store_seckill', '', '', '', '[]', 75, 1, 0, 1, '/marketing/store_seckill', '27', 1, 'marketing', 0, 'marketing-store_seckill-index', 0),
(34, 27, '', '积分管理', 'admin', 'marketing.user_point', '', '', '', '[]', 95, 1, 0, 1, '/marketing/user_point', '27', 1, 'marketing', 0, 'marketing-user_point-index', 0),
(35, 0, 'logo-usd', '财务', 'admin', 'finance', '', '', '', '[]', 90, 1, 0, 1, '/finance', '', 1, 'home', 1, 'admin-finance', 0),
(36, 35, '', '财务操作', 'admin', 'finance', '', '', '', '[]', 1, 1, 0, 1, '/finance/user_extract', '', 1, 'finance', 0, 'finance-user_extract-index', 0),
(37, 35, '', '财务记录', 'admin', 'finance', '', '', '', '[]', 1, 1, 0, 1, '/finance/user_recharge', '', 1, 'finance', 0, 'finance-user-recharge-index', 0),
(38, 35, '', '佣金记录', 'admin', 'finance', '', '', '', '[]', 1, 1, 0, 1, '/finance/finance', '', 1, 'finance', 0, 'finance-finance-index', 0),
(39, 36, '', '提现申请', 'admin', 'finance.user_extract', '', '', '', '[]', 1, 1, 0, 1, '/finance/user_extract/index', '', 1, 'finance', 0, 'finance-user_extract', 0),
(40, 37, '', '充值记录', 'admin', 'finance.user_recharge', '', '', '', '[]', 1, 1, 0, 1, '/finance/user_recharge/index', '', 1, 'finance', 0, 'finance-user-recharge', 0),
(42, 38, '', '佣金记录', 'admin', 'finance.finance', '', '', '', '[]', 1, 1, 0, 1, '/finance/finance/commission', '', 1, 'finance', 0, 'finance-finance-commission', 0),
(43, 0, 'ios-book', '内容', 'admin', 'cms', '', '', '', '[]', 85, 1, 0, 1, '/cms', '', 1, 'home', 1, 'admin-cms', 0),
(44, 43, '', '文章管理', 'admin', 'cms.article', 'index', '', '', '[]', 1, 1, 0, 1, '/cms/article/index', '', 1, 'cms', 0, 'cms-article-index', 0),
(45, 43, '', '文章分类', 'admin', 'cms.article_category', 'index', '', '', '[]', 1, 1, 0, 1, '/cms/article_category/index', '', 1, 'cms', 0, 'cms-article-category', 0),
(46, 43, '', '文章添加', 'admin', 'cms.article', 'add_article', '', '', '[]', 0, 1, 1, 1, '/cms/article/add_article', '', 1, 'cms', 0, 'cms-article-creat', 0),
(47, 65, '', '系统日志', 'admin', 'system.system_log', 'index', '', '', '[]', 0, 1, 0, 1, '/system/maintain/system_log/index', '', 1, 'system', 0, 'system-maintain-system-log', 0),
(48, 7, '', '控制台', 'admin', 'index', 'index', '', '', '[]', 127, 1, 0, 1, '/home/index', '', 1, 'home', 0, '', 1),
(56, 25, '', '开发配置', 'admin', 'system', '', '', '', '[]', 10, 1, 0, 1, '/system/config', '', 1, 'system', 0, 'system-config-index', 0),
(57, 65, '', '刷新缓存', 'admin', 'system', 'clear', '', '', '[]', 1, 1, 0, 1, '/system/maintain/clear/index', '', 1, 'system', 0, 'system-clear', 0),
(64, 65, '', '文件校验', 'admin', 'system.system_file', 'index', '', '', '[]', 0, 1, 0, 1, '/system/maintain/system_file/index', '', 1, 'system', 0, 'system-maintain-system-file', 0),
(65, 25, '', '安全维护', 'admin', 'system', '', '', '', '[]', 7, 1, 0, 1, '/system/maintain', '', 1, 'system', 0, 'system-maintain-index', 0),
(66, 1073, '', '清除数据', 'admin', 'system.system_cleardata', 'index', '', '', '[]', 0, 1, 0, 1, '/system/maintain/system_cleardata/index', '25/1073', 1, 'system', 0, 'system-maintain-system-cleardata', 0),
(67, 1073, '', '数据备份', 'admin', 'system.system_databackup', 'index', '', '', '[]', 0, 1, 0, 1, '/system/maintain/system_databackup/index', '25/1073', 1, 'system', 0, 'system-maintain-system-databackup', 0),
(69, 135, '', '公众号', 'admin', 'wechat', '', '', '', '[]', 4, 1, 0, 1, '/app/wechat', '135', 1, 'app', 0, 'admin-wechat', 0),
(70, 30, '', '优惠券模板', 'admin', 'marketing.store_coupon', 'index', '', '', '[]', 0, 0, 0, 1, '/marketing/store_coupon/index', '', 1, 'marketing', 0, 'marketing-store_coupon', 0),
(71, 30, '', '优惠券列表', 'admin', 'marketing.store_coupon_issue', 'index', '', '', '[]', 0, 1, 0, 1, '/marketing/store_coupon_issue/index', '', 1, 'marketing', 0, 'marketing-store_coupon_issue', 0),
(72, 30, '', '用户领取记录', 'admin', 'marketing.store_coupon_user', 'index', '', '', '[]', 0, 1, 0, 1, '/marketing/store_coupon_user/index', '', 1, 'marketing', 0, 'marketing-store_coupon_user', 0),
(74, 31, '', '砍价商品', 'admin', 'marketing.store_bargain', 'index', '', '', '[]', 0, 1, 0, 1, '/marketing/store_bargain/index', '', 1, 'marketing', 0, 'marketing-store_bargain', 0),
(75, 32, '', '拼团商品', 'admin', 'marketing.store_combination', 'index', '', '', '[]', 0, 1, 0, 1, '/marketing/store_combination/index', '', 1, 'marketing', 0, 'marketing-store_combination', 0),
(76, 32, '', '拼团列表', 'admin', 'marketing.store_combination', 'combina_list', '', '', '[]', 0, 1, 0, 1, '/marketing/store_combination/combina_list', '', 1, 'marketing', 0, 'marketing-store_combination-combina_list', 0),
(77, 33, '', '秒杀商品', 'admin', 'marketing.store_seckill', 'index', '', '', '[]', 0, 1, 0, 1, '/marketing/store_seckill/index', '', 1, 'marketing', 0, 'marketing-store_seckill', 0),
(78, 33, '', '秒杀配置', 'admin', 'marketing.store_seckill', 'index', '', '', '[]', 0, 1, 0, 1, '/marketing/store_seckill_data/index/49', '', 1, 'marketing', 0, 'marketing-store_seckill-data', 0),
(79, 34, '', '积分配置', 'admin', 'setting.system_config/index.html', 'index', '', '', '[]', 0, 1, 0, 1, '/marketing/integral/system_config/3/11', '', 1, 'marketing', 0, 'marketing-integral-system_config', 0),
(90, 32, '', '拼团添加', 'admin', 'marketing.store_combination', '', '', '', '[]', 0, 1, 0, 1, '/marketing/store_combination/add_commodity/:id', '', 1, 'marketing', 0, '', 1),
(91, 69, '', '公众号配置', 'admin', 'application.wechat', '', '', '', '[]', 0, 1, 0, 1, '/app/wechat/setting', '', 1, 'app', 0, '', 1),
(92, 69, '', '微信菜单', 'admin', 'application.wechat_menus', 'index', '', '', '[]', 0, 1, 0, 1, '/app/wechat/setting/menus/index', '', 1, 'app', 0, 'application-wechat-menus', 0),
(94, 1056, '', '一号通', 'admin', 'setting.sms_config', '', '', '', '[]', 8, 1, 0, 1, '/setting/sms/sms_config/index', '12/1056', 1, 'setting', 0, 'setting-sms', 0),
(95, 94, '', '账户管理', 'admin', 'sms.sms_config', 'index', '', '', '[]', 0, 0, 0, 1, '/setting/sms/sms_config/index', '', 1, 'setting', 0, 'setting-sms-sms-config', 0),
(96, 94, '', '短信模板', 'admin', 'sms.sms_template_apply', 'index', '', '', '[]', 0, 0, 0, 1, '/setting/sms/sms_template_apply/index', '', 1, 'setting', 0, 'setting-sms-config-template', 0),
(97, 94, '', '套餐购买', 'admin', 'sms.sms_pay', 'index', '', '', '[]', 0, 0, 0, 1, '/setting/sms/sms_pay/index', '', 1, 'setting', 0, 'setting-sms-sms-template', 0),
(99, 1, '', '商品规格', 'admin', 'store.store_product', 'index', '', '', '[]', 1, 1, 0, 1, '/product/product_attr', '', 1, 'product', 0, 'product-product-attr', 0),
(105, 22, '', '添加产品保存', 'admin', 'store.store_product', 'save', 'product/product/<id>', 'POST', '[]', 0, 0, 0, 1, '/product/save', '', 2, 'product', 0, 'product-save', 0),
(108, 2, '', '产品列表', 'admin', 'product.product', 'index', 'product/product', 'GET', '[]', 20, 0, 0, 1, '/product/product', '1/2', 2, 'product', 0, 'product-product-index', 0),
(109, 69, '', '图文管理', 'admin', 'wechat.wechat_news_category', 'index', '', '', '[]', 0, 1, 0, 1, '/app/wechat/news_category/index', '', 1, 'app', 0, 'wechat-wechat-news-category-index', 0),
(110, 69, '', '图文添加', 'admin', 'wechat.wechat_news_category', 'save', '', '', '[]', 0, 1, 1, 1, '/app/wechat/news_category/save', '', 1, 'app', 0, 'wechat-wechat-news-category-save', 0),
(111, 56, '', '配置分类', 'admin', 'setting.system_config_tab', 'index', '', '', '[]', 99, 1, 0, 1, '/system/config/system_config_tab/index', '25/56', 1, 'system', 0, 'system-config-system_config-tab', 0),
(112, 56, '', '组合数据', 'admin', 'setting.system_group', 'index', '', '', '[]', 98, 1, 0, 1, '/system/config/system_group/index', '25/56', 1, 'system', 0, 'system-config-system_config-group', 0),
(113, 114, '', '关注回复', 'admin', 'wechat.reply', 'index', '', '', '[]', 0, 1, 0, 1, '/app/wechat/reply/follow/subscribe', '135/69/114', 1, 'app', 0, 'wechat-wechat-reply-subscribe', 0),
(114, 69, '', '自动回复', 'admin', 'wechat.reply', '', '', '', '[]', 0, 1, 0, 1, '/app/wechat/reply', '', 1, 'app', 0, 'wechat-wechat-reply-index', 0),
(115, 114, '', '关键字回复', 'admin', 'wechat.reply', 'keyword', '', '', '[]', 0, 1, 0, 1, '/app/wechat/reply/keyword', '', 1, 'app', 0, 'wechat-wechat-reply-keyword', 0),
(116, 114, '', '无效词回复', 'admin', 'wechat.reply', 'index', '', '', '[]', 0, 1, 0, 1, '/app/wechat/reply/index/default', '135/69/114', 1, 'app', 0, 'wechat-wechat-reply-default', 0),
(125, 111, '', '配置列表', 'admin', 'system.config', 'index', '', '', '[]', 0, 0, 1, 1, '/system/config/system_config_tab/list', '25/56/111', 1, 'system', 0, 'system-config-system_config_tab-list', 0),
(126, 112, '', '组合数据列表', 'admin', 'system.system_group', 'list', '', '', '[]', 0, 0, 1, 1, '/system/config/system_group/list', '25/56/112', 1, 'system', 0, 'system-config-system_config-list', 0),
(128, 656, '', '数据配置', 'admin', 'setting.system_group_data', 'index', '', '', '[]', 2, 1, 0, 1, '/setting/system_visualization_data', '12/656', 1, 'system', 0, 'admin-setting-system_visualization_data', 0),
(134, 114, '', '关键字添加', 'admin', '', 'index', '', '', '[]', 0, 1, 1, 1, '/app/wechat/reply/keyword/save', '', 1, 'app', 0, 'wechat-wechat-reply-save', 0),
(135, 0, 'md-cube', '应用', 'admin', 'app', 'index', '', '', '[]', 70, 1, 0, 1, '/app', '', 1, 'app', 1, 'admin-app', 0),
(144, 303, '', '提货点设置', 'admin', 'merchant.system_store', 'index', '', '', '[]', 5, 1, 0, 1, '/setting/merchant/system_store/index', '', 1, '', 0, 'setting-system-config-merchant', 0),
(145, 1073, '', '物流公司', 'admin', 'freight.express', 'index', '', '', '[]', 4, 1, 0, 1, '/setting/freight/express/index', '25/1073', 1, '', 0, 'setting-freight-express', 0),
(146, 31, '', '添加砍价', 'admin', '/marketing.store_bargain', 'create', '', '', '[]', 0, 1, 1, 1, '/marketing/store_bargain/create', '', 1, '', 0, 'marketing-store_bargain-create', 0),
(147, 32, '', '添加拼团', 'admin', 'marketing.store_combination', 'create', '', '', '[]', 0, 1, 1, 1, '/marketing/store_combination/create', '', 1, '', 0, 'marketing-store_combination-create', 0),
(148, 33, '', '添加秒杀', 'admin', 'marketing.store_seckill', 'create', '', '', '[]', 0, 1, 1, 1, '/marketing/store_seckill/create', '', 1, '', 0, 'marketing-store_seckill-create', 0),
(154, 34, '', '签到配置', 'admin', 'setting.system_group_data', 'index', '', '', '[]', 0, 1, 0, 1, '/marketing/sign', '', 1, '', 0, 'marketing-sign-index', 0),
(165, 0, 'md-chatboxes', '客服', 'admin', 'setting.storeService', 'index', '', '', '[]', 104, 1, 0, 1, '/kefu', '', 1, '', 1, 'setting-store-service', 0),
(166, 65, '', '日志', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/log', '25/65', 1, '', 0, 'system-log', 0),
(169, 577, '', '商品删除', 'admin', 'product', '商品删除', 'product/product/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '0', 0, '', 0),
(170, 3, '', '分类列表', 'admin', '', '', 'product/category', 'GET', '[]', 0, 0, 0, 1, 'product/category', '', 2, '', 0, '', 0),
(171, 578, '', '删除分类', 'admin', '', '', 'product/category/<id>', 'DELETE', '[]', 0, 0, 0, 1, 'product/category/<id>', '', 2, '', 0, '', 0),
(172, 578, '', '修改分类', 'admin', '', '', 'product/category/<id>', 'PUT', '[]', 0, 0, 0, 1, 'product/category/<id>', '', 2, '', 0, '', 0),
(173, 578, '', '新增分类', 'admin', '', '', 'product/category', 'POST', '[]', 0, 0, 0, 1, 'product/category', '', 2, '', 0, 'product-save-cate', 0),
(174, 578, '', '分类状态', 'admin', '', '', 'product/category/set_show/<id>/<is_show>', 'PUT', '[]', 0, 0, 0, 1, 'product/category/set_show/<id>/<is_show>', '', 2, '', 0, '', 0),
(175, 578, '', '快速编辑', 'admin', '', '', 'product/category/set_category/<id>', 'PUT', '[]', 0, 0, 0, 1, 'product/category/set_category/<id>', '', 2, '', 0, '', 0),
(176, 578, '', '分类表单添加', 'admin', '', '', 'product/category/create', 'GET', '[]', 0, 0, 0, 1, 'category/create', '', 2, '', 0, '', 0),
(177, 578, '', '分类表单编辑', 'admin', '', '', 'product/category/<id>', 'GET', '[]', 0, 0, 0, 1, 'category/<id>/edit', '', 2, '', 0, '', 0),
(178, 3, '', '分类树形列表', 'admin', '', '', 'product/category/tree/<type>', 'GET', '[]', 0, 0, 0, 1, 'category/tree/:type', '', 2, '', 0, '', 0),
(179, 577, '', '产品状态', 'admin', '', '', 'product/product/set_show/<id>/<is_show>', 'PUT', '[]', 0, 0, 0, 1, 'product/set_show/<id>/<is_show>', '', 2, '', 0, '', 0),
(180, 577, '', '快速编辑', 'admin', '', '', 'product/product/set_product/<id>', 'PUT', '[]', 0, 0, 0, 1, 'product/product/set_product/<id>', '', 2, '', 0, '', 0),
(181, 577, '', '批量上架商品', 'admin', '', '', 'product/product/product_show', 'PUT', '[]', 0, 0, 0, 1, 'product/product/product_show', '', 2, '', 0, 'product-product-product_show', 0),
(182, 577, '', '采集商品', 'admin', '', '', 'product/copy', 'POST', '[]', 0, 0, 0, 1, 'product/crawl', '', 2, '', 0, 'product-crawl-save', 0),
(183, 577, '', '采集商品保存', 'admin', '', '', 'product/crawl/save', 'POST', '[]', 0, 0, 0, 1, 'product/crawl/save', '', 2, '', 0, '', 0),
(184, 579, '', '虚拟评论表单', 'admin', '', '', 'product/reply/fictitious_reply/<product_id>', 'GET', '[]', 0, 0, 0, 1, 'product/reply/fictitious_reply', '', 2, '', 0, '', 0),
(185, 579, '', '保存虚拟评论', 'admin', '', '', 'product/reply/save_fictitious_reply', 'POST', '[]', 0, 0, 0, 1, 'product/reply/save_fictitious_reply', '', 2, '', 0, 'product-reply-save_fictitious_reply', 0),
(186, 22, '', '获取属性模板列表', 'admin', '', '', 'product/product/get_rule', 'GET', '[]', 0, 0, 0, 1, 'product/product/get_rule', '', 2, '', 0, '', 0),
(187, 22, '', '运费模板列表', 'admin', '', '', 'product/product/get_template', 'GET', '[]', 0, 0, 0, 1, 'product/product/get_template', '', 2, '', 0, '', 0),
(188, 579, '', '删除评论', 'admin', '', '', 'product/reply/<id>', 'DELETE', '[]', 0, 0, 0, 1, 'product/reply/<id>', '', 2, '', 0, '', 0),
(189, 579, '', '评论回复', 'admin', '', '', 'product/reply/set_reply/<id>', 'PUT', '[]', 0, 0, 0, 1, 'reply/set_reply/<id>', '', 2, '', 0, '', 0),
(190, 6, '', '评论列表', 'admin', '', '', 'product/reply', 'GET', '[]', 0, 0, 0, 1, 'product/reply', '', 2, '', 0, '', 0),
(191, 22, '', '生成属性', 'admin', '', '', 'product/generate_attr/<id>/<type>', 'POST', '[]', 0, 0, 0, 1, 'product/generate_attr/<id>', '', 2, '', 0, '', 0),
(192, 2, '', '商品列表头部', 'admin', '', '', 'product/product/type_header', 'GET', '[]', 10, 0, 0, 1, 'product/product/type_header', '', 2, '', 0, '', 0),
(193, 577, '', '商品列表插件', 'admin', '', '', 'product/product/list', 'GET', '[]', 0, 0, 0, 1, 'product/product/list', '', 2, '', 0, '', 0),
(194, 99, '', '属性规则列表', 'admin', '', '', 'product/product/rule', 'GET', '[]', 0, 0, 0, 1, 'product/product/rule', '', 2, '', 0, '', 0),
(195, 580, '', '保存修改规则', 'admin', '', '', 'product/product/rule/<id>', 'POST', '[]', 0, 0, 0, 1, 'product/rule/<id>', '', 2, '', 0, 'product-rule-save', 0),
(196, 580, '', '规则详情', 'admin', '', '', 'product/product/rule/<id>', 'GET', '[]', 0, 0, 0, 1, 'product/product/rule/<id>', '', 2, '', 0, '', 0),
(197, 580, '', '删除规则', 'admin', '', '', 'product/product/rule/delete', 'DELETE', '[]', 0, 0, 0, 1, 'product/product/rule/delete', '', 2, '', 0, 'product-product-rule-delete', 0),
(198, 5, '', '订单列表', 'admin', '', '', 'order/list', 'GET', '[]', 0, 0, 0, 1, 'order/list', '', 2, '', 0, '', 0),
(199, 5, '', '订单数据', 'admin', '', '', 'order/chart', 'GET', '[]', 0, 0, 0, 1, 'order/chart', '', 2, '', 0, '', 0),
(200, 581, '', '订单核销', 'admin', '', '', 'order/write', 'POST', '[]', 0, 0, 0, 1, 'order/write', '', 2, '', 0, 'order-write', 0),
(201, 215, '', '订单修改表格', 'admin', '', '', 'order/edit/<id>', 'GET', '[]', 0, 0, 0, 1, 'order/edit/<id>', '', 2, '', 0, '', 0),
(202, 215, '', '订单修改', 'admin', '', '', 'order/update/<id>', 'PUT', '[]', 0, 0, 0, 1, 'order/update/<id>', '', 2, '', 0, '', 0),
(203, 581, '', '订单收货', 'admin', '', '', 'order/take/<id>', 'PUT', '[]', 0, 0, 0, 1, 'order/take/<id>', '', 2, '', 0, '', 0),
(204, 209, '', '订单发货', 'admin', '', '', 'order/delivery/<id>', 'PUT', '[]', 0, 0, 0, 1, 'order/delivery/<id>', '', 2, '', 0, '', 0),
(205, 214, '', '订单退款表格', 'admin', '', '', 'order/refund/<id>', 'GET', '[]', 0, 0, 0, 1, 'order/refund/<id>', '', 2, '', 0, '', 0),
(206, 214, '', '订单退款', 'admin', '', '', 'order/refund/<id>', 'PUT', '[]', 0, 0, 0, 1, 'order/refund/<id>', '', 2, '', 0, '', 0),
(207, 581, '', '订单物流信息', 'admin', '', '', 'order/express/<id>', 'GET', '[]', 0, 0, 0, 1, 'order/express/<id>', '', 2, '', 0, '', 0),
(208, 209, '', '物流公司列表', 'admin', '', '', 'order/express_list', 'GET', '[]', 0, 0, 0, 1, 'order/express_list', '', 2, '', 0, '', 0),
(209, 581, '', '发货', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, 'order/delivery', '', 1, '', 0, '', 0),
(210, 767, '', '附加权限', 'admin', '', '', '', 'GET', '[]', 99, 1, 0, 1, 'order/info/<id>', '', 2, '', 0, '', 0),
(211, 213, '', '订单配送表格', 'admin', '', '', 'order/distribution/<id>', 'GET', '[]', 0, 0, 0, 1, 'order/distribution/<id>', '', 2, '', 0, '', 0),
(212, 213, '', '修改配送信息', 'admin', '', '', 'order/distribution/<id>', 'PUT', '[]', 0, 0, 0, 1, 'order/distribution/<id>', '', 2, '', 0, '', 0),
(213, 581, '', '订单配送', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, 'order/distribution', '', 1, '', 0, '', 0),
(214, 581, '', '退款', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, 'order/refund', '', 1, '', 0, '', 0),
(215, 581, '', '修改', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, 'order/update', '', 1, '', 0, '', 0),
(216, 581, '', '不退款', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, 'order/no_refund', '', 1, '', 0, '', 0),
(217, 216, '', '不退款表格', 'admin', '', '', 'order/no_refund/<id>', 'GET', '[]', 0, 0, 0, 1, 'order/no_refund/<id>', '', 2, '', 0, '', 0),
(218, 216, '', '不退款理由修改', 'admin', '', '', 'order/no_refund/<id>', 'PUT', '[]', 0, 0, 0, 1, 'order/no_refund/<id>', '', 2, '', 0, '', 0),
(219, 581, '', '线下支付', 'admin', '', '', 'order/pay_offline/<id>', 'POST', '[]', 98, 0, 0, 1, '', '', 2, '', 0, '', 0),
(220, 581, '', '退积分', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, 'order/refund_integral', '', 1, '', 0, '', 0),
(221, 220, '', '退积分表单', 'admin', '', '', 'order/refund_integral/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(222, 220, '', '修改退积分', 'admin', '', '', 'order/refund_integral/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(223, 581, '', '订单备注', 'admin', '', '', 'order/remark/<id>', 'PUT', '[]', 97, 0, 0, 1, '', '', 2, '', 0, '', 0),
(224, 209, '', '获取电子面单信息', 'admin', '', '', 'order/express/temp', 'GET', '[]', 96, 0, 1, 1, '', '4/5/581/209', 2, '', 0, '', 0),
(225, 581, '', '订单删除', 'admin', '', '', 'order/del/<id>', 'DELETE', '[]', 95, 0, 0, 1, '', '', 2, '', 0, '', 0),
(226, 581, '', '批量删除订单', 'admin', '', '', 'order/dels', 'POST', '[]', 100, 0, 0, 1, '', '4/5/581', 2, '', 0, 'order-dels', 0),
(227, 9, '', '用户分组', 'admin', 'user.user_group', 'index', '', '', '[]', 9, 1, 0, 1, '/user/group', '', 1, 'user', 0, 'user-user-group', 0),
(229, 1073, '', '城市数据', 'admin', 'setting.system_city', '', '', '', '[]', 1, 1, 0, 1, '/setting/freight/city/list', '25/1073', 1, 'setting', 0, 'setting-system-city', 0),
(230, 303, '', '运费模板', 'admin', 'setting.shipping_templates', '', '', '', '[]', 0, 1, 0, 1, '/setting/freight/shipping_templates/list', '', 1, 'setting', 0, 'setting-shipping-templates', 0),
(231, 767, '', '发票列表', 'admin', '', '', 'order/invoice/list', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, 'admin-order-invoice-index', 0),
(232, 585, '', '用户详情', 'admin', '', '', 'user/one_info/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(233, 585, '', '创建用户表单', 'admin', '', '', 'user/user/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(234, 585, '', '修改用户信息表单', 'admin', '', '', 'user/user/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(235, 585, '', '获取用户信息', 'admin', '', '', 'user/user/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(236, 585, '', '修改用户信息', 'admin', '', '', 'user/user/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(238, 585, '', '发送优惠卷', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/coupon', '', 1, '', 0, 'admin-user-coupon', 0),
(239, 238, '', '优惠卷列表', 'admin', '', '', 'marketing/coupon/grant', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(240, 238, '', '发送优惠卷', 'admin', '', '', 'marketing/coupon/user/grant', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(241, 585, '', '发送图文', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/wechat/news/', '', 1, '', 0, 'admin-wechat-news', 0),
(242, 241, '', '图文列表', 'admin', '', '', 'app/wechat/news', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(243, 241, '', '发送图文', 'admin', '', '', 'app/wechat/push', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(244, 585, '', '批量用户分组', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/group_set/', '', 1, '', 0, 'admin-user-group_set', 0),
(245, 244, '', '用户分组表单', 'admin', '', '', 'user/set_group/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(246, 244, '', '保存分组', 'admin', '', '', 'user/set_group', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(247, 586, '', '添加修改用户等级', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/level_add', '', 1, '', 0, 'admin-user-level_add', 0),
(248, 247, '', '添加会员等级表单', 'admin', '', '', 'user/user_level/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(249, 247, '', '保存会员等级', 'admin', '', '', 'user/user_level', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(250, 11, '', '用户等级列表', 'admin', '', '', 'user/user_level/vip_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(251, 586, '', '用户等级是否显示', 'admin', '', '', 'user/user_level/set_show/<id>/<is_show>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(252, 586, '', '删除用户等级', 'admin', '', '', 'user/user_level/delete/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(253, 586, '', '等级任务', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/user_level', '', 1, '', 0, '', 0),
(254, 253, '', '等级任务列表', 'admin', '', '', 'user/user_level/task/<level_id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(255, 253, '', '等级任务显示隐藏', 'admin', '', '', 'user/user_level/set_task_show/<id>/<is_show>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(256, 253, '', '等级任务是否必达', 'admin', '', '', 'user/user_level/set_task_must/<id>/<is_must>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(257, 253, '', '添加修改等级任务', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(258, 257, '', '添加等级任务表单', 'admin', '', '', 'user/user_level/create_task', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(259, 257, '', '保存修改任务', 'admin', '', '', 'user/user_level/save_task', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(260, 253, '', '删除等级任务', 'admin', '', '', 'user/user_level/delete_task/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(261, 227, '', '用户分组列表', 'admin', '', '', 'user/user_group/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(262, 227, '', '删除用户分组', 'admin', '', '', 'user/user_group/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(263, 227, '', '添加修改用户分组', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/group', '', 1, '', 0, 'admin-user-group', 0),
(264, 263, '', '添加修改用户分组表单', 'admin', '', '', 'user/user_group/add/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(265, 263, '', '保存修改用户分组', 'admin', '', '', 'user/user_group/save', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(266, 29, '', '分销员列表', 'admin', '', '', 'agent/index', 'GET', '[]', 0, 0, 0, 1, '', '26/29', 2, '', 0, '', 0),
(267, 584, '', '分销员数据', 'admin', '', '', 'agent/statistics', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(268, 29, '', '推广人列表', 'admin', '', '', 'agent/stair', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(269, 29, '', '推广人订单列表', 'admin', '', '', 'agent/stair/order', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(270, 584, '', '清除推广人', 'admin', '', '', 'agent/stair/delete_spread/<uid>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(271, 584, '', '推广二维码', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(272, 271, '', '公众号推广二维码', 'admin', '', '', 'agent/look_code', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(273, 271, '', '小程序推广二维码', 'admin', '', '', 'agent/look_xcx_code', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(274, 583, '', '添加优惠卷', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/marketing/store_coupon/add', '', 1, '', 0, 'admin-marketing-store_coupon-add', 0),
(275, 274, '', '添加优惠卷表单', 'admin', '', '', 'marketing/coupon/create/<type>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(276, 274, '', '保存优惠卷', 'admin', '', '', 'marketing/coupon/save_coupon', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(277, 583, '', '发布优惠卷', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/marketing/store_coupon/push', '', 1, '', 0, 'admin-marketing-store_coupon-push', 0),
(278, 277, '', '发布优惠卷表单', 'admin', '', '', 'marketing/coupon/issue/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(279, 277, '', '发布优惠卷', 'admin', '', '', 'marketing/coupon/issue/<id>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(280, 583, '', '立即失效', 'admin', '', '', 'marketing/coupon/status/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(281, 583, '', '删除优惠卷', 'admin', '', '', 'marketing/coupon/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(282, 71, '', '优惠卷已发布列表', 'admin', '', '', 'marketing/coupon/released', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(283, 71, '', '领取记录', 'admin', '', '', 'marketing/coupon/released/issue_log/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(284, 71, '', '删除优惠卷', 'admin', '', '', 'marketing/coupon/released/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(285, 71, '', '修改状态', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(286, 285, '', '修改状态表单', 'admin', '', '', 'marketing/coupon/released/<id>/status', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(287, 285, '', '保存修改状态', 'admin', '', '', 'marketing/coupon/released/status/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(290, 405, '', '审核状态通过', 'admin', '', '', 'finance/extract/adopt/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(291, 405, '', '拒绝申请', 'admin', '', '', 'finance/extract/refuse/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(292, 405, '', '提现编辑', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(293, 292, '', '编辑表单', 'admin', '', '', 'finance/extract/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(294, 292, '', '保存修改', 'admin', '', '', 'finance/extract/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(295, 40, '', '充值列表', 'admin', '', '', 'finance/recharge', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(296, 40, '', '充值数据', 'admin', '', '', 'finance/recharge/user_recharge', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(297, 40, '', '退款', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(298, 297, '', '获取退款表单', 'admin', '', '', 'finance/recharge/<id>/refund_edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(299, 297, '', '保存退款', 'admin', '', '', 'finance/recharge/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(300, 144, '', '提货点', 'admin', 'merchant.system_store', 'index', '', '', '[]', 0, 1, 0, 1, '/setting/merchant/system_store/list', '', 1, 'setting', 0, 'setting-merchant-system-store', 0),
(301, 144, '', '核销员', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/merchant/system_store_staff/index', '', 1, 'setting', 0, 'setting-merchant-system-store-staff', 0),
(302, 4, '', '核销订单', 'admin', '', 'index', '', '', '[]', 0, 1, 0, 1, '/setting/merchant/system_verify_order/index', '4', 1, 'setting', 0, 'setting-merchant-system-verify-order', 0),
(303, 12, '', '发货设置', 'admin', 'setting', 'index', '', '', '[]', 0, 1, 0, 1, '/setting/freight', '', 1, '', 0, '', 0),
(304, 303, '', '物流配置', 'admin', 'setting.systemConfig', 'index', '', '', '[]', 0, 0, 0, 1, '/setting/system_config_logistics/3/10', '', 1, '', 0, 'setting-system-config-logistics', 0),
(305, 44, '', '文章列表', 'admin', '', '', 'cms/cms', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(306, 409, '', '文章分类', 'admin', '', '', 'cms/category', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(307, 42, '', '佣金记录列表', 'admin', '', '', 'finance/finance/commission_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(308, 42, '', '用户详情', 'admin', 'finance.finance', 'user_info', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(309, 308, '', '获取用户信息', 'admin', '', '', 'finance/finance/user_info/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(310, 308, '', '佣金详细列表', 'admin', '', '', 'finance/finance/extract_list/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(313, 23, '', '获取头部导航', 'admin', '', '', 'setting/config/header_basics', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(314, 23, '', '获取配置列表', 'admin', '', '', 'setting/config/edit_basics', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(315, 23, '', '修改配置', 'admin', '', '', 'setting/config/save_basics', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(316, 423, '', '添加客服', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/store_service/add', '', 1, '', 0, 'setting-store_service-add', 0),
(317, 316, '', '客服用户列表', 'admin', '', '', 'app/wechat/kefu/add', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(318, 316, '', '保存客服', 'admin', '', '', 'app/wechat/kefu', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(319, 423, '', '聊天记录', 'admin', '', '', 'app/wechat/kefu/record/', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(320, 423, '', '编辑客服', 'admin', '', '', '', '', '[]', 80, 0, 0, 1, '/setting/store_service/edit', '', 1, '', 0, 'setting-store_service-edit', 0),
(321, 423, '', '删除客服', 'admin', '', '', 'app/wechat/kefu/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(322, 423, '', '客服是否开启', 'admin', '', '', 'app/wechat/kefu/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(323, 320, '', '编辑客服表单', 'admin', '', '', 'app/wechat/kefu/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(324, 320, '', '修改客服', 'admin', '', '', 'app/wechat/kefu/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(325, 19, '', '添加身份', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/system_role/add', '', 1, '', 0, 'setting-system_role-add', 0),
(326, 325, '', '添加身份表单', 'admin', '', '', 'setting/role/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(327, 325, '', '添加修改身份', 'admin', '', '', 'setting/role/<id>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(328, 325, '', '修改身份表单', 'admin', '', '', 'setting/role/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(329, 19, '', '修改身份状态', 'admin', '', '', 'setting/role/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(330, 19, '', '删除身份', 'admin', '', '', 'setting/role/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(331, 20, '', '添加管理员', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/system_admin/add', '', 1, '', 0, 'setting-system_admin-add', 0),
(332, 331, '', '添加管理员表单', 'admin', '', '', 'setting/admin/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(333, 331, '', '添加管理员', 'admin', '', '', 'setting/admin', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(334, 20, '', '编辑管理员', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, ' /setting/system_admin/edit', '', 1, '', 0, ' setting-system_admin-edit', 0),
(335, 334, '', '编辑管理员表单', 'admin', '', '', 'setting/admin/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(336, 334, '', '修改管理员', 'admin', '', '', 'setting/admin/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(337, 20, '', '删除管理员', 'admin', '', '', 'setting/admin/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(338, 21, '', '添加规则', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/system_menus/add', '', 1, '', 0, 'setting-system_menus-add', 0),
(339, 338, '', '添加权限表单', 'admin', '', '', 'setting/menus/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(340, 338, '', '添加权限', 'admin', '', '', 'setting/menus', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(341, 21, '', '修改权限', 'admin', 'setting.system_menus', 'edit', '', '', '[]', 0, 0, 0, 1, '/setting/system_menus/edit', '', 1, '', 0, '/setting-system_menus-edit', 0),
(342, 341, '', '编辑权限表单', 'admin', '', '', 'setting/menus/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(343, 341, '', '修改权限', 'admin', '', '', 'setting/menus/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(344, 21, '', '修改权限状态', 'admin', '', '', 'setting/menus/show/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(345, 21, '', '删除权限菜单', 'admin', '', '', 'setting/menus/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(346, 338, '', '添加子菜单', 'admin', 'setting.system_menus', 'add', '', '', '[]', 0, 0, 0, 1, '/setting/system_menus/add_sub', '', 1, '', 0, 'setting-system_menus-add_sub', 0),
(347, 361, '', '是否登陆短信平台', 'admin', '', '', 'notify/sms/is_login', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(348, 361, '', '短信剩余条数', 'admin', '', '', 'notify/sms/number', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(349, 95, '', '获取短信验证码', 'admin', '', '', 'serve/captcha', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(350, 95, '', '修改注册账号', 'admin', '', '', 'serve/register', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(351, 95, '', '登陆短信平台', 'admin', '', '', 'serve/login', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(353, 95, '', '退出短信登陆', 'admin', '', '', 'notify/sms/logout', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(355, 96, '', '短信模板列表', 'admin', '', '', 'serve/sms/temps', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(356, 96, '', '申请模板', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/sms/sms_template_apply/add', '', 1, '', 0, 'setting-sms-sms_template_apply-add', 0),
(357, 356, '', '申请短信模板表单', 'admin', '', '', 'notify/sms/temp/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(358, 356, '', '保存申请短信模板', 'admin', '', '', 'notify/sms/temp', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(359, 97, '', '短信套餐', 'admin', '', '', 'serve/meal_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(360, 97, '', '短信购买支付码', 'admin', '', '', 'serve/pay_meal', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(361, 94, '', '短信设置附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/sms/attach', '', 1, '', 0, '', 0),
(362, 300, '', '门店数据', 'admin', '', '', 'merchant/store/get_header', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(363, 300, '', '门店列表展示', 'admin', '', '', 'merchant/store', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(364, 424, '', '修改门店状态', 'admin', '', '', 'merchant/store/set_show/<id>/<is_show>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(366, 7, '', '首页统计数据', 'admin', '', '', 'home/header', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(367, 7, '', '首页订单图表', 'admin', '', '', 'home/order', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(368, 7, '', '首页用户图表', 'admin', '', '', 'home/user', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(369, 7, '', '首页交易额排行', 'admin', '', '', 'home/rank', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(370, 72, '', '优惠卷领取列表', 'admin', '', '', 'marketing/coupon/user', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(371, 74, '', '砍价列表', 'admin', '', '', 'marketing/bargain', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(372, 74, '', '附加权限', 'admin', 'marketing.store_bargain', '', '', '', '[]', 0, 0, 0, 1, '/marketing/store_bargain/attr', '', 1, '', 0, '', 0),
(373, 372, '', '修改砍价状态', 'admin', '', '', 'marketing/bargain/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(374, 372, '', '砍价商品详情', 'admin', '', '', 'marketing/bargain/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(375, 74, '', '公共权限', 'admin', 'marketing.store_bargain', 'public', '', '', '[]', 0, 0, 0, 1, '/marketing/store_bargain/public', '', 1, '', 0, '', 0),
(376, 375, '', '分类树型列表', 'admin', '', '', 'product/category/tree/<type>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(377, 375, '', '商品插件列表', 'admin', '', '', 'product/product/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(378, 375, '', '运费模板', 'admin', '', '', 'product/product/get_template', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(379, 372, '', '修改添加砍价商品', 'admin', '', '', 'marketing/bargain/<id>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(380, 372, '', '删除砍价商品', 'admin', '', '', 'marketing/bargain/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(381, 75, '', '拼团列表', 'admin', '', '', 'marketing/combination', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(382, 75, '', '拼团数据', 'admin', '', '', 'marketing/combination/statistics', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(383, 75, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(384, 383, '', '拼团状态', 'admin', '', '', 'marketing/combination/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(385, 383, '', '删除拼团', 'admin', '', '', 'marketing/combination/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(386, 75, '', '公共权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(387, 386, '', '树型分类列表', 'admin', '', '', 'product/category/tree/<type>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(388, 386, '', '商品插件列表', 'admin', '', '', 'product/product/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(389, 386, '', '运费模板列表', 'admin', '', '', 'product/product/get_template', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(390, 383, '', '获取拼团详情', 'admin', '', '', 'marketing/combination/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(391, 383, '', '编辑添加拼团', 'admin', '', '', 'marketing/combination/<id>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(392, 76, '', '正在拼团列表', 'admin', '', '', 'marketing/combination/combine/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(393, 76, '', '拼团人员列表', 'admin', '', '', 'marketing/combination/order_pink/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(395, 77, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(396, 395, '', '修改拼团状态', 'admin', '', '', 'marketing/seckill/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(397, 77, '', '公共权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(398, 397, '', '分类树型列表', 'admin', '', '', 'product/category/tree/<type>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(399, 397, '', '商品插件列表', 'admin', '', '', 'product/product/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(400, 397, '', '运费模板列表', 'admin', '', '', 'product/product/get_template', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(401, 397, '', '秒杀时间段列表', 'admin', '', '', 'marketing/seckill/time_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(402, 395, '', '编辑添加秒杀商品', 'admin', '', '', 'marketing/seckill/<id>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(403, 395, '', '删除秒杀商品', 'admin', '', '', 'marketing/seckill/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(404, 39, '', '提现申请列表', 'admin', '', '', 'finance/extract', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(405, 39, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(406, 44, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(407, 406, '', '保存修改文章', 'admin', '', '', 'cms/cms', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(408, 406, '', '获取文章详情', 'admin', '', '', 'cms/cms/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(409, 44, '', '公共权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(410, 406, '', '关联商品列表', 'admin', '', '', 'product/product/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(411, 406, '', '分类树型列表', 'admin', '', '', 'product/category/tree/<type>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(412, 406, '', '关联商品', 'admin', '', '', 'cms/cms/relation/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(413, 406, '', '取消关联', 'admin', '', '', 'cms/cms/unrelation/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(414, 406, '', '删除文章', 'admin', '', '', 'cms/cms/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(415, 45, '', '文章列表', 'admin', '', '', 'cms/category', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(416, 45, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(417, 416, '', '文章分类添加表单', 'admin', '', '', 'cms/category/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'cms-category-create', 0),
(418, 416, '', '保存文章分类', 'admin', '', '', 'cms/category', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(419, 416, '', '编辑文章分类', 'admin', '', '', 'cms/category/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(420, 416, '', '修改文章分类', 'admin', '', '', 'cms/category/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(421, 416, '', '删除文章分类', 'admin', '', '', 'cms/category/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(422, 678, '', '客服列表', 'admin', '', '', 'app/wechat/kefu', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(423, 678, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(424, 300, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(425, 144, '', '公共权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(426, 425, '', '地图KEY权限', 'admin', '', '', 'merchant/store/address', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(427, 424, '', '添加编辑门店', 'admin', '', '', 'merchant/store/<id>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'setting-merchant-system_store-save', 0),
(428, 424, '', '设置门店隐藏显示', 'admin', '', '', 'merchant/store/set_show/<id>/<is_show>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(429, 424, '', '门店详情', 'admin', '', '', 'merchant/store/get_info/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(430, 424, '', '删除门店', 'admin', '', '', 'merchant/store/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(431, 425, '', '店员搜索门店列表', 'admin', '', '', 'merchant/store_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(432, 301, '', '店员列表', 'admin', '', '', 'merchant/store_staff', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(433, 301, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(434, 433, '', '添加店员表单', 'admin', '', '', 'merchant/store_staff/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'merchant-store_staff-create', 0),
(435, 425, '', '选择用户插件列表', 'admin', '', '', 'app/wechat/kefu/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(436, 433, '', '添加修改店员', 'admin', '', '', 'merchant/store_staff/save/<id>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(437, 433, '', '店员显示隐藏', 'admin', '', '', 'merchant/store_staff/set_show/<id>/<is_show>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(438, 433, '', '编辑店员表单', 'admin', '', '', 'merchant/store_staff/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(439, 433, '', '删除店员', 'admin', '', '', 'merchant/store_staff/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(440, 302, '', '核销订单列表', 'admin', '', '', 'merchant/verify_order', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(441, 302, '', '核销订单数据', 'admin', '', '', 'merchant/verify_badge', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(442, 229, '', '城市数据列表', 'admin', '', '', 'setting/city/list/<parent_id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(443, 229, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(444, 443, '', '获取添加城市表单', 'admin', '', '', 'setting/city/add/<parent_id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(445, 443, '', '保存修改城市数据', 'admin', '', '', 'setting/city/save', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(446, 443, '', '获取修改城市表单', 'admin', '', '', 'setting/city/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(447, 443, '', '删除城市数据', 'admin', '', '', 'setting/city/del/<city_id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(448, 145, '', '物流公司列表', 'admin', '', '', 'freight/express', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(449, 145, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(450, 449, '', '修改物流公司状态', 'admin', '', '', 'freight/express/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(451, 449, '', '获取添加物流公司表单', 'admin', '', '', 'freight/express/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(452, 449, '', '保存物流公司', 'admin', '', '', 'freight/express', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(453, 449, '', '获取编辑物流公司表单', 'admin', '', '', 'freight/express/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(454, 449, '', '修改物流公司', 'admin', '', '', 'freight/express/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(455, 449, '', '删除物流公司', 'admin', '', '', 'freight/express/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(456, 230, '', '运费模板列表', 'admin', '', '', 'setting/shipping_templates/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(457, 230, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(458, 457, '', '运费模板城市数据', 'admin', '', '', 'setting/shipping_templates/city_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(459, 457, '', '保存或者修改运费模板', 'admin', '', '', 'setting/shipping_templates/save/<id>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(460, 457, '', '删除运费模板', 'admin', '', '', 'setting/shipping_templates/del/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(461, 111, '', '配置分类列表', 'admin', '', '', 'setting/config_class', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(462, 111, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(463, 462, '', '配置分类添加表单', 'admin', '', '', 'setting/config_class/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(464, 462, '', '保存配置分类', 'admin', '', '', 'setting/config_class', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(465, 641, '', '编辑配置分类', 'admin', '', '', 'setting/config_class/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(466, 462, '', '删除配置分类', 'admin', '', '', 'setting/config_class/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(467, 125, '', '配置列表展示', 'admin', '', '', 'setting/config', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(468, 125, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(469, 468, '', '添加配置字段表单', 'admin', '', '', 'setting/config/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(470, 468, '', '保存配置字段', 'admin', '', '', 'setting/config', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(471, 468, '', '编辑配置字段表单', 'admin', '', '', 'setting/config/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(472, 468, '', '编辑配置分类', 'admin', '', '', 'setting/config/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(473, 468, '', '删除配置', 'admin', '', '', 'setting/config/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(474, 468, '', '修改配置状态', 'admin', '', '', 'setting/config/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(475, 112, '', '组合数据列表', 'admin', '', '', 'setting/group', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, '', 0),
(476, 112, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(477, 476, '', '新增组合数据', 'admin', '', '', 'setting/group', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(478, 476, '', '获取组合数据', 'admin', '', '', 'setting/group/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(479, 476, '', '修改组合数据', 'admin', '', '', 'setting/group/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(480, 476, '', '删除组合数据', 'admin', '', '', 'setting/group/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(481, 126, '', '组合数据列表表头', 'admin', '', '', 'setting/group_data/header', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(482, 126, '', '组合数据列表', 'admin', '', '', 'setting/group_data', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(483, 126, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(484, 483, '', '获取组合数据添加表单', 'admin', '', '', 'setting/group_data/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(485, 483, '', '保存组合数据', 'admin', '', '', 'setting/group_data', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(486, 483, '', '获取组合数据信息', 'admin', '', '', 'setting/group_data/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(487, 483, '', '修改组合数据信息', 'admin', '', '', 'setting/group_data/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(488, 483, '', '删除组合数据', 'admin', '', '', 'setting/group_data/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(489, 483, '', '修改组合数据状态', 'admin', '', '', 'setting/group_data/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(490, 57, '', '清除缓存', 'admin', '', '', 'system/refresh_cache/cache', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(491, 57, '', '清除日志', 'admin', '', '', 'system/refresh_cache/log', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(492, 47, '', '管理员搜索列表', 'admin', '', '', 'system/log/search_admin', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(493, 47, '', '系统日志列表', 'admin', '', '', 'system/log', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(494, 64, '', '文件校验列表', 'admin', '', '', 'system/file', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(495, 66, '', '清除数据接口', 'admin', '', '', 'system/clear/<type>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(496, 67, '', '数据库列表', 'admin', '', '', 'system/backup', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(497, 67, '', '数据库备份列表', 'admin', '', '', 'system/backup/file_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(498, 67, '', '数据表详情', 'admin', '', '', 'system/backup/read', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(499, 67, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(500, 499, '', '备份表', 'admin', '', '', 'system/backup/backup', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(501, 499, '', '优化表', 'admin', '', '', 'system/backup/optimize', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(502, 499, '', '修复表', 'admin', '', '', 'system/backup/repair', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(503, 499, '', '导入sql', 'admin', '', '', 'system/backup/import', 'POST', '[]', 0, 0, 1, 1, '', '', 2, '', 0, '', 0),
(504, 499, '', '删除数据库备份', 'admin', '', '', 'system/backup/del_file', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(505, 499, '', '备份下载', 'admin', '', '', 'backup/download', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(507, 92, '', '微信菜单列表', 'admin', '', '', 'app/wechat/menu', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(508, 92, '', '保存微信菜单', 'admin', '', '', 'app/wechat/menu', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(553, 109, '', '图文列表', 'admin', '', '', 'app/wechat/news', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(554, 109, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(555, 554, '', '保存图文', 'admin', '', '', 'app/wechat/news', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(556, 554, '', '图文详情', 'admin', '', '', 'app/wechat/news/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(557, 554, '', '删除图文', 'admin', '', '', 'app/wechat/news/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(558, 114, '', '公共权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(559, 558, '', '回复关键词', 'admin', '', '', 'app/wechat/reply', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(560, 115, '', '关键词回复列表', 'admin', '', '', 'app/wechat/keyword', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(561, 115, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(562, 558, '', '保存修改关键字', 'admin', '', '', 'app/wechat/keyword/<id>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(563, 561, '', '获取关键字信息', 'admin', '', '', 'app/wechat/keyword/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(564, 561, '', '修改关键字状态', 'admin', '', '', 'app/wechat/keyword/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(565, 561, '', '删除关键字', 'admin', '', '', 'app/wechat/keyword/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(566, 656, '', '素材管理', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/system/file', '12/656', 1, '', 0, 'system-file', 0),
(567, 566, '', '附件列表', 'admin', '', '', 'file/file', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(568, 566, '', '附件分类', 'admin', '', '', 'file/category', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(569, 566, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(570, 569, '', '附件分类表单', 'admin', '', '', 'file/category/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(571, 569, '', '附件分类保存', 'admin', '', '', 'file/category', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(572, 569, '', '删除附件', 'admin', '', '', 'file/file/delete', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(573, 569, '', '移动附件分类', 'admin', '', '', 'file/file/do_move', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(574, 566, '', '上传附件', 'admin', '', '', 'file/upload/<upload_type?>', 'POST', '[]', 10, 0, 0, 1, '', '', 2, '', 0, '', 0),
(575, 569, '', '附件分类编辑表单', 'admin', '', '', 'file/category/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(576, 569, '', '附件分类修改', 'admin', '', '', 'file/category/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(577, 2, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(578, 3, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(579, 6, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(580, 99, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(581, 5, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(582, 70, '', '优惠卷模板列表', 'admin', '', '', 'marketing/coupon/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(583, 70, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(584, 29, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(585, 10, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(586, 11, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(587, 25, '', '个人中心', 'admin', '', '', '', '', '[]', 0, 1, 1, 1, '/system/user', '', 1, '', 0, 'system-user', 0),
(589, 9, '', '用户标签', 'admin', 'user.user_label', 'index', '', '', '[]', 8, 1, 0, 1, '/user/label', '', 1, 'user', 0, 'user-user-label', 0),
(590, 589, '', '获取用户标签', 'admin', '', '', 'user/label/<uid>', 'GET', '[]', 0, 0, 0, 1, '', '9/589', 2, '', 0, '', 0),
(591, 589, '', '删除用户标签', 'admin', '', '', 'user/user_label/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(592, 589, '', '添加修改用户标签', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/label_add', '', 1, '', 0, 'admin-user-label_add', 0),
(593, 592, '', '添加修改用户标签表单', 'admin', '', '', 'user/user_label/add/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(594, 592, '', '保存修改用户标签', 'admin', '', '', 'user/user_label/save', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(596, 2, '', '商品导出', 'admin', '', '', 'export/storeProduct', 'GET', '[]', 10, 0, 0, 1, '', '', 2, '', 0, 'export-storeProduct', 0),
(597, 5, '', '订单导出', 'admin', '', '', 'export/storeorder', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'export-storeOrder', 0),
(598, 77, '', '秒杀商品导出', 'admin', '', '', 'export/storeSeckill', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'export-storeSeckill', 0),
(600, 75, '', '拼团商品导出', 'admin', '', '', 'export/storeCombination', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'export-storeCombination', 0),
(601, 74, '', '砍价商品导出', 'admin', '', '', 'export/storeBargain', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'export-storeBargain', 0),
(602, 29, '', '推广员列表导出', 'admin', '', '', 'export/userAgent', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'export-userAgent', 0),
(603, 40, '', '用户充值导出', 'admin', '', '', 'export/userRecharge', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'export-userRecharge', 0),
(605, 25, '', '商业授权', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/system/maintain/auth', '', 1, '', 0, 'system-maintain-auth', 0),
(606, 29, '', '分销员数据', 'admin', '', '', 'agent/statistics', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(607, 587, '', '修改密码', 'admin', '', '', 'setting/update_admin', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(608, 605, '', '商业授权', 'admin', '', '', 'auth', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, '', 0),
(610, 20, '', '管理员列表', 'admin', '', '', 'setting/admin', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(611, 19, '', '身份列表', 'admin', '', '', 'setting/role', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(612, 2, '', '批量上下架', 'admin', '', '', 'product/product/product_show', 'PUT', '[]', 5, 0, 0, 1, '', '', 2, '', 0, 'product-product-product_show', 0),
(613, 585, '', '批量设置标签', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/set_label', '', 1, '', 0, 'admin-user-set_label', 0),
(614, 613, '', '获取标签表单', 'admin', '', '', 'user/set_label', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(615, 613, '', '保存标签', 'admin', '', '', 'user/save_set_label', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(618, 42, '', '佣金导出', 'admin', '', '', 'export/userCommission', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'export-userCommission', 0),
(619, 21, '', '权限列表', 'admin', '', '', 'setting/menus', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(620, 22, '', '商品详情', 'admin', '', '', 'product/product/<id>', 'GET', '[]', 0, 1, 1, 1, '', '', 2, '', 0, '', 0),
(621, 585, '', '保存用户信息', 'admin', '', '', 'user/user', 'POST', '[]', 10, 0, 0, 1, '', '', 2, '', 0, '', 0),
(622, 585, '', '积分余额', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/edit_other', '', 1, '', 0, '', 0),
(623, 622, '', '获取修改用户详情表单', 'admin', '', '', 'user/edit_other/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(624, 622, '', '修改用户余额', 'admin', '', '', 'user/update_other/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(625, 585, '', '赠送用户', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/user_level', '', 1, '', 0, '', 0),
(626, 625, '', '获取表单', 'admin', '', '', 'user/give_level/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(627, 625, '', '赠送会员等级', 'admin', '', '', 'user/save_give_level/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(628, 585, '', '单个用户分组设置', 'admin', '', '', 'user/save_set_group', 'PUT', '[]', 10, 0, 0, 1, '', '', 2, '', 0, '', 0),
(630, 375, '', '获取商品属性', 'admin', '', '', 'product/product/attrs/<id>/<type>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(631, 386, '', '商品规格获取', 'admin', '', '', 'product/product/attrs/<id>/<type>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(632, 397, '', '商品规格和获取', 'admin', '', '', 'product/product/attrs/<id>/<type>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(633, 395, '', '获取秒杀详情', 'admin', '', '', 'marketing/seckill/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(634, 40, '', '删除充值记录', 'admin', '', '', 'finance/recharge/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(635, 20, '', '修改管理员状态', 'admin', '', '', 'setting/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(636, 25, '', '其他权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/system/other', '', 1, '', 0, '', 0),
(637, 636, '', '消息提醒', 'admin', '', '', 'jnotice', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(638, 457, '', '获取运费模板详情', 'admin', '', '', 'setting/shipping_templates/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(639, 457, '', '删除运费模板', 'admin', '', '', 'setting/shipping_templates/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(640, 462, '', '修改配置分类状态', 'admin', '', '', 'setting/config_class/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(641, 462, '', '编辑配置分类', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, 'system/config/system_config_tab/edit', '', 1, '', 0, '', 0),
(642, 641, '', '获取编辑配置分类表单', 'admin', '', '', 'setting/config_class/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(655, 65, '', '在线升级', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/system/onlineUpgrade/index', '25/65', 1, '', 0, 'system-onlineUpgrade-index', 0),
(656, 0, 'md-construct', '装修', 'admin', '', '', '', '', '[]', 80, 1, 0, 1, '/setting/pages', '', 1, '', 1, 'admin-setting-pages', 0),
(657, 656, '', '页面设计', 'admin', '', '', '', '', '[]', 3, 1, 0, 1, '/setting/pages/devise', '12/656', 1, '', 0, 'admin-setting-pages-devise', 0),
(658, 656, '', '页面编辑', 'admin', '', '', '', '', '[]', 3, 1, 1, 1, '/setting/pages/diy', '12/656', 1, '', 0, 'admin-setting-pages-diy', 0),
(660, 656, '', '页面链接', 'admin', '', '', '', '', '[]', 3, 0, 0, 1, '/setting/pages/links', '12/656', 1, '', 0, 'admin-setting-pages-links', 0),
(661, 657, '', 'DIY列表', 'admin', '', '', 'diy/get_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(662, 657, '', '组件文章分类', 'admin', '', '', 'cms/category_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(663, 657, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/diy', '', 1, '', 0, 'admin-setting-diy-additional', 0),
(664, 663, '', '获取页面设计', 'admin', '', '', 'diy/get_info/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(665, 663, '', '保存和修改页面', 'admin', '', '', 'diy/save/<id?>', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'admin-setting-pages-diy-save', 0),
(666, 660, '', '路径列表', 'admin', '', '', 'diy/get_url', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(667, 663, '', '删除页面', 'admin', '', '', 'diy/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(668, 663, '', '修改页面状态', 'admin', '', '', 'diy/set_status/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(669, 2, '', '批量下架', 'admin', '', '', 'product/product/product_unshow', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(670, 581, '', '订单打印', 'admin', '', '', 'order/print/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(671, 585, '', '清除会员等级', 'admin', '', '', 'user/del_level/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(672, 271, '', 'H5推广二维码', 'admin', '', '', 'agent/look_h5_code', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(673, 416, '', '修改文章分类状态', 'admin', '', '', 'cms/category/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(674, 229, '', '清除城市缓存', 'admin', '', '', 'setting/city/clean_cache', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(675, 657, '', '组件商品分类', 'admin', '', '', 'diy/get_category', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(676, 657, '', '组件商品列表', 'admin', '', '', 'diy/get_product', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(677, 581, '', '订单号核销', 'admin', '', '', 'order/write_update/<order_id>', 'PUT', '[]', 0, 0, 0, 1, 'order/dels', '', 2, '', 0, 'admin-order-write_update', 0),
(678, 165, '', '客服列表', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/store_service/index', '', 1, '', 0, 'admin-setting-store_service-index', 0),
(679, 165, '', '客服话术', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/store_service/speechcraft', '', 1, '', 0, 'admin-setting-store_service-speechcraft', 0),
(685, 22, '', '上传商品视频', 'admin', '', '', 'product/product/get_temp_keys', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(686, 27, '', '直播管理', 'admin', '', '', '', '', '[]', 65, 1, 0, 1, '/marketing/live', '27', 1, '', 0, 'admin-marketing-live', 0),
(687, 686, '', '直播间管理', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/live/live_room', '', 1, '', 0, 'admin-marketing-live-live_room', 0),
(688, 686, '', '直播商品管理', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/live/live_goods', '', 1, '', 0, 'admin-marketing-live-live_goods', 0),
(689, 686, '', '主播管理', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/live/anchor', '', 1, '', 0, 'admin-marketing-live-anchor', 0),
(690, 687, '', '添加直播间', 'admin', '', '', '', '', '[]', 0, 0, 1, 1, '/marketing/live/add_live_room', '', 1, '', 0, 'admin-marketing-live-add_live_room', 0),
(691, 688, '', '添加直播商品', 'admin', '', '', '', '', '[]', 0, 0, 1, 1, '/marketing/live/add_live_goods', '', 1, '', 0, 'admin-marketing-live-add_live_goods', 0),
(693, 689, '', '主播列表', 'admin', '', '', 'live/anchor/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'admin-marketing-live-anchor', 0),
(694, 689, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/*', '', 1, '', 0, '', 0),
(695, 694, '', '添加/修改主播表单', 'admin', '', '', 'live/anchor/add/<id>', 'GET', '[]', 0, 0, 0, 1, 'live/anchor/add/<id>', '', 2, '', 0, 'live-anchor-add', 0),
(696, 694, '', '添加/修改提交', 'admin', '', '', 'live/anchor/save', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(697, 694, '', '删除主播', 'admin', '', '', 'live/anchor/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(698, 694, '', '设置主播是否显示', 'admin', '', '', 'live/anchor/set_show/<id>/<is_show>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(699, 688, '', '直播商品列表', 'admin', '', '', 'live/goods/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(700, 691, '', '生成直播商品', 'admin', '', '', 'live/goods/create', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(701, 691, '', '保存直播商品', 'admin', '', '', 'live/goods/add', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(702, 688, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/*', '', 1, '', 0, '/admin/*', 0),
(703, 702, '', '直播商品详情', 'admin', '', '', 'live/goods/detail/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(704, 702, '', '删除直播商品', 'admin', '', '', 'live/goods/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(705, 702, '', '同步直播商品', 'admin', '', '', 'live/goods/syncGoods', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(706, 702, '', '设置直播商品是否显示', 'admin', '', '', 'live/goods/set_show/<id>/<is_show>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(707, 687, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/*', '', 1, '', 0, '', 0),
(708, 687, '', '直播间列表', 'admin', '', '', 'live/room/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(709, 707, '', '添加直播间提交', 'admin', '', '', 'live/room/add', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(710, 707, '', '直播间详情', 'admin', '', '', 'live/room/detail/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(711, 707, '', '直播间添加（关联）商品', 'admin', '', '', 'live/room/add_goods', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(712, 707, '', '删除直播间', 'admin', '', '', 'live/room/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(713, 707, '', '设置直播间是否显示', 'admin', '', '', 'live/room/set_show/<id>/<is_show>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(714, 707, '', '同步直播间状态', 'admin', '', '', 'live/room/syncRoom', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(715, 898, '', '一键同步订阅消息', 'admin', '', '', 'app/routine/syncSubscribe', 'GET', '[]', 0, 0, 0, 1, '', '12/898', 2, '', 0, 'app-wechat-template-sync', 0),
(716, 0, 'md-stats', '统计', 'admin', '', '', '', '', '[]', 105, 1, 0, 1, '/statistic', '', 1, '', 1, 'admin-statistic', 0),
(717, 716, '', '商品统计', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/statistic/product', '', 1, '', 0, 'admin-statistic', 0),
(718, 716, '', '用户统计', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/statistic/user', '', 1, '', 0, 'admin-statistic', 0),
(719, 71, '', '添加优惠卷', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/marketing/store_coupon_issue/create', '27/30/71', 1, '', 0, 'marketing-store_coupon_issue-create', 0),
(720, 303, '', '配送员管理', 'admin', '', '', '', '', '[]', 10, 1, 0, 1, '/setting/delivery_service/index', '', 1, '', 0, 'setting-delivery-service', 0),
(721, 729, '', '编辑配送员', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/delivery_service/edit', '', 1, '', 0, 'setting-delivery_service-edit', 0),
(722, 720, '', '配送员列表', 'admin', '', '', 'order/delivery/index', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(723, 721, '', '修改配送员', 'admin', '', '', 'order/delivery/update/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(724, 729, '', '添加配送员', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/delivery_service/add', '', 1, '', 0, 'setting-delivery_service-add', 0),
(725, 724, '', '获取添加配送员表单', 'admin', '', '', 'order/delivery/add', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(726, 724, '', '保存配送员', 'admin', '', '', 'order/delivery/save', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(727, 729, '', '删除配送员', 'admin', '', '', 'order/delivery/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(728, 729, '', '配送员是否开启', 'admin', '', '', 'order/delivery/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(729, 720, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(731, 27, '', '付费会员', 'admin', '', '', '', '', '[]', 70, 1, 0, 1, '/user/grade', '27', 1, '', 0, 'user-user-grade', 0),
(732, 762, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 1, 1, '*', '', 1, '', 0, '', 0),
(733, 732, '', ' 添加会员批次', 'admin', '', '', 'user/member_batch/save/<id>', 'POST', '[]', 0, 1, 0, 1, '', '', 2, '', 0, '', 0),
(734, 732, '', '列表字段修改', 'admin', '', '', 'user/member_batch/set_value/<id>', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, 'user-member_batch-set_value', 0),
(735, 732, '', '会员卡导出', 'admin', '', '', 'export/memberCard/<id>', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, 'export-member_card', 0),
(736, 762, '', '卡密列表', 'admin', '', '', 'user/member_batch/index', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, '', 0),
(737, 732, '', '会员卡列表', 'admin', '', '', 'user/member_card/index', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'user-member_card-index', 0),
(738, 165, '', '用户留言', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/store_service/feedback', '', 1, '', 0, 'admin-setting-store_service-feedback', 0),
(739, 738, '', '列表展示', 'admin', '', '', 'app/feedback', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(740, 738, '', '附件权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(741, 740, '', '删除反馈', 'admin', '', '', 'app/feedback/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(742, 679, '', '列表展示', 'admin', '', '', 'app/wechat/speechcraft', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(743, 679, '', '附件权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '*', '', 1, '', 0, '', 0),
(744, 743, '', '添加话术', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/store_service/speechcraft/add', '', 1, '', 0, 'admin-setting-store_service-speechcraft-add', 0),
(745, 743, '', '编辑话术', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/store_service/speechcraft/edit', '', 1, '', 0, 'admin-setting-store_service-speechcraft-edit', 0),
(746, 744, '', '获取添加话术表单', 'admin', '', '', 'app/wechat/speechcraft/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(747, 744, '', '保存话术', 'admin', '', '', 'app/wechat/speechcraft', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(748, 745, '', '获取编辑话术表单', 'admin', '', '', 'app/wechat/speechcraft/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(749, 745, '', '确认修改', 'admin', '', '', 'app/wechat/speechcraft/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(750, 743, '', '删除话术', 'admin', '', '', 'app/wechat/speechcraft/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(751, 731, '', '会员类型', 'admin', '', '', '', '', '[]', 5, 1, 0, 1, '/user/grade/type', '', 1, '', 0, 'admin-user-member-type', 0),
(752, 751, '', '会员分类列表', 'admin', '', '', 'user/member/ship', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'user-member-ship', 0),
(753, 751, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 1, 1, '*', '', 1, '', 0, '', 0),
(754, 753, '', '会员卡类型保存', 'admin', '', '', 'user/member_ship/save/<id>', 'POST', '[]', 0, 1, 1, 1, '', '', 2, '', 0, 'user-member_ship-save', 0),
(755, 31, '', '砍价列表', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/store_bargain/bargain_list', '', 1, '', 0, 'marketing-store_bargain-bargain_list', 0),
(756, 585, '', '添加用户', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/save', '', 1, '', 0, 'admin-user-save', 0),
(757, 756, '', '获取添加用户表单', 'admin', '', '', 'user/user/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(758, 756, '', '保存用户', 'admin', '', '', 'user/user', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(759, 585, '', '同步公众号用户', 'admin', '', '', 'user/user/syncUsers', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'admin-user-synchro', 0),
(760, 4, '', '收银订单', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/order/offline', '', 1, '', 0, 'admin-order-offline', 0),
(761, 760, '', '线下收银订单', 'admin', '', '', 'order/scan_list', 'GET', '[]', 0, 0, 1, 1, '', '', 2, '', 0, 'admin-order-scan_list', 0),
(762, 731, '', '卡密会员', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/user/grade/card', '', 1, '', 0, 'admin-user-grade-card', 0),
(763, 731, '', '会员记录', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/user/grade/record', '', 1, '', 0, 'admin-user-grade-record', 0),
(764, 763, '', '会员记录列表', 'admin', '', '', 'user/member/record', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'user-member-record', 0),
(765, 731, '', '会员权益', 'admin', '', '', '', '', '[]', 4, 1, 0, 1, '/user/grade/right', '', 1, '', 0, 'admin-user-grade-right', 0),
(766, 716, '', '交易统计', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/statistic/transaction', '', 1, '', 0, 'admin-statistic', 0),
(767, 36, '', '发票管理', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/order/invoice/list', '', 1, '', 0, 'admin-order-startOrderInvoice-index', 0),
(768, 210, '', '编辑', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '', '', 2, '', 0, 'admin-order-invoice-edit', 0),
(769, 210, '', '订单信息', 'admin', '', '', 'order/invoice_order_info/<id>', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, 'admin-order-invoice-orderInfo', 0),
(770, 210, '', '编辑提交', 'admin', '', '', 'order/invoice/set/<id>', 'POST', '[]', 0, 1, 0, 1, '', '', 2, '', 0, 'admin-order-invoice-update', 0),
(771, 765, '', '会员权益列表', 'admin', '', '', 'user/member/right', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'user-member-right', 0),
(772, 765, '', '附加权限', 'admin', '', '', '', '', '[]', 0, 0, 1, 1, '*', '', 1, '', 0, '', 0),
(773, 772, '', '会员权益保存', 'admin', '', '', 'user/member_right/save/<id>', 'POST', '[]', 0, 1, 1, 1, '', '', 2, '', 0, 'user-member_right-save', 0),
(774, 589, '', '用户标签列表', 'admin', '', '', 'user/user_label_cate/all', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, 'admin-user-user_lable_cate-all', 0),
(778, 740, '', '获取修改备注表单接口', 'admin', '', '', 'app/feedback/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(779, 740, '', '修改用户备注接口', 'admin', '', '', 'app/feedback/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(780, 589, '', '标签分类', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/label_cate', '', 1, '', 0, '', 0),
(781, 780, '', '获取标签分类列表', 'admin', '', '', 'user/user_label_cate/all', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(782, 780, '', '添加标签分类', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/label_cate/add', '', 1, '', 0, '', 0),
(783, 782, '', '获取标签分类表单', 'admin', '', '', 'user/user_label_cate/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(784, 782, '', '保存标签分类', 'admin', '', '', 'user/user_label_cate', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(785, 780, '', '修改标签分类', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/user/label_cate/edit', '', 1, '', 0, '', 0),
(786, 785, '', '获取修改标签分类表单', 'admin', '', '', 'user/user_label_cate/<id>/edit', 'GET', '[]', 0, 0, 0, 1, 'user/user_label_cate/<id>/edit', '', 2, '', 0, '', 0),
(787, 785, '', '保存用户标签分类', 'admin', '', '', 'user/user_label_cate/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(788, 780, '', '删除用户标签分类', 'admin', '', '', 'user/user_label_cate/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(789, 743, '', '话术分类', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/store_service/speechcraft/cate', '', 1, '', 0, 'admin-setting-store_service-speechcraft-cate', 0),
(790, 789, '', '获取话术分类列表', 'admin', '', '', 'app/wechat/speechcraftcate', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(791, 789, '', '添加话术分类', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/store_service/speechcraft/cate/create', '', 1, '', 0, '', 0),
(792, 791, '', '获取话术分类表单', 'admin', '', '', 'app/wechat/speechcraftcate/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(793, 791, '', '保存话术分类', 'admin', '', '', 'app/wechat/speechcraftcate', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(794, 795, '', '获取话术分类表单', 'admin', '', '', 'app/wechat/speechcraftcate/<id>/edit', 'GET', '[]', 0, 0, 0, 1, 'app/wechat/speechcraftcate/<id>/edit', '', 2, '', 0, '', 0),
(795, 789, '', '修改话术分类', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/setting/store_service/speechcraft/cate/edit', '', 1, '', 0, '', 0),
(796, 795, '', '保存修改客户话术分类', 'admin', '', '', 'app/wechat/speechcraftcate/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(797, 789, '', '删除话术分类', 'admin', '', '', 'app/wechat/speechcraftcate/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(798, 209, '', '获取送货人列表', 'admin', '', '', 'order/delivery/list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(799, 209, '', '获取电子面单打印默认配置', 'admin', '', '', 'order/sheet_info', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(800, 581, '', '电子面单打印', 'admin', '', '', 'order/order_dump/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(801, 760, '', '获取收银二维码', 'admin', '', '', 'order/offline_scan', 'GET', '[]', 0, 0, 0, 1, '', '4/760', 2, '', 0, '', 0),
(802, 767, '', '获取订单发票数据', 'admin', '', '', 'order/invoice/chart', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(803, 762, '', '下载卡密二维码', 'admin', '', '', 'user/member_scan', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(805, 584, '', '修改推广人', 'admin', '', '', 'agent/spread', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(806, 71, '', '复制优惠券', 'admin', '', '', 'marketing/coupon/copy/<id>', 'GET', '[]', 0, 0, 0, 1, 'marketing/coupon/copy/369', '', 2, '', 0, '', 0),
(807, 755, '', '获取砍价列表', 'admin', '', '', 'marketing/bargain_list', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(808, 77, '', '秒杀商品列表', 'admin', '', '', 'marketing/seckill', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(809, 95, '', '获取平台用户信息', 'admin', '', '', 'serve/info', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(810, 95, '', '获取平台消费列表', 'admin', '', '', 'serve/record', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(811, 95, '', '修改手机号', 'admin', '', '', 'serve/update_phone', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(812, 95, '', '修改签名', 'admin', '', '', 'serve/sms/sign', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(813, 95, '', '修改账号密码', 'admin', '', '', 'serve/modify', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(814, 721, '', '获取编辑配送员表单', 'admin', '', '', 'order/delivery/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(815, 717, '', '获取基础商品接口', 'admin', '', '', 'statistic/product/get_basic', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(816, 717, '', '获取商品趋势', 'admin', '', '', 'statistic/product/get_trend', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(817, 717, '', '获取商品排行', 'admin', '', '', 'statistic/product/get_product_ranking', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(818, 718, '', '获取用户基础', 'admin', '', '', 'statistic/user/get_basic', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(819, 718, '', '获取用户趋势', 'admin', '', '', 'statistic/user/get_trend', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(820, 718, '', '获取用户地区排行', 'admin', '', '', 'statistic/user/get_region', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(821, 718, '', '获取用户性别排行', 'admin', '', '', 'statistic/user/get_sex', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(822, 766, '', '获取交易趋势', 'admin', '', '', 'statistic/trade/top_trade', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(823, 766, '', '获取订单趋势', 'admin', '', '', 'statistic/trade/bottom_trade', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(824, 718, '', '导出用户统计', 'admin', '', '', 'statistic/user/get_excel', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(825, 717, '', '导出商品统计', 'admin', '', '', 'statistic/product/get_excel', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(828, 10, '', '用户列表', 'admin', '', '', 'user/user', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(830, 732, '', '卡密列表', 'admin', '', '', 'user/member_card/index/<card_batch_id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(831, 423, '', '进入工作台', 'admin', '', '', 'app/wechat/kefu/login/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(832, 71, '', '保存优惠券', 'admin', '', '', 'marketing/coupon/save_coupon', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(833, 755, '', '砍价详情', 'admin', '', '', 'marketing/bargain_list_info/<id>', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(834, 95, '', '短信记录列表', 'admin', '', '', 'notify/sms/record', 'GET', '[]', 0, 0, 0, 1, 'notify/sms/record', '', 2, '', 0, '', 0),
(835, 28, '', '分销设置表单', 'admin', '', '', 'agent/config/edit_basics', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(836, 28, '', '分销设置表单提交', 'admin', '', '', 'agent/config/save_basics', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(837, 79, '', '积分配置表单', 'admin', '', '', 'marketing/integral_config/edit_basics', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, '', 0),
(838, 79, '', '积分配置表单提交', 'admin', '', '', 'marketing/integral_config/save_basics', 'POST', '[]', 0, 1, 0, 1, '', '', 2, '', 0, '', 0),
(843, 154, '', '签到天数头部数据', 'admin', '', '', 'setting/sign_data/header', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, '', 0),
(844, 154, '', '设置签到数据状态', 'admin', '', '', 'setting/sign_data/set_status/<id>/<status>', 'PUT', '[]', 0, 1, 0, 1, '', '', 2, '', 0, '', 0),
(845, 154, '', '签到天数列表', 'admin', '', '', 'setting/sign_data', 'GET', '[]', 0, 1, 0, 1, '', '', 2, '', 0, '', 0),
(846, 154, '', '添加签到天数表单', 'admin', '', '', 'setting/sign_data/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(847, 154, '', '添加签到天数', 'admin', '', '', 'setting/sign_data', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(848, 154, '', '编辑签到天数表单', 'admin', '', '', 'setting/sign_data/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(849, 154, '', '编辑签到天数', 'admin', '', '', 'setting/sign_data/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(850, 154, '', '删除签到天数', 'admin', '', '', 'setting/sign_data/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(876, 78, '', '秒杀配置列表', 'admin', '', '', 'setting/seckill_data', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(877, 78, '', '添加秒杀表单', 'admin', '', '', 'setting/seckill_data/create', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(878, 78, '', '添加秒杀', 'admin', '', '', 'setting/seckill_data', 'POST', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(879, 78, '', '编辑秒杀表单', 'admin', '', '', 'setting/seckill_data/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(880, 78, '', '编辑秒杀', 'admin', '', '', 'settting/seckill_data/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(881, 78, '', '删除秒杀', 'admin', '', '', 'setting/seckill_data/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(884, 128, '', '获取数据分类', 'admin', '', '', 'setting/group_all', 'GET', '[]', 0, 0, 0, 1, '', '', 2, '', 0, '', 0),
(885, 569, '', '附件名称修改', 'admin', '', '', 'file/file/update/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '25/566/569', 2, '', 0, '', 0),
(886, 577, '', '用户标签接口', 'admin', '', '', 'user/user_label', 'GET', '[]', 0, 0, 0, 1, '', '1/2/577', 2, '', 0, '', 0),
(887, 625, '', '获取赠送付费会员时长表单', 'admin', '', '', 'user/give_level_time/<id>', 'GET', '[]', 0, 0, 0, 1, '', '9/10/585/625', 2, '', 0, '', 0),
(888, 625, '', '保存赠送付费会员时长', 'admin', '', '', 'user/save_give_level_time/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '9/10/585/625', 2, '', 0, '', 0),
(889, 663, '', '添加页面', 'admin', '', '', 'diy/create', 'GET', '[]', 0, 0, 0, 1, '', '12/656/657/663', 2, '', 0, 'admin-template', 0),
(890, 663, '', '保存新增', 'admin', '', '', 'diy/create', 'POST', '[]', 0, 0, 0, 1, '', '12/656/657/663', 2, '', 0, 'admin-template', 0),
(891, 663, '', '设置默认数据', 'admin', '', '', 'diy/set_recovery/<id?>', 'GET', '[]', 0, 0, 0, 1, '', '12/656/657/663', 2, '', 0, '', 0),
(892, 663, '', '获取商品列表', 'admin', '', '', 'diy/get_product_list', 'GET', '[]', 0, 0, 0, 1, '', '12/656/657/663', 2, '', 0, '', 0),
(893, 577, '', '商品活动状态检测', 'admin', '', '', 'product/product/check_activity/<id>', 'GET', '[]', 0, 0, 0, 1, '', '1/2/577', 2, '', 0, '', 0),
(894, 589, '', '会员标签列表', 'admin', '', '', 'user/user_label', 'GET', '[]', 0, 0, 0, 1, '', '9/589', 2, '', 0, '', 0),
(895, 585, '', '新增客服选择用户列表', 'admin', '', '', 'app/wechat/kefu/create', 'GET', '[]', 0, 0, 0, 1, '', '9/10/585', 2, '', 0, '', 0),
(896, 26, '', '分销等级', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/membership_level/index', '26', 1, '', 0, '', 0),
(897, 4, '', '售后订单', 'admin', '', '', '', '', '[]', 9, 1, 0, 1, '/order/refund', '4', 1, '', 0, 'admin-order-refund', 0),
(898, 12, '', '消息管理', 'admin', '', '', '', '', '[]', 9, 1, 0, 1, '/setting/notification/index', '12', 1, '', 0, 'setting-notification', 0),
(902, 656, '', '主题风格', 'admin', '', '', '', '', '[]', 2, 1, 0, 1, '/setting/theme_style', '12/656', 1, '', 0, 'admin-setting-theme_style', 0),
(903, 656, '', 'PC商城', 'admin', '', '', '', '', '[]', 2, 1, 0, 1, '/setting/pc_group_data', '12/656', 1, '', 0, 'setting-system-pc_data', 0),
(904, 656, '', '客服页面广告', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/system_group_data/kf_adv', '', 1, '', 0, 'setting-system-group_data-kf_adv', 1),
(905, 34, '', '积分商品', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/store_integral/index', '27/34', 1, '', 0, 'marketing-store_integral', 0),
(906, 905, '', '积分商品列表', 'admin', '', '', 'marketing/integral_product', 'GET', '[]', 0, 0, 0, 1, '', '27/34/905', 2, '', 0, '', 0),
(908, 905, '', '添加积分商品', 'admin', '', '', '', '', '[]', 0, 0, 1, 1, '/marketing/store_integral/create', '27/34/905', 1, '', 0, 'marketing-store_integral-create', 0),
(909, 27, '', '抽奖管理', 'admin', '', '', '', '', '[]', 90, 1, 0, 1, '/marketing/lottery/index', '27', 1, '', 0, 'marketing-lottery-index', 0),
(912, 34, '', '积分订单', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/store_integral/order_list', '27/34', 1, '', 0, 'marketing-store_integral-order', 0),
(913, 905, '', '批量添加积分商品', 'admin', '', '', '', '', '[]', 0, 0, 1, 1, '/pages/marketing/store_integral/add_store_integral', '27/34/905', 1, '', 0, 'marketing-store_integral-create', 0),
(914, 897, '', '售后订单列表', 'admin', '', '', 'refund/list', 'GET', '[]', 0, 0, 0, 1, '', '4/897', 2, '', 0, '', 0),
(915, 5, '', '子订单列表', 'admin', '', '', 'order/split_order/<id>', 'GET', '[]', 0, 0, 0, 1, 'order/split_order/<id>', '4/5', 2, '', 0, '', 0),
(916, 5, '', '订单详情', 'admin', '', '', 'order/info/<id>', 'GET', '[]', 0, 0, 1, 1, '', '4/5', 2, '', 0, '', 0),
(917, 5, '', '订单记录', 'admin', '', '', 'order/status/<id>', 'GET', '[]', 0, 0, 1, 1, '', '4/5', 2, '', 0, '', 0),
(918, 5, '', '可拆分商品列表', 'admin', '', '', 'order/split_cart_info/<id>', 'GET', '[]', 0, 0, 1, 1, '', '4/5', 2, '', 0, '', 0),
(919, 5, '', '拆单发送货', 'admin', '', '', 'order/split_delivery/<id>', 'PUT', '[]', 0, 0, 1, 1, '', '4/5', 2, '', 0, '', 0),
(920, 896, '', '修改分销等级状态', 'admin', '', '', 'agent/level/set_status/<id>/<status>', 'PUT', '{\"[PUT] agent\":\"level\",\"set_status\":\"<id>\"}', 0, 0, 1, 1, '', '26/896', 2, '', 0, '', 0),
(921, 896, '', '修改分销等级任务状态', 'admin', '', '', 'agent/level_task/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(922, 896, '', '获取赠送分销等级表单', 'admin', '', '', 'agent/get_level_form', 'GET', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(923, 896, '', '赠送分销等级', 'admin', '', '', 'agent/give_level', 'POST', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(924, 896, '', '分销等级列表', 'admin', '', '', 'agent/level', 'GET', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(925, 896, '', '添加分销等级表单', 'admin', '', '', 'agent/level/create', 'GET', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(926, 896, '', '编辑分销等级表单', 'admin', '', '', 'agent/level/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(927, 896, '', '分销等级任务', 'admin', '', '', 'agent/level_task', 'GET', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(928, 896, '', '删除分销等级', 'admin', '', '', 'agent/level/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(929, 896, '', '添加分销员等级', 'admin', '', '', 'agent/level', 'POST', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(930, 896, '', '编辑分销员等级', 'admin', '', '', 'agent/level/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(931, 896, '', '添加分销员等级任务表单', 'admin', '', '', 'agent/level_task/create', 'GET', '[]', 0, 0, 0, 1, 'agent/level_task/create', '26/896', 2, '', 0, '', 0),
(932, 896, '', '添加分销员等级任务', 'admin', '', '', 'agent/level_task', 'POST', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(933, 896, '', '编辑分销员等级任务表单', 'admin', '', '', 'agent/level_task/<id>/edit', 'GET', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(934, 896, '', '编辑分销员等级任务', 'admin', '', '', 'agent/level_task/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '26/896', 2, '', 0, '', 0),
(935, 896, '', '删除分销员等级任务', 'admin', '', '', 'agent/level_task/<id>', 'DELETE', '[]', 0, 0, 0, 1, 'agent/level_task/<id>', '26/896', 2, '', 0, '', 0),
(936, 423, '', '新增客服选择用户列表', 'admin', '', '', 'app/wechat/kefu/create', 'GET', '[]', 0, 0, 1, 1, '', '165/678/423', 2, '', 0, '', 0),
(937, 78, '', '秒杀配置头部', 'admin', '', '', 'setting/seckill_data/header', 'GET', '[]', 0, 0, 0, 1, '', '27/33/78', 2, '', 0, '', 0),
(938, 154, '', '签到配置编辑保存', 'admin', '', '', 'setting/group_data/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '27/34/154', 2, '', 0, '', 0),
(939, 154, '', '签到配置添加保存', 'admin', '', '', 'setting/group_data', 'POST', '[]', 0, 0, 0, 1, '', '27/34/154', 2, '', 0, '', 0),
(940, 905, '', '添加积分商品保存', 'admin', '', '', 'marketing/integral/<id>', 'POST', '[]', 0, 0, 0, 1, '', '27/34/905', 2, '', 0, '', 0),
(941, 912, '', '积分订单头部', 'admin', '', '', 'marketing/integral/order/chart', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(942, 912, '', '积分订单列表', 'admin', '', '', 'marketing/integral/order/list', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(943, 905, '', '积分商品编辑', 'admin', '', '', 'marketing/integral/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/34/905', 2, '', 0, '', 0),
(944, 912, '', '发货物流列表', 'admin', '', '', 'marketing/integral/order/express_list', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(945, 912, '', '快递列表', 'admin', '', '', 'marketing/integral/order/delivery/list', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(946, 912, '', '图表详情', 'admin', '', '', 'marketing/integral/order/sheet_info', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(947, 912, '', '发货', 'admin', '', '', 'marketing/integral/order/delivery/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(948, 912, '', '配送信息表单', 'admin', '', '', 'marketing/integral/order/distribution/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(949, 912, '', '配送信息保存', 'admin', '', '', 'marketing/integral/order/distribution/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(950, 912, '', '订单详情', 'admin', '', '', 'marketing/integral/order/info/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(951, 912, '', '订单记录', 'admin', '', '', 'marketing/integral/order/status/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(952, 912, '', '小票打印', 'admin', '', '', 'marketing/integral/order/print/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(953, 912, '', '物流查询', 'admin', '', '', 'marketing/integral/order/express/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(954, 912, '', '订单备注', 'admin', '', '', 'marketing/integral/order/remark/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(955, 912, '', '收货', 'admin', '', '', 'marketing/integral/order/take/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '27/34/912', 2, '', 0, '', 0),
(960, 909, '', '抽奖列表', 'admin', '', '', 'marketing/lottery/list', 'GET', '[]', 0, 1, 0, 1, '', '27/909', 2, '', 0, '', 0),
(961, 909, '', '抽奖商品详情', 'admin', '', '', 'marketing/lottery/detail/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(962, 909, '', '用户等级', 'admin', '', '', 'user/user_label', 'GET', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(963, 909, '', '会员等级', 'admin', '', '', 'user/user_level/vip_list', 'GET', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(964, 909, '', '编辑保存', 'admin', '', '', 'marketing/lottery/edit/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(965, 27, '', '营销公共权限', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, 'admin-marketing', '27', 1, '', 0, '', 0),
(966, 965, '', '附件分类', 'admin', '', '', 'file/category', 'GET', '[]', 0, 0, 0, 1, '', '27/965', 2, '', 0, '', 0),
(967, 965, '', '附件列表', 'admin', '', '', 'file/file', 'GET', '[]', 0, 0, 0, 1, '', '27/965', 2, '', 0, '', 0),
(968, 909, '', '删除抽奖', 'admin', '', '', 'marketing/lottery/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(969, 909, '', '抽奖记录列表', 'admin', '', '', 'marketing/lottery/record/list/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(970, 909, '', '物流公司', 'admin', '', '', 'order/express_list', 'GET', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(971, 909, '', '抽奖记录备注', 'admin', '', '', 'marketing/lottery/record/deliver', 'POST', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(972, 909, '', '抽奖状态', 'admin', '', '', 'marketing/lottery/set_status/<id>/<status>', 'POST', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(973, 909, '', '添加抽奖', 'admin', '', '', 'marketing/lottery/add', 'POST', '[]', 0, 0, 0, 1, '', '27/909', 2, '', 0, '', 0),
(975, 28, '', '分销配置头部', 'admin', '', '', 'setting/config/header_basics', 'GET', '[]', 0, 0, 0, 1, '', '26/28', 2, '', 0, '', 0),
(976, 717, '', '查看商品', 'admin', '', '', 'product/product/<id>', 'GET', '[]', 0, 0, 0, 1, '', '716/717', 2, '', 0, '', 0),
(977, 657, '', '获取风格设置', 'admin', '', '', 'diy/get_color_change/<type>', 'GET', '[]', 0, 0, 0, 1, '', '12/656/657', 2, '', 0, '', 0),
(978, 657, '', '获取个人中心菜单', 'admin', '', '', 'diy/get_member', 'GET', '[]', 0, 0, 0, 1, '', '12/656/657', 2, '', 0, '', 0),
(979, 657, '', '个人中心组件分类', 'admin', '', '', 'diy/get_page_category', 'GET', '[]', 0, 0, 0, 1, '', '12/656/657', 2, '', 0, '', 0),
(980, 657, '', '个人中心组件树形分类', 'admin', '', '', 'product/category/tree/<type>', 'GET', '[]', 0, 0, 0, 1, '', '12/656/657', 2, '', 0, '', 0),
(981, 657, '', '获取页面链接', 'admin', '', '', 'diy/get_page_link/<cate_id>', 'GET', '[]', 0, 0, 0, 1, '', '12/656/657', 2, '', 0, '', 0),
(982, 657, '', '商品分类页保存', 'admin', '', '', 'diy/color_change/<status>/<type>', 'PUT', '[]', 0, 0, 0, 1, '', '12/656/657', 2, '', 0, '', 0),
(983, 657, '', '个人中心页保存', 'admin', '', '', 'diy/member_save', 'POST', '[]', 0, 0, 0, 1, '', '12/656/657', 2, '', 0, '', 0),
(984, 128, '', '获取组合数据', 'admin', '', '', 'setting/group_data', 'GET', '[]', 0, 0, 0, 1, '', '12/656/128', 2, '', 0, '', 0),
(985, 128, '', '获取头部', 'admin', '', '', 'setting/sign_data/header', 'GET', '[]', 0, 0, 0, 1, '', '12/656/128', 2, '', 0, '', 0),
(986, 128, '', '保存配置', 'admin', '', '', 'setting/group_data/save_all', 'POST', '[]', 0, 0, 0, 1, '', '12/656/128', 2, '', 0, '', 0),
(987, 128, '', '客服页面广告', 'admin', '', '', 'setting/get_kf_adv', 'GET', '[]', 0, 0, 0, 1, '', '12/656/128', 2, '', 0, '', 0),
(988, 898, '', '消息管理列表', 'admin', '', '', 'setting/notification/index', 'GET', '[]', 0, 0, 0, 1, '', '12/898', 2, '', 0, '', 0),
(989, 898, '', '模板消息详情', 'admin', '', '', 'setting/notification/info', 'GET', '[]', 0, 0, 0, 1, '', '12/898', 2, '', 0, '', 0),
(990, 898, '', '编辑保存', 'admin', '', '', 'setting/notification/save', 'POST', '[]', 0, 0, 0, 1, '', '12/898', 2, '', 0, '', 0),
(991, 898, '', '模板消息状态修改', 'admin', '', '', 'setting/notification/set_status/<type>/<status>/<id>', 'PUT', '[]', 0, 0, 0, 1, '', '12/898', 2, '', 0, '', 0),
(992, 898, '', '一键同步模版消息', 'admin', '', '', 'app/wechat/syncSubscribe', 'GET', '[]', 0, 0, 0, 1, '', '12/898', 2, '', 0, '', 0),
(993, 135, '', '小程序', 'admin', '', '', '', '', '[]', 3, 1, 0, 1, '/app/routine', '135', 1, '', 0, 'admin-routine', 0),
(994, 993, '', '小程序下载', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/app/routine/download', '135/993', 1, '', 0, 'routine-download', 0),
(995, 994, '', '下载小程序页面数据', 'admin', '', '', 'app/routine/info', 'GET', '[]', 0, 0, 0, 1, '', '135/993/994', 2, '', 0, '', 0),
(996, 994, '', '下载小程序模版', 'admin', '', '', 'app/routine/download', 'POST', '[]', 0, 0, 0, 1, '', '135/993/994', 2, '', 0, '', 0),
(997, 716, '', '订单统计', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/statistic/order', '716', 1, '', 0, 'admin-statistic', 0),
(998, 37, '', '资金流水', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/finance/capital_flow/index', '35/37', 1, '', 0, 'finance-capital_flow-index', 0),
(999, 37, '', '账单记录', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/finance/billing_records/index', '35/37', 1, '', 0, 'finance-billing_records-index', 0),
(1000, 566, '', '富文本上传图片', 'admin', '', '', '', '', '[]', 0, 0, 1, 1, '/widget.images/index.html', '25/566', 1, '', 0, 'admin-user-user-index', 0),
(1001, 34, '', '积分记录', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/point_record', '27/34', 1, '', 0, 'marketing-point_record-index', 0),
(1002, 34, '', '积分统计', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/point_statistic', '27/34', 1, '', 0, 'marketing-point_statistic-index', 0),
(1003, 35, '', '余额记录', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/finance/balance', '35', 1, '', 0, 'finance-balance-index', 0),
(1004, 1003, '', '余额记录', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/finance/balance/balance', '35/1003', 1, '', 0, 'finance-user-balance', 0),
(1005, 716, '', '余额统计', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/statistic/balance', '716', 1, '', 0, 'admin-statistic', 0),
(1006, 69, '', '公众号配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/wechat_config/3/2', '135/69', 1, '', 0, 'setting-system-config', 0),
(1007, 993, '', '小程序配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/routine_config/3/7', '135/993', 1, '', 0, 'setting-system-config', 0),
(1008, 135, '', 'PC端', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/app/pc', '135', 1, '', 0, 'admin-pc', 0),
(1009, 135, '', 'APP', 'admin', '', '', '', '', '[]', 2, 1, 0, 1, '/app/app', '135', 1, '', 0, 'admin-app', 0),
(1010, 1008, '', 'PC端配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/pc_config/3/75', '135/1008', 1, '', 0, 'setting-system-config', 0),
(1011, 1009, '', 'APP配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/app_config/3/77', '135/1009', 1, '', 0, 'setting-system-config', 0),
(1012, 1056, '', '系统存储配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/storage', '12', 1, '', 0, 'setting-storage', 0),
(1013, 26, '', '事业部', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/agent/division', '26', 1, '', 0, 'agent-division', 0),
(1014, 1013, '', '事业部列表', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/division/index', '26/1013', 1, '', 0, 'agent-division-index', 0),
(1015, 1013, '', '代理商列表', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/division/agent/index', '26/1013', 1, '', 0, 'agent-division-agent-index', 0),
(1016, 1013, '', '代理商申请', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/division/agent/applyList', '26/1013', 1, '', 0, 'agent-division-agent-applyList', 0),
(1018, 909, '', '抽奖配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/lottery/create', '27/909', 1, '', 0, 'admin-marketing-lottery-create', 0),
(1019, 909, '', '中奖记录', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/marketing/lottery/recording_list', '27/909', 1, '', 0, 'admin-marketing-lottery-recording_list-id', 0),
(1020, 1014, '', '事业部列表', 'admin', '', '', 'agent/division/list', 'GET', '[]', 0, 0, 0, 1, '', '26/1013/1014', 2, '', 0, '', 0),
(1021, 1014, '', '添加事业部', 'admin', '', '', 'agent/division/create/<uid>', 'GET', '[]', 0, 0, 0, 1, '', '26/1013/1014', 2, '', 0, '', 0),
(1023, 27, '', '渠道码', 'admin', '', '', '', '', '[]', 55, 1, 0, 1, '/marketing/channel_code/channelCodeIndex', '27', 1, '', 0, 'marketing-channel_code-index', 0),
(1024, 1023, '', '添加公众号渠道码', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/marketing/channel_code/create', '27/1021', 1, '', 0, 'marketing-channel_code-create', 0),
(1025, 1023, '', '渠道码统计', 'admin', '', '', '', '', '[]', 0, 0, 0, 1, '/marketing/channel_code/code_statistic', '27/1021', 1, '', 0, 'marketing-channel_code-statistic', 0),
(1026, 1014, '', '事业部下级列表', 'admin', '', '', '/agent/division/down_list', 'GET', '[]', 0, 0, 0, 1, '', '26/1013/1014', 2, '', 0, '', 0),
(1027, 1014, '', '事业部保存', 'admin', '', '', 'agent/division/save', 'POST', '[]', 0, 0, 0, 1, '', '26/1013/1014', 2, '', 0, '', 0),
(1028, 1014, '', '事业部状态切换', 'admin', '', '', 'agent/division/set_status/<status>/<uid>', 'PUT', '[]', 0, 0, 0, 1, '', '26/1013/1014', 2, '', 0, '', 0),
(1029, 1014, '', '事业部删除', 'admin', '', '', 'division/del/<type>/<uid>', 'DELETE', '[]', 0, 0, 0, 1, '', '26/1013/1014', 2, '', 0, '', 0),
(1030, 1015, '', '代理商列表', 'admin', '', '', 'agent/division/list', 'GET', '[]', 0, 0, 0, 1, '', '26/1013/1015', 2, '', 0, '', 0),
(1031, 1015, '', '代理商下级列表', 'admin', '', '', 'agent/division/down_list', 'GET', '[]', 0, 0, 0, 1, '', '26/1013/1015', 2, '', 0, '', 0),
(1032, 1015, '', '添加代理商', 'admin', '', '', 'agent/division/agent/create/<uid>', 'GET', '[]', 0, 0, 0, 1, '', '26/1013/1015', 2, '', 0, '', 0),
(1033, 1015, '', '代理商保存', 'admin', '', '', 'agent/division/agent/save', 'POST', '[]', 0, 0, 0, 1, '', '26/1013/1015', 2, '', 0, '', 0),
(1034, 1015, '', '代理商状态切换', 'admin', '', '', 'agent/division/set_status/<status>/<uid>', 'PUT', '[]', 0, 0, 0, 1, '', '26/1013/1015', 2, '', 0, '', 0),
(1035, 1015, '', '代理商删除', 'admin', '', '', 'agent/division/del/<type>/<uid>', 'DELETE', '[]', 0, 0, 0, 1, '', '26/1013/1015', 2, '', 0, '', 0),
(1036, 1016, '', '代理商申请列表', 'admin', '', '', 'agent/division/agent_apply/list', 'GET', '[]', 0, 0, 0, 1, '', '26/1013/1016', 2, '', 0, '', 0),
(1037, 1016, '', '代理商审核', 'admin', '', '', 'agent/division/examine_apply/<id>/<type>', 'GET', '[]', 0, 0, 0, 1, '', '26/1013/1016', 2, '', 0, '', 0),
(1038, 1016, '', '提交审核', 'admin', '', '', 'agent/division/apply_agent/save', 'POST', '[]', 0, 0, 0, 1, '', '26/1013/1016', 2, '', 0, '', 0),
(1039, 1016, '', '删除审核', 'admin', '', '', 'agent/division/del_apply/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '26/1013/1016', 2, '', 0, '', 0),
(1042, 1023, '', '渠道码分类列表', 'admin', '', '', 'app/wechat_qrcode/cate/list', 'GET', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1043, 1023, '', '渠道码分类添加编辑表单', 'admin', '', '', 'app/wechat_qrcode/cate/create/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1044, 1023, '', '渠道码分类保存', 'admin', '', '', 'app/wechat_qrcode/cate/save', 'POST', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1045, 1023, '', '渠道码分类删除', 'admin', '', '', 'app/wechat_qrcode/cate/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1046, 1023, '', '保存渠道码', 'admin', '', '', 'app/wechat_qrcode/save/<id>', 'POST', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1047, 1023, '', '渠道码详情', 'admin', '', '', 'app/wechat_qrcode/info/<id>', 'GET', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1048, 1023, '', '渠道码列表', 'admin', '', '', 'app/wechat_qrcode/list', 'GET', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1049, 1023, '', '删除渠道码', 'admin', '', '', 'app/wechat_qrcode/del/<id>', 'DELETE', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1050, 1023, '', '渠道码状态切换', 'admin', '', '', 'app/wechat_qrcode/set_status/<id>/<status>', 'PUT', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1051, 1023, '', '渠道码用户列表', 'admin', '', '', 'app/wechat_qrcode/user_list/<qid>', 'GET', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1052, 1023, '', '获取用户标签', 'admin', '', '', 'user/user_label', 'GET', '[]', 0, 0, 0, 1, '', '27/1023', 2, '', 0, '', 0),
(1053, 27, '', '充值配置', 'admin', '', '', '', '', '[]', 60, 1, 0, 1, '/marketing/recharge', '27', 1, '', 0, 'marketing-recharge-index', 0),
(1055, 1009, '', '版本管理', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/app/app/version', '135/1009', 1, '', 0, 'admin-app-version', 0),
(1056, 12, '', '接口配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/other_config', '12', 1, '', 0, 'setting-other', 0),
(1057, 1056, '', '小票打印配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/other_config/print/2/21', '12/1056', 1, '', 0, 'setting-other-print', 0),
(1058, 1056, '', '商品采集配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/other_config/copy/2/41', '12/1056', 1, '', 0, 'setting-other-copy', 0),
(1059, 1056, '', '物流查询配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/other_config/logistics/2/64', '12/1056', 1, '', 0, 'setting-other-logistics', 0),
(1060, 1056, '', '电子面单配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/other_config/electronic/2/66', '12/1056', 1, '', 0, 'setting-other-electronic', 0),
(1061, 12, '', '协议设置', 'admin', '', '', '', '', '[]', 9, 1, 0, 1, '/setting/agreement', '12', 1, '', 0, 'setting-agreement', 0),
(1062, 1056, '', '短信接口配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/other_config/sms/2/96', '12/1056', 1, '', 0, 'setting-other-sms', 0),
(1063, 1056, '', '商城支付配置', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/other_config/pay/2/23', '12/1056', 1, '', 0, 'setting-other-pay', 0),
(1064, 25, '', '对外接口', 'admin', '', '', '', '', '[]', 6, 1, 0, 1, '/setting/other_out_config', '25', 1, '', 0, 'setting-other-out', 0),
(1066, 1064, '', '账号管理', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/system_out_account/index', '56/1064', 1, '', 0, 'setting-system-out-account-index', 0),
(1067, 25, '', '语言设置', 'admin', '', '', '', '', '[]', 5, 1, 0, 1, '/setting/lang', '25', 1, '', 0, 'admin-lang', 0),
(1068, 1067, '', '语言列表', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/lang/list', '12/1067', 1, '', 0, 'admin-lang-list', 0),
(1069, 1067, '', '语言详情', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/lang/info', '12/1067', 1, '', 0, 'admin-lang-info', 0),
(1070, 1067, '', '地区列表', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/lang/country', '25/1067', 1, '', 0, 'admin-lang-country', 0),
(1071, 56, '', '文件管理', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/system/maintain/system_file/opendir', '25/56', 1, '', 0, 'system-maintain-system-file', 0),
(1072, 1064, '', '接口文档', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/setting/system_out_interface/index', '56/1064', 1, '', 0, 'setting-system-out-interface-index', 0),
(1073, 25, '', '数据维护', 'admin', '', '', '', '', '[]', 7, 1, 0, 1, 'system/database/index', '25', 1, '', 0, 'system-database-index', 0),
(1075, 731, '', '会员配置', 'admin', '', '', '', '', '[]', 6, 1, 0, 1, '/marketing/member/system_config/3/67', '27/731', 1, '', 0, 'marketing-member-system_config', 0),
(1076, 56, '', '定时任务', 'admin', '', '', '', '', '[]', 0, 1, 0, 1, '/system/crontab', '25/56', 1, '', 0, 'system-crontab-index', 0);
SQL
            ],
            [
                'code' => 470,
                'type' => 1,
                'table' => "system_timer",
                'findSql' => "select * from information_schema.tables where table_name ='@table'",
                'sql' => "CREATE TABLE IF NOT EXISTS `@table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '定时器名称',
  `mark` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '周期状态 1=每隔多少秒 2=每隔多少分钟 3=每隔多少小时 4=每隔多少天 5=每天几点执行 6=每周周几几点执行 7=每月几号几点执行',
  `week` int(11) NOT NULL DEFAULT '0' COMMENT '周',
  `day` int(11) NOT NULL DEFAULT '0' COMMENT '日',
  `hour` int(11) NOT NULL DEFAULT '0' COMMENT '时',
  `minute` int(11) NOT NULL DEFAULT '0' COMMENT '分',
  `second` int(11) NOT NULL DEFAULT '0' COMMENT '秒',
  `last_execution_time` int(11) NOT NULL DEFAULT '0' COMMENT '上次执行时间',
  `next_execution_time` int(11) NOT NULL DEFAULT '0' COMMENT '下次执行时间',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `is_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='定时器'"
            ],
            [
                'code' => 470,
                'type' => -1,
                'table' => "system_timer",
                'sql' => <<<SQL
INSERT INTO `@table` (`id`, `name`, `mark`, `content`, `type`, `week`, `day`, `hour`, `minute`, `second`, `last_execution_time`, `next_execution_time`, `add_time`, `is_del`, `is_open`) VALUES
(1, '未支付自动取消订单', 'orderCancel', '每隔30秒执行自动取消到期未支付的订单', 1, 1, 1, 1, 30, 30, 0, 1670642407, 1670642377, 0, 1),
(2, '拼团到期订单处理', 'pinkExpiration', '每隔1分钟拼团到期之后的操作', 2, 1, 1, 1, 1, 0, 0, 1670642487, 1670642427, 0, 1),
(3, '到期自动解绑上级', 'agentUnbind', '每隔1分钟执行到期的绑定关系的解除', 2, 1, 1, 1, 1, 0, 0, 1670642534, 1670642474, 0, 1),
(4, '自动更新直播商品状态', 'liveProductStatus', '每隔3分钟执行更新直播商品状态', 2, 1, 1, 1, 3, 0, 0, 1670642694, 1670642514, 0, 1),
(5, '自动更新直播间状态', 'liveRoomStatus', '每隔3分钟执行更新直播间状态', 2, 1, 1, 1, 3, 0, 0, 1670642709, 1670642529, 0, 1),
(6, '订单自动收货', 'takeDelivery', '每隔5分钟执行订单到期自动收货', 2, 1, 1, 1, 5, 0, 0, 1670642891, 1670642591, 0, 1),
(7, '预售商品到期自动下架', 'advanceOff', '每隔5分钟执行预售商品到期下架', 2, 1, 1, 1, 5, 0, 0, 1670642913, 1670642613, 0, 1),
(8, '订单商品自动好评', 'productReplay', '每隔5分钟执行订单到期商品好评', 2, 1, 1, 1, 5, 0, 0, 1670642933, 1670642633, 0, 1),
(9, '清除昨日海报', 'clearPoster', '每天0时30分0秒执行一次清除昨日海报', 5, 1, 1, 0, 30, 0, 0, 1670862600, 1670815378, 0, 1);
SQL
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
