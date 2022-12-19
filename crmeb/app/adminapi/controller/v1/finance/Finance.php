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
namespace app\adminapi\controller\v1\finance;

use app\services\user\UserBillServices;
use think\facade\App;
use app\adminapi\controller\AuthController;

/**
 * Class Finance
 * @package app\adminapi\controller\v1\finance
 */
class Finance extends AuthController
{
    /**
     * Finance constructor.
     * @param App $app
     * @param UserBillServices $services
     */
    public function __construct(App $app, UserBillServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 筛选类型
     */
    public function bill_type()
    {
        return app('json')->success($this->services->bill_type());
    }

    /**
     * 资金记录
     */
    public function list()
    {
        $where = $this->request->getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
            ['limit', 20],
            ['page', 1],
            ['type', ''],
        ]);
        return app('json')->success($this->services->getBillList($where));
    }

    /**
     * 佣金记录
     * @return mixed
     */
    public function get_commission_list()
    {
        $where = $this->request->getMore([
            ['nickname', ''],
            ['price_max', ''],
            ['price_min', ''],
            ['sum_number', 'normal'],
            ['brokerage_price', 'normal'],
            ['time', '']
        ]);
        return app('json')->success($this->services->getCommissionList($where));
    }

    /**
     * 佣金详情用户信息
     * @param $id
     * @return mixed
     */
    public function user_info($id)
    {
        return app('json')->success($this->services->user_info((int)$id));
    }

    /**
     * 佣金提现记录个人列表
     */
    public function get_extract_list($id = '')
    {
        if ($id == '') return app('json')->fail(100100);
        $where = $this->request->getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', '']
        ]);
        $where['category'] = 'now_money';
        $where['type'] = ['brokerage', 'brokerage_user'];
        return app('json')->success($this->services->getBillOneList((int)$id, $where));
    }

}
