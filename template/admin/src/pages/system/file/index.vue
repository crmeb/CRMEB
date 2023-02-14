<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="box" ref="picBox">
        <upload-file v-if="uploadShow" :isShow="0" :pageLimit="pageLimit"></upload-file>
      </div>
    </Card>
  </div>
</template>
<script>
import uploadFile from '@/components/uploadPictures/index';
export default {
  components: { uploadFile },
  name: 'system_file',
  data() {
    return {
      pageLimit: 30,
      uploadShow: false,
    };
  },
  mounted() {
    console.log(this.$refs.picBox.clientWidth);

    this.$nextTick(() => {
      let winWidth = this.$refs.picBox.clientWidth;
      let winHeight = document.body.clientHeight - 170;
      if (winWidth < 1018) {
        this.pageLimit = 18;
        this.pageLimit = winHeight > 790 ? 24 : 18;
      } else if (winWidth < 1185) {
        this.pageLimit = winHeight > 790 ? 30 : 18;
      } else if (winWidth < 1222) {
        this.pageLimit = 30;
      } else if (winWidth < 1327) {
        this.pageLimit = 32;
      } else if (winWidth < 1750) {
        this.pageLimit = 40;
      } else if (winWidth < 2100) {
        this.pageLimit = winHeight > 790 ? 60 : 48;
      } else if (winWidth > 2100) {
        this.pageLimit = winHeight > 790 ? 75 : 60;
      }
      this.uploadShow = true;
    });
  },
  methods: {},
};
</script>
<style scoped lang="stylus">
.box {
  width: 100%;
  height 100%
  background: #fff;
}
/deep/ .ivu-card-body{
  min-height: 700px;
}
/deep/ .conter .pictrueList{
  max-width 100%
}
</style>
