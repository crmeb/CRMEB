<template>
  <div class="box">
    <div class="c_row-item" v-if="this.$route.query.type !== 2">
      <Col class="label" span="4"> 模板名称 </Col>
      <Col span="19" class="slider-box">
        <Input v-model="name" placeholder="选填不超过15个字" maxlength="15" @on-change="changName" />
      </Col>
    </div>
    <div class="c_row-item">
      <Col class="label" span="4"> 页面标题 </Col>
      <Col span="19" class="slider-box">
        <Input v-model="value" placeholder="选填不超过30个字" maxlength="30" @on-change="changVal" />
      </Col>
    </div>
    <div class="c_row-item">
      <Col class="label" span="4"> 页面状态 </Col>
      <Col span="19" class="slider-box">
        <i-switch v-model="isShow" @on-change="changeState" />
      </Col>
    </div>
    <div class="c_row-item acea-row row-top">
      <Col class="label" span="4"> 背景设置 </Col>
      <Col span="19" class="slider-box">
        <div class="acea-row row-between row-top color">
          <Checkbox v-model="bgColor" @on-change="bgColorTap">背景色</Checkbox>
          <ColorPicker v-model="colorPicker" @on-change="colorPickerTap(colorPicker)" />
        </div>
        <div class="acea-row row-between row-top color">
          <Checkbox v-model="bgPic" @on-change="bgPicTap">背景图</Checkbox>
          <RadioGroup v-model="tabVal" type="button" @on-change="radioTap">
            <Radio :label="index" v-for="(item, index) in picList" :key="index">
              <span class="iconfont-diy" :class="item"></span>
            </Radio>
          </RadioGroup>
        </div>
        <div v-if="bgPic">
          <div class="title">建议尺寸：690 * 240px</div>
          <div class="boxs" @click="modalPicTap('单选')">
            <img :src="bgPicUrl" alt="" v-if="bgPicUrl" />
            <div class="upload-box" v-else>
              <Icon type="ios-camera-outline" size="36" />
            </div>
            <div class="replace" v-if="bgPicUrl">更换图片</div>
            <!--<span class="iconfont-diy icondel_1" @click.stop="bindDelete" v-if="bgPicUrl"></span>-->
          </div>
        </div>
      </Col>
    </div>
    <div>
      <Modal
        v-model="modalPic"
        width="950px"
        scrollable
        footer-hide
        closable
        title="上传背景图"
        :mask-closable="false"
        :z-index="1"
      >
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </Modal>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import uploadPictures from '@/components/uploadPictures';
export default {
  name: 'pageTitle',
  components: {
    uploadPictures,
  },
  data() {
    return {
      value: '',
      name: '',
      isShow: true,
      picList: ['icondantu', 'iconpingpu', 'iconlashen'],
      bgColor: false,
      bgPic: false,
      tabVal: 0,
      colorPicker: '#f5f5f5',
      modalPic: false,
      isChoice: '单选',
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
      bgPicUrl: '',
    };
  },
  created() {
    let state = this.$store.state.mobildConfig;
    this.value = state.pageTitle;
    this.name = state.pageName;
    this.isShow = state.pageShow ? true : false;
    this.bgColor = state.pageColor ? true : false;
    this.bgPic = state.pagePic ? true : false;
    this.colorPicker = state.pageColorPicker;
    this.tabVal = state.pageTabVal;
    this.bgPicUrl = state.pagePicUrl;
  },
  methods: {
    // 点击图文封面
    modalPicTap(title) {
      this.modalPic = true;
    },
    bindDelete() {
      this.bgPicUrl = '';
    },
    getPic(pc) {
      this.$nextTick(() => {
        this.bgPicUrl = pc.att_dir;
        this.modalPic = false;
        this.$store.commit('mobildConfig/UPPICURL', pc.att_dir);
      });
    },
    colorPickerTap(colorPicker) {
      this.$store.commit('mobildConfig/UPPICKER', colorPicker);
    },
    radioTap(val) {
      this.$store.commit('mobildConfig/UPRADIO', val);
    },
    changVal(val) {
      this.$store.commit('mobildConfig/UPTITLE', val.target.value);
    },
    changName(val) {
      this.$store.commit('mobildConfig/UPNAME', val.target.value);
    },
    changeState(val) {
      this.$store.commit('mobildConfig/UPSHOW', val);
    },
    bgColorTap(val) {
      this.$store.commit('mobildConfig/UPCOLOR', val);
    },
    bgPicTap(val) {
      this.$store.commit('mobildConfig/UPPIC', val);
    },
  },
};
</script>

<style scoped lang="stylus">
.upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  background: #ccc;
}

/deep/.ivu-input {
  font-size: 13px !important;
}

.slider-box .title {
  color: #999999;
  font-size: 13px;
  margin-bottom: 5px;
}

.c_row-item {
  padding: 0 15px;
  margin-top: 22px;
}

.slider-box .color {
  margin-bottom: 15px;
}

.boxs {
  width: 60px;
  height: 60px;
  margin-bottom: 10px;
  position: relative;

  .replace {
    background: rgba(0, 0, 0, 0.4);
    border-radius: 0 0 6px 6px;
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    color: #fff;
    font-size: 12px;
    text-align: center;
    height: 24px;
    line-height: 24px;
  }

  .iconfont-diy {
    position: absolute;
    top: -15px;
    right: -8px;
    font-size: 25px;
    color: #999;
  }

  img {
    width: 100%;
    height: 100%;
    border-radius: 6px;
  }
}

.ivu-color-picker /deep/ .ivu-select-dropdown {
  position: absolute;
  // width: 300px !important;
  left: 34px !important;
}
</style>
