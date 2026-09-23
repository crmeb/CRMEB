<template>
  <div class="slider-box">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="datas[name].title">
        {{ datas[name].title }}
      </el-col>
      <el-col :span="19">
        <el-select v-model="datas[name].activeValue" clearable style="width: 350px" @change="sliderChange">
          <el-option
            v-for="(item, index) in datas[name].list"
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
import { onMounted, onUnmounted, ref, watch } from 'vue';
import bus from '@/utils/bus';

defineOptions({ name: 'c_select' });

const emit = defineEmits(['getConfig']);

const props = defineProps({
  name: {
    type: String,
  },
  configData: {
    type: null,
  },
  configNum: {
    type: [Number, String],
    default: 'default',
  },
});

const defaults = ref({});
const datas = ref(props.configData[props.configNum]);

onMounted(() => {
  if (props.name === 'selectConfig') {
    bus.on('upData', upDataHandler);
  }
});

onUnmounted(() => {
  bus.off('upData', upDataHandler);
});

watch(
  () => props.configData,
  (nVal) => {
    datas.value = nVal[props.configNum];
  },
  { deep: true },
);

function upDataHandler(data) {
  datas.value[props.name].list = data;
  bus.off('upData', upDataHandler);
}

function sliderChange(e) {
  emit('getConfig', { name: 'select', values: e });
}
</script>

<style lang="scss" scoped>
.slider-box {
  margin-top: 10px;
}
</style>
