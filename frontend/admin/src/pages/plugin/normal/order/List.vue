<template>
  <div class="normal-order-list-wrap">
    <div class="normal-order-list">
      <!-- 搜索栏组件 -->
      <OrderSearch @search="onSearch" @reset="onReset" />

      <!-- 统计卡片组件 -->
      <div class="mt16">
        <OrderStatistics :data="statData" />
      </div>

      <!-- 订单表格组件（tabs + table + 分页 + 操作按钮） -->
      <OrderTable
        ref="tableRef"
        :table-data="list"
        :total="total"
        :loading="loading"
        :stat-data="statData"
        @tab-change="onTabChange"
        @detail="handleDetail"
        @confirm-pay="handleConfirmPay"
        @shipment="openShipment"
        @confirm="handleConfirm"
        @edit-order="handleEditOrder"
        @print-receipt="handlePrintReceipt"
        @print-pick="handlePrintPick"
        @edit-address="openEditAddress"
        @remark="editRemark"
        @refund="openRefund"
        @delivery-info="handleDeliveryInfo"
        @record="openRecord"
        @delete="handleDelete"
        @batch-shipment="openBatchShipment"
        @batch-delete="handleBatchDelete"
        @logistics="openLogistics"
        @export="handleExport"
        @page-change="onPageChange"
      />

      <!-- 发货弹窗组件（不可加 v-if：挂载时 visible 已为 true 会因 EP 弹窗非立即监听而无法打开） -->
      <OrderSend
        :visible="sendVisible"
        :order-no="currentOrderNo"
        @success="onShipmentSuccess"
        @close="sendVisible = false"
      />

      <!-- 备注弹窗组件（同上，禁用 v-if 挂载式写法） -->
      <OrderRemark
        :visible="remarkVisible"
        :order-id="remarkOrderId"
        :remark="remarkContent"
        @success="onRemarkSuccess"
        @close="remarkVisible = false"
      />

      <!-- 修改地址弹窗组件（同上，禁用 v-if 挂载式写法） -->
      <OrderAddress
        :visible="addressVisible"
        :order-no="addressOrderNo"
        :contact-name="addressName"
        :contact-phone="addressPhone"
        :contact-address="addressDetail"
        @success="onAddressSuccess"
        @close="addressVisible = false"
      />

      <!-- 退款弹窗组件（同上，禁用 v-if 挂载式写法） -->
      <OrderRefund
        :visible="refundVisible"
        :order-no="refundOrderNo"
        @success="onRefundSuccess"
        @close="refundVisible = false"
      />

      <!-- 操作记录弹窗组件（drawer） -->
      <OrderRecord
        :visible="recordVisible"
        :order-no="recordOrderNo"
        :order-id="recordOrderId"
        @close="recordVisible = false"
      />

      <!-- 物流查询对话框 -->
      <el-dialog v-model="logisticsVisible" title="物流查询" width="500px" @close="logisticsVisible = false">
        <div v-if="logisticsOrder" class="logistics-box">
          <div class="logistics-header">
            <el-image :src="logisticsIcon" class="logistics-icon" />
            <div class="logistics-info">
              <p>物流公司：{{ logisticsOrder.delivery_name || '--' }}</p>
              <p>物流单号：{{ logisticsOrder.delivery_id || '--' }}</p>
            </div>
          </div>
          <el-timeline>
            <el-timeline-item v-for="(item, i) in logisticsData" :key="i" :timestamp="item.time" placement="top">
              <p class="logistics-status">{{ item.status }}</p>
            </el-timeline-item>
          </el-timeline>
          <el-empty v-if="!logisticsData.length" description="暂无物流信息" />
        </div>
      </el-dialog>

      <!-- 配送信息编辑对话框（从参考页面 orderShipment 简化） -->
      <el-dialog v-model="deliveryInfoVisible" title="配送信息" width="500px">
        <el-form v-if="deliveryInfoData" label-width="120px">
          <el-form-item label="配送方式：">
            <span>{{
              deliveryInfoData.delivery_type === 'express'
                ? '快递'
                : deliveryInfoData.delivery_type === 'send'
                ? '送货'
                : '虚拟发货'
            }}</span>
          </el-form-item>
          <el-form-item label="配送人：">
            <span>{{ deliveryInfoData.delivery_name || '--' }}</span>
          </el-form-item>
          <el-form-item label="配送单号：">
            <span>{{ deliveryInfoData.delivery_id || '--' }}</span>
          </el-form-item>
        </el-form>
      </el-dialog>

      <!-- 批量发货对话框 -->
      <el-dialog v-model="batchShipmentModal" title="批量发货" width="560px">
        <div>
          <p class="mb10">请上传 Excel 文件（<b>xls/xlsx</b>），格式如下：</p>
          <el-table :data="templateData" border stripe class="mb10">
            <el-table-column label="订单号" width="200">
              <template>CP1234567890</template>
            </el-table-column>
            <el-table-column label="快递公司" width="150">
              <template>顺丰速运</template>
            </el-table-column>
            <el-table-column label="快递单号" width="150">
              <template>SF1234567890</template>
            </el-table-column>
          </el-table>
          <p class="mb10" style="color: var(--prev-color-text-secondary); font-size: 12px">
            第一行为表头，从第二行开始为数据
          </p>
          <el-upload
            ref="upload"
            :action="adminApi + 'normal/order/batch_shipment'"
            :headers="headers"
            :on-success="onBatchUploadSuccess"
            :on-error="onBatchUploadError"
            :before-upload="onBeforeUpload"
            accept=".xls,.xlsx"
            :show-file-list="false"
          >
            <template #trigger><el-button type="primary">选取文件上传</el-button></template>
          </el-upload>
        </div>
        <div v-if="batchResult" class="batch-result mt10">
          <p>{{ batchResult.msg }}</p>
          <p v-if="batchResult.data && batchResult.data.errors && batchResult.data.errors.length">
            <a v-db-click @click="showBatchErrors = !showBatchErrors">
              {{ showBatchErrors ? '收起' : '查看' }}失败详情（{{ batchResult.data.errors.length }}条）
            </a>
          </p>
          <ul v-if="showBatchErrors" style="color: #ed4014; font-size: 12px">
            <li v-for="(err, idx) in batchResult.data.errors" :key="idx">{{ err }}</li>
          </ul>
        </div>
      </el-dialog>
    </div>

    <!-- 订单编辑弹窗 -->
    <OrderEdit ref="editRef" @saved="onEditSuccess" />

    <!-- 订单详情抽屉 -->
    <el-drawer
      v-model="detailDrawerVisible"
      title="订单详情"
      size="720px"
      class="order-detail-drawer"
      @opened="onDetailDrawerOpened"
    >
      <OrderDetail :orderNo="detailOrderNo" ref="detailRef" />
    </el-drawer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import logisticsIcon from '@/assets/images/expressi.jpg';

