<template>
  <!-- 修改收货地址弹窗 -->
  <el-dialog
    title="修改收货地址"
    width="500px"
    v-model="dialogVisible"
    @close="handleClose"
    :close-on-click-modal="false"
    :destroy-on-close="true"
  >
    <el-form ref="addressFormRef" :model="formData" label-width="100px" :rules="rules">
      <el-form-item label="订单号：">
        <span>{{ formData.order_no }}</span>
      </el-form-item>
      <el-form-item label="收货人：" prop="real_name">
        <el-input v-model="formData.real_name" placeholder="请输入收货人姓名" style="width: 300px"></el-input>
      </el-form-item>
      <el-form-item label="收货电话：" prop="user_phone">
        <el-input v-model="formData.user_phone" placeholder="请输入收货人电话" style="width: 300px"></el-input>
      </el-form-item>
      <el-form-item label="详细地址：" prop="user_address">
        <el-input v-model="formData.user_address" placeholder="请输入省市区详细地址" style="width: 300px"></el-input>
      </el-form-item>
    </el-form>
    <template #footer
      ><div>
        <el-button @click="handleClose">取消</el-button>
        <el-button type="primary" @click="submitAddress" :loading="submitLoading">保存</el-button>
      </div></template
    >
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import request from '@/libs/request';

defineOptions({ name: 'OrderAddress' });

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  orderNo: {
    type: String,
    default: '',
  },
  contactName: {
    type: String,
    default: '',
  },
  contactPhone: {
    type: String,
    default: '',
  },
  contactAddress: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['success', 'close']);

const addressFormRef = ref(null);
const dialogVisible = ref(false);
const submitLoading = ref(false);
const formData = ref({
  order_no: '',
  real_name: '',
  user_phone: '',
  user_address: '',
});
const rules = {
  real_name: [{ required: true, message: '请输入收货人姓名', trigger: 'blur' }],
  user_phone: [{ required: true, message: '请输入收货人电话', trigger: 'blur' }],
  user_address: [{ required: true, message: '请输入收货人详细地址', trigger: 'blur' }],
};

watch(
  () => props.visible,
  (val) => {
    dialogVisible.value = val;
    if (val) {
      formData.value = {
        order_no: props.orderNo || '',
        real_name: props.contactName || '',
        user_phone: props.contactPhone || '',
        user_address: props.contactAddress || '',
      };
      nextTick(() => {
        addressFormRef.value && addressFormRef.value.clearValidate();
      });
    }
  },
  { immediate: true },
);

/**
 * 提交修改地址
 */
function submitAddress() {
  addressFormRef.value.validate((valid) => {
    if (!valid) return;
    submitLoading.value = true;
    request
      .put('normal/order/edit_address', {
        order_no: formData.value.order_no,
        real_name: formData.value.real_name,
        user_phone: formData.value.user_phone,
        user_address: formData.value.user_address,
      })
      .then((res) => {
        ElMessage.success(res.msg || '修改地址成功');
        dialogVisible.value = false;
        emit('success');
      })
      .catch((res) => {
        ElMessage.error(res.msg || '修改地址失败');
      })
      .finally(() => {
        submitLoading.value = false;
      });
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
