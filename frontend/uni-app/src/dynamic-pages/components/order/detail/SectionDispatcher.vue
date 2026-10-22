<template>
  <!-- ================================================================ -->
  <!-- 小程序端专用 section 分发器                                       -->
  <!-- 小程序不支持 <component :is> 动态组件（平台限制），此处用 v-if    -->
  <!-- 枚举替代；分支判断基于注册表命中的 key 字符串（单一事实源）       -->
  <!-- 注意：不能用「组件对象 ===」做比较——MP 编译器会把本文件的组件    -->
  <!-- import 改写为加载函数，与注册表中的组件对象永不相等              -->
  <!-- H5 / APP 端仍走 OrderDetailEngine 的动态组件，不经过本文件        -->
  <!-- 新增组件：放 .vue 文件 + 声明 componentKey 后，需在此补一行分支   -->
  <!-- ================================================================ -->
  <action-card v-if="matchedKey === 'action_card'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <activity-card v-else-if="matchedKey === 'activity_card'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <address-card v-else-if="matchedKey === 'address_card'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <after-sale-progress v-else-if="matchedKey === 'after_sale_progress'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <button-group v-else-if="matchedKey === 'button_group'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <contract-entry v-else-if="matchedKey === 'contract_entry'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <countdown v-else-if="matchedKey === 'countdown'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <custom-form v-else-if="matchedKey === 'custom_form'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <faq-list v-else-if="matchedKey === 'faq_list'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <gift-info-card v-else-if="matchedKey === 'gift_info'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <fulfillment-card v-else-if="fulfillmentKeys.includes(matchedKey)" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <invoice-card v-else-if="matchedKey === 'info_list:invoice'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <logistics-timeline v-else-if="matchedKey === 'timeline:logistics'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <notice-bar v-else-if="matchedKey === 'notice_bar'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <order-info-card v-else-if="matchedKey === 'info_list:order_info'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <order-timeline v-else-if="matchedKey === 'timeline'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <payment-card v-else-if="matchedKey === 'info_list:payment'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <price-detail v-else-if="matchedKey === 'price_detail'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <product-card v-else-if="matchedKey === 'product_card'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <product-list v-else-if="matchedKey === 'product_list'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <progress-bar v-else-if="matchedKey === 'progress_bar'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <qr-code-card v-else-if="matchedKey === 'qrcode'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <related-order-list v-else-if="matchedKey === 'related_order_list'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <review-card v-else-if="matchedKey === 'review_card'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <service-appointment v-else-if="matchedKey === 'service_appointment'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <status-bar v-else-if="matchedKey === 'status_bar'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <store-card v-else-if="matchedKey === 'store_card'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <visitor-info-card v-else-if="matchedKey === 'visitors'" :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
  <!-- info_list 自身与未命中注册表的 section 统一降级为 InfoList 键值对渲染 -->
  <info-list v-else :section="section" @click="emit('click', $event)" @preview="emit('preview', $event)" @action="emit('action', $event)" />
</template>

<script setup>
/**
 * SectionDispatcher — 订单详情 section 小程序端分发器
 *
 * 小程序平台不支持 <component :is>（uni-app MP 编译器硬限制），
 * OrderDetailEngine 在 MP 端把每个 section 交给本组件，
 * 本组件按注册表命中的 key 字符串做 v-if 枚举渲染。
 * 组件解析逻辑与 H5 端共用同一注册表（./index.js），复合 key 逻辑天然一致。
 */
import { computed } from 'vue';
import { resolveSectionKey } from './index.js';
// 以下导入仅用于模板标签的组件注册（MP 端由编译器改写为加载函数，不可用于身份比较）
import ActionCard from './ActionCard.vue';
import ActivityCard from './ActivityCard.vue';
import AddressCard from './AddressCard.vue';
import AfterSaleProgress from './AfterSaleProgress.vue';
import ButtonGroup from './ButtonGroup.vue';
import ContractEntry from './ContractEntry.vue';
import Countdown from './Countdown.vue';
import CustomForm from './CustomForm.vue';
import FaqList from './FaqList.vue';
import FulfillmentCard from './FulfillmentCard.vue';
import GiftInfoCard from './GiftInfoCard.vue';
import InfoList from './InfoList.vue';
import InvoiceCard from './InvoiceCard.vue';
import LogisticsTimeline from './LogisticsTimeline.vue';
import NoticeBar from './NoticeBar.vue';
import OrderInfoCard from './OrderInfoCard.vue';
import OrderTimeline from './OrderTimeline.vue';
import PaymentCard from './PaymentCard.vue';
import PriceDetail from './PriceDetail.vue';
import ProductCard from './ProductCard.vue';
import ProductList from './ProductList.vue';
import ProgressBar from './ProgressBar.vue';
import QrCodeCard from './QrCodeCard.vue';
import RelatedOrderList from './RelatedOrderList.vue';
import ReviewCard from './ReviewCard.vue';
import ServiceAppointment from './ServiceAppointment.vue';
import StatusBar from './StatusBar.vue';
import StoreCard from './StoreCard.vue';
import VisitorInfoCard from './VisitorInfoCard.vue';

defineOptions({ name: 'SectionDispatcher' });

const props = defineProps({
  /** 区段对象（引擎已通过 withCtx 注入 _ctx） */
  section: { type: Object, required: true },
});

const emit = defineEmits(['click', 'preview', 'action']);

/** FulfillmentCard 在注册表声明的 5 个复用 key（与该组件 componentKey 数组保持一致） */
const fulfillmentKeys = ['card_list', 'download_link', 'info_list:fulfillment', 'ticket_info', 'coupon_code'];

/** 注册表命中的 key（分支判断的唯一依据），未命中为空串走 v-else 兜底 */
const matchedKey = computed(() => resolveSectionKey(props.section) || '');
</script>
