<template>
  <div class="code-wapper">
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="routePre + '/system/code_generation_list'"
      description="按基础信息、字段配置、存放位置三步生成标准 CRUD 代码，减少重复开发。"
    ></pages-header>
    <div class="message mt10">
      <el-card :bordered="false" shadow="never" class="">
        <steps :stepList="headerList" :isActive="currentTab"></steps>
      </el-card>
    </div>
    <div class="pt10 tab-1" v-show="currentTab == '0'" v-loading="isLoading">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <FoundationForm
          ref="Foundation"
          :foundation="formItem.foundation"
          :tableField="tableField"
          @storageData="storageData"
        />
      </el-card>
    </div>
    <div class="pt10" v-show="currentTab == '1'">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <TableForm
          ref="TableFormRef"
          :foundation="formItem.foundation"
          :tableField="tableField"
          :id="id"
          @storageData="storageData"
        />
      </el-card>
    </div>
    <div class="pt10" v-show="currentTab == '2'">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <StorageLoc :storage="formItem.storage" />
      </el-card>
    </div>
    <el-card :bordered="false" class="fixed-card" :style="{ left: `${fixBottomWidth}` }" shadow="never">
      <el-button :disabled="!currentTab" class="mr20" v-db-click @click="beforeTab">上一步</el-button>
      <el-button type="primary" v-db-click @click="nextTab">{{ currentTab == 2 ? '提交' : '下一步' }}</el-button>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import { codeCrud } from '@/api/setting';
import FoundationForm from './components/FoundationFor.vue';
import TableForm from './components/TableForm.vue';
import StorageLoc from './components/StorageLoc.vue';
import { getMenusUnique as getMenusUniqueApi } from '@/api/systemMenus';
import { formatFlatteningRoutes } from '@/libs/system';
import { crudFilePath } from '@/api/systemCodeGeneration';
import { crudDet } from '@/api/systemCodeGeneration';
import { setStatus } from '@diy/api';
import steps from '@/components/steps/index';
import bus from '@/utils/bus';
import Setting from '@/setting';
import { useThemeConfigStore } from '@/store/module/themeConfig';
import { useMenusStore } from '@/store/module/menus';
import { useUserInfoStore } from '@/store/module/userInfo';
import { useRoutesListStore } from '@/store/module/routesList';
import { useKeepAliveNamesStore } from '@/store/module/keepAliveNames';

defineOptions({ name: 'system_code_generation' });

const route = useRoute();
const router = useRouter();
const themeConfigStore = useThemeConfigStore();
const menusStore = useMenusStore();
const userInfoStore = useUserInfoStore();
const routesListStore = useRoutesListStore();
const keepAliveNamesStore = useKeepAliveNamesStore();

const routePre = Setting.routePre;

const pageHeader = ref(null);
const Foundation = ref(null);
const TableFormRef = ref(null);

const currentTab = ref(0);
const headerList = ['基础信息', '字段配置', '存放位置'];
const formItem = reactive({
  foundation: {
    pid: '',
    tableName: '',
    modelName: '',
    isTable: 1,
    menuName: '',
  },
  tableForm: {},
  storage: {},
  field: {},
  formItem: {},
});
const ruleValidate = {
  foundation: {},
};
const tableField = ref([]);
const rowList = ref([]);
const reqloading = ref(false);
const isLoading = ref(false);
const id = ref('');

// 设置是否显示 tagsView
const fixBottomWidth = computed(() => {
  let { layout, isCollapse } = themeConfigStore.themeConfig;
  let w;
  if (['columns'].includes(layout)) {
    if (isCollapse) {
      w = '85px';
    } else {
      w = '265px';
    }
  } else if (['classic'].includes(layout)) {
    if (isCollapse) {
      w = '69px';
    } else {
      w = '190px';
    }
  } else if (['defaults', 'classic'].includes(layout)) {
    if (isCollapse) {
      w = '64px';
    } else {
      w = '180px';
    }
  } else {
    w = '0px';
  }
  return w;
});

