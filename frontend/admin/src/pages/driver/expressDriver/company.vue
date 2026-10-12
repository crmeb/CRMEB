<template>
  <div>
    <el-row class="mb14">
      <el-col :span="24">
        <el-button type="primary" v-db-click @click="openAddDialog"> 新增物流公司 </el-button>
      </el-col>
    </el-row>

    <el-table :data="list" v-loading="loading" highlight-current-row row-key="id">
      <el-table-column label="排序" width="80" prop="sort" align="center" />

      <el-table-column label="快递公司" min-width="120">
        <template #default="scope">
          <span class="font-weight-bold">{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="编码" width="100" prop="code">
        <template #default="scope">
          <el-tag>{{ scope.row.code }}</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="绑定驱动" min-width="120" prop="driver_name">
        <template #default="scope">
          <el-tag type="info">{{ scope.row.driver_name || scope.row.driver_key || '-' }}</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="月结账号" width="110" prop="account">
        <template #default="scope">
          <span>{{ scope.row.account || '-' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="打印机" width="90">
        <template #default="scope">
          <el-tag v-if="scope.row.print_open" type="success">已配置</el-tag>
          <span v-else class="text-muted">未开启</span>
        </template>
      </el-table-column>

      <el-table-column label="展示" width="90">
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

      <el-table-column label="状态" width="90">
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

      <el-table-column label="操作" fixed="right" width="120" align="center">
        <template #default="scope">
          <a v-db-click @click="openEditDialog(scope.row)"> 编辑 </a>
          <el-divider direction="vertical" />
          <a v-db-click class="text-danger" @click="handleDelete(scope.row)"> 删除 </a>
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
import { modalSure } from '@/utils/public';
import { companyListApi, companySetStatusApi, companySetIsShowApi } from '@/api/driver/expressCompany';

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

function handleDelete(row) {
  modalSure({
    title: `删除物流公司「${row.name}」`,
    info: '删除后不可恢复，请谨慎操作。',
    url: `/driver/express_company/delete/${row.id}`,
    method: 'DELETE',
    ids: '',
  })
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
/* 开关未选中态：白字压在浅灰轨道上对比度过低，改用次要文本色 */
:deep(.defineSwitch .el-switch__label--left.is-active) {
  color: var(--prev-color-text-secondary) !important;
}
</style>
