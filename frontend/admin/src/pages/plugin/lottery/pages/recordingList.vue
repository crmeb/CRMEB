<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="fl_header">
        <el-button class="btn-back" icon="ArrowLeft" type="text" v-db-click @click="$router.go(-1)">返回</el-button>
        <el-divider direction="vertical"></el-divider>
        <div class="header-main">
          <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
          <span class="header-description">
            <el-icon class="header-description-icon"><InfoFilled /></el-icon>
            中奖记录查询与发货，实物奖品可直接录入物流发货。
          </span>
        </div>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="tableFrom" :label-width="labelWidth" label-position="right" @submit.prevent inline>
          <el-form-item label="时间选择：">
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
          <el-form-item label="奖品类型：">
            <el-select type="button" v-model="tableFrom.type" @change="selectType" class="form_content_width" clearable>
              <el-option v-for="(item, i) in typeList" :key="i" :label="item.text" :value="item.val"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索用户：" label-for="store_name">
            <el-input clearable placeholder="请输入用户信息" v-model="tableFrom.keyword" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <div>{{ scope.row.id }}</div>
          </template>
        </el-table-column>
        <el-table-column label="用户信息" min-width="90">
          <template #default="scope">
            <span>{{ scope.row.user.nickname }} </span>
          </template>
        </el-table-column>
        <el-table-column label="奖品信息" min-width="130">
          <template #default="scope">
            <div class="prize">
              <img :src="scope.row.prize.image" alt="" />
              <span>{{ scope.row.prize.name }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="抽奖时间" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.add_time }}</div>
          </template>
        </el-table-column>
        <el-table-column label="收货信息" min-width="100">
          <template #default="scope">
            <div v-if="scope.row.receive_info.name">
              <div>姓名：{{ scope.row.receive_info.name }}</div>
              <div>电话：{{ scope.row.receive_info.phone }}</div>
              <div>地址：{{ scope.row.receive_info.address }}</div>
              <div v-if="scope.row.receive_info.mark">备注：{{ scope.row.receive_info.mark }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.deliver_info.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <a v-db-click @click="deliver(scope.row, 1)" v-if="scope.row.type == 6 && scope.row.is_deliver === 0"
              >发货</a
            >
            <a v-else-if="scope.row.type == 6 && scope.row.is_deliver === 1" v-db-click @click="isDeliver(scope.row)"
              >配送信息</a
            >
            <el-divider direction="vertical" v-if="scope.row.type == 6" />
            <a v-db-click @click="deliver(scope.row, 2)">备注</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="tableFrom.page"
          v-model:limit="tableFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <!-- 发货-->
    <el-dialog
      v-model="shipModel"
      width="540px"
      :title="!modelTitle ? (modelType === 1 ? '发货' : '备注') : modelTitle"
      :close-on-click-modal="false"
    >
      <el-form
        v-model="shipModel"
        ref="formRecordRef"
        :model="modelType === 1 ? shipForm : markForm"
        :rules="modelType === 1 ? ruleShip : ruleMark"
        label-width="90px"
      >
        <el-form-item v-if="modelType === 1" label="快递公司：" prop="deliver_name">
          <el-select v-model="shipForm.deliver_name" class="w100">
            <el-option v-for="item in locationList" :value="item.value" :key="item.id" :label="item.value"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item v-if="modelType === 1" label="快递单号：" prop="deliver_number">
          <el-input v-model="shipForm.deliver_number" placeholder="请输入快递单号" class="w100"></el-input>
          <div class="tip" v-if="shipForm.deliver_name == '顺丰速运'">
            <p>顺丰请输入单号 :收件人或寄件人手机号后四位</p>
            <p>例如：SF000000000000:3941</p>
          </div>
        </el-form-item>
        <el-form-item v-if="modelType === 2" label="备注：">
          <el-input v-model="markForm.mark" placeholder="请输入备注" class="w100"></el-input>
        </el-form-item>
        <el-form-item>
          <div class="acea-row row-right">
            <el-button v-db-click @click="cancel('formValidate')">关闭</el-button>
            <el-button type="primary" v-db-click @click="ok(modelType === 1 ? 'shipForm' : 'markForm')">提交</el-button>
          </div>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { lotteryRecordList, lotteryRecordDeliver } from '../api';
import { getExpressData } from '@/api/order';
import { ruleShip, ruleMark } from './formRule/ruleShip';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'lotteryRecordList' });

