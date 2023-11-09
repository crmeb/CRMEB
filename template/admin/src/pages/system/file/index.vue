<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <div class="box" ref="picBox">
        <upload-file :isPage="true" :isShow="0" :pageLimit="pageLimit"></upload-file>
      </div>
    </el-card>
  </div>
</template>
<script>
import uploadFile from '@/components/uploadPictures/index';
export default {
  components: { uploadFile },
  name: 'system_file',
  data() {
    return {
      pageLimit: 12,
      uploadShow: false,
    };
  },
  mounted() {
    // this.$nextTick(() => {
    //   let winWidth = this.$refs.picBox.clientWidth;
    //   let winHeight = document.body.clientHeight - 170;
    //   if (winWidth < 1018) {
    //     this.pageLimit = 12;
    //     this.pageLimit = winHeight > 790 ? 12 : 16;
    //   } else if (winWidth < 1185) {
    //     this.pageLimit = winHeight > 790 ? 15 : 24;
    //   } else if (winWidth < 1222) {
    //     this.pageLimit = 15;
    //   } else if (winWidth < 1327) {
    //     this.pageLimit = 18;
    //   } else if (winWidth < 1750) {
    //     this.pageLimit = 21;
    //   } else if (winWidth < 2100) {
    //     this.pageLimit = winHeight > 790 ? 30 : 26;
    //   } else if (winWidth > 2100) {
    //     this.pageLimit = winHeight > 790 ? 36 : 26;
    //   }
    this.uploadShow = true;
    // });
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
::v-deep .el-card__body{
  min-height: 700px;
  padding 16px 16px 16px 0
}
::v-deep .conter .pictrueList{
  max-width 100%
}
</style>
