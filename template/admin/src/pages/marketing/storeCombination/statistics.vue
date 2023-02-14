<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span>
          <Button class="return" icon="ios-arrow-back" size="small" type="text" @click="$router.go(-1)">返回</Button>
        </span>
        <Divider class="return" type="vertical" />
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <div>
      <Tabs v-model="currentTab" @on-click="onClickTab">
        <TabPane v-for="(item, index) in tabs" :label="item.label" :name="item.type" :key="index" />
      </Tabs>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="pagination"
        :model="pagination"
        :label-width="labelWidth"
        label-position="right"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col span="6" v-if="type == 1">
            <FormItem label="订单状态：" label-for="status">
              <Select v-model="pagination.status" placeholder="请选择订单状态">
                <Option value="">全部</Option>
                <Option value="0">未支付</Option>
                <Option value="1">待发货</Option>
                <Option value="2">待收货</Option>
                <Option value="3">待评价</Option>
                <Option value="4">交易完成</Option>
              </Select>
            </FormItem>
          </Col>
          <Col span="6">
            <FormItem label="搜索：" label-for="title">
              <Input
                search
                enter-button
                v-model="pagination.real_name"
                placeholder="请输入用户姓名|手机号|UID"
                @on-search="searchList"
              />
            </FormItem>
          </Col>
        </Row>
      </Form>
      <Table
        :columns="type ? thead2 : thead"
        :data="tbody"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="avatar">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.avatar" />
          </div>
        </template>
        <template slot-scope="{ row }" slot="people">
          <span> {{ row.count_people + ' / ' + row.people }}</span>
        </template>
        <template slot-scope="{ row }" slot="status">
          <Tag color="blue" v-show="row.status === 1">进行中</Tag>
          <Tag color="volcano" v-show="row.status === 3">已失败</Tag>
          <Tag color="cyan" v-show="row.status === 2">已成功</Tag>
        </template>
        <template slot-scope="{ row }" slot="action">
          <a @click="Info(row)">查看详情</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="pagination.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="pagination.limit"
        />
      </div>
    </Card>
    <!-- 详情模态框-->
    <Modal
      v-model="modals"
      class="tableBox"
      scrollable
      footer-hide
      closable
      title="查看详情"
      :mask-closable="false"
      width="750"
    >
      <Table
        ref="selection"
        :columns="columns2"
        :data="tabList3"
        :loading="loading2"
        no-data-text="暂无数据"
        highlight-row
        max-height="600"
        size="small"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="nickname">
          <span> {{ row.nickname + ' / ' + row.uid }}</span>
        </template>
        <template slot-scope="{ row }" slot="avatar">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.avatar" />
          </div>
        </template>
        <template slot-scope="{ row }" slot="action">
          <Tag color="volcano" v-show="row.is_refund != 0">已退款</Tag>
          <Tag color="cyan" v-show="row.is_refund === 0">未退款</Tag>
        </template>
      </Table>
    </Modal>
  </div>
</template>

<script>
import cardsData from '@/components/cards/cards';
import {
  getcombinationStatistics,
  getcombinationStatisticsPeople,
  getcombinationStatisticsOrder,
  orderPinkListApi,
} from '@/api/marketing';

export default {
  name: 'index',
  components: { cardsData },
  data() {
    return {
      modals: false,
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      id: 0,
      tbody: [],
      labelWidth: 75,
      total: 0,
      tabs: [
        {
          type: '',
          label: '活动参与人',
        },
        {
          type: '',
          label: '活动订单',
        },
      ],
      currentTab: 0,
      loading: false,
      thead: [
        {
          title: '头像',
          slot: 'avatar',
        },
        {
          title: '发起用户',
          key: 'nickname',
        },
        {
          title: '开团时间',
          key: '_add_time',
        },
        {
          title: '拼团人数',
          slot: 'people',
        },
        {
          title: '结束时间',
          key: '_stop_time',
        },
        {
          title: '拼团状态',
          slot: 'status',
        },
        {
          title: '操作',
          slot: 'action',
        },
      ],
      thead2: [
        {
          title: '订单号',
          key: 'order_id',
        },
        {
          title: '用户',
          key: 'real_name',
        },
        {
          title: '订单状态',
          key: 'status',
        },
        {
          title: '订单支付金额',
          key: 'pay_price',
        },
        {
          title: '订单商品数',
          key: 'total_num',
        },
        {
          title: '下单时间',
          key: 'add_time',
        },
        {
          title: '支付时间',
          key: 'pay_time',
        },
      ],
      cardLists: [
        {
          col: 6,
          count: 0,
          name: '活动参与人数（人）',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '推广人数（人）',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '发起拼团数',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '成团数',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '支付订单额（元）',
          className: 'ios-speedometer-outline',
        },
        {
          col: 6,
          count: 0,
          name: '支付人数（人）',
          className: 'ios-speedometer-outline',
        },
      ],
      pagination: {
        page: 1,
        limit: 15,
        real_name: '',
        status: '',
      },
      type: 0,
      loading2: false,
      tabList3: [],
      columns2: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '用户名称',
          slot: 'nickname',
          minWidth: 100,
        },
        {
          title: '用户头像',
          slot: 'avatar',
        },
        {
          title: '订单编号',
          key: 'order_id',
        },
        {
          title: '金额',
          key: 'price',
        },
        {
          title: '订单状态',
          slot: 'action',
        },
      ],
    };
  },
  created() {
    this.id = this.$route.params.id;
    this.getStatistics(this.id);
    this.getList(this.id);
  },
  methods: {
    // 统计
    getStatistics(id) {
      console.log(id);
      getcombinationStatistics(id).then((res) => {
        let arr = ['people_count', 'spread_count', 'start_count', 'success_count', 'pay_price', 'pay_count'];
        this.cardLists.map((i, index) => {
          i.count = res.data[arr[index]];
        });
      });
    },
    // 列表
    getList(id) {
      this.loading = true;
      if (this.type == 0) {
        getcombinationStatisticsPeople(this.id, this.pagination).then((res) => {
          this.loading = false;
          const { count, list } = res.data;
          this.total = count;
          this.tbody = list;
        });
      } else {
        getcombinationStatisticsOrder(this.id, this.pagination).then((res) => {
          this.loading = false;
          const { count, list } = res.data;
          this.total = count;
          this.tbody = list;
        });
      }
    },
    // 标签切换
    onClickTab() {
      this.type = this.currentTab;
      this.getList(this.id);
    },
    // 搜索
    searchList() {
      this.pagination.page = 1;
      this.getList(this.id);
    },
    // 分页
    pageChange(index) {
      this.pagination.page = index;
      this.getList(this.id);
    },
    // 查看详情
    Info(row) {
      this.modals = true;
      this.rows = row;
      orderPinkListApi(row.id)
        .then(async (res) => {
          let data = res.data;
          this.tabList3 = data.list;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
.cl {
  margin-right: 20px;
}

.code-row-bg {
  display: flex;
  flex-wrap: nowrap;
}

.code-row-bg .ivu-mt {
  width: 100%;
  margin: 0 5px;
}

.ech-box {
  margin-top: 10px;
}

.change-style {
  border: 1px solid #ccc;
  border-radius: 15px;
  padding: 0px 10px;
  cursor: pointer;
}

.table-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.return {
  margin-bottom: 6px;
}

.i-layout-page-header {
  padding-left: 13px;
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
</style>
