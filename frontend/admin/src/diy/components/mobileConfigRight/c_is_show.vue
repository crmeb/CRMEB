<template>
  <div class="c_row-item">
    <el-col
      class="c_label"
      :class="{ on: configData.type == 'form', on2: configData.type == 'ranges' }"
      :span="configData.type == 'form' || configData.type == 'ranges' ? 4 : ''"
      >{{ configData.title }}</el-col
    >
    <el-col :span="configData.type == 'form' || configData.type == 'ranges' ? 19 : ''">
      <el-switch
        class="defineSwitch"
        :active-value="1"
        :inactive-value="0"
        v-model="configData.val"
        active-text="开启"
        inactive-text="关闭"
      >
      </el-switch>
    </el-col>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_is_show' });

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

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { immediate: true, deep: true },
);
</script>

<style scoped lang="scss">
.c_row-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  .c_label {
    &.on {
      color: #666;
      text-align: right;
    }
    &.on2 {
      text-align: left;
      color: #666;
    }
  }
}
</style>
