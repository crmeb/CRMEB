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
 * 用户模块 相关路由
 */
Route::group('user', function () {

    /** 用户 */
    Route::group(function () {
        //用户管理资源路由（控制器未提供 delete，显式排除）
        Route::resource('user', \app\adminapi\controller\user\UserController::class)->except(['delete'])->option([
            'real_name' => [
                'index' => '获取用户列表',
                'create' => '获取用户表单',
                'save' => '保存用户',
                'read' => '获取用户详情',
                'edit' => '获取修改用户表单',
                'update' => '修改用户'
            ]
        ]);
        //添加用户保存 无控制器方法
        // Route::post('user/save', [\app\adminapi\controller\user\UserController::class, 'saveInfo'])->option(['real_name' => '添加用户']);
        //同步微信用户
        Route::get('user/syncUsers', [\app\adminapi\controller\user\UserController::class, 'syncWechatUsers'])->option(['real_name' => '同步微信用户']);
        //用户信息
        Route::get('user/user_save_info/:uid', [\app\adminapi\controller\user\UserController::class, 'userSaveInfo'])->option(['real_name' => '添加编辑用户信息时候的信息']);
        //赠送会员等级
        Route::get('give_level/:id', [\app\adminapi\controller\user\UserController::class, 'giveLevel'])->option(['real_name' => '赠送用户等级']);
        //执行赠送会员等级
        Route::put('save_give_level/:id', [\app\adminapi\controller\user\UserController::class, 'saveGiveLevel'])->option(['real_name' => '执行赠送用户等级']);
        //赠送付费会员时长
        Route::get('give_level_time/:id', [\app\adminapi\controller\user\UserController::class, 'giveLevelTime'])->option(['real_name' => '赠送付费会员时长']);
        //执行赠送付费会员时长
        Route::put('save_give_level_time/:id', [\app\adminapi\controller\user\UserController::class, 'saveGiveLevelTime'])->option(['real_name' => '执行赠送付费会员时长']);
        //清除会员等级
        Route::delete('del_level/:id', [\app\adminapi\controller\user\UserController::class, 'delLevel'])->option(['real_name' => '清除用户等级']);
        //编辑其他
        Route::get('edit_other/:id/:type', [\app\adminapi\controller\user\UserController::class, 'editOther'])->option(['real_name' => '修改积分余额表单']);
        //编辑其他
        Route::put('update_other/:id', [\app\adminapi\controller\user\UserController::class, 'updateOther'])->option(['real_name' => '修改积分余额']);
        //修改用户状态 无控制器方法
        // Route::put('set_status/:status/:id', [\app\adminapi\controller\user\UserController::class, 'setStatus'])->option(['real_name' => '修改用户状态']);
        //获取指定用户的信息
        Route::get('one_info/:id', [\app\adminapi\controller\user\UserController::class, 'oneUserInfo'])->option(['real_name' => '获取指定用户的信息']);
        //设置会员分组
        Route::post('set_group', [\app\adminapi\controller\user\UserController::class, 'setGroup'])->option(['real_name' => '用户分组表单']);
        //执行设置会员分组
        Route::put('save_set_group', [\app\adminapi\controller\user\UserController::class, 'saveSetGroup'])->option(['real_name' => '设置用户分组']);
        //设置会员标签
        Route::post('set_label', [\app\adminapi\controller\user\UserController::class, 'setLabel'])->option(['real_name' => '设置用户标签']);
    })->option(['parent' => 'user', 'cate_name' => '用户']);

    /** 用户等级 */
    Route::group(function () {
        //获取添加会员等级表单
        Route::get('user_level/create', [\app\adminapi\controller\user\UserLevelController::class, 'create'])->option(['real_name' => '添加用户等级表单']);
        //添加或修改会员等级
        Route::post('user_level', [\app\adminapi\controller\user\UserLevelController::class, 'save'])->option(['real_name' => '添加或修改用户等级']);
        //获取系统设置的vip列表
        Route::get('user_level/vip_list', [\app\adminapi\controller\user\UserLevelController::class, 'getSystemVipList'])->option(['real_name' => '获取系统设置的用户等级列表']);
        //删除会员等级
        Route::put('user_level/delete/:id', [\app\adminapi\controller\user\UserLevelController::class, 'delete'])->option(['real_name' => '删除用户等级']);
        //设置单个商品上架|下架
        Route::put('user_level/set_show/:id/:is_show', [\app\adminapi\controller\user\UserLevelController::class, 'setShow'])->option(['real_name' => '设置用户等级上下架']);
        //等级列表快速编辑
        Route::put('user_level/set_value/:id', [\app\adminapi\controller\user\UserLevelController::class, 'setValue'])->option(['real_name' => '用户等级列表快速编辑']);
        //等级详情 无控制器方法
        // Route::get('user_level/read/:id', [\app\adminapi\controller\user\UserLevelController::class, 'read'])->option(['real_name' => '用户等级详情']);
        //等级任务列表 无控制器方法
        // Route::get('user_level/task/:level_id', [\app\adminapi\controller\user\UserLevelController::class, 'getTaskList'])->option(['real_name' => '用户等级任务列表']);
        //快速编辑等级任务 无控制器方法
        // Route::put('user_level/set_task/:id', [\app\adminapi\controller\user\UserLevelController::class, 'setTaskValue'])->option(['real_name' => '快速编辑用户等级任务']);
        //设置等级任务显示|隐藏 无控制器方法
        // Route::put('user_level/set_task_show/:id/:is_show', [\app\adminapi\controller\user\UserLevelController::class, 'setTaskShow'])->option(['real_name' => '设置用户等级任务显示|隐藏']);
        //设置是否务必达成 无控制器方法
        // Route::put('user_level/set_task_must/:id/:is_must', [\app\adminapi\controller\user\UserLevelController::class, 'setTaskMust'])->option(['real_name' => '设置用户等级任务是否务必达成']);
        //添加等级任务表单 无控制器方法
        // Route::get('user_level/create_task', [\app\adminapi\controller\user\UserLevelController::class, 'createTask'])->option(['real_name' => '添加用户等级任务表单']);
        //保存或修改等级任务 无控制器方法
        // Route::post('user_level/save_task', [\app\adminapi\controller\user\UserLevelController::class, 'saveTask'])->option(['real_name' => '保存或修改用户等级任务']);
        //删除等级任务 无控制器方法
        // Route::delete('user_level/delete_task/:id', [\app\adminapi\controller\user\UserLevelController::class, 'deleteTask'])->option(['real_name' => '删除用户等级任务']);
    })->option(['parent' => 'user', 'cate_name' => '用户等级']);

    /** 用户分组 */
    Route::group(function () {
        //获取用户分组列表
        Route::get('user_group/list', [\app\adminapi\controller\user\UserGroupController::class, 'index'])->option(['real_name' => '获取用户分组列表']);
        //添加修改分组表单
        Route::get('user_group/add/:id', [\app\adminapi\controller\user\UserGroupController::class, 'add'])->option(['real_name' => '添加修改分组表单']);
        //保存分组表单数据
        Route::post('user_group/save', [\app\adminapi\controller\user\UserGroupController::class, 'save'])->option(['real_name' => '保存分组表单数据']);
        //删除分组数据
        Route::delete('user_group/del/:id', [\app\adminapi\controller\user\UserGroupController::class, 'delete'])->option(['real_name' => '删除用户分组数据']);
    })->option(['parent' => 'user', 'cate_name' => '用户分组']);

    /** 用户标签 */
    Route::group(function () {
        //会员标签列表
        Route::get('user_label', [\app\adminapi\controller\user\UserLabelController::class, 'index'])->option(['real_name' => '用户标签列表']);
        //会员标签添加修改表单
        Route::get('user_label/add/:id', [\app\adminapi\controller\user\UserLabelController::class, 'add'])->option(['real_name' => '添加或修改用户标签表单']);
        //保存标签表单数据
        Route::post('user_label/save', [\app\adminapi\controller\user\UserLabelController::class, 'save'])->option(['real_name' => '添加或修改用户标签']);
        //删除会员标签
        Route::delete('user_label/del/:id', [\app\adminapi\controller\user\UserLabelController::class, 'delete'])->option(['real_name' => '删除用户标签']);
        //获取用户标签
        Route::get('label/:uid', [\app\adminapi\controller\user\UserLabelController::class, 'getUserLabel'])->option(['real_name' => '获取用户标签']);
        //设置和取消用户标签
        Route::post('label/:uid', [\app\adminapi\controller\user\UserLabelController::class, 'setUserLabel'])->option(['real_name' => '设置和取消用户标签']);
        //设置会员分组
        Route::put('save_set_label', [\app\adminapi\controller\user\UserController::class, 'saveSetLabel'])->option(['real_name' => '保存用户标签']);
        //标签分类
        Route::resource('user_label_cate', \app\adminapi\controller\user\UserLabelCateController::class)->except(['read'])->option([
            'real_name' => [
                'index' => '获取标签分类',
                'create' => '获取标签分类表单',
                'save' => '保存标签分类',
                'edit' => '获取修改标签分类表单',
                'update' => '修改标签分类',
                'delete' => '删除标签分类'
            ]
        ]);
        Route::get('user_label_cate/all', [\app\adminapi\controller\user\UserLabelCateController::class, 'getAll'])->option(['real_name' => '获取用户标签分类全部']);
        //用户标签（分类）树形列表
        Route::get('user_tree_label', [\app\adminapi\controller\user\UserLabelController::class, 'treeList'])->option(['real_name' => '用户标签（分类）树形列表']);
    })->option(['parent' => 'user', 'cate_name' => '用户标签']);

    /** 付费会员 */
    Route::group(function () {
        //会员卡批次列表资源
        Route::get('member_batch/index', [\app\adminapi\controller\user\member\MemberCardBatchController::class, 'index'])->option(['real_name' => '会员卡批次列表']);
        //添加会员卡批次
        Route::post('member_batch/save/:id', [\app\adminapi\controller\user\member\MemberCardBatchController::class, 'save'])->option(['real_name' => '添加会员卡批次']);
        //会员卡列表
        Route::get('member_card/index/:card_batch_id', [\app\adminapi\controller\user\member\MemberCardController::class, 'index'])->option(['real_name' => '会员卡列表']);
        //会员卡修改状态
        Route::get('member_card/set_status', [\app\adminapi\controller\user\member\MemberCardController::class, 'setStatus'])->option(['real_name' => '会员卡修改状态']);
        //列表单字段修改操作
        Route::get('member_batch/set_value/:id', [\app\adminapi\controller\user\member\MemberCardBatchController::class, 'setValue'])->option(['real_name' => '会员卡批次快速修改']);
        //会员类型
        Route::get('member/ship', [\app\adminapi\controller\user\member\MemberCardController::class, 'memberShip'])->option(['real_name' => '会员类型列表']);
        //会员类型删除
        Route::delete('member_ship/delete/:id', [\app\adminapi\controller\user\member\MemberCardController::class, 'delete'])->option(['real_name' => '会员类型删除']);
        //会员类型修改状态
        Route::get('member_ship/set_ship_status', [\app\adminapi\controller\user\member\MemberCardController::class, 'setShipStatus'])->option(['real_name' => '会员类型修改状态']);
        //会员卡类型编辑
        Route::post('member_ship/save/:id', [\app\adminapi\controller\user\member\MemberCardController::class, 'shipSave'])->option(['real_name' => '会员卡类型编辑']);
        //兑换会员卡二维码
        Route::get('member_scan', [\app\adminapi\controller\user\member\MemberCardBatchController::class, 'memberScan'])->option(['real_name' => '兑换会员卡二维码']);
        //会员记录
        Route::get('member/record', [\app\adminapi\controller\user\member\MemberCardController::class, 'memberRecord'])->option(['real_name' => '会员记录']);
        Route::get('member/right', [\app\adminapi\controller\user\member\MemberCardController::class, 'memberRight'])->option(['real_name' => '会员权益列表']);
        Route::post('member_right/save/:id', [\app\adminapi\controller\user\member\MemberCardController::class, 'rightSave'])->option(['real_name' => '会员权益修改']);
        Route::post('member_agreement/save/:id', [\app\adminapi\controller\user\member\MemberCardBatchController::class, 'saveMemberAgreement'])->option(['real_name' => '会员协议']);
        //获取会员协议
        Route::get('member/agreement', [\app\adminapi\controller\user\member\MemberCardBatchController::class, 'getAgreement'])->option(['real_name' => '获取会员协议']);
        //获取线下付款二维码
        Route::get('offline_scan', [\app\adminapi\controller\user\member\OfflineReceiptController::class, 'offlineScan'])->option(['real_name' => '获取线下付款二维码']);
        //线下收银列表
        Route::get('scan_list', [\app\adminapi\controller\user\member\OfflineReceiptController::class, 'scanList'])->option(['real_name' => '线下收银列表']);
    })->option(['parent' => 'user', 'cate_name' => '付费会员']);


    /** 用户注销 */
    Route::group(function () {
        Route::get('cancel_list', [\app\adminapi\controller\user\UserCancelController::class, 'getCancelList'])->option(['real_name' => '用户注销列表']);
        Route::post('cancel/set_mark', [\app\adminapi\controller\user\UserCancelController::class, 'setMark'])->option(['real_name' => '注销列表备注']);
        Route::get('cancel/agree/:id', [\app\adminapi\controller\user\UserCancelController::class, 'agreeCancel'])->option(['real_name' => '同意注销']);
        Route::get('cancel/refuse/:id', [\app\adminapi\controller\user\UserCancelController::class, 'refuseCancel'])->option(['real_name' => '拒绝注销']);
    })->option(['parent' => 'user', 'cate_name' => '用户注销']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'user', 'mark_name' => '用户管理']);
