<template>
  <!-- 立即退款弹窗 -->
  <el-dialog
    title="立即退款"
    width="500px"
    v-model="dialogVisible"
    @close="handleClose"
    :close-on-click-modal="false"
    :destroy-on-close="true"
  >
    <el-form ref="refundFormRef" :model="formData" label-width="100px" v-loading="formLoading">
      <el-form-item label="订单号：">
        <span>{{ formData.order_no }}</span>
      </el-form-item>
      <el-form-item label="订单金额：">
        <span>¥{{ formData.pay_price }}</span>
      </el-form-item>
      <el-form-item label="可退金额：">
        <span style="color: #ed4014; font-weight: bold">¥{{ formData.max_refund }}</span>
      </el-form-item>
      <el-form-item label="退款金额：">
        <el-input-number
          v-model="formData.refund_price"
          :min="0.01"
          :max="parseFloat(formData.max_refund) || 0"
          :precision="2"
          :step="0.01"
          style="width: 200px"
        ></el-input-number>
        <span style="margin-left: 8px; color: var(--prev-color-text-secondary); font-size: 12px"
          >输入0~{{ formData.max_refund }}</span
        >
      </el-form-item>
    </el-form>
    <template #footer
      ><div>
        <el-button @click="handleClose">取消</el-button>
        <el-button type="danger" @click="submitRefund" :loading="submitLoading">确认退款</el-button>
      </div></template
    >
  </el-dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import request from '@/libs/request';

defineOptions({ name: 'OrderRefund' });

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  orderNo: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['success', 'close']);

const refundFormRef = ref(null);
const dialogVisible = ref(false);
const formLoading = ref(false);
const submitLoading = ref(false);
const formData = ref({
  order_no: '',
  pay_price: '0.00',
  max_refund: '0.00',
  refund_price: 0,
});

watch(
  () => props.visible,
  (val) => {
    dialogVisible.value = val;
    if (val && props.orderNo) {
      loadRefundForm();
    }
  },
  { immediate: true },
);

/**
 * 获取退款表单数据
 */
function loadRefundForm() {
  formLoading.value = true;
  request
    .get('normal/order/get_refund_form/' + props.orderNo)
    .then((res) => {
      formData.value = {
        order_no: res.data.order_no,
        pay_price: res.data.pay_price,
        max_refund: res.data.max_refund,
        refund_price: parseFloat(res.data.max_refund) || 0,
      };
    })
    .catch((res) => {
      ElMessage.error(res.msg || '获取退款信息失败');
    })
    .finally(() => {
      formLoading.value = false;
    });
}

/**
 * 提交退款
 */
function submitRefund() {
  if (!formData.value.refund_price || parseFloat(formData.value.refund_price) <= 0) {
    ElMessage.error('请输入退款金额');
    return;
  }
  if (parseFloat(formData.value.refund_price) > parseFloat(formData.value.max_refund)) {
    ElMessage.error('退款金额不能超过可退金额');
    return;
  }
  submitLoading.value = true;
  request
    .post('normal/order/process_refund', {
      order_no: formData.value.order_no,
      refund_price: formData.value.refund_price,
    })
    .then((res) => {
      ElMessage.success(res.msg || '退款成功');
      dialogVisible.value = false;
      emit('success');
    })
    .catch((res) => {
      ElMessage.error(res.msg || '退款失败');
    })
    .finally(() => {
      submitLoading.value = false;
    });
}

/**
 * 关闭弹窗
 */
function handleClose() {
  dialogVisible.value = false;
  emit('close');
}
</script>