import request from '@/libs/request';
import setting from '@/setting';
import exportExcel from '@/utils/newToExcel.js';
import {
  getOrderList,
  getOrderStatistics,
  getPrintReceipt,
  getPrintPickList,
  getOrderExpress,
  exportOrderList as exportNormalOrder,
} from '../normalOrder';

import OrderStatistics from './components/OrderStatistics';
import OrderSearch from './components/OrderSearch';
import OrderTable from './components/OrderTable';
import OrderSend from './handle/OrderSend';
import OrderRemark from './handle/OrderRemark';
import OrderAddress from './handle/OrderAddress';
import OrderRefund from './handle/OrderRefund';
import OrderRecord from './handle/OrderRecord';
import OrderEdit from './Edit.vue';
import OrderDetail from './Detail.vue';

defineOptions({ name: 'normalOrderList' });

const routePre = setting.routePre;

const tableRef = ref(null);
const editRef = ref(null);
const detailRef = ref(null);

// 列表数据
const loading = ref(false);
const list = ref([]);
const total = ref(0);
const searchParams = ref({});
const statusTab = ref('');
const statData = ref({});

// 订单编辑弹窗
const editVisible = ref(false);
const editOrderNo = ref('');

// 订单详情抽屉
const detailDrawerVisible = ref(false);
const detailOrderNo = ref('');

