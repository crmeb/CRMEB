<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: '0 20px 20px' }">
      <el-tabs v-model="signFrom.type" @tab-click="onClickTab">
        <el-tab-pane :label="item.name" :name="item.type" v-for="(item, index) in tabList" :key="index" />
      </el-tabs>
      <el-button v-db-click @click="add" type="primary">{{
        signFrom.type == 0 ? '添加连续签到奖励' : '添加累积签到奖励'
      }}</el-button>
      <el-table
        :data="tableData"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="类型" min-width="80">
          <template slot-scope="scope">
            <span>{{
              scope.row.type == 0 ? `连续签到${scope.row.days}天奖励` : `累积签到${scope.row.days}天奖励`
            }}</span>
          </template>
        </el-table-column>
        <el-table-column label="天数" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.days }} (天)</span>
          </template>
        </el-table-column>
        <el-table-column label="奖励积分" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.point }} (积分)</span>
          </template>
        </el-table-column>
        <el-table-column label="奖励经验" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.exp }} (经验)</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="signFrom.page"
          :limit.sync="signFrom.limit"
          @pagination="pageChange"
        />
      </div>
    </el-card>
  </div>
</template>
<script>
import { addSignRewards, signRewards, editSignRewards } from '@/api/marketing.js';
export default {
  name: '',
  data() {
    return {
      signFrom: {
        type: 0,
        page: 1,
        limit: 20,
      },
      tabList: [
        { type: '0', name: '连续签到奖励' },
        { type: '1', name: '累积签到奖励' },
      ],
      total: 0,
      tableData: [],
      loading: false,
    };
  },
  created() {
    this.getList();
  },
  mounted() {},
  methods: {
    onClickTab() {
      this.signFrom.page = 1;
      this.getList();
    },
    getList() {
      this.loading = true;
      signRewards(this.signFrom)
        .then((res) => {
          this.tableData = res.data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((err) => {
          this.$message.error(err.msg);
          this.loading = false;
        });
    },
    pageChange(val) {
      this.signFrom.page = val;
      this.getList();
    },
    add() {
      this.$modalForm(addSignRewards({ type: this.signFrom.type })).then((res) => {
        this.getList();
      });
    },
    edit(row) {
      this.$modalForm(editSignRewards(row.id)).then((res) => {
        this.getList();
      });
    },
    del(row) {
      let delfromData = {
        title: row.type == 0 ? `删除连续签到${row.days}天奖励` : `删除累计签到${row.days}天奖励`,
        url: `/marketing/sign/del_rewards/${row.id}`,
        method: 'DELETE',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>
<style lang="scss" scoped>
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
</style>
