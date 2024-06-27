<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/marketing/store_seckill/index'"
    ></pages-header>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0" class="ivu-mt-16"></cards-data>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="pagination"
        :model="pagination"
        label-width="80px"
        label-position="right"
        @submit.native.prevent
        inline
        ><el-form-item v-if="type == 1" label="订单状态：" label-for="status">
          <el-select
            v-model="pagination.status"
            clearable
            placeholder="请选择订单状态"
            @change="changeStatus"
            class="form_content_width"
          >
            <el-option value="1" label="待发货"></el-option>
            <el-option value="2" label="待收货"></el-option>
            <el-option value="3" label="待评价"></el-option>
            <el-option value="4" label="交易完成"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="订单搜索：" label-for="title">
          <el-input
            clearable
            v-model="pagination.real_name"
            placeholder="请输入用户姓名|手机号|UID"
            class="form_content_width"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="changeStatus">查询</el-button>
        </el-form-item>
      </el-form>
      <el-tabs v-model="type" @tab-click="onClickTab">
        <el-tab-pane v-for="(item, index) in tabs" :label="item.label" :name="item.type" :key="index" />
      </el-tabs>
      <el-table
        :data="tbody"
        ref="table"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column
          :label="item.title"
          :min-width="item.minWidth || 100"
          v-for="(item, index) in type == 1 ? thead2 : thead"
          :key="index"
        >
          <template slot-scope="scope">
            <template v-if="item.key">
              <span>{{ scope.row[item.key] }}</span>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="pagination.page"
          :limit.sync="pagination.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import cardsData from '@/components/cards/cards';
import { getseckillStatistics, getseckillStatisticsPeople, getseckillStatisticsOrder } from '@/api/marketing';

export default {
  name: 'index',
  components: { cardsData },
  data() {
    return {
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
          type: '0',
          label: '活动参与人',
        },
        {
          type: '1',
          label: '活动订单',
        },
      ],
      type: 0,
      loading: false,
      thead: [
        {
          title: '用户姓名',
          key: 'real_name',
        },
        {
          title: '购买件数',
          key: 'goods_num',
        },
        {
          title: '支付订单数',
          key: 'order_num',
        },
        {
          title: '支付金额',
          key: 'total_price',
        },
        {
          title: '最近参与时间',
          key: 'add_time',
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
          name: '下单人数（人）',
          className: 'iconxiadanrenshu',
        },
        {
          col: 6,
          count: 0,
          name: '支付订单额（元）',
          className: 'iconzhifudingdan',
        },
        {
          col: 6,
          count: 0,
          name: '支付人数（人）',
          className: 'iconzhifurenshu',
        },
        {
          col: 6,
          count: 0,
          name: '剩余库存/总库存',
          className: 'iconshengyukucun',
        },
      ],
      pagination: {
        page: 1,
        limit: 15,
        real_name: '',
        status: '',
      },
      type: 0,
    };
  },
  created() {
    this.id = this.$route.params.id;
    this.getStatistics(this.id);
    this.getList();
  },
  methods: {
    changeStatus() {
      this.pagination.page = 1;
      this.getList();
    },
    // 统计
    getStatistics(id) {
      getseckillStatistics(id).then((res) => {
        let arr = ['order_count', 'all_price', 'pay_count', 'pay_rate'];
        this.cardLists.map((i, index) => {
          i.count = res.data[arr[index]];
        });
      });
    },
    // 列表
    getList() {
      this.loading = true;
      if (this.type == 0) {
        getseckillStatisticsPeople(this.id, this.pagination).then((res) => {
          this.loading = false;
          const { count, list } = res.data;
          this.total = count;
          this.tbody = list;
        });
      } else {
        getseckillStatisticsOrder(this.id, this.pagination).then((res) => {
          this.loading = false;
          const { count, list } = res.data;
          this.total = count;
          this.tbody = list;
        });
      }
    },
    // 标签切换
    onClickTab(e) {
      this.type = e.index;
      this.getList();
    },
    // 搜索
    searchList() {
      this.pagination.page = 1;
      this.getList();
    },
  },
};
</script>

<style lang="scss" scoped>
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
::v-deep .ivu-tabs-nav-scroll {
  background-color: #fff;
  padding-top: 5px;
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
</style>
