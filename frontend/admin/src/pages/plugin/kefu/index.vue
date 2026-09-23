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
            <el-form ref="formInlineRef" :model="formInline" :rules="ruleInline" @keyup.enter="handleSubmit()">
              <el-form-item class="mb20" prop="username">
                <el-input type="text" v-model="formInline.username" placeholder="请输入用户名" size="large" />
              </el-form-item>
              <el-form-item class="mb20" prop="password">
                <el-input type="password" v-model="formInline.password" placeholder="请输入密码" size="large" />
              </el-form-item>
              <el-form-item>
                <el-button type="primary" size="large" v-db-click @click="handleSubmit()" class="btn">登录 </el-button>
              </el-form-item>
            </el-form>
            <div class="qh_box" v-if="!isMobile" v-db-click @click="bindScan">
              <span class="iconfont iconerweima2"></span>
            </div>
          </div>
          <div :style="{ display: loginType ? 'block' : 'none' }">
            <div class="page-account-top">
              <div class="page-account-top-logo">微信扫码登录</div>
            </div>
            <div class="code-box">
              <div class="qrcode" ref="qrCodeUrl"></div>
              <div class="rxpired-box" v-show="rxpired">
                <p>已过期</p>
                <el-button type="primary" v-db-click @click="bindRefresh">点击刷新</el-button>
              </div>
            </div>
            <div class="qh_box" v-db-click @click="loginType = 0"><span class="iconfont iconzhanghaomima"></span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="foot-box" v-if="copyright">{{ copyright }}</div>
    <div class="foot-box" v-else>
      Copyright © 2016~2026 <a href="https://www.crmeb.com" target="_blank">{{ version }}</a>
    </div>
  </div>
</template>
<script setup>
import __asset_sw_0 from '@/assets/images/sw.jpg';
import __asset_logo_dark_1 from '@/assets/images/logo-dark.png';

import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { AccountLogin, getSanCodeKey, scanStatus, kefuConfig } from '@/pages/plugin/kefu/api';
import Setting from '@/setting';
import QRCode from '@/vendor/qrcodejs2';
import { setCookies } from '@/libs/util';
import { useMediaStore } from '@/store/module/media';
import { useKefuStore } from '@/pages/plugin/kefu/store';

defineOptions({ name: 'kefu_index' });

const route = useRoute();
const router = useRouter();
const mediaStore = useMediaStore();
const kefuStore = useKefuStore();

const formInlineRef = ref(null);
const qrCodeUrl = ref(null);

const fullWidth = ref(document.documentElement.clientWidth);
const swiperOption = ref({
  pagination: '.swiper-pagination',
  autoplay: true,
});
const modals = ref(false);
const autoLogin = ref(true);
const imgcode = ref('');
const formInline = ref({
  username: '',
  password: '',
  code: '',
});
const ruleInline = {
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
  code: [{ required: true, message: '请输入验证码', trigger: 'blur' }],
};
const errorNum = ref(0);
const jigsaw = ref(null);
const login_logo = ref('');
const swiperList = ref([]);
const defaultSwiperList = ref(__asset_sw_0);
const loginType = ref(0); // 0 账号 1 扫码
const codeKey = ref('');
const scanTime = ref('');
const rxpired = ref(false); // 扫码是否过期
const isMobile = ref(false);
const version = ref(''); //版本号
const isScan = ref(false);
const timeNum = ref(0);
const copyright = ref('');
const copyrightImg = ref(__asset_logo_dark_1);

// created
kefuConfig().then((res) => {
  version.value = res.data.version;
  copyright.value = res.data.copyright;
  if (res.data.site_name) {
    document.title = res.data.site_name;
  }
  if (res.data.copyrightImg) {
    copyrightImg.value = res.data.copyrightImg;
  }
});
isMobile.value = mediaStore.isMobile;
top != window && (top.location.href = location.href);
document.onkeydown = function (e) {
  if (route.name === 'login') {
    let key = window.event.keyCode;
    if (key === 13) {
      handleSubmit();
    }
  }
};
window.addEventListener('resize', handleResize);

// 为了避免频繁触发resize函数导致页面卡顿，使用定时器（原实现中 screenWidth 从未被读取，此处仅保留节流语义）
let resizeTimer = false;
watch(fullWidth, (val) => {
  if (!resizeTimer) {
    resizeTimer = true;
    setTimeout(function () {
      resizeTimer = false;
    }, 400);
  }
});

watch(
  () => route.path,
  () => {
    captchas();
  },
);

onMounted(function () {
  captchas();
});

onBeforeUnmount(function () {
  timeNum.value = 0;
  if (qrCodeUrl.value) qrCodeUrl.value.innerHTML = '';
  window.clearInterval(scanTime.value);
  window.removeEventListener('resize', handleResize);
});

