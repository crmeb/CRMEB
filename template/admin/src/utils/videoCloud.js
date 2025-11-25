// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import * as qiniu from 'qiniu-js';
import Cos from 'cos-js-sdk-v5';
import axios from 'axios';
import { upload, ossUpload } from '@/api/upload';

const sign = (method, publicKey, privateKey, md5, contentType, date, bucketName, fileName) => {
  const CryptoJS = require('crypto-js'); // 这里使用了crypto-js加密算法库，安装方法会在后面说明
  const CanonicalizedResource = `/${bucketName}/${fileName}`;
  const StringToSign = method + '\n' + md5 + '\n' + contentType + '\n' + date + '\n' + CanonicalizedResource; // 此处的md5以及date是可选的，contentType对于PUT请求是可选的，对于POST请求则是必须的
  let Signature = CryptoJS.HmacSHA1(StringToSign, privateKey);
  Signature = CryptoJS.enc.Base64.stringify(Signature);
  return 'UCloud' + ' ' + publicKey + ':' + Signature;
};
export default {
  videoUpload(config) {
    let result;
    switch (config.type) {
      case 'COS':
        result = this.cosUpload(config.evfile, config.res.data, config.uploading);
        break;
      case 'OSS':
        result = this.ossHttp(config.evfile, config.res, config.uploading);
        break;
      case 'OBS':
        result = this.obsHttp(config.evfile, config.res, config.uploading);
        break;
      case 'US3':
        result = this.us3Http(config.evfile, config.res, config.uploading);
        break;
      case 'JDOSS':
        result = this.jdHttp(config.evfile, config.res, config.uploading);
        break;
      case 'CTOSS':
        result = this.obsHttp(config.evfile, config.res, config.uploading);
        break;
      case 'QINIU':
        result = this.qiniuHttp(config.evfile, config.res, config.uploading);
        break;
      case 'local':
        result = this.uploadMp4ToLocal(config.evfile, config.res, config.uploading);
        break;
    }
    return result;
  },
  cosUpload(file, config, uploading) {
    let cos = new Cos({
      getAuthorization(options, callback) {
        callback({
          TmpSecretId: config.credentials.tmpSecretId, // 临时密钥的 tmpSecretId
          TmpSecretKey: config.credentials.tmpSecretKey, // 临时密钥的 tmpSecretKey
          XCosSecurityToken: config.credentials.sessionToken, // 临时密钥的 sessionToken
          ExpiredTime: config.expiredTime, // 临时密钥失效时间戳，是申请临时密钥时，时间戳加 durationSeconds
        });
      },
    });
    let fileObject = file.target.files[0];
    let Key = fileObject.name;
    let pos = Key.lastIndexOf('.');
    let suffix = '';
    if (pos !== -1) {
      suffix = Key.substring(pos);
    }
    let filename = this.getVideoName(suffix);
    return new Promise((resolve, reject) => {
      cos.sliceUploadFile(
        {
          Bucket: config.bucket /* 必须 */,
          Region: config.region /* 必须 */,
          Key: filename /* 必须 */,
          Body: fileObject, // 上传文件对象
          onProgress: function (progressData) {
            uploading(progressData);
          },
        },
        function (err, data) {
          if (err) {
            reject({ msg: err });
          } else {
            resolve({ url: 'http://' + data.Location, ETag: data.ETag });
          }
        },
      );
    });
  },
  cosHttp(evfile, res, videoIng) {
    // 腾讯云
    // 对更多字符编码的 url encode 格式
    let camSafeUrlEncode = function (str) {
      return encodeURIComponent(str)
        .replace(/!/g, '%21')
        .replace(/'/g, '%27')
        .replace(/\(/g, '%28')
        .replace(/\)/g, '%29')
        .replace(/\*/g, '%2A');
    };
    let fileObject = evfile.target.files[0];
    let Key = fileObject.name;
    let pos = Key.lastIndexOf('.');
    let suffix = '';
    if (pos !== -1) {
      suffix = Key.substring(pos);
    }
    let filename = this.getVideoName(suffix);
    let data = res.data;
    let XCosSecurityToken = data.credentials.sessionToken;
    let url = data.url + camSafeUrlEncode(filename).replace(/%2F/g, '/');
    let xhr = new XMLHttpRequest();
    xhr.open('PUT', url, true);
    XCosSecurityToken && xhr.setRequestHeader('x-cos-security-token', XCosSecurityToken);
    xhr.upload.onprogress = function (e) {
      let progress = Math.round((e.loaded / e.total) * 10000) / 100;
      videoIng(true, progress);
    };
    return new Promise((resolve, reject) => {
      xhr.onload = function () {
        if (/^2\d\d$/.test('' + xhr.status)) {
          var ETag = xhr.getResponseHeader('etag');
          videoIng(false, 0);
          resolve({ url: url, ETag: ETag });
        } else {
          reject({ msg: '文件 ' + filename + ' 上传失败，状态码：' + xhr.statu });
        }
      };
      xhr.onerror = function () {
        reject({ msg: '文件 ' + filename + '上传失败，请检查是否没配置 CORS 跨域规' });
      };
      xhr.send(fileObject);
      xhr.onreadystatechange = function () {};
    });
  },
  ossHttp(evfile, res, videoIng) {
    let that = this;
    let fileObject = evfile.target.files[0];
    let file = fileObject.name;
    let pos = file.lastIndexOf('.');
    let suffix = '';
    if (pos !== -1) {
      suffix = file.substring(pos);
    }
    let filename = this.getVideoName(suffix);
    let formData = new FormData();
    let data = res.data;
    // 注意formData里append添加的键的大小写
    formData.append('key', filename); // 存储在oss的文件路径
    formData.append('OSSAccessKeyId', data.accessid); // accessKeyId
    formData.append('policy', data.policy); // policy
    formData.append('Signature', data.signature); // 签名
    // 如果是base64文件，那么直接把base64字符串转成blob对象进行上传就可以了
    formData.append('file', fileObject);
    formData.append('success_action_status', 200); // 成功后返回的操作码
    let url = data.host;
    let fileUrl = url + '/' + filename;
    videoIng(true, 100);
    return new Promise((resolve, reject) => {
      axios.defaults.withCredentials = false;
      axios
        .post(url, formData)
        .then(() => {
          // that.progress = 0;
          videoIng(false, 0);
          resolve({ url: fileUrl });
        })
        .catch((res) => {
          reject({ msg: res });
        });
    });
  },
  obsHttp(file, res, videoIng) {
    const fileObject = file.target.files[0];
    const Key = fileObject.name;
    const pos = Key.lastIndexOf('.');
    let suffix = '';
    if (pos !== -1) {
      suffix = Key.substring(pos);
    }
    const filename = this.getVideoName(suffix);
    const formData = new FormData();
    const data = res.data;
    // 注意formData里append添加的键的大小写
    formData.append('key', filename);
    formData.append('AccessKeyId', data.accessid);
    formData.append('policy', data.policy);
    formData.append('signature', data.signature);
    formData.append('file', fileObject);
    formData.append('success_action_status', 200);
    const url = data.host;
    const fileUrl = url + '/' + filename;
    videoIng(true, 100);
    return new Promise((resolve, reject) => {
      axios.defaults.withCredentials = false;
      axios
        .post(url, formData)
        .then(() => {
          videoIng(false, 0);
          resolve({ url: data.cdn ? data.cdn + '/' + filename : fileUrl });
        })
        .catch((res) => {
          reject({ msg: res });
        });
    });
  },
  us3Http(file, res, videoIng) {
    const fileObject = file.target.files[0];
    const Key = fileObject.name;
    const pos = Key.lastIndexOf('.');
    let suffix = '';
    if (pos !== -1) {
      suffix = Key.substring(pos);
    }
    const filename = this.getVideoName(suffix);
    const data = res.data;

    const auth = sign('PUT', data.accessid, data.secretKey, '', fileObject.type, '', data.storageName, filename);
    return new Promise((resolve, reject) => {
      axios.defaults.withCredentials = false;
      const url = `https://${data.storageName}.cn-bj.ufileos.com/${filename}`;
      axios
        .put(url, fileObject, {
          headers: {
            Authorization: auth,
            'content-type': fileObject.type,
          },
        })
        .then((res) => {
          videoIng(false, 0);
          resolve({ url: data.cdn ? data.cdn + '/' + filename : url });
        })
        .catch((res) => {
          reject({ msg: res });
        });
    });
  },
  qiniuHttp(evfile, res, videoIng) {
    const uptoken = res.data.token;
    const file = evfile.target.files[0]; // Blob 对象，上传的文件
    const Key = file.name; // 上传后文件资源名以设置的 key 为主，如果 key 为 null 或者 undefined，则文件资源名会以 hash 值作为资源名。
    const pos = Key.lastIndexOf('.');
    let suffix = '';
    if (pos !== -1) {
      suffix = Key.substring(pos);
    }
    const filename = this.getVideoName(suffix);
    const fileUrl = res.data.domain + '/' + filename;
    const config = {
      useCdnDomain: true,
    };
    const putExtra = {
      fname: '', // 文件原文件名
      params: {}, // 用来放置自定义变量
      mimeType: null, // 用来限制上传文件类型，为 null 时表示不对文件类型限制；限制类型放到数组里： ["image/png", "image/jpeg", "image/gif"]
    };
    const observable = qiniu.upload(file, filename, uptoken, putExtra, config);

    return new Promise((resolve, reject) => {
      observable.subscribe({
        next: (result) => {
          console.log(videoIng)
          const progress = Math.round(result.total.loaded / result.total.size);
          videoIng(true, progress);
          // 主要用来展示进度
        },
        error: (errResult) => {
          // 失败报错信息
          console.log(errResult);
          reject({ msg: errResult });
        },
        complete: (result) => {
          // 接收成功后返回的信息
          console.log(result,'result');
          videoIng(false, 0);
          resolve({ url: res.data.cdn ? res.data.cdn + '/' + filename : fileUrl });
        },
      });
    });
  },
  // 京东云上传
  jdHttp(evfile, r, videoIng) {
    const fileObject = evfile.target.files[0]; // 获取的文件对象
    const formData = new FormData();
    formData.append('file', fileObject);
    return new Promise((resolve, reject) => {
      ossUpload(r.data.upload_url, formData)
        .then((res) => {
          console.log(res);
        })
        .catch((err) => {
          videoIng(true, 100);
          resolve(r.data);
        });
    });
  },
  // 本地上传
  uploadMp4ToLocal(evfile, res, videoIng) {
    const fileObject = evfile.target.files[0]; // 获取的文件对象
    const formData = new FormData();
    formData.append('file', fileObject);
    videoIng(true, 100);
    return upload(formData);
  },
  // 获取上传云存储视频名称
  getVideoName(suffix) {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const name = new Date().getTime();
    return `attach/${year}/${month}/${name}` + suffix;
  },
};
