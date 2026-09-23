<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="tableFrom" :label-width="labelWidth" label-position="right" @submit.prevent inline>
          <el-form-item label="商品搜索：" label-for="store_name">
            <el-input
              placeholder="请输入商品名称，ID"
              v-model="tableFrom.store_name"
              clearable
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item label="活动搜索：" label-for="activity_name">
            <el-input
              placeholder="请输入活动名称"
              v-model="tableFrom.activity_name"
              clearable
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item label="活动状态：">
            <el-select
              placeholder="请选择"
              clearable
              v-model="tableFrom.status"
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="开启"></el-option>
              <el-option value="0" label="关闭"></el-option>
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
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button
        v-auth="['marketing-seckill-product-create']"
        class="action-row"
        type="primary"
        v-db-click
        @click="add"
      >
        添加秒杀商品
      </el-button>
      <el-button v-auth="['export-storeSeckill']" class="action-row export" v-db-click @click="exports">导出</el-button>
      <el-table :data="tableList" v-loading="loading" highlight-current-row class="mt14">
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品图片" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="商品标题" min-width="130">
          <template #default="scope">
            <el-tooltip placement="top" :open-delay="600">
              <template #content
                ><div>{{ scope.row.title }}</div></template
              >
              <span class="line2">{{ scope.row.title }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="商品简介" min-width="100">
          <template #default="scope">
            <el-tooltip placement="top" :open-delay="600">
              <template #content
                ><div>{{ scope.row.info }}</div></template
              >
              <span class="line2">{{ scope.row.info }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="活动名称" min-width="100">
          <template #default="scope">
            <el-tooltip placement="top" :open-delay="600">
              <template #content
                ><div>{{ scope.row.activity_name }}</div></template
              >
              <span class="line2">{{ scope.row.activity_name }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="售价" min-width="90">
          <template #default="scope">
            <span>{{ scope.row.product_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="秒杀价" min-width="90">
          <template #default="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.quota_show }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量剩余" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.quota }}</span>
          </template>
        </el-table-column>
        <el-table-column label="秒杀状态" min-width="90">
          <template #default="scope">
            <span>{{ scope.row.start_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="190">
          <template #default="scope">
            <p>开始：{{ scope.row.start_time }}</p>
            <p>结束：{{ scope.row.end_time }}</p>
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
        <el-table-column label="操作" fixed="right" width="150">
          <template #default="scope">
            <a v-if="scope.row.stop_status === 0" v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical" v-if="scope.row.stop_status === 0" />
            <a v-db-click @click="copy(scope.row)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除秒杀商品', scope.$index)">删除</a>
            <el-divider direction="vertical"></el-divider>
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
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { seckillListApi, seckillStatusApi, seckillTimeListApi, exportSeckillListApi } from '../../api';
import { formatDate } from '@/utils/validate';
import { modalSure } from '@/utils/public';
import exportExcel from '@/utils/newToExcel.js';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'seckillProductList' });

const route = useRoute();
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
  store_name: '',
  page: 1,
  limit: 15,
  time_ids: [],
  time: '',
  activity_name: '',
});
const timeVal = ref([]);
const total = ref(0);

// 原 filters.formatDate
function filterFormatDate(time) {
  if (time !== 0) {
    let date = new Date(time * 1000);
    return formatDate(date, 'yyyy-MM-dd');
  }
}

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
  router.push({ path: routePre + '/marketing/seckill/product/create' });
}
// 导出
async function exports() {
  let [th, filekey, data, fileName] = [[], [], [], ''];
  let excelData = JSON.parse(JSON.stringify(tableFrom.value));
  excelData.page = 1;
  excelData.limit = 200;
  for (let i = 0; i < excelData.page + 1; i++) {
    let lebData = await getExcelData(excelData);
    if (!fileName) fileName = lebData.filename;
    if (!filekey.length) {
      filekey = lebData.fileKey;
    }
    if (!th.length) th = lebData.header;
    if (lebData.export.length) {
      data = data.concat(lebData.export);
      excelData.page++;
    } else {
      exportExcel(th, filekey, fileName, data);
      return;
    }
  }
}
function getExcelData(excelData) {
  return new Promise((resolve, reject) => {
    exportSeckillListApi(excelData).then((res) => {
      resolve(res.data);
    });
  });
}
// 编辑
function edit(row) {
  router.push({
    path: routePre + '/marketing/seckill/product/create/' + row.id + '/0',
  });
}
// 一键复制
function copy(row) {
  router.push({
    path: routePre + '/marketing/seckill/product/create/' + row.id + '/1',
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `marketing/seckill/${row.id}`,
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
  tableFrom.value.product_id = route.params.product_id || '';
  seckillListApi(tableFrom.value)
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
  seckillStatusApi(data)
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
