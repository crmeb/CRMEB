<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="tableFrom" :label-width="labelWidth" label-position="right" @submit.prevent inline>
          <el-form-item label="活动搜索：" label-for="title">
            <el-input placeholder="请输入活动名称，ID" v-model="tableFrom.title" clearable class="form_content_width" />
          </el-form-item>
          <el-form-item label="活动状态：">
            <el-select
              placeholder="请选择"
              clearable
              v-model="tableFrom.status"
              @change="searchs"
              class="form_content_width"
            >
              <el-option value="1" label="开启"></el-option>
              <el-option value="0" label="关闭"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="活动时段：">
            <el-select v-model="tableFrom.time_ids" multiple class="form_content_width" @change="searchs">
              <el-option v-for="item in timeList" :value="item.id" :key="item.id" :label="item.time_name"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="活动时间：">
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
            <el-button type="primary" v-db-click @click="searchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button
        v-auth="['marketing-seckill-activity-create']"
        class="action-row"
        type="primary"
        v-db-click
        @click="add"
      >
        添加秒杀活动
      </el-button>
      <el-table :data="tableList" v-loading="loading" highlight-current-row class="mt14">
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动标题" min-width="130">
          <template #default="scope">
            <el-tooltip placement="top" :open-delay="600">
              <template #content
                ><div>{{ scope.row.title }}</div></template
              >
              <span class="line2">{{ scope.row.title }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="单次限购" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.once_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="总购买数量限制" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品数量" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.product_count }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动时段" min-width="200">
          <template #default="scope">
            <el-tag class="m2" v-for="(item, index) in scope.row.times_list" :key="index" effect="plain">
              {{ item }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="210">
          <template #default="scope">
            <div>开始: {{ scope.row.start_day }}</div>
            <div>结束: {{ scope.row.end_day }}</div>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical" />
            <a v-db-click @click="del(scope.row, '删除秒杀活动', scope.$index)">删除</a>
            <el-divider direction="vertical" />
            <a v-db-click @click="viewInfo(scope.row)">统计</a>
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
import { computed, onActivated, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { seckillActivityListApi, seckillActivityStatusApi, seckillTimeListApi } from '../../api';
import { modalSure } from '@/utils/public';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'seckillActivityList' });

const router = useRouter();
const routePre = setting.routePre;
const mediaStore = useMediaStore();

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const loading = ref(false);
const tableList = ref([]);
const timeList = ref([]);
const grid = {
  xl: 7,
  lg: 10,
  md: 12,
  sm: 24,
  xs: 24,
};
const tableFrom = ref({
  status: '',
  title: '',
  page: 1,
  limit: 15,
  time_ids: [],
  time: '',
});
const total = ref(0);
const timeVal = ref([]);

onMounted(() => {
  getList();
  seckillTimeList();
});

onActivated(() => {
  getList();
  seckillTimeList();
});

function seckillTimeList() {
  seckillTimeListApi()
    .then((res) => {
      timeList.value = res.data.list.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  tableFrom.value.time = timeVal.value ? timeVal.value.join('-') : '';
  tableFrom.value.page = 1;
  if (!e[0]) {
    tableFrom.value.time = '';
  }
  getList();
}
// 添加
function add() {
  router.push({ path: routePre + '/marketing/seckill/activity/create' });
}
// 编辑
function edit(row) {
  router.push({
    path: routePre + '/marketing/seckill/activity/create/' + row.id,
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `marketing/seckill_activity/del/${row.id}`,
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
function viewInfo(row) {
  router.push({
    path: routePre + '/marketing/store_seckill/statistics/' + row.id,
  });
}
// 列表
function getList() {
  loading.value = true;
  tableFrom.value.status = tableFrom.value.status || '';
  seckillActivityListApi(tableFrom.value)
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
function searchs() {
  tableFrom.value.page = 1;
  getList();
}
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  seckillActivityStatusApi(data)
    .then(async (res) => {
      getList();
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
      row.status = !row.status;
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
