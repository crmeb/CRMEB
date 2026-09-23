<template>
  <div class="btn-box" style="display: none">
    <el-button type="primary" v-db-click @click="handleSubmit('formInline')" style="text-align: center; width: 60%"
      >确定</el-button
    >
  </div>
</template>

<script setup>
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'rightBtn' });

const props = defineProps(['activeIndex', 'configObj']);
const mobildConfigStore = useMobildConfigStore();

// 右侧确认保存配置
function handleSubmit(name) {
  let obj = {};
  obj.activeIndex = props.activeIndex;
  obj.data = props.configObj;
  mobildConfigStore.UPDATEARR(obj);
}
</script>

<style scoped></style>
