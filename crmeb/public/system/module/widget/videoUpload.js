(function (global) {

    var AdminUpload = {
        config: {
            file: null,
            token: '',
            accessKeyId: '',
            accessKeySecret: "",
            bucketName: '',
            region: "",
            domain:'',
            uploadIng: function (res) {

            }
        },
        uploadName: function () {
            var data =
                ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r",
                    "s", "t", "u", "v", "w", "x", "y", "z"], nums = "";
            for (var i = 0; i < 32; i++) {
                var r = parseInt(Math.random() * 35);
                nums += data[r];
            }
            return nums;
        }
    };

    /**
     * QINIU 七牛上传
     * @return {Promise<any>}
     * @constructor
     */
    AdminUpload.QINIU = function () {
        var putExtra = {
            fname: '',
            params: {},
            mimeType: null,
        }, config = {
            useCdnDomain: true, //使用cdn加速
        }, that = this;
        return new Promise(function (resolve, reject) {
            var observable = qiniu.upload(that.config.file, that.uploadName(), that.config.token, putExtra, config);
            observable.subscribe({
                next: (result) => {
                    // 主要用来展示进度
                    that.config.uploadIng(result.total.percent.toFixed(2));
                },
                error: (err) => {
                    reject(err);
                },
                complete: (res) => {
                    resolve({url: that.config.domain + "/" + res.key, type: 2});
                },
            });
        })
    }

    /**
     * cos 上传
     * @return {Promise<any>}
     * @constructor
     */
    AdminUpload.COS = function () {
        var that = this;
        return new Promise(function (resolve, reject) {
            console.log(that.config);
            var client = new COS({
                SecretId: that.config.accessKeyId,
                SecretKey: that.config.accessKeySecret
            });
            client.putObject({
                Bucket: that.config.bucketName,
                Region: that.config.region,
                Key: that.uploadName(),
                StorageClass: 'STANDARD',
                Body: that.config.file,
                onProgress: function (progressData) {
                    that.config.uploadIng(parseInt(progressData.percent * 100));
                }
            }, function (err, data) {
                if (err) {
                    reject(err);
                } else {
                    resolve({url: "http://" + data.Location, type: 4});
                }
            });
        })
    }

    /**
     * oss 上传
     * @return {Promise<any>}
     * @constructor
     */
    AdminUpload.OSS = function () {
        var that = this, file = that.config.file, suffix = file.name.substr(file.name.indexOf(".")),
            storeAs = this.uploadName() + suffix;
        return new Promise(function (resolve, reject) {
            var client = new OSS.Wrapper({
                region: that.config.region,
                accessKeyId: that.config.accessKeyId,
                accessKeySecret: that.config.accessKeySecret,
                bucket: that.config.bucketName,
            });
            var options = {
                progress: async function (p, k, i) {
                    that.config.uploadIng(parseInt(p.toFixed(2) * 100));
                },
                partSize: 1000 * 1024,//设置分片大小
                timeout: 120000,//设置超时时间
            }
            client.multipartUpload(storeAs, file, options).then(function (result) {
                var url = result.res.requestUrls[0];
                var length = url.lastIndexOf('?');
                var imgUrl = url.substr(0, length);
                resolve({url: imgUrl, type: 3});
            }).catch(function (err) {
                reject(err);
            });
        });
    }

    /**
     * 执行上传
     * @param driver
     * @param opt
     * @return {*}
     */
    AdminUpload.upload = function (driver, opt) {
        if (typeof opt !== 'object') {
            opt = {};
        }
        Object.assign(this.config, opt);
        var suffix = this.config.file.name.substr(this.config.file.name.indexOf("."));
        if (suffix != '.mp4') {
            return new Promise(function (resolve, reject) {
                reject('只能上传MP4文件');
            })
        }
        if (this[driver]) {
            return this[driver]();
        } else {
            return new Promise(function (resolve, reject) {
                reject('上传句柄不存在');
            })
        }
    }

    global.AdminUpload = AdminUpload;

    return AdminUpload;

}(this));




