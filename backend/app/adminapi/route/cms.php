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
 * 文章管理 相关路由
 */
Route::group('cms', function () {

    /** 文章 */
    Route::group(function () {
        //文章资源路由（新增编辑统一走 save，控制器未提供 create/edit/update，显式排除）
        Route::resource('cms', \app\adminapi\controller\cms\ArticleController::class)->except(['create', 'edit', 'update'])->option([
            'real_name' => [
                'index' => '获取文章列表',
                'read' => '获取文章详细信息',
                'save' => '保存文章',
                'delete' => '删除文章'
            ]
        ]);
        //关联商品
        Route::put('cms/relation/:id', [\app\adminapi\controller\cms\ArticleController::class, 'relation'])->name('Relation')->option(['real_name' => '文章关联商品']);
        //取消关联
        Route::put('cms/unrelation/:id', [\app\adminapi\controller\cms\ArticleController::class, 'unrelation'])->name('UnRelation')->option(['real_name' => '取消文章关联商品']);
    })->option(['parent' => 'cms', 'cate_name' => '文章管理']);

    /** 文章分类 */
    Route::group(function () {
        //文章分类资源路由
        Route::resource('category', \app\adminapi\controller\cms\ArticleCategoryController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取文章分类列表',
                'create' => '获取文章分类表单',
                'save' => '保存文章分类',
                'edit' => '获取修改文章分类表单',
                'update' => '修改文章分类',
                'delete' => '删除文章分类'
            ]
        ]);
        //修改状态
        Route::put('category/set_status/:id/:status', [\app\adminapi\controller\cms\ArticleCategoryController::class, 'setStatus'])->name('CategoryStatus')->option(['real_name' => '修改文章分类状态']);
        //分类列表
        Route::get('category_list', [\app\adminapi\controller\cms\ArticleCategoryController::class, 'categoryList'])->name('categoryList')->option(['real_name' => '分类列表']);
        //分类树形列表
        Route::get('category_tree_list', [\app\adminapi\controller\cms\ArticleCategoryController::class, 'getTreeList'])->name('getTreeList')->option(['real_name' => '分类树形列表']);
    })->option(['parent' => 'cms', 'cate_name' => '文章分类']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'cms', 'mark_name' => '文章模块']);
