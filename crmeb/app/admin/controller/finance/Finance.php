<?php
/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2018/6/14 下午5:25
 */

namespace app\admin\controller\finance;

use app\admin\controller\AuthController;
use app\admin\model\user\{User,UserBill};
use app\admin\model\finance\FinanceModel;
use crmeb\services\{UtilService as Util,JsonService as Json};

/**
 * 微信充值记录
 * Class UserRecharge
 * @package app\admin\controller\user
 */
class Finance extends AuthController
{
    /**
     * 显示资金记录
     */
    public function bill()
    {
        $list = UserBill::where('type', 'not in', ['gain', 'system_sub', 'deduction', 'sign'])
            ->where('category', 'not in', 'integral')
            ->field(['title', 'type'])
            ->group('type')
            ->distinct(true)
            ->select()
            ->toArray();
        $this->assign('selectList', $list);
        return $this->fetch();
    }

    /**
     * 显示资金记录ajax列表
     */
    public function billlist()
    {
        $where = Util::getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
            ['limit', 20],
            ['page', 1],
            ['type', ''],
        ]);
        return Json::successlayui(FinanceModel::getBillList($where));
    }

    /**
     *保存资金监控的excel表格
     */
    public function save_bell_export()
    {
        $where = Util::getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
            ['type', ''],
        ]);
        FinanceModel::SaveExport($where);
    }

    /**
     * 显示佣金记录
     */
    public function commission_list()
    {
        $this->assign('is_layui', true);
        return $this->fetch();
    }

    /**
     * 佣金记录异步获取
     */
    public function get_commission_list()
    {
        $get = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['price_max', ''],
            ['price_min', ''],
            ['order', ''],
            ['excel', ''],
        ]);
        return Json::successlayui(User::getCommissionList($get));
    }

    /**
     * 显示操作记录
     */
    public function index3()
    {

    }

    /**
     * 佣金详情
     */
    public function content_info($uid = '')
    {
        if ($uid == '') return $this->failed('缺少参数');
        $this->assign('userinfo', User::getUserinfo($uid));
        $this->assign('uid', $uid);
        return $this->fetch();
    }

    /**
     * 佣金提现记录个人列表
     */
    public function get_extract_list($uid = '')
    {
        if ($uid == '') return Json::fail('缺少参数');
        $where = Util::getMore([
            ['page', 1],
            ['limit', 20],
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', '']
        ]);
        return Json::successlayui(UserBill::getExtrctOneList($where, $uid));
    }

}

