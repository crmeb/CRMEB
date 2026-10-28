<!--
  自定义表单回显组件（DIY 通用化契约版）
  展示用户下单时填写的自定义字段（如观众姓名、联系方式等）
  数据来源：section.props.items（表单字段数组）
-->
<template>
  <view class="info-card custom-form-card" v-if="items && items.length > 0">
    <view class="card-title">{{ $t('自定义信息') }}</view>
    <template v-for="(item, idx) in items" :key="idx">
      <!-- 图片字段：名称左、图片右（多图可换行） -->
      <view class="info-row info-row--image" v-if="isImageItem(item)">
        <text class="info-label">{{ item.title || item.label }}</text>
        <view class="info-images">
          <image
            v-for="(url, i) in imageList(item)"
            :key="i"
            class="info-image"
            :src="url"
            mode="aspectFill"
            @click="previewImages(item, i)"
          />
        </view>
      </view>
      <!-- 文本字段：名称左、内容右，与订单信息卡片左右分离一致 -->
      <view class="info-row" v-else>
        <text class="info-label">{{ item.title || item.label }}</text>
        <text class="info-value">{{ item.value }}</text>
      </view>
    </template>
  </view>
</template>

<script>
/**
 * 自定义表单回显组件
 * 展示用户下单时填写的自定义字段
 * 字段结构：{title/label, type, value}；type=img 为图片字段（存量订单无 type，按图片链接特征兜底）
 * 通过 componentKey='custom_form' 匹配 section.component
 */
export default {
  name: 'CustomForm',
  // DIY 引擎 componentKey 标识
  componentKey: 'custom_form',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.items 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前 CustomForm 不依赖全局状态） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 表单字段数组（后端下发在 section.props.items） */
const items = computed(() => p.value.items || []);

/** 图片扩展名判断 */
var IMAGE_EXT_RE = /\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i;

/** 规范化字段值为图片 URL 数组（字符串 / 数组均可） */
function toUrlList(value) {
  if (!value) return [];
  var list = Array.isArray(value) ? value : [value];
  return list.filter(function (v) {
    return typeof v === 'string' && v.indexOf('http') === 0;
  });
}

/** 是否图片字段：显式 type=img，或存量订单无类型但值为图片链接 */
function isImageItem(item) {
  if (item.type === 'img') return true;
  if (item.type) return false;
  var urls = toUrlList(item.value);
  return urls.length > 0 && urls.every(function (v) {
    return IMAGE_EXT_RE.test(v) || v.indexOf('/uploads/') !== -1;
  });
}

/** 图片字段的可展示 URL 列表 */
function imageList(item) {
  return toUrlList(item.value);
}

/** 点击预览大图 */
function previewImages(item, index) {
  var urls = imageList(item);
  if (!urls.length) return;
  uni.previewImage({ urls: urls, current: index });
}
</script>

<style scoped>
.custom-form-card { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.card-title { font-size: 30rpx; font-weight: bold; color: #333; margin-bottom: 20rpx; }
.info-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 12rpx 0;
  font-size: 26rpx;
  gap: 24rpx;
}
.info-label {
  color: #999;
  flex-shrink: 0;
  max-width: 40%;
  line-height: 1.5;
}
.info-value {
  color: #333;
  flex: 1;
  min-width: 0;
  line-height: 1.5;
  word-break: break-all;
  text-align: right;
}
/* 图片字段：名称左、缩略图右对齐 */
.info-row--image { align-items: flex-start; }
.info-images {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  flex: 1;
  min-width: 0;
}
.info-image {
  width: 120rpx;
  height: 120rpx;
  border-radius: 12rpx;
  margin: 0 0 12rpx 12rpx;
  background: #f5f5f5;
}
</style>
