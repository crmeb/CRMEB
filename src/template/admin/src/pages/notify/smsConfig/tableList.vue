<template>
    <div>
        <Card :bordered="false" dis-hover>
            <Tabs v-model="isChecked" @on-click="onChangeType">
                <TabPane label="短信" name="1"></TabPane>
                <TabPane label="商品采集" name="4"></TabPane>
                <TabPane label="物流查询" name="3"></TabPane>
            </Tabs>
            <!--短信列表-->
            <div class="note" v-if="isChecked === '1' && sms.open === 1 ">
                <div class="acea-row row-between-wrapper">
                    <div>
                        <span>短信状态：</span>
                        <RadioGroup type="button" v-model="tableFrom.type" @on-change="selectChange(tableFrom.type)">
                            <Radio label="">全部</Radio>
                            <Radio label="1">成功</Radio>
                            <Radio label="2">失败</Radio>
                            <Radio label="0">发送中</Radio>
                        </RadioGroup>
                    </div>
                    <div>
                        <Button type="primary" @click="shortMes">短信模板</Button>
                        <Button style="margin-left: 20px;" @click="editSign">修改签名</Button>
                    </div>
                </div>
                <Table :columns="columns1" :data="tableList"
                       :loading="loading" highlight-row
                       no-userFrom-text="暂无数据"
                       no-filtered-userFrom-text="暂无筛选结果" class="mt25"></Table>
                <div class="acea-row row-right page">
                    <Page :total="total" :current="tableFrom.page" show-elevator show-total @on-change="pageChange"
                          :page-size="tableFrom.limit"/>
                </div>
            </div>
            <!--商品采集，物流，电子面单列表-->
            <div v-else-if="(isChecked === '3' && query.open === 1) || (isChecked === '4' && copy.open === 1) ">
                <Table :columns="columns2" :data="tableList"
                       :loading="loading" highlight-row
                       no-userFrom-text="暂无数据"
                       no-filtered-userFrom-text="暂无筛选结果" class="mt25">
                    <template slot-scope="{ row }" slot="num" v-if="isChecked === '3' && query.open === 1">
                        <div>{{ row.content.num }}</div>
                    </template>
                </Table>
                <div class="acea-row row-right page">
                    <Page :total="total" :current="tableFrom.page" show-elevator show-total @on-change="pageChangeOther"
                          :page-size="tableFrom.limit"/>
                </div>
            </div>
            <!--无开通-->
            <div v-else>
                <!--开通按钮-->
                <div v-if="(isChecked === '1' && !isSms) || ( isChecked === '3' && !isDump) || (isChecked === '4' && !isCopy)" class="wuBox acea-row row-column-around row-middle">
                    <div class="wuTu"><img src="../../../assets/images/wutu.png"></div>
                    <span class="wuSp1" v-if="isChecked === '1'">短信服务未开通哦</span>
                    <span class="wuSp2" v-if="isChecked === '1'">点击立即开通按钮，即可使用短信服务哦～～～</span>
                    <span class="wuSp1" v-if="isChecked === '4'">商品采集服务未开通哦</span>
                    <span class="wuSp2" v-if="isChecked === '4'">点击立即开通按钮，即可使用商品采集服务哦～～～</span>
                    <span class="wuSp1" v-if="isChecked === '3'">物流查询未开通哦</span>
                    <span class="wuSp2" v-if="isChecked === '3'">点击立即开通按钮，即可使用物流查询服务哦～～～</span>
                    <Button size="default" type="primary" @click="onOpen">立即开通</Button>
                </div>
                <!--短信立即开通-->
                <div class="smsBox" v-if="isSms && isChecked === '1'">
                    <div class="index_from page-account-container">
                        <div class="page-account-top">
                            <span class="page-account-top-tit">开通短信服务</span>
                        </div>
                        <Form ref="formInline" :model="formInline" :rules="ruleInline" @submit.native.prevent @keyup.enter="handleSubmit('formInline')">
                            <FormItem prop="sign" class="maxInpt">
                                <Input type="text" v-model="formInline.sign" prefix="ios-contact-outline"
                                       placeholder="请输入短信签名"/>
                            </FormItem>
                            <FormItem class="maxInpt">
                                <Button type="primary" long size="default" @click="handleSubmit('formInline')" class="btn">登录</Button>
                            </FormItem>
                        </Form>
                    </div>
                </div>
                <!--电子面单立即开通-->
                <div class="smsBox" v-if="isDump && isChecked === '3' ">
                    <div class="index_from page-account-container">
                        <div class="page-account-top">
                            <span class="page-account-top-tit">开通物流查询服务</span>
                        </div>
                        <Button size="default" type="primary" @click="handleSubmitDump">立即开通</Button>
                    </div>
                </div>
            </div>
        </Card>
        <Modal v-model="modals" footer-hide scrollable closable title="短信账户签名修改" class="order_box" @on-cancel="cancel('formInline')">
            <Form ref="formInline" :model="formInline" :rules="ruleInline" :label-width="100" @submit.native.prevent>
                <FormItem>
                    <Input v-model="accountInfo.account" disabled prefix="ios-person-outline" size="large" style="width:87%;"></Input>
                </FormItem>
                <FormItem prop="sign">
                    <!--<Icon type="   ios-calendar-outline" />-->
                    <Input v-model="formInline.sign" prefix="ios-document-outline"  placeholder="请输入短信签名，例如：CRMEB" size="large" style="width:87%;"></Input>
                </FormItem>
                <FormItem prop="phone">
                    <Input v-model="formInline.phone" prefix="ios-call-outline" placeholder="请输入您的手机号" size="large" style="width:87%;"></Input>
                </FormItem>
                <FormItem prop="code">
                    <div class="code acea-row row-middle" style="width: 87%">
                        <Input type="text" v-model="formInline.code" prefix="ios-keypad-outline"
                               placeholder="验证码" size="large" style="width: 75%"/>
                        <Button :disabled=!this.canClick @click="cutDown" size="large">{{cutNUm}}</Button>
                    </div>
                </FormItem>
                <FormItem>
                    <Button type="primary" long size="large" @click="editSubmit('formInline')" class="btn" style="width:87%;">确认修改</Button>
                </FormItem>
            </Form>
            <!--<div slot="footer">-->
                <!--&lt;!&ndash;<Button type="primary" @click="putSend('formInline')">提交</Button>&ndash;&gt;-->
                <!--&lt;!&ndash;<Button @click="cancel('formInline')">取消</Button>&ndash;&gt;-->
            <!--</div>-->
        </Modal>
    </div>
