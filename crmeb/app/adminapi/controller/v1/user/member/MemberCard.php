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

namespace app\adminapi\controller\v1\user\member;

use app\adminapi\controller\AuthController;
use app\services\user\member\MemberCardServices;
use app\services\user\member\MemberRightServices;
use app\services\user\member\MemberShipServices;
use think\facade\App;

/**
 * Class MemberCard
 * @package app\adminapi\controller\v1\user\member
 */
class MemberCard extends AuthController
{

    /**
     * 初始化service层句柄
     * MemberCard constructor.
     * @param App $app
     * @param MemberCardServices $memberCardServices
     */
    public function __construct(App $app, MemberCardServices $memberCardServices)
    {
        parent::__construct($app);
        $this->services = $memberCardServices;
    }

    /**
     * 会员卡列表
     * @param $card_batch_id
     * @return mixed
     */
    public function index($card_batch_id)
    {
        $where = $this->request->getMore([
            ['card_number', ""],
            ['phone', ""],
            ['card_batch_id', $card_batch_id],
            ['is_use', ""],
            ['is_status', ""],
            ['page', 1],
            ['limit', 20],
        ]);
        $data = $this->services->getSearchList($where);
        return app('json')->success($data);

    }

    /**
     * 会员分类
     * @return mixed
     */
    public function member_ship()
    {
        /** @var MemberShipServices $memberShipService */
        $memberShipService = app()->make(MemberShipServices::class);
        $data = $memberShipService->getSearchList();
        return app('json')->success($data);
    }

    /**
     * 保存分类
     * @param $id
     * @param MemberShipServices $memberShipServices
     * @return mixed
     */
    public function ship_save($id, MemberShipServices $memberShipServices)
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['price', ''],
            ['pre_price', ''],
            ['vip_day', ''],
            ['type', ''],
            ['sort', ''],
        ]);
        $memberShipServices->save((int)$id, $data);
        return app('json')->success($id ? "编辑成功" : '添加成功');
    }

    /**
     * 删除
     * @param $id
     * @param MemberShipServices $memberShipServices
     * @return mixed
     */
    public function delete($id,MemberShipServices $memberShipServices)
    {
        if (!$id) return app('json')->fail('数据不存在');
        $res = $memberShipServices->delete((int)$id);
        return app('json')->success($res ? "删除成功" : '删除失败');
    }

    /**
     * 获取会员记录
     * @return mixed
     */
    public function member_record()
    {
        $where = $this->request->getMore([
            ['name', ""],
            ['add_time', ""],
            ['member_type', ""],
            ['pay_type', ""],
            ['page', 1],
            ['limit', 20],
        ]);
        $data = $this->services->getSearchRecordList($where);
        return app('json')->success($data);
    }

    /**
     * 会员权益
     * @return mixed
     */
    public function member_right()
    {
        /** @var MemberRightServices $memberRightService */
        $memberRightService = app()->make(MemberRightServices::class);
        $data = $memberRightService->getSearchList();
        return app('json')->success($data);
    }

    /**
     * 保存会员权益
     * @param $id
     * @param MemberRightServices $memberRightServices
     * @return mixed
     */
    public function right_save($id, MemberRightServices $memberRightServices)
    {
        $data = $this->request->postMore([
            ['title', ''],
            ['show_title', ''],
            ['image', ''],
            ['right_type', ''],
            ['explain', ''],
            ['number', ''],
            ['sort', ''],
            ['status', ''],
        ]);
        $memberRightServices->save((int)$id, $data);
        return app('json')->success("权益编辑成功");
    }

    /**
     * 会员卡激活冻结状态修改
     * @return mixed
     */
    public function set_status()
    {
        [$card_id, $status] = $this->request->getMore([
            ['card_id', 0],
            ['status', 0],
        ], true);
        $msg = $status == 1 ? '激活' : '冻结';
        $res = $this->services->setStatus($card_id, $status);
        if ($res) return app('json')->success($msg . '成功');
        return app('json')->success($msg . '失败');
    }

    /**
     * 付费会员类型启用/禁用
     * @return mixed
     */
    public function set_ship_status()
    {
        [$id, $is_del] = $this->request->getMore([
            ['id', 0],
            ['is_del', 0],
        ], true);
        /** @var MemberShipServices $memberShipService */
        $memberShipService = app()->make(MemberShipServices::class);
        $msg = $is_del == 1 ? '禁用' : '启用';
        $res = $memberShipService->setStatus($id, $is_del);
        if ($res) return app('json')->success($msg . '成功');
        return app('json')->success($msg . '失败');
    }
}
