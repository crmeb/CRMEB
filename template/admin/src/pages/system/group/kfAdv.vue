<template>
    <div class="article-manager">

        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form class="form" ref="formValidate" :model="formValidate" :rules="ruleValidate" :label-width="labelWidth"
                  :label-position="labelPosition" @submit.native.prevent>
                <div class="goodsTitle acea-row">
                    <div class="title">客服聊天页面展示：</div>
                </div>
                <FormItem label="展示内容：" prop="content">
                    <vue-ueditor-wrap v-model="formValidate.content" @beforeInit="addCustomDialog" :config="myConfig"
                                      style="width: 90%;"></vue-ueditor-wrap>
                </FormItem>


                <Button type="primary" class="submission" @click="onsubmit('formValidate')">提交</Button>
            </Form>
        </Card>
    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import VueUeditorWrap from 'vue-ueditor-wrap'
    import {getKfAdv, setKfAdv} from '@/api/system'

    export default {
        name: 'kfAdv',
        components: {VueUeditorWrap},
        data() {
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
                    content: '',
                },
                ruleValidate: {
                    // content: [
                    //     {required: true, message: '请输入内容', trigger: 'change'}
                    // ]
                },
                value: '',
                modalPic: false,
                template: false,
                treeData: [],
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
            labelWidth() {
                return this.isMobile ? undefined : 120
            },
            labelPosition() {
                return this.isMobile ? 'top' : 'right'
            }
        },
        watch: {
            $route(to, from) {
                this.getKfAdv()
            }
        },
        methods: {
            // ...mapActions('admin/page', [
            //   'close'
            // ]),
            getContent(val) {
                this.formValidate.content = val
                console.log(this.formValidate.content)
            },
            // 提交数据
            onsubmit(name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        setKfAdv(this.formValidate).then(async res => {
                            this.$Message.success(res.msg)
                        }).catch(res => {
                            this.$Message.error(res.msg)
                        })
                    } else {
                        return false
                    }
                })
            },
            //详情
            getKfAdv() {
                getKfAdv().then(async res => {
                    let data = res.data
                    this.formValidate = {
                        content: data.content,
                    }
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
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
        mounted() {
            this.getKfAdv()
        },
        created() {
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
