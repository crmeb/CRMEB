<template>
  <!-- 弹层打开时锁页面滚动，避免滑动穿透到订单详情 -->
  <page-meta :page-style="pageLocked ? 'overflow:hidden;' : ''" />
  <view :style="colorStyle" :class="{ 'page-locked': pageLocked }">
    <page-loading v-if="loading" />
    <view v-else class="order-details">
      <!-- =============================================== -->
      <!-- DIY 通用化渲染：按 section.component 动态匹配组件 -->
      <!-- 组件通过 detail/index.js 的 import.meta.glob 自动注册 -->
      <!-- 新增产品类型只需放 .vue 文件 + 声明 componentKey   -->
      <!-- 小程序端不支持动态组件，走 SectionDispatcher 枚举分发 -->
      <!-- =============================================== -->
      <template v-for="section in sortedVisibleSections" :key="section.key || section.component">
        <!-- #ifdef H5 || APP-PLUS -->
        <component
          :is="resolveSectionComponent(section) || InfoListComponent"
          :section="withCtx(section)"
          @click="onSectionClick(section, $event)"
          @preview="onPreviewImage"
          @action="onSectionAction"
        />
        <!-- #endif -->
        <!-- #ifdef MP -->
        <section-dispatcher
          :section="withCtx(section)"
          @click="onSectionClick(section, $event)"
          @preview="onPreviewImage"
          @action="onSectionAction"
        />
        <!-- #endif -->
      </template>
      <!-- 送礼订单：分享入口由底部按钮组（与「申请退款」同组）的 shareGift 动作唤起 -->
      <giftModal
        :aleartStatus="giftModalShow"
        :giftData="giftShareData"
        @shareH5="shareH5"
        @close="giftModalShow = false"
      />
      <view class="mask" v-if="giftModalShow || invoiceModalShow" @click="closeInvoiceOverlays"></view>
      <!-- 查看发票 -->
      <invoiceModal
        :aleartStatus="invoiceModalShow"
        :invoiceData="invoiceModalData"
        @close="invoiceModalShow = false"
      />
      <!-- 申请开票：选择已有抬头并提交 make_up_invoice -->
      <invoice-picker
        :inv-show="invoicePickerShow"
        :is-special="specialInvoice"
        :url-query="invoiceUrlQuery"
        :inv-checked="invoiceCheckedId"
        :order-id="orderNo"
        :inv-list="invoiceList"
        :is-order="1"
        @inv-close="invoicePickerShow = false"
        @inv-change="submitInvoiceApply"
        @inv-cancel="invoicePickerShow = false"
      />
    </view>
  </view>
</template>

<script setup>
/**
 * OrderDetailEngine — 订单详情 DIY 通用化渲染引擎
 *
 * 核心职责：
 * 1. 调用接口获取 page_config（sections + extensions）
 * 2. 按 section.component 通过 resolveSectionComponent 动态匹配组件
 * 3. 全局状态（payPrice/payStatus/isRefund）通过 withCtx 注入到 section._ctx
 * 4. 组件 emit 的 click/preview/action 事件由引擎统一分发处理
 *
 * 组件全部独立封装在 dynamic-pages/components/order/detail/ 下
 * 新增产品类型只需放 .vue 文件 + 声明 componentKey，引擎零修改
 */
import { ref, computed, watch } from 'vue';
// resolveSectionComponent / InfoListComponent 为具名导入，script setup 中导入的绑定模板可直接使用
// （Vue2 时代需经 methods/data 中转，Vue3 模板直接调用导入函数）
import { resolveSectionComponent, InfoListComponent } from '../components/order/detail/index.js';
// #ifdef MP
// 小程序端动态组件替换为枚举分发器（见模板 #ifdef MP 分支）
import SectionDispatcher from '../components/order/detail/SectionDispatcher.vue';
// #endif
import actionMap from '../api/action-map.js';
import { getCenterOrderDetail, takeOrder, cancelOrder, delOrder } from '@/api/order-center.js';
import routes from '../api/routes.js';
import { useColorTheme } from '@/composables/useColorTheme';
import i18n from '@/utils/lang.js';
import giftModal from '@/pages/goods/order_pay_status/components/giftModal.vue';
import invoiceModal from '@/pages/goods/components/invoiceModal/index.vue';
import invoicePicker from '@/pages/goods/components/invoicePicker/index.vue';
import { invoiceList as fetchInvoiceList, makeUpinvoice } from '@/api/user.js';

