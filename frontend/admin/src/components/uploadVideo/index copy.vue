<template>
  <div>
    <div class="mt20 ml20">
      <el-input class="perW35" v-model="videoLink" placeholder="请输入视频链接" />
      <input type="file" ref="refid" style="display: none" @change="zh_uploadFile_change" />
      <el-button
        v-if="upload_type !== '1' || videoLink"
        type="primary"
        icon="ios-cloud-upload-outline"
        class="ml10"
        v-db-click
        @click="zh_uploadFile"
        >{{ videoLink ? '确认添加' : '上传视频' }}</el-button
      >
      <el-upload
        v-if="upload_type === '1' && !videoLink"
        :show-file-list="false"
        :action="fileUrl"
        class="ml10"
        :before-upload="videoSaveToUrl"
        :data="uploadData"
        :headers="header"
        :multiple="true"
        style="display: inline-block"
        accept=".mp4"
      >
        <el-button type="primary" icon="ios-cloud-upload-outline">上传视频</el-button>
      </el-upload>
      <Progress :percent="progress" :stroke-width="5" v-if="upload.videoIng" />
      <div class="video-style" v-if="formValidate.video_link">
        <video
          style="width: 100%; height: 100% !important; border-radius: 10px"
          :src="formValidate.video_link"
          controls="controls"
        >
          您的浏览器不支持 video 标签。
        </video>
        <div class="mark"></div>
        <i class="el-icon-delete iconv" v-db-click @click="delVideo"></i>
      </div>
    </div>
    <div class="mt50 ml20">
      <el-button type="primary" v-db-click @click="uploads">确认</el-button>
    </div>
  </div>
</template>

<script setup>
import { nextTick, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { uploadByPieces } from '@/utils/upload'; //引入uploadByPieces方法
import { productGetTempKeysApi, uploadType } from '@/api/product';
import videoCloud from '@/utils/videoCloud';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';
import { isVideoUpload } from '@/utils';

defineOptions({ name: 'vide11o' });

const emit = defineEmits(['getvideo']);

const fileUrl = Setting.apiBaseURL + '/file/upload';
const upload = reactive({
  videoIng: false, // 是否显示进度条；
});
const progress = ref(0); // 进度条默认0
const videoLink = ref('');
const formValidate = reactive({
  video_link: '',
});
const upload_type = ref('');
const uploadData = ref({});
const header = ref({});
const refid = ref(null);

// created
getUploadType();
getToken();

// 删除视频；
function delVideo() {
  formValidate['video_link'] = '';
}
//获取视频上传类型
function getUploadType() {
  uploadType().then((res) => {
    upload_type.value = res.data.upload_type;
  });
}
// 上传成功
function handleSuccess(res, file, fileList) {
  if (res.status === 200) {
    formValidate.video_link = res.data.src;
    ElMessage.success(res.msg);
  } else {
    ElMessage.error(res.msg);
  }
}
function videoSaveToUrl(file) {
  if (isVideoUpload(filex))
    uploadByPieces({
      file: file, // 视频实体
      pieceSize: 3, // 分片大小
      success: (data) => {
        formValidate.video_link = data.file_path;
        progress.value = 100;
      },
      error: (e) => {
        ElMessage.error(e.msg);
      },
      uploading: (chunk, allChunk) => {
        upload.videoIng = true;
        let st = Math.floor((chunk / allChunk) * 100);
        progress.value = st;
      },
    });
  return false;
}
function getToken() {
  header.value['Authori-zation'] = 'Bearer ' + getCookies('token');
}
function beforeUpload() {
  uploadData.value = {};
  let promise = new Promise((resolve) => {
    nextTick(function () {
      resolve(true);
    });
  });
  return promise;
}
function zh_uploadFile() {
  if (videoLink.value) {
    formValidate.video_link = videoLink.value;
  } else {
    refid.value.click();
  }
}
function zh_uploadFile_change(evfile) {
  if (evfile.target.files[0].type !== 'video/mp4') {
    return ElMessage.error('只能上传mp4文件');
  }
  let types = {
    key: evfile.target.files[0].name,
    contentType: evfile.target.files[0].type,
  };
  productGetTempKeysApi(types).then((res) => {
    videoCloud
      .videoUpload({
        type: res.data.type,
        evfile: evfile,
        res: res,
        uploading(status, progress) {
          upload.videoIng = status;
        },
      })
      .then((res) => {
        formValidate.video_link = res.url;
        ElMessage.success('视频上传成功');
      })
      .catch((res) => {
        ElMessage.error(res);
      });
  });
}
function uploads() {
  emit('getvideo', formValidate.video_link);
}
</script>

<style scoped>
.video-style {
  width: 40%;
  height: 180px;
  border-radius: 10px;
  background-color: #707070;
  margin-top: 10px;
  position: relative;
  overflow: hidden;
}
.video-style .iconv {
  color: #fff;
  line-height: 180px;
  width: 50px;
  height: 50px;
  display: inherit;
  font-size: 26px;
  position: absolute;
  top: -74px;
  left: 50%;
  margin-left: -25px;
}
.video-style .mark {
  position: absolute;
  width: 100%;
  height: 30px;
  top: 0;
  background-color: rgba(0, 0, 0, 0.5);
  text-align: center;
}
</style>
