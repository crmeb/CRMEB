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
use app\services\activity\seckill\StoreSeckillServices;
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
            [['store_name', 's'], '']
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
            [['time_id', 'd'], 0],
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
            $name = 'seckill_' . $unique . '_1';
            /** @var CacheService $cache */
            $cache = app()->make(CacheService::class);
            $cache->del($name);
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
        return app('json')->success(compact('list'));
    }

    /**
     * 秒杀统计
     * @return mixed
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
            ['keyword', '']
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
}
