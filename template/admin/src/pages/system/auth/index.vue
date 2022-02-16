<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <div class="auth acea-row row-between-wrapper">
                <div class="acea-row row-middle">
                    <Icon type="ios-bulb-outline" class="iconIos blue" v-if="status === 1"/>
                    <Icon type="ios-bulb-outline" class="iconIos" v-else/>
                    <!--<div class="text" v-if="status === -1 || status === -9">-->
                        <!--<div>体验时间剩余 {{dayNum}}天</div>-->
                        <!--<div class="code">到期后后台将不能正常使用，如果您对我们的系统满意，请支持正版！</div>-->
                    <!--</div>-->
                    <!--<div class="text" v-else-if="status === 2">-->
                        <!--<div>体验时间剩余 {{dayNum}}天</div>-->
                        <!--<div class="code red">审核未通过</div>-->
                    <!--</div>-->
                    <div class="text" v-if="status === 1">
                        <div>商业授权</div>
                        <div class="code">授权码：{{ authCode }}</div>
                    </div>
                    <div class="text" v-else>
                        <div>商业授权</div>
                        <div class="code">授权码：未授权</div>
                    </div>
                    <!--<div class="text" v-else-if="status === 0">-->
                        <!--<div>体验时间剩余 {{dayNum}}天</div>-->
                        <!--<div class="code blue">授权申请已提交，请等待审核</div>-->
                    <!--</div>-->
                </div>
                <Button @click="toCrmeb()" v-if="status === 1">进入官网</Button>
                <Button type="primary" @click="isTemplate = true" v-else>申请授权</Button>
                <!--<Button type="primary" @click="isTemplate = true" v-else-if="status === -1 || status === -9">申请授权</Button>-->
                <!--<Button type="primary" @click="isTemplate = true" v-else-if="status === 2">重新申请</Button>-->
                <!--<Button class="grey" v-else-if="status === 0">审核中</Button>-->
            </div>
        </Card>
        <Modal v-model="isTemplate" scrollable footer-hide closable title="申请商业授权" :z-index="1" width="640"
               @on-cancel="cancel">
            <div class="article-manager">
                <Card :bordered="false" dis-hover>
                    <Form ref="formItem" :model="formItem" :label-width="labelWidth" :label-position="labelPosition"
                          :rules="ruleValidate" @submit.native.prevent>
                        <Row type="flex" :gutter="24">
                            <Col span="24">
                                <Col>
                                    <FormItem label="企业名称：" prop="company_name" label-for="company_name">
                                        <Input v-model="formItem.company_name" placeholder="请填写您的企业名称"/>
                                    </FormItem>
                                </Col>
                            </Col>
                            <Col span="24">
                                <Col>
                                    <FormItem label="企业域名：" prop="domain_name" label-for="domain_name">
                                        <Input v-model="formItem.domain_name" :disabled="true" placeholder="请输入域名，格式：baidu.com"/>
                                    </FormItem>
                                </Col>
                            </Col>
                            <Col span="24">
                                <Col>
                                    <FormItem label="订单号：" label-for="order_id" prop="order_id">
                                        <Input v-model="formItem.order_id" placeholder="请输入您在淘宝或小程序购买的源码订单号"
                                               class="customer">
                                            <a slot="append" target="_blank" href="http://www.crmeb.com/web/auth/query.html">联系客服获取订单号</a>
                                        </Input>
                                    </FormItem>
                                </Col>
                            </Col>
                            <Col span="24">
                                <Col>
                                    <FormItem label="手机号：" label-for="phone" prop="phone">
                                        <Input v-model="formItem.phone" type="number" placeholder="负责人电话"/>
                                    </FormItem>
                                </Col>
                            </Col>
                            <Col span="24">
                                <Col>
                                    <FormItem label="验证码：" label-for="captcha" prop="captcha">
                                        <div class="acea-row row-middle code">
                                            <Input v-model="formItem.captcha" placeholder="验证码" class="input"/>
                                            <img @click="captchsChang" :src="captchs"
                                                 class="pictrue"/>
                                        </div>
                                    </FormItem>
                                </Col>
                            </Col>
                        </Row>
                        <Row type="flex">
                            <Col span="24">
                                <Button type="primary" @click="handleSubmit('formItem')" class="submit">提交</Button>
                            </Col>
                        </Row>
                    </Form>
                </Card>
            </div>
        </Modal>
    </div>
</template>
<script>
    import { auth, authApply } from '@/api/system'
    import { mapState } from 'vuex'

    export default {
        name: 'system_auth',
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            ...mapState('userLevel', [
                'categoryId'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 85
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right'
            }
        },
        data () {
            return {
                captchs: 'http://authorize.crmeb.net/api/captchs/',
                authCode: '',
                status: 1,
                dayNum: 0,
                isTemplate: false,
                formItem: {
                    company_name: '',
                    order_id: '',
                    phone: '',
                    captcha: '',
                    domain_name: location.hostname,
                },
                ruleValidate: {
                    company_name: [
                        { required: true, message: '请填写您的企业名称', trigger: 'blur' }
                    ],
                    domain_name: [
                        { required: true, message: '请输入域名，格式：baidu.com', trigger: 'blur' }
                    ],
                    order_id: [
                        { required: true, message: '请输入您在淘宝或小程序购买的源码订单号', trigger: 'blur' }
                    ],
                    phone: [
                        { required: true, message: '请输入负责人电话', trigger: 'blur' }
                    ],
                    captcha: [
                        { required: true, message: '请输入验证码', trigger: 'blur' }
                    ]
                }
            }
        },
        mounted () {
            this.getAuth()
            this.captchsChang()
        },
        methods: {
            captchsChang () {
                this.captchs = this.captchs + Date.parse(new Date())
            },
            cancel () {
                this.isTemplate = false
            },
            // 提交
            handleSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        authApply(this.formItem).then(res => {
                            this.isTemplate = false
                            this.getAuth()
                            return this.$Message.success(res.msg)
                        }).catch(res => {
                            this.captchsChang()
                            return this.$Message.error(res.msg)
                        })
                    } else {
                        return false
                    }
                })
            },
            getAuth () {
                auth().then(res => {
                    let data = res.data || {}
                    this.authCode = data.authCode || ''
                    this.status = data.status === undefined ? -1 : data.status
                    this.dayNum = data.day || 0
                })
            },
            toCrmeb () {
                window.open('http://www.crmeb.com')
            }
        }
    }
</script>
<style scoped lang="stylus">
    .auth {
        padding: 9px 16px 9px 10px;
    }

    .auth .iconIos {
        font-size: 40px;
        margin-right: 10px;
        color: #001529;
    }

    .auth .text {
        font-weight: 400;
        color: rgba(0, 0, 0, 1);
        font-size: 18px;
    }

    .auth .text .code {
        font-size: 14px;
        color: rgba(0, 0, 0, 0.5);
    }

    .auth .blue {
        color: #1890FF !important;
    }

    .auth .red {
        color: #ED4014 !important;
    }

    .grey {
        background-color: #999999;
        border-color: #999999;
        color: #fff;
    }

    .submit {
        width: 100%;
    }

    .code .input {
        width: 83%;
    }

    .code .input .ivu-input {
        border-radius: 4px 0 0 4px !important;
    }

    .code .pictrue {
        height: 32px;
        width: 17%;
    }

    .customer {
        border-right: 0;
    }

    .customer a {
        font-size: 12px;
    }

    .ivu-input-group-prepend, .ivu-input-group-append {
        background-color: #fff;
    }

    .ivu-input-group .ivu-input {
        border-right: 0 !important;
    }
</style>
