<template>
    <Row type="flex">
        <Col span="24">
            <div class="index_from page-account-container">
                <div class="page-account-top ">
                    <span class="page-account-top-tit">一号通账户注册</span>
                </div>
                <Form ref="formInline" :model="formInline" :rules="ruleInline" @submit.native.prevent>
                    <!--<FormItem prop="account">-->
                    <!--<Input type="text" v-model="formInline.account" prefix="ios-contact-outline"-->
                    <!--placeholder="请输入短信平台账号" />-->
                    <!--</FormItem>-->
                    <FormItem prop="phone" class="maxInpt">
                        <Input type="number" v-model="formInline.phone" prefix="ios-contact-outline"
                               placeholder="请输入您的手机号" />
                    </FormItem>
                    <FormItem prop="password" class="maxInpt">
                        <Input type="password" v-model="formInline.password" prefix="ios-lock-outline"
                               placeholder="请输入短信平台密码/token" />
                    </FormItem>
                    <!--<FormItem prop="password">-->
                    <!--<Input type="password" v-model="formInline.password" prefix="ios-lock-outline"-->
                    <!--placeholder="请确认短信平台密码/token" />-->
                    <!--</FormItem>-->
                    <FormItem prop="url" class="maxInpt">
                        <Input type="text" v-model="formInline.url" prefix="ios-contact-outline"
                               placeholder="请输入网址域名" />
                    </FormItem>
                    <!--<FormItem prop="sign">-->
                    <!--<Input type="text" v-model="formInline.sign" prefix="ios-contact-outline"-->
                    <!--placeholder="请输入短信签名，例如：CRMEB" />-->
                    <!--</FormItem>-->
                    <FormItem prop="verify_code" class="maxInpt">
                        <div class="code">
                            <Input type="text" v-model="formInline.verify_code" prefix="ios-keypad-outline"
                                   placeholder="请输入验证码" />
                            <Button :disabled=!canClick @click="cutDown">{{cutNUm}}</Button>
                        </div>
                    </FormItem>
                    <FormItem class="maxInpt">
                        <Button type="primary" long size="large" @click="handleSubmit('formInline')" class="btn">注册</Button>
                    </FormItem>
                </Form>
                <div class="page-account-other">
                    <span @click="changelogo">立即登录</span>
                </div>
            </div>
        </Col>
    </Row>
</template>

<script>
    import { captchaApi, registerApi } from '@/api/setting';
    export default {
        name: 'register',
        data () {
            const validatePhone = (rule, value, callback) => {
                if (!value) {
                    return callback(new Error('请填写手机号'));
                } else if (!/^1[3456789]\d{9}$/.test(value)) {
                    callback(new Error('手机号格式不正确!'));
                } else {
                    callback();
                }
            };
            return {
                cutNUm: '获取验证码',
                canClick: true,
                formInline: {
                    url: '',
                    password: '',
                    verify_code: '',
                    phone: ''
                },
                ruleInline: {
                    account: [
                        { required: true, message: '请输入短信平台账号', trigger: 'blur' }
                    ],
                    password: [
                        { required: true, message: '请输入短信平台密码/token', trigger: 'blur' }
                    ],
                    url: [
                        { required: true, message: '请输入网址域名', trigger: 'blur' }
                    ],
                    phone: [
                        { required: true, validator: validatePhone, trigger: 'blur' }
                    ],
                    sign: [
                        { required: true, message: '请输入短信签名', trigger: 'blur' }
                    ],
                    verify_code: [
                        { required: true, message: '请输入验证码', trigger: 'blur' }
                    ]
                }
            }
        },
        methods: {
            // 短信验证码
            cutDown () {
                if (this.formInline.phone) {
                    if (!this.canClick) return;
                    this.canClick = false;
                    this.cutNUm = 60;
                    let data = {
                        phone: this.formInline.phone
                    };
                    captchaApi(data).then(async res => {
                        this.$Message.success(res.msg);
                    }).catch(res => {
                        this.$Message.error(res.msg);
                    })
                    let time = setInterval(() => {
                        this.cutNUm--;
                        if (this.cutNUm === 0) {
                            this.cutNUm = '获取验证码';
                            this.canClick = true;
                            clearInterval(time)
                        }
                    }, 1000)
                } else {
                    this.$Message.warning('请填写手机号!');
                }
            },
            // 注册
            handleSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        registerApi(this.formInline).then(async res => {
                            this.$Message.success(res.msg);
                            setTimeout(() => {
                                this.changelogo();
                            }, 1000);
                        }).catch(res => {
                            this.$Message.error(res.msg);
                        })
                    } else {
                        return false;
                    }
                })
            },
            // 立即登录
            changelogo () {
                this.$emit('on-change')
            }
        }
    }
</script>

<style scoped lang="stylus">
    .maxInpt{
        max-width 400px
        margin-left auto
        margin-right auto
    }
    .page-account-container{
        text-align center
        padding 50px 0
    }
    .page-account-top{
        margin-bottom 20px
    }
    .page-account-top-tit
        font-size 21px
        color #1890FF
    .page-account-other
        text-align center
        color #1890FF
        font-size 12px
        span
            cursor pointer
    .code {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
