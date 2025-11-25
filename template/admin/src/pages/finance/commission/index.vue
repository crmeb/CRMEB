<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form ref="formValidate" :label-width="labelWidth" label-position="right" inline @submit.native.prevent>
          <el-form-item label="昵称/ID：">
            <el-input placeholder="请输入" v-model="formValidate.nickname" clearable class="form_content_width" />
          </el-form-item>
          <el-form-item label="佣金范围：" class="tab_data">
            <el-input-number :controls="false" :min="0" class="mr10" v-model="formValidate.price_min" />
            <span class="mr10">一</span>
            <el-input-number :controls="false" :min="0" v-model="formValidate.price_max" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-button v-auth="['export-userCommission']" class="export" v-db-click @click="exports">导出</el-button>
      <el-table
        ref="table"
        :data="tabList"
        v-loading="loading"
        empty-text="暂无数据"
        @on-sort-change="sortChanged"
        class="mt14"
      >
        <el-table-column label="用户信息" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="总佣金金额" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.sum_number }}</span>
          </template>
        </el-table-column>
        <!-- <el-table-column label="账户余额" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.now_money }}</span>
          </template>
        </el-table-column> -->
        <el-table-column label="账户佣金" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.brokerage_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="提现佣金" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.extract_price }}</span>
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
    </el-card>
    <commission-details ref="commission"></commission-details>
  </div>
</template>
<script>
import { mapState } from 'vuex';
import { commissionListApi, userCommissionApi } from '@/api/finance';
import commissionDetails from './handle/commissionDetails';

export default {
  name: 'commissionRecord',
  components: { commissionDetails },
  data() {
    return {
      total: 0,
      loading: false,
      tabList: [],
      formValidate: {
        nickname: '',
        price_max: undefined,
        price_min: undefined,
        excel: 0,
        page: 1, // 当前页
        limit: 20, // 每页显示条数
      },
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
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
    // 列表
    getList() {
      this.loading = true;
      commissionListApi(this.formValidate)
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
    // 搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 导出
    exports() {
      let formValidate = this.formValidate;
      let data = {
        price_max: formValidate.price_max,
        price_min: formValidate.price_min,
        nickname: formValidate.nickname,
      };
      userCommissionApi(data)
        .then((res) => {
          location.href = res.data[0];
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 详情
    Info(row) {
      this.$refs.commission.modals = true;
      this.$refs.commission.getDetails(row.uid);
      this.$refs.commission.getList(row.uid);
    },
    // 排序
    sortChanged(e) {
      if (e.key == 'sum_number') {
        delete this.formValidate.brokerage_price;
      } else {
        delete this.formValidate.sum_number;
      }
      this.formValidate[e.key] = e.order;
      this.getList();
    },
  },
};
</script>

<style lang="scss" scoped>
.lines {
  padding-top: 6px !important;
}
.tabform .export {
  margin-left: 10px;
}
.tab_data ::v-deep .ivu-form-item-content {
  display: flex !important;
}
</style>
