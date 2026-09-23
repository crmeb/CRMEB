<template>
  <div class="main" v-loading="loading || preparing > 0">
    <div v-if="uploading">
      <div class="img-list" :class="{ 'none-card': imgList.length }">
        <el-upload
          ref="uploadRef"
          :action="fileUrl"
          list-type="picture-card"
          :on-change="fileChange"
          :on-error="fileError"
          :before-upload="beforeUpload"
          :file-list="imgList"
          :auto-upload="false"
          :disabled="loading || preparing > 0"
          :multiple="true"
          :limit="limit"
          accept="image/*"
        >
          <template #default
            ><div class="upload-card" v-if="!imgList.length">
              <i class="el-icon-plus"></i>
              <p class="text">点击选择图片</p>
            </div></template
          >
          <template #file="{ file }"
            ><div>
              <img class="el-upload-list__item-thumbnail" :src="file.url" alt="" />
              <i class="el-icon-error btndel" v-db-click @click="handleRemove(file)" /></div
          ></template>
        </el-upload>
      </div>

      <div class="footer">
        <div v-if="imgList.length">共{{ imgList.length }}/{{ limit }}张，{{ (allSize / 1000000).toFixed(2) }} M</div>
        <div v-else></div>
        <div class="upload-btn">
          <div v-if="imgList.length < limit" class="btn" v-db-click @click="selectImgs">
            {{ imgList.length ? '继续选择' : '选择图片' }}
          </div>
          <div class="btn upload" :class="{ 'no-pic': !imgList.length }" v-db-click @click="submitUpload">确认上传</div>
        </div>
      </div>
    </div>
    <div v-else class="upload-success">
      <div class="success">
        <img class="image" src="../../../assets/images/success.jpg" alt="" />
      </div>
      <div class="text">图片上传成功</div>
      <div class="again" v-db-click @click="again">继续上传</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import Setting from '@/setting';
import { scanUpload } from '@/api/setting';
import compressImg from '@/utils/compressImg.js';
import { isPicUpload } from '@/utils';

defineOptions({ name: 'app_upload_file' });

const route = useRoute();

const uploadRef = ref(null);

const fileUrl = Setting.apiBaseURL + '/image/scan_upload';
const imgList = ref([]);
const allSize = ref(0);
const token = ref('');
const uploading = ref(true);
const limit = ref(20);
const loading = ref(false);
const preparing = ref(0);
const pid = ref(0);

// created
token.value = route.query.token;
pid.value = route.query.pid;
document.title = '手机端扫码上传';

function selectImgs() {
  if (loading.value || preparing.value) return;
  // element-plus 无 $refs['upload-inner'].handleClick()，改为触发内部 file input
  uploadRef.value.$el.querySelector('input[type="file"]').click();
}
function again() {
  if (loading.value || preparing.value) return;
  uploading.value = true;
  imgList.value = [];
  allSize.value = 0;
}
async function submitUpload() {
  if (loading.value || preparing.value) return;
  if (!imgList.value.length) {
    ElMessage.warning('请先选择图片');
    return;
  }
  loading.value = true;
  try {
    for (const item of [...imgList.value]) {
      if (item._scanUploaded) continue;
      await uploadItem(item.raw);
      item._scanUploaded = true;
    }
    uploading.value = false;
  } catch (err) {
    ElMessage.error(err.msg || err.message || '上传失败');
  } finally {
    loading.value = false;
  }
}
function handleRemove(file) {
  if (loading.value || preparing.value) return;
  const index = imgList.value.findIndex((item) => item === file || (file.uid !== undefined && item.uid === file.uid));
  if (index < 0) return;
  imgList.value.splice(index, 1);
  updateTotalSize();
}
function updateTotalSize() {
  allSize.value = imgList.value.reduce((total, item) => total + item.raw.size, 0);
}

