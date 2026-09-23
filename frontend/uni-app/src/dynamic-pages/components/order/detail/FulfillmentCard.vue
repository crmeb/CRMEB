<!--
  履约信息卡片（通用数据驱动版，合并原 TicketInfo / CouponCode）
  支持 5 个 componentKey 复用：card_list / download_link / info_list:fulfillment / ticket_info / coupon_code
  数据来源：section.props.fulfillment，支持两种格式：
  1. fields 数组格式（推荐）：后端直接下发 fields 数组，前端遍历渲染
  2. 类型特定格式：按 fulfillment.type 自动转换为 fields 数组
     - cardkey:  卡密列表 {type:'cardkey', cards:[{card_no, card_pwd, valid_days, status}]}
     - virtual:   网盘链接 {type:'virtual', download_url, download_password, expire_time}
     - coupon:    优惠券码 {type:'coupon', coupon_code, face_value, expire_time}（券码高亮）
     - ticket:    票务信息 {type:'ticket', venue, show_time, seat, ticket_code}
     - logistics: 物流信息 {type:'logistics', delivery_name, delivery_id, ...}（兜底，主物流走 LogisticsTimeline）
-->
<template>
  <view v-if="cards.length > 0 || visibleFields.length > 0" class="info-card">
    <view v-if="title" class="card-title">{{ title }}</view>
    <!-- 卡密类：按卡片分组展示 -->
    <template v-if="fulfillmentType === 'cardkey' && cards.length > 0">
      <view
        v-for="(card, idx) in cards"
        :key="'card-' + idx"
        class="card-key-item"
      >
        <view class="info-row acea-row row-between">
          <text class="info-label">{{ $t('卡号') }}</text>
          <text class="info-value" selectable>{{ card.card_no || '-' }}</text>
        </view>
        <view class="info-row acea-row row-between">
          <text class="info-label">{{ $t('卡密') }}</text>
          <text class="info-value" selectable>{{ card.card_pwd || '-' }}</text>
        </view>
        <view v-if="card.valid_days" class="info-row acea-row row-between">
          <text class="info-label">{{ $t('有效期') }}</text>
          <text class="info-value">{{ card.valid_days }}{{ $t('天') }}</text>
        </view>
        <view class="info-row acea-row row-between">
          <text class="info-label">{{ $t('状态') }}</text>
          <text class="info-value" :class="card.status === 'unused' ? 'status-unused' : 'status-used'">
            {{ card.status === 'unused' ? $t('未使用') : $t('已使用') }}
          </text>
        </view>
      </view>
    </template>
    <!-- 通用字段列表展示 -->
    <template v-else>
      <view
        v-for="(field, index) in visibleFields"
        :key="index"
        class="info-row acea-row row-between"
      >
        <text class="info-label">{{ field.label }}</text>
        <view class="info-value">
          <!-- type=link：蓝色可点击，点击复制到剪贴板 -->
          <text
            v-if="field.type === 'link'"
            class="link-text"
            selectable
            @click="onLinkClick(field)"
          >{{ field.value }}</text>
          <!-- type=highlight：主题色大字号高亮（券码等） -->
          <text
            v-else-if="field.type === 'highlight'"
            class="highlight-text"
            selectable
          >{{ field.value }}</text>
          <!-- 默认普通文本 -->
          <text
            v-else
            :selectable="!!field.selectable"
          >{{ field.value }}</text>
        </view>
      </view>
    </template>
  </view>
</template>

<script>
/**
 * 履约信息卡片组件
 * 通用数据驱动，合并原 TicketInfo（票务）和 CouponCode（券码）功能
 * 通过 componentKey 多 key 复用，匹配 5 种 section.component
 */
export default {
  name: 'FulfillmentCard',
  // 多 key 复用：卡密/网盘/履约信息/票务/券码
  componentKey: ['card_list', 'download_link', 'info_list:fulfillment', 'ticket_info', 'coupon_code'],
};
</script>

<script setup>
import { computed } from 'vue';
import i18n from '@/utils/lang.js';

