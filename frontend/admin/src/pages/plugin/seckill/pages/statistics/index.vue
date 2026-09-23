<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/marketing/store_seckill/index'"
      description="查看秒杀活动的参与人与订单数据，评估活动效果。"
    ></pages-header>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0" class="ivu-mt-16"></cards-data>
    <el-card :bordered="false" shadow="never">
      <el-form :model="pagination" label-width="80px" label-position="right" @submit.prevent inline>
        <el-form-item v-if="type == 1" label="订单状态：" label-for="status">
          <el-select
            v-model="pagination.status"
            clearable
            placeholder="请选择订单状态"
            @change="changeStatus"
            class="form_content_width"
          >
            <el-option value="1" label="待发货"></el-option>
            <el-option value="2" label="待收货"></el-option>
            <el-option value="3" label="待评价"></el-option>
            <el-option value="4" label="交易完成"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="订单搜索：" label-for="title">
          <el-input
            clearable
            v-model="pagination.real_name"
            placeholder="请输入用户姓名|手机号|UID"
            class="form_content_width"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" v-db-click @click="changeStatus">查询</el-button>
        </el-form-item>
      </el-form>
      <el-tabs v-model="type" @tab-change="onClickTab">
        <el-tab-pane v-for="(item, index) in tabs" :label="item.label" :name="item.type" :key="index" />
      </el-tabs>
      <el-table :data="tbody" ref="table" v-loading="loading" highlight-current-row class="mt14">
        <el-table-column
          :label="item.title"
          :min-width="item.minWidth || 100"
          v-for="(item, index) in type == 1 ? thead2 : thead"
          :key="index"
        >
          <template #default="scope">
            <template v-if="item.key">
              <span>{{ scope.row[item.key] }}</span>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="pagination.page"
          v-model:limit="pagination.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import cardsData from '@/components/cards/cards';
import { getseckillStatistics, getseckillStatisticsPeople, getseckillStatisticsOrder } from '../../api';

defineOptions({ name: 'seckillStatistics' });

const route = useRoute();

const pageHeader = ref(null);
const table = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const id = ref(0);
const tbody = ref([]);
const labelWidth = 75;
const total = ref(0);
const tabs = ref([
  { type: '0', label: '活动参与人' },
  { type: '1', label: '活动订单' },
]);
const type = ref('0');
const loading = ref(false);
const thead = ref([
  { title: '用户姓名', key: 'real_name' },
  { title: '购买件数', key: 'goods_num' },
  { title: '支付订单数', key: 'order_num' },
  { title: '支付金额', key: 'total_price' },
  { title: '最近参与时间', key: 'add_time' },
]);
const thead2 = ref([
  { title: '订单号', key: 'order_id' },
  { title: '用户', key: 'real_name' },
  { title: '订单状态', key: 'status' },
  { title: '订单支付金额', key: 'pay_price' },
  { title: '订单商品数', key: 'total_num' },
  { title: '下单时间', key: 'add_time' },
  { title: '支付时间', key: 'pay_time' },
]);
const cardLists = ref([
  { col: 6, count: 0, name: '下单人数（人）', className: 'iconxiadanrenshu' },
  { col: 6, count: 0, name: '支付订单额（元）', className: 'iconzhifudingdan' },
  { col: 6, count: 0, name: '支付人数（人）', className: 'iconzhifurenshu' },
  { col: 6, count: 0, name: '剩余库存/总库存', className: 'iconshengyukucun' },
]);
const pagination = ref({
  page: 1,
  limit: 15,
  real_name: '',
  status: '',
});

onMounted(() => {
  id.value = route.params.id;
  getStatistics(id.value);
  getList();
});

function changeStatus() {
  pagination.value.page = 1;
  getList();
}
// 统计
function getStatistics(id) {
  getseckillStatistics(id).then((res) => {
    let arr = ['order_count', 'all_price', 'pay_count', 'pay_rate'];
    cardLists.value.map((i, index) => {
      i.count = res.data[arr[index]];
    });
  });
}
// 列表
function getList() {
  loading.value = true;
  if (type.value == '0') {
    getseckillStatisticsPeople(id.value, pagination.value).then((res) => {
      loading.value = false;
      const { count, list } = res.data;
      total.value = count;
      tbody.value = list;
    });
  } else {
    getseckillStatisticsOrder(id.value, pagination.value).then((res) => {
      loading.value = false;
      const { count, list } = res.data;
      total.value = count;
      tbody.value = list;
    });
  }
}
// 标签切换
function onClickTab() {
  getList();
}
</script>

<style lang="scss" scoped>
.cl {
  margin-right: 20px;
}
.code-row-bg {
  display: flex;
  flex-wrap: nowrap;
}
.code-row-bg .ivu-mt {
  width: 100%;
  margin: 0 5px;
}
.ech-box {
  margin-top: 10px;
}
:deep(.ivu-tabs-nav-scroll) {
  background-color: #fff;
  padding-top: 5px;
}
.change-style {
  border: 1px solid #ccc;
  border-radius: 15px;
  padding: 0px 10px;
  cursor: pointer;
}
.table-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.return {
  margin-bottom: 6px;
}
</style>
