<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="formValidate" :model="formValidate"  :label-width="100" @submit.native.prevent>
                <Row :gutter="24" type="flex">
                    <Col span="24" class="ivu-text-left">
                        <FormItem label="核销日期：">
                            <RadioGroup v-model="formValidate.data" type="button" class="mr" @on-change="selectChange(formValidate.data)">
                                <Radio :label=item.val v-for="(item,i) in fromList.fromTxt" :key="i">{{item.text}}</Radio>
                            </RadioGroup>
                            <DatePicker :editable="false" @on-change="onchangeTime" :value="timeVal" format="yyyy/MM/dd" type="daterange"
                                        placement="bottom-end" placeholder="请选择时间" style="width: 200px;" ></DatePicker>
                        </FormItem>
                    </Col>
                    <Col span="12" class="ivu-text-left">
                        <FormItem label="筛选条件：" >
                            <Input enter-button  placeholder="请输入搜索内容" v-model="formValidate.real_name" style="width: 90%;">
                              <Select v-model="field_key" slot="prepend" style="width: 80px">
                                <Option value="all">全部</Option>
                                <Option value="order_id">订单号</Option>
                                <Option value="uid">UID</Option>
                                <Option value="real_name">用户姓名</Option>
                                <Option value="user_phone">用户电话</Option>
                                <Option value="title">商品名称(模糊)</Option>
                              </Select>
                            </Input>
                        </FormItem>
                    </Col>
                    <Col span="12" class="mr">
                        <FormItem label="选择门店："  label-for="store_name">
                            <Select v-model="formValidate.store_id" element-id="store_id" clearable @on-change="userSearchs">
                                <Option v-for="item in storeSelectList" :value="item.id" :key="item.id">{{ item.name }}</Option>
                            </Select>
                        </FormItem>
                    </Col>
                    <Col span="12" class="mr">
                        <FormItem label=""  label-for="store_name">
                            <Button type="primary" class="mr15" @click="userSearchs">搜索</Button>
                            <Button class="mr15" @click="refresh">刷新</Button>
                        </FormItem>
                    </Col>
                </Row>
            </Form>
        </Card>
        <cards-data :cardLists="cardLists" v-if="cardLists.length>0"></cards-data>
        <Card :bordered="false" dis-hover>
            <Table :columns="columns" :data="orderList"
                   ref="table"
                   :loading="loading" highlight-row
                   no-data-text="暂无数据"
                   no-filtered-data-text="暂无筛选结果"
                   class="orderData mt25"
            >
                <template slot-scope="{ row, index }" slot="name">
                    {{row.nickname}}/{{row.uid}}
                </template>
                <template slot-scope="{ row, index }" slot="spread_nickname">
                    <a href="javascript:void(0);" @click="referenceInfo(row.spread_uid)">{{row.spread_nickname}}</a>
                </template>
                <template slot-scope="{ row, index }" slot="info">
                    <div class="tabBox" v-for="(val, i ) in row._info" :key="i">
                        <div class="tabBox_img">
                            <img v-lazy="val.cart_info.productInfo.attrInfo?val.cart_info.productInfo.attrInfo.image:val.cart_info.productInfo.image">
                        </div>
                        <span class="tabBox_tit">{{ val.cart_info.productInfo.store_name + ' | ' }}{{val.cart_info.productInfo.attrInfo?val.cart_info.productInfo.attrInfo.suk:''}}</span>
                        <span class="tabBox_pice">{{ '￥'+ val.cart_info.truePrice + ' x '+ val.cart_info.cart_num}}</span>
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="status_name">
                    {{row.status_name.status_name}}
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"
                      :page-size="formValidate.limit"/>
            </div>
        </Card>
        <referrer-info ref="info"></referrer-info>
    </div>
</template>

