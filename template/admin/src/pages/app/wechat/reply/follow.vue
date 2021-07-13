<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
              <router-link :to="{path:'/admin/app/wechat/reply/keyword'}"><Button icon="ios-arrow-back" size="small"  class="mr20" v-show="$route.params.id">返回</Button></router-link>
              <span class="ivu-page-header-title mr20" v-text="($route.params.key || $route.params.id !== '0')?'关键字编辑':'关键字添加'" v-if="$route.params.id"></span>
              <span class="ivu-page-header-title mr20" v-text="$route.meta.title" v-else></span>
            </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <!-- 公众号设置 -->
            <Row :gutter="24" type="flex">
                <Col span="24" class="ml40">
                    <!-- 预览功能 -->
                    <Col :span="24">
                        <Col :xl="7" :lg="7" :md="22" :sm="22" :xs="22" class="left mb15">
                            <img class="top" src="../../../../assets/images/mobilehead.png"/>
                            <img class="bottom" src="../../../../assets/images/mobilefoot.png"/>
                            <div class="centent">
                                <div class="time-wrapper"><span class="time">9:36</span></div>
                                <div class="view-item text-box clearfix" v-if="formValidate.type !== 'news'">
                                    <div class="avatar fl"><img src="../../../../assets/images/head.gif"/></div>
                                    <div class="box-content fl">
                                        <span v-text="formValidate.data.content"
                                              v-if="formValidate.type === 'text'"></span>
                                        <div class="box-content_pic" v-if="formValidate.data.src"><img
                                                :src="formValidate.data.src?imgUrl+formValidate.data.src:''"></div>
                                    </div>
                                </div>
                                <div v-if="formValidate.type === 'news'">
                                    <div  v-for="(j, i) in formValidate.data.list" :key="i" >
                                        <div  v-if="i === 0">
                                            <div class="news_pic" :style="{backgroundImage: 'url(' + (j.image_input[0]) + ')',backgroundSize:'100% 100%'}"></div>
                                            <span class="news_sp">{{j.title}}</span>
                                        </div>
                                        <div v-else class="news_cent">
                                            <span class="news_sp1" v-if="j.synopsis">{{j.title}}</span>
                                            <div class="news_cent_img" v-if="j.image_input.length!==0"><img :src="j.image_input[0]"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Col>
                        <Col :xl="11" :lg="12" :md="22" :sm="22" :xs="22">
                            <Col span="24" class="userAlert">
                                <div class="box-card right">
                                    <Form ref="formValidate" :model="formValidate" :rules="ruleValidate"
                                          :label-width="100" class="mt20" @submit.native.prevent>
                                        <FormItem label="关键字：" prop="val" v-if="$route.params.id">
                                            <div class="arrbox">
                                                <!--:closable="$route.params.id==='0'? true : false"-->
                                                <Tag  @on-close="handleClose" :name="item" :closable="true"
                                                     v-for="(item, index) in labelarr" :key="index">{{item}}
                                                </Tag>
                                                <!--:readonly="$route.params.id!=='0'"-->
                                                <input class="arrbox_ip" v-model="val" placeholder="输入后回车"
                                                       style="width: 90%;" @keyup.enter="addlabel"></input>
                                            </div>
                                        </FormItem>
                                        <FormItem label="消息状态：">
                                            <RadioGroup v-model="formValidate.status">
                                                <Radio :label=1>启用</Radio>
                                                <Radio :label=0>禁用</Radio>
                                            </RadioGroup>
                                        </FormItem>
                                        <FormItem label="消息类型：" prop="type">
                                            <Select v-model="formValidate.type" placeholder="请选择规则状态"
                                                    style="width: 90%;" @on-change="RuleFactor(formValidate.type)">
                                                <Option value="text">文字消息</Option>
                                                <Option value="image">图片消息</Option>
                                                <Option value="news">图文消息</Option>
                                                <Option value="voice">声音消息</Option>
                                            </Select>
                                        </FormItem>
                                        <FormItem label="消息内容：" prop="content" v-if="formValidate.type === 'text'">
                                            <textarea v-model="formValidate.data.content" placeholder="请填写消息内容"
                                                   style="width: 90%;"></textarea>
                                        </FormItem>
                                        <FormItem label="选取图文："  v-if="formValidate.type === 'news'">
                                            <Button type="info" @click="changePic">选择图文消息</Button>
                                        </FormItem>
                                        <FormItem :label="formValidate.type === 'image'?'图片地址：':'语音地址：'" prop="src"
                                                  v-if="formValidate.type === 'image' || formValidate.type === 'voice'">
                                            <div class="acea-row row-middle">
                                                <Input readonly="readonly" placeholder="default size"
                                                       style="width: 75%;" class="mr15"
                                                       v-model="formValidate.data.src"/>
                                                <Upload :show-upload-list="false" :action="fileUrl"
                                                        :on-success="handleSuccess"
                                                        :format="formValidate.type === 'image' ? formatImg:formatVoice"
                                                        :max-size="2048"
                                                        :headers="header"
                                                        :on-format-error="handleFormatError"
                                                        :on-exceeded-size="handleMaxSize"
                                                        class="mr20" style="margin-top: 1px">
                                                    <Button type="primary">上传</Button>
                                                </Upload>
                                            </div>
                                            <span v-show="formValidate.type === 'image'">文件最大2Mb，支持bmp/png/jpeg/jpg/gif格式</span>
                                            <span v-show="formValidate.type === 'voice'">文件最大2Mb，支持mp3/wma/wav/amr格式,播放长度不超过60s</span>
                                        </FormItem>
                                    </Form>

                                </div>
                            </Col>
                            <Col :span="24">
                                <div class="acea-row row-center">
                                    <Button type="primary" class="mr20"
                                            @click="submenus('formValidate')">保存并发布
                                    </Button>
                                </div>
                            </Col>
                        </Col>
                    </Col>
                </Col>
            </Row>
        </Card>

        <!--图文消息 -->
        <Modal v-model="modals" scrollable title="发送消息" width="1200" height="800" footer-hide class="modelBox">
            <news-category v-if="modals" @getCentList="getCentList"  :scrollerHeight="scrollerHeight" :contentTop="contentTop" :contentWidth="contentWidth" :maxCols="maxCols"></news-category>
        </Modal>
    </div>
