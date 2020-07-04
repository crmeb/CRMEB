<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href="{__FRAME_PATH}css/font-awesome.min.css" rel="stylesheet">
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/jquery.min.js"></script>
    <link rel="stylesheet" href="/static/plug/layui/css/layui.css">
    <script src="/static/plug/layui/layui.js"></script>
    <script src="{__PLUG_PATH}vue/dist/vue.min.js"></script>
    <script src="/static/plug/axios.min.js"></script>
    <script src="{__MODULE_PATH}widget/aliyun-oss-sdk-4.4.4.min.js"></script>
    <script src="{__MODULE_PATH}widget/cos-js-sdk-v5.min.js"></script>
    <script src="{__MODULE_PATH}widget/qiniu-js-sdk-2.5.5.js"></script>
    <script src="{__MODULE_PATH}widget/plupload.full.min.js"></script>
    <script src="{__MODULE_PATH}widget/videoUpload.js"></script>
    <style>
        .layui-form-item {
            margin-bottom: 0px;
        }

        .pictrueBox {
            display: inline-block !important;
        }

        .pictrue {
            width: 60px;
            height: 60px;
            border: 1px dotted rgba(0, 0, 0, 0.1);
            margin-right: 15px;
            display: inline-block;
            position: relative;
            cursor: pointer;
        }

        .pictrue img {
            width: 100%;
            height: 100%;
        }

        .upLoad {
            width: 58px;
            height: 58px;
            line-height: 58px;
            border: 1px dotted rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.02);
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .rulesBox {
            display: flex;
            flex-wrap: wrap;
            margin-left: 10px;
        }

        .layui-tab-content {
            margin-top: 15px;
        }

        .ml110 {
            margin: 18px 0 4px 110px;
        }

        .rules {
            display: flex;
        }

        .rules-btn-sm {
            height: 30px;
            line-height: 30px;
            font-size: 12px;
            width: 109px;
        }

        .rules-btn-sm input {
            width: 79% !important;
            height: 84% !important;
            padding: 0 10px;
        }

        .ml10 {
            margin-left: 10px !important;
        }

        .ml40 {
            margin-left: 40px !important;
        }

        .closes {
            position: absolute;
            left: 86%;
            top: -18%;
        }

        .red {
            color: red;
        }

        .layui-input-block .layui-video-box {
            width: 64%;
            height: 180px;
            border-radius: 10px;
            background-color: #707070;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .layui-input-block .layui-video-box i {
            color: #fff;
            line-height: 180px;
            margin: 0 auto;
            width: 50px;
            height: 50px;
            display: inherit;
            font-size: 50px;
        }

        .layui-input-block .layui-video-box .mark {
            position: absolute;
            width: 100%;
            height: 30px;
            top: 0;
            background-color: rgba(0, 0, 0, .5);
            text-align: center;
        }
    </style>
</head>
<body>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" id="app" v-cloak="">
        <div class="layui-card">
            <div class="layui-card-header">视频上传</div>
            <div class="layui-card-body" id="app">
                <div class="layui-form-item submit">
                    <div class="layui-input-block" style="margin-left: 0;">
                        <input type="text" name="link_key" v-model="videoLink"
                               style="width:50%;display:inline-block;margin-right: 10px;" autocomplete="off"
                               placeholder="请输入视频链接" class="layui-input">
                        <button type="button" @click="uploadVideo" class="layui-btn layui-btn-sm layui-btn-normal">
                            {{videoLink ? '确认添加' : '上传视频'}}
                        </button>
                        <input ref="filElem" type="file" style="display: none">
                    </div>
                    <div class="layui-input-block video_show" style="margin-top: 20px;margin-left: 0;width: 64%"
                         v-if="upload.videoIng">
                        <div class="layui-progress" style="margin-bottom: 10px">
                            <div class="layui-progress-bar layui-bg-blue" :style="'width:'+progress+'%'"></div>
                        </div>
                        <button type="button" class="layui-btn layui-btn-sm layui-btn-danger percent">{{progress}}%
                        </button>
                    </div>
                    <div class="layui-input-block" style="margin-left: 0;">
                        <div class="layui-video-box" v-if="formData.video">
                            <video style="width:100%;height: 100%!important;border-radius: 10px;" :src="formData.video"
                                   controls="controls">
                                您的浏览器不支持 video 标签。
                            </video>
                            <div class="mark" @click="delVideo">
                                <span class="layui-icon layui-icon-delete"
                                      style="font-size: 30px; color: #1E9FFF;"></span>
                            </div>

                        </div>
                        <div class="layui-video-box" v-else>
                            <i class="layui-icon layui-icon-play"></i>
                        </div>
                    </div>
                    <div class="layui-input-block" style="margin-left: 0;margin-top: 20px">
                        <button class="layui-btn layui-btn-lg layui-btn-normal" type="button" @click="handleSubmit()">
                            确认
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</html>
<script>
    var fodder = '{$fodder}';
    new Vue({
        el: '#app',
        data: {
            upload: {
                videoIng: false,
            },
            progress: 0,
            formData: {video: ''},
            videoLink: '',
            fodder: fodder,
        },
        methods: {
            requestPost: function (url, data) {
                return new Promise(function (resolve, reject) {
                    axios.post(url, data).then(function (res) {
                        if (res.status == 200 && res.data.code == 200) {
                            resolve(res.data)
                        } else {
                            reject(res.data);
                        }
                    }).catch(function (err) {
                        reject({msg: err})
                    });
                })
            },
            U: function (opt) {
                var m = opt.m || 'admin', c = opt.c || window.controlle || '', a = opt.a || 'index', q = opt.q || '',
                    p = opt.p || {};
                var params = Object.keys(p).map(function (key) {
                    return key + '/' + p[key];
                }).join('/');
                var gets = Object.keys(q).map(function (key) {
                    return key + '=' + q[key];
                }).join('&');

                return '/' + m + '/' + c + '/' + a + (params == '' ? '' : '/' + params) + (gets == '' ? '' : '?' + gets);
            },
            /**
             * 提示
             * */
            showMsg: function (msg, success) {
                layui.use(['layer'], function () {
                    layui.layer.msg(msg, success);
                });
            },
            uploadVideo: function () {
                if (this.videoLink) {
                    this.formData.video = this.videoLink;
                } else {
                    $(this.$refs.filElem).click();
                }
            },
            delVideo: function () {
                var that = this;
                that.$set(that.formData, 'video', '');
            },
            handleSubmit: function () {
                if (!this.formData.video) {
                    return this.showMsg('请上传后再确认添加');
                }
                parent.insertEditorVideo(this.formData.video);
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            }
        },
        mounted: function () {
            var that = this;
            $(that.$refs.filElem).change(function () {
                var inputFile = this.files[0];
                that.requestPost(that.U({c: "widget.video", a: 'get_signature'})).then(function (res) {
                    AdminUpload.upload(res.data.uploadType, {
                        token: res.data.uploadToken || '',
                        file: inputFile,
                        accessKeyId: res.data.accessKey || '',
                        accessKeySecret: res.data.secretKey || '',
                        bucketName: res.data.storageName || '',
                        region: res.data.storageRegion || '',
                        domain: res.data.domain || '',
                        uploadIng: function (progress) {
                            that.upload.videoIng = true;
                            that.progress = progress;
                        }
                    }).then(function (res) {
                        //成功
                        that.$set(that.formData, 'video', res.url);
                        that.progress = 0;
                        that.upload.videoIng = false;
                        return that.showMsg('上传成功');
                    }).catch(function (err) {
                        //失败
                        console.log(err)
                        return that.showMsg('上传失败');
                    });
                }).catch(function (res) {
                    return that.showMsg('获取密钥失败');
                });
            })
        }
    })
</script>