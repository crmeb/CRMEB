<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="artFromRef"
          :model="artFrom"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="门店名称：">
            <el-select v-model="artFrom.store_id" clearable @change="userSearchs" class="form_content_width">
              <el-option v-for="item in storeSelectList" :value="item.id" :key="item.id" :label="item.name"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button v-auth="['merchant-store_staff-create']" type="primary" class="action-row" v-db-click @click="add"
        >添加核销员</el-button
      >
      <el-table
        :data="storeLists"
        ref="table"
        class="mt14"
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
        <el-table-column label="头像" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="核销员名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.staff_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="所属门店" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="150">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row.id, scope.row.status)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除核销员', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="artFrom.page"
          v-model:limit="artFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import {
  storeStaffApi,
  storeStaffCreateApi,
  merchantStoreListApi,
  storeStaffSetShowApi,
  storeStaffEditApi,
} from '@/api/setting';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import { useUserLevelStore } from '@/store/module/userLevel';

defineOptions({ name: 'setting_staff' });

const mediaStore = useMediaStore();
const userLevelStore = useUserLevelStore();

const table = ref(null);
const artFromRef = ref(null);

const isMobile = computed(() => mediaStore.isMobile);
const categoryId = computed(() => userLevelStore.categoryId);
const labelWidth = computed(() => (isMobile.value ? undefined : '90px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const grid = {
  xl: 10,
  lg: 10,
  md: 12,
  sm: 24,
  xs: 24,
};
const artFrom = reactive({
  page: 1,
  limit: 15,
  store_id: '',
});
const loading = ref(false);
const storeLists = ref([]);
const storeSelectList = ref([]);
const total = ref(0);

function storeList() {
  merchantStoreListApi().then((res) => {
    storeSelectList.value = res.data;
  });
}
function getList() {
  loading.value = true;
  storeStaffApi(artFrom)
    .then((res) => {
      loading.value = false;
      storeLists.value = res.data.list;
      total.value = res.data.count;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 搜索；
function userSearchs() {
  artFrom.page = 1;
  getList();
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `merchant/store_staff/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      storeLists.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 添加核销员；
function add() {
  modalForm(storeStaffCreateApi(0)).then(() => getList());
}
function onchangeIsShow(id, is_show) {
  storeStaffSetShowApi(id, is_show).then((res) => {
    ElMessage.success(res.msg);
    getList();
  });
}
function edit(id) {
  modalForm(storeStaffEditApi(id)).then(() => getList());
}

onMounted(() => {
  getList();
  storeList();
});
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
