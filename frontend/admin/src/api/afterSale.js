import request from '@/libs/request';

/**
 * 售后单列表
 * 支持按售后单号/订单号/状态/类型/产品类型/订单大类/时间范围筛选
 * @param {Object} data 查询参数
 */
export function afterSaleList(data) {
  return request({
    url: '/after_sale/list',
    method: 'get',
    params: data,
  });
}

/**
 * 售后单详情
 * 含时间线、审核信息、管理备注、用户可操作按钮
 * @param {Number|String} id 售后单主键
 */
export function afterSaleDetail(id) {
  return request({
    url: `/after_sale/detail/${id}`,
    method: 'get',
  });
}

/**
 * 审核售后
 * 商家同意或拒绝售后申请，拒绝时需填写拒绝原因
 * @param {Number|String} id 售后单主键
 * @param {Object} data { result: 'pass'|'reject', reject_reason: '', audit_remark: '' }
 */
export function auditAfterSale(id, data) {
  return request({
    url: `/after_sale/audit/${id}`,
    method: 'post',
    data,
  });
}

/**
 * 商家确认收货
 * 用户退货寄回后，商家确认收到退货商品，按售后类型分流后续流程
 * @param {Number|String} id 售后单主键
 */
export function receiveAfterSale(id) {
  return request({
    url: `/after_sale/receive/${id}`,
    method: 'post',
  });
}

/**
 * 执行退款
 * 仅退款审核通过后直接退款，或退货退款确认收货后退款
 * @param {Number|String} id 售后单主键
 * @param {Object} data { amount: Number, refund_type: 'original'|'balance', refund_trade_no: '' }
 */
export function refundAfterSale(id, data) {
  return request({
    url: `/after_sale/refund/${id}`,
    method: 'post',
    data,
  });
}

/**
 * 商家补发商品
 * 换货/补发/维修完成后商家寄出商品，填写补发物流单号
 * @param {Number|String} id 售后单主键
 * @param {Object} data { delivery_type: '', delivery_no: '' }
 */
export function resendAfterSale(id, data) {
  return request({
    url: `/after_sale/resend/${id}`,
    method: 'post',
    data,
  });
}

/**
 * 开始维修
 * 维修类型售后，商家确认收到维修件后开始维修
 * @param {Number|String} id 售后单主键
 */
export function startRepairAfterSale(id) {
  return request({
    url: `/after_sale/start_repair/${id}`,
    method: 'post',
  });
}

/**
 * 维修完成
 * 维修完成后商家寄回商品，进入补发流程
 * @param {Number|String} id 售后单主键
 * @param {Object} data { repair_remark: '' }
 */
export function repairCompleteAfterSale(id, data) {
  return request({
    url: `/after_sale/repair_complete/${id}`,
    method: 'post',
    data,
  });
}

/**
 * 更新管理备注
 * @param {Number|String} id 售后单主键
 * @param {Object} data { remark: '' }
 */
export function remarkAfterSale(id, data) {
  return request({
    url: `/after_sale/remark/${id}`,
    method: 'put',
    data,
  });
}

/**
 * 查询售后物流进度
 * 支持退货物流与补发物流两种类型查询，返回快递公司、运单号及物流轨迹
 * @param {Number|String} id 售后单主键
 * @param {String} type 物流类型 return=退货物流 resend=补发物流
 */
export function getAfterSaleExpress(id, type = 'return') {
  return request({
    url: `/after_sale/express/${id}`,
    method: 'get',
    params: { type },
  });
}
