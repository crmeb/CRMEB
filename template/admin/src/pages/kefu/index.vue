<template>
  <div class="wrapper-box">
    <div class="page-account kf">
      <div class="content">
        <img :src="copyrightImg" alt="" />
        <div class="desc">
          <p class="tit">让客户服务如此简单</p>
          <p class="kefu">专业客服系统<br />助力企业打造一流的服务体验</p>
        </div>
      </div>
      <div class="container" :class="[fullWidth > 768 ? 'containerSamll' : 'containerBig']">
        <div class="index_from page-account-container">
          <div :style="{ display: !loginType ? 'block' : 'none' }">
            <div class="page-account-top">
              <div class="page-account-top-logo">客服登录</div>
            </div>
            <Form ref="formInline" :model="formInline" :rules="ruleInline" @keyup.enter="handleSubmit('formInline')">
              <FormItem prop="username">
                <Input type="text" v-model="formInline.username" placeholder="请输入用户名" size="large" />
              </FormItem>
              <FormItem prop="password">
                <Input type="password" v-model="formInline.password" placeholder="请输入密码" size="large" />
              </FormItem>
              <FormItem>
                <Button type="primary" long size="large" @click="handleSubmit('formInline')" class="btn">登录 </Button>
              </FormItem>
            </Form>
            <div class="qh_box" v-if="!isMobile" @click="bindScan"><span class="iconfont iconerweima2"></span></div>
          </div>
          <div :style="{ display: loginType ? 'block' : 'none' }">
            <div class="page-account-top">
              <div class="page-account-top-logo">微信扫码登录</div>
            </div>
            <div class="code-box">
              <div class="qrcode" ref="qrCodeUrl"></div>
              <div class="rxpired-box" v-show="rxpired">
                <p>已过期</p>
                <Button type="primary" @click="bindRefresh">点击刷新</Button>
              </div>
            </div>
            <div class="qh_box" @click="loginType = 0"><span class="iconfont iconzhanghaomima"></span></div>
          </div>
        </div>
      </div>
      <!--            <Modal v-model="modals" scrollable footer-hide closable title="请完成安全校验" :mask-closable="false" :z-index="2"-->
      <!--                   width="342">-->
      <!--                <div class="captchaBox">-->
      <!--                    <div id="captcha" style="position: relative" ref="captcha"></div>-->
      <!--                    <div id="msg"></div>-->
      <!--                </div>-->
      <!--            </Modal>-->
    </div>
    <div class="foot-box" v-if="copyright">{{ copyright }}</div>
    <div class="foot-box" v-else>
      Copyright © 2014-2022 <a href="https://www.crmeb.com" target="_blank">{{ version }}</a>
    </div>
  </div>
