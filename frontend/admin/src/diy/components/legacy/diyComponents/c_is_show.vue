<template>
  <div class="c_row-item">
    <el-col class="label" :span="4"> 是否显示 </el-col>
    <el-col :span="19">
      <el-switch v-model="datas[name].val" />
    </el-col>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_is_show' });

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

watch(
  () => props.configData,
  (nVal) => {
    datas.value = nVal[props.configNum];
  },
  { immediate: true, deep: true },
);
</script>

<style scoped>
.c_row-item {
  margin-bottom: 20px;
}
</style>
