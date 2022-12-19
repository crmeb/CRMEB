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
            <Col span="7" class="ivu-text-left">
              <FormItem label="会员类型：">
                <Select v-model="formValidate.member_type" clearable @on-change="userSearchs">
                  <Option v-for="item in treeSelect" :value="item.id" :key="item.id">{{ item.label }}</Option>
                </Select>
              </FormItem>
            </Col>
            <Col span="7" class="ivu-text-left ml20">
              <FormItem label="支付方式：">
                <Select v-model="formValidate.pay_type" clearable @on-change="paySearchs">
                  <Option v-for="item in payList" :value="item.val" :key="item.val">{{ item.label }}</Option>
                </Select>
              </FormItem>
            </Col>
            <Col span="7" class="ivu-text-left ml20">
              <FormItem label="购买时间：">
                <DatePicker
                  :editable="false"
                  @on-change="onchangeTime"
                  :value="timeVal"
                  format="yyyy/MM/dd"
                  type="datetimerange"
                  placement="bottom-start"
                  placeholder="请选择时间"
                  style="width: 90%"
                  :options="options"
                ></DatePicker>
              </FormItem>
            </Col>
            <Col span="7" class="ivu-text-left">
              <FormItem label="搜索：">
                <Input
                  search
                  enter-button
                  @on-search="selChange"
                  placeholder="请输入用户名称搜索"
                  element-id="name"
                  v-model="formValidate.name"
                  style="width: 90%; display: inline-table"
                  class="mr"
                />
              </FormItem>
            </Col>
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
        <!--<template slot-scope="{ row, index }" slot="status">-->
        <!--<i-switch-->
        <!--v-model="row.status"-->
        <!--:value="row.status"-->
        <!--:true-value="1"-->
        <!--:false-value="0"-->
        <!--@on-change="onchangeIsShow(row)"-->
        <!--size="large"-->
        <!--&gt;-->
        <!--<span slot="open">激活</span>-->
        <!--<span slot="close">冻结</span>-->
        <!--</i-switch>-->
        <!--</template>-->
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="tablePage.page"
          :page-size="tablePage.limit"
          show-elevator
          show-total
          @on-change="pageChange"
        />
      </div>
    </Card>
  </div>
</template>

<script>
import { userMemberCard, memberRecord } from '@/api/user';
import { mapState } from 'vuex';

export default {
  name: 'card',
  data() {
    return {
      treeSelect: [
        {
          id: 'free',
          label: '试用',
        },
        {
          id: 'card',
          label: '卡密',
        },
        {
          id: 'month',
          label: '月卡',
        },
        {
          id: 'quarter',
          label: '季卡',
        },
        {
          id: 'year',
          label: '年卡',
        },
        {
          id: 'ever',
          label: '永久',
        },
      ],
      payList: [
        {
          val: 'free',
          label: '免费',
        },
        {
          val: 'weixin',
          label: '微信',
        },
        {
          val: 'alipay',
          label: '支付宝',
        },
      ],
      thead: [
        {
          title: '订单号',
          key: 'order_id',
          minWidth: 100,
        },
        {
          title: '用户名',
          minWidth: 50,
          ellipsis: true,
          render: (h, params) => {
            return h('span', params.row.user.nickname);
          },
        },
        {
          title: '手机号码',
          minWidth: 80,
          render: (h, params) => {
            return h('span', params.row.user.phone || '--');
          },
        },
        {
          title: '会员类型',
          key: 'member_type',
          minWidth: 40,
        },
        {
          title: '有效期限（天）',
          minWidth: 50,
          render: (h, params) => {
            return h('span', params.row.vip_day === -1 ? '永久' : params.row.vip_day);
          },
        },
        {
          title: '支付金额（元）',
          key: 'pay_price',
          minWidth: 50,
        },
        {
          title: '支付方式',
          key: 'pay_type',
          minWidth: 30,
        },
        {
          title: '购买时间',
          key: 'pay_time',
          minWidth: 90,
        },
      ],
      tbody: [],
      loading: false,
      total: 0,
      formValidate: {
        name: '',
        member_type: '',
        pay_type: '',
        add_time: '',
      },
      options: {
        shortcuts: [
          {
            text: '今天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()));
              return [start, end];
            },
          },
          {
            text: '昨天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)),
              );
              end.setTime(
                end.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 1)),
              );
              return [start, end];
            },
          },
          {
            text: '最近7天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 6)),
              );
              return [start, end];
            },
          },
          {
            text: '最近30天',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(
                start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - 29)),
              );
              return [start, end];
            },
          },
          {
            text: '本月',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.setTime(new Date(new Date().getFullYear(), new Date().getMonth(), 1)));
              return [start, end];
            },
          },
          {
            text: '本年',
            value() {
              const end = new Date();
              const start = new Date();
              start.setTime(start.setTime(new Date(new Date().getFullYear(), 0, 1)));
              return [start, end];
            },
          },
        ],
      },
      timeVal: [],
      tablePage: {
        page: 1,
        limit: 15,
      },
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {
    this.getMemberRecord();
  },
  methods: {
    // 用户名搜索；
    selChange() {
      this.tablePage.page = 1;
      this.getMemberRecord();
    },
    //用户类型搜索；
    userSearchs() {
      this.tablePage.page = 1;
      this.getMemberRecord();
    },
    //支付方式搜索；
    paySearchs() {
      this.tablePage.page = 1;
      this.getMemberRecord();
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.add_time = this.timeVal[0] ? this.timeVal.join('-') : '';
      this.tablePage.page = 1;
      this.getMemberRecord();
    },
    getMemberRecord() {
      this.loading = true;
      let data = {
        page: this.tablePage.page,
        limit: this.tablePage.limit,
        member_type: this.formValidate.member_type,
        pay_type: this.formValidate.pay_type,
        add_time: this.formValidate.add_time,
        name: this.formValidate.name,
      };
      memberRecord(data)
        .then((res) => {
          this.loading = false;
          const { list, count } = res.data;
          this.tbody = list;
          this.total = count;
        })
        .catch((err) => {
          this.loading = false;
          this.$Message.error(err.msg);
        });
    },
    // 分页
    pageChange(index) {
      this.tablePage.page = index;
      this.getMemberRecord();
    },
  },
};
</script>
