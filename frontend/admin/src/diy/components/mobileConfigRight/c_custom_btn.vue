<template>
  <div class="custom-btn-box">
    <el-button type="primary" class="btn" @click="onClick">{{ configData.title || '设计组件' }}</el-button>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

defineOptions({ name: 'c_custom_btn' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const emit = defineEmits(['getConfig']);

const configData = ref({});

onMounted(() => {
  nextTick(() => {
    configData.value = props.configObj[props.configNme] || {};
  });
});

watch(
  () => props.configObj,
  (nVal) => {
    configData.value = nVal[props.configNme] || {};
  },
  { deep: true },
);

function onClick() {
  emit('getConfig', { name: 'custom_btn_click' });
}
</script>

<style scoped lang="scss">
.custom-btn-box {
  padding: 0 15px 20px;
  .btn {
    width: 100%;
  }
}
</style>
