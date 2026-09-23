<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="levelFrom" :label-width="labelWidth" :label-position="labelPosition" inline @submit.prevent>
          <el-form-item label="等级状态：" label-for="status1">
            <el-select
              v-model="levelFrom.is_show"
              placeholder="请选择"
              clearable
              element-id="status1"
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="显示"></el-option>
              <el-option value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="等级名称：" label-for="title">
            <el-input clearable v-model="levelFrom.title" placeholder="请输入等级名称" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button v-auth="['admin-user-level_add']" type="primary" class="action-row" v-db-click @click="add"
        >添加用户等级</el-button
      >
      <el-table
        :data="levelLists"
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
        <el-table-column label="等级图标" min-width="100">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.icon" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="等级背景图" min-width="100">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="等级名称" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="等级" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.grade }}</span>
          </template>
        </el-table-column>
        <el-table-column label="享受折扣" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.discount }}</span>
          </template>
        </el-table-column>
        <el-table-column label="经验值要求" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.exp_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否显示" min-width="100">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              size="large"
              @change="onchangeIsShow(scope.row)"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="操作" width="100">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除等级', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="levelFrom.page"
          v-model:limit="levelFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>
<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import { useUserLevelStore } from '@/store/module/userLevel';
import { levelListApi, setShowApi, createApi } from '@/api/user';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'user_level' });

const { isMobile } = storeToRefs(useMediaStore());
const userLevelStore = useUserLevelStore();

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const levelFrom = ref({
  is_show: '',
  title: '',
  page: 1,
  limit: 15,
});
const levelLists = ref([]);
const total = ref(0);
const FromData = ref(null);
const imgName = ref('');
const visible = ref(false);
const levelId = ref(0);
const modalTitleSs = ref('');
const titleType = ref('level');
const num = ref(0);

// created
getList();

// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `user/user_level/delete/${row.id}`,
    method: 'put',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      levelLists.value.splice(num, 1);
      total.value--;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    is_show: row.is_show,
  };
  setShowApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

// 等级列表
function getList() {
  loading.value = true;
  levelFrom.value.is_show = levelFrom.value.is_show || '';
  levelListApi(levelFrom.value)
    .then(async (res) => {
      let data = res.data;
      levelLists.value = data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

// 添加
function add() {
  levelId.value = 0;
  modalForm(createApi({ id: levelId.value })).then(() => getList());
}

// 编辑
function edit(row) {
  levelId.value = row.id;
  modalForm(createApi({ id: levelId.value })).then(() => getList());
  userLevelStore.getlevelId(levelId.value);
}

// 表格搜索
function userSearchs() {
  levelFrom.value.page = 1;
  getList();
}

// 修改成功
function submitFail() {
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
</style>