defineOptions({ name: 'OrderDetailEngine' });

const props = defineProps({
  /** 订单号 */
  orderNo: { type: String, default: '' },
  /** 从发票抬头页带回的待选抬头 ID；有值且订单尚未开票时自动打开开票弹层 */
  pendingInvoiceId: { type: String, default: '' },
});
/** 向包装页同步开票弹层显隐（用于隐藏悬浮 home） */
const emit = defineEmits(['invoice-overlay', 'pending-invoice-consumed']);

const { colorStyle } = useColorTheme();

// ---------- data ----------
// 声明式渲染配置（res.data.page_config），v8.1 只返回 page_config
const pageConfig = ref({ sections: [], extensions: {} });
const loading = ref(true);
const confirmingReceive = ref(false);
const cancellingOrder = ref(false);
const deletingOrder = ref(false);
// 送礼分享
const giftModalShow = ref(false);
// 发票：申请开票 / 查看发票
const invoicePickerShow = ref(false);
const invoiceModalShow = ref(false);
const invoiceList = ref([]);
const invoiceCheckedId = ref('');
const invoiceApplying = ref(false);

// ---------- computed ----------
/**
 * sections 按 order 升序排列，过滤 visible=false
 * 前端不计算可见性，完全信任后端的 visible 字段
 */
const sortedVisibleSections = computed(() => {
  return (pageConfig.value.sections || [])
    .filter(s => s.visible !== false)
    .sort((a, b) => (a.order || 100) - (b.order || 100));
});
/**
 * section key → props 映射
 * 引擎逻辑（状态判断/页面跳转）从渲染配置中取字段，props 为后端直填值
 */
const sectionPropsMap = computed(() => {
  var map = {};
  (pageConfig.value.sections || []).forEach(function (s) {
    map[s.key] = s.props || {};
  });
  return map;
});
/** 退款类状态标识（影响状态头部样式），从 status section 读取 */
const isRefund = computed(() => {
  var code = (sectionPropsMap.value['status'] && sectionPropsMap.value['status'].code) || '';
  return ['refunding', 'refunded'].includes(code) ? code : false;
});
/** 实付金额，从 pricing section 读取 */
const payPrice = computed(() => {
  return (sectionPropsMap.value['pricing'] && sectionPropsMap.value['pricing'].pay_price) || '0.00';
});
/** 支付状态，从 status section 读取 */
const payStatus = computed(() => {
  return (sectionPropsMap.value['status'] && sectionPropsMap.value['status'].pay_status) || 0;
});
/** 查看者角色：gift_receiver 表示当前用户是礼品领取人（只读视图） */
const viewerRole = computed(() => {
  return (pageConfig.value.extensions && pageConfig.value.extensions.viewer_role) || '';
});
/** 订单信息字段（送礼标识/领取人等） */
const orderInfoFields = computed(() => {
  return (sectionPropsMap.value['order_info'] && sectionPropsMap.value['order_info'].fields) || {};
});
/** 是否启用专用发票（extensions.special_invoice） */
const specialInvoice = computed(() => !!(pageConfig.value.extensions && pageConfig.value.extensions.special_invoice));
/** 跳转抬头表单时携带的回跳参数 */
const invoiceUrlQuery = computed(() => `specialInvoice=${specialInvoice.value ? 1 : 0}`);
/**
 * 查看发票弹窗数据（兼容 invoiceModal 使用的 name/duty_number 字段）
 */
