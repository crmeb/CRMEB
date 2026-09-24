<template>
  <div class="page-account">
    <div class="container" :class="[fullWidth > 768 ? 'containerSamll' : 'containerBig']">
      <swiper
        :modules="[Autoplay, Pagination]"
        :pagination="{ clickable: true }"
        :autoplay="true"
        class="swiperPross"
        v-if="fullWidth > 768"
      >
        <swiper-slide class="swiperPic" v-for="(item, index) in swiperList" :key="index">
          <img :src="item.slide" alt="" />
        </swiper-slide>
        <template #pagination><div class="swiper-pagination"></div></template>
      </swiper>
      <div class="index_from page-account-container from-wh">
        <div class="page-account-top">
          <div class="page-account-top-logo">
            <img :src="login_logo" alt="logo" style="width: 100%; height: 74px" />
          </div>
        </div>
        <el-form ref="formInlineRef" :model="formInline" :rules="ruleInline" @keyup.enter="handleSubmit()">
          <el-form-item prop="username">
            <el-input
              type="text"
              v-model="formInline.username"
              prefix="ios-contact-outline"
              placeholder="请输入用户名"
              size="large"
            />
          </el-form-item>
          <el-form-item prop="password">
            <el-input
              type="password"
              v-model="formInline.password"
              prefix="ios-lock-outline"
              placeholder="请输入密码"
              size="large"
              show-password
            />
          </el-form-item>
          <!-- <el-form-item prop="code">
            <div class="code">
              <el-input
                type="text"
                v-model="formInline.code"
                prefix="ios-keypad-outline"
                placeholder="请输入验证码"
                size="large"
              />
              <img :src="imgcode" class="pictrue" v-db-click @click="captchas" />
            </div>
          </el-form-item> -->
          <el-form-item class="pt10">
            <el-button type="primary" :loading="loading" size="large" v-db-click @click="handleSubmit()" class="btn"
              >登录</el-button
            >
          </el-form-item>
        </el-form>
      </div>
    </div>

    <Verify
      @success="success"
      captchaType="blockPuzzle"
      :imgSize="{ width: '330px', height: '155px' }"
      ref="verifyRef"
    ></Verify>
    <div class="footer">
      <div class="pull-right" v-if="copyright">{{ copyright }}</div>
      <div class="pull-right" v-else>
        Copyright © 2016~2026 <a href="https://www.crmeb.com" target="_blank">{{ version }}</a>
      </div>
    </div>
  </div>
</template>
<script setup>
import __asset_sw_0 from '@/assets/images/sw.png';
import __asset_logo_1 from '@/assets/images/logo.png';

import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage, ElNotification } from 'element-plus';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

import { AccountLogin, loginInfoApi } from '@/api/account';
import { getWorkermanUrl } from '@/api/setting';
import { setCookies } from '@/libs/util';
import '@/assets/js/canvas-nest.min';
import Verify from '@/components/verifition/Verify';
import { PrevLoading } from '@/utils/loading.js';
import { formatFlatteningRoutes, findFirstNonNullChildren } from '@/libs/system';
import { Local } from '@/utils/storage.js';
import Setting from '@/setting';
import { useAppStore } from '@/store/module/app';
import { useUserInfoStore } from '@/store/module/userInfo';
import { useMenusStore } from '@/store/module/menus';
import { useRoutesListStore } from '@/store/module/routesList';
import { useKeepAliveNamesStore } from '@/store/module/keepAliveNames';

defineOptions({ name: 'login' });

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
const userInfoStore = useUserInfoStore();
const menusStore = useMenusStore();
const routesListStore = useRoutesListStore();
const keepAliveNamesStore = useKeepAliveNamesStore();

const formInlineRef = ref(null);
const verifyRef = ref(null);

const fullWidth = ref(document.documentElement.clientWidth);
const loading = ref(false);
const isShow = ref(false);
const imgcode = ref('');
const formInline = ref({
  username: '',
  password: '',
});
const ruleInline = {
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
};
const login_captcha = ref(0);
const login_logo = ref('');
const swiperList = ref([]);
const defaultSwiperList = __asset_sw_0;
const key = ref('');
const copyright = ref('');
const version = ref('');
const timer = ref(null);

