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

namespace app\adminapi\controller\finance;

use app\adminapi\controller\AuthController;
use app\services\user\UserRechargeServices;
use think\facade\App;

/**
 * 用户充值记录管理
 * 后台充值记录列表、退款、删除、统计，属于系统自带功能（不依赖充值插件）
 */
class UserRechargeController extends AuthController
{
    /**
     * @var UserRechargeServices
     */
    protected $services;

    /**
     * UserRechargeController constructor.
     * @param App $app
     * @param UserRechargeServices $services
     */
    public function __construct(App $app, UserRechargeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 充值记录列表
     * @return mixed
     */
    public function index()
    {
        $where['data'] = $this->request->param('data', '');
        $where['paid'] = $this->request->param('paid', '');
        $where['nickname'] = $this->request->param('nickname', '');
        return app('json')->success($this->services->getRechargeList($where));
    }

    /**
     * 获取用户充值统计数据
     * @return mixed
     */
    public function user_recharge()
    {
        $where['data'] = $this->request->param('data', '');
        $where['paid'] = $this->request->param('paid', '');
        $where['nickname'] = $this->request->param('nickname', '');
        return app('json')->success($this->services->user_recharge($where));
    }

    /**
     * 充值退款表单
     * @param int $id 充值记录ID
     * @return mixed
     */
    public function refund_edit($id)
    {
        return app('json')->success($this->services->refund_edit((int)$id));
    }

    /**
     * 执行充值退款
     * @param int $id 充值记录ID
     * @return mixed
     */
    public function refund_update($id)
    {
        $refund_price = $this->request->param('refund_price', 1);
        $this->services->refund_update((int)$id, (string)$refund_price);
        return app('json')->success('退款成功');
    }

    /**
     * 删除充值记录
     * @param int $id 充值记录ID
     * @return mixed
     */
    public function delete($id)
    {
        $this->services->delRecharge((int)$id);
        return app('json')->success('删除成功');
    }
}
