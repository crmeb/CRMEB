<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2020 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
use think\facade\Route;


/**
 * 维护 相关路由
 */
Route::group('system', function () {
    //系统日志
    Route::get('log', 'v1.system.SystemLog/index')->name('SystemLog');
    //系统日志管理员搜索条件
    Route::get('log/search_admin', 'v1.system.SystemLog/search_admin');
    //文件校验
    Route::get('file', 'v1.system.SystemFile/index')->name('SystemFile');
    //打开目录
    Route::get('file/opendir', 'v1.system.SystemFile/opendir');
    //读取文件
    Route::get('file/openfile', 'v1.system.SystemFile/openfile');
    //保存文件
    Route::post('file/savefile', 'v1.system.SystemFile/savefile');
    //数据所有表
    Route::get('backup', 'v1.system.SystemDatabackup/index');
    //数据备份详情
    Route::get('backup/read', 'v1.system.SystemDatabackup/read');
    //数据备份 优化表
    Route::put('backup/optimize', 'v1.system.SystemDatabackup/optimize');
    //数据备份 修复表
    Route::put('backup/repair', 'v1.system.SystemDatabackup/repair');
    //数据备份 备份表
    Route::put('backup/backup', 'v1.system.SystemDatabackup/backup');
    //备份记录
    Route::get('backup/file_list', 'v1.system.SystemDatabackup/fileList');
    //删除备份记录
    Route::delete('backup/del_file', 'v1.system.SystemDatabackup/delFile');
    //导入备份记录表
    Route::post('backup/import', 'v1.system.SystemDatabackup/import');
    //下载备份记录表
//        Route::get('backup/download', 'v1.system.SystemDatabackup/downloadFile');
    //清除用户数据
    Route::get('clear/:type', 'v1.system.SystemClearData/index');
    //清除缓存
    Route::get('refresh_cache/cache', 'v1.system.Clear/refresh_cache');
    //清除日志
    Route::get('refresh_cache/log', 'v1.system.Clear/delete_log');
    //域名替换接口
    Route::post('replace_site_url', 'v1.system.SystemClearData/replaceSiteUrl');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
