<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
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
            <el-select v-model="formValidate.status" @change="selChange" class="form_content_width">
              <el-option :label="item" :value="index" v-for="(item, index) in withdrawal" :key="index"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="流水搜索：">
            <div class="acea-row row-middle">
              <el-input
                clearable
                placeholder="订单号/昵称/电话/用户ID"
                v-model="formValidate.keywords"
                class="form_content_width"
              />
            </div>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-table ref="table" :data="tabList" class="ivu-mt" v-loading="loading" empty-text="暂无数据">
        <el-table-column label="交易单号" width="180">
          <template #default="scope">
            <span>{{ scope.row.flow_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="关联订单" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.order_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="交易时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="交易金额" min-width="90">
          <template #default="scope">
            <div v-if="scope.row.price >= 0" class="z-price">+{{ scope.row.price }}</div>
            <div v-if="scope.row.price < 0" class="f-price">{{ scope.row.price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="交易用户" min-width="90">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付方式" min-width="90">
          <template #default="scope">
            <div v-for="item in payment" :key="item.value">
              <span v-if="scope.row.pay_type == item.value"> {{ item.title }} </span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="90">
          <template #default="scope">
            <span>{{ scope.row.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
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
          <el-button type="primary" v-db-click @click.prevent="oks">确定</el-button>
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
import { getFlowList, cashEditApi, setMarks } from '@/api/finance';

defineOptions({ name: 'cashApply' });

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
  trading_type: 0,
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
  setMarks(extractId.value, mark_msg.value)
    .then((res) => {
      ElMessage.success(res.msg);
      modal_loading.value = false;
      modals.value = false;
      getList();
    })
    .catch((err) => {
      modal_loading.value = false;
      ElMessage.error(err.msg);
    });
}
// 备注
function setMark(row) {
  modals.value = true;
  extractId.value = row.id;
  mark_msg.value.mark = row.mark;
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
  getList();
}
// 列表
function getList() {
  loading.value = true;
  getFlowList(formValidate.value)
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
.tabform {
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
