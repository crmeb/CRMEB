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
use think\facade\Route;

/**
 * 每日签到路由
 */
Route::group('marketing', function () {

    /** 每日签到 */
    Route::group(function () {
        //签到奖励列表
        Route::get('sign/rewards', [\modules\marketing\signin\controller\admin\SignRewardsController::class, 'index'])->option(['real_name' => '签到奖励列表']);
        //添加签到奖励
        Route::get('sign/add_rewards', [\modules\marketing\signin\controller\admin\SignRewardsController::class, 'addRewards'])->option(['real_name' => '添加签到奖励']);
        //编辑签到奖励
        Route::get('sign/edit_rewards/:id', [\modules\marketing\signin\controller\admin\SignRewardsController::class, 'editRewards'])->option(['real_name' => '保存签到奖励']);
        //保存签到奖励
        Route::post('sign/save_rewards/:id', [\modules\marketing\signin\controller\admin\SignRewardsController::class, 'saveRewards'])->option(['real_name' => '保存签到奖励']);
        //删除签到奖励
        Route::delete('sign/del_rewards/:id', [\modules\marketing\signin\controller\admin\SignRewardsController::class, 'delRewards'])->option(['real_name' => '删除签到奖励']);
    })->option(['parent' => 'marketing', 'cate_name' => '每日签到']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'marketing', 'mark_name' => '营销活动']);