// 发货弹窗
const sendVisible = ref(false);
const currentOrderNo = ref('');

// 备注弹窗
const remarkVisible = ref(false);
const remarkOrderId = ref(0);
const remarkContent = ref('');

// 地址弹窗
const addressVisible = ref(false);
const addressOrderNo = ref('');
const addressName = ref('');
const addressPhone = ref('');
const addressDetail = ref('');

// 退款弹窗
const refundVisible = ref(false);
const refundOrderNo = ref('');

// 操作记录弹窗
const recordVisible = ref(false);
const recordOrderNo = ref('');
const recordOrderId = ref(0);

// 物流查询
const logisticsVisible = ref(false);
const logisticsOrder = ref(null);
const logisticsData = ref([]);

// 配送信息
const deliveryInfoVisible = ref(false);
const deliveryInfoData = ref(null);

// 编辑订单
const editOrderVisible = ref(false);
const editOrderData = ref(null);

// 批量发货
const batchShipmentModal = ref(false);
const batchResult = ref(null);
const showBatchErrors = ref(false);
const templateData = [{}];

// 批量删除
const selectedIds = ref([]);

const adminApi = computed(() => {
  return import.meta.env.VITE_APP_API_URL + '/adminapi/';
});
const headers = computed(() => {
  return { 'Authori-zation': 'Bearer ' + localStorage.getItem('token') };
});

/* ===== 数据加载 ===== */

/**
 * 获取订单列表
 */
function getList() {
  loading.value = true;
  const params = {
    ...searchParams.value,
  };
  if (statusTab.value) {
    params.business_status = statusTab.value;
  }
  getOrderList(params)
    .then((res) => {
      list.value = res.data.list || [];
      total.value = res.data.total || 0;
    })
    .catch((res) => {
      ElMessage.error(res.msg || '加载订单列表失败');
    })
    .finally(() => {
      loading.value = false;
    });
}

/**
 * 获取统计信息
 * 传入当前搜索参数，使Tab统计数据随搜索条件变化
 */
function getStatistics() {
  const params = {
    order_no: searchParams.value.order_no || '',
    keyword: searchParams.value.keyword || '',
    search_field: searchParams.value.search_field || 'all',
    date_range: searchParams.value.date_range || '',
    pay_type: searchParams.value.pay_type || '',
  };
  getOrderStatistics(params)
    .then((res) => {
      statData.value = res.data || {};
    })
    .catch(() => {});
}

/* ===== 搜索 ===== */

/**
 * 搜索事件
 */
function onSearch(params) {
  searchParams.value = params;
  getList();
  getStatistics();
}

/**
 * 重置事件
 */
function onReset() {
  searchParams.value = {};
  statusTab.value = '';
  // Tab 选中态在 OrderTable 组件内部，需同步重置，否则列表已查全部而 Tab 仍停在原状态
  tableRef.value?.resetTab();
  getList();
  getStatistics();
}

/* ===== Tab切换 ===== */

function onTabChange(tab) {
  statusTab.value = tab;
  // 切换状态后回到第一页（OrderTable 内部已把分页归 1，这里同步请求参数）
  searchParams.value.page = 1;
  getList();
  getStatistics();
}

/* ===== 分页 ===== */

function onPageChange(page) {
  searchParams.value.page = page.page;
  searchParams.value.limit = page.limit;
  getList();
}

/* ===== 路由跳转 ===== */

function handleDetail(row) {
  detailOrderNo.value = row.order_no;
  detailDrawerVisible.value = true;
}

function onDetailDrawerOpened() {
  // 抽屉打开后触发 Detail 组件加载数据
  nextTick(() => {
    if (detailRef.value) {
      detailRef.value.loadDetail();
    }
  });
}

/* ===== 发货 ===== */

function openShipment(row) {
  currentOrderNo.value = row.order_no;
  sendVisible.value = true;
}

