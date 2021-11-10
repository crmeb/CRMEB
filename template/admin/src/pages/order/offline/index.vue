<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
            </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="pagination" :model="pagination" :label-width="labelWidth" :label-position="labelPosition" @submit.native.prevent>
                <Row type="flex" :gutter="24">
                    <Col v-bind="grid">
                        <FormItem label="订单号：" label-for="title">
                            <Input
                                search
                                enter-button
                                v-model="pagination.order_id"
                                placeholder="请输入订单号"
                                @on-search="orderSearch"
                            />
                        </FormItem>
                    </Col>
                    <Col v-bind="grid">
                        <FormItem label="用户名：" label-for="title">
                            <Input
                                    search
                                    enter-button
                                    v-model="pagination.name"
                                    placeholder="请输入用户名"
                                    @on-search="nameSearch"
                            />
                        </FormItem>
                    </Col>
                    <Col span="24" class="ivu-text-left">
                        <FormItem label="创建时间：">
                            <DatePicker :editable="false" @on-change="onchangeTime" :value="timeVal" format="yyyy/MM/dd"
                                        type="datetimerange" placement="bottom-start" placeholder="请选择时间"
                                        style="width: 300px;" class="mr20" :options="options"></DatePicker>
                        </FormItem>
                    </Col>
                </Row>
                <Row type="flex">
                    <Col v-bind="grid">
                        <Button type="primary" @click="qrcodeShow">查看收款二维码</Button>
                    </Col>
                </Row>
            </Form>
            <Table
                :columns="thead"
                :data="tbody"
                ref="table"
                class="mt25"
                :loading="loading"
                highlight-row
                no-userFrom-text="暂无数据"
                no-filtered-userFrom-text="暂无筛选结果"
            >
            </Table>
            <div class="acea-row row-right page">
                <Page
                    :total="total"
                    :current="pagination.page"
                    show-elevator
                    show-total
                    @on-change="pageChange"
                    :page-size="pagination.limit"
                />
            </div>
        </Card>
        <Modal v-model="modal" title="收款码" footer-hide>
            <div>
                <!--<div class="acea-row row-around mb10">-->
                    <!--<RadioGroup v-model="animal" @on-change="onchangeCode(animal)" style="width: 180px;">-->
                        <!--<Radio :label="0">二维码</Radio>-->
                        <!--<Radio :label="1">收款码</Radio>-->
                    <!--</RadioGroup>-->
                    <!--<div style="width: 180px;"></div>-->
                <!--</div>-->
                <div v-viewer class="acea-row row-around code">
                    <Spin fix v-if="spin"></Spin>
                    <div class="acea-row row-column-around row-between-wrapper">
                        <div class="QRpic">
                            <img v-lazy="qrcode && qrcode.wechat" />
                        </div>
                        <span class="mt10">{{animal?'公众号收款码':'公众号二维码'}}</span>
                    </div>
                    <div class="acea-row row-column-around row-between-wrapper">
                        <div class="QRpic">
                            <img v-lazy="qrcode && qrcode.routine" />
                        </div>
                        <span class="mt10">{{animal?'小程序收款码':'小程序二维码'}}</span>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import { mapState } from "vuex";
import { orderScanList, orderOfflineScan } from "@/api/order";

export default {
    data() {
        return {
            grid: {
                xl: 7,
                lg: 7,
                md: 12,
                sm: 24,
                xs: 24
            },
            thead: [
                {
                    title: "订单号",
                    key: "order_id"
                },
                // {
                //     title: "订单类型",
                //     render: (h, params) => {
                //         return h("span", "线下支付");
                //     }
                // },
                {
                    title: "用户信息",
                    key: "nickname"
                },
                {
                    title: "实际支付",
                    key: "pay_price"
                },
                {
                    title: "优惠价格",
                    key: "true_price"
                },
                // {
                //     title: "订单状态",
                //     render: (h, params) => {
                //         return h("span", "已支付");
                //     }
                // },
                {
                    title: "支付时间",
                    key: "pay_time"
                },
            ],
            tbody: [],
            loading: false,
            total: 0,
            animal:1,
            pagination: {
                page: 1,
                limit: 30,
                order_id:'',
                add_time:''
            },
            options: {
                shortcuts: [
                    {
                        text: '今天',
                        value () {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()))
                            return [start, end]
                        }
                    },
                    {
                        text: '昨天',
                        value () {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)))
                            end.setTime(end.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() -1)))
                            return [start, end]
                        }
                    },
                    {
                        text: '最近7天',
                        value () {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 6)))
                            return [start, end]
                        }
                    },
                    {
                        text: '最近30天',
                        value () {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)))
                            return [start, end]
                        }
                    },
                    {
                        text: '本月',
                        value () {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), 1)))
                            return [start, end]
                        }
                    },
                    {
                        text: '本年',
                        value () {
                            const end = new Date()
                            const start = new Date()
                            start.setTime(start.setTime(new Date(new Date().getFullYear(), 0, 1)))
                            return [start, end]
                        }
                    }
                ]
            },
            timeVal:[],
            modal: false,
            qrcode: null,
            name: '',
            spin:false
        };
    },
    computed: {
        ...mapState("media", ["isMobile"]),
        labelWidth() {
            return this.isMobile ? undefined : 75;
        },
        labelPosition() {
            return this.isMobile ? "top" : "right";
        }
    },
    created() {
        this.getOrderList();
    },
    methods: {
        onchangeCode (e) {
            this.animal = e;
            this.qrcodeShow();
        },
        // 具体日期搜索()；
        onchangeTime (e) {
            this.pagination.page = 1;
            this.timeVal = e;
            this.pagination.add_time = this.timeVal[0] ? this.timeVal.join('-') : '';
            this.getOrderList();
        },
        // 订单列表
        getOrderList() {
            this.loading = true;
            orderScanList(this.pagination)
                .then(res => {
                    this.loading = false;
                    const { count, list } = res.data;
                    this.total = count;
                    this.tbody = list;
                })
                .catch(err => {
                    this.loading = false;
                    this.$Message.error(err.msg);
                });
        },
        // 分页
        pageChange(index) {
            this.pagination.page = index;
            this.getOrderList();
        },
        nameSearch() {
            this.pagination.page = 1;
            this.getOrderList();
        },
        // 订单搜索
        orderSearch() {
            this.pagination.page = 1;
            this.getOrderList();
        },
        // 查看二维码
        qrcodeShow() {
            this.spin = true;
            orderOfflineScan({type:this.animal})
                .then(res => {
                    this.spin = false;
                    this.qrcode = res.data;
                    this.modal = true;
                })
                .catch(err => {
                    this.spin = false;
                    this.$Message.error(err.msg);
                });
        }
    }
};
</script>

<style lang="less" scoped>
.code{
    position: relative;
}
.QRpic {
    width: 180px;
    height: 259px;

    img {
        width: 100%;
        height: 100%;
    }
}
</style>
