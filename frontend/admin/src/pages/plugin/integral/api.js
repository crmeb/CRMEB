/**
 * 积分商城 API
 */
import request from '@/libs/request';

/**
 * 积分商品列表
 */
export function integralProductListApi(params) {
  return request({
    url: 'marketing/integral_product',
    method: 'GET',
    params,
  });
}

/**
 * 积分商品保存（新增/编辑）
 */
export function integralAddApi(data) {
  return request({
    url: `marketing/integral/${data.id}`,
    method: 'post',
    data,
  });
}

/**
 * 积分商品详情
 */
export function integralInfoApi(id) {
  return request({
    url: `marketing/integral/${id}`,
    method: 'GET',
  });
}

/**
 * 积分商品修改状态
 */
export function integralIsShowApi(data) {
  return request({
    url: `marketing/integral/set_show/${data.id}/${data.is_show}`,
    method: 'put',
  });
}

/**
 * 积分商品删除
 */
export function integralDelApi(id) {
  return request({
    url: `marketing/integral/${id}`,
    method: 'DELETE',
  });
}

/**
 * 获取商品规格
 */
export function productAttrsApi(id, type) {
  return request({
    url: `product/product/attrs/${id}/${type}`,
    method: 'GET',
  });
}

/**
 * 积分订单列表
 */
export function integralOrderList(params) {
  return request({
    url: 'marketing/integral/order/list',
    method: 'get',
    params,
  });
}

/**
 * 积分订单统计数据（头部tab）
 */
export function integralGetOrdes(data) {
  return request({
    url: 'marketing/integral/order/chart',
    method: 'get',
    params: data,
  });
}

/**
 * 积分订单详情
 */
export function getIntegralOrderDataInfo(id) {
  return request({
    url: `marketing/integral/order/info/${id}`,
    method: 'get',
  });
}

/**
 * 积分订单物流
 */
export function getExpress(id) {
  return request({
    url: `marketing/integral/order/express/${id}`,
    method: 'get',
  });
}

/**
 * 获取快递公司
 */
export function getExpressData(status) {
  return request({
    url: `marketing/integral/order/express_list?status=` + status,
    method: 'get',
  });
}

/**
 * 积分订单配送信息表单
 */
export function getIntegralOrderDistribution(id) {
  return request({
    url: `marketing/integral/order/distribution/${id}`,
    method: 'get',
  });
}

/**
 * 积分订单发货
 */
export function integralOrderPutDelivery(data) {
  return request({
    url: `marketing/integral/order/delivery/${data.id}`,
    method: 'put',
    data: data.datas,
  });
}

/**
 * 积分订单修改配送信息
 */
export function integralOrderPutDistribution(data) {
  return request({
    url: `marketing/integral/order/distribution/${data.id}`,
    method: 'put',
    data: data.datas,
  });
}

/**
 * 积分订单备注
 */
export function integralOrderPutRemarkData(data) {
  return request({
    url: `marketing/integral/order/remark/${data.id}`,
    method: 'put',
    data: { remark: data.remark },
  });
}

/**
 * 积分订单状态记录
 */
export function getIntegralOrderRecord(data) {
  return request({
    url: `marketing/integral/order/status/${data.id}`,
    method: 'get',
  });
}

/**
 * 获取配送员列表
 */
export function orderDeliveryList() {
  return request({
    url: 'marketing/integral/order/delivery/list',
    method: 'get',
  });
}

/**
 * 电子面单模板
 */
export function orderExpressTemp(data) {
  return request({
    url: 'marketing/integral/order/express/temp',
    method: 'get',
    params: data,
  });
}

/**
 * 积分面单配置
 */
export function orderSheetInfo() {
  return request({
    url: 'marketing/integral/order/sheet_info',
    method: 'get',
  });
}

/**
 * 积分订单确认收货
 */
export function integralOrderTake(id) {
  return request({
    url: `marketing/integral/order/take/${id}`,
    method: 'put',
  });
}

/**
 * 积分订单删除
 */
export function integralOrderDel(id) {
  return request({
    url: `marketing/integral/order/del/${id}`,
    method: 'DELETE',
  });
}

/**
 * 积分订单打印
 */
export function integralOrderPrint(id) {
  return request({
    url: `marketing/integral/order/print/${id}`,
    method: 'post',
  });
}