function onShipmentSuccess() {
  sendVisible.value = false;
  getList();
  getStatistics();
}

function onEditSuccess() {
  getList();
  getStatistics();
}

/* ===== 确认收货 ===== */

function handleConfirm(row) {
  const isPickup = row?.extra_info?.delivery_type === 'pickup' || row?.shipping_type === 2;
  const actionText = isPickup ? '到店核销' : '确认收货';
  ElMessageBox({
    title: '提示',
    message: `确定要${actionText}吗？`,
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
  })
    .then(() => {
      request
        .post('normal/order/confirm_receipt', { order_no: row.order_no })
        .then((res) => {
          ElMessage.success(res.msg || `${actionText}成功`);
          getList();
          getStatistics();
        })
        .catch((res) => {
          ElMessage.error(res.msg || `${actionText}失败`);
        });
    })
    .catch(() => {});
}

/* ===== 备注 ===== */

function editRemark(row) {
  remarkOrderId.value = row.id;
  remarkContent.value = row.remark || '';
  remarkVisible.value = true;
}

function onRemarkSuccess() {
  remarkVisible.value = false;
  getList();
}

/* ===== 修改地址 ===== */

function openEditAddress(row) {
  addressOrderNo.value = row.order_no;
  addressName.value = row.contact_name || row.real_name || '';
  addressPhone.value = row.contact_phone || '';
  addressDetail.value = row.user_address || '';
  addressVisible.value = true;
}

function onAddressSuccess() {
  addressVisible.value = false;
  getList();
}

/* ===== 退款 ===== */

function openRefund(row) {
  refundOrderNo.value = row.order_no;
  refundVisible.value = true;
}

function onRefundSuccess() {
  refundVisible.value = false;
  getList();
  getStatistics();
}

/* ===== 打印 ===== */

function handlePrintReceipt(row) {
  getPrintReceipt(row.order_no)
    .then((res) => {
      openPrintWindow(res.data, 'receipt');
    })
    .catch((res) => {
      ElMessage.error(res.msg || '获取小票数据失败');
    });
}

function handlePrintPick(row) {
  getPrintPickList(row.order_no)
    .then((res) => {
      openPrintWindow(res.data, 'picklist');
    })
    .catch((res) => {
      ElMessage.error(res.msg || '获取配货单数据失败');
    });
}