const invoiceModalData = computed(() => {
  const inv = (pageConfig.value.extensions && pageConfig.value.extensions.invoice) || {};
  const phone = inv.drawer_phone || inv.phone || inv.tell || '';
  return {
    ...inv,
    name: inv.name || inv.title || '',
    duty_number: inv.duty_number || inv.tax_number || '',
    drawer_phone: phone,
    phone,
    remark: inv.remark || '',
    is_invoice: Number(inv.is_invoice || 0),
    pay_price: payPrice.value,
  };
});
/** giftModal 分享数据 */
const giftShareData = computed(() => {
  // section.props.product = { product: {title,image,items}, product_id }
  var productObj = (sectionPropsMap.value['product'] && sectionPropsMap.value['product'].product) || {};
  var productItems = productObj.items || [];
  var first = productItems[0] || {};
  return {
    id: orderInfoFields.value.id || 0,
    image: first.image || productObj.image || '',
    title: first.store_name || first.title || productObj.title || '',
    message: orderInfoFields.value.gift_mark || '',
    avatar: '',
    nickname: '',
    code: '',
  };
});

// ---------- watch ----------
// The parent page receives route options in onLoad, after this child may
// already have been created. Avoid requesting the legacy empty URL and
// load once the order number becomes available.
watch(() => props.orderNo, (value) => {
  if (value) loadDetail();
}, { immediate: true });

// ---------- methods ----------
/**
 * 加载订单详情
 * v8.1 接口只返回 page_config（sections + extensions），无 data 分组
 */
async function loadDetail() {
  if (!props.orderNo) return;
  loading.value = true;
  try {
    const res = await getCenterOrderDetail(props.orderNo);
    // 后端 redirect 指令：扩展模块可控制页面跳转（如拼团未成团跳转拼团进度页）
    if (res.data && res.data.redirect) {
      var r = res.data.redirect;
      uni[r.mode || 'redirectTo']({ url: r.url });
      return;
    }
    pageConfig.value = sanitizePageConfig((res.data && res.data.page_config) || { sections: [], extensions: {} });
    // 详情页配送信息只展示公司/单号，轨迹由「查看物流」单独查询，此处不再异步拉轨迹
    maybeOpenPendingInvoice();
  } catch (e) {
    uni.showToast({ title: i18n.t('加载失败'), icon: 'none' });
  } finally {
    loading.value = false;
  }
}

/**
 * 从发票抬头编辑页返回时：若带回 invoice_id 且订单尚未开票，自动打开抬头选择并预选该抬头
 */
function maybeOpenPendingInvoice() {
  const invoiceId = String(props.pendingInvoiceId || '');
  if (!invoiceId) return;
  emit('pending-invoice-consumed');
  const inv = (pageConfig.value.extensions && pageConfig.value.extensions.invoice) || {};
  // 已有开票申请则不再弹层
  if (inv && (inv.id || inv.invoice_id || inv.name || inv.title)) {
    return;
  }
  openInvoiceApply(invoiceId);
}

/**
 * 裁剪查看物流按钮：虚拟发货 / 商家送货 / 无配送信息区时不展示「查看物流」
 * （配送信息区本身由后端按类型下发；虚拟发货有备注时仍保留区块）
 * @param {object} config page_config
 * @return {object}
 */
function sanitizePageConfig(config) {
  const next = config || { sections: [], extensions: {} };
  const sections = Array.isArray(next.sections) ? next.sections : [];
  const logistics = sections.find(function (s) { return s && s.key === 'logistics'; });
  const deliveryType = String((logistics && logistics.props && logistics.props.delivery_type) || '');
  const stripLogisticsBtn = deliveryType === 'fictitious'
    || deliveryType === 'send'
    || !logistics
    || logistics.visible === false;
  if (!stripLogisticsBtn) return next;

  const stripViewLogistics = function (list) {
    if (!Array.isArray(list)) return list;
    return list.filter(function (btn) { return (btn && btn.action) !== 'viewLogistics'; });
  };
  const ex = next.extensions || {};
  if (ex.order_buttons) ex.order_buttons = stripViewLogistics(ex.order_buttons);
  if (ex.buttons) ex.buttons = stripViewLogistics(ex.buttons);
  next.extensions = ex;
  next.sections = sections.map(function (s) {
    if (!s || !s.props || !Array.isArray(s.props.buttons)) return s;
    return Object.assign({}, s, {
      props: Object.assign({}, s.props, { buttons: stripViewLogistics(s.props.buttons) }),
    });
  });
  return next;
}

