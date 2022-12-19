<template>
  <div class="box" v-if="configData">
    <WangEditor style="width: 100%; height: 60%" :content="content" @editorContent="getEditorContent"></WangEditor>
  </div>
</template>

<script>
import WangEditor from '@/components/wangEditor/index.vue';

export default {
  name: 'c_page_ueditor',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  components: { WangEditor },
  data() {
    return {
      description: '',
      defaults: {},
      content: '',
      configData: {},
    };
  },
  created() {
    this.defaults = this.configObj;
    this.configData = this.configObj[this.configNme];
    this.$nextTick((e) => {
      this.content = this.configData.val;
    });
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    getEditorContent(data) {
      this.configData.val = data;
    },
  },
};
</script>

<style scoped></style>
