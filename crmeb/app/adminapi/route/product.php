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

Route::group('product', function () {

    Route::get('category', 'v1.product.StoreCategory/index')->option(['real_name' => '商品分类列表']);
    //商品树形列表
    Route::get('category/tree/:type', 'v1.product.StoreCategory/tree_list')->option(['real_name' => '商品分类树形列表']);
    //商品分类新增表单
    Route::get('category/create', 'v1.product.StoreCategory/create')->option(['real_name' => '商品分类新增表单']);
    //商品分类新增
    Route::post('category', 'v1.product.StoreCategory/save')->option(['real_name' => '商品分类新增']);
    //商品分类编辑表单
    Route::get('category/:id', 'v1.product.StoreCategory/edit')->option(['real_name' => '商品分类编辑表单']);
    //商品分类编辑
    Route::put('category/:id', 'v1.product.StoreCategory/update')->option(['real_name' => '商品分类编辑']);
    //删除商品分类
    Route::delete('category/:id', 'v1.product.StoreCategory/delete')->option(['real_name' => '删除商品分类']);
    //商品分类修改状态
    Route::put('category/set_show/:id/:is_show', 'v1.product.StoreCategory/set_show')->option(['real_name' => '商品分类修改状态']);
    //商品分类快捷编辑
    Route::put('category/set_category/:id', 'v1.product.StoreCategory/set_category')->option(['real_name' => '商品分类快捷编辑']);
    //商品列表
    Route::get('product', 'v1.product.StoreProduct/index')->option(['real_name' => '商品列表']);
    //获取退出未保存的数据
    Route::get('cache', 'v1.product.StoreProduct/getCacheData')->option(['real_name' => '获取退出未保存的数据']);
    //1分钟保存一次数据
    Route::post('cache', 'v1.product.StoreProduct/saveCacheData')->option(['real_name' => '保存还未提交数据']);
    //获取所有商品列表
    Route::get('product/list', 'v1.product.StoreProduct/search_list')->option(['real_name' => '获取所有商品列表']);
    //获取商品规格
    Route::get('product/attrs/:id/:type', 'v1.product.StoreProduct/get_attrs')->option(['real_name' => '获取商品规格']);
    //商品列表头
    Route::get('product/type_header', 'v1.product.StoreProduct/type_header')->option(['real_name' => '商品列表头部数据']);
    //商品详情
    Route::get('product/:id', 'v1.product.StoreProduct/get_product_info')->option(['real_name' => '商品详情']);
    //加入回收站
    Route::delete('product/:id', 'v1.product.StoreProduct/delete')->option(['real_name' => '商品放入回收站']);
    //保存新建或保存
    Route::post('product/:id', 'v1.product.StoreProduct/save')->option(['real_name' => '新建或修改商品']);
    //修改商品状态
    Route::put('product/set_show/:id/:is_show', 'v1.product.StoreProduct/set_show')->option(['real_name' => '修改商品状态']);
    //商品快速编辑
    Route::put('product/set_product/:id', 'v1.product.StoreProduct/set_product')->option(['real_name' => '商品快速编辑']);
    //设置批量商品上架
    Route::put('product/product_show', 'v1.product.StoreProduct/product_show')->option(['real_name' => '设置批量商品上架']);
    //设置批量商品下架
    Route::put('product/product_unshow', 'v1.product.StoreProduct/product_unshow')->option(['real_name' => '设置批量商品下架']);
    //规则列表
    Route::get('product/rule', 'v1.product.StoreProductRule/index')->option(['real_name' => '商品规则列表']);
    //规则 保存新建或编辑
    Route::post('product/rule/:id', 'v1.product.StoreProductRule/save')->option(['real_name' => '新建或编辑商品规则']);
    //规则详情
    Route::get('product/rule/:id', 'v1.product.StoreProductRule/read')->option(['real_name' => '商品规则详情']);
    //删除属性规则
    Route::delete('product/rule/delete', 'v1.product.StoreProductRule/delete')->option(['real_name' => '删除商品规则']);
    //生成属性
    Route::post('generate_attr/:id/:type', 'v1.product.StoreProduct/is_format_attr')->option(['real_name' => '生成商品规格列表']);
    //评论列表
    Route::get('reply', 'v1.product.StoreProductReply/index')->option(['real_name' => '商品评论列表']);
    //回复评论
    Route::put('reply/set_reply/:id', 'v1.product.StoreProductReply/set_reply')->option(['real_name' => '商品回复评论']);
    //删除评论
    Route::delete('reply/:id', 'v1.product.StoreProductReply/delete')->option(['real_name' => '删除商品评论']);
    //获取商品数据
    Route::post('crawl', 'v1.product.CopyTaobao/get_request_contents')->option(['real_name' => '获取采集商品数据']);
    //获取复制商品配置
    Route::get('copy_config', 'v1.product.CopyTaobao/getConfig')->option(['real_name' => '获取复制商品配置']);
    //复制其他平台商品
    Route::post('copy', 'v1.product.CopyTaobao/copyProduct')->option(['real_name' => '复制其他平台商品']);
    //保存商品数据
    Route::post('crawl/save', 'v1.product.CopyTaobao/save_product')->option(['real_name' => '保存采集商品数据']);
    //调起虚拟评论表单
    Route::get('reply/fictitious_reply/:product_id', 'v1.product.StoreProductReply/fictitious_reply')->option(['real_name' => '虚拟评论表单']);
    //保存虚拟评论
    Route::post('reply/save_fictitious_reply', 'v1.product.StoreProductReply/save_fictitious_reply')->option(['real_name' => '保存虚拟评论']);
    //获取规则属性模板
    Route::get('product/get_rule', 'v1.product.StoreProduct/get_rule')->option(['real_name' => '获取商品规则属性模板']);
    //获取运费模板
    Route::get('product/get_template', 'v1.product.StoreProduct/get_template')->option(['real_name' => '获取运费模板']);
    //上传视频密钥接口
    Route::get('product/get_temp_keys', 'v1.product.StoreProduct/getTempKeys')->option(['real_name' => '上传视频密钥接口']);
    //检测是否有活动开启
    Route::get('product/check_activity/:id', 'v1.product.StoreProduct/check_activity')->option(['real_name' => '检测是商品否有活动开启']);
    //导入虚拟商品卡密
    Route::get('product/import_card', 'v1.product.StoreProduct/import_card')->option(['real_name' => '导入虚拟商品卡密']);

    /** 商品批量操作 */
    Route::post('batch/setting', 'v1.product.StoreProduct/batchSetting')->option(['real_name' => '商品批量设置']);


})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
