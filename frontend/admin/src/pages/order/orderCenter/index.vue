<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="searchForm" label-width="80px" label-position="right" inline @submit.prevent>
          <el-form-item label="订单分类：">
            <el-select
              v-model="searchForm.order_category"
              clearable
              @change="getList"
              placeholder="全部"
              class="form_content_width"
            >
              <el-option label="全部" value="" />
              <el-option v-for="item in categoryOptions" :key="item.key" :value="item.key" :label="item.label" />
            </el-select>
          </el-form-item>
          <el-form-item label="产品类型：">
            <el-select
              v-model="searchForm.product_type"
              clearable
              @change="getList"
              placeholder="全部"
              class="form_content_width"
            >
              <el-option label="全部" value="" />
              <el-option v-for="item in productTypeOptions" :key="item.key" :value="item.key" :label="item.label" />
            </el-select>
          </el-form-item>
          <el-form-item label="活动类型：">
            <el-select
              v-model="searchForm.activity_type"
              clearable
              @change="getList"
              placeholder="全部"
              class="form_content_width"
            >
              <el-option label="全部" value="" />
              <el-option v-for="item in activityTypeOptions" :key="item.key" :value="item.key" :label="item.label" />
            </el-select>
          </el-form-item>
          <el-form-item label="支付方式：">
            <el-select
              v-model="searchForm.pay_type"
              clearable
              @change="getList"
              placeholder="全部"
              class="form_content_width"
            >
              <el-option label="全部" value="" />
              <el-option v-for="item in payTypeOptions" :key="item.key" :value="item.key" :label="item.label" />
            </el-select>
          </el-form-item>
          <el-form-item label="创建时间：">
            <date-range-picker
              width="250px"
              clearable
              v-model="timeVal"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY-MM-DD"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="订单搜索：">
            <el-input
              clearable
              v-model="searchForm.keyword"
              placeholder="请输入订单号/联系人/联系电话"
              class="form_content_width"
              @keyup.enter="getList"
            >
              <template #prepend
                ><el-select v-model="searchForm.search_field" style="width: 100px">
                  <el-option value="order_no" label="订单号"></el-option>
                  <el-option value="contact_name" label="联系人"></el-option>
                  <el-option value="contact_phone" label="联系电话"></el-option> </el-select
              ></template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="getList">查询</el-button>
            <el-button v-db-click @click="handleReset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <div class="mt16">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-card :bordered="false" shadow="never" class="stat-card">
            <div class="stat-item">
              <div class="stat-icon stat-icon-total">
                <i class="el-icon-s-order"></i>
              </div>
              <div class="stat-info">
                <div class="stat-value">{{ statData.total_count || 0 }}</div>
                <div class="stat-label">订单总数</div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card :bordered="false" shadow="never" class="stat-card">
            <div class="stat-item">
              <div class="stat-icon stat-icon-pay">
                <i class="el-icon-yuan"></i>
              </div>
              <div class="stat-info">
                <div class="stat-value">¥{{ statData.total_amount || 0 }}</div>
                <div class="stat-label">总金额</div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card :bordered="false" shadow="never" class="stat-card">
            <div class="stat-item">
              <div class="stat-icon stat-icon-pending">
                <i class="el-icon-clock"></i>
              </div>
              <div class="stat-info">
                <div class="stat-value">{{ statData.pending_count || 0 }}</div>
                <div class="stat-label">待支付</div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card :bordered="false" shadow="never" class="stat-card">
            <div class="stat-item">
              <div class="stat-icon stat-icon-completed">
                <i class="el-icon-check-circle"></i>
              </div>
              <div class="stat-info">
                <div class="stat-value">{{ statData.completed_count || 0 }}</div>
                <div class="stat-label">已完成</div>
              </div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>
    <el-card :bordered="false" shadow="never" class="mt16" :body-style="{ padding: '0 0 20px' }">
      <el-tabs v-model="statusTab" @tab-change="handleStatusTabClick">
        <el-tab-pane label="全部" name=""></el-tab-pane>
        <el-tab-pane :label="getTabLabel('待支付', 'pending_payment')" name="pending_payment"></el-tab-pane>
        <el-tab-pane :label="getTabLabel('待处理', 'processing')" name="processing"></el-tab-pane>
        <el-tab-pane :label="getTabLabel('待评价', 'pending_review')" name="pending_review"></el-tab-pane>
        <el-tab-pane :label="getTabLabel('已完成', 'completed')" name="completed"></el-tab-pane>
        <el-tab-pane :label="getTabLabel('已取消', 'canceled')" name="canceled"></el-tab-pane>
        <el-tab-pane :label="getTabLabel('售后中', 'after_sale')" name="after_sale"></el-tab-pane>
        <el-tab-pane :label="getTabLabel('已删除', 'deleted')" name="deleted"></el-tab-pane>
      </el-tabs>
      <div class="table-content">
        <el-table :data="orderList" ref="table" v-loading="loading" empty-text="暂无数据" class="orderData">
          <el-table-column type="selection" width="55"> </el-table-column>
          <el-table-column label="订单号" width="170">
            <template #default="scope">
              <span>{{ scope.row.order_no }}</span>
            </template>
          </el-table-column>
          <el-table-column label="订单分类" width="120">
            <template #default="scope">
              <span :class="getOrderCategoryClass(scope.row.order_category)">{{
                getOrderCategoryLabel(scope.row.order_category)
              }}</span>
            </template>
          </el-table-column>
          <el-table-column label="产品类型" width="120">
            <template #default="scope">
              <span>{{ getProductTypeLabel(scope.row.product_type) }}</span>
            </template>
          </el-table-column>
          <el-table-column label="商品信息" min-width="250">
            <template #default="scope">
              <div class="tab">
                <img v-lazy="scope.row.image" />
                <el-tooltip placement="top" :open-delay="300">
                  <template #content
                    ><div>
                      <div>
                        <span>商品名称：</span><span>{{ scope.row.title }}</span>
                      </div>
                      <div>
                        <span>购买数量：</span><span>{{ scope.row.total_num }}</span>
                      </div>
                    </div></template
                  >
                  <span class="line2 w-250">{{ scope.row.title }}</span>
                </el-tooltip>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="用户信息" min-width="150">
            <template #default="scope">
              <div>{{ scope.row.nickname || '--' }}</div>
              <div>{{ scope.row.user_phone || '--' }}</div>
            </template>
          </el-table-column>
          <el-table-column label="订单金额" min-width="120">
            <template #default="scope">
              <div>
                <span>总价：</span><span class="price">¥{{ scope.row.total_price }}</span>
              </div>
              <div>
                <span>实付：</span><span class="price">¥{{ scope.row.pay_price }}</span>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="支付状态" width="100">
            <template #default="scope">
              <el-tag :type="getPayStatusClass(scope.row.pay_status)">{{
                getPayStatusLabel(scope.row.pay_status)
              }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="订单状态" min-width="120">
            <template #default="scope">
              <el-tag :type="getStatusClass(scope.row.status_code)">{{ getStatusLabel(scope.row.status_code) }}</el-tag>
              <div v-if="scope.row.business_status">
                <span class="business-status">{{ scope.row.business_status.label }}</span>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="创建时间" min-width="160">
            <template #default="scope">
              <span>{{ scope.row.add_time ? formatDate(scope.row.add_time * 1000) : '--' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right" width="180">
            <template #default="scope">
              <a v-db-click @click="viewDetail(scope.row)">详情</a>
              <el-divider direction="vertical" />
              <a v-db-click @click="editRemark(scope.row)" v-auth="['admin-order-orderCenter-remark']">备注</a>
              <template v-if="statusTab === 'deleted'">
                <el-divider direction="vertical" />
                <a v-db-click @click="deleteOrder(scope.row)" v-auth="['admin-order-orderCenter-delete']">删除</a>
              </template>
            </template>
          </el-table-column>
        </el-table>
        <div class="acea-row row-right page">
          <pagination
            v-if="total"
            :total="total"
            v-model:page="page.page"
            v-model:limit="page.limit"
            @pagination="getList"
          />
        </div>
      </div>
    </el-card>
    <el-dialog v-model="remarkModal" title="订单备注" width="500px">
      <el-form :model="remarkForm" label-width="80px">
        <el-form-item label="备注内容">
          <el-input type="textarea" v-model="remarkForm.remark" :rows="4" placeholder="请输入备注内容"></el-input>
        </el-form-item>
      </el-form>
      <template #footer
        ><div>
          <el-button @click="remarkModal = false">取消</el-button>
          <el-button type="primary" @click="saveRemark">保存</el-button>
        </div></template
      >
    </el-dialog>
    <el-drawer
      v-model="detailDrawerVisible"
      title="订单详情"
      size="720px"
      class="order-detail-drawer"
      @opened="onDetailDrawerOpened"
    >
      <OrderCenterDetail :id="detailOrderId" ref="orderDetailRef" />
    </el-drawer>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
  orderCenterList,
  orderCenterStat,
  orderCenterRemark,
  orderCenterDelete,
  orderCenterCategories,
  orderCenterFilterOptions,
} from '@/api/orderCenter';
import OrderCenterDetail from './detail.vue';

defineOptions({ name: 'orderCenter' });

const searchForm = ref({
  order_category: '',
  product_type: '',
  activity_type: '',
  status_code: '',
  pay_type: '',
  keyword: '',
  search_field: 'order_no',
  date_range: '',
  uid: 0,
});
const timeVal = ref([]);
const orderList = ref([]);
const total = ref(0);
const page = ref({ page: 1, limit: 15 });
const loading = ref(false);
const statData = ref({});
const remarkModal = ref(false);
const remarkForm = ref({ id: 0, remark: '' });
const detailDrawerVisible = ref(false);
const detailOrderId = ref(0);
const orderDetailRef = ref(null);
const statusTab = ref('');
const categoryOptions = ref([]);
const productTypeOptions = ref([]);
const activityTypeOptions = ref([]);
const statusCodeOptions = [
  { key: 'pending_payment', label: '待支付' },
  { key: 'processing', label: '待处理' },
  { key: 'pending_review', label: '待评价' },
  { key: 'completed', label: '已完成' },
  { key: 'canceled', label: '已取消' },
  { key: 'after_sale', label: '售后中' },
  { key: 'refunded', label: '已退款' },
];
const payTypeOptions = ref([]);

function getList(res) {
  page.value.page = res === 1 ? 1 : page.value.page;
  loading.value = true;
  orderCenterList({
    ...searchForm.value,
    // 「已删除」tab 只查用户删除（is_del=1）的订单，不按状态过滤
    status_code: statusTab.value === 'deleted' ? '' : statusTab.value,
    is_deleted: statusTab.value === 'deleted' ? 1 : '',
    page: page.value.page,
    limit: page.value.limit,
  })
    .then((res) => {
      orderList.value = res.data.list;
      total.value = res.data.total;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

function getStat() {
  orderCenterStat({
    date_range: searchForm.value.date_range,
    order_category: searchForm.value.order_category,
  })
    .then((res) => {
      const d = res.data;
      statData.value = {
        total_count: d.all?.count || 0,
        total_amount: d.all?.amount || 0,
        pending_count: d.status_counts?.pending_payment?.count || 0,
        completed_count: d.status_counts?.completed?.count || 0,
        status_counts: d.status_counts || {},
        deleted_count: d.deleted_count || 0,
      };
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

function loadCategories() {
  orderCenterCategories()
    .then((res) => {
      categoryOptions.value = res.data || [];
    })
    .catch(() => {});
}

function loadFilterOptions() {
  orderCenterFilterOptions()
    .then((res) => {
      const data = res.data || {};
      productTypeOptions.value = data.product_types || [];
      activityTypeOptions.value = data.activity_types || [];
      payTypeOptions.value = data.pay_types || [];
    })
    .catch(() => {});
}

function onchangeTime(e) {
  timeVal.value = e || [];
  searchForm.value.date_range = timeVal.value[0] ? timeVal.value.join(',') : '';
  getList(1);
  getStat();
}

function handleReset() {
  searchForm.value = {
    order_category: '',
    product_type: '',
    activity_type: '',
    status_code: '',
    pay_type: '',
    keyword: '',
    search_field: 'order_no',
    date_range: '',
    uid: 0,
  };
  timeVal.value = [];
  statusTab.value = '';
  getList(1);
  getStat();
}

function viewDetail(row) {
  detailOrderId.value = row.id;
  detailDrawerVisible.value = true;
}

function onDetailDrawerOpened() {
  // 抽屉打开后触发详情组件重新加载，保证同一订单重复打开时数据最新
  if (orderDetailRef.value) {
    orderDetailRef.value.loadDetail();
  }
}

function handleStatusTabClick() {
  getList(1);
}

function editRemark(row) {
  remarkForm.value = { id: row.id, remark: row.remark || '' };
  remarkModal.value = true;
}

function saveRemark() {
  orderCenterRemark(remarkForm.value)
    .then((res) => {
      ElMessage.success(res.msg);
      remarkModal.value = false;
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

function deleteOrder(row) {
  ElMessageBox({
    title: '提示',
    message: '确定删除该订单吗？删除后后台列表将不再显示。',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
    iconClass: 'el-icon-warning',
  })
    .then(() => {
      orderCenterDelete(row.id)
        .then((res) => {
          ElMessage.success(res.msg);
          getList();
          getStat();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    })
    .catch(() => {});
}

function getOrderCategoryLabel(category) {
  const item = categoryOptions.value.find((opt) => opt.key === category);
  return item ? item.label : category || '--';
}

function getOrderCategoryClass(category) {
  const map = { product: 'category-product', activity: 'category-activity', user: 'category-user' };
  return map[category] || '';
}

function getProductTypeLabel(type) {
  const item = productTypeOptions.value.find((opt) => opt.key === type);
  return item ? item.label : type || '--';
}

function getStatusLabel(code) {
  const item = statusCodeOptions.find((opt) => opt.key === code);
  return item ? item.label : code || '--';
}

function getStatusClass(code) {
  const map = {
    pending_payment: 'warning',
    processing: 'primary',
    pending_review: 'success',
    completed: 'success',
    canceled: 'danger',
    after_sale: 'warning',
    refunding: 'warning',
    refunded: 'info',
  };
  return map[code] || 'info';
}

function getPayStatusLabel(status) {
  const map = { 0: '未支付', 1: '已支付', 2: '部分支付' };
  return map[status] || '--';
}

function getPayStatusClass(status) {
  const map = { 0: 'danger', 1: 'success', 2: 'warning' };
  return map[status] || 'info';
}

function getTabLabel(text, statusCode) {
  const count =
    statusCode === 'deleted' ? statData.value.deleted_count : statData.value.status_counts?.[statusCode]?.count;
  return count ? `${text}(${count})` : text;
}

function formatDate(date) {
  const d = new Date(date);
  return `${d.getFullYear()}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d
    .getDate()
    .toString()
    .padStart(2, '0')} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d
    .getSeconds()
    .toString()
    .padStart(2, '0')}`;
}

onMounted(() => {
  getList();
  getStat();
  loadCategories();
  loadFilterOptions();
});
</script>

<style lang="scss" scoped>
.form_content_width {
  width: 250px;
}

.table-content {
  padding: 12px 20px 0;
}

.tab {
  display: flex;
  align-items: center;

  img {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}

.w-250 {
  max-width: 250px;
}

.price {
  color: #ed4014;
  font-weight: bold;
}

.category-product {
  background: #e6f7ff;
  color: #1890ff;
}

.category-activity {
  background: #f6ffed;
  color: #52c41a;
}

.category-user {
  background: #fff7e6;
  color: #fa8c16;
}

.business-status {
  font-size: 12px;
  color: #999;
}

.stat-card {
  .stat-item {
    display: flex;
    align-items: center;
  }

  .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 15px;

    &-total {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
    }

    &-pay {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: #fff;
    }

    &-pending {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: #fff;
    }

    &-completed {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
      color: #fff;
    }
  }

  .stat-info {
    .stat-value {
      font-size: 24px;
      font-weight: bold;
      color: var(--prev-color-text-regular);
    }

    .stat-label {
      font-size: 14px;
      color: #999;
      margin-top: 5px;
    }
  }
}

:deep(.el-tabs__header) {
  margin: 0;
  padding: 0 20px;
}

/* 详情抽屉 body 左右归零（全局 variables.scss 为 20px 35px），与实物/网盘订单详情抽屉一致 */
:deep(.order-detail-drawer .el-drawer__body) {
  padding: 20px 0 !important;
}
</style>
