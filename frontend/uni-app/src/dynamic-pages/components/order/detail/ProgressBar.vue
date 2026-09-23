<!--
  进度条组件（节点式，DIY 通用化版）
  展示订单流程节点（提交订单→支付成功→商品发货→确认收货→订单完成）
  含节点图标和连线，已完成节点高亮
  数据来源：section.props.nodes
-->
<template>
  <view class="progress-bar-wrap mb-16" v-if="nodes && nodes.length > 0">
    <view class="nav">
      <view class="navCon acea-row row-between-wrapper">
        <view
          v-for="(node, idx) in nodes"
          :key="node.key || idx"
          :class="node.completed || node.active ? 'on' : ''"
        >{{ node.title }}</view>
      </view>
      <view class="progress acea-row row-between-wrapper">
        <template v-for="(node, idx) in nodes" :key="node.key || idx">
          <view
            class="iconfont"
            :class="[
              node.completed ? 'icon-webicon318' : 'icon-yuandianxiao',
              node.completed || node.active ? 'font-num' : ''
            ]"
          ></view>
          <view
            v-if="idx < nodes.length - 1"
            class="line"
            :class="nodes[idx + 1].completed ? 'bg-color' : ''"
          ></view>
        </template>
      </view>
    </view>
  </view>
</template>

<script>
/**
 * 进度条组件
 * 展示订单流程的节点式进度条
 * 节点状态：completed(已完成) / active(当前进行中) / 默认(未开始)
 */
export default {
  name: 'ProgressBar',
  // DIY 通用化契约：组件唯一标识
  componentKey: 'progress_bar',
};
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  /** section 对象，含 props.nodes */
  section: { type: Object, required: true },
});

/** 后端直填 props */
const p = computed(() => (props.section && props.section.props) || {});
/** 引擎注入的全局状态（预留，当前组件不依赖） */
const ctx = computed(() => (props.section && props.section._ctx) || {});
/** 进度节点数组，每项含 {title, key, completed, active, time} */
const nodes = computed(() => p.value.nodes || []);
</script>

<style scoped>
.progress-bar-wrap { margin: 20rpx; background: #fff; border-radius: 16rpx; padding: 30rpx; }
.navCon { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20rpx; }
.navCon > view { font-size: 24rpx; color: #999; text-align: center; flex: 1; }
.navCon > view.on { color: var(--view-theme, #e93323); font-weight: 500; }
.progress { display: flex; justify-content: space-between; align-items: center; }
.iconfont { font-size: 32rpx; color: #ddd; }
.iconfont.font-num { color: var(--view-theme, #e93323); }
.line { flex: 1; height: 4rpx; background: #ddd; margin: 0 8rpx; }
.line.bg-color { background: var(--view-theme, #e93323); }
</style>
