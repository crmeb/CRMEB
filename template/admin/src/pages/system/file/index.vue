<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="box" ref="picBox">
        <upload-file
          v-if="uploadShow"
          :isShow="0"
          :pageLimit="pageLimit"
        ></upload-file>
      </div>
    </Card>
  </div>
</template>
<script>
import uploadFile from "@/components/uploadPictures/index";
export default {
  components: { uploadFile },
  name: "system_file",
  data() {
    return {
      pageLimit: 30,
      uploadShow: false,
    };
  },
  mounted() {
    let winwidth = this.$refs.picBox.clientWidth;
    console.log(this.$refs.picBox.clientWidth);
    if (winwidth < 1018) {
      this.pageLimit = 12;
    } else if (winwidth < 1185) {
      this.pageLimit = 21;
    } else if (winwidth < 1327) {
      this.pageLimit = 24;
    } else if (winwidth < 1475) {
      this.pageLimit = 27;
    } else if (winwidth > 1483) {
      this.pageLimit = 40;
    }
    this.$nextTick(() => {
      this.uploadShow = true;
    });
  },
  methods: {},
};
</script>
<style scoped lang="stylus">
.box {
  width: 100%;
  background: #fff;
}
</style>