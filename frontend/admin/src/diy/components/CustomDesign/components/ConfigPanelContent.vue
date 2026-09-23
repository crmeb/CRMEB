<template>
  <div>
    <ConfigLink
      :curComponent="curComponent"
      :type="type"
      :currentFieldList="currentFieldList"
      :showLinkType="true"
      @change="onChange"
      @get-link="getLink"
    />
  </div>
</template>

<script setup>
import ConfigLink from './ConfigLink';

// 注意：原组件 name 即为 ConfigIconContent（历史复制遗留），保持不变
defineOptions({ name: 'ConfigIconContent' });

defineProps({
  curComponent: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    default: 'user',
  },
  currentFieldList: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['get-link', 'change']);

function getLink() {
  emit('get-link');
}
function onChange() {
  emit('change');
}
</script>

<style scoped lang="scss">
.upload-box {
  width: 80px;
  height: 80px;
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  &:hover {
    border-color: #409eff;
  }
  .upload-icon {
    font-size: 28px;
    color: #8c939d;
  }
}
</style>
