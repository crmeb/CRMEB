<template>
    <div>
        <Table :columns="columns" :data="orderList"
               ref="table"
               :loading="loading" highlight-row
               no-data-text="暂无数据"
               no-filtered-data-text="暂无筛选结果"
               @on-selection-change="onSelectTab"
               class="orderData mt25"
        >
            <template slot-scope="{ row, index }" slot="order_id">
                <span v-text="row.order_id" style="display: block;"></span>
                <span v-show="row.is_del === 1" style="color: #ED4014;display: block;">用户已删除</span>
            </template>
            <template slot-scope="{ row, index }" slot="info">
                <div class="tabBox" v-for="(val, i ) in row._info" :key="i">
                    <div class="tabBox_img" v-viewer>
                        <img v-lazy="val.cart_info.productInfo.attrInfo?val.cart_info.productInfo.attrInfo.image:val.cart_info.productInfo.image">
                    </div>
                    <span class="tabBox_tit">{{ val.cart_info.productInfo.store_name + ' | ' }}{{val.cart_info.productInfo.attrInfo?val.cart_info.productInfo.attrInfo.suk:''}}</span>
                    <span class="tabBox_pice">{{ '￥'+ val.cart_info.truePrice + ' x '+ val.cart_info.cart_num}}</span>
                </div>
            </template>
            <template slot-scope="{ row, index }" slot="statusName">
                <div v-html="row.status_name.status_name" class="pt5"></div>
                <div v-viewer v-if="row.status_name.pics" class="pictrue mr10" v-for="(item,index) in row.status_name.pics || []" :key="index"><img v-lazy="item" :src="item" /></div>
            </template>
            <template slot-scope="{ row, index }" slot="action">
                <a @click="edit(row)"  v-if='row._status === 1'>编辑</a>
                <a @click="sendOrder(row)" v-if='row._status === 2 && row.shipping_type === 1'>发送货</a>
                <a @click="delivery(row)"  v-if='row._status === 4'>配送信息</a>
                <a @click="bindWrite(row)"  v-if="row.shipping_type == 2 && row.status == 0  && row.paid == 1 && row.refund_status === 0">立即核销</a>
                <Divider type="vertical" v-if='row._status === 1 || row._status === 2 || row._status === 4 || (row.shipping_type == 2 && row.status == 0  && row.paid == 1 && row.refund_status === 0)'/>
                <template>
                    <Dropdown @on-click="changeMenu(row,$event)">
                        <a href="javascript:void(0)">更多
                            <Icon type="ios-arrow-down"></Icon>
                        </a>
                        <DropdownMenu slot="list">
                            <DropdownItem name="1" ref="ones" v-show='row._status === 1 && row.paid === 0 && row.pay_type === "offline" '>立即支付</DropdownItem>
                            <DropdownItem name="2">订单详情</DropdownItem>
                            <DropdownItem name="3">订单记录</DropdownItem>
                            <DropdownItem name="4"  v-show='row._status !==1 || ( row._status === 3 && (row.use_integral > 0 && row.use_integral >= row.back_integral) ) '>订单备注</DropdownItem>
                            <DropdownItem name="5"  v-show='row._status !==1 && ((parseFloat(row.pay_price) > parseFloat(row.refund_price) || (row.pay_price == 0 && [0,1].indexOf(row.refund_status) !== -1)))'>立即退款</DropdownItem>
                            <DropdownItem name="7"  v-show='row._status === 3'>不退款</DropdownItem>
                            <DropdownItem name="8"  v-show='row._status === 4'>已收货</DropdownItem>
                            <DropdownItem name="9">删除订单</DropdownItem>
                        </DropdownMenu>
                    </Dropdown>
                </template>
            </template>
        </Table>
        <div class="acea-row row-right page">
            <Page :total="page.total" :current="page.pageNum" show-elevator show-total @on-change="pageChange"
                  :page-size="page.pageSize" @on-page-size-change="limitChange" show-sizer/>
        </div>
        <!-- 编辑 退款 退积分 不退款-->
        <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
        <!-- 详情 -->
        <details-from ref="detailss" :orderDatalist="orderDatalist" :orderId="orderId"></details-from>
        <!-- 备注 -->
        <order-remark ref="remarks" :orderId="orderId" @submitFail="submitFail"></order-remark>
        <!-- 记录 -->
        <order-record ref="record"></order-record>
        <!-- 发送货 -->
        <order-send ref="send" :orderId="orderId" @submitFail="submitFail"></order-send>
    </div>
