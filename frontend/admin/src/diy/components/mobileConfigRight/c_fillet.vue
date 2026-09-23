<template>
  <div class="margin-style-config" v-if="configData">
    <div class="c_row">
      <div class="c_label">{{ configData.title }}</div>
      <div class="c_content">
        <div class="main-setting">
          <c_slider_deferred v-model="configData.val" show-input :min="configData.min" :max="configData.max || 100" />
          <div class="expand-icon" :class="configData.type ? 'selected' : ''" @click="toggleExpand">
            <span class="iconfont iconcaozuo-bianjiao"></span>
          </div>
        </div>
        <div class="sub-settings" v-if="configData.type">
          <div class="sub-item" v-for="(item, index) in configData.valList" :key="index">
            <div class="input-box">
              <span class="prefix-icon iconfont" :class="getIcon(index)"></span>
              <el-input-number
                v-model="item.val"
                :min="configData.min"
                :max="configData.max || 100"
                controls-position="right"
              ></el-input-number>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import c_slider_deferred from './c_slider_deferred.vue';

defineOptions({ name: 'c_fillet' });

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
    if (!nVal) return;
    configData.value = nVal[props.configNme];
  },
  { immediate: true, deep: true },
);

watch(
  () => configData.value?.val,
  (nVal) => {
    if (configData.value && configData.value.type == 0 && configData.value.valList) {
      configData.value.valList.forEach((item) => {
        item.val = nVal;
      });
    }
  },
);

watch(
  () => configData.value?.type,
  (nVal) => {
    if (nVal == 0 && configData.value && configData.value.valList) {
      configData.value.valList.forEach((item) => {
        item.val = configData.value.val;
      });
    }
  },
);

function toggleExpand() {
  if (!configData.value) return;
  configData.value['type'] = configData.value.type ? 0 : 1;
}
function getIcon(index) {
  const icons = ['iconzuoshangjiao', 'iconyoushangjiao', 'iconzuoxiajiao', 'iconyouxiajiao'];
  // Adjust index order if needed: Top-Left, Top-Right, Bottom-Left, Bottom-Right
  // valList usually [TL, TR, BL, BR] or [TL, TR, BR, BL]?
  // home_bottom_menu: valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  // Common order in CSS: TL, TR, BR, BL.
  // But c_fillet old code: 0: zuoshang, 1: youshang, 2: zuoxia, 3: youxia. (TL, TR, BL, BR)
  // Check c_margin_style: icons[index].
  // Old c_fillet: index 2 is zuoxia (BL), index 3 is youxia (BR).
  // So order is TL, TR, BL, BR.
  return icons[index] || '';
}
</script>

<style scoped lang="scss">
.margin-style-config {
  padding: 0px 15px 0 15px;
  .c_row {
    display: flex;
    justify-content: space-between;
  }
  .c_label {
    font-size: 12px;
    color: #999;
    line-height: 38px;
    margin-bottom: 10px;
    white-space: nowrap;
  }
  .c_content {
    width: 75%;
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
        width: 110px;
      }
      .el-slider__runway {
        // margin-right: 100px;
      }
    }
  }
  .sub-settings {
    display: flex;
    flex-wrap: wrap;
    margin-top: 10px;
    margin-right: -10px;
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
