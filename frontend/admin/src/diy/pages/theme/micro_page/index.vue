<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          自定义移动端页面，添加后可通过链接在菜单、广告位等场景跳转使用。
        </span>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <div class="acea-row row-between-wrapper mb20">
        <div class="acea-row row-middle">
          <el-button type="primary" @click="add">添加微页面</el-button>
        </div>
      </div>
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="编号" min-width="80" prop="id"></el-table-column>
        <el-table-column label="名称" min-width="150" prop="title"></el-table-column>
        <el-table-column label="添加时间" min-width="150" prop="add_time"></el-table-column>
        <el-table-column label="更新时间" min-width="150" prop="up_time"></el-table-column>
        <el-table-column label="操作" fixed="right" width="150">
          <template #default="scope">
            <a @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a @click="del(scope.row, '删除微页面', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination v-if="total" :total="total" v-model:page="page" v-model:limit="limit" @pagination="getList" />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { getMicroPageList } from '@diy/api';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'MicroPageList' });

const router = useRouter();

const loading = ref(false);
const tableList = ref([]);
const total = ref(0);
const page = ref(1);
const limit = ref(20);

// created
getList();

function getList() {
  loading.value = true;
  getMicroPageList({ page: page.value, limit: limit.value })
    .then((res) => {
      tableList.value = res.data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err.msg);
    });
}

function add() {
  router.push({
    path: '/admin/setting/edit_theme',
    query: { type: 'home', page_type: 'micro', id: 0 },
  });
}

function edit(row) {
  router.push({
    path: '/admin/setting/edit_theme',
    query: { type: 'home', page_type: 'micro', id: row.id },
  });
}

function del(row, title, num) {
  let delfromData = {
    title: title,
    num: num,
    url: `theme/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
      if (!tableList.value.length && page.value > 1) {
        page.value = page.value - 1;
        getList();
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

function preview(row) {
  ElMessage.info('功能开发中');
}
</script>

<style scoped></style>
