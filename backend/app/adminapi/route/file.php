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
 * 附件相关路由
 */
Route::group('file', function () {
    //附件列表
    Route::get('file', [\app\adminapi\controller\file\SystemAttachmentController::class, 'index'])->option(['real_name' => '图片附件列表']);
    //删除图片和数据记录
    Route::post('file/delete', [\app\adminapi\controller\file\SystemAttachmentController::class, 'delete'])->option(['real_name' => '删除图片']);
    //移动图片分类表单 无控制器方法
    // Route::get('file/move', [\app\adminapi\controller\file\SystemAttachmentController::class, 'move'])->option(['real_name' => '移动图片分类表单']);
    //移动图片分类
    Route::put('file/do_move', [\app\adminapi\controller\file\SystemAttachmentController::class, 'moveImageCate'])->option(['real_name' => '移动图片分类']);
    //修改图片名称
    Route::put('file/update/:id', [\app\adminapi\controller\file\SystemAttachmentController::class, 'update'])->option(['real_name' => '修改图片名称']);
    //上传图片
    Route::post('upload/[:upload_type]', [\app\adminapi\controller\file\SystemAttachmentController::class, 'upload'])->option(['real_name' => '上传图片']);
    //附件分类管理资源路由
    Route::resource('category', \app\adminapi\controller\file\SystemAttachmentCategoryController::class)->except(['read'])->option([
        'real_name' => [
            'index' => '获取附件分类管理列表',
            'create' => '获取附件分类管理表单',
            'save' => '保存附件分类管理',
            'edit' => '获取修改附件分类管理表单',
            'update' => '修改附件分类管理',
            'delete' => '删除附件分类管理'
        ],

    ]);
    //获取上传类型
    Route::get('upload_type', [\app\adminapi\controller\file\SystemAttachmentController::class, 'uploadType'])->option(['real_name' => '上传类型']);
    //分片上传本地视频
    Route::post('video_upload', [\app\adminapi\controller\file\SystemAttachmentController::class, 'videoUpload'])->option(['real_name' => '分片上传本地视频']);
    //云存储视频保存数据
    Route::post('video_data_save', [\app\adminapi\controller\file\SystemAttachmentController::class, 'videoDataSave'])->option(['real_name' => '云存储视频保存数据']);
    //获取扫码上传页面链接以及参数
    Route::get('scan_upload/qrcode', [\app\adminapi\controller\file\SystemAttachmentController::class, 'scanUploadQrcode'])->option(['real_name' => '扫码上传页面链接']);
    //删除扫码上传token
    Route::delete('scan_upload/qrcode', [\app\adminapi\controller\file\SystemAttachmentController::class, 'removeUploadQrcode'])->option(['real_name' => '删除扫码上传页面链接']);
    //获取扫码上传的图片数据
    Route::get('scan_upload/image/:scan_token', [\app\adminapi\controller\file\SystemAttachmentController::class, 'scanUploadImage'])->option(['real_name' => '获取扫码上传的图片数据']);
    //网络图片上传
    Route::post('online_upload', [\app\adminapi\controller\file\SystemAttachmentController::class, 'onlineUpload'])->option(['real_name' => '网络图片上传']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'file', 'mark_name' => '素材管理']);
