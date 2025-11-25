<template>
  <div class="onePictrue">
    <div class="info">建议：请先选择图片，图片宽度750px，高度不限</div>
    <div class="pictrues">
      <img :src="configData.url" v-if="configData.url" />
      <div class="emptyBox" v-else>750*高度不限</div>
    </div>
    <div class="uploadImg">
      <div class="name">图片</div>
      <div class="picTxt">
        <div class="box" @click="modalPicTap('单选')">
          <div class="pictrue acea-row row-center-wrapper" v-if="configData.url">
            <img :src="configData.url" alt="" />
            <div class="iconfont icondel_1" @click.stop="bindDelete"></div>
          </div>
          <div class="upload-box" v-else><i class="el-icon-plus"></i></div>
        </div>
        <div class="tip">{{ configData.info }}</div>
      </div>
    </div>
    <div class="bnt" @click="openFloorModal">+ 编辑热区</div>
    <div>
      <el-dialog
        :visible.sync="modalPic"
        width="960px"
        :title="'上传图片'"
      >
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </el-dialog>
      <OperationFloorModal
        ref="hotpot"
        :imgs="configData.url"
        :img-area-data="imgAreaData"
        @delAreaData="handleAreaData"
        @saveAreaData="handleAreaData"
      ></OperationFloorModal>
    </div>
  </div>
</template>

<script>
import uploadPictures from '@/components/uploadPictures';
import OperationFloorModal from '@/components/hotpotModal';
export default {
  name: 'c_one_pictrue',
  components: {
    uploadPictures,
    OperationFloorModal,
  },
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  data() {
    return {
      defaults: {},
      configData: {},
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      modalPic: false,
      isChoice: '单选',
      imgAreaData: [], //热区数据
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal.picStyle;
        this.$set(this, 'imgAreaData', nVal.picStyle.list);
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
      this.imgAreaData = this.configObj.picStyle.list;
    });
  },
  methods: {
    bindDelete() {
      this.configData.url = '';
    },
    // 点击图文封面
    modalPicTap(title) {
      this.modalPic = true;
    },
    // 获取图片信息
    getPic(pc) {
      this.$nextTick(() => {
        this.configData.url = pc.att_dir;
        this.modalPic = false;
      });
    },
    openFloorModal() {
      // 如果配置数据中有url，则显示热点图对话框
      if (this.configData.url) this.$refs.hotpot.dialogVisible = true;
    },
    /**
     * 处理区域数据
     * @param {Object} areaData - 区域数据对象
     */
    handleAreaData(areaData) {
      // 打印保存的数据
      this.configData.list = areaData;
      console.log('保存的数据', areaData);
    },
  },
};
</script>

<style scoped lang="scss">
.onePictrue {
  padding: 0 15px;

  .info {
    font-size: 12px;
    color: #bbbbbb;
  }

  .bnt {
    width: 100%;
    height: 36px;
    border-radius: 3px;
    opacity: 1;
    border: 1px solid #eeeeee;
    color: #666666;
    font-size: 12px;
    text-align: center;
    line-height: 36px;
    margin-top: 20px;
    cursor: pointer;
  }

  .pictrues {
    width: 370px;
    height: 100%;
    margin-top: 20px;

    img {
      width: 100%;
      height: 100%;
    }

    .emptyBox {
      height: 164px;
      background: #f9f9f9;
      border-radius: 3px 3px 3px 3px;
      font-size: 12px;
      color: #bbbbbb;
      text-align: center;
      line-height: 164px;
    }
  }

  .uploadImg {
    display: flex;
    align-items: center;
    height: 96px;
    background: #f9f9f9;
    border-radius: 3px;
    width: 100%;
    margin-top: 20px;
    padding: 0 20px;

    .name {
      font-size: 12px;
      color: #999999;
      margin-right: 16px;
    }

    .picTxt {
      display: flex;
      align-items: center;

      .box {
        width: 64px;
        height: 64px;
        position: relative;
        background: url('../../assets/images/transparents.jpg') no-repeat;
        background-size: 100% 100%;
        border-radius: 3px;

        .upload-box {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 64px;
          height: 64px;
          background: #fff;
          border-radius: 4px;
          border: 1px solid #eeeeee;

          .ivu-icon {
            color: #ccc;
          }
        }

        .pictrue {
          position: relative;
          width: 100%;
          height: 100%;

          .iconfont {
            position: absolute;
            right: -12px;
            top: -19px;
            font-size: 24px;
            color: #cccccc;
          }
        }

        img {
          width: 64px;
          border-radius: 3px;
          max-height: 64px;
          object-fit: cover;
        }
      }

      .tip {
        color: #bbbbbb;
        font-size: 12px;
        margin-left: 20px;
      }
    }
  }
}
</style>
