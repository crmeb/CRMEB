<template>
    <div class="newsBox">
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <router-link :to="{path:'/admin/app/wechat/news_category/index'}"><Button icon="ios-arrow-back" size="small"  class="mr20" v-show="$route.params.id">返回</Button></router-link>
            <span class="ivu-page-header-title mr20" v-text="$route.meta.title"></span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="save_from ivu-mt">
            <Row type="flex" :gutter="24">
                <Col :xl="6" :lg="6" :md="12" :sm="24" :xs="24">
<!--                    v-if="list.length!=0"-->
                    <div v-for="(item, i) in list" :key="i">
                        <div  v-if="i === 0"  @click="onSubSave(i)" :class="{ checkClass:i === current}" @mouseenter="isDel=true"  @mouseleave="isDel=false">
                            <div class="news_pic"  :style="{backgroundImage: 'url(' + (item.image_input ? item.image_input : baseImg) + ')',backgroundSize:'100% 100%'}">
                                <Button type="error" shape="circle" icon="md-trash" @click="del(i)" v-show="isDel" ></Button>
                            </div>
                            <span class="news_sp">{{item.title}}</span>
                        </div>
                        <div class="news_cent" v-else  @click="onSubSave(i)" :class="{ checkClass:i === current}">
                            <span class="news_sp1">{{item.title}}</span>
                            <div class="news_cent_img ivu-mr-8"><img :src="item.image_input ? item.image_input : baseImg"></div>
                            <Button type="error" shape="circle" icon="md-trash" @click="del(i)"></Button>
                        </div>
                    </div>
                    <div class="acea-row row-center-wrapper">
                        <Button  icon="ios-download-outline" class="mt20" type="primary" @click="handleAdd">添加图文</Button>
                    </div>
                </Col>
                <Col :xl="18" :lg="18" :md="12" :sm="24" :xs="24">
                    <Form class="saveForm" ref="saveForm" :model="saveForm" :label-width="labelWidth" :rules="ruleValidate"
                          :label-position="labelPosition" @submit.native.prevent>
                        <Row :gutter="24" type="flex">
                            <Col span="24" class="ml40">
                                <FormItem label="标题：" prop="title">
                                    <Input style="width: 60%;" v-model="saveForm.title" type="text"
                                           placeholder="请输入文章标题"/>
                                </FormItem>
                            </Col>
                            <Col span="24" class="ml40">
                                <FormItem label="作者：" prop="author">
                                    <Input style="width: 60%;" v-model="saveForm.author" type="text"
                                           placeholder="请输入作者名称"/>
                                </FormItem>
                            </Col>
                            <Col span="24" class="ml40">
                                <FormItem label="摘要：" prop="synopsis">
                                    <Input style="width: 60%;" v-model="saveForm.synopsis" type="textarea"
                                           placeholder="请输入摘要"/>
                                </FormItem>
                            </Col>
                            <Col span="24" class="ml40">
                                <FormItem label="图文封面：" prop="image_input">
                                    <div class="picBox" @click="modalPicTap('单选')">
                                        <div class="pictrue" v-if="saveForm.image_input"><img :src="saveForm.image_input"></div>
                                        <div class="upLoad acea-row row-center-wrapper"   v-else>
                                            <Icon type="ios-camera-outline" size="26"/>
                                        </div>
                                    </div>
                                </FormItem>
                                <FormItem label="正文：" prop="content">
                                    <ueditor-wrap v-model="saveForm.content" :config="myConfig"  @beforeInit="addCustomDialog"  style="width: 90%;"></ueditor-wrap>
                                </FormItem>
                            </Col>
                            <Col span="24" class="ml40">
                                <FormItem>
                                    <Button type="primary" class="submission" @click="subFrom('saveForm')">提交</Button>
                                </FormItem>
                            </Col>
                            <Modal v-model="modalPic" width="60%" scrollable  footer-hide closable title='上传文章图' :mask-closable="false" :z-index="1">
                                <uploadPictures :isChoice="isChoice" @getPic="getPic" :gridBtn="gridBtn" :gridPic="gridPic" v-if="modalPic"></uploadPictures>
                            </Modal>
                        </Row>
                    </Form>
                </Col>
            </Row>
        </Card>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import UeditorWrap from 'vue-ueditor-wrap'
    import uploadPictures from '@/components/uploadPictures'
    import { wechatNewsAddApi, wechatNewsInfotApi } from '@/api/app'
    export default {
        name: 'newsCategorySave',
        components: { uploadPictures, UeditorWrap },
        watch: {
            $route (to, from) {
                if (this.$route.params.id !== '0') {
                    this.info()
                } else {
                    this.list = [
                        {
                            title: '',
                            author: '',
                            synopsis: '',
                            image_input: '',
                            content: '',
                            id: 0
                        }
                    ]
                    this.saveForm = this.list[this.current]
                }
            }
        },
        data () {
            const validateUpload = (rule, value, callback) => {
                if (this.saveForm.image_input) {
                    callback()
                } else {
                    callback(new Error('请上传图文封面'))
                }
            }
            return {
                myConfig: {
                    autoHeightEnabled: false, // 编辑器不自动被内容撑高
                    initialFrameHeight: 500, // 初始容器高度
                    initialFrameWidth: '100%', // 初始容器宽度
                    UEDITOR_HOME_URL: '/admin/UEditor/',
                    serverUrl: ''
                },
                ruleValidate: {
                    title: [
                        { required: true, message: '请输入标题', trigger: 'blur' }
                    ],
                    author: [
                        { required: true, message: '请输入作者', trigger: 'blur' }
                    ],
                    image_input: [
                        { required: true, validator: validateUpload, trigger: 'change' }
                    ],
                    content: [
                        { required: true, message: '请输入正文', trigger: 'change' }
                    ],
                    synopsis: [
                        { required: true, message: '请输入文章摘要', trigger: 'blur' }
                    ]
                },
                isChoice: '单选',
                dragging: null,
                isDel: false,
                msg: '',
                count: [],
                baseImg: require('../../../../assets/images/bjt.png'),
                saveForm: {
                    title: '',
                    author: '',
                    synopsis: '',
                    image_input: '',
                    content: '',
                    id: 0
                },
                current: 0,
                list: [
                    {
                        title: '',
                        author: '',
                        synopsis: '',
                        image_input: '',
                        content: '',
                        id: 0
                    }
                ],
                uploadList: [],
                modalPic: false,
                gridPic: {
                    xl: 6,
                    lg: 8,
                    md: 12,
                    sm: 12,
                    xs: 12
                },
                gridBtn: {
                    xl: 4,
                    lg: 8,
                    md: 8,
                    sm: 8,
                    xs: 8
                }
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 120
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right'
            }
        },
        mounted () {
            if (this.$route.params.id !== '0') {
                this.info()
            } else {
                this.saveForm = this.list[this.current]
            }
        },
        methods: {
            // 添加自定义弹窗
            addCustomDialog (editorId) {
                window.UE.registerUI('test-dialog', function (editor, uiName) {
                    let dialog = new window.UE.ui.Dialog({
                        iframeUrl: '/admin/widget.images/index.html?fodder=dialog',
                        editor: editor,
                        name: uiName,
                        title: '上传图片',
                        cssRules: 'width:1200px;height:500px;padding:20px;'
                    })
                    this.dialog = dialog
                    // 参考上面的自定义按钮
                    var btn = new window.UE.ui.Button({
                        name: 'dialog-button',
                        title: '上传图片',
                        cssRules: `background-image: url(../../../assets/images/icons.png);background-position: -726px -77px;`,
                        onclick: function () {
                            // 渲染dialog
                            dialog.render()
                            dialog.open()
                        }
                    })

                    return btn
                }, 37)
            },
            // 点击图文封面
            modalPicTap () {
                this.modalPic = true
            },
            // 获取图片信息
            getPic (pc) {
                this.saveForm.image_input = pc.att_dir
                this.modalPic = false
            },
            // 添加图文按钮
            handleAdd () {
                if (!this.check()) return false
                let obj = {
                    'title': '',
                    'author': '',
                    'synopsis': '',
                    'image_input': '',
                    'content': '',
                    'id': 0
                }
                this.list.push(obj)
            },
            // 点击模块
            onSubSave (i) {
                this.current = i
                this.list.map((item, index) => {
                    /* eslint-disable */
                    if (index === this.current) return this.saveForm = this.list[this.current];
                })
            },
            // 删除
            del (i) {
                if (i === 0) {
                    this.$Message.warning('不能再删除了');
                } else {
                    this.list.splice(i, 1);
                    this.saveForm = {};
                }
            },
            // 详情
            info () {
                wechatNewsInfotApi(this.$route.params.id).then(async res => {
                    let info = res.data.info;
                    this.list = info.new;
                     this.saveForm = this.list[this.current];
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 提交数据
            subFrom (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        let data = {
                            id:this.$route.params.id || 0,
                            list:this.list
                        }
                        wechatNewsAddApi(data).then(async res => {
                            this.$Message.success(res.msg);
                            setTimeout(() => {
                                this.$router.push({ path: '/admin/app/wechat/news_category/index' });
                            }, 500);

                        }).catch(res => {
                            this.$Message.error(res.msg);
                        })
                    } else {
                        return false;
                    }
                })
            },
            check () {
                for (let index in this.list){
                    if(!this.list[index].title){
                        this.$Message.warning('请输入文章的标题');
                        return false;
                    }
                    else if(!this.list[index].author){
                        this.$Message.warning('请输入文章的作者');
                        return false;
                    }
                    else if(!this.list[index].synopsis){
                        this.$Message.warning('请输入文章的摘要');
                        return false;
                    }
                    else if(!this.list[index].image_input){
                        this.$Message.warning('请输入文章的图文封面');
                        return false;
                    }
                    else if(!this.list[index].content){
                        this.$Message.warning('请输入文章的内容');
                        return false;
                    }else{
                        return true
                    }
                }
                // if(!this.saveForm.title){
                //     this.$Message.warning('请输入文章的标题');
                //     return false;
                // }
                // else if(!this.saveForm.author){
                //     this.$Message.warning('请输入文章的作者');
                //     return false;
                // }
                // else if(!this.saveForm.synopsis){
                //     this.$Message.warning('请输入文章的摘要');
                //     return false;
                // }
                // else if(!this.saveForm.image_input){
                //     this.$Message.warning('请输入文章的图文封面');
                //     return false;
                // }
                // else if(!this.saveForm.content){
                //     this.$Message.warning('请输入文章的内容');
                //     return false;
                // }else{
                //     return true
                // }

            }
        }
    }
