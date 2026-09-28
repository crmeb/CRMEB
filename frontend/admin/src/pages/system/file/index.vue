<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-tabs v-model="fileType">
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
<script setup>
import { ref, onMounted } from 'vue';
import uploadFile from '@/components/uploadPictures/index';
import videoModal from '@/components/uploadVideo2/index';

defineOptions({ name: 'system_file' });

const picBox = ref(null);

const fileType = ref('img');
const pageLimit = ref(12);
const uploadShow = ref(false); // 是否显示上传按钮

onMounted(() => {
  uploadShow.value = true;
});
</script>
<style lang="scss" scoped>
.box {
  width: 100%;
  height: 100%;
  padding-top: 15px;
  background: var(--prev-bg-white);
}
:deep(.el-card__body) {
  min-height: 700px;
  padding: 0 16px 16px 0;
}
:deep(.el-tabs__header) {
  margin: 0;
  padding: 0 20px;
}
:deep(.conter .pictrueList) {
  max-width: 100%;
}
</style>
