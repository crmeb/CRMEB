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
namespace app\adminapi\controller\v1\agent;

use app\adminapi\controller\AuthController;
use app\services\agent\DivisionAgentApplyServices;
use app\services\agent\DivisionServices;
use app\services\other\AgreementServices;
use app\services\user\UserServices;
use crmeb\exceptions\AdminException;
use think\facade\App;

/**
 * 事业部控制器
 * Class Division
 * @package app\adminapi\controller\v1\agent
 */
class Division extends AuthController
{
    /**
     * Division constructor.
     * @param App $app
     * @param DivisionServices $services
     */
    public function __construct(App $app, DivisionServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 事业部列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function divisionList()
    {
        $where = $this->request->getMore([
            ['division_type', 0],
            ['keyword', '']
        ]);
        if ($where['division_type'] == 2) {
            $where['division_id'] = $this->adminInfo['division_id'];
        }
        $data = $this->services->getDivisionList($where);
        return app('json')->success($data);
    }

    /**
     * 下级列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function divisionDownList()
    {
        [$type, $uid] = $this->request->getMore([
            ['division_type', 0],
            ['uid', 0],
        ], true);
        $data = $this->services->divisionDownList($type, $uid);
        return app('json')->success($data);
    }

    /**
     * 添加编辑事业部
     * @param $uid
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function divisionCreate($uid)
    {
        return app('json')->success($this->services->getDivisionForm((int)$uid));
    }

    /**
     * 保存事业部
     * @return mixed
     */
    public function divisionSave()
    {
        $data = $this->request->postMore([
            ['uid', 0],
            ['aid', 0],
            ['division_percent', 0],
            ['division_end_time', ''],
            ['division_status', 1],
            ['account', ''],
            ['pwd', ''],
            ['conf_pwd', ''],
            ['real_name', ''],
            ['roles', []]
        ]);
        $this->services->divisionSave($data);
        return app('json')->success(100000);
    }

    /**
     * 添加编辑代理商
     * @param $uid
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function divisionAgentCreate($uid)
    {
        return app('json')->success($this->services->getDivisionAgentForm((int)$uid));
    }

    /**
     * 保存代理商
     * @param UserServices $userServices
     * @return mixed
     */
    public function divisionAgentSave(UserServices $userServices)
    {
        $data = $this->request->postMore([
            ['uid', 0],
            ['division_percent', 0],
            ['division_end_time', ''],
            ['division_status', 1],
            ['edit', 0],
        ]);
        $userInfo = $userServices->get((int)$data['uid']);
        if (!$userInfo) throw new AdminException(100100);
        $data['division_id'] = $this->adminInfo['division_id'];
        $this->services->divisionAgentSave($data);
        return app('json')->success(100000);
    }

    /**
     * 设置状态
     * @param $status
     * @param $uid
     * @return mixed
     */
    public function setDivisionStatus($status, $uid)
    {
        $this->services->setDivisionStatus($status, $uid);
        return app('json')->success(100014);
    }

    /**
     * 删除成功
     * @param $type
     * @param $uid
     * @return mixed
     */
    public function delDivision($type, $uid)
    {
        $this->services->delDivision($type, $uid);
        return app('json')->success(100002);
    }

    /**
     * 后台申请列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function AdminApplyList()
    {
        $where = $this->request->getMore([
            ['uid', 0],
            ['division_id', 0],
            ['division_invite', ''],
            ['status', ''],
            ['keyword', ''],
            ['time', ''],
        ]);
        $where['division_id'] = $this->adminInfo['division_id'];
        /** @var DivisionAgentApplyServices $applyServices */
        $applyServices = app()->make(DivisionAgentApplyServices::class);
        $data = $applyServices->AdminApplyList($where);
        return app('json')->success($data);
    }

    /**
     * 审核表单
     * @param $id
     * @param $type
     * @return mixed
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function examineApply($id, $type)
    {
        /** @var DivisionAgentApplyServices $applyServices */
        $applyServices = app()->make(DivisionAgentApplyServices::class);
        $data = $applyServices->examineApply($id, $type);
        return app('json')->success($data);
    }

    /**
     * 代理商审核
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function applyAgentSave()
    {
        $data = $this->request->getMore([
            ['type', 0],
            ['id', 0],
            ['division_percent', ''],
            ['division_end_time', ''],
            ['division_status', ''],
            ['refusal_reason', 0]
        ]);
        /** @var DivisionAgentApplyServices $applyServices */
        $applyServices = app()->make(DivisionAgentApplyServices::class);
        $data = $applyServices->applyAgentSave($data);
        return app('json')->success(100014);
    }

    /**
     * 删除代理商审核
     * @param $id
     * @return mixed
     */
    public function delApply($id)
    {
        /** @var DivisionAgentApplyServices $applyServices */
        $applyServices = app()->make(DivisionAgentApplyServices::class);
        $applyServices->delApply($id);
        return app('json')->success(100002);
    }
}