function openPrintWindow(data, type) {
  var w = window.open('', '_blank', 'width=420,height=600,scrollbars=yes');
  if (!w) {
    ElMessage.error('浏览器阻止了弹出窗口，请允许本网站弹出窗口');
    return;
  }
  var html =
    '<html><head><meta charset="utf-8"><title>' + (type === 'receipt' ? '小票打印' : '配货单打印') + '</title><style>';
  html +=
    'body{font-family:"Microsoft YaHei",sans-serif;padding:0;margin:20px;font-size:14px;color: var(--prev-color-text-regular)}';
  html += 'h2{text-align:center;margin-bottom:15px}';
  html += 'table{width:100%;border-collapse:collapse;margin-bottom:15px}';
  html += 'table,th,td{border:1px solid #ddd}';
  html += 'th,td{padding:8px;text-align:left}';
  html += 'th{background:#f5f7fa}';
  html += '.info{line-height:1.8;margin-bottom:10px}';
  html += '.info .label{color:#999}';
  html += '.total{text-align:right;font-size:16px;font-weight:bold;margin-top:10px}';
  html += '.no-price{color:#999;font-size:12px;text-align:center;margin-top:20px}';
  html += '@media print{body{margin:10px;font-size:12px}button{display:none}}';
  html += '</style></head><body>';

  if (type === 'receipt') {
    html += '<h2>收银小票</h2>';
    html += '<div class="info">';
    html += '<div><span class="label">订单号：</span>' + data.order_no + '</div>';
    html += '<div><span class="label">商家：</span>' + ((data.store && data.store.name) || '') + '</div>';
    html += '<div><span class="label">商家电话：</span>' + ((data.store && data.store.phone) || '') + '</div>';
    html += '<div><span class="label">顾客：</span>' + data.contact_name + '</div>';
    html += '<div><span class="label">顾客电话：</span>' + data.contact_phone + '</div>';
    html += '</div>';
    html += '<table><thead><tr><th>商品</th><th>规格</th><th>单价</th><th>数量</th><th>小计</th></tr></thead><tbody>';
    if (data.items) {
      for (var i = 0; i < data.items.length; i++) {
        var item = data.items[i];
        html +=
          '<tr><td>' +
          item.name +
          '</td><td>' +
          (item.sku || '-') +
          '</td><td>¥' +
          item.price +
          '</td><td>' +
          item.quantity +
          '</td><td>¥' +
          item.subtotal +
          '</td></tr>';
      }
    }
    html += '</tbody></table>';
    html += '<div class="total">实付金额：¥' + data.pay_price + '</div>';
    if (data.pay_postage && parseFloat(data.pay_postage) > 0) {
      html += '<div style="text-align:right">含运费：¥' + data.pay_postage + '</div>';
    }
  } else {
    html += '<h2>配货单</h2>';
    html += '<div class="info">';
    html += '<div><span class="label">订单号：</span>' + data.order_no + '</div>';
    html += '</div>';
    html += '<table><thead><tr><th>商品名称</th><th>规格</th><th>数量</th></tr></thead><tbody>';
    if (data.items) {
      for (var j = 0; j < data.items.length; j++) {
        var pi = data.items[j];
        html += '<tr><td>' + pi.name + '</td><td>' + (pi.sku || '-') + '</td><td>' + pi.quantity + '</td></tr>';
      }
    }
    html += '</tbody></table>';
    html += '<div class="info"><span class="label">备注：</span>' + (data.remark || '无') + '</div>';
    html += '<div class="no-price">—— 配货单（无价格信息）——</div>';
  }

  html += '<div style="text-align:center;margin-top:20px">';
  html += '<button onclick="window.print()">打印</button>';
  html += ' <button onclick="window.close()">关闭</button>';
  html += '</div>';
  html += '</body></html>';
  w.document.write(html);
  w.document.close();
}

/* ===== 删除 ===== */

function handleDelete(row) {
  ElMessageBox({
    title: '提示',
    message: '确定删除该订单吗？删除后后台列表将不再显示。',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
  })
    .then(() => {
      request
        .delete('normal/order/delete/' + row.id)
        .then((res) => {
          ElMessage.success(res.msg || '删除成功');
          getList();
          getStatistics();
        })
        .catch((res) => {
          ElMessage.error(res.msg || '删除失败');
        });
    })
    .catch(() => {});
}

/* ===== 操作记录 ===== */

function openRecord(row) {
  recordOrderNo.value = row.order_no;
  recordOrderId.value = row.id;
  recordVisible.value = true;
}

/* ===== 批量发货 ===== */

function openBatchShipment() {
  batchResult.value = null;
  showBatchErrors.value = false;
  batchShipmentModal.value = true;
}

function onBatchUploadSuccess(res) {
  batchResult.value = res;
  if (res.code === 200) {
    ElMessage.success(res.msg || '批量发货完成');
    getList();
  } else {
    ElMessage.error(res.msg || '批量发货失败');
  }
}

function onBatchUploadError(err) {
  ElMessage.error('批量发货失败：' + (err.msg || '文件上传异常'));
}

function onBeforeUpload(file) {
  var suffix = file.name.split('.').pop().toLowerCase();
  if (!['xls', 'xlsx'].includes(suffix)) {
    ElMessage.error('文件格式不正确，请上传xls或xlsx格式');
    return false;
  }
  return true;
}

/* ===== 确认付款（线下支付） ===== */

function handleConfirmPay(row) {
  ElMessageBox({
    title: '提示',
    message: '确认该订单已支付（线下支付）？',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
    type: 'warning',
  })
    .then(() => {
      request
        .post('normal/order/confirm_pay', { order_no: row.order_no })
        .then((res) => {
          ElMessage.success(res.msg || '确认付款成功');
          getList();
          getStatistics();
        })
        .catch((res) => {
          ElMessage.error(res.msg || '确认付款失败');
        });
    })
    .catch(() => {});
}

