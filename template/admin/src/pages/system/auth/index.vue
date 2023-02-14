<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="auth acea-row row-between-wrapper">
        <div class="acea-row row-middle">
          <Icon type="ios-bulb-outline" class="iconIos blue" />
          <div class="text" v-if="status === 1">
            <div>商业授权</div>
            <div class="code">授权码：{{ authCode }}</div>
          </div>
          <div class="text" v-else>
            <div>商业授权</div>
            <div class="code">未授权</div>
          </div>
        </div>
        <!-- <Button class="grey" @click="toCrmeb()" v-if="status === 1">进入官网</Button> -->
        <div>
          <Button type="primary" @click="toCrmeb()" v-if="status === 1">进入官网</Button>
          <Button type="primary" @click="payment('bz')" v-if="status !== 1">购买授权</Button>
        </div>
      </div>
    </Card>
    <Card :bordered="false" dis-hover class="ivu-mt" v-if="!copyright && status == 1">
      <!-- v-if="copyright == '0' && status == 1" -->
      <div class="auth acea-row row-between-wrapper">
        <div class="acea-row row-middle">
          <span class="iconfont iconbanquan iconIos blue"></span>
          <div class="text">
            <div>去版权服务</div>
            <div class="code">购买之后可以设置</div>
            <div class="pro_price" v-if="productStatus">￥{{ price }}</div>
          </div>
        </div>
        <Button type="primary" @click="payment('copyright')">立即购买</Button>
      </div>
    </Card>
    <Card :bordered="false" dis-hover class="ivu-mt" v-if="copyright">
      <div class="auth acea-row row-between-wrapper">
        <div class="acea-row row-middle">
          <span class="iconfont iconbanquan iconIos blue"></span>
          <div class="acea-row row-middle">
            <span class="update">修改授权信息:</span>
            <Input style="width: 460px" v-model="copyrightText" />
          </div>
        </div>
        <Button type="primary" @click="saveCopyRight">保存</Button>
      </div>
      <div class="authorized">
        <div>
          <span class="update">上传授权图片:</span>
        </div>
        <div class="uploadPictrue" v-if="authorizedPicture" @click="modalPicTap('单选')">
          <img v-lazy="authorizedPicture" />
        </div>
        <div class="upload" v-else @click="modalPicTap('单选')">
          <div class="iconfont">+</div>
        </div>
      </div>
      <span class="prompt">建议尺寸：宽290px*高100px</span>
    </Card>

    <Modal
      v-model="isTemplate"
      scrollable
      footer-hide
      closable
      title="商业授权"
      :z-index="1"
      width="447"
      @on-cancel="cancel"
    >
      <iframe width="100%" height="580" :src="iframeUrl" frameborder="0"></iframe>
    </Modal>
    <Modal
      v-model="modalPic"
      width="960px"
      scrollable
      footer-hide
      closable
      title="上传授权图片"
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
</template>
<script>
import uploadPictures from '@/components/uploadPictures';
import { auth, getVersion, crmebProduct, saveCrmebCopyRight, getCrmebCopyRight } from '@/api/system';
import { mapState } from 'vuex';
import { formatDate } from '@/utils/validate';
import QRCode from 'qrcodejs2';
// import Vcode from 'vue-puzzle-vcode';