function uploadItem(file) {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('uploadToken', token.value);
  formData.append('pid', pid.value);
  return scanUpload(formData).then((res) => {
    if (res.status !== 200) throw res;
  });
}
function fileError(err, file, fileList) {
  ElMessage.error(err.msg || err.message || '上传失败');
}
function beforeUpload(file) {
  return isPicUpload(file);
}
async function fileChange(file, fileList) {
  if (loading.value) return;
  imgList.value = [...fileList];
  if (file.size >= 2097152) {
    preparing.value++;
    try {
      const compressed = await compressImg(file.raw);
      const item = imgList.value.find((item) => item.uid === file.uid);
      if (item) item.raw = compressed;
    } catch (err) {
      imgList.value = imgList.value.filter((item) => item.uid !== file.uid);
      ElMessage.error(err.msg || err.message || '图片处理失败');
    } finally {
      preparing.value--;
    }
  }
  updateTotalSize();
}
function loadData(item, callback) {
  getCategoryListApi({
    pid: item.value,
  })
    .then(async (res) => {
      const data = res.data.list;
      callback(data);
    })
    .catch((res) => {});
}
</script>
<style lang="scss" scoped>
.upload-btn {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.img-list {
  padding: 10px;
  box-sizing: border-box;
  overflow-y: auto;
  height: calc(100vh - 50px);
  background-color: #fff;
}
:deep(.el-upload-list--picture-card) {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  width: 100%;
  max-width: 720px;
  margin: 0 auto;
}
:deep(.el-upload-list--picture-card .el-upload-list__item) {
  margin: 0;
  width: 100%;
  height: auto;
  padding: 0;
  aspect-ratio: 1 / 1;
}
:deep(.el-upload-list--picture-card .el-upload-list__item > div) {
  // position: relative;
  width: 100%;
  height: 100%;
}
:deep(.el-upload--picture-card) {
  grid-column: 1 / -1;
  width: 100%;
  display: flex;
  height: 146px;
  justify-content: center;
  align-items: center;
  background: #f9f9f9;
}
:deep(.el-upload-list--picture-card .el-upload-list__item img) {
  width: 100%;
  height: 100%;
  border-radius: 6px;
  object-fit: cover;
  position: absolute;
  left: 0;
  top: 0;
}
.btndel {
  position: absolute;
  z-index: 1;
  font-size: 18px;
  right: -1px;
  top: -1px;
  color: #282828;
  opacity: 0.5;
}
:deep(.el-upload--picture-card:hover),
.el-upload:focus {
  border-color: #c0ccda;
}
.img-box {
  display: flex;
  padding-left: 100px;
  flex-wrap: wrap;
}
.none-card :deep(.el-upload--picture-card) {
  display: none !important;
}
.footer {
  padding: 0 10px 0 15px;
  position: fixed;
  bottom: 0;
  width: 100%;
  box-sizing: border-box;
  background-color: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(10px);
  z-index: 277;
  border-top: 1px solid #f0f0f0;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  > div:first-child {
    min-width: 0;
    line-height: 18px;
    overflow-wrap: anywhere;
  }
  .btn {
    border: 1px solid #cccccc;
    width: 88px;
    height: 30px;
    box-sizing: border-box;
    border-radius: 15px;
    color: #000;
    font-size: 14px;
    font-family: PingFang SC-Regular, PingFang SC;
    font-weight: 400;
    color: #666666;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .upload {
    background-color: #e93323;
    color: #fff;
    margin-left: 10px;
  }
  .upload.no-pic {
    background: #e93323;
    opacity: 0.3;
  }
}
.upload-card {
  display: flex;
  flex-direction: column;
  line-height: 16px;
  .el-icon-plus {
    font-size: 28px;
    font-weight: bold;
    color: #bbbbbb;
  }
  .text {
    font-size: 13px;
    font-weight: 400;
    color: #999999;
    margin-top: 18px;
  }
}
.upload-success {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  height: 80vh;
  .success {
    width: 50px;
    height: 50px;
    background: #4bbc12;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    .image {
      width: 60%;
    }
  }
  .text {
    font-size: 16px;
    font-family: PingFang SC-Medium, PingFang SC;
    font-weight: 500;
    color: #282828;
    margin-bottom: 40px;
  }
  .again {
    width: 150px;
    height: 43px;
    border-radius: 21px;
    text-align: center;
    line-height: 41px;
    font-size: 15px;
    font-weight: 400;
    color: #333333;
    border: 1px solid #cccccc;
  }
}
</style>
