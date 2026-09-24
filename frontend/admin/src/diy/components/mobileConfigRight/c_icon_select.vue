<template>
  <el-dialog v-model="visible" width="640px" title="选择图标" append-to-body>
    <div class="icon-selector">
      <div class="search-box">
        <el-input v-model="searchText" placeholder="搜索图标名称" prefix-icon="Search" clearable></el-input>
      </div>
      <div class="icon-list">
        <div
          class="icon-item"
          v-for="(item, index) in filteredIcons"
          :key="index"
          :title="item.name"
          @click="selectIcon(item)"
        >
          <span :class="['mb-iconfont', cssPrefix + item.font_class]"></span>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import iconData from '@/styles/font/mobile.json';

defineOptions({ name: 'c_icon_select' });

const emit = defineEmits(['select']);

const visible = ref(false);
const searchText = ref('');
const icons = ref([]);
const cssPrefix = ref('icon-');

icons.value = iconData.glyphs || [];
cssPrefix.value = iconData.css_prefix_text || 'icon-';

const filteredIcons = computed(() => {
  if (!searchText.value) {
    return icons.value;
  }
  const lower = searchText.value.toLowerCase();
  return icons.value.filter(
    (item) => item.name.toLowerCase().includes(lower) || item.font_class.toLowerCase().includes(lower),
  );
});

function show() {
  visible.value = true;
}
function selectIcon(item) {
  emit('select', cssPrefix.value + item.font_class);
  visible.value = false;
}

defineExpose({ show });
</script>

<style scoped lang="scss">
.icon-selector {
  .search-box {
    margin-bottom: 12px;
  }
  .icon-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
    gap: 4px;
    max-height: 420px;
    overflow-y: auto;
    border: 1px solid var(--el-border-color-lighter);
    padding: 8px;
    border-radius: 4px;

    .icon-item {
      aspect-ratio: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border-radius: 4px;
      transition: all 0.2s;

      // .mb-iconfont 全局默认 font-size:16px，直接命中图标，必须在图标上覆盖
      .mb-iconfont {
        font-size: 30px;
      }

      &:hover {
        background-color: var(--prev-color-primary-light-9);
        color: var(--prev-color-primary);
      }
    }
  }
}
</style>
