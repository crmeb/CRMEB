<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="formValidate" :model="formValidate" :label-width="labelWidth" :label-position="labelPosition" class="tabform" @submit.native.prevent>
                <Row :gutter="24" type="flex">
                    <Col span="24">
                        <FormItem label="时间选择：">
                            <RadioGroup v-model="formValidate.data" type="button"  @on-change="selectChange(formValidate.data)" class="mr">
                                <Radio :label=item.val v-for="(item,i) in fromList.fromTxt" :key="i">{{item.text}}</Radio>
                            </RadioGroup>
                            <DatePicker :editable="false" @on-change="onchangeTime" :value="timeVal"  format="yyyy/MM/dd" type="daterange" placement="bottom-end" placeholder="请选择时间" style="width: 200px;"></DatePicker>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="提现状态：">
                            <RadioGroup type="button" v-model="formValidate.status" class="mr15" @on-change="selChange">
                                <Radio :label="itemn.value" v-for="(itemn,indexn) in treeData.withdrawal" :key="indexn">{{itemn.title}}</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="提现方式：">
                            <RadioGroup type="button" v-model="formValidate.extract_type" class="mr15" @on-change="selChange">
                                <Radio :label="itemn.value" v-for="(itemn,indexn) in treeData.payment" :key="indexn">{{itemn.title}}</Radio>
                            </RadioGroup>
                        </FormItem>
                    </Col>
                    <Col span="24">
                        <FormItem label="搜索：">
                            <div class="acea-row row-middle">
                                <Input search enter-button @on-search="selChange" placeholder="微信昵称/姓名/支付宝账号/银行卡号" element-id="name" v-model="formValidate.nireid" style="width: 30%;"/>
                                <router-link to="/admin/finance/finance/commission" class="ml20">佣金记录</router-link>
                            </div>
                        </FormItem>
                    </Col>
                </Row>
            </Form>
        </Card>
        <cards-data :cardLists="cardLists" v-if="extractStatistics"></cards-data>
        <Card :bordered="false" dis-hover>
            <Table
                    ref="table"
                    :columns="columns"
                    :data="tabList"
                    class="ivu-mt"
                    :loading="loading"
                    no-data-text="暂无数据"
                    no-filtered-data-text="暂无筛选结果"
            >
                <template slot-scope="{ row }" slot="nickname">
                    <div>用户昵称: {{row.nickname}} <br> 用户id:{{row.uid}}</div>
                </template>
                <template slot-scope="{ row }" slot="extract_price">
                    <div>{{row.extract_price}}</div>
                </template>
                <template slot-scope="{ row, index }" slot="add_time">
                    <span> {{row.add_time | formatDate}}</span>
                </template>
                <template slot-scope="{ row }" slot="extract_type">
                    <div class="type" v-if="row.extract_type === 'bank'">
                        <div class="item">姓名:{{row.real_name}}</div>
                        <div class="item">银行卡号:{{row.bank_code}}</div>
                        <div class="item">银行开户地址:{{row.bank_address}}</div>
                    </div>
                    <div class="type" v-if="row.extract_type === 'weixin'">
                        <div class="item">昵称:{{row.nickname}}</div>
                        <div class="item">微信号:{{row.wechat}}</div>
                    </div>
                    <div class="type" v-if="row.extract_type === 'alipay'">
                        <div class="item">姓名:{{row.real_name}}</div>
                        <div class="item">支付宝号:{{row.alipay_code}}</div>
                    </div>
                    <div class="type" v-if="row.extract_type === 'balance'">
                        <div class="item">姓名:{{row.real_name}}</div>
                        <div class="item">提现方式：佣金转入余额</div>
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="qrcode_url">
                    <div class="tabBox_img" v-viewer v-if="row.extract_type === 'weixin' || row.extract_type === 'alipay'">
                        <img v-lazy="row.qrcode_url">
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="status">
                    <div class="status" v-if="row.status === 0">
                        <div class="statusVal">申请中</div>
                        <div>
                            <Button type="error" icon="md-close" size="small" class="item" @click="invalid(row)">驳回</Button>
                            <Button type="info" icon="md-checkmark" size="small" class="item" @click="adopt(row, '审核通过', index)">通过</Button>
                        </div>
                    </div>
                    <div class="statusVal"  v-if="row.status === 1">提现通过</div>
                    <div class="statusVal"  v-if="row.status === -1">提现未通过<br/>未通过原因：{{row.fail_msg}}</div>
                </template>
                <template slot-scope="{ row }" slot="createModalFrame">
                    <a href="javascript:void(0);" @click="edit(row)">编辑</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"
                      :page-size="formValidate.limit"/>
            </div>
        </Card>

        <!-- 编辑表单-->
        <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
        <!-- 拒绝通过-->
        <Modal v-model="modals"  scrollable   closable title="未通过原因" :mask-closable="false">
            <Input v-model="fail_msg.message" type="textarea" :rows="4" placeholder="请输入未通过原因" />
            <div slot="footer">
                <Button type="primary" size="large" long :loading="modal_loading" @click="oks">确定</Button>
            </div>
        </Modal>
    </div>
