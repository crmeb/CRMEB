import axios from 'axios';
import Setting from '@/setting';
import { getCookies, removeCookies } from '@/libs/util';
import { Message } from 'element-ui';

const service = axios.create({
  baseURL: location.protocol + '//' + location.hostname,
  timeout: 10000, // 请求超时时间
});
axios.defaults.withCredentials = true; // 携带cookie

// 请求拦截器
service.interceptors.request.use(
  (config) => {
    if (config.file) {
      config.headers['Content-Type'] = 'multipart/form-data';
    } else {
      config.headers['Content-Type'] = 'application/json;charset=UTF-8';
    }
    try {
      if (config.headerItem) {
        for (let i in config.headerItem) {
          config.headers[i] = config.headerItem[i];
        }
      }
    } catch (error) {
      console.log(error);
    }

    const token = getCookies('token');
    const kefuToken = getCookies('kefu_token');
    // if (token || kefuToken) {
    //   config.headers['Authori-zation'] = config.kefu ? 'Bearer ' + kefuToken : 'Bearer ' + token;
    // }
    return config;
  },
  (error) => {
    // do something with request error
    return Promise.reject(error);
  },
);
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
      default:
        return Promise.reject(obj || { msg: '未知错误' });
    }
  },
  (error) => {
    Message.error('接口异常');

    // return Promise.reject(error);
  },
);
export default service;
