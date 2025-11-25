<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-tabs class="p-x-20" v-model="fileType">
        <el-tab-pane name="img" label="图片管理"></el-tab-pane>
        <el-tab-pane name="video" label="视频管理"></el-tab-pane>
      </el-tabs>
      <div class="box" ref="picBox">
        <upload-file v-if="fileType === 'img'" :isPage="true" :isShow="0" :pageLimit="pageLimit"></upload-file>
        <videoModal v-if="fileType === 'video'" :isPage="true" :isShow="0" :pageLimit="pageLimit"></videoModal>
      </div>
    </el-card>
  </div>
</template>
<script>
import uploadFile from '@/components/uploadPictures/index';
import videoModal from '@/components/uploadVideo2/index';
export default {
  components: { uploadFile, videoModal },
  name: 'system_file',
  data() {
    return {
      fileType: 'img',
      pageLimit: 12,
      uploadShow: false, // 是否显示上传按钮
    };
  },
  mounted() {
    this.uploadShow = true;
  },
  methods: {},
};
</script>
<style lang="scss" scoped>
.box {
  width: 100%;
  height: 100%;
  background: #fff;
}
::v-deep .el-card__body {
  min-height: 700px;
  padding: 16px 16px 16px 0;
}
::v-deep .conter .pictrueList {
  max-width: 100%;
}
</style>
