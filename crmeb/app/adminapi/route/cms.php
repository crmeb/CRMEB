<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
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
    Route::resource('cms', 'v1.cms.Article')->name('ArticleResource')->option(['real_name' => '文章']);
    //关联商品
    Route::put('cms/relation/:id', 'v1.cms.Article/relation')->name('Relation')->option(['real_name' => '文章关联商品']);
    //取消关联
    Route::put('cms/unrelation/:id', 'v1.cms.Article/unrelation')->name('UnRelation')->option(['real_name' => '取消文章关联商品']);
    //文章分类资源路由
    Route::resource('category', 'v1.cms.ArticleCategory')->name('ArticleCategoryResource')->option(['real_name' => '文章分类']);
    //修改状态
    Route::put('category/set_status/:id/:status', 'v1.cms.ArticleCategory/set_status')->name('CategoryStatus')->option(['real_name' => '修改文章分类状态']);
    //分类列表
    Route::get('category_list', 'v1.cms.ArticleCategory/categoryList')->name('categoryList')->option(['real_name' => '分类列表']);
    //分类树形列表
    Route::get('category_tree_list', 'v1.cms.ArticleCategory/getTreeList')->name('getTreeList')->option(['real_name' => '分类树形列表']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
