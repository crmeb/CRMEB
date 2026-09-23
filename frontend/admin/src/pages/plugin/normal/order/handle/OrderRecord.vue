<template>
  <el-drawer v-model="drawerVisible" title="操作记录" :wrapper-closable="false" :size="700" @close="handleClose">
    <el-card :bordered="false" shadow="never">
      <el-table :data="recordData" v-loading="loading" empty-text="暂无操作记录" highlight-current-row>
        <el-table-column label="操作类型" min-width="120">
          <template #default="scope">
            <el-tag :type="getRecordTagType(scope.row.change_type)">
              {{ getRecordTypeName(scope.row.change_type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作内容" min-width="200">
          <template #default="scope">
            <span>{{ scope.row.change_message || '--' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作时间" width="180">
          <template #default="scope">
            <span>{{ scope.row.change_time || '--' }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </el-drawer>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { getOrderRecord } from '../../normalOrder';

/**
 * 订单操作记录弹窗组件
 * 展示订单的操作日志（发货、退款、备注等）
 */
defineOptions({ name: 'OrderRecord' });

const props = defineProps({
  /** 弹窗可见性 */
  visible: {
    type: Boolean,
    default: false,
  },
  /** 订单ID */
  orderId: {
    type: Number,
    default: 0,
  },
  /** 订单号 */
  orderNo: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['close']);

const drawerVisible = ref(false);
const loading = ref(false);
const recordData = ref([]);

watch(
  () => props.visible,
  (val) => {
    drawerVisible.value = val;
    if (val && (props.orderId || props.orderNo)) {
      loadRecord();
    }
  },
  { immediate: true },
);

/**
 * 加载操作记录
 */
function loadRecord() {
  loading.value = true;
  const params = {};
  if (props.orderNo) {
    params.order_no = props.orderNo;
  } else {
    params.id = props.orderId;
  }
  getOrderRecord(params)
    .then((res) => {
      recordData.value = res.data || [];
    })
    .catch((res) => {
      ElMessage.error(res.msg || '获取操作记录失败');
    })
    .finally(() => {
      loading.value = false;
    });
}

/**
 * 获取操作类型的标签样式
 */
function getRecordTagType(type) {
  const map = {
    create: 'success',
    pay: 'success',
    delivery: 'primary',
    confirm: 'success',
    refund: 'warning',
    remark: 'info',
    edit: 'warning',
    cancel: 'danger',
    delete: 'danger',
  };
  return map[type] || 'info';
}

/**
 * 获取操作类型的中文名
 */
function getRecordTypeName(type) {
  const map = {
    create: '创建订单',
    pay: '支付',
    delivery: '发货',
    confirm: '确认收货',
    refund: '退款',
    remark: '备注',
    edit: '编辑',
    cancel: '取消订单',
    delete: '删除订单',
  };
  return map[type] || type || '--';
}

/**
 * 关闭弹窗
 */
function handleClose() {
  drawerVisible.value = false;
  emit('close');
}
</script>

<style scoped></style>
