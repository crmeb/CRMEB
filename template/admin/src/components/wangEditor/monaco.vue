<template>
  <div ref="code_box" class="text">
    <el-input v-model="content" type="textarea" placeholder="" @change="changeContent" />
  </div>
</template>

<script>
// import * as monaco from 'monaco-editor';
// import 'monaco-editor/esm/vs/basic-languages/javascript/javascript.contribution';
export default {
  data() {
    return {
      monacoInstance: null,
      content: '',
    };
  },
  props: {
    value: {
      type: String,
      default: '',
    },
  },
  watch: {
    value(nal) {},
  },
  mounted() {
    // this.seteditor();
    this.content = this.value;
  },
  methods: {
    changeContent() {
      this.$emit('change', this.content);
    },
    setValue(val) {
      // this.monacoInstance.setValue(val)
    },
    seteditor() {
      // 初始化编辑器实例

      this.monacoInstance = monaco.editor.create(this.$refs.code_box, {
        value: this.value,
        theme: 'vs', // vs, hc-black, or vs-dark

        language: 'html', // shell、sql、python

        readOnly: false, // 不能编辑
      });
      // 编辑器内容发生改变时触发
      this.monacoInstance.onDidChangeModelContent(() => {
        this.$emit('change', this.monacoInstance.getValue());
      });
    },
  },
  beforeDestroy() {
    if (this.monacoInstance) {
      this.monacoInstance.dispose();
      this.monacoInstance = null;
    }
  },
};
</script>

<style lang="css" scoped>
.editor {
  width: 100%;
  margin: 0 auto;
}
.text ::v-deep .ivu-input-wrapper {
  min-height: 600px;
}
.text ::v-deeptextarea.ivu-input {
  min-height: 600px;
}
.text {
  border: 1px solid #ccc;
  min-height: 600px;
}
.w-e-text-container {
  /* height: 490px !important; */
}
</style>
