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
 * 第三方驱动管理路由
 */
Route::group('driver', function () {

    /** 支付方式管理 */
    Route::group(function () {
        //支付方式列表
        Route::get('pay_channel/list', [\app\adminapi\controller\driver\pay\PayChannelController::class, 'index'])->option(['real_name' => '支付方式列表']);
        //获取支付驱动列表
        Route::get('pay_channel/drivers', [\app\adminapi\controller\driver\pay\PayChannelController::class, 'drivers'])->option(['real_name' => '获取支付驱动列表']);
        //批量排序
        Route::put('pay_channel/sort', [\app\adminapi\controller\driver\pay\PayChannelController::class, 'sort'])->option(['real_name' => '支付方式排序']);
        //修改支付方式状态
        Route::put('pay_channel/set_status/:id/:status', [\app\adminapi\controller\driver\pay\PayChannelController::class, 'setStatus'])->option(['real_name' => '修改支付方式状态']);
        //支付方式详情
        Route::get('pay_channel/:id', [\app\adminapi\controller\driver\pay\PayChannelController::class, 'read'])->option(['real_name' => '支付方式详情']);
        //修改支付方式
        Route::put('pay_channel/:id', [\app\adminapi\controller\driver\pay\PayChannelController::class, 'update'])->option(['real_name' => '修改支付方式']);
    })->option(['parent' => 'driver', 'cate_name' => '支付方式']);

    /** 发票驱动管理 */
    Route::group(function () {
        //驱动列表
        Route::get('invoice_driver/list', [\app\adminapi\controller\driver\invoice\InvoiceDriverController::class, 'index'])->option(['real_name' => '发票驱动列表']);
        //修改驱动状态
        Route::put('invoice_driver/set_status/:id', [\app\adminapi\controller\driver\invoice\InvoiceDriverController::class, 'status'])->option(['real_name' => '修改发票驱动状态']);
        //获取驱动详情
        Route::get('invoice_driver/detail/:id', [\app\adminapi\controller\driver\invoice\InvoiceDriverController::class, 'detail'])->option(['real_name' => '获取发票驱动详情']);
        //获取驱动配置字段
        Route::get('invoice_driver/config_fields/:driverKey', [\app\adminapi\controller\driver\invoice\InvoiceDriverController::class, 'configFields'])->option(['real_name' => '获取发票驱动配置字段']);
        //获取所有已扫描的驱动
        Route::get('invoice_driver/drivers', [\app\adminapi\controller\driver\invoice\InvoiceDriverController::class, 'drivers'])->option(['real_name' => '获取发票驱动列表']);
        //获取所有启用的驱动
        Route::get('invoice_driver/enabled', [\app\adminapi\controller\driver\invoice\InvoiceDriverController::class, 'enabled'])->option(['real_name' => '获取启用的发票驱动列表']);
        //保存驱动配置
        Route::post('invoice_driver/save_config/:id', [\app\adminapi\controller\driver\invoice\InvoiceDriverController::class, 'saveConfig'])->option(['real_name' => '保存发票驱动配置']);
        //批量排序
        Route::put('invoice_driver/sort', [\app\adminapi\controller\driver\invoice\InvoiceDriverController::class, 'sort'])->option(['real_name' => '发票驱动排序']);
    })->option(['parent' => 'driver', 'cate_name' => '发票驱动']);

    /** 存储驱动管理 */
    Route::group(function () {
        //驱动列表
        Route::get('upload_driver/list', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'index'])->option(['real_name' => '存储驱动列表']);
        // 同步已安装存储驱动的声明，不删除仅被禁用的驱动。
        Route::post('upload_driver/sync', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'sync'])->option(['real_name' => '同步存储驱动']);
        // 获取上传能力声明。
        Route::get('upload_driver/capabilities/:driver_key', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'capabilities'])->option(['real_name' => '获取存储驱动能力']);
        //修改驱动状态
        Route::put('upload_driver/set_status/:id', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'status'])->option(['real_name' => '修改驱动状态']);
        //获取驱动详情
        Route::get('upload_driver/detail/:id', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'detail'])->option(['real_name' => '获取驱动详情']);
        //获取驱动配置字段
        Route::get('upload_driver/config_fields/:driver_key', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'configFields'])->option(['real_name' => '获取驱动配置字段']);
        //获取驱动存储空间配置字段
        Route::get('upload_driver/bucket_fields/:driver_key', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'bucketFields'])->option(['real_name' => '获取驱动存储空间配置字段']);
        //获取所有启用的驱动
        Route::get('upload_driver/enabled', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'enabled'])->option(['real_name' => '获取启用的驱动列表']);
        //获取上传公共配置
        Route::get('upload_driver/common_config/:driver_key', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'commonConfig'])->option(['real_name' => '获取上传公共配置']);
        //保存上传公共配置
        Route::post('upload_driver/save_common_config/:driver_key', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'saveCommonConfig'])->option(['real_name' => '保存上传公共配置']);
        //保存驱动配置
        Route::post('upload_driver/save_config/:id', [\app\adminapi\controller\driver\upload\UploadDriverController::class, 'saveConfig'])->option(['real_name' => '保存驱动配置']);
    })->option(['parent' => 'driver', 'cate_name' => '存储驱动']);

    /** 采集驱动管理 */
    Route::group(function () {
        Route::get('copy_driver/list', [\app\adminapi\controller\driver\copyproduct\CopyDriverController::class, 'index'])->option(['real_name' => '采集驱动列表']);
        Route::put('copy_driver/set_status/:id', [\app\adminapi\controller\driver\copyproduct\CopyDriverController::class, 'status'])->option(['real_name' => '修改驱动状态']);
        Route::get('copy_driver/detail/:id', [\app\adminapi\controller\driver\copyproduct\CopyDriverController::class, 'detail'])->option(['real_name' => '获取驱动详情']);
        Route::post('copy_driver/save_config/:id', [\app\adminapi\controller\driver\copyproduct\CopyDriverController::class, 'saveConfig'])->option(['real_name' => '保存驱动配置']);
    })->option(['parent' => 'driver', 'cate_name' => '采集驱动']);

    /** 物流查询驱动管理 */
    Route::group(function () {
        //驱动列表
        Route::get('logistics_driver/list', [\app\adminapi\controller\driver\logistics\LogisticsDriverController::class, 'index'])->option(['real_name' => '物流查询驱动列表']);
        //修改驱动状态
        Route::put('logistics_driver/set_status/:id', [\app\adminapi\controller\driver\logistics\LogisticsDriverController::class, 'status'])->option(['real_name' => '修改物流驱动状态']);
        //获取驱动详情
        Route::get('logistics_driver/detail/:id', [\app\adminapi\controller\driver\logistics\LogisticsDriverController::class, 'detail'])->option(['real_name' => '获取物流驱动详情']);
        //获取驱动配置字段
        Route::get('logistics_driver/config_fields/:driverKey', [\app\adminapi\controller\driver\logistics\LogisticsDriverController::class, 'configFields'])->option(['real_name' => '获取物流驱动配置字段']);
        //获取所有启用的驱动
        Route::get('logistics_driver/enabled', [\app\adminapi\controller\driver\logistics\LogisticsDriverController::class, 'enabled'])->option(['real_name' => '获取启用的物流驱动列表']);
        //保存驱动配置
        Route::post('logistics_driver/save_config/:id', [\app\adminapi\controller\driver\logistics\LogisticsDriverController::class, 'saveConfig'])->option(['real_name' => '保存物流驱动配置']);
    })->option(['parent' => 'driver', 'cate_name' => '物流查询驱动']);

    /** 电子面单驱动管理 */
    Route::group(function () {
        //驱动列表
        Route::get('express_driver/list', [\app\adminapi\controller\driver\express\ExpressDriverController::class, 'index'])->option(['real_name' => '电子面单驱动列表']);
        //修改驱动状态
        Route::put('express_driver/set_status/:id', [\app\adminapi\controller\driver\express\ExpressDriverController::class, 'status'])->option(['real_name' => '修改电子面单驱动状态']);
        //获取驱动详情
        Route::get('express_driver/detail/:id', [\app\adminapi\controller\driver\express\ExpressDriverController::class, 'detail'])->option(['real_name' => '获取电子面单驱动详情']);
        //获取驱动配置字段
        Route::get('express_driver/config_fields/:driverKey', [\app\adminapi\controller\driver\express\ExpressDriverController::class, 'configFields'])->option(['real_name' => '获取电子面单驱动配置字段']);
        //获取所有已扫描的驱动
        Route::get('express_driver/drivers', [\app\adminapi\controller\driver\express\ExpressDriverController::class, 'drivers'])->option(['real_name' => '获取电子面单驱动列表']);
        //获取所有启用的驱动
        Route::get('express_driver/enabled', [\app\adminapi\controller\driver\express\ExpressDriverController::class, 'enabled'])->option(['real_name' => '获取启用的电子面单驱动列表']);
        //保存驱动配置
        Route::post('express_driver/save_config/:id', [\app\adminapi\controller\driver\express\ExpressDriverController::class, 'saveConfig'])->option(['real_name' => '保存电子面单驱动配置']);
        //批量排序
        Route::put('express_driver/sort', [\app\adminapi\controller\driver\express\ExpressDriverController::class, 'sort'])->option(['real_name' => '电子面单驱动排序']);
    })->option(['parent' => 'driver', 'cate_name' => '电子面单驱动']);

    /** 电子面单物流公司管理 */
    Route::group(function () {
        //物流公司列表
        Route::get('express_company/list', [\app\adminapi\controller\driver\express\ExpressCompanyController::class, 'index'])->option(['real_name' => '电子面单物流公司列表']);
        //物流公司详情
        Route::get('express_company/read/:id', [\app\adminapi\controller\driver\express\ExpressCompanyController::class, 'read'])->option(['real_name' => '电子面单物流公司详情']);
        //新增物流公司
        Route::post('express_company/save', [\app\adminapi\controller\driver\express\ExpressCompanyController::class, 'save'])->option(['real_name' => '新增电子面单物流公司']);
        //修改物流公司
        Route::put('express_company/update/:id', [\app\adminapi\controller\driver\express\ExpressCompanyController::class, 'update'])->option(['real_name' => '修改电子面单物流公司']);
        //删除物流公司
        Route::delete('express_company/delete/:id', [\app\adminapi\controller\driver\express\ExpressCompanyController::class, 'delete'])->option(['real_name' => '删除电子面单物流公司']);
        //修改状态
        Route::put('express_company/set_status/:id/:status', [\app\adminapi\controller\driver\express\ExpressCompanyController::class, 'setStatus'])->option(['real_name' => '修改电子面单物流公司状态']);
        //修改展示
        Route::put('express_company/set_is_show/:id/:isShow', [\app\adminapi\controller\driver\express\ExpressCompanyController::class, 'setIsShow'])->option(['real_name' => '修改电子面单物流公司展示']);
        //排序
        Route::put('express_company/sort', [\app\adminapi\controller\driver\express\ExpressCompanyController::class, 'sort'])->option(['real_name' => '电子面单物流公司排序']);
        //获取驱动列表下拉
        Route::get('express_company/driver_options', [\app\adminapi\controller\driver\express\ExpressCompanyController::class, 'driverOptions'])->option(['real_name' => '获取电子面单驱动列表下拉']);
    })->option(['parent' => 'driver', 'cate_name' => '电子面单物流公司']);

    /** 电子面单管理 */
    Route::group(function () {
        //模板列表
        Route::get('express_template/list', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'index'])->option(['real_name' => '电子面单模板列表']);
        //同步模板
        Route::post('express_template/sync', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'sync'])->option(['real_name' => '同步电子面单模板']);
        //模板详情
        Route::get('express_template/detail/:id', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'detail'])->option(['real_name' => '电子面单模板详情']);
        //修改模板状态
        Route::put('express_template/set_status/:id', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'status'])->option(['real_name' => '修改电子面单模板状态']);
        //更新模板
        Route::put('express_template/:id', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'update'])->option(['real_name' => '更新电子面单模板']);
        //删除模板
        Route::delete('express_template/:id', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'delete'])->option(['real_name' => '删除电子面单模板']);
        //获取电子面单配置
        Route::get('express_template/config', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'getConfig'])->option(['real_name' => '获取电子面单配置']);
        //保存电子面单配置
        Route::post('express_template/config', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'saveConfig'])->option(['real_name' => '保存电子面单配置']);
        //检查电子面单是否可用
        Route::get('express_template/check', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'checkAvailable'])->option(['real_name' => '检查电子面单可用性']);
        //打印记录列表
        Route::get('express_template/dump_log/list', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'dumpLogList'])->option(['real_name' => '电子面单打印记录列表']);
        //打印记录详情
        Route::get('express_template/dump_log/:id', [\app\adminapi\controller\driver\express\ExpressTemplateController::class, 'dumpLogDetail'])->option(['real_name' => '电子面单打印记录详情']);
    })->option(['parent' => 'driver', 'cate_name' => '电子面单']);

    /** 打印机驱动管理 */
    Route::group(function () {
        Route::get('printer_driver/list', [\app\adminapi\controller\driver\printer\PrinterDriverController::class, 'index'])->option(['real_name' => '打印机驱动列表']);
        Route::post('printer_driver/sync', [\app\adminapi\controller\driver\printer\PrinterDriverController::class, 'sync'])->option(['real_name' => '同步打印机驱动']);
        Route::get('printer_driver/detail/:id', [\app\adminapi\controller\driver\printer\PrinterDriverController::class, 'detail'])->option(['real_name' => '打印机驱动详情']);
        Route::put('printer_driver/set_status/:id', [\app\adminapi\controller\driver\printer\PrinterDriverController::class, 'status'])->option(['real_name' => '修改打印机驱动状态']);
        Route::post('printer_driver/save_config/:id', [\app\adminapi\controller\driver\printer\PrinterDriverController::class, 'saveConfig'])->option(['real_name' => '保存打印机驱动配置']);
    })->option(['parent' => 'driver', 'cate_name' => '打印机驱动管理']);

    /** 打印机渠道管理 */
    Route::group(function () {
        //打印机列表
        Route::get('printer_channel/list', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'index'])->option(['real_name' => '打印机列表']);
        //获取打印机驱动列表
        Route::get('printer_channel/drivers', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'drivers'])->option(['real_name' => '获取打印机驱动列表']);
        //新增打印机
        Route::post('printer_channel/save', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'save'])->option(['real_name' => '新增打印机']);
        //批量排序
        Route::put('printer_channel/sort', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'sort'])->option(['real_name' => '打印机排序']);
        //修改打印机状态
        Route::put('printer_channel/set_status/:id/:status', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'setStatus'])->option(['real_name' => '修改打印机状态']);
        //删除打印机
        Route::delete('printer_channel/del/:id', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'delete'])->option(['real_name' => '删除打印机']);
        //获取小票打印内容配置
        Route::get('printer_channel/content/:id', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'content'])->option(['real_name' => '获取小票打印内容配置']);
        //保存小票打印内容配置
        Route::post('printer_channel/save_content/:id', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'saveContent'])->option(['real_name' => '保存小票打印内容配置']);
        //打印机详情
        Route::get('printer_channel/:id', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'read'])->option(['real_name' => '打印机详情']);
        //修改打印机
        Route::put('printer_channel/:id', [\app\adminapi\controller\driver\printer\PrinterChannelController::class, 'update'])->option(['real_name' => '修改打印机']);

        //小票打印内容配置（保留旧版，基于 printer_channel）
        Route::get('ticket/content/:id', [\app\adminapi\controller\driver\printer\SystemTicketController::class, 'ticketContent'])->option(['real_name' => '获取小票打印详情']);
        Route::post('ticket/save_content/:id', [\app\adminapi\controller\driver\printer\SystemTicketController::class, 'ticketContentSave'])->option(['real_name' => '保存小票打印详情']);
    })->option(['parent' => 'driver', 'cate_name' => '打印机管理']);

    /** 短信服务管理 */
    Route::group(function () {
        //短信驱动列表
        Route::get('sms_driver/list', [\app\adminapi\controller\driver\sms\SmsDriverController::class, 'index'])->option(['real_name' => '短信驱动列表']);
        //修改驱动状态
        Route::put('sms_driver/set_status/:id', [\app\adminapi\controller\driver\sms\SmsDriverController::class, 'status'])->option(['real_name' => '修改短信驱动状态']);
        //获取驱动详情
        Route::get('sms_driver/detail/:id', [\app\adminapi\controller\driver\sms\SmsDriverController::class, 'detail'])->option(['real_name' => '获取短信驱动详情']);
        //获取驱动配置字段
        Route::get('sms_driver/config_fields/:driver_key', [\app\adminapi\controller\driver\sms\SmsDriverController::class, 'configFields'])->option(['real_name' => '获取短信驱动配置字段']);
        //获取所有已扫描的驱动
        Route::get('sms_driver/drivers', [\app\adminapi\controller\driver\sms\SmsDriverController::class, 'drivers'])->option(['real_name' => '获取短信驱动列表']);
        //获取所有启用的驱动
        Route::get('sms_driver/enabled', [\app\adminapi\controller\driver\sms\SmsDriverController::class, 'enabled'])->option(['real_name' => '获取启用的短信驱动列表']);
        //保存驱动配置
        Route::post('sms_driver/save_config/:id', [\app\adminapi\controller\driver\sms\SmsDriverController::class, 'saveConfig'])->option(['real_name' => '保存短信驱动配置']);
        //批量排序
        Route::put('sms_driver/sort', [\app\adminapi\controller\driver\sms\SmsDriverController::class, 'sort'])->option(['real_name' => '短信驱动排序']);
    })->option(['parent' => 'driver', 'cate_name' => '短信服务']);

    /** 邮件服务管理 */
    Route::group(function () {
        Route::get('email_driver/list', [\app\adminapi\controller\driver\email\EmailDriverController::class, 'index'])->option(['real_name' => '邮件驱动列表']);
        Route::put('email_driver/set_status/:id', [\app\adminapi\controller\driver\email\EmailDriverController::class, 'status'])->option(['real_name' => '修改邮件驱动状态']);
        Route::get('email_driver/detail/:id', [\app\adminapi\controller\driver\email\EmailDriverController::class, 'detail'])->option(['real_name' => '获取邮件驱动详情']);
        Route::get('email_driver/config_fields/:driver_key', [\app\adminapi\controller\driver\email\EmailDriverController::class, 'configFields'])->option(['real_name' => '获取邮件驱动配置字段']);
        Route::get('email_driver/drivers', [\app\adminapi\controller\driver\email\EmailDriverController::class, 'drivers'])->option(['real_name' => '获取邮件驱动列表']);
        Route::get('email_driver/enabled', [\app\adminapi\controller\driver\email\EmailDriverController::class, 'enabled'])->option(['real_name' => '获取启用的邮件驱动列表']);
        Route::post('email_driver/save_config/:id', [\app\adminapi\controller\driver\email\EmailDriverController::class, 'saveConfig'])->option(['real_name' => '保存邮件驱动配置']);
        Route::put('email_driver/sort', [\app\adminapi\controller\driver\email\EmailDriverController::class, 'sort'])->option(['real_name' => '邮件驱动排序']);
    })->option(['parent' => 'driver', 'cate_name' => '邮件服务']);

    /** 机器人通知管理 */
    Route::group(function () {
        Route::get('bot_driver/list', [\app\adminapi\controller\driver\bot\BotDriverController::class, 'index'])->option(['real_name' => '机器人驱动列表']);
        Route::put('bot_driver/set_status/:id', [\app\adminapi\controller\driver\bot\BotDriverController::class, 'status'])->option(['real_name' => '修改机器人驱动状态']);
        Route::get('bot_driver/detail/:id', [\app\adminapi\controller\driver\bot\BotDriverController::class, 'detail'])->option(['real_name' => '获取机器人驱动详情']);
        Route::get('bot_driver/config_fields/:driver_key', [\app\adminapi\controller\driver\bot\BotDriverController::class, 'configFields'])->option(['real_name' => '获取机器人驱动配置字段']);
        Route::get('bot_driver/drivers', [\app\adminapi\controller\driver\bot\BotDriverController::class, 'drivers'])->option(['real_name' => '获取机器人驱动列表']);
        Route::get('bot_driver/enabled', [\app\adminapi\controller\driver\bot\BotDriverController::class, 'enabled'])->option(['real_name' => '获取启用的机器人驱动列表']);
        Route::post('bot_driver/save_config/:id', [\app\adminapi\controller\driver\bot\BotDriverController::class, 'saveConfig'])->option(['real_name' => '保存机器人驱动配置']);
        Route::put('bot_driver/sort', [\app\adminapi\controller\driver\bot\BotDriverController::class, 'sort'])->option(['real_name' => '机器人驱动排序']);
    })->option(['parent' => 'driver', 'cate_name' => '机器人通知']);

})->middleware([
    \app\http\middleware\AllowOriginMiddleware::class,
    \app\adminapi\middleware\AdminAuthTokenMiddleware::class,
    \app\adminapi\middleware\AdminCheckRoleMiddleware::class,
    \app\adminapi\middleware\AdminLogMiddleware::class
])->option(['mark' => 'driver', 'mark_name' => '驱动管理']);
