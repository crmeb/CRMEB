<template>
  <div class="multi-select-panel">
    <div
      class="section-title flex justify-between"
      style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px"
    >
      <span style="font-size: 14px; font-weight: bold; color: #333">位置设置</span>
    </div>

    <el-form label-width="70px" label-position="left">
      <el-form-item label="对齐方式">
        <div class="alignment-bar">
          <div class="bar-item" @click="alignComponents('left')" title="左对齐">
            <span class="iconfont iconzuoduiqi1"></span>
          </div>
          <div class="bar-item-divider"></div>
          <div class="bar-item" @click="alignComponents('center')" title="水平居中">
            <span class="iconfont iconchuizhijuzhong"></span>
          </div>
          <div class="bar-item-divider"></div>
          <div class="bar-item" @click="alignComponents('right')" title="右对齐">
            <span class="iconfont iconyouduiqi1"></span>
          </div>
          <div class="bar-item-divider"></div>
          <div class="bar-item" @click="alignComponents('top')" title="顶部对齐">
            <span class="iconfont icondingduiqi"></span>
          </div>
          <div class="bar-item-divider"></div>
          <div class="bar-item" @click="alignComponents('middle')" title="垂直居中">
            <span class="iconfont iconshuipingjuzhong"></span>
          </div>
          <div class="bar-item-divider"></div>
          <div class="bar-item" @click="alignComponents('bottom')" title="底部对齐">
            <span class="iconfont icondiduiqi"></span>
          </div>
          <div class="bar-item-divider"></div>
          <div class="bar-item" @click="alignComponents('all-center')" title="水平垂直居中">
            <span class="iconfont iconjuzhongduiqi"></span>
          </div>
        </div>
      </el-form-item>
      <el-form-item label="X 轴">
        <div class="row" style="display: flex; align-items: center">
          <el-slider
            v-model="localGroupX"
            :max="canvasWidth"
            :min="0"
            @change="handleXChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="localGroupX"
            :max="canvasWidth"
            :min="0"
            controls-position="right"
            @change="handleXChange"
            style="width: 70px"
          ></el-input-number>
        </div>
      </el-form-item>
      <el-form-item label="Y 轴">
        <div class="row" style="display: flex; align-items: center">
          <el-slider
            v-model="localGroupY"
            :max="canvasHeight"
            :min="0"
            @change="handleYChange"
            style="flex: 1; margin-right: 10px"
          ></el-slider>
          <el-input-number
            v-model="localGroupY"
            :max="canvasHeight"
            :min="0"
            controls-position="right"
            @change="handleYChange"
            style="width: 70px"
          ></el-input-number>
        </div>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'ConfigMultiSelect' });

const props = defineProps({
  groupX: {
    type: Number,
    default: 0,
  },
  groupY: {
    type: Number,
    default: 0,
  },
  canvasWidth: {
    type: Number,
    default: 375,
  },
  canvasHeight: {
    type: Number,
    default: 667,
  },
});

const emit = defineEmits(['align', 'update:groupX', 'update:groupY']);

const localGroupX = ref(props.groupX);
const localGroupY = ref(props.groupY);

watch(
  () => props.groupX,
  (val) => {
    localGroupX.value = val;
  },
);
watch(
  () => props.groupY,
  (val) => {
    localGroupY.value = val;
  },
);

function alignComponents(type) {
  emit('align', type);
}
function handleXChange(val) {
  emit('update:groupX', val);
}
function handleYChange(val) {
  emit('update:groupY', val);
}
</script>

<style scoped lang="scss">
.multi-select-panel {
  :deep(.el-input-number.is-controls-right .el-input__inner) {
    text-align: center;
    padding: 0 5px;
  }
}
.alignment-bar {
  display: flex;
  align-items: center;
  border: 1px solid #eee;
  border-radius: 4px;
  gap: 2px;
  padding: 0 4px;
  .bar-item {
    flex: 1;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    color: #515a6e;
    border-radius: 8px;
    padding: 0 5px;
    .iconfont {
      font-size: 18px;
    }
    &:hover {
      background: #fff;
      color: #2d8cf0;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    &:active {
      background: #eee;
    }
  }
  .bar-item-divider {
    width: 1px;
    height: 32px;
    background: #dcdee0;
    margin: 0 4px;
  }
}
</style>
