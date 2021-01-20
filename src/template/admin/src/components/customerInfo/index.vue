<template>
    <div class="customer">
        <Form ref="formValidate" :model="formValidate"  :label-width="100" @submit.native.prevent>
            <Row :gutter="24" type="flex">
                <Col span="24" class="ivu-text-left">
                    <FormItem label="搜索日期：">
                        <RadioGroup v-model="formValidate.data" type="button" @on-change="selectChange(formValidate.data)"
                                    class="mr">
                            <Radio :label=item.val v-for="(item,i) in fromList.fromTxt" :key="i">{{item.text}}</Radio>
                        </RadioGroup>
                        <DatePicker :editable="false" @on-change="onchangeTime" :value="timeVal" format="yyyy/MM/dd" type="daterange"
                                    placement="bottom-end" placeholder="自定义时间" style="width: 200px;"></DatePicker>
                    </FormItem>
                </Col>
                <Col span="12" class="ivu-text-left">
                    <FormItem label="用户名称：" >
                        <Input search enter-button  placeholder="请输入用户名称" v-model="formValidate.nickname" style="width: 90%;" @on-search="userSearchs"></Input>
                    </FormItem>
                </Col>
            </Row>
        </Form>
        <Table :loading="loading2" highlight-row no-userFrom-text="暂无数据"
               no-filtered-userFrom-text="暂无筛选结果" ref="selection" :columns="columns4" :data="tableList2">
            <template slot-scope="{ row, index }" slot="headimgurl">
                <div class="tabBox_img" v-viewer>
                    <img v-lazy="row.headimgurl">
                </div>
            </template>
            <template slot-scope="{ row, index }" slot="sex">
                <span v-show="row.sex ===1">男</span>
                <span v-show="row.sex ===2">女</span>
                <span v-show="row.sex ===0">保密</span>
            </template>
            <template slot-scope="{ row, index }" slot="country">
                <span>{{row.country + row.province + row.city}}</span>
            </template>
            <template slot-scope="{ row, index }" slot="subscribe">
                <span v-text="row.subscribe === 1?'关注':'未关注'"></span>
            </template>
        </Table>
        <div class="acea-row row-right page">
            <Page :total="total2" show-elevator show-total @on-change="pageChange2"
                  :page-size="formValidate.limit"/>
        </div>
    </div>
</template>
<script>
    import { kefucreateApi } from '@/api/setting'
    export default {
        name: 'index',
        data () {
            return {
                formValidate: {
                    page: 1,
                    limit: 15,
                    data: '',
                    nickname: ''
                },
                tableList2: [],
                timeVal: [],
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
                currentid: 0,
                productRow: {},
                columns4: [
                    {
                        title: '选择',
                        key: 'chose',
                        width: 60,
                        align: 'center',
                        render: (h, params) => {
                            let uid = params.row.uid
                            let flag = false
                            if (this.currentid === uid) {
                                flag = true
                            } else {
                                flag = false
                            }
                            let self = this
                            return h('div', [
                                h('Radio', {
                                    props: {
                                        value: flag
                                    },
                                    on: {
                                        'on-change': () => {
                                            self.currentid = uid
                                            this.productRow = params.row
                                            if (this.productRow.uid) {
                                                if (this.$route.query.fodder === 'image') {
                                                    /* eslint-disable */
                                                    let imageObject = {
                                                        image: this.productRow.headimgurl,
                                                        uid: this.productRow.uid
                                                    };
                                                    form_create_helper.set('image', imageObject);
                                                    form_create_helper.close('image');
                                                }else {
                                                    this.$emit('imageObject',{
                                                        image: this.productRow.headimgurl,
                                                        uid: this.productRow.uid
                                                    });
                                                }
                                            } else {
                                                this.$Message.warning('请先选择商品');
                                            }
                                        }
                                    }
                                })
                            ])
                        }
                    },
                    {
                        title: 'ID',
                        key: 'uid',
                        width: 80
                    },
                    {
                        title: '微信用户名称',
                        key: 'nickname',
                        minWidth: 180
                    },
                    {
                        title: '客服头像',
                        slot: 'headimgurl',
                        minWidth: 60
                    },
                    {
                        title: '性别',
                        slot: 'sex',
                        minWidth: 60
                    },
                    {
                        title: '地区',
                        slot: 'country',
                        minWidth: 120
                    },
                    {
                        title: '是否关注公众号',
                        slot: 'subscribe',
                        minWidth: 120
                    }
                ],
                loading2: false,
                total2: 0
            }
        },
        created () {
        },
        mounted () {
            this.getListService();
        },
        methods: {
            // 具体日期
            onchangeTime (e) {
                this.timeVal = e;
                this.formValidate.data = this.timeVal.join('-');
                this.getListService();
            },
            // 选择时间
            selectChange (tab) {
                this.formValidate.data = tab;
                this.timeVal = [];
                this.getListService();
            },
            // 客服列表
            getListService () {
                this.loading2 = true;
                kefucreateApi(this.formValidate).then(async res => {
                    let data = res.data;
                    this.tableList2 = data.list;
                    this.total2 = data.count;
                    this.tableList2.map((item) => {
                        item._isChecked = false;
                    });
                    this.loading2 = false;
                }).catch(res => {
                    this.loading2 = false;
                    this.$Message.error(res.msg);
                })
            },
            pageChange2 (pageIndex) {
                this.formValidate.page = pageIndex;
                this.getListService();
            },
            // 搜索
            userSearchs () {
                this.formValidate.page = 1;
                this.getListService();
            },
        }
    }
</script>

<style scoped lang="stylus">
    .customer
      overflow-y auto
      overflow-x hidden
      height 500px
    .tabBox_img
        width 36px
        height 36px
        border-radius:4px;
        cursor pointer
        img
            width 100%
            height 100%
    .modelBox
        >>>
        .ivu-table-header
            width 100% !important
    .trees-coadd
        width: 100%;
        height: 385px;
        .scollhide
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            overflow-y: scroll;
    .scollhide::-webkit-scrollbar {
        display: none;
    }
    .footer{
        margin: 15px 0;
        padding-right: 20px;
    }
</style>
