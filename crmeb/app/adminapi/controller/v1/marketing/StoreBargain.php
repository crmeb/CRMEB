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
namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\bargain\StoreBargainUserHelpServices;
use app\services\activity\bargain\StoreBargainUserServices;
use think\facade\App;

/**
 * 砍价管理
 * Class StoreBargain
 * @package app\adminapi\controller\v1\marketing
 */
class StoreBargain extends AuthController
{
    /**
     * StoreBargain constructor.
     * @param App $app
     * @param StoreBargainServices $services
     */
    public function __construct(App $app, StoreBargainServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 砍价列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['start_status', ''],
            ['status', ''],
            ['store_name', ''],
        ]);
        $where['is_del'] = 0;
        $list = $this->services->getStoreBargainList($where);
        return app('json')->success($list);
    }

    /**
     * 保存砍价商品
     * @param $id
     * @return mixed
     */
    public function save($id)
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['info', ''],
            ['unit_name', ''],
            ['section_time', []],
            ['images', []],
            ['bargain_max_price', 0],
            ['bargain_min_price', 0],
            ['sort', 0],
            ['give_integral', 0],
            ['is_hot', 0],
            ['status', 0],
            ['product_id', 0],
            ['description', ''],
            ['attrs', []],
            ['items', []],
            ['temp_id', 0],
            ['rule', ''],
            ['num', 1],
            ['copy', 0],
            ['bargain_num', 1],
            ['people_num', 1],
            ['logistics', []],//物流方式
            ['freight', 1],//运费设置
            ['postage', 0],//邮费
            ['custom_form', ''],
            ['virtual_type', 0],
        ]);
        $this->validate($data, \app\adminapi\validate\marketing\StoreBargainValidate::class, 'save');
        if ($data['section_time']) {
            [$start_time, $end_time] = $data['section_time'];
            if (strtotime($end_time) < time()) {
                return app('json')->fail(400507);
            }
        }
        $bragain = [];
        if ($id) {
            $bragain = $this->services->get((int)$id);
            if (!$bragain) {
                return app('json')->fail(100026);
            }
        }
        //限制编辑
        if ($data['copy'] == 0 && $bragain) {
            if ($bragain['stop_time'] < time()) {
                return app('json')->fail(400508);
            }
        }
        if ($data['copy'] == 1) {
            $id = 0;
            unset($data['copy']);
        }
        $this->services->saveData($id, $data);
        return app('json')->success(100000);
    }

    /**
     * 获取详情
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        $info = $this->services->getInfo($id);
        return app('json')->success(compact('info'));
    }

    /**
     * 删除砍价
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $this->services->update($id, ['is_del' => 1]);
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $bargainUserService->userBargainStatusFail($id, true);
        return app('json')->success(100002);
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function set_status($id, $status)
    {
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $bargainUserService->userBargainStatusFail($id, false);
        $this->services->update($id, ['status' => $status]);
        return app('json')->success($status == 0 ? 100001 : 100007);
    }

    /**
     * 砍价列表
     * @return mixed
     */
    public function bargainList()
    {
        $where = $this->request->getMore([
            ['status', ''],
            ['data', '', '', 'time'],
        ]);
        /** @var StoreBargainUserServices $bargainUserService */
        $bargainUserService = app()->make(StoreBargainUserServices::class);
        $list = $bargainUserService->bargainUserList($where);
        return app('json')->success($list);
    }

    /**
     * 砍价信息
     * @param $id
     * @return mixed
     */
    public function bargainListInfo($id)
    {
        /** @var StoreBargainUserHelpServices $bargainUserHelpService */
        $bargainUserHelpService = app()->make(StoreBargainUserHelpServices::class);
        $list = $bargainUserHelpService->getHelpList($id);
        return app('json')->success(compact('list'));
    }

    /**
     * 砍价统计
     * @param $id
     * @return mixed
     */
    public function bargainStatistics($id)
    {
        $data = $this->services->bargainStatistics($id);
        return app('json')->success($data);
    }

    /**
     * 砍价列表
     * @param $id
     * @return mixed
     */
    public function bargainStatisticsList($id)
    {
        $where = $this->request->getMore([
            ['real_name', ''],
        ]);
        $data = $this->services->bargainStatisticsList($id, $where);
        return app('json')->success($data);
    }

    /**
     * 砍价订单
     * @param $id
     * @return mixed
     */
    public function bargainStatisticsOrder($id)
    {
        $where = $this->request->getMore([
            ['real_name', ''],
            ['status', '']
        ]);
        return app('json')->success($this->services->bargainStatisticsOrder($id, $where));
    }
}
