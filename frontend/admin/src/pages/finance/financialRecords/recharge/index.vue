<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          inline
          @submit.prevent
        >
          <el-form-item label="时间选择：">
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
          <el-form-item label="支付类型：">
            <el-select
              clearable
              v-model="formValidate.paid"
              placeholder="请选择"
              @change="selChange"
              class="form_content_width"
            >
              <el-option value="" label="全部"></el-option>
              <el-option value="1" label="已支付"></el-option>
              <el-option value="0" label="未支付"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="请输入用户昵称、订单号"
              v-model="formValidate.nickname"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card :bordered="false" shadow="never">
      <el-button v-auth="['export-userRecharge']" class="mr" v-db-click @click="exports">导出</el-button>
      <el-table ref="table" :data="tabList" class="mt14" v-loading="loading" empty-text="暂无数据"
        ><el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="头像" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar ? scope.row.avatar : moren" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户昵称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="订单号" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.order_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付金额" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否支付" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.paid_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="充值类型" min-width="130">
          <template #default="scope">
            <span>{{ scope.row._recharge_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row._pay_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100">
          <template #default="scope">
            <a
              href="javascript:void(0);"
              v-if="scope.row.refund_price <= 0 && scope.row.paid && scope.row.recharge_type != 'system'"
              v-db-click
              @click="refund(scope.row)"
              >退款</a
            >
            <!--                    <el-divider direction="vertical"  v-if="scope.row.paid"/>-->
            <a
              href="javascript:void(0);"
              v-if="scope.row.paid === 0"
              v-db-click
              @click="del(scope.row, '此条充值记录', scope.$index)"
              >删除</a
            >
            <span class="refund" v-if="scope.row.refund_price > 0">已退款</span>
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
    <!-- 退款表单-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import cardsData from '@/components/cards/cards';
import editFrom from '@/components/from/from';
import moren from '@/assets/images/moren.jpg';
import { rechargelistApi, userRechargeApi, refundEditApi, exportUserRechargeApi } from '@/api/finance';
import { modalSure } from '@/utils/public';
import { authLapse } from '@/utils/authLapse';

defineOptions({ name: 'recharge' });

const { isMobile } = storeToRefs(useMediaStore());

const edits = ref(null);
const FromData = ref(null);
const formValidate = ref({
  data: '',
  paid: '',
  nickname: '',
  excel: 0,
  page: 1,
  limit: 15,
});
const formValidate2 = ref({
  data: '',
  paid: '',
  nickname: '',
});
const total = ref(0);
const cardLists = ref([]);
const loading = ref(false);
const tabList = ref([]);
const timeVal = ref([]);

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

onMounted(() => {
  getList();
  getUserRecharge();
});

// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `finance/recharge/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tabList.value.splice(delfromData.num, 1);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 退款
function refund(row) {
  refundEditApi(row.id)
    .then(async (res) => {
      if (res.data.status === false) {
        return authLapse(res.data);
      }
      FromData.value = res.data;
      edits.value.modals = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑提交成功
function submitFail() {
  getList();
  getUserRecharge();
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.value.data = timeVal.value ? timeVal.value.join('-') : '';
  formValidate.value.page = 1;
  getList();
  getUserRecharge();
}
// 选择时间
function selectChange(tab) {
  formValidate.value.data = tab;
  timeVal.value = [];
  formValidate.value.page = 1;
  getList();
  getUserRecharge();
}
// 选择
function selChange(x) {
  formValidate.value.page = 1;
  getList();
  getUserRecharge();
}
// 列表
function getList() {
  loading.value = true;
  rechargelistApi(formValidate.value)
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
// 小方块
function getUserRecharge() {
  userRechargeApi({
    data: formValidate.value.data,
    paid: formValidate.value.paid,
    nickname: formValidate.value.nickname,
  })
    .then(async (res) => {
      let data = res.data;
      cardLists.value = data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 导出
function exports() {
  let data = {
    data: formValidate.value.data,
    paid: formValidate.value.paid,
    nickname: formValidate.value.nickname,
  };
  exportUserRechargeApi(data)
    .then((res) => {
      location.href = res.data[0];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
</script>
<style lang="scss" scoped>
.ivu-mt .type .item {
  margin: 3px 0;
}
.tabform {
  margin-bottom: 10px;
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
/*.ivu-mt ::v-deep .ivu-table-header*/
/*    border-top:1px dashed #ddd!important*/
</style>
