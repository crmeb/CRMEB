<template>
  <div class="mobile-config-component" v-if="configData">
    <div class="title-bar">{{ configData.title }}</div>
    <div class="box-content">
      <!-- Color Tone -->
      <div class="box-item">
        <span class="label">{{ configData.colorTone.title }}</span>
        <div class="input-box">
          <el-radio-group v-model="configData.colorTone.tabVal">
            <el-radio
              :label="item.val"
              :value="item.val"
              v-for="(item, index) in configData.colorTone.tabList"
              :key="index"
            >
              {{ item.name }}
            </el-radio>
          </el-radio-group>
        </div>
      </div>

      <!-- Custom Colors -->
      <div v-if="configData.colorTone.tabVal === 1">
        <c_bg_color :configObj="configData" configNme="textColor" />
        <c_bg_color :configObj="configData" configNme="selectedBorderColor" />
        <div v-if="showAdvancedSpecColors">
          <c_bg_color :configObj="configData" configNme="selectedTextColor" />
          <c_bg_color :configObj="configData" configNme="selectedBgColor" />
          <c_bg_color :configObj="configData" configNme="unselectedTextColor" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import c_bg_color from './c_bg_color';

defineOptions({ name: 'c_spec_settings' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const configData = ref(null);

const showAdvancedSpecColors = computed(() => {
  const styleVal = props.configObj && props.configObj.specStyle ? props.configObj.specStyle.tabVal : 0;
  return styleVal === 1 || styleVal === 2 || styleVal === 3;
});

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
          margin-right: 15px;
        }
      }
    }
  }
}
</style>
