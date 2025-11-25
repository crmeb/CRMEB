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

/**
 * 分销管理 相关路由
 */
Route::group('agent', function () {

    /** 分销员管理 */
    Route::group(function () {
        //推销员列表
        Route::get('index', 'v1.agent.AgentManage/index')->option(['real_name' => '分销员列表']);
        //修改上级推广人
        Route::put('spread', 'v1.agent.AgentManage/editSpread')->option(['real_name' => '修改上级推广人']);
        //头部统计
        Route::get('statistics', 'v1.agent.AgentManage/get_badge')->option(['real_name' => '分销员列表头部统计']);
        //推广人列表
        Route::get('stair', 'v1.agent.AgentManage/get_stair_list')->option(['real_name' => '推广人列表']);
        //统计推广订单列表
        Route::get('stair/order', 'v1.agent.AgentManage/get_stair_order_list')->option(['real_name' => '推广订单列表']);
        //清除上级推广人
        Route::put('stair/delete_spread/:uid', 'v1.agent.AgentManage/delete_spread')->option(['real_name' => '清除上级推广人']);
        //取消推广资格
        Route::put('stair/delete_system_spread/:uid', 'v1.agent.AgentManage/delete_system_spread')->option(['real_name' => '取消推广资格']);
        //查看公众号推广二维码
        Route::get('look_code', 'v1.agent.AgentManage/look_code')->option(['real_name' => '查看公众号推广二维码']);
        //查看小程序推广二维码
        Route::get('look_xcx_code', 'v1.agent.AgentManage/look_xcx_code')->option(['real_name' => '查看小程序推广二维码']);
        //查看H5推广二维码
        Route::get('look_h5_code', 'v1.agent.AgentManage/look_h5_code')->option(['real_name' => '查看H5推广二维码']);
    })->option(['parent' => 'agent', 'cate_name' => '分销员管理']);

    /** 分销设置 */
    Route::group(function () {
        //分销配置编辑表单
        Route::get('config/edit_basics', 'v1.setting.SystemConfig/edit_basics')->option(['real_name' => '积分配置编辑表单']);
        //分销配置保存数据
        Route::post('config/save_basics', 'v1.setting.SystemConfig/save_basics')->option(['real_name' => '积分配置保存数据']);
    })->option(['parent' => 'agent', 'cate_name' => '分销设置']);

    /** 分销等级 */
    Route::group(function () {
        //分销员等级资源路由
        Route::resource('level', 'v1.agent.AgentLevel')->except(['read'])->name('AgentLevel')->option([
            'real_name' => [
                'index' => '获取分销员等级列表',
                'create' => '获取分销员等级表单',
                'save' => '保存分销员等级',
                'edit' => '获取修改分销员等级表单',
                'update' => '修改分销员等级',
                'delete' => '删除分销员等级'
            ]
        ]);
        //修改分销等级状态
        Route::put('level/set_status/:id/:status', 'v1.agent.AgentLevel/set_status')->name('levelSetStatus')->option(['real_name' => '修改分销等级状态']);
        //分销员等级任务资源路由
        Route::resource('level_task', 'v1.agent.AgentLevelTask')->except(['read'])->option([
            'real_name' => [
                'index' => '获取分销员等级任务列表',
                'create' => '获取分销员等级任务表单',
                'save' => '保存分销员等级任务',
                'edit' => '获取修改分销员等级任务表单',
                'update' => '修改分销员等级任务',
                'delete' => '删除分销员等级任务'
            ]
        ]);
        //修改分销任务状态
        Route::put('level_task/set_status/:id/:status', 'v1.agent.AgentLevelTask/set_status')->name('levelTaskSetStatus')->option(['real_name' => '修改分销等级任务状态']);
        //获取赠送分销等级表单
        Route::get('get_level_form', 'v1.agent.AgentManage/getLevelForm')->name('getLevelForm')->option(['real_name' => '获取赠送分销等级表单']);
        //赠送分销等级
        Route::post('give_level', 'v1.agent.AgentManage/giveAgentLevel')->name('giveAgentLevel')->option(['real_name' => '赠送分销等级']);
        //设置任务完成数量表单
        Route::get('get_task_num_form/:id', 'v1.agent.AgentLevel/getTaskNumForm')->name('getTaskNumForm')->option(['real_name' => '获取任务完成数量表单']);
        //设置完成任务数量
        Route::post('set_task_num/:id', 'v1.agent.AgentLevel/setTaskNum')->name('setTaskNum')->option(['real_name' => '设置完成任务数量']);
    })->option(['parent' => 'agent', 'cate_name' => '分销等级']);

    /** 事业部 */
    Route::group(function () {
        Route::get('division/list', 'v1.agent.Division/divisionList')->name('divisionList')->option(['real_name' => '事业部列表']);//事业部/代理商/员工列表
        Route::get('division/down_list', 'v1.agent.Division/divisionDownList')->name('divisionDownList')->option(['real_name' => '下级列表']);//下级列表
        Route::get('division/create/:uid', 'v1.agent.Division/divisionCreate')->name('divisionCreate')->option(['real_name' => '添加事业部']);//添加事业部
        Route::post('division/save', 'v1.agent.Division/divisionSave')->name('divisionSave')->option(['real_name' => '事业部保存']);//事业部保存
        Route::get('division/agent/create/:uid', 'v1.agent.Division/divisionAgentCreate')->name('divisionAgentCreate')->option(['real_name' => '添加事业部']);//添加代理商
        Route::post('division/agent/save', 'v1.agent.Division/divisionAgentSave')->name('divisionAgentSave')->option(['real_name' => '事业部保存']);//代理商保存
        Route::put('division/set_status/:status/:uid', 'v1.agent.Division/setDivisionStatus')->name('setDivisionStatus')->option(['real_name' => '状态切换']);//状态切换
        Route::delete('division/del/:type/:uid', 'v1.agent.Division/delDivision')->name('delDivision')->option(['real_name' => '删除代理商']);//状态切换
        Route::get('division/staff/create/:uid', 'v1.agent.Division/divisionStaffCreate')->name('divisionStaffCreate')->option(['real_name' => '添加事业部']);//添加代理商
        Route::post('division/staff/save', 'v1.agent.Division/divisionStaffSave')->name('divisionStaffSave')->option(['real_name' => '事业部保存']);//代理商保存
        Route::get('division/agent_apply/list', 'v1.agent.Division/AdminApplyList')->name('AdminApplyList')->option(['real_name' => '代理商申请列表']);//代理商申请列表
        Route::get('division/examine_apply/:id/:type', 'v1.agent.Division/examineApply')->name('examineApply')->option(['real_name' => '审核表单']);//审核表单
        Route::post('division/apply_agent/save', 'v1.agent.Division/applyAgentSave')->name('applyAgentSave')->option(['real_name' => '提交审核']);//提交审核
        Route::delete('division/del_apply/:id', 'v1.agent.Division/delApply')->name('delApply')->option(['real_name' => '删除审核']);//删除审核
        Route::get('division/statistics', 'v1.agent.Division/divisionStatistics')->name('divisionStatistics')->option(['real_name' => '事业部统计']);//事业部统计
    })->option(['parent' => 'agent', 'cate_name' => '事业部']);

    /** 分销员申请 */
    Route::group(function () {
        Route::get('spread/apply/list', 'v1.agent.SpreadApply/applyList')->name('applyList')->option(['real_name' => '分销员申请列表']);
        Route::post('spread/apply/examine/:id/:uid/:status', 'v1.agent.SpreadApply/applyExamine')->name('applyExamine')->option(['real_name' => '分销员审核']);
        Route::delete('spread/apply/del/:id', 'v1.agent.SpreadApply/applyDelete')->name('applyDelete')->option(['real_name' => '删除分销员申请']);
    })->option(['parent' => 'agent', 'cate_name' => '分销员申请']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'agent', 'mark_name' => '分销模块']);
