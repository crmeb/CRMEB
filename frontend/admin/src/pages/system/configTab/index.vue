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
          <el-form-item label="是否显示：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="显示"></el-option>
              <el-option value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="分类名称：" label-for="status2">
            <el-input clearable placeholder="请输入分类名称" v-model="formValidate.title" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询分类</el-button>
          </el-form-item>
          <div>
            <el-form-item label="配置名称：" label-for="status2">
              <el-input clearable placeholder="请输入配置名称" v-model="config_name" class="form_content_width" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" v-db-click @click="searchConfig">查询配置</el-button>
            </el-form-item>
          </div>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button type="primary" v-db-click @click="classAdd" class="action-row mr20">添加配置分类</el-button>
      <vxe-table
        :border="false"
        class="vxeTable mt14"
        :loading="loading"
        ref="xTable"
        :tree-config="tabconfig"
        :data="classList"
        :row-config="{ keyField: 'id', isCurrent: true, isHover: true }"
      >
        <vxe-table-column field="id" title="ID" tooltip width="85"></vxe-table-column>
        <vxe-table-column field="title" tree-node title="分类名称" min-width="150"></vxe-table-column>
        <vxe-table-column field="eng_title" title="分类字段" min-width="150"></vxe-table-column>
        <vxe-table-column field="statuss" title="状态" width="250">
          <template v-slot="{ row }">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="row.status"
              :value="row.status"
              @change="onchangeIsShow(row)"
              size="large"
            >
            </el-switch>
          </template>
        </vxe-table-column>
        <vxe-table-column field="action" title="操作" width="160" fixed="right">
          <template v-slot="{ row, index }">
            <a v-db-click @click="goList(row)">配置列表</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="edit(row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(row, '删除分类', index)">删除</a>
          </template>
        </vxe-table-column>
      </vxe-table>
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

    <!-- 新建  编辑表单-->
    <edit-from ref="edits" :update="true" :FromData="FromData" @submitFail="submitFail"></edit-from>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import editFrom from '@/components/from/from';
import { classListApi, classAddApi, classEditApi, setStatusApi } from '@/api/system';
import { modalSure } from '@/utils/public';
import { authLapse } from '@/utils/authLapse';
import { useMediaStore } from '@/store/module/media';
import Setting from '@/setting';

defineOptions({ name: 'configTab' });

const router = useRouter();
const mediaStore = useMediaStore();

const xTable = ref(null);
const formValidateRef = ref(null);
const edits = ref(null);

const tabconfig = { childrenField: 'children', reserve: true, accordion: true };
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const formValidate = reactive({
  status: '',
  page: 1,
  limit: 15,
  title: '',
});
const total = ref(0);
const FromData = ref(null);
const classId = ref(0);
const classList = ref([]);
const config_name = ref('');

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// 跳转到配置列表页面
function goList(row) {
  router.push({
    path: Setting.routePre + '/system/config/system_config_tab/list/' + row.id,
  });
}
// 添加配置分类
function classAdd() {
  classAddApi()
    .then(async (res) => {
      FromData.value = res.data;
      edits.value.modals = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(row) {
  classEditApi(row.id)
    .then(async (res) => {
      if (res.data.status === false) {
        return authLapse(res.data);
      }
      FromData.value = res.data;
      edits.value.modals = true;
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
    url: `setting/config_class/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      // classList.value.splice(num, 1);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  formValidate.status = formValidate.status || '';
  classListApi(formValidate)
    .then(async (res) => {
      let data = res.data;
      classList.value = data.list;
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
//搜索配置项
function searchConfig() {
  if (config_name.value == '') {
    return ElMessage.error('请输入要搜索的配置名称');
  }
  // vue-router 4 中 path 内拼接的 query 会被丢弃，必须用 query 对象传递参数（/0 为路由动态参数保留在 path 中）
  router.push({
    path: Setting.routePre + '/system/config/system_config_tab/list/0',
    query: { config_name: config_name.value },
  });
}
// 修改成功
function submitFail() {
  //   getList();
}
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  setStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

onMounted(() => {
  getList();
});
</script>

<style scoped></style>
