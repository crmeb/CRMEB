<?php
namespace app\api\controller\user;

use app\models\system\SystemUserLevel;
use app\models\system\SystemUserTask;
use app\models\user\UserLevel;
use app\Request;

/**
 * 会员等级类
 * Class UserLevelController
 * @package app\api\controller\user
 */
class UserLevelController
{

    /**
     * 检测用户是否可以成为会员
     * @param Request $request
     * @return mixed
     */
    public function detection(Request $request)
    {
        return app('json')->successful(UserLevel::setLevelComplete($request->uid()));
    }

    /**
     * 会员等级列表
     * @param Request $request
     * @return mixed
     */
    public function grade(Request $request)
    {
        return app('json')->successful(SystemUserLevel::getLevelList($request->uid()));
    }

    /**
     * 获取等级任务
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function task(Request $request, $id)
    {
        return app('json')->successful(SystemUserTask::getTashList($id,$request->uid()));
    }

}