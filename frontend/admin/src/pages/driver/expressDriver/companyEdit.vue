<template>
  <el-drawer
    :title="isEdit ? '编辑物流公司' : '新增物流公司'"
    v-model="visible"
    direction="rtl"
    size="80%"
    @close="handleClose"
    @opened="handleOpen"
  >
    <div style="padding: 0 24px 100px">
      <el-form ref="formRef" :model="editForm" :rules="editRules" label-width="140px" v-loading="editLoading">
        <el-divider content-position="left">基本信息</el-divider>

        <el-form-item label="绑定驱动" prop="driver_key">
          <el-select v-model="editForm.driver_key" placeholder="请选择驱动" style="width: 100%">
            <el-option v-for="opt in driverOptions" :key="opt.value" :label="opt.label" :value="opt.value" />
          </el-select>
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="快递公司名称" prop="name">
              <el-input v-model="editForm.name" placeholder="如：圆通速递" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="快递公司编码" prop="code">
              <el-input v-model="editForm.code" placeholder="如：yuantong" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="状态">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="editForm.status"
                active-text="启用"
                inactive-text="关闭"
                class="defineSwitch"
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="展示">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="editForm.is_show"
                active-text="展示"
                inactive-text="隐藏"
                class="defineSwitch"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="排序">
          <el-input-number v-model="editForm.sort" :min="0" :max="9999" />
        </el-form-item>

        <el-divider content-position="left">月结账号</el-divider>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="启用月结账号">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="editForm.partner_id"
                active-text="是"
                inactive-text="否"
                class="defineSwitch"
              />
            </el-form-item>
            <el-form-item v-if="editForm.partner_id" label="月结账号">
              <el-input v-model="editForm.account" placeholder="请输入月结账号" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="启用月结密钥">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="editForm.partner_key"
                active-text="是"
                inactive-text="否"
                class="defineSwitch"
              />
            </el-form-item>
            <el-form-item v-if="editForm.partner_key" label="月结密钥">
              <el-input v-model="editForm.key" placeholder="请输入月结密钥" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-divider content-position="left">网点 / 揽件 / 客户</el-divider>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="启用网点">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="editForm.net"
                active-text="是"
                inactive-text="否"
                class="defineSwitch"
              />
            </el-form-item>
            <el-form-item v-if="editForm.net" label="网点名称">
              <el-input v-model="editForm.net_name" placeholder="请输入网点名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="启用揽件人">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="editForm.check_man"
                active-text="是"
                inactive-text="否"
                class="defineSwitch"
              />
            </el-form-item>
            <el-form-item v-if="editForm.check_man" label="揽件人">
              <el-input v-model="editForm.courier_name" placeholder="请输入揽件人" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="启用客户名">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="editForm.partner_name"
                active-text="是"
                inactive-text="否"
                class="defineSwitch"
              />
            </el-form-item>
            <el-form-item v-if="editForm.partner_name" label="客户名">
              <el-input v-model="editForm.customer_name" placeholder="请输入客户名" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="启用编码">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="editForm.is_code"
                active-text="是"
                inactive-text="否"
                class="defineSwitch"
              />
            </el-form-item>
            <el-form-item v-if="editForm.is_code" label="编码名称">
              <el-input v-model="editForm.code_name" placeholder="请输入编码名称" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-divider content-position="left">打印机配置</el-divider>

        <el-form-item label="开启打印">
          <el-switch
            :active-value="1"
            :inactive-value="0"
            v-model="editForm.print_open"
            active-text="开启"
            inactive-text="关闭"
            class="defineSwitch"
          />
        </el-form-item>

        <el-row v-if="editForm.print_open" :gutter="20">
          <el-col :span="12">
            <el-form-item label="云打印机编号">
              <el-input v-model="editForm.siid" placeholder="请购买快递100二代云打印机" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="快递模板ID">
              <el-input v-model="editForm.temp_id" placeholder="登录快递100后台获取" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </div>

    <div class="drawer-footer">
      <el-button v-db-click @click="visible = false">取消</el-button>
      <el-button type="primary" v-db-click @click="handleSubmit" :loading="submitLoading"> 保存 </el-button>
    </div>
  </el-drawer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { companyReadApi, companySaveApi, companyUpdateApi, companyDriverOptionsApi } from '@/api/driver/expressCompany';

defineOptions({ name: 'companyEdit' });

const props = defineProps({
  companyId: {
    type: [Number, String],
    default: null,
  },
});

const emit = defineEmits(['close', 'success']);

const formRef = ref(null);
const visible = ref(false);
const editLoading = ref(false);
const submitLoading = ref(false);
const driverOptions = ref([]);
const editForm = ref({
  driver_key: '',
  name: '',
  code: '',
  account: '',
  key: '',
  net_name: '',
  courier_name: '',
  customer_name: '',
  code_name: '',
  siid: '',
  temp_id: '',
  print_open: 0,
  partner_id: 0,
  partner_key: 0,
  net: 0,
  check_man: 0,
  partner_name: 0,
  is_code: 0,
  sort: 0,
  is_show: 1,
  status: 1,
});
const editRules = ref({
  driver_key: [{ required: true, message: '请选择绑定驱动', trigger: 'change' }],
  name: [{ required: true, message: '请输入快递公司名称', trigger: 'blur' }],
  code: [{ required: true, message: '请输入快递公司编码', trigger: 'blur' }],
});

const isEdit = computed(() => !!props.companyId);

onMounted(() => {
  visible.value = true;
});

function handleOpen() {
  getDriverOptions();
  if (isEdit.value) {
    getDetail();
  }
}

function handleClose() {
  emit('close');
}

function getDriverOptions() {
  companyDriverOptionsApi()
    .then((res) => {
      driverOptions.value = res.data || [];
    })
    .catch(() => {});
}

function getDetail() {
  editLoading.value = true;
  companyReadApi(props.companyId)
    .then((res) => {
      const data = res.data;
      Object.keys(editForm.value).forEach((key) => {
        if (data[key] !== undefined) {
          editForm.value[key] = data[key];
        }
      });
    })
    .catch(() => {})
    .finally(() => {
      editLoading.value = false;
    });
}

function handleSubmit() {
  formRef.value.validate((valid) => {
    if (!valid) return;
    submitLoading.value = true;

    const apiCall = isEdit.value ? companyUpdateApi(props.companyId, editForm.value) : companySaveApi(editForm.value);

    apiCall
      .then((res) => {
        ElMessage.success(res.msg || '保存成功');
        emit('success');
        visible.value = false;
      })
      .catch(() => {})
      .finally(() => {
        submitLoading.value = false;
      });
  });
}
</script>

<style scoped>
.drawer-footer {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 16px 24px;
  background: #fff;
  border-top: 1px solid #e8e8e8;
  text-align: right;
  z-index: 10;
}
</style>