function getDetail(pid) {
  isLoading.value = true;
  crudDet(pid)
    .then((res) => {
      let data = res.data.crudInfo.field;
      formItem.foundation.pid = Number(data.pid);
      formItem.foundation.tableName = data.tableName;
      formItem.foundation.modelName = data.modelName;
      formItem.foundation.menuName = data.menuName;
      TableFormRef.value.tableField = data.tableField;
      formItem.storage = data.filePath;
      let i = 0;
      data.tableField.map((e) => {
        if (e.field === 'create_time' || e.field === 'update_time') {
          i++;
          if (i == 2) TableFormRef.value.isCreate = true;
        }
        if (e.field === 'delete_time') {
          TableFormRef.value.isDelete = true;
        }
      });
      isLoading.value = false;
    })
    .catch((err) => {
      ElMessage.warning(err.msg);
    });
}
function storageData(data) {
  formItem.storage = data;
}
function beforeTab() {
  currentTab.value--;
}
function addRow() {
  let foundation = formItem.foundation;
  if (!foundation.tableName) return ElMessage.warning('请先填写表名');
  let data = {
    menuName: foundation.menuName,
    tableName: foundation.tableName,
    // isTable: foundation.isTable,
    fromField: [],
    columnField: [],
  };
  crudFilePath(data)
    .then((res) => {
      TableFormRef.value.tableField = res.data.tableField.length ? res.data.tableField : [];
      formItem.storage = res.data.makePath;
      if (!res.data.tableField.length) {
        TableFormRef.value.tableField.push({
          field: 'id',
          field_type: 'int',
          default: '',
          comment: '自增ID',
          required: false,
          is_table: true,
          table_name: 'ID',
          limit: '15',
          primaryKey: 1,
          from_type: '',
        });
      }
      currentTab.value++;
    })
    .catch((err) => {
      ElMessage.warning(err.msg);
    });
}
function nextTab() {
  if (currentTab.value == 0) {
    // if (!formItem.foundation.pid) return ElMessage.warning('请选择菜单');
    if (!formItem.foundation.tableName) return ElMessage.warning('请输入表名');
    if (!formItem.foundation.modelName) return ElMessage.warning('请输入模块名');
    if (!formItem.foundation.isTable) {
      if (!TableFormRef.value.tableField.length) return ElMessage.warning('请先添加表数据');
      if (TableFormRef.value.tableField.length)
        for (let i = 0; i < TableFormRef.value.tableField.length; i++) {
          const el = TableFormRef.value.tableField[i];
          if (
            ['addSoftDelete', 'addTimestamps'].indexOf(el.field_type) === -1 &&
            (!el.field || !el.field_type || !el.comment)
          ) {
            return ElMessage.warning('请完善sql表数据');
          }
        }
    }
    if (id.value) {
      return currentTab.value++;
    }
    addRow();
  } else if (currentTab.value == 2) {
    if (reqloading.value) return;
    let data = {
      ...formItem.foundation,
      filePath: formItem.storage,
      tableField: TableFormRef.value.tableField,
      deleteField: id.value ? TableFormRef.value.deleteField : [],
    };
    if (id.value) {
      data.id = id.value;
      ElMessageBox({
        title: '生成提醒',
        message: '重新提交会重新生成文件,删除、新增、修改的字段将直接从改表中进行修改,请慎重操作！！',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          saveCodeCrud(data, true);
        })
        .catch(() => {});
    } else {
      ElMessageBox({
        title: '生成提醒',
        message:
          '生成后本地开发调试会直接加载生成的vue页面；如果是上线后进行生成,可以进行浏览，代码生成列表中的修改文件将不生效。需要重新打包上线！',
        showCancelButton: true,
        cancelButtonText: '取消',
        confirmButtonText: '确定',
        iconClass: 'el-icon-warning',
        confirmButtonClass: 'btn-custom-cancel',
      })
        .then(() => {
          saveCodeCrud(data, true);
        })
        .catch(() => {});
    }
  } else {
    if (currentTab.value < 3) currentTab.value++;
  }
}
function saveCodeCrud(data, loading) {
  reqloading.value = true;
  codeCrud(data)
    .then((res) => {
      ElMessage.success(res.msg);
      getMenusUnique();
      reqloading.value = false;
      router.push({
        name: 'system_code_generation_list',
      });
    })
    .catch((err) => {
      reqloading.value = false;
      ElMessage.error(err.msg);
    });
}
function getMenusUnique() {
  getMenusUniqueApi().then((res) => {
    let data = res.data;
    userInfoStore.setUniqueAuth(data.uniqueAuth);
    menusStore.getmenusNav(data.menus);
    routesListStore.setRoutesList(data.menus);
    let arr = formatFlatteningRoutes(router.options.routes);
    formatTwoStageRoutes(arr);
    let routes = formatFlatteningRoutes(data.menus);
    menusStore.setOneLvRoute(routes);
    bus.emit('routesListChange');
  });
}
function formatTwoStageRoutes(arr) {
  if (arr.length <= 0) return false;
  const newArr = [];
  const cacheList = [];
  arr.forEach((v) => {
    if (v && v.meta && v.meta.keepAlive) {
      newArr.push({ ...v });
      cacheList.push(v.name);
      keepAliveNamesStore.setCacheKeepAlive(cacheList);
    }
  });
  return newArr;
}

if (route.query.id) {
  id.value = route.query.id;
  getDetail(route.query.id);
}
</script>
<style lang="scss" scoped>
.ivu-steps .ivu-steps-title {
  line-height: 26px;
}
.code-wapper {
  min-height: 800px;
  padding-bottom: 90px;
}
.btn {
  position: fixed;
  bottom: 10px;
  // height: 80px;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  background-color: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(4px);
  z-index: 2;
}
.tab-1 {
  padding-bottom: 100px;
}
:deep(.el-input__inner) {
  padding-left: 7px;
}
:deep(.ivu-form-item) {
  margin-bottom: 17px;
}
:deep(.ivu-form-item-error-tip) {
  padding-top: 2px;
}
:deep(.tip) {
  color: #bbb;
  line-height: 16px;
  padding-top: 5px;
  font-size: 12px;
}
</style>