</template>

<script>
    import Setting from '@/setting'
    import { replyApi, keywordsinfoApi } from '@/api/app'
    // import { mapActions } from 'vuex'
    import newsCategory from '@/components/newsCategory/index'
    import { getCookies } from '@/libs/util'
    export default {
        name: 'follow',
        components: { newsCategory },
        data () {
            const validateContent = (rule, value, callback) => {
                if (this.formValidate.type === 'text') {
                    if (this.formValidate.data.content === '') {
                        callback(new Error('请填写规则内容'))
                    } else {
                        callback()
                    }
                }
            }
            const validateSrc = (rule, value, callback) => {
                if (this.formValidate.type === 'image' && this.formValidate.data.src === '') {
                    callback(new Error('请上传'))
                } else {
                    callback()
                }
            }
            const validateVal = (rule, value, callback) => {
                if (this.labelarr.length === 0) {
                    callback(new Error('请输入后回车'))
                } else {
                    callback()
                }
            }
            return {
                delfromData: {},
                isShow: false,
                maxCols: 4,
                scrollerHeight: '600',
                contentTop: '130',
                contentWidth: '98%',
                modals: false,
                val: '',
                formatImg: ['jpg', 'jpeg', 'png', 'bmp', 'gif'],
                formatVoice: ['mp3', 'wma', 'wav', 'amr'],
                header: {},
                formValidate: {
                    status: 1,
                    type: '',
                    key: this.$route.params.key || '',
                    data: {
                        content: '',
                        src: '',
                        list: []
                    },
                    id: 0
                },
                fileUrl: Setting.apiBaseURL + '/file/upload/1',
                ruleValidate: {
                    val: [
                        { required: true, validator: validateVal, trigger: 'change' }
                    ],
                    type: [
                        { required: true, message: '请选择消息类型', trigger: 'change' }
                    ],
                    content: [
                        { required: true, validator: validateContent, trigger: 'blur' }
                    ],
                    src: [
                        { required: true, validator: validateSrc, trigger: 'change' }
                    ]
                },
                labelarr: []
            }
        },
        watch: {
            $route (to, from) {
                if (this.$route.params.key || this.$route.params.id !== '0') {
                    this.formValidate.key = this.$route.params.key
                    this.details()
                } else {
                    this.labelarr = []
                    this.$refs['formValidate'].resetFields()
                }
            }
        },
        computed: {
            imgUrl () {
                const search = '/adminapi/'
                const start = Setting.apiBaseURL.indexOf(search)
                return Setting.apiBaseURL.substring(0, start)// 截取字符串
            }
        },
        mounted () {
            this.getToken()
            if (this.$route.params.key || (this.$route.params.id && this.$route.params.id !== '0')) {
                this.details()
            }
        },
        methods: {
            getCentList (val) {
                this.formValidate.data.list = val.new
                this.modals = false
            },
            addlabel () {
                let count = this.labelarr.indexOf(this.val)
                if (count === -1) {
                    this.labelarr.push(this.val)
                }
                this.val = ''
            },
            handleClose (event, name) {
                const index = this.labelarr.indexOf(name)
                this.labelarr.splice(index, 1)
            },
            // 详情
            details () {
                let url = ''
                let data = {}
                if (this.$route.params.id) {
                    url = 'app/wechat/keyword/' + this.$route.params.id
                    data = {}
                } else {
                    url = 'app/wechat/reply'
                    data = {
                        key: {
                            key: this.formValidate.key
                        }
                    }
                }
                keywordsinfoApi(url, data).then(async res => {
                    let info = res.data.info || {}
                    let data = info.data || {}
                    this.formValidate = {
                        status: info.status,
                        type: info.type,
                        key: info.key,
                        data: {
                            content: data.content,
                            src: data.src,
                            list: data.list
                        },
                        id: info.id
                    }
                    if (this.$route.params.id) {
                        this.labelarr = this.formValidate.key.split(',') || []
                    };
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 选择图文
            changePic () {
                this.modals = true
            },
            // 下拉选择
            RuleFactor (type) {
                switch (type) {
                case 'text':
                    this.formValidate.data.src = ''
                    this.formValidate.data.list = []
                    break
                case 'news':
                    this.formValidate.data.src = ''
                    this.formValidate.data.content = ''
                    break
                default:
                    this.formValidate.data.list = []
                    this.formValidate.data.content = ''
                    this.formValidate.data.src = ''
                }
                // this.$refs['formValidate'].resetFields();
            },
            // 上传头部token
            getToken () {
                this.header['Authori-zation'] = 'Bearer ' + getCookies('token')
            },
            // 上传成功
            handleSuccess (res, file) {
                if (res.status === 200) {
                    this.formValidate.data.src = res.data.src
                    this.$Message.success(res.msg)
                } else {
                    this.$Message.error(res.msg)
                }
            },
            handleFormatError (file) {
                if (this.formValidate.type === 'image') {
                    this.$Message.warning('请上传bmp/png/jpeg/jpg/gif格式的图片')
                } else {
                    this.$Message.warning('请上传mp3/wma/wav/amr格式的语音')
                }
            },
            handleMaxSize (file) {
                this.$Message.warning('请上传文件2M以内的文件')
            },
            // 保存
            submenus (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        let data = {}
                        if (this.$route.params.id) {
                            this.formValidate.key = this.labelarr.join(',')
                            data = {
                                url: 'app/wechat/keyword/' + this.$route.params.id,
                                key: this.formValidate
                            }
                        } else {
                            data = {
                                url: 'app/wechat/keyword/' + this.formValidate.id,
                                key: this.formValidate
                            }
                        }
                        replyApi(data).then(async res => {
                            this.operation()
                            this.$Message.success(res.msg)
                        }).catch(res => {
                            this.$Message.error(res.msg)
                        })
                    } else {
                        return false
                    }
                })
            },
            // 保存成功操作
            operation () {
                if (this.$route.params.id && this.$route.params.id === '0') {
                    this.$Modal.confirm({
                        title: '提示',
                        content: '<p>是否继续添加？</p>',
                        okText: '是',
                        cancelText: '否',
                        loading: true,
                        onOk: () => {
                            setTimeout(() => {
                                this.$Modal.remove()
                                this.labelarr = []
                                this.val = ''
                                this.$refs['formValidate'].resetFields()
                            }, 1000)
                        },
                        onCancel: () => {
                            setTimeout(() => {
                                this.$Modal.remove()
                                this.$router.push({ path: '/admin/app/wechat/reply/keyword' })
                            }, 500)
                        }
                    })
                } else if (this.$route.params.id && this.$route.params.id !== '0') {
                    this.$Modal.remove()
                    this.$router.push({ path: '/admin/app/wechat/reply/keyword' })
                }
            }
        }
    }
