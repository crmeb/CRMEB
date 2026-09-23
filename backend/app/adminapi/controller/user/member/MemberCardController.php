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

namespace app\adminapi\controller\user\member;

use app\adminapi\controller\AuthController;
use app\services\user\member\MemberCardServices;
use app\services\user\member\MemberRightServices;
use app\services\user\member\MemberShipServices;
use think\facade\App;

/**
 * 会员卡控制器
 * 负责会员卡相关的后台管理操作，包括会员卡列表、会员类型管理、会员记录查询、会员权益管理等
 */
class MemberCardController extends AuthController
{
    /**
     * @var MemberCardServices
     */
    protected $services;

    /**
     * 初始化服务层实例
     * MemberCardController constructor.
     * @param App $app
     * @param MemberCardServices $memberCardServices
     */
    public function __construct(App $app, MemberCardServices $memberCardServices)
    {
        parent::__construct($app);
        $this->services = $memberCardServices;
    }

    /**
     * 获取会员卡列表
     * 根据批次ID查询会员卡列表，支持按卡号、手机号、使用状态筛选
     * @param int $card_batch_id 会员卡批次ID
     * @return mixed 返回分页后的会员卡列表数据
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
     * 获取会员类型列表
     * 查询所有会员类型数据，用于会员类型管理页面展示
     * @return mixed 返回会员类型列表数据
     */
    public function memberShip()
    {
        /** @var MemberShipServices $memberShipService */
        $memberShipService = app()->make(MemberShipServices::class);
        $data = $memberShipService->getSearchList();
        return app('json')->success($data);
    }

    /**
     * 保存会员类型
     * 添加或编辑会员类型信息，包括名称、价格、有效期等
     * @param int $id 会员类型ID，0表示新增
     * @param MemberShipServices $memberShipServices
     * @return mixed 返回操作结果
     */
    public function shipSave($id, MemberShipServices $memberShipServices)
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
        return app('json')->success($id ? '修改成功' : '添加成功');
    }

    /**
     * 删除会员类型
     * 根据ID删除指定的会员类型
     * @param int $id 会员类型ID
     * @param MemberShipServices $memberShipServices
     * @return mixed 返回删除结果
     */
    public function delete($id,MemberShipServices $memberShipServices)
    {
        if (!$id) return app('json')->fail('数据不存在');
        $res = $memberShipServices->delete((int)$id);
        return app('json')->success($res ? '删除成功' : '删除失败');
    }

    /**
     * 获取会员记录列表
     * 查询用户购买或领取会员卡的记录，支持按用户名、时间、会员类型、支付方式筛选
     * @return mixed 返回分页后的会员记录列表数据
     */
    public function memberRecord()
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
     * 获取会员权益列表
     * 查询所有会员权益配置信息
     * @return mixed 返回会员权益列表数据
     */
    public function memberRight()
    {
        /** @var MemberRightServices $memberRightService */
        $memberRightService = app()->make(MemberRightServices::class);
        $data = $memberRightService->getSearchList();
        return app('json')->success($data);
    }

    /**
     * 保存会员权益
     * 添加或编辑会员权益信息，包括权益名称、类型、描述等
     * @param int $id 权益ID，0表示新增
     * @param MemberRightServices $memberRightServices
     * @return mixed 返回操作结果
     */
    public function rightSave($id, MemberRightServices $memberRightServices)
    {
        $data = $this->request->postMore([
            ['id', 0],
            ['title', ''],
            ['show_title', ''],
            ['image', ''],
            ['right_type', ''],
            ['explain', ''],
            ['number', ''],
            ['sort', ''],
            ['status', ''],
        ]);
        $realId = (int)($data['id'] ?: $id);
        $memberRightServices->save($realId, $data);
        return app('json')->success('权益编辑成功');
    }

    /**
     * 修改会员卡激活/冻结状态
     * 根据卡号ID和目标状态更新会员卡状态
     * @return mixed 返回操作结果
     */
    public function setStatus()
    {
        [$card_id, $status] = $this->request->getMore([
            ['card_id', 0],
            ['status', 0],
        ], true);
        $res = $this->services->setStatus($card_id, $status);
        if ($res) return app('json')->success('操作成功');
        return app('json')->fail('操作失败');
    }

    /**
     * 修改付费会员类型启用/禁用状态
     * 根据会员类型ID和目标状态更新状态
     * @return mixed 返回操作结果
     */
    public function setShipStatus()
    {
        [$id, $is_del] = $this->request->getMore([
            ['id', 0],
            ['is_del', 0],
        ], true);
        /** @var MemberShipServices $memberShipService */
        $memberShipService = app()->make(MemberShipServices::class);
        $res = $memberShipService->setStatus($id, $is_del);
        if ($res) return app('json')->success('操作成功');
        return app('json')->success('操作失败');
    }
}