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

Route::group('product', function () {

    /** 商品分类 */
    Route::group(function () {
        Route::get('category', 'v1.product.StoreCategory/index')->option(['real_name' => '商品分类列表']);
        //商品树形列表
        Route::get('category/tree/:type', 'v1.product.StoreCategory/tree_list')->option(['real_name' => '商品分类树形列表']);
        //商品分类树形列表
        Route::get('category/cascader/:type', 'v1.product.StoreCategory/cascader_list')->option(['real_name' => '商品分类树形列表']);
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
    })->option(['parent' => 'product', 'cate_name' => '商品分类']);

    /** 商品 */
    Route::group(function () {
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
        //修改商品状态
        Route::put('product/set_show/:id/:is_show', 'v1.product.StoreProduct/set_show')->option(['real_name' => '修改商品状态']);
        //商品快速编辑
//        Route::put('product/set_product/:id', 'v1.product.StoreProduct/set_product')->option(['real_name' => '商品快速编辑']);
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
        //商品详情
        Route::get('product/:id', 'v1.product.StoreProduct/get_product_info')->option(['real_name' => '商品详情']);
        //加入回收站
        Route::delete('product/:id', 'v1.product.StoreProduct/delete')->option(['real_name' => '商品放入回收站']);
        //加入回收站
        Route::post('product/batch_delete', 'v1.product.StoreProduct/batchDelete')->option(['real_name' => '商品批量放入回收站']);
        //批量从回收站恢复
        Route::post('product/batch_recover', 'v1.product.StoreProduct/batchRecover')->option(['real_name' => '批量从回收站恢复']);
        //保存新建或保存
        Route::post('product/:id', 'v1.product.StoreProduct/save')->option(['real_name' => '新建或修改商品']);
        //生成属性
        Route::post('generate_attr/:id/:type', 'v1.product.StoreProduct/is_format_attr')->option(['real_name' => '生成商品规格列表']);
        //商品批量操作
        Route::post('batch/setting', 'v1.product.StoreProduct/batchSetting')->option(['real_name' => '商品批量设置']);
        //商品类型接口
        Route::get('product_type_config', 'v1.product.StoreProduct/productTypeConfig')->option(['real_name' => '商品类型接口']);
        //商品迁移导出
        Route::get('product_export', 'v1.product.StoreProduct/productExport')->option(['real_name' => '商品迁移导出']);
        //商品迁移导入
        Route::post('product_import', 'v1.product.StoreProduct/productImport')->option(['real_name' => '商品迁移导出']);
        //回收站商品彻底删除
        Route::delete('full_del/:id', 'v1.product.StoreProduct/fullDel')->option(['real_name' => '回收站商品彻底删除']);

        Route::get('other_info/:id/:type', 'v1.product.StoreProduct/otherInfo')->option(['real_name' => '商品其他信息']);
        Route::post('other_save/:id/:type', 'v1.product.StoreProduct/otherSave')->option(['real_name' => '修改商品其他信息']);

    })->option(['parent' => 'product', 'cate_name' => '商品']);

    /** 商品评论 */
    Route::group(function () {
        //评论列表
        Route::get('reply', 'v1.product.StoreProductReply/index')->option(['real_name' => '商品评论列表']);
        //回复评论
        Route::put('reply/set_reply/:id', 'v1.product.StoreProductReply/set_reply')->option(['real_name' => '商品回复评论']);
        //删除评论
        Route::delete('reply/:id', 'v1.product.StoreProductReply/delete')->option(['real_name' => '删除商品评论']);
        //调起虚拟评论表单
        Route::get('reply/fictitious_reply/:product_id', 'v1.product.StoreProductReply/fictitious_reply')->option(['real_name' => '虚拟评论表单']);
        //保存虚拟评论
        Route::post('reply/save_fictitious_reply', 'v1.product.StoreProductReply/save_fictitious_reply')->option(['real_name' => '保存虚拟评论']);
        //审核商品评论
        Route::put('reply/set_status/:id/:status', 'v1.product.StoreProductReply/set_status')->option(['real_name' => '审核商品评论']);
        //批量审核商品评论
        Route::post('reply/batch_set_status', 'v1.product.StoreProductReply/batch_set_status')->option(['real_name' => '批量审核商品评论']);
    })->option(['parent' => 'product', 'cate_name' => '商品评论']);

    /** 商品采集 */
    Route::group(function () {
        //获取商品数据
        Route::post('crawl', 'v1.product.CopyTaobao/get_request_contents')->option(['real_name' => '获取采集商品数据']);
        //获取复制商品配置
        Route::get('copy_config', 'v1.product.CopyTaobao/getConfig')->option(['real_name' => '获取复制商品配置']);
        //复制其他平台商品
        Route::post('copy', 'v1.product.CopyTaobao/copyProduct')->option(['real_name' => '复制其他平台商品']);
        //保存商品数据
        Route::post('crawl/save', 'v1.product.CopyTaobao/save_product')->option(['real_name' => '保存采集商品数据']);
    })->option(['parent' => 'product', 'cate_name' => '商品采集']);

    /** 商品标签 */
    Route::group(function () {
        //商品标签分类
        Route::get('label_cate/list', 'v1.product.StoreProductLabel/labelCateList')->option(['real_name' => '商品标签分类']);
        Route::get('label_cate/form/:id', 'v1.product.StoreProductLabel/labelCateForm')->option(['real_name' => '商品标签分类添加表单']);
        Route::post('label_cate/save/:id', 'v1.product.StoreProductLabel/labelCateSave')->option(['real_name' => '商品标签分类保存']);
        Route::delete('label_cate/del/:id', 'v1.product.StoreProductLabel/labelCateDel')->option(['real_name' => '商品标签分类删除']);
        Route::get('label/list', 'v1.product.StoreProductLabel/labelList')->option(['real_name' => '商品标签列表']);
        Route::get('label/info/:id', 'v1.product.StoreProductLabel/labelInfo')->option(['real_name' => '商品标签详情']);
        Route::post('label/save', 'v1.product.StoreProductLabel/labelSave')->option(['real_name' => '商品标签保存']);
        Route::delete('label/del/:id', 'v1.product.StoreProductLabel/labelDel')->option(['real_name' => '商品标签删除']);
        Route::put('label/status/:id/:status', 'v1.product.StoreProductLabel/labelStatus')->option(['real_name' => '修改商品标签状态']);
        Route::put('label/is_show/:id/:is_show', 'v1.product.StoreProductLabel/labelIsShow')->option(['real_name' => '修改商品标签显示']);
        Route::get('label/use_list', 'v1.product.StoreProductLabel/labelUseList')->option(['real_name' => '使用商品标签列表']);
    })->option(['parent' => 'product', 'cate_name' => '商品标签']);

    /** 商品参数 */
    Route::group(function () {
        Route::get('param/list', 'v1.product.StoreProductParam/getParamList')->option(['real_name' => '商品参数列表']);
        Route::get('param/info/:id', 'v1.product.StoreProductParam/getParamInfo')->option(['real_name' => '商品参数详情']);
        Route::get('param/value/:id', 'v1.product.StoreProductParam/getParamValue')->option(['real_name' => '商品参数值']);
        Route::post('param/save/:id', 'v1.product.StoreProductParam/saveParamData')->option(['real_name' => '保存商品参数']);
        Route::put('param/status/:id/:status', 'v1.product.StoreProductParam/setParamStatus')->option(['real_name' => '修改商品参数状态']);
        Route::delete('param/del/:id', 'v1.product.StoreProductParam/delParamData')->option(['real_name' => '删除商品参数']);
    })->option(['parent' => 'product', 'cate_name' => '商品参数']);

    /** 商品保障 */
    Route::group(function () {
        Route::get('protection/list', 'v1.product.StoreProductProtection/protectionList')->option(['real_name' => '商品保障列表']);
        Route::get('protection/info/:id', 'v1.product.StoreProductProtection/protectionInfo')->option(['real_name' => '商品保障详情']);
        Route::get('protection/form/:id', 'v1.product.StoreProductProtection/protectionForm')->option(['real_name' => '商品保障表单']);
        Route::post('protection/save/:id', 'v1.product.StoreProductProtection/protectionSave')->option(['real_name' => '保存商品保障']);
        Route::put('protection/status/:id/:status', 'v1.product.StoreProductProtection/protectionStatus')->option(['real_name' => '修改商品保障状态']);
        Route::delete('protection/del/:id', 'v1.product.StoreProductProtection/protectionDel')->option(['real_name' => '删除商品保障']);
    })->option(['parent' => 'product', 'cate_name' => '商品参数']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'product', 'mark_name' => '商品管理']);
