<!--
  售后引擎（双模式，数据驱动）
  - 申请模式（orderNo）：available_types → form_config 动态渲染表单 → 提交 apply
  - 详情模式（afterSaleNo）：detail 返回信息+时间线+actions → 渲染进度与操作按钮
  新增售后类型/产品类型只需后端 Handler 返回不同 form_config / actions，前端零改动
-->
<template>
  <view class="engine-page" :style="colorStyle">
    <!-- 加载状态 -->
    <view class="loading-box" v-if="loading">
      <view class="loading-text">{{ $t('加载中...') }}</view>
    </view>

    <!-- 加载失败提示 -->
    <view class="loading-box" v-if="!loading && errorMsg">
      <view class="loading-text error">{{ errorMsg }}</view>
      <view class="retry-btn" @tap="initPage">{{ $t('重新加载') }}</view>
    </view>

    <!-- 申请模式：订单不支持售后 -->
    <view class="loading-box" v-if="!loading && !errorMsg && mode === 'apply' && !allowAllowed">
      <view class="loading-text error">{{ allowReason || $t('当前订单不支持售后') }}</view>
      <view class="retry-btn" @tap="backToList">{{ $t('返回订单列表') }}</view>
    </view>

    <!-- ============ 申请模式 ============ -->
    <view class="apply-return" v-if="!loading && !errorMsg && mode === 'apply' && allowAllowed">
      <!-- 订单信息卡 -->
      <view class="order-card">
        <view class="order-row">
          <text class="label">{{ $t('订单编号') }}</text>
          <text class="value">{{ orderNo }}</text>
        </view>
      </view>

      <!-- 商品列表卡（商品级售后选择功能暂时关闭，默认全量售后） -->
      <view class="goods-card" v-if="refundableItems.length">
        <view class="goods-card-title">{{ $t('售后商品') }}</view>
        <view
          class="goods-item"
          v-for="item in refundableItems"
          :key="item.cart_id"
          :class="{ disabled: item.refundable_num === 0, selected: selectedCartIds.includes(item.cart_id) }"
          @tap="itemSelectionEnabled && item.refundable_num > 0 ? onToggleItem(item.cart_id) : null"
        >
          <!-- 勾选框 -->
          <view class="goods-check" v-if="itemSelectionEnabled">
            <view class="checkbox" :class="{ checked: selectedCartIds.includes(item.cart_id), disabled: item.refundable_num === 0 }">
              <text class="iconfont icon-duihao" v-if="selectedCartIds.includes(item.cart_id)"></text>
            </view>
          </view>
          <image class="goods-img" :src="item.image" mode="aspectFill"></image>
          <view class="goods-info">
            <view class="goods-name">{{ item.store_name }}</view>
            <view class="goods-sku" v-if="item.sku">{{ $t('规格') }}：{{ item.sku }}</view>
            <view class="goods-bottom">
              <text class="goods-price">¥{{ getProductPaidAmount(item) }}</text>
              <view class="goods-right">
                <!-- 选中时显示数量步进器，未选中显示购买数量 -->
                <view class="num-stepper" v-if="itemSelectionEnabled && selectedCartIds.includes(item.cart_id)" @tap.stop>
                  <view class="step-btn" @tap="onRefundNumChange(item.cart_id, -1)">-</view>
                  <text class="step-num">{{ refundNumMap[item.cart_id] || 1 }}</text>
                  <view class="step-btn" @tap="onRefundNumChange(item.cart_id, 1)">+</view>
                </view>
                <text class="goods-num" v-else>x{{ itemSelectionEnabled ? item.cart_num : (item.refundable_num || item.cart_num) }}</text>
              </view>
            </view>
            <!-- 已申请过的商品显示已退数量提示 -->
            <view class="goods-refunded" v-if="item.refunded_num > 0">
              {{ $t('已申请') }} {{ item.refunded_num }} {{ $t('件') }}，{{ $t('剩余可退') }} {{ item.refundable_num }} {{ $t('件') }}
            </view>
          </view>
        </view>
      </view>

      <!-- 退款提示条（仅退款类售后选中商品时展示，换货/补发/维修不涉及退款） -->
      <!-- 现金退款标的：展示退款金额上限（现金与积分两个维度独立，可同时展示） -->
      <view class="refund-tip" v-if="selectedCartIds.length > 0 && isRefundType && Number(maxRefundPrice) > 0">
        <text class="tip-label">{{ $t('退款金额上限') }}</text>
        <text class="tip-value">¥{{ maxRefundPrice }}</text>
      </view>
      <!-- 积分退款标的：展示将退还积分数 -->
      <view class="refund-tip" v-if="selectedCartIds.length > 0 && isRefundType && maxRefundIntegral > 0">
        <text class="tip-label">{{ $t('将退还积分') }}</text>
        <text class="tip-value">{{ maxRefundIntegral }}</text>
      </view>

      <view class="list">
        <!-- 退款金额/退还积分置顶，位于售后类型上方 -->
        <view class="item acea-row row-between-wrapper" v-if="refundPriceField">
          <view>{{ $t('退款金额') }}</view>
          <input
            class="num price-input"
            type="digit"
            v-model="formValues.refund_price"
            :placeholder="$t(refundPriceField.placeholder || '')"
          />
        </view>
        <view class="item acea-row row-between-wrapper" v-if="refundIntegralField">
          <view>{{ $t('退还积分') }}</view>
          <input
            class="num"
            type="number"
            v-model="formValues.refund_integral"
            :placeholder="$t(refundIntegralField.placeholder || '')"
          />
        </view>

        <!-- 售后类型选择 -->
        <view class="item acea-row row-between-wrapper">
          <view>{{ $t('售后类型') }}</view>
          <picker class="num" @change="onTypeChange" :value="typeIndex" :range="typeOptions">
            <view class="picker acea-row row-between-wrapper">
              <view class="reason">{{ typeOptions[typeIndex] }}</view>
              <text class="iconfont icon-jiantou"></text>
            </view>
          </picker>
        </view>

        <!-- 动态表单字段（由后端 form_config 下发） -->
        <block v-for="field in otherFields" :key="field.key">
          <!-- 退款金额 -->
          <view class="item acea-row row-between-wrapper" v-if="field.type === 'price'">
            <view>{{ $t(field.label) }}</view>
            <input
              class="num price-input"
              type="digit"
              v-model="formValues[field.key]"
              :placeholder="$t(field.placeholder || '')"
            />
          </view>
          <!-- 单行文本 -->
          <view class="item acea-row row-between-wrapper" v-else-if="field.type === 'text'">
            <view>{{ $t(field.label) }}</view>
            <input
              class="num"
              type="text"
              v-model="formValues[field.key]"
              :placeholder="$t(field.placeholder || '')"
            />
          </view>
          <!-- 下拉选择（选项由后端 options 下发） -->
          <view class="item acea-row row-between-wrapper" v-else-if="field.type === 'select'">
            <view>{{ $t(field.label) }}</view>
            <picker
              class="num"
              mode="selector"
              :range="field.options || []"
              :value="(field.options || []).indexOf(formValues[field.key])"
              @change="onSelectFieldChange(field, $event)"
            >
              <view class="picker acea-row row-between-wrapper">
                <view class="reason" :class="{ 'picker-placeholder': !formValues[field.key] }">
                  {{ formValues[field.key] || $t(field.placeholder || '请选择') }}
                </view>
                <text class="iconfont icon-jiantou"></text>
              </view>
            </picker>
          </view>
          <!-- 数字 -->
          <view class="item acea-row row-between-wrapper" v-else-if="field.type === 'number'">
            <view>{{ $t(field.label) }}</view>
            <input
              class="num"
              type="number"
              v-model="formValues[field.key]"
              :placeholder="$t(field.placeholder || '')"
            />
          </view>
          <!-- 多行文本 -->
          <view class="item textarea acea-row row-between" v-else-if="field.type === 'textarea'">
            <view>{{ $t(field.label) }}</view>
            <textarea
              :placeholder="$t(field.placeholder || '')"
              class="reason-textarea"
              v-model="formValues[field.key]"
              :maxlength="field.maxlength || 200"
            ></textarea>
          </view>
          <!-- 图片上传 -->
          <view class="item acea-row row-between upload" v-else-if="field.type === 'images'">
            <view class="title acea-row row-between-wrapper">
              <view>{{ $t(field.label) }}</view>
              <view class="tip">{{ $t('最多') }}{{ field.max || 6 }}{{ $t('张') }}</view>
            </view>
            <view class="upload-list acea-row row-middle">
              <view class="pictrue" v-for="(img, imgIdx) in formValues[field.key]" :key="imgIdx">
                <image :src="img"></image>
                <view class="iconfont icon-guanbi1 font-num" @tap="deleteImage(field.key, imgIdx)"></view>
              </view>
              <view
                class="pictrue upload-btn acea-row row-center-wrapper row-column"
                @tap="uploadImage(field.key, field.max || 6)"
                v-if="(formValues[field.key] || []).length < (field.max || 6)"
              >
                <text class="iconfont icon-icon25201"></text>
                <view>{{ $t('上传图片') }}</view>
              </view>
            </view>
          </view>
        </block>
      </view>

      <view class="apply-footer">
        <button class="submit-btn bg-color" @click="submitApply" :disabled="isRes" :loading="isRes">
          {{ $t('提交申请') }}
        </button>
      </view>
    </view>

    <!-- ============ 详情模式 ============ -->
    <view class="detail-page" v-if="!loading && !errorMsg && mode === 'detail' && detail">
      <!-- 售后信息卡 -->
      <view class="detail-card">
        <view class="status-banner" :class="statusClass">
          <view class="status-text">{{ $t(detail.status_text) }}</view>
          <view class="type-text">{{ $t(detail.type_text) }}</view>
        </view>
        <view class="info-row">
          <text class="label">{{ $t('售后单号') }}</text>
          <text class="value">{{ detail.after_sale_no }}</text>
        </view>
        <view class="info-row">
          <text class="label">{{ $t('订单编号') }}</text>
          <text class="value" @tap="copyText(detail.order_no)">{{ detail.order_no }}</text>
        </view>
        <view class="info-row" v-if="detail.refund_price > 0">
          <text class="label">{{ $t('退款金额') }}</text>
          <text class="value price">¥{{ detail.refund_price }}</text>
        </view>
        <view class="info-row" v-if="detail.refund_integral > 0">
          <text class="label">{{ $t('退还积分') }}</text>
          <text class="value price">{{ detail.refund_integral }}</text>
        </view>
        <view class="info-row" v-if="detail.actual_refund_price > 0">
          <text class="label">{{ $t('实际退款') }}</text>
          <text class="value price">¥{{ detail.actual_refund_price }}</text>
        </view>
        <view class="info-row" v-if="detail.reason">
          <text class="label">{{ $t('申请原因') }}</text>
          <text class="value reason-text">{{ detail.reason }}</text>
        </view>
        <view class="info-row" v-if="detail.remark">
          <text class="label">{{ $t('备注') }}</text>
          <text class="value reason-text">{{ detail.remark }}</text>
        </view>
        <view class="info-row" v-if="detail.reject_reason">
          <text class="label">{{ $t('拒绝原因') }}</text>
          <text class="value reason-text error">{{ detail.reject_reason }}</text>
        </view>
        <!-- 凭证图片 -->
        <view class="evidence-box" v-if="detail.evidence_images && detail.evidence_images.length">
          <view class="label">{{ $t('凭证图片') }}</view>
          <view class="evidence-list acea-row row-middle">
            <view class="evidence-img" v-for="(img, idx) in detail.evidence_images" :key="idx">
              <image :src="img" @tap="previewImage(detail.evidence_images, idx)" mode="aspectFill"></image>
            </view>
          </view>
        </view>
      </view>

      <!-- 售后商品卡（展示 product_snapshot.items，只读无勾选/步进器） -->
      <view class="detail-card" v-if="snapshotItems.length">
        <view class="card-title-bar">
          <text class="card-title-text">{{ $t('售后商品') }}</text>
        </view>
        <view class="snapshot-item" v-for="(item, idx) in snapshotItems" :key="idx">
          <image class="snapshot-img" :src="item.image" mode="aspectFill"></image>
          <view class="snapshot-info">
            <view class="snapshot-name">{{ item.store_name }}</view>
            <view class="snapshot-sku" v-if="item.sku">{{ $t('规格') }}：{{ item.sku }}</view>
            <view class="snapshot-bottom">
              <text class="snapshot-price">¥{{ item.original_price || item.true_price }}</text>
              <text class="snapshot-num">x{{ item.cart_num }}</text>
            </view>
          </view>
        </view>
      </view>

      <!-- 退货物流信息（用户填写退货物流后展示） -->
      <view class="detail-card" v-if="detail.return_delivery && detail.return_delivery.no">
        <view class="card-title-bar">
          <text class="card-title-text">{{ $t('退货物流') }}</text>
        </view>
        <view class="info-row">
          <text class="label">{{ $t('快递公司') }}</text>
          <text class="value">{{ detail.return_delivery.type || '-' }}</text>
        </view>
        <view class="info-row">
          <text class="label">{{ $t('快递单号') }}</text>
          <view class="value copy-row" @tap="copyText(detail.return_delivery.no)">
            <text class="express-no">{{ detail.return_delivery.no }}</text>
            <text class="copy-btn">{{ $t('复制') }}</text>
          </view>
        </view>
        <!-- 退货凭证图片 -->
        <view class="evidence-box" v-if="detail.return_delivery.images && detail.return_delivery.images.length">
          <view class="label">{{ $t('凭证图片') }}</view>
          <view class="evidence-list acea-row row-middle">
            <view class="evidence-img" v-for="(img, idx) in detail.return_delivery.images" :key="idx">
              <image :src="img" @tap="previewImage(detail.return_delivery.images, idx)" mode="aspectFill"></image>
            </view>
          </view>
        </view>
        <!-- 查看退货物流进度 -->
        <view class="logistics-action" @tap="viewLogistics('aftersale')">
          <text>{{ $t('查看物流') }}</text>
          <text class="iconfont icon-jiantou"></text>
        </view>
      </view>

      <!-- 补发物流信息（商家补发后展示） -->
      <view class="detail-card" v-if="detail.resend_delivery && detail.resend_delivery.no">
        <view class="card-title-bar">
          <text class="card-title-text">{{ $t('补发物流') }}</text>
        </view>
        <view class="info-row">
          <text class="label">{{ $t('快递公司') }}</text>
          <text class="value">{{ detail.resend_delivery.type || '-' }}</text>
        </view>
        <view class="info-row">
          <text class="label">{{ $t('快递单号') }}</text>
          <view class="value copy-row" @tap="copyText(detail.resend_delivery.no)">
            <text class="express-no">{{ detail.resend_delivery.no }}</text>
            <text class="copy-btn">{{ $t('复制') }}</text>
          </view>
        </view>
      </view>

      <!-- 时间线 -->
      <view class="timeline-card" v-if="detail.timeline && detail.timeline.length">
        <view class="card-title">{{ $t('处理进度') }}</view>
        <view class="timeline">
          <view
            class="timeline-node"
            v-for="(node, idx) in detail.timeline"
            :key="node.key"
            :class="{ done: node.done, last: idx === detail.timeline.length - 1 }"
          >
            <view class="node-dot"></view>
            <view class="node-line" v-if="idx !== detail.timeline.length - 1"></view>
            <view class="node-content">
              <view class="node-label">{{ $t(node.label) }}</view>
              <view class="node-time" v-if="node.time">{{ formatTime(node.time) }}</view>
            </view>
          </view>
        </view>
      </view>

      <!-- 操作按钮组（由后端 actions 下发） -->
      <view class="actions-bar" v-if="detail.actions && detail.actions.length">
        <button
          v-for="action in detail.actions"
          :key="action.key"
          class="action-btn"
          :class="{ 'bg-color': action.key !== 'cancel', 'cancel-btn': action.key === 'cancel' }"
          @click="doAction(action)"
        >
          {{ $t(action.label) }}
        </button>
      </view>
    </view>

    <!-- 退货物流填写弹层 -->
    <view class="popup-mask" v-if="showDeliverForm" @tap="showDeliverForm = false"></view>
    <view class="popup-content" v-if="showDeliverForm">
      <view class="popup-title">{{ $t('填写退货物流') }}</view>
      <view class="popup-body">
        <view class="popup-item">
          <text class="label">{{ $t('快递公司') }}</text>
          <input class="input" v-model="deliverForm.delivery_type" :placeholder="$t('请输入快递公司')" />
        </view>
        <view class="popup-item">
          <text class="label">{{ $t('快递单号') }}</text>
          <input class="input" v-model="deliverForm.delivery_no" :placeholder="$t('请输入快递单号')" />
        </view>
        <view class="popup-item">
          <text class="label">{{ $t('凭证图片') }}</text>
          <view class="upload-list acea-row row-middle">
            <view class="pictrue" v-for="(img, idx) in deliverForm.images" :key="idx">
              <image :src="img"></image>
              <view class="iconfont icon-guanbi1 font-num" @tap="deliverForm.images.splice(idx, 1)"></view>
            </view>
            <view
              class="pictrue upload-btn acea-row row-center-wrapper row-column"
              @tap="uploadImage('deliver_images', 6)"
              v-if="deliverForm.images.length < 6"
            >
              <text class="iconfont icon-icon25201"></text>
              <view>{{ $t('上传图片') }}</view>
            </view>
          </view>
        </view>
      </view>
      <view class="popup-footer">
        <button class="cancel-btn" @tap="showDeliverForm = false">{{ $t('取消') }}</button>
        <button class="bg-color submit-btn" @click="submitDeliverReturn">{{ $t('提交') }}</button>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  name: 'AfterSaleEngine',
  // 接收 App 中的布局、主题及 iconfont 样式，组件 scoped 样式仍保持局部作用域。
  options: { styleIsolation: 'apply-shared' },
};
</script>

