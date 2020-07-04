<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\controller\system;

use app\admin\controller\AuthController;
use crmeb\services\{
    UtilService as Util,
    JsonService as Json
};
use app\admin\model\system\SystemVerifyOrder as VerifyOrderModel;
use app\admin\model\system\SystemStore as StoreModel;

/**
 * 核销订单管理控制器
 * Class SystemVerifyOrder
 * @package app\admin\controller\system
 */
class SystemVerifyOrder extends AuthController
{
    /**
     * @return mixed
     */
    public function index()
    {
        $this->assign([
            'year' => get_month(),
            'real_name' => $this->request->get('real_name', ''),
            'store_list' => StoreModel::dropList()
        ]);
        return $this->fetch();
    }

    /**
     * 获取头部订单金额等信息
     * return json
     */
    public function getBadge()
    {
        $where = Util::postMore([
            ['status', ''],
            ['real_name', ''],
            ['is_del', 0],
            ['data', ''],
            ['store_id', ''],
            ['order', '']
        ]);
        return Json::successful(VerifyOrderModel::getBadge($where));
    }

    /**
     * 获取订单列表
     * return json
     */
    public function order_list()
    {
        $where = Util::getMore([
            ['real_name', $this->request->param('real_name', '')],
            ['is_del', 0],
            ['data', ''],
            ['store_id', ''],
            ['page', 1],
            ['limit', 20],
        ]);
        return Json::successlayui(VerifyOrderModel::OrderList($where));
    }

    /**
     * 删除订单
     * */
    public function del_order()
    {
        $ids = Util::postMore(['ids'])['ids'];
        if (!count($ids)) return Json::fail('请选择需要删除的订单');
        if (VerifyOrderModel::where('is_del', 0)->where('id', 'in', $ids)->count())
            return Json::fail('您选择的的订单存在用户未删除的订单，无法删除用户未删除的订单');
        $res = VerifyOrderModel::where('id', 'in', $ids)->update(['is_system_del' => 1]);
        if ($res)
            return Json::successful('删除成功');
        else
            return Json::fail('删除失败');
    }

}
