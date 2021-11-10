// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from '@/libs/request'

/**
 * @description 订单管理--列表
 * @param {Object} param data {Object} 传值参数
 */
export function orderList (data) {
    return request({
        url: '/order/list',
        method: 'get',
        params: data
    })
};

/**
 * @description 发票头部统计
 * @param {Object} param data {Object} 传值参数
 */
export function orderInvoiceChart () {
    return request({
        url: 'order/invoice/chart',
        method: 'get'
    })
};

/**
 * @description 发票头部统计
 * @param {Object} param data {Object} 传值参数
 */
export function orderInvoiceList (data) {
    return request({
        url: 'order/invoice/list',
        method: 'get',
        params: data
    })
};

/**
 * @description 发票提交订单
 * @param {Object} param data {Object} 传值参数
 */
export function orderInvoiceSet (id,data) {
    return request({
        url: `order/invoice/set/${id}`,
        method: 'post',
        data
    })
};

/**
 * @description 发票订单详情；
 * @param {Object} param data {Object} 传值参数
 */
export function orderInvoiceInfo (id) {
    return request({
        url: `order/invoice_order_info/${id}`,
        method: 'get'
    })
};

/**
 * @description 订单数据--列表
 * @param {Object} param data {Object} 传值参数
 */
export function getOrdes (data) {
    return request({
        url: '/order/chart',
        method: 'get',
        params: data
    })
};

/**
 * @description 订单表单编辑数据
 * @param {Number} param id {Number} 订单id
 */
export function getOrdeDatas (id) {
    return request({
        url: `/order/edit/${id}`,
        method: 'get'
    })
};

/**
 * @description 订单表单详情数据
 * @param {Number} param id {Number} 订单id
 */
export function getDataInfo (id) {
    return request({
        url: `/order/info/${id}`,
        method: 'get'
    })
};

/**
 * @description 修改备注信息
 * @param {Number} param data.id {Number} 订单id
 * @param {String} param data.remark {String} 备注信息
 */
export function putRemarkData (data) {
    return request({
        url: `/order/remark/${data.id}`,
        method: 'put',
        data: data.remark
    })
};

/**
 * @description 获取订单记录
 * @param {Number} param data.id {Number} 订单id
 * @param {String} param data.datas {String} 分页参数
 */
export function getOrderRecord (data) {
    return request({
        url: `/order/status/${data.id}`,
        method: 'get',
        params: data.datas
    })
};

/**
 * @description 获取退款表单数据
 * @param {Number} param id {Number} 订单id
 */
export function getRefundFrom (id) {
    return request({
        url: `/order/refund/${id}`,
        method: 'get'
    })
};

/**
 * @description 获取快递公司
 */
export function getExpressData (status) {
    return request({
        url: `/order/express_list?status=`+status,
        method: 'get'
    })
};

/**
 * @description 获取不退款表单数据
 * @param {Number} param id {Number} 订单id
 */
export function getnoRefund (id) {
    return request({
        url: `/order/no_refund/${id}`,
        method: 'get'
    })
};

/**
 * @description 发送货提交表单
 * @param {Number} param data.id {Number} 订单id
 * @param {Object} param data.datas {Object} 表单信息
 */
export function putDelivery (data) {
    return request({
        url: `/order/delivery/${data.id}`,
        method: 'put',
        data: data.datas
    })
};

export function orderSheetInfo () {
    return request({
        url: '/order/sheet_info',
        method: 'get'
    });
}

/**
 * 所有配送员列表
 */
export function deliveryList () {
    return request({
        url: '/order/delivery/index',
        method: 'get'
    });
}

/**
 * 订单时获取所有配送员列表
 */
export function orderDeliveryList () {
    return request({
        url: '/order/delivery/list',
        method: 'get'
    });
}

/**
 * 列表修改账号状态
 * @param {*} data data
 */
export function orderDeliveryStatus (data) {
    return request({
        url: `/order/delivery/set_status/${data.id}/${data.status}`,
        method: 'get'
    });
}

/**
 * 编辑配送员表单
 * @param {*} id id
 */
export function orderDeliveryEdit (id) {
    return request({
        url: `/order/delivery/${id}/edit`,
        method: 'get'
    });
}

/**
 * 新增配送员表单
 */
export function orderDeliveryAdd () {
    return request({
        url: '/order/delivery/add',
        method: 'get'
    });
}

/**
 * 电子面单模板
 * @param {com} data 快递公司编号
 */
export function orderExpressTemp (data) {
    return request({
        url: '/order/express/temp',
        method: 'get',
        params: data
    });
}

/**
 * @description 子订单列表---拆单
 * @param {Object} param data {Object} 传值参数
 */
 export function splitOrderList(id) {
    return request({
        url: `order/split_order/${id}`,
        method: 'get'
    })
};

