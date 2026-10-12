<template>
  <div>
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="配置物流查询服务商并启停驱动，订单物流轨迹经此获取。"
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

        <el-table-column label="驱动标识" min-width="120">
          <template #default="scope">
            <el-tag>{{ scope.row.driver_key }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="是否内置" width="80">
          <template #default="scope">
            <el-tag v-if="scope.row.is_builtin" type="success">是</el-tag>
            <el-tag v-else type="info">否</el-tag>
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

        <el-table-column label="操作" fixed="right" width="150">
          <template #default="scope">
            <a v-db-click @click="openEditDrawer(scope.row)">设置</a>
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
      <LogisticsDriverEdit
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
import LogisticsDriverEdit from './edit.vue';
import { logisticsDriverListApi, logisticsDriverSetStatusApi } from '@/api/driver/logisticsDriver';

defineOptions({ name: 'logisticsDriver' });

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
  logisticsDriverListApi()
    .then((res) => {
      list.value = res.data.list || [];
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
}

function handleStatusChange(row) {
  logisticsDriverSetStatusApi(row.id, row.status)
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
