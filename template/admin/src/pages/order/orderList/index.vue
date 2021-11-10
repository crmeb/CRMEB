<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">订单管理</span>
        <div>
          <Tabs v-model="currentTab" @on-click="onClickTab" v-if="tablists">
            <TabPane :label="'全部订单（' + tablists.all + '）'" name=" " />
            <TabPane :label="'普通订单（' + tablists.general + '）'" name="1" />
            <TabPane :label="'拼团订单（' + tablists.pink + '）'" name="2" />
            <TabPane :label="'秒杀订单（' + tablists.seckill + '）'" name="3" />
            <TabPane :label="'砍价订单（' + tablists.bargain + '）'" name="4" />
            <TabPane :label="'预售订单（' + tablists.advance + '）'" name="5" />
          </Tabs>
        </div>
      </div>
    </div>
    <productlist-details
      v-if="currentTab === 'article' || 'project' || 'app'"
      ref="productlist"
    ></productlist-details>
    <Spin size="large" fix v-if="spinShow"></Spin>
  </div>
</template>

<script>
import productlistDetails from "./orderlistDetails";
import { mapMutations } from "vuex";
export default {
  name: "list",
  components: {
    productlistDetails,
  },
  data() {
    return {
      spinShow: false,
      currentTab: "",
      data: [],
      tablists: null,
    };
  },
  created() {
    this.getOrderType("");
    this.getOrderStatus("");
    this.getOrderTime("");
    this.getOrderNum("");
    this.getfieldKey("");
    this.onChangeTabs("");
    this.getisDelIdListl("");
    this.getIsDel(1);
  },
  beforeDestroy() {
    this.getOrderType("");
    this.getOrderStatus("");
    this.getOrderTime("");
    this.getOrderNum("");
    this.getfieldKey("");
    this.onChangeTabs("");
    this.getisDelIdListl("");
    this.getIsDel(1);
  },
  mounted() {
    this.getTabs();
  },
  methods: {
    ...mapMutations("order", [
      "onChangeTabs",
      "getOrderStatus",
      "getOrderTime",
      "getOrderNum",
      "getfieldKey",
      "onChangeTabs",
      "getOrderType",
      "getisDelIdListl",
      "getIsDel",
      // 'onChangeChart'
    ]),
    // 订单类型  @on-changeTabs="getChangeTabs"
    getTabs() {
      this.spinShow = true;
      this.$store
        .dispatch("order/getOrderTabs", {
          data: "",
        })
        .then((res) => {
          this.tablists = res.data;
          // this.onChangeChart(this.tablists)
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$Message.error(res.msg);
        });
      // getOrdes({}).then(async res => {
      //     this.tablists = res.data;
      //     this.onChangeChart(this.tablists)
      //     this.spinShow = false;
      // }).catch(res => {
      //     this.spinShow = false;
      //     this.$Message.error(res.msg);
      // })
    },
    onClickTab() {
      this.onChangeTabs(Number(this.currentTab));
      this.$store.dispatch("order/getOrderTabs", {
        data: "",
        type: Number(this.currentTab),
      });
      this.$refs.productlist.getChangeTabs();
      this.$store.dispatch("order/getOrderTabs", { type: this.currentTab });
    },
  },
};
</script>
<style scoped lang="stylus">
.product_tabs >>> .ivu-tabs-bar {
  margin-bottom: 0px !important;
}

.product_tabs >>> .ivu-page-header-content {
  margin-bottom: 0px !important;
}

.product_tabs >>> .ivu-page-header-breadcrumb {
  margin-bottom: 0px !important;
}
</style>
