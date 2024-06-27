<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row>
        <el-col v-bind="grid">
          <el-button v-auth="['admin-user-label_add']" type="primary" v-db-click @click="add">添加主播</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="labelLists"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="名称" min-width="300">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="电话" min-width="300">
          <template slot-scope="scope">
            <span>{{ scope.row.phone }}</span>
          </template>
        </el-table-column>
        <el-table-column label="微信号" min-width="300">
          <template slot-scope="scope">
            <span>{{ scope.row.wechat }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row.id)">修改</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除主播', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="labelFrom.page"
          :limit.sync="labelFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { liveAuchorList, liveAuchorAdd, liveAuchorDel } from '@/api/live';
export default {
  name: 'anchor',
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      labelFrom: {
        kerword: '',
        page: 1,
        limit: 15,
      },
      labelLists: [],
      total: 0,
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
  created() {
    this.getList();
  },
  methods: {
    // 添加
    add() {
      this.$modalForm(liveAuchorAdd(0)).then(() => this.getList());
    },
    // 修改
    edit(id) {
      this.$modalForm(liveAuchorAdd(id)).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `live/anchor/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.labelLists.splice(num, 1);
          if (!this.labelLists.length && this.labelFrom.page != 1) {
            this.labelFrom.page -= 1;
            this.getList();
          } else {
            this.getList();
          }
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 分组列表
    getList() {
      this.loading = true;
      liveAuchorList(this.labelFrom)
        .then(async (res) => {
          let data = res.data;
          this.labelLists = data.list;
          this.total = data.count;
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

<style scoped></style>