</template>

<script>
    import expandRow from './tableExpand.vue';
    import { orderList, getOrdeDatas, getDataInfo, getRefundFrom, getnoRefund, refundIntegral, getDistribution, writeUpdate } from '@/api/order';
    import { mapState, mapMutations } from 'vuex';
    import editFrom from '../../../../components/from/from';
    import detailsFrom from '../handle/orderDetails';
    import orderRemark from '../handle/orderRemark';
    import orderRecord from '../handle/orderRecord';
    import orderSend from '../handle/orderSend';

    export default {
        name: 'table_list',
        components: { expandRow, editFrom, detailsFrom, orderRemark, orderRecord, orderSend },
        data () {
            return {
                delfromData: {},
                modal: false,
                orderList: [],
                orderCards: [],
                loading: false,
                orderId: 0,
                columns: [
                    {
                        type: 'expand',
                        width: 30,
                        render: (h, params) => {
                            return h(expandRow, {
                                props: {
                                    row: params.row
                                }
                            })
                        }
                    },
                    {
                        type: 'selection',
                        width: 40,
                        align: 'center'
                    },
                    {
                        title: '订单号',
                        align: 'center',
                        slot: 'order_id',
                        minWidth: 150
                    },
                    {
                        title: '订单类型',
                        key: 'pink_name',
                        minWidth: 120
                    },
                    {
                        title: '用户信息',
                        key: 'nickname',
                        minWidth: 100
                    },
                    {
                        title: '商品信息',
                        slot: 'info',
                        minWidth: 330
                    },
                    {
                        title: '实际支付',
                        key: 'pay_price',
                        minWidth: 70
                    },
                    {
                        title: '支付时间',
                        key: '_pay_time',
                        minWidth: 100
                    },
                    {
                        title: '支付状态',
                        key: 'pay_type_name',
                        minWidth: 80
                    },
                    {
                        title: '订单状态',
                        key: 'statusName',
                        slot: 'statusName',
                        minWidth: 100
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 150,
                        align: 'center'
                    }
                ],
                page: {
                    total: 0, // 总条数
                    pageNum: 1, // 当前页
                    pageSize: 10 // 每页显示条数
                },
                data: [],
                FromData: null,
                orderDatalist: null,
                modalTitleSs: '',
                isDelIdList: []
            }
        },
        computed: {
            ...mapState('order', [
                'orderStatus',
                'orderTime',
                'orderNum',
                'fieldKey',
                'orderType'
            ])
        },
        mounted () {},
        created () {
            this.getList();
        },
        watch: {
            'orderType': function () {
                this.page.pageNum = 1;
                this.getList();
            }
        },
        methods: {
            ...mapMutations('order', [
                'getIsDel',
                'getisDelIdListl'
            ]),
            // 操作
            changeMenu (row, name) {
                this.orderId = row.id;
                switch (name) {
                    case '1':
                        this.delfromData = {
                            title: '修改立即支付',
                            url: `/order/pay_offline/${row.id}`,
                            method: 'post',
                            ids: ''
                        };
                        this.$modalSure(this.delfromData).then((res) => {
                            this.$Message.success(res.msg);
                            this.$emit('changeGetTabs');
                            this.getList();
                        }).catch(res => {
                            console.log(res);
                            this.$Message.error(res.msg);
                        });
                        // this.modalTitleSs = '修改立即支付';
                        break;
                    case '2':
                        this.getData(row.id);
                        break;
                    case '3':
                        this.$refs.record.modals = true;
                        this.$refs.record.getList(row.id);
                        break;
                    case '4':
                        this.$refs.remarks.modals = true;
                        break;
                    case '5':
                        this.getRefundData(row.id);
                        break;
                    case '6':
                        this.getRefundIntegral(row.id);
                        break;
                    case '7':
                        this.getNoRefundData(row.id);
                        break;
                    case '8':
                        this.delfromData = {
                            title: '修改确认收货',
                            url: `/order/take/${row.id}`,
                            method: 'put',
                            ids: ''
                        };
                        this.$modalSure(this.delfromData).then((res) => {
                            this.$Message.success(res.msg);
                            this.getList();
                        }).catch(res => {
                            this.$Message.error(res.msg);
                        });
                        // this.modalTitleSs = '修改确认收货';
                        break;
                    default:
                        this.delfromData = {
                            title: '删除订单',
                            url: `/order/del/${row.id}`,
                            method: 'DELETE',
                            ids: ''
                        };
                        // this.modalTitleSs = '删除订单';
                        this.delOrder(row, this.delfromData);
                }
            },
            // 立即支付 /确认收货//删除单条订单
            submitModel () {
                this.getList();
            },
            pageChange (index) {
                this.page.pageNum = index;
                this.getList();
            },
            limitChange (limit) {
                this.page.pageSize = limit;
                this.getList();
            },
            // 订单列表
            getList (res) {
                this.page.pageNum = res === 1 ? 1 : this.page.pageNum;
                this.loading = true;
                orderList({
                    page: this.page.pageNum,
                    limit: this.page.pageSize,
                    status: this.orderStatus,
                    data: this.orderTime,
                    real_name: this.orderNum,
                    field_key: this.fieldKey,
                    type: this.orderType === 0 ? '' : this.orderType
                }).then(async res => {
                    let data = res.data;
                    this.orderList = data.data;
                    this.orderCards = data.stat;
                    this.page.total = data.count;
                    this.$emit('on-changeCards', data.stat);
                    this.loading = false;
                }).catch(res => {
                    this.loading = false;
                    this.$Message.error(res.msg);
                })
            },
            // 全选
            onSelectTab (selection) {
                let isDel = selection.some(item => {
                    return item.is_del === 1
                });
                this.getIsDel(isDel);
                this.getisDelIdListl(selection);
            },
            // 编辑
            edit (row) {
                this.getOrderData(row.id);
            },
            // 删除单条订单
            delOrder (row, data) {
                if (row.is_del === 1) {
                    this.$modalSure(data).then((res) => {
                        this.$Message.success(res.msg);
                        this.getList();
                    }).catch(res => {
                        this.$Message.error(res.msg);
                    });
                } else {
                    const title = '错误！';
                    const content = '<p>您选择的的订单存在用户未删除的订单，无法删除用户未删除的订单！</p>';
                    this.$Modal.error({
                        title: title,
                        content: content
                    });
                }
            },
            // 获取编辑表单数据
            getOrderData (id) {
                getOrdeDatas(id).then(async res => {
                    if (res.data.status === false) {
                        return this.$authLapse(res.data);
                    }
                    this.$authLapse(res.data);
                    this.FromData = res.data;
                    this.$refs.edits.modals = true;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 获取详情表单数据
            getData (id) {
                getDataInfo(id).then(async res => {
                    this.$refs.detailss.modals = true;
                    this.orderDatalist = res.data;
                    if (this.orderDatalist.orderInfo.refund_reason_wap_img) {
                        try {
                            this.orderDatalist.orderInfo.refund_reason_wap_img = JSON.parse(this.orderDatalist.orderInfo.refund_reason_wap_img);
                        } catch (e) {
                            this.orderDatalist.orderInfo.refund_reason_wap_img = [];
                        }
                    }
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 修改成功
            submitFail () {
                this.getList();
            },
            // 获取退款表单数据
            getRefundData (id) {
                this.$modalForm(getRefundFrom(id)).then(() => {
                    this.getList();
                    this.$emit('changeGetTabs');
                });
            },
            // 获取退积分表单数据
            getRefundIntegral (id) {
                refundIntegral(id).then(async res => {
                    this.FromData = res.data;
                    this.$refs.edits.modals = true;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            // 不退款表单数据
            getNoRefundData (id) {
                this.$modalForm(getnoRefund(id)).then(() => {
                    this.getList();
                    this.$emit('changeGetTabs');
                });
            },
            // 发送货
            sendOrder (row) {
                this.$refs.send.modals = true;
                this.$refs.send.getList();
                this.$refs.send.getDeliveryList();
                this.orderId = row.id;
            },
            // 配送信息表单数据
            delivery (row) {
                getDistribution(row.id).then(async res => {
                    this.FromData = res.data;
                    this.$refs.edits.modals = true;
                }).catch(res => {
                    this.$Message.error(res.msg);
                })
            },
            change (status) {
                console.log(status);
            },
            // 数据导出；
            exportData: function () {
                this.$refs.table.exportCsv({
                    filename: '商品列表'
                });
            },
            // 核销订单
            bindWrite (row) {
                let self = this
                this.$Modal.confirm({
                    title: '提示',
                    content: '确定要核销该订单吗？',
                    cancelText: '取消',
                    closable: true,
                    maskClosable: true,
                    onOk: function () {
                        writeUpdate(row.order_id).then(res => {
                            self.$Message.success(res.msg)
                            self.getList();
                        })
                    }
                });
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
</style>
