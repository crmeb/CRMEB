import request from '@/libs/request'

/**
 * @description 设置 系统设置 应用设置头部
 * @param {Object} param data {Object} 传值参数 type类型
 */
export function headerListApi (data) {
    return request({
        url: 'setting/config/header_basics',
        method: 'get',
        params: data
    })
}

/**
 * @description 设置 系统设置 应用设置 编辑表单
 * @param {Object} param data {Object} 传值参数 type类型
 */
export function dataFromApi (data,url) {
    return request({
        url: url,
        method: 'get',
        params: data
    })
}

/**
 * @description 设置 短信设置 列表
 * @param {Object} param params {Object} 传值参数
 */
export function tempListApi (params) {
    return request({
        url: params.url,
        method: 'get',
        params: params.data
    })
}

/**
 * @description 设置 短信设置 申请模板表单
 * @param {Object} param data {Object} 传值参数 type类型
 */
export function tempCreateApi () {
    return request({
        url: 'notify/sms/temp/create',
        method: 'get'
    })
}

/**
 * @description 设置 短信设置 登录
 * @param {Object} param data {Object} 传值参数
 */
export function configApi (data) {
    return request({
        url: 'serve/login',
        method: 'post',
        data
    });
}

/**
 * @description 设置 短信 修改密码
 */
export function serveModifyApi (data) {
    return request({
        url: 'serve/modify',
        method: 'post',
        data
    });
}

/**
 * @description 设置 短信 修改手机号
 */
export function updateHoneApi (data) {
    return request({
        url: 'serve/update_phone',
        method: 'post',
        data
    });
}

/**
 * @description 设置 短信设置 修改账户密码
 * @param {Object} param data {Object} 传值参数
 */
// export function configApi (data) {
//     return request({
//         url: 'notify/sms/config',
//         method: 'post',
//         data
//     });
// }

/**
 * @description 设置 短信设置 发送验证码
 * @param {Object} param data {Object} 传值参数
 */
export function captchaApi (data) {
    return request({
        url: 'serve/captcha',
        method: 'post',
        data
    });
}
/**
 * @description 验证验证码
 * @param {Object} param data {Object} 传值参数
 */
export function checkCaptchaApi (data) {
    return request({
        url: 'serve/checkCode',
        method: 'post',
        data
    });
}

/**
 * @description 设置 短信设置 注册
 * @param {Object} param data {Object} 传值参数
 */
export function registerApi (data) {
    return request({
        url: 'serve/register',
        method: 'post',
        data
    });
}

/**
 * @description 设置 短信设置 短信剩余条数
 */
export function smsNumberApi () {
    return request({
        url: 'notify/sms/number',
        method: 'get'
    })
}

/**
 * @description 设置 短信设置 平台用户信息
 */
export function serveInfoApi () {
    return request({
        url: 'serve/info',
        method: 'get'
    });
}

/**
 * @description 修改短信签名
 */
export function serveSign (data) {
    return request({
        url: 'serve/sms/sign',
        method: 'PUT',
        data
    });
}

/**
 * 客服登录
 */
export function kefuLogin (id) {
    return request({
        url: `app/wechat/kefu/login/${id}`,
        method: 'get'
    });
}

/**
 * 客服话术列表
 */
export function wechatSpeechcraft (data) {
    return request({
        url: `app/wechat/speechcraft`,
        method: 'get',
        params: data
    });
}

/**
 * 客服话术编辑
 */
export function speechcraftEdit (id) {
    return request({
        url: `app/wechat/speechcraft/${id}/edit`,
        method: 'get'
    });
}

/**
 * 客服话术添加
 */
export function speechcraftCreate () {
    return request({
        url: `app/wechat/speechcraft/create`,
        method: 'get'
    });
}

/**
 * 客服反馈
 */
export function kefuFeedBack (params) {
    return request({
        url: `app/feedback`,
        method: 'get',
        params
    });
}

/**
 * 客服反馈
 */
