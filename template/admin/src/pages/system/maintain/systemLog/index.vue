<template>
    <div>
        <div class="i-layout-page-header">
          <div class="i-layout-page-header">
            <span class="ivu-page-header-title">{{$route.meta.title}}</span>
          </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <div class="table_box">
                <Form ref="formValidate" :label-width="labelWidth" :label-position="labelPosition" class="tabform" @submit.native.prevent>
                    <Row :gutter="24" type="flex" justify="end">
                        <Col span="24" class="ivu-text-left">
                            <FormItem :label="fromList.title+'：'">
                                <RadioGroup type="button" v-model="formValidate.data" class="mr15"
                                            @on-change="selectChange(formValidate.data)">
                                    <Radio :label="itemn.val" v-for="(itemn,indexn) in fromList.fromTxt" :key="indexn">
                                        {{itemn.text}}
                                    </Radio>
                                </RadioGroup>
                                <DatePicker :editable="false" @on-change="onchangeTime" :value="timeVal" format="yyyy/MM/dd"
                                            type="daterange" placement="bottom-end" placeholder="请选择时间"
                                            style="width: 200px;"></DatePicker>
                            </FormItem>
                        </Col>
                        <Col span="24" class="ivu-text-left">
                            <Col :xl="5" :lg="12" :md="12" :sm="24" :xs="24" class="sex_box">
                                <FormItem label="名称：">
                                    <Select v-model="formValidate.admin_id" style="width: 90%;" clearable @on-change="userSearchs">
                                        <Option :value="item.id" v-for="(item,index) in dataList" :key="index">{{item.real_name}}</Option>
                                    </Select>
                                </FormItem>
                            </Col>
                            <!--<Col :xl="5" :lg="12" :md="12" :sm="24" :xs="24">-->
                                <!--<FormItem label="行为：">-->
                                    <!--<Input  placeholder="请输入行为" v-model="formValidate.pages" style="width: 90%;" clearable></Input>-->
                                <!--</FormItem>-->
                            <!--</Col>-->
                            <Col :xl="5" :lg="12" :md="12" :sm="24" :xs="24" class="subscribe_box">
                                <FormItem label="链接：">
                                    <Input  placeholder="请输入链接" v-model="formValidate.path" style="width: 90%;" clearable></Input>
                                </FormItem>
                            </Col>
                            <Col :xl="5" :lg="12" :md="12" :sm="24" :xs="24" class="subscribe_box">
                                <FormItem label="IP：">
                                    <Input  placeholder="请输入IP" v-model="formValidate.ip" style="width: 90%;" clearable></Input>
                                </FormItem>
                            </Col>
                            <Col :xl="3" :lg="12" :md="3" :sm="24" :xs="24" class="btn_box">
                                <!--<FormItem>-->
                                    <Button type="primary" icon="ios-search" label="default" class="userSearch"  @click="userSearchs">搜索</Button>
                                <!--</FormItem>-->
                            </Col>
                        </Col>
                    </Row>
                </Form>
            </div>
            <Table ref="selection" :columns="columns4" :data="tabList" :loading="loading"
                   no-data-text="暂无数据" highlight-row
                   no-filtered-data-text="暂无筛选结果">
                <template slot-scope="{ row }" slot="nickname">
                    <span>{{row.admin_id + ' / ' + row.admin_name }}</span>
                </template>
                <template slot-scope="{ row, index }" slot="add_time">
                    <span> {{row.add_time | formatDate}}</span>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" :current="formValidate.page" show-elevator show-total @on-change="pageChange"
                      :page-size="formValidate.limit"  /></div>
        </Card>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { searchAdminApi, systemListApi } from '@/api/system'
    import { formatDate } from '@/utils/validate'
    export default {
        name: 'systemLog',
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
                formValidate: {
                    limit: 20,
                    page: 1,
                    pages: '',
                    data: '',
                    path: '',
                    ip: '',
                    admin_id: ''
                },
                loading: false,
                tabList: [],
                total: 0,
                columns4: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: 'ID/名称',
                        slot: 'nickname',
                        minWidth: 100
                    },
                    // {
                    //     title: '行为',
                    //     key: 'page',
                    //     minWidth: 150
                    // },
                    {
                        title: '链接',
                        key: 'path',
                        minWidth: 300
                    },
                    {
                        title: '操作ip',
                        key: 'ip',
                        minWidth: 150
                    },
                    {
                        title: '类型',
                        key: 'type',
                        minWidth: 100
                    },
                    {
                        title: '操作时间',
                        slot: 'add_time',
                        minWidth: 150
                    }
                ],
                dataList: []
            }
        },
        computed: {
            ...mapState('media', [
                'isMobile'
            ]),
            labelWidth () {
                return this.isMobile ? undefined : 75
            },
            labelPosition () {
                return this.isMobile ? 'top' : 'right'
            }
        },
        created () {
            this.getSearchAdmin()
            this.getList()
        },
        methods: {
            // 具体日期
            onchangeTime (e) {
                this.timeVal = e
                this.formValidate.data = this.timeVal.join('-')
                this.formValidate.page = 1
                this.getList()
            },
            // 选择时间
            selectChange (tab) {
                this.formValidate.data = tab
                this.timeVal = []
                this.formValidate.page = 1
                this.getList()
            },
            // 搜索条件
            getSearchAdmin () {
                searchAdminApi().then(async res => {
                    this.dataList = res.data.info
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 列表
            getList () {
                this.loading = true
                systemListApi(this.formValidate).then(async res => {
                    let data = res.data
                    this.tabList = data.list
                    this.total = data.count
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
            // 搜索
            userSearchs () {
                this.formValidate.page = 1
                this.getList()
            }
        }
    }
</script>

<style scoped>

</style>
