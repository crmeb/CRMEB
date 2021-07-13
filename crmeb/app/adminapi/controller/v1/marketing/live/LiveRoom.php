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
use app\services\live\LiveRoomServices;
use think\facade\App;

/**
 * 直播间
 * Class LiveRoom
 * @package app\adminapi\controller\v1\marketing\live
 */
class LiveRoom extends AuthController
{
    /**
     * LiveRoom constructor.
     * @param App $app
     * @param LiveRoomServices $services
     */
    public function __construct(App $app, LiveRoomServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    public function list()
    {
        $where = $this->request->postMore([
            ['kerword', ''],
            ['status', '']
        ]);
        return app('json')->success($this->services->getList($where));
    }

    public function detail($id)
    {
        if (!$id)
            return app('json')->fail('数据不存在');
        return app('json')->success('ok', $this->services->get((int)$id)->toArray());
    }

    public function add()
    {
        $data = $this->request->postMore([
            ['name', ''],
            ['cover_img', ''],
            ['share_img', ''],
            ['anchor_name', ''],
            ['anchor_wechat', ''],
            ['phone', ''],
            ['start_time', ['', '']],
            ['type', 1],
            ['screen_type', 1],
            ['close_like', 0],
            ['close_goods', 0],
            ['close_comment', 0],
            ['replay_status', 1],
            ['sort', 0]
        ]);
        if (!$data['name']) return app('json')->fail('请输入名称');
        if (!$data['cover_img']) return app('json')->fail('请选择背景图');
        if (!$data['share_img']) return app('json')->fail('请选择分享图');
        if (!$data['anchor_wechat']) return app('json')->fail('请选择主播');
        if (!$data['start_time'] || count($data['start_time']) != 2) return app('json')->fail('请选择直播开始、结束时间');
        if (!$data['phone'] || !check_phone($data['phone'])) return app('json')->fail('请输入正确手机号');
        [$data['start_time'], $data['end_time']] = $data['start_time'];
        $this->services->add($data);
        return app('json')->success('添加成功');
    }

    public function addGoods()
    {
        [$room_id, $goods_ids] = $this->request->postMore([
            ['room_id', 0],
            ['goods_ids', []]
        ], true);
        if (!$room_id) return app('json')->fail('请选择直播间');
        if (!$goods_ids) return app('json')->fail('请选择商品');
        $this->services->exportGoods((int)$room_id, $goods_ids);
        return app('json')->success('添加商品成功');
    }

    public function apply($id)
    {
        [$status, $msg] = $this->request->postMore([
            ['status', ''],
            ['msg', '']
        ], true);
        if (!$id)
            return app('json')->fail('数据不存在');
        $status = $status == 1 ? 1 : -1;
        if ($status == -1 && !$msg)
            return app('json')->fail('请输入理由');
        $this->services->apply((int)$id, $status, $msg);
        return app('json')->success('操作成功');
    }

    public function setShow($id, $is_show)
    {
        if (!$id)
            return app('json')->fail('数据不存在');
        return app('json')->success($this->services->isShow((int)$id, $is_show));
    }

    public function delete($id)
    {
        if (!$id)
            return app('json')->fail('数据不存在');
        $this->services->delete($id);
        return app('json')->success('删除成功');
    }

    public function syncRoom()
    {
        $this->services->syncRoomStatus();
        return app('json')->success('同步成功');
    }

}
