<template>
  <!-- 渲染错误边界：form-create 子组件渲染异常时显示错误信息，避免整页静默空白 -->
  <el-alert
    v-if="renderError"
    type="error"
    :title="'表单渲染异常'"
    :description="renderError"
    show-icon
    :closable="false"
  />
  <form-create v-else v-model:api="fapi" :rule="formRules" :option="option" class="schema-form common-form-create" />
</template>

<script setup>
/**
 * 通用页面表单（复用现有 form-create 体系）
 *
 * 消费后端 create_form() 标准规则结构（{rules, title, action, method}），
 * 隐藏内置提交按钮，由抽屉/页脚统一触发 submit() 收集表单数据。
 * 提交目标由父级决定：schema.action 为空时默认提交网关 save 端点。
 */
import { ref, computed, onErrorCaptured } from 'vue';
import formCreateFactory from '@form-create/element-ui';
import SchemaEditor from './SchemaEditor.vue';
import SchemaCoupon from './SchemaCoupon.vue';
import SchemaCheckboxGroup from './SchemaCheckboxGroup.vue';

defineOptions({ name: 'SchemaForm' });

// 注册富文本编辑器自定义组件（后端 Form::editor() 生成 type=SchemaEditor 规则）
// 注册优惠券标签选择器自定义组件（后端 Form::couponSelect() 生成 type=SchemaCoupon 规则）
// 注册多列勾选组自定义组件（后端 Form::checkboxGroup() 生成 type=SchemaCheckboxGroup 规则）
// 注意：component 方法在默认导出对象上（$form() 返回的 maker 无此方法）
formCreateFactory.component('SchemaEditor', SchemaEditor);
formCreateFactory.component('SchemaCoupon', SchemaCoupon);
formCreateFactory.component('SchemaCheckboxGroup', SchemaCheckboxGroup);

const props = defineProps({
  /** 表单规则结构 {rules, title, action, method} */
  schema: {
    type: Object,
    required: true,
  },
});

/** form-create 实例 */
const fapi = ref(null);

/** 渲染错误信息（onErrorCaptured 捕获，展示于错误边界） */
const renderError = ref('');

// 捕获子组件（form-create 及其渲染的自定义组件）渲染错误
onErrorCaptured((err) => {
  renderError.value = String(err?.message || err) + (err?.stack ? '\n' + String(err.stack).slice(0, 400) : '');
  return false;
});

/** 渲染配置：隐藏内置按钮，统一由外部触发提交；与系统 modalForm 配置保持一致 */
const option = {
  form: {
    labelWidth: '105px',
  },
  resetBtn: false,
  submitBtn: false,
  // 数字输入框去除 +/- 步进控件，呈普通输入框形态（文字左对齐）
  global: {
    inputNumber: {
      props: {
        controls: false,
      },
    },
  },
};

/** 规则（与系统 modalForm 一致：title 统一拼接中文冒号，label 右对齐带冒号） */
const formRules = computed(() => {
  const list = props.schema?.rules || [];
  return list.map((rule) => {
    if (rule && rule.title && !String(rule.title).endsWith('：')) {
      return { ...rule, title: rule.title + '：' };
    }
    return rule;
  });
});

/**
 * 收集并校验表单数据
 * @returns {Promise<object>} 校验通过时 resolve 表单数据，失败时 reject
 */
function submit() {
  return new Promise((resolve, reject) => {
    if (!fapi.value) {
      reject(new Error('表单尚未就绪'));
      return;
    }
    fapi.value.submit((formData) => resolve(formData));
  });
}

defineExpose({ submit });
</script>
