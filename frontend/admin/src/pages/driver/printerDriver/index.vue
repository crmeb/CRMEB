<template>
  <div>
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="配置打印服务商账号并启停驱动，具体打印机设备在打印设置中维护。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-alert
        title="在这里配置打印服务商账号和密钥；具体设备只在“设置 > 打印设置”中填写打印机 ID。"
        type="info"
        show-icon
        :closable="false"
        class="mb20"
      />

      <el-table ref="table" v-loading="loading" :data="list" row-key="name">
        <el-table-column label="驱动名称" min-width="160">
          <template #default="scope">
            <span class="font-weight-bold">{{ scope.row.driver_name }}</span>
          </template>
        </el-table-column>

        <el-table-column label="驱动标识" min-width="140">
          <template #default="scope">
            <el-tag>{{ scope.row.driver_key }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="版本" width="110">
          <template #default="scope">
            {{ (scope.row.driver_info && scope.row.driver_info.version) || '-' }}
          </template>
        </el-table-column>

        <el-table-column label="作者" width="120">
          <template #default="scope">
            {{ (scope.row.driver_info && scope.row.driver_info.author) || '-' }}
          </template>
        </el-table-column>

        <el-table-column label="状态" width="120">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              v-auth="['admin-printer-driver-status']"
              :loading="statusLoading[scope.row.id]"
              :disabled="
                loading || statusLoading[scope.row.id] || !scope.row.id || !hasPermission('admin-printer-driver-status')
              "
              @change="changeStatus(scope.row)"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
            />
          </template>
        </el-table-column>

        <el-table-column label="说明" min-width="220">
          <template #default="scope">
            {{ scope.row.description || (scope.row.driver_info && scope.row.driver_info.description) || '-' }}
          </template>
        </el-table-column>

        <el-table-column label="操作" fixed="right" width="100" align="center">
          <template #default="scope">
            <el-button
              v-if="hasPermission('admin-printer-driver-detail')"
              v-auth="['admin-printer-driver-detail']"
              type="text"
              v-db-click
              :disabled="!scope.row.id"
              @click="showDetail(scope.row)"
            >
              配置
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-drawer
      :title="currentDriver ? '配置 - ' + currentDriver.driver_name : '驱动配置'"
      v-model="drawerVisible"
      direction="rtl"
      :size="drawerSize"
      :close-on-click-modal="!drawerSubmitting"
      :show-close="!drawerSubmitting"
      :close-on-press-escape="!drawerSubmitting"
    >
      <PrinterDriverEdit
        v-if="drawerVisible && currentDriver"
        :driver-id="currentDriver.id"
        @close="drawerVisible = false"
        @success="saved"
        @submitting="drawerSubmitting = $event"
      />
    </el-drawer>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import PrinterDriverEdit from './edit.vue';
import { printerDriverListApi, printerDriverSetStatusApi } from '@/api/driver/printerDriver';
import { usePrinterAuth } from '../printer/usePrinterAuth';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'PrinterDriverList' });

const loading = ref(false);
const list = ref([]);
const drawerVisible = ref(false);
const drawerSubmitting = ref(false);
const currentDriver = ref(null);
const statusLoading = ref({});
const { hasPermission } = usePrinterAuth();
const mediaStore = useMediaStore();
const drawerSize = computed(() => (mediaStore.isMobile ? '100%' : '50%'));

getList();

function getList() {
  if (!hasPermission('admin-printer-driver-list')) return;
  loading.value = true;
  printerDriverListApi()
    .then((res) => {
      list.value = res.data || [];
    })
    .catch((err) => {
      list.value = [];
      ElMessage.error(err.msg || '打印机驱动加载失败');
    })
    .finally(() => {
      loading.value = false;
    });
}
function showDetail(driver) {
  if (!driver.id || !hasPermission('admin-printer-driver-detail')) return;
  currentDriver.value = driver;
  drawerVisible.value = true;
}
function changeStatus(driver) {
  const previousStatus = driver.status === 1 ? 0 : 1;
  if (!driver.id || !hasPermission('admin-printer-driver-status')) {
    driver.status = previousStatus;
    return;
  }
  if (statusLoading.value[driver.id]) return;
  statusLoading.value[driver.id] = true;
  return printerDriverSetStatusApi(driver.id, driver.status)
    .then((res) => {
      ElMessage.success(res.msg || '状态更新成功');
    })
    .catch((err) => {
      driver.status = previousStatus;
      ElMessage.error(err.msg || '打印机驱动状态更新失败');
    })
    .finally(() => {
      statusLoading.value[driver.id] = false;
    });
}
function saved() {
  drawerVisible.value = false;
  getList();
  ElMessage.success('保存成功');
}
</script>

<style lang="scss" scoped>
.font-weight-bold {
  font-weight: bold;
}
</style>