export function kefuFeedBackEdit (id) {
    return request({
        url: `app/feedback/${id}/edit`,
        method: 'get'
    });
}

/**
 * @description 设置 短信 物流公司
 */
export function exportAllApi () {
    return request({
        url: 'serve/export_all',
        method: 'get'
    });
}

/**
 * 是否开通电子面单
 */
// export function serveDumpOpen () {
//     return request({
//         url: `serve/dump_open`,
//         method: 'get'
//     });
// }

/**
 * 物流开通
 */
export function serveOpen () {
    return request({
        url: `serve/open`,
        method: 'get'
    });
}

/**
 * @description 设置 短信 物流公司面板
 */
export function exportTempApi (params) {
    return request({
        url: 'serve/export_temp',
        method: 'get',
        params
    });
}

/**
 * @description 设置 短信 2= 电子面单，3 = 物流查询 列表
 */
export function serveRecordListApi (params) {
    return request({
        url: 'serve/record',
        method: 'get',
        params
    });
}

/**
 * @description 设置 短信 开通其他服务
 */
export function serveOpnOtherApi (params) {
    return request({
        url: 'serve/open',
        method: 'get',
        params
    });
}

/**
 * @description 设置 短信 开通电子面单
 */
export function serveOpnExpressApi (data) {
    return request({
        url: 'serve/opn_express',
        method: 'post',
        data
    });
}

/**
 * @description 设置 短信 开通短信服务
 */
export function serveSmsOpenApi (params) {
    return request({
        url: 'serve/sms/open',
        method: 'get',
        params
    });
}

/**
 * @description 设置 短信设置 支付套餐
 */
export function smsPriceApi (params) {
    return request({
        url: 'serve/meal_list',
        method: 'get',
        params
    });
}

/**
 * @description 设置 短信设置 支付码
 * @param {Object} param data {Object} 传值参数
 */
export function payCodeApi (data) {
    return request({
        url: 'serve/pay_meal',
        method: 'post',
        data
    });
}

/**
 * @description 设置 短信设置 发送记录
 */
export function smsRecordApi (params) {
    return request({
        url: 'notify/sms/record',
        method: 'get',
        params
    })
}

/**
 * @description 门店设置 详情
 */
export function storeApi () {
    return request({
        url: 'merchant/store',
        method: 'GET'
    })
}

/**
 * @description 门店设置 获取地图key
 */
export function keyApi () {
    return request({
        url: 'merchant/store/address',
        method: 'GET'
    })
}

/**
 * @description 门店设置 提交数据,
 * @param {Object} param data {Object} 传值参数
 */
export function storeAddApi (data) {
    return request({
        url: `merchant/store/${data.id}`,
        method: 'POST',
        data
    })
}

/**
 * @description 设置 物流公司 列表
 * @param {Object} param data {Object} 传值参数
 */
export function freightListApi (params) {
    return request({
        url: 'freight/express',
        method: 'get',
        params
    })
}

/**
 * @description 设置 物流公司 新增表单
 */
export function freightCreateApi () {
    return request({
        url: '/freight/express/create',
        method: 'get'
    })
}

/**
 * @description 设置 物流公司 编辑表单
 * @param {Number} param id {Number} 物流公司id
 */
export function freightEditApi (id) {
    return request({
        url: `freight/express/${id}/edit`,
        method: 'get'
    })
}

/**
 * @description 设置 物流公司 修改状态
 * @param {Number} param id {Number} 物流公司id
 */
export function freightStatusApi (data) {
    return request({
        url: `freight/express/set_status/${data.id}/${data.status}`,
        method: 'PUT'
    })
}

/**
 * @description 同步物流快递公司
 */
export function freightSyncExpressApi () {
    return request({
        url: `freight/express/sync_express`,
        method: 'get'
    });
}

/**
 * @description 话术分类
 */
export function speechcraftcate () {
    return request({
        url: `app/wechat/speechcraftcate`,
        method: 'get'
    });
}

/**
 * @description 获取创建分类表单
 */