// 包装页 onShow 返回时调用（评价完成/售后进度变化等场景，返回详情需看到最新状态）
/** 开票/查看发票弹层是否打开（供包装页隐藏悬浮 home） */
const invoiceOverlayOpen = computed(() => invoicePickerShow.value || invoiceModalShow.value);
/** 任意遮罩弹层打开时锁定页面滚动 */
const pageLocked = computed(() => invoiceOverlayOpen.value || giftModalShow.value);

watch(invoiceOverlayOpen, (open) => {
  emit('invoice-overlay', open);
}, { immediate: true });

defineExpose({ refresh: loadDetail, invoiceOverlayOpen });

// ========== 全局状态注入 ==========

/**
 * 把全局状态注入到 section._ctx，供组件跨 section 取值
 * 每次渲染重新构造，保证响应式；不污染原 section 对象
 * @param {object} section 区段对象
 * @return {object} 带 _ctx 的 section 副本
 */
function withCtx(section) {
  return Object.assign({}, section, {
    _ctx: {
      payPrice: payPrice.value,
      payStatus: payStatus.value,
      isRefund: isRefund.value,
      sectionPropsMap: sectionPropsMap.value,
      viewerRole: viewerRole.value,
    },
  });
}

// ========== 事件分发 ==========

/**
 * click 事件统一分发（按 section.component 路由到对应处理方法）
 * @param {object} section 区段对象
 * @param {*} payload 组件 emit 的参数（ProductCard 无参数，RelatedOrderList 传 item，ContractEntry 传 url，ActionCard 传 {action, link}）
 */
function onSectionClick(section, payload) {
  switch (section.component) {
    case 'product_card':
      // v8.1.1 多商品：ProductCard emit { product_id }，优先用 payload 中的 id
      goProductDetail(payload);
      break;
    case 'related_order_list':
      goRelatedOrder(payload);
      break;
    case 'contract_entry':
      openContract(payload);
      break;
    case 'action_card':
      onActionCardClick(payload);
      break;
  }
}
/**
 * action 事件分发（ButtonGroup emit）
 * @param {object} payload {action, params}
 */
function onSectionAction(payload) {
  onButtonAction(payload);
}

// ========== 事件处理 ==========

/**
 * 底部按钮点击（ButtonGroup emit）
 * @param {object} payload {action, params}
 */
function onButtonAction(payload) {
  var handler = actionMap[payload.action];
  if (handler) {
    // 统一补全 order_no（从 order_info section 的 props.fields 读取），action-map 处理器依赖该字段
    var orderNo = ((sectionPropsMap.value['order_info'] && sectionPropsMap.value['order_info'].fields)
      && sectionPropsMap.value['order_info'].fields.order_no)
      || (payload.params && payload.params.order_no) || '';
    // 需要页面内弹窗/刷新等交互的动作，通过 context 回调注入，action-map 不直接持有页面状态
    var context;
    if (payload.action === 'confirmReceive') {
      context = { onConfirm: confirmReceive };
    } else if (payload.action === 'cancelOrder') {
      context = { onCancel: cancelUnpaidOrder };
    } else if (payload.action === 'deleteOrder') {
      context = { onDelete: deleteOrderAndBack };
    } else if (payload.action === 'shareGift') {
      context = { onShareGift: openGiftShare };
    } else if (payload.action === 'applyInvoice') {
      context = { onApplyInvoice: openInvoiceApply };
    } else if (payload.action === 'viewInvoice') {
      context = { onViewInvoice: openInvoiceView };
    }
    handler(Object.assign({}, sectionPropsMap.value, { order_no: orderNo }), payload.params || {}, context);
  }
}

/**
 * 打开送礼分享弹窗（送给好友按钮，与申请退款同组）
 */
function openGiftShare() {
  giftModalShow.value = true;
}

/** 关闭发票相关浮层 */
function closeInvoiceOverlays() {
  invoiceModalShow.value = false;
  giftModalShow.value = false;
}

/** 查看发票 */
function openInvoiceView() {
  if (!invoiceModalData.value || !(invoiceModalData.value.name || invoiceModalData.value.title)) {
    uni.showToast({ title: i18n.t('暂无发票信息'), icon: 'none' });
    return;
  }
  invoiceModalShow.value = true;
}

