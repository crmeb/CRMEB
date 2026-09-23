<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row>
        <el-col v-bind="grid">
          <el-button v-auth="['admin-template']" type="primary" v-db-click @click="add">添加模板</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="list"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="页面ID" width="90">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="页面名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="页面类型" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.template_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="更新时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.update_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <div style="display: inline-block" v-if="scope.row.status != 1">
              <a v-db-click @click="setStatus(scope.row, index)">设为首页</a>
            </div>
            <el-divider direction="vertical" v-if="scope.row.status != 1" />
            <div style="display: inline-block" v-if="scope.row.status || scope.row.type">
              <a v-db-click @click="edit(scope.row)">编辑</a>
            </div>
            <el-divider direction="vertical" v-if="scope.row.status || scope.row.type" />
            <el-dropdown @command="changeMenu(scope.row, index, $event)" :transfer="true">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
              <template #dropdown
                ><el-dropdown-menu>
                  <el-dropdown-item command="1" v-show="!scope.row.type">设置默认数据</el-dropdown-item>
                  <el-dropdown-item command="2" v-show="!scope.row.type">恢复默认数据</el-dropdown-item>
                  <el-dropdown-item command="3" v-show="scope.row.id != 1">删除模板</el-dropdown-item>
                </el-dropdown-menu></template
              >
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <el-dialog
      v-model="isTemplate"
      title="开发移动端链接"
      width="470px"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <div class="article-manager">
        <el-card :bordered="false" shadow="never" class="ivu-mt">
          <el-form
            ref="formItemRef"
            :model="formItem"
            label-width="120px"
            label-position="right"
            :rules="ruleValidate"
            @submit.prevent
          >
            <el-row :gutter="24">
              <el-col :span="24">
                <el-col v-bind="grid">
                  <el-form-item label="开发移动端链接：" prop="link" label-for="link">
                    <el-input v-model="formItem.link" placeholder="http://localhost:8080" />
                  </el-form-item>
                </el-col>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="cancel">取消</el-button>
          <el-button type="primary" v-db-click @click="handleSubmit()">提交</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import {
  diyList,
  diyDel,
  setStatus as setStatusApi,
  recovery as recoveryApi,
  getDiyCreate,
  getRecovery,
} from '@diy/api';
import { getCookies, setCookies } from '@/libs/util';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import Setting from '@/setting';

defineOptions({ name: 'devise_list' });

const router = useRouter();

const table = ref(null);
const formItemRef = ref(null);

const grid = {
  xl: 18,
  lg: 18,
  md: 18,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const list = ref([]);
const isTemplate = ref(false);
const formItem = reactive({
  id: 0,
  link: '',
});
const ruleValidate = {
  link: [{ required: true, message: '请输入移动端链接', trigger: 'blur' }],
};

function cancel() {
  formItemRef.value.resetFields();
}
function handleSubmit() {
  formItemRef.value.validate((valid) => {
    if (valid) {
      setCookies('moveLink', formItem.link);
      router.push({
        path: Setting.routePre + '/setting/pages/diy',
        query: { id: formItem.id, type: 1 },
      });
    } else {
      return false;
    }
  });
}
function changeMenu(row, index, name) {
  switch (name) {
    case '1':
      setDefault(row);
      break;
    case '2':
      recovery(row);
      break;
    case '3':
      del(row, '删除此模板', index);
      break;
    default:
  }
}
//设置默认数据
function setDefault(row) {
  getRecovery(row.id)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
// 添加
function add() {
  modalForm(getDiyCreate()).then(() => getList());
}
// 获取列表
function getList() {
  loading.value = true;
  diyList().then((res) => {
    loading.value = false;
    list.value = res.data.list;
  });
}
// 编辑
function edit(row) {
  formItem.id = row.id;
  if (row.type) {
    isTemplate.value = true;
  } else {
    router.push({ path: Setting.routePre + '/setting/pages/diy', query: { id: row.id, type: 0 } });
  }
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `diy/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 使用模板
function setStatus(row) {
  setStatusApi(row.id)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
function recovery(row) {
  recoveryApi(row.id).then((res) => {
    ElMessage.success(res.msg);
    getList();
  });
}

formItem.link = getCookies('moveLink');
getList();
</script>

<style scoped></style>
