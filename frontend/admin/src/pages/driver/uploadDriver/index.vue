<template>
  <div>
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="管理本地与云存储驱动并切换启用，图片附件将上传至启用的存储平台。"
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

        <el-table-column label="类型" min-width="100">
          <template #default="scope">
            <el-tag :type="scope.row.is_builtin ? 'success' : 'primary'">
              {{ scope.row.is_builtin ? '本地存储' : '云存储扩展' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="状态" width="80">
          <template #default="scope">
            <el-switch
              v-auth="['driver-upload-status']"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :disabled="statusSaving || scope.row._statusSaving"
              @change="handleStatusChange(scope.row)"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
            />
          </template>
        </el-table-column>

        <el-table-column label="操作" fixed="right" width="150">
          <template #default="scope">
            <a v-auth="['driver-upload-save-config']" v-db-click @click="openEditDrawer(scope.row)">设置</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-drawer
      class="upload-driver-drawer"
      :title="'编辑 - ' + (currentDriver ? currentDriver.driver_name : '')"
      v-model="showEditDrawer"
      direction="rtl"
      size="min(1100px, 100%)"
    >
      <UploadDriverEdit
        v-if="showEditDrawer"
        :driver-id="currentDriverId"
        @close="showEditDrawer = false"
        @success="handleEditSuccess"
      />
    </el-drawer>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import UploadDriverEdit from './edit.vue';
import { uploadDriverListApi, uploadDriverSetStatusApi } from '@/api/driver/uploadDriver';

defineOptions({ name: 'uploadDriver' });

const list = ref([]);
const loading = ref(false);
const statusSaving = ref(false);
let listRequest = 0;
const showEditDrawer = ref(false);
const currentDriver = ref(null);
const currentDriverId = ref(null);

onMounted(() => {
  getList();
});

function getList() {
  const requestId = ++listRequest;
  loading.value = true;
  return uploadDriverListApi()
    .then((res) => {
      if (requestId !== listRequest) return;
      list.value = res.data.list || [];
    })
    .catch((err) => {
      if (requestId === listRequest) ElMessage.error(err.msg || err.message || '驱动列表加载失败');
    })
    .finally(() => {
      if (requestId === listRequest) loading.value = false;
    });
}

function handleStatusChange(row) {
  if (row._statusSaving || statusSaving.value) return;
  const previous = row.status === 1 ? 0 : 1;
  row._statusSaving = true;
  statusSaving.value = true;
  return uploadDriverSetStatusApi(row.id, row.status)
    .then((res) => {
      ElMessage.success(res.msg);
      return getList();
    })
    .catch((err) => {
      row.status = previous;
      ElMessage.error(err.msg || err.message || '切换失败');
    })
    .finally(() => {
      row._statusSaving = false;
      statusSaving.value = false;
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
@media (max-width: 576px) {
  .upload-driver-drawer :deep(.el-drawer__body) {
    padding: 20px 16px;
  }
}
</style>
