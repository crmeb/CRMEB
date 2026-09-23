<template>
  <el-dialog v-model="modals" title="订单详情" width="680px" class="order_box" :show-close="true">
    <el-row v-if="orderDatalist">
      <el-col :span="24">
        <el-card :bordered="false" shadow="never" class="ivu-mt">
          <template #header
            ><div><span>用户信息</span></div></template
          >
          <el-row>
            <el-col :span="12">用户昵称：{{ orderDatalist.userInfo.nickname }}</el-col>
            <el-col :span="12">用户电话：{{ orderDatalist.userInfo.phone }}</el-col>
          </el-row>
        </el-card>
      </el-col>
      <el-col :span="24">
        <el-card :bordered="false" shadow="never" class="ivu-mt">
          <template #header
            ><div><span>订单信息</span></div></template
          >
          <el-row>
            <el-col :span="12">订单号：{{ orderDatalist.orderInfo.order_id }}</el-col>
            <el-col :span="12">下单时间：{{ orderDatalist.orderInfo.add_time }}</el-col>
            <el-col :span="12">商品名称：{{ orderDatalist.orderInfo.store_name }}</el-col>
            <el-col :span="12">商品规格：{{ orderDatalist.orderInfo.suk }}</el-col>
            <el-col :span="12">兑换积分：{{ orderDatalist.orderInfo.total_price }}</el-col>
            <el-col :span="12">订单状态：{{ orderDatalist.orderInfo.status_name }}</el-col>
          </el-row>
          <el-row v-if="orderDatalist.orderInfo.user_address">
            <el-col :span="24">收货地址：{{ orderDatalist.orderInfo.user_address }}</el-col>
          </el-row>
        </el-card>
      </el-col>
    </el-row>
    <template #footer><span></span></template>
  </el-dialog>
</template>

<script setup>
import { ref } from 'vue';

defineOptions({ name: 'integralOrderDetails' });

defineProps({
  orderDatalist: Object,
  orderId: Number,
});

const modals = ref(false);

defineExpose({ modals });
</script>
