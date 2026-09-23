<template>
  <div class="upload-driver-storage">
    <el-card :bordered="false" shadow="never">
      <el-alert closable type="info" style="margin-bottom: 16px">
        <template #title>
          <p>第一步：添加【存储空间】（空间名称不能重复）</p>
          <p>第二步：开启【使用状态】</p>
          <p>第三步（可选）：选择存储空间列表上的【修改空间域名】操作，填写已备案并配置好CNAME解析的自定义域名</p>
          <p>第四步（可选）：选择存储空间列表上的【CNAME配置】，打开后复制记录值到对应的域名解析平台完成解析</p>
        </template>
      </el-alert>
      <el-row style="margin-bottom: 16px">
        <el-col :span="24" class="storage-toolbar">
          <el-button
            v-auth="['driver-storage-save']"
            type="primary"
            v-db-click
            :disabled="!driverInfo"
            @click="showAddStorageModal = true"
          >
            添加存储空间
          </el-button>
          <el-button
            v-auth="['driver-storage-sync']"
            type="success"
            v-db-click
            :loading="syncSaving"
            :disabled="!driverInfo"
            @click="synchro"
          >
            同步存储空间
          </el-button>
          <el-button
            v-auth="['driver-upload-save-config']"
            v-db-click
            :disabled="!driverInfo"
            @click="showConfigModal = true"
          >
            修改配置信息
          </el-button>
        </el-col>
      </el-row>

      <el-table
        :data="storageList"
        ref="table"
        v-loading="loading"
        highlight-current-row
        row-key="id"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="存储空间名称" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>

        <el-table-column label="区域" min-width="90">
          <template #default="scope">
            <span>{{ scope.row._region }}</span>
          </template>
        </el-table-column>

        <el-table-column label="空间域名" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.domain }}</span>
          </template>
        </el-table-column>

        <el-table-column label="使用状态" min-width="80">
          <template #default="scope">
            <el-switch
              v-auth="['driver-storage-status']"
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :disabled="scope.row._statusSaving"
              :value="scope.row.status"
              @change="changeSwitch(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            />
          </template>
        </el-table-column>

        <el-table-column label="创建时间" min-width="100">
          <template #default="scope">
            <span>{{ scope.row._add_time }}</span>
          </template>
        </el-table-column>

        <el-table-column label="更新时间" min-width="100">
          <template #default="scope">
            <span>{{ scope.row._update_time }}</span>
          </template>
        </el-table-column>

        <el-table-column label="操作" fixed="right" width="220">
          <template #default="scope">
            <template v-if="canConfigureCname(scope.row)">
              <span class="btn" v-db-click @click="config(scope.row)">CNAME配置</span>
              <el-divider direction="vertical"></el-divider>
            </template>
            <span v-auth="['driver-storage-domain']" class="btn" v-db-click @click="edit(scope.row)">修改空间域名</span>
            <el-divider direction="vertical"></el-divider>
            <span
              v-auth="['driver-storage-delete']"
              class="btn"
              v-db-click
              @click="del(scope.row, '删除该数据', scope.$index)"
            >
              删除
            </span>
          </template>
        </el-table-column>
      </el-table>

      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="list.page"
          v-model:limit="list.limit"
          @pagination="getlist"
        />
      </div>
    </el-card>

    <el-dialog
      v-model="configuModal"
      class="storage-dialog"
      title="CNAME配置"
      width="min(570px, calc(100% - 32px))"
      append-to-body
    >
      <el-alert closable type="info" style="margin-bottom: 16px">
        <template #title>
          <p>请将以下记录值复制后，到您的域名DNS解析管理平台添加CNAME解析记录。</p>
        </template>
      </el-alert>
      <div>
        <div class="confignv"><span class="configtit">主机记录：</span>{{ configData.hostname }}</div>
        <div class="confignv"><span class="configtit">记录类型：</span>CNAME</div>
        <div class="confignv">
          <span class="configtit">记录值：</span>{{ configData.cname }}
          <span class="copy copy-data" v-db-click @click="insertCopy(configData.cname)">复制</span>
        </div>
      </div>
    </el-dialog>

    <el-dialog
      v-model="showConfigModal"
      class="storage-dialog"
      title="配置信息"
      width="min(570px, calc(100% - 32px))"
      :close-on-click-modal="false"
      append-to-body
    >
      <el-form
        class="storage-form"
        ref="configFormRef"
        :model="configFormData"
        :rules="configRules"
        label-width="140px"
      >
        <template v-for="(field, index) in configFields" :key="index">
          <el-form-item :label="field.label + ':'" :prop="field.field">
            <el-input
              v-if="field.type === 'text'"
              v-model="configFormData[field.field]"
              :placeholder="'请输入' + field.label"
            ></el-input>
            <el-input
              v-else-if="field.type === 'password'"
              v-model="configFormData[field.field]"
              :placeholder="'请输入' + field.label"
              type="password"
              show-password
            ></el-input>
            <el-select
              v-else-if="field.type === 'select'"
              v-model="configFormData[field.field]"
              :placeholder="'请选择' + field.label"
            >
              <el-option
                v-for="opt in field.options"
                :key="opt.value"
                :label="opt.label"
                :value="opt.value"
              ></el-option>
            </el-select>
            <div v-if="field.tip" class="form-tip">{{ field.tip }}</div>
          </el-form-item>
        </template>
      </el-form>
      <template #footer
        ><div class="dialog-footer">
          <el-button v-db-click @click="showConfigModal = false">取消</el-button>
          <el-button type="primary" v-db-click :loading="configSaving" @click="submitConfigForm">确定</el-button>
        </div></template
      >
    </el-dialog>

    <el-dialog
      v-model="showAddStorageModal"
      class="storage-dialog"
      title="添加云空间"
      width="min(570px, calc(100% - 32px))"
      :close-on-click-modal="false"
      append-to-body
    >
      <el-form
        class="storage-form"
        ref="addStorageFormRef"
        :model="addStorageFormData"
        :rules="addStorageRules"
        label-width="140px"
      >
        <template v-for="(field, index) in bucketFields" :key="index">
          <el-form-item :label="field.label + ':'" :prop="field.field">
            <el-input
              v-if="field.type === 'text'"
              v-model="addStorageFormData[field.field]"
              :placeholder="'请输入' + field.label"
            ></el-input>
            <el-input
              v-else-if="field.type === 'password'"
              v-model="addStorageFormData[field.field]"
              :placeholder="'请输入' + field.label"
              type="password"
              show-password
            ></el-input>
            <el-select
              v-else-if="field.type === 'select'"
              v-model="addStorageFormData[field.field]"
              :placeholder="'请选择' + field.label"
            >
              <el-option
                v-for="opt in field.options"
                :key="opt.value"
                :label="opt.label"
                :value="opt.value"
              ></el-option>
            </el-select>
            <el-radio-group v-else-if="field.type === 'radio'" v-model="addStorageFormData[field.field]">
              <el-radio v-for="opt in field.options" :key="opt.value" :label="opt.value" :value="opt.value">{{
                opt.label
              }}</el-radio>
            </el-radio-group>
          </el-form-item>
        </template>
      </el-form>
      <template #footer
        ><div class="dialog-footer">
          <el-button v-db-click @click="showAddStorageModal = false">取消</el-button>
          <el-button type="primary" v-db-click :loading="addSaving" @click="submitAddStorageForm">确定</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { getCurrentInstance, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import { storageListApi, storageSynchApi, storageStatusApi, editStorageApi, storageSaveApi } from '@/api/setting';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { uploadDriverDetailApi, saveDriverConfigApi } from '@/api/driver/uploadDriver';

defineOptions({ name: 'UploadDriverStorage' });

const props = defineProps({
  driverId: {
    type: [Number, String],
    default: null,
  },
});

const route = useRoute();
const { proxy } = getCurrentInstance();

const configFormRef = ref(null);
const addStorageFormRef = ref(null);
const storageList = ref([]);
const loading = ref(false);
const total = ref(0);
const list = ref({
  page: 1,
  limit: 15,
  driver_key: '',
});
const driverInfo = ref(null);
const configuModal = ref(false);
const configData = ref('');
const showConfigModal = ref(false);
const showAddStorageModal = ref(false);
const configFields = ref([]);
const bucketFields = ref([]);
const configFormData = ref({});
const addStorageFormData = ref({});
const configRules = ref({});
const addStorageRules = ref({});
const configSaving = ref(false);
const addSaving = ref(false);
const syncSaving = ref(false);
let detailRequest = 0;
let listRequest = 0;

watch(
  () => props.driverId || route.params.id,
  () => {
    getDriverDetail();
  },
  { immediate: true },
);

function getDriverDetail() {
  const id = props.driverId || (route && route.params.id);
  if (!id) return;
  const requestId = ++detailRequest;
  ++listRequest;
  driverInfo.value = null;
  storageList.value = [];
  total.value = 0;
  loading.value = true;
  return uploadDriverDetailApi(id)
    .then((res) => {
      if (requestId !== detailRequest) return;
      driverInfo.value = res.data;
      list.value.driver_key = res.data.driver_key;
      list.value.page = 1;
      configFields.value = res.data.config_fields || [];
      bucketFields.value = res.data.bucket_fields || [];
      initFormData(configFields.value, 'config');
      initFormData(bucketFields.value, 'add');
      return getlist();
    })
    .catch((err) => {
      if (requestId === detailRequest) ElMessage.error(err.msg || err.message || '驱动加载失败');
    })
    .finally(() => {
      if (requestId === detailRequest) loading.value = false;
    });
}
function initFormData(fields, type) {
  const formData = {};
  const rules = {};
  fields.forEach((field) => {
    formData[field.field] = field.value !== undefined ? field.value : '';
    if (field.required) {
      rules[field.field] = [
        {
          required: true,
          message: '请输入' + field.label,
          trigger: field.type === 'select' || field.type === 'radio' ? 'change' : 'blur',
        },
      ];
    }
  });
  if (type === 'config') {
    configFormData.value = formData;
    configRules.value = rules;
  } else {
    addStorageFormData.value = formData;
    addStorageRules.value = rules;
  }
}
function insertCopy(text) {
  proxy
    .$copyText(text)
    .then(() => {
      ElMessage.success('复制成功');
    })
    .catch(() => {
      ElMessage.error('复制失败');
    });
}
function getlist() {
  if (!driverInfo.value) return;
  const requestId = ++listRequest;
  loading.value = true;
  return storageListApi({ ...list.value })
    .then((res) => {
      if (requestId !== listRequest) return;
      total.value = res.data.count;
      storageList.value = res.data.list || [];
    })
    .catch((err) => {
      if (requestId === listRequest) ElMessage.error(err.msg || err.message || '存储空间加载失败');
    })
    .finally(() => {
      if (requestId === listRequest) loading.value = false;
    });
}
function synchro() {
  if (syncSaving.value || !driverInfo.value) return;
  syncSaving.value = true;
  return storageSynchApi(list.value.driver_key)
    .then((res) => {
      ElMessage.success(res.msg);
      getlist();
    })
    .catch((err) => {
      ElMessage.error(err.msg || err.message || '同步失败');
    })
    .finally(() => {
      syncSaving.value = false;
    });
}
function changeSwitch(row) {
  const previous = row.status === 1 ? 0 : 1;
  row._statusSaving = true;
  ElMessageBox({
    title: '切换状态',
    message: '您确认要切换使用状态吗',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
    iconClass: 'el-icon-warning',
    confirmButtonClass: 'btn-custom-cancel',
  })
    .then(() => {
      return storageStatusApi(row.id, row.status).then((res) => {
        ElMessage.success(res.msg);
        getlist();
      });
    })
    .catch((err) => {
      row.status = previous;
      if (err !== 'cancel' && err !== 'close') ElMessage.error(err.msg || err.message || '切换失败');
    })
    .finally(() => {
      row._statusSaving = false;
    });
}
function config(row) {
  if (!canConfigureCname(row)) return;
  configuModal.value = true;
  configData.value = { ...row, hostname: storageHostname(row.domain) };
}
function storageHostname(value) {
  if (!value) return '';
  try {
    return new URL(value.startsWith('//') ? 'https:' + value : value.includes('://') ? value : 'https://' + value)
      .hostname;
  } catch {
    return '';
  }
}
function canConfigureCname(row) {
  const hostname = storageHostname(row.domain);
  return Boolean(hostname && row.cname && hostname !== storageHostname(row.cname));
}
function edit(row) {
  modalForm(editStorageApi(row.id)).then(() => {
    getlist();
  });
}
function del(row, tit, num) {
  const delfromData = {
    title: tit,
    num: num,
    url: `system/config/storage/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getlist();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function submitConfigForm() {
  if (configSaving.value || !driverInfo.value) return;
  configFormRef.value.validate((valid) => {
    if (valid && !configSaving.value) {
      const id = props.driverId || (route && route.params.id);
      const driverData = {
        ...configFormData.value,
      };
      configSaving.value = true;
      saveDriverConfigApi(id, driverData)
        .then((res) => {
          ElMessage.success(res.msg || '保存成功');
          showConfigModal.value = false;
          getDriverDetail();
        })
        .catch((err) => {
          ElMessage.error(err.msg || err.message || '保存失败');
        })
        .finally(() => {
          configSaving.value = false;
        });
    }
  });
}
function submitAddStorageForm() {
  if (addSaving.value || !driverInfo.value) return;
  addStorageFormRef.value.validate((valid) => {
    if (valid && !addSaving.value) {
      addSaving.value = true;
      storageSaveApi(list.value.driver_key, { ...addStorageFormData.value })
        .then((res) => {
          ElMessage.success(res.msg || '添加成功');
          showAddStorageModal.value = false;
          initFormData(bucketFields.value, 'add');
          getlist();
        })
        .catch((err) => {
          ElMessage.error(err.msg || err.message || '添加失败');
        })
        .finally(() => {
          addSaving.value = false;
        });
    }
  });
}
</script>

<style scoped>
:deep(.el-card__body) {
  padding: 0;
}
.btn {
  cursor: pointer;
  color: var(--prev-color-primary);
  font-size: 12px;
}
.confignv {
  margin: 10px 0px;
  overflow-wrap: anywhere;
}
.configtit {
  display: inline-block;
  width: 90px;
  text-align: right;
}
.copy {
  padding: 3px 5px;
  border: 1px solid var(--prev-border-color-base);
  border-radius: 4px;
  color: var(--prev-color-text-regular);
  cursor: pointer;
  margin-left: 5px;
}
.copy:hover {
  border-color: var(--prev-color-primary);
  color: var(--prev-color-primary);
}
.mt16 {
  margin-top: 16px;
}
.mb20 {
  margin-bottom: 20px;
}
.form-tip {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
  line-height: 1.5;
  margin-top: 5px;
}
.storage-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
}
.storage-toolbar .el-button {
  margin: 0;
}
.storage-toolbar .el-button:last-child {
  margin-left: auto;
}
.upload-driver-storage .page,
.upload-driver-storage :deep(.pagination-container) {
  max-width: 100%;
  min-width: 0;
}
@media (max-width: 576px) {
  .upload-driver-storage :deep(.pagination-container) {
    width: 100%;
  }
  .upload-driver-storage :deep(.el-pagination) {
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 8px;
  }
  .upload-driver-storage :deep(.el-pager) {
    flex-wrap: wrap;
    justify-content: flex-end;
    max-width: 100%;
  }
  .storage-toolbar .el-button:last-child {
    margin-left: 0;
  }
  .storage-form :deep(.el-form-item) {
    flex-direction: column;
    align-items: stretch;
    min-width: 0;
  }
  .storage-form :deep(.el-form-item__label) {
    justify-content: flex-start;
  }
  .storage-form :deep(.el-form-item__content) {
    width: 100%;
    min-width: 0;
  }
}
</style>
