<template>
  <div class="c_bg_tool" v-if="configData">
    <div class="title-tips" v-if="configData.tabList">
      <span>{{ configData.title }}</span
      >{{ configData.tabList[configData.tabVal].name }}
    </div>
    <div class="radio-box" :class="{ on: configData.type == 1 }">
      <el-radio-group v-model="configData.tabVal" type="button" size="large" @change="radioChange($event)">
        <el-radio :label="index" :value="index" v-for="(item, index) in configData.tabList" :key="index">
          <span class="iconfont" :class="item.icon" v-if="item.icon"></span>
          <span v-else>{{ item.name }}</span>
        </el-radio>
      </el-radio-group>
    </div>

    <div v-if="configData.tabVal == 0">
      <c_bg_color :configObj="configData" configNme="colorConfig"></c_bg_color>
      <c_radio :configObj="configData" configNme="colorDirection"></c_radio>
    </div>

    <div v-if="configData.tabVal == 1">
      <c_upload_img :configObj="configData" configNme="imageConfig"></c_upload_img>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import c_bg_color from './c_bg_color';
import c_upload_img from './c_upload_img';
import c_radio from './c_radio';

defineOptions({ name: 'c_bg_tool' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const emit = defineEmits(['getConfig']);

const configData = ref(null);

watch(
  () => props.configObj,
  (nVal) => {
    configData.value = nVal[props.configNme];
  },
  { deep: true, immediate: true },
);

function radioChange(e) {
  emit('getConfig', e);
}
</script>

<style scoped lang="scss">
.title-tips {
  padding-bottom: 10px;
  font-size: 14px;
  color: #333;
  span {
    margin-right: 14px;
    color: #999;
  }
}
.radio-box {
  margin-bottom: 20px;
  :deep(.el-radio-group) {
    display: flex;
    .el-radio {
      flex: 1;
      .el-radio-button__inner {
        width: 100%;
      }
    }
  }
}
</style>
