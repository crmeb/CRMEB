<template>
    <Row type="flex" justify="center" align="middle">
        <Col span="20" style="margin-top: 70px;" class="mb50">
            <Steps :current="current">
                <Step title="验证账号信息"></Step>
                <Step title="修改手机号码"></Step>
                <Step title="登录"></Step>
            </Steps>
        </Col>
        <Col span="24">
            <div class="index_from page-account-container">
                <Form ref="formInline" :model="formInline" :rules="ruleInline" @submit.native.prevent>
                    <template v-if="current === 0">
                        <FormItem prop="account" class="maxInpt">
                            <Input type="text" v-model="formInline.account" prefix="ios-contact-outline"
                                   placeholder="请输入当前手机号" size="large"/>
                        </FormItem>
                        <FormItem prop="password" class="maxInpt">
                            <Input type="password" v-model="formInline.password" prefix="ios-lock-outline"
                                   placeholder="请输入密码"/>
                        </FormItem>
                    </template>
                    <template v-if="current === 1">
                        <FormItem prop="phone" class="maxInpt">
                            <Input type="text" v-model="formInline.phone" prefix="ios-lock-outline"
                                   placeholder="请输入新手机号" size="large"/>
                        </FormItem>
                        <FormItem prop="verify_code" class="maxInpt">
                            <div class="code">
                                <Input type="text" v-model="formInline.verify_code" prefix="ios-keypad-outline"
                                       placeholder="请输入验证码" size="large" />
                                <Button :disabled=!this.canClick @click="cutDown" size="large">{{cutNUm}}</Button>
                            </div>
                        </FormItem>
                    </template>
                    <template v-if="current === 2">
                        <FormItem prop="phone" class="maxInpt">
                            <Input type="text" v-model="formInline.phone" prefix="ios-contact-outline"
                                   placeholder="请输入手机号"/>
                        </FormItem>
                        <FormItem prop="password" class="maxInpt">
                            <Input type="password" v-model="formInline.password" prefix="ios-lock-outline"
                                   placeholder="请输入密码"/>
                        </FormItem>
                    </template>
                    <FormItem class="maxInpt">
                        <Button v-if="current === 0" type="primary" long size="large" @click="handleSubmit1('formInline',current)" class="mb20">下一步</Button>
                        <Button  v-if="current === 1" type="primary" long size="large" @click="handleSubmit2('formInline',current)" class="mb20">提交</Button>
                        <Button  v-if="current === 2" type="primary" long size="large" @click="handleSubmit('formInline',current)" class="mb20">登录</Button>
                        <Button long size="large" @click="returns('formInline')" class="btn">返回
                        </Button>
                    </FormItem>
                </Form>
            </div>
        </Col>
    </Row>
</template>

<script>
    import { captchaApi, configApi, serveModifyApi, updateHoneApi } from '@/api/setting';
    export default {
        name: 'forgetPhone',
        props: {
            isIndex: {
                type: Boolean,
                default: false
            }
        },
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
            var validatePass = (rule, value, callback) => {
                if (value === '') {
                    callback(new Error('请输入密码'));
                } else {
                    if (this.formInline.checkPass !== '') {
                        this.$refs.formInline.validateField('checkPass');
                    }
                    callback();
                }
            };

            return {
                cutNUm: '获取验证码',
                canClick: true,
                current: 0,
                formInline: {
                    account: '',
                    phone: '',
                    verify_code: '',
                    password: '',
                },
                ruleInline: {
                    phone: [
                        { required: true, validator: validatePhone, trigger: 'blur' }
                    ],
                    verify_code: [
                        { required: true, message: '请输入验证码', trigger: 'blur' }
                    ],
                    password: [
                        { required: true, message: '请输入密码', trigger: 'blur' }
                    ],
                    account: [
                        { required: true, validator: validatePhone, trigger: 'blur' }
                    ],
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
            handleSubmit1 (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        this.handleSubmit(name,1);
                    } else {
                        return false;
                    }
                })
            },
            handleSubmit2(name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        updateHoneApi(this.formInline).then(async res => {
                            this.$Message.success(res.msg)
                            this.current = 2;
                        }).catch(res => {
                            this.$Message.error(res.msg);
                        })
                    } else {
                        return false;
                    }
                })
            },
            //登录
            handleSubmit (name,num) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        configApi({
                            account: this.formInline.account,
                            password: this.formInline.password
                        }).then(async res => {
                            num===1?this.$Message.success("原手机号密码正确"):this.$Message.success("登录成功");
                            num===1?this.current = 1:this.$emit('on-Login');
                        }).catch(res => {
                            this.$Message.error(res.msg);
                        })
                    } else {
                        return false;
                    }
                })
            },
            returns () {
                this.current === 0 ? this.$emit('gobackPhone'): this.current = 0
            }
        }
    }
</script>

<style scoped lang="less">
    .maxInpt{
        max-width:400px;
        margin-left:auto;
        margin-right:auto;
    }
    .code {
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>