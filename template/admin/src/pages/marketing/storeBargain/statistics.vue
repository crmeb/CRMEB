<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/marketing/store_bargain/index'"
    ></pages-header>
    <cards-data :cardLists="cardLists" v-if="cardLists.length" class="ivu-mt-16"></cards-data>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="pagination"
        :model="pagination"
        :label-width="labelWidth"
        label-position="right"
        @submit.native.prevent
        inline
      >
        <el-form-item v-if="type == 1" label="订单状态：" label-for="status">
          <el-select
            v-model="pagination.status"
            placeholder="请选择订单状态"
            clearable
            @change="searchList"
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
            v-model="pagination.real_name"
            :placeholder="type == 1 ? '请输入用户姓名|订单号|UID' : '请输入用户UID'"
            class="form_content_width"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="searchList">查询</el-button>
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
          :min-width="item.minWidth"
          v-for="(item, index) in type == 1 ? thead2 : thead"
          :key="index"
        >
          <template slot-scope="scope">
            <template v-if="item.key">
              <div>
                <span>{{ scope.row[item.key] }}</span>
              </div>
            </template>
            <template v-else-if="item.slot === 'avatar'">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="scope.row.avatar" />
              </div>
            </template>
            <template v-else-if="item.slot === 'status'">
              <el-tag size="medium" type="info" v-show="scope.row.status === 1">进行中</el-tag>
              <el-tag size="medium" type="danger" v-show="scope.row.status === 2">已失败</el-tag>
              <el-tag size="medium" v-show="scope.row.status === 3">已成功</el-tag>
            </template>
            <template v-else-if="item.slot === 'action'">
              <a v-db-click @click="Info(scope.row)">查看详情</a>
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
    <!-- 详情模态框-->
    <el-dialog :visible.sync="modals" class="tableBox" title="查看详情" :close-on-click-modal="false" width="720px">
      <el-table
        ref="selection"
        :data="tabList3"
        v-loading="loading2"
        empty-text="暂无数据"
        highlight-current-row
        max-height="600"
        size="small"
      >
        <el-table-column label="用户ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户头像" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="砍价金额" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="砍价时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script>
import cardsData from '@/components/cards/cards';
import {
  getbargainStatistics,
  getbargainStatisticsPeople,
  getbargainStatisticsOrder,
  bargainUserInfoApi,
} from '@/api/marketing';

export default {
  name: 'index',
  components: { cardsData },
  data() {
    return {
      modals: false,
      tabList3: [],
      loading2: false,
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      id: 0,
      tbody: [],
      labelWidth: '80px',
      total: 0,
      tabs: [
        {
          type: '0',
          label: '发起砍价',
        },
        {
          type: '1',
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
          title: '发起时间',
          key: 'add_time',
        },
        {
          title: '帮砍人数',
          key: 'already_num',
        },
        {
          title: '结束时间',
          key: 'datatime',
        },
        {
          title: '砍价状态',
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
          col: 4,
          count: 0,
          name: '活动参与人数（人）',
          className: 'iconcanyurenshu',
        },
        {
          col: 4,
          count: 0,
          name: '砍价人数（人）',
          className: 'iconxiadanrenshu',
        },
        {
          col: 4,
          count: 0,
          name: '发起砍价数',
          className: 'icontuiguangrenshu',
        },
        {
          col: 4,
          count: 0,
          name: '砍价成功数',
          className: 'iconkanjiachenggong',
        },
        {
          col: 4,
          count: 0,
          name: '支付订单额（元）',
          className: 'iconzhifudingdan',
        },
        {
          col: 4,
          count: 0,
          name: '支付人数（人）',
          className: 'iconzhifurenshu',
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
    this.getList(this.id);
  },
  methods: {
    // 统计
    getStatistics(id) {
      getbargainStatistics(id).then((res) => {
        let arr = [
          'people_count',
          'spread_count',
          'start_count',
          'success_count',
          'pay_price',
          'pay_count',
          'pay_rate',
        ];
        this.cardLists.map((i, index) => {
          i.count = res.data[arr[index]];
        });
      });
    },
    // 列表
    getList(id) {
      this.loading = true;
      if (this.type == 0) {
        getbargainStatisticsPeople(this.id, this.pagination).then((res) => {
          this.loading = false;
          const { count, list } = res.data;
          this.total = count;
          this.tbody = list;
        });
      } else {
        getbargainStatisticsOrder(this.id, this.pagination).then((res) => {
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
      this.getList(this.id);
    },
    // 搜索
    searchList() {
      this.pagination.page = 1;
      this.getList(this.id);
    },
    // 查看详情
    Info(row) {
      this.modals = true;
      this.rows = row;
      bargainUserInfoApi(row.id)
        .then(async (res) => {
          let data = res.data;
          this.tabList3 = data.list;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
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
::v-deep .ivu-tabs-nav-scroll {
  background-color: #fff;
  padding-top: 5px;
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
