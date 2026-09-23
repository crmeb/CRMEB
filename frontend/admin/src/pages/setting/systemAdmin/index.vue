<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          label-width="80px"
          label-position="right"
          @submit.prevent
          inline
        >
          <el-form-item label="状态：" label-for="status1">
            <el-select v-model="status" placeholder="请选择" @change="userSearchs" clearable class="form_content_width">
              <el-option value="all" label="全部"></el-option>
              <el-option value="1" label="开启"></el-option>
              <el-option value="0" label="关闭"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：" label-for="status2">
            <el-input
              clearable
              placeholder="请输入姓名或者账号"
              v-model="formValidate.name"
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
      <el-button v-auth="['setting-system_admin-add']" type="primary" class="action-row" v-db-click @click="add"
        >添加管理员</el-button
      >
      <el-table
        :data="list"
        class="mt14"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        v-loading="loading"
        highlight-current-row
      >
        <el-table-column label="姓名" width="120">
          <template #default="scope">
            <span>{{ scope.row.real_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="账号" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.account }}</span>
          </template>
        </el-table-column>
        <el-table-column label="身份" min-width="130">
          <template #default="scope">
            <div v-if="scope.row.roles.length !== 0">
              <el-tag v-for="(item, index) in scope.row.roles.split(',')" :key="index">{{ item }}</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="最后一次登录时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row._last_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="最后一次登录ip" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.last_ip }}</span>
          </template>
        </el-table-column>
        <el-table-column label="开启" min-width="70">
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
        <el-table-column label="操作" fixed="right" width="100">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除管理员', scope.$index)">删除</a>
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
    <!-- 添加 编辑 -->
    <admin-from :FromData="FromData" ref="adminfrom" @submitFail="submitFail"></admin-from>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { adminListApi, adminFromApi, adminEditFromApi, setShowApi } from '@/api/systemAdmin';
import adminFrom from '../../../components/from/from';
import { modalSure } from '@/utils/public';
import { authLapse } from '@/utils/authLapse';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'systemAdmin' });

const mediaStore = useMediaStore();

const formValidateRef = ref(null);
const adminfrom = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const total = ref(0);
const loading = ref(false);
const roleData = {
  status1: '',
};
const formValidate = reactive({
  roles: '',
  status: '',
  name: '',
  page: 1, // 当前页
  limit: 15, // 每页显示条数
});
const status = ref('');
const list = ref([]);
const FromData = ref(null);
const modalTitleSs = ref('');
const ids = Number;

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '50px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'left'));

// 修改是否开启
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  setShowApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 请求列表
function submitFail() {
  getList();
}
// 列表
function getList() {
  loading.value = true;
  formValidate.roles = formValidate.roles || '';
  adminListApi(formValidate)
    .then(async (res) => {
      total.value = res.data.count;
      list.value = res.data.list;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 添加表单
function add() {
  adminFromApi()
    .then(async (res) => {
      FromData.value = res.data;
      adminfrom.value.modals = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(row) {
  adminEditFromApi(row.id)
    .then(async (res) => {
      if (res.data.status === false) {
        return authLapse(res.data);
      }
      FromData.value = res.data;
      adminfrom.value.modals = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `setting/admin/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      list.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  formValidate.status = status.value === 'all' ? '' : status.value;
  formValidate.page = 1;
  list.value = [];
  getList();
}

getList();
</script>

<style scoped></style>