export function speechcraftcateCreate() {
    return request({
        url: `app/wechat/speechcraftcate/create`,
        method: 'get'
    });
}

/**
 * @description 修改话术分类(获取表单)
 */
export function speechcraftcateEdit(id) {
    return request({
        url: `app/wechat/speechcraftcate/${id}/edit`,
        method: 'get'
    });
}

/**
 * @description 设置 身份管理 列表
 * @param {Number} param id {Number} 物流公司id
 */
export function roleListApi (params) {
    return request({
        url: `setting/role`,
        method: 'GET',
        params
    })
}

/**
 * @description 设置 身份管理 修改状态
 * @param {Object} param data {Object} 传值参数
 */
export function roleSetStatusApi (data) {
    return request({
        url: `setting/role/set_status/${data.id}/${data.status}`,
        method: 'PUT'
    })
}

/**
 * @description 设置 身份管理 ==新增 编辑
 * @param {Object} param data {Object} 传值参数
 */
export function roleCreatApi (data) {
    return request({
        url: `setting/role/${data.id}`,
        method: 'post',
        data
    })
}

/**
 * @description 设置 身份管理 ==详情
 * @param {Number} param id {Number} 身份管理id
 */
export function roleInfoApi (id) {
    return request({
        url: `setting/role/${id}/edit`,
        method: 'get'
    })
}

/**
 * @description 设置 身份管理 ==权限列表
 */
export function menusListApi () {
    return request({
        url: `setting/role/create`,
        method: 'get'
    })
}

/**
 * @description 设置 客服管理 -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function kefuListApi (params) {
    return request({
        url: `app/wechat/kefu`,
        method: 'get',
        params
    })
}

/**
 * @description 设置 客服管理 -- 选择用户
 *  @param {Object} param params {Object} 传值参数
 */
export function kefucreateApi (params) {
    return request({
        url: `app/wechat/kefu/create`,
        method: 'get',
        params
    })
}

/**
 * @description 设置 客服管理 -- 添加客服
 *  @param {Object} param params {Object} 传值参数
 */
export function kefuaddApi () {
    return request({
        url: `app/wechat/kefu/add`,
        method: 'get'
    })
}

/**
 * @description 设置 客服管理 -- 添加客服保存
 *  @param {Object} param params {Object} 传值参数
 */
export function kefuAddApi (data) {
    return request({
        url: `app/wechat/kefu`,
        method: 'post',
        data
    })
}

/**
 * @description 设置 客服管理 -- 修改状态
 *  @param {Object} param data {Object} 传值参数
 */
export function kefusetStatusApi (data) {
    return request({
        url: `app/wechat/kefu/set_status/${data.id}/${data.status}`,
        method: 'PUT'
    })
}

/**
 * @description 设置 客服管理 -- 编辑表单
 *  @param {Number} param id {Number} 客服id
 */
export function kefuEditApi (id) {
    return request({
        url: `app/wechat/kefu/${id}/edit`,
        method: 'GET'
    })
}

/**
 * @description 设置 客服管理 -- 聊天记录列表
 *  @param {Number} param id {Number} 客服id
 *  @param {Object} param params {Object} 传参数
 */
export function kefuRecordApi (params, id) {
    return request({
        url: `app/wechat/kefu/record/${id}`,
        method: 'GET',
        params
    })
}

/**
 * @description 设置 客服管理 -- 查看对话列表
 *  @param {Object} param params {Object} 传参数
 */
export function kefuChatlistApi (params) {
    return request({
        url: `app/wechat/kefu/chat_list`,
        method: 'GET',
        params
    })
}

/**
 * @description 短信设置 -- 查看是否登录
 */
export function isLoginApi () {
    return request({
        url: `notify/sms/is_login`,
        method: 'GET'
    })
}

/**
 * @description 短信设置 -- 退出登录
 */
export function logoutApi () {
    return request({
        url: `notify/sms/logout`,
        method: 'GET'
    })
}

