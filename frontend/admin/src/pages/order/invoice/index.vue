<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="orderData" label-width="80px" label-position="right" inline @submit.prevent>
          <el-form-item label="创建时间：">
            <date-range-picker
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
              class="mr20"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="搜索：" prop="real_name" label-for="real_name">
            <el-input clearable v-model="orderData.real_name" placeholder="请输入" class="form_content_width">
              <template #prepend
                ><el-select v-model="orderData.field_key" style="width: 100px">
                  <el-option value="all" label="全部"></el-option>
                  <el-option value="order_id" label="订单号"></el-option>
                  <el-option value="uid" label="UID"></el-option>
                  <el-option value="real_name" label="用户姓名"></el-option>
                  <el-option value="user_phone" label="用户电话"></el-option> </el-select
              ></template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="orderSearch">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: '0 20px 20px' }">
      <el-tabs v-model="currentTab" @tab-change="onClickTab" v-if="tablists">
        <el-tab-pane :label="'全部发票（' + tablists.all + '）'" name=" " />
        <el-tab-pane :label="'待开发票（' + tablists.noOpened + '）'" name="1" />
        <el-tab-pane :label="'已开发票（' + tablists.opened + '）'" name="2" />
        <el-tab-pane :label="'退款发票（' + tablists.refund + '）'" name="3" />
      </el-tabs>
      <el-table
        :data="orderList"
        ref="table"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="订单号" min-width="140">
          <template #default="scope">
            <span>{{ scope.row.order_id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="订单金额" min-width="90">
          <template #default="scope">
            <div>¥ {{ scope.row.pay_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="发票类型" min-width="130">
          <template #default="scope">
            <div v-if="scope.row.type === 1">电子普通发票</div>
            <div v-else>纸质专用发票</div>
          </template>
        </el-table-column>
        <el-table-column label="发票抬头类型" min-width="130">
          <template #default="scope">
            <div v-if="scope.row.header_type === 1">个人</div>
            <div v-else>企业</div>
          </template>
        </el-table-column>
        <el-table-column label="下单时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="开票状态" min-width="130">
          <template #default="scope">
            <div v-if="scope.row.is_invoice === 1">已开票</div>
            <div v-else>未开票</div>
          </template>
        </el-table-column>
        <el-table-column label="订单状态" min-width="130">
          <template #default="scope">
            <div v-if="scope.row.status === 0">未发货</div>
            <div v-else-if="scope.row.status === 1">待收货</div>
            <div v-else-if="scope.row.status === 2">待评价</div>
            <div v-else-if="scope.row.status === 3">已完成</div>
            <div v-else-if="scope.row.status === -2">已退款</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="300">
          <template #default="scope">
            <template v-if="tablists.elec_invoice && tablists.elec_invoice == 1">
              <a
                v-if="
                  scope.row.is_invoice == 1 &&
                  scope.row.unique_num !== '' &&
                  scope.row.red_invoice_num == '' &&
                  scope.row.refund_status == 0
                "
                v-db-click
                @click="downInvoiceFn(scope.row)"
                >下载发票</a
              >
              <el-divider
                v-if="
                  scope.row.is_invoice == 1 &&
                  scope.row.unique_num !== '' &&
                  scope.row.red_invoice_num == '' &&
                  scope.row.refund_status == 0
                "
                direction="vertical"
              />
              <a
                v-if="scope.row.is_invoice == 1 && scope.row.unique_num !== '' && scope.row.red_invoice_num == ''"
                v-db-click
                @click="openNegative(scope.row)"
                >开具负数发票</a
              >
              <el-divider
                v-if="scope.row.is_invoice == 1 && scope.row.unique_num !== '' && scope.row.red_invoice_num == ''"
                direction="vertical"
              />
              <a
                v-if="scope.row.is_invoice !== 1 && scope.row.refund_status == 0"
                v-db-click
                @click="getInvoice(scope.row)"
                >开具电子发票</a
              >
              <el-divider v-if="scope.row.is_invoice !== 1 && scope.row.refund_status == 0" direction="vertical" />
            </template>
            <a v-if="scope.row.status != -2" v-db-click @click="edit(scope.row)">操作</a>
            <el-divider v-if="scope.row.status != -2" direction="vertical" />
            <a v-db-click @click="orderInfo(scope.row.id)">订单信息</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="orderData.page"
          v-model:limit="orderData.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <el-dialog v-model="invoiceShow" title="发票详情" class="order_box" width="720px" @closed="cancel">
      <el-form :model="formInline" label-width="80px" @submit.prevent>
        <div v-if="invoiceDetails.header_type === 1 && invoiceDetails.type === 1">
          <div class="list">
            <div class="title">发票信息</div>
            <el-row class="row">
              <el-col :span="12"
                >发票抬头: <span class="info">{{ invoiceDetails.name }}</span></el-col
              >
              <el-col :span="12">发票类型: <span class="info">电子普通发票</span></el-col>
            </el-row>
            <el-row class="row">
              <el-col :span="12">发票抬头类型: 个人</el-col>
              <el-col :span="12">订单金额: {{ invoiceDetails.pay_price }}</el-col>
            </el-row>
          </div>
          <div class="list">
            <div class="title row">联系信息</div>
            <el-row class="row">
              <el-col :span="12">真实姓名: {{ invoiceDetails.name }}</el-col>
              <el-col :span="12">联系电话: {{ invoiceDetails.drawer_phone }}</el-col>
            </el-row>
            <el-row class="row">
              <el-col :span="12">联系邮箱: {{ invoiceDetails.email }}</el-col>
            </el-row>
          </div>
        </div>
        <div v-if="invoiceDetails.header_type === 2 && invoiceDetails.type === 1">
          <div class="list">
            <div class="title">发票信息</div>
            <el-row class="row">
              <el-col :span="12"
                >发票抬头: <span class="info">{{ invoiceDetails.name }}</span></el-col
              >
              <el-col :span="12"
                >企业税号: <span class="info">{{ invoiceDetails.duty_number }}</span></el-col
              >
            </el-row>
            <el-row class="row">
              <el-col :span="12">发票类型: 电子普通发票</el-col>
              <el-col :span="12">发票抬头类型: 企业</el-col>
            </el-row>
          </div>
          <div class="list">
            <div class="title row">联系信息</div>
            <el-row class="row">
              <el-col :span="12">真实姓名: {{ invoiceDetails.name }}</el-col>
              <el-col :span="12">联系电话: {{ invoiceDetails.user_phone }}</el-col>
            </el-row>
            <el-row class="row">
              <el-col :span="12">联系邮箱: {{ invoiceDetails.email }}</el-col>
            </el-row>
          </div>
        </div>
        <div v-if="invoiceDetails.header_type === 2 && invoiceDetails.type === 2">
          <div class="list">
            <div class="title">发票信息</div>
            <el-row class="row">
              <el-col :span="12"
                >发票抬头: <span class="info">{{ invoiceDetails.name }}</span></el-col
              >
              <el-col :span="12"
                >企业税号: <span class="info">{{ invoiceDetails.duty_number }}</span></el-col
              >
            </el-row>
            <el-row class="row">
              <el-col :span="12">发票类型: 纸质专用发票</el-col>
              <el-col :span="12">发票抬头类型: 企业</el-col>
            </el-row>
            <el-row class="row">
              <el-col :span="12"
                >开户银行: <span class="info">{{ invoiceDetails.bank }}</span></el-col
              >
              <el-col :span="12"
                >银行账号: <span class="info">{{ invoiceDetails.card_number }}</span></el-col
              >
            </el-row>
            <el-row class="row">
              <el-col :span="12">企业地址: {{ invoiceDetails.address }}</el-col>
              <el-col :span="12">企业电话: {{ invoiceDetails.tell }}</el-col>
            </el-row>
          </div>
          <div class="list">
            <div class="title row">联系信息</div>
            <el-row class="row">
              <el-col :span="12">真实姓名: {{ invoiceDetails.real_name }}</el-col>
              <el-col :span="12">联系电话: {{ invoiceDetails.user_phone }}</el-col>
            </el-row>
            <el-row class="row">
              <el-col :span="12">联系邮箱: {{ invoiceDetails.email }}</el-col>
            </el-row>
          </div>
        </div>
        <el-form-item label="开票状态：" style="margin-top: 14px">
          <el-radio-group v-model="formInline.is_invoice" @change="kaiInvoice(formInline.is_invoice)">
            <el-radio :label="1" :value="1">已开票</el-radio>
            <el-radio :label="0" :value="0">未开票</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="发票编号：" v-if="formInline.is_invoice === 1">
          <el-input v-model="formInline.invoice_number" placeholder="请输入发票编号"></el-input>
        </el-form-item>
        <el-form-item label="发票备注：" v-if="formInline.is_invoice === 1">
          <el-input
            v-model="formInline.remark"
            value="备注"
            type="textarea"
            :autosize="{ minRows: 2, maxRows: 5 }"
            placeholder="请输入发票备注"
          ></el-input>
        </el-form-item>
        <div class="acea-row row-right">
          <el-button type="primary" v-db-click @click="handleSubmit()">确定</el-button>
        </div>
      </el-form>
    </el-dialog>
    <el-dialog v-model="orderShow" title="订单详情" class="order_box" width="720px">
      <orderDetall :orderId="orderId" @detall="detall" v-if="orderShow"></orderDetall>
    </el-dialog>
    <el-dialog
      v-model="invoiceModalShow"
      title="发票信息"
      append-to-body
      :close-on-click-modal="false"
      width="1320px"
      class="mapBox"
    >
      <iframe id="invoicePage" width="100%" height="600px" frameborder="0" v-bind:src="keyUrl"></iframe>
    </el-dialog>
  </div>
</template>
<script setup>
import { computed, ref } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import orderDetall from './orderDetall';
import {
  orderInvoiceChart,
  orderInvoiceList,
  orderInvoiceSet,
  invoiceIssuanceUrl,
  downInvoice,
  redInvoiceIssuance,
  saveInvoiceInfo,
} from '@/api/order';

defineOptions({ name: 'invoice' });

const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

const orderShow = ref(false);
const invoiceShow = ref(false);
const invoiceModalShow = ref(false);
const invoiceDetails = ref({});
const formInline = ref({
  is_invoice: 0,
  invoice_number: '',
  remark: '',
});
const keyUrl = ref('');
const loading = ref(false);
const currentTab = ref('');
const tablists = ref(null);
const timeVal = ref([]);
const orderList = ref([]);
const total = ref(0); // 总条数
const orderData = ref({
  page: 1, // 当前页
  limit: 15, // 每页显示条数
  status: '',
  data: '',
  real_name: '',
  field_key: '',
  type: '',
});
const orderId = ref(0);
const invoiceId = ref(0);

// created
getTabs();
getList();

function openNegative(row) {
  // 弹窗确认
  ElMessageBox.confirm('确定开具负数发票？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  }).then(() => {
    redInvoiceIssuance(row.invoice_id).then((res) => {
      ElMessage.success(res.msg);
      getList();
    });
  });
}

function downInvoiceFn(row) {
  downInvoice(row.invoice_id).then((res) => {
    window.open(res.data.downloadBase64.pdfUrl, '_blank');
  });
}

function getInvoice(row) {
  invoiceIssuanceUrl(row.invoice_id).then((res) => {
    invoiceId.value = row.invoice_id;
    keyUrl.value = res.data.uri;
    invoiceModalShow.value = true;
    window.addEventListener('message', handleMessage);
  });
}

// 处理iframe传值
function handleMessage(event) {
  switch (event.data.event) {
    case 'onCancel':
      invoiceModalShow.value = false;
      keyUrl.value = '';
      invoiceId.value = 0;
      window.removeEventListener('message');
      break;
    case 'onSuccess':
      saveInvoiceInfo(invoiceId.value, event.data.data).then((res) => {
        ElMessage.success(res.msg);
        getList();
        keyUrl.value = '';
        invoiceId.value = 0;
        invoiceModalShow.value = false;
        window.removeEventListener('message');
      });
      break;
  }
}

function detall(e) {
  orderShow.value = e;
}

function orderInfo(id) {
  orderId.value = id;
  orderShow.value = true;
}

function empty() {
  formInline.value = {
    is_invoice: 1,
    invoice_number: '',
    remark: '',
  };
}

function cancel() {
  invoiceShow.value = false;
  empty();
}

function kaiInvoice(invoice) {
  if (invoice !== 1) {
    formInline.value.invoice_number = '';
    formInline.value.remark = '';
  }
}

function handleSubmit() {
  if (formInline.value.is_invoice === 1) {
    if (formInline.value.invoice_number.trim() === '') return ElMessage.error('请填写发票编号');
  }
  orderInvoiceSet(invoiceDetails.value.invoice_id, formInline.value)
    .then((res) => {
      ElMessage.success(res.msg);
      invoiceShow.value = false;
      getList();
      empty();
      getTabs();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

function edit(row) {
  invoiceShow.value = true;
  invoiceDetails.value = row;
  formInline.value.invoice_number = row.invoice_number;
  formInline.value.remark = row.invoice_reamrk;
  formInline.value.is_invoice = row.is_invoice;
}

// 订单列表
function getList() {
  loading.value = true;
  orderInvoiceList(orderData.value)
    .then(async (res) => {
      loading.value = false;
      let data = res.data;
      orderList.value = data.list;
      total.value = data.count;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

function getTabs() {
  orderInvoiceChart(orderData.value)
    .then((res) => {
      tablists.value = res.data;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

// 精确搜索()
function orderSearch() {
  orderData.value.page = 1;
  getTabs();
  getList();
}

// 具体日期搜索()；
function onchangeTime(e) {
  orderData.value.page = 1;
  timeVal.value = e || [];
  orderData.value.data = timeVal.value[0] ? (timeVal.value ? timeVal.value.join('-') : '') : '';
  getList();
  getTabs();
}

// 订单状态搜索()
function selectChange() {
  orderData.value.page = 1;
  getList();
}

// 订单搜索()
function onClickTab() {
  orderData.value.page = 1;
  orderData.value.type = currentTab.value;
  getList();
}
</script>
<style lang="scss" scoped>
.order_box .list {
  font-size: 12px;
  color: #17233d;
  border-bottom: 1px solid #e7eaec;
  margin: 0 10px;
  padding-bottom: 22px;
}
.ivu-form-item {
  margin-left: 10px;
  margin-right: 10px;
}
:deep(.ivu-form-item-label) {
  text-align: left;
  width: 83px !important;
}
:deep(.ivu-form-item-content) {
  margin-left: 83px !important;
}
.order_box .list .title {
  color: #000000;
  font-weight: bold;
}
.order_box .list .row {
  margin-top: 13px;
}
.order_box .list .info {
  color: #515a6e;
}
.tab_data :deep(.ivu-form-item-content) {
  margin-left: 0 !important;
}
.table_box :deep(.ivu-divider-horizontal) {
  margin-top: 0px !important;
}
.table_box :deep(.ivu-form-item) {
  margin-bottom: 15px !important;
}
.tabform {
  margin-bottom: 10px;
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
}
</style>
