<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          用户资金流水明细，按类型与时间筛选，支持导出对账。
        </span>
      </div>
    </div>
    <el-card :bordered="false" shadow="never">
      <el-form
        ref="formRef"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.prevent
      >
        <el-row :gutter="24">
          <el-col :xl="6" :lg="12" :md="13" :sm="12" :xs="24">
            <el-form-item label="关键字：">
              <el-input enter-button placeholder="请输入" element-id="name" v-model="formValidate.nickname" />
            </el-form-item>
          </el-col>
          <el-col :xl="6" :lg="12" :md="13" :sm="12" :xs="24">
            <el-form-item label="时间范围：" class="tab_data">
              <date-range-picker
                clearable
                :editable="false"
                @change="onchangeTime"
                format="YYYY/MM/DD"
                value-format="YYYY/MM/DD"
                range-separator="-"
              ></date-range-picker>
            </el-form-item>
          </el-col>
          <el-col :xl="6" :lg="12" :md="13" :sm="12" :xs="24">
            <el-form-item label="筛选类型：" class="tab_data">
              <el-select v-model="formValidate.type" style="width: 200px; height: 32px" clearable>
                <el-option
                  v-for="(item, index) in billList"
                  :key="index"
                  :value="item.type"
                  :label="item.title"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item>
              <el-button type="primary" v-db-click @click="userSearchs">搜索</el-button>
              <el-button v-auth="['export-userFinance']" class="export" v-db-click @click="exports">导出 </el-button>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-table ref="table" highlight-current-row :data="tabList" v-loading="loading" empty-text="暂无数据">
        <el-table-column label="用户ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="昵称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="金额" min-width="130">
          <template #default="scope">
            <div :class="[scope.row.pm === 1 ? 'green' : 'red']">
              {{ scope.row.pm === 1 ? scope.row.number : '-' + scope.row.number }}
            </div>
          </template>
        </el-table-column>
        <el-table-column label="类型" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { billTypeApi, billListApi, userFinanceApi } from '@/api/finance';

defineOptions({ name: 'bill' });

const { isMobile } = storeToRefs(useMediaStore());

const billList = ref([]);
const formValidate = ref({
  nickname: '',
  start_time: '',
  end_time: '',
  type: '',
  page: 1, // 当前页
  limit: 15, // 每页显示条数
});
const loading = ref(false);
const tabList = ref([]);
const total = ref(0);

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

// created
selList();
getList();

// 时间
function onchangeTime(e) {
  formValidate.value.start_time = e[0];
  formValidate.value.end_time = e[1];
}
// 获取筛选类型
function selList() {
  billTypeApi()
    .then(async (res) => {
      billList.value = res.data.list;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  billListApi(formValidate.value)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.data;
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
    start_time: formValidate.value.start_time,
    end_time: formValidate.value.end_time,
    nickname: formValidate.value.nickname,
    type: formValidate.value.type,
  };
  userFinanceApi(data)
    .then((res) => {
      location.href = res.data[0];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
.ivu-form-label-left :deep(.ivu-form-item-label) {
  text-align: right;
}
.tabform .export {
  margin-left: 10px;
}
.red {
  color: #ff5722;
}
.green {
  color: #009688;
}
.ivu-mt :deep(.ivu-select-placeholder) {
  height: 32px;
}
</style>