<script setup>
import { ref, computed, watch } from 'vue';
import { storeToRefs } from 'pinia';
import {
  getStoreOrderDetail,
  getAfterSaleAvailableTypes,
  getAfterSaleFormConfig,
  submitAfterSale,
  getAfterSaleDetail,
  deliverReturnAfterSale,
} from '@/api/order-center.js';
import request from '@/utils/request.js';
import { toLogin } from '@/libs/login.js';
import { useAppStore } from '@/store/modules/app';
import { useColorTheme } from '@/composables/useColorTheme';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import routes from '@/dynamic-pages/api/routes.js';
import { getRefundIntegral } from '@/utils/refundIntegral.js';
import { openAfterSaleSubscribe } from '@/utils/subscribeMessage.js';

const props = defineProps({
  options: { type: Object, default: null },
});

// 页面参数由包装页响应式传入，兼容小程序端 onLoad 与组件创建的先后差异。
/** 订单号（申请模式入口） */
const orderNo = ref('');
/** 售后单号（详情模式入口） */
const afterSaleNo = ref('');

const { colorStyle } = useColorTheme();

// 原 mapGetters(['isLogin'])
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

/** 售后类型 → 文案映射（前端多语言 fallback，后端 type_text 优先） */
const AFTER_SALE_TYPE_TEXT = {
  refund_only: '仅退款',
  return_refund: '退货退款',
  exchange: '换货',
  resend: '补发',
  repair: '维修',
};

