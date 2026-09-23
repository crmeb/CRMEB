<!--
  订单备注输入
  限制 100 字以内的备注说明
-->
<template>
  <view class="remark-section" v-if="visible">
    <view class="section-title">{{ $t('备注说明') }}</view>
    <textarea
      class="remark-textarea"
      :placeholder="$t('填写备注信息，100字以内')"
      maxlength="100"
      :value="modelValue"
      @input="onInput"
    ></textarea>
  </view>
</template>

<script setup>
defineOptions({ name: 'OrderRemarkInput' });

defineProps({
  /** 备注内容（双向绑定） */
  modelValue: { type: String, default: '' },
  /** 是否显示（受 features.remark 控制） */
  visible: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'change']);

/** 输入事件，向父组件同步备注内容 */
function onInput(e) {
  var val = e.detail.value;
  emit('update:modelValue', val);
  emit('change', val);
}
</script>

<style scoped>
/* 订单备注 */
.remark-section {
  margin: 20rpx;
  background: #fff;
  border-radius: 12rpx;
  padding: 24rpx 30rpx;
}
.section-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #282828;
  margin-bottom: 20rpx;
}
.remark-textarea {
  width: 100%;
  height: 120rpx;
  background: #f8f8f8;
  border-radius: 8rpx;
  padding: 20rpx;
  font-size: 28rpx;
  box-sizing: border-box;
}
</style>