/**
 * @description 设置 城市数据 -- 列表
 *  @param {Object} param data {Object} 传值参数
 */
export function cityListApi (id) {
    return request({
        url: `setting/city/list/${id}`,
        method: 'get'
    })
}

/**
 * @description 设置 城市添加 -- 表单
 *  @param {Object} param data {Object} 传值参数
 */
export function cityAddApi (id) {
    return request({
        url: `setting/city/add/${id}`,
        method: 'get'
    })
}

/**
 * @description 设置 城市修改 -- 表单
 *  @param {Object} param data {Object} 传值参数
 */
export function cityApi (id) {
    return request({
        url: `setting/city/${id}/edit`,
        method: 'get'
    })
}

/**
 * @description 设置 运费模板 -- 列表
 *  @param {Object} param data {Object} 传值参数
 */
export function templatesApi (data) {
    return request({
        url: `setting/shipping_templates/list`,
        method: 'get',
        params: data
    })
}

/**
 * @description 设置 运费模板 -- 城市数据
 */
export function templatesCityListApi (data) {
    return request({
        url: `setting/shipping_templates/city_list`,
        method: 'get'
    })
}

/**
 * @description 设置 运费模板 -- 提交修改表单；
 */
export function templatesSaveApi (id, data) {
    return request({
        url: `setting/shipping_templates/save/${id}`,
        method: 'post',
        data
    })
}

/**
 * @description 设置 运费模板 -- 提交修改表单；
 */
export function shipTemplatesApi (id) {
    return request({
        url: `setting/shipping_templates/${id}/edit`,
        method: 'get'
    })
}

/**
 * @description 门店设置 -- 门店列表分类数量；
 */
export function storeGetHeaderApi () {
    return request({
        url: `merchant/store/get_header`,
        method: 'get'
    })
}

/**
 * @description 门店设置 -- 门店列表；
 */
export function merchantStoreApi (data) {
    return request({
        url: `merchant/store`,
        method: 'get',
        params: data
    })
}

/**
 * @description 门店设置 -- 门店设置；
 */
export function storeSetShowApi (id, is_show) {
    return request({
        url: `merchant/store/set_show/${id}/${is_show}`,
        method: 'put'
    })
}

/**
 * @description 门店设置 -- 门店修改信息；
 */
export function storeGetInfoApi (id) {
    return request({
        url: `merchant/store/get_info/${id}`,
        method: 'get'
    })
}

/**
 * @description 门店设置 -- 店员列表；
 */
export function storeStaffApi (data) {
    return request({
        url: `merchant/store_staff`,
        method: 'get',
        params: data
    })
}

/**
 * @description 门店设置 -- 新增店员；
 */
export function storeStaffCreateApi () {
    return request({
        url: `merchant/store_staff/create`,
        method: 'get'
    })
}

/**
 * @description 门店设置 -- 新增店员；
 */
export function storeStaffEditApi (id) {
    return request({
        url: `merchant/store_staff/${id}/edit`,
        method: 'get'
    })
}

/**
 * @description 店员设置 -- 店员设置显示隐藏；
 */
export function storeStaffSetShowApi (id, is_show) {
    return request({
        url: `merchant/store_staff/set_show/${id}/${is_show}`,
        method: 'put'
    })
}

/**
 * @description 订单设置 -- 核销订单列表；
 */
export function verifyOrderApi (data) {
    return request({
        url: `merchant/verify_order`,
        method: 'get',
        params: data
    })
}

/**
 * @description 订单设置 -- 核销订单头部；
 */
export function verifySpreadInfoApi (uid) {
    return request({
        url: `merchant/verify/spread_info/${uid}`,
        method: 'get'
    })
}

/**
 * 获取店员搜索门店列表
 */
export function merchantStoreListApi () {
    return request({
        url: `merchant/store_list`,
        method: 'get'
    })
}

/**
 * 清除城市数据缓存
 */
export function cityCleanCacheApi () {
    return request({
        url: `setting/city/clean_cache`,
        method: 'get'
    })
}
