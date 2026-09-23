<template>
  <div class="theme-import">
    <div class="goods-upload">
      <el-upload
        v-show="!fileUrl && !importStatus"
        ref="upload"
        class="upload-demo"
        :drag="!fileUrl"
        :show-file-list="false"
        :action="uploadUrl"
        :headers="header"
        :before-upload="fileChange"
        :on-success="handleSuccess"
        :on-error="handleError"
        accept=".zip"
      >
        <div v-if="uploadLoading" class="flex-column">
          <i class="el-icon-loading" style="font-size: 40px; color: #ccc"></i>
          <div class="el-upload__text">正在上传中...</div>
        </div>
        <template v-else>
          <img class="el-upload-dragger__icon mb20" src="@/assets/images/upload-theme-icon.png" alt="" />
          <div class="el-upload__text">将文件拖到此处，或<em>点击添加</em></div>
          <div class="el-upload__trip">支持 .zip，限50M以内</div>
        </template>
      </el-upload>
      <div v-show="fileUrl && !importStatus" class="file-info">
        <img class="el-upload-dragger__icon mb20" src="@/assets/images/upload-theme-icon.png" alt="" />
        <div class="el-upload__text">{{ fileName }}</div>
        <div class="flex mt12" v-if="fileUrl && !importLoading">
          <div class="active-btn" @click="selectFile">重新上传</div>
          <div class="active-btn" @click="fileUrl = ''">删除</div>
        </div>
        <div class="el-upload__trip" v-if="importLoading">
          正在导入，您可关闭当前弹窗，稍候可在列表查看导入结果
          <i class="el-icon-loading"></i>
        </div>
        <el-button v-else class="btn-import" type="primary" @click="importThemePkg">立即导入</el-button>
      </div>
      <div v-show="fileUrl && importStatus" class="file-info">
        <img class="el-upload-dragger__icon mb20" :src="statusImage" alt="" />
        <div class="el-upload__text">导入成功</div>
        <div>
          <el-button class="btn-import" @click="selectFile">再次导入</el-button>
          <el-button type="primary" class="btn-import" @click="close">完成</el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import __asset_file_success_0 from '@/assets/images/file-success.png';

import { importTheme } from '@diy/api';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';

defineOptions({ name: 'themeImport' });

const emit = defineEmits(['close', 'success']);

const upload = ref(null);
const uploadUrl = Setting.apiBaseURL + '/file/upload/1?type=1';
const header = {
  'Authori-zation': 'Bearer ' + getCookies('token'),
};
const fileName = ref('');
const fileUrl = ref('');
const importStatus = ref(false);
const importLoading = ref(false);
const uploadLoading = ref(false);
const statusImage = ref(__asset_file_success_0);

function fileChange(file, fileList) {
  const isZip = file.name.endsWith('.zip');
  if (!isZip) {
    ElMessage.error('请上传 .zip 格式的文件');
    return false;
  }
  // 限制50M
  if (file.size >= 52428800) {
    ElMessage.error('文件大小不能超过50MB');
    return false;
  } else {
    uploadLoading.value = true;
    fileName.value = file.name;
    return true;
  }
}

function selectFile() {
  importStatus.value = false;
  importLoading.value = false;
  uploadLoading.value = false;
  // 调起选择文件
  // element-plus 无 $refs['upload-inner'].handleClick()，改为触发内部 file input
  upload.value.$el.querySelector('input[type="file"]').click();
}

function handleSuccess(res, file, fileList) {
  uploadLoading.value = false;
  if (res.status === 200) {
    fileUrl.value = res.data.src;
  } else {
    ElMessage.error(res.msg);
  }
}

function handleError(err, file, fileList) {
  uploadLoading.value = false;
  ElMessage.error('上传失败');
}

function importThemePkg() {
  importLoading.value = true;
  importStatus.value = false;
  importTheme({
    url: fileUrl.value,
  })
    .then((res) => {
      // 返回导入结果
      importStatus.value = true;
      importLoading.value = false;
      statusImage.value = __asset_file_success_0;
      ElMessage.success('导入成功');
      emit('success');
    })
    .catch((err) => {
      importLoading.value = false;
      importStatus.value = false;
      ElMessage.error(err.msg);
    });
}

function close() {
  fileUrl.value = '';
  fileName.value = '';
  importStatus.value = false;
  emit('close');
}
</script>

<style lang="scss" scoped>
.goods-upload {
  width: 100%;
  height: 680px;
  .upload-demo {
    height: 100%;
  }
  :deep(.el-upload) {
    width: 100%;
    height: 100%;
    .el-upload-dragger {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 100px 0; // Adjusted padding since there is no top part
      .el-upload-dragger__icon {
        width: 54px;
        // height: 57px;
      }
      .el-upload__trip {
        font-weight: 400;
        font-size: 12px;
        color: #999999;
        margin-top: 6px;
      }
    }
  }
  .file-info {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    border: 1px dashed #d9d9d9;
    border-radius: 4px;
    .active-btn {
      color: var(--prev-color-primary);
      font-size: 12px;
      font-weight: 400;
      margin: 0 6px;
      cursor: pointer;
    }
    .btn-import {
      margin-top: 26px;
    }
    .el-upload-dragger__icon {
      height: 57px;
    }
    .el-upload__trip {
      display: flex;
      align-items: center;
      font-weight: 400;
      font-size: 12px;
      color: #999;
      margin-top: 6px;
    }
  }
}
</style>