<script>
    import { verifyOrderApi, merchantStoreListApi } from '@/api/setting'
    import cardsData from '@/components/cards/cards'
    import referrerInfo from '@/components/referrerInfo/index'
    export default {
        name: 'setting_order',
        components: { cardsData, referrerInfo },
        data () {
            return {
                formValidate: {
                    page: 1,
                    limit: 15,
                    data: '',
                    real_name: '',
                    store_id: '',
                    field_key: ''
                },
                field_key:'',
                fromList: {
                    title: '选择时间',
                    custom: true,
                    fromTxt: [
                        { text: '全部', val: '' },
                        { text: '今天', val: 'today' },
                        { text: '昨天', val: 'yesterday' },
                        { text: '最近7天', val: 'lately7' },
                        { text: '最近30天', val: 'lately30' },
                        { text: '本月', val: 'month' },
                        { text: '本年', val: 'year' }
                    ]
                },
                timeVal: [],
                storeSelectList: [],
                cardLists: [],
                columns: [
                    {
                        title: '订单号',
                        key: 'order_id',
                        minWidth: 180,
                        sortable: true
                    },
                    {
                        title: '用户信息',
                        slot: 'name',
                        minWidth: 120
                    },
                    {
                        title: '推荐人信息',
                        slot: 'spread_nickname',
                        minWidth: 120
                    },
                    {
                        title: '商品信息',
                        slot: 'info',
                        minWidth: 360
                    },
                    {
                        title: '实际支付',
                        key: 'pay_price',
                        minWidth: 90
                    },
                    {
                        title: '核销员',
                        key: 'clerk_name',
                        minWidth: 90
                    },
                    {
                        title: '核销门店',
                        key: 'store_name',
                        minWidth: 120
                    },
                    {
                        title: '支付状态',
                        key: 'pay_type_name',
                        minWidth: 80
                    },
                    {
                        title: '订单状态',
                        slot: 'status_name',
                        minWidth: 80
                    },
                    {
                        title: '下单时间',
                        key: 'add_time',
                        minWidth: 130,
                        sortable: true
                    }
                ],
                orderList: [],
                loading: false,
                total: 0
            }
        },
        mounted () {
            this.getList()
            this.storeList()
        },
        methods: {
            getList () {
                let that = this;
                that.loading = true;
                that.formValidate.field_key = this.field_key === 'all'?'':this.field_key;
                verifyOrderApi(that.formValidate).then(res => {
                    that.loading = false
                    that.orderList = res.data.data
                    that.total = res.data.count
                    that.cardLists = res.data.badge
                }).catch(res => {
                    that.$Message.error(res.msg)
                })
            },
            userSearchs () {
                this.formValidate.page = 1
                this.getList()
            },
            // 具体日期
            onchangeTime (e) {
                this.timeVal = e
                this.formValidate.data = this.timeVal.join('-')
                this.getList()
            },
                // 选择时间
            selectChange(tab) {
            this.formValidate.page = 1;
            this.formValidate.data = tab;
            this.timeVal = [];
            this.getList();
            },
            storeList () {
                let that = this
                merchantStoreListApi().then(res => {
                    that.storeSelectList = res.data
                }).catch(res => {
                    that.$Message.error(res.msg)
                })
            },
            pageChange (index) {
                this.formValidate.page = index
                this.getList()
            },
            referenceInfo (uid) {
                this.$refs.info.isTemplate = true
                this.$refs.info.verifySpreadInfo(uid)
            },
            refresh () {
                this.formValidate = {
                    page: 1,
                    limit: 15,
                    data: '',
                    real_name: '',
                    store_id: '',
                    field_key:''
                }
                this.field_key = '';
                this.getList()
            }
        }
    }
</script>

<style scoped lang="stylus">
    img {
        height: 36px;
        display: block;
    }
    .tabBox
        width 100%
        height 100%
        display flex
        align-items: center
        .tabBox_img
            width 36px
            height 36px
            img
                width 100%
                height 100%
        .tabBox_tit
            width 60%
            font-size 12px !important
            margin 0 2px 0 10px
            letter-spacing: 1px;
            padding: 5px 0;
            box-sizing: border-box;
    .orderData >>>.ivu-table-cell{padding-left: 0 !important;}
    .vertical-center-modal{
        display: flex;
        align-items: center;
        justify-content: center;}
    .ivu-mt a
       color #515a6e
    .ivu-mt a:hover
        color: #2D8cF0;

</style>
