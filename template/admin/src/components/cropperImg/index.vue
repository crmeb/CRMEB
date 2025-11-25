<template>
  <div class="cropper-content">
    <div class="cropper-box">
      <div class="cropper">
        <vue-cropper
          ref="cropper"
          :img="option.img"
          :outputSize="option.outputSize"
          :outputType="option.outputType"
          :info="option.info"
          :canScale="option.canScale"
          :autoCrop="option.autoCrop"
          :autoCropWidth="option.autoCropWidth"
          :autoCropHeight="option.autoCropHeight"
          :fixed="option.fixed"
          :fixedNumber="option.fixedNumber"
          :full="option.full"
          :fixedBox="option.fixedBox"
          :canMove="option.canMove"
          :canMoveBox="option.canMoveBox"
          :original="option.original"
          :centerBox="option.centerBox"
          :height="option.height"
          :infoTrue="option.infoTrue"
          :maxImgSize="option.maxImgSize"
          :enlarge="option.enlarge"
          :mode="option.mode"
          @realTime="realTime"
          @imgLoad="imgLoad"
        >
        </vue-cropper>
      </div>
      <!--底部操作工具按钮-->
      <div class="footer-btn">
        <div class="scope-btn">
          <input
            type="file"
            id="uploads"
            style="position: absolute; clip: rect(0 0 0 0)"
            accept="image/png, image/jpeg, image/gif, image/jpg"
            @change="selectImg($event)"
          />
          <el-button size="mini" type="danger" plain icon="el-icon-zoom-in" v-db-click @click="changeScale(1)"
            >放大</el-button
          >
          <el-button size="mini" type="danger" plain icon="el-icon-zoom-out" v-db-click @click="changeScale(-1)"
            >缩小</el-button
          >
          <el-button size="mini" type="danger" plain v-db-click @click="rotateLeft">↺ 左旋转</el-button>
          <el-button size="mini" type="danger" plain v-db-click @click="rotateRight">↻ 右旋转</el-button>
        </div>
      </div>
    </div>
    <!--预览效果图-->
    <div class="show-preview">
      <div class="preview">
        <img :src="previews.url" :style="previews.img" />
      </div>
      <div class="upload-btn">
        <label class="btn" for="uploads">选择图片</label>
        <el-button size="mini" type="success" v-db-click @click="uploadImg()">确认上传</el-button>
      </div>
    </div>
  </div>
</template>

