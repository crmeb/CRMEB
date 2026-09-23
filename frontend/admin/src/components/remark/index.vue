<template>
  <el-dialog v-model="modals" title="备注" class="order_box" width="470px" :show-close="true">
    <el-form ref="formValidate" :model="formValidate" :rules="ruleValidate" label-width="85px" @submit.prevent>
      <el-form-item label="备注：" prop="remark">
        <el-input
          v-model="formValidate.remark"
          :maxlength="200"
          show-word-limit
          type="textarea"
          placeholder="请输入备注信息"
          style="width: 100%"
        />
      </el-form-item>
    </el-form>
    <template #footer
      ><div>
        <el-button type="primary" v-db-click @click="putRemark('formValidate')">提交</el-button>
        <el-button v-db-click @click="cancel('formValidate')">取消</el-button>
      </div></template
    >
  </el-dialog>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';

defineOptions({ name: 'orderMark' });

const emit = defineEmits(['submitFail']);

const props = defineProps({
  remark: {
    default: '',
    type: String,
  },
});

const formValidate = reactive({
  remark: '',
});
const modals = ref(false);
const formValidateRef = ref(null);
const ruleValidate = {
  remark: [
    { required: true, message: '请输入备注信息', trigger: 'blur' },
    // { type: 'string', min: 20, message: 'Introduce no less than 20 words', trigger: 'blur' }
  ],
};

function cancel(name) {
  modals.value = false;
  formValidateRef.value.resetFields();
}
function putRemark(name) {
  formValidateRef.value.validate((valid) => {
    if (valid) {
      emit('submitFail', formValidate.remark);
    } else {
      ElMessage.warning('请填写备注信息');
    }
  });
}

// 页面通过 $refs.remark.modals / $refs.remark.formValidate 驱动
defineExpose({ modals, formValidate });
</script>

<style scoped></style>
