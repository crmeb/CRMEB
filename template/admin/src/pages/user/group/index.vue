<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row>
        <el-col v-bind="grid">
          <el-button v-auth="['admin-user-group']" type="primary" v-db-click @click="add">添加分组</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="groupLists"
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
        <el-table-column label="分组" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.group_name }}</span>
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="操作" width="100">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row.id)">修改</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除分组', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="groupFrom.page"
          :limit.sync="groupFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { userGroupApi, groupAddApi } from '@/api/user';
export default {
  name: 'user_group',
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

      groupFrom: {
        page: 1,
        limit: 15,
      },
      groupLists: [],
      total: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 添加
    add() {
      this.$modalForm(groupAddApi(0))
        .then(() => this.getList())
        .catch(() => {
          console.log('error');
        });
    },
    // 分组列表
    getList() {
      this.loading = true;
      userGroupApi(this.groupFrom)
        .then(async (res) => {
          let data = res.data;
          this.groupLists = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 修改
    edit(id) {
      this.$modalForm(groupAddApi(id)).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `user/user_group/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.groupLists.splice(num, 1);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus"></style>
