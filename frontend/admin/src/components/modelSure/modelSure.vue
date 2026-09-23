<template>
  <el-dialog v-model="modals" class="paymentFooter" width="470px" :destroy-on-close="true">
    <template #header
      ><p style="color: #f60">
        <i class="el-icon-warning"></i>
        <span>{{ `${delfromData.title}` }}</span>
      </p></template
    >
    <div>
      <p>{{ `您确定要${delfromData.title}吗?` }}</p>
      <p v-if="delfromData.info !== undefined">{{ `${delfromData.info}` }}</p>
    </div>
    <template #footer
      ><div class="acea-row row-right">
        <el-button type="primary" v-db-click @click="cancel">取消</el-button>
        <el-button type="warning" v-db-click @click="ok">确定</el-button>
      </div></template
    >
  </el-dialog>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { tableDelApi } from '@/api/common';

defineOptions({ name: 'modelSure' });

const emit = defineEmits(['submitModel']);

const props = defineProps({
  title: String,
  delfromData: {
    type: Object,
    default: null,
  },
});

const modals = ref(false);
const modal_loading = ref(false);

function ok() {
  modal_loading.value = true;
  setTimeout(() => {
    tableDelApi(props.delfromData)
      .then(async (res) => {
        ElMessage.success(res.msg);
        modal_loading.value = false;
        modals.value = false;
        emit('submitModel');
      })
      .catch((res) => {
        modal_loading.value = false;
        ElMessage.error(res.msg);
        modals.value = false;
      });
  }, 2000);
}
function cancel() {
  modals.value = false;
}

defineExpose({ modals });
</script>

<style lang="scss" scoped>
.acea-row :deep(.ivu-btn-primary) {
  background-color: rgb(170, 170, 170);
  border-color: rgb(170, 170, 170);
}
</style>
