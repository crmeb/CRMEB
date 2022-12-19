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
use app\services\activity\advance\StoreAdvanceServices;
use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\combination\StoreCombinationServices;
use app\services\activity\seckill\StoreSeckillServices;
use crmeb\exceptions\AdminException;
use think\facade\App;

/**
 * 预售控制器
 * Class StoreAdvance
 * @package app\adminapi\controller\v1\marketing
 */
class StoreAdvance extends AuthController
{
    /**
     * StoreAdvance constructor.
     * @param App $app
     * @param StoreAdvanceServices $services
     */
    public function __construct(App $app, StoreAdvanceServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 管理端预售列表
     * @return mixed
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['title', ''],
            ['type', ''],
            ['status', ''],
            ['time_type', 0]
        ]);
        return app('json')->success($this->services->getList($where));
    }

    /**
     * 添加/修改预售商品
     * @param $id
     * @return mixed
     */
    public function save($id)
    {
        $data = $this->request->postMore([
            [['product_id', 'd'], 0],
            [['title', 's'], ''],
            [['info', 's'], ''],
            [['unit_name', 's'], ''],
            ['image', ''],
            ['images', []],
            [['description', 's'], ''],
            [['temp_id', 'd'], 0],
            [['status', 'd'], 0],
            [['sort', 'd'], 0],
            [['num', 'd'], 0],
            [['once_num', 'd'], 1000],
            ['section_time', []],
            ['type', 0],
            ['deposit', 0],
            ['pay_time', []],
            ['attrs', []],
            ['items', []],
            ['deliver_time', 0],
            ['copy', 0]
        ]);
        if (!$id) {
            /** @var StoreSeckillServices $storeSeckillService */
            $storeSeckillService = app()->make(StoreSeckillServices::class);
            $res1 = $storeSeckillService->count(['product_id' => $data['product_id'], 'is_del' => 0, 'status' => 1, 'seckill_time' => 1]);
            if ($res1) {
                throw new AdminException(400506);
            }
            /** @var StoreBargainServices $storeBargainService */
            $storeBargainService = app()->make(StoreBargainServices::class);
            $res2 = $storeBargainService->count(['product_id' => $data['product_id'], 'is_del' => 0, 'status' => 1, 'bargain_time' => 1]);
            if ($res2) {
                throw new AdminException(400506);
            }
            /** @var StoreCombinationServices $storeCombinationService */
            $storeCombinationService = app()->make(StoreCombinationServices::class);
            $res3 = $storeCombinationService->count(['product_id' => $data['product_id'], 'is_del' => 0, 'is_show' => 1, 'pinkIngTime' => 1]);
            if ($res3) {
                throw new AdminException(400506);
            }
        }
        $this->services->saveData($id, $data);
        return app('json')->success(100000);
    }

    /**
     * 详情
     * @param $id
     * @return mixed
     */
    public function info($id)
    {
        $info = $this->services->getInfo($id);
        return app('json')->success(compact('info'));
    }

    /**
     * 删除预售
     * @param $id
     * @return mixed
     */
    public function del($id)
    {
        $res = $this->services->update($id, ['is_del' => 1]);
        if ($res) {
            return app('json')->success(100002);
        } else {
            return app('json')->fail(100008);
        }
    }

    /**
     * 预售商品上下架
     * @param $id
     * @param $status
     * @return mixed
     */
    public function setStatus($id, $status)
    {
        $res = $this->services->update($id, ['status' => $status]);
        if ($res) {
            return app('json')->success(100014);
        } else {
            return app('json')->fail(100015);
        }
    }
}
