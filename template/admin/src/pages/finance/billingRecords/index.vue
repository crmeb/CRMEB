<template>
  <div>
    <Card :bordered="false" dis-hover class="ive-mt tablebox">
      <div class="ive-mt tabbox">
        <Tabs @on-click="onClickTab" class="mb20">
          <TabPane label="日账单" name="day" />
          <TabPane label="周账单" name="week" />
          <TabPane label="月账单" name="month" />
        </Tabs>
        <Form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
        >
          <FormItem label="创建时间：">
            <DatePicker
              :editable="false"
              :clearable="false"
              @on-change="onchangeTime"
              :value="timeVal"
              format="yyyy/MM/dd"
              type="daterange"
              placement="bottom-start"
              placeholder="请选择时间"
              style="width: 200px"
              :options="options"
              class="mr20"
            ></DatePicker>
          </FormItem>
        </Form>
      </div>
      <div class="table">
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
          <template slot-scope="{ row }" slot="income_price">
            <span style="color: #f5222d">￥{{ row.income_price }}</span>
          </template>
          <template slot-scope="{ row }" slot="exp_price">
            <span style="color: #00c050">￥{{ row.exp_price }}</span>
          </template>
          <template slot-scope="{ row }" slot="entry_price">
            <span>￥{{ row.entry_price }}</span>
          </template>
          <template slot-scope="{ row }" slot="action">
            <a @click="Info(row)">账单详情</a>
            <Divider type="vertical" />
            <a @click="download(row)">下载</a>
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
      </div>
    </Card>
    <Modal
      v-model="modals"
      scrollable
      footer-hide
      closable
      title="账单详情"
      :mask-closable="false"
      @on-cancel="cancel"
      width="1000"
    >
      <commission-details v-if="modals" ref="commission" :ids="ids" :time="formValidate.time"></commission-details>
    </Modal>
  </div>
</template>

<script>
import exportExcel from '@/utils/newToExcel.js';
import commissionDetails from '../components/commissionDetails';
import { getRecord } from '@/api/statistic.js';
import { getFlowList } from '@/api/finance';

export default {
  name: 'bill',
  components: {
    commissionDetails,
  },
  data() {
    return {
      modals: false,
      options: this.$timeOptions,
      ids: '',
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      total: 0,
      loading: false,
      tab: 'day',
      staff: [],
      columns: [
        {
          title: 'ID',
          key: 'id',
          width: 60,
        },
        {
          title: '标题',
          key: 'title',
          minWidth: 80,
        },
        {
          title: '日期',
          key: 'add_time',
          minWidth: 80,
        },
        {
          title: '收入金额',
          slot: 'income_price',
          minWidth: 80,
        },
        {
          title: '支出金额',
          slot: 'exp_price',
          minWidth: 80,
        },
        {
          title: '入账金额',
          slot: 'entry_price',
          minWidth: 80,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
          align: 'center',
        },
      ],
      orderList: [
        {
          id: '1',
          order_id: '200',
          pay_price: '200',
          status: 1,
          phone: '13000000000',
          address: '100',
        },
      ],
      formValidate: {
        store_id: '',
        time: '',
        page: 1,
        limit: 15,
      },
      timeVal: [],
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
          { text: '全部', val: '' },
          { text: '昨天', val: 'yesterday' },
          { text: '今天', val: 'today' },
          { text: '本周', val: 'week' },
          { text: '本月', val: 'month' },
          { text: '本季度', val: 'quarter' },
          { text: '本年', val: 'year' },
        ],
      },
    };
  },
  computed: {
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  mounted() {
    this.onClickTab(this.tab);
    this.staffApi();
  },
  methods: {
    staffApi() {
      staffListInfo().then((res) => {
        this.staff = res.data;
      });
    },
    onClickTab(e) {
      this.tab = e;
      this.getList();
    },
    search() {
      this.getList();
    },
    getList() {
      this.loading = true;
      let data = {
        type: this.tab,
        time: this.formValidate.time,
        page: this.formValidate.page,
        limit: this.formValidate.limit,
        store_id: this.formValidate.store_id,
      };
      getRecord(data).then((res) => {
        this.orderList = res.data.list;
        this.loading = false;
        this.total = res.data.count;
      });
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.page = 1;
      this.formValidate.time = tab;
      this.timeVal = [];
      this.getList();
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.time = this.timeVal[0] ? this.timeVal.join('-') : '';
      this.formValidate.page = 1;
      this.getList();
    },
    //分页
    pageChange(status) {
      this.formValidate.page = status;
      this.getList();
    },
    // 账单详情
    Info(row) {
      this.ids = row.ids || '';
      this.modals = true;
    },
    cancel() {
      this.modals = false;
    },
    //下载
    async download(row) {
      let [th, fileKey, data, fileName] = [[], [], [], ''];
      let excelData = {
        ids: row.ids,
        page: 1,
        export: 1,
        time: this.formValidate.time,
      };
      let lebData = await this.getExcelData(excelData);
      if (!fileName) fileName = lebData.fileName;
      if (!fileKey.length) {
        fileKey = lebData.fileKey;
      }
      if (!th.length) th = lebData.header;
      data = data.concat(lebData.list);
      exportExcel(th, fileKey, fileName, data);
      return;
    },
    getExcelData(excelData) {
      return new Promise((resolve, reject) => {
        getFlowList(excelData).then((res) => {
          return resolve(res.data);
        });
      });
    },
  },
};
</script>

<style scoped lang="less">
/deep/.ivu-page-header,
/deep/.ivu-tabs-bar {
  border-bottom: 1px solid #ffffff;
}
/deep/.ivu-card-body {
  padding: 0;
}
/deep/.ivu-tabs-nav {
  height: 45px;
}
.tabbox {
  padding: 16px 20px 0px;
}
.box {
  padding: 20px;
  padding-bottom: 1px;
}
.tablebox {
  margin-top: 15px;
  padding-bottom: 10px;
}
.btnbox {
  padding: 20px 0px 0px 30px;
  .btns {
    width: 99px;
    height: 32px;
    background: #1890ff;
    border-radius: 4px;
    text-align: center;
    line-height: 32px;
    color: #ffffff;
    cursor: pointer;
  }
}
.table {
  padding: 0px 30px 15px 30px;
}
</style>
