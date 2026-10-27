<!--
  底部操作按钮组组件
  展示订单操作按钮（如去支付、取消订单、确认收货、申请退款等），点击触发对应 action
  样式对齐旧版订单详情 footer：固定于屏幕底部 + 底部安全区适配
  - 右侧：主操作胶囊按钮
  - 左侧：placement=more 的次要操作收入「更多」菜单（如申请开票/查看发票）
  数据来源：section.props.buttons（每项含 {text, action, primary, params, placement?}）
-->
<template>
  <view class="btn-group" v-if="hasAnyButton">
    <!-- 左下角「更多」（开票等次要操作） -->
    <view class="more-wrap" v-if="moreButtons.length">
      <view class="more" @tap.stop="toggleMore">
        {{ $t('更多') }}
        <text class="iconfont icon-xiangshang"></text>
      </view>
      <view class="more-box" v-if="moreOpen">
        <view
          class="more-btn"
          v-for="(btn, idx) in moreButtons"
          :key="'more-' + idx"
          @tap.stop="onTap(btn)"
        >{{ btn.text }}</view>
      </view>
    </view>
    <view v-else class="more-placeholder"></view>

    <view class="right-btn">
      <view
        v-for="(btn, idx) in mainButtons"
        :key="'main-' + idx"
        class="btn-item"
        :class="btn.primary ? 'btn-primary' : 'btn-default'"
        @tap="onTap(btn)"
      >
        {{ btn.text }}
      </view>
    </view>

    <view class="more-mask" v-if="moreOpen" @tap="moreOpen = false"></view>
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
import { computed, ref, watch } from 'vue';

const props = defineProps({
  /** section 对象，含 props.buttons 和 _ctx 全局状态 */
  section: { type: Object, required: true },
});

const emit = defineEmits(['action']);

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 按钮数组 */
const buttons = computed(() => p.value.buttons || []);
/** placement=more 收入左下角「更多」 */
const moreButtons = computed(() =>
  (buttons.value || []).filter((btn) => (btn.placement || '') === 'more')
);
/** 右侧主操作按钮 */
const mainButtons = computed(() =>
  (buttons.value || []).filter((btn) => (btn.placement || '') !== 'more')
);
const hasAnyButton = computed(() => moreButtons.value.length > 0 || mainButtons.value.length > 0);

const moreOpen = ref(false);

watch(buttons, () => {
  moreOpen.value = false;
});

function toggleMore() {
  moreOpen.value = !moreOpen.value;
}

/**
 * 按钮点击事件
 * @param {object} btn 按钮对象
 */
function onTap(btn) {
  moreOpen.value = false;
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
  justify-content: space-between;
  padding: 20rpx 30rpx;
  padding-bottom: calc(20rpx + constant(safe-area-inset-bottom)); /*兼容 IOS<11.2*/
  padding-bottom: calc(20rpx + env(safe-area-inset-bottom)); /*兼容 IOS>11.2*/
  background: #fff;
  border-top: 1rpx solid #f5f5f5;
  z-index: 20;
}
.more-placeholder { width: 1rpx; }
.more-wrap { position: relative; z-index: 22; }
.more {
  font-size: 26rpx;
  color: #666;
  display: flex;
  align-items: center;
}
.more .iconfont { font-size: 22rpx; margin-left: 4rpx; }
.more-box {
  position: absolute;
  left: 0;
  bottom: 70rpx;
  min-width: 180rpx;
  background: #fff;
  border-radius: 12rpx;
  box-shadow: 0 4rpx 24rpx rgba(0, 0, 0, 0.12);
  overflow: hidden;
  z-index: 23;
}
.more-btn {
  padding: 24rpx 30rpx;
  font-size: 26rpx;
  color: #333;
  white-space: nowrap;
  text-align: center;
}
.more-btn + .more-btn { border-top: 1rpx solid #f5f5f5; }
.more-mask {
  position: fixed;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 21;
  background: transparent;
}
.right-btn {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  flex: 1;
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
