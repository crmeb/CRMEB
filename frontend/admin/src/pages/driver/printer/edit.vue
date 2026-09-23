<template>
  <el-form
    ref="formRef"
    :model="editForm"
    :rules="editRules"
    :disabled="editLoading || !loaded || submitLoading || !hasPermission(saveAuth)"
    :label-width="labelWidth"
    :label-position="labelPosition"
    v-loading="editLoading"
    @submit.prevent
  >
    <el-divider content-position="left">基本信息</el-divider>

    <el-form-item label="打印机名称" prop="print_name">
      <el-input v-model="editForm.print_name" placeholder="请输入打印机名称" />
    </el-form-item>

    <!-- 新增时选择驱动 -->
    <el-form-item label="选择驱动" prop="driver" v-if="isAdd">
      <el-select v-model="editForm.driver" placeholder="请选择打印机驱动" style="width: 100%">
        <el-option
          v-for="item in driverOptions"
          :key="item.name"
          :label="item.description || item.name"
          :value="item.name"
        >
          <span>{{ item.description || item.name }}</span>
          <span class="driver-name-tag">[{{ item.name }}]</span>
        </el-option>
      </el-select>
    </el-form-item>

    <!-- 编辑时显示驱动名（只读） -->
    <el-form-item label="驱动" v-else>
      <el-tag>{{ editForm.driver }}</el-tag>
    </el-form-item>

    <el-form-item :label="deviceIdMeta.label" prop="printer_id">
      <el-input v-model="editForm.printer_id" :placeholder="deviceIdMeta.tip" />
      <div class="tips-info">服务商账号和密钥请在“扩展管理 > 打印机驱动管理”中配置</div>
    </el-form-item>

    <el-divider content-position="left">打印设置</el-divider>

    <el-form-item label="打印时机" prop="print_type">
      <el-radio-group v-model="editForm.print_type">
        <el-radio :label="1" :value="1">支付后打印</el-radio>
        <el-radio :label="2" :value="2">下单后打印</el-radio>
      </el-radio-group>
    </el-form-item>

    <el-form-item label="打印联数" prop="times">
      <el-input-number v-model="editForm.times" :min="1" :max="99" :precision="0" />
    </el-form-item>

    <el-divider content-position="left">其他设置</el-divider>

    <el-form-item label="状态">
      <el-switch
        class="defineSwitch"
        :active-value="1"
        :inactive-value="0"
        v-model="editForm.status"
        active-text="开启"
        inactive-text="关闭"
      />
    </el-form-item>

    <el-form-item label="排序" prop="sort">
      <el-input-number v-model="editForm.sort" :min="0" :max="9999" :precision="0" />
    </el-form-item>

    <div class="form-actions">
      <el-button
        v-if="hasPermission(saveAuth)"
        v-auth="[saveAuth]"
        type="primary"
        v-db-click
        :disabled="editLoading || !loaded"
        @click="handleSubmit"
        :loading="submitLoading"
      >
        保存
      </el-button>
      <el-button v-db-click :disabled="submitLoading" @click="$emit('close')"> 取消 </el-button>
    </div>
  </el-form>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { printerChannelDetailApi, printerChannelSaveApi, printerChannelUpdateApi } from '@/api/driver/printer';
import { usePrinterAuth } from './usePrinterAuth';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'PrinterEdit' });

const props = defineProps({
  driverId: {
    type: [Number, String],
    default: null,
  },
  isAdd: {
    type: Boolean,
    default: false,
  },
  driverOptions: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close', 'success', 'submitting']);
const { hasPermission } = usePrinterAuth();
const mediaStore = useMediaStore();
const labelWidth = computed(() => (mediaStore.isMobile ? undefined : 120));
const labelPosition = computed(() => (mediaStore.isMobile ? 'top' : 'right'));
const saveAuth = computed(() => (props.isAdd ? 'admin-printer-create' : 'admin-printer-edit'));

const formRef = ref(null);
const editLoading = ref(false);
const submitLoading = ref(false);
const loaded = ref(props.isAdd);
const editForm = ref({
  print_name: '',
  driver: '',
  printer_id: '',
  status: 1,
  sort: 0,
  times: 1,
  print_type: 1,
});
const editRules = ref({
  print_name: [{ required: true, whitespace: true, message: '请输入打印机名称', trigger: 'blur' }],
  driver: [{ required: true, message: '请选择打印机驱动', trigger: 'change' }],
  printer_id: [{ required: true, whitespace: true, message: '请输入打印机ID', trigger: 'blur' }],
  times: [{ type: 'integer', required: true, min: 1, max: 99, message: '打印联数须为1到99的整数', trigger: 'change' }],
  print_type: [{ type: 'enum', required: true, enum: [1, 2], message: '请选择打印时机', trigger: 'change' }],
  sort: [{ type: 'integer', required: true, min: 0, max: 9999, message: '排序须为0到9999的整数', trigger: 'change' }],
});

const deviceIdMeta = computed(() => {
  const driver = props.driverOptions.find((item) => item.name === editForm.value.driver);
  return (driver && driver.device_id) || { label: '打印机ID', tip: '请输入打印机设备ID/SN/终端号' };
});

onMounted(() => {
  if (!props.isAdd) {
    getDetail();
  }
});

// 获取详情
function getDetail() {
  if (!props.driverId || !hasPermission('admin-printer-detail')) return;
  editLoading.value = true;
  printerChannelDetailApi(props.driverId)
    .then((res) => {
      const data = res.data;
      editForm.value = {
        id: data.id,
        print_name: data.print_name,
        driver: data.driver,
        printer_id: data.printer_id || '',
        icon: data.icon || '',
        sort: Number(data.sort),
        status: Number(data.status),
        times: Number(data.times),
        print_type: Number(data.print_type),
      };
      loaded.value = true;
    })
    .catch((err) => {
      loaded.value = false;
      ElMessage.error(err.msg || '打印机配置加载失败');
    })
    .finally(() => {
      editLoading.value = false;
    });
}

// 提交
async function handleSubmit() {
  if (submitLoading.value || editLoading.value || !loaded.value || !formRef.value || !hasPermission(saveAuth.value)) {
    return;
  }
  submitLoading.value = true;
  emit('submitting', true);
  try {
    const valid = await formRef.value.validate().catch(() => false);
    if (!valid) return;
    const data = {
      ...editForm.value,
      print_name: editForm.value.print_name.trim(),
      printer_id: editForm.value.printer_id.trim(),
    };
    const res = props.isAdd
      ? await printerChannelSaveApi(data)
      : await printerChannelUpdateApi(editForm.value.id, data);
    ElMessage.success(res.msg || '保存成功');
    emit('success');
  } catch (err) {
    ElMessage.error(err.msg || '打印机配置保存失败');
  } finally {
    submitLoading.value = false;
    emit('submitting', false);
  }
}
</script>

<style lang="scss" scoped>
.driver-name-tag {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
  margin-left: 5px;
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
