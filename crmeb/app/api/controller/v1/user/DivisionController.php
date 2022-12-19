<?php


namespace app\api\controller\v1\user;


use app\Request;
use app\services\agent\DivisionAgentApplyServices;
use app\services\other\AgreementServices;
use app\services\user\UserServices;
use crmeb\services\CacheService;

class DivisionController
{
    protected $services;

    /**
     * DivisionController constructor.
     * @param DivisionAgentApplyServices $services
     */
    public function __construct(DivisionAgentApplyServices $services)
    {
        $this->services = $services;
    }

    /**
     * 申请代理商
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function applyAgent(Request $request, $id)
    {
        $data = $request->postMore([
            ['uid', 0],
            ['agent_name', ''],
            ['name', ''],
            ['phone', 0],
            ['code', 0],
            ['division_invite', 0],
            ['images', []]
        ]);
        $verifyCode = CacheService::get('code_' . $data['phone']);
        if ($verifyCode != $data['code']) return app('json')->fail(410010);
        $this->services->applyAgent($data, $id);
        return app('json')->success(100017);
    }

    /**
     * 申请详情
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function applyInfo(Request $request)
    {
        $uid = $request->uid();
        $data = $this->services->applyInfo($uid);
        return app('json')->success($data);
    }

    /**
     * 移动端获取规则
     * @param AgreementServices $agreementServices
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAgentAgreement(AgreementServices $agreementServices)
    {
        $data = $agreementServices->getAgreementBytype(2);
        return app('json')->success($data);
    }

    /**
     * 员工列表
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStaffList(Request $request)
    {
        $where = $request->postMore([
            ['keyword', ''],
            ['sort', ''],
        ]);
        $where['agent_id'] = $request->uid();
        return app('json')->success($this->services->getStaffList($request->user(), $where));
    }

    /**
     * 设置员工比例
     * @param Request $request
     * @return mixed
     */
    public function setStaffPercent(Request $request)
    {
        [$agentPercent, $uid] = $request->postMore([
            ['agent_percent', ''],
            ['uid', 0],
        ], true);
        $agentId = $request->uid();
        if (!$uid) return app('json')->fail(100100);
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $upPercent = $userService->value(['uid' => $agentId], 'division_percent');
        if ($agentPercent >= $upPercent) return app('json')->fail(410164);
        $userService->update(['uid' => $uid, 'agent_id' => $agentId], ['division_percent' => $agentPercent]);
        return app('json')->success(100014);
    }

    /**
     * 删除员工
     * @param Request $request
     * @param $uid
     * @return mixed
     */
    public function delStaff(Request $request, $uid)
    {
        if (!$uid) return app('json')->fail(100100);
        $agentId = $request->uid();
        /** @var UserServices $userService */
        $userService = app()->make(UserServices::class);
        $userService->update(['uid' => $uid, 'agent_id' => $agentId], ['division_percent' => 0, 'agent_id' => 0, 'division_id' => 0, 'staff_id' => 0, 'division_type' => 0, 'is_staff' => 0]);
        return app('json')->success(100002);
    }
}
