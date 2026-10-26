<!--
  订单自定义表单
  根据后端 custom_form 动态渲染表单信息字段
  字段结构：{ label, field, type, options, required }
  type 为管理端「自定义表单」类型：text 文本框 / number 数字 / email 邮件 /
  data 日期 / time 时间 / id 身份证 / phone 手机号 / img 图片，
  兼容历史值 input / textarea / picker
-->
<template>
  <view class="custom-form-section" v-if="visible && fields.length > 0">
    <view class="section-title">{{ $t('表单信息') }}</view>
    <view class="form-list">
      <view class="form-item" v-for="(field, index) in fields" :key="index" :class="{ 'is-bordered': index > 0 }">
        <view class="form-label">
          <text class="required" v-if="field.required">*</text>
          <text class="required-holder" v-else></text>
          {{ field.label }}
        </view>
        <view class="form-input-wrap">
          <!-- 文本类：文本框/数字/邮件/身份证/手机号，按类型切换键盘 -->
          <input
            v-if="isTextInput(field)"
            class="form-input"
            :class="{ 'is-focus': focusField === field.field }"
            :type="textInputKeyboard(field)"
            :placeholder="$t('请输入') + field.label"
            :value="formData[field.field]"
            @focus="focusField = field.field"
            @blur="focusField = ''"
            @input="onInput(field.field, $event)"
          />
          <!-- 下拉选择（自定义 options，历史值） -->
          <picker
            v-else-if="field.type === 'picker'"
            class="form-picker-wrap"
            :range="field.options"
            range-key="label"
            @change="onPickerChange(field, $event)"
          >
            <view class="form-picker" :class="{ 'is-set': formData[field.field] }">
              <view class="pick-main">
                <text class="iconfont pick-icon icon-fenlei1"></text>
                <text class="pick-value">{{ pickLabel(field) || $t('请选择') }}</text>
              </view>
              <text class="picker-arrow">›</text>
            </view>
          </picker>
          <!-- 日期（管理端类型值 data，兼容 date） -->
          <picker
            v-else-if="field.type === 'data' || field.type === 'date'"
            class="form-picker-wrap"
            mode="date"
            @change="onValueChange(field.field, $event)"
          >
            <view class="form-picker" :class="{ 'is-set': formData[field.field] }">
              <view class="pick-main">
                <text class="iconfont pick-icon icon-rilitubiao"></text>
                <text class="pick-value">{{ formData[field.field] || $t('请选择') + field.label }}</text>
              </view>
              <text class="picker-arrow">›</text>
            </view>
          </picker>
          <!-- 时间 -->
          <picker
            v-else-if="field.type === 'time'"
            class="form-picker-wrap"
            mode="time"
            @change="onValueChange(field.field, $event)"
          >
            <view class="form-picker" :class="{ 'is-set': formData[field.field] }">
              <view class="pick-main">
                <text class="iconfont pick-icon icon-shijian"></text>
                <text class="pick-value">{{ formData[field.field] || $t('请选择') + field.label }}</text>
              </view>
              <text class="picker-arrow">›</text>
            </view>
          </picker>
          <!-- 图片上传 -->
          <view v-else-if="field.type === 'img'" class="form-upload">
            <image
              v-if="formData[field.field]"
              class="upload-preview"
              :src="formData[field.field]"
              mode="aspectFill"
              @click="previewUpload(formData[field.field])"
            />
            <view v-else class="upload-add" @click="uploadImage(field.field)">+</view>
            <view v-if="formData[field.field]" class="upload-del" @click.stop="removeImage(field.field)">×</view>
          </view>
          <!-- 多行文本 -->
          <textarea
            v-else-if="field.type === 'textarea'"
            class="form-textarea"
            :class="{ 'is-focus': focusField === field.field }"
            :placeholder="$t('请输入') + field.label"
            :value="formData[field.field]"
            @focus="focusField = field.field"
            @blur="focusField = ''"
            @input="onInput(field.field, $event)"
          ></textarea>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { computed, ref } from 'vue';
import i18n from '@/utils/lang.js';
import util from '@/utils/util.js';

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

/** 当前聚焦的输入字段（用于高亮样式） */
const focusField = ref('');

/** 文本类输入类型（input 为历史值，其余为管理端类型） */
var TEXT_INPUT_TYPES = ['input', 'text', 'number', 'email', 'id', 'phone'];
/** 各类型格式校验规则，返回错误提示文案，通过返回空串 */
var FORMAT_RULES = {
  number: /^[+-]?\d+(\.\d+)?$/,
  email: /^[\w.+-]+@[\w-]+(\.[\w-]+)+$/,
  phone: /^1[3-9]\d{9}$/,
  id: /(^\d{15}$)|(^\d{17}[\dXx]$)/,
};

/** 是否文本类输入框 */
function isTextInput(field) {
  return TEXT_INPUT_TYPES.indexOf(field.type) !== -1;
}
/** 文本类输入键盘：数字/手机号用数字键盘，其余默认键盘 */
function textInputKeyboard(field) {
  return field.type === 'number' || field.type === 'phone' ? 'number' : 'text';
}

/** 下拉字段按已选 value 反查选项文案（无匹配时回退显示原值） */
function pickLabel(field) {
  var val = formData.value[field.field];
  if (val === undefined || val === null || val === '') return '';
  var opt = (field.options || []).find(function (o) { return o.value === val; });
  return opt ? opt.label : String(val);
}

