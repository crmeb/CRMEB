<template>
  <div>
    <div class="i-layout-page-header header-title">
      <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-table :data="tabList" v-loading="loading" empty-text="暂无数据" highlight-current-row>
        <el-table-column label="类型" width="100">
          <template #default="scope">
            <span>{{ scope.row.type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="文件地址" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.filename }}</span>
          </template>
        </el-table-column>
        <el-table-column label="校验码" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.cthash }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上次访问时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.atime }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上次修改时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.mtime }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上次改变时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.ctime }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { fileListApi } from './api';

defineOptions({ name: 'systemFile' });

const loading = ref(false);
const tabList = ref([]);

getList();

// 列表
function getList() {
  loading.value = true;
  fileListApi()
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
</script>

<style scoped></style>
