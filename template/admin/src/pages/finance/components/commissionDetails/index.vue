<template>
  <div>
    <el-form
      ref="formValidate"
      :label-width="labelWidth"
      :label-position="labelPosition"
      class="tabform"
      @submit.native.prevent
      inline
    >
      <el-form-item label="订单搜索：" label-for="status1">
        <el-input
          v-model="formValidate.keywords"
          placeholder="请输入交易单号/交易人"
          class="form_content_width"
        ></el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" v-db-click @click="searchs">搜索</el-button>
      </el-form-item>
      <el-form-item>
        <el-button v-db-click @click="reset">重置</el-button>
      </el-form-item>
    </el-form>
    <el-table
      :data="tabList"
      ref="table"
      v-loading="loading"
      no-userFrom-text="暂无数据"
      no-filtered-userFrom-text="暂无筛选结果"
      class="table"
    >
      <el-table-column label="交易单号" width="180">
        <template slot-scope="scope">
          <span>{{ scope.row.flow_id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="关联订单" min-width="130">
        <template slot-scope="scope">
          <span>{{ scope.row.order_id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="交易时间" min-width="130">
        <template slot-scope="scope">
          <span>{{ scope.row.add_time }}</span>
        </template>
      </el-table-column>
      <el-table-column label="交易金额" min-width="130">
        <template slot-scope="scope">
          <div v-if="scope.row.price >= 0" class="z-price">+{{ scope.row.price }}</div>
          <div v-if="scope.row.price < 0" class="f-price">{{ scope.row.price }}</div>
        </template>
      </el-table-column>
      <el-table-column label="交易用户" min-width="130">
        <template slot-scope="scope">
          <span>{{ scope.row.nickname }}</span>
        </template>
      </el-table-column>
      <el-table-column label="交易类型" min-width="130">
        <template slot-scope="scope">
          <span>{{ scope.row.trading_type }}</span>
        </template>
      </el-table-column>
      <el-table-column label="支付方式" min-width="130">
        <template slot-scope="scope">
          <span>{{ scope.row.pay_type_name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="备注" min-width="130">
        <template slot-scope="scope">
          <span>{{ scope.row.mark }}</span>
        </template>
      </el-table-column>
    </el-table>
    <div class="acea-row row-right page">
      <pagination
        v-if="total"
        :total="total"
        :page.sync="formValidate.page"
        :limit.sync="formValidate.limit"
        @pagination="getList"
      />
    </div>
  </div>
</template>

<script>
import { getFlowList } from '@/api/finance';
import { mapState } from 'vuex';

export default {
  name: 'commissionDetails',
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      modals: false,
      detailsData: {},
      loading: false,
      staff: [],
      formValidate: {
        trading_type: 0,
        time: '',
        keywords: '',
        page: 1,
        limit: 20,
      },
      total: 0,
      tabList: [],
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
    };
  },
  props: {
    ids: {
      type: String,
      default: '',
    },
    time: {
      type: String,
      default: '',
    },
  },
  computed: {
    ...mapState('admin/layout', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    this.getList();
  },
  methods: {
    staffApi() {
      getFlowList(this.formValidate).then((res) => {
        this.staff = res.data;
      });
    },
    searchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 时间
    onchangeTime(e) {
      this.formValidate.start_time = e[0];
      this.formValidate.end_time = e[1];
    },
    // 列表
    getList() {
      this.formValidate.ids = this.ids;
      this.loading = true;
      getFlowList(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    reset() {
      this.formValidate = {
        ids: this.ids,
        store_id: '',
        keywork: '',
        data: '',
        page: 1,
        limit: 15,
      };
      this.getList();
    },
    // 关闭按钮
    cancel() {
      this.$emit('close');
      this.formValidate = {
        ids: '',
        store_id: '',
        keywork: '',
        data: '',
        page: 1,
        limit: 15,
      };
    },
  },
};
</script>

<style lang="scss" scoped>
.colorred {
  color: #ff5722;
}
.colorgreen {
  color: #009688;
}
.search {
  width: 86px;
  height: 32px;
  background: var(--prev-color-primary);
  border-radius: 4px;
  text-align: center;
  line-height: 32px;
  font-size: 13px;
  font-family: PingFangSC-Regular, PingFang SC;
  font-weight: 400;
  color: #ffffff;
  cursor: pointer;
}
.reset {
  width: 86px;
  height: 32px;
  border-radius: 4px;
  border: 1px solid rgba(151, 151, 151, 0.36);
  text-align: center;
  line-height: 32px;
  font-size: 13px;
  font-family: PingFangSC-Regular, PingFang SC;
  font-weight: 400;
  color: rgba(0, 0, 0, 0.85);
  cursor: pointer;
}
.table {
  .ivu-table-default {
    overflow-y: auto;
    max-height: 350px;
  }
}
.dashboard-workplace {
  &-header {
    &-avatar {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      margin-right: 16px;
      font-weight: 600;
    }

    &-tip {
      width: 82%;
      display: inline-block;
      vertical-align: middle;

      &-title {
        font-size: 13px;
        color: #000000;
        margin-bottom: 12px;
      }

      &-desc {
        &-sp {
          width: 33.33%;
          color: #17233d;
          font-size: 12px;
          display: inline-block;
        }
      }
    }

    &-extra {
      .ivu-col {
        p {
          text-align: right;
        }

        p:first-child {
          span:first-child {
            margin-right: 4px;
          }

          span:last-child {
            color: #808695;
          }
        }

        p:last-child {
          font-size: 22px;
        }
      }
    }
  }
}
.z-price {
  color: red;
}

.f-price {
  color: green;
}
</style>
