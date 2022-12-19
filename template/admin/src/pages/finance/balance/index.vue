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
        <Row :gutter="24" type="flex">
          <Col span="24">
            <FormItem label="订单时间：">
              <dateRadio @selectDate="onSelectDate"></dateRadio>
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
          </Col>
          <Col span="24">
            <FormItem label="交易类型：">
              <Select
                type="button"
                v-model="formValidate.trading_type"
                class="mr15"
                @on-change="selChange"
                style="width: 30%"
              >
                <Option
                  :label="item"
                  :value="Object.keys(withdrawal)[index]"
                  v-for="(item, index) in Object.values(withdrawal)"
                  :key="index"
                  >{{ item }}</Option
                >
              </Select>
            </FormItem>
          </Col>
        </Row>
      </Form>
    </Card>
    <Card :bordered="false" dis-hover>
      <Table
        ref="table"
        :columns="columns"
        :data="tabList"
        :loading="loading"
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="extract_price">
          <div>{{ row.extract_price }}</div>
        </template>
        <template slot-scope="{ row }" slot="pay_type">
          <div v-for="item in payment" :key="item.value">
            <span v-if="row.pay_type == item.value"> {{ item.title }} </span>
          </div>
        </template>
        <template slot-scope="{ row }" slot="number">
          <div v-if="row.pm" class="z-price">+ {{ row.number }}</div>
          <div v-else class="f-price">- {{ row.number }}</div>
        </template>
        <template slot-scope="{ row }" slot="add_time">
          <span> {{ row.add_time | formatDate }}</span>
        </template>
        <template slot-scope="{ row }" slot="set">
          <Button size="small" type="primary" class="item" @click="setMark(row)">备注</Button>
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
    <!-- 拒绝通过-->
    <Modal v-model="modals" scrollable closable title="备注" :mask-closable="false">
      <Input v-model="mark_msg.mark" type="textarea" :rows="4" placeholder="请输入备注" />
      <div slot="footer">
        <Button type="primary" size="large" long :loading="modal_loading" @click="oks">确定</Button>
      </div>
    </Modal>
  </div>
</template>
<script>
import { mapState } from 'vuex';
import { getBalanceList, setBalanceMark } from '@/api/finance';
import { formatDate } from '@/utils/validate';
import dateRadio from '@/components/dateRadio';
export default {
  name: 'balance',
  components: { dateRadio },
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  data() {
    return {
      images: ['1.jpg', '2.jpg'],
      modal_loading: false,
      options: this.$timeOptions,

      mark_msg: {
        mark: '',
      },
      modals: false,
      total: 0,
      loading: false,
      columns: [
        {
          title: 'ID',
          key: 'id',
          width: 50,
        },
        {
          title: '关联订单',
          key: 'relation',
          minWidth: 120,
        },
        {
          title: '交易时间',
          key: 'add_time',
          minWidth: 90,
        },
        {
          title: '交易金额',
          slot: 'number',
          minWidth: 100,
        },
        {
          title: '用户',
          key: 'nickname',
          minWidth: 80,
        },
        {
          title: '交易类型',
          key: 'type_name',
          minWidth: 100,
        },
        {
          title: '备注',
          key: 'mark',
          minWidth: 100,
        },
        {
          title: '操作',
          slot: 'set',
          fixed: 'right',
          width: 100,
        },
      ],
      tabList: [],
      withdrawal: [],
      selectIndexTime: '',
      payment: [
        {
          title: '全部',
          value: '',
        },
        {
          title: '微信',
          value: 'weixin',
        },
        {
          title: '支付宝',
          value: 'alipay',
        },
        {
          title: '银行卡',
          value: 'bank',
        },
        {
          title: '线下支付',
          value: 'offline',
        },
      ],
      formValidate: {
        trading_type: '',
        time: '',
        keywords: '',
        page: 1,
        limit: 20,
      },
      timeVal: [],
      FromData: null,
      extractId: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  mounted() {
    this.getList();
  },
  methods: {
    // 确定
    oks() {
      this.modal_loading = true;
      this.mark_msg.mark = this.mark_msg.mark.trim();
      setBalanceMark(this.extractId, this.mark_msg)
        .then(async (res) => {
          this.$Message.success(res.msg);
          this.modal_loading = false;
          this.modals = false;
          this.getList();
        })
        .catch((res) => {
          this.modal_loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 备注
    setMark(row) {
      this.modals = true;
      this.extractId = row.id;
      this.mark_msg.mark = row.mark;
    },
    onSelectDate(e) {
      this.formValidate.time = e;
      this.formValidate.page = 1;
      this.getList();
    },
    dateToMs(date) {
      let result = new Date(date).getTime();
      return result;
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.time = this.timeVal.join('-');
      this.formValidate.page = 1;
      this.getList();
    },
    // 选择
    selChange(e) {
      this.formValidate.page = 1;
      this.formValidate.trading_type = e;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      getBalanceList(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
          this.withdrawal = data.status;
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
    // 编辑提交成功
    submitFail() {
      this.getList();
    },
  },
};
</script>
<style scoped lang="stylus">
.ivu-mt .type .item {
  margin: 3px 0;
}

.tabform {
  margin-bottom: 10px;
}

.Refresh {
  font-size: 12px;
  color: #1890FF;
  cursor: pointer;
}

.ivu-form-item {
  margin-bottom: 10px;
}

.status >>> .item~.item {
  margin-left: 6px;
}

.status >>> .statusVal {
  margin-bottom: 7px;
}

/* .ivu-mt >>> .ivu-table-header */
/* border-top:1px dashed #ddd!important */
.type {
  padding: 3px 0;
  box-sizing: border-box;
}

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

.z-price {
  color: red;
}

.f-price {
  color: green;
}
</style>