/**
 * 申请开票：拉取抬头列表并打开选择弹窗
 * @param {string} [preselectId] 预选抬头 ID（从抬头编辑页返回时传入）
 */
function openInvoiceApply(preselectId = '') {
  uni.showLoading({ title: i18n.t('正在加载中'), mask: true });
  fetchInvoiceList()
    .then((res) => {
      uni.hideLoading();
      invoiceList.value = (res.data || []).map((item) => {
        item.id = String(item.id);
        return item;
      });
      invoiceCheckedId.value = preselectId ? String(preselectId) : '';
      invoicePickerShow.value = true;
    })
    .catch((err) => {
      uni.hideLoading();
      uni.showToast({ title: (typeof err === 'string' ? err : err?.msg) || i18n.t('加载失败'), icon: 'none' });
    });
}

/**
 * 确认提交开票申请
 * @param {string} invoiceId 选中的抬头 ID
 */
function submitInvoiceApply(invoiceId) {
  if (!invoiceId) {
    uni.showToast({ title: i18n.t('请选择发票抬头'), icon: 'none' });
    return;
  }
  if (invoiceApplying.value) return;
  invoiceApplying.value = true;
  makeUpinvoice({
    order_id: props.orderNo,
    invoice_id: invoiceId,
  })
    .then(async () => {
      uni.showToast({ title: i18n.t('申请成功'), icon: 'success' });
      invoicePickerShow.value = false;
      await loadDetail();
      invoiceModalShow.value = true;
    })
    .catch((err) => {
      uni.showToast({ title: (typeof err === 'string' ? err : err?.msg) || i18n.t('申请失败'), icon: 'none' });
    })
    .finally(() => {
      invoiceApplying.value = false;
    });
}

/**
 * 详情页确认收货
 * @param {object} payload {order_no}
 */
async function confirmReceive(payload) {
  if (confirmingReceive.value) return;
  confirmingReceive.value = true;
  try {
    await takeOrder(payload.order_no);
    uni.showToast({ title: i18n.t('收货成功'), icon: 'success' });
    await loadDetail();
    // 通知列表页同步刷新（栈内列表仍挂载时立即更新）
    uni.$emit('order:list-refresh');
  } catch (e) {
    uni.showToast({ title: (typeof e === 'string' ? e : e?.msg) || i18n.t('收货失败'), icon: 'none' });
  } finally {
    confirmingReceive.value = false;
  }
}

/**
 * 详情页取消未支付订单
 * @param {object} payload {order_no}
 */
async function cancelUnpaidOrder(payload) {
  if (cancellingOrder.value) return;
  cancellingOrder.value = true;
  try {
    await cancelOrder(payload.order_no);
    uni.showToast({ title: i18n.t('取消成功'), icon: 'success' });
    await loadDetail();
    uni.$emit('order:list-refresh');
  } catch (e) {
    uni.showToast({ title: (typeof e === 'string' ? e : e?.msg) || i18n.t('取消失败'), icon: 'none' });
  } finally {
    cancellingOrder.value = false;
  }
}

/**
 * 详情页删除订单：成功后通知列表刷新并返回列表（订单已不存在，不可留在详情）
 * @param {object} payload {order_no}
 */
async function deleteOrderAndBack(payload) {
  if (deletingOrder.value) return;
  deletingOrder.value = true;
  try {
    await delOrder(payload.order_no);
    uni.$emit('order:list-refresh');
    uni.showToast({ title: i18n.t('删除成功'), icon: 'success' });
    setTimeout(() => {
      const pages = getCurrentPages();
      if (pages.length > 1) {
        uni.navigateBack({ delta: 1 });
      } else {
        uni.redirectTo({ url: '/pages/goods/order_list/index' });
      }
    }, 500);
  } catch (e) {
    uni.showToast({ title: (typeof e === 'string' ? e : e?.msg) || i18n.t('删除失败'), icon: 'none' });
  } finally {
    deletingOrder.value = false;
  }
}
/**
 * 行动入口卡片点击（ActionCard emit）
 * @param {object} payload {action, link}
 */
