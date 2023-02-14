<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        class="tabform"
        @submit.native.prevent
      >
        <Row :gutter="24" type="flex" justify="end">
          <Col span="24" class="ivu-text-left">
            <FormItem label="时间选择：">
              <RadioGroup
                v-model="formValidate.data"
                type="button"
                @on-change="selectChange(formValidate.data)"
                class="mr"
              >
                <Radio :label="item.val" v-for="(item, i) in fromList.fromTxt" :key="i">{{ item.text }}</Radio>
              </RadioGroup>
              <DatePicker
                :editable="false"
                @on-change="onchangeTime"
                :value="timeVal"
                format="yyyy/MM/dd"
                type="daterange"
                placement="bottom-end"
                placeholder="请选择时间"
                style="width: 200px"
              ></DatePicker>
            </FormItem>
          </Col>
          <Col span="24" class="ivu-text-left">
            <FormItem label="支付类型：">
              <RadioGroup v-model="formValidate.paid" type="button" @on-change="selChange">
                <Radio label="">全部</Radio>
                <Radio label="1">已支付</Radio>
                <Radio label="0">未支付</Radio>
              </RadioGroup>
            </FormItem>
          </Col>
          <Col span="24" class="ivu-text-left">
            <FormItem label="搜索：">
              <Input
                search
                enter-button
                @on-search="selChange"
                placeholder="请输入用户昵称、订单号"
                element-id="name"
                v-model="formValidate.nickname"
                style="width: 30%; display: inline-table"
                class="mr"
              />
              <Button v-auth="['export-userRecharge']" class="mr" icon="ios-share-outline" @click="exports"
                >导出</Button
              >
              <!--                            <span class="Refresh">刷新</span><Icon type="ios-refresh" />-->
            </FormItem>
          </Col>
        </Row>
      </Form>
    </Card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <Card :bordered="false" dis-hover>
      <div>充值记录列表</div>
      <Table
        ref="table"
        :columns="columns"
        :data="tabList"
        class="ivu-mt"
        :loading="loading"
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="right">
          <a
            href="javascript:void(0);"
            v-if="row.refund_price <= 0 && row.paid && row.recharge_type != 'system'"
            @click="refund(row)"
            >退款</a
          >
          <!--                    <Divider type="vertical"  v-if="row.paid"/>-->
          <a href="javascript:void(0);" v-if="row.paid === 0" @click="del(row, '此条充值记录', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="formValidate.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="formValidate.limit"
        />
      </div>
    </Card>
    <!-- 退款表单-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
  </div>
</template>
<script>
import cardsData from '@/components/cards/cards';
import searchFrom from '@/components/publicSearchFrom';
import { mapState } from 'vuex';
import { rechargelistApi, userRechargeApi, refundEditApi, exportUserRechargeApi } from '@/api/finance';
import editFrom from '@/components/from/from';
export default {
  name: 'recharge',
  components: { cardsData, searchFrom, editFrom },
  data() {
    return {
      FromData: null,
      formValidate: {
        data: '',
        paid: '',
        nickname: '',
        excel: 0,
        page: 1,
        limit: 20,
      },
      formValidate2: {
        data: '',
        paid: '',
        nickname: '',
      },
      total: 0,
      cardLists: [],
      loading: false,
      columns: [
        {
          title: 'ID',
          key: 'id',
          sortable: true,
          width: 80,
        },
        {
          title: '头像',
          key: 'avatar',
          minWidth: 80,
          render: (h, params) => {
            return h('viewer', [
              h(
                'div',
                {
                  style: {
                    width: '36px',
                    height: '36px',
                    borderRadius: '4px',
                    cursor: 'pointer',
                  },
                },
                [
                  h('img', {
                    attrs: {
                      src: params.row.avatar ? params.row.avatar : require('../../../../assets/images/moren.jpg'),
                    },
                    style: {
                      width: '100%',
                      height: '100%',
                    },
                  }),
                ],
              ),
            ]);
          },
        },
        {
          title: '用户昵称',
          key: 'nickname',
          minWidth: 150,
        },
        {
          title: '订单号',
          key: 'order_id',
          minWidth: 150,
        },
        {
          title: '支付金额',
          key: 'price',
          minWidth: 110,
        },
        {
          title: '是否支付',
          key: 'paid_type',
          minWidth: 110,
        },
        {
          title: '充值类型',
          key: '_recharge_type',
          minWidth: 100,
        },
        {
          title: '支付时间',
          key: '_pay_time',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'right',
          fixed: 'right',
          minWidth: 100,
        },
      ],
      tabList: [],
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
          { text: '本年', val: 'year' },
        ],
      },
      timeVal: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    this.getList();
    this.getUserRecharge();
  },
  methods: {
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `finance/recharge/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tabList.splice(delfromData.num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 退款
    refund(row) {
      refundEditApi(row.id)
        .then(async (res) => {
          if (res.data.status === false) {
            return this.$authLapse(res.data);
          }
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 编辑提交成功
    submitFail() {
      this.getList();
      this.getUserRecharge();
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal.join('-');
      this.formValidate.page = 1;
      this.getList();
      this.getUserRecharge();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.formValidate.page = 1;
      this.getList();
      this.getUserRecharge();
    },
    // 选择
    selChange(x) {
      this.formValidate.page = 1;
      this.getList();
      this.getUserRecharge();
    },
    // 列表
    getList() {
      this.loading = true;
      rechargelistApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    // 小方块
    getUserRecharge() {
      userRechargeApi({
        data: this.formValidate.data,
        paid: this.formValidate.paid,
        nickname: this.formValidate.nickname,
      })
        .then(async (res) => {
          let data = res.data;
          this.cardLists = data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 导出
    exports() {
      let formValidate = this.formValidate;
      let data = {
        data: formValidate.data,
        paid: formValidate.paid,
        nickname: formValidate.nickname,
      };
      exportUserRechargeApi(data)
        .then((res) => {
          location.href = res.data[0];
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
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
</style>
