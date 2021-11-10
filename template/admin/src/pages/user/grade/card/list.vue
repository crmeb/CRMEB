<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
            </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form
                ref="formData"
                :model="table"
                :label-width="labelWidth"
                :label-position="labelPosition"
                inline
                @submit.native.prevent
            >
                <FormItem label="卡号：" style="width:200px">
                    <Input v-model="table.card_number" placeholder="请输入卡号" />
                </FormItem>
                <FormItem label="手机号：" style="width:200px">
                    <Input v-model="table.phone" placeholder="请输入手机号" />
                </FormItem>
                <FormItem label="是否领取：" style="width:200px">
                    <Select clearable v-model="table.is_use">
                        <Option value="1">已领取</Option>
                        <Option value="0">未领取</Option>
                    </Select>
                </FormItem>
                <FormItem>
                    <Button type="primary" @click="formSubmit">搜索</Button>
                </FormItem>
            </Form>
            <Table
                :columns="columns1"
                :data="data1"
                ref="table"
                class="mt25"
                :loading="loading"
                highlight-row
                no-userFrom-text="暂无数据"
                no-filtered-userFrom-text="暂无筛选结果"
            >
                <template slot-scope="{ row, index }" slot="status">
                    <i-switch
                        v-model="row.status"
                        :value="row.status"
                        :true-value="1"
                        :false-value="0"
                        @on-change="onchangeIsShow(row)"
                        size="large"
                    >
                        <span slot="open">激活</span>
                        <span slot="close">冻结</span>
                    </i-switch>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page
                    :total="total"
                    :current="table.page"
                    :page-size="table.limit"
                    show-elevator
                    show-total
                    @on-change="pageChange"
                />
            </div>
        </Card>
    </div>
</template>

<script>
import { userMemberCard, memberRecord, memberCardStatus } from "@/api/user";
import { mapState } from "vuex";

export default {
    name: "card",
    data() {
        return {
            columns1: [
                {
                    title: "编号",
                    key: "id",
                    minWidth: 100
                },
                {
                    title: "卡号",
                    key: "card_number",
                    minWidth: 100
                },
                {
                    title: "密码",
                    key: "card_password",
                    minWidth: 100
                },
                {
                    title: "领取人名称",
                    key: "username",
                    minWidth: 100
                },
                {
                    title: "领取人电话",
                    key: "phone",
                    minWidth: 100
                },
                {
                    title: "领取时间",
                    key: "use_time",
                    minWidth: 100
                },
                {
                    title: "是否激活",
                    slot: "status",
                    minWidth: 100
                }
            ],
            data1: [],
            loading: false,
            total: 0,
            table: {
                page: 1,
                limit: 15,
                card_number: "",
                phone: "",
                is_use: ""
            }
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
        this.getMemberCard();
    },
    methods: {
        onchangeIsShow(row){
            let data = {
                card_id: row.id,
                status: row.status
            };
            memberCardStatus(data).then(res=>{
                this.$Message.success(res.msg);
                this.getMemberCard();
            }).catch(err=>{
                this.$Message.error(err.msg);
            })
        },
        getMemberCard() {
            this.loading = true;
            userMemberCard(this.$route.params.id, this.table)
                .then(res => {
                    this.loading = false;
                    this.data1 = res.data.list;
                    this.total = res.data.count;
                })
                .catch(err => {
                    this.loading = false;
                    this.$Message.error(err.msg);
                });
        },
        // 搜索
        formSubmit() {
            this.table.page = 1;
            this.getMemberCard();
        },
        // 分页
        pageChange(index) {
            this.table.page = index;
            this.getMemberCard();
        }
    }
};
</script>
