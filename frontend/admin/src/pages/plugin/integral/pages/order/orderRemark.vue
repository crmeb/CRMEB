<template>
  <el-dialog v-model="modals" title="订单备注" width="500px" class="order_box" :show-close="true">
    <el-form ref="formValidate" :model="formValidate" label-width="80px">
      <el-form-item label="备注内容：">
        <el-input v-model="formValidate.remark" type="textarea" :rows="4" placeholder="请输入备注内容"></el-input>
      </el-form-item>
    </el-form>
    <template #footer
      ><span class="dialog-footer">
        <el-button v-db-click @click="modals = false">取消</el-button>
        <el-button type="primary" v-db-click @click="submit">确定</el-button>
      </span></template
    >
  </el-dialog>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { integralOrderPutRemarkData } from '../../api';

defineOptions({ name: 'integralOrderRemark' });

const props = defineProps({
  orderId: [Number, String],
});

const emit = defineEmits(['submitFail']);

const modals = ref(false);
const formValidate = ref({
  remark: '',
});

function submit() {
  let data = {
    id: props.orderId,
    remark: formValidate.value.remark,
  };
  integralOrderPutRemarkData(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
      modals.value = false;
      emit('submitFail');
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

defineExpose({ modals, formValidate });
</script>
