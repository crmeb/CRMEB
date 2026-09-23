<template>
  <div class="monaco-container">
    <div ref="container" class="monaco-editor"></div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import * as monaco from '@/libs/monaco';

const props = defineProps({
  // 编辑器中呈现的内容
  codes: {
    type: String,
    default: function () {
      return '';
    },
  },
  readOnly: {
    type: Boolean,
    default: function () {
      return false;
    },
  },
  // 主要配置
  editorOptions: {
    type: Object,
    default: function () {
      return {
        selectOnLineNumbers: true,
        roundedSelection: false,
        readOnly: false, // 只读（以 readOnly prop 为准，见下方合并）
        cursorStyle: 'line', // 光标样式
        automaticLayout: false, // 自动布局
        glyphMargin: true, // 字形边缘
        useTabStops: false,
        fontSize: 28, // 字体大小
        autoIndent: true, // 自动布局
      };
    },
  },
});

const container = ref(null);
let monacoEditor = null;

onMounted(() => {
  monacoEditor = monaco.editor.create(container.value, {
    value: props.codes, // 见props
    language: 'json',
    theme: 'vs', // 编辑器主题：vs, hc-black, or vs-dark，更多选择详见官网
    automaticLayout: true, //自动布局
    //   foldingStrategy: 'indentation', // 代码可分小段折叠
    scrollbar: {
      // 滚动条设置
      verticalScrollbarSize: 4, // 竖滚动条
      horizontalScrollbarSize: 10, // 横滚动条
    },
    lineNumbersMinChars: 5,
    editorOptions: { ...props.editorOptions, readOnly: props.readOnly }, // 同codes
  });
  setTimeout(() => {
    monacoEditor.trigger('anyString', 'editor.action.formatDocument');
    monacoEditor.setValue(monacoEditor.getValue());
  }, 100);
});
</script>
<style lang="scss" scoped>
.monaco-editor {
  min-height: 300px;
}
</style>