const route = useRoute();
const mediaStore = useMediaStore();

const formRecordRef = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const shipModel = ref(false);
const loading = ref(false);
const locationList = ref([]);
const shipForm = ref({
  id: '',
  deliver_name: '',
  deliver_number: null,
});
const markForm = ref({
  id: '',
  mark: '',
});
const typeList = ref([
  { text: '全部', val: '' },
  { text: '未中奖', val: '1' },
  { text: '积分', val: '2' },
  { text: '余额', val: '3' },
  { text: '红包', val: '4' },
  { text: '优惠券', val: '5' },
  { text: '商品', val: '6' },
]);
const tableList = ref([]);
const grid = {
  xl: 7,
  lg: 10,
  md: 12,
  sm: 24,
  xs: 24,
};
const tableFrom = ref({
  keyword: '',
  time: [],
  page: 1,
  limit: 15,
  lottery_id: 0,
});
const total = ref(0);
const timeVal = ref([]);
const modelType = ref(1);
const lottery_id = ref('');
const modelTitle = ref('');

onMounted(() => {
  tableFrom.value.lottery_id = route.query.id;
  lottery_id.value = route.query.id;
  getList();
  getExpressDataList();
});

function deliver(row, type) {
  markForm.value.id = row.id;
  shipForm.value.id = row.id;
  shipForm.value.deliver_name = '';
  shipForm.value.deliver_number = '';
  markForm.value.mark = row.deliver_info.mark;
  modelType.value = type;
  shipModel.value = true;
}
function isDeliver(row) {
  markForm.value.id = row.id;
  shipForm.value.id = row.id;
  modelType.value = 1;
  modelTitle.value = '配送信息';
  shipModel.value = true;
  shipForm.value.deliver_name = row.deliver_info.deliver_name;
  shipForm.value.deliver_number = row.deliver_info.deliver_number;
}
function ok(name) {
  formRecordRef.value.validate((valid) => {
    lotteryRecordDeliver(modelType.value == 1 ? shipForm.value : markForm.value)
      .then((res) => {
        ElMessage.success('操作成功');
        shipModel.value = false;
        getList();
        shipForm.value = {
          id: '',
          deliver_name: '',
          deliver_number: null,
        };
        modelTitle.value = '';
        markForm.value = {
          id: '',
          mark: '',
        };
      })
      .catch((err) => {
        ElMessage.error(err.msg);
      });
  });
}
function cancel() {
  modelType.value = 1;
  modelTitle.value = '';
  shipModel.value = false;
}
// 物流公司列表
function getExpressDataList() {
  getExpressData()
    .then(async (res) => {
      locationList.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e || [];
  tableFrom.value.time = timeVal.value[0] ? (timeVal.value ? timeVal.value.join('-') : '') : '';
  tableFrom.value.page = 1;
  getList();
}
// 选择时间
function selectChange(tab) {
  tableFrom.value.page = 1;
  tableFrom.value.time = tab;
  timeVal.value = [];
  getList();
}
function selectType(type) {
  tableFrom.value.page = 1;
  timeVal.value = [];
  getList();
}
function selectChangeFactor() {
  tableFrom.value.page = 1;
  timeVal.value = [];
  getList();
}
// 列表
function getList() {
  loading.value = true;
  lotteryRecordList(tableFrom.value)
    .then(async (res) => {
      let data = res.data;
      tableList.value = data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  tableFrom.value.page = 1;
  getList();
}
</script>

<style lang="scss" scoped>
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}
.prize {
  display: flex;
  align-items: center;
}
.prize img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 5px;
}
.trips {
  color: #ccc;
}
.w414 {
  width: 414px;
}
</style>