/**
 * @description 获取订单可拆分商品列表
 * @param {Object} param data {Object} 传值参数
 */
 export function splitCartInfo(id) {
    return request({
        url: `order/split_cart_info/${id}`,
        method: 'get'
    })
};

/**
 * @description 拆单发送货
 * @param {Number} param data.id {Number} 订单id
 * @param {Object} param data.datas {Object} 表单信息
 */
 export function splitDelivery(data) {
    return request({
        url: `/order/split_delivery/${data.id}`,
        method: 'put',
        data: data.datas
    });
};

/**
 * @description 获取退积分表单
 * @param {Number} param id {Number} 订单id
 */
export function refundIntegral (id) {
    return request({
        url: `/order/refund_integral/${id}`,
        method: 'get'
    })
};

/**
 * @description 立即支付
 * @param {String} param path {String} 请求地址
 * @param {String} param method {String} 请求方式
 */
export function payOffline (path, method) {
    return request({
        url: path,
        method: method
    })
};

/**
 * @description 配送信息表单
 * @param {Number} param id {Number} 订单id
 */
export function getDistribution (id) {
    return request({
        url: `/order/distribution/${id}`,
        method: 'get'
    })
};

/**
 * @description 订单物流信息
 * @param {Number} param id {Number} 订单id
 */
export function getExpress (id) {
    return request({
        url: `/order/express/${id}`,
        method: 'get'
    })
};

/**
 * @description  订单核销
 * @param {String} param data {String} 核销内容
 */
export function putWrite (data) {
    return request({
        url: '/order/write',
        method: 'post',
        data: data
    })
}

/**
 * @description 订单管理 -- 导出
 */
export function storeOrderApi (data) {
    return request({
        url: `export/storeOrder`,
        method: 'get',
        params: data
    })
}

/**
 * @description 核销单个订单
 */
export function writeUpdate (order_id) {
    return request({
        url: `order/write_update/${order_id}`,
        method: 'put',
    })
}

/**
 * 收银订单
 */
export function orderScanList (data) {
    return request({
        url: 'order/scan_list',
        method: 'get',
        params: data
    })
}

/**
 * 线下收款码
 */
export function orderOfflineScan (id) {
    return request({
        url: 'order/offline_scan',
        method: 'get',
        params: id
    })
}

/**
 * @description 售后订单
 * @param {Object} param data {Object} 传值参数
 */
 export function orderRefundList(data) {
    return request({
        url: 'refund/list',
        method: 'get',
        params: data
    })
};

/**
 * @description 批量发货记录
 * @param {Object} param data {Object} 传值参数
 */
 export function queueIndex(data) {
    return request({
        url: 'queue/index',
        method: 'get',
        params: data
    })
};

/**
 * @description 批量发货-手动
 * @param {Object} param data {Object} 传值参数
 */
 export function handBatchDelivery(data) {
    return request({
        url: 'order/hand/batch_delivery',
        method: 'get',
        params: data
    })
};
/**
 * @description 下载
 * @param {Object} param data {Object} 传值参数
 */
 export function batchOrderDelivery(id, type, catchType) {
    return request({
        url: `export/batchOrderDelivery/${id}/${type}/${catchType}`,
        method: 'get'
    })
};
/**
 * @description 积分商城订单 -- 导出
 */
 export function storeIntegralOrder(data) {
    return request({
        url: `export/storeIntegralOrder`,
        method: 'get',
        params: data
    });
}

/**
 * @description 任务列表-查看
 * @param {Object} param data {Object} 传值参数
 */
 export function deliveryLog(id, type, data) {
    return request({
        url: `queue/delivery/log/${id}/${type}`,
        method: 'get',
        params: data
    })
};

/**
 * @description 下载物流公司对照表
 * @param {Object} param data {Object} 传值参数
 */
 export function exportExpressList(id) {
    return request({
        url: 'export/expressList',
        method: 'get'
    })
};



/**
 * @description 批量发货-自动
 * @param {Object} param data {Object} 传值参数
 */
 export function otherBatchDelivery(data) {
    return request({
        url: 'order/other/batch_delivery',
        method: 'post',
        data
    })
};
/**
 * @description 重新执行
 * @param {Object} param data {Object} 传值参数
 */
 export function queueAgain(id, type) {
    return request({
        url: `queue/again/do_queue/${id}/${type}`,
        method: 'get'
    })
};
/**
 * @description 清除异常任务
 * @param {Object} param data {Object} 传值参数
 */
 export function queueDel(id, type) {
    return request({
        url: `queue/del/wrong_queue/${id}/${type}`,
        method: 'get'
    })
};

/**
 * @description 停止任务
 * @param {Object} param data {Object} 传值参数
 */
 export function stopWrongQueue(id) {
    return request({
        url: `queue/stop/wrong_queue/${id}`,
        method: 'get'
    })
};