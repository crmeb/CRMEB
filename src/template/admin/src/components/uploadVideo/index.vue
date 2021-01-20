<template>
    <div>
        <div class="mt20 ml20">
            <Input class="perW35"  v-model="videoLink" placeholder="请输入视频链接"/>
            <input type="file" ref='refid' style="display:none" @change="zh_uploadFile_change">
            <Button type="primary" icon="ios-cloud-upload-outline" class="ml10" @click="zh_uploadFile">{{videoLink ? '确认添加' : '上传视频'}}</Button>
            <Progress :percent = progress :stroke-width="5" v-if="upload.videoIng" />
            <div class="iview-video-style" v-if="formValidate.video_link">
                <video style="width:100%;height: 100%!important;border-radius: 10px;" :src="formValidate.video_link" controls="controls">
                    您的浏览器不支持 video 标签。
                </video>
                <div class="mark">
                </div>
                <Icon type="ios-trash-outline" class="iconv" @click="delVideo"/>
            </div>
        </div>
        <div class="mt50 ml20">
            <Button type="primary" @click="uploads">确认</Button>
        </div>
    </div>
</template>

<script>
    import { productGetTempKeysApi } from '@/api/product'
    import '../../../public/UEditor/dialogs/internal'
    export default {
        name: 'vide11o',
        data () {
            return {
                upload: {
                    videoIng: false // 是否显示进度条；
                },
                progress: 0, // 进度条默认0
                videoLink: '',
                formValidate: {
                    video_link: ''
                }
            }
        },
        methods: {
            // 删除视频；
            delVideo () {
                let that = this
                that.$set(that.formValidate, 'video_link', '')
            },
            zh_uploadFile () {
                if (this.videoLink) {
                    this.formValidate.video_link = this.videoLink
                } else {
                    this.$refs.refid.click()
                }
            },
            zh_uploadFile_change (evfile) {
                let that = this
                let suffix = evfile.target.files[0].name.substr(evfile.target.files[0].name.indexOf('.'))
                if (suffix !== '.mp4') {
                    return that.$Message.error('只能上传MP4文件')
                }
                productGetTempKeysApi().then(res => {
                    that.$videoCloud.videoUpload({
                        type: res.data.type,
                        evfile: evfile,
                        res: res,
                        uploading (status, progress) {
                            that.upload.videoIng = status
                            console.log(status, progress)
                        }
                    }).then(res => {
                        that.formValidate.video_link = res.url
                        that.$Message.success('视频上传成功')
                    }).catch(res => {
                        that.$Message.error(res)
                    })
                })
            },
            uploads () {
                nowEditor.dialog.close(true)
                nowEditor.editor.setContent("<p><video src='" + this.formValidate.video_link + "' controls='controls'></video><br/></p>", true)
                // nowEditor.editor.execCommand('insertvideo',"<video src='" + this.formValidate.video_link + "' controls='controls'></video>",)
            }
        }
    }
</script>

<style scoped>
    .iview-video-style{
        width: 40%;
        height: 180px;
        border-radius: 10px;
        background-color: #707070;
        margin-top: 10px;
        position: relative;
        overflow: hidden;
    }
    .iview-video-style .iconv{
        color: #fff;
        line-height: 180px;
        width: 50px;
        height: 50px;
        display: inherit;
        font-size: 26px;
        position: absolute;
        top: -74px;
        left: 50%;
        margin-left: -25px;
    }
    .iview-video-style .mark{
        position: absolute;
        width: 100%;
        height: 30px;
        top: 0;
        background-color: rgba(0,0,0,.5);
        text-align: center;
    }
</style>