</script>

<style scoped lang="stylus">
    * {
        -moz-user-select: none; /*火狐*/
        -webkit-user-select: none; /*webkit浏览器*/
        -ms-user-select: none; /*IE10*/
        -khtml-user-select: none; /*早期浏览器*/
        user-select: none;
    }

    .arrbox {
        background-color: white;
        font-size: 12px;
        border: 1px solid #dcdee2;
        border-radius: 6px;
        margin-bottom: 0px;
        padding:0 5px;
        text-align: left;
        box-sizing border-box;
        width: 90%;
    }

    .arrbox_ip {
        font-size: 12px;
        border: none;
        box-shadow: none;
        outline: none;
        background-color: transparent;
        padding: 0;
        margin: 0;
        width: auto !important;
        max-width: inherit;
        min-width: 80px;
        vertical-align: top;
        height: 30px;
        color: #34495e;
        margin: 2px;
        line-height: 30px;
    }

    .left {
        min-width: 390px;
        min-height: 550px;
        position: relative;
        padding-left: 40px;
    }

    .top {
        position: absolute;
        top: 0px;
    }

    .bottom {
        position: absolute;
        bottom: 0px;
    }

    .centent {
        background: #F4F5F9;
        min-height: 438px;
        position: absolute;
        top: 63px;
        width: 320px;
        padding: 15px;
        box-sizing: border-box;
    }

    .right {
        background: #fff;
        min-height: 300px;
    }

    .box-content {
        position: relative;
        max-width: 60%;
        min-height: 40px;
        margin-left: 15px;
        padding: 10px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        word-break: break-all;
        word-wrap: break-word;
        line-height: 1.5;
        border-radius: 5px;
    }

    .box-content_pic {
        width: 100%;
    }

    .box-content_pic img {
        width: 100%;
        height: auto;
    }

    .box-content:before {
        content: '';
        position: absolute;
        left: -13px;
        top: 11px;
        display: block;
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 10px solid #ccc;
        -webkit-transform: rotate(90deg);
        transform: rotate(90deg);
    }

    .box-content:after {
        content: '';
        content: '';
        position: absolute;
        left: -12px;
        top: 11px;
        display: block;
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 10px solid #f5f5f5;
        -webkit-transform: rotate(90deg);
        transform: rotate(90deg);
    }

    .time-wrapper {
        margin-bottom: 10px;
        text-align: center;
    }

    .time {
        display: inline-block;
        color: #f5f5f5;
        background: rgba(0, 0, 0, .3);
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
    }

    .text-box {
        display: flex;
    }

    .avatar {
        width: 40px;
        height: 40px;
    }

    .avatar img {
        width: 100%;
        height: 100%;
    }
    .modelBox
       >>> .ivu-modal-body
         padding 0 16px 16px 16px !important
    .news_pic
        width: 100%
        height: 150px
        overflow: hidden
        position: relative
        background-size: 100%
        background-position: center center
        border-radius: 5px 5px 0 0
        padding 10px
        box-sizing border-box
        display flex
        flex-direction column
        align-items: flex-end
    .news_sp
        font-size 12px
        color #000000
        background #fff
        width 100%
        height 38px
        line-height 38px
        padding 0 12px
        box-sizing border-box
        display: block;
    .news_cent
        width 100%
        height auto
        background #fff
        border-top: 1px dashed #eee;
        display flex
        padding 10px
        box-sizing border-box
        justify-content: space-between
        .news_sp1
            font-size 12px
            color #000000
            width: 71%;
        .news_cent_img
            width 81px
            height 46px
            border-radius 6px
            overflow hidden
            img
                width 100%
                height 100%
</style>
