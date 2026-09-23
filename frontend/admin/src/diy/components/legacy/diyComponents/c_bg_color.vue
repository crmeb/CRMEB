<template>
  <div>
    <div class="c_row-item" v-if="configData[configNum]">
      <el-col :span="8" class="c_label">{{ configData[configNum][name].title }}</el-col>
      <el-col :span="14" class="color-box">
        <div
          class="color-item acea-row row-middle"
          v-for="(color, key) in configData[configNum][name].color"
          :key="key"
        >
          <el-color-picker v-model="color.item" @change="changeColor($event, color)"></el-color-picker
          ><span class="white-space-nowrap" v-db-click @click="resetBgA(color, index, key)">重置</span>
        </div>
      </el-col>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

let restColor = '';

defineOptions({ name: 'c_bg_color' });

const props = defineProps({
  configData: {
    type: Object,
  },
  name: {
    type: String,
  },
  configNum: {
    type: [Number, String],
    default: 'default',
  },
});

const defaults = ref({});
const bgColor = ref({
  bgStar: '',
  bgEnd: '',
});
const oldColor = ref({
  bgStar: '',
  bgEnd: '',
});
const index = ref(0);

watch(
  () => props.configData,
  (nVal) => {
    defaults.value = nVal[props.configNum];
  },
  { immediate: true, deep: true },
);

// created
defaults.value = props.configData[props.configNum][props.configNum];

function changeColor(e, color) {
  if (!e) {
    // color.item = 'transparent';
  }
  // emit('getConfig', defaults.value)
}
// 重置
function resetBgA(color, index, key) {
  color.item = props.configData[props.configNum][props.name].default[key].item;
}
</script>

<style lang="scss" scoped>
.c_row-item {
  margin-bottom: 20px;
  :deep(.ivu-select-dropdown) {
    left: -27px !important;
  }
}
.color-box {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  .color-item {
    margin-left: 15px;

    span {
      margin-left: 5px;
      color: #999;
      font-size: 13px;
      cursor: pointer;
    }
  }
}
</style>
