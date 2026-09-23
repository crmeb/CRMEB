<template>
  <div class="mobile-config-component" v-if="configData">
    <div class="title-bar">{{ configData.title }}</div>
    <div class="box-content">
      <!-- Indicator Style -->
      <div class="box-item">
        <span class="label">指示器样式</span>
        <div class="input-box">
          <el-radio-group v-model="configData.tabVal">
            <el-radio :label="index" :value="index" v-for="(item, index) in configData.tabList" :key="index">
              {{ item.name }}
            </el-radio>
          </el-radio-group>
        </div>
      </div>

      <!-- Indicator Position -->
      <div class="box-item" v-if="configData.tabVal !== 0">
        <span class="label">指示器位置</span>
        <div class="input-box">
          <el-radio-group v-model="configData.positionVal">
            <el-radio :label="index" :value="index" v-for="(item, index) in configData.positionList" :key="index">
              {{ item.name }}
            </el-radio>
          </el-radio-group>
        </div>
      </div>

      <!-- Colors -->
      <c_bg_color :configObj="configData" configNme="selectColor" />
      <c_bg_color :configObj="configData" configNme="defaultColor" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import c_bg_color from './c_bg_color';

defineOptions({ name: 'c_indicator_settings' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const configData = ref({});

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
  border-bottom: 6px solid rgb(240, 242, 245);
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
        :deep(.el-radio) {
          margin-bottom: 0px;
        }
        &.red-text {
          color: #e93323;
          font-size: 12px;
        }
      }
    }
  }
}
</style>
