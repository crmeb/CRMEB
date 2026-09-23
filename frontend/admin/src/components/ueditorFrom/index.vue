<template>
  <div>
    <WangEditor style="width: 100%; height: 60%" :content="content" @editorContent="getEditorContent"></WangEditor>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import WangEditor from '@/components/wangEditor/index.vue';

defineOptions({ name: 'index' });

const props = defineProps({
  content: '',
});

const contents = ref('');

watch(
  () => props.content,
  (val) => {
    // contents.value = val
    // emit('getContent', val);
  },
  { deep: true },
);

function getEditorContent(data) {
  contents.value = data;
}
</script>

<style scoped></style>
