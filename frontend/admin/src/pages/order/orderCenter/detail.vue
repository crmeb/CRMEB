<template>
  <div>
    <div v-loading="detailLoading">
      <div v-if="detailData" class="order-detail">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderIcon" alt="" />
            <div class="text">
              <div class="title">普通订单</div>
              <div>订单号：{{ detailData.order_no }}</div>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">订单状态</div>
              <div>
                <el-tag :type="getStatusClass(detailData)">{{ getStatusLabel(detailData) }}</el-tag>
                <span
                  v-if="Number(detailData.is_del) !== 1 && detailData.refund_status == 1"
                  style="color: #ed4014; margin-left: 4px"
                >
                  退款中
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
              <div>{{ detailData.pay_type_name || detailData.pay_type || '--' }}</div>
            </li>
            <li class="item">
              <div class="title">支付时间</div>
              <div>{{ detailData.pay_time ? formatDate(detailData.pay_time * 1000) : '--' }}</div>
            </li>
          </ul>
        </div>
        <el-tabs type="border-card" v-model="activeTab">
          <el-tab-pane label="订单信息" name="detail">
            <DetailOrderInfo :data="detailData" @openLogistics="openLogistics" />
          </el-tab-pane>
          <el-tab-pane label="订单进度" name="timeline">
            <DetailOrderList :timeline="detailData.timeline" />
          </el-tab-pane>
        </el-tabs>
      </div>
      <div v-else-if="!detailLoading" class="empty-tip">
        <el-empty description="暂无订单数据"></el-empty>
      </div>
    </div>
    <DetailLogistics v-model:visible="logisticsVisible" :data="detailData" :logisticsList="logisticsData" />
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import __asset_order_icon_0 from '@/assets/images/order_icon.png';

import { orderCenterDetail } from '@/api/orderCenter';
import { getExpress } from '@/api/order';
import DetailOrderInfo from './components/DetailOrderInfo';
import DetailOrderList from './components/DetailOrderList';
import DetailLogistics from './components/DetailLogistics';

defineOptions({ name: 'orderCenterDetail' });

// 传入 id 时作为抽屉内嵌组件使用；不传时为路由整页（取 route.params.id）
const props = defineProps({ id: { type: [String, Number], default: '' } });

const route = useRoute();

const detailData = ref(null);
const detailLoading = ref(false);
const activeTab = ref('detail');
const orderIcon = __asset_order_icon_0;
const logisticsVisible = ref(false);
const logisticsData = ref([]);

function loadDetail() {
  const id = props.id || route.params.id;
  if (!id) return;
  detailLoading.value = true;
  orderCenterDetail(id)
    .then((res) => {
      detailData.value = res.data;
      detailLoading.value = false;
    })
    .catch((res) => {
      detailLoading.value = false;
      ElMessage.error(res.msg);
    });
}

function openLogistics() {
  logisticsVisible.value = true;
  getLogisticsData();
}

function getLogisticsData() {
  if (!detailData.value) return;
  getExpress(detailData.value.order_no)
    .then((res) => {
      logisticsData.value = res.data.result || [];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

const CENTER_STATUS_CODES = [
  'pending_payment',
  'processing',
  'pending_review',
  'completed',
  'canceled',
  'after_sale',
  'refunding',
  'refunded',
  'deleted',
];

function getBusinessStatusText(detail) {
  if (!detail || typeof detail === 'string') return '';
  const bs = detail.business_status;
  if (!bs || typeof bs !== 'object') return '';
  const text = String(bs.label || bs.text || '').trim();
  if (!text) return '';
  const code = String(bs.code || '');
  const genericTexts = [
    '待支付',
    '待处理',
    '进行中',
    '待评价',
    '已完成',
    '已取消',
    '售后中',
    '退款中',
    '已退款',
    '已删除',
  ];
  if (!genericTexts.includes(text)) return text;
  if (code && !CENTER_STATUS_CODES.includes(code)) return text;
  return '';
}

/** 有业务子状态（待发货/待收货等）时只展示这一项，否则按中台 status_code */
function getStatusLabel(detail) {
  if (detail && Number(detail.is_del) === 1) return '已删除';
  const businessText = getBusinessStatusText(detail);
  if (businessText) return businessText;
  const code = typeof detail === 'string' ? detail : detail?.status_code;
  const map = {
    pending_payment: '待支付',
    processing: '待处理',
    pending_review: '待评价',
    completed: '已完成',
    canceled: '已取消',
    after_sale: '售后中',
    refunding: '退款中',
    refunded: '已退款',
    deleted: '已删除',
  };
  return map[code] || detail?.status_text || code || '--';
}

/* 与列表 getStatusClass 同口径 */
function getStatusClass(detail) {
  if (detail && Number(detail.is_del) === 1) return 'danger';
  if (getBusinessStatusText(detail)) return 'primary';
  const code = typeof detail === 'string' ? detail : detail?.status_code;
  const map = {
    pending_payment: 'warning',
    processing: 'primary',
    pending_review: 'success',
    completed: 'success',
    canceled: 'danger',
    after_sale: 'warning',
    refunding: 'warning',
    refunded: 'info',
    deleted: 'danger',
  };
  return map[code] || 'info';
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
  loadDetail();
});

watch(
  () => props.id,
  (val) => {
    if (val) loadDetail();
  },
);

defineExpose({ loadDetail });
</script>

<style lang="scss" scoped>
:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item.is-active) {
  border-bottom: none;
}

:deep(.el-tabs__item) {
  height: 40px !important;
  line-height: 40px !important;
}

.business-status {
  margin-top: 8px;
  font-size: 12px;
  color: #999;
  line-height: 1.2;
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
      color: var(--prev-color-text-primary);

      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        color: var(--prev-color-text-regular);
      }
    }
  }

  .list {
    display: flex;
    flex-wrap: wrap;
    row-gap: 12px;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;

    .item {
      flex: 1 1 auto;
      min-width: 0;
      padding-right: 16px;
      font-size: 14px;
      line-height: 14px;
      color: var(--prev-color-text-regular);

      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: var(--prev-color-text-primary);
      }

      .value1 {
        color: #f56022;
        font-weight: bold;
      }
    }
  }
}

:deep(.el-tabs--border-card > .el-tabs__content) {
  /* 独立路由详情页：与 .head 的 30px 左右缩进对齐 */
  padding: 0 30px;
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
  color: var(--prev-color-text-regular);
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
  color: var(--prev-color-text-regular);
  line-height: 16px;
}

:deep(.el-tabs--border-card) {
  border: none;
  box-shadow: none;
}

.empty-tip {
  padding: 40px;
}
</style>
