<template>
  <div>
    <div v-loading="detailLoading">
      <div v-if="detailData" class="order-detail">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderIcon" alt="" />
            <div class="text">
              <div class="title">卡密商品订单</div>
              <div>订单号：{{ detailData.order_no }}</div>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">订单状态</div>
              <div>
                {{ getStatusLabel(detailData.status_code) }}
                <span v-if="detailData.refund_status == 1" style="color: #ed4014">
                  {{ detailData.is_all_refund ? '退款中' : '部分退款中' }}
                </span>
              </div>
            </li>
            <li class="item">
              <div class="title">订单金额</div>
              <div class="value1">¥{{ detailData.total_price || '0.00' }}</div>
            </li>
            <li class="item" v-if="detailData.refund_status == 2">
              <div class="title">实际退款</div>
              <div>¥{{ detailData.refunded_price || '0.00' }}</div>
            </li>
            <li class="item">
              <div class="title">支付方式</div>
              <div>{{ payType(detailData.pay_type) }}</div>
            </li>
            <li class="item">
              <div class="title">支付时间</div>
              <div>{{ detailData.pay_time ? formatDate(detailData.pay_time) : '--' }}</div>
            </li>
          </ul>
        </div>
        <el-tabs type="border-card" v-model="activeTab">
          <el-tab-pane label="订单信息" name="detail">
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

            <div class="section" v-if="detailData.card_info && detailData.card_info.length">
              <div class="section-title">卡密信息</div>
              <el-table :data="detailData.card_info" stripe class="goods-table">
                <el-table-column label="卡号" min-width="200">
                  <template #default="{ row }">{{ row.card_no || '--' }}</template>
                </el-table-column>
                <el-table-column label="卡密" min-width="200">
                  <template #default="{ row }">
                    <div class="card-key-wrapper">
                      <span class="card-key">{{ row.card_key || '--' }}</span>
                      <a v-db-click @click="copyCardKey(row.card_key)">复制</a>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column label="有效期" width="150">
                  <template #default="{ row }">{{
                    row.expire_time ? formatDate(row.expire_time) : '永久有效'
                  }}</template>
                </el-table-column>
                <el-table-column label="状态" width="100">
                  <template #default="{ row }">
                    <el-tag :type="row.status == 1 ? 'success' : 'warning'">{{
                      row.status == 1 ? '已使用' : '未使用'
                    }}</el-tag>
                  </template>
                </el-table-column>
              </el-table>
            </div>

            <div class="section">
              <div class="section-title">用户信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">用户名称：</span
                    ><span class="value">{{ detailData.real_name || detailData.contact?.name || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">联系电话：</span
                    ><span class="value">{{ detailData.user_phone || detailData.contact?.phone || '--' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <div class="section">
              <div class="section-title">订单信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">创建时间：</span
                    ><span class="value">{{ formatDate(detailData.add_time) }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">商品总数：</span><span class="value">{{ detailData.total_num }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">商品总价：</span><span class="value">¥{{ detailData.total_price }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">优惠券金额：</span
                    ><span class="value">-¥{{ detailData.coupon_price || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">积分抵扣：</span
                    ><span class="value">-¥{{ detailData.deduction_price || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">会员等级优惠：</span
                    ><span class="value">-¥{{ detailData.member_level_discount || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">付费会员优惠：</span
                    ><span class="value">-¥{{ detailData.vip_discount || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label price-label">实际支付：</span
                    ><span class="value price-value">¥{{ detailData.pay_price || '0.00' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <div
              class="section"
              v-if="
                detailData.one_brokerage ||
                detailData.two_brokerage ||
                detailData.staff_brokerage ||
                detailData.agent_brokerage ||
                detailData.division_brokerage
              "
            >
              <div class="section-title">返佣信息（金额|用户UID）</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">一级佣金：</span
                    ><span class="value"
                      >{{ detailData.one_brokerage || '0.00' }} | {{ detailData.spread_uid || '0' }}</span
                    >
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">二级佣金：</span
                    ><span class="value"
                      >{{ detailData.two_brokerage || '0.00' }} | {{ detailData.spread_two_uid || '0' }}</span
                    >
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">员工返佣：</span
                    ><span class="value"
                      >{{ detailData.staff_brokerage || '0.00' }} | {{ detailData.staff_id || '0' }}</span
                    >
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">代理商佣金：</span
                    ><span class="value"
                      >{{ detailData.agent_brokerage || '0.00' }} | {{ detailData.agent_id || '0' }}</span
                    >
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">事业部佣金：</span
                    ><span class="value"
                      >{{ detailData.division_brokerage || '0.00' }} | {{ detailData.division_id || '0' }}</span
                    >
                  </div></el-col
                >
              </el-row>
            </div>

            <div class="section" v-if="detailData.invoice && Object.keys(detailData.invoice).length">
              <div class="section-title">发票信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">发票抬头：</span
                    ><span class="value">{{ detailData.invoice.name || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="detailData.invoice.header_type === 2 && detailData.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">企业税号：</span
                    ><span class="value">{{ detailData.invoice.duty_number || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">开票状态：</span
                    ><span class="value">{{ detailData.invoice.is_invoice ? '已开票' : '未开票' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <div class="section">
              <div class="section-title">买家留言</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="24"
                  ><div class="detail-item">
                    <span class="value">{{ detailData.mark || '--' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <div class="section" v-if="detailData.custom_form && detailData.custom_form.length">
              <div class="section-title">表单信息</div>
              <el-row :gutter="24" class="detail-grid">
                <template v-for="(item, idx) in detailData.custom_form" :key="idx">
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

            <div class="section">
              <div class="section-title">订单备注</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="24"
                  ><div class="detail-item">
                    <span class="value">{{ detailData.remark || '--' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>
          </el-tab-pane>

          <el-tab-pane label="订单进度" name="timeline">
            <div class="tl-section" v-if="detailData.timeline && detailData.timeline.length">
              <el-timeline>
                <el-timeline-item
                  v-for="(item, index) in detailData.timeline"
                  :key="index"
                  :timestamp="item.time"
                  :color="index === 0 ? '#409eff' : '#e4e7ed'"
                >
                  <span class="tl-title">{{ item.title }}</span>
                  <span class="tl-desc">{{ item.desc }}</span>
                </el-timeline-item>
              </el-timeline>
            </div>
            <div v-else class="empty-tips">暂无订单进度</div>
          </el-tab-pane>
        </el-tabs>
      </div>
      <div v-else-if="!detailLoading" class="empty-tip">
        <el-empty description="暂无订单数据"></el-empty>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import __asset_order_icon_0 from '@/assets/images/order_icon.png';

import request from '@/libs/request';

defineOptions({ name: 'cardkeyOrderDetail' });

const route = useRoute();

const detailData = ref(null);
const detailLoading = ref(false);
const activeTab = ref('detail');
const orderIcon = __asset_order_icon_0;

const orderItems = computed(() => {
  if (!detailData.value) return [];
  return detailData.value.order_items || detailData.value.cartInfo || [];
});

function loadDetail() {
  const orderNo = route.params.orderNo;
  if (!orderNo) return;
  detailLoading.value = true;
  request
    .get('cardkey/order/' + orderNo)
    .then((res) => {
      detailData.value = res.data;
      detailLoading.value = false;
    })
    .catch((res) => {
      detailLoading.value = false;
      ElMessage.error(res.msg || '加载订单详情失败');
    });
}
function copyCardKey(key) {
  if (!key) return;
  const input = document.createElement('input');
  input.value = key;
  document.body.appendChild(input);
  input.select();
  document.execCommand('copy');
  document.body.removeChild(input);
  ElMessage.success('复制成功');
}
function getStatusLabel(code) {
  const map = {
    pending_payment: '待支付',
    processing: '待处理',
    completed: '已完成',
    canceled: '已取消',
    refunding: '退款中',
    refunded: '已退款',
  };
  return map[code] || code || '--';
}
// 原 filters.payType
function payType(val) {
  const obj = {
    yue: '余额',
    weixin: '微信支付',
    alipay: '支付宝支付',
    offline: '线下支付',
    balance: '余额支付',
    integral: '积分支付',
  };
  return obj[val] || val || '--';
}
function formatDate(ts) {
  if (!ts) return '--';
  const d = new Date(ts * 1000);
  return `${d.getFullYear()}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d
    .getDate()
    .toString()
    .padStart(2, '0')} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d
    .getSeconds()
    .toString()
    .padStart(2, '0')}`;
}

onMounted(() => {
  loadDetail();
});
</script>

<style lang="scss" scoped>
:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item.is-active) {
  border-bottom: none;
}

:deep(.el-tabs__item) {
  height: 40px !important;
  line-height: 40px !important;
}

.head {
  padding: 0 30px 24px;

  .full {
    display: flex;
    align-items: center;

    .order_icon {
      width: 60px;
      height: 60px;
    }

    .text {
      align-self: center;
      flex: 1;
      min-width: 0;
      padding-left: 12px;
      font-size: 13px;
      color: #606266;

      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        color: rgba(0, 0, 0, 0.85);
      }
    }
  }

  .list {
    display: flex;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;

    .item {
      flex: none;
      width: 200px;
      font-size: 14px;
      line-height: 14px;
      color: rgba(0, 0, 0, 0.85);

      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: #666666;
      }

      .value1 {
        color: #f56022;
        font-weight: bold;
      }
    }
  }
}

:deep(.el-tabs--border-card > .el-tabs__content) {
  padding: 0 35px;
}

:deep(.el-tabs--border-card > .el-tabs__header),
:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item:active) {
  border: none;
  height: 40px;
}

:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item.is-active) {
  border: none;
  border-top: 2px solid var(--prev-color-primary);
  font-size: 13px;
  font-weight: 500;
  color: #303133;
  line-height: 16px;
}

:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item) {
  border: none;
}

:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item) {
  margin-top: 0;
  transition: none;
  height: 40px !important;
  line-height: 40px !important;
  width: 92px !important;
  font-size: 13px;
  font-weight: 400;
  color: #303133;
  line-height: 16px;
}

:deep(.el-tabs--border-card) {
  border: none;
  box-shadow: none;
}

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
  color: #303133;
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
  color: #909399;
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

.goods-table {
  border: 1px solid #ebeef5;
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
  color: #303133;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.goods-sku {
  margin: 2px 0 0;
  font-size: 12px;
  color: #909399;
}

.card-key-wrapper {
  display: flex;
  align-items: center;
}
.card-key {
  font-family: monospace;
  font-size: 12px;
  color: #303133;
  margin-right: 8px;
}

.form-img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  margin-right: 8px;
  cursor: pointer;
}

.tl-section {
  padding: 16px 0;
}
:deep(.el-timeline-item__content .tl-title) {
  font-size: 13px;
  font-weight: 500;
  color: #303133;
}
:deep(.el-timeline-item__content .tl-desc) {
  margin-left: 8px;
  font-size: 12px;
  color: #909399;
}

.empty-tip {
  padding: 40px;
}
.empty-tips {
  padding: 20px 0;
  color: #909399;
  font-size: 13px;
}
</style>
