<template>
  <el-card :bordered="false" shadow="never" class="mt16" :body-style="{ padding: '0 0 20px' }">
    <!-- 状态选项卡 -->
    <el-tabs v-model="currentTab" @tab-change="onTabClick">
      <el-tab-pane label="全部" name=""></el-tab-pane>
      <el-tab-pane :label="getTabLabel('待支付', 'pending_payment')" name="pending_payment"></el-tab-pane>
      <el-tab-pane :label="getTabLabel('待发货', 'pending_shipped')" name="pending_shipped"></el-tab-pane>
      <el-tab-pane :label="getTabLabel('待收货', 'pending_receive')" name="pending_receive"></el-tab-pane>
      <el-tab-pane :label="getTabLabel('待评价', 'pending_review')" name="pending_review"></el-tab-pane>
      <el-tab-pane :label="getTabLabel('已完成', 'completed')" name="completed"></el-tab-pane>
      <el-tab-pane :label="getTabLabel('退换/售后', 'after_sale')" name="after_sale"></el-tab-pane>
      <el-tab-pane :label="getTabLabel('已删除', 'deleted')" name="deleted"></el-tab-pane>
    </el-tabs>

    <!-- 工具栏 -->
    <div class="toolbar">
      <el-button v-db-click type="primary" @click="emit('batch-shipment')">批量发货</el-button>
      <el-button v-db-click @click="emit('batch-delete')">批量删除</el-button>
      <el-button v-db-click @click="exportList">订单导出</el-button>
    </div>

    <!-- 订单列表 -->
    <el-table
      :data="tableData"
      v-loading="loading"
      stripe
      empty-text="暂无数据"
      class="orderData"
      @select="onSelectRow"
      @select-all="onSelectAll"
    >
      <el-table-column type="expand" width="50">
        <template #default="scope">
          <OrderExpand :row="scope.row" />
        </template>
      </el-table-column>
      <el-table-column type="selection" width="50" />

      <el-table-column label="订单号" width="180">
        <template #default="scope">
          <div>{{ scope.row.order_no }}</div>
          <div style="font-size: 11px; color: var(--prev-color-text-secondary)">
            [{{ getShippingTypeLabel(scope.row) }}]
          </div>
          <span v-if="scope.row.is_del === 1" style="color: #ed4014; font-size: 12px">用户已删除</span>
          <span v-if="scope.row.is_cancel === 1 && scope.row.is_del === 0" style="color: #ed4014; font-size: 12px"
            >用户已取消</span
          >
        </template>
      </el-table-column>
      <el-table-column label="商品信息" min-width="240">
        <template #default="scope">
          <div class="tab">
            <img v-lazy="scope.row.image" />
            <el-tooltip placement="top" :open-delay="300">
              <template #content
                ><div>
                  <div>
                    <span>商品名称：</span><span>{{ scope.row.title }}</span>
                  </div>
                  <div v-if="scope.row.subtitle">
                    <span>副标题：</span><span>{{ scope.row.subtitle }}</span>
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
      <el-table-column label="订单金额" min-width="130">
        <template #default="scope">
          <div>
            <span>总价：</span><span class="price">¥{{ scope.row.total_price }}</span>
          </div>
          <div>
            <span>实付：</span><span class="price">¥{{ scope.row.pay_price }}</span>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="支付方式" width="90">
        <template #default="scope">
          <span>{{ scope.row.pay_type_name || scope.row.pay_type || '--' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="支付状态" width="90">
        <template #default="scope">
          <el-tag :type="isPaid(scope.row) ? 'success' : 'danger'">
            {{ isPaid(scope.row) ? '已支付' : '未支付' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="订单状态" min-width="100">
        <template #default="scope">
          <el-tag :type="getBusinessStatus(scope.row).type">
            {{ getRowStatusLabel(scope.row) }}
          </el-tag>
          <div v-if="!scope.row.is_all_refund && scope.row.refund_status === 1" class="trip">部分退款中</div>
        </template>
      </el-table-column>
      <el-table-column label="售后" width="100">
        <template #default="scope">
          <el-tooltip
            v-if="scope.row.after_sale && scope.row.after_sale.has_after_sale"
            placement="top"
            :open-delay="300"
          >
            <template #content
              ><div>
                <div>售后单数：{{ scope.row.after_sale.count }}</div>
                <div>售后类型：{{ formatAfterSaleTypes(scope.row.after_sale.types) }}</div>
                <div>售后状态：{{ formatAfterSaleStatuses(scope.row.after_sale.statuses) }}</div>
              </div></template
            >
            <el-tag :type="scope.row.after_sale.is_processing ? 'warning' : 'info'">
              {{ scope.row.after_sale.is_processing ? '售后中' : '有售后' }}
            </el-tag>
          </el-tooltip>
          <span v-else style="color: #c0c4cc">--</span>
        </template>
      </el-table-column>
      <el-table-column label="配送信息" min-width="150">
        <template #default="scope">
          <template v-if="scope.row.delivery_name">
            <div>{{ scope.row.delivery_name || '--' }}</div>
            <div style="font-size: 12px; color: var(--prev-color-text-secondary)">
              {{ scope.row.delivery_id || '--' }}
            </div>
            <a
              v-if="scope.row.delivery_type === 'express' && scope.row.delivery_id"
              v-db-click
              @click="onLogistics(scope.row)"
              style="font-size: 12px"
              >物流查询</a
            >
          </template>
          <span v-else style="color: var(--prev-color-text-secondary)">{{
            isPickupOrder(scope.row) ? '--' : '未发货'
          }}</span>
        </template>
      </el-table-column>
      <el-table-column label="创建时间" width="160">
        <template #default="scope">
          <span>{{ scope.row.add_time ? formatTime(scope.row.add_time) : '--' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" fixed="right" width="200">
        <template #default="scope">
          <a v-db-click @click="onDetail(scope.row)">详情</a>
          <el-divider direction="vertical" />
          <el-dropdown @command="(cmd) => handleCommand(scope.row, cmd)" :transfer="true">
            <span class="el-dropdown-link"> 更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
            <template #dropdown
              ><el-dropdown-menu>
                <!-- 确认付款（线下支付待支付） -->
                <el-dropdown-item command="confirmPay" v-if="canConfirmPay(scope.row)">确认付款</el-dropdown-item>
                <!-- 发货 -->
                <el-dropdown-item command="shipment" v-if="canShip(scope.row)">发货</el-dropdown-item>
                <!-- 确认收货 / 到店核销 -->
                <el-dropdown-item command="confirm" v-if="canConfirm(scope.row)">{{
                  getConfirmLabel(scope.row)
                }}</el-dropdown-item>
                <!-- 订单编辑 -->
                <el-dropdown-item command="editOrder" v-if="canEditOrder(scope.row)">订单编辑</el-dropdown-item>
                <!-- 小票打印 -->
                <el-dropdown-item command="printReceipt" v-if="canPrint(scope.row)">小票打印</el-dropdown-item>
                <!-- 配货单打印 -->
                <el-dropdown-item command="printPick" v-if="canPrint(scope.row)">配货单打印</el-dropdown-item>
                <!-- 修改地址 -->
                <el-dropdown-item command="editAddress" v-if="canEditAddress(scope.row)">修改地址</el-dropdown-item>
                <!-- 订单备注 -->
                <el-dropdown-item command="remark">订单备注</el-dropdown-item>
                <!-- 立即退款 -->
                <el-dropdown-item command="refund" v-if="canRefund(scope.row)">立即退款</el-dropdown-item>
                <!-- 配送信息 -->
                <el-dropdown-item command="deliveryInfo" v-if="canShowDeliveryInfo(scope.row)"
                  >配送信息</el-dropdown-item
                >
                <!-- 删除订单 -->
                <el-dropdown-item command="delete" v-if="canDelete(scope.row)">删除订单</el-dropdown-item>
              </el-dropdown-menu></template
            >
          </el-dropdown>
        </template>
      </el-table-column>
    </el-table>

    <!-- 分页 -->
    <div class="acea-row row-right page" v-if="total">
      <pagination :total="total" v-model:page="page.page" v-model:limit="page.limit" @pagination="onPageChange" />
    </div>
  </el-card>
</template>

<script setup>
import { ref, reactive } from 'vue';
import OrderExpand from './OrderExpand';

defineOptions({ name: 'OrderTable' });

const props = defineProps({
  /** 表格数据 */
  tableData: {
    type: Array,
    default: () => [],
  },
  /** 总条数 */
  total: {
    type: Number,
    default: 0,
  },
  /** 加载状态 */
  loading: {
    type: Boolean,
    default: false,
  },
  /** 统计信息（用于tab显示数量） */
  statData: {
    type: Object,
    default: () => ({}),
  },
  /** 搜索参数 */
  searchParams: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits([
  'tab-change',
  'batch-shipment',
  'batch-delete',
  'detail',
  'confirm-pay',
  'shipment',
  'confirm',
  'edit-order',
  'print-receipt',
  'print-pick',
  'edit-address',
  'remark',
  'refund',
  'delivery-info',
  'record',
  'delete',
  'logistics',
  'export',
  'page-change',
]);

const currentTab = ref('');
const page = reactive({
  page: 1,
  limit: 15,
});
const selectedIds = ref([]);

/**
 * 部分退款和全额退款均发生在支付之后，支付状态列仍显示已支付。
 */
function isPaid(row) {
  return [1, 2, 3].includes(Number(row.pay_status));
}

/* ===== Tab ===== */

/**
 * 状态选项卡切换
 */
function onTabClick() {
  page.page = 1;
  emit('tab-change', currentTab.value);
}

/**
 * 获取tab标签文字（含数量）
 */
function getTabLabel(text, statusCode) {
  const counts = props.statData.business_status_counts;
  const count = counts && counts[statusCode];
  return count ? `${text}(${count})` : text;
}

/**
 * 格式化售后类型为中文
 */
function formatAfterSaleTypes(types) {
  const map = {
    refund_only: '仅退款',
    return_refund: '退货退款',
    exchange: '换货',
    resend: '补发',
    repair: '维修',
  };
  return (types || []).map((t) => map[t] || t).join('、');
}

/**
 * 格式化售后状态为中文
 */
function formatAfterSaleStatuses(statuses) {
  const map = {
    pending_audit: '待审核',
    audit_passed: '审核通过',
    refunding: '退款中',
    pending_return: '待退货',
    receiving: '待收货',
    resending: '补发中',
    repairing: '维修中',
    repair_complete: '维修完成',
    refunded: '已退款',
    completed: '已完成',
    canceled: '已取消',
    rejected: '已拒绝',
  };
  return (statuses || []).map((s) => map[s] || s).join('、');
}

/* ===== 选中行 ===== */

function onSelectRow(selection) {
  selectedIds.value = selection.map((row) => row.id);
}

function onSelectAll(selection) {
  selectedIds.value = selection.map((row) => row.id);
}

/* ===== 按钮可见性 ===== */

/**
 * 订单备注和操作记录应对所有订单可用，其他按钮按条件显示
 */
function showMoreBtn() {
  return true;
}

/**
 * 判断是否可确认付款（线下支付待支付）
 */
function canConfirmPay(row) {
  return getBusinessStatus(row).code === 'pending_payment' && row.pay_type === 'offline';
}

/**
 * 判断是否可编辑订单（待支付且未支付）
 */
function canEditOrder(row) {
  return getBusinessStatus(row).code === 'pending_payment' && row.pay_status === 0;
}

/**
 * 判断是否可发货（待发货状态）
 */
function canShip(row) {
  return getBusinessStatus(row).code === 'pending_shipped' && row.delivery_type !== 'fictitious';
}

/**
 * 判断是否可确认收货 / 到店核销
 */
function canConfirm(row) {
  const statusCode = getBusinessStatus(row).code;
  return statusCode === 'pending_receive' || statusCode === 'pending_pickup';
}

/**
 * 判断是否到店自提订单
 */
function isPickupOrder(row) {
  return row?.extra_info?.delivery_type === 'pickup' || row?.shipping_type === 2;
}

/**
 * 配送方式文案（shipping_type：1快递 2自提 3上门）
 */
function getShippingTypeLabel(row) {
  const type = Number(row?.shipping_type ?? 1);
  return type === 2 ? '到店自提' : type === 3 ? '上门服务' : '快递配送';
}

function getConfirmLabel(row) {
  return isPickupOrder(row) ? '到店核销' : '确认收货';
}

/**
 * 判断是否可显示配送信息（已发货且有物流单号）
 */
function canShowDeliveryInfo(row) {
  return row.delivery_time > 0 && row.delivery_name && row.delivery_id;
}

/**
 * 判断是否可删除（仅未支付/已取消可删除）
 */
function canDelete(row) {
  const statusCode = getBusinessStatus(row).code;
  return statusCode === 'pending_payment' || statusCode === 'canceled';
}

/**
 * 判断是否可修改地址（待发货状态）
 */
function canEditAddress(row) {
  return getBusinessStatus(row).code === 'pending_shipped';
}

/**
 * 判断是否可退款（已支付且未全额退款）
 */
function canRefund(row) {
  return row.pay_status === 1 && row.refund_status !== 2;
}

/**
 * 判断是否可打印（已支付订单）
 */
function canPrint(row) {
  return row.pay_status === 1;
}

/* ===== 操作命令 ===== */

/**
 * 统一操作命令处理
 */
function handleCommand(row, command) {
  switch (command) {
    case 'confirmPay':
      emit('confirm-pay', row);
      break;
    case 'shipment':
      emit('shipment', row);
      break;
    case 'confirm':
      emit('confirm', row);
      break;
    case 'editOrder':
      emit('edit-order', row);
      break;
    case 'printReceipt':
      emit('print-receipt', row);
      break;
    case 'printPick':
      emit('print-pick', row);
      break;
    case 'editAddress':
      emit('edit-address', row);
      break;
    case 'remark':
      emit('remark', row);
      break;
    case 'refund':
      emit('refund', row);
      break;
    case 'deliveryInfo':
      emit('delivery-info', row);
      break;
    case 'record':
      emit('record', row);
      break;
    case 'delete':
      emit('delete', row);
      break;
  }
}

/**
 * 详情
 */
function onDetail(row) {
  emit('detail', row);
}

/**
 * 物流查询
 */
function onLogistics(row) {
  emit('logistics', row);
}

/* ===== 导出 ===== */

/**
 * 导出订单列表
 */
function exportList() {
  emit('export');
}

/* ===== 分页 ===== */

function onPageChange() {
  emit('page-change', { ...page });
}

/* ===== 工具方法 ===== */

function getBusinessStatus(row) {
  if (row.is_del === 1) {
    return { code: 'deleted', text: '已删除', type: 'danger' };
  }

  if (row.status_code === 'pending_payment') {
    return { code: 'pending_payment', text: '待支付', type: 'warning' };
  }

  if (row.status_code === 'canceled') {
    return { code: 'canceled', text: '已取消', type: 'danger' };
  }

  if (row.status_code === 'after_sale' || row.status_code === 'refunding') {
    return { code: 'after_sale', text: '退换/售后', type: 'info' };
  }

  if (row.status_code === 'refunded') {
    return { code: 'refunded', text: '已退款', type: 'info' };
  }

  if (row.status_code === 'processing') {
    // 到店自提订单显示待到店
    if (row.extra_info?.delivery_type === 'pickup') {
      return { code: 'pending_pickup', text: '待到店', type: 'warning' };
    }
    if (!row.delivery_time || row.delivery_time === 0) {
      return { code: 'pending_shipped', text: '待发货', type: 'danger' };
    } else {
      return { code: 'pending_receive', text: '待收货', type: 'primary' };
    }
  }

  if (row.status_code === 'pending_review') {
    return { code: 'pending_review', text: '待评价', type: 'success' };
  }

  if (row.status_code === 'completed') {
    return { code: 'completed', text: '已完成', type: 'success' };
  }

  return { code: row.status_code, text: row.status_code || '未知', type: 'info' };
}

function getStatusTagType(code) {
  const map = {
    pending_payment: 'warning',
    pending_shipped: 'danger',
    pending_receive: 'primary',
    pending_pickup: 'warning',
    pending_review: 'success',
    completed: 'success',
    canceled: 'danger',
    after_sale: 'info',
    refunded: 'info',
    deleted: 'danger',
  };
  return map[code] || 'info';
}

function getStatusLabel(code) {
  const map = {
    pending_payment: '待支付',
    pending_shipped: '待发货',
    pending_receive: '待收货',
    pending_pickup: '待到店',
    pending_review: '待评价',
    completed: '已完成',
    canceled: '已取消',
    after_sale: '退换/售后',
    refunded: '已退款',
    deleted: '已删除',
  };
  return map[code] || code || '-';
}

/**
 * 获取行的状态标签，使用业务状态
 */
function getRowStatusLabel(row) {
  const businessStatus = getBusinessStatus(row);
  return businessStatus.text;
}

function formatTime(ts) {
  if (!ts) return '--';
  const d = new Date(ts * 1000);
  const y = d.getFullYear();
  const m = (d.getMonth() + 1).toString().padStart(2, '0');
  const day = d.getDate().toString().padStart(2, '0');
  const h = d.getHours().toString().padStart(2, '0');
  const min = d.getMinutes().toString().padStart(2, '0');
  const s = d.getSeconds().toString().padStart(2, '0');
  return `${y}/${m}/${day} ${h}:${min}:${s}`;
}

defineExpose({ selectedIds, page });
</script>

<style lang="scss" scoped>
.mt16 {
  margin-top: 16px;
}

:deep(.el-tabs__header) {
  margin: 0;
  padding: 0 20px;
}

.toolbar {
  padding: 10px 20px;
  border-bottom: 1px solid var(--prev-border-color-lighter);
}

.orderData {
  .tab {
    display: flex;
    align-items: center;

    img {
      width: 36px;
      height: 36px;
      margin-right: 10px;
      flex-shrink: 0;
    }
  }

  .w-250 {
    max-width: 250px;
  }
}

.price {
  color: #ed4014;
  font-weight: bold;
}

.trip {
  color: #ed4014;
  font-size: 12px;
  margin-top: 2px;
}

:deep(.el-dropdown-link) {
  cursor: pointer;
  color: var(--prev-color-primary);
  font-size: 13px;
}

:deep(.el-dropdown-menu__item) {
  font-size: 13px;
}
</style>
