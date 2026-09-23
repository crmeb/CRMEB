<!--
  订单游客信息表单（门票专属）
  按 quantity 数量倍增渲染 N 组"姓名+身份证"输入卡片
  由后端 features.visitor_form 控制显示，fields 配置由后端 visitor_form_config 下发
  提交时通过 v-model 双向绑定回传 visitors 数组到 OrderCreateEngine
-->
<template>
  <view class="visitor-form-section" v-if="visible && quantity > 0">
    <view class="section-title">{{ $t('游客信息') }}</view>
    <view class="section-tip">{{ $t('每张门票需填写一位游客的实名信息') }}</view>
    <view class="visitor-card" v-for="(visitor, idx) in visitorList" :key="idx">
      <view class="visitor-card__header">
        <text class="visitor-card__title">{{ $t('游客') }}{{ idx + 1 }}</text>
      </view>
      <view class="visitor-card__body">
        <view class="form-item">
          <view class="form-label">
            {{ $t('姓名') }}
            <text class="required">*</text>
          </view>
          <input
            class="form-input"
            :placeholder="$t('请输入姓名')"
            :value="visitor.name"
            maxlength="20"
            @input="onInput(idx, 'name', $event)"
          />
        </view>
        <view class="form-item">
          <view class="form-label">
            {{ $t('身份证号') }}
            <text class="required">*</text>
          </view>
          <input
            class="form-input"
            :placeholder="$t('请输入身份证号')"
            :value="visitor.id_card"
            maxlength="18"
            @input="onInput(idx, 'id_card', $event)"
          />
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, watch } from 'vue';
import i18n from '@/utils/lang.js';

defineOptions({ name: 'OrderVisitorForm' });

const props = defineProps({
  /** 是否显示（由 features.visitor_form 控制） */
  visible: { type: Boolean, default: true },
  /** 购买数量，决定渲染的游客卡片数 */
  quantity: { type: Number, default: 1 },
  /** 字段配置（由后端 visitor_form_config 下发，预留扩展，当前固定姓名+身份证） */
  fields: { type: Array, default: () => [] },
});

// 本地维护的游客列表，长度与 quantity 一致
const visitorList = ref([]);

/** 数量变化时同步列表长度，保留已填数据 */
watch(
  () => props.quantity,
  () => {
    syncListLength();
  },
  { immediate: true }
);

/** 同步本地列表长度到 quantity，保留已填数据 */
function syncListLength() {
  var qty = Math.max(1, parseInt(props.quantity, 10) || 1);
  var list = visitorList.value.slice();
  if (list.length < qty) {
    for (var i = list.length; i < qty; i++) {
      list.push({ name: '', id_card: '' });
    }
  } else if (list.length > qty) {
    list = list.slice(0, qty);
  }
  visitorList.value = list;
}
/** 输入事件，更新对应游客字段 */
function onInput(idx, field, e) {
  var val = e.detail.value;
  // 身份证强制大写（末位可能是 X）
  if (field === 'id_card') {
    val = val.toUpperCase();
  }
  visitorList.value[idx][field] = val;
}
/**
 * 获取当前游客数据（供父组件通过 ref 调用）
 * @returns {Array} 游客数组 [{name, id_card}, ...]
 */
function getData() {
  return visitorList.value.slice();
}
/**
 * 校验：必填 + 身份证格式
 * @returns {boolean} 校验通过返回 true，失败 toast 提示并返回 false
 */
function validate() {
  for (var i = 0; i < visitorList.value.length; i++) {
    var v = visitorList.value[i];
    if (!v.name || !v.name.trim()) {
      uni.showToast({ title: i18n.t('请填写第') + (i + 1) + i18n.t('位游客姓名'), icon: 'none' });
      return false;
    }
    if (!v.id_card || !v.id_card.trim()) {
      uni.showToast({ title: i18n.t('请填写第') + (i + 1) + i18n.t('位游客身份证号'), icon: 'none' });
      return false;
    }
    if (!isValidIdCard(v.id_card)) {
      uni.showToast({ title: i18n.t('第') + (i + 1) + i18n.t('位游客身份证号格式不正确'), icon: 'none' });
      return false;
    }
  }
  return true;
}
/**
 * 身份证号格式校验
 * 支持 15 位（全数字）和 18 位（前17数字+末位数字/X）
 * @param {string} idCard 身份证号
 * @returns {boolean} 是否合法
 */
function isValidIdCard(idCard) {
  if (!idCard) return false;
  // 15 位：全数字（老式身份证）
  if (/^\d{15}$/.test(idCard)) return true;
  // 18 位：前 17 位数字 + 末位数字或 X
  if (/^\d{17}[\dX]$/.test(idCard)) return true;
  return false;
}

/** 引擎提交前通过 visitorFormRef.validate() / visitorFormRef.getData() 调用 */
defineExpose({ getData, validate });
</script>

<style scoped>
/* 游客信息表单 */
.visitor-form-section {
  margin: 20rpx;
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx 30rpx;
}
.section-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #282828;
  margin-bottom: 8rpx;
}
.section-tip {
  font-size: 24rpx;
  color: #999;
  margin-bottom: 20rpx;
}
.visitor-card {
  border: 1px solid #f0f0f0;
  border-radius: 10rpx;
  margin-bottom: 20rpx;
  overflow: hidden;
}
.visitor-card:last-child {
  margin-bottom: 0;
}
.visitor-card__header {
  background: #fafafa;
  padding: 16rpx 24rpx;
  border-bottom: 1px solid #f0f0f0;
}
.visitor-card__title {
  font-size: 28rpx;
  font-weight: 600;
  color: #333;
}
.visitor-card__body {
  padding: 16rpx 24rpx;
}
.form-item {
  padding: 12rpx 0;
}
.form-label {
  font-size: 26rpx;
  color: #333;
  margin-bottom: 12rpx;
}
.form-label .required {
  color: var(--view-theme, #e93323);
  margin-left: 4rpx;
}
.form-input {
  width: 100%;
  height: 80rpx;
  background: #f8f8f8;
  border-radius: 8rpx;
  padding: 0 24rpx;
  font-size: 28rpx;
  box-sizing: border-box;
}
</style>