function onActionCardClick(payload) {
  handleActionCard(payload.action, payload.link);
}
/**
 * 行动入口统一处理逻辑
 * @param {string} action 行动标识
 * @param {string} link 跳转链接
 */
function handleActionCard(action, link) {
  // 客服：跳转客服聊天页
  if (action === 'goGoodCall') {
    goGoodCall();
    return;
  }
  // 带 link 的行动入口（抽奖/促销/分享等）：统一跳转
  if (link) {
    uni.navigateTo({
      url: link,
      fail: () => {
        uni.switchTab({
          url: link,
          fail: () => { uni.showToast({ title: i18n.t('页面不存在'), icon: 'none' }); },
        });
      },
    });
    return;
  }
  // 无 link 的入口（领券/积分奖励）：提示（具体逻辑由插件扩展）
  if (action) {
    uni.showToast({ title: i18n.t('功能开发中'), icon: 'none' });
  }
}
/**
 * 评价图片预览（ReviewCard emit）
 * @param {object} payload {images, index}
 */
function onPreviewImage(payload) {
  uni.previewImage({
    current: payload.images[payload.index],
    urls: payload.images,
  });
}

// ========== 页面跳转 ==========

/** 跳转商品详情页（活动订单优先跳后端下发的活动详情地址，普通订单跳商品详情页） */
function goProductDetail(payload) {
  // v8.1.1 多商品：优先用 payload 中的 product_id，兜底用 section props 中的
  var productId = (payload && payload.product_id) || (sectionPropsMap.value['product'] && sectionPropsMap.value['product'].product_id) || 0;
  if (!productId) {
    uni.showToast({ title: i18n.t('商品信息缺失'), icon: 'none' });
    return;
  }
  // 活动订单：order_info.fields.activity_detail_url 由各活动模块监听器下发完整跳转地址
  // （含页面路径与参数），前端零硬编码，新增活动类型只需后端下发该字段
  var activityUrl = (sectionPropsMap.value['order_info'] && sectionPropsMap.value['order_info'].fields
    && sectionPropsMap.value['order_info'].fields.activity_detail_url) || '';
  if (activityUrl) {
    uni.navigateTo({ url: activityUrl });
    return;
  }
  uni.navigateTo({ url: routes.goodsDetail + '?id=' + productId });
}
/**
 * 跳转关联订单详情（RelatedOrderList emit）
 * @param {object} item 关联订单项
 */
function goRelatedOrder(item) {
  var orderNo = (item && item.order_no) || '';
  if (!orderNo) return;
  uni.navigateTo({ url: routes.orderDetail + '?order_no=' + orderNo });
}
/**
 * 打开合同/协议链接（ContractEntry emit）
 * @param {string} url 合同URL
 */
function openContract(url) {
  if (!url) {
    uni.showToast({ title: i18n.t('协议链接不存在'), icon: 'none' });
    return;
  }
  // #ifdef H5
  window.open(url, '_blank');
  // #endif
  // #ifndef H5
  uni.navigateTo({
    url: routes.newsDetail + '?id=0&url=' + encodeURIComponent(url),
  });
  // #endif
}
/** 跳转客服聊天页 */
function goGoodCall() {
  var orderNo = ((sectionPropsMap.value['order_info'] && sectionPropsMap.value['order_info'].fields)
    && sectionPropsMap.value['order_info'].fields.order_no) || '';
  uni.navigateTo({ url: routes.customerChat + '?orderId=' + orderNo });
}
/** H5 分享面板回调（giftModal） */
function shareH5() {
  // 链接复制由 giftModal 内部完成
}
</script>

<style scoped>
/* 引擎容器样式 */
.order-details {
  padding-bottom: calc(150rpx + constant(safe-area-inset-bottom));
  padding-bottom: calc(150rpx + env(safe-area-inset-bottom));
}

/* H5：弹层打开时锁住页面滚动（小程序侧靠 page-meta） */
.page-locked {
  height: 100vh;
  overflow: hidden;
}

.mask {
  position: fixed;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  /* 低于 giftModal 的 999，避免遮罩盖住弹窗按钮；高于开票弹层由 invoicePicker 自管 */
  z-index: 998;
}
</style>
