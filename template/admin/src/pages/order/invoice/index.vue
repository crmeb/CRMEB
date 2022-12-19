<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="table_box">
        <Form
          ref="orderData"
          :model="orderData"
          :label-width="80"
          label-position="right"
          class="tabform"
          @submit.native.prevent
        >
          <Row :gutter="24" type="flex" justify="end">
            <!--                        <Col span="24" class="ivu-text-left">-->
            <!--                            <FormItem label="订单状态：">-->
            <!--                                <RadioGroup v-model="orderData.status" type="button"  @on-change="selectChange()">-->
            <!--                                    <Radio label="">全部 {{  '(' +tablists.statusAll?tablists.statusAll:0 + ')' }}</Radio>-->
            <!--                                    <Radio label="0">未支付 {{  '(' +tablists.unpaid?tablists.unpaid:0+ ')' }}</Radio>-->
            <!--                                    <Radio label="1">未发货 {{  '(' +tablists.unshipped?tablists.unshipped:0+ ')' }}</Radio>-->
            <!--                                    <Radio label="2">待收货 {{  '(' +tablists.untake?tablists.untake:0+ ')' }}</Radio>-->
            <!--                                    <Radio label="3">待评价 {{  '(' +tablists.unevaluate?tablists.unevaluate:0+ ')' }}</Radio>-->
            <!--                                    <Radio label="4">交易完成 {{  '(' +tablists.complete?tablists.complete:0+ ')' }}</Radio>-->
            <!--                                    <Radio label="5">待核销 {{  '(' +tablists.write_off?tablists.write_off:0+ ')' }}</Radio>-->
            <!--                                    <Radio label="-1">退款中 {{  '(' +tablists.refunding?tablists.refunding:0+ ')' }}</Radio>-->
            <!--                                    <Radio label="-2">已退款 {{  '(' +tablists.refund?tablists.refund:0+ ')' }}</Radio>-->
            <!--                                    <Radio label="-4">已删除 {{  '(' +tablists.del?tablists.del:0+ ')' }}</Radio>-->
            <!--                                </RadioGroup>-->
            <!--                            </FormItem>-->
            <!--                        </Col>-->
            <Col span="24" class="ivu-text-left">
              <FormItem label="创建时间：">
                <DatePicker
                  :editable="false"
                  @on-change="onchangeTime"
                  :value="timeVal"
                  format="yyyy/MM/dd"
                  type="datetimerange"
                  placement="bottom-start"
                  placeholder="请选择时间"
                  style="width: 300px"
                  class="mr20"
                  :options="options"
                ></DatePicker>
              </FormItem>
            </Col>
            <Col span="24">
              <Col v-bind="grid" class="mr">
                <FormItem label="搜索：" prop="real_name" label-for="real_name">
                  <Input
                    v-model="orderData.real_name"
                    search
                    enter-button
                    placeholder="请输入"
                    element-id="name"
                    @on-search="orderSearch()"
                  >
                    <Select v-model="orderData.field_key" slot="prepend" style="width: 80px">
                      <Option value="all">全部</Option>
                      <Option value="order_id">订单号</Option>
                      <Option value="uid">UID</Option>
                      <Option value="real_name">用户姓名</Option>
                      <Option value="user_phone">用户电话</Option>
                    </Select>
                  </Input>
                </FormItem>
              </Col>
            </Col>
          </Row>
        </Form>
      </div>
      <Tabs v-model="currentTab" @on-click="onClickTab" v-if="tablists" class="mb20">
        <TabPane :label="'全部发票（' + tablists.all + '）'" name=" " />
        <TabPane :label="'待开发票（' + tablists.noOpened + '）'" name="1" />
        <TabPane :label="'已开发票（' + tablists.opened + '）'" name="2" />
        <TabPane :label="'退款发票（' + tablists.refund + '）'" name="3" />
      </Tabs>
      <Table
        :columns="columns"
        :data="orderList"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="pay_price">
          <div>¥ {{ row.pay_price }}</div>
        </template>
        <template slot-scope="{ row, index }" slot="type">
          <div v-if="row.type === 1">电子普通发票</div>
          <div v-else>纸质专用发票</div>
        </template>
        <template slot-scope="{ row, index }" slot="is_invoice">
          <div v-if="row.is_invoice === 1">已开票</div>
          <div v-else>未开票</div>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          <div v-if="row.status === 0">未发货</div>
          <div v-else-if="row.status === 1">待收货</div>
          <div v-else-if="row.status === 2">待评价</div>
          <div v-else-if="row.status === 3">已完成</div>
        </template>
        <template slot-scope="{ row, index }" slot="header_type">
          <div v-if="row.header_type === 1">个人</div>
          <div v-else>企业</div>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="orderInfo(row.id)">订单信息</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="orderData.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="orderData.limit"
        />
      </div>
    </Card>
    <Modal
      v-model="invoiceShow"
      scrollable
      title="发票详情"
      class="order_box"
      width="700"
      @on-cancel="cancel"
      footer-hide
    >
      <Form ref="formInline" :model="formInline" :label-width="100" @submit.native.prevent>
        <div v-if="invoiceDetails.header_type === 1 && invoiceDetails.type === 1">
          <div class="list">
            <div class="title">发票信息</div>
            <Row class="row">
              <Col span="12"
                >发票抬头: <span class="info">{{ invoiceDetails.name }}</span></Col
              >
              <Col span="12">发票类型: <span class="info">电子普通发票</span></Col>
            </Row>
            <Row class="row">
              <Col span="12">发票抬头类型: 个人</Col>
              <Col span="12">订单金额: {{ invoiceDetails.pay_price }}</Col>
            </Row>
          </div>
          <div class="list">
            <div class="title row">联系信息</div>
            <Row class="row">
              <Col span="12">真实姓名: {{ invoiceDetails.real_name }}</Col>
              <Col span="12">联系电话: {{ invoiceDetails.user_phone }}</Col>
            </Row>
            <Row class="row">
              <Col span="12">联系邮箱: {{ invoiceDetails.email }}</Col>
            </Row>
          </div>
        </div>
        <div v-if="invoiceDetails.header_type === 2 && invoiceDetails.type === 1">
          <div class="list">
            <div class="title">发票信息</div>
            <Row class="row">
              <Col span="12"
                >发票抬头: <span class="info">{{ invoiceDetails.name }}</span></Col
              >
              <Col span="12"
                >企业税号: <span class="info">{{ invoiceDetails.duty_number }}</span></Col
              >
            </Row>
            <Row class="row">
              <Col span="12">发票类型: 电子普通发票</Col>
              <Col span="12">发票抬头类型: 企业</Col>
            </Row>
          </div>
          <div class="list">
            <div class="title row">联系信息</div>
            <Row class="row">
              <Col span="12">真实姓名: {{ invoiceDetails.real_name }}</Col>
              <Col span="12">联系电话: {{ invoiceDetails.user_phone }}</Col>
            </Row>
            <Row class="row">
              <Col span="12">联系邮箱: {{ invoiceDetails.email }}</Col>
            </Row>
          </div>
        </div>
        <div v-if="invoiceDetails.header_type === 2 && invoiceDetails.type === 2">
          <div class="list">
            <div class="title">发票信息</div>
            <Row class="row">
              <Col span="12"
                >发票抬头: <span class="info">{{ invoiceDetails.name }}</span></Col
              >
              <Col span="12"
                >企业税号: <span class="info">{{ invoiceDetails.duty_number }}</span></Col
              >
            </Row>
            <Row class="row">
              <Col span="12">发票类型: 纸质专用发票</Col>
              <Col span="12">发票抬头类型: 企业</Col>
            </Row>
            <Row class="row">
              <Col span="12"
                >开户银行: <span class="info">{{ invoiceDetails.bank }}</span></Col
              >
              <Col span="12"
                >银行账号: <span class="info">{{ invoiceDetails.card_number }}</span></Col
              >
            </Row>
            <Row class="row">
              <Col span="12">企业地址: {{ invoiceDetails.address }}</Col>
              <Col span="12">企业电话: {{ invoiceDetails.drawer_phone }}</Col>
            </Row>
          </div>
          <div class="list">
            <div class="title row">联系信息</div>
            <Row class="row">
              <Col span="12">真实姓名: {{ invoiceDetails.real_name }}</Col>
              <Col span="12">联系电话: {{ invoiceDetails.user_phone }}</Col>
            </Row>
            <Row class="row">
              <Col span="12">联系邮箱: {{ invoiceDetails.email }}</Col>
            </Row>
          </div>
        </div>
        <FormItem label="开票状态：" style="margin-top: 14px">
          <RadioGroup v-model="formInline.is_invoice" @on-change="kaiInvoice(formInline.is_invoice)">
            <Radio :label="1">已开票</Radio>
            <Radio :label="0">未开票</Radio>
          </RadioGroup>
        </FormItem>
        <FormItem label="发票编号：" v-if="formInline.is_invoice === 1">
          <Input v-model="formInline.invoice_number" placeholder="请输入发票编号"></Input>
        </FormItem>
        <FormItem label="发票备注：" v-if="formInline.is_invoice === 1">
          <Input
            v-model="formInline.remark"
            value="备注"
            type="textarea"
            :autosize="{ minRows: 2, maxRows: 5 }"
            placeholder="请输入发票备注"
          ></Input>
        </FormItem>
        <Button type="primary" @click="handleSubmit()" style="width: 100%; margin: 0 10px">确定</Button>
      </Form>
    </Modal>
    <Modal v-model="orderShow" scrollable title="订单详情" footer-hide class="order_box" width="700">
      <orderDetall :orderId="orderId" @detall="detall" v-if="orderShow"></orderDetall>
    </Modal>
  </div>
