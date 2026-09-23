<!--
  订单自定义表单
  根据后端 custom_form 动态渲染必填信息字段
  字段结构：{ label, field, type, options, required }
-->
<template>
  <view class="custom-form-section" v-if="visible && fields.length > 0">
    <view class="section-title">{{ $t('必填信息') }}</view>
    <view class="form-list">
      <view class="form-item" v-for="(field, index) in fields" :key="index">
        <view class="form-label">
          {{ field.label }}
          <text class="required" v-if="field.required">*</text>
        </view>
        <view class="form-input-wrap">
          <!-- 单行文本 -->
          <input
            v-if="field.type === 'input'"
            class="form-input"
            :placeholder="$t('请输入') + field.label"
            :value="formData[field.field]"
            @input="onInput(field.field, $event)"
          />
          <!-- 下拉选择 -->
          <picker
            v-else-if="field.type === 'picker'"
            :range="field.options"
            range-key="label"
            @change="onPickerChange(field, $event)"
          >
            <view class="form-picker">
              {{ formData[field.field] || $t('请选择') }}
              <text class="picker-arrow">›</text>
            </view>
          </picker>
          <!-- 多行文本 -->
          <textarea
            v-else-if="field.type === 'textarea'"
            class="form-textarea"
            :placeholder="$t('请输入') + field.label"
            :value="formData[field.field]"
            @input="onInput(field.field, $event)"
          ></textarea>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { computed } from 'vue';
import i18n from '@/utils/lang.js';

defineOptions({ name: 'OrderCustomForm' });

const props = defineProps({
  /** 字段定义数组 */
  fields: { type: Array, default: () => [] },
  /** 表单数据对象（双向绑定） */
  modelValue: { type: Object, default: () => ({}) },
  /** 是否显示（受 features.custom_form 控制） */
  visible: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'change']);

/** 当前表单数据（基于 value 拷贝避免直接修改 prop） */
const formData = computed(() => props.modelValue || {});

/** 文本类输入事件，更新对应字段并通知父组件 */
function onInput(field, e) {
  var val = e.detail.value;
  var next = { ...formData.value, [field]: val };
  emit('update:modelValue', next);
  emit('change', next);
}
/** picker 选择事件，根据 options 取出 value */
function onPickerChange(field, e) {
  var option = field.options[e.detail.value] || {};
  var next = { ...formData.value, [field.field]: option.value };
  emit('update:modelValue', next);
  emit('change', next);
}
/** 校验必填字段是否已填写 */
function validate() {
  for (var i = 0; i < props.fields.length; i++) {
    var field = props.fields[i];
    if (field.required) {
      var val = formData.value[field.field];
      if (!val || (typeof val === 'string' && val.trim() === '')) {
        uni.showToast({ title: i18n.t('请填写') + field.label, icon: 'none' });
        return false;
      }
    }
  }
  return true;
}

/** 引擎 handleSubmit 提交前通过 customFormRef.validate() 调用 */
defineExpose({ validate });
</script>

<style scoped>
/* 自定义表单 */
.custom-form-section {
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
.form-list {
  border-top: 1px dashed #eee;
  padding-top: 16rpx;
}
.form-item {
  padding: 16rpx 0;
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
.form-input-wrap {
  display: flex;
  align-items: center;
}
.form-input {
  flex: 1;
  height: 80rpx;
  background: #f8f8f8;
  border-radius: 8rpx;
  padding: 0 24rpx;
  font-size: 28rpx;
}
.form-textarea {
  flex: 1;
  height: 160rpx;
  background: #f8f8f8;
  border-radius: 8rpx;
  padding: 20rpx 24rpx;
  font-size: 28rpx;
}
.form-picker {
  flex: 1;
  height: 80rpx;
  background: #f8f8f8;
  border-radius: 8rpx;
  padding: 0 24rpx;
  font-size: 28rpx;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: #999;
}
.picker-arrow {
  font-size: 36rpx;
}
</style>
