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

namespace app\adminapi\controller\v1\marketing\live;

use app\adminapi\controller\AuthController;
use app\services\activity\live\LiveGoodsServices;
use think\facade\App;

/**
 * 直播间商品
 * Class LiveGoods
 * @package app\controller\admin\store
 */
class LiveGoods extends AuthController
{
    /**
     * LiveGoods constructor.
     * @param App $app
     * @param LiveGoodsServices $services
     */
    public function __construct(App $app, LiveGoodsServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function list()
    {
        $where = $this->request->postMore([
            ['kerword', ''],
            ['status', ''],
            ['is_show', ''],
            ['live_id', 0]
        ]);
        return app('json')->success($this->services->getList($where));
    }

    public function create()
    {
        [$product_ids] = $this->request->postMore([
            ['product_id', []]
        ], true);
        if (!$product_ids) return app('json')->fail('请选择商品');
        return app('json')->success($this->services->create($product_ids));
    }

    public function add()
    {
        [$goods_info] = $this->request->postMore([
            ['goods_info', []]
        ], true);
        if (!$goods_info) return app('json')->fail('请选择商品');
        foreach ($goods_info as $goods) {
            if (!$goods['id']) return app('json')->fail('请选择商品');
            if (!$goods['store_name']) return app('json')->fail('请输入名称');
            if (!$goods['image']) return app('json')->fail('请选择背景图');
            if (!$goods['price']) return app('json')->fail('请输入直播价格');
            if ($goods['price'] <= 0) return app('json')->fail('直播价格必须大于0');
        }
        $this->services->add($goods_info);
        return app('json')->success('添加成功');
    }

    public function detail($id)
    {
        if (!$id)
            return app('json')->fail('数据不存在');
        $goods = $this->services->get($id, ['*'], ['product']);
        return app('json')->success('ok', $goods ? $goods->toArray() : []);
    }

    public function syncGoods()
    {
        $this->services->syncGoodStatus();
        return app('json')->success('同步成功');
    }

    public function audit($id)
    {
        if (!$id)
            return app('json')->fail('数据不存在');
        $this->services->audit((int)$id);
        return app('json')->success('提交审核成功');
    }

    public function resetAudit($id)
    {
        if (!$id)
            return app('json')->fail('数据不存在');
        $this->services->resetAudit((int)$id);
        return app('json')->success('撤销成功');
    }

    public function setShow(int $id, $is_show)
    {
        if (!$id)
            return app('json')->fail('数据不存在');
        return app('json')->success($this->services->isShow($id, $is_show));
    }

    public function delete($id)
    {
        if (!$id)
            return app('json')->fail('数据不存在');
        $this->services->delete($id);
        return app('json')->success('删除成功');
    }

}
