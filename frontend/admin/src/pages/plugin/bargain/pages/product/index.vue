<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="tableFrom" :label-width="labelWidth" :label-position="labelPosition" @submit.prevent inline>
          <el-form-item label="上架状态：">
            <el-select
              placeholder="请选择"
              v-model="tableFrom.status"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="上架"></el-option>
              <el-option value="0" label="下架"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="商品搜索：" label-for="store_name">
            <el-input
              clearable
              placeholder="请输入砍价名称，ID"
              v-model="tableFrom.store_name"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button v-auth="['marketing-store_bargain-create']" type="primary" class="action-row" v-db-click @click="add"
        >添加砍价商品</el-button
      >
      <el-button
        v-auth="['export-storeBargain']"
        class="action-row export"
        icon="ios-share-outline"
        v-db-click
        @click="exportList"
        >导出</el-button
      >
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        class="mt14"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <div>{{ scope.row.id }}</div>
          </template>
        </el-table-column>
        <el-table-column label="砍价图片" min-width="80">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="砍价名称" min-width="150">
          <template #default="scope">
            <el-tooltip placement="top" :open-delay="600">
              <template #content
                ><div>{{ scope.row.title }}</div></template
              >
              <span class="line2">{{ scope.row.title }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="砍价价格" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="最低价" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.min_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="参与人数" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.count_people_all }}</div>
          </template>
        </el-table-column>
        <el-table-column label="帮忙砍价人数" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.count_people_help }}</div>
          </template>
        </el-table-column>
        <el-table-column label="砍价成功人数" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.count_people_success }}</div>
          </template>
        </el-table-column>
        <el-table-column label="限量" min-width="80">
          <template #default="scope">
            <div>{{ scope.row.quota_show }}</div>
          </template>
        </el-table-column>
        <el-table-column label="限量剩余" min-width="80">
          <template #default="scope">
            <div>{{ scope.row.quota }}</div>
          </template>
        </el-table-column>
        <el-table-column label="活动状态" min-width="100">
          <template #default="scope">
            <el-tag size="default" v-show="scope.row.start_name === '进行中'">进行中</el-tag>
            <el-tag size="default" type="warning" v-show="scope.row.start_name === '未开始'">未开始</el-tag>
            <el-tag size="default" type="info" v-show="scope.row.start_name === '已结束'">已结束</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="180">
          <template #default="scope">
            <p>开始：{{ scope.row.start_time }}</p>
            <p>结束：{{ scope.row.stop_time }}</p>
          </template>
        </el-table-column>
        <el-table-column label="上架状态" min-width="100">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="上架"
              inactive-text="下架"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-if="scope.row.stop_status === 0" v-db-click @click="edit(scope.row, 0)">编辑</a>
            <el-divider v-if="scope.row.stop_status === 0" direction="vertical" />
            <a v-db-click @click="edit(scope.row, 1)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除砍价商品', scope.$index)">删除</a>
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
import { computed, onActivated, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { bargainListApi, bargainSetStatusApi } from '@/api/marketing';
import { formatDate } from '@/utils/validate';
import { exportBargainList } from '@/api/export';
import { modalSure } from '@/utils/public';
import exportExcel from '@/utils/newToExcel.js';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'bargainProduct' });

const route = useRoute();
const router = useRouter();
const routePre = setting.routePre;
const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const loading = ref(false);
const tableList = ref([]);
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
});
const tableFrom2 = ref({
  status: '',
  store_name: '',
  export: 1,
});
const total = ref(0);

// 原 filters：转为普通 function
function formatDateFilter(time) {
  if (time !== 0) {
    let date = new Date(time * 1000);
    return formatDate(date, 'yyyy-MM-dd hh:mm:ss');
  }
}

getList();

onActivated(() => {
  getList();
});

// 添加
function add() {
  router.push({ path: routePre + '/marketing/bargain/create' });
}
// 用户导出
async function exportList() {
  tableFrom.value.status = tableFrom.value.status || '';
  let [th, filekey, data, fileName] = [[], [], [], ''];
  let excelData = JSON.parse(JSON.stringify(tableFrom.value));
  excelData.page = 1;
  excelData.limit = 50;
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
    exportBargainList(excelData).then((res) => {
      resolve(res.data);
    });
  });
}
// 编辑 / 复制  type 0 编辑 1 复制
function edit(row, type) {
  router.push({
    path: routePre + `/marketing/bargain/create/${row.id}/${type}`,
  });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `marketing/bargain/${row.id}`,
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
    path: routePre + '/marketing/bargain/statistics/' + row.id,
  });
}
// 列表
function getList() {
  loading.value = true;
  tableFrom.value.status = tableFrom.value.status || '';
  tableFrom.value.product_id = route.params.product_id || '';
  bargainListApi(tableFrom.value)
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
  bargainSetStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
      getList();
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
