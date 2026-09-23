<template>
  <div class="config-item">
    <div class="item-header">
      <span class="item-title">{{ configData.title }}</span>
      <el-switch
        class="defineSwitch"
        v-model="configData.enable"
        active-text="开启"
        inactive-text="关闭"
        @change="handleChange"
      ></el-switch>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'c_header_switch' });

const props = defineProps({
  configNme: {
    type: String,
  },
  configObj: {
    type: Object,
    default: () => {},
  },
});

const emit = defineEmits(['getConfig']);

const configData = ref({
  title: '',
  enable: false,
});

watch(
  () => props.configObj,
  (nVal) => {
    configData.value = nVal[props.configNme] || { title: '', enable: false };
  },
  { deep: true, immediate: true },
);

function handleChange() {
  emit('getConfig', configData.value);
}
</script>

<style lang="scss" scoped>
.config-item {
  padding: 12px 16px;

  .item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .item-title {
      font-size: 14px;
      color: #333;
    }
  }
}
</style>
