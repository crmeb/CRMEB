<!--
  表单构建器基元（form_builder）
  根据后端 custom_form JSON 动态渲染表单字段
  覆盖50%以上的差异化需求
-->
<template>
  <view v-if="fields && fields.length > 0" class="form-builder">
    <view v-for="(field, idx) in fields" :key="idx" class="form-field">
      <view class="field-label">
        {{ field.title }}
        <text v-if="field.required" class="required">*</text>
      </view>
      <!-- 文本输入 -->
      <input v-if="field.type === 'input' || field.type === 'phone' || field.type === 'id_card'"
        v-model="formData[field.name]"
        :placeholder="field.placeholder || $t('请输入') + field.title"
        class="field-input" />
      <!-- 多行文本 -->
      <textarea v-if="field.type === 'textarea'"
        v-model="formData[field.name]"
        :placeholder="field.placeholder || $t('请输入') + field.title"
        class="field-textarea" />
      <!-- 下拉选择 -->
      <picker v-if="field.type === 'select'" mode="selector"
        :range="field.options || []" range-key="label"
        @change="(e) => formData[field.name] = (field.options || [])[e.detail.value]">
        <view class="field-picker">
          {{ formData[field.name] || $t('请选择') + field.title }}
        </view>
      </picker>
      <!-- 日期选择 -->
      <picker v-if="field.type === 'date'" mode="date"
        @change="(e) => formData[field.name] = e.detail.value">
        <view class="field-picker">
          {{ formData[field.name] || $t('请选择') + field.title }}
        </view>
      </picker>
    </view>
  </view>
</template>

<script setup>
import { ref, watch } from 'vue';
import i18n from '@/utils/lang.js';

defineOptions({ name: 'PrimitiveFormBuilder' });

const props = defineProps({
  /** 后端 custom_form JSON 解析后的字段定义数组 */
  fields: { type: Array, default: () => [] },
  /** 外部传入的表单数据 */
  value: { type: Object, default: () => ({}) },
});

const formData = ref({});

watch(
  () => props.value,
  (val) => {
    formData.value = { ...val };
  },
  { immediate: true }
);

watch(
  () => props.fields,
  (val) => {
    // 用字段默认值初始化
    const initial = { ...formData.value };
    (val || []).forEach(f => {
      if (!(f.name in initial)) {
        initial[f.name] = f.default || '';
      }
    });
    formData.value = initial;
  },
  { immediate: true }
);

function getData() { return formData.value; }
/**
 * 校验协议：引擎 handleSubmit 提交前调用
 * 遍历必填字段，任一为空返回 {valid: false, msg}
 * @return {Object} {valid: boolean, msg?: string}
 */
function validate() {
  const fields = props.fields || [];
  for (const field of fields) {
    if (!field.required) continue;
    const val = formData.value[field.name];
    if (val === '' || val === null || val === undefined) {
      return { valid: false, msg: i18n.t('请填写') + field.title };
    }
  }
  return { valid: true };
}

/** 供引擎/父组件通过 ref 调用（提交前取数与校验） */
defineExpose({ getData, validate });
</script>

<style scoped>
.form-builder { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 0 30rpx; }
.form-field { padding: 25rpx 0; border-bottom: 1rpx solid #f0f0f0; }
.field-label { font-size: 28rpx; color: #333; margin-bottom: 12rpx; }
.required { color: var(--view-theme, #e93323); margin-left: 4rpx; }
.field-input { height: 80rpx; border: 1rpx solid #eee; border-radius: 8rpx; padding: 0 20rpx; font-size: 28rpx; }
.field-textarea { height: 160rpx; border: 1rpx solid #eee; border-radius: 8rpx; padding: 15rpx 20rpx; font-size: 28rpx; }
.field-picker { height: 80rpx; line-height: 80rpx; border: 1rpx solid #eee; border-radius: 8rpx; padding: 0 20rpx; font-size: 28rpx; color: #999; }
</style>