export default {
  name: 'system_auth',
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    ...mapState('admin/userLevel', ['categoryId']),
    labelWidth() {
      return this.isMobile ? undefined : 85;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },

  data() {
    return {
      baseUrl: 'https://shop.crmeb.net/html/index.html',
      iframeUrl: '',
      captchs: 'http://authorize.crmeb.net/api/captchs/',
      authCode: '',
      status: 1,
      dayNum: 0,
      copyright: '',
      isTemplate: false,
      price: '',
      proPrice: '',
      productStatus: false,
      copyrightText: '',
      success: false,
      payType: '',
      disabled: false,
      isShow: false, // 验证码模态框是否出现
      active: 0,
      timer: null,
      version: '',
      label: '',
      productType: '',
      modalPic: false,
      isChoice: '单选',
      authorizedPicture: '', // 版权图片
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
    };
  },
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  components: {
    uploadPictures,
  },
  mounted() {
    this.getAuth();
    this.getVersion();
    window.addEventListener('message', (e) => {
      if (e.data.event === 'onCancel') {
        this.cancel();
      }
    });
  },
  methods: {
    getVersion() {
      getVersion().then((res) => {
        this.version = res.data.version;
        this.label = res.data.label;
      });
    },
    getCrmebCopyRight() {
      getCrmebCopyRight().then((res) => {
        this.getAuth();
        return this.$Message.success(res.msg);
      });
    },
    //保存版权信息
    saveCopyRight() {
      saveCrmebCopyRight({
        copyright: this.copyrightText,
        copyright_img: this.authorizedPicture,
      }).then((res) => {
        return this.$Message.success(res.msg);
      });
    },
    // 选择图片
    modalPicTap() {
      this.modalPic = true;
    },
    // 选中图片
    getPic(pc) {
      this.authorizedPicture = pc.att_dir;
      this.modalPic = false;
    },
    //获取版权信息
    getCopyRight() {
      getCrmebCopyRight().then((res) => {
        this.copyrightText = res.data.copyrightContext || '';
        this.authorizedPicture = res.data.copyrightImage || '';
      });
    },
    cancel() {
      if (this.productType === 'copyright') {
        this.getCrmebCopyRight();
      } else {
        this.getAuth();
      }
      this.iframeUrl = '';
      this.isTemplate = false;
    },
    loginTabSwitch(index) {
      this.active = index;
    },
    getAuth() {
      auth()
        .then((res) => {
          let data = res.data || {};
          this.authCode = data.authCode || '';
          this.status = data.status === undefined ? -1 : data.status;
          this.dayNum = data.day || 0;
          this.copyright = data.copyright;
          if (this.copyright) {
            this.getCopyRight();
          }
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    toCrmeb() {
      window.open('http://www.crmeb.com');
    },
    getProduct() {
      crmebProduct({ type: 'copyright' })
        .then((res) => {
          this.price = res.data.attr.price;
          this.productStatus = true;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
      crmebProduct({ type: 'pro' })
        .then((res) => {
          this.proPrice = res.data.attr.price;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    payment(product) {
      this.productType = product;
      let host = location.host;
      let hostData = host.split('.');
      if (hostData[0] === 'test' && hostData.length === 4) {
        host = host.replace('test.', '');
      } else if (hostData[0] === 'www' && hostData.length === 3) {
        host = host.replace('www.', '');
      }
      this.iframeUrl =
        this.baseUrl + '?url=' + host + '&product=' + product + '&version=' + this.version + '&label=' + this.label;
      this.isTemplate = true;
    },
    // 用户点击遮罩层，应该关闭模态框
    onClose() {
      this.isShow = false;
    },
  },
  destroyed() {},
};
</script>
<style scoped lang="stylus">
.auth {
  padding: 9px 16px 9px 10px;
}

.auth .iconIos {
  font-size: 40px;
  margin-right: 10px;
  color: #001529;
}

.auth .text {
  font-weight: 400;
  color: rgba(0, 0, 0, 1);
  font-size: 18px;
}

.auth .text .code {
  font-size: 14px;
  color: rgba(0, 0, 0, 0.5);
}

.auth .text .pro_price {
  height: 18px;
  font-size: 14px;
  font-family: PingFangSC-Semibold, PingFang SC;
  font-weight: 600;
  color: #F5222D;
  line-height: 18px;
}

.auth .blue {
  color: #1890FF !important;
}

.auth .red {
  color: #ED4014 !important;
}

.authorized {
  display: flex;
  margin-left: 18px;
  margin-bottom: 14px;

  .upload {
    width: 60px;
    height: 60px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 4px;
    border: 1px solid #DDDDDD;
  }
}

.upload .iconfont {
  text-align: center;
  line-height: 60px;
}

.uploadPictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-left: 2px;
}

.uploadPictrue img {
  width: 100%;
  height: 100%;
}

.phone_code {
  border: 1px solid #eee;
  padding: 0 10px 0;
  cursor: pointer;
}

.grey {
  background-color: #999999;
  border-color: #999999;
  color: #fff;
}

.update {
  font-size: 13px;
  color: rgba(0, 0, 0, 0.85);
  padding-right: 12px;
}

.prompt {
  margin-left: 114px;
  font-size: 12px;
  font-weight: 400;
  color: #999999;
}

.submit {
  width: 100%;
}

.code .input {
  width: 83%;
}

.code .input .ivu-input {
  border-radius: 4px 0 0 4px !important;
}

.code .pictrue {
  height: 32px;
  width: 17%;
}

.customer {
  border-right: 0;
}

.customer a {
  font-size: 12px;
}

.ivu-input-group-prepend, .ivu-input-group-append {
  background-color: #fff;
}

.ivu-input-group .ivu-input {
  border-right: 0 !important;
}

.qrcode {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 180px;
  height: 180px;
  border: 1px solid #E5E5E6;
}

.qrcode_desc {
  display: inline-block;
  text-align: center;
  margin: 10px 0 10px;
  width: 180px;
  font-size: 12px;
  color: #666;
  line-height: 16px;
}

.login_tab {
  font-size: 16px;
  margin: 0 0 20px;
  justify-content: center;
}

.login_tab_item {
  width: 50%;
  text-align: center;
  padding-bottom: 15px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
}

.active_tab {
  border-bottom: 2px solid #1495ED;
  color: #1495ED;
  font-weight: 600;
}
</style>
