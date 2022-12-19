<template>
  <div>
    <div class="mt20 ml20">
      <Input class="perW35" v-model="videoLink" placeholder="请输入视频链接" />
      <input type="file" ref="refid" style="display: none" @change="zh_uploadFile_change" />
      <Button
        v-if="upload_type !== '1' || videoLink"
        type="primary"
        icon="ios-cloud-upload-outline"
        class="ml10"
        @click="zh_uploadFile"
        >{{ videoLink ? '确认添加' : '上传视频' }}</Button
      >
      <Upload
        v-if="upload_type === '1' && !videoLink"
        :show-upload-list="false"
        :action="fileUrl"
        class="ml10"
        :before-upload="videoSaveToUrl"
        :data="uploadData"
        :headers="header"
        :multiple="true"
        style="display: inline-block"
      >
        <Button type="primary" icon="ios-cloud-upload-outline">上传视频</Button>
      </Upload>
      <Progress :percent="progress" :stroke-width="5" v-if="upload.videoIng" />
      <div class="iview-video-style" v-if="formValidate.video_link">
        <video
          style="width: 100%; height: 100% !important; border-radius: 10px"
          :src="formValidate.video_link"
          controls="controls"
        >
          您的浏览器不支持 video 标签。
        </video>
        <div class="mark"></div>
        <Icon type="ios-trash-outline" class="iconv" @click="delVideo" />
      </div>
    </div>
    <div class="mt50 ml20">
      <Button type="primary" @click="uploads">确认</Button>
    </div>
  </div>
</template>

<script>
import { uploadByPieces } from '@/utils/upload'; //引入uploadByPieces方法
import { productGetTempKeysApi, uploadType } from '@/api/product';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';
// import "../../../public/UEditor/dialogs/internal";
export default {
  name: 'vide11o',
  data() {
    return {
      fileUrl: Setting.apiBaseURL + '/file/upload',
      upload: {
        videoIng: false, // 是否显示进度条；
      },
      progress: 0, // 进度条默认0
      videoLink: '',
      formValidate: {
        video_link: '',
      },
      upload_type: '',
      uploadData: {},
      header: {},
    };
  },
  created() {
    this.uploadType();
    this.getToken();
  },
  methods: {
    videoSaveToUrl(file) {
      uploadByPieces({
        file: file, // 视频实体
        pieceSize: 3, // 分片大小
        success: (data) => {
          this.formValidate.video_link = data.file_path;
          this.progress = 100;
        },
        error: (e) => {
          this.$Message.error(e.msg);
        },
        uploading: (chunk, allChunk) => {
          this.videoIng = true;
          let st = Math.floor((chunk / allChunk) * 100);
          this.progress = st;
        },
      });
      return false;
    },
    // 删除视频；
    delVideo() {
      let that = this;
      that.$set(that.formValidate, 'video_link', '');
    },
    //获取视频上传类型
    uploadType() {
      uploadType().then((res) => {
        this.upload_type = res.data.upload_type;
      });
    },
    // 上传成功
    handleSuccess(res, file, fileList) {
      if (res.status === 200) {
        this.formValidate.video_link = res.data.src;
        this.$Message.success(res.msg);
      } else {
        this.$Message.error(res.msg);
      }
    },
    getToken() {
      this.header['Authori-zation'] = 'Bearer ' + getCookies('token');
    },
    beforeUpload() {
      this.uploadData = {};
      let promise = new Promise((resolve) => {
        this.$nextTick(function () {
          resolve(true);
        });
      });
      return promise;
    },
    zh_uploadFile() {
      if (this.videoLink) {
        this.formValidate.video_link = this.videoLink;
      } else {
        this.$refs.refid.click();
      }
    },
    zh_uploadFile_change(evfile) {
      let that = this;
      if (evfile.target.files[0].type !== 'video/mp4') {
        return that.$Message.error('只能上传mp4文件');
      }
      productGetTempKeysApi().then((res) => {
        that.$videoCloud
          .videoUpload({
            type: res.data.type,
            evfile: evfile,
            res: res,
            uploading(status, progress) {
              that.upload.videoIng = status;
            },
          })
          .then((res) => {
            that.formValidate.video_link = res.url;
            that.$Message.success('视频上传成功');
          })
          .catch((res) => {
            that.$Message.error(res);
          });
      });
    },
    uploads() {
      this.$emit('getVideo', this.formValidate.video_link);
    },
  },
};
</script>

<style scoped>
.iview-video-style {
  width: 40%;
  height: 180px;
  border-radius: 10px;
  background-color: #707070;
  margin-top: 10px;
  position: relative;
  overflow: hidden;
}
.iview-video-style .iconv {
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
.iview-video-style .mark {
  position: absolute;
  width: 100%;
  height: 30px;
  top: 0;
  background-color: rgba(0, 0, 0, 0.5);
  text-align: center;
}
</style>