</template>
<script>
    import cardsData from '@/components/cards/cards'
    import searchFrom from '@/components/publicSearchFrom'
    import { mapState } from 'vuex'
    import { cashListApi, cashEditApi, refuseApi } from '@/api/finance'
    import { formatDate } from '@/utils/validate'
    import editFrom from '@/components/from/from'
    export default {
        name: 'cashApply',
        components: { cardsData, searchFrom, editFrom },
        filters: {
            formatDate (time) {
                if (time !== 0) {
                    let date = new Date(time * 1000)
                    return formatDate(date, 'yyyy-MM-dd hh:mm')
                }
            }
        },
        data () {
            return {
                images: ['1.jpg', '2.jpg'],
                modal_loading: false,
                fail_msg: {
                    message: '输入信息不完整或有误!'
                },
                modals: false,
                total: 0,
                cardLists: [],
                loading: false,
                columns: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '用户信息',
                        slot: 'nickname',
                        minWidth: 180
                    },
                    {
                        title: '提现金额',
                        slot: 'extract_price',
                        minWidth: 90
                    },
                    {
                        title: '提现方式',
                        slot: 'extract_type',
                        minWidth: 150
                    },
                    {
                        title: '收款码',
                        slot: 'qrcode_url',
                        minWidth: 150
                    },
                    {
                        title: '添加时间',
                        slot: 'add_time',
                        minWidth: 100
                    },
                    {
                        title: '备注',
                        key: 'mark',
                        minWidth: 100
                    },
                    {
                        title: '审核状态',
                        slot: 'status',
                        minWidth: 180
                    },
                    {
                        title: '操作',
                        slot: 'createModalFrame',
                        fixed: 'right',
                        width: 100
                    }
                ],
                tabList: [],
                fromList: {
                    title: '选择时间',
                    custom: true,
                    fromTxt: [
                        { text: '全部', val: '' },
                        { text: '今天', val: 'today' },
                        { text: '本周', val: 'week' },
                        { text: '本月', val: 'month' },
                        { text: '本季度', val: 'quarter' },
                        { text: '本年', val: 'year' }]
                },
                treeData: {
                    withdrawal: [
                        {
                            title: '全部',
                            value: ''
                        },
                        {
                            title: '未通过',
                            value: -1
                        },
                        {
                            title: '申请中',
                            value: 0
                        },
                        {
                            title: '已通过',
                            value: 1
                        }
                    ],
                    payment: [
                        {
                            title: '全部',
                            value: ''
                        },
                        {
                            title: '微信',
                            value: 'wx'
                        },
                        {
                            title: '支付宝',
                            value: 'alipay'
                        },
                        {
                            title: '银行卡',
                            value: 'bank'
                        }
                    ]
                },
                formValidate: {
                    status: '',
                    extract_type: '',
                    nireid: '',
                    data: '',
                    page: 1,
                    limit: 20
                },
                extractStatistics: {},
                timeVal: [],
                FromData: null,
                extractId: 0
            }
        },
        watch: {
            $route(){
                if(this.$route.fullPath==='/admin/finance/user_extract/index?status=0'){
                    this.getPath();
                }
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 80
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'left'
            }
        },
        mounted () {
            if(this.$route.fullPath==='/admin/finance/user_extract/index?status=0'){
                this.getPath()
            }else {
                this.getList()
            }
        },
        methods: {
            getPath(){
                this.formValidate.page = 1;
                this.formValidate.status = parseInt(this.$route.query.status);
                this.getList();
            },
            // 无效
            invalid (row) {
                this.extractId = row.id
                this.modals = true
            },
            // 确定
            oks () {
                this.modal_loading = true;
                refuseApi(this.extractId, this.fail_msg).then(async res => {
                    this.$Message.success(res.msg);
                    this.modal_loading = false;
                    this.modals = false;
                    this.getList();
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 通过
            adopt (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `finance/extract/adopt/${row.id}`,
                    method: 'put',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.getList()
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 具体日期
            onchangeTime (e) {
                this.timeVal = e
                this.formValidate.data = this.timeVal.join('-')
                this.formValidate.page = 1
                this.getList()
            },
            // 选择时间
            selectChange (tab) {
                this.formValidate.page = 1
                this.formValidate.data = tab
                this.timeVal = []
                this.getList()
            },
            // 选择
            selChange () {
                this.formValidate.page = 1
                this.getList()
            },
            // 列表
            getList () {
                this.loading = true
                cashListApi(this.formValidate).then(async res => {
                    let data = res.data
                    this.tabList = data.list.list
                    this.total = data.list.count
                    this.extractStatistics = data.extract_statistics
                    this.cardLists = [
                        { 'col': 6, 'count': this.extractStatistics.price, 'name': '待提现金额', className: 'md-basket' },
                        { 'col': 6, 'count': this.extractStatistics.brokerage_count, 'name': '佣金总金额', className: 'md-pricetags' },
                        { 'col': 6, 'count': this.extractStatistics.priced, 'name': '已提现金额', className: 'md-cash' },
                        { 'col': 6, 'count': this.extractStatistics.brokerage_not, 'name': '未提现金额', className: 'ios-cash' }
                    ]
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            pageChange (index) {
                this.formValidate.page = index
                this.getList()
            },
            // 编辑
            edit (row) {
                cashEditApi(row.id).then(async res => {
                    if (res.data.status === false) {
                        return this.$authLapse(res.data)
                    }
                    this.FromData = res.data
                    this.$refs.edits.modals = true
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 编辑提交成功
            submitFail () {
                this.getList()
            }
        }
    }
</script>
<style scoped lang="stylus">
    .ivu-mt .type .item
        margin:3px 0;
    .tabform
        margin-bottom 10px
    .Refresh
        font-size 12px
        color #1890FF
        cursor pointer
    .ivu-form-item
        margin-bottom 10px
    .status >>> .item~.item
        margin-left 6px
    .status >>> .statusVal
        margin-bottom 7px
    /*.ivu-mt >>> .ivu-table-header*/
    /*    border-top:1px dashed #ddd!important*/
    .type
       padding 3px 0
       box-sizing border-box
    .tabBox_img
        width 36px
        height 36px
        border-radius:4px
        cursor pointer
        img
            width 100%
            height 100%
</style>
