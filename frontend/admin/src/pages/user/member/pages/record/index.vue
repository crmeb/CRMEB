<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          inline
          @submit.prevent
          class="tabform"
        >
          <el-form-item label="会员类型：">
            <el-select v-model="formValidate.member_type" clearable @change="userSearchs" class="form_content_width">
              <el-option v-for="item in treeSelect" :value="item.id" :key="item.id" :label="item.label"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="支付方式：">
            <el-select v-model="formValidate.pay_type" clearable @change="paySearchs" class="form_content_width">
              <el-option v-for="item in payList" :value="item.val" :key="item.val" :label="item.label"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="购买时间：">
            <date-range-picker
              width="250px"
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="请输入用户名称搜索"
              v-model="formValidate.name"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-table :data="tbody" ref="table" v-loading="loading">
        <el-table-column label="订单号" width="170">
          <template #default="scope"
            ><span>{{ scope.row.order_id }}</span></template
          >
        </el-table-column>
        <el-table-column label="用户名" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.user.nickname }}</span></template
          >
        </el-table-column>
        <el-table-column label="手机号码" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.user.phone || '--' }}</span></template
          >
        </el-table-column>
        <el-table-column label="会员类型" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.member_type }}</span></template
          >
        </el-table-column>
        <el-table-column label="有效期限（天）" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.vip_day === -1 ? '永久' : scope.row.vip_day }}</span></template
          >
        </el-table-column>
        <el-table-column label="支付金额（元）" min-width="50">
          <template #default="scope"
            ><span>{{ scope.row.pay_price }}</span></template
          >
        </el-table-column>
        <el-table-column label="支付方式" min-width="30">
          <template #default="scope"
            ><span>{{ scope.row.pay_type }}</span></template
          >
        </el-table-column>
        <el-table-column label="购买时间" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.pay_time }}</span></template
          >
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="tablePage.page"
          v-model:limit="tablePage.limit"
          @pagination="getMemberRecord"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { memberRecord } from '../../api';

defineOptions({ name: 'memberRecordList' });

const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

const treeSelect = [
  { id: 'free', label: '试用' },
  { id: 'card', label: '卡密' },
  { id: 'month', label: '月卡' },
  { id: 'quarter', label: '季卡' },
  { id: 'year', label: '年卡' },
  { id: 'ever', label: '永久' },
];
const payList = [
  { val: 'free', label: '免费' },
  { val: 'yue', label: '余额' },
  { val: 'weixin', label: '微信' },
  { val: 'alipay', label: '支付宝' },
];
const tbody = ref([]);
const loading = ref(false);
const total = ref(0);
const formValidate = ref({ name: '', member_type: '', pay_type: '', add_time: '' });
const timeVal = ref([]);
const tablePage = ref({ page: 1, limit: 15 });

// created
getMemberRecord();

function userSearchs() {
  tablePage.value.page = 1;
  getMemberRecord();
}

function paySearchs() {
  tablePage.value.page = 1;
  getMemberRecord();
}

function onchangeTime(e) {
  timeVal.value = e || [];
  formValidate.value.add_time = timeVal.value[0] ? (timeVal.value ? timeVal.value.join('-') : '') : '';
  tablePage.value.page = 1;
  getMemberRecord();
}

function getMemberRecord() {
  loading.value = true;
  let data = { page: tablePage.value.page, limit: tablePage.value.limit, ...formValidate.value };
  memberRecord(data)
    .then((res) => {
      loading.value = false;
      tbody.value = res.data.list;
      total.value = res.data.count;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err.msg);
    });
}
</script>
