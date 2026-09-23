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
use app\adminapi\controller\product\CenterProductController;

Route::group('product', function () {

    /** 商品分类 */
    Route::group(function () {
        Route::get('category', [\app\adminapi\controller\product\StoreCategoryController::class, 'index'])->option(['real_name' => '商品分类列表']);
        //商品树形列表
        Route::get('category/tree/:type', [\app\adminapi\controller\product\StoreCategoryController::class, 'treeList'])->option(['real_name' => '商品分类树形列表']);
        Route::get('category/cascader/:type', [\app\adminapi\controller\product\StoreCategoryController::class, 'cascaderList'])->option(['real_name' => '商品分类树形列表']);
        //商品分类新增表单
        Route::get('category/create', [\app\adminapi\controller\product\StoreCategoryController::class, 'create'])->option(['real_name' => '商品分类新增表单']);
        //商品分类新增
        Route::post('category', [\app\adminapi\controller\product\StoreCategoryController::class, 'save'])->option(['real_name' => '商品分类新增']);
        //商品分类编辑表单
        Route::get('category/:id', [\app\adminapi\controller\product\StoreCategoryController::class, 'edit'])->option(['real_name' => '商品分类编辑表单']);
        //商品分类编辑
        Route::put('category/:id', [\app\adminapi\controller\product\StoreCategoryController::class, 'update'])->option(['real_name' => '商品分类编辑']);
        //删除商品分类
        Route::delete('category/:id', [\app\adminapi\controller\product\StoreCategoryController::class, 'delete'])->option(['real_name' => '删除商品分类']);
        //商品分类修改状态
        Route::put('category/set_show/:id/:is_show', [\app\adminapi\controller\product\StoreCategoryController::class, 'setShow'])->option(['real_name' => '商品分类修改状态']);
        //商品分类快捷编辑
        // Route::put('category/set_category/:id', [\app\adminapi\controller\product\StoreCategoryController::class, 'setCategory'])->option(['real_name' => '商品分类快捷编辑']);
    })->option(['parent' => 'product', 'cate_name' => '商品分类']);

    /** 商品 */
    Route::group(function () {
        //商品列表
        Route::get('product', [\app\adminapi\controller\product\CenterProductController::class, 'index'])->option(['real_name' => '商品列表']);
        //获取退出未保存的数据
        Route::get('cache', [\app\adminapi\controller\product\CenterProductController::class, 'getCacheData'])->option(['real_name' => '获取退出未保存的数据']);
        //1分钟保存一次数据
        Route::post('cache', [\app\adminapi\controller\product\CenterProductController::class, 'saveCacheData'])->option(['real_name' => '保存还未提交数据']);
        //取消临时保存
        Route::delete('cache', [\app\adminapi\controller\product\CenterProductController::class, 'deleteCacheData'])->option(['real_name' => '取消临时保存']);
        //获取所有商品列表
        Route::get('product/list', [\app\adminapi\controller\product\CenterProductController::class, 'searchList'])->option(['real_name' => '获取所有商品列表']);
        Route::get('product/attrs/:id/:type', [\app\adminapi\controller\product\CenterProductController::class, 'getAttrs'])->option(['real_name' => '获取商品规格']);
        //商品列表头
        Route::get('product/type_header', [\app\adminapi\controller\product\CenterProductController::class, 'typeHeader'])->option(['real_name' => '商品列表头部数据']);
        //修改商品状态
        Route::put('product/set_show/:id/:is_show', [\app\adminapi\controller\product\CenterProductController::class, 'setShow'])->option(['real_name' => '修改商品状态']);
        Route::put('product/product_show', [\app\adminapi\controller\product\CenterProductController::class, 'productShow'])->option(['real_name' => '设置批量商品上架']);
        //设置批量商品下架
        Route::put('product/product_unshow', [\app\adminapi\controller\product\CenterProductController::class, 'productUnshow'])->option(['real_name' => '设置批量商品下架']);
        //规则列表
        Route::get('product/rule', [\app\adminapi\controller\product\StoreProductRuleController::class, 'index'])->option(['real_name' => '商品规则列表']);
        //规则 保存新建或编辑
        Route::post('product/rule/:id', [\app\adminapi\controller\product\StoreProductRuleController::class, 'save'])->option(['real_name' => '新建或编辑商品规则']);
        //规则详情
        Route::get('product/rule/:id', [\app\adminapi\controller\product\StoreProductRuleController::class, 'read'])->option(['real_name' => '商品规则详情']);
        //删除属性规则
        Route::delete('product/rule/delete', [\app\adminapi\controller\product\StoreProductRuleController::class, 'delete'])->option(['real_name' => '删除商品规则']);
        //获取规则属性模板
        Route::get('product/get_rule', [\app\adminapi\controller\product\CenterProductController::class, 'getRule'])->option(['real_name' => '获取商品规则属性模板']);
        //获取运费模板
        Route::get('product/get_template', [\app\adminapi\controller\product\CenterProductController::class, 'getTemplate'])->option(['real_name' => '获取运费模板']);
        //上传视频密钥接口
        Route::get('product/get_temp_keys', [\app\adminapi\controller\product\CenterProductController::class, 'getTempKeys'])->option(['real_name' => '上传视频密钥接口']);
        //检测是否有活动开启
        Route::get('product/check_activity/:id', [\app\adminapi\controller\product\CenterProductController::class, 'checkActivity'])->option(['real_name' => '检测是商品否有活动开启']);
        //导入虚拟商品卡密
        Route::get('product/import_card', [\app\adminapi\controller\product\CenterProductController::class, 'importCard'])->option(['real_name' => '导入虚拟商品卡密']);
        //商品详情
        Route::get('product/:id', [\app\adminapi\controller\product\CenterProductController::class, 'getProductInfo'])->option(['real_name' => '商品详情']);
        //加入回收站
        Route::delete('product/:id', [\app\adminapi\controller\product\CenterProductController::class, 'delete'])->option(['real_name' => '商品放入回收站']);
        //加入回收站
        Route::post('product/batch_delete', [\app\adminapi\controller\product\CenterProductController::class, 'batchDelete'])->option(['real_name' => '商品批量放入回收站']);
        //批量从回收站恢复
        Route::post('product/batch_recover', [\app\adminapi\controller\product\CenterProductController::class, 'batchRecover'])->option(['real_name' => '批量从回收站恢复']);
        //保存新建或保存
        Route::post('product/:id', [\app\adminapi\controller\product\CenterProductController::class, 'save'])->option(['real_name' => '新建或修改商品']);
        //生成属性
        Route::post('generate_attr/:id/:type', [\app\adminapi\controller\product\CenterProductController::class, 'isFormatAttr'])->option(['real_name' => '生成商品规格列表']);
        //商品批量操作
        Route::post('batch/setting', [\app\adminapi\controller\product\CenterProductController::class, 'batchSetting'])->option(['real_name' => '商品批量设置', 'desc' => '批量设置商品参数']);
        //商品类型接口
        Route::get('product_type_config', [\app\adminapi\controller\product\CenterProductController::class, 'productTypeConfig'])->option(['real_name' => '商品类型接口']);
        //商品迁移导出
        Route::get('product_export', [\app\adminapi\controller\product\CenterProductController::class, 'productExport'])->option(['real_name' => '商品迁移导出']);
        //商品迁移导入
        Route::post('product_import', [\app\adminapi\controller\product\CenterProductController::class, 'productImport'])->option(['real_name' => '商品迁移导出']);
        //回收站商品彻底删除
        Route::delete('full_del/:id', [\app\adminapi\controller\product\CenterProductController::class, 'fullDel'])->option(['real_name' => '回收站商品彻底删除']);

        Route::get('other_info/:id/:type', [\app\adminapi\controller\product\CenterProductController::class, 'otherInfo'])->option(['real_name' => '商品其他信息']);
        Route::post('other_save/:id/:type', [\app\adminapi\controller\product\CenterProductController::class, 'otherSave'])->option(['real_name' => '修改商品其他信息']);

    })->option(['parent' => 'product', 'cate_name' => '商品']);

    /** 商品评论 */
    Route::group(function () {
        //评论列表
        Route::get('reply', [\app\adminapi\controller\product\StoreProductReplyController::class, 'index'])->option(['real_name' => '商品评论列表']);
        //回复评论
        Route::put('reply/set_reply/:id', [\app\adminapi\controller\product\StoreProductReplyController::class, 'setReply'])->option(['real_name' => '商品回复评论']);
        //删除评论
        Route::delete('reply/:id', [\app\adminapi\controller\product\StoreProductReplyController::class, 'delete'])->option(['real_name' => '删除商品评论']);
        //调起虚拟评论表单
        Route::get('reply/fictitious_reply/:product_id', [\app\adminapi\controller\product\StoreProductReplyController::class, 'fictitiousReply'])->option(['real_name' => '虚拟评论表单']);
        Route::post('reply/save_fictitious_reply', [\app\adminapi\controller\product\StoreProductReplyController::class, 'saveFictitiousReply'])->option(['real_name' => '保存虚拟评论']);
        //审核商品评论
        Route::put('reply/set_status/:id/:status', [\app\adminapi\controller\product\StoreProductReplyController::class, 'setStatus'])->option(['real_name' => '审核商品评论']);
        Route::post('reply/batch_set_status', [\app\adminapi\controller\product\StoreProductReplyController::class, 'batchSetStatus'])->option(['real_name' => '批量审核商品评论']);
    })->option(['parent' => 'product', 'cate_name' => '商品评论']);

    /** 商品采集 */
    Route::group(function () {
        //获取商品数据
        Route::post('crawl', [\app\adminapi\controller\product\CopyProductController::class, 'copyProduct'])->option(['real_name' => '获取采集商品数据']);
        //获取复制商品配置
        Route::get('copy_config', [\app\adminapi\controller\product\CopyProductController::class, 'getConfig'])->option(['real_name' => '获取复制商品配置']);
        //复制其他平台商品
        Route::post('copy', [\app\adminapi\controller\product\CopyProductController::class, 'copyProduct'])->option(['real_name' => '复制其他平台商品']);
    })->option(['parent' => 'product', 'cate_name' => '商品采集']);

    /** 商品标签 */
    Route::group(function () {
        //商品标签分类
        Route::get('label_cate/list', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelCateList'])->option(['real_name' => '商品标签分类']);
        Route::get('label_cate/form/:id', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelCateForm'])->option(['real_name' => '商品标签分类添加表单']);
        Route::post('label_cate/save/:id', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelCateSave'])->option(['real_name' => '商品标签分类保存']);
        Route::delete('label_cate/del/:id', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelCateDel'])->option(['real_name' => '商品标签分类删除']);
        Route::get('label/list', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelList'])->option(['real_name' => '商品标签列表']);
        Route::get('label/info/:id', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelInfo'])->option(['real_name' => '商品标签详情']);
        Route::post('label/save', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelSave'])->option(['real_name' => '商品标签保存']);
        Route::delete('label/del/:id', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelDel'])->option(['real_name' => '商品标签删除']);
        Route::put('label/status/:id/:status', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelStatus'])->option(['real_name' => '修改商品标签状态']);
        Route::put('label/is_show/:id/:is_show', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelIsShow'])->option(['real_name' => '修改商品标签显示']);
        Route::get('label/use_list', [\app\adminapi\controller\product\StoreProductLabelController::class, 'labelUseList'])->option(['real_name' => '使用商品标签列表']);
    })->option(['parent' => 'product', 'cate_name' => '商品标签']);

    /** 商品参数 */
    Route::group(function () {
        Route::get('param/list', [\app\adminapi\controller\product\StoreProductParamController::class, 'getParamList'])->option(['real_name' => '商品参数列表']);
        Route::get('param/info/:id', [\app\adminapi\controller\product\StoreProductParamController::class, 'getParamInfo'])->option(['real_name' => '商品参数详情']);
        Route::get('param/value/:id', [\app\adminapi\controller\product\StoreProductParamController::class, 'getParamValue'])->option(['real_name' => '商品参数值']);
        Route::post('param/save/:id', [\app\adminapi\controller\product\StoreProductParamController::class, 'saveParamData'])->option(['real_name' => '保存商品参数']);
        Route::put('param/status/:id/:status', [\app\adminapi\controller\product\StoreProductParamController::class, 'setParamStatus'])->option(['real_name' => '修改商品参数状态']);
        Route::delete('param/del/:id', [\app\adminapi\controller\product\StoreProductParamController::class, 'delParamData'])->option(['real_name' => '删除商品参数']);
    })->option(['parent' => 'product', 'cate_name' => '商品参数']);

    /** 商品保障 */
    Route::group(function () {
        Route::get('protection/list', [\app\adminapi\controller\product\StoreProductProtectionController::class, 'protectionList'])->option(['real_name' => '商品保障列表']);
        Route::get('protection/info/:id', [\app\adminapi\controller\product\StoreProductProtectionController::class, 'protectionInfo'])->option(['real_name' => '商品保障详情']);
        Route::get('protection/form/:id', [\app\adminapi\controller\product\StoreProductProtectionController::class, 'protectionForm'])->option(['real_name' => '商品保障表单']);
        Route::post('protection/save/:id', [\app\adminapi\controller\product\StoreProductProtectionController::class, 'protectionSave'])->option(['real_name' => '保存商品保障']);
        Route::put('protection/status/:id/:status', [\app\adminapi\controller\product\StoreProductProtectionController::class, 'protectionStatus'])->option(['real_name' => '修改商品保障状态']);
        Route::delete('protection/del/:id', [\app\adminapi\controller\product\StoreProductProtectionController::class, 'protectionDel'])->option(['real_name' => '删除商品保障']);
    })->option(['parent' => 'product', 'cate_name' => '商品保障']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'product', 'mark_name' => '商品管理']);