// ===== data =====
// 模式判定：orderNo 有则申请模式，afterSaleNo 有则详情模式
const mode = ref('apply');
const loading = ref(true);
const errorMsg = ref('');
const isRes = ref(false);
// ===== 申请模式数据 =====
const orderDetail = ref({});
const availableTypes = ref([]);
const allowAllowed = ref(false);
const allowReason = ref('');
const typeIndex = ref(0);
const formFields = ref([]);
const formValues = ref({});
// ===== 商品级售后（v8.1.1）=====
const refundableItems = ref([]);     // 后端下发的可售后商品列表（含 cart_id/refundable_num）
const selectedCartIds = ref([]);     // 选中的 cart_id 数组
const refundNumMap = ref({});        // cart_id => 售后数量（步进器值，默认 refundable_num）
// 商品级选择/部分数量功能暂时保留实现，但当前退款页面默认整单售后。
const itemSelectionEnabled = false;
// ===== 详情模式数据 =====
const detail = ref({});
// ===== 退货物流弹层 =====
const showDeliverForm = ref(false);
const deliverForm = ref({ delivery_type: '', delivery_no: '', images: [] });
const pendingAction = ref(null);

// ===== computed =====
/** 售后类型选项文案列表（优先后端 type_text，前端映射 fallback） */
const typeOptions = computed(() => availableTypes.value.map(type => i18n.global.t(AFTER_SALE_TYPE_TEXT[type] || type)));
/** 退款金额字段（置顶展示） */
const refundPriceField = computed(() => formFields.value.find(field => field.key === 'refund_price') || null);
/** 退还积分字段（紧跟退款金额展示） */
const refundIntegralField = computed(() => formFields.value.find(field => field.key === 'refund_integral') || null);
/** 其余动态字段（退款金额/积分已单独置顶，避免重复渲染） */
const otherFields = computed(() => formFields.value.filter(field => !['refund_price', 'refund_integral'].includes(field.key)));
/** 状态样式类（用于详情页顶部横幅配色） */
const statusClass = computed(() => {
  const status = detail.value.status || '';
  if (['completed', 'refunded'].includes(status)) return 'success';
  if (['canceled', 'audit_rejected'].includes(status)) return 'error';
  return 'processing';
});
/** 选中的商品明细列表（用于展示和构建快照） */
const selectedItems = computed(() => refundableItems.value.filter(item => selectedCartIds.value.includes(item.cart_id)));
/** 行实付金额按数量累计取整（与后端 paidAmountForQuantity 同算法，单位：元） */
function paidAmountForQuantity(rowTotal, quantity, refunded, selected) {
  const totalCents = Math.round(Number(rowTotal || 0) * 100);
  if (quantity <= 0 || totalCents <= 0) return 0;
  const before = Math.min(quantity, Math.max(0, Number(refunded) || 0));
  const after = Math.min(quantity, before + Math.max(0, Number(selected) || 0));
  if (after <= before) return 0;
  const centAt = num => Math.floor(totalCents * num / quantity);
  return (centAt(after) - centAt(before)) / 100;
}