<script>
import { VueCropper } from 'vue-cropper';
// import { updateAvatar } from ''; //这里为文件上传的接口换成自己的文件
import { fileUpload } from '@/api/setting';
export default {
  name: 'CropperImage',
  components: {
    VueCropper,
  },
  data() {
    return {
      name: '',
      resImg: '',
      previews: {},
      option: {
        img: '', //裁剪图片的地址
        outputSize: 1, //裁剪生成图片的质量(可选0.1 - 1)
        outputType: 'png', //裁剪生成图片的格式（jpeg || png || webp）
        info: true, //图片大小信息
        canScale: true, //图片是否允许滚轮缩放
        autoCrop: true, //是否默认生成截图框
        autoCropWidth: 200, //默认生成截图框宽度
        autoCropHeight: 200, //默认生成截图框高度
        fixed: true, //是否开启截图框宽高固定比例
        fixedNumber: [1, 1], //截图框的宽高比例
        full: false, //false按原比例裁切图片，不失真
        fixedBox: false, //固定截图框大小，不允许改变
        canMove: true, //上传图片是否可以移动
        canMoveBox: true, //截图框能否拖动
        original: false, //上传图片按照原始比例渲染
        centerBox: true, //截图框是否被限制在图片里面
        height: false, //是否按照设备的dpr 输出等比例图片
        infoTrue: false, //true为展示真实输出图片宽高，false展示看到的截图框宽高
        maxImgSize: 3000, //限制图片最大宽度和高度
        enlarge: 1, //图片根据截图框输出比例倍数
        mode: '300px 300px', //图片默认渲染方式
      },
    };
  },
  methods: {
    //初始化函数
    imgLoad(msg) {
      console.log('工具初始化函数=====' + msg);
    },
    //图片缩放
    changeScale(num) {
      num = num || 1;
      this.$refs.cropper.changeScale(num);
    },
    //向左旋转
    rotateLeft() {
      this.$refs.cropper.rotateLeft();
    },
    //向右旋转
    rotateRight() {
      this.$refs.cropper.rotateRight();
    },
    // //实时预览函数
    realTime(data) {
      let that = this;
      that.previews = data;
      this.$refs.cropper.getCropBlob((data) => {
        this.blobToDataURI(data, function (res) {
          that.previewImg = res;
        });
      });
    },
    blobToDataURI(blob, callback) {
      var reader = new FileReader();
      reader.readAsDataURL(blob);
      reader.onload = function (e) {
        callback(e.target.result);
      };
    },
    //选择图片
    selectImg(e) {
      let file = e.target.files[0];
      if (!/\.(jpg|jpeg|png|JPG|PNG)$/.test(e.target.value)) {
        this.$message({
          message: '图片类型要求：jpeg、jpg、png',
          type: 'error',
        });
        return false;
      }
      //转化为blob
      let reader = new FileReader();
      reader.onload = (e) => {
        let data;
        if (typeof e.target.result === 'object') {
          data = window.URL.createObjectURL(new Blob([e.target.result]));
        } else {
          data = e.target.result;
        }
        this.option.img = data;
      };
      //转化为base64
      reader.readAsDataURL(file);
    },

    base64ImgtoFile(dataurl, filename = 'file') {
      //将base64格式分割：['data:image/png;base64','XXXX']
      const arr = dataurl.split(',');
      // .*？ 表示匹配任意字符到下一个符合条件的字符 刚好匹配到：
      // image/png
      const mime = arr[0].match(/:(.*?);/)[1]; //image/png
      //[image,png] 获取图片类型后缀
      const suffix = mime.split('/')[1]; //png
      const bstr = atob(arr[1]); //atob() 方法用于解码使用 base-64 编码的字符串
      let n = bstr.length;
      const u8arr = new Uint8Array(n);
      while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
      }
      return new File([u8arr], `${filename}.${suffix}`, {
        type: mime,
      });
    },

    uploadFile(file) {
      const formData = new FormData();
      formData.append('file', file);
      fileUpload(formData).then((res) => {
        if (res.status == 200) {
          this.$emit('uploadImgSuccess', res.data);
        } else {
          this.$message({
            message: '上传失败',
            type: 'error',
            duration: 1000,
          });
        }
      });
    },
    //上传图片
    uploadImg() {
      this.$refs.cropper.getCropData((data) => {
        this.resImg = this.base64ImgtoFile(data);
        this.uploadFile(this.resImg);
      });
    },
  },
};
</script>

<style scoped lang="scss">
.btn {
  outline: none;
  display: inline-block;
  line-height: 1;
  white-space: nowrap;
  cursor: pointer;
  -webkit-appearance: none;
  text-align: center;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  outline: 0;
  -webkit-transition: 0.1s;
  transition: 0.1s;
  font-weight: 500;
  padding: 8px 15px;
  font-size: 12px;
  border-radius: 3px;
  color: #fff;
  background-color: #409eff;
  border-color: #409eff;
  margin-right: 10px;
}
.cropper-content {
  display: flex;
  display: -webkit-flex;
  justify-content: flex-end;
  .cropper-box {
    flex: 1;
    width: 100%;
    .cropper {
      width: auto;
      height: 300px;
    }
  }

  .show-preview {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    .preview {
      overflow: hidden;
      height: 200px;
      width: 200px;
      background: #cccccc;
      transform: scale(0.8);
      border-radius: 50%;
    }
  }
}
.footer-btn {
  margin-top: 30px;
  display: flex;
  display: -webkit-flex;
  justify-content: space-around;
  .scope-btn {
    display: flex;
    display: -webkit-flex;
    justify-content: space-between;
    padding-right: 10px;
  }
  .upload-btn {
    flex: 1;
    -webkit-flex: 1;
    display: flex;
    display: -webkit-flex;
    justify-content: center;
  }
}
</style>
