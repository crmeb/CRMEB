<template>
  <!-- 订单备注弹窗 -->
  <el-dialog
    title="订单备注"
    width="540px"
    v-model="dialogVisible"
    @close="handleClose"
    :close-on-click-modal="false"
    :destroy-on-close="true"
  >
    <el-form ref="remarkFormRef" :model="formData" label-width="80px">
      <el-form-item label="备注：">
        <el-input
          type="textarea"
          v-model="formData.remark"
          :rows="4"
          maxlength="200"
          show-word-limit
          placeholder="请输入备注内容"
        ></el-input>
      </el-form-item>
    </el-form>
    <template #footer
      ><div>
        <el-button @click="handleClose">取消</el-button>
        <el-button type="primary" @click="submitRemark" :loading="submitLoading">保存</el-button>
      </div></template
    >
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { ElMessage } from 'element-plus';
import request from '@/libs/request';

defineOptions({ name: 'OrderRemark' });

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  orderId: {
    type: [Number, String],
    default: 0,
  },
  remark: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['success', 'close']);

const remarkFormRef = ref(null);
const dialogVisible = ref(false);
const submitLoading = ref(false);
const formData = reactive({
  remark: '',
});

watch(
  () => props.visible,
  (val) => {
    dialogVisible.value = val;
    if (val) {
      formData.remark = props.remark || '';
    }
  },
  { immediate: true },
);

/**
 * 提交备注
 */
function submitRemark() {
  if (!formData.remark) {
    ElMessage.error('请输入备注内容');
    return;
  }
  submitLoading.value = true;
  request
    .put('normal/order/remark', {
      id: props.orderId,
      remark: formData.remark,
    })
    .then((res) => {
      ElMessage.success(res.msg || '备注成功');
      dialogVisible.value = false;
      emit('success');
    })
    .catch((res) => {
      ElMessage.error(res.msg || '备注失败');
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
