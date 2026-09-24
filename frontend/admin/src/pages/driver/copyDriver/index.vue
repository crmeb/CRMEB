<template>
  <div>
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="接入商品采集服务商，按支持平台启用并填写账号密钥，采集商品时自动调用。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-table :data="list" ref="table" v-loading="loading" highlight-current-row row-key="id">
        <el-table-column label="排序" width="80" prop="sort">
          <template #default="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>

        <el-table-column label="驱动名称" min-width="150">
          <template #default="scope">
            <span class="font-weight-bold">{{ scope.row.driver_name }}</span>
          </template>
        </el-table-column>

        <el-table-column label="驱动标识" min-width="100">
          <template #default="scope">
            <el-tag>{{ scope.row.driver_key }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="支持平台" min-width="200">
          <template #default="scope">
            <span>{{ getPlatformNames(scope.row.platforms) }}</span>
          </template>
        </el-table-column>

        <el-table-column label="状态" width="80">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              @change="handleStatusChange(scope.row)"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
            />
          </template>
        </el-table-column>

        <el-table-column label="操作" fixed="right" width="150" align="center">
          <template #default="scope">
            <el-button type="text" v-db-click @click="openEditDrawer(scope.row)"> 设置 </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-drawer
      :title="'编辑 - ' + (currentDriver ? currentDriver.driver_name : '')"
      v-model="showEditDrawer"
      direction="rtl"
      size="600px"
    >
      <CopyDriverEdit :driver-id="currentDriverId" @close="showEditDrawer = false" @success="handleEditSuccess" />
    </el-drawer>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import CopyDriverEdit from './edit.vue';
import { copyDriverListApi, copyDriverSetStatusApi } from '@/api/driver/copyDriver';

defineOptions({ name: 'copyDriver' });

const list = ref([]);
const loading = ref(false);
const showEditDrawer = ref(false);
const currentDriver = ref(null);
const currentDriverId = ref(null);

onMounted(() => {
  getList();
});

function getList() {
  loading.value = true;
  copyDriverListApi()
    .then((res) => {
      list.value = res.data.list || [];
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
}

function getPlatformNames(platforms) {
  if (!platforms || !Array.isArray(platforms)) return '-';
  return platforms.map((p) => p.name).join('、');
}

function handleStatusChange(row) {
  copyDriverSetStatusApi(row.id, row.status)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch(() => {
      row.status = row.status === 1 ? 0 : 1;
    });
}

function openEditDrawer(row) {
  currentDriver.value = row;
  currentDriverId.value = row.id;
  showEditDrawer.value = true;
}

function handleEditSuccess() {
  showEditDrawer.value = false;
  getList();
  ElMessage.success('保存成功');
}
</script>

<style scoped>
.font-weight-bold {
  font-weight: bold;
}
.mt16 {
  margin-top: 16px;
}
.mb20 {
  margin-bottom: 20px;
}
</style>
