<template>
  <div class="box" v-if="datas">
    <WangEditor style="width: 100%; height: 60%" :content="content" @editorContent="getEditorContent"></WangEditor>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import WangEditor from '@/components/wangEditor/index.vue';

defineOptions({ name: 'c_page_ueditor' });

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

const datas = ref({});

watch(
  () => props.configData,
  (nVal) => {
    datas.value = nVal[props.configNum][props.name];
  },
  { immediate: true, deep: true },
);

// created
const content = ref(datas.value.val || '');

function getEditorContent(data) {
  datas.value.val = data;
}
</script>

<style scoped></style>
