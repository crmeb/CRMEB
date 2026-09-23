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
 * 系统设置维护 系统权限管理、系统菜单管理 系统配置 相关路由
 */
Route::group('setting', function () {

    /** 管理员 */
    Route::group(function () {
        //管理员资源路由
        Route::resource('admin', \app\adminapi\controller\setting\SystemAdminController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取管理员列表',
                'create' => '获取管理员表单',
                'save' => '保存管理员',
                'edit' => '获取修改管理员表单',
                'update' => '修改管理员',
                'delete' => '删除管理员'
            ]
        ]);
        //退出登陆
        Route::get('admin/logout', [\app\adminapi\controller\setting\SystemAdminController::class, 'logout'])->name('SystemAdminLogout')->option(['real_name' => '退出登陆']);
        //修改状态
        Route::put('set_status/:id/:status', [\app\adminapi\controller\setting\SystemAdminController::class, 'setStatus'])->name('SystemAdminSetStatus')->option(['real_name' => '修改管理员状态']);
        //获取当前管理员信息
        Route::get('info', [\app\adminapi\controller\setting\SystemAdminController::class, 'info'])->name('SystemAdminInfo')->option(['real_name' => '获取当前管理员信息']);
        //修改当前管理员信息
        Route::put('update_admin', [\app\adminapi\controller\setting\SystemAdminController::class, 'updateAdmin'])->name('SystemAdminUpdateAdmin')->option(['real_name' => '修改当前管理员信息']);
        //设置文件管理密码
        Route::put('set_file_password', [\app\adminapi\controller\setting\SystemAdminController::class, 'setFilePassword'])->name('SystemAdminSetFilePassword')->option(['real_name' => '设置当前文件管理密码']);
    })->option(['parent' => 'setting', 'cate_name' => '管理员']);

    /** 权限菜单 */
    Route::group(function () {
        //获取菜单权限和权限标识
        Route::get('menus/unique', [\app\adminapi\controller\setting\SystemMenusController::class, 'unique'])->name('SystemMenusUnique')->option(['real_name' => '获取菜单权限和权限标识']);
        //批量保存权限
        Route::post('menus/batch', [\app\adminapi\controller\setting\SystemMenusController::class, 'batchSave'])->name('SystemMenusBatchSave')->option(['real_name' => '批量保存权限']);
        //权限菜单资源路由
        Route::resource('menus', \app\adminapi\controller\setting\SystemMenusController::class)->option([
            'real_name' => [
                'index' => '获取权限菜单列表',
                'create' => '获取权限菜单表单',
                'save' => '保存权限菜单',
                'edit' => '获取修改权限菜单表单',
                'read' => '查看权限菜单信息',
                'update' => '修改权限菜单',
                'delete' => '删除权限菜单'
            ],
        ]);
        //未添加的权限规则列表
        Route::get('ruleList', [\app\adminapi\controller\setting\SystemMenusController::class, 'ruleList'])->option(['real_name' => '权限规则列表']);
        //权限规则分类
        Route::get('rule_cate', [\app\adminapi\controller\setting\SystemMenusController::class, 'ruleCate'])->option(['real_name' => '权限规则分类']);
        //修改显示
        Route::put('menus/show/:id', [\app\adminapi\controller\setting\SystemMenusController::class, 'show'])->name('SystemMenusShow')->option(['real_name' => '修改权限规格显示状态']);
    })->option(['parent' => 'setting', 'cate_name' => '权限菜单']);

    /** 管理员身份 */
    Route::group(function () {
        //身份列表
        Route::get('role', [\app\adminapi\controller\setting\SystemRoleController::class, 'index'])->option(['real_name' => '管理员身份列表']);
        //身份权限列表
        Route::get('role/create', [\app\adminapi\controller\setting\SystemRoleController::class, 'create'])->option(['real_name' => '管理员身份权限列表']);
        //编辑详情
        Route::get('role/:id/edit', [\app\adminapi\controller\setting\SystemRoleController::class, 'edit'])->option(['real_name' => '编辑管理员详情']);
        //保存新建或编辑
        Route::post('role/:id', [\app\adminapi\controller\setting\SystemRoleController::class, 'save'])->option(['real_name' => '新建或编辑管理员']);
        //修改身份状态
        Route::put('role/set_status/:id/:status', [\app\adminapi\controller\setting\SystemRoleController::class, 'setStatus'])->option(['real_name' => '修改管理员身份状态']);
        //删除身份
        Route::delete('role/:id', [\app\adminapi\controller\setting\SystemRoleController::class, 'delete'])->option(['real_name' => '删除管理员身份']);
    })->option(['parent' => 'setting', 'cate_name' => '管理员身份']);

    /** 系统配置 */
    Route::group(function () {
        //配置分类资源路由
        Route::resource('config_class', \app\adminapi\controller\setting\SystemConfigTabController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取系统配置分类列表',
                'create' => '获取系统配置分类表单',
                'save' => '保存系统配置分类',
                'edit' => '获取修改系统配置分类表单',
                'update' => '修改系统配置分类',
                'delete' => '删除系统配置分类'
            ],
        ]);
        //修改配置分类状态
        Route::put('config_class/set_status/:id/:status', [\app\adminapi\controller\setting\SystemConfigTabController::class, 'setStatus'])->option(['real_name' => '修改配置分类状态']);
        //配置资源路由
        Route::resource('config', \app\adminapi\controller\setting\SystemConfigController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取系统配置列表',
                'create' => '获取系统配置表单',
                'save' => '保存系统配置',
                'edit' => '获取修改系统配置表单',
                'update' => '修改系统配置',
                'delete' => '删除系统配置'
            ]
        ]);
        //修改配置状态
        Route::put('config/set_status/:id/:status', [\app\adminapi\controller\setting\SystemConfigController::class, 'setStatus'])->option(['real_name' => '修改配置状态']);
        //基本配置编辑表单
        Route::get('config/header_basics', [\app\adminapi\controller\setting\SystemConfigController::class, 'headerBasics'])->option(['real_name' => '基本配置编辑头部数据']);
        //基本配置编辑表单
        Route::get('config/edit_basics', [\app\adminapi\controller\setting\SystemConfigController::class, 'editBasics'])->option(['real_name' => '基本配置编辑表单']);
        //基本配置保存数据
        Route::post('config/save_basics', [\app\adminapi\controller\setting\SystemConfigController::class, 'saveBasics'])->option(['real_name' => '基本配置保存数据']);
        //基本配置上传文件 无控制器方法
        // Route::post('config/upload', [\app\adminapi\controller\setting\SystemConfigController::class, 'fileUpload'])->option(['real_name' => '基本配置上传文件']);
        //获取单个配置值
        Route::get('config/get_system/:name', [\app\adminapi\controller\setting\SystemConfigController::class, 'getSystem'])->option(['real_name' => '基本配置编辑表单']);
        //获取某个分类下的所有配置信息
        Route::get('config_list/:tabId', [\app\adminapi\controller\setting\SystemConfigController::class, 'getConfigList'])->option(['real_name' => '获取某个分类下的所有配置信息']);
        //获取电子发票配置页全部配置
        Route::get('config/elec_invoice', [\app\adminapi\controller\setting\SystemConfigController::class, 'elecInvoice'])->option(['real_name' => '获取电子发票配置']);
        //保存电子发票配置页全部配置
        Route::post('config/elec_invoice', [\app\adminapi\controller\setting\SystemConfigController::class, 'saveElecInvoice'])->option(['real_name' => '保存电子发票配置']);
    })->option(['parent' => 'setting', 'cate_name' => '系统配置']);

    /** 组合数据 */
    Route::group(function () {
        //组合数据资源路由
        Route::resource('group', \app\adminapi\controller\setting\SystemGroupController::class)->option([
            'real_name' => [
                'index' => '获取组合数据列表',
                'create' => '获取组合数据表单',
                'save' => '保存组合数据',
                'edit' => '获取修改组合数据表单',
                'update' => '修改组合数据',
                'delete' => '删除组合数据'
            ]
        ]);
        //组合数据全部
        Route::get('group_all', [\app\adminapi\controller\setting\SystemGroupController::class, 'getGroup'])->option(['real_name' => '组合数据全部']);
        //组合数据子数据资源路由
        Route::resource('group_data', \app\adminapi\controller\setting\SystemGroupDataController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取组合数据子数据列表',
                'create' => '获取组合数据子数据表单',
                'save' => '保存组合数据子数据',
                'edit' => '获取修改组合数据子数据表单',
                'update' => '修改组合数据子数据',
                'delete' => '删除组合数据子数据'
            ]
        ]);
        //修改数据状态
        Route::get('group_data/header', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'header'])->option(['real_name' => '组合数据头部']);
        //修改数据状态
        Route::put('group_data/set_status/:id/:status', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'setStatus'])->option(['real_name' => '修改组合数据状态']);
        //数据配置保存
        Route::post('group_data/save_all', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'saveAll'])->option(['real_name' => '提交数据配置']);
        //获取客服广告
        Route::get('get_kf_adv', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'getKfAdv'])->option(['real_name' => '获取客服广告']);
        //设置客服广告
        Route::post('set_kf_adv', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'setKfAdv'])->option(['real_name' => '设置客服广告']);
        //签到天数配置资源
        Route::resource('sign_data', \app\adminapi\controller\setting\SystemGroupDataController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取签到天数配置列表',
                'create' => '获取签到天数配置表单',
                'save' => '保存签到天数配置',
                'edit' => '获取修改签到天数配置表单',
                'update' => '修改签到天数配置',
                'delete' => '删除签到天数配置'
            ]
        ]);
        //签到数据字段
        Route::get('sign_data/header', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'header'])->option(['real_name' => '签到数据头部']);
        //修改签到数据状态
        Route::put('sign_data/set_status/:id/:status', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'setStatus'])->option(['real_name' => '修改签到数据状态']);
        //订单详情动态图配置资源
        Route::resource('order_data', \app\adminapi\controller\setting\SystemGroupDataController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取订单详情动态图列表',
                'create' => '获取订单详情动态图表单',
                'save' => '保存订单详情动态图',
                'edit' => '获取修改订单详情动态图表单',
                'update' => '修改订单详情动态图',
                'delete' => '删除订单详情动态图'
            ]
        ]);
        //订单数据字段
        Route::get('order_data/header', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'header'])->option(['real_name' => '订单数据字段']);
        //订单数据状态
        Route::put('order_data/set_status/:id/:status', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'setStatus'])->option(['real_name' => '订单数据状态']);
        //个人中心菜单配置资源
        Route::resource('usermenu_data', \app\adminapi\controller\setting\SystemGroupDataController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取个人中心菜单列表',
                'create' => '获取个人中心菜单表单',
                'save' => '保存个人中心菜单',
                'edit' => '获取修改个人中心菜单表单',
                'update' => '修改个人中心菜单',
                'delete' => '删除个人中心菜单'
            ]
        ]);
        //个人中心菜单数据字段
        Route::get('usermenu_data/header', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'header'])->option(['real_name' => '个人中心菜单数据字段']);
        //个人中心菜单数据状态
        Route::put('usermenu_data/set_status/:id/:status', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'setStatus'])->option(['real_name' => '个人中心菜单数据状态']);
        //分享海报配置资源
        Route::resource('poster_data', \app\adminapi\controller\setting\SystemGroupDataController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取分享海报列表',
                'create' => '获取分享海报表单',
                'save' => '保存分享海报',
                'edit' => '获取修改分享海报表单',
                'update' => '修改分享海报',
                'delete' => '删除分享海报'
            ]
        ]);
        //分享海报数据字段
        Route::get('poster_data/header', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'header'])->option(['real_name' => '分享海报数据字段']);
        //分享海报数据状态
        Route::put('poster_data/set_status/:id/:status', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'setStatus'])->option(['real_name' => '分享海报数据状态']);
        //秒杀配置资源
        Route::resource('seckill_data', \app\adminapi\controller\setting\SystemGroupDataController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取分秒杀配置列表',
                'create' => '获取秒杀配置表单',
                'save' => '保存秒杀配置',
                'edit' => '获取修改秒杀配置表单',
                'update' => '修改秒杀配置',
                'delete' => '删除秒杀配置'
            ]
        ]);
        //秒杀数据字段
        Route::get('seckill_data/header', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'header'])->option(['real_name' => '秒杀数据字段']);
        //秒杀数据状态
        Route::put('seckill_data/set_status/:id/:status', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'setStatus'])->option(['real_name' => '秒杀数据状态']);
        //获取隐私协议
        Route::get('get_user_agreement', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'getUserAgreement'])->option(['real_name' => '获取隐私协议']);
        //设置隐私协议
        Route::post('set_user_agreement', [\app\adminapi\controller\setting\SystemGroupDataController::class, 'setUserAgreement'])->option(['real_name' => '设置隐私协议']);
    })->option(['parent' => 'setting', 'cate_name' => '组合数据']);

    /** 城市数据 */
    Route::group(function () {
        //获取城市数据完整列表
        Route::get('city/full_list', [\app\adminapi\controller\setting\SystemCityController::class, 'fullList'])->option(['real_name' => '获取城市数据完整列表']);
        //获取城市数据列表
        Route::get('city/list/:parent_id', [\app\adminapi\controller\setting\SystemCityController::class, 'index'])->option(['real_name' => '获取城市数据列表']);
        //添加城市数据表单
        Route::get('city/add/:parent_id', [\app\adminapi\controller\setting\SystemCityController::class, 'add'])->option(['real_name' => '添加城市数据表单']);
        //修改城市数据表单
        Route::get('city/:id/edit', [\app\adminapi\controller\setting\SystemCityController::class, 'edit'])->option(['real_name' => '修改城市数据表单']);
        //新增/修改城市数据
        Route::post('city/save', [\app\adminapi\controller\setting\SystemCityController::class, 'save'])->option(['real_name' => '新增/修改城市数据']);
        //修改城市数据表单
        Route::delete('city/del/:city_id', [\app\adminapi\controller\setting\SystemCityController::class, 'delete'])->option(['real_name' => '删除城市数据']);
        //清除城市数据缓存
        Route::get('city/clean_cache', [\app\adminapi\controller\setting\SystemCityController::class, 'cleanCache'])->option(['real_name' => '清除城市数据缓存']);
    })->option(['parent' => 'setting', 'cate_name' => '城市数据']);

    /** 系统通知 */
    Route::group(function () {
        //系统通知列表
        Route::get('notification/index', [\app\adminapi\controller\setting\NoticeConfigController::class, 'index'])->option(['real_name' => '系统通知列表']);
        //获取单条数据
        Route::get('notification/info', [\app\adminapi\controller\setting\NoticeConfigController::class, 'info'])->option(['real_name' => '获取单条通知数据']);
        //保存通知设置
        Route::post('notification/save', [\app\adminapi\controller\setting\NoticeConfigController::class, 'save'])->option(['real_name' => '保存通知设置']);
        //修改消息状态
        Route::put('notification/set_status/:type/:status/:id', [\app\adminapi\controller\setting\NoticeConfigController::class, 'setStatus'])->option(['real_name' => '修改消息状态']);

        // ==================== 渠道管理 ====================
        //渠道列表
        Route::get('notice_channel/list', [\app\adminapi\controller\setting\notification\NoticeChannelController::class, 'index'])->option(['real_name' => '渠道列表']);
        //渠道详情
        Route::get('notice_channel/detail/:id', [\app\adminapi\controller\setting\notification\NoticeChannelController::class, 'detail'])->option(['real_name' => '渠道详情']);
        //更新渠道配置
        Route::put('notice_channel/:id', [\app\adminapi\controller\setting\notification\NoticeChannelController::class, 'update'])->option(['real_name' => '更新渠道配置']);
        //设置渠道状态
        Route::put('notice_channel/set_status/:id/:status', [\app\adminapi\controller\setting\notification\NoticeChannelController::class, 'setStatus'])->option(['real_name' => '设置渠道状态']);
        //渠道选项
        Route::get('notice_channel/options', [\app\adminapi\controller\setting\notification\NoticeChannelController::class, 'options'])->option(['real_name' => '渠道选项']);
        //测试渠道发送
        Route::post('notice_channel/test/:id', [\app\adminapi\controller\setting\notification\NoticeChannelController::class, 'test'])->option(['real_name' => '测试渠道发送']);

        // ==================== 发送记录 ====================
        //发送记录列表
        Route::get('notice_log/list', [\app\adminapi\controller\setting\notification\NoticeLogController::class, 'index'])->option(['real_name' => '发送记录列表']);
        //发送记录详情
        Route::get('notice_log/detail/:id', [\app\adminapi\controller\setting\notification\NoticeLogController::class, 'detail'])->option(['real_name' => '发送记录详情']);
        //发送统计
        Route::get('notice_log/statistics', [\app\adminapi\controller\setting\notification\NoticeLogController::class, 'statistics'])->option(['real_name' => '发送统计']);
        //重试发送
        Route::post('notice_log/retry/:id', [\app\adminapi\controller\setting\notification\NoticeLogController::class, 'retry'])->option(['real_name' => '重试发送']);
        //清理日志
        Route::post('notice_log/clean', [\app\adminapi\controller\setting\notification\NoticeLogController::class, 'clean'])->option(['real_name' => '清理日志']);
    })->option(['parent' => 'setting', 'cate_name' => '系统通知']);

    /** 协议版权 */
    Route::group(function () {
        //协议设置
        Route::get('agreement_list', [\app\adminapi\controller\setting\SystemAgreementController::class, 'agreementList'])->option(['real_name' => '获取协议列表']);
        Route::get('get_agreement/:type', [\app\adminapi\controller\setting\SystemAgreementController::class, 'getAgreement'])->option(['real_name' => '获取协议内容']);
        Route::post('save_agreement', [\app\adminapi\controller\setting\SystemAgreementController::class, 'saveAgreement'])->option(['real_name' => '设置协议内容']);
        //获取版权信息
        Route::get('get_version', [\app\adminapi\controller\setting\SystemConfigController::class, 'getVersion'])->option(['real_name' => '获取版权信息']);
    })->option(['parent' => 'setting', 'cate_name' => '协议版权']);

    /** 服务人员管理 */
    Route::group(function () {
        Route::get('service_personnel/index', [\app\adminapi\controller\personnel\ServicePersonnelController::class, 'index'])->option(['real_name' => '服务人员列表']);
        Route::get('service_personnel/add', [\app\adminapi\controller\personnel\ServicePersonnelController::class, 'add'])->option(['real_name' => '新增服务人员表单']);
        Route::post('service_personnel/save', [\app\adminapi\controller\personnel\ServicePersonnelController::class, 'save'])->option(['real_name' => '保存服务人员']);
        Route::get('service_personnel/<id>/edit', [\app\adminapi\controller\personnel\ServicePersonnelController::class, 'edit'])->option(['real_name' => '编辑服务人员表单']);
        Route::put('service_personnel/<id>', [\app\adminapi\controller\personnel\ServicePersonnelController::class, 'update'])->option(['real_name' => '修改服务人员']);
        Route::delete('service_personnel/del/<id>', [\app\adminapi\controller\personnel\ServicePersonnelController::class, 'delete'])->option(['real_name' => '删除服务人员']);
        Route::put('service_personnel/set_status/<id>/<status>', [\app\adminapi\controller\personnel\ServicePersonnelController::class, 'setStatus'])->option(['real_name' => '服务人员状态修改']);
    })->option(['parent' => 'setting', 'cate_name' => '服务人员管理']);

    /** 多语言 */
    Route::group(function () {
        //语言国家列表
        Route::get('lang_country/list', [\app\adminapi\controller\setting\LangCountryController::class, 'langCountryList'])->option(['real_name' => '语言国家列表']);
        //添加语言地区表单
        Route::get('lang_country/form/:id', [\app\adminapi\controller\setting\LangCountryController::class, 'langCountryForm'])->option(['real_name' => '添加语言地区表单']);
        //保存语言地区
        Route::post('lang_country/save/:id', [\app\adminapi\controller\setting\LangCountryController::class, 'langCountrySave'])->option(['real_name' => '保存语言地区']);
        //删除语言地区
        Route::delete('lang_country/del/:id', [\app\adminapi\controller\setting\LangCountryController::class, 'langCountryDel'])->option(['real_name' => '删除语言地区']);
        //语言类型列表
        Route::get('lang_type/list', [\app\adminapi\controller\setting\LangTypeController::class, 'langTypeList'])->option(['real_name' => '语言类型列表']);
        //新增修改语言类型表单
        Route::get('lang_type/form/:id', [\app\adminapi\controller\setting\LangTypeController::class, 'langTypeForm'])->option(['real_name' => '新增修改语言类型表单']);
        //保存新增修改语言
        Route::post('lang_type/save/:id', [\app\adminapi\controller\setting\LangTypeController::class, 'langTypeSave'])->option(['real_name' => '保存新增修改语言']);
        //删除语言
        Route::delete('lang_type/del/:id', [\app\adminapi\controller\setting\LangTypeController::class, 'langTypeDel'])->option(['real_name' => '删除语言']);
        //修改语言类型状态
        Route::put('lang_type/status/:id/:status', [\app\adminapi\controller\setting\LangTypeController::class, 'langTypeStatus'])->option(['real_name' => '修改语言类型状态']);
        //获取语言列表
        Route::get('lang_code/list', [\app\adminapi\controller\setting\LangCodeController::class, 'langCodeList'])->option(['real_name' => '语言列表']);
        //获取语言信息
        Route::get('lang_code/info', [\app\adminapi\controller\setting\LangCodeController::class, 'langCodeInfo'])->option(['real_name' => '语言详情']);
        //保存修改语言
        Route::post('lang_code/save', [\app\adminapi\controller\setting\LangCodeController::class, 'langCodeSave'])->option(['real_name' => '保存修改语言']);
        //删除语言
        Route::delete('lang_code/del/:id', [\app\adminapi\controller\setting\LangCodeController::class, 'langCodeDel'])->option(['real_name' => '删除语言']);
        //机器翻译
        Route::post('lang_code/translate', [\app\adminapi\controller\setting\LangCodeController::class, 'langCodeTranslate'])->option(['real_name' => '机器翻译']);
    })->option(['parent' => 'setting', 'cate_name' => '多语言']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'setting', 'mark_name' => '系统设置']);
