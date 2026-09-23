<?php
use think\facade\Route;

Route::group('system', function () {
    Route::group(function () {
        Route::get('notice/list', [\app\adminapi\controller\message\NoticeController::class, 'index'])->option(['real_name' => '站内信列表']);
        Route::post('notice/create', [\app\adminapi\controller\message\NoticeController::class, 'create'])->option(['real_name' => '创建站内信']);
        Route::put('notice/:id', [\app\adminapi\controller\message\NoticeController::class, 'update'])->option(['real_name' => '更新站内信']);
        Route::delete('notice/:id', [\app\adminapi\controller\message\NoticeController::class, 'delete'])->option(['real_name' => '删除站内信']);
        Route::post('notice/publish/:id', [\app\adminapi\controller\message\NoticeController::class, 'publish'])->option(['real_name' => '发布站内信']);
        Route::post('notice/revoke/:id', [\app\adminapi\controller\message\NoticeController::class, 'revoke'])->option(['real_name' => '撤回站内信']);
        Route::get('notice/:id', [\app\adminapi\controller\message\NoticeController::class, 'detail'])->option(['real_name' => '站内信详情']);
        Route::post('notice/send-system', [\app\adminapi\controller\message\NoticeController::class, 'sendSystemNotice'])->option(['real_name' => '发送系统通知']);
    })->option(['parent' => 'system', 'cate_name' => '站内信管理']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'notice', 'mark_name' => '消息通知']);
