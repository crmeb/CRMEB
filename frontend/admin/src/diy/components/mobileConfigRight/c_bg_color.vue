<template>
  <div>
    <!-- el-col 不传 span 时 Element Plus 默认 span=24（flex:0 0 100% 不收缩），
         在 .c_row-item 里会把第二个色值选择区挤出面板可视区，故用普通 div（参照 single_admin 迁移修复） -->
    <div class="c_row-item" v-if="configData && configData.color && configData.color.length">
      <div class="c_label">{{ configData.title }}</div>
      <div class="color-box">
        <div class="color-item" v-for="(color, key) in configData.color" :key="key">
          <el-color-picker v-model="color.item" @change="changeColor($event, color)" show-alpha=""></el-color-picker>
          <el-input class="input" v-model="color.item" />
          <span class="white-space-nowrap" @click="resetBgA(color, index, key)">重置</span>
        </div>
        <div class="iconfont iconlianjie" v-if="configData.color.length > 1"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_bg_color' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const defaults = ref({});
const configData = ref({});
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
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { immediate: true, deep: true },
);

function changeColor(e, color) {
  if (!e) {
    // color.item = 'transparent';
  }
  // emit('getConfig', defaults.value)
}
// 重置
function resetBgA(color, index, key) {
  color.item = configData.value.default[key].item;
}
</script>

<style scoped lang="scss">
.color-box {
  position: relative;
  .iconfont {
    position: absolute;
    top: 27px;
    left: 24px;
    color: #bbbbbb;
    font-size: 21px;
  }
  .color-item {
    display: flex;
    align-items: center;
    margin-left: 15px;
    & ~ .color-item {
      margin-top: 15px;
    }
    span {
      margin-left: 15px;
      color: var(--prev-color-primary);
      font-size: 13px;
      cursor: pointer;
    }
    .input {
      margin-left: 11px;
      width: 192px;
    }
  }
  .white-space-nowrap {
    white-space: nowrap;
  }
}
.c_row-item {
  margin: 0 15px 20px 15px;
  align-items: flex-start;
  .c_label {
    font-size: 12px;
    margin-top: 8px;
  }
}
:deep(.ivu-color-picker-color) {
  width: 22px;
  height: 22px;
}
:deep(.ivu-input-icon) {
  width: 35px;
  height: 35px;
  line-height: 35px;
  font-size: 14px;
  right: -1px;
  color: #fff;
}
:deep(.ivu-input-icon-normal + .ivu-input) {
  padding-right: 4px;
}

:deep(.ivu-color-picker-color) {
  top: 0;
}
:deep(.ivu-input) {
  padding: 4px 5px;
  border: 1px solid #eee;
}
</style>
