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

use app\adminapi\controller\AuthController;
use app\services\user\UserRechargeServices;
use think\facade\App;

/**
 * Class UserRecharge
 * @package app\adminapi\controller\v1\finance
 */
class UserRecharge extends AuthController
{
    /**
     * UserRecharge constructor.
     * @param App $app
     * @param UserRechargeServices $services
     */
    public function __construct(App $app, UserRechargeServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     * @return \think\Response
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['data', ''],
            ['paid', ''],
            ['nickname', ''],
        ]);
        return app('json')->success($this->services->getRechargeList($where));
    }

    /**
     * 删除指定资源
     * @param int $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail(100100);
        return app('json')->success($this->services->delRecharge((int)$id) ? 100002 : 100008);
    }

    /**
     * 获取用户充值数据
     * @return array
     */
    public function user_recharge()
    {
        $where = $this->request->getMore([
            ['data', ''],
            ['paid', ''],
            ['nickname', ''],
        ]);
        return app('json')->success($this->services->user_recharge($where));
    }

    /**
     * 退款表单
     * @param $id
     * @return mixed|void
     */
    public function refund_edit($id)
    {
        if (!$id) return app('json')->fail(100026);
        return app('json')->success($this->services->refund_edit((int)$id));
    }

    /**
     * 退款操作
     * @param $id
     * @return mixed
     */
    public function refund_update($id)
    {
        $data = $this->request->postMore([
            'refund_price',
        ]);
        if (!$id) return app('json')->fail(100026);
        return app('json')->success($this->services->refund_update((int)$id, $data['refund_price']) ? 100036 : 100037);
    }
}