/* ===== 订单编辑 ===== */

function handleEditOrder(row) {
  editRef.value.open(row.order_no);
}

/* ===== 配送信息 ===== */

function handleDeliveryInfo(row) {
  deliveryInfoData.value = {
    delivery_type: row.delivery_type || 'express',
    delivery_name: row.delivery_name || '',
    delivery_id: row.delivery_id || '',
  };
  deliveryInfoVisible.value = true;
}

/* ===== 物流查询 ===== */

function openLogistics(row) {
  logisticsOrder.value = row;
  logisticsVisible.value = true;
  getLogisticsData(row);
}

function getLogisticsData(row) {
  if (!row) return;
  getOrderExpress(row.order_no)
    .then((res) => {
      logisticsData.value = res.data.result || [];
    })
    .catch((res) => {
      ElMessage.error(res.msg || '物流查询失败');
    });
}

/* ===== 批量删除 ===== */

function handleBatchDelete() {
  const ids = tableRef.value ? tableRef.value.selectedIds : [];
  if (!ids || ids.length === 0) {
    ElMessage.error('请先选择要删除的订单');
    return;
  }
  ElMessageBox({
    title: '提示',
    message: '确定批量删除选中的订单吗？删除后后台列表将不再显示。',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
    type: 'warning',
  })
    .then(() => {
      request
        .post('normal/order/batch_delete', { ids })
        .then((res) => {
          ElMessage.success(res.msg || '批量删除成功');
          getList();
          getStatistics();
        })
        .catch((res) => {
          ElMessage.error(res.msg || '批量删除失败');
        });
    })
    .catch(() => {});
}

/* ===== 导出 ===== */

function handleExport() {
  ElMessageBox({
    title: '提示',
    message: '确定要导出订单数据吗？',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
  })
    .then(() => {
      const params = {
        page: 1,
        limit: 200,
        ...searchParams.value,
        status_code: statusTab.value === 'deleted' ? '' : statusTab.value,
        is_deleted: statusTab.value === 'deleted' ? 1 : '',
      };
      doExport(params);
    })
    .catch(() => {});
}

function doExport(params) {
  ElMessage.info('正在导出，请稍候...');
  exportNormalOrder(params)
    .then((res) => {
      const data = res.data || {};
      if (data.export && data.export.length) {
        exportExcel(
          data.header || [],
          data.fileKey || [],
          data.filename || '订单导出',
          data.export,
          data.filename || '订单导出',
        );
      } else {
        ElMessage.info('暂无数据可导出');
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg || '导出失败');
    });
}

onMounted(() => {
  getList();
  getStatistics();
});
</script>

<style lang="scss" scoped>
/* 详情抽屉 body 左右归零（全局 variables.scss 为 20px 35px）：
   内容组件 tabs 已不再叠加 padding，对齐用户详情抽屉的 20px 紧凑边距 */
:deep(.order-detail-drawer .el-drawer__body) {
  padding: 20px 0 !important;
}
.normal-order-list {
  .mt16 {
    margin-top: 16px;
  }

  .mb10 {
    margin-bottom: 10px;
  }

  .mt10 {
    margin-top: 10px;
  }

  .batch-result {
    padding: 10px;
    background: #f5f7fa;
    border-radius: 4px;
    font-size: 14px;
  }

  .logistics-box {
    .logistics-header {
      display: flex;
      align-items: center;
      padding: 10px 0 20px;
      border-bottom: 1px solid var(--prev-border-color-lighter);
      margin-bottom: 20px;

      .logistics-icon {
        width: 45px;
        height: 45px;
        margin-right: 12px;
      }

      .logistics-info {
        p {
          margin: 0;
          line-height: 1.8;
          font-size: 13px;
          color: var(--prev-color-text-regular);
        }
      }
    }

    .logistics-status {
      font-size: 13px;
      color: var(--prev-color-text-primary);
    }
  }
}
</style>