</template>
<script>
import orderDetall from './orderDetall';
import { orderInvoiceChart, orderInvoiceList, orderInvoiceSet } from '@/api/order';
import { mapState } from 'vuex';
export default {
  name: 'invoice',
  components: {
    orderDetall,
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
  data() {
    return {
      orderShow: false,
      invoiceShow: false,
      invoiceDetails: {},
      formInline: {
        is_invoice: 0,
        invoice_number: '',
        remark: '',
      },
      loading: false,
      currentTab: '',
      tablists: null,
      timeVal: [],
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
      grid: {
        xl: 8,
        lg: 8,
        md: 8,
        sm: 24,
        xs: 24,
      },
      columns: [
        {
          title: '订单号',
          key: 'order_id',
          minWidth: 170,
        },
        // {
        //     title: '订单类型',
        //     key: 'pink_name',
        //     minWidth: 150
        // },
        {
          title: '订单金额',
          slot: 'pay_price',
          minWidth: 100,
        },
        {
          title: '发票类型',
          slot: 'type',
          minWidth: 120,
          filters: [
            {
              label: '电子普通发票',
              value: 1,
            },
            {
              label: '纸质专用发票',
              value: 2,
            },
          ],
          filterMultiple: false,
          filterMethod(value, row) {
            if (value === 1) {
              return row.type === 1;
            } else if (value === 2) {
              return row.type === 2;
            }
          },
        },
        {
          title: '发票抬头类型',
          slot: 'header_type',
          minWidth: 110,
          filters: [
            {
              label: '个人',
              value: 1,
            },
            {
              label: '企业',
              value: 2,
            },
          ],
          filterMultiple: false,
          filterMethod(value, row) {
            if (value === 1) {
              return row.header_type === 1;
            } else if (value === 2) {
              return row.header_type === 2;
            }
          },
        },
        // {
        //     title: '支付状态',
        //     key: 'pay_type_name',
        //     minWidth: 90
        // },
        {
          title: '下单时间',
          key: 'add_time',
          minWidth: 150,
          sortable: true,
        },
        {
          title: '开票状态',
          slot: 'is_invoice',
          minWidth: 80,
        },
        {
          title: '订单状态',
          slot: 'status',
          minWidth: 80,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 150,
          align: 'center',
        },
      ],
      orderList: [],
      total: 0, // 总条数
      orderData: {
        page: 1, // 当前页
        limit: 10, // 每页显示条数
        status: '',
        data: '',
        real_name: '',
        field_key: '',
        type: '',
      },
      orderId: 0,
    };
  },
  created() {
    this.getTabs();
    this.getList();
  },
  mounted() {},
  methods: {
    detall(e) {
      this.orderShow = e;
    },
    orderInfo(id) {
      this.orderId = id;
      this.orderShow = true;
    },
    empty() {
      this.formInline = {
        is_invoice: 1,
        invoice_number: '',
        remark: '',
      };
    },
    cancel() {
      this.invoiceShow = false;
      this.empty();
    },
    kaiInvoice(invoice) {
      if (invoice !== 1) {
        this.formInline.invoice_number = '';
        this.formInline.remark = '';
      }
    },
    handleSubmit() {
      if (this.formInline.is_invoice === 1) {
        if (this.formInline.invoice_number.trim() === '') return this.$Message.error('请填写发票编号');
      }
      orderInvoiceSet(this.invoiceDetails.invoice_id, this.formInline)
        .then((res) => {
          this.$Message.success(res.msg);
          this.invoiceShow = false;
          this.getList();
          this.empty();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    edit(row) {
      this.invoiceShow = true;
      this.invoiceDetails = row;
      this.formInline.invoice_number = row.invoice_number;
      this.formInline.remark = row.invoice_reamrk;
      this.formInline.is_invoice = row.is_invoice;
    },
    // 订单列表
    getList() {
      this.loading = true;
      orderInvoiceList(this.orderData)
        .then(async (res) => {
          this.loading = false;
          let data = res.data;
          this.orderList = data.list;
          this.total = data.count;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.orderData.page = index;
      this.getList();
    },
    getTabs() {
      orderInvoiceChart()
        .then((res) => {
          this.tablists = res.data;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    // 精确搜索()
    orderSearch() {
      this.orderData.page = 1;
      this.getList();
    },
    // 具体日期搜索()；
    onchangeTime(e) {
      this.orderData.page = 1;
      this.timeVal = e;
      this.orderData.data = this.timeVal[0] ? this.timeVal.join('-') : '';
      this.getList();
    },
    //订单状态搜索()
    selectChange() {
      this.orderData.page = 1;
      this.getList();
    },
    //订单搜索()
    onClickTab() {
      this.orderData.page = 1;
      this.orderData.type = this.currentTab;
      this.getList();
    },
  },
};
</script>
<style scoped lang="stylus">
.order_box .list {
  font-size: 12px;
  color: #17233D;
  border-bottom: 1px solid #E7EAEC;
  margin: 0 10px;
  padding-bottom: 22px;
}

.ivu-form-item {
  margin-left: 10px;
  margin-right: 10px;
}

/deep/.ivu-form-item-label {
  text-align: left;
  width: 83px !important;
}

/deep/.ivu-form-item-content {
  margin-left: 83px !important;
}

.order_box .list .title {
  color: #000000;
  font-weight: bold;
}

.order_box .list .row {
  margin-top: 13px;
}

.order_box .list .info {
  color: #515A6E;
}

.tab_data >>> .ivu-form-item-content {
  margin-left: 0 !important;
}

.table_box >>> .ivu-divider-horizontal {
  margin-top: 0px !important;
}

.table_box >>> .ivu-form-item {
  margin-bottom: 15px !important;
}

.tabform {
  margin-bottom: 10px;
}

.Refresh {
  font-size: 12px;
  color: #1890FF;
  cursor: pointer;
}
</style>
