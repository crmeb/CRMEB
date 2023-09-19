<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
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

    /** 存储配置 */
    Route::group(function () {
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
    })->option(['parent' => 'system', 'cate_name' => '存储配置']);

    /** 系统日志 */
    Route::group(function () {
        //系统日志
        Route::get('log', 'v1.system.SystemLog/index')->name('SystemLog')->option(['real_name' => '系统日志']);
        //系统日志管理员搜索条件
        Route::get('log/search_admin', 'v1.system.SystemLog/search_admin')->option(['real_name' => '系统日志管理员搜索条件']);
        //文件校验
        Route::get('file', 'v1.system.SystemFile/index')->name('SystemFile')->option(['real_name' => '文件校验']);
    })->option(['parent' => 'system', 'cate_name' => '系统日志']);


    /** 数据备份 */
    Route::group(function () {
        //数据所有表
        Route::get('backup', 'v1.system.SystemDatabackup/index')->option(['real_name' => '数据库所有表']);
        //数据备份详情
        Route::get('backup/read', 'v1.system.SystemDatabackup/read')->option(['real_name' => '数据备份详情']);
        //更新数据表或者表字段备注
        Route::post('database/update_mark', 'v1.system.SystemDatabackup/updateMark')->option(['real_name' => '更新数据表或者表字段备注']);
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
        //Route::get('backup/download', 'v1.system.SystemDatabackup/downloadFile');
    })->option(['parent' => 'system', 'cate_name' => '数据备份']);

    /** 数据清除 */
    Route::group(function () {
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
    })->option(['parent' => 'system', 'cate_name' => '数据清除']);

    /** 在线升级 */
    Route::group(function () {
        //升级状态
        Route::get('upgrade_status', 'UpgradeController/upgradeStatus')->option(['real_name' => '升级状态']);
        //升级包列表
        Route::get('upgrade/list', 'UpgradeController/upgradeList')->option(['real_name' => '升级包列表']);
        //可升级包列表
        Route::get('upgradeable/list', 'UpgradeController/upgradeableList')->option(['real_name' => '可升级包列表']);
        //升级协议
        Route::get('upgrade/agreement', 'UpgradeController/agreement')->option(['real_name' => '升级协议']);
        //升级包下载
        Route::post('upgrade_download/:package_key', 'UpgradeController/download')->option(['real_name' => '升级包下载']);
        //升级进度
        Route::get('upgrade_progress', 'UpgradeController/progress')->option(['real_name' => '升级进度']);
        //升级记录
        Route::get('upgrade_log/list', 'UpgradeController/upgradeLogList')->option(['real_name' => '升级记录']);
        //导出备份项目
        Route::get('upgrade_export/:id/:type', 'UpgradeController/export')->option(['real_name' => '导出备份']);
        //文件管理登录
        Route::post('file/login', 'v1.system.SystemFile/login')->option(['real_name' => '文件管理登录']);
    })->option(['parent' => 'system', 'cate_name' => '在线升级']);

    /** 定时任务 */
    Route::group(function () {
        //定时任务列表
        Route::get('crontab/list', 'v1.system.SystemCrontab/getTimerList')->option(['real_name' => '定时任务列表']);
        //定时任务类型
        Route::get('crontab/mark', 'v1.system.SystemCrontab/getMarkList')->option(['real_name' => '定时任务类型']);
        //定时任务详情
        Route::get('crontab/info/:id', 'v1.system.SystemCrontab/getTimerInfo')->option(['real_name' => '定时任务详情']);
        //定时任务添加编辑
        Route::post('crontab/save', 'v1.system.SystemCrontab/saveTimer')->option(['real_name' => '定时任务添加编辑']);
        //删除定时任务
        Route::delete('crontab/del/:id', 'v1.system.SystemCrontab/delTimer')->option(['real_name' => '删除定时任务']);
        //定时任务是否开启开关
        Route::get('crontab/set_open/:id/:is_open', 'v1.system.SystemCrontab/setTimerStatus')->option(['real_name' => '定时任务是否开启开关']);
    })->option(['parent' => 'system', 'cate_name' => '定时任务']);

    /** 系统路由 */
    Route::group(function () {
        //同步路由接口
        Route::get('route/sync_route/[:appName]', 'v1.setting.SystemRoute/syncRoute')->option(['real_name' => '同步路由']);
        //获取路由tree行数据
        Route::get('route/tree', 'v1.setting.SystemRoute/tree')->option(['real_name' => '获取路由tree']);
        //权限路由
        Route::delete('route/:id', 'v1.setting.SystemRoute/delete')->option(['real_name' => '删除路由权限']);
        //查看路由权限
        Route::get('route/:id', 'v1.setting.SystemRoute/read')->option(['real_name' => '查看路由权限']);
        //保存路由权限
        Route::post('route/:id', 'v1.setting.SystemRoute/save')->option(['real_name' => '保存路由权限']);
        //路由分类
        Route::resource('route_cate', 'v1.setting.SystemRouteCate')->except(['read'])->option([
            'real_name' => [
                'index' => '获取路由分类列表',
                'create' => '获取创建路由分类表单',
                'save' => '保存路由分类',
                'edit' => '获取修改路由分类表单',
                'update' => '修改路由分类',
                'delete' => '删除路由分类'
            ],
        ]);
    })->option(['parent' => 'system', 'cate_name' => '系统路由']);

    /** 代码生成 */
    Route::group(function () {
        //保存CRUD修改的文件
        Route::post('crud/save_file/:id', 'v1.setting.SystemCrud/savefile')->option(['real_name' => '保存CRUD修改的文件']);
        //获取CRUD配置
        Route::get('crud/config/:tableName', 'v1.setting.SystemCrud/getRouteList')->option(['real_name' => '获取CRUD配置']);
        //下载生成的文件
        Route::get('crud/download/:id', 'v1.setting.SystemCrud/download')->option(['real_name' => '下载生成的文件']);
        //获取CRUD列表
        Route::get('crud/column_type', 'v1.setting.SystemCrud/columnType')->option(['real_name' => '获取CRUD列表']);
        //获取菜单TREE形数据
        Route::get('crud/menus', 'v1.setting.SystemCrud/getMenus')->option(['real_name' => '获取菜单TREE形数据']);
        //获取CRUD文件存放
        Route::post('crud/file_path', 'v1.setting.SystemCrud/getFilePath')->option(['real_name' => '获取CRUD文件存放']);
        //获取数据字典列表
        Route::get('crud/data_dictionary', 'v1.setting.SystemCrud/getDataDictionary')->option(['real_name' => '获取数据字典列表']);
        //查看数据字典
        Route::get('crud/data_dictionary/:id', 'v1.setting.SystemCrud/getDataDictionaryOne')->option(['real_name' => '查看数据字典']);
        //修改或者保存字典数据
        Route::post('crud/data_dictionary/[:id]', 'v1.setting.SystemCrud/saveDataDictionary')->option(['real_name' => '修改或者保存字典数据']);
        //删除数据字典
        Route::delete('crud/data_dictionary/:id', 'v1.setting.SystemCrud/deleteDataDictionary')->option(['real_name' => '删除数据字典']);
        //获取可以进行关联的表名
        Route::get('crud/association_table', 'v1.setting.SystemCrud/getAssociationTable')->option(['real_name' => '获取可以进行关联的表名']);
        //获取表的详细信息
        Route::get('crud/association_table/:tableName', 'v1.setting.SystemCrud/getAssociationTableInfo')->option(['real_name' => '获取表的详细信息']);
        //删除CRUD
        Route::delete('crud/:id', 'v1.setting.SystemCrud/delete')->option(['real_name' => '删除CRUD']);
        //查看CRUD
        Route::get('crud/:id', 'v1.setting.SystemCrud/read')->option(['real_name' => '查看CRUD']);
        //获取CRUD列表
        Route::get('crud', 'v1.setting.SystemCrud/index')->option(['real_name' => '获取CRUD列表']);
        //保存生成CRUD
        Route::post('crud', 'v1.setting.SystemCrud/save')->option(['real_name' => '保存生成CRUD']);
    })->option(['parent' => 'system', 'cate_name' => '代码生成']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'system', 'mark_name' => '系统维护']);

Route::group('system', function () {

    //打开目录
    Route::get('file/opendir', 'v1.system.SystemFile/opendir')->option(['real_name' => '打开目录']);
    //读取文件
    Route::get('file/openfile', 'v1.system.SystemFile/openfile')->option(['real_name' => '读取文件']);
    //保存文件
    Route::post('file/savefile', 'v1.system.SystemFile/savefile')->option(['real_name' => '保存文件']);
    //创建文件夹
    Route::get('file/createFolder', 'v1.system.SystemFile/createFolder')->option(['real_name' => '创建文件夹']);
    //创建文件
    Route::get('file/createFile', 'v1.system.SystemFile/createFile')->option(['real_name' => '创建文件']);
    //删除文件夹或者文件
    Route::get('file/delFolder', 'v1.system.SystemFile/delFolder')->option(['real_name' => '删除文件夹']);
    //重命名文件
    Route::get('file/rename', 'v1.system.SystemFile/rename')->option(['real_name' => '重命名文件夹']);
    //目录文件备注表单
    Route::get('file/mark', 'v1.system.SystemFile/fileMark')->option(['real_name' => '目录文件备注表单']);
    //目录文件备注保存
    Route::post('file/mark/save', 'v1.system.SystemFile/fileMarkSave')->option(['real_name' => '目录文件备注保存']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminEditorTokenMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'system_file', 'mark_name' => '文件管理']);

