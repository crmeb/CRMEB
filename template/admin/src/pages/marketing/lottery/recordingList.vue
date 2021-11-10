<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="tableFrom"
        :model="tableFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col span="24">
            <FormItem label="时间选择：">
              <RadioGroup
                v-model="tableFrom.data"
                type="button"
                @on-change="selectChange(tableFrom.data)"
                class="mr"
              >
                <Radio
                  :label="item.val"
                  v-for="(item, i) in fromList.fromTxt"
                  :key="i"
                  >{{ item.text }}</Radio
                >
              </RadioGroup>
              <DatePicker
                :editable="false"
                @on-change="onchangeTime"
                :value="timeVal"
                format="yyyy/MM/dd"
                type="daterange"
                placement="bottom-end"
                placeholder="自定义时间"
                style="width: 200px"
              ></DatePicker>
            </FormItem>
          </Col>
          <Col span="24">
            <FormItem label="奖品类型：">
              <RadioGroup
                v-model="tableFrom.type"
                type="button"
                @on-change="selectType()"
                class="mr"
              >
                <Radio
                  :label="item.val"
                  v-for="(item, i) in typeList"
                  :key="i"
                  >{{ item.text }}</Radio
                >
              </RadioGroup>
            </FormItem>
          </Col>
          <!-- <Col v-bind="grid">
            <FormItem label="领取状态：" clearable>
              <Select
                v-model="tableFrom.is_receive"
                placeholder="请选择"
                clearable
                @on-change="userSearchs"
              >
                <Option value="0">待发货</Option>
                <Option value="1">已发货</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="处理状态：" clearable>
              <Select
                v-model="tableFrom.is_deliver"
                placeholder="请选择"
                clearable
                @on-change="userSearchs"
              >
                <Option value="0">未处理</Option>
                <Option value="1">已处理</Option>
              </Select>
            </FormItem>
          </Col> -->
          <Col v-bind="grid">
            <FormItem label="搜索用户：" label-for="store_name">
              <Input
                search
                enter-button
                placeholder="请输入用户信息"
                v-model="tableFrom.keyword"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="tableList"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="is_fail">
          <Icon
            type="md-checkmark"
            v-if="row.is_fail === 1"
            color="#0092DC"
            size="14"
          />
          <Icon type="md-close" v-else color="#ed5565" size="14" />
        </template>
        <template slot-scope="{ row, index }" slot="user">
          <span>{{ row.user.nickname }} </span>
        </template>
        <template slot-scope="{ row, index }" slot="mark">
          <span>{{ row.deliver_info.mark }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="receive_info">
          <div v-if="row.receive_info.name">
            <div>姓名：{{ row.receive_info.name }}</div>
            <div>电话：{{ row.receive_info.phone }}</div>
            <div>地址：{{ row.receive_info.address }}</div>
            <div v-if="row.receive_info.mark">
              备注：{{ row.receive_info.mark }}
            </div>
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="prize">
          <div class="prize">
            <img :src="row.prize.image" alt="" />
            <span>{{ row.prize.name }}</span>
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a
            @click="deliver(row, 1)"
            v-if="row.type == 6 && row.is_deliver === 0"
            >发货</a
          >
          <a
            v-else-if="row.type == 6 && row.is_deliver === 1"
            @click="isDeliver(row)"
            >配送信息</a
          >
          <Divider type="vertical" v-if="row.type == 6" />
          <a @click="deliver(row, 2)">备注</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="tableFrom.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="tableFrom.limit"
        />
      </div>
    </Card>
    <!-- 发货-->
    <Modal
      v-model="shipModel"
      width="40%"
      scrollable
      closable
      footer-hide
      :title="!modelTitle ? (modelType === 1 ? '发货' : '备注') : modelTitle"
      :mask-closable="false"
      :z-index="1"
    >
      <Form
        v-model="shipModel"
        :ref="modelType === 1 ? 'shipForm' : 'markForm'"
        :model="modelType === 1 ? shipForm : markForm"
        :rules="modelType === 1 ? ruleShip : ruleMark"
        :label-width="80"
      >
        <FormItem v-if="modelType === 1" label="快递公司" prop="deliver_name">
          <Select v-model="shipForm.deliver_name">
            <Option
              v-for="item in locationList"
              :value="item.value"
              :key="item.id"
              >{{ item.value }}</Option
            >
          </Select>
        </FormItem>
        <FormItem v-if="modelType === 1" label="快递单号" prop="deliver_number">
          <Input
            v-model="shipForm.deliver_number"
            placeholder="请输入快递单号"
          ></Input>
          <div class="trips" v-if="shipForm.deliver_name == '顺丰速运'">
            <p>顺丰请输入单号 :收件人或寄件人手机号后四位</p>
            <p>例如：SF000000000000:3941</p>
          </div>
        </FormItem>
        <FormItem v-if="modelType === 2" label="备注">
          <Input v-model="markForm.mark" placeholder="请输入备注"></Input>
        </FormItem>
        <FormItem>
          <Button
            type="primary"
            @click="ok(modelType === 1 ? 'shipForm' : 'markForm')"
            >提交</Button
          >
          <Button @click="cancel('formValidate')" style="margin-left: 8px"
            >关闭</Button
          >
        </FormItem></Form
      >
    </Modal>
  </div>
</template>

<script>
import { mapState } from "vuex";
import { lotteryRecordList, lotteryRecordDeliver } from "@/api/lottery";
import { formatDate } from "@/utils/validate";
import { getExpressData } from "@/api/order";
import { ruleShip, ruleMark } from "./formRule/ruleShip";
export default {
  name: "lotteryRecordList",
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, "yyyy-MM-dd hh:mm");
      }
    },
  },
  data() {
    return {
      shipModel: false,
      loading: false,
      locationList: [],
      shipForm: {
        id: "",
        deliver_name: "",
        deliver_number: null,
      },
      markForm: {
        id: "",
        mark: "",
      },
      ruleShip: ruleShip,
      ruleMark: ruleMark,
      fromList: {
        title: "选择时间",
        fromTxt: [
          { text: "全部", val: "" },
          { text: "今天", val: "today" },
          { text: "昨天", val: "yesterday" },
          { text: "最近7天", val: "lately7" },
          { text: "最近30天", val: "lately30" },
          { text: "本月", val: "month" },
          { text: "本年", val: "year" },
        ],
      },
      typeList: [
        { text: "全部", val: "" },
        { text: "未中奖", val: "1" },
        { text: "积分", val: "2" },
        { text: "余额", val: "3" },
        { text: "红包", val: "4" },
        { text: "优惠券", val: "5" },
        { text: "商品", val: "6" },
      ],
      columns1: [
        {
          title: "ID",
          key: "id",
          width: 80,
        },
        {
          title: "用户信息",
          slot: "user",
          minWidth: 90,
        },
        {
          title: "奖品信息",
          slot: "prize",
          minWidth: 130,
        },
        {
          title: "抽奖时间",
          key: "add_time",
          minWidth: 100,
        },
        {
          title: "收货信息",
          slot: "receive_info",
          minWidth: 100,
        },
        {
          title: "备注",
          slot: "mark",
          minWidth: 100,
        },
        {
          title: "操作",
          slot: "action",
          fixed: "right",
          minWidth: 130,
        },
      ],
      tableList: [],
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tableFrom: {
        keyword: "",
        date: [],
        page: 1,
        limit: 15,
      },
      total: 0,
      timeVal: [],
      modelType: 1,
      lottery_id: "",
      modelTitle: "",
    };
  },
  computed: {
    ...mapState("admin/layout", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? "top" : "left";
    },
  },
  created() {
    this.tableFrom.lottery_id = this.$route.query.id;
    this.lottery_id = this.$route.query.id;
    this.getList();
    this.getExpressData();
  },
  methods: {
    deliver(row, type) {
      this.markForm.id = row.id;
      this.shipForm.id = row.id;
      this.shipForm.deliver_name = "";
      this.shipForm.deliver_number = "";
      this.markForm.mark = row.deliver_info.mark;
      this.modelType = type;
      this.shipModel = true;
    },
    isDeliver(row) {
      this.markForm.id = row.id;
      this.shipForm.id = row.id;
      this.modelType = 1;
      this.modelTitle = "配送信息";
      this.shipModel = true;
      this.shipForm.deliver_name = row.deliver_info.deliver_name;
      this.shipForm.deliver_number = row.deliver_info.deliver_number;
    },
    ok(name) {
      this.$refs[name].validate((valid) => {
        lotteryRecordDeliver(
          this.modelType == 1 ? this.shipForm : this.markForm
        )
          .then((res) => {
            this.$Message.success("操作成功");
            this.shipModel = false;
            this.getList();
            this.shipForm = {
              id: "",
              deliver_name: "",
              deliver_number: null,
            };
            this.modelTitle = "";
            this.markForm = {
              id: "",
              mark: "",
            };
          })
          .catch((err) => {
            this.$Message.error(err.msg);
          });
      });
    },
    cancel() {
      this.modelType = 1;
      this.modelTitle = "";
      this.shipModel = false;
    },
    // 物流公司列表
    getExpressData() {
      getExpressData()
        .then(async (res) => {
          this.locationList = res.data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.data = this.timeVal[0] ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList();
    },
    // 选择时间
    selectChange(tab) {
      this.tableFrom.page = 1;
      this.tableFrom.date = tab;
      this.timeVal = [];
      this.getList();
    },
    selectType(type) {
      this.tableFrom.page = 1;
      this.timeVal = [];
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      lotteryRecordList(this.tableFrom, this.lottery_id)
        .then(async (res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.tableFrom.page = index;
      this.getList();
    },
    // 表格搜索
    userSearchs() {
      this.tableFrom.page = 1;
      this.getList();
    },
  },
};
</script>

<style scoped lang="stylus">
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

.prize {
  display: flex;
  align-items: center;
}

.prize img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 5px;
}

.trips {
  color: #ccc;
}
</style>
