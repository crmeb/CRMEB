<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt setting-card" :body-style="{ padding: '0 20px 20px' }">
      <el-tabs v-model="activeTab">
        <el-tab-pane label="基础设置" name="config">
          <el-form :model="configForm" label-width="140px" v-loading="configLoading" class="tab-content">
            <el-divider content-position="left">打印设置</el-divider>
            <el-form-item label="电子面单打印：">
              <el-switch
                v-model="configForm.open"
                :active-value="1"
                :inactive-value="0"
                active-text="开启"
                inactive-text="关闭"
                class="defineSwitch"
              />
            </el-form-item>
            <el-form-item label="云打印机编号：">
              <el-input v-model="configForm.siid" placeholder="请输入云打印机编号" class="field-md" />
              <div class="form-tip">不填写时返回面单图片，由管理员手动打印。</div>
            </el-form-item>

            <el-divider content-position="left">默认快递公司</el-divider>
            <el-form-item label="快递公司编码：">
              <el-input v-model="configForm.com" placeholder="如：ZTO" class="field-md" />
            </el-form-item>
            <el-form-item label="快递公司ID：">
              <el-input v-model="configForm.id" placeholder="请输入快递公司ID" class="field-md" />
            </el-form-item>
            <el-form-item label="默认模板ID：">
              <el-input v-model="configForm.temp_id" placeholder="请输入面单模板ID" class="field-md" />
            </el-form-item>

            <el-divider content-position="left">默认寄件人</el-divider>
            <el-form-item label="发货人姓名：">
              <el-input v-model="configForm.to_name" placeholder="请输入发货人姓名" class="field-md" />
            </el-form-item>
            <el-form-item label="发货人电话：">
              <el-input v-model="configForm.to_tel" placeholder="请输入发货人电话" class="field-md" />
            </el-form-item>
            <el-form-item label="发货人地址：">
              <el-input v-model="configForm.to_address" placeholder="请输入发货人详细地址" class="field-lg" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" v-db-click :loading="saving" @click="handleSaveConfig">保存配置</el-button>
            </el-form-item>
          </el-form>
        </el-tab-pane>

        <el-tab-pane label="物流公司" name="company">
          <div class="tab-content"><ExpressCompany ref="companyComponent" /></div>
        </el-tab-pane>

        <el-tab-pane label="打印记录" name="log">
          <div class="tab-content">
            <el-row class="mb20" :gutter="16">
              <el-col :span="6"><el-input v-model="logSearch.order_sn" placeholder="订单编号" clearable /></el-col>
              <el-col :span="6"><el-input v-model="logSearch.kuaidinum" placeholder="快递单号" clearable /></el-col>
              <el-col :span="6">
                <el-select v-model="logSearch.status" placeholder="打印状态" clearable>
                  <el-option label="成功" :value="1" />
                  <el-option label="失败" :value="0" />
                </el-select>
              </el-col>
              <el-col :span="6"><el-button type="primary" v-db-click @click="getLogList">查询</el-button></el-col>
            </el-row>
            <el-table :data="logList" v-loading="logLoading" row-key="id">
              <el-table-column label="订单编号" min-width="160" prop="order_sn" />
              <el-table-column label="快递公司" min-width="100" prop="com_name" />
              <el-table-column label="快递单号" min-width="140" prop="kuaidinum" />
              <el-table-column label="打印方式" width="100">
                <template #default="scope">
                  <el-tag v-if="scope.row.print_type === 'CLOUD'">云打印</el-tag>
                  <el-tag v-else type="info">图片</el-tag>
                </template>
              </el-table-column>
              <el-table-column label="状态" width="80">
                <template #default="scope">
                  <el-tag :type="scope.row.status === 1 ? 'success' : 'danger'">
                    {{ scope.row.status === 1 ? '成功' : '失败' }}
                  </el-tag>
                </template>
              </el-table-column>
              <el-table-column label="打印时间" min-width="160" prop="add_time" />
              <el-table-column label="操作" fixed="right" width="100" align="center">
                <template #default="scope">
                  <el-button type="text" v-db-click @click="viewLogDetail(scope.row)">详情</el-button>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <el-dialog title="打印记录详情" v-model="showLogDetail" width="600px">
      <el-descriptions v-if="logDetail" :column="2" border>
        <el-descriptions-item label="订单编号">{{ logDetail.order_sn }}</el-descriptions-item>
        <el-descriptions-item label="快递公司">{{ logDetail.com_name }}</el-descriptions-item>
        <el-descriptions-item label="快递单号">{{ logDetail.kuaidinum }}</el-descriptions-item>
        <el-descriptions-item label="模板ID">{{ logDetail.temp_id }}</el-descriptions-item>
        <el-descriptions-item label="打印机编号">{{ logDetail.siid || '-' }}</el-descriptions-item>
        <el-descriptions-item label="收件人">{{ logDetail.to_name }}</el-descriptions-item>
        <el-descriptions-item label="收件电话">{{ logDetail.to_tel }}</el-descriptions-item>
        <el-descriptions-item label="收件地址" :span="2">{{ logDetail.to_addr }}</el-descriptions-item>
        <el-descriptions-item label="寄件人">{{ logDetail.from_name }}</el-descriptions-item>
        <el-descriptions-item label="寄件电话">{{ logDetail.from_tel }}</el-descriptions-item>
        <el-descriptions-item label="寄件地址" :span="2">{{ logDetail.from_addr }}</el-descriptions-item>
        <el-descriptions-item v-if="logDetail.error_msg" label="错误信息" :span="2">{{
          logDetail.error_msg
        }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import ExpressCompany from '@/pages/driver/expressDriver/company.vue';
import {
  getExpressConfigApi,
  saveExpressConfigApi,
  expressDumpLogListApi,
  expressDumpLogDetailApi,
} from '@/api/driver/expressTemplate';

defineOptions({ name: 'ExpressSetting' });

const defaultConfig = () => ({
  open: 0,
  siid: '',
  id: '',
  temp_id: '',
  com: '',
  to_name: '',
  to_tel: '',
  to_address: '',
});

const companyComponent = ref(null);

const activeTab = ref('config');
const configForm = ref(defaultConfig());
const configLoading = ref(false);
const saving = ref(false);
const logList = ref([]);
const logLoading = ref(false);
const logLoaded = ref(false);
const logSearch = ref({ order_sn: '', kuaidinum: '', status: '' });
const showLogDetail = ref(false);
const logDetail = ref(null);

watch(activeTab, (value) => {
  if (value === 'company' && companyComponent.value) {
    companyComponent.value.getList();
  }
  if (value === 'log' && !logLoaded.value) {
    getLogList();
  }
});

function getConfig() {
  configLoading.value = true;
  getExpressConfigApi()
    .then((res) => {
      configForm.value = { ...defaultConfig(), ...(res.data || {}) };
    })
    .finally(() => {
      configLoading.value = false;
    });
}
function handleSaveConfig() {
  saving.value = true;
  saveExpressConfigApi(configForm.value)
    .then((res) => ElMessage.success(res.msg || '保存成功'))
    .catch((err) => ElMessage.error(err.msg || '保存失败'))
    .finally(() => {
      saving.value = false;
    });
}
function getLogList() {
  logLoading.value = true;
  expressDumpLogListApi(logSearch.value)
    .then((res) => {
      logList.value = res.data.list || [];
      logLoaded.value = true;
    })
    .finally(() => {
      logLoading.value = false;
    });
}
function viewLogDetail(row) {
  expressDumpLogDetailApi(row.id).then((res) => {
    logDetail.value = res.data;
    showLogDetail.value = true;
  });
}

getConfig();
</script>

<style scoped lang="scss">
:deep(.el-tabs__header) {
  margin: 0;
}
.setting-card {
  min-height: calc(100vh - 200px);
}
.tab-content {
  padding-top: 20px;
}
.field-md {
  width: 300px;
}
.field-lg {
  width: 500px;
}
.form-tip {
  color: var(--prev-color-text-secondary);
  font-size: 12px;
  line-height: 1.5;
  margin-top: 5px;
}
.mb20 {
  margin-bottom: 20px;
}
</style>