/** 行实付现金：优先用后端分摊的实付金额，缺失时回退商品原价（旧数据兜底） */
function rowPaidTotal(item) {
  return item.paid_amount_total !== undefined && item.paid_amount_total !== null
    ? Number(item.paid_amount_total)
    : Number(item.true_price || 0) * Number(item.cart_num || 0);
}

/** 选中商品的退款金额上限（行实付金额 + 按实付占比分摊的实付运费；优惠券/积分抵扣不再折现退还） */
const maxRefundPrice = computed(() => {
  let selectedPaidAmount = 0;
  let totalPaidAmount = 0;
  refundableItems.value.forEach(item => {
    totalPaidAmount += rowPaidTotal(item);
    if (selectedCartIds.value.includes(item.cart_id)) {
      const num = refundNumMap.value[item.cart_id] || 0;
      selectedPaidAmount += paidAmountForQuantity(
        rowPaidTotal(item),
        Number(item.cart_num || 0),
        Number(item.refunded_num || 0),
        num
      );
    }
  });
  const payPostage = Number(orderDetail.value.pay_postage || 0);
  const postageRefund = selectedPaidAmount > 0 && payPostage > 0
    ? (totalPaidAmount > 0
      ? payPostage * Math.min(1, selectedPaidAmount / totalPaidAmount)
      : payPostage)
    : 0;
  return (selectedPaidAmount + postageRefund).toFixed(2);
});
/** 选中商品的退还积分合计，普通抵扣积分按数量累计取整。 */
const maxRefundIntegral = computed(() => {
  let total = 0;
  selectedItems.value.forEach(item => {
    const num = refundNumMap.value[item.cart_id] || 0;
    total += getRefundIntegral(item, num);
  });
  return total;
});
/** 当前售后类型是否为退款类（仅退款/退货退款）
 *  换货/补发/维修不涉及退款，不展示退款金额相关 UI，不传 refund_price 字段 */
const isRefundType = computed(() => {
  const type = availableTypes.value[typeIndex.value];
  return ['refund_only', 'return_refund'].includes(type);
});
/** 售后详情模式的商品快照列表（detail.product_snapshot.items）
 *  后端 getDetail 返回 product_snapshot 为已解码数组，items 含 cart_id/store_name/image/sku/cart_num/true_price */
const snapshotItems = computed(() => {
  const snapshot = detail.value.product_snapshot || {};
  const items = snapshot.items || [];
  return Array.isArray(items) ? items : [];
});

// ===== watch =====
// 同时等待路由参数和登录状态，避免依赖小程序模板 ref 的挂载时机。
watch(
  [() => props.options, isLogin],
  ([options, loggedIn]) => {
    if (options === null) return;
    if (!loggedIn) {
      toLogin();
      return;
    }
    initFromOptions(options);
  },
  {
    immediate: true,
  }
);

// ===== methods =====
/**
 * 从页面 onLoad 参数初始化引擎并触发首查。
 * 未登录时跳过初始化，由参数与登录状态 watch 在登录完成后触发。
 * @param {Object} options 页面 onLoad 参数（order_no / after_sale_no）
 */
function initFromOptions(options) {
  orderNo.value = options.order_no || '';
  afterSaleNo.value = options.after_sale_no || '';
  if (isLogin.value) {
    initPage();
  }
}

/** 初始化页面：根据参数判定模式 */
function initPage() {
  loading.value = true;
  errorMsg.value = '';
  // 优先详情模式（after_sale_no 优先级高于 order_no）
  if (afterSaleNo.value) {
    mode.value = 'detail';
    loadDetail();
  } else if (orderNo.value) {
    mode.value = 'apply';
    initApplyMode();
  } else {
    loading.value = false;
    errorMsg.value = i18n.global.t('参数错误');
  }
}

// ==================== 申请模式 ====================

/** 初始化申请模式：加载订单详情 + 可用售后类型 */
async function initApplyMode() {
  try {
    await loadOrderDetail();
    await loadAvailableTypes();
  } catch (e) {
    errorMsg.value = e.msg || e.message || i18n.global.t('加载失败');
  } finally {
    loading.value = false;
  }
}

/** 加载订单详情 */
async function loadOrderDetail() {
  try {
    const res = await getStoreOrderDetail(orderNo.value);
    const data = res.data || {};
    // 后端 detail 接口返回声明式结构 {page_config: {sections, extensions}}
    // 售后引擎只需扁平字段（pay_price/order_items/product_type 等），从 sections 提取拍平
    const sections = data?.page_config?.sections || [];
    const findSection = key => sections.find(s => s.key === key) || {};
    const orderInfo = findSection('order_info').props?.fields || {};
    const pricingProps = findSection('pricing').props || {};
    const productProps = findSection('product').props?.product || {};
    orderDetail.value = {
      ...orderInfo,                                     // order_no/uid/product_type 等
      pay_price: pricingProps.pay_price || '0.00',      // 订单应付金额（退款金额默认值依据）
      total_price: pricingProps.total_price || '0.00',  // 订单总金额
      pay_postage: pricingProps.pay_postage || '0.00',  // 运费
      order_items: productProps.items || [],            // 商品明细（构建售后快照用）
    };
  } catch (e) {
    throw new Error(e.message || e || i18n.global.t('加载订单失败'));
  }
}

/** 加载可用售后类型（后端三维规则下发） */
async function loadAvailableTypes() {
  try {
    const res = await getAfterSaleAvailableTypes(orderNo.value);
    const data = res.data || {};
    allowAllowed.value = data.allowed !== false;
    allowReason.value = data.reason || '';
    availableTypes.value = data.types || [];
    // 以售后接口的订单实付运费为准，避免订单详情与售后金额计算使用不同数据源。
    if (data.pay_postage !== undefined && data.pay_postage !== null) {
      orderDetail.value.pay_postage = data.pay_postage;
    }
    // v8.1.1 商品级售后：接收 items 列表
    // BIGINT cart_id 必须按字符串处理，避免 JavaScript number 往返 JSON 时丢失末位精度。
    refundableItems.value = (data.items || []).map(item => ({
      ...item,
      cart_id: String(item.cart_id ?? ''),
    }));
    // 当前暂不支持单独商品退款：默认选中全部仍可退款的商品。
    selectedCartIds.value = refundableItems.value
      .filter(item => Number(item.refundable_num) > 0)
      .map(item => item.cart_id);
    refundNumMap.value = {};
    refundableItems.value.forEach(item => {
      // 步进器默认值 = 最大可退数量
      refundNumMap.value[item.cart_id] = item.refundable_num;
    });
    // 默认选首个类型，加载对应表单配置
    if (allowAllowed.value && availableTypes.value.length) {
      await loadFormConfig(availableTypes.value[0]);
      syncRefundPriceField();
    }
  } catch (e) {
    throw new Error(e.message || e || i18n.global.t('加载售后类型失败'));
  }
}

