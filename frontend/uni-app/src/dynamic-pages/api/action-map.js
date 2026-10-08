/**
 * 订单操作按钮映射
 * action 字符串 → 执行函数
 * 新增操作类型只需追加一条映射
 */
import routes from './routes.js';
import i18n from '@/utils/lang.js';
import { orderAgain } from '@/api/order.js';

/**
 * 再次购买：调 order/again 将原订单商品重新加入购物车（返回购物车缓存 key），
 * 再跳转下单页购物车模式（cartId），保证标准/网盘等各产品类型均可再次下单
 * @param {Object} order 订单数据
 * @param {Object} params 参数（order_no）
 */
function repurchaseToOrderCreate(order, params) {
  const orderNo = params.order_no || order.order_no || '';
  if (!orderNo) {
    uni.showToast({ title: i18n.t(`订单信息异常`), icon: 'none' });
    return;
  }
  uni.showLoading({ title: i18n.t(`加载中`), mask: true });
  orderAgain(orderNo)
    .then((res) => {
      uni.hideLoading();
      const cateId = (res && res.data && res.data.cateId) || '';
      if (!cateId) {
        uni.showToast({ title: i18n.t(`再来一单失败`), icon: 'none' });
        return;
      }
      uni.navigateTo({ url: routes.orderCreate + '?cartId=' + encodeURIComponent(cateId) + '&new=1' });
    })
    .catch(() => {
      uni.hideLoading();
      uni.showToast({ title: i18n.t(`再来一单失败`), icon: 'none' });
    });
}

export default {
  /** 去支付 - 跳转到已有收银台页面，使用 order_no 作为 order_id */
  payOrder(order, params) {
    const orderId = order.order_no || (params && params.order_no) || '';
    if (!orderId) {
      uni.showToast({ title: i18n.t(`订单信息异常`), icon: 'none' });
      return;
    }
    uni.navigateTo({ url: routes.cashier + '?order_no=' + orderId + '&from_type=order' });
  },
  /** 取消订单 */
  cancelOrder(order, params) {
    const orderNo = params.order_no || order.order_no || '';
    uni.showModal({
      title: i18n.t(`提示`),
      content: i18n.t(`确定取消该订单吗？`),
      success: (res) => {
        if (res.confirm) {
          uni.$emit('order:cancel', { order_no: orderNo });
        }
      },
    });
  },
  /** 确认收货 */
  confirmReceive(order, params) {
    const orderNo = params.order_no || order.order_no || '';
    uni.showModal({
      title: i18n.t(`提示`),
      content: i18n.t(`确定已收到商品吗？`),
      success: (res) => {
        if (res.confirm) {
          uni.$emit('order:confirm', { order_no: orderNo });
        }
      },
    });
  },
  /** 申请退款（统一走售后流程，售后页按 available_types 渲染仅退款等类型） */
  applyRefund(order, params) {
    const orderNo = params.order_no || order.order_no || '';
    uni.navigateTo({ url: routes.aftersale + '?order_no=' + orderNo });
  },
  /** 申请售后 */
  applyAfterSale(order, params) {
    const orderNo = params.order_no || order.order_no || '';
    uni.navigateTo({ url: routes.aftersale + '?order_no=' + orderNo });
  },
  /** 查看售后（进入售后详情模式，查看进度+进行中操作） */
  viewAfterSale(order, params) {
    const afterSaleNo = params.after_sale_no || order.after_sale_no || '';
    if (!afterSaleNo) {
      uni.showToast({ title: i18n.t(`售后单信息异常`), icon: 'none' });
      return;
    }
    uni.navigateTo({ url: routes.aftersale + '?after_sale_no=' + afterSaleNo });
  },
  /** 查看物流（使用旧版页面，参数名为 orderId） */
  viewLogistics(order, params) {
    const orderNo = params.order_no || order.order_no || '';
    uni.navigateTo({ url: routes.logistics + '?orderId=' + orderNo });
  },
  /** 再次购买 */
  repurchase(order, params) {
    repurchaseToOrderCreate(order, params);
  },
  /** 再次购买（别名） */
  buyAgain(order, params) {
    repurchaseToOrderCreate(order, params);
  },
  /** 删除订单 */
  deleteOrder(order, params) {
    const orderNo = params.order_no || order.order_no || '';
    uni.showModal({
      title: i18n.t(`提示`),
      content: i18n.t(`确定删除该订单吗？删除后不可恢复`),
      success: (res) => {
        if (res.confirm) {
          uni.$emit('order:delete', { order_no: orderNo });
        }
      },
    });
  },
  /** 查看核销码 */
  showVerifyCode(order, params) {
    uni.navigateTo({ url: routes.verify + '?order_no=' + order.order_no });
  },
  /** 查看电子票（门票）：跳转订单详情，游客信息卡片展示一人一码核销二维码 */
  viewTicket(order, params) {
    const orderNo = params.order_no || order.order_no || '';
    if (!orderNo) {
      uni.showToast({ title: i18n.t(`订单信息异常`), icon: 'none' });
      return;
    }
    uni.navigateTo({ url: routes.orderDetail + '?order_no=' + orderNo });
  },
  /** 通用页面跳转（route 由后端按钮参数下发） */
  navigatePage(order, params) {
    if (params && params.route) {
      uni.navigateTo({ url: params.route });
    }
  },
  /** 拨打电话 */
  callPhone(order, params) {
    if (params && params.phone) {
      uni.makePhoneCall({ phoneNumber: params.phone });
    }
  },
  /** 待收货（查看订单详情） */
  waitReceipt(order, params) {
    uni.navigateTo({ url: routes.orderDetail + '?order_no=' + order.order_no });
  },
  /** 去评价 */
  commentOrder(order, params) {
    uni.navigateTo({ url: routes.comment + '?order_no=' + order.order_no });
  },
  /** 去评价（后端 NormalOrderAdapter 等模块下发的 action 名；评价页按 SKU unique 提交） */
  goComment(order, params) {
    var orderNo = order.order_no || (params && params.order_no) || '';
    // 取第一个商品条目的 SKU unique；section 以 key='product' 注册，props.product.items 为商品条目
    var items = (order.product && order.product.product && order.product.product.items) || [];
    var skuUnique = items.length && items[0].attr_info && items[0].attr_info.unique;
    if (!orderNo || !skuUnique) {
      uni.showToast({ title: i18n.t(`订单信息异常`), icon: 'none' });
      return;
    }
    uni.navigateTo({ url: routes.comment + '?unique=' + skuUnique + '&uni=' + orderNo });
  },
  /** 查看卡密（卡密商品已发卡状态；独立卡密页未开发前先落订单详情） */
  viewCardKey(order, params) {
    uni.navigateTo({ url: routes.orderDetail + '?order_no=' + (order.order_no || (params && params.order_no) || '') });
  },
  /** 退款中（查看订单详情） */
  refunding(order, params) {
    uni.navigateTo({ url: routes.orderDetail + '?order_no=' + order.order_no });
  },
  /** 已退款（查看订单详情） */
  refunded(order, params) {
    uni.navigateTo({ url: routes.orderDetail + '?order_no=' + order.order_no });
  },
};
