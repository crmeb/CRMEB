<!-- @FileDescription: 菜单图标渲染：兼容 Element Plus 图标、官方图标库（iconfont）、旧版 el-icon-* 字体图标 -->
<template>
  <el-icon v-if="isElementIcon"><component :is="props.icon" /></el-icon>
  <i v-else-if="iconfontClass" class="crmeb-icon menu-icon" :class="iconfontClass" />
  <i v-else-if="props.icon" class="ivu-icon" :class="legacyClass" />
</template>

<script setup>
import { computed } from 'vue';
import elementIconNames from '@/utils/icon';
import { resolveIconfontIcon } from '@/styles/font/crmeb/icons';

defineOptions({ name: 'MenuIcon' });

const props = defineProps({
  icon: {
    type: String,
    default: '',
  },
});

// Element Plus 图标为全局注册的组件名（见 main.js），用组件名集合精确匹配；
// 官方图标类名同样以大写开头，不能按首字母判断
const elementIconSet = new Set(elementIconNames);
const isElementIcon = computed(() => elementIconSet.has(props.icon));

// 官方图标类名，支持对截断/缺样式后缀的存储值做唯一前缀解析
const iconfontClass = computed(() => resolveIconfontIcon(props.icon));

// 历史数据可能已带 el-icon- 前缀，避免拼出 el-icon-el-icon-* 双前缀
const legacyClass = computed(() => (props.icon.startsWith('el-icon-') ? props.icon : 'el-icon-' + props.icon));
</script>

<style lang="scss" scoped>
// 对齐 el-icon 在菜单中的尺寸与间距
.menu-icon {
  width: 1em;
  font-size: 16px;
  margin-right: 5px;
  text-align: center;
  vertical-align: middle;
  flex-shrink: 0;
}

// Element Plus 图标走组件默认渲染，需压过菜单内 .el-icon 的默认 18px
.el-menu .el-icon {
  font-size: 16px;
}
</style>
