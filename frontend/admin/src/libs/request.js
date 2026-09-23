// +---------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +---------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +---------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +---------------------------------------------------------------------

import axios from 'axios';
import { ElMessage } from 'element-plus';
import { getCookies, removeCookies } from '@/libs/util';
import Setting from '@/setting';
import router from '@/router';
const service = axios.create({
  baseURL: Setting.apiBaseURL,
  timeout: 100000, // 请求超时时间
});

axios.defaults.withCredentials = true; // 携带cookie

let loginRedirecting = false;

// 业务 401 和 HTTP 401 共用处理，路由加载失败时回退到整页跳转。
function redirectToLogin() {
  if (loginRedirecting) return;
  localStorage.clear();
  removeCookies('token');
  removeCookies('expires_time');
  removeCookies('uuid');
  if (router.currentRoute.value.name === 'login') return;

  loginRedirecting = true;
  const login = { name: 'login' };
  const fallback = () => window.location.replace(router.resolve(login).href);
  router
    .replace(login)
    .then(() => {
      if (router.currentRoute.value.name !== 'login') {
        fallback();
      }
    })
    .catch(fallback)
    .finally(() => {
      loginRedirecting = false;
    });
}

// 请求拦截器
service.interceptors.request.use(
  (config) => {
    if (config.file) {
      config.headers['Content-Type'] = 'multipart/form-data';
    }
    const token = getCookies('token');
    const kefuToken = getCookies('kefu_token');
    // kefu 标志仅表示客服工作台请求：携带客服 token（kefu_token），URL 与后台接口统一走 adminapi
    if (token || kefuToken) {
      config.headers['Authori-zation'] = config.kefu ? 'Bearer ' + kefuToken : 'Bearer ' + token;
    }
    return config;
  },
  (error) => {
    // do something with request error
    return Promise.reject(error);
  },
);

// response interceptor
service.interceptors.response.use(
  (response) => {
    let obj = {};
    if (!!response.data) {
      if (typeof response.data == 'string') {
        obj = JSON.parse(response.data);
      } else {
        obj = response.data;
      }
    }
    let status = response.data ? obj.status : 0;
    // let status = response.data ? response.data.status : 0;
    const code = status;
    switch (code) {
      case 200:
        return obj;
      case 401:
        redirectToLogin();
        return Promise.reject({ ...obj, msg: obj.msg || '未登录' });
      case 403:
        return Promise.reject({ msg: '没有权限' });
      default:
        return Promise.reject(obj || { msg: '未知错误' });
    }
  },
  (error) => {
    if (error.response?.status === 401) {
      redirectToLogin();
      return Promise.reject({ ...error.response.data, msg: error.response.data?.msg || '未登录' });
    }
    ElMessage.error(error.response?.data?.msg || error.msg || error.message || '网络请求失败');
    return Promise.reject(error);
  },
);

export default service;