// created
document.onkeydown = (e) => {
  if (route.name === 'login' && (e.keyCode === 13 || e.which === 13)) {
    handleSubmit();
  }
};
window.addEventListener('resize', handleResize);

onMounted(() => {
  nextTick(() => {
    handleResize();
    swiperData();
  });
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize);
  document.onkeydown = null;
  const canvas = document.getElementsByTagName('canvas')[0];
  if (canvas) canvas.removeAttribute('class', 'index_bg');
});

function swiperData() {
  loginInfoApi()
    .then((res) => {
      const data = res.data || {};
      document.title = `${data.site_name} - 登录`;
      localStorage.setItem('ADMIN_TITLE', data.site_name || '');
      appStore.setAdminTitle(data.site_name);
      login_logo.value = data.login_logo || __asset_logo_1;
      swiperList.value = data.slide && data.slide.length ? data.slide : [{ slide: defaultSwiperList }];
      key.value = data.key;
      copyright.value = data.copyright;
      version.value = data.version;
      login_captcha.value = data.login_captcha;
    })
    .catch((err) => {
      ElMessage.error(err);
      login_logo.value = __asset_logo_1;
      swiperList.value = [{ slide: defaultSwiperList }];
    });
}
function success(params) {
  closeModel(params);
}
function closeModel(params) {
  // 防重复提交：按钮有 v-db-click 拦截，回车提交路径无拦截，并发两次登录会导致提示弹窗成对出现
  if (loading.value) return;
  isShow.value = false;
  loading.value = true;
  AccountLogin({
    account: formInline.value.username,
    pwd: formInline.value.password,
    key: key.value,
    captchaType: 'blockPuzzle',
    captchaVerification: params ? params.captchaVerification : '',
  })
    .then(async (res) => {
      const data = res.data;
      const expires = getExpiresTime(data.expires_time);
      setCookies('uuid', data.user_info.id, expires);
      setCookies('token', data.token, expires);
      setCookies('expires_time', data.expires_time, expires);
      Local.set('PERMISSIONS', data.site_func);
      userInfoStore.setUniqueAuth(data.unique_auth);
      userInfoStore.setUserInfo(data.user_info);
      menusStore.setopenMenus([]);
      menusStore.getmenusNav(data.menus);
      routesListStore.setRoutesList(data.menus);
      const arr = formatFlatteningRoutes(router.options.routes);
      formatTwoStageRoutes(arr);
      menusStore.setOneLvMenus(arr);
      const routes = formatFlatteningRoutes(data.menus);
      menusStore.setOneLvRoute(routes);
      userInfoStore.setName(data.user_info.account);
      userInfoStore.setAvatar(data.user_info.head_pic);
      userInfoStore.setAccess(data.unique_auth);
      userInfoStore.setLogo(data.logo);
      userInfoStore.setLogoSmall(data.logo_square);
      userInfoStore.setVersion(data.version);
      userInfoStore.setNewOrderAudioLink(data.newOrderAudioLink);
      login_captcha.value = 0;
      try {
        if (data.queue === false) {
          ElNotification.warning({
            title: '温馨提示',
            dangerouslyUseHTMLString: true,
            message:
              '您的【消息队列】未开启，没有开启会导致异步任务无法执行。请尽快执行命令开启！！<a href="https://doc.crmeb.com/single/v54/13667" target="_blank">点击查看开启方法</a>',
            duration: 30000,
          });
        }
        if (data.timer === false) {
          setTimeout(() => {
            ElNotification.warning({
              title: '温馨提示',
              dangerouslyUseHTMLString: true,
              message:
                '您的【定时任务】未开启，没有开启会导致自动收货、未支付自动取消订单、订单自动好评、拼团到期退款等任务无法正常执行。请尽快执行命令开启！！<a href="https://doc.crmeb.com/single/v54/13667" target="_blank">点击查看开启方法</a>',
              duration: 30000,
            });
          }, 0);
        }
        checkSocket();
      } catch (e) {}
      PrevLoading.start();
      router.push({
        path: data.menus.length ? findFirstNonNullChildren(data.menus).path : Setting.routePre + '/',
      });
    })
    .catch((res) => {
      const data = res || {};
      ElMessage.error(data.msg || '登录失败');
      if (res && res.data) login_captcha.value = res.data.login_captcha;
    })
    .finally(() => {
      setTimeout(() => {
        loading.value = false;
      }, 1000);
    });
}
function formatTwoStageRoutes(arr) {
  if (!arr.length) return false;
  const cacheList = [];
  arr.forEach((v) => {
    if (v && v.meta && v.meta.keepAlive) {
      cacheList.push(v.name);
    }
  });
  if (cacheList.length) {
    keepAliveNamesStore.setCacheKeepAlive(cacheList);
  }
}
function checkSocket() {
  getWorkermanUrl()
    .then((res) => {
      const url = res.data.admin;
      let isNotice = false;
      const socket = new window.WebSocket(url);
      socket.onopen = () => {
        isNotice = true;
        socket.close();
      };
      socket.onerror = socket.onclose = () => {
        if (!isNotice) {
          isNotice = true;
          ElNotification.warning({
            title: '温馨提示',
            message:
              '您的【长连接】未开启，没有开启会导致系统默认客服无法使用,后台订单通知无法收到。请尽快执行命令开启！！<a href="https://doc.crmeb.com/single/v54/13667" target="_blank">点击查看开启方法</a>',
            dangerouslyUseHTMLString: true,
            duration: 30000,
          });
        }
      };
    })
    .catch(() => {});
}
function getExpiresTime(expiresTime) {
  const nowTimeNum = Math.round(Date.now() / 1000);
  const expiresTimeNum = expiresTime - nowTimeNum;
  return parseFloat(expiresTimeNum / 60 / 60 / 24);
}
function closefail() {
  ElMessage.error('校验错误');
}
function handleResize() {
  fullWidth.value = document.documentElement.clientWidth;
  const canvas = document.getElementsByTagName('canvas')[0];
  if (canvas) {
    if (fullWidth.value < 768) {
      canvas.removeAttribute('class', 'index_bg');
    } else {
      canvas.className = 'index_bg';
    }
  }
}
function handleSubmit() {
  formInlineRef.value.validate((valid) => {
    if (valid) {
      if (login_captcha.value === 1) {
        verifyRef.value.show();
      } else {
        closeModel();
      }
    }
  });
}
</script>
<style lang="scss" scoped>
.page-account {
  display: flex;
  width: 100%;
  background-image: url('../../../assets/images/bg.jpg');
  background-size: cover;
  background-position: center;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  overflow: auto;
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
  border-radius: 12px 0px 0px 12px;
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
  border-radius: 12px;
  z-index: 1;
  display: flex;
}
.containerSamll {
  /* width: 56% !important; */
  background: #fff !important;
}
.containerBig {
  width: auto !important;
  background: #f7f7f7 !important;
}
.index_from {
  padding: 32px 40px 32px 40px;
  height: 400px;
  box-sizing: border-box;
}
.page-account-top {
  padding: 20px 0 24px 0 !important;
  box-sizing: border-box !important;
  display: flex;
  justify-content: center;
}
.page-account-container {
  border-radius: 0px 6px 6px 0px;
}
.btn {
  width: 100%;
  background: linear-gradient(90deg, rgba(25, 180, 241, 1) 0%, rgba(14, 115, 232, 1) 100%) !important;
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
.from-wh {
  width: 400px;
}
.pull-right {
  float: right !important;
}
:deep(.el-button--primary) {
  border: none;
}
:deep(.el-button) {
  padding: 13px 20px !important;
}
.pull-right {
  float: right !important;
  color: #666;
}
.pull-right a {
  margin-left: 0;
  color: #666;
}
.footer {
  position: fixed;
  bottom: 0;
  width: 100%;
  left: 0;
  margin: 0;
  background: rgba(255, 255, 255, 0.8);
  border-top: 1px solid #e7eaec;
  overflow: hidden;
  padding: 10px 20px;
  height: 36px;
  line-height: 18px;
  z-index: 999;
}
</style>