const props = defineProps({
  /** section 对象，含 props.fulfillment 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前 FulfillmentCard 不依赖全局状态） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** fulfillment 对象（后端下发在 section.props.fulfillment） */
const fulfillment = computed(() => p.value.fulfillment || null);
/**
 * 卡片标题
 * 优先级：fulfillment.title（后端下发）> titleMap（按 component 复合 key）> 空
 */
const title = computed(() => {
  // 后端下发 title 优先
  if (fulfillment.value && fulfillment.value.title) return fulfillment.value.title;
  // titleMap 按 component 复合 key 兜底
  var comp = (props.section && props.section.component) || '';
  var key = (props.section && props.section.key) || '';
  var composite = comp + ':' + key;
  var titleMap = {
    card_list: '卡密信息',
    download_link: '网盘信息',
    'info_list:fulfillment': '履约信息',
    ticket_info: '票务信息',
    coupon_code: '券码信息',
  };
  return i18n.t(titleMap[composite] || titleMap[comp] || '');
});
/** 履约类型（从 fulfillment.type 取，与 section.component 独立） */
const fulfillmentType = computed(() => (fulfillment.value && fulfillment.value.type) || '');
/** 卡密列表（仅 cardkey 类型） */
const cards = computed(() => (fulfillment.value && fulfillment.value.cards) || []);
/** 待展示字段列表：优先用 fields 数组，否则按 type 自动转换 */
const visibleFields = computed(() => {
  var f = fulfillment.value;
  if (!f) return [];
  // 优先使用后端下发的 fields 数组
  if (f.fields && f.fields.length > 0) {
    return f.fields.filter(function (item) {
      return item && item.value !== '' && item.value !== null && item.value !== undefined;
    });
  }
  // 按 type 自动转换为 fields 数组
  return buildFieldsFromType();
});

/**
 * 按 fulfillment.type 将类型特定结构转换为通用 fields 数组
 * @return {Array} fields 数组
 */
function buildFieldsFromType() {
  if (!fulfillment.value) return [];
  var f = fulfillment.value;
  var fields = [];
  switch (f.type) {
    case 'virtual':
      // 网盘产品：网盘链接
      if (f.download_url) {
        fields.push({ label: i18n.t('网盘链接'), value: f.download_url, type: 'link', selectable: true });
      }
      if (f.download_password) {
        fields.push({ label: i18n.t('提取码'), value: f.download_password, selectable: true });
      }
      if (f.expire_time) {
        fields.push({ label: i18n.t('有效期至'), value: formatTime(f.expire_time) });
      }
      break;
    case 'coupon':
      // 优惠券商品：券码（高亮显示）
      if (f.coupon_code) {
        fields.push({ label: i18n.t('券码'), value: f.coupon_code, type: 'highlight', selectable: true });
      }
      if (f.face_value) {
        fields.push({ label: i18n.t('面值'), value: '¥' + f.face_value });
      }
      if (f.expire_time) {
        fields.push({ label: i18n.t('有效期至'), value: formatTime(f.expire_time) });
      }
      break;
    case 'ticket':
      // 票务商品
      if (f.venue) fields.push({ label: i18n.t('场馆'), value: f.venue });
      if (f.show_time) fields.push({ label: i18n.t('演出时间'), value: formatTime(f.show_time) });
      if (f.seat) fields.push({ label: i18n.t('座位'), value: f.seat });
      if (f.ticket_code) fields.push({ label: i18n.t('票号'), value: f.ticket_code, selectable: true });
      break;
    case 'cardkey':
      // 卡密类：cards 数组在模板中单独渲染，fields 不重复
      break;
    case 'logistics':
      // 物流信息（兜底，主物流走 LogisticsTimeline）
      if (f.delivery_name) fields.push({ label: i18n.t('快递公司'), value: f.delivery_name });
      if (f.delivery_id) fields.push({ label: i18n.t('快递单号'), value: f.delivery_id, selectable: true });
      break;
    default:
      // 未知类型：尝试通用转换，将对象键值对转为 fields
      for (var key in f) {
        if (key === 'type' || key === 'title') continue;
        var val = f[key];
        if (val !== null && val !== undefined && val !== '' && typeof val !== 'object') {
          fields.push({ label: key, value: String(val) });
        }
      }
      break;
  }
  return fields;
}

/**
 * 格式化时间戳为可读字符串（秒级时间戳）
 * @param {number} ts 时间戳（秒）
 * @return {string} 格式化后的时间 YYYY-MM-DD HH:mm
 */
function formatTime(ts) {
  if (!ts) return '';
  var t = typeof ts === 'number' ? ts : parseInt(ts);
  if (!t) return '';
  var d = new Date(t * 1000);
  var pad = function (n) { return n < 10 ? '0' + n : '' + n; };
  return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

/**
 * 链接类型字段点击：复制到剪贴板
 * @param {object} field 字段对象
 */
function onLinkClick(field) {
  if (!field.value) return;
  // #ifdef H5
  if (typeof navigator !== 'undefined' && navigator.clipboard) {
    navigator.clipboard.writeText(field.value);
  }
  // #endif
  // #ifndef H5
  uni.setClipboardData({
    data: String(field.value),
    success: () => {
      uni.showToast({ title: i18n.t('链接已复制'), icon: 'none' });
    },
  });
  // #endif
}
</script>

<style scoped>
/* 统一卡片样式（与引擎其他组件一致） */
.info-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row { display: flex; justify-content: space-between; align-items: center; padding: 12rpx 0; font-size: 26rpx; }
.info-label { color: #999; flex-shrink: 0; }
.info-value { color: #333; text-align: right; }

/* 链接类型字段：蓝色可点击复制 */
.link-text { color: #0066cc; word-break: break-all; }
/* 高亮类型字段：主题色大字号（券码等） */
.highlight-text { color: var(--view-theme, #e93323); font-weight: 600; font-size: 30rpx; }

/* 卡密列表 */
.card-key-item { background: #f9f9fb; border-radius: 12rpx; padding: 20rpx; margin-bottom: 16rpx; }
.card-key-item:last-child { margin-bottom: 0; }
.status-unused { color: #07c160; font-weight: 500; }
.status-used { color: #999; }
</style>
