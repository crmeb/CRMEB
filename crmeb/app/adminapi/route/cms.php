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
 * 文章管理 相关路由
 */
Route::group('cms', function () {
    //文章资源路由
    Route::resource('cms', 'v1.cms.Article')->name('ArticleResource');
    //分类列表
    Route::get('cms/merchant_index/:id', 'v1.cms.Article/merchantIndex')->name('MerchantIndex');
    //关联商品
    Route::put('cms/relation/:id', 'v1.cms.Article/relation')->name('Relation');
    //取消关联
    Route::put('cms/unrelation/:id', 'v1.cms.Article/unrelation')->name('UnRelation');
    //文章分类资源路由
    Route::resource('category', 'v1.cms.ArticleCategory')->name('ArticleCategoryResource');
    //修改状态
    Route::put('category/set_status/:id/:status', 'v1.cms.ArticleCategory/set_status')->name('CategoryStatus');
    //分类列表
    Route::get('category_list', 'v1.cms.ArticleCategory/categoryList')->name('categoryList');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
