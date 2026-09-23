<template>
  <el-dialog v-model="visible" width="800px" title="选择图标" append-to-body>
    <div class="icon-selector">
      <div class="search-box">
        <el-input v-model="searchText" placeholder="搜索图标名称" prefix-icon="Search" clearable></el-input>
      </div>
      <div class="icon-list">
        <div class="icon-item" v-for="(item, index) in filteredIcons" :key="index" @click="selectIcon(item)">
          <div class="icon-box">
            <span :class="['mb-iconfont', cssPrefix + item.font_class]"></span>
          </div>
          <div class="icon-name">{{ item.name }}</div>
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
    margin-bottom: 20px;
  }
  .icon-list {
    display: flex;
    flex-wrap: wrap;
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #eee;
    padding: 10px;
    border-radius: 4px;

    .icon-item {
      width: 100px;
      height: 100px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border-radius: 4px;
      transition: all 0.3s;
      margin: 5px;

      &:hover {
        background-color: #f5f7fa;
        color: #409eff;
      }

      .icon-box {
        margin-bottom: 10px;
        .mb-iconfont {
          font-size: 32px;
        }
      }

      .icon-name {
        font-size: 12px;
        text-align: center;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 0 5px;
      }
    }
  }
}
</style>
