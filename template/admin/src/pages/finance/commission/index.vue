<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form ref="formValidate" :label-width="labelWidth" :label-position="labelPosition" class="tabform"
            @submit.native.prevent>
        <Row :gutter="24" type="flex">
          <Col :xl="5" :lg="8" :md="10" :sm="11" :xs="24" class="mr10">
            <FormItem label="昵称/ID：">
              <Input enter-button placeholder="请输入" element-id="nickname" v-model="formValidate.nickname" clearable/>
            </FormItem>
          </Col>
          <Col :xl="8" :lg="12" :md="13" :sm="12" :xs="24">
            <FormItem label="佣金范围：" class="tab_data">
              <Input enter-button placeholder="￥" element-id="price_min" class="mr10" v-model="formValidate.price_min"
                     clearable/>
              <span class="mr10">一</span>
              <Input enter-button placeholder="￥" element-id="price_max" v-model="formValidate.price_max" clearable/>
            </FormItem>
          </Col>
          <Col span="4">
            <Button type="primary" icon="ios-search" @click="userSearchs">搜索</Button>
            <Button v-auth="['export-userCommission']" class="export" icon="ios-share-outline" @click="exports">导出
            </Button>
          </Col>
        </Row>
      </Form>
      <Table
          ref="table"
          :columns="columns"
          :data="tabList"
          class="mt25"
          :loading="loading"
          no-data-text="暂无数据"
          no-filtered-data-text="暂无筛选结果"
          @on-sort-change="sortChanged"
      >
        <template slot-scope="{ row, index }" slot="action">
          <a @click="Info(row)">详情</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page :total=total :current="formValidate.page" show-elevator show-total :page-size="formValidate.limit"
              @on-change="pageChange"/>
      </div>
    </Card>
    <commission-details ref="commission"></commission-details>
  </div>
</template>
<script>
  import { mapState } from 'vuex'
  import { commissionListApi, userCommissionApi } from '@/api/finance'
  import commissionDetails from './handle/commissionDetails'

  export default {
    name: 'commissionRecord',
    components: { commissionDetails },
    data () {
      return {
        total: 0,
        loading: false,
        tabList: [],
        formValidate: {
          nickname: '',
          price_max: '',
          price_min: '',
          excel: 0,
          page: 1, // 当前页
          limit: 20 // 每页显示条数
        },
        columns: [
          {
            title: '用户信息',
            key: 'nickname',
            minWidth: 150
          },
          {
            title: '总佣金金额',
            key: 'sum_number',
            sortable: 'custom',
            minWidth: 120
          },
          {
            title: '账户余额',
            key: 'now_money',
            minWidth: 100
          },
          {
            title: '账户佣金',
            key: 'brokerage_price',
            sortable: 'custom',
            minWidth: 120
          },
          {
            title: '提现到账佣金',
            key: 'extract_price',
            minWidth: 150
          },
          {
            title: '操作',
            slot: 'action',
            fixed: 'right',
            minWidth: 100
          }
        ]
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
      this.getList()
    },
    methods: {
      // 列表
      getList () {
        this.loading = true
        commissionListApi(this.formValidate).then(async res => {
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
      },
      // 导出
      exports () {
        let formValidate = this.formValidate
        let data = {
          price_max: formValidate.price_max,
          price_min: formValidate.price_min,
          nickname: formValidate.nickname
        }
        userCommissionApi(data).then(res => {
          location.href = res.data[0]
        }).catch(res => {
          this.$Message.error(res.msg)
        })
      },
      // 详情
      Info (row) {
        this.$refs.commission.modals = true
        this.$refs.commission.getDetails(row.uid)
        this.$refs.commission.getList(row.uid)
      },
      // 排序
      sortChanged (e) {
        if (e.key == 'sum_number') {
          delete this.formValidate.brokerage_price
        } else {
          delete this.formValidate.sum_number
        }
        this.formValidate[e.key] = e.order
        this.getList();
      }
    }
  }
</script>

<style scoped lang="stylus">
.lines
  padding-top 6px !important

.tabform .export
  margin-left 10px;

.tab_data >>> .ivu-form-item-content
  display flex !important
</style>
