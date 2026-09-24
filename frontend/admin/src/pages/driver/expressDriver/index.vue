<template>
  <div>
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="配置电子面单服务商账号并启停驱动，面单模板与寄件信息在电子面单设置中维护。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-alert
        title="这里仅管理电子面单服务商驱动；物流公司、打印机编号和寄件信息请到“设置 > 电子面单设置”配置。"
        type="info"
        show-icon
        :closable="false"
        class="mb20"
      />

      <el-table :data="driverList" v-loading="driverLoading" row-key="id">
        <el-table-column label="排序" width="80" prop="sort" />
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
        <el-table-column label="类型" width="120">
          <template #default="scope">
            <el-tag :type="scope.row.is_builtin ? 'success' : 'primary'">
              {{ scope.row.is_builtin ? '内置驱动' : '扩展驱动' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              class="defineSwitch"
              @change="handleStatusChange(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100" align="center">
          <template #default="scope">
            <el-button type="text" v-db-click @click="openEditDrawer(scope.row)">配置</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-drawer
      :title="'配置 - ' + (currentDriver ? currentDriver.driver_name : '')"
      v-model="showEditDrawer"
      direction="rtl"
      size="50%"
    >
      <ExpressDriverEdit
        v-if="showEditDrawer"
        :driver-id="currentDriverId"
        @close="showEditDrawer = false"
        @success="handleEditSuccess"
      />
    </el-drawer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import ExpressDriverEdit from './edit.vue';
import { expressDriverListApi, expressDriverSetStatusApi } from '@/api/driver/expressDriver';

defineOptions({ name: 'ExpressDriverList' });

const driverList = ref([]);
const driverLoading = ref(false);
const showEditDrawer = ref(false);
const currentDriver = ref(null);
const currentDriverId = ref(null);

getDriverList();

function getDriverList() {
  driverLoading.value = true;
  expressDriverListApi()
    .then((res) => {
      driverList.value = res.data.list || [];
    })
    .finally(() => {
      driverLoading.value = false;
    });
}
function handleStatusChange(row) {
  expressDriverSetStatusApi(row.id, row.status)
    .then((res) => {
      ElMessage.success(res.msg || '修改成功');
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
  getDriverList();
}
</script>

<style scoped>
.font-weight-bold {
  font-weight: bold;
}
.mb20 {
  margin-bottom: 20px;
}
.mt16 {
  margin-top: 16px;
}
</style>
