<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="pagination" :label-width="labelWidth" :label-position="labelPosition" @submit.prevent inline>
          <el-form-item label="创建时间：">
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
          <el-form-item label="订单号：" label-for="title">
            <el-input clearable v-model="pagination.order_id" placeholder="请输入订单号" class="form_content_width" />
          </el-form-item>
          <el-form-item label="用户名：" label-for="title">
            <el-input clearable v-model="pagination.name" placeholder="请输入用户名" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="orderSearch">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button type="primary" v-db-click @click="qrcodeShow">查看收款二维码</el-button>
      <el-table :data="tbody" ref="table" class="mt14" v-loading="loading" highlight-current-row>
        <el-table-column label="订单号" min-width="180">
          <template #default="scope"
            ><span>{{ scope.row.order_id }}</span></template
          >
        </el-table-column>
        <el-table-column label="用户信息" min-width="180">
          <template #default="scope"
            ><span>{{ scope.row.nickname }} | {{ scope.row.uid }}</span></template
          >
        </el-table-column>
        <el-table-column label="实际支付" min-width="180">
          <template #default="scope"
            ><span>{{ scope.row.pay_price }}</span></template
          >
        </el-table-column>
        <el-table-column label="优惠价格" min-width="180">
          <template #default="scope"
            ><span>{{ scope.row.true_price }}</span></template
          >
        </el-table-column>
        <el-table-column label="支付时间" min-width="180">
          <template #default="scope"
            ><span>{{ scope.row.pay_time }}</span></template
          >
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="pagination.page"
          v-model:limit="pagination.limit"
          @pagination="getOrderList"
        />
      </div>
    </el-card>
    <el-dialog v-model="modal" title="收款码" width="540px">
      <div v-viewer class="acea-row row-around code">
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic"><img v-lazy="qrcode && qrcode.wechat" /></div>
          <span class="mt10">公众号收款码</span>
        </div>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic"><img v-lazy="qrcode && qrcode.routine" /></div>
          <span class="mt10">小程序收款码</span>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { orderScanList, orderOfflineScan } from '../../api';

defineOptions({ name: 'memberOfflineOrder' });

const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

const tbody = ref([]);
const loading = ref(false);
const total = ref(0);
const modal = ref(false);
const qrcode = ref(null);
const spin = ref(false);
const pagination = ref({ page: 1, limit: 15, order_id: '', add_time: '' });
const timeVal = ref([]);

// created
getOrderList();

function onchangeTime(e) {
  pagination.value.page = 1;
  timeVal.value = e || [];
  pagination.value.add_time = timeVal.value[0] ? (timeVal.value ? timeVal.value.join('-') : '') : '';
  getOrderList();
}

function getOrderList() {
  loading.value = true;
  orderScanList(pagination.value)
    .then((res) => {
      loading.value = false;
      total.value = res.data.count;
      tbody.value = res.data.list;
    })
    .catch((err) => {
      loading.value = false;
      ElMessage.error(err.msg);
    });
}

function orderSearch() {
  pagination.value.page = 1;
  getOrderList();
}

function qrcodeShow() {
  modal.value = true;
  spin.value = true;
  orderOfflineScan({ type: 0 })
    .then((res) => {
      spin.value = false;
      qrcode.value = res.data;
    })
    .catch((err) => {
      spin.value = false;
      ElMessage.error(err.msg);
    });
}
</script>

<style lang="scss" scoped>
.code {
  position: relative;
}
.QRpic {
  width: 180px;
  img {
    width: 100%;
    height: 100%;
  }
}
</style>
