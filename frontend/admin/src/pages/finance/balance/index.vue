<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formRef"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          inline
          @submit.prevent
        >
          <el-form-item label="订单时间：">
            <date-range-picker
              width="250px"
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
              class="mr20"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="交易类型：">
            <el-select clearable v-model="formValidate.trading_type" @change="selChange" class="form_content_width">
              <el-option
                :label="item"
                :value="Object.keys(withdrawal)[index]"
                v-for="(item, index) in Object.values(withdrawal)"
                :key="index"
              ></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never">
      <el-table ref="table" :data="tabList" v-loading="loading" empty-text="暂无数据">
        <el-table-column label="ID" width="70">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="关联订单" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.relation }}</span>
          </template>
        </el-table-column>
        <el-table-column label="交易时间" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="交易金额" min-width="100">
          <template #default="scope">
            <div v-if="scope.row.pm" class="z-price">+ {{ scope.row.number }}</div>
            <div v-else class="f-price">- {{ scope.row.number }}</div>
          </template>
        </el-table-column>
        <el-table-column label="用户" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="交易类型" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.type_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="70">
          <template #default="scope">
            <a v-db-click @click="setMark(scope.row)">备注</a>
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
    <!-- 拒绝通过-->
    <el-dialog v-model="modals" title="备注" :close-on-click-modal="false" width="540px">
      <el-input v-model="mark_msg.mark" type="textarea" :rows="4" placeholder="请输入备注" />
      <template #footer
        ><span class="dialog-footer">
          <el-button type="primary" v-db-click @click="oks">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { getBalanceList, setBalanceMark } from '@/api/finance';

defineOptions({ name: 'balance' });

const { isMobile } = storeToRefs(useMediaStore());

const modal_loading = ref(false);
const mark_msg = ref({
  mark: '',
});
const modals = ref(false);
const total = ref(0);
const loading = ref(false);
const tabList = ref([]);
const withdrawal = ref([]);
const selectIndexTime = ref('');
const payment = [
  {
    title: '全部',
    value: '',
  },
  {
    title: '微信',
    value: 'weixin',
  },
  {
    title: '支付宝',
    value: 'alipay',
  },
  {
    title: '银行卡',
    value: 'bank',
  },
  {
    title: '线下支付',
    value: 'offline',
  },
];
const formValidate = ref({
  trading_type: '',
  time: '',
  keywords: '',
  page: 1,
  limit: 15,
});
const timeVal = ref([]);
const extractId = ref(0);

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

onMounted(() => {
  getList();
});

// 确定
function oks() {
  modal_loading.value = true;
  mark_msg.value.mark = mark_msg.value.mark.trim();
  setBalanceMark(extractId.value, mark_msg.value)
    .then(async (res) => {
      ElMessage.success(res.msg);
      modal_loading.value = false;
      modals.value = false;
      getList();
    })
    .catch((res) => {
      modal_loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 备注
function setMark(row) {
  modals.value = true;
  extractId.value = row.id;
  mark_msg.value.mark = row.mark;
}
function onSelectDate(e) {
  formValidate.value.time = e;
  formValidate.value.page = 1;
  getList();
}
function dateToMs(date) {
  let result = new Date(date).getTime();
  return result;
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.value.time = timeVal.value ? timeVal.value.join('-') : '';
  formValidate.value.page = 1;
  getList();
}
// 选择
function selChange(e) {
  formValidate.value.page = 1;
  formValidate.value.trading_type = e;
  getList();
}
// 列表
function getList() {
  loading.value = true;
  getBalanceList(formValidate.value)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      total.value = data.count;
      withdrawal.value = data.status;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 编辑提交成功
function submitFail() {
  getList();
}
</script>
<style lang="scss" scoped>
.ivu-mt .type .item {
  margin: 3px 0;
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
}
.ivu-form-item {
  margin-bottom: 10px;
}
.status :deep(.item ~ .item) {
  margin-left: 6px;
}
.status :deep(.statusVal) {
  margin-bottom: 7px;
}

/* .ivu-mt :deep(.ivu-table-header */
/* border-top:1px dashed #ddd!important */
:deep(.type) {
  padding: 3px 0;
  box-sizing: border-box;
}
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}
.z-price {
  color: red;
}
.f-price {
  color: green;
}
</style>
