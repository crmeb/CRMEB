import axios from 'axios';
import Setting from '@/setting';
import { getCookies, removeCookies } from '@/libs/util';

const service = axios.create({
  baseURL: Setting.apiBaseURL,
  timeout: 10000, // 请求超时时间
});
axios.defaults.withCredentials = true; // 携带cookie

// 请求拦截器
service.interceptors.request.use(
  (config) => {
    if (config.kefu) {
      let baseUrl = Setting.apiBaseURL.replace(/adminapi/, 'kefuapi');
      config.baseURL = baseUrl;
    } else {
      config.baseURL = Setting.apiBaseURL;
    }
    if (config.file) {
      config.headers['Content-Type'] = 'multipart/form-data';
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
    return Promise.reject(error);
  },
);
export default service;

// function sendRequest(url, method, params, header) {
//   const instance = axios.create({
//     baseURL: Setting.apiBaseURL, // 请求的根域名
//     timeout: 1000, // 请求超时时间
//     headers: {
//       'X-Custom-Header': header, // 自定义头信息
//     },
//   });

//   if (method === 'GET') {
//     instance
//       .get(url, { params: params })
//       .then((response) => {
//         // 处理响应数据
//       })
//       .catch((error) => {
//         // 处理错误
//       });
//   } else if (method === 'POST') {
//     instance
//       .post(url, params, { headers: header })
//       .then((response) => {
//         // 处理响应数据
//       })
//       .catch((error) => {
//         // 处理错误
//       });
//   } else if (method === 'PUT') {
//     instance
//       .put(url, params, { headers: header })
//       .then((response) => {
//         // 处理响应数据
//       })
//       .catch((error) => {
//         // 处理错误
//       });
//   } else if (method === 'DELETE') {
//     instance
//       .delete(url, { headers: header })
//       .then((response) => {
//         // 处理响应数据
//       })
//       .catch((error) => {
//         // 处理错误
//       });
//   }

//   return instance;
// }
