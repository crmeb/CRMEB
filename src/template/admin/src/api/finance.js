import request from '@/libs/request'

/**
 * @description 资金监控 -- 筛选类型
 */
export function billTypeApi () {
    return request({
        url: 'finance/finance/bill_type',
        method: 'get'
    })
};

/**
 * @description 资金监控 -- 列表
 * @param {Object} param data {Object} 传值
 */
export function billListApi (data) {
    return request({
        url: 'finance/finance/list',
        method: 'get',
        params: data
    })
};




/**
 * @description 财务记录 -- 用户资金导出
 * @param {Number} param data {Number} 请求参数data
 */
export function userFinanceApi (data) {
    return request({
        url: `export/userFinance`,
        method: 'get',
        params: data
    })
};

/**
 * @description 佣金记录 -- 用户佣金导出
 * @param {Number} param data {Number} 请求参数data
 */
export function userCommissionApi (data) {
    return request({
        url: `export/userCommission`,
        method: 'get',
        params: data
    })
}
