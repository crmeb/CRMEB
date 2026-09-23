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

// 按诊断需求匿名开放，仅限制测试创建频率，不加载管理员身份中间件。
Route::group('system', function () {
    // 按诊断调用需求使用 GET，限流仅绑定创建接口。
    Route::get('queue_probe', [\app\adminapi\controller\system\QueueProbeController::class, 'create'])->middleware([\app\adminapi\middleware\QueueProbeThrottleMiddleware::class])->option(['real_name' => '创建队列诊断']);
    Route::get('queue_probe/:id', [\app\adminapi\controller\system\QueueProbeController::class, 'read'])->option(['real_name' => '查询队列诊断']);
});

/**
 * 维护 相关路由
 */
Route::group('system', function () {

    /** 存储配置 */
    Route::group(function () {
        //云存储列表
        Route::put('config/storage/save_type/:type', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'uploadType'])->name('UploadStorageUploadType')->option(['real_name' => '选择存储方式']);
        //云存储列表
        Route::get('config/storage', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'index'])->name('UploadStorageIndex')->option(['real_name' => '云存储列表']);
        //获取云存储创建表单
        Route::get('config/storage/create/:type', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'create'])->name('UploadStorageCreate')->option(['real_name' => '获取云存储创建表单']);
        //获取云存储配置表单
        Route::get('config/storage/form/:type', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'getConfigForm'])->name('getConfigForm')->option(['real_name' => '获取云存储配置表单']);
        //获取云存储配置
        Route::get('config/storage/config', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'getConfig'])->name('UploadStorageConfig')->option(['real_name' => '获取云存储配置']);
        //保存云存储配置
        Route::post('config/storage/config', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'saveConfig'])->name('UploadStorageSaveConfig')->option(['real_name' => '保存云存储配置']);
        //同步云存储列表
        Route::put('config/storage/synch/:type', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'synch'])->name('UploadStorageSynch')->option(['real_name' => '同步云存储列表']);
        //获取修改云存储域名表单
        Route::get('config/storage/domain/:id', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'getUpdateDomainForm'])->name('getUpdateDomainForm')->option(['real_name' => '获取修改云存储域名表单']);
        //修改云存储域名
        Route::post('config/storage/domain/:id', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'updateDomain'])->name('updateDomain')->option(['real_name' => '修改云存储域名']);
        //保存云存储数据
        Route::post('config/storage/:type', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'save'])->name('UploadStorageSave')->option(['real_name' => '保存云存储数据']);
        //删除云存储
        Route::delete('config/storage/:id', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'delete'])->name('UploadStorageDelete')->option(['real_name' => '删除云存储']);
        //修改云存储状态
        Route::put('config/storage/status/:id', [\app\adminapi\controller\driver\upload\UploadStorageController::class, 'status'])->name('UploadStorageStatus')->option(['real_name' => '修改云存储状态']);
    })->option(['parent' => 'system', 'cate_name' => '存储配置']);

    /** 系统日志 */
    Route::group(function () {
        //系统日志
        Route::get('log', [\app\adminapi\controller\system\SystemLogController::class, 'index'])->name('SystemLog')->option(['real_name' => '系统日志']);
        //系统日志管理员搜索条件
        Route::get('log/search_admin', [\app\adminapi\controller\system\SystemLogController::class, 'searchAdmin'])->option(['real_name' => '系统日志管理员搜索条件']);
    })->option(['parent' => 'system', 'cate_name' => '系统日志']);


    /** 数据备份 */
    Route::group(function () {
        //数据所有表
        Route::get('backup', [\app\adminapi\controller\system\SystemDatabackupController::class, 'index'])->option(['real_name' => '数据库所有表']);
        //数据备份详情
        Route::get('backup/read', [\app\adminapi\controller\system\SystemDatabackupController::class, 'read'])->option(['real_name' => '数据备份详情']);
        //更新数据表或者表字段备注
        Route::post('database/update_mark', [\app\adminapi\controller\system\SystemDatabackupController::class, 'updateMark'])->option(['real_name' => '更新数据表或者表字段备注']);
        //数据备份 优化表
        Route::put('backup/optimize', [\app\adminapi\controller\system\SystemDatabackupController::class, 'optimize'])->option(['real_name' => '数据备份优化表']);
        //数据备份 修复表
        Route::put('backup/repair', [\app\adminapi\controller\system\SystemDatabackupController::class, 'repair'])->option(['real_name' => '数据备份修复表']);
        //数据备份 备份表
        Route::put('backup/backup', [\app\adminapi\controller\system\SystemDatabackupController::class, 'backup'])->option(['real_name' => '数据备份备份表']);
        //备份记录
        Route::get('backup/file_list', [\app\adminapi\controller\system\SystemDatabackupController::class, 'fileList'])->option(['real_name' => '数据库备份记录']);
        //删除备份记录
        Route::delete('backup/del_file', [\app\adminapi\controller\system\SystemDatabackupController::class, 'delFile'])->option(['real_name' => '删除数据库备份记录']);
        //导入备份记录表
        Route::post('backup/import', [\app\adminapi\controller\system\SystemDatabackupController::class, 'import'])->option(['real_name' => '导入数据库备份记录']);
        //下载备份记录表
        //Route::get('backup/download', 'v1.system.SystemDatabackup/downloadFile');
    })->option(['parent' => 'system', 'cate_name' => '数据备份']);

    /** 数据清除 */
    Route::group(function () {
        //清除用户数据
        Route::get('clear/:type', [\app\adminapi\controller\system\SystemClearDataController::class, 'index'])->option(['real_name' => '清除用户数据']);
        //清除缓存
        Route::get('refresh_cache/cache', [\app\adminapi\controller\system\ClearController::class, 'refreshCache'])->option(['real_name' => '清除系统缓存']);
        Route::get('refresh_cache/log', [\app\adminapi\controller\system\ClearController::class, 'deleteLog'])->option(['real_name' => '清除系统日志']);
        //域名替换接口
        Route::post('replace_site_url', [\app\adminapi\controller\system\SystemClearDataController::class, 'replaceSiteUrl'])->option(['real_name' => '域名替换']);
        //获取APP版本列表
        Route::get('version_list', [\app\adminapi\controller\system\AppVersionController::class, 'list'])->option(['real_name' => '获取APP版本列表']);
        //添加版本信息
        Route::get('version_crate/:id', [\app\adminapi\controller\system\AppVersionController::class, 'crate'])->option(['real_name' => '添加版本']);
        //添加版本信息
        Route::post('version_save', [\app\adminapi\controller\system\AppVersionController::class, 'save'])->option(['real_name' => '保存版本']);
        //删除版本信息
        Route::delete('version_del/:id', [\app\adminapi\controller\system\AppVersionController::class, 'del'])->option(['real_name' => '删除版本']);
    })->option(['parent' => 'system', 'cate_name' => '数据清除']);

    /** 在线升级 */
    Route::group(function () {
        //升级状态
        Route::get('upgrade_status', [\app\adminapi\controller\UpgradeController::class, 'upgradeStatus'])->option(['real_name' => '升级状态']);
        //升级包列表
        Route::get('upgrade/list', [\app\adminapi\controller\UpgradeController::class, 'upgradeList'])->option(['real_name' => '升级包列表']);
        //可升级包列表
        Route::get('upgradeable/list', [\app\adminapi\controller\UpgradeController::class, 'upgradeableList'])->option(['real_name' => '可升级包列表']);
        //升级协议
        Route::get('upgrade/agreement', [\app\adminapi\controller\UpgradeController::class, 'agreement'])->option(['real_name' => '升级协议']);
        //升级记录
        Route::get('upgrade_log/list', [\app\adminapi\controller\UpgradeController::class, 'upgradeLogList'])->option(['real_name' => '升级记录']);
        //检测文件
        Route::get('upgrade/check_file', [\app\adminapi\controller\UpgradeController::class, 'checkFile'])->option(['real_name' => '检测文件']);
        //重新执行
        Route::get('upgrade/reExecute', [\app\adminapi\controller\UpgradeController::class, 'reExecute'])->option(['real_name' => '重新执行']);
        //升级包下载
        Route::post('package_download/:package_key', [\app\adminapi\controller\UpgradeController::class, 'packageDownload'])->option(['real_name' => '升级包下载']);
        //升级包下载进度
        Route::get('upgrade_download/progress', [\app\adminapi\controller\UpgradeController::class, 'downloadProgress'])->option(['real_name' => '升级包下载进度']);
        //升级进度
        Route::get('upgrade_progress', [\app\adminapi\controller\UpgradeController::class, 'progress'])->option(['real_name' => '升级进度']);
        //导出备份项目
        Route::get('upgrade_export/:id/:type', [\app\adminapi\controller\UpgradeController::class, 'export'])->option(['real_name' => '导出备份']);

        // 跨版本升级接口
        //获取跨版本升级概览
        Route::get('cross_version/overview', [\app\adminapi\controller\UpgradeController::class, 'crossVersionOverview'])->option(['real_name' => '跨版本升级概览']);
        //获取待升级版本列表
        Route::get('cross_version/pending', [\app\adminapi\controller\UpgradeController::class, 'pendingVersions'])->option(['real_name' => '待升级版本列表']);
        //获取待执行的升级SQL
        Route::get('cross_version/pending_sql', [\app\adminapi\controller\UpgradeController::class, 'pendingUpgradeSql'])->option(['real_name' => '待执行SQL列表']);
        //执行跨版本升级(单步)
        Route::post('cross_version/execute', [\app\adminapi\controller\UpgradeController::class, 'executeCrossVersionUpgrade'])->option(['real_name' => '执行跨版本升级']);
        //一键执行全部跨版本升级
        Route::post('cross_version/execute_all', [\app\adminapi\controller\UpgradeController::class, 'executeAllCrossVersionUpgrade'])->option(['real_name' => '一键升级']);
        //检查是否需要跨版本升级
        Route::get('cross_version/check', [\app\adminapi\controller\UpgradeController::class, 'checkCrossVersionUpgrade'])->option(['real_name' => '检查跨版本升级']);
        //获取备份状态
        Route::get('cross_version/backup_status', [\app\adminapi\controller\UpgradeController::class, 'backupStatus'])->option(['real_name' => '备份状态']);
        //获取升级进度
        Route::get('cross_version/progress', [\app\adminapi\controller\UpgradeController::class, 'upgradeProgress'])->option(['real_name' => '升级进度']);
        //获取可回退的版本列表
        Route::get('rollback/versions', [\app\adminapi\controller\UpgradeController::class, 'rollbackVersions'])->option(['real_name' => '可回退版本列表']);
        //执行版本回退
        Route::post('rollback/execute', [\app\adminapi\controller\UpgradeController::class, 'executeRollback'])->option(['real_name' => '执行版本回退']);
    })->option(['parent' => 'system', 'cate_name' => '在线升级']);

    /** 定时任务 */
    Route::group(function () {
        //定时任务列表
        Route::get('crontab/list', [\app\adminapi\controller\system\SystemCrontabController::class, 'getTimerList'])->option(['real_name' => '定时任务列表']);
        //定时任务类型
        Route::get('crontab/mark', [\app\adminapi\controller\system\SystemCrontabController::class, 'getMarkList'])->option(['real_name' => '定时任务类型']);
        //定时任务详情
        Route::get('crontab/info/:id', [\app\adminapi\controller\system\SystemCrontabController::class, 'getTimerInfo'])->option(['real_name' => '定时任务详情']);
        //定时任务添加编辑
        Route::post('crontab/save', [\app\adminapi\controller\system\SystemCrontabController::class, 'saveTimer'])->option(['real_name' => '定时任务添加编辑']);
        //删除定时任务
        Route::delete('crontab/del/:id', [\app\adminapi\controller\system\SystemCrontabController::class, 'delTimer'])->option(['real_name' => '删除定时任务']);
        //定时任务是否开启开关
        Route::get('crontab/set_open/:id/:is_open', [\app\adminapi\controller\system\SystemCrontabController::class, 'setTimerStatus'])->option(['real_name' => '定时任务是否开启开关']);
    })->option(['parent' => 'system', 'cate_name' => '定时任务']);

    /** 系统事件管理 */
    Route::group(function () {
        //事件列表
        Route::get('event/list', [\app\adminapi\controller\system\SystemEventController::class, 'index'])->option(['real_name' => '系统事件列表']);
        //事件分类
        Route::get('event/categories', [\app\adminapi\controller\system\SystemEventController::class, 'categories'])->option(['real_name' => '事件分类']);
        //事件详情
        Route::get('event/read/:name', [\app\adminapi\controller\system\SystemEventController::class, 'read'])->option(['real_name' => '事件详情']);
    })->option(['parent' => 'system', 'cate_name' => '系统事件']);

    /** 系统路由 */
    Route::group(function () {
        //同步路由接口
        Route::get('route/sync_route/[:appName]', [\app\adminapi\controller\setting\SystemRouteController::class, 'syncRoute'])->option(['real_name' => '同步路由']);
        //获取路由tree行数据
        Route::get('route/tree', [\app\adminapi\controller\setting\SystemRouteController::class, 'tree'])->option(['real_name' => '获取路由tree']);
        //权限路由
        Route::delete('route/:id', [\app\adminapi\controller\setting\SystemRouteController::class, 'delete'])->option(['real_name' => '删除路由权限']);
        //查看路由权限
        Route::get('route/:id', [\app\adminapi\controller\setting\SystemRouteController::class, 'read'])->option(['real_name' => '查看路由权限']);
        //保存路由权限
        Route::post('route/:id', [\app\adminapi\controller\setting\SystemRouteController::class, 'save'])->option(['real_name' => '保存路由权限']);
        //路由分类
        Route::resource('route_cate', \app\adminapi\controller\setting\SystemRouteCateController::class)->except(['read'])->option([
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
        Route::post('crud/save_file/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'savefile'])->option(['real_name' => '保存CRUD修改的文件']);
        //获取CRUD配置
        Route::get('crud/config/:tableName', [\app\adminapi\controller\setting\SystemCrudController::class, 'getRouteList'])->option(['real_name' => '获取CRUD配置']);
        //下载生成的文件
        Route::get('crud/download/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'download'])->option(['real_name' => '下载生成的文件']);
        //获取CRUD列表
        Route::get('crud/column_type', [\app\adminapi\controller\setting\SystemCrudController::class, 'columnType'])->option(['real_name' => '获取CRUD列表']);
        //获取菜单TREE形数据
        Route::get('crud/menus', [\app\adminapi\controller\setting\SystemCrudController::class, 'getMenus'])->option(['real_name' => '获取菜单TREE形数据']);
        //获取CRUD文件存放
        Route::post('crud/file_path', [\app\adminapi\controller\setting\SystemCrudController::class, 'getFilePath'])->option(['real_name' => '获取CRUD文件存放']);

        //获取数据字典列表
        Route::get('crud/data_dictionary_list', [\app\adminapi\controller\setting\SystemCrudController::class, 'dataDictionaryList'])->option(['real_name' => '获取数据字典列表']);
        //获取数据字典添加修改表单
        Route::get('crud/data_dictionary_list/create/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'dataDictionaryListCreate'])->option(['real_name' => '获取数据字典添加修改表单']);
        //保存数据字典
        Route::post('crud/data_dictionary_list/save/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'dataDictionaryListSave'])->option(['real_name' => '保存数据字典']);
        //删除数据字典
        Route::delete('crud/data_dictionary_list/del/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'dataDictionaryListDel'])->option(['real_name' => '删除数据字典']);
        //查看数据字典内容列表
        Route::get('crud/data_dictionary/info_list/:cid', [\app\adminapi\controller\setting\SystemCrudController::class, 'dataDictionaryInfoList'])->option(['real_name' => '查看数据字典内容列表']);
        //查看数据字典内容添加修改表单
        Route::get('crud/data_dictionary/info_create/:cid/:id/:pid', [\app\adminapi\controller\setting\SystemCrudController::class, 'dataDictionaryInfoCreate'])->option(['real_name' => '查看数据字典内容添加修改表单']);
        //修改或者保存字典数据内容
        Route::post('crud/data_dictionary/info_save/:cid/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'dataDictionaryInfoSave'])->option(['real_name' => '修改或者保存字典数据内容']);
        //删除数据字典内容
        Route::delete('crud/data_dictionary/info_del/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'dataDictionaryInfoDel'])->option(['real_name' => '删除数据字典内容']);

        //获取数据字典列表
        Route::get('crud/data_dictionary', [\app\adminapi\controller\setting\SystemCrudController::class, 'getDataDictionary'])->option(['real_name' => '获取数据字典列表']);
        //查看数据字典
        Route::get('crud/data_dictionary/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'getDataDictionaryOne'])->option(['real_name' => '查看数据字典']);
        //修改或者保存字典数据
        Route::post('crud/data_dictionary/[:id]', [\app\adminapi\controller\setting\SystemCrudController::class, 'saveDataDictionary'])->option(['real_name' => '修改或者保存字典数据']);
        //删除数据字典
        Route::delete('crud/data_dictionary/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'deleteDataDictionary'])->option(['real_name' => '删除数据字典']);
        //获取可以进行关联的表名
        Route::get('crud/association_table', [\app\adminapi\controller\setting\SystemCrudController::class, 'getAssociationTable'])->option(['real_name' => '获取可以进行关联的表名']);
        //获取表的详细信息
        Route::get('crud/association_table/:tableName', [\app\adminapi\controller\setting\SystemCrudController::class, 'getAssociationTableInfo'])->option(['real_name' => '获取表的详细信息']);
        //删除CRUD
        Route::delete('crud/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'delete'])->option(['real_name' => '删除CRUD']);
        //查看CRUD
        Route::get('crud/:id', [\app\adminapi\controller\setting\SystemCrudController::class, 'read'])->option(['real_name' => '查看CRUD']);
        //获取CRUD列表
        Route::get('crud', [\app\adminapi\controller\setting\SystemCrudController::class, 'index'])->option(['real_name' => '获取CRUD列表']);
        //保存生成CRUD
        Route::post('crud', [\app\adminapi\controller\setting\SystemCrudController::class, 'save'])->option(['real_name' => '保存生成CRUD']);
    })->option(['parent' => 'system', 'cate_name' => '代码生成']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'system', 'mark_name' => '系统维护']);
