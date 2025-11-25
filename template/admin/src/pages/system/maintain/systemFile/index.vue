<template>
  <div>
    <div class="i-layout-page-header header-title">
      <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-table ref="selection" :data="tabList" v-loading="loading" empty-text="暂无数据" highlight-current-row>
        <el-table-column label="类型" width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="文件地址" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.filename }}</span>
          </template>
        </el-table-column>
        <el-table-column label="校验码" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.cthash }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上次访问时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.atime }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上次修改时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.mtime }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上次改变时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.ctime }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script>
import { fileListApi } from '@/api/system';
export default {
  name: 'systemFile',
  data() {
    return {
      loading: false,
      tabList: [],
    };
  },
  created() {
    this.getList();
  },
  methods: {
    // 列表
    getList() {
      this.loading = true;
      fileListApi()
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
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
