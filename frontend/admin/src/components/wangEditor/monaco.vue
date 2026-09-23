<template>
  <div ref="code_box" class="text">
    <el-input v-model="content" type="textarea" placeholder="" @change="changeContent" />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

defineOptions({ name: 'wangEditorMonaco' });

const props = defineProps({
  value: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['change']);

const content = ref('');

// 源码视图随外层最新 HTML 刷新（monaco 由 v-if 控制，重挂载时也会重新取值）
watch(
  () => props.value,
  (val) => {
    content.value = val;
  },
  { immediate: true },
);

function changeContent() {
  emit('change', content.value);
}
</script>

<style lang="css" scoped>
.editor {
  width: 100%;
  margin: 0 auto;
}
.text :deep(.el-textarea__inner) {
  min-height: 600px;
}
.text {
  border: 1px solid #ccc;
  min-height: 600px;
}
</style>
