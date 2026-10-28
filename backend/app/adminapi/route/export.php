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
 * 导出excel相关路由
 */
Route::group('export', function () {
    //用户列表
    Route::get('user_list', [\app\adminapi\controller\export\ExportExcelController::class, 'userList'])->option(['real_name' => '用户列表导出']);
    //订单列表
    Route::get('order_list', [\app\adminapi\controller\export\ExportExcelController::class, 'orderList'])->option(['real_name' => '订单列表导出']);
    //发货订单列表
    Route::get('order_delivery_list', [\app\adminapi\controller\export\ExportExcelController::class, 'orderDeliveryList'])->option(['real_name' => '发货订单列表导出']);
    //商品列表
    Route::get('product_list', [\app\adminapi\controller\export\ExportExcelController::class, 'productList'])->option(['real_name' => '商品列表导出']);
    //砍价列表
    Route::get('bargain_list', [\app\adminapi\controller\export\ExportExcelController::class, 'bargainList'])->option(['real_name' => '砍价商品列表导出']);
    //拼团列表
    Route::get('combination_list', [\app\adminapi\controller\export\ExportExcelController::class, 'combinationList'])->option(['real_name' => '拼团商品列表导出']);
    //秒杀列表
    Route::get('seckill_list', [\app\adminapi\controller\export\ExportExcelController::class, 'seckillList'])->option(['real_name' => '秒杀商品列表导出']);
    //导出会员卡
    Route::get('member_card/:id', [\app\adminapi\controller\export\ExportExcelController::class, 'memberCardList'])->option(['real_name' => '会员卡导出']);
    //分销用户推广列表
    Route::get('userAgent', [\app\adminapi\controller\export\ExportExcelController::class, 'userAgent'])->option(['real_name' => '分销员推广列表导出']);
    //用户资金监控
    Route::get('userFinance', [\app\adminapi\controller\export\ExportExcelController::class, 'userFinance'])->option(['real_name' => '用户资金导出']);
    //用户积分
    Route::get('userPoint', [\app\adminapi\controller\export\ExportExcelController::class, 'userPoint'])->option(['real_name' => '用户积分导出']);
    //用户充值
    Route::get('userRecharge', [\app\adminapi\controller\export\ExportExcelController::class, 'userRecharge'])->option(['real_name' => '用户充值导出']);
})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'export', 'mark_name' => '数据导出']);
