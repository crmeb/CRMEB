<!-- @FileDescription: 选择图标组件（例：菜单管理选择图标），支持 Element Plus 图标与官方图标库（iconfont） -->
<template>
  <el-dialog v-model="visible" width="1020px" title="图标选择" append-to-body>
    <div class="select-icon">
      <div class="toolbar">
        <span class="toolbar__count">共 {{ displayList.length }} 项</span>
        <el-input
          v-model="keyword"
          class="toolbar__search"
          placeholder="请输入图标名称"
          :prefix-icon="Search"
          clearable
        />
        <el-select v-model="source" class="toolbar__source">
          <el-option v-for="opt in sourceOptions" :key="opt.value" :label="opt.label" :value="opt.value" />
        </el-select>
        <el-button class="toolbar__reset" :disabled="!hasFilter" v-db-click @click="resetFilter">
          <el-icon><RefreshLeft /></el-icon>
        </el-button>
        <el-radio-group v-if="source === 'crmeb'" v-model="iconType" class="toolbar__segmented">
          <el-radio-button value="outlined">线性</el-radio-button>
          <el-radio-button value="filled">面性</el-radio-button>
        </el-radio-group>
      </div>
      <div class="icons-container">
        <div class="grid">
          <div
            v-for="item in displayList"
            :key="item.value"
            class="icon-item"
            v-db-click
            @click="selectIcon(item.value)"
          >
            <el-icon v-if="item.source === 'element'" :size="26"><component :is="item.value" /></el-icon>
            <i v-else class="crmeb-icon" :class="item.value" />
            <span class="line1" :title="item.name">{{ item.name }}</span>
          </div>
        </div>
        <el-empty v-if="!displayList.length" description="暂无匹配图标" />
      </div>
    </div>
  </el-dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Search, RefreshLeft } from '@element-plus/icons-vue';
import crmebIcons from '@/styles/font/crmeb/icons';
import icon from '@/utils/icon';

defineOptions({ name: 'SelectIcon' });

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'select']);

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const sourceOptions = [
  { label: '官方图标库', value: 'crmeb' },
  { label: 'ELEMENT', value: 'element' },
];
const keyword = ref('');
const source = ref('crmeb'); // crmeb：官方图标库 | element：Element Plus 图标
const iconType = ref('outlined'); // outlined：线性 | filled：面性，仅官方图标库生效

// 官方图标：icons 模块已按类名序号排序，value 为字体类名，name 为中文名（不含线性/面性字样）
const crmebItems = crmebIcons.map((item) => ({ value: item.value, name: item.name, source: 'crmeb' }));
// Element Plus 图标：组件名，全局注册见 main.js
const elementItems = icon.map((name) => ({ value: name, name, source: 'element' }));

const displayList = computed(() => {
  let pool;
  if (source.value === 'crmeb') {
    // 线性/面性按字体类名后缀区分，展示名不含该字样
    const suffix = iconType.value === 'filled' ? '-mianxing' : '-xianxing';
    pool = crmebItems.filter((item) => item.value.endsWith(suffix));
  } else {
    pool = elementItems;
  }
  const kw = keyword.value.trim().toLowerCase();
  if (kw) pool = pool.filter((item) => item.name.toLowerCase().includes(kw) || item.value.toLowerCase().includes(kw));
  return pool;
});

const hasFilter = computed(() => !!keyword.value || source.value !== 'crmeb' || iconType.value !== 'outlined');

function resetFilter() {
  keyword.value = '';
  source.value = 'crmeb';
  iconType.value = 'outlined';
}

function selectIcon(value) {
  visible.value = false;
  emit('select', value);
}
</script>

<style lang="scss" scoped>
.select-icon {
  background-color: var(--el-bg-color);
}

.toolbar {
  display: flex;
  align-items: center;
  gap: 10px;

  &__count {
    min-width: 60px;
    white-space: nowrap;
    font-size: 12px;
    color: var(--el-text-color-secondary);
  }

  &__search {
    width: 220px;
  }

  &__source {
    width: 150px;
  }

  &__reset {
    width: 32px;
    padding: 0;
  }

  // 线性/面性分段切换，居右
  &__segmented {
    margin-left: auto;
  }
}

.icons-container {
  height: 460px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid var(--el-border-color-lighter);
  overflow: auto;

  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, 82px);
    gap: 10px 26px;
  }

  .icon-item {
    display: flex;
    flex-flow: column;
    align-items: center;
    justify-content: center;
    height: 76px;
    overflow: hidden;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.15s ease;

    &:hover {
      background-color: var(--el-fill-color-light);
    }

    .crmeb-icon {
      font-size: 26px;
      line-height: 1;
      color: var(--el-text-color-regular);
    }

    span {
      width: 100%;
      padding: 0 3px;
      margin-top: 6px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      text-align: center;
      font-size: 12px;
      color: var(--el-text-color-primary);
    }
  }
}
</style>
