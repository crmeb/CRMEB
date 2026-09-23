<template>
  <div class="slider-box">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="configData.title">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <el-select v-model="configData.activeValue" @change="sliderChange" style="width: 100%">
          <el-option
            v-for="(item, index) in configData.list"
            :value="item.activeValue"
            :key="index"
            :label="item.title"
          ></el-option>
        </el-select>
      </el-col>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

defineOptions({ name: 'c_select' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
  number: {
    type: null,
  },
});

const emit = defineEmits(['getConfig']);

const defaults = ref({});
const configData = ref({});
const timeStamp = ref('');

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
  });
});

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { deep: true },
);

watch(
  () => props.number,
  (nVal) => {
    timeStamp.value = nVal;
  },
);

function sliderChange(e) {
  configData.value.activeValue = e;
  emit('getConfig', { name: 'select', values: e });
}
</script>

<style scoped lang="scss">
.slider-box {
  padding: 0 15px;
  .label {
    color: #999999;
    font-size: 12px;
  }
}
.c_row-item {
  margin-bottom: 20px;
}
</style>
