<template>
  <div v-loading="spinShow">
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="orderData" :label-width="labelWidth" label-position="right" @submit.prevent inline>
          <el-form-item label="订单搜索：">
            <el-input
              placeholder="请输入订单号/用户昵称/UID"
              v-model="orderData.real_name"
              clearable
              style="width: 350px"
            >
              <template #prepend
                ><el-select placeholder="请选择" clearable v-model="orderData.field_key" style="width: 100px">
                  <el-option value="all" label="全部"></el-option>
                  <el-option value="order_id" label="订单号"></el-option>
                  <el-option value="real_name" label="用户昵称"></el-option>
                  <el-option value="uid" label="UID"></el-option> </el-select
              ></template>
            </el-input>
          </el-form-item>
          <el-form-item label="订单状态：">
            <el-select
              placeholder="请选择"
              clearable
              v-model="orderData.status"
              @change="getList"
              class="form_content_width"
            >
              <el-option :value="item.status" :label="item.name" v-for="(item, i) in statusList" :key="i"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="下单时间：">
            <date-range-picker
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
            ></date-range-picker>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="getList">查询</el-button>
            <el-button v-db-click @click="reset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <!-- tabs -->
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-tag
        class="mr10 mb10"
        v-for="(item, i) in tablists"
        :key="i"
        :type="orderData.type === item.status ? '' : 'info'"
        @click="onClickTab(item)"
        style="cursor: pointer"
      >
        {{ item.name }} ({{ item.count }})
      </el-tag>
    </el-card>
    <!-- 订单列表 -->
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-table :data="orderList" v-loading="loading" highlight-current-row class="mt14">
        <el-table-column label="订单号" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.order_id }}</span>
            <span v-if="scope.row.is_del == 1" style="color: #ed4014; display: block">用户已删除</span>
          </template>
        </el-table-column>
        <el-table-column label="用户信息" min-width="100">
          <template #default="scope"> {{ scope.row.nickname }}/{{ scope.row.uid }} </template>
        </el-table-column>
        <el-table-column label="商品信息" min-width="300">
          <template #default="scope">
            <div class="tabBox">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="scope.row.image" />
              </div>
              <span class="tabBox_tit">{{ scope.row.store_name + ' | ' }}{{ scope.row.suk || '' }}</span>
              <span class="tabBox_pice">{{ '积分' + scope.row.total_price + ' x ' + scope.row.total_num }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="兑换积分" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.total_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="订单状态" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.status_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="下单时间" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="160">
          <template #default="scope">
            <a v-db-click @click="openSend(scope.row)" v-if="scope.row.status === 1">发送货</a>
            <a v-db-click @click="openDistribution(scope.row)" v-if="scope.row.status === 2">配送信息</a>
            <el-divider direction="vertical" v-if="scope.row.status === 1 || scope.row.status === 2" />
            <el-dropdown @command="changeMenu(scope.row, $event)" :transfer="true">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i></span>
              <template #dropdown
                ><el-dropdown-menu>
                  <el-dropdown-item command="detail">订单详情</el-dropdown-item>
                  <el-dropdown-item command="record">订单记录</el-dropdown-item>
                  <el-dropdown-item command="remark" v-show="scope.row.status !== 4">订单备注</el-dropdown-item>
                  <el-dropdown-item command="take" v-show="scope.row.status === 2">已收货</el-dropdown-item>
                  <el-dropdown-item command="del" v-show="scope.row.is_del === 1">删除订单</el-dropdown-item>
                </el-dropdown-menu></template
              >
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="page.pageNum"
          v-model:limit="page.pageSize"
          @pagination="getList"
        />
      </div>
    </el-card>
    <!-- 弹窗组件 -->
    <order-details ref="detailsRef" :orderDatalist="orderDatalist" :orderId="orderId"></order-details>
    <order-remark ref="remarkRef" :orderId="orderId" @submitFail="submitFail"></order-remark>
    <order-record ref="recordRef" :orderId="orderId"></order-record>
    <order-send ref="sendRef" :orderId="orderId" @submitFail="submitFail"></order-send>
    <!-- 配送信息编辑表单 -->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
  </div>
</template>

<script setup>
import { computed, onActivated, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { integralOrderList, integralGetOrdes, getIntegralOrderDataInfo, getIntegralOrderDistribution } from '../../api';
import orderDetails from './orderDetails.vue';
import orderRemark from './orderRemark.vue';
import orderRecord from './orderRecord.vue';
import orderSend from './orderSend.vue';
import editFrom from '@/components/from/from';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'integralOrderList' });

