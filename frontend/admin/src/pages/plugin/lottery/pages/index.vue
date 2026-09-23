<template>
  <div>
    <el-card :bordered="false" shadow="never">
      <el-form :model="tableFrom" :label-width="labelWidth" :label-position="labelPosition" @submit.prevent>
        <el-row :gutter="24">
          <el-col>
            <el-form-item label="活动类型：" clearable>
              <el-select
                style="width: 200px"
                v-model="tableFrom.factor"
                placeholder="请选择活动类型"
                clearable
                @change="userSearchs"
              >
                <el-option value="1" label="积分抽取"></el-option>
                <el-option value="3" label="订单支付"></el-option>
                <el-option value="4" label="订单评价"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item label="活动状态：" clearable>
              <el-select
                style="width: 200px"
                v-model="tableFrom.start_status"
                placeholder="请选择"
                clearable
                @change="userSearchs"
              >
                <el-option value="0" label="未开始"></el-option>
                <el-option value="1" label="进行中"></el-option>
                <el-option value="-1" label="已结束"></el-option>
              </el-select>
            </el-form-item>
          </el-col>

          <el-col>
            <el-form-item label="上架状态：">
              <el-select
                style="width: 200px"
                placeholder="请选择"
                v-model="tableFrom.status"
                clearable
                @change="userSearchs"
              >
                <el-option value="1" label="上架"></el-option>
                <el-option value="0" label="下架"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item label="抽奖搜索：" label-for="store_name">
              <el-input
                style="width: 200px"
                placeholder="请输入抽奖名称，ID"
                v-model="tableFrom.store_name"
                @keyup.enter="userSearchs"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row class="mb14">
          <el-button v-auth="['marketing-store_bargain-create']" type="primary" v-db-click @click="add" class="mr10"
            >添加抽奖</el-button
          >
        </el-row>
      </el-form>
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动类型" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.lottery_type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="参与次数" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.lottery_all }}</span>
          </template>
        </el-table-column>
        <el-table-column label="抽奖人数" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.lottery_people }}</span>
          </template>
        </el-table-column>
        <el-table-column label="中奖人数" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.lottery_win }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动状态" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.status_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上架状态" min-width="130">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              :disabled="scope.row.lottery_status == 2 ? true : false"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="上架"
              inactive-text="下架"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="130">
          <template #default="scope">
            <div>起：{{ scope.row.start_time || '--' }}</div>
            <div>止：{{ scope.row.end_time || '--' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="活动状态" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.status_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除抽奖', scope.$index)">删除</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="copy(scope.row)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="getRecording(scope.row)">抽奖记录</a>
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
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { lotteryListApi, lotteryStatusApi } from '../api';
import { modalSure } from '@/utils/public';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'storeBargain' });

const router = useRouter();
const routePre = setting.routePre;
const mediaStore = useMediaStore();

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const loading = ref(false);
const tableList = ref([]);
const tableFrom = ref({
  start_status: '',
  status: '',
  store_name: '',
  export: 0,
  page: 1,
  factor: '',
  limit: 15,
});
const total = ref(0);

onMounted(() => {
  getList();
});

// 添加
function add() {
  router.push({ path: routePre + '/marketing/lottery/create' });
}
// 编辑
function edit(row) {
  router.push({
    path: routePre + '/marketing/lottery/create',
    query: {
      lottery_id: row.id,
      type: row.factor,
    },
  });
}
// 一键复制
function copy(row) {
  router.push({
    name: 'marketing_create',
    query: {
      id: row.id,
      copy: 1,
    },
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `marketing/lottery/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
//查看抽奖记录
function getRecording(row) {
  router.push({
    path: routePre + `/marketing/lottery/recording_list`,
    query: {
      id: row.id,
    },
  });
}
// 列表
function getList() {
  loading.value = true;
  tableFrom.value.start_status = tableFrom.value.start_status || '';
  tableFrom.value.status = tableFrom.value.status || '';
  lotteryListApi(tableFrom.value)
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
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  lotteryStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
      getList();
    });
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
</style>