</template>
<script>
import { AccountLogin, loginInfoApi, getSanCodeKey, scanStatus, kefuConfig } from '@/api/kefu';
import mixins from '../account/mixins';
import Setting from '@/setting';
import util from '@/libs/util';
import QRCode from 'qrcodejs2';
import { getCookies, removeCookies, setCookies } from '@/libs/util';
export default {
  mixins: [mixins],
  data() {
    return {
      fullWidth: document.documentElement.clientWidth,
      swiperOption: {
        pagination: '.swiper-pagination',
        autoplay: true,
      },
      modals: false,
      autoLogin: true,
      imgcode: '',
      formInline: {
        username: '',
        password: '',
        code: '',
      },
      ruleInline: {
        username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
        password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
        code: [{ required: true, message: '请输入验证码', trigger: 'blur' }],
      },
      errorNum: 0,
      jigsaw: null,
      login_logo: '',
      swiperList: [],
      defaultSwiperList: require('@/assets/images/sw.jpg'),
      loginType: 0, // 0 账号 1 扫码
      codeKey: '',
      scanTime: '',
      rxpired: false, // 扫码是否过期
      isMobile: false,
      version: '', //版本号
      isScan: false,
      timeNum: 0,
      copyright: '',
      copyrightImg: require('@/assets/images/logo-dark.png'),
    };
  },
  created() {
    kefuConfig().then((res) => {
      this.version = res.data.version;
      this.copyright = res.data.copyright;
      if (res.data.site_name) {
        document.title = res.data.site_name;
      }
      if (res.data.copyrightImg) {
        this.copyrightImg = res.data.copyrightImg;
      }
    });
    this.isMobile = this.$store.state.media.isMobile;
    var _this = this;
    top != window && (top.location.href = location.href);
    document.onkeydown = function (e) {
      if (_this.$route.name === 'login') {
        let key = window.event.keyCode;
        if (key === 13) {
          _this.handleSubmit('formInline');
        }
      }
    };
    window.addEventListener('resize', this.handleResize);
  },
  watch: {
    fullWidth(val) {
      // 为了避免频繁触发resize函数导致页面卡顿，使用定时器
      if (!this.timer) {
        // 一旦监听到的screenWidth值改变，就将其重新赋给data里的screenWidth
        this.screenWidth = val;
        this.timer = true;
        let that = this;
        setTimeout(function () {
          // 打印screenWidth变化的值
          that.timer = false;
        }, 400);
      }
    },
    $route(n) {
      this.captchas();
    },
  },
  mounted: function () {
    this.$nextTick(() => {});

    this.captchas();
  },
  methods: {
    // 切换扫码
    bindScan() {
      if (!this.isScan) {
        this.isScan = true;
        this.getSanCodeKey();
      }
      this.loginType = 1;
    },
    // 生成二维码
    creatQrCode() {
      let url = `${window.location.protocol}//${window.location.host}/pages/users/scan_login/index?key=${this.codeKey}`;
      var qrcode = new QRCode(this.$refs.qrCodeUrl, {
        text: url, // 需要转换为二维码的内容
        width: 160,
        height: 160,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H,
      });
    },
    // 关闭模态框
    closeModel() {
      let msg = this.$Message.loading({
        content: '登录中...',
        duration: 0,
      });
      AccountLogin({
        account: this.formInline.username,
        password: this.formInline.password,
        imgcode: this.formInline.code,
      })
        .then(async (res) => {
          msg();
          let expires = this.getExpiresTime(res.data.exp_time);
          // 记录用户登陆信息
          setCookies('kefu_uuid', res.data.kefuInfo.uid, expires);
          setCookies('kefu_token', res.data.token, expires);
          setCookies('kefu_expires_time', res.data.exp_time, expires);
          setCookies('kefuInfo', res.data.kefuInfo, expires);

          // 记录用户信息
          this.$store.commit('kefu/setInfo', res.data.kefuInfo);

          if (this.$store.state.media.isMobile) {
            //手机页面
            return this.$router.replace({ path: this.$route.query.redirect || '/kefu/mobile_list' });
          } else {
            // pc页面
            return this.$router.replace({ path: this.$route.query.redirect || '/kefu/pc_list' });
          }
        })
        .catch((res) => {
          msg();
          let data = res === undefined ? {} : res;
          this.errorNum++;
          this.captchas();
          this.$Message.error(data.msg || '登录失败');
          if (this.jigsaw) this.jigsaw.reset();
        });
    },
    getExpiresTime(expiresTime) {
      let nowTimeNum = Math.round(new Date() / 1000);
      let expiresTimeNum = expiresTime - nowTimeNum;
      return parseFloat(parseFloat(parseFloat(expiresTimeNum / 60) / 60) / 24);
    },
    closefail() {
      if (this.jigsaw) this.jigsaw.reset();
      this.$Message.error('校验错误');
    },
    handleResize(event) {
      this.fullWidth = document.documentElement.clientWidth;
    },
    captchas: function () {
      this.imgcode = Setting.apiBaseURL + '/captcha_pro?' + Date.parse(new Date());
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.closeModel();
        }
      });
    },
    // 获取客服扫码key
    getSanCodeKey() {
      getSanCodeKey()
        .then((res) => {
          this.codeKey = res.data.key;
          this.creatQrCode();
          this.scanTime = setInterval(() => {
            this.timeNum++;
            if (this.timeNum >= 60) {
              this.timeNum = 0;
              window.clearInterval(this.scanTime);
              this.rxpired = true;
            } else {
              this.getScanStatus();
            }
          }, 1000);
        })
        .catch((error) => {
          this.timeNum = 0;
          window.clearInterval(this.scanTime);
          this.rxpired = true;
          this.$Message.error(error.msg);
        });
    },
    // 扫码登录情况
    getScanStatus() {
      scanStatus(this.codeKey)
        .then(async (res) => {
          // 0 = 二维码过期需要重新获取授权凭证
          if (res.data.status == 0) {
            this.timeNum = 0;
            window.clearInterval(this.scanTime);
            this.rxpired = true;
          }
          // 1=正在扫描
          if (res.data.status == 1) {
          }
          // 3 扫描成功正在登录
          if (res.data.status == 3) {
            window.clearInterval(this.scanTime);
            let expires = this.getExpiresTime(res.data.exp_time);
            // 记录用户登陆信息
            setCookies('kefu_uuid', res.data.kefuInfo.uid, expires);
            setCookies('kefu_token', res.data.token, expires);
            setCookies('kefu_expires_time', res.data.exp_time, expires);
            setCookies('kefuInfo', res.data.kefuInfo, expires);
            // 记录用户信息
            this.$store.commit('kefu/setInfo', res.data.kefuInfo);
            if (this.$store.state.media.isMobile) {
              //手机页面
              return this.$router.replace({ path: this.$route.query.redirect || '/kefu/mobile_list' });
            } else {
              // pc页面
              return this.$router.replace({ path: this.$route.query.redirect || '/kefu/pc_list' });
            }
          }
        })
        .catch((error) => {
          this.$Modal.error({
            title: '提示',
            content: error.msg,
          });
          this.timeNum = 0;
          window.clearInterval(this.scanTime);
          this.rxpired = true;
        });
    },
    // 刷新二维码
    bindRefresh() {
      this.$refs.qrCodeUrl.innerHTML = '';
      this.rxpired = false;
      this.getSanCodeKey();
    },
  },
  beforeCreate() {},
  beforeDestroy: function () {
    this.timeNum = 0;
    this.$refs.qrCodeUrl.innerHTML = '';
    window.clearInterval(this.scanTime);
    window.removeEventListener('resize', this.handleResize);
    // document.getElementsByTagName('canvas')[0].removeAttribute('class', 'index_bg');
  },
};
</script>
<style scoped lang="stylus">
.page-account {
  display: flex;
  width: 100%;
  background-image: url('~@/assets/images/kfbg_2.jpg');
  background-size: cover;
  background-position: center;
  justify-content: center;
  align-items: center;
  height: 100vh;
  overflow: auto;

  .content {
    height: 400px;
    margin-right: 100px;

    .desc {
      color: #fff;

      .tit {
        font-size: 40px;
        font-weight: 600;
      }

      .kefu {
        margin-top: 30px;
        font-weight: 500;
        font-size: 20px;
      }
    }

    img {
      width: 360px;
      margin-left: -100px;
    }
  }
}

