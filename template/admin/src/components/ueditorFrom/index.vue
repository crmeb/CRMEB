<template>
  <div>
    <WangEditor style="width: 100%; height: 60%" :content="content" @editorContent="getEditorContent"></WangEditor>
  </div>
</template>

<script>
import WangEditor from '@/components/wangEditor/index.vue';
export default {
  name: 'index',
  components: { WangEditor },
  props: {
    content: '',
  },
  watch: {
    content: {
      handler(val) {
        // this.contents = val
        // this.$emit('getContent', val);
      },
      deep: true,
    },
  },
  data() {
    return {
      contents: '',
    };
  },
  methods: {
    getEditorContent(data) {
      this.contents = data;
    },
  },
  created() {},
};
</script>

<style scoped></style>
