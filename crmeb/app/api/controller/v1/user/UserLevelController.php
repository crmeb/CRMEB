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
namespace app\api\controller\v1\user;

use app\model\system\SystemUserTask;
use app\Request;
use app\services\user\UserLevelServices;

/**
 * 会员等级类
 * Class UserLevelController
 * @package app\api\controller\user
 */
class UserLevelController
{
    protected $services = NUll;

    /**
     * UserLevelController constructor.
     * @param UserLevelServices $services
     */
    public function __construct(UserLevelServices $services)
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
     * 会员等级列表
     * @param Request $request
     * @return mixed
     */
    public function grade(Request $request)
    {
        return app('json')->success(['list'=>$this->services->grade((int)$request->uid()),'task'=>['list'=>[],'task'=>[]]]);
    }

    /**
     * 获取等级任务
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function task(Request $request, $id)
    {
        return app('json')->success((new SystemUserTask())->getTashList($id, $request->uid()));
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

    /**
     * 经验列表
     * @param Request $request
     * @return mixed
     */
    public function expList(Request $request)
    {
        return app('json')->success($this->services->expList((int)$request->uid()));
    }

}
