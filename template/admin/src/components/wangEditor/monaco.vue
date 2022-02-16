<template>
  <div ref="code_box" class="text"></div>
</template>


<script>
import * as monaco from "monaco-editor";

export default {
  data() {
    return {
      monacoInstance: null,
    };
  },
  props: {
    value: {
      type: String,
      default: "",
    },
  },
  mounted() {
    this.seteditor();
  },
  methods: {
    setValue(val) {
      // this.monacoInstance.setValue(val)
    },
    seteditor() {
      // 初始化编辑器实例

      this.monacoInstance = monaco.editor.create(this.$refs.code_box, {
        value: this.value,
        theme: "vs", // vs, hc-black, or vs-dark

        language: "html", // shell、sql、python

        readOnly: false, // 不能编辑
      });
      // 编辑器内容发生改变时触发
      this.monacoInstance.onDidChangeModelContent(() => {
        this.$emit("change", this.monacoInstance.getValue());
      });
    },
  },
  beforeDestroy() {
    this.monacoInstance.dispose();
    this.monacoInstance = null;
  },
};
</script>

<style lang="css" scoped>
.editor {
  width: 100%;
  margin: 0 auto;
}
.toolbar {
  border: 1px solid #ccc;
}
.text {
  border: 1px solid #ccc;
  min-height: 600px;
}
.w-e-text-container {
  /* height: 490px !important; */
}
</style>

