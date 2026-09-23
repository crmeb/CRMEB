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
 * diy 相关路由
 */
Route::group('diy', function () {

    Route::get('get_list', [\app\adminapi\controller\diy\DiyController::class, 'getList'])->option(['real_name' => 'Diy模板列表']);
    Route::get('component_status', [\app\adminapi\controller\diy\DiyController::class, 'getComponentStatus'])->option(['real_name' => '获取DIY组件可用状态']);
    Route::get('get_info/:id', [\app\adminapi\controller\diy\DiyController::class, 'getInfo'])->option(['real_name' => 'Diy模板数据详情']);
    Route::get('get_diy_info/:id', [\app\adminapi\controller\diy\DiyController::class, 'getDiyInfo'])->option(['real_name' => 'Diy模板数据详情']);
    Route::delete('del/:id', [\app\adminapi\controller\diy\DiyController::class, 'del'])->option(['real_name' => '删除DIY模板']);
    Route::put('set_status/:id', [\app\adminapi\controller\diy\DiyController::class, 'setStatus'])->option(['real_name' => '使用DIY模板']);
    Route::get('create', [\app\adminapi\controller\diy\DiyController::class, 'create'])->option(['real_name' => '添加表单']);
    Route::post('create', [\app\adminapi\controller\diy\DiyController::class, 'save'])->option(['real_name' => '添加DIY']);
    Route::post('save/[:id]', [\app\adminapi\controller\diy\DiyController::class, 'saveData'])->option(['real_name' => '添加DIY模板']);
    Route::post('diy_save/[:id]', [\app\adminapi\controller\diy\DiyController::class, 'saveDiyData'])->option(['real_name' => '添加DIY模板']);
    Route::get('get_url', [\app\adminapi\controller\diy\DiyController::class, 'getUrl'])->option(['real_name' => '获取前端页面路径']);
    Route::get('get_category', [\app\adminapi\controller\diy\DiyController::class, 'getCategory'])->option(['real_name' => '获取商品分类']);
    Route::get('get_product', [\app\adminapi\controller\diy\DiyController::class, 'getProduct'])->option(['real_name' => '获取商品列表']);
    Route::get('get_store_status', [\app\adminapi\controller\diy\DiyController::class, 'getStoreStatus'])->option(['real_name' => '获取门店自提开启状态']);
    Route::get('recovery/:id', [\app\adminapi\controller\diy\DiyController::class, 'Recovery'])->option(['real_name' => '还原Diy默认数据']);
    Route::get('get_by_category', [\app\adminapi\controller\diy\DiyController::class, 'getByCategory'])->option(['real_name' => '获取所有二级分类']);
    Route::get('set_recovery/:id', [\app\adminapi\controller\diy\DiyController::class, 'setRecovery'])->option(['real_name' => '设置Diy默认数据']);
    Route::get('get_product_list', [\app\adminapi\controller\diy\DiyController::class, 'getProductList'])->option(['real_name' => '获取商品列表']);
    Route::get('get_color_change/:type', [\app\adminapi\controller\diy\DiyController::class, 'getColorChange'])->option(['real_name' => '获取风格设置']);
    Route::put('color_change/:status/:type', [\app\adminapi\controller\diy\DiyController::class, 'colorChange'])->option(['real_name' => '换色和分类保存']);
    Route::get('get_member', [\app\adminapi\controller\diy\DiyController::class, 'getMember'])->option(['real_name' => '个人中心详情']);
    Route::get('get_page_category', [\app\adminapi\controller\diy\PageLinkController::class, 'getCategory'])->option(['real_name' => '获取页面链接分类']);
    Route::get('get_page_link/:cate_id', [\app\adminapi\controller\diy\PageLinkController::class, 'getLinks'])->option(['real_name' => '获取页面链接']);
    Route::post('member_save', [\app\adminapi\controller\diy\DiyController::class, 'memberSaveData'])->option(['real_name' => '个人中心保存']);
    Route::get('get_routine_code/:id', [\app\adminapi\controller\diy\DiyController::class, 'getRoutineCode'])->option(['real_name' => 'diy小程序预览码']);
    Route::get('open_adv/info', [\app\adminapi\controller\diy\DiyController::class, 'getOpenAdv'])->option(['real_name' => '获取开屏广告']);
    Route::post('open_adv/add', [\app\adminapi\controller\diy\DiyController::class, 'openAdvAdd'])->option(['real_name' => '保存开屏广告']);
    Route::get('groom_list/:type', [\app\adminapi\controller\diy\DiyController::class, 'getGroomList'])->option(['real_name' => '推荐商品']);
    Route::get('link/category', [\app\adminapi\controller\diy\PageLinkController::class, 'getLinkCategory'])->option(['real_name' => '获取链接分类']);
    Route::get('link/category/form/:cate_id/[:pid]', [\app\adminapi\controller\diy\PageLinkController::class, 'getLinkCategoryForm'])->option(['real_name' => '链接分类表单']);
    Route::post('link/category/save/:cate_id', [\app\adminapi\controller\diy\PageLinkController::class, 'getLinkCategorySave'])->option(['real_name' => '链接分类保存']);
    Route::delete('link/category/del/:cate_id', [\app\adminapi\controller\diy\PageLinkController::class, 'getLinkCategoryDel'])->option(['real_name' => '链接分类删除']);
    Route::get('link/list/:cate_id', [\app\adminapi\controller\diy\PageLinkController::class, 'getLinkList'])->option(['real_name' => '链接列表']);
    Route::get('link/form/:id', [\app\adminapi\controller\diy\PageLinkController::class, 'getLinkForm'])->option(['real_name' => '链接表单']);
    Route::post('link/save/:id', [\app\adminapi\controller\diy\PageLinkController::class, 'getLinkSave'])->option(['real_name' => '链接保存']);
    Route::delete('link/del/:id', [\app\adminapi\controller\diy\PageLinkController::class, 'getLinkDel'])->option(['real_name' => '链接删除']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'diy', 'mark_name' => '页面装修']);


/**
 * diy_pro 相关路由
 */
Route::group('diy_pro', function () {
    Route::get('get_list', [\app\adminapi\controller\diy\DiyProController::class, 'getList'])->option(['real_name' => 'DiyPro模板列表']);
    Route::get('get_info/:id', [\app\adminapi\controller\diy\DiyProController::class, 'getInfo'])->option(['real_name' => 'DiyPro模板详情']);
    Route::post('save/:id', [\app\adminapi\controller\diy\DiyProController::class, 'saveInfo'])->option(['real_name' => 'DiyPro模板保存']);
    Route::get('get_product', [\app\adminapi\controller\diy\DiyProController::class, 'getProduct'])->option(['real_name' => '获取商品列表']);
    Route::post('update/name/:id', [\app\adminapi\controller\diy\DiyProController::class, 'updateName'])->option(['real_name' => '修改名称']);
    Route::get('export/data/:id', [\app\adminapi\controller\diy\DiyProController::class, 'exportDIYData'])->option(['real_name' => '导出DIY数据']);
    Route::post('import/data', [\app\adminapi\controller\diy\DiyProController::class, 'importDIYData'])->option(['real_name' => '导入DIY数据']);
    Route::get('text/field', [\app\adminapi\controller\diy\DiyProController::class, 'textField'])->option(['real_name' => '文本字段']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'diy_pro', 'mark_name' => '页面装修']);


/**
 * 主题相关路由
 */
Route::group('theme', function () {
    Route::get('plugin_status', [\app\adminapi\controller\diy\ThemeController::class, 'getPluginStatus'])->option(['real_name' => '主题活动插件安装状态']);
    Route::get('list', [\app\adminapi\controller\diy\ThemeController::class, 'getThemeList'])->option(['real_name' => '主题列表']);
    Route::get('info/:id/[:type]', [\app\adminapi\controller\diy\ThemeController::class, 'getThemeInfo'])->option(['real_name' => '主题详情']);
    Route::post('save/:id', [\app\adminapi\controller\diy\ThemeController::class, 'saveTheme'])->option(['real_name' => '保存主题']);
    Route::post('save_title/:id', [\app\adminapi\controller\diy\ThemeController::class, 'saveThemeTitle'])->option(['real_name' => '保存主题名称和简介']);
    Route::post('save_image/:id', [\app\adminapi\controller\diy\ThemeController::class, 'saveThemeImage'])->option(['real_name' => '保存主题的图片']);
    Route::get('article', [\app\adminapi\controller\diy\ThemeController::class, 'getThemeArticleList'])->option(['real_name' => '自定义组件-文章']);
    Route::get('coupon', [\app\adminapi\controller\diy\ThemeController::class, 'getThemeCouponList'])->option(['real_name' => '自定义组件-优惠券']);
    Route::get('product', [\app\adminapi\controller\diy\ThemeController::class, 'getThemeProductList'])->option(['real_name' => '自定义组件-商品']);
    Route::delete('del/:id', [\app\adminapi\controller\diy\ThemeController::class, 'deleteTheme'])->option(['real_name' => '删除主题']);
    Route::get('export/:id', [\app\adminapi\controller\diy\ThemeController::class, 'exportTheme'])->option(['real_name' => '主题导出']);
    Route::get('export_record/:record_id', [\app\adminapi\controller\diy\ThemeController::class, 'getExportRecord'])->option(['real_name' => '查询主题导出记录']);
    Route::post('import', [\app\adminapi\controller\diy\ThemeController::class, 'importTheme'])->option(['real_name' => '主题导入']);
    Route::get('use/:id', [\app\adminapi\controller\diy\ThemeController::class, 'useTheme'])->option(['real_name' => '使用主题']);
    Route::get('use_data/:id', [\app\adminapi\controller\diy\ThemeController::class, 'useThemeData'])->option(['real_name' => '使用主题数据']);
    Route::get('using', [\app\adminapi\controller\diy\ThemeController::class, 'getUsingTheme'])->option(['real_name' => '正在使用的主题']);
    Route::get('restore/:id', [\app\adminapi\controller\diy\ThemeController::class, 'restoreTheme'])->option(['real_name' => '还原主题']);
    Route::get('micro_page', [\app\adminapi\controller\diy\ThemeController::class, 'getMicroPageList'])->option(['real_name' => '微页面列表']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'theme', 'mark_name' => '主题']);

/**
 * 主题组件相关路由
 */
Route::group('theme_module', function () {
    Route::get('list', [\app\adminapi\controller\diy\ThemeModuleController::class, 'index'])->option(['real_name' => '主题组件列表']);
    Route::post('save', [\app\adminapi\controller\diy\ThemeModuleController::class, 'save'])->option(['real_name' => '新增主题组件']);
    Route::delete('del/:id', [\app\adminapi\controller\diy\ThemeModuleController::class, 'delete'])->option(['real_name' => '删除主题组件']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'theme_module', 'mark_name' => '主题组件']);
