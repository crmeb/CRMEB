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
namespace app\api\controller\v2\agent;


use app\Request;
use app\services\agent\AgentLevelServices;
use app\services\agent\AgentLevelTaskServices;

/**
 * Class AgentLevel
 * @package app\controller\api\v2\agent
 */
class AgentLevel
{
    protected $services;

    public function __construct(AgentLevelServices $services)
    {
        $this->services = $services;
    }

    /**
     * 检测用户是否可以成为会员
     * @param Request $request
     * @return mixed
     */
    public function detection(Request $request)
    {
        return app('json')->success($this->services->detection((int)$request->uid()));
    }

    /**
     * 分销员等级列表
     * @param Request $request
     * @return mixed
     */
    public function levelList(Request $request)
    {
        return app('json')->success($this->services->getUserlevelList((int)$request->uid()));
    }

    /**
     * 获取等级任务
     * @param Request $request
     * @param AgentLevelTaskServices $services
     * @param $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function levelTaskList(Request $request, AgentLevelTaskServices $services, $id)
    {
        return app('json')->success($services->getUserLevelTaskList((int)$request->uid(), (int)$id));
    }

    /**
     * 会员详情
     * @param Request $request
     * @return mixed
     */
    public function userLevelInfo(Request $request)
    {
        return app('json')->success($this->services->getUserLevelInfo((int)$request->uid()));
    }

}
