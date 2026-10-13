<template>
  <div class="virtual-order-list">
    <el-card shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form label-width="80px" label-position="right" inline @submit.prevent="onSearch">
          <el-form-item label="订单号：">
            <el-input
              v-model="search.order_no"
              placeholder="订单号"
              clearable
              @keyup.enter="onSearch"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item label="状态：">
            <el-select v-model="search.status" clearable placeholder="全部" class="form_content_width">
              <el-option label="待付款" value="pending_payment" />
              <el-option label="已完成" value="completed" />
              <el-option label="已关闭" value="closed" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="onSearch">查询</el-button>
            <el-button v-db-click @click="onReset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>

    <el-card shadow="never" :body-style="{ padding: '12px 20px' }" class="mt16">
      <el-table :data="list" v-loading="loading" stripe empty-text="暂无数据">
        <el-table-column label="订单号" min-width="200" prop="order_no" />
        <el-table-column label="商品名称" min-width="180">
          <template #default="{ row }">{{ row.title || '--' }}</template>
        </el-table-column>
        <el-table-column label="规格" min-width="90">
          <template #default="{ row }">{{ row.extra_info?.items?.[0]?.sku || '--' }}</template>
        </el-table-column>
        <el-table-column label="金额" min-width="100">
          <template #default="{ row }">
            <span style="color: #ed4014; font-weight: bold">¥{{ row.pay_price || '0.00' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="120">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)">{{ statusLabel(row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="完成时间" min-width="160">
          <template #default="{ row }">{{ dateFormat(row.complete_time) }}</template>
        </el-table-column>
        <el-table-column label="创建时间" min-width="160">
          <template #default="{ row }">{{ dateFormat(row.add_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="120" fixed="right">
          <template #default="{ row }">
            <a v-db-click @click="openDetail(row.order_no)">详情</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="search.page"
          v-model:limit="search.limit"
          @pagination="loadList"
        />
      </div>
    </el-card>

    <el-drawer v-model="detailVisible" title="订单详情" size="720px" class="order-detail-drawer" destroy-on-close>
      <OrderDetail :order-no="detailOrderNo" :show-back="false" />
    </el-drawer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { virtualApi } from '../api';
import OrderDetail from './Detail.vue';

defineOptions({ name: 'virtualOrderList' });

const loading = ref(false);
const list = ref([]);
const total = ref(0);
const search = ref({ page: 1, limit: 15, order_no: '', status: '' });

const detailVisible = ref(false);
const detailOrderNo = ref('');

function dateFormat(ts) {
  if (!ts) return '';
  const d = new Date(ts * 1000);
  const y = d.getFullYear();
  const M = ('0' + (d.getMonth() + 1)).slice(-2);
  const day = ('0' + d.getDate()).slice(-2);
  const h = ('0' + d.getHours()).slice(-2);
  const m = ('0' + d.getMinutes()).slice(-2);
  return y + '-' + M + '-' + day + ' ' + h + ':' + m;
}

function statusType(status) {
  const map = {
    pending_payment: 'warning',
    processing: 'primary',
    pending_review: 'success',
    completed: 'success',
    canceled: 'info',
    refunding: 'danger',
    refunded: 'info',
    closed: 'info',
  };
  return map[status] || 'info';
}
function statusLabel(status) {
  const map = {
    pending_payment: '待付款',
    processing: '处理中',
    pending_review: '待评价',
    completed: '已完成',
    canceled: '已取消',
    refunding: '退款中',
    refunded: '已退款',
    closed: '已关闭',
  };
  return map[status] || status;
}
async function loadList() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    params.set('page', search.value.page);
    params.set('limit', search.value.limit);
    if (search.value.order_no) params.set('order_no', search.value.order_no);
    if (search.value.status) params.set('status', search.value.status);
    const res = await virtualApi('/order/list?' + params.toString());
    list.value = res.data.list || [];
    total.value = res.data.total || 0;
  } catch (e) {
    ElMessage.error(e.message);
  }
  loading.value = false;
}
function onSearch() {
  search.value.page = 1;
  loadList();
}
function onReset() {
  search.value = { page: 1, limit: 15, order_no: '', status: '' };
  loadList();
}
function openDetail(orderNo) {
  detailOrderNo.value = orderNo;
  detailVisible.value = true;
}

// created
loadList();
</script>

<style lang="scss" scoped>
.mt16 {
  margin-top: 16px;
}

/* 详情抽屉 body 左右归零（全局 variables.scss 为 20px 35px），与实物订单详情抽屉一致 */
:deep(.order-detail-drawer .el-drawer__body) {
  padding: 20px 0 !important;
}
</style>
