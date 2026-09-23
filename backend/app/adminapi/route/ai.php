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
 * AI 对话 相关路由
 */
Route::group('ai', function () {

    Route::group(function () {
        //AI 流式对话（SSE）
        Route::post('chat_stream', [\app\adminapi\controller\ai\AiChatController::class, 'chatStream'])
            ->name('AiChatStream')->option(['real_name' => 'AI流式对话']);
        //AI 对话（非流式）
        Route::post('chat', [\app\adminapi\controller\ai\AiChatController::class, 'chat'])
            ->name('AiChat')->option(['real_name' => 'AI对话']);
    })->option(['parent' => 'ai', 'cate_name' => 'AI对话']);

    /** AI模型管理 */
    Route::group(function () {
        Route::get('model/list', [\app\adminapi\controller\ai\AiModelController::class, 'index'])->option(['real_name' => 'AI模型列表']);
        Route::get('model/options', [\app\adminapi\controller\ai\AiModelController::class, 'options'])->option(['real_name' => '可选AI模型清单']);
        Route::get('model/protocols', [\app\adminapi\controller\ai\AiModelController::class, 'protocols'])->option(['real_name' => 'AI协议清单']);
        Route::get('model/detail/:id', [\app\adminapi\controller\ai\AiModelController::class, 'read'])->option(['real_name' => 'AI模型详情']);
        Route::post('model/save', [\app\adminapi\controller\ai\AiModelController::class, 'save'])->option(['real_name' => '新增AI模型']);
        Route::put('model/update/:id', [\app\adminapi\controller\ai\AiModelController::class, 'update'])->option(['real_name' => '修改AI模型']);
        Route::delete('model/delete/:id', [\app\adminapi\controller\ai\AiModelController::class, 'delete'])->option(['real_name' => '删除AI模型']);
        Route::put('model/set_status/:id', [\app\adminapi\controller\ai\AiModelController::class, 'setStatus'])->option(['real_name' => '修改AI模型状态']);
        Route::put('model/set_default/:id', [\app\adminapi\controller\ai\AiModelController::class, 'setDefault'])->option(['real_name' => '设置默认AI模型']);
        Route::put('model/sort', [\app\adminapi\controller\ai\AiModelController::class, 'sort'])->option(['real_name' => 'AI模型排序']);
        Route::post('model/test', [\app\adminapi\controller\ai\AiModelController::class, 'test'])->option(['real_name' => 'AI模型连通性测试']);
    })->option(['parent' => 'ai', 'cate_name' => 'AI模型管理']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'ai', 'mark_name' => 'AI对话模块']);