</template>

<script>
    import { smsRecordApi, serveInfoApi, serveSmsOpenApi, serveOpnExpressApi, serveOpnOtherApi, serveRecordListApi, exportTempApi, exportAllApi, serveSign, captchaApi } from '@/api/setting';
    export default {
        name: 'tableList',
        props: {
            copy: {
                type: Object,
                default: null
            },
            query: {
                type: Object,
                default: null
            },
            sms: {
                type: Object,
                default: null
            },
            accountInfo: {
                type: Object,
                default: null
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
            return {
                cutNUm: '获取验证码',
                canClick: true,
                spinShow: true,
                formInline: {
                    sign: '',
                    phone: '',
                    code: ''
                },
                ruleInline: {
                    sign: [
                        { required: true, message: '请输入短信签名', trigger: 'blur' }
                    ],
                    phone: [
                        { required: true, validator: validatePhone, trigger: 'blur' }
                    ],
                    code: [
                        { required: true, message: '请输入验证码', trigger: 'blur' }
                    ]
                },
                isChecked: '1',
                columns1: [
                    // {
                    //     title: 'ID',
                    //     key: 'id',
                    //     width: 80
                    // },
                    {
                        title: '手机号',
                        key: 'phone',
                        minWidth: 100
                    },
                    {
                        title: '模板内容',
                        key: 'content',
                        minWidth: 590
                    },
                    // {
                    //     title: '模板ID',
                    //     key: 'template',
                    //     minWidth: 100
                    // },
                    {
                        title: '发送时间',
                        key: 'add_time',
                        minWidth: 150
                    },
                    {
                        title: '状态码',
                        key: '_resultcode',
                        minWidth: 100
                    }
                ],
                columns2: [],
                tableFrom: {
                    page: 1,
                    limit: 20,
                    type: ''
                },
                total: 0,
                loading: false,
                tableList: [],
                formInlineDump: {
                    temp_id: '',
                    com: '',
                    to_name: '',
                    to_tel: '',
                    siid: '',
                    to_address: ''
                },
                tempImg: '', // 图片
                exportTempList: [], // 电子面单模板
                exportList: [], // 快递公司列表
                isSms: false, // 是否开通短信
                isDump: false, // 是否开通电子面单,是否开通物流查询
                isCopy: false, // 是否开通商品采集
                modals: false
            }
        },
        watch: {
            sms (n) {
                if (n.open === 1) this.getList();
            }
        },
        created () {
            if (this.isChecked === '1' && this.sms.open === 1) this.getList();
        },
        methods: {
            //短信模板页
            shortMes() {
                this.$router.push({ path:'/admin/setting/sms/sms_template_apply/index'})
            },
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
            editSign (){
                this.formInline.sign = this.accountInfo.sms.sign;
                this.modals = true;
            },
            cancel (name) {
                this.modals = false;
                this.$refs[name].resetFields();
            },
            // 提交
            editSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        serveSign(this.formInline).then(res => {
                            this.modals = false;
                            this.$Message.success(res.msg);
                            this.$refs[name].resetFields();
                        }).catch(res => {
                            this.$Message.error(res.msg);
                        })
                    }
                })
            },
            onChangeImg (item) {
                this.exportTempList.map(i => {
                    if (i.temp_id === item) this.tempImg = i.pic
                })
            },
            onChangeType () {
                if (this.isChecked === '1' && this.sms.open === 1) {
                    this.tableFrom.type = ''
                    this.getList()
                } else {
                    if (this.query.open === 1 || this.copy.open === 1) this.getRecordList()
                }
            },
            // 其他列表
            getRecordList () {
                this.loading = true;
                this.tableFrom.type = this.isChecked
                serveRecordListApi(this.tableFrom).then(async res => {
                    let data = res.data
                    this.tableList = data.data;
                    this.total = res.data.count;
                    switch (this.isChecked) {
                        case '3':
                            this.columns2 = [
                                {
                                    title: '快递单号',
                                    slot: 'num',
                                    minWidth: 120
                                },
                                {
                                    title: '快递公司编码',
                                    key: 'code',
                                    minWidth: 120
                                },
                                {
                                    title: '状态',
                                    key: '_resultcode',
                                    minWidth: 120
                                },
                                {
                                    title: '添加时间',
                                    key: 'add_time',
                                    minWidth: 150
                                }
                            ]
                            break;
                        default:
                            this.columns2 = [
                                {
                                    title: '复制URL',
                                    key: 'url',
                                    minWidth: 400
                                },
                                {
                                    title: '请求状态',
                                    key: '_resultcode',
                                    minWidth: 120
                                },
                                {
                                    title: '添加时间',
                                    key: 'add_time',
                                    minWidth: 150
                                }
                            ]
                            break;
                    }
                    this.loading = false;
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChangeOther (index) {
                this.tableFrom.page = index;
                this.getRecordList();
            },
            // 开通短信提交
            handleSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        serveSmsOpenApi(this.formInline).then(async res => {
                            this.$Message.success('开通成功!');
                            this.getList();
                            this.$emit('openService', 'sms')
                        }).catch(res => {
                            this.$Message.error(res.msg);
                        })
                    } else {
                        return false;
                    }
                })
            },
            // 首页去开通
            onOpenIndex (val) {
                switch (val) {
                    case 'sms':
                        this.isChecked = '1'
                        this.isSms = true;
                        break;
                    case 'copy':
                        this.isChecked = '4'
                        this.openOther();
                        break;
                    case 'query':
                        this.isChecked = '3'
                        this.handleSubmitDump();
                        break;
                }
            },
            // 开通按钮
            onOpen () {
                if (this.isChecked === '1') this.isSms = true;
                if (this.isChecked === '3') this.isDump = true;
                // if (this.isChecked === '2' || this.isChecked === '3') this.openDump();
                if (this.isChecked === '4') this.openOther();
            },
            // 开通其他
            openOther () {
                this.$Modal.confirm({
                    title: '开通商品采集吗',
                    content: '<p>确定要开通商品采集吗</p>',
                    loading: true,
                    onOk: () => {
                        this.$Modal.remove();
                        setTimeout(() => {
                            serveOpnOtherApi({ type: 1 }).then(async res => {
                                this.getRecordList();
                                this.$emit('openService', 'copy')
                            }).catch(res => {
                                this.$Message.error(res.msg);
                            })
                        }, 300);
                    },
                    onCancel: () => {}
                });
            },
            // 选择
            selectChange (tab) {
                this.tableFrom.type = tab;
                this.tableFrom.page = 1;
                this.getList();
            },
            // 列表
            getList () {
                this.loading = true;
                smsRecordApi(this.tableFrom).then(async res => {
                    let data = res.data
                    this.tableList = data.data;
                    this.total = res.data.count;
                    this.spinShow = false;
                    this.loading = false;
                }).catch(res => {
                    this.spinShow = false;
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange (index) {
                this.tableFrom.page = index;
                this.getList();
            },
            // 表格搜索
            userSearchs () {
                this.getList();
            },
            handleSubmitDump () {
                this.$Modal.confirm({
                    title: '开通物流查询吗',
                    content: '<p>确定要开通物流查询吗</p>',
                    loading: true,
                    onOk: () => {
                        this.$Modal.remove();
                        this.isDump = true;
                        serveOpnExpressApi(this.formInlineDump).then(async res => {
                            this.$Message.success('开通成功!');
                            this.getRecordList();
                            this.$emit('openService', 'query')
                        }).catch(res => {
                            this.$Message.error(res.msg);
                        })
                    },
                    onCancel: () => {}
                });
            }
        }
    }
</script>
<style lang="less" scoped>
    @aaa: ~'>>>';
    .order_box /deep/.ivu-form-item-content{
        margin-left: 50px!important;
    }
    .maxInpt{
        max-width:400px;
        margin-left:auto;
        margin-right:auto;
    }
    .smsBox .page-account-top{
        text-align: center;
        margin: 70px 0 30px 0;
    }
    .note{
        margin-top: 15px;
    }
    .tempImg{
        cursor: pointer;
        margin-left: 11px;
        color: #1890FF;
    }
    .tabBox_img{
        opacity: 0;
        width: 38px;
        height: 30px;
        margin-top: -30px;
        cursor: pointer;
        img{
            width: 100%;
            height: 100%;
        }
    }
    .width9{
        width: 90%;
    }
    .width10{
        width: 100%;
    }
    .wuBox{
        width: 100%;
    }
    .wuSp1{
        color: #000000;
        font-size: 21px;
        font-weight: 500;
        line-height: 32px;
        margin-top: 23px;
        margin-bottom: 5px;
    }
    .wuSp2{
        opacity: 45%;
        font-weight: 400;
        color: #000000;
        line-height: 22px;
        margin-bottom: 30px;
    }
    .page-account-top-tit{
        font-size: 21px;
        color: #1890FF;
    }
    .wuTu{
        width: 295px;
        height: 164px;
        margin-top: 54px;
        img{
            width: 100%;
            height: 100%;
        }
    }
    .tempId{
    @aaa .ivu-form-item-content{
        text-align: left !important;
    }
    }
</style>
