/**
 * 页面路由常量表
 * 统一管理 dynamic-pages 内所有页面跳转路径，路径变更只需改此处
 */
export default {
  // 订单
  /** 统一下单页（确认订单） */
  orderCreate: '/pages/center/order/create/index',
  /** 订单详情页 */
  orderDetail: '/pages/center/order/detail/index',
  /** 收银台（支付页，新引擎 CashierEngine） */
  cashier: '/pages/center/pay/index',
  /** 收银台旧路由（pages/goods/cashier，旧页面 order_list/order_confirm/order_details 仍在使用，迁移完成前保留） */
  cashierLegacy: '/pages/goods/cashier/index',
  /** 申请售后页（退款统一走售后流程，由 AfterSaleEngine 按 available_types 渲染仅退款等类型） */
  aftersale: '/pages/center/order/aftersale/index',
  /** 售后单列表页（用户端，查看已申请的售后单及处理进度） */
  aftersaleList: '/pages/users/user_return_list/index',
  /** 查看物流页（使用旧版页面） */
  logistics: '/pages/goods/goods_logistics/index',
  /** 核销码页（核销码展示区块暂未迁移到新订单详情页，先落详情页，核销类商品接入时补独立页面） */
  verify: '/pages/center/order/detail/index',
  /** 评价页（pages.json 实际注册路径为 goods_comment_con） */
  comment: '/pages/goods/goods_comment_con/index',
  /** 支付结果页（支付失败/取消场景） */
  payStatus: '/pages/center/pay/status/index',
  // 用户
  /** 收货地址列表页 */
  addressList: '/pages/users/user_address_list/index',
  /** 新增/编辑收货地址页 */
  address: '/pages/users/user_address/index',
  /** 发票抬头表单页 */
  invoiceForm: '/pages/users/user_invoice_form/index',
  /** 好友代付页 */
  paymentOnBehalf: '/pages/users/payment_on_behalf/index',
  /** 支付宝小程序调起页 */
  alipayInvoke: '/pages/users/alipay_invoke/index',
  /** 佣金提现列表与微信确认收款页 */
  withdrawalList: '/pages/users/user_spread_money/index',
  withdrawalReceiving: '/pages/users/user_spread_money/receiving',
  /** 抽奖记录页（共用微信收款页） */
  lotteryRecord: '/pages/goods/lottery/grids/record',
  // 门店
  /** 门店列表页（选择门店） */
  storeList: '/pages/goods/goods_details_store/index',
  // 商品与内容
  /** 商品详情页 */
  goodsDetail: '/pages/goods_details/index',
  /** 资讯详情页（H5 外链通过 iframe 嵌入） */
  newsDetail: '/pages/extension/news_details/index',
  /** 客服聊天页 */
  customerChat: '/pages/extension/customer_list/chat',
};