/** 加载售后表单配置（数据驱动渲染） */
async function loadFormConfig(type) {
  try {
    const res = await getAfterSaleFormConfig(orderNo.value, type);
    const data = res.data || {};
    formFields.value = data.fields || [];
    // 初始化表单值（按字段类型设默认值）
    formValues.value = {};
    formFields.value.forEach(field => {
      if (field.type === 'images') {
        formValues.value[field.key] = [];
      } else if (field.type === 'price') {
        formValues.value[field.key] = (orderDetail.value.pay_price || '0.00').toString();
      } else if (field.type === 'select') {
        // 下拉字段默认选中第一个选项（无 options 时留空走必填校验）
        formValues.value[field.key] = field.default || (field.options && field.options[0]) || '';
      } else {
        formValues.value[field.key] = field.default || '';
      }
    });
  } catch (e) {
    formFields.value = [];
    formValues.value = {};
  }
}

/** 切换售后类型 */
async function onTypeChange(e) {
  typeIndex.value = Number(e.detail.value);
  const type = availableTypes.value[typeIndex.value];
  if (type) {
    await loadFormConfig(type);
    // 类型切换会重置表单值，与首次加载一致地回填退款金额/退还积分默认值
    syncRefundPriceField();
  }
}

/** 动态表单下拉字段变更（选项文本直接作为字段值提交） */
function onSelectFieldChange(field, e) {
  const options = field.options || [];
  const idx = Number(e.detail.value);
  if (options[idx] !== undefined) {
    formValues.value[field.key] = options[idx];
  }
}

/** 切换商品勾选（v8.1.1 商品级售后） */
function onToggleItem(cartId) {
  const idx = selectedCartIds.value.indexOf(cartId);
  if (idx >= 0) {
    selectedCartIds.value.splice(idx, 1);
  } else {
    selectedCartIds.value.push(cartId);
  }
  // 重新同步退款金额默认值
  syncRefundPriceField();
}

/** 数量步进器变更（v8.1.1 部分数量售后） */
function onRefundNumChange(cartId, delta) {
  const current = refundNumMap.value[cartId] || 0;
  const item = refundableItems.value.find(i => i.cart_id === cartId);
  if (!item) return;
  // 限制在 [1, refundable_num] 范围内
  const next = Math.min(item.refundable_num, Math.max(1, current + delta));
  refundNumMap.value[cartId] = next;
  // 同步退款金额默认值
  syncRefundPriceField();
}

/** 退款金额/退还积分字段同步：选中商品变化时重置为默认值（合计）
 *  现金与积分是两个独立维度，可同时存在，分别同步各自的默认值 */
function syncRefundPriceField() {
  // 同步"申请退款金额"字段（若表单含该字段）
  const priceField = formFields.value.find(f => f.key === 'refund_price');
  if (priceField) {
    formValues.value[priceField.key] = maxRefundPrice.value;
  }
  // 同步"退还积分"字段（若表单含该字段）
  const integralField = formFields.value.find(f => f.key === 'refund_integral');
  if (integralField) {
    formValues.value[integralField.key] = maxRefundIntegral.value;
  }
}

/** 删除凭证图片 */
function deleteImage(fieldKey, idx) {
  if (Array.isArray(formValues.value[fieldKey])) {
    formValues.value[fieldKey].splice(idx, 1);
  }
}

/** 上传图片（fieldKey=deliver_images 时为退货物流弹层上传） */
function uploadImage(fieldKey, max) {
  util.uploadImageOne('upload/image', (res) => {
    if (fieldKey === 'deliver_images') {
      deliverForm.value.images.push(res.data.url);
    } else if (Array.isArray(formValues.value[fieldKey])) {
      formValues.value[fieldKey].push(res.data.url);
    }
  });
}

/**
 * 商品实际付款金额（单价）：商品分摊实付 + 按实付占比分摊的运费
 * @param {Object} item 可售后商品
 * @return {string} 两位小数金额
 */
function getProductPaidAmount(item) {
  const cartNum = Number(item.cart_num || 1) || 1;
  const rowTotal = rowPaidTotal(item);
  const totalPaid = refundableItems.value.reduce((sum, row) => sum + rowPaidTotal(row), 0);
  const payPostage = Number(orderDetail.value.pay_postage || 0);
  const postageShare = totalPaid > 0 && rowTotal > 0 ? payPostage * rowTotal / totalPaid : 0;
  return ((rowTotal + postageShare) / cartNum).toFixed(2);
}

/** 构建售后商品快照（v8.1.1 按 cart_id 选择范围） */
function buildSnapshot() {
  const items = selectedItems.value.map(item => ({
    cart_id: String(item.cart_id),
    product_id: item.product_id,
    store_name: item.store_name,
    image: item.image,
    sku: item.sku,
    cart_num: refundNumMap.value[item.cart_id] || 0, // 售后数量（部分数量支持）
    true_price: item.true_price,
    original_price: item.original_price || item.true_price,
  }));
  return {
    product_type: orderDetail.value.product_type || 'normal',
    order_no: orderNo.value,
    order_amount: orderDetail.value.pay_price || '0.00',
    items: items,
  };
}

