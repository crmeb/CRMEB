<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">{{$route.meta.title}}</span>
            </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Row type="flex" class="mb20">
                <Col span="24">
                    <Button
                            v-auth="['setting-delivery_service-add']"
                            type="primary"
                            icon="md-add"
                            @click="add"
                            class="mr10"
                    >添加配送员</Button
                    >
                </Col>
            </Row>
            <Table
                    :columns="columns1"
                    :data="data1"
                    :loading="loading"
                    highlight-row
                    no-userFrom-text="暂无数据"
                    no-filtered-userFrom-text="暂无筛选结果"
            >
                <template slot-scope="{ row, index }" slot="avatar">
                    <div class="tabBox_img" v-viewer>
                        <img v-lazy="row.avatar" />
                    </div>
                </template>
                <template slot-scope="{ row, index }" slot="status">
                    <i-switch
                            v-model="row.status"
                            :value="row.status"
                            :true-value="1"
                            :false-value="0"
                            @on-change="onchangeIsShow(row)"
                            size="large"
                    >
                        <span slot="open">显示</span>
                        <span slot="close">隐藏</span>
                    </i-switch>
                </template>
                <template slot-scope="{ row, index }" slot="add_time">
                    <span> {{ row.add_time | formatDate }}</span>
                </template>

                <template slot-scope="{ row, index }" slot="action">
                    <a @click="edit(row)">编辑</a>
                    <Divider type="vertical" />
                    <a @click="del(row, '删除配送员', index)">删除</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total=total :current="tableOptions.page" show-elevator show-total :page-size="tableOptions.limit" @on-change="pageChange"/>
            </div>
        </Card>
    </div>
</template>

<script>
    import { mapState } from 'vuex';
    import {
        deliveryList,
        orderDeliveryAdd,
        orderDeliveryEdit,
        orderDeliveryStatus
    } from '@/api/order';

    export default {
        name: 'index',
        computed: {
            ...mapState('media', [
                'isMobile'
            ])
        },
        data () {
            return {
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '头像',
                        slot: 'avatar',
                        minWidth: 60
                    },
                    {
                        title: '名称',
                        key: 'nickname',
                        minWidth: 120
                    },
                    {
                        title: '手机号码',
                        key: 'phone',
                        minWidth: 120
                    },
                    {
                        title: '是否显示',
                        slot: 'status',
                        minWidth: 120
                    },
                    {
                        title: '添加时间',
                        key: 'add_time',
                        minWidth: 120
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 150
                    }
                ],
                data1: [],
                total: 0,
                tableOptions: {
                    page: 1,
                    limit: 15
                },
                loading: false
            };
        },
        created () {
            this.getOrderDeliveryList();
        },
        methods: {
            // 配送员列表
            getOrderDeliveryList () {
                this.loading = true;
                deliveryList(this.tableOptions)
                    .then((res) => {
                        this.data1 = res.data.list;
                        this.total = res.data.count;
                        this.loading = false;
                    })
                    .catch((err) => {
                        this.loading = false;
                        this.$Message.error(err.msg);
                    });
            },
            // 添加配送员
            add () {
                this.$modalForm(orderDeliveryAdd()).then(() => this.getOrderDeliveryList());
            },
            // 编辑
            edit (row) {
                this.$modalForm(orderDeliveryEdit(row.id)).then(() => this.getOrderDeliveryList());
            },
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `/order/delivery/del/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                };
                this.$modalSure(delfromData)
                    .then((res) => {
                        this.$Message.success(res.msg);
                        this.data1.splice(num, 1);
                    })
                    .catch((res) => {
                        this.$Message.error(res.msg);
                    });
            },
            // 是否显示
            onchangeIsShow (row) {
                orderDeliveryStatus(row)
                    .then((res) => {
                        this.$Message.success(res.msg);
                    })
                    .catch((err) => {
                        this.$Message.error(err.msg);
                    });
            },
            pageChange (index) {
                this.tableOptions.page = index;
                this.getOrderDeliveryList();
            }
        }
    };
</script>

<style lang="less" scoped>
    .tabBox_img {
        width: 36px;
        height: 36px;
        border-radius: 4px;
        cursor: pointer;

        img {
            width: 100%;
            height: 100%;
        }
    }
</style>
