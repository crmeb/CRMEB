<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="`配置列表${$route.query.config_name ? ` - ` + $route.query.config_name : ''}`"
      :backUrl="$routeProStr + '/system/config/system_config_tab/index'"
      description="查看并维护当前配置分类下的配置项，支持自定义字段类型与显示隐藏。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-row v-if="!$route.query.config_name" class="action-row">
        <el-col v-bind="grid">
          <!-- <el-button type="primary" v-db-click @click="goIndex">配置分类</el-button> -->
          <el-button type="primary" v-db-click @click="configureAdd">添加配置</el-button>
        </el-col>
      </el-row>
      <el-table
        :data="classList"
        ref="table"
        v-loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        class="mt14"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="配置名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.info }}</span>
          </template>
        </el-table-column>
        <el-table-column label="字段变量" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.menu_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="字段类型" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="值" min-width="130">
          <template #default="scope">
            <span
              v-if="
                scope.row.type === 'text' ||
                scope.row.type === 'textarea' ||
                scope.row.type === 'radio' ||
                scope.row.type === 'checkbox'
              "
              >{{ scope.row.value }}</span
            >
            <div class="valBox acea-row" v-if="scope.row.type === 'upload' && scope.row.upload_type === 3">
              <div v-if="scope.row.value.length">
                <div
                  class="valPicbox acea-scope.row scope.row-column-around"
                  v-for="(item, index) in scope.row.value"
                  :key="index"
                >
                  <div class="valPicbox_pic"><i class="el-icon-document" /></div>
                  <span class="valPicbox_sp">{{ item.filename }}</span>
                </div>
              </div>
            </div>
            <div class="valBox acea-row" v-if="scope.row.type === 'upload' && scope.row.upload_type !== 3">
              <div v-if="scope.row.value.length">
                <div class="valPicbox acea-row row-column-around" v-for="(item, index) in scope.row.value" :key="index">
                  <div class="valPicbox_pic"><img v-lazy="item.filepath" /></div>
                  <span class="valPicbox_sp">{{ item.filename }}</span>
                </div>
              </div>
            </div>
            <span v-if="scope.row.type === 'switch'">{{ scope.row.value == 1 ? '开启' : '关闭' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="关联配置/值" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.link_data }}</span>
          </template>
        </el-table-column>
        <el-table-column label="配置分类" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.config_tab_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否显示" min-width="130">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="显示"
              inactive-text="隐藏"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除分类', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 新建 表单-->
    <el-dialog
      v-model="modals2"
      :title="`${rowId ? '修改' : '添加'}配置字段`"
      :close-on-click-modal="false"
      :show-close="true"
      width="720px"
    >
      <el-tabs v-if="!rowId" v-model="typeFrom.type" @tab-change="onhangeTab" class="tabsName">
        <el-tab-pane label="文本框 " name="0"></el-tab-pane>
        <el-tab-pane label="多行文本框" name="1"></el-tab-pane>
        <el-tab-pane label="单选框" name="2"></el-tab-pane>
        <el-tab-pane label="文件上传" name="3"></el-tab-pane>
        <el-tab-pane label="多选框" name="4"></el-tab-pane>
        <el-tab-pane label="下拉框" name="5"></el-tab-pane>
        <el-tab-pane label="开关" name="6"></el-tab-pane>
      </el-tabs>
      <form-create
        v-if="rules.length != 0"
        :rule="rules"
        v-model:api="fapi"
        :option="config"
        @submit="onSubmit"
        class="formBox"
        ref="fc"
        handleIcon="false"
      ></form-create>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="modals2 = false">取消</el-button>
          <el-button type="primary" v-db-click @click="submitForm">确定</el-button>
        </span></template
      >
    </el-dialog>
    <!-- 编辑表单-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { configTabListApi, configTabAddApi, configTabEditApi, configSetStatusApi } from '@/api/system';
import editFrom from '@/components/from/from';
import request from '@/libs/request';
import { modalSure } from '@/utils/public';
import { authLapse } from '@/utils/authLapse';
import Setting from '@/setting';

defineOptions({ name: 'list' });

const route = useRoute();
const router = useRouter();

const pageHeader = ref(null);
const table = ref(null);
const edits = ref(null);
const fc = ref(null);

const modals2 = ref(false);
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const formValidate = reactive({
  tab_id: 0,
  config_name: '',
  page: 1,
  limit: 15,
});
const config = {
  form: {
    labelWidth: '100px',
  },
  resetBtn: false,
  submitBtn: false,
  global: {
    upload: {
      props: {
        onSuccess(res, file) {
          if (res.status === 200) {
            file.url = res.data.src;
          } else {
            ElMessage.error(res.msg);
          }
        },
      },
    },
  },
};
const total = ref(0);
const FromData = ref(null);
const FromRequestData = ref({});
const modalTitleSs = ref('');
const classList = ref([]);
const num = ref(0);
const typeFrom = reactive({
  type: 0,
  tab_id: route.params.id,
});
const rules = ref([]);
const fapi = ref(null);
const rowId = ref(0);

watch(route, {
  handler: function (val, oldVal) {
    getList();
  },
  // 深度观察监听
  deep: true,
});

// 点击tab
function onhangeTab() {
  classAdd();
}
function submitForm() {
  fapi.value.submit();
}
// 新增表单
function classAdd() {
  configTabAddApi(typeFrom)
    .then(async (res) => {
      if (res.data.status === false) {
        return authLapse(res.data);
      }
      let data = res.data || {};
      FromRequestData.value = { action: data.action, method: data.method };
      rules.value = data.rules;
      modals2.value = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑表单
function edit(row) {
  rowId.value = row.id;
  configTabEditApi(row.id)
    .then(async (res) => {
      if (res.data.status === false) {
        return authLapse(res.data);
      }
      let data = res.data || {};
      FromRequestData.value = { action: data.action, method: data.method };
      rules.value = data.rules;
      edits.value.modals = true;
      modals2.value = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 提交表单
function onSubmit(formData) {
  request({
    url: FromRequestData.value.action,
    method: FromRequestData.value.method,
    data: formData,
  })
    .then((res) => {
      ElMessage.success(res.msg);
      setTimeout(() => {
        modals2.value = false;
      }, 1000);
      setTimeout(() => {
        getList();
      }, 1500);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 修改成功
function submitFail() {
  getList();
}
// 跳转到配置分类页面
function goIndex() {
  router.push({
    path: Setting.routePre + '/system/config/system_config_tab/index',
  });
}
// 添加配置
function configureAdd() {
  // modals2.value = true;
  rowId.value = 0;
  typeFrom.type = 0;
  classAdd();
}
// 列表
function getList() {
  loading.value = true;
  formValidate.tab_id = route.params.id;
  formValidate.config_name = route.query.config_name;
  configTabListApi(formValidate)
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
function pageChange(index) {
  formValidate.page = index;
  getList();
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `/setting/config/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      classList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 修改是否显示
function onchangeIsShow(row) {
  configSetStatusApi(row.id, row.status)
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
<style lang="scss" scoped>
.tabsName {
  margin-bottom: 15px;
}
.valBox {
  margin: 10px 0;
}
.valPicbox {
  border: 1px solid #e7eaec;
}
.valPicbox_pic {
  width: 200px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;

  img {
    width: 100%;
    height: 100%;
  }
  :deep(.ivu-icon-md-document) {
    font-size: 70px;
    color: #dadada;
  }
}
.valPicbox_sp {
  display: block;
  font-size: 12px;
  width: 200px;
  padding: 7px;
  box-sizing: border-box;
  border-top: 1px solid #e7eaec;
}
</style>
