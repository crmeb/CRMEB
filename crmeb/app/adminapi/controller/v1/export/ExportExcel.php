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
namespace app\adminapi\controller\v1\export;

use app\adminapi\controller\AuthController;
use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\combination\StoreCombinationServices;
use app\services\activity\combination\StorePinkServices;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\agent\AgentManageServices;
use app\services\other\export\ExportServices;
use app\services\order\StoreOrderServices;
use app\services\product\product\StoreProductServices;
use app\services\system\store\SystemStoreServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserBillServices;
use app\services\user\UserRechargeServices;
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

    public function userList()
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['status', ''],
            ['pay_count', ''],
            ['is_promoter', ''],
            ['order', ''],
            ['data', ''],
            ['user_type', ''],
            ['country', ''],
            ['province', ''],
            ['city', ''],
            ['user_time_type', ''],
            ['user_time', ''],
            ['sex', ''],
            [['level', 0], 0],
            [['group_id', 'd'], 0],
            ['label_id', ''],
            ['now_money', 'normal'],
            ['field_key', ''],
            ['isMember', '']
        ]);
        return app('json')->success($this->service->exportUserList($where));
    }

    /**
     * 订单导出
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderList()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['real_name', ''],
            ['is_del', ''],
            ['data', '', '', 'time'],
            ['type', ''],
            ['pay_type', ''],
            ['order', ''],
            ['field_key', ''],
        ]);
        $where['is_system_del'] = 0;
        $where['pid'] = 0;
        return app('json')->success($this->service->exportOrderList($where));
    }

    /**
     * 商品列表导出
     * @return mixed
     */
    public function productList()
    {
        $where = $this->request->getMore([
            ['store_name', ''],
            ['cate_id', ''],
            ['type', 1]
        ]);
        return app('json')->success($this->service->exportProductList($where));
    }

    /**
     * 砍价商品列表导出
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bargainList()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['store_name', ''],
        ]);
        $where['is_del'] = 0;
        return app('json')->success($this->service->exportBargainList($where));
    }

    /**
     * 拼团商品导出
     * @return mixed
     */
    public function combinationList()
    {
        $where = $this->request->getMore([
            ['is_show', ''],
            ['store_name', '']
        ]);
        $where['is_del'] = 0;
        return app('json')->success($this->service->exportCombinationList($where));
    }

    /**
     * 秒杀商品导出
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function seckillList()
    {
        $where = $this->request->getMore([
            [['status', 's'], ''],
            [['store_name', 's'], '']
        ]);
        return app('json')->success($this->service->exportSeckillList($where));
    }

    /**
     * 会员卡导出
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function memberCardList($id)
    {
        return app('json')->success($this->service->exportMemberCard($id));
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
     * 用户佣金
     * @param UserBillServices $services
     * @return mixed
     */
    public function userCommission(UserBillServices $services)
    {
        $where = $this->request->getMore([
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['price_max', ''],
            ['price_min', ''],
            ['excel', '1'],
        ]);
        $data = $services->getCommissionList($where, false);
        return app('json')->success($this->service->userCommission($data['list'] ?? []));
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
     * 用户充值
     * @param UserRechargeServices $services
     * @return mixed
     */
    public function userRecharge(UserRechargeServices $services)
    {
        $where = $this->request->getMore([
            ['data', ''],
            ['paid', ''],
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['excel', '1'],
        ]);
        $data = $services->getRechargeList($where, '*', false);
        return app('json')->success($this->service->userRecharge($data['list'] ?? []));
    }

    /**
     * 分销管理 用户推广
     * @param AgentManageServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userAgent(AgentManageServices $services)
    {
        $where = $this->request->getMore([
            ['nickname', ''],
            ['data', ''],
            ['excel', '1'],
        ]);
        $data = $services->agentSystemPage($where, false);
        return app('json')->success($this->service->userAgent($data['list']));
    }

    /**
     * 微信用户导出（弃用）
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
     * 商铺砍价活动导出
     * @param StoreBargainServices $services
     * @return mixed
     */
    public function storeBargain(StoreBargainServices $services)
    {
        $where = $this->request->getMore([
            ['start_status', ''],
            ['status', ''],
            ['store_name', ''],
        ]);
        $data = $services->getList($where);
        return app('json')->success($this->service->storeBargain($data));
    }

    /**
     * 拼团导出
     * @param StoreCombinationServices $services
     * @return mixed
     */
    public function storeCombination(StoreCombinationServices $services)
    {
        $where = $this->request->getMore([
            ['start_status', ''],
            ['is_show', ''],
            ['store_name', ''],
        ]);
        $data = $services->getList($where);
        /** @var StorePinkServices $storePinkServices */
        $storePinkServices = app()->make(StorePinkServices::class);
        $countAll = $storePinkServices->getPinkCount([]);
        $countTeam = $storePinkServices->getPinkCount(['k_id' => 0, 'status' => 2]);
        $countPeople = $storePinkServices->getPinkCount(['k_id' => 0]);
        foreach ($data as &$item) {
            $item['count_people'] = $countPeople[$item['id']] ?? 0;//拼团数量
            $item['count_people_all'] = $countAll[$item['id']] ?? 0;//参与人数
            $item['count_people_pink'] = $countTeam[$item['id']] ?? 0;//成团数量
            $item['stop_status'] = $item['stop_time'] < time() ? 1 : 0;
            if ($item['is_show']) {
                if ($item['start_time'] > time())
                    $item['start_name'] = '未开始';
                else if ($item['stop_time'] < time())
                    $item['start_name'] = '已结束';
                else if ($item['stop_time'] > time() && $item['start_time'] < time()) {
                    $item['start_name'] = '进行中';
                }
            } else $item['start_name'] = '已结束';
        }
        return app('json')->success($this->service->storeCombination($data));
    }

    /**
     * 秒杀导出
     * @param StoreSeckillServices $services
     * @return mixed
     */
    public function storeSeckill(StoreSeckillServices $services)
    {
        $where = $this->request->getMore([
            ['start_status', ''],
            ['status', ''],
            ['store_name', '']
        ]);
        $data = $services->getList($where);
        return app('json')->success($this->service->storeSeckill($data));
    }

    /**
     * 商品导出
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
        $where['pid'] = 0;
        $ids = $this->request->get('ids');
        if ($ids) {
            $idsArr = array_filter(explode(',', $ids));
            if ($idsArr) {
                $where['id'] = $idsArr;
            }
        }
        $data = $services->getExportList($where);
        return app('json')->success($this->service->storeOrder($data));
    }

    /**
     * 获取提货点
     * @param SystemStoreServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function storeMerchant(SystemStoreServices $services)
    {
        $where = $this->request->getMore([
            [['keywords', 's'], ''],
            [['type', 'd'], 0],
        ]);
        $data = $services->getExportData($where);
        return app('json')->success($this->service->storeMerchant($data));
    }

    /**
     * 会员卡导出
     * @param int $id
     * @param MemberCardServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function memberCard(int $id, MemberCardServices $services)
    {
        $data = $services->getExportData(['batch_card_id' => $id]);
        return app('json')->success($this->service->memberCard($data));
    }
}
