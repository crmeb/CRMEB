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
namespace app\adminapi\controller\export;

use app\adminapi\controller\AuthController;
use app\services\other\export\ExportServices;
use app\services\order\OrderCenterServices;
use app\services\product\CenterProductServices;
use app\services\system\store\SystemStoreServices;
use app\services\user\member\MemberCardServices;
use app\services\user\UserBillServices;
use app\services\user\UserRechargeServices;
use think\facade\App;

/**
 * 导出excel类
 * Class ExportExcel
 * @package app\adminapi\controller\export
 */
class ExportExcelController extends AuthController
{
    /**
     * 通过事件代理查询活动数据，避免 app 层直接引用 modules
     * @param string $mode 活动类型（seckill/combination/bargain）
     * @param string $action 查询方法名
     * @param array $where 查询条件
     * @param string $subService 子服务标识（如拼团统计用 pink）
     * @return array 查询结果
     */
    private function proxyActivityData(string $mode, string $action, array $where = [], string $subService = ''): array
    {
        $payload = ['mode' => $mode, 'action' => $action, 'where' => $where];
        if ($subService) {
            $payload['sub_service'] = $subService;
        }
        $results = event('ActivityDataProxy', $payload);
        if (is_array($results)) {
            foreach ($results as $result) {
                if ($result !== null) {
                    return is_array($result) ? $result : [];
                }
            }
        }
        return [];
    }

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
            ['isMember', ''],
            ['ids', []]
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
            ['ids', []],
        ]);
        $where['is_system_del'] = 0;
        $where['pid'] = 0;
        return app('json')->success($this->service->exportOrderList($where));
    }

    /**
     * 发货订单列表导出
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderDeliveryList()
    {
        return app('json')->success($this->service->exportOrderDeliveryList());
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
            ['type', 1],
            ['ids', []]
        ]);
        // 商品数据导出入口已迁移到实物商品管理，固定只导出实物商品。
        $where['product_type'] = 'normal';
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
     * @return mixed
     */
    public function userRecharge()
    {
        $where = $this->request->getMore([
            ['data', ''],
            ['paid', ''],
            ['page', 1],
            ['limit', 20],
            ['nickname', ''],
            ['excel', '1'],
        ]);
        // 直接调用充值服务查询列表，避免依赖充值插件
        /** @var UserRechargeServices $userRechargeServices */
        $userRechargeServices = app()->make(UserRechargeServices::class);
        $data = $userRechargeServices->getRechargeList($where, '*', false);
        return app('json')->success($this->service->userRecharge($data['list'] ?? []));
    }

    /**
     * 分销管理 用户推广
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userAgent()
    {
        try {
            $where = $this->request->getMore([
                ['nickname', ''],
                ['data', ''],
                ['excel', '1'],
            ]);
            //通过事件代理查询分销用户列表，避免 app 层直接引用 modules
            $_agentResults = event('DistributionServiceProxy', ['service' => 'DistributionManageServices', 'action' => 'distributionSystemPage', 'args' => [$where, false]]);
            $data = (is_array($_agentResults) && $_agentResults[0] !== null) ? $_agentResults[0] : ['list' => []];
            return app('json')->success($this->service->userAgent($data['list']));
        } catch (\Throwable $e) {
            return app('json')->fail('分销模块未安装');
        }
    }

    /**
     * 微信用户导出（弃用）
     * @return mixed
     */
    public function wechatUser()
    {
        //该方法已弃用，微信公众号用户导出功能由 channel/wechat_mp 模块自行提供
        return app('json')->success($this->service->wechatUser([]));
    }

    /**
     * 商铺砍价活动导出
     * @return mixed
     */
    public function storeBargain()
    {
        $where = $this->request->getMore([
            ['start_status', ''],
            ['status', ''],
            ['store_name', ''],
        ]);
        //通过事件代理查询砍价列表，避免 app 层直接引用 modules
        $data = $this->proxyActivityData('bargain', 'getList', $where);
        return app('json')->success($this->service->storeBargain($data));
    }

    /**
     * 拼团导出
     * @return mixed
     */
    public function storeCombination()
    {
        $where = $this->request->getMore([
            ['start_status', ''],
            ['is_show', ''],
            ['store_name', ''],
        ]);
        //通过事件代理查询拼团列表，避免 app 层直接引用 modules
        $data = $this->proxyActivityData('combination', 'getList', $where);
        //通过事件代理查询拼团统计（StorePinkServices），避免 app 层直接引用 modules
        $countAll = $this->proxyActivityData('combination', 'getPinkCount', [], 'pink');
        $countTeam = $this->proxyActivityData('combination', 'getPinkCount', ['k_id' => 0, 'status' => 2], 'pink');
        $countPeople = $this->proxyActivityData('combination', 'getPinkCount', ['k_id' => 0], 'pink');
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
     * @return mixed
     */
    public function storeSeckill()
    {
        $where = $this->request->getMore([
            ['start_status', ''],
            ['status', ''],
            ['store_name', '']
        ]);
        //通过事件代理查询秒杀列表，避免 app 层直接引用 modules
        $data = $this->proxyActivityData('seckill', 'getList', $where);
        return app('json')->success($this->service->storeSeckill($data));
    }

    /**
     * 商品导出
     * @param CenterProductServices $services
     * @return mixed
     */
    public function storeProduct(CenterProductServices $services)
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
     * @param OrderCenterServices $services
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function storeOrder(OrderCenterServices $services)
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
     * 获取门店
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
