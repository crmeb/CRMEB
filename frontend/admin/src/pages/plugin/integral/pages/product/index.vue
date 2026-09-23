<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="tableFrom" :label-width="labelWidth" label-position="right" @submit.prevent inline>
          <el-form-item label="创建时间：">
            <date-range-picker
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="上架状态：">
            <el-select
              placeholder="请选择"
              clearable
              v-model="tableFrom.is_show"
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="上架"></el-option>
              <el-option value="0" label="下架"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="商品搜索：" label-for="store_name">
            <el-input
              placeholder="请输入商品名称，ID"
              v-model="tableFrom.store_name"
              clearable
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
        v-auth="['marketing-store_integral-create']"
        type="primary"
        icon="md-add"
        v-db-click
        @click="add"
        class="action-row mr10"
        >添加积分商品</el-button
      >
      <el-table :data="tableList" v-loading="loading" highlight-current-row class="mt14">
        <el-table-column label="ID" min-width="50">
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
        <el-table-column label="兑换积分" min-width="60">
          <template #default="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量" min-width="60">
          <template #default="scope">
            <span>{{ scope.row.quota_show }}</span>
          </template>
        </el-table-column>
        <el-table-column label="限量剩余" min-width="60">
          <template #default="scope">
            <span>{{ scope.row.quota }}</span>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="排序" min-width="60">
          <template #default="scope">
            <span>{{ scope.row.sort }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="80">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="上架"
              inactive-text="下架"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="200">
          <template #default="scope">
            <a v-db-click @click="orderList(scope.row)">兑换记录</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="copy(scope.row)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除积分商品', scope.$index)">删除</a>
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
import { integralProductListApi, integralIsShowApi } from '../../api';
import { formatDate } from '@/utils/validate';
import { modalSure } from '@/utils/public';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'integralProductList' });

const router = useRouter();
const routePre = setting.routePre;
const mediaStore = useMediaStore();

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const loading = ref(false);
const tableList = ref([]);
const timeVal = ref([]);
const tableFrom = ref({
  integral_time: '',
  is_show: '',
  store_name: '',
  page: 1,
  limit: 15,
});
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
});

onActivated(() => {
  getList();
});

function add() {
  router.push({ path: routePre + '/marketing/integral/product/create' });
}
function orderList(row) {
  router.push({
    path: routePre + '/marketing/integral/order',
    query: { product_id: row.id },
  });
}
function edit(row) {
  router.push({
    path: routePre + '/marketing/integral/product/create/' + row.id + '/0',
  });
}
function copy(row) {
  router.push({
    path: routePre + '/marketing/integral/product/create/' + row.id + '/1',
  });
}
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `marketing/integral/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableFrom.value.page = 1;
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function getList() {
  loading.value = true;
  tableFrom.value.is_show = tableFrom.value.is_show || '';
  integralProductListApi(tableFrom.value)
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
function userSearchs() {
  tableFrom.value.page = 1;
  getList();
}
function onchangeTime(e) {
  timeVal.value = e;
  tableFrom.value.integral_time = timeVal.value ? timeVal.value.join('-') : '';
  getList();
}
function onchangeIsShow(row) {
  let data = { id: row.id, is_show: row.is_show };
  integralIsShowApi(data)
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
