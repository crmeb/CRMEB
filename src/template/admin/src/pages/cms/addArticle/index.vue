<template>
    <div class="article-manager">
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <router-link :to="{path:'/admin/cms/article/index'}"><Button icon="ios-arrow-back" size="small"  class="mr20">返回</Button></router-link>
            <span class="ivu-page-header-title mr20" v-text="$route.params.id?'编辑文章':'添加文章'"></span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form class="form" ref="formValidate" :model="formValidate" :rules="ruleValidate"  :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <div class="goodsTitle acea-row">
                    <div class="title">文章信息</div>
                </div>
                <Row :gutter="24" type="flex">
                    <Col v-bind="grid" class="mr50">
                        <FormItem label="标题：" prop="title" label-for="title">
                            <Input v-model="formValidate.title" placeholder="请输入" element-id="title" style="width: 90%"/>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid" class="mr50">
                        <FormItem label="作者：" prop="author" label-for="author">
                            <Input v-model="formValidate.author" placeholder="请输入" element-id="author" style="width: 90%"/>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid" class="mr50">
                        <FormItem label="文章分类："  label-for="cid" prop="cid">
                            <div class="perW90">
                                <Select v-model="formValidate.cid">
                                    <Option v-for="item in treeData" :value="item.id" :key="item.id">{{ item.title }}</Option>
                                </Select>
                            </div>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid" class="mr50">
                        <FormItem label="文章简介：" prop="synopsis" label-for="synopsis">
                            <Input v-model="formValidate.synopsis" type="textarea" placeholder="请输入"  style="width: 90%"/>
                        </FormItem>
                    </Col>
                    <Col v-bind="grid" class="mr50">
                        <FormItem label="图文封面：" prop="image_input">
                            <div class="picBox" @click="modalPicTap('单选')">
                                <div class="pictrue" v-if="formValidate.image_input"><img :src="formValidate.image_input"></div>
                                <div class="upLoad acea-row row-center-wrapper"   v-else>
                                    <Icon type="ios-camera-outline" size="26" class="iconfont"/>
                                </div>
                            </div>
                        </FormItem>
                    </Col>
                </Row>
                <div class="goodsTitle acea-row">
                    <div class="title">文章内容</div>
                </div>
                <FormItem label="文章内容：" prop="content">
                    <vue-ueditor-wrap v-model="formValidate.content"  @beforeInit="addCustomDialog"  :config="myConfig"  style="width: 90%;"></vue-ueditor-wrap>
                </FormItem>
                <div class="goodsTitle acea-row">
                    <div class="title">其他设置</div>
                </div>
                <Row :gutter="24" type="flex">
