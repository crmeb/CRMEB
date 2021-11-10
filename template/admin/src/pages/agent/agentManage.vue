<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col span="24">
            <FormItem label="时间选择：">
              <RadioGroup
                v-model="formValidate.data"
                type="button"
                @on-change="selectChange(formValidate.data)"
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
                placeholder="请选择时间"
                style="width: 200px"
              ></DatePicker>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="搜索：" label-for="status">
              <Input
                search
                enter-button
                placeholder="请输入请输入姓名、电话、UID"
                v-model="formValidate.nickname"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <Button
              v-auth="['export-userAgent']"
              class="export"
              icon="ios-share-outline"
              @click="exports"
              >导出</Button
            >
          </Col>
        </Row>
      </Form>
    </Card>
    <cards-data
      :cardLists="cardLists"
      v-if="cardLists.length >= 0"
    ></cards-data>
    <Card :bordered="false" dis-hover>
      <Table
        ref="selection"
        :columns="columns"
        :data="tableList"
        class="ivu-mt"
        :loading="loading"
        no-data-text="暂无数据"
        highlight-row
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="nickname">
          <div class="name">
            <div class="item">昵称:{{ row.nickname }}</div>
            <div class="item">姓名:{{ row.real_name }}</div>
            <div class="item">电话:{{ row.phone }}</div>
          </div>
        </template>
        <template slot-scope="{ row }" slot="agentLevel">
          <div>{{ row.agentLevel ? row.agentLevel.name : "--" }}</div>
        </template>
        <template slot-scope="{ row, index }" slot="right">
          <a @click="promoters(row, 'man')">推广人</a>
          <Divider type="vertical" />
          <template>
            <Dropdown @on-click="changeMenu(row, $event, index)">
              <a href="javascript:void(0)">
                更多
                <Icon type="ios-arrow-down"></Icon>
              </a>
              <DropdownMenu slot="list">
                <DropdownItem name="1">推广订单</DropdownItem>
                <DropdownItem name="2">推广方式</DropdownItem>
                <!--                                <DropdownItem name="3">修改上级推广人</DropdownItem>-->
                <!--                                <DropdownItem name="4">清除上级推广人</DropdownItem>-->
                <!--                                <DropdownItem name="5">取消推广资格</DropdownItem>-->
                <DropdownItem name="5">赠送分销等级</DropdownItem>
              </DropdownMenu>
            </Dropdown>
          </template>
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
    <!-- 推广人列表-->
    <promoters-list ref="promotersLists"></promoters-list>
    <!-- 推广方式-->
    <Modal
      v-model="modals"
      scrollable
      footer-hide
      closable
      title="推广二维码"
      :mask-closable="false"
      width="600"
    >
      <div class="acea-row row-around">
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" v-if="code_src"><img v-lazy="code_src" /></div>
          <span class="QRpic_sp1 mt10" @click="getWeChat"
            >公众号推广二维码</span
          >
        </div>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" v-if="code_xcx"><img v-lazy="code_xcx" /></div>
          <span class="QRpic_sp2 mt10" @click="getXcx">小程序推广二维码</span>
        </div>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" v-if="code_h5"><img v-lazy="code_h5" /></div>
          <span class="QRpic_sp2 mt10" @click="getH5">H5推广二维码</span>
        </div>
      </div>
      <Spin size="large" fix v-if="spinShow"></Spin>
    </Modal>
    <!--修改推广人-->
    <Modal
      v-model="promoterShow"
      scrollable
      title="修改推广人"
      class="order_box"
      :closable="false"
    >
      <Form
        ref="formInline"
        :model="formInline"
        :label-width="100"
        @submit.native.prevent
      >
        <FormItem label="用户头像：" prop="image">
          <div class="picBox" @click="customer">
            <div class="pictrue" v-if="formInline.image">
              <img v-lazy="formInline.image" />
            </div>
            <div class="upLoad acea-row row-center-wrapper" v-else>
              <Icon type="ios-camera-outline" size="26" />
            </div>
          </div>
        </FormItem>
      </Form>
      <div slot="footer">
        <Button type="primary" @click="putSend('formInline')">提交</Button>
        <Button @click="cancel('formInline')">取消</Button>
      </div>
    </Modal>
    <Modal
      v-model="customerShow"
      scrollable
      title="请选择商城用户"
      :closable="false"
      width="50%"
    >
      <customerInfo
        v-if="customerShow"
        @imageObject="imageObject"
      ></customerInfo>
    </Modal>
  </div>