.code-box {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;

  .qrcode {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 180px;
    height: 180px;
    border: 1px solid #E5E5E6;
  }

  .rxpired-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 160px;
    height: 160px;
    background: rgba(0, 0, 0, 0.6);

    p {
      margin-bottom: 10px;
      font-size: 15px;
      color: #fff;
    }
  }
}

.page-account-top-logo {
  color: #000000;
  font-size: 21px;
}

.wrapper-box {
  display: flex;
  flex-direction: column;
  height: 100vh;

  .foot-box {
    padding: 20px 20px;
    font-size: 14px;
    color: #666666;
    text-align: right;
    box-sizing: border-box;

    a {
      margin-left: 0;
      color: #666666;
    }
  }
}

.page-account {
  display: flex;
  flex: 1;
}

.page-account .code {
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-account .code .pictrue {
  height: 40px;
}

.swiperPross {
  border-radius: 6px 0px 0px 6px;
}

.swiperPross, .swiperPic, .swiperPic img {
  width: 510px;
  height: 100%;
}

.swiperPic img {
  width: 100%;
  height: 100%;
}

.container {
  height: 400px !important;
  padding: 0 !important;
  /* overflow: hidden; */
  border-radius: 6px;
  z-index: 1;
  display: flex;
}

.containerSamll {
  width: 384px !important;
  // margin-left: 30%;
  background: #fff !important;
}

.containerBig {
  width: 90%;
  padding-bottom: 20px;
  margin-top: 84px;
  background: #f7f7f7 !important;
  height: auto !important;
  box-shadow: 0px 3px 20px rgba(0, 20, 41, 0.06);
}

.index_from {
  position: relative;
  padding: 40px 40px 32px 40px;
  height: 400px;
  width: 100%;
  box-sizing: border-box;
}

.containerBig .index_from {
  padding: 20px;
  height: auto !important;
}

.index_from .qh_box {
  position: absolute;
  right: 12px;
  top: 0;
  cursor: pointer;

  .iconfont {
    color: #265BED;
    font-size: 36px;
  }
}

.page-account-top {
  padding: 20px 0 50px !important;
  box-sizing: border-box !important;
  display: flex;
  justify-content: center;
}

.page-account-container {
  border-radius: 0px 6px 6px 0px;
}

.btn {
  background: #265BED;
}

.captchaBox {
  width: 310px;
}

input {
  display: block;
  width: 290px;
  line-height: 40px;
  margin: 10px 0;
  padding: 0 10px;
  outline: none;
  border: 1px solid #c8cccf;
  border-radius: 4px;
  color: #6a6f77;
}

#msg {
  width: 100%;
  line-height: 40px;
  font-size: 14px;
  text-align: center;
}

a:link, a:visited, a:hover, a:active {
  margin-left: 100px;
  color: #0366D6;
}

.index_from >>> .ivu-input-large {
  font-size: 14px !important;
}
</style>
<style>
@media screen and (min-width: 320px) and (max-width: 960px) {
  .page-account {
    background-image: url('~@/assets/images/m_bg.png') !important;
    background-size: 100% auto !important;
    background-repeat: no-repeat;
    background-position: left top !important;
    display: flex;
  }

  .content {
    display: none;
  }
  .index_from {
    box-shadow: 0px 3px 20px rgba(0, 20, 41, 0.06);
    background: #fff;
  }
  .wrapper-box .foot-box {
    padding: 20px 66px !important;
    color: #adadad !important;
    font-size: 0.22rem !important;
  }
  .containerBig {
    width: 86% !important;
    border-radius: 0.2rem !important;
    overflow: hidden;
  }
  .btn {
    background: linear-gradient(90deg, #3875ea 0%, #1890fc 100%) !important;

    border-radius: 0.41rem;
  }
  .ivu-input {
    border: 1px solid #dcdee2;
    -webkit-appearance: none; /*去除阴影边框*/
    outline: none;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0); /*点击高亮的颜色*/
  }
}
</style>