<!--                    <Col span="24">-->
<!--                        <FormItem label="原文链接：">-->
<!--                            <Input v-model="formValidate.url" placeholder="请输入" element-id="url" style="width: 60%"/>-->
<!--                        </FormItem>-->
<!--                    </Col>-->
                    <Col span="24">
                        <FormItem label="banner显示："  label-for="is_banner">
                            <RadioGroup v-model="formValidate.is_banner" element-id="is_banner">
                                <Radio :label="1" class="radio">显示</Radio>
                                <Radio :label="0">不显示</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="热门文章："  label-for="is_hot">
                            <RadioGroup v-model="formValidate.is_hot" element-id="is_hot">
                                <Radio :label=1 class="radio">显示</Radio>
                                <Radio :label=0>不显示</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                </Row>
                <Button type="primary" class="submission" @click="onsubmit('formValidate')">提交</Button>
            </Form>
            <Modal v-model="modalPic" width="60%" scrollable  footer-hide closable title='上传商品图' :mask-closable="false" :z-index="1">
                <uploadPictures :isChoice="isChoice" @getPic="getPic" :gridBtn="gridBtn" :gridPic="gridPic" v-if="modalPic"></uploadPictures>
            </Modal>
        </Card>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import uploadPictures from '@/components/uploadPictures'
    import VueUeditorWrap from 'vue-ueditor-wrap'
    import { cmsAddApi, createApi, categoryListApi } from '@/api/cms'
    export default {
        name: 'addArticle',
        components: { uploadPictures, VueUeditorWrap },
        data () {
            const validateUpload = (rule, value, callback) => {
                if (this.formValidate.image_input) {
                    callback()
                } else {
                    callback(new Error('请上传图文封面'))
                }
            }
            const validateUpload2 = (rule, value, callback) => {
                if (!this.formValidate.cid) {
                    callback(new Error('请选择文章分类'))
                } else {
                    callback()
                }
            }
            return {
                dialog: {},
                isChoice: '单选',
                grid: {
                    xl: 8,
                    lg: 8,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
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
                },
                loading: false,
                formValidate: {
                    id: 0,
                    title: '',
                    author: '',
                    image_input: '',
                    content: '',
                    synopsis: '',
                    url: '',
                    is_hot: 0,
                    is_banner: 0,
                    cid: ''
                },
                ruleValidate: {
                    title: [
                        { required: true, message: '请输入标题', trigger: 'blur' }
                    ],
                    cid: [
                        { required: true, validator: validateUpload2, trigger: 'change', type: 'number' }
                    ],
                    image_input: [
                        { required: true, validator: validateUpload, trigger: 'change' }
                    ],
                    content: [
                        { required: true, message: '请输入文章内容', trigger: 'change' }
                    ]
                },
                value: '',
                modalPic: false,
                template: false,
                treeData: [],
                formValidate2: {
                    type: 1
                },
                myConfig: {
                    autoHeightEnabled: false, // 编辑器不自动被内容撑高
                    initialFrameHeight: 500, // 初始容器高度
                    initialFrameWidth: '100%', // 初始容器宽度
                    UEDITOR_HOME_URL: '/admin/UEditor/',
                    serverUrl: ''
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
        watch: {
            $route (to, from) {
                if (this.$route.params.id) {
                    this.getDetails()
                } else {
                    this.formValidate = {
                        id: 0,
                        title: '',
                        author: '',
                        image_input: '',
                        content: '',
                        synopsis: '',
                        url: '',
                        is_hot: 0,
                        is_banner: 0
                    }
                }
            }
        },
        methods: {
            // ...mapActions('admin/page', [
            //   'close'
            // ]),
            getContent (val) {
                this.formValidate.content = val
                console.log(this.formValidate.content)
            },
            // 选择图片
            modalPicTap () {
                this.modalPic = true
            },
            // 选中图片
            getPic (pc) {
                this.formValidate.image_input = pc.att_dir
                this.modalPic = false
            },
            // 分类
            getClass () {
                categoryListApi(this.formValidate2).then(async res => {
                    this.treeData = res.data
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 提交数据
            onsubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        cmsAddApi(this.formValidate).then(async res => {
                            this.$Message.success(res.msg)
                            setTimeout(() => {
                                this.$router.push({ path: '/admin/cms/article/index' })
                            }, 500)
                        }).catch(res => {
                            this.$Message.error(res.msg)
                        })
                    } else {
                        return false
                    }
                })
            },
            // 文章详情
            getDetails () {
                createApi(this.$route.params.id ? this.$route.params.id : 0).then(async res => {
                    let data = res.data
                    let news = data.info
                    this.formValidate = {
                        id: news.id,
                        title: news.title,
                        author: news.author,
                        image_input: news.image_input,
                        content: news.content,
                        synopsis: news.synopsis,
                        url: news.url,
                        is_hot: news.is_hot,
                        is_banner: news.is_banner,
                        cid: news.cid
                    }
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            // 添加自定义弹窗
            addCustomDialog (editorId) {
                window.UE.registerUI('test-dialog', function (editor, uiName) {
                    // 创建 dialog
                    let dialog = new window.UE.ui.Dialog({
                        // 指定弹出层中页面的路径，这里只能支持页面，路径参考常见问题 2
                        iframeUrl: '/admin/widget.images/index.html?fodder=dialog',
                        // 需要指定当前的编辑器实例
                        editor: editor,
                        // 指定 dialog 的名字
                        name: uiName,
                        // dialog 的标题
                        title: '上传图片',
                        // 指定 dialog 的外围样式
                        cssRules: 'width:1200px;height:500px;padding:20px;'
                    })
                    this.dialog = dialog
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
            }
        },
        mounted () {
            if (this.$route.params.id) {
                this.getDetails()
            }
        },
        created () {
            this.getClass()
        }
    }
</script>
<style scoped>
    .picBox {
        display: inline-block;
        cursor: pointer;
    }

    .form .goodsTitle {
        border-bottom: 1px solid rgba(0, 0, 0, 0.09);
        margin-bottom: 25px;
    }

    .form .goodsTitle ~ .goodsTitle {
        margin-top: 20px;
    }

    .form .goodsTitle .title {
        border-bottom: 2px solid #1890FF;
        padding: 0 8px 12px 5px;
        color: #000;
        font-size: 14px;
    }

    .form .goodsTitle .icons {
        font-size: 15px;
        margin-right: 8px;
        color: #999;
    }

    .form .add {
        font-size: 12px;
        color: #1890FF;
        padding: 0 12px;
        cursor: pointer;
    }

    .form .radio {
        margin-right: 20px;
    }

    .form .submission {
        width: 10%;
        margin-left: 27px;
    }

    .form .upLoad {
        width: 58px;
        height: 58px;
        line-height: 58px;
        border: 1px dotted rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        background: rgba(0, 0, 0, 0.02);
    }

    .form .iconfont {
        color: #898989;
    }

    .form .pictrue {
        width: 60px;
        height: 60px;
        border: 1px dotted rgba(0, 0, 0, 0.1);
        margin-right: 10px;
    }

    .form .pictrue img {
        width: 100%;
        height: 100%;
    }

    .Modals .address {
        width: 90%;
    }

    .Modals .address .iconfont {
        font-size: 20px;
    }
</style>
