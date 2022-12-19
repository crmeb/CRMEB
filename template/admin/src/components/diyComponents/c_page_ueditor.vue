<template>
  <div class="box" v-if="datas">
    <WangEditor style="width: 100%; height: 60%" :content="content" @editorContent="getEditorContent"></WangEditor>
  </div>
</template>

<script>
import WangEditor from '@/components/wangEditor/index.vue';

export default {
  name: 'c_page_ueditor',
  props: {
    name: {
      type: String,
    },
    configData: {
      type: null,
    },
    configNum: {
      type: Number | String,
      default: 'default',
    },
  },
  components: { WangEditor },
  data() {
    return {
      datas: {},
    };
  },
  created() {
    this.datas = this.configData[this.configNum][this.name];
    this.content = this.datas.val || '';
  },
  watch: {
    configData: {
      handler(nVal, oVal) {
        this.datas = nVal[this.configNum][this.name];
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    getEditorContent(data) {
      this.datas.val = data;
    },
  },
};
</script>

<style scoped></style>
