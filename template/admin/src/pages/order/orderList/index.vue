<template>
  <div>
    <Card :bordered="false" dis-hover class="mt10">
      <Tabs class="mb20" v-model="currentTab" @on-click="onClickTab" v-if="tablists">
        <TabPane v-for="(item, index) in tabs" :label="item.label" :name="item.type" :key="index" />
      </Tabs>
      <productlist-details
        v-if="currentTab === 'article' || 'project' || 'app'"
        ref="productlist"
      ></productlist-details>
      <Spin size="large" fix v-if="spinShow"></Spin>
    </Card>
  </div>
</template>

<script>
import productlistDetails from './orderlistDetails';
import { mapMutations } from 'vuex';
export default {
  name: 'list',
  components: {
    productlistDetails,
  },
  data() {
    return {
      tabs: [
        {
          type: '',
          label: (h) => {
            return h('div', [
              h('span', '全部订单'),
              h('Badge', {
                props: {
                  count: Number(this.tablists.all),
                  'overflow-count': 999999,
                },
              }),
            ]);
          },
        },
        {
          type: '1',
          label: (h) => {
            return h('div', [
              h('span', '普通订单'),
              h('Badge', {
                props: {
                  count: Number(this.tablists.general),
                  'overflow-count': 999999,
                },
              }),
            ]);
          },
        },
        {
          type: '2',
          label: (h) => {
            return h('div', [
              h('span', '拼团订单'),
              h('Badge', {
                props: {
                  count: Number(this.tablists.pink),
                  'overflow-count': 999999,
                },
              }),
            ]);
          },
        },
        {
          type: '3',
          label: (h) => {
            return h('div', [
              h('span', '秒杀订单'),
              h('Badge', {
                props: {
                  count: Number(this.tablists.seckill),
                  'overflow-count': 999999,
                },
              }),
            ]);
          },
        },
        {
          type: '4',
          label: (h) => {
            return h('div', [
              h('span', '砍价订单'),
              h('Badge', {
                props: {
                  count: Number(this.tablists.bargain),
                  'overflow-count': 999999,
                },
              }),
            ]);
          },
        },
        {
          type: '5',
          label: (h) => {
            return h('div', [
              h('span', '预售订单'),
              h('Badge', {
                props: {
                  count: Number(this.tablists.advance),
                  'overflow-count': 999999,
                },
              }),
            ]);
          },
        },
      ],
      spinShow: false,
      currentTab: '',
      data: [],
      tablists: null,
    };
  },
  created() {
    this.getOrderType('');
    this.getOrderStatus('');
    this.getOrderTime('');
    this.getOrderNum('');
    this.getfieldKey('');
    this.onChangeTabs('');
    this.getisDelIdListl('');
    this.getIsDel(1);
  },
  beforeDestroy() {
    this.getOrderType('');
    this.getOrderStatus('');
    this.getOrderTime('');
    this.getOrderNum('');
    this.getfieldKey('');
    this.onChangeTabs('');
    this.getisDelIdListl('');
    this.getIsDel(1);
  },
  mounted() {
    this.getTabs();
  },
  methods: {
    ...mapMutations('order', [
      'onChangeTabs',
      'getOrderStatus',
      'getOrderTime',
      'getOrderNum',
      'getfieldKey',
      'getOrderType',
      'getisDelIdListl',
      'getIsDel',
      // 'onChangeChart'
    ]),
    // 订单类型  @on-changeTabs="getChangeTabs"
    getTabs() {
      this.spinShow = true;
      this.$store
        .dispatch('order/getOrderTabs', {
          data: '',
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
    },
    onClickTab() {
      this.onChangeTabs(Number(this.currentTab));
      // this.$store.dispatch("order/getOrderTabs", {
      //   data: "",
      //   type: Number(this.currentTab),
      // });
      // this.$refs.productlist.getChangeTabs();
      this.$store.dispatch('order/getOrderTabs', { type: this.currentTab });
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

.i-layout-page-header /deep/ .ivu-badge-count-alone {
  top: -7px;
}

.i-layout-page-header /deep/ .ivu-badge-count {
  line-height: 14px;
  height: 15px;
}
</style>
