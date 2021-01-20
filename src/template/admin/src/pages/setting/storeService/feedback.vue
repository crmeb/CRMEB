<template>
    <div>
        <div class="i-layout-page-header">
            <div class="i-layout-page-header">
                <span class="ivu-page-header-title">{{$route.meta.title}}</span>
            </div>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <Form ref="formValidate" :model="formValidate" :label-width="labelWidth" :label-position="labelPosition" class="tabform" @submit.native.prevent>
                <Row :gutter="24" type="flex" justify="end">
                    <Col span="24" class="ivu-text-left">
                        <FormItem label="留言信息：">
                            <Input search enter-button @on-search="selChange" placeholder="请输入用户昵称/电话/留言内容搜索" element-id="name" v-model="formValidate.title" style="width: 30%;display: inline-table;" class="mr"/>
                        </FormItem>
                    </Col>
                    <Col span="24" class="ivu-text-left">
                        <FormItem label="留言时间：">
                            <RadioGroup v-model="formValidate.time" type="button"  @on-change="selectChange(formValidate.time)" class="mr">
                                <Radio :label=item.val v-for="(item,i) in fromList.fromTxt" :key="i">{{item.text}}</Radio>
                            </RadioGroup>
                            <DatePicker :editable="false" @on-change="onchangeTime" :value="timeVal"  format="yyyy/MM/dd" type="daterange" placement="bottom-end" placeholder="自定义时间" style="width: 200px;"></DatePicker>
                        </FormItem>
                    </Col>
                </Row>
            </Form>
            <Table :columns="columns1" :data="list"
                   :loading="loading"
                   no-userFrom-text="暂无数据"
                   no-filtered-userFrom-text="暂无筛选结果">
                <template slot-scope="{ row, index }" slot="status">
                    <div>{{row.status===1?'已处理':'未处理'}}</div>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a @click="remarks(row.id)">{{row.status===1?'备注':'处理'}}</a>
                    <Divider type="vertical"/>
                    <a @click="del(row,'删除反馈',index)">删除</a>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="count" show-elevator show-total @on-change="pageChange"
                      :page-size="limit"/>
            </div>
        </Card>
    </div>
</template>

<script>
    import { kefuFeedBack, kefuFeedBackEdit } from '@/api/setting'
    import { mapState } from 'vuex'
    export default {
        name: "feedback",
        data(){
            return {
                loading: false,
                list:[],
                page:1,
                limit:15,
                formValidate:{
                    time: '',
                    title: ''
                },
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
                count:0,
                columns1: [
                    {
                        title: 'ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '昵称',
                        key: 'rela_name',
                        minWidth: 120
                    },
                    {
                        title: '电话',
                        key: 'phone',
                        minWidth: 120
                    },
                    {
                        title: '内容',
                        key: 'content',
                        minWidth: 320
                    },
                    {
                        title: '状态',
                        slot: 'status',
                        minWidth: 120
                    },
                    {
                        title: '时间',
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
                return this.isMobile ? 'top' : 'right'
            }
        },
        created() {
            this.getList()
        },
        methods:{
            //备注；
            remarks (id){
                this.$modalForm(kefuFeedBackEdit(id)).then(() => this.getList());
            },
            // 选择
            selChange () {
                this.page = 1;
                this.getList()
            },
            // 选择时间
            selectChange (tab) {
                this.formValidate.time = tab
                this.timeVal = []
                this.page = 1
                this.getList()
            },
            // 具体日期
            onchangeTime (e) {
                this.timeVal = e
                this.formValidate.time = this.timeVal.join('-')
                this.page = 1
                this.getList()
            },
            getList(){
                kefuFeedBack({
                    page:this.page,
                    limit:this.limit,
                    time: this.formValidate.time,
                    title: this.formValidate.title
                }).then(res=>{
                    this.list = res.data.data
                    this.count = res.data.count
                })
            },
            // 删除
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `/app/feedback/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                };
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg);
                    this.list.splice(num, 1);
                }).catch(res => {
                    this.$Message.error(res.msg);
                });
            },
            pageChange (index) {
                this.page = index;
                this.getList();
            },
        }
    }
</script>

<style scoped>

</style>
