<template>
  <div>
    <el-row class="mb14">
      <el-col :span="24">
        <el-button type="primary" v-db-click @click="openAddDialog">
          <i class="el-icon-plus"></i> 新增物流公司
        </el-button>
      </el-col>
    </el-row>

    <el-table :data="list" v-loading="loading" highlight-current-row row-key="id">
      <el-table-column label="排序" width="120" prop="sort" align="center">
        <template #default="scope">
          <el-input
            v-model="scope.row.sort"
            type="number"
            style="width: 70px"
            class="sort-input-center"
            @change="handleSortChange(scope.row)"
          />
        </template>
      </el-table-column>

      <el-table-column label="快递公司" min-width="140">
        <template #default="scope">
          <span class="font-weight-bold">{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="编码" width="120" prop="code">
        <template #default="scope">
          <el-tag>{{ scope.row.code }}</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="绑定驱动" width="120" prop="driver_name">
        <template #default="scope">
          <el-tag type="info">{{ scope.row.driver_name || scope.row.driver_key || '-' }}</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="月结账号" width="140" prop="account">
        <template #default="scope">
          <span>{{ scope.row.account || '-' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="打印机" width="120">
        <template #default="scope">
          <el-tag v-if="scope.row.print_open" type="success">已配置</el-tag>
          <span v-else class="text-muted">未开启</span>
        </template>
      </el-table-column>

      <el-table-column label="展示" width="120">
        <template #default="scope">
          <el-switch
            :active-value="1"
            :inactive-value="0"
            v-model="scope.row.is_show"
            @change="handleIsShowChange(scope.row)"
            active-text="展示"
            inactive-text="隐藏"
            class="defineSwitch"
          />
        </template>
      </el-table-column>

      <el-table-column label="状态" width="120">
        <template #default="scope">
          <el-switch
            :active-value="1"
            :inactive-value="0"
            v-model="scope.row.status"
            @change="handleStatusChange(scope.row)"
            active-text="启用"
            inactive-text="禁用"
            class="defineSwitch"
          />
        </template>
      </el-table-column>

      <el-table-column label="操作" fixed="right" width="160" align="center">
        <template #default="scope">
          <el-button type="text" v-db-click @click="openEditDialog(scope.row)"> 编辑 </el-button>
          <el-popconfirm title="确认删除该物流公司？" @confirm="handleDelete(scope.row)">
            <template #reference><el-button type="text" class="text-danger" v-db-click> 删除 </el-button></template>
          </el-popconfirm>
        </template>
      </el-table-column>
    </el-table>

    <companyEdit
      v-if="showEditDialog"
      :company-id="currentCompanyId"
      @close="showEditDialog = false"
      @success="handleEditSuccess"
    />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import companyEdit from './companyEdit.vue';
import {
  companyListApi,
  companySetStatusApi,
  companySetIsShowApi,
  companySortApi,
  companyDeleteApi,
} from '@/api/driver/expressCompany';

defineOptions({ name: 'expressCompany' });

const list = ref([]);
const loading = ref(false);
const showEditDialog = ref(false);
const currentCompanyId = ref(null);

onMounted(() => {
  getList();
});

function getList() {
  loading.value = true;
  companyListApi()
    .then((res) => {
      list.value = res.data.list || [];
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
}

function openAddDialog() {
  currentCompanyId.value = null;
  showEditDialog.value = true;
}

function openEditDialog(row) {
  currentCompanyId.value = row.id;
  showEditDialog.value = true;
}

function handleEditSuccess() {
  showEditDialog.value = false;
  getList();
}

function handleStatusChange(row) {
  companySetStatusApi(row.id, row.status)
    .then((res) => {
      ElMessage.success(res.msg || '修改成功');
    })
    .catch(() => {
      row.status = row.status === 1 ? 0 : 1;
    });
}

function handleIsShowChange(row) {
  companySetIsShowApi(row.id, row.is_show)
    .then((res) => {
      ElMessage.success(res.msg || '修改成功');
    })
    .catch(() => {
      row.is_show = row.is_show === 1 ? 0 : 1;
    });
}

function handleSortChange(row) {
  companySortApi({ id: row.id, sort: row.sort })
    .then((res) => {
      ElMessage.success(res.msg || '排序成功');
    })
    .catch(() => {
      getList();
    });
}

function handleDelete(row) {
  companyDeleteApi(row.id)
    .then((res) => {
      ElMessage.success(res.msg || '删除成功');
      getList();
    })
    .catch(() => {});
}

defineExpose({ getList });
</script>

<style scoped>
.font-weight-bold {
  font-weight: bold;
}
.mb20 {
  margin-bottom: 20px;
}
.text-muted {
  color: #999;
}
.text-danger {
  color: #e74c3c;
}
:deep(.sort-input-center input) {
  text-align: center;
}
</style>
