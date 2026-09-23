<template>
  <el-form-item :label="`${labelPrefix}圆角`" class="margin-style-config">
    <div class="c_row">
      <div class="c_content">
        <div class="main-setting">
          <el-slider
            v-model="curComponent.propValue.borderRadius"
            show-input
            :min="0"
            :max="100"
            @input="handleRadiusChange"
            :show-input-controls="false"
            input-size="small"
          ></el-slider>
          <div class="expand-icon" :class="!curComponent.propValue.isRadiusAll ? 'selected' : ''" @click="toggleExpand">
            <span class="iconfont iconcaozuo-bianjiao"></span>
          </div>
        </div>
        <div class="sub-settings" v-if="!curComponent.propValue.isRadiusAll">
          <div class="sub-item">
            <div class="input-box">
              <span class="prefix-icon iconfont iconzuoshangjiao"></span>
              <el-input-number
                v-model="curComponent.propValue.borderRadiusTopLeft"
                :min="0"
                :max="100"
                controls-position="right"
                @change="onChange"
              ></el-input-number>
            </div>
          </div>
          <div class="sub-item">
            <div class="input-box">
              <span class="prefix-icon iconfont iconyoushangjiao"></span>
              <el-input-number
                v-model="curComponent.propValue.borderRadiusTopRight"
                :min="0"
                :max="100"
                controls-position="right"
                @change="onChange"
              ></el-input-number>
            </div>
          </div>
          <div class="sub-item">
            <div class="input-box">
              <span class="prefix-icon iconfont iconzuoxiajiao"></span>
              <el-input-number
                v-model="curComponent.propValue.borderRadiusBottomLeft"
                :min="0"
                :max="100"
                controls-position="right"
                @change="onChange"
              ></el-input-number>
            </div>
          </div>
          <div class="sub-item">
            <div class="input-box">
              <span class="prefix-icon iconfont iconyouxiajiao"></span>
              <el-input-number
                v-model="curComponent.propValue.borderRadiusBottomRight"
                :min="0"
                :max="100"
                controls-position="right"
                @change="onChange"
              ></el-input-number>
            </div>
          </div>
        </div>
      </div>
    </div>
  </el-form-item>
</template>

<script setup>
defineOptions({ name: 'ConfigRadius' });

const props = defineProps({
  curComponent: {
    type: Object,
    required: true,
  },
  labelPrefix: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['change']);

function onChange() {
  emit('change');
}
function toggleExpand() {
  const isRadiusAll = !props.curComponent.propValue.isRadiusAll;
  props.curComponent.propValue['isRadiusAll'] = isRadiusAll;
  if (isRadiusAll) {
    // Switch to all mode: sync individual values to current main value
    // Or actually, if switching to All, we should probably take the main value.
    // But if switching FROM individual TO all, which value to use?
    // Usually the main slider value.
    handleRadiusChange(props.curComponent.propValue.borderRadius);
  } else {
    // Switch to individual mode: sync main value to all individuals
    const r = props.curComponent.propValue.borderRadius || 0;
    props.curComponent.propValue['borderRadiusTopLeft'] = r;
    props.curComponent.propValue['borderRadiusTopRight'] = r;
    props.curComponent.propValue['borderRadiusBottomRight'] = r;
    props.curComponent.propValue['borderRadiusBottomLeft'] = r;
  }
  onChange();
}
function handleRadiusChange(val) {
  props.curComponent.propValue['borderRadius'] = val;
  if (props.curComponent.propValue.isRadiusAll) {
    props.curComponent.propValue['borderRadiusTopLeft'] = val;
    props.curComponent.propValue['borderRadiusTopRight'] = val;
    props.curComponent.propValue['borderRadiusBottomRight'] = val;
    props.curComponent.propValue['borderRadiusBottomLeft'] = val;
  }
  onChange();
}
</script>

<style scoped lang="scss">
.margin-style-config {
  // padding: 0px 15px 0 15px;
  margin-bottom: 18px;
  .c_row {
    display: flex;
    justify-content: space-between;
  }
  .c_label {
    font-size: 12px;
    color: #999;
    line-height: 38px;
    white-space: nowrap;
    width: 70px;
  }
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
        font-size: 18px;
      }
    }
    .expand-icon.selected {
      color: #409eff;
      border-color: #c6e2ff;
    }
    :deep(.el-slider) {
      flex: 1;
      margin-right: 0;
      .el-slider__input {
        width: 60px;
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
      margin-bottom: 10px;
      box-sizing: border-box;
      .input-box {
        display: flex;
        align-items: center;
        border: 1px solid #dcdfe6;
        border-radius: 4px;
        padding-left: 5px;
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
            padding-right: 40px;
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