</script>

<style scoped lang="stylus">
    .newsBox
        >>>.ivu-global-footer
            dispaly:none !important
    .demo-upload-list{
        display: inline-block;
        width: 60px;
        height: 60px;
        text-align: center;
        line-height: 60px;
        border: 1px solid transparent;
        border-radius: 4px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 1px rgba(0,0,0,.2);
        margin-right: 15px;
        position: relative;
    }
    .btndel{
        position absolute
        z-index 111
        width 20px !important
        height 20px !important
        left: 46px;
        top: -4px;
    }
    .demo-upload-list img{
        width: 100%;
        height: 100%;
    }
    .demo-upload-list-cover{
        display: none;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,.6);
    }
    .demo-upload-list:hover .demo-upload-list-cover{
        display: block;
    }
    .demo-upload-list-cover i{
        color: #fff;
        font-size: 20px;
        cursor: pointer;
        margin: 0 2px;
    }
    .save_from >>> .ivu-btn-error
        width:24px !important;
        height:24px !important;
        background:#FFF !important;
        color: #999 !important;
        border:1px solid  #eee !important;
    .save_from >>>.ivu-btn-error:hover
        background:#FF5D5F !important;
        border:1px solid #fff !important;
        color: #fff !important;
    .picBox
        display: inline-block
        cursor: pointer
    .pictrue
        width:60px;
        height:60px;
        border:1px dotted rgba(0,0,0,0.1);
        margin-right:10px;
    .pictrue img
        width:100%;height:100%;
    .upLoad
        width: 58px;
        height:58px;
        line-height: 58px;
        border:1px dotted rgba(0,0,0,0.1);
        border-radius:4px;background:rgba(0,0,0,0.02);
    .checkClass
        border 1px dashed #0091FF !important
    .checkClass2
        border 1px solid #0091FF !important
    .submission
        width:10%;margin-left:27px;
    .cover
        width 60px
        height 60px
        img
          width 100%
          height 100%
    .Refresh
        font-size 12px
        color #1890FF
        cursor pointer
        line-height: 35px
        display: inline-block

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
        border-bottom: 1px dashed #eee;
    .news_cent
        width 100%
        height auto
        background #fff
        border-bottom: 1px dashed #eee;
        display flex
        padding 10px
        box-sizing border-box
        justify-content: space-between
        align-items center
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