/** 提交售后申请 */
async function submitApply() {
  if (isRes.value) return;
  // v8.1.1 校验：必须勾选至少一个商品
  if (selectedCartIds.value.length === 0) {
    return util.Tips({ title: i18n.global.t('请选择要售后的商品') });
  }
  // 必填校验
  for (const field of formFields.value) {
    if (field.required) {
      const val = formValues.value[field.key];
      const empty = Array.isArray(val) ? val.length === 0 : !String(val || '').trim();
      if (empty) {
        const prefix = field.type === 'select' ? i18n.global.t('请选择') : i18n.global.t('请填写');
        return util.Tips({ title: prefix + i18n.global.t(field.label) });
      }
    }
  }
  // 组装请求体
  // v8.1.1 items: 售后商品范围 [{cart_id, refund_num}]
  const items = selectedItems.value.map(item => ({
    cart_id: String(item.cart_id),
    refund_num: refundNumMap.value[item.cart_id] || 0,
  }));
  const postData = {
    order_no: orderNo.value,
    type: availableTypes.value[typeIndex.value] || '',
    product_snapshot: buildSnapshot(),
    items: items,
  };
  // 数据驱动收集：遍历 formFields 把所有字段值塞入请求体
  // 后端 form_config 新增任何字段（如 fault_description），前端无需改动即可提交
  formFields.value.forEach(field => {
    const val = formValues.value[field.key];
    if (val !== undefined && val !== null && val !== '') {
      postData[field.key] = val;
    }
  });
  // 退款金额/退还积分处理：仅退款类售后（refund_only/return_refund）才传
  // - 若 formFields 含对应字段，值已由上面 forEach 收集到 postData
  // - 若 formFields 无对应字段（兼容旧 form_config），用选中商品合计作为默认值
  // 退款金额（现金）与退还积分是两个独立维度，可同时存在，分别兜底
  if (isRefundType.value) {
    if (!postData.refund_price && Number(maxRefundPrice.value) > 0) {
      postData.refund_price = maxRefundPrice.value;
    }
    if (!postData.refund_integral && maxRefundIntegral.value > 0) {
      postData.refund_integral = maxRefundIntegral.value;
    }
  } else {
    // 非退款类售后：确保不传退款金额/积分（即使旧前端误传也剔除）
    delete postData.refund_price;
    delete postData.refund_integral;
  }

  isRes.value = true;
  // 订阅请求必须发生在用户点击提交的调用链中；订阅失败/拒绝不应阻断售后申请。
  try {
    await openAfterSaleSubscribe();
  } catch {
    // subscribe() 已将平台/用户拒绝转换为 resolved Promise，这里仅兜底同步异常。
  }

  uni.showLoading({ title: i18n.global.t('提交中') });
  submitAfterSale(postData)
    .then(res => {
      uni.hideLoading();
      isRes.value = false;
      // 申请成功后提示并跳转到售后列表页（redirectTo 替换当前申请页，避免返回回到已提交的申请页）
      return util.Tips(
        { title: i18n.global.t('申请成功'), icon: 'success' },
        { url: routes.aftersaleList, tab: 5 }
      );
    })
    .catch(err => {
      uni.hideLoading();
      isRes.value = false;
      return util.Tips({ title: err.message || err });
    });
}

// ==================== 详情模式 ====================

/** 加载售后详情 */
async function loadDetail() {
  try {
    const res = await getAfterSaleDetail(afterSaleNo.value);
    const data = res.data || {};
    // 凭证图过滤空值项（历史数据可能存有 [""] 空串，不过滤会渲染出空图占位）
    data.evidence_images = filterValidImages(data.evidence_images);
    if (data.return_delivery) {
      data.return_delivery.images = filterValidImages(data.return_delivery.images);
    }
    detail.value = data;
    if (!detail.value.after_sale_no) {
      errorMsg.value = i18n.global.t('售后单不存在');
    }
  } catch (e) {
    errorMsg.value = e.message || e || i18n.global.t('加载失败');
  } finally {
    loading.value = false;
  }
}

/** 过滤凭证图中的空值项 */
function filterValidImages(images) {
  if (!Array.isArray(images)) return [];
  return images.filter(img => img && String(img).trim() !== '');
}

/** 执行操作按钮（由后端 actions 下发） */
function doAction(action) {
  pendingAction.value = action;
  // 有确认提示：先弹确认框
  if (action.confirm) {
    uni.showModal({
      title: i18n.global.t('提示'),
      content: i18n.global.t(action.confirm),
      success: (res) => {
        if (res.confirm) {
          executeAction(action);
        }
      },
    });
    return;
  }
  // 有子表单：弹退货物流填写层
  if (action.form === 'deliver_return') {
    deliverForm.value = { delivery_type: '', delivery_no: '', images: [] };
    showDeliverForm.value = true;
    return;
  }
  // 无确认无表单：直接执行
  executeAction(action);
}

/**
 * 执行操作（通用 API 调用）
 * 按 action.api / action.method 直接调用，后端新增任何无表单 action 前端零改动
 * 默认请求体携带 after_sale_no，后端按需接收
 */
function executeAction(action) {
  const apiPath = action.api;
  const method = (action.method || 'post').toLowerCase();
  if (!apiPath) {
    return util.Tips({ title: i18n.global.t('操作接口缺失') });
  }
  uni.showLoading({ title: i18n.global.t('处理中') });
  // 通用调用：默认带 after_sale_no，按 action.method 选择请求方式
  const payload = { after_sale_no: afterSaleNo.value };
  const req = method === 'get'
    ? request.get(apiPath, payload)
    : request.post(apiPath, payload);
  req.then(() => {
      uni.hideLoading();
      util.Tips({ title: i18n.global.t('操作成功'), icon: 'success' });
      // 刷新详情
      loadDetail();
    })
    .catch(err => {
      uni.hideLoading();
      util.Tips({ title: err.message || err });
    });
}

/** 提交退货物流 */
function submitDeliverReturn() {
  if (!deliverForm.value.delivery_type.trim() || !deliverForm.value.delivery_no.trim()) {
    return util.Tips({ title: i18n.global.t('请填写快递公司和单号') });
  }
  uni.showLoading({ title: i18n.global.t('提交中') });
  deliverReturnAfterSale({
    after_sale_no: afterSaleNo.value,
    delivery_type: deliverForm.value.delivery_type,
    delivery_no: deliverForm.value.delivery_no,
    images: deliverForm.value.images,
  })
    .then(() => {
      uni.hideLoading();
      showDeliverForm.value = false;
      util.Tips({ title: i18n.global.t('提交成功'), icon: 'success' });
      loadDetail();
    })
    .catch(err => {
      uni.hideLoading();
      util.Tips({ title: err.message || err });
    });
}

// ==================== 工具方法 ====================

/** 查看物流进度（跳转物流页，type=aftersale 退货物流 / aftersale_resend 补发物流） */
function viewLogistics(type) {
  uni.navigateTo({
    url: routes.logistics + '?orderId=' + afterSaleNo.value + '&type=' + type
  });
}

