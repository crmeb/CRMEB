<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\export;

use app\adminapi\controller\AuthController;
use app\services\export\ExportServices;
use app\services\order\StoreOrderServices;
use app\services\product\product\StoreProductServices;
use app\services\user\UserBillServices;
use app\services\wechat\WechatUserServices;
use think\facade\App;

/**
 * 导出excel类
 * Class ExportExcel
 * @package app\adminapi\controller\v1\export
 */
class ExportExcel extends AuthController
{
    /**
     * @var ExportServices
     */
    protected $service;

    /**
     * ExportExcel constructor.
     * @param App $app
     * @param ExportServices $services
     */
    public function __construct(App $app, ExportServices $services)
    {
        parent::__construct($app);
        $this->service = $services;
    }

    /**
     * 保存用户资金监控的excel表格
     * @param UserBillServices $services
     * @return mixed
     */
    public function userFinance(UserBillServices $services)
    {
        $where = $this->request->getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
            ['type', ''],
        ]);
        $data = $services->getBillList($where, '*', false);
        return app('json')->success($this->service->userFinance($data['data'] ?? []));
    }


    /**
     * 用户积分
     * @param UserBillServices $services
     * @return mixed
     */
    public function userPoint(UserBillServices $services)
    {
        $where = $this->request->getMore([
            ['start_time', ''],
            ['end_time', ''],
            ['nickname', ''],
            ['excel', '1'],
        ]);
        $data = $services->getPointList($where, '*', false);
        return app('json')->success($this->service->userPoint($data['list'] ?? []));
    }

    /**
     * 微信用户导出
     * @param WechatUserServices $services
     * @return mixed
     */
    public function wechatUser(WechatUserServices $services)
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['data', ''],
            ['tagid_list', ''],
            ['groupid', '-1'],
            ['sex', ''],
            ['export', '1'],
            ['subscribe', '']
        ]);
        $tagidList = explode(',', $where['tagid_list']);
        foreach ($tagidList as $k => $v) {
            if (!$v) {
                unset($tagidList[$k]);
            }
        }
        $tagidList = array_unique($tagidList);
        $where['tagid_list'] = implode(',', $tagidList);
        $data = $services->exportData($where);
        return app('json')->success($this->service->wechatUser($data));
    }

    /**
     * 商铺产品导出
     * @param StoreProductServices $services
     * @return mixed
     */
    public function storeProduct(StoreProductServices $services)
    {
        $where = $this->request->getMore([
            ['store_name', ''],
            ['cate_id', ''],
            ['type', 1]
        ]);
        $data = $services->searchList($where, true, false);
        return app('json')->success($this->service->storeProduct($data['list'] ?? []));
    }

    /**
     * 订单列表导出
     * @param StoreOrderServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function storeOrder(StoreOrderServices $services)
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['real_name', ''],
            ['data', '', '', 'time']
        ]);
        $data = $services->getExportList($where);
        return app('json')->success($this->service->storeOrder($data));
    }
}
