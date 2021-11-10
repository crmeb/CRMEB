<template>
  <div>
    <Card :bordered="false" dis-hover>
      <table-form
        ref="orderData"
        :is-all="isAll"
        :auto-disabled="autoDisabled"
        :form-selection="selection"
        @getList="getData"
        @order-data="orderDatas"
      />
      <table-list
        ref="table"
        :where="orderData"
        :is-all="isAll"
        @on-all="onAll"
        @auto-disabled="onAutoDisabled"
        @order-data="onOrderData"
        @on-changeCards="getCards"
        @changeGetTabs="changeGetTabs"
        @order-select="orderSelect"
        @updata="updata"
      />
    </Card>
  </div>
</template>

<script>
import cardsData from "../../../components/cards/cards";
import tableForm from "./components/tableFrom";
import tableList from "./components/tableList";
export default {
  name: "orderlistDetails",
  components: {
    tableForm,
    tableList,
    cardsData,
  },
  data() {
    return {
      currentTab: "",
      cardLists: [],
      selection: [],
      orderData: {
        status: "",
        data: "",
        real_name: "",
        field_key: "all",
        pay_type: "",
      },
      // display: 'none',
      autoDisabled: true,
      isAll: -1,
    };
  },
  methods: {
    updata() {
      this.$refs.orderData.integralGetOrdes();
    },
    changeGetTabs() {
      this.$parent.getTabs();
    },
    // tab xuanxiang dezhi
    getChangeTabs(tab) {
      this.$refs.table.getList();
    },
    // 列表数据
    getData(res) {
      if (this.$refs.table) {
        this.$refs.table.checkBox = false;
        this.$refs.table.getList(res);
      }
    },
    // 模块数据
    getCards(list) {
      this.cardLists = list;
    },
    handleResize() {
      this.$refs.ellipsis.forEach((item) => item.init());
    },
    orderSelect(selection) {
      this.selection = selection;
    },
    onOrderData(e) {
      this.orderData = e;
    },
    orderDatas(e) {
      this.orderData = e;
    },
    onAutoDisabled(e) {
      this.autoDisabled = e ? true : false;
    },
    onAll(e) {
      this.isAll = e;
    },
  },
  mounted() {},
};
</script>

<style scoped lang="stylus">
.card_cent >>> .ivu-card-body {
  width: 100%;
  height: 100%;
}

.card_box {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 25px;
  box-sizing: border-box;
  border-radius: 4px;

  .card_box_img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 20px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  .card_box_txt {
    .sp1 {
      display: block;
      color: #252631;
      font-size: 24px;
    }

    .sp2 {
      display: block;
      color: #98A9BC;
      font-size: 12px;
    }
  }
}
</style>
