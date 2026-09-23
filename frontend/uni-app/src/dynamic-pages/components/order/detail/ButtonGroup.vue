<!--
  底部操作按钮组组件
  展示订单操作按钮（如去支付、取消订单、确认收货、申请退款等），点击触发对应 action
  样式对齐旧版订单详情 footer：固定于屏幕底部 + 底部安全区适配 + 右对齐胶囊按钮
  数据来源：section.props.buttons（每项含 {text, action, primary, params}）
-->
<template>
  <view class="btn-group" v-if="buttons && buttons.length > 0">
    <view
      v-for="(btn, idx) in buttons"
      :key="idx"
      class="btn-item"
      :class="btn.primary ? 'btn-primary' : 'btn-default'"
      @tap="onTap(btn)"
    >
      {{ btn.text }}
    </view>
  </view>
</template>

<script>
/**
 * 底部操作按钮组组件
 * 按钮点击后 emit('action', { action, params }) 由引擎统一处理
 * 通过 componentKey='button_group' 匹配 section.component
 */
export default {
  name: 'ButtonGroup',
  // 匹配 section.component='button_group'
  componentKey: 'button_group',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.buttons 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

const emit = defineEmits(['action']);

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 按钮数组 */
const buttons = computed(() => p.value.buttons || []);

/**
 * 按钮点击事件
 * @param {object} btn 按钮对象
 */
function onTap(btn) {
  emit('action', {
    action: btn.action,
    params: btn.params || {},
  });
}
</script>

<style scoped>
/* 固定底部操作栏（对齐旧版 .order-details .footer） */
.btn-group {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 20rpx 30rpx;
  padding-bottom: calc(20rpx + constant(safe-area-inset-bottom)); /*兼容 IOS<11.2*/
  padding-bottom: calc(20rpx + env(safe-area-inset-bottom)); /*兼容 IOS>11.2*/
  background: #fff;
  border-top: 1rpx solid #f5f5f5;
  z-index: 20;
}
/* 按钮胶囊样式（对齐旧版 .footer .bnt） */
.btn-item {
  height: 60rpx;
  line-height: 60rpx;
  padding: 0 30rpx;
  font-size: 27rpx;
  border-radius: 999rpx;
  white-space: nowrap;
}
.btn-item + .btn-item { margin-left: 18rpx; }
.btn-default { color: #666; border: 1rpx solid #ccc; background: #fff; }
.btn-primary { color: #fff; background: var(--view-theme, #e93323); }
</style>
