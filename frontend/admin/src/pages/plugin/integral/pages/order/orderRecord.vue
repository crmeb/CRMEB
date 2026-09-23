<template>
  <el-dialog v-model="modals" title="订单记录" width="680px" class="order_box" :show-close="true">
    <el-table :data="recordList" v-loading="loading" empty-text="暂无数据">
      <el-table-column label="操作时间" min-width="160">
        <template #default="scope">
          <span>{{ scope.row.change_time }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作类型" min-width="100">
        <template #default="scope">
          <span>{{ scope.row.change_type }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作描述" min-width="200">
        <template #default="scope">
          <span>{{ scope.row.change_message }}</span>
        </template>
      </el-table-column>
    </el-table>
    <template #footer><span></span></template>
  </el-dialog>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { getIntegralOrderRecord } from '../../api';

defineOptions({ name: 'integralOrderRecord' });

defineProps({
  orderId: Number,
});

const modals = ref(false);
const loading = ref(false);
const recordList = ref([]);

function getList(id) {
  loading.value = true;
  getIntegralOrderRecord({ id })
    .then(async (res) => {
      recordList.value = res.data;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

defineExpose({ modals, getList });
</script>
