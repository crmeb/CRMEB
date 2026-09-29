<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          inline
          @submit.prevent
        >
          <el-form-item label="创建时间：">
            <date-range-picker
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
              class="mr20"
            ></date-range-picker>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="mt16" :body-style="{ padding: '0 20px 20px' }">
      <div class="ivu-mt">
        <el-tabs v-model="tab" @tab-change="onClickTab">
          <el-tab-pane label="日账单" name="day" />
          <el-tab-pane label="周账单" name="week" />
          <el-tab-pane label="月账单" name="month" />
        </el-tabs>
      </div>
      <div class="table">
        <el-table
          :data="orderList"
          ref="table"
          v-loading="loading"
          highlight-current-row
          no-userFrom-text="暂无数据"
          no-filtered-userFrom-text="暂无筛选结果"
        >
          <el-table-column label="ID" width="80">
            <template #default="scope">
              <span>{{ scope.row.id }}</span>
            </template>
          </el-table-column>
          <el-table-column label="标题" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.title }}</span>
            </template>
          </el-table-column>
          <el-table-column label="日期" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.add_time }}</span>
            </template>
          </el-table-column>
          <el-table-column label="收入金额" min-width="130">
            <template #default="scope">
              <span style="color: #f5222d">￥{{ scope.row.income_price }}</span>
            </template>
          </el-table-column>
          <el-table-column label="支出金额" min-width="130">
            <template #default="scope">
              <span style="color: #00c050">￥{{ scope.row.exp_price }}</span>
            </template>
          </el-table-column>
          <el-table-column label="入账金额" min-width="130">
            <template #default="scope">
              <span>￥{{ scope.row.entry_price }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right" width="170">
            <template #default="scope">
              <a v-db-click @click="Info(scope.row)">账单详情</a>
              <el-divider direction="vertical"></el-divider>
              <a v-db-click @click="download(scope.row)">下载</a>
            </template>
          </el-table-column>
        </el-table>
        <div class="acea-row row-right page">
          <pagination
            v-if="total"
            :total="total"
            v-model:page="formValidate.page"
            v-model:limit="formValidate.limit"
            @pagination="getList"
          />
        </div>
      </div>
    </el-card>
    <el-dialog v-model="modals" title="账单详情" width="1000px">
      <commission-details v-if="modals" ref="commission" :ids="ids" :time="formValidate.time"></commission-details>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import exportExcel from '@/utils/newToExcel.js';
import commissionDetails from '../components/commissionDetails';
import { getRecord } from '@/api/statistic.js';
import { getFlowList } from '@/api/finance';

defineOptions({ name: 'bill' });

const { isMobile } = storeToRefs(useMediaStore());

const modals = ref(false);
const ids = ref('');
const total = ref(0);
const loading = ref(false);
const tab = ref('day');
const orderList = ref([
  {
    id: '1',
    order_id: '200',
    pay_price: '200',
    status: 1,
    phone: '13000000000',
    address: '100',
  },
]);
const formValidate = ref({
  store_id: '',
  time: '',
  page: 1,
  limit: 15,
});
const timeVal = ref([]);

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

onMounted(() => {
  onClickTab(tab.value);
});

function onClickTab() {
  getList();
}
function search() {
  getList();
}
function getList() {
  loading.value = true;
  let data = {
    type: tab.value,
    time: formValidate.value.time,
    page: formValidate.value.page,
    limit: formValidate.value.limit,
    store_id: formValidate.value.store_id,
  };
  getRecord(data).then((res) => {
    orderList.value = res.data.list;
    loading.value = false;
    total.value = res.data.count;
  });
}
// 选择时间
function selectChange(tab) {
  formValidate.value.page = 1;
  formValidate.value.time = tab;
  timeVal.value = [];
  getList();
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e || [];
  formValidate.value.time = timeVal.value[0] ? (timeVal.value ? timeVal.value.join('-') : '') : '';
  formValidate.value.page = 1;
  getList();
}
// 账单详情
function Info(row) {
  ids.value = row.ids || '';
  modals.value = true;
}
function cancel() {
  modals.value = false;
}
//下载
async function download(row) {
  let [th, fileKey, data, fileName] = [[], [], [], ''];
  let excelData = {
    ids: row.ids,
    page: 1,
    export: 1,
    time: formValidate.value.time,
  };
  let lebData = await getExcelData(excelData);
  if (!fileName) fileName = lebData.fileName;
  if (!fileKey.length) {
    fileKey = lebData.fileKey;
  }
  if (!th.length) th = lebData.header;
  data = data.concat(lebData.list);
  exportExcel(th, fileKey, fileName, data);
  return;
}
function getExcelData(excelData) {
  return new Promise((resolve, reject) => {
    getFlowList(excelData).then((res) => {
      return resolve(res.data);
    });
  });
}
</script>

<style scoped lang="scss">
:deep(.el-tabs__item) {
  height: 54px !important;
  line-height: 54px !important;
}
:deep(.ivu-card-body) {
  padding: 0;
}

.tabbox {
  padding: 16px 20px 0px;
}
.box {
  padding: 20px;
  padding-bottom: 1px;
}
.tablebox {
  margin-top: 15px;
  padding-bottom: 10px;
}
.btnbox {
  padding: 20px 0px 0px 30px;
  .btns {
    width: 99px;
    height: 32px;
    background: var(--prev-color-primary);
    border-radius: 4px;
    text-align: center;
    line-height: 32px;
    color: #ffffff;
    cursor: pointer;
  }
}
</style>
