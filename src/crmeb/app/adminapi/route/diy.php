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
 * 分销管理 相关路由
 */
Route::group('diy', function () {

    //DIY列表
    Route::get('get_list', 'v1.diy.Diy/getList');
    //DIY列表
    Route::get('get_info/:id', 'v1.diy.Diy/getInfo');
    //删除DIY模板
    Route::delete('del/:id', 'v1.diy.Diy/del');
    //使用DIY模板
    Route::put('set_status/:id', 'v1.diy.Diy/setStatus');
    //保存DIY模板
    Route::post('save/[:id]', 'v1.diy.Diy/saveData');
    //获取路径
    Route::get('get_url','v1.diy.Diy/getUrl');
    //获取商品分类
    Route::get('get_category','v1.diy.Diy/getCategory');
    //获取商品
    Route::get('get_product','v1.diy.Diy/getProduct');
    //还原默认数据
    Route::get('recovery/:id','v1.diy.Diy/Recovery');
    //获取所有二级分类
    Route::get('get_by_category','v1.diy.Diy/getByCategory');

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCkeckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
]);