</template>

<script>
import cardsData from "@/components/cards/cards";
import searchFrom from "@/components/publicSearchFrom";
import { mapState } from "vuex";
import {
  agentListApi,
  statisticsApi,
  lookCodeApi,
  lookxcxCodeApi,
  lookh5CodeApi,
  userAgentApi,
  agentSpreadApi,
} from "@/api/agent";
import promotersList from "./handle/promotersList";
import customerInfo from "@/components/customerInfo";
import { membershipDataAddApi } from "@/api/membershipLevel";
export default {
  name: "agentManage",
  components: { cardsData, searchFrom, promotersList, customerInfo },
  data() {
    return {
      customerShow: false,
      promoterShow: false,
      modals: false,
      spinShow: false,
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      fromList: {
        title: "选择时间",
        custom: true,
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
      formValidate: {
        nickname: "",
        data: "",
        page: 1,
        limit: 15,
      },
      date: "all",
      total: 0,
      cardLists: [],
      loading: false,
      tableList: [],
      timeVal: [],
      columns: [
        // {
        //     type: 'selection',
        //     width: 60,
        //     align: 'center'
        // },
        {
          title: "ID",
          key: "uid",
          width: 80,
        },
        {
          title: "头像",
          key: "headimgurl",
          minWidth: 60,
          render: (h, params) => {
            return h("viewer", [
              h(
                "div",
                {
                  style: {
                    width: "36px",
                    height: "36px",
                    borderRadius: "4px",
                    cursor: "pointer",
                  },
                },
                [
                  h("img", {
                    attrs: {
                      src: params.row.headimgurl
                        ? params.row.headimgurl
                        : require("../../assets/images/moren.jpg"),
                    },
                    style: {
                      width: "100%",
                      height: "100%",
                    },
                  }),
                ]
              ),
            ]);
          },
        },
        {
          title: "用户信息",
          slot: "nickname",
          minWidth: 120,
        },
        {
          title: "推广用户数量",
          key: "spread_count",
          minWidth: 125,
        },
        {
          title: "分销等级",
          slot: "agentLevel",
          minWidth: 120,
        },
        {
          title: "订单数量",
          key: "order_count",
          minWidth: 90,
        },
        {
          title: "订单金额",
          key: "order_price",
          minWidth: 120,
        },
        {
          title: "佣金金额",
          key: "brokerage_money",
          minWidth: 120,
        },
        {
          title: "已提现金额",
          key: "extract_count_price",
          minWidth: 120,
        },
        {
          title: "提现次数",
          key: "extract_count_num",
          minWidth: 100,
        },
        {
          title: "未提现金额",
          key: "new_money",
          minWidth: 105,
        },
        {
          title: "上级推广人",
          key: "spread_name",
          minWidth: 105,
        },
        {
          title: "操作",
          slot: "right",
          fixed: "right",
          minWidth: 130,
        },
      ],
      code_src: "",
      code_xcx: "",
      code_h5: "",
      formInline: {
        uid: 0,
        spread_uid: 0,
        image: "",
      },
    };
  },
  computed: {
    ...mapState("media", ["isMobile"]),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? "top" : "right";
    },
  },
  created() {
    this.getList();
    this.getStatistics();
  },
  methods: {
    // 提交
    putSend(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          if (!this.formInline.spread_uid) {
            return this.$Message.error("请上传用户");
          }
          agentSpreadApi(this.formInline)
            .then((res) => {
              this.promoterShow = false;
              this.$Message.success(res.msg);
              this.getList();
              this.$refs[name].resetFields();
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        }
      });
    },
    // 导出
    exports() {
      let formValidate = this.formValidate;
      let data = {
        data: formValidate.data,
        nickname: formValidate.nickname,
      };
      userAgentApi(data)
        .then((res) => {
          location.href = res.data[0];
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 操作
    changeMenu(row, name, index) {
      switch (name) {
        case "1":
          this.promoters(row, "order");
          break;
        case "2":
          this.spreadQR(row);
          break;
        case "3":
          this.editS(row);
          break;
        case "4":
          this.del(row, "解除【 " + row.nickname + " 】的上级推广人", index);
        case "5":
          this.$modalForm(
            membershipDataAddApi({ uid: row.uid }, "/agent/get_level_form")
          ).then(() => this.getList());
          break;
        default:
          this.del_open(row, "解除【 " + row.nickname + " 】的推广资格", index);
          break;
      }
    },
    editS(row) {
      this.promoterShow = true;
      this.formInline.uid = row.uid;
    },
    customer() {
      this.customerShow = true;
    },
    imageObject(e) {
      this.customerShow = false;
      this.formInline.spread_uid = e.uid;
      this.formInline.image = e.image;
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `agent/stair/delete_spread/${row.uid}`,
        method: "PUT",
        ids: "",
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 删除
    del_open(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `agent/stair/delete_system_spread/${row.uid}`,
        method: "PUT",
        ids: "",
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    edit(row) {
      this.promoterShow = true;
      this.formInline.uid = row.uid;
    },
    cancel(name) {
      this.promoterShow = false;
      this.$refs[name].resetFields();
    },
    // 推广人列表 订单
    promoters(row, tit) {
      this.$refs.promotersLists.modals = true;
      this.$refs.promotersLists.getList(row, tit);
    },
    // 统计
    getStatistics() {
      let data = {
        nickname: this.formValidate.nickname,
        data: this.formValidate.data,
      };
      statisticsApi(data)
        .then(async (res) => {
          let data = res.data;
          this.cardLists = data.res;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal.join("-");
      this.formValidate.page = 1;
      if (!e[0]) {
        this.formValidate.data = "";
      }
      this.getList();
      this.getStatistics();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.page = 1;
      this.formValidate.data = tab;
      this.timeVal = [];
      this.getList();
      this.getStatistics();
    },
    // 列表
    getList() {
      this.loading = true;
      agentListApi(this.formValidate)
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
      this.formValidate.page = index;
      this.getList();
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
      this.getStatistics();
    },
    // 二维码
    spreadQR(row) {
      this.modals = true;
      this.rows = row;
      // this.getWeChat(row);
      // this.getXcx(row);
    },
    // 公众号推广二维码
    getWeChat() {
      this.spinShow = true;
      let data = {
        uid: this.rows.uid,
        action: "wechant_code",
      };
      lookCodeApi(data)
        .then(async (res) => {
          let data = res.data;
          this.code_src = data.code_src;
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$Message.error(res.msg);
        });
    },
    // 小程序推广二维码
    getXcx() {
      this.spinShow = true;
      let data = {
        uid: this.rows.uid,
      };
      lookxcxCodeApi(data)
        .then(async (res) => {
          let data = res.data;
          this.code_xcx = data.code_src;
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$Message.error(res.msg);
        });
    },
    getH5() {
      this.spinShow = true;
      let data = {
        uid: this.rows.uid,
      };
      lookh5CodeApi(data)
        .then(async (res) => {
          let data = res.data;
          this.code_h5 = data.code_src;
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>
<style scoped lang="stylus">
.picBox {
  display: inline-block;
  cursor: pointer;

  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }

  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  .iconfont {
    color: #898989;
  }
}

.QRpic {
  width: 180px;
  height: 180px;

  img {
    width: 100%;
    height: 100%;
  }
}

.QRpic_sp1 {
  font-size: 13px;
  color: #19be6b;
  cursor: pointer;
}

.QRpic_sp2 {
  font-size: 13px;
  color: #2d8cf0;
  cursor: pointer;
}

img {
  height: 36px;
  display: block;
}

.ivu-mt .name .item {
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

/* .ivu-mt >>> .ivu-table-header */
/* border-top:1px dashed #ddd!important */
</style>