/** 更新字段值并通知父组件 */
function setFieldValue(field, val) {
  var next = { ...formData.value, [field]: val };
  emit('update:modelValue', next);
  emit('change', next);
}

/** 文本类输入事件 */
function onInput(field, e) {
  setFieldValue(field, e.detail.value);
}
/** picker 选择事件，根据 options 取出 value */
function onPickerChange(field, e) {
  var option = field.options[e.detail.value] || {};
  setFieldValue(field.field, option.value);
}
/** 日期/时间选择事件，直接取选中值 */
function onValueChange(field, e) {
  setFieldValue(field, e.detail.value);
}
/** 上传图片字段：选择并上传后存图片 URL */
function uploadImage(field) {
  util.uploadImageOne('upload/image', function (res) {
    setFieldValue(field, res.data.url);
  });
}
/** 删除已上传图片 */
function removeImage(field) {
  setFieldValue(field, '');
}
/** 预览已上传图片 */
function previewUpload(url) {
  uni.previewImage({ urls: [url] });
}

/** 校验必填与格式，通过返回 true */
function validate() {
  for (var i = 0; i < props.fields.length; i++) {
    var field = props.fields[i];
    var val = formData.value[field.field];
    var isEmpty = !val || (typeof val === 'string' && val.trim() === '');
    if (field.required && isEmpty) {
      uni.showToast({ title: i18n.t('请填写') + field.label, icon: 'none' });
      return false;
    }
    if (isEmpty) continue;
    var rule = FORMAT_RULES[field.type];
    if (rule && !rule.test(String(val).trim())) {
      uni.showToast({ title: i18n.t('请输入正确的') + field.label, icon: 'none' });
      return false;
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
  padding: 24rpx 30rpx 12rpx;
}
.section-title {
  display: flex;
  align-items: center;
  font-size: 30rpx;
  font-weight: bold;
  color: #282828;
  margin-bottom: 8rpx;
}
/* 标题左侧主题色装饰条 */
.section-title::before {
  content: '';
  width: 6rpx;
  height: 28rpx;
  border-radius: 3rpx;
  background: var(--view-theme, #e93323);
  margin-right: 12rpx;
}
.form-list {
  padding-top: 4rpx;
}
.form-item {
  padding: 20rpx 0;
}
/* 相邻选择器小程序 WXSS 支持不稳，用 class 显式加分隔线 */
.form-item.is-bordered {
  border-top: 1px solid #f5f5f5;
}
.form-label {
  display: flex;
  align-items: center;
  font-size: 26rpx;
  color: #333;
  margin-bottom: 14rpx;
  line-height: 1;
}
/* 必填星号前置，与必填占位符等宽，保证所有标题左对齐 */
.form-label .required {
  display: inline-block;
  width: 24rpx;
  color: var(--view-theme, #e93323);
  font-size: 28rpx;
  line-height: 1;
}
.required-holder {
  display: inline-block;
  width: 24rpx;
}
.form-input-wrap {
  display: flex;
  align-items: center;
}
.form-input,
.form-textarea {
  flex: 1;
  width: 100%;
  background: #f8f8f8;
  border: 1px solid transparent;
  border-radius: 12rpx;
  padding: 0 24rpx;
  font-size: 28rpx;
  color: #282828;
  box-sizing: border-box;
  transition: background-color 0.2s, border-color 0.2s;
}
.form-input {
  height: 80rpx;
}
.form-textarea {
  height: 160rpx;
  padding: 20rpx 24rpx;
}
/* 聚焦态：白底 + 主题色描边 */
.form-input.is-focus,
.form-textarea.is-focus {
  background: #fff;
  border-color: var(--view-theme, #e93323);
}
/* 下拉/日期/时间选择器：占满整行（picker 包裹层不伸展会导致内容宽度塌缩） */
.form-picker-wrap {
  flex: 1;
  width: 100%;
  display: block;
}
.form-picker {
  width: 100%;
  height: 80rpx;
  background: #f8f8f8;
  border: 1px solid transparent;
  border-radius: 12rpx;
  padding: 0 24rpx;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: background-color 0.2s, border-color 0.2s;
}
.form-picker:active {
  background: #f2f2f2;
  border-color: var(--view-theme, #e93323);
}
.pick-main {
  display: flex;
  align-items: center;
  min-width: 0;
}
/* 日期/时间/下拉的类型图标 */
.pick-icon {
  font-size: 30rpx;
  color: var(--view-theme, #e93323);
  margin-right: 12rpx;
  line-height: 1;
}
.pick-value {
  font-size: 28rpx;
  color: #bbb;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
/* 已选择值用深色 */
.form-picker.is-set .pick-value {
  color: #282828;
}
.picker-arrow {
  font-size: 32rpx;
  color: #ccc;
  margin-left: 12rpx;
}
.form-upload {
  position: relative;
  width: 140rpx;
  height: 140rpx;
}
.upload-add {
  width: 100%;
  height: 100%;
  border: 1px dashed #ddd;
  border-radius: 12rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 60rpx;
  color: #c0c4cc;
}
.upload-preview {
  width: 100%;
  height: 100%;
  border-radius: 12rpx;
}
.upload-del {
  position: absolute;
  top: -14rpx;
  right: -14rpx;
  width: 36rpx;
  height: 36rpx;
  border-radius: 50%;
  background: #666;
  color: #fff;
  font-size: 28rpx;
  line-height: 36rpx;
  text-align: center;
}
</style>
