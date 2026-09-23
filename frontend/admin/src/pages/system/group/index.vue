<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="数据搜索：">
            <el-input
              clearable
              placeholder="请输入ID,KEY,数据组名称,简介"
              v-model="formValidate.title"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button type="primary" v-db-click @click="groupAdd('添加数据组')" class="action-row mr20"
        >添加数据组</el-button
      >
      <el-table
        :data="tabList"
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
        <el-table-column label="KEY" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.config_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="数据组名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="简介" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.info }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="goList(scope.row)">数据列表</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="edit(scope.row, '编辑')">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除数据组', scope.$index)">删除</a>
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
    <!-- 新增 编辑-->
    <group-from
      ref="groupfroms"
      :titleFrom="titleFrom"
      :groupId="groupId"
      :addId="addId"
      @getList="getList"
    ></group-from>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import groupFrom from './components/groupFrom';
import { groupListApi } from '@/api/system';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import Setting from '@/setting';

defineOptions({ name: 'group' });

const router = useRouter();
const mediaStore = useMediaStore();

const table = ref(null);
const formValidateRef = ref(null);
const groupfroms = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const formValidate = reactive({
  page: 1,
  limit: 15,
  title: '',
});
const loading = ref(false);
const tabList = ref([]);
const total = ref(0);

const FromData = ref(null);
const titleFrom = ref('');
const groupId = ref(0);
const addId = ref('');

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// 跳转到组合数据列表页面
function goList(row) {
  router.push({
    path: Setting.routePre + '/system/config/system_group/list/' + row.id,
  });
}
// 列表
function getList() {
  loading.value = true;
  groupListApi(formValidate)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  formValidate.page = 1;
  getList();
}
// 点击添加
function groupAdd(title) {
  groupfroms.value.modals = true;
  titleFrom.value = title;
  addId.value = 'addId';
  groupId.value = 0;
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `setting/group/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tabList.value.splice(num, 1);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(row, title) {
  titleFrom.value = title;
  groupId.value = row.id;
  groupfroms.value.fromData(row.id);
  groupfroms.value.modals = true;
  addId.value = '';
}

onMounted(() => {
  getList();
});
</script>

<style scoped></style>
