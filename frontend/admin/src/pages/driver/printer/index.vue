<template>
  <div>
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="添加并维护打印机设备，绑定驱动、设置打印时机与打印联数。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-row class="action-row mb14">
        <el-col :span="24">
          <el-button
            v-if="hasPermission('admin-printer-create')"
            v-auth="['admin-printer-create']"
            type="primary"
            v-db-click
            :loading="driverLoading"
            @click="handleAdd"
          >
            添加打印机
          </el-button>
        </el-col>
      </el-row>

      <el-table :data="list" ref="table" v-loading="loading" highlight-current-row row-key="id">
        <el-table-column label="排序" width="80" prop="sort">
          <template #default="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>

        <el-table-column label="打印机名称" min-width="150">
          <template #default="scope">
            <span class="font-weight-bold">{{ scope.row.print_name }}</span>
          </template>
        </el-table-column>

        <el-table-column label="驱动" min-width="120">
          <template #default="scope">
            <el-tag>{{ scope.row.driver }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="打印机ID" min-width="150" prop="printer_id" />

        <el-table-column label="驱动状态" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.driver_status ? 'success' : 'danger'">
              {{ scope.row.driver_status ? '正常' : '缺失' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="打印时机" width="120">
          <template #default="scope">
            <el-tag :type="scope.row.print_type == 1 ? 'primary' : 'warning'">
              {{ scope.row.print_type == 1 ? '支付后打印' : '下单后打印' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="打印联数" width="80">
          <template #default="scope">
            <span>{{ scope.row.times }} 联</span>
          </template>
        </el-table-column>

        <el-table-column label="状态" width="120">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              v-auth="['admin-printer-status']"
              :loading="statusLoading[scope.row.id]"
              :disabled="loading || statusLoading[scope.row.id] || !hasPermission('admin-printer-status')"
              @change="handleStatusChange(scope.row)"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
            />
          </template>
        </el-table-column>

        <el-table-column label="操作" fixed="right" width="200">
          <template #default="scope">
            <el-button
              v-if="hasPermission('admin-printer-content-read')"
              v-auth="['admin-printer-content-read']"
              link
              type="primary"
              v-db-click
              @click="handleDesign(scope.row)"
            >
              设计
            </el-button>
            <el-button
              v-if="hasPermission('admin-printer-detail')"
              v-auth="['admin-printer-detail']"
              link
              type="primary"
              v-db-click
              @click="handleEdit(scope.row)"
            >
              设置
            </el-button>
            <el-button
              v-if="hasPermission('admin-printer-delete')"
              v-auth="['admin-printer-delete']"
              link
              type="danger"
              v-db-click
              @click="handleDelete(scope.row, scope.$index)"
            >
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 添加/编辑抽屉 -->
    <el-drawer
      v-model="drawerVisible"
      :title="drawerTitle"
      :size="drawerSize"
      direction="rtl"
      :close-on-click-modal="false"
      :show-close="!drawerSubmitting"
      :close-on-press-escape="!drawerSubmitting"
    >
      <PrinterEdit
        v-if="drawerVisible"
        :driver-id="currentDriverId"
        :is-add="isAddMode"
        :driver-options="driverOptions"
        @close="drawerVisible = false"
        @success="handleEditSuccess"
        @submitting="drawerSubmitting = $event"
      />
    </el-drawer>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import PrinterEdit from './edit.vue';
import { usePrinterAuth } from './usePrinterAuth';
import { modalSure } from '@/utils/public';
import Setting from '@/setting';
import { useMediaStore } from '@/store/module/media';
import { printerChannelListApi, printerChannelSetStatusApi, printerChannelDriversApi } from '@/api/driver/printer';

defineOptions({ name: 'PrinterChannelList' });

const router = useRouter();
const { hasPermission } = usePrinterAuth();
const mediaStore = useMediaStore();

const loading = ref(false);
const list = ref([]);
const statusLoading = ref({});
const driverLoading = ref(false);

// 抽屉相关
const drawerVisible = ref(false);
const drawerSubmitting = ref(false);
const isAddMode = ref(false);
const currentDriverId = ref(null);
const driverOptions = ref([]);

const drawerTitle = computed(() => (isAddMode.value ? '添加打印机' : '设置打印机'));
const drawerSize = computed(() => (mediaStore.isMobile ? '100%' : '50%'));

// created
getList();

// 获取列表
function getList() {
  if (!hasPermission('admin-printer-list')) return;
  loading.value = true;
  printerChannelListApi()
    .then((res) => {
      list.value = res.data || [];
    })
    .catch((err) => {
      ElMessage.error(err.msg || '打印机列表加载失败');
    })
    .finally(() => {
      loading.value = false;
    });
}

// 添加
async function handleAdd() {
  if (driverLoading.value || !hasPermission('admin-printer-create')) return;
  if (!(await loadDriverOptions())) return;
  if (!driverOptions.value.length) {
    ElMessage.warning('暂无可用打印机驱动');
    return;
  }
  isAddMode.value = true;
  currentDriverId.value = null;
  drawerVisible.value = true;
}

// 编辑
function handleEdit(row) {
  if (!hasPermission('admin-printer-detail')) return;
  isAddMode.value = false;
  currentDriverId.value = row.id;
  drawerVisible.value = true;
}

// 编辑成功回调
function handleEditSuccess() {
  drawerVisible.value = false;
  getList();
}

// 设计（跳转小票内容配置）
function handleDesign(row) {
  if (!hasPermission('admin-printer-content-read')) return;
  router.push({
    path: Setting.routePre + '/setting/printer/content',
    query: { id: row.id },
  });
}

// 从驱动字典加载可用项
async function loadDriverOptions() {
  if (!hasPermission('admin-printer-drivers')) {
    ElMessage.error('没有查看打印机驱动的权限');
    return false;
  }
  driverLoading.value = true;
  try {
    const res = await printerChannelDriversApi();
    driverOptions.value = res.data || [];
    return true;
  } catch (err) {
    ElMessage.error(err.msg || '打印机驱动加载失败');
    return false;
  } finally {
    driverLoading.value = false;
  }
}

// 状态变更
function handleStatusChange(row) {
  const previousStatus = row.status === 1 ? 0 : 1;
  if (!hasPermission('admin-printer-status')) {
    row.status = previousStatus;
    return;
  }
  if (statusLoading.value[row.id]) return;
  statusLoading.value[row.id] = true;
  return printerChannelSetStatusApi(row.id, row.status)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      row.status = previousStatus;
      ElMessage.error(err.msg || '打印机状态更新失败');
    })
    .finally(() => {
      statusLoading.value[row.id] = false;
    });
}

// 删除
function handleDelete(row, index) {
  if (!hasPermission('admin-printer-delete')) return;
  return modalSure({
    title: '删除打印机',
    info: '删除后该打印机将不再接收小票，设备配置不可恢复。',
    num: index,
    url: `driver/printer_channel/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  })
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((err) => {
      ElMessage.error(err.msg || '打印机删除失败');
    });
}
</script>

<style lang="scss" scoped>
.font-weight-bold {
  font-weight: bold;
}
</style>