// 切换扫码
function bindScan() {
  if (!isScan.value) {
    isScan.value = true;
    getSanCodeKeyData();
  }
  loginType.value = 1;
}
// 生成二维码
function creatQrCode() {
  let url = `${window.location.protocol}//${window.location.host}/pages/users/scan_login/index?key=${codeKey.value}`;
  var qrcode = new QRCode(qrCodeUrl.value, {
    text: url, // 需要转换为二维码的内容
    width: 160,
    height: 160,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
  });
}
// 关闭模态框
function closeModel() {
  AccountLogin({
    account: formInline.value.username,
    password: formInline.value.password,
    imgcode: formInline.value.code,
  })
    .then(async (res) => {
      let expires = getExpiresTime(res.data.exp_time);
      // 记录用户登录信息
      setCookies('kefu_uuid', res.data.kefuInfo.uid, expires);
      setCookies('kefu_token', res.data.token, expires);
      setCookies('kefu_expires_time', res.data.exp_time, expires);
      // js-cookie 存对象会被 String() 成 "[object Object]"，必须显式序列化为 JSON 字符串（对话页 JSON.parse 解析）
      setCookies('kefuInfo', JSON.stringify(res.data.kefuInfo), expires);

      // 记录用户信息
      kefuStore.setInfo(res.data.kefuInfo);

      if (mediaStore.isMobile) {
        //手机页面
        return router.replace({ path: route.query.redirect || Setting.routePre + '/kefu/mobile_list' });
      } else {
        // pc页面
        return router.replace({ path: route.query.redirect || Setting.routePre + '/kefu/pc_list' });
      }
    })
    .catch((res) => {
      let data = res === undefined ? {} : res;
      errorNum.value++;
      captchas();
      ElMessage.error(data.msg || '登录失败');
      if (jigsaw.value) jigsaw.value.reset();
    });
}
function getExpiresTime(expiresTime) {
  let nowTimeNum = Math.round(new Date() / 1000);
  let expiresTimeNum = expiresTime - nowTimeNum;
  return parseFloat(parseFloat(parseFloat(expiresTimeNum / 60) / 60) / 24);
}
function closefail() {
  if (jigsaw.value) jigsaw.value.reset();
  ElMessage.error('校验错误');
}
function handleResize(event) {
  fullWidth.value = document.documentElement.clientWidth;
}
function captchas() {
  imgcode.value = Setting.apiBaseURL + '/captcha_pro?' + Date.parse(new Date());
}
function handleSubmit() {
  formInlineRef.value.validate((valid) => {
    if (valid) {
      closeModel();
    }
  });
}
// 获取客服扫码key（原方法名与导入的 api 同名，改名避免遮蔽）
function getSanCodeKeyData() {
  getSanCodeKey()
    .then((res) => {
      codeKey.value = res.data.key;
      creatQrCode();
      scanTime.value = setInterval(() => {
        timeNum.value++;
        if (timeNum.value >= 60) {
          timeNum.value = 0;
          window.clearInterval(scanTime.value);
          rxpired.value = true;
        } else {
          getScanStatus();
        }
      }, 1000);
    })
    .catch((error) => {
      timeNum.value = 0;
      window.clearInterval(scanTime.value);
      rxpired.value = true;
      ElMessage.error(error.msg);
    });
}
// 扫码登录情况
function getScanStatus() {
  scanStatus(codeKey.value)
    .then(async (res) => {
      // 0 = 二维码过期需要重新获取授权凭证
      if (res.data.status == 0) {
        timeNum.value = 0;
        window.clearInterval(scanTime.value);
        rxpired.value = true;
      }
      // 1=正在扫描
      if (res.data.status == 1) {
      }
      // 3 扫描成功正在登录
      if (res.data.status == 3) {
        window.clearInterval(scanTime.value);
        let expires = getExpiresTime(res.data.exp_time);
        // 记录用户登录信息
        setCookies('kefu_uuid', res.data.kefuInfo.uid, expires);
        setCookies('kefu_token', res.data.token, expires);
        setCookies('kefu_expires_time', res.data.exp_time, expires);
        setCookies('kefuInfo', res.data.kefuInfo, expires);
        // 记录用户信息
        kefuStore.setInfo(res.data.kefuInfo);
        if (mediaStore.isMobile) {
          //手机页面
          return router.replace({ path: route.query.redirect || Setting.routePre + '/kefu/mobile_list' });
        } else {
          // pc页面
          return router.replace({ path: route.query.redirect || Setting.routePre + '/kefu/pc_list' });
        }
      }
    })
    .catch((error) => {
      ElMessage.error(error.msg);
      timeNum.value = 0;
      window.clearInterval(scanTime.value);
      rxpired.value = true;
    });
}
// 刷新二维码
function bindRefresh() {
  qrCodeUrl.value.innerHTML = '';
  rxpired.value = false;
  getSanCodeKeyData();
}
</script>
<style lang="scss" scoped>
.page-account {
  display: flex;
  width: 100%;
  background-image: url('@/assets/images/kfbg_2.jpg');
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
    border: 1px solid #e5e5e6;
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
.swiperPross,
.swiperPic,
.swiperPic img {
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
    color: #265bed;
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
  width: 100%;
  background: #265bed;
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

a:link,
a:visited,
a:hover,
a:active {
  margin-left: 100px;
  color: #0366d6;
}
.index_from :deep(.ivu-input-large) {
  font-size: 14px !important;
}
</style>
<style>
@media screen and (min-width: 320px) and (max-width: 960px) {
  .page-account {
    background-image: url('@/assets/images/m_bg.png') !important;
    background-size: 100% auto !important;
    background-repeat: no-repeat;
    background-position: left top !important;
    display: flex;
  }
  .wrapper-box .foot-box {
    text-align: center !important;
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
