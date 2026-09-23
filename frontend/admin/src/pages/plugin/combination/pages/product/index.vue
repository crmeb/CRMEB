<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formRef"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          @submit.prevent
          inline
        >
          <el-form-item label="上架状态：">
            <el-select
              v-model="formValidate.is_show"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option :value="1" label="上架"></el-option>
              <el-option :value="0" label="下架"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="拼团搜索：" prop="store_name">
            <el-input
              clearable
              placeholder="请输入拼团名称/ID"
              v-model="formValidate.store_name"
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
      <el-button
        v-auth="['marketing-store_combination-create']"
        class="action-row"
        type="primary"
        v-db-click
        @click="add"
      >
        添加拼团商品
      </el-button>
      <el-button v-auth="['export-storeCombination']" class="action-row export" v-db-click @click="exports"
        >导出</el-button
      >
      <el-table :data="tableList" v-loading="loading" highlight-current-row class="mt14">
        <el-table-column label="ID" width="80">
          <template #default="scope"
            ><span>{{ scope.row.id }}</span></template
          >
        </el-table-column>
        <el-table-column label="拼团图片" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer><img v-lazy="scope.row.image" /></div>
          </template>
        </el-table-column>
        <el-table-column label="拼团名称" min-width="130">
          <template #default="scope">
            <el-tooltip placement="top" :show-after="600">
              <template #content
                ><div>{{ scope.row.title }}</div></template
              >
              <span class="line2">{{ scope.row.title }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="原价" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.ot_price }}</span></template
          >
        </el-table-column>
        <el-table-column label="拼团价" min-width="100">
          <template #default="scope"
            ><span>{{ scope.row.price }}</span></template
          >
        </el-table-column>
        <el-table-column label="拼团人数" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.count_people }}</span></template
          >
        </el-table-column>
        <el-table-column label="参与人数" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.count_people_all }}</span></template
          >
        </el-table-column>
        <el-table-column label="成团数量" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.count_people_pink }}</span></template
          >
        </el-table-column>
        <el-table-column label="限量" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.quota_show }}</span></template
          >
        </el-table-column>
        <el-table-column label="限量剩余" min-width="80">
          <template #default="scope"
            ><span>{{ scope.row.quota }}</span></template
          >
        </el-table-column>
        <el-table-column label="活动状态" min-width="100">
          <template #default="scope">
            <el-tag v-show="scope.row.start_name === '进行中'">进行中</el-tag>
            <el-tag type="warning" v-show="scope.row.start_name === '未开始'">未开始</el-tag>
            <el-tag type="info" v-show="scope.row.start_name === '已结束'">已结束</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="180">
          <template #default="scope">
            <p>开始：{{ scope.row.start_time }}</p>
            <p>结束：{{ scope.row.stop_time }}</p>
          </template>
        </el-table-column>
        <el-table-column label="上架状态" min-width="120">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
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
            <a v-if="scope.row.stop_status === 0" v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical" v-if="scope.row.stop_status === 0" />
            <a v-db-click @click="copy(scope.row)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除拼团商品', scope.$index)">删除</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="viewInfo(scope.row)">统计</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="formValidate.page"
          v-model:limit="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { combinationListApi, combinationSetStatusApi, storeCombinationApi } from '../../api';
import exportExcel from '@/utils/newToExcel.js';
import { modalSure } from '@/utils/public';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'combinationProductList' });

const route = useRoute();
const router = useRouter();
const mediaStore = useMediaStore();

const routePre = setting.routePre;

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const formRef = ref(null);
const loading = ref(false);
const formValidate = ref({ is_show: '', store_name: '', page: 1, limit: 15 });
const tableList = ref([]);
const total = ref(0);

onMounted(() => {
  getList();
});

async function exports() {
  let [th, filekey, data, fileName] = [[], [], [], ''];
  let excelData = JSON.parse(JSON.stringify(formValidate.value));
  excelData.page = 1;
  excelData.limit = 200;
  for (let i = 0; i < excelData.page + 1; i++) {
    let lebData = await getExcelData(excelData);
    if (!fileName) fileName = lebData.filename;
    if (!filekey.length) filekey = lebData.fileKey;
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
  return new Promise((resolve) => {
    storeCombinationApi(excelData).then((res) => resolve(res.data));
  });
}
function add() {
  router.push({ path: routePre + '/marketing/combination/create' });
}
function edit(row) {
  router.push({
    path: routePre + '/marketing/combination/create/' + row.id + '/0',
  });
}
function copy(row) {
  router.push({
    path: routePre + '/marketing/combination/create/' + row.id + '/1',
  });
}
function del(row, tit, num) {
  let delfromData = { title: tit, num: num, url: `marketing/combination/${row.id}`, method: 'DELETE', ids: '' };
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
    path: routePre + '/marketing/combination/statistics/' + row.id,
  });
}
function getList() {
  loading.value = true;
  formValidate.value.product_id = route.params.product_id || '';
  combinationListApi(formValidate.value)
    .then(async (res) => {
      tableList.value = res.data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function userSearchs() {
  formValidate.value.page = 1;
  getList();
}
function onchangeIsShow(row) {
  let data = { id: row.id, status: row.is_show };
  combinationSetStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
      row.is_show = !row.is_show;
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
