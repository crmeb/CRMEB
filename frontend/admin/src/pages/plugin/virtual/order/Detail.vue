<template>
  <div class="virtual-order-detail" v-loading="loading">
    <el-card v-if="showBack" class="mb10" shadow="never" :body-style="{ padding: '16px 20px' }">
      <el-button @click="goBack">返回</el-button>
    </el-card>

    <div v-if="!loading && orderInfo.order_no">
      <!-- 头部摘要 -->
      <el-card shadow="never" class="mt16">
        <template #header
          ><div class="head">
            <div class="full">
              <img class="order-icon" src="@/assets/images/order_icon.png" alt="" />
              <div class="text">
                <div class="title">网盘商品订单</div>
                <div>订单号：{{ orderInfo.order_no }}</div>
              </div>
            </div>
            <ul class="head-list">
              <li class="item">
                <div class="label">订单状态</div>
                <div>
                  <el-tag :type="statusType(orderInfo.status_code)">
                    {{ statusLabel(orderInfo.status_code) }}
                  </el-tag>
                  <span v-if="orderInfo.refund_status == 1" style="color: #ed4014; margin-left: 4px">
                    {{ orderInfo.is_all_refund ? '退款中' : '部分退款中' }}
                  </span>
                </div>
              </li>
              <li class="item">
                <div class="label">订单金额</div>
                <div class="price">¥{{ orderInfo.total_price || '0.00' }}</div>
              </li>
              <li class="item" v-if="orderInfo.refund_status == 2">
                <div class="label">实际退款</div>
                <div>¥{{ orderInfo.refunded_price || '0.00' }}</div>
              </li>
              <li class="item">
                <div class="label">支付方式</div>
                <div>{{ orderInfo.pay_type_name || orderInfo.pay_type || '--' }}</div>
              </li>
              <li class="item">
                <div class="label">支付时间</div>
                <div>{{ fmt(orderInfo.pay_time) }}</div>
              </li>
              <li class="item">
                <div class="label">业务状态</div>
                <div>
                  <el-tag v-if="orderInfo.business_status" :type="tagType(orderInfo.business_status)">
                    {{ orderInfo.business_status.text }}
                  </el-tag>
                  <span v-else>--</span>
                </div>
              </li>
            </ul>
          </div></template
        >
      </el-card>

      <!-- 订单信息 Tab -->
      <el-card shadow="never" class="mt16">
        <el-tabs v-model="activeTab">
          <el-tab-pane label="订单信息" name="detail">
            <!-- 商品信息 -->
            <div class="section" v-if="orderItems.length">
              <div class="section-title">商品信息</div>
              <el-table :data="orderItems" stripe class="goods-table">
                <el-table-column label="商品" min-width="300">
                  <template #default="{ row }">
                    <div class="goods-cell">
                      <el-image :src="row.image" class="goods-img" />
                      <div class="goods-meta">
                        <p class="goods-name">{{ row.store_name }}</p>
                        <p v-if="row.sku" class="goods-sku">{{ row.sku }}</p>
                      </div>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column label="单价" width="100">
                  <template #default="{ row }">¥{{ row.true_price || row.price }}</template>
                </el-table-column>
                <el-table-column label="数量" width="80" prop="cart_num" />
                <el-table-column label="小计" width="120">
                  <template #default="{ row }"
                    >¥{{ (parseFloat(row.true_price || row.price) * row.cart_num).toFixed(2) }}</template
                  >
                </el-table-column>
              </el-table>
            </div>

            <!-- 用户信息 -->
            <div class="section">
              <div class="section-title">用户信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">用户名称：</span
                    ><span class="value">{{ orderInfo.real_name || orderInfo.contact?.name || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">联系电话：</span
                    ><span class="value">{{ orderInfo.user_phone || orderInfo.contact?.phone || '--' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 订单信息 -->
            <div class="section">
              <div class="section-title">订单信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">创建时间：</span><span class="value">{{ fmt(orderInfo.add_time) }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">商品总数：</span><span class="value">{{ orderInfo.total_num }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">商品总价：</span><span class="value">¥{{ orderInfo.total_price }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">优惠券金额：</span
                    ><span class="value">-¥{{ orderInfo.coupon_price || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">积分抵扣：</span
                    ><span class="value">-¥{{ orderInfo.deduction_price || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">会员等级优惠：</span
                    ><span class="value">-¥{{ orderInfo.member_level_discount || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">付费会员优惠：</span
                    ><span class="value">-¥{{ orderInfo.vip_discount || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label price-label">实际支付：</span
                    ><span class="value price-value">¥{{ orderInfo.pay_price || '0.00' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 网盘商品扩展信息 -->
            <div class="section" v-if="orderInfo.virtual_extend">
              <div class="section-title">网盘商品信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">商品ID：</span
                    ><span class="value">{{ orderInfo.virtual_extend.product_id || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">状态：</span
                    ><span class="value">
                      <el-tag :type="statusType(orderInfo.virtual_extend.status)">
                        {{ orderInfo.virtual_extend.status_text || statusLabel(orderInfo.virtual_extend.status) }}
                      </el-tag>
                    </span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">发放时间：</span
                    ><span class="value">{{ fmt(orderInfo.virtual_extend.send_time) }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">完成时间：</span
                    ><span class="value">{{ fmt(orderInfo.virtual_extend.complete_time) }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 发货/交付信息 -->
            <div class="section" v-if="orderInfo.virtual_extend && orderInfo.virtual_extend.delivery_info">
              <div class="section-title">交付信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="24"
                  ><div class="detail-item">
                    <span class="label">交付内容：</span
                    ><span class="value" style="white-space: pre-wrap">{{
                      orderInfo.virtual_extend.delivery_info
                    }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 发票信息 -->
            <div class="section" v-if="orderInfo.invoice && Object.keys(orderInfo.invoice).length">
              <div class="section-title">发票信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">发票抬头：</span><span class="value">{{ orderInfo.invoice.name || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="orderInfo.invoice.header_type === 2 && orderInfo.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">企业税号：</span
                    ><span class="value">{{ orderInfo.invoice.duty_number || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="orderInfo.invoice.header_type === 2 && orderInfo.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">发票类型：</span><span class="value">电子普通发票</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="orderInfo.invoice.header_type === 2 && orderInfo.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">发票抬头类型：</span><span class="value">企业</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="orderInfo.invoice.header_type === 1 && orderInfo.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">真实姓名：</span><span class="value">{{ orderInfo.invoice.name || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="orderInfo.invoice.header_type === 1 && orderInfo.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">联系电话：</span
                    ><span class="value">{{ orderInfo.invoice.drawer_phone || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="orderInfo.invoice.header_type === 2 && orderInfo.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">联系电话：</span
                    ><span class="value">{{ orderInfo.invoice.user_phone || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="orderInfo.invoice.header_type === 2 && orderInfo.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">联系邮箱：</span
                    ><span class="value">{{ orderInfo.invoice.email || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">开票状态：</span
                    ><span class="value">{{ orderInfo.invoice.is_invoice ? '已开票' : '未开票' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 买家留言 -->
            <div class="section">
              <div class="section-title">买家留言</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="24"
                  ><div class="detail-item">
                    <span class="value">{{ orderInfo.mark || '--' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 表单信息 -->
            <div class="section" v-if="orderInfo.custom_form && orderInfo.custom_form.length">
              <div class="section-title">表单信息</div>
              <el-row :gutter="24" class="detail-grid">
                <template v-for="(item, idx) in orderInfo.custom_form" :key="idx">
                  <el-col :span="8" v-if="item.label !== 'img'">
                    <div class="detail-item">
                      <span class="label">{{ item.title }}：</span><span class="value">{{ item.value }}</span>
                    </div>
                  </el-col>
                  <el-col :span="24" v-else>
                    <div class="detail-item">
                      <span class="label">{{ item.title }}：</span>
                      <span class="value">
                        <img v-for="(img, i) in item.value" :key="i" :src="img" class="form-img" v-viewer />
                      </span>
                    </div>
                  </el-col>
                </template>
              </el-row>
            </div>

            <!-- 订单备注 -->
            <div class="section">
              <div class="section-title">订单备注</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="24"
                  ><div class="detail-item">
                    <span class="value">{{ orderInfo.remark || '--' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>
          </el-tab-pane>

          <el-tab-pane label="订单进度" name="timeline">
            <div class="tl-section" v-if="orderInfo.timeline && orderInfo.timeline.length">
              <el-timeline>
                <el-timeline-item
                  v-for="(item, index) in orderInfo.timeline"
                  :key="index"
                  :timestamp="item.time"
                  :color="index === 0 ? '#409eff' : '#e4e7ed'"
                >
                  <span class="tl-title">{{ item.title }}</span>
                  <span class="tl-desc">{{ item.desc }}</span>
                </el-timeline-item>
              </el-timeline>
            </div>
          </el-tab-pane>
        </el-tabs>
      </el-card>
    </div>
    <div v-else-if="!loading" class="empty-tip">
      <el-empty description="订单信息未找到"></el-empty>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import request from '@/libs/request';

function dateFormat(ts) {
  if (!ts) return '';
  const d = new Date(ts * 1000);
  return (
    d.getFullYear() +
    '-' +
    ('0' + (d.getMonth() + 1)).slice(-2) +
    '-' +
    ('0' + d.getDate()).slice(-2) +
    ' ' +
    ('0' + d.getHours()).slice(-2) +
    ':' +
    ('0' + d.getMinutes()).slice(-2) +
    ':' +
    ('0' + d.getSeconds()).slice(-2)
  );
}

const statusLabelMap = {
  pending_payment: '待支付',
  processing: '待处理',
  completed: '已完成',
  canceled: '已取消',
  refunding: '退款中',
  refunded: '已退款',
};

const statusTypeMap = {
  pending_payment: 'warning',
  processing: 'primary',
  completed: 'success',
  canceled: 'info',
  refunding: 'danger',
  refunded: 'info',
};

defineOptions({ name: 'virtualOrderDetail' });

const props = defineProps({
  orderNo: { type: String, default: '' },
  // 独立页面时显示返回头部；列表抽屉内使用时传 false
  showBack: { type: Boolean, default: true },
});

const router = useRouter();

const loading = ref(false);
const orderInfo = ref({});
const activeTab = ref('detail');

const orderItems = computed(() => orderInfo.value.order_items || orderInfo.value.cartInfo || []);

watch(
  () => props.orderNo,
  (val) => {
    if (val) loadDetail();
  },
  { immediate: true },
);

function statusType(code) {
  return statusTypeMap[code] || 'info';
}
function statusLabel(code) {
  return statusLabelMap[code] || code || '--';
}
function tagType(bs) {
  if (!bs) return 'info';
  const map = {
    pending_payment: 'warning',
    pending_send: 'warning',
    completed: 'success',
    canceled: 'info',
    refunding: 'danger',
    refunded: 'info',
  };
  return map[bs.code] || 'info';
}
function fmt(ts) {
  return ts ? dateFormat(ts) : '--';
}
function goBack() {
  router.push({ path: '/admin/product/virtual/order/list' });
}
async function loadDetail() {
  if (!props.orderNo) return;
  loading.value = true;
  try {
    const res = await request.get('virtual/order/' + props.orderNo);
    orderInfo.value = res.data || {};
  } catch (e) {
    ElMessage.error('加载订单详情失败');
  }
  loading.value = false;
}
</script>

<style scoped>
.mt16 {
  margin-top: 16px;
}

/* 头部摘要 */
.head {
  padding: 0;
}
.head .full {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}
.head .order-icon {
  width: 50px;
  height: 50px;
  margin-right: 12px;
}
.head .text {
  flex: 1;
}
.head .text .title {
  font-size: 16px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
  margin-bottom: 6px;
}
.head .text div {
  font-size: 13px;
  color: var(--prev-color-text-primary);
}
.head-list {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  flex-wrap: wrap;
}
.head-list .item {
  flex: 0 0 160px;
  margin-top: 12px;
  font-size: 14px;
  color: var(--prev-color-text-regular);
}
.head-list .item .label {
  font-size: 13px;
  color: var(--prev-color-text-secondary);
  margin-bottom: 8px;
}
.head-list .item .price {
  color: #ed4014;
  font-weight: bold;
}

/* section 通用 */
.section {
  padding: 20px 0;
  border-bottom: 1px dashed #eee;
}
.section:last-child {
  border-bottom: none;
}
.section-title {
  padding-left: 10px;
  border-left: 3px solid var(--prev-color-primary, #1890ff);
  font-size: 15px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
  margin-bottom: 16px;
}
.detail-grid {
  padding: 0;
}
.detail-item {
  display: flex;
  padding: 8px 0;
  font-size: 13px;
  color: #666;
}
.detail-item .label {
  flex-shrink: 0;
  color: var(--prev-color-text-secondary);
  margin-right: 4px;
}
.detail-item .value {
  flex: 1;
  word-break: break-all;
}
.price-label {
  color: #ed4014 !important;
}
.price-value {
  color: #ed4014;
  font-weight: bold;
}

/* 商品表格 */
.goods-table {
  border: 1px solid var(--prev-border-color-lighter);
  border-radius: 4px;
}
.goods-cell {
  display: flex;
  align-items: center;
}
.goods-img {
  width: 50px;
  height: 50px;
  border-radius: 4px;
  margin-right: 12px;
  flex-shrink: 0;
}
.goods-meta {
  min-width: 0;
}
.goods-name {
  margin: 0;
  font-size: 13px;
  color: var(--prev-color-text-regular);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.goods-sku {
  margin: 2px 0 0;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

/* 表单图片 */
.form-img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  margin-right: 8px;
  cursor: pointer;
}

/* 时间线 */
.tl-section {
  padding: 16px 0;
}
:deep(.el-timeline-item__content .tl-title) {
  font-size: 13px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
}
:deep(.el-timeline-item__content .tl-desc) {
  margin-left: 8px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

.empty-tip {
  padding: 60px 0;
}

:deep(.el-tabs--border-card > .el-tabs__content) {
  /* 独立路由详情页：.head 无缩进，tabs 内容对齐 */
  padding: 0;
}
:deep(.el-tabs--border-card) {
  border: none;
  box-shadow: none;
}
:deep(.el-tabs__item) {
  height: 40px !important;
  line-height: 40px !important;
}
</style>
