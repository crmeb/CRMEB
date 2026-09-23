<template>
  <div class="cardkey-order-list">
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :inline="true" @submit.prevent="onSearch">
          <el-form-item label="订单号">
            <el-input
              v-model="search.keyword"
              placeholder="请输入订单号/收货人/电话"
              clearable
              @keyup.enter="onSearch"
              style="width: 220px"
            />
          </el-form-item>
          <el-form-item label="订单状态">
            <el-select v-model="search.status_code" placeholder="全部" clearable style="width: 120px">
              <el-option label="待支付" value="pending_payment" />
              <el-option label="待发货" value="pending_delivery" />
              <el-option label="已发货" value="delivered" />
              <el-option label="已完成" value="completed" />
              <el-option label="已取消" value="canceled" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="onSearch">查询</el-button>
            <el-button @click="onReset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>

    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '20px 20px 20px' }">
      <el-table :data="list" v-loading="loading" stripe empty-text="暂无订单数据" row-key="id">
        <el-table-column label="订单号" min-width="180" prop="order_no" show-overflow-tooltip />
        <el-table-column label="商品" min-width="220">
          <template #default="{ row }">
            <div class="goods-cell">
              <el-image v-if="row.image" :src="row.image" class="goods-img" />
              <span class="goods-name">{{ row.title || '-' }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="商品总价" width="100">
          <template #default="{ row }">{{ row.total_price || '0.00' }}</template>
        </el-table-column>
        <el-table-column label="实付金额" width="100">
          <template #default="{ row }">
            <span class="pay-price">{{ row.pay_price || '0.00' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="订单状态" width="110">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status_code)">{{ getStatusText(row.status_code) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="支付状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.pay_status == 1 ? 'success' : 'info'">{{
              row.pay_status == 1 ? '已支付' : '未支付'
            }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="下单时间" width="160">
          <template #default="{ row }">{{ formatDate(row.add_time) }}</template>
        </el-table-column>
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{ row }">
            <a v-db-click @click="goDetail(row.order_no)">详情</a>
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
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { cardkeyOrderList } from '../cardkeyProduct';
import setting from '@/setting';

defineOptions({ name: 'cardkeyOrderList' });

const router = useRouter();
const routePre = setting.routePre;

const loading = ref(false);
const list = ref([]);
const total = ref(0);
const search = ref({ page: 1, limit: 15, keyword: '', status_code: '' });

async function loadList() {
  loading.value = true;
  try {
    const params = { page: search.value.page, limit: search.value.limit };
    if (search.value.keyword) params.keyword = search.value.keyword;
    if (search.value.status_code) params.status_code = search.value.status_code;
    const res = await cardkeyOrderList(params);
    const data = res.data || {};
    list.value = data.list || data.data || [];
    total.value = data.count || data.total || 0;
  } catch (e) {
    ElMessage.error(e.message || '获取订单列表失败');
  }
  loading.value = false;
}
function onSearch() {
  search.value.page = 1;
  loadList();
}
function onReset() {
  search.value = { page: 1, limit: 15, keyword: '', status_code: '' };
  loadList();
}
function goDetail(orderNo) {
  router.push({ path: routePre + '/product/cardkey/order/detail/' + orderNo });
}
function getStatusText(status) {
  const map = {
    pending_payment: '待支付',
    pending_delivery: '待发货',
    delivered: '已发货',
    completed: '已完成',
    canceled: '已取消',
    refunding: '退款中',
    refunded: '已退款',
  };
  return map[status] || status || '-';
}
function getStatusType(status) {
  const map = {
    pending_payment: 'warning',
    pending_delivery: 'primary',
    delivered: 'primary',
    completed: 'success',
    canceled: 'info',
    refunding: 'warning',
    refunded: 'danger',
  };
  return map[status] || '';
}
function formatDate(ts) {
  if (!ts) return '--';
  const d = new Date(ts * 1000);
  const y = d.getFullYear();
  const M = ('0' + (d.getMonth() + 1)).slice(-2);
  const day = ('0' + d.getDate()).slice(-2);
  const h = ('0' + d.getHours()).slice(-2);
  const m = ('0' + d.getMinutes()).slice(-2);
  return y + '-' + M + '-' + day + ' ' + h + ':' + m;
}

onMounted(() => {
  loadList();
});
</script>

<style scoped>
.mt16 {
  margin-top: 16px;
}
.goods-cell {
  display: flex;
  align-items: center;
}
.goods-img {
  width: 40px;
  height: 40px;
  border-radius: 4px;
  margin-right: 8px;
  flex-shrink: 0;
}
.goods-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 13px;
}
.pay-price {
  color: #f56022;
  font-weight: bold;
}
</style>
