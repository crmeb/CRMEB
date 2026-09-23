<template>
  <div class="mobile-config-component" v-if="configData">
    <div class="title-bar">{{ configData.title }}</div>
    <div class="box-content">
      <c_bg_color :configObj="configData" configNme="originalPriceColor" />
      <c_bg_color :configObj="configData" configNme="stockColor" />
      <c_bg_color :configObj="configData" configNme="salesColor" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import c_bg_color from './c_bg_color';

defineOptions({ name: 'c_data_settings' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const configData = ref(null);

watch(
  () => props.configObj,
  (nVal) => {
    configData.value = nVal[props.configNme];
  },
  { deep: true, immediate: true },
);
</script>

<style scoped lang="scss">
.mobile-config-component {
  margin: 15px 0;
  .title-bar {
    font-size: 14px;
    color: #333;
    padding: 0 15px;
    margin-bottom: 15px;
  }
  .box-content {
    .box-item {
      padding: 0 15px;
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      .label {
        width: 80px;
        font-size: 13px;
        color: #999;
      }
      .input-box {
        flex: 1;
      }
    }
  }
}
</style>
