<template>
  <el-form-item label="内边距" class="margin-style-config">
    <div class="c_content">
      <div class="main-setting">
        <el-slider v-model="paddingMainVal" :max="100" show-input @input="handlePaddingMainChange"></el-slider>
        <div class="expand-icon" @click="isPaddingExpanded = !isPaddingExpanded">
          <span class="iconfont iconbianju" :class="isPaddingExpanded ? 'selected' : ''"></span>
        </div>
      </div>

      <div class="sub-settings" v-if="isPaddingExpanded">
        <div class="sub-item">
          <div class="input-box">
            <span class="prefix-icon iconfont iconneibianju-shang"></span>
            <el-input-number
              v-model="curComponent.propValue.paddingTop"
              controls-position="right"
              @change="onChange"
            ></el-input-number>
          </div>
        </div>
        <div class="sub-item">
          <div class="input-box">
            <span class="prefix-icon iconfont iconneibianju-you"></span>
            <el-input-number
              v-model="curComponent.propValue.paddingRight"
              controls-position="right"
              @change="onChange"
            ></el-input-number>
          </div>
        </div>
        <div class="sub-item">
          <div class="input-box">
            <span class="prefix-icon iconfont iconneibianju-xia"></span>
            <el-input-number
              v-model="curComponent.propValue.paddingBottom"
              controls-position="right"
              @change="onChange"
            ></el-input-number>
          </div>
        </div>
        <div class="sub-item">
          <div class="input-box">
            <span class="prefix-icon iconfont iconneibianju-zuo"></span>
            <el-input-number
              v-model="curComponent.propValue.paddingLeft"
              controls-position="right"
              @change="onChange"
            ></el-input-number>
          </div>
        </div>
      </div>
    </div>
  </el-form-item>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'ConfigPadding' });

const props = defineProps({
  curComponent: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['change']);

const paddingMainVal = ref(0);
const isPaddingExpanded = ref(false);

watch(
  () => props.curComponent?.id,
  () => {
    if (props.curComponent && props.curComponent.propValue) {
      // Ensure padding properties are reactive
      if (props.curComponent.propValue.paddingTop === undefined) props.curComponent.propValue['paddingTop'] = 0;
      if (props.curComponent.propValue.paddingRight === undefined) props.curComponent.propValue['paddingRight'] = 0;
      if (props.curComponent.propValue.paddingBottom === undefined) props.curComponent.propValue['paddingBottom'] = 0;
      if (props.curComponent.propValue.paddingLeft === undefined) props.curComponent.propValue['paddingLeft'] = 0;

      paddingMainVal.value = props.curComponent.propValue.paddingTop || 0;
    }
  },
  { immediate: true },
);

function onChange() {
  emit('change');
}
function handlePaddingMainChange(val) {
  props.curComponent.propValue['paddingTop'] = val;
  props.curComponent.propValue['paddingRight'] = val;
  props.curComponent.propValue['paddingBottom'] = val;
  props.curComponent.propValue['paddingLeft'] = val;
  onChange();
}
</script>

<style scoped lang="scss">
.margin-style-config {
  margin-bottom: 20px;
  .c_content {
    flex: 1;
  }
  .main-setting {
    display: flex;
    align-items: center;
    .expand-icon {
      margin-left: 10px;
      cursor: pointer;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid #dcdfe6;
      border-radius: 4px;
      color: #606266;
      &:hover {
        color: #409eff;
        border-color: #c6e2ff;
      }
      .iconfont {
        font-size: 12px;
      }
      .selected {
        color: #409eff;
        border-color: #c6e2ff;
      }
    }
    :deep(.el-slider) {
      flex: 1;
      margin-right: 0;
      .el-slider__input {
        width: 60px;
        padding-right: 0px;
      }
      .el-slider__runway {
        margin-right: 75px;
      }
    }
  }
  .sub-settings {
    display: flex;
    flex-wrap: wrap;
    margin-top: 10px;
    .sub-item {
      width: 50%;
      padding-right: 10px;
      margin-bottom: 10px;
      box-sizing: border-box;
      .input-box {
        display: flex;
        align-items: center;
        border: 1px solid #dcdfe6;
        border-radius: 4px;
        padding-left: 5px;
        background-color: #fff;
        &:hover {
          border-color: #c0c4cc;
        }
        .prefix-icon {
          color: #909399;
          font-size: 14px;
          margin-right: 5px;
        }
        :deep(.el-input-number) {
          width: 100%;
          border: none;
          .el-input__inner {
            border: none;
            padding-left: 5px;
            text-align: left;
            padding-right: 30px;
          }
        }
        :deep(.el-input__wrapper) {
          box-shadow: none;
        }
      }
    }
  }
}
</style>
