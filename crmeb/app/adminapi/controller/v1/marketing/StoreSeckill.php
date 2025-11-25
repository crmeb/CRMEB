<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
namespace app\adminapi\controller\v1\marketing;

use app\adminapi\controller\AuthController;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\activity\StoreActivityServices;
use app\services\product\sku\StoreProductAttrValueServices;
use crmeb\services\CacheService;
use think\facade\App;

/**
 * 限时秒杀  控制器
 * Class StoreSeckill
 * @package app\admin\controller\store
 */
class StoreSeckill extends AuthController
{
    public function __construct(App $app, StoreSeckillServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 显示资源列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $where = $this->request->getMore([
            ['start_status', ''],
            [['status', 's'], ''],
            [['store_name', 's'], ''],
            [['product_id', 'd'], 0],
            ['activity_name', ''],
            ['time', ''],
            ['time_ids', []],
        ]);
        return app('json')->success($this->services->systemPage($where));
    }

    /**
     * 详情
     * @param $id
     * @return mixed
     */
    public function read($id)
    {
        $info = $this->services->getInfo($id);
        return app('json')->success(compact('info'));
    }

    /**
     * 保存秒杀商品
     * @param int $id
     */
    public function save($id)
    {
        $data = $this->request->postMore([
            [['product_id', 'd'], 0],
            [['title', 's'], ''],
            [['info', 's'], ''],
            [['unit_name', 's'], ''],
            ['images', []],
            [['give_integral', 'd'], 0],
            ['section_time', []],
            [['is_hot', 'd'], 0],
            [['status', 'd'], 0],
            [['num', 'd'], 0],
            [['once_num', 'd'], 0],
            ['time_id', []],
            [['temp_id', 'd'], 0],
            [['sort', 'd'], 0],
            [['description', 's'], ''],
            ['attrs', []],
            ['items', []],
            ['copy', 0],
            ['logistics', []],//物流方式
            ['freight', 1],//运费设置
            ['postage', 0],//邮费
            ['custom_form', ''],
            ['virtual_type', 0],
            ['is_commission', 0],
        ]);
        $this->validate($data, \app\adminapi\validate\marketing\StoreSeckillValidate::class, 'save');
        $this->services->saveData($id, $data);
        return app('json')->success(100000);
    }

    /**
     * 删除秒杀
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        if (!$id) return app('json')->fail(100100);
        $this->services->update($id, ['is_del' => 1]);
        /** @var StoreProductAttrValueServices $storeProductAttrValueServices */
        $storeProductAttrValueServices = app()->make(StoreProductAttrValueServices::class);
        $unique = $storeProductAttrValueServices->value(['product_id' => $id, 'type' => 1], 'unique');
        if ($unique) {
            CacheService::delete('seckill_' . $unique . '_1');
        }
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
        if ($status == 1) {
            $info = $this->services->get($id);
            if ($info['stop_time'] < time()) {
                return app('json')->fail('活动已结束，无法继续上架');
            }
        }
        $this->services->update($id, ['status' => $status]);
        return app('json')->success(100014);
    }

    /**
     * 秒杀时间段列表
     * @return mixed
     */
    public function time_list()
    {
        $list['data'] = sys_data('routine_seckill_time');
        foreach ($list['data'] as &$item) {
            $startTime = sprintf("%02d:00", $item['time']);
            $endTime = sprintf("%02d:00", $item['time'] + $item['continued']);
            $item['time_name'] = $startTime . '-' . $endTime;
        }
        return app('json')->success(compact('list'));
    }

    /**
     * 秒杀统计
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function seckillStatistics($id)
    {
        $data = $this->services->seckillStatistics($id);
        return app('json')->success($data);
    }

    /**
     * 秒杀参与人统计
     * @param $id
     * @return mixed
     */
    public function seckillPeople($id)
    {
        [$keyword] = $this->request->getMore([
            ['real_name', '', '', 'keyword']
        ], true);
        return app('json')->success($this->services->seckillPeople($id, $keyword));
    }

    /**
     * 秒杀订单统计
     * @param $id
     * @return mixed
     */
    public function seckillOrder($id)
    {
        $where = $this->request->getMore([
            ['real_name', ''],
            ['status', '']
        ]);
        return app('json')->success($this->services->seckillOrder($id, $where));
    }

    public function seckillActivityList()
    {
        $where = $this->request->getMore([
            ['time', ''],
            ['status', ''],
            ['title', ''],
            ['time_ids', []]
        ]);
        $where['is_del'] = 0;
        $where['type'] = 1;
        return app('json')->success(app()->make(StoreActivityServices::class)->activityList($where));
    }

    public function seckillActivityInfo($id)
    {
        return app('json')->success(app()->make(StoreActivityServices::class)->activityInfo($id));
    }

    public function seckillActivitySave($id)
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['section_time', []],
            ['time_ids', []],
            ['num', 0],
            ['once_num', 0],
            ['status', 1],
            ['is_commission', 0],
            ['product_infos', []]
        ]);
        $this->services->seckillActivitySave($id, $data);
        return app('json')->success('保存成功');
    }

    public function seckillActivityDel($id)
    {
        app()->make(StoreActivityServices::class)->activityDel($id, 1);
        return app('json')->success('删除成功');
    }

    public function seckillActivityStatus($id, $status)
    {
        app()->make(StoreActivityServices::class)->activityStatus($id, $status, 1);
        return app('json')->success('修改成功');
    }
}
