<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form ref="formRef" :label-width="labelWidth" label-position="right" inline @submit.prevent>
          <el-form-item label="昵称/ID：">
            <el-input placeholder="请输入" v-model="formValidate.nickname" clearable class="form_content_width" />
          </el-form-item>
          <el-form-item label="佣金范围：" class="tab_data">
            <el-input-number :controls="false" :min="0" class="mr10" v-model="formValidate.price_min" />
            <span class="mr10">一</span>
            <el-input-number :controls="false" :min="0" v-model="formValidate.price_max" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-button v-auth="['export-userCommission']" class="export" v-db-click @click="exports">导出</el-button>
      <el-table
        ref="table"
        :data="tabList"
        v-loading="loading"
        empty-text="暂无数据"
        @on-sort-change="sortChanged"
        class="mt14"
      >
        <el-table-column label="用户信息" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="总佣金金额" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.sum_number }}</span>
          </template>
        </el-table-column>
        <!-- <el-table-column label="账户余额" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.now_money }}</span>
          </template>
        </el-table-column> -->
        <el-table-column label="账户佣金" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.brokerage_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="提现佣金" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.extract_price }}</span>
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
    </el-card>
    <commission-details ref="commission"></commission-details>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { commissionListApi, userCommissionApi } from '@/api/finance';
import commissionDetails from './handle/commissionDetails';

defineOptions({ name: 'commissionRecord' });

const { isMobile } = storeToRefs(useMediaStore());

const total = ref(0);
const loading = ref(false);
const tabList = ref([]);
const formValidate = ref({
  nickname: '',
  price_max: undefined,
  price_min: undefined,
  excel: 0,
  page: 1, // 当前页
  limit: 15, // 每页显示条数
});
const commission = ref(null);

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

onMounted(() => {
  getList();
});

// 列表
function getList() {
  loading.value = true;
  commissionListApi(formValidate.value)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 搜索
function userSearchs() {
  formValidate.value.page = 1;
  getList();
}
// 导出
function exports() {
  let data = {
    price_max: formValidate.value.price_max,
    price_min: formValidate.value.price_min,
    nickname: formValidate.value.nickname,
  };
  userCommissionApi(data)
    .then((res) => {
      location.href = res.data[0];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 详情
function Info(row) {
  commission.value.modals = true;
  commission.value.getDetails(row.uid);
  commission.value.getList(row.uid);
}
// 排序
function sortChanged(e) {
  if (e.key == 'sum_number') {
    delete formValidate.value.brokerage_price;
  } else {
    delete formValidate.value.sum_number;
  }
  formValidate.value[e.key] = e.order;
  getList();
}
</script>

<style lang="scss" scoped>
.lines {
  padding-top: 6px !important;
}
.tabform .export {
  margin-left: 10px;
}
.tab_data :deep(.ivu-form-item-content) {
  display: flex !important;
}
</style>
