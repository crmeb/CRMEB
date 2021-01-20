import * as qiniu from 'qiniu-js';
import Cos from 'cos-js-sdk-v5';
import axios from 'axios';

export default {

    videoUpload (config) {
        if (config.type === 'COS') {
            return this.cosUpload(config.evfile, config.res.data, config.uploading);
        } else if (config.type === 'OSS') {
            return this.ossHttp(config.evfile, config.res, config.uploading);
        } else {
            return this.qiniuHttp(config.evfile, config.res, config.uploading);
        }
    },
    cosUpload (file, config, uploading) {
        console.log(config);
        let cos = new Cos({
            getAuthorization (options, callback) {
                callback({
                    TmpSecretId: config.credentials.tmpSecretId, // 临时密钥的 tmpSecretId
                    TmpSecretKey: config.credentials.tmpSecretKey, // 临时密钥的 tmpSecretKey
                    XCosSecurityToken: config.credentials.sessionToken, // 临时密钥的 sessionToken
                    ExpiredTime: config.expiredTime // 临时密钥失效时间戳，是申请临时密钥时，时间戳加 durationSeconds
                });
            }
        });
        let fileObject = file.target.files[0];
        console.log(fileObject);
        let Key = fileObject.name;
        let pos = Key.lastIndexOf('.');
        let suffix = '';
        if (pos !== -1) {
            suffix = Key.substring(pos);
        }
        let filename = new Date().getTime() + suffix;
        return new Promise((resolve, reject) => {
            cos.sliceUploadFile({
                Bucket: config.bucket, /* 必须 */
                Region: config.region, /* 必须 */
                Key: filename, /* 必须 */
                Body: fileObject, // 上传文件对象
                onProgress: function (progressData) {
                    console.log(progressData)
                    uploading(progressData);
                }
            }, function (err, data) {
                if (err) {
                    reject({ msg: err });
                } else {
                    resolve({ url: 'http://' + data.Location, ETag: data.ETag });
                }
            });
        })
    },
    cosHttp (evfile, res, videoIng) {
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
        let filename = new Date().getTime() + suffix;
        let data = res.data;
        let XCosSecurityToken = data.credentials.sessionToken;
        let url = data.url + camSafeUrlEncode(filename).replace(/%2F/g, '/');
        let xhr = new XMLHttpRequest();
        xhr.open('PUT', url, true);
        XCosSecurityToken && xhr.setRequestHeader('x-cos-security-token', XCosSecurityToken);
        xhr.upload.onprogress = function (e) {
            let progress = Math.round(e.loaded / e.total * 10000) / 100;
            videoIng(true, progress);
        };
        return new Promise((resolve, reject) => {
            xhr.onload = function () {
                console.log('上传中');
                if (/^2\d\d$/.test('' + xhr.status)) {
                    var ETag = xhr.getResponseHeader('etag');
                    console.log(null, { url: url, ETag: ETag });
                    videoIng(false, 0);
                    resolve({ url: url, ETag: ETag });
                } else {
                    reject({ msg: '文件 ' + filename + ' 上传失败，状态码：' + xhr.statu })
                }
            };
            xhr.onerror = function () {
                reject({ msg: '文件 ' + filename + '上传失败，请检查是否没配置 CORS 跨域规' });
            };
            xhr.send(fileObject);
            xhr.onreadystatechange = function () {
                console.log(xhr.statusText, xhr.responseText, 'xhr')
            }
        });
    },
    ossHttp (evfile, res, videoIng) {
        let that = this;
        let fileObject = evfile.target.files[0];
        let file = fileObject.name;
        let pos = file.lastIndexOf('.');
        let suffix = '';
        if (pos !== -1) {
            suffix = file.substring(pos);
        }
        let filename = new Date().getTime() + suffix;
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
            axios.post(url, formData).then(() => {
                // that.progress = 0;
                videoIng(false, 0);
                resolve({ url: fileUrl });
            }).catch(res => {
                reject({ msg: res });
            })
        });
    },
    qiniuHttp (evfile, res, videoIng) {
        let uptoken = res.data.token;
        let file = evfile.target.files[0]; // Blob 对象，上传的文件
        let Key = file.name; // 上传后文件资源名以设置的 key 为主，如果 key 为 null 或者 undefined，则文件资源名会以 hash 值作为资源名。
        let pos = Key.lastIndexOf('.');
        let suffix = '';
        if (pos !== -1) {
            suffix = Key.substring(pos);
        }
        let filename = new Date().getTime() + suffix;
        let fileUrl = res.data.domain + '/' + filename;
        let config = {
            useCdnDomain: true
        };
        let putExtra = {
            fname: '', // 文件原文件名
            params: {}, // 用来放置自定义变量
            mimeType: null // 用来限制上传文件类型，为 null 时表示不对文件类型限制；限制类型放到数组里： ["image/png", "image/jpeg", "image/gif"]
        };
        let observable = qiniu.upload(file, filename, uptoken, putExtra, config);
        return new Promise((resolve, reject) => {
            observable.subscribe({
                next: (result) => {
                    console.log(result);
                    let progress = Math.round(result.total.loaded / result.total.size);
                    videoIng(true, progress);
                    // 主要用来展示进度
                },
                error: (errResult) => {
                    // 失败报错信息
                    reject({ msg: errResult })
                },
                complete: (result) => {
                    // 接收成功后返回的信息
                    videoIng(false, 0);
                    resolve({ url: fileUrl });
                }
            })
        });
    }
}