/** 格式化时间戳 */
function formatTime(timestamp) {
  if (!timestamp) return '';
  const d = new Date(timestamp * 1000);
  const pad = n => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

/** 预览图片 */
function previewImage(images, idx) {
  uni.previewImage({ urls: images, current: images[idx] });
}

/** 复制文本 */
function copyText(text) {
  uni.setClipboardData({ data: text });
}

/** 返回订单列表 */
function backToList() {
  uni.navigateBack({
    fail: () => uni.redirectTo({ url: '/pages/goods/order_list/index' }),
  });
}

// 保留显式初始化入口，兼容其他包装组件。
defineExpose({
  initFromOptions,
});
</script>

<style scoped lang="scss">
.engine-page {
  min-height: 100vh;
  background: #f6f7f9;
  color: #303133;
  font-size: 28rpx;
  line-height: 1.5;
  padding-top: 1rpx;
  box-sizing: border-box;

  .bg-color {
    background-color: var(--view-theme, #e93323) !important;
  }
}

.apply-return {
  padding-bottom: calc(160rpx + env(safe-area-inset-bottom));
}

.order-card {
  background: #fff;
  margin: 20rpx 20rpx 0;
  border-radius: 24rpx;
  padding: 24rpx 30rpx;

  .order-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 28rpx;
    color: #333;
    padding: 8rpx 0;

    .label {
      color: #333;
      font-size: 30rpx;
      font-weight: 600;
      flex-shrink: 0;
      margin-right: 24rpx;
    }

    .value {
      min-width: 0;
      text-align: right;
      word-break: break-all;
    }

    .value.price {
      color: #e93323;
      font-weight: 600;
    }

  }
}

/* 商品列表卡 */
.goods-card {
  background: #fff;
  margin: 18rpx 20rpx 0;
  border-radius: 24rpx;
  padding: 24rpx 30rpx;

  .goods-card-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #333;
    padding-bottom: 16rpx;
    border-bottom: 1rpx solid #f5f5f5;
    margin-bottom: 16rpx;
  }

  .goods-item {
    display: flex;
    align-items: center;
    padding: 16rpx 0;
    transition: background 0.2s;

    &.selected {
      background: #fff5f3;
      margin: 0 -16rpx;
      padding: 16rpx;
      border-radius: 12rpx;
    }

    &.disabled {
      opacity: 0.5;
    }

    .goods-check {
      flex-shrink: 0;
      margin-right: 16rpx;

      .checkbox {
        width: 40rpx;
        height: 40rpx;
        border-radius: 50%;
        border: 2rpx solid #ccc;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        transition: all 0.2s;

        &.checked {
          background: #e93323;
          border-color: #e93323;
        }

        &.disabled {
          background: #f5f5f5;
          border-color: #ddd;
        }

        .iconfont {
          color: #fff;
          font-size: 24rpx;
        }
      }
    }

    .goods-img {
      width: 140rpx;
      height: 140rpx;
      border-radius: 8rpx;
      flex-shrink: 0;
      background: #f5f5f5;
    }

    .goods-info {
      flex: 1;
      margin-left: 20rpx;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-width: 0;

      .goods-name {
        font-size: 28rpx;
        color: #333;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      .goods-sku {
        font-size: 24rpx;
        color: #999;
        margin-top: 8rpx;
      }

      .goods-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8rpx;
        height: 68rpx;

        .goods-price {
          font-size: 30rpx;
          color: #e93323;
          font-weight: 600;
        }

        .goods-right {
          display: flex;
          align-items: center;
        }

        .goods-num {
          font-size: 26rpx;
          color: #999;
        }
      }

      .goods-refunded {
        font-size: 22rpx;
        color: #ff9900;
        margin-top: 6rpx;
      }
    }
  }
}

/* 数量步进器 */
.num-stepper {
  display: flex;
  align-items: center;
  border: 1rpx solid #ddd;
  border-radius: 8rpx;
  overflow: hidden;
  height: 64rpx;

  .step-btn {
    width: 64rpx;
    height: 64rpx;
    line-height: 64rpx;
    text-align: center;
    font-size: 32rpx;
    color: #333;
    background: #f5f5f5;

    &:active {
      background: #e0e0e0;
    }
  }

  .step-num {
    min-width: 60rpx;
    text-align: center;
    font-size: 26rpx;
    color: #333;
    padding: 0 8rpx;
  }
}

/* 退款金额提示条 */
.refund-tip {
  background: #fff5f3;
  margin: 18rpx 20rpx 0;
  border-radius: 12rpx;
  padding: 20rpx 30rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 26rpx;
  border-left: 6rpx solid #e93323;

  .tip-label {
    color: #666;
  }

  .tip-value {
    color: #e93323;
    font-weight: 600;
    font-size: 30rpx;
  }
}

.list {
  background-color: #fff;
  margin: 18rpx 20rpx 0;
  border-radius: 24rpx;
  overflow: hidden;
}

.list .item {
  display: flex;
  flex-wrap: nowrap;
  align-items: center;
  justify-content: space-between;
  margin-left: 30rpx;
  padding-right: 30rpx;
  min-height: 108rpx;
  border-bottom: 1rpx solid #eee;
  font-size: 30rpx;
  color: #333;

  &:last-child {
    border-bottom: 0;
  }
}

.list .item .num {
  color: #282828;
  flex: 1;
  min-width: 0;
  width: 0;
  margin-left: 24rpx;
  text-align: right;
}

.list .item .num .picker .reason {
  flex: 1;
  min-width: 0;
  white-space: normal;
  word-break: break-word;
  padding: 20rpx 12rpx 20rpx 0;

  &.picker-placeholder {
    color: #bbb;
  }
}

.list .item .picker {
  display: flex;
  flex-wrap: nowrap;
  align-items: center;
}

.list .item .num .picker .iconfont {
  color: #666;
  font-size: 30rpx;
  margin-top: 2rpx;
}

.list .item .price-input {
  height: 88rpx;
}

.list .item.textarea {
  padding: 30rpx 30rpx 30rpx 0;
  flex-direction: column;
  align-items: stretch;
}

.list .item .reason-textarea {
  height: 180rpx;
  width: 100%;
  flex: none;
  margin: 20rpx 0 0;
  padding: 20rpx;
  box-sizing: border-box;
  background: #f6f7f9;
  border-radius: 12rpx;
  text-align: left;
  font-size: 30rpx;
}

.list .item.upload {
  display: block;
}

.list .item .title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 95rpx;
  width: 100%;
}

.list .item .title .tip {
  font-size: 26rpx;
  color: #bbb;
}

.list .item .upload-list {
  display: flex;
  padding-bottom: 36rpx;
  flex-wrap: wrap;
}

.list .item .upload-list .pictrue {
  margin: 22rpx 23rpx 0 0;
  width: 156rpx;
  height: 156rpx;
  position: relative;
  font-size: 24rpx;
  color: #bbb;

  image {
    width: 100%;
    height: 100%;
    border-radius: 3rpx;
  }
}

.list .item .upload-list .pictrue .icon-guanbi1 {
  position: absolute;
  font-size: 45rpx;
  top: -10rpx;
  right: -10rpx;
}

.list .item .upload-list .pictrue .icon-icon25201 {
  color: #bfbfbf;
  font-size: 50rpx;
}

.list .item .upload-list .pictrue.upload-btn {
  border: 1rpx solid #ddd;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.submit-btn {
  font-size: 32rpx;
  color: #fff;
  width: 100%;
  height: 86rpx;
  border-radius: 50rpx;
  text-align: center;
  line-height: 86rpx;
  margin: 0;
  display: block;
  padding: 0;
  box-sizing: border-box;

  &::after {
    border: 0;
  }

  &:disabled {
    opacity: 0.6;
  }
}

.apply-footer {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 10;
  padding: 20rpx 30rpx calc(20rpx + env(safe-area-inset-bottom));
  background: #fff;
  border-top: 1rpx solid #eee;
}

.loading-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
}

.loading-text {
  font-size: 28rpx;
  color: #999;

  &.error {
    color: #e93323;
    margin-bottom: 30rpx;
    text-align: center;
    padding: 0 40rpx;
    line-height: 1.6;
  }
}

.retry-btn {
  padding: 16rpx 60rpx;
  background: #e93323;
  color: #fff;
  font-size: 28rpx;
  border-radius: 40rpx;
}

/* ============ 详情模式样式 ============ */
.detail-page {
  padding-bottom: calc(140rpx + env(safe-area-inset-bottom));
}

.detail-card {
  background: #fff;
  margin: 20rpx;
  border-radius: 16rpx;
  overflow: hidden;
}