const route = useRoute();
const mediaStore = useMediaStore();

const detailsRef = ref(null);
const remarkRef = ref(null);
const recordRef = ref(null);
const sendRef = ref(null);
const edits = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const spinShow = ref(false);
const loading = ref(false);
const orderList = ref([]);
const tablists = ref([]);
const statusList = ref([
  { status: '', name: '全部' },
  { status: 1, name: '未发货' },
  { status: 2, name: '待收货' },
  { status: 3, name: '已完成' },
]);
const orderData = ref({
  status: '',
  data: '',
  real_name: '',
  field_key: 'all',
  product_id: '',
  type: '',
});
const timeVal = ref([]);
const orderId = ref(0);
const orderDatalist = ref(null);
const FromData = ref(null);
const total = ref(0);
const page = ref({
  pageNum: 1,
  pageSize: 15,
});

onMounted(() => {
  getTabs();
  getList();
});

onActivated(() => {
  getList();
});

function getTabs() {
  spinShow.value = true;
  integralGetOrdes({
    data: orderData.value.data,
    product_id: route.query.product_id || '',
  })
    .then((res) => {
      tablists.value = res.data;
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
function onClickTab(item) {
  orderData.value.type = item.status;
  page.value.pageNum = 1;
  getList();
}
function getList() {
  loading.value = true;
  orderData.value.product_id = route.query.product_id || '';
  integralOrderList({
    page: page.value.pageNum,
    limit: page.value.pageSize,
    status: orderData.value.status,
    data: orderData.value.data,
    real_name: orderData.value.real_name,
    field_key: orderData.value.field_key,
    type: orderData.value.type === 0 ? '' : orderData.value.type,
    product_id: orderData.value.product_id,
  })
    .then(async (res) => {
      let data = res.data;
      orderList.value = data.data;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function onchangeTime(e) {
  timeVal.value = e;
  orderData.value.data = timeVal.value ? timeVal.value.join('-') : '';
  getList();
}
function openSend(row) {
  orderId.value = row.id;
  sendRef.value.modals = true;
  sendRef.value.getList(1);
  sendRef.value.getDeliveryList();
}
function openDistribution(row) {
  getIntegralOrderDistribution(row.id)
    .then(async (res) => {
      FromData.value = res.data;
      edits.value.modals = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function changeMenu(row, name) {
  orderId.value = row.id;
  switch (name) {
    case 'detail':
      getIntegralOrderDataInfo(row.id)
        .then(async (res) => {
          orderDatalist.value = res.data;
          detailsRef.value.modals = true;
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
      break;
    case 'record':
      recordRef.value.modals = true;
      recordRef.value.getList(row.id);
      break;
    case 'remark':
      remarkRef.value.modals = true;
      remarkRef.value.formValidate.remark = row.remark;
      break;
    case 'take':
      modalSure({
        title: '修改确认收货',
        url: `marketing/integral/order/take/${row.id}`,
        method: 'put',
        ids: '',
      })
        .then((res) => {
          ElMessage.success(res.msg);
          getList();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
      break;
    case 'del':
      modalSure({
        title: '删除订单',
        url: `marketing/integral/order/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      })
        .then((res) => {
          ElMessage.success(res.msg);
          getList();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
      break;
  }
}
function reset() {
  orderData.value = {
    status: '',
    data: '',
    real_name: '',
    field_key: 'all',
    product_id: route.query.product_id || '',
    type: '',
  };
  timeVal.value = [];
  page.value.pageNum = 1;
  getList();
  getTabs();
}
function submitFail() {
  getList();
}
</script>

<style lang="scss" scoped>
.tabBox {
  display: flex;
  align-items: center;
  .tabBox_img {
    width: 36px;
    height: 36px;
    img {
      width: 100%;
      height: 100%;
    }
  }
  .tabBox_tit {
    width: 60%;
    font-size: 12px;
    margin: 0 2px 0 10px;
    letter-spacing: 1px;
    padding: 5px 0;
  }
}
</style>
