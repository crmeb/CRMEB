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

Route::group('product', function () {

    Route::get('category', 'v1.product.StoreCategory/index');
    //商品树形列表
    Route::get('category/tree/:type', 'v1.product.StoreCategory/tree_list');
    //商品分类新增表单
    Route::get('category/create', 'v1.product.StoreCategory/create');
    //商品分类新增
    Route::post('category', 'v1.product.StoreCategory/save');
    //商品分类编辑表单
    Route::get('category/:id', 'v1.product.StoreCategory/edit');
    //商品分类编辑
    Route::put('category/:id', 'v1.product.StoreCategory/update');
    //删除商品分类
    Route::delete('category/:id', 'v1.product.StoreCategory/delete');
    //商品分类修改状态
    Route::put('category/set_show/:id/:is_show', 'v1.product.StoreCategory/set_show');
    //商品分类快捷编辑
    Route::put('category/set_category/:id', 'v1.product.StoreCategory/set_category');
    //商品列表
    Route::get('product', 'v1.product.StoreProduct/index');
    //获取退出未保存的数据
    Route::get('cache', 'v1.product.StoreProduct/getCacheData');
    //1分钟保存一次数据
    Route::post('cache', 'v1.product.StoreProduct/saveCacheData');
    //获取所有商品列表
    Route::get('product/list', 'v1.product.StoreProduct/search_list');
    //获取商品规格
    Route::get('product/attrs/:id/:type', 'v1.product.StoreProduct/get_attrs');
    //商品列表头
    Route::get('product/type_header', 'v1.product.StoreProduct/type_header');
    //商品详情
    Route::get('product/:id', 'v1.product.StoreProduct/get_product_info');
    //加入回收站
    Route::delete('product/:id', 'v1.product.StoreProduct/delete');
    //保存新建或保存
    Route::post('product/:id', 'v1.product.StoreProduct/save');
    //修改商品状态
    Route::put('product/set_show/:id/:is_show', 'v1.product.StoreProduct/set_show');
    //商品快速编辑
    Route::put('product/set_product/:id', 'v1.product.StoreProduct/set_product');
    //设置批量商品上架
    Route::put('product/product_show', 'v1.product.StoreProduct/product_show');
    //设置批量商品下架
    Route::put('product/product_unshow', 'v1.product.StoreProduct/product_unshow');
    //规则列表
    Route::get('product/rule', 'v1.product.StoreProductRule/index');
    //规则 保存新建或编辑
    Route::post('product/rule/:id', 'v1.product.StoreProductRule/save');
    //规则详情
    Route::get('product/rule/:id', 'v1.product.StoreProductRule/read');
    //删除属性规则
    Route::delete('product/rule/delete', 'v1.product.StoreProductRule/delete');
    //生成属性
    Route::post('generate_attr/:id/:type', 'v1.product.StoreProduct/is_format_attr');
    //评论列表
    Route::get('reply', 'v1.product.StoreProductReply/index');
    //回复评论
    Route::put('reply/set_reply/:id', 'v1.product.StoreProductReply/set_reply');
    //删除评论
    Route::delete('reply/:id', 'v1.product.StoreProductReply/delete');
    //获取商品数据
    Route::post('crawl', 'v1.product.CopyTaobao/get_request_contents');
    //复制其他平台商品
    Route::post('copy', 'v1.product.CopyTaobao/copyProduct');
    //保存商品数据
    Route::post('crawl/save', 'v1.product.CopyTaobao/save_product');
    //调起虚拟评论表单
    Route::get('reply/fictitious_reply/:product_id', 'v1.product.StoreProductReply/fictitious_reply');
    //保存虚拟评论
    Route::post('reply/save_fictitious_reply', 'v1.product.StoreProductReply/save_fictitious_reply');
    //获取规则属性模板
    Route::get('product/get_rule', 'v1.product.StoreProduct/get_rule');
    //获取运费模板
    Route::get('product/get_template', 'v1.product.StoreProduct/get_template');
    //上传视频密钥接口
    Route::get('product/get_temp_keys', 'v1.product.StoreProduct/getTempKeys');
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
