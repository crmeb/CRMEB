<template>
  <!-- 变量选择器：输入框右下角 { } 按钮，点击弹出可选变量列表 -->
  <el-popover
    v-if="list && list.length"
    placement="bottom-end"
    width="200"
    trigger="click"
    popper-class="variable-picker-popover"
  >
    <div class="variable">
      <div class="item" v-for="v in list" :key="v.key" :title="'插入 {' + v.key + '}'" @click="$emit('select', v.key)">
        {{ v.label }}
      </div>
    </div>
    <template #reference><span class="value-list" title="插入变量">{ }</span></template>
  </el-popover>
</template>

<script setup>
/**
 * 变量选择器组件
 *
 * 在通知内容输入框右下角渲染 { } 按钮，点击弹出可选变量列表。
 * 选中变量后通过 select 事件抛出变量 key，由父组件负责插入到对应输入框。
 *
 * 用法：
 * <variable-picker :list="variableList" @select="insertVariable" />
 */
defineOptions({ name: 'VariablePicker' });

// 可选变量列表 [{key, label}, ...]
defineProps({
  list: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['select']);
</script>

<style scoped lang="scss">
// 输入框右下角的 { } 触发按钮，绝对定位于父级 .content（position: relative）
.value-list {
  position: absolute;
  right: 7px;
  bottom: 7px;
  width: 22px;
  height: 22px;
  line-height: 22px;
  text-align: center;
  background: var(--prev-color-primary);
  color: #ededed;
  cursor: pointer;
  border-radius: 4px;
  z-index: 1;
}
</style>

<style lang="scss">
// 弹出层默认 append-to-body 在 body 下，scoped 样式不生效，需用全局样式 + popper-class
.variable-picker-popover .variable {
  max-height: 240px;
  overflow-y: auto;
  .item {
    cursor: pointer;
    padding: 5px 10px;
    transition: all 0.3s ease;
  }
  .item:hover {
    background: var(--prev-color-primary-light-9);
    color: var(--prev-color-primary);
    border-radius: 4px;
  }
}
// 滚动条样式
.variable-picker-popover .variable::-webkit-scrollbar {
  width: 4px;
  height: 4px;
}
.variable-picker-popover .variable::-webkit-scrollbar-thumb {
  background: var(--prev-color-primary-light-9);
  border-radius: 4px;
}
.variable-picker-popover .variable::-webkit-scrollbar-track {
  background: #f2f2f2;
}
</style>
