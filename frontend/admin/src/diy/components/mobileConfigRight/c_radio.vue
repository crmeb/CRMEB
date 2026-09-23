<template>
  <div class="c_radio" :class="configData.type == 'form' ? 'mb15' : 'on mb5'" v-if="configData">
    <div class="c_row-item">
      <el-col class="c_label" :class="configData.type == 'form' ? 'on' : ''" :span="4">
        {{ configData.title }}
      </el-col>
      <el-col class="color-box" :span="configData.type == 'form' ? 19 : 18">
        <el-radio-group v-model="configData.tabVal" @change="radioChange()">
          <el-radio :label="key" :value="key" v-for="(radio, key) in configData.tabList" :key="key">
            <span>{{ radio.name }}</span>
          </el-radio>
        </el-radio-group>
      </el-col>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_radio' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const emit = defineEmits(['getConfig']);

const defaults = ref({});
const configData = ref({});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { immediate: true, deep: true },
);

function radioChange(e) {
  emit('getConfig', e, 'radio');
}
</script>

<style scoped lang="scss">
.c_radio {
  &.on {
    padding: 0 15px;
    .c_label {
      color: #999999;
      font-size: 12px;
    }
    :deep(.ivu-radio-wrapper) {
      margin: 5px 25px 15px 0;
    }
  }
  .c_row-item {
    align-items: unset;
  }
  .c_label {
    color: #000;
    margin-top: 10px;
    margin-right: 15px;
    &.on {
      text-align: right;
      color: #666;
    }
  }
  .color-box {
    margin-top: 2px;
  }
}
</style>
