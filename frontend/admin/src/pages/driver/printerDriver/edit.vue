<template>
  <el-form
    ref="formRef"
    v-loading="loading"
    :disabled="loading || !loaded || submitLoading || !hasPermission('admin-printer-driver-edit')"
    :model="form"
    :label-width="labelWidth"
    :label-position="labelPosition"
    class="form-wrap"
    @submit.prevent
  >
    <el-divider content-position="left">服务商配置</el-divider>
    <el-form-item
      v-for="field in fields"
      :key="field.field"
      :label="field.label"
      :prop="field.field"
      :rules="configRules(field)"
    >
      <el-switch
        v-if="field.type === 'switch'"
        v-model="form[field.field]"
        active-value="1"
        inactive-value="0"
        :disabled="field.readonly"
      />
      <el-input
        v-else
        v-model="form[field.field]"
        :type="field.type === 'password' ? 'password' : 'text'"
        :show-password="field.type === 'password'"
        :disabled="field.readonly"
        :placeholder="field.tip || '请输入' + field.label"
      />
      <div v-if="field.tip" class="tips-info">{{ field.tip }}</div>
    </el-form-item>
    <el-divider content-position="left">其他设置</el-divider>
    <el-form-item label="启用驱动">
      <el-switch v-model="form.status" :active-value="1" :inactive-value="0" />
    </el-form-item>
    <div class="form-actions">
      <el-button
        v-if="hasPermission('admin-printer-driver-edit')"
        v-auth="['admin-printer-driver-edit']"
        type="primary"
        v-db-click
        :loading="submitLoading"
        :disabled="loading || !loaded"
        @click="submit"
      >
        保存
      </el-button>
      <el-button v-db-click :disabled="submitLoading" @click="$emit('close')">取消</el-button>
    </div>
  </el-form>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { printerDriverDetailApi, printerDriverSaveConfigApi } from '@/api/driver/printerDriver';
import { usePrinterAuth } from '../printer/usePrinterAuth';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'PrinterDriverEdit' });

const props = defineProps({
  driverId: { type: [Number, String], required: true },
});

const emit = defineEmits(['close', 'success', 'submitting']);
const { hasPermission } = usePrinterAuth();
const mediaStore = useMediaStore();
const labelWidth = computed(() => (mediaStore.isMobile ? undefined : 120));
const labelPosition = computed(() => (mediaStore.isMobile ? 'top' : 'right'));

const loading = ref(false);
const form = ref({ status: 0 });
const fields = ref([]);
const formRef = ref(null);
const loaded = ref(false);
const submitLoading = ref(false);

getDetail();

/**
 * @description 加载驱动配置，失败时禁止提交空配置
 */
async function getDetail() {
  if (!hasPermission('admin-printer-driver-detail')) return;
  loading.value = true;
  try {
    const res = await printerDriverDetailApi(props.driverId);
    form.value = { status: Number(res.data.status || 0) };
    fields.value = res.data.config_fields || [];
    fields.value.forEach((field) => {
      form.value[field.field] = field.value !== undefined ? field.value : field.default ?? '';
    });
    loaded.value = true;
  } catch (err) {
    loaded.value = false;
    ElMessage.error(err.msg || '打印机驱动配置加载失败');
  } finally {
    loading.value = false;
  }
}

/**
 * @description 按驱动声明校验配置项
 */
function configRules(field) {
  if (field.type === 'switch' || field.required === false) return [];
  return [{ required: true, whitespace: true, message: '请输入' + field.label, trigger: 'blur' }];
}

async function submit() {
  if (
    submitLoading.value ||
    loading.value ||
    !loaded.value ||
    !formRef.value ||
    !hasPermission('admin-printer-driver-edit')
  ) {
    return;
  }
  submitLoading.value = true;
  emit('submitting', true);
  try {
    const valid = await formRef.value.validate().catch(() => false);
    if (!valid) return;
    const data = { status: form.value.status };
    fields.value.forEach((field) => {
      data[field.field] = form.value[field.field];
    });
    await printerDriverSaveConfigApi(props.driverId, data);
    emit('success');
  } catch (err) {
    ElMessage.error(err.msg || '打印机驱动配置保存失败');
  } finally {
    submitLoading.value = false;
    emit('submitting', false);
  }
}
</script>

<style lang="scss" scoped>
.form-wrap {
  padding: 0 20px 20px;
}
.form-actions {
  margin-top: 24px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
.form-actions .el-button + .el-button {
  margin-left: 0;
}
</style>
