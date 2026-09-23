<template>
  <div class="mobile-config-component" v-if="configData">
    <div class="c_row-item">
      <el-col class="label" :span="4">
        {{ configData.title }}
      </el-col>
      <el-col :span="18" class="slider-box">
        <el-date-picker
          v-model="configData.val"
          type="daterange"
          range-separator="-"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          value-format="YYYY/MM/DD"
          style="width: 100%"
        >
        </el-date-picker>
      </el-col>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_datetime_picker' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const configData = ref(null);

watch(
  () => props.configObj,
  (nVal) => {
    configData.value = nVal[props.configNme];
  },
  { deep: true, immediate: true },
);
</script>

<style scoped lang="scss">
.mobile-config-component {
  padding: 0 15px;
  margin-bottom: 10px;
  // border-bottom: 6px solid rgb(240, 242, 245);

  .label {
    color: #999;
    font-size: 12px;
    margin-right: 15px;
  }
}
</style>
