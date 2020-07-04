<!DOCTYPE html>
<html lang="zh-CN">
<head>
    {include file="public/head"}

    <link href="/system/frame/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
    <link href="/system/frame/css/style.min.css?v=3.0.0" rel="stylesheet">
    <title>{$title|default=''}</title>
    <style>
        .check {
            color: #f00
        }

        .demo-upload {
            display: block;
            height: 33px;
            text-align: center;
            border: 1px solid transparent;
            border-radius: 4px;
            overflow: hidden;
            background: #fff;
            position: relative;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .2);
            margin-right: 4px;
        }

        .demo-upload img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .demo-upload-cover {
            display: none;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, .6);
        }

        .demo-upload:hover .demo-upload-cover {
            display: block;
        }

        .demo-upload-cover i {
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            margin: 0 2px;
        }

        .code-send {
            cursor: pointer;
        }
    </style>
    <script>
        window.test = 1;
    </script>
</head>
<body>
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>短信账号注册</h5>
                    <a style="margin-left: 10px;display: inline-block;" href="{:Url('sms.smsConfig/index')}?type=4&tab_id=18">返回</a>
                </div>
                <div id="store-attr" class="mp-form" v-cloak="">
                    <i-Form :label-width="80" style="width: 100%">
                        <template>
                            <template>
                                <Form-Item>
                                    <Row>
                                        <i-Col span="13">
                                            <span>账&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;号：</span>
                                            <i-Input placeholder="短信平台账号" v-model="form.account" style="width: 80%"
                                                     type="text"></i-Input>
                                        </i-Col>
                                    </Row>
                                </Form-Item>
                                <Form-Item>
                                    <Row>
                                        <i-Col span="13">
                                            <span>密&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;码：</span>
                                            <i-Input placeholder="短信平台密码/token" v-model="form.password"
                                                     style="width: 80%" type="password"></i-Input>
                                        </i-Col>
                                    </Row>
                                </Form-Item>
                                <Form-Item>
                                    <Row>
                                        <i-Col span="13">
                                            <span>域&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;名：</span>
                                            <i-Input placeholder="网址域名" v-model="form.url" style="width: 80%"></i-Input>
                                        </i-Col>
                                    </Row>
                                </Form-Item>
                                <Form-Item>
                                    <Row>
                                        <i-Col span="13">
                                            <span>&nbsp;&nbsp;&nbsp;&nbsp;手机号：</span>
                                            <i-Input placeholder="注册手机号" v-model="form.phone"
                                                     style="width: 80%"></i-Input>
                                        </i-Col>
                                    </Row>
                                </Form-Item>
                                <Form-Item>
                                    <Row>
                                        <i-Col span="13">
                                            <span>短信签名：</span>
                                            <i-Input placeholder="短信签名 例如:CRMEB" v-model="form.sign"
                                                     style="width: 80%"></i-Input>
                                        </i-Col>
                                    </Row>
                                </Form-Item>
                                <Form-Item>
                                    <Row>
                                        <i-Col span="13">
                                            <span style="float: left">&nbsp;&nbsp;&nbsp;&nbsp;验证码：</span>
                                            <i-Input placeholder="验证码" v-model="form.code" style="width: 80%">
                                                <span slot="append" @click="sendCode" v-text="codeMsg"
                                                      class="code-send"></span>
                                            </i-Input>
                                        </i-Col>
                                    </Row>
                                </Form-Item>
                            </template>
                            <Form-Item>
                                <Row>
                                    <i-Col span="8" offset="6">
                                        <i-Button type="primary" @click="submit">提交</i-Button>
                                    </i-Col>
                                </Row>
                            </Form-Item>
                        </template>
                    </i-Form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var _vm;
    mpFrame.start(function (Vue) {
        new Vue({
            data() {
                return {
                    codeUrl: "{:Url('captcha')}",
                    codeMsg: "发送验证码",
                    form: {
                        account: '',
                        password: '',
                        url: '',
                        sign: '',
                        phone: '',
                        code: '',
                    },
                    isSend: true,
                }
            },
            methods: {
                isPhone: function (test) {
                    var reg = /^1[3456789]\d{9}$/;
                    return reg.test(test);
                },
                sendCode: function () {
                    var that = this;
                    if (!that.isSend) return;
                    if (!that.form.phone.length) {
                        $eb.message('error', '请填写手机号');
                        return false;
                    }
                    if (!that.form.sign || !that.form.sign.length) {
                        $eb.message('error', '请填写短信签名');
                        return false;
                    }
                    if (!that.isPhone(that.form.phone)) {
                        $eb.message('error', '手机号格式错误');
                        return false;
                    }
                    that.isSend = false;
                    $eb.axios.post(that.codeUrl, {phone: that.form.phone}).then(function (res) {
                        if (res.data.code == 200) {
                            var cd = 60;
                            var timeClone = setInterval(function () {
                                cd--;
                                if (cd <= 0) {
                                    that.codeMsg = '重新发送';
                                    clearInterval(timeClone);
                                    that.isSend = true;
                                } else {
                                    that.isSend = false;
                                    that.codeMsg = '剩余' + cd + 's';
                                }
                            }, 1000);
                            $eb.message('success', res.data.msg || '发送成功');
                        } else {
                            $eb.message('error', res.data.msg || '发送失败');
                        }
                        return false;
                    }).catch(function (err) {
                        that.isSend = false;
                        $eb.message('error', err);
                    })
                },
                submit() {
                    var that = this;
                    $eb.axios.post("{:Url('save')}", that.form).then(function (res) {
                        if (res.status == 200 && res.data.code == 200) {
                            $eb.message('success', res.data.msg || '提交成功!');
                            $eb.closeModalFrame(window.name);
                            location.href = "{:url('sms.smsConfig/index', array('type'=>4, 'tab_id'=>18))}";
                        } else {
                            $eb.message('error', res.data.msg || '请求失败!');
                        }
                    }).catch(function (err) {
                        $eb.message('error', err);
                    })
                },
            },
            mounted() {

            }
        }).$mount(document.getElementById('store-attr'));
    });
</script>
</body>