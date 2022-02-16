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
    //云存储列表
    Route::get('config/storage/save_type/:type', 'v1.setting.SystemStorage/uploadType')->name('SystemStorageUploadType')->option(['real_name' => '选择存储方式']);
    //云存储列表
    Route::get('config/storage', 'v1.setting.SystemStorage/index')->name('SystemStorageIndex')->option(['real_name' => '云存储列表']);
    //获取云存储创建表单
    Route::get('config/storage/create/:type', 'v1.setting.SystemStorage/create')->name('SystemStorageCreate')->option(['real_name' => '获取云存储创建表单']);
    //获取云存储配置表单
    Route::get('config/storage/form/:type', 'v1.setting.SystemStorage/getConfigForm')->name('getConfigForm')->option(['real_name' => '获取云存储配置表单']);
    //获取云存储配置
    Route::get('config/storage/config', 'v1.setting.SystemStorage/getConfig')->name('SystemStorageConfig')->option(['real_name' => '获取云存储配置']);
    //保存云存储配置
    Route::post('config/storage/config', 'v1.setting.SystemStorage/saveConfig')->name('SystemStorageSaveConfig')->option(['real_name' => '保存云存储配置']);
    //同步云存储列表
    Route::put('config/storage/synch/:type', 'v1.setting.SystemStorage/synch')->name('SystemStorageSynch')->option(['real_name' => '同步云存储列表']);
    //获取修改云存储域名表单
    Route::get('config/storage/domain/:id', 'v1.setting.SystemStorage/getUpdateDomainForm')->name('getUpdateDomainForm')->option(['real_name' => '获取修改云存储域名表单']);
    //修改云存储域名
    Route::post('config/storage/domain/:id', 'v1.setting.SystemStorage/updateDomain')->name('updateDomain')->option(['real_name' => '修改云存储域名']);
    //保存云存储数据
    Route::post('config/storage/:type', 'v1.setting.SystemStorage/save')->name('SystemStorageSave')->option(['real_name' => '保存云存储数据']);
    //删除云存储
    Route::delete('config/storage/:id', 'v1.setting.SystemStorage/delete')->name('SystemStorageDelete')->option(['real_name' => '删除云存储']);
    //修改云存储状态
    Route::put('config/storage/status/:id', 'v1.setting.SystemStorage/status')->name('SystemStorageStatus')->option(['real_name' => '修改云存储状态']);
    //系统日志
    Route::get('log', 'v1.system.SystemLog/index')->name('SystemLog')->option(['real_name' => '系统日志']);
    //系统日志管理员搜索条件
    Route::get('log/search_admin', 'v1.system.SystemLog/search_admin')->option(['real_name' => '系统日志管理员搜索条件']);
    //文件校验
    Route::get('file', 'v1.system.SystemFile/index')->name('SystemFile')->option(['real_name' => '文件校验']);
    //打开目录
//    Route::get('file/opendir', 'v1.system.SystemFile/opendir')->option(['real_name' => '打开目录']);
    //读取文件
//    Route::get('file/openfile', 'v1.system.SystemFile/openfile')->option(['real_name' => '读取文件']);
    //保存文件
//    Route::post('file/savefile', 'v1.system.SystemFile/savefile')->option(['real_name' => '保存文件']);
    //数据所有表
    Route::get('backup', 'v1.system.SystemDatabackup/index')->option(['real_name' => '数据库所有表']);
    //数据备份详情
    Route::get('backup/read', 'v1.system.SystemDatabackup/read')->option(['real_name' => '数据备份详情']);
    //数据备份 优化表
    Route::put('backup/optimize', 'v1.system.SystemDatabackup/optimize')->option(['real_name' => '数据备份优化表']);
    //数据备份 修复表
    Route::put('backup/repair', 'v1.system.SystemDatabackup/repair')->option(['real_name' => '数据备份修复表']);
    //数据备份 备份表
    Route::put('backup/backup', 'v1.system.SystemDatabackup/backup')->option(['real_name' => '数据备份备份表']);
    //备份记录
    Route::get('backup/file_list', 'v1.system.SystemDatabackup/fileList')->option(['real_name' => '数据库备份记录']);
    //删除备份记录
    Route::delete('backup/del_file', 'v1.system.SystemDatabackup/delFile')->option(['real_name' => '删除数据库备份记录']);
    //导入备份记录表
    Route::post('backup/import', 'v1.system.SystemDatabackup/import')->option(['real_name' => '导入数据库备份记录']);
    //下载备份记录表
//        Route::get('backup/download', 'v1.system.SystemDatabackup/downloadFile');
    //清除用户数据
    Route::get('clear/:type', 'v1.system.SystemClearData/index')->option(['real_name' => '清除用户数据']);
    //清除缓存
    Route::get('refresh_cache/cache', 'v1.system.Clear/refresh_cache')->option(['real_name' => '清除系统缓存']);
    //清除日志
    Route::get('refresh_cache/log', 'v1.system.Clear/delete_log')->option(['real_name' => '清除系统日志']);
    //域名替换接口
    Route::post('replace_site_url', 'v1.system.SystemClearData/replaceSiteUrl')->option(['real_name' => '域名替换']);
    //获取APP版本列表
    Route::get('version_list', 'v1.system.AppVersion/list')->option(['real_name' => '获取APP版本列表']);
    //添加版本信息
    Route::get('version_crate/:id', 'v1.system.AppVersion/crate')->option(['real_name' => '添加版本']);
    //添加版本信息
    Route::post('version_save', 'v1.system.AppVersion/save')->option(['real_name' => '添加版本']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
