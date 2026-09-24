<template>
  <div>
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="配置 IM 机器人通知渠道并启停驱动，系统告警与业务消息推送经此发送。"
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

        <el-table-column label="类型" min-width="100">
          <template #default="scope">
            <el-tag :type="scope.row.is_builtin ? 'success' : 'primary'">
              {{ scope.row.is_builtin ? '内置驱动' : '扩展驱动' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="状态" width="120">
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
            <el-button type="text" v-db-click @click="openEditDrawer(scope.row)"> 配置 </el-button>
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
      <BotDriverEdit
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
import BotDriverEdit from './edit.vue';
import { botDriverListApi, botDriverSetStatusApi } from '@/api/driver/botDriver';

defineOptions({ name: 'botDriver' });

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
  botDriverListApi()
    .then((res) => {
      list.value = res.data || [];
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
}

function handleStatusChange(row) {
  botDriverSetStatusApi(row.id, row.status)
    .then((res) => {
      ElMessage.success(res.msg || '修改成功');
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