.status-banner {
  padding: 36rpx 30rpx;
  display: flex;
  align-items: center;
  justify-content: space-between;

  &.processing {
    background: linear-gradient(135deg, #e93323, #ff6b4a);
  }
  &.success {
    background: linear-gradient(135deg, #41b549, #5bc06a);
  }
  &.error {
    background: linear-gradient(135deg, #999, #bbb);
  }

  .status-text {
    font-size: 36rpx;
    font-weight: 600;
    color: #fff;
  }
  .type-text {
    font-size: 26rpx;
    color: rgba(255, 255, 255, 0.85);
  }
}

.info-row {
  display: flex;
  padding: 20rpx 30rpx;
  border-bottom: 1rpx solid #f5f5f5;
  font-size: 28rpx;

  .label {
    width: 160rpx;
    color: #999;
    flex-shrink: 0;
  }
  .value {
    flex: 1;
    color: #333;
    word-break: break-all;
  }
  .value.price {
    color: #e93323;
    font-weight: 600;
  }
  .value.reason-text {
    line-height: 1.5;
    &.error {
      color: #e93323;
    }
  }
}

.evidence-box {
  padding: 20rpx 30rpx;

  .label {
    font-size: 28rpx;
    color: #999;
    margin-bottom: 16rpx;
  }
  .evidence-list {
    display: flex;
    flex-wrap: wrap;
  }
  .evidence-img {
    width: 156rpx;
    height: 156rpx;
    margin: 0 20rpx 20rpx 0;
    border-radius: 8rpx;
    overflow: hidden;

    image {
      width: 100%;
      height: 100%;
    }
  }
}

/* 物流信息卡标题栏 */
.card-title-bar {
  padding: 24rpx 30rpx 16rpx;
  border-bottom: 1rpx solid #f5f5f5;

  .card-title-text {
    font-size: 30rpx;
    font-weight: 600;
    color: #333;
  }
}

/* 售后商品快照项（详情模式只读展示，无勾选框/步进器） */
.snapshot-item {
  display: flex;
  padding: 20rpx 30rpx;
  border-bottom: 1rpx solid #f5f5f5;

  &:last-child {
    border-bottom: none;
  }

  .snapshot-img {
    width: 140rpx;
    height: 140rpx;
    border-radius: 8rpx;
    flex-shrink: 0;
    background: #f5f5f5;
  }

  .snapshot-info {
    flex: 1;
    margin-left: 20rpx;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;

    .snapshot-name {
      font-size: 28rpx;
      color: #333;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .snapshot-sku {
      font-size: 24rpx;
      color: #999;
      margin-top: 8rpx;
    }

    .snapshot-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 8rpx;

      .snapshot-price {
        font-size: 30rpx;
        color: #e93323;
        font-weight: 600;
      }

      .snapshot-num {
        font-size: 26rpx;
        color: #999;
      }
    }
  }
}

/* 快递单号行（单号 + 复制按钮） */
.copy-row {
  display: flex;
  align-items: center;
  justify-content: space-between;

  .express-no {
    flex: 1;
    word-break: break-all;
    color: #333;
  }

  .copy-btn {
    color: #e93323;
    font-size: 26rpx;
    margin-left: 16rpx;
    flex-shrink: 0;
  }
}

/* 物流卡查看物流操作行 */
.logistics-action {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24rpx 30rpx;
  border-top: 1rpx solid #f5f5f5;
  font-size: 28rpx;
  color: #e93323;

  .iconfont {
    font-size: 24rpx;
    margin-left: 6rpx;
  }
}

.timeline-card {
  background: #fff;
  margin: 20rpx;
  border-radius: 16rpx;
  padding: 30rpx;
}

.card-title {
  font-size: 30rpx;
  font-weight: 600;
  color: #333;
  margin-bottom: 30rpx;
}

.timeline {
  padding-left: 10rpx;
}

.timeline-node {
  position: relative;
  padding-left: 40rpx;
  padding-bottom: 36rpx;
  min-height: 40rpx;

  &.last {
    padding-bottom: 0;
  }

  .node-dot {
    position: absolute;
    left: 0;
    top: 6rpx;
    width: 20rpx;
    height: 20rpx;
    border-radius: 50%;
    background: #ddd;
    border: 4rpx solid #f0f0f0;
    z-index: 1;
  }

  .node-line {
    position: absolute;
    left: 9rpx;
    top: 26rpx;
    bottom: 0;
    width: 2rpx;
    background: #eee;
  }

  &.done .node-dot {
    background: #e93323;
    border-color: #fce0db;
  }

  .node-content {
    .node-label {
      font-size: 28rpx;
      color: #333;
      margin-bottom: 6rpx;
    }
    .node-time {
      font-size: 24rpx;
      color: #999;
    }
  }

  &:not(.done) .node-label {
    color: #999;
  }
}

.actions-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 10;
  display: flex;
  padding: 16rpx 20rpx calc(16rpx + env(safe-area-inset-bottom));
  background: #fff;
  box-shadow: 0 -2rpx 12rpx rgba(0, 0, 0, 0.06);
  gap: 20rpx;

  .action-btn {
    flex: 1;
    height: 80rpx;
    line-height: 80rpx;
    border-radius: 40rpx;
    font-size: 30rpx;
    color: #fff;
    border: none;
    padding: 0;

    &.cancel-btn {
      background: #f5f5f5;
      color: #666;
    }
  }
}

/* ============ 退货物流弹层 ============ */
.popup-mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 99;
}

.popup-content {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #fff;
  border-radius: 24rpx 24rpx 0 0;
  z-index: 100;
  padding-bottom: env(safe-area-inset-bottom);
  max-height: 85vh;
  display: flex;
  flex-direction: column;
}

.popup-title {
  text-align: center;
  font-size: 32rpx;
  font-weight: 600;
  padding: 30rpx;
  border-bottom: 1rpx solid #eee;
}

.popup-body {
  padding: 20rpx 30rpx;
  min-height: 0;
  overflow-y: auto;
}

.popup-item {
  padding: 20rpx 0;

  .label {
    display: block;
    font-size: 28rpx;
    color: #666;
    margin-bottom: 12rpx;
  }
  .input {
    box-sizing: border-box;
    width: 100%;
    height: 72rpx;
    background: #f5f5f5;
    border-radius: 8rpx;
    padding: 0 20rpx;
    font-size: 28rpx;
  }
  .upload-list {
    display: flex;
    flex-wrap: wrap;
  }
  .upload-list .pictrue {
    margin: 16rpx 16rpx 0 0;
    width: 140rpx;
    height: 140rpx;
    position: relative;

    image {
      width: 100%;
      height: 100%;
      border-radius: 6rpx;
    }
    .icon-guanbi1 {
      position: absolute;
      font-size: 40rpx;
      top: -8rpx;
      right: -8rpx;
      color: #e93323;
    }
  }
  .upload-list .pictrue.upload-btn {
    border: 1rpx dashed #ddd;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #bbb;
    font-size: 24rpx;

    .icon-icon25201 {
      font-size: 44rpx;
    }
  }
}

.popup-footer {
  display: flex;
  padding: 20rpx 30rpx 30rpx;
  gap: 20rpx;

  button {
    flex: 1;
    width: 0;
    min-width: 0;
    margin: 0;
    height: 80rpx;
    line-height: 80rpx;
    border-radius: 40rpx;
    font-size: 30rpx;
    border: none;
    padding: 0;

    &::after {
      border: 0;
    }
 
    &.cancel-btn {
      background: #f5f5f5;
      color: #666;
    }
    &.submit-btn {
      color: #fff;
    }
  }
}
</style>
