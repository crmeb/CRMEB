<template>
  <div>
    <div class="setUpTop"></div>
    <div class="setUp">
      <!-- <div class="label" v-if="defaults.cname">
        {{ defaults.cname }}
      </div> -->
      <el-tabs v-model="activeTab" @tab-click="handleTabClick">
        <el-tab-pane v-for="(item, index) in list" :key="index" :label="item" :name="String(index)"> </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

defineOptions({ name: 'c_set_up' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const defaults = ref({});
const configData = ref({
  tabVal: 0,
});
const list = ref(['内容设置', '样式设置']);
const current = ref(0);
const activeTab = ref('0');

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
    current.value = configData.value?.tabVal;
    activeTab.value = String(configData.value?.tabVal);
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
    nextTick((e) => {
      current.value = configData.value?.tabVal;
      activeTab.value = String(configData.value?.tabVal);
    });
  });
});

function handleTabClick(tab) {
  // Element Plus 的 tab-click 参数是 TabPaneContext（取 paneName）；
  // element-ui 时代传组件实例（取 name），直接读 name 会得到 undefined → parseInt 出 NaN
  const index = parseInt(tab.paneName ?? tab.props?.name ?? tab.name);
  configData.value.tabVal = index;
  current.value = index;
}
function onClickTab(index) {
  configData.value.tabVal = index;
  current.value = index;
  activeTab.value = String(index);
}
</script>

<style scoped lang="scss">
.setUpTop {
  height: 6px;
  background: rgb(240, 242, 245);
}
.setUp {
  display: flex;
  justify-content: center;
  padding: 0px 15px 0;
  border-bottom: 1px solid #f0f2f5;
  z-index: 99;
  position: relative;
  background: #fff;
  .label {
    font-size: 16px;
    color: #333333;
    margin-bottom: 15px;
  }

  :deep(.el-tabs__header) {
    margin: 0;
  }

  :deep(.el-tabs__nav-wrap::after) {
    height: 1px;
    background-color: #e4e7ed;
  }

  :deep(.el-tabs__item) {
    font-size: 14px;
    color: #606266;
    padding: 0 60px;
    height: 40px;
    line-height: 40px;

    &.is-active {
      color: var(--prev-color-primary);
      font-weight: 500;
    }

    &:hover {
      color: var(--prev-color-primary);
    }
  }

  :deep(.el-tabs__active-bar) {
    background-color: var(--prev-color-primary);
    height: 2px;
  }

  :deep(.el-tabs__content) {
    display: none;
  }
}
</style>
