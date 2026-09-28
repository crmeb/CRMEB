<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="roleDataRef"
          :model="roleData"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="按钮名称：" prop="status2" label-for="status2">
            <el-input clearable v-model="roleData.keyword" placeholder="请输入按钮名称" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="getData">查询</el-button>
          </el-form-item>
          <!-- <el-row >
            <el-col v-bind="grid">
              <el-button type="primary" v-db-click @click="menusAdd('添加规则')">添加规则 </el-button>
            </el-col>
          </el-row> -->
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <vxe-table
        :border="false"
        class="vxeTable"
        :loading="loading"
        ref="xTable"
        header-row-class-name="false"
        :tree-config="tabconfig"
        :data="tableData"
        :row-config="{ keyField: 'id', isCurrent: true, isHover: true }"
      >
        <vxe-table-column field="menu_name" tree-node title="按钮名称" min-width="100"></vxe-table-column>
        <vxe-table-column field="menu_path" title="类型" min-width="240" tooltip="true">
          <template v-slot="{ row }">
            <span v-if="row.auth_type == 1">菜单：{{ row.menu_path }}</span>
            <span v-if="row.auth_type == 3">按钮</span>
            <span v-if="row.auth_type == 2">数据权限</span>
          </template>
        </vxe-table-column>
        <vxe-table-column field="sort" title="排序" width="150"></vxe-table-column>
        <vxe-table-column field="flag" title="是否显示" width="150">
          <template v-slot="{ row }">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="row.is_show_path"
              :value="row.is_show_path"
              @change="onchangeIsShow(row)"
              size="large"
            >
            </el-switch>
          </template>
        </vxe-table-column>
        <vxe-table-column field="date" title="操作" align="center" width="150" fixed="right">
          <template v-slot="{ row }">
            <a v-db-click @click="edit(row, '编辑')">编辑</a>
          </template>
        </vxe-table-column>
      </vxe-table>
    </el-card>
    <menus-from
      :formValidate="formValidate"
      :titleFrom="titleFrom"
      @getList="getList"
      @changeMenu="changeMenu"
      ref="menusFromRef"
      @clearFrom="clearFrom"
    ></menus-from>
    <el-dialog v-model="ruleModal" width="1100px" title="权限列表" @closed="modalchange">
      <div class="search-rule">
        <el-alert
          title="基础接口，可多选，并且添加后不会再展示出现；删除权限后才会出现；公共接口，可多选，并且添加后会继续展示；"
        ></el-alert>
        <el-input
          class="mr10"
          v-model="searchRule"
          placeholder="输入关键词搜索"
          clearable
          style="width: 300px"
          ref="search"
          @on-enter="searchRules"
          @on-clear="searchRules"
        />
        <el-button type="primary" v-db-click @click="searchRules">搜索</el-button>
        <el-button v-db-click @click="init">重置</el-button>
      </div>
      <div class="route-list">
        <div class="tree">
          <el-tree
            ref="treeBox"
            :data="ruleCateList"
            :highlight-current="true"
            :props="defaultProps"
            node-key="id"
            :default-expanded-keys="expandedKeys"
            :current-node-key="nodeKey"
            @node-click="handleNodeClick"
          ></el-tree>
        </div>
        <div class="rule">
          <div
            class="rule-list"
            v-show="!arrs.length || arrs.includes(item.id)"
            :class="{ 'select-rule': seletRouteIds.includes(item.id) }"
            v-for="(item, index) in children"
            :key="index"
            v-db-click
            @click="selectRule(item)"
          >
            <div>接口名称：{{ item.name }}</div>
            <div>请求方式：{{ item.method }}</div>
            <div>接口地址：{{ item.path }}</div>
          </div>
        </div>
      </div>
      <!-- <el-tabs v-model="routeType" @on-click="changTab">
        <el-tab-pane :label="item.name" :name="'' + index" v-for="(item, index) in foundationList" :key="item"></el-tab-pane>
      </el-tabs> -->
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="ruleModal = false">取 消</el-button>
          <el-button type="primary" v-db-click @click="addRouters">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import {
  getTable,
  menusDetailsApi,
  isShowApi,
  editMenus,
  getRuleList,
  menusBatch,
  getMenusUnique as getMenusUniqueApi,
  menusRuleCate,
} from '@/api/systemMenus';
import menusFrom from './components/menusFrom';
import { formatFlatteningRoutes, findFirstNonNullChildren, findFirstNonNullChildrenKeys } from '@/libs/system';
import { modalSure } from '@/utils/public';
import bus from '@/utils/bus';
import { useLayoutStore } from '@/store/module/layout';
import { useMenusStore } from '@/store/module/menus';
import { useUserInfoStore } from '@/store/module/userInfo';
import { useRoutesListStore } from '@/store/module/routesList';
import { useKeepAliveNamesStore } from '@/store/module/keepAliveNames';

defineOptions({ name: 'systemMenus' });

const emit = defineEmits(['onCancel']);

const router = useRouter();
// 原 mapState('admin/layout') 模块不存在，isMobile 恒为 undefined，保持等价
const layoutStore = useLayoutStore();
const menusStore = useMenusStore();
const userInfoStore = useUserInfoStore();
const routesListStore = useRoutesListStore();
const keepAliveNamesStore = useKeepAliveNamesStore();

const xTable = ref(null);
const roleDataRef = ref(null);
const menusFromRef = ref(null);
const search = ref(null);
const treeBox = ref(null);

const children = ref([]);
const expandedKeys = ref([]);
const tabconfig = { childrenField: 'children', reserve: true, accordion: true };
const spinShow = ref(false);
const ruleModal = ref(false);
const searchRule = ref('');
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const roleData = reactive({
  is_show: '',
  keyword: '',
  // auth_type: 3,
});
const defaultProps = {
  children: 'children',
  label: 'name',
};
const ruleCateList = ref([]); //权限树
const loading = ref(false);
const tableData = ref([]);
const FromData = ref(null);
const icons = ref('');
const defaultFormValidate = () => ({});
const formValidate = ref(defaultFormValidate());
const titleFrom = ref('');
const modalTitleSs = ref('');
const routeType = ref('0');
const arrs = ref([]);
const foundationList = ref([]); // 基础接口列表
const openList = ref([]); // 公开接口列表
const seletRoute = ref([]); // 选中路由
const seletRouteIds = ref([]); // 选中id
const menusId = ref(0); // 选中分类id
const nodeKey = ref(0); // 选中节点
const openId = ref('');

const isMobile = computed(() => layoutStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

function init() {
  searchRule.value = '';
  searchRules();
}
function addRouters() {
  let data = {
    menus: seletRoute.value,
  };
  menusBatch(data)
    .then((res) => {
      getData();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function selectRule(data) {
  if (seletRouteIds.value.includes(data.id)) {
    let i = seletRouteIds.value.findIndex((e) => e == data.id);
    seletRouteIds.value.splice(i, 1);
    seletRoute.value.splice(i, 1);
  } else {
    seletRouteIds.value.push(data.id);
    seletRoute.value.push({
      menu_name: data.name,
      unique_auth: '',
      api_url: data.path,
      path: menusId.value,
      method: data.method,
    });
  }
}
function changTab(name) {
  routeType.value = name;
  let index = parseInt(name);
  children.value = foundationList.value[index] ? foundationList.value[index].children : [];
  searchRules();
}
// 搜索规则
function searchRules() {
  if (searchRule.value.trim()) {
    arrs.value = [];
    let arr = foundationList.value;
    for (var i = 0; i < arr.length; i++) {
      if (arr[i].name.indexOf(searchRule.value) !== -1) {
        arrs.value.push(arr[i].id);
      }
    }
  } else {
    arrs.value = [];
  }
}
function addRoute(row) {
  menusId.value = row.id;
  routeType.value = '0';
  // getRuleList();
  menusRuleCate().then((res) => {
    ruleCateList.value = res.data;
    ruleModal.value = true;
    if (res.data.length) {
      nextTick((e) => {
        expandedKeys.value = findFirstNonNullChildrenKeys(res.data[0], []);
        nodeKey.value = findFirstNonNullChildren(res.data).id;
        treeBox.value.setCurrentKey(nodeKey.value);
        getRuleListByCate(nodeKey.value);
      });
    }
  });
}
function handleNodeClick(data) {
  getRuleListByCate(data.id);
}
function modalchange() {
  seletRouteIds.value = [];
  seletRoute.value = [];
}
// 获取权限列表
function getRuleListByCate(cate_id) {
  getRuleList(cate_id).then((res) => {
    foundationList.value = res.data;
    children.value = res.data;
    searchRules();

    // openList.value = [];
    // seletRouteIds.value = [];
    // seletRoute.value = [];
  });
}
// 修改规则状态
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    is_show_path: row.is_show_path,
    is_show: -1,
  };
  isShowApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
      menusStore.getMenusNavList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 请求列表
function getList() {
  formValidate.value = Object.assign({}, defaultFormValidate());
  getData();
}

// 清除表单数据
function clearFrom() {
  formValidate.value = Object.assign({}, defaultFormValidate());
}
// 添加子菜单
function addE(row, title) {
  formValidate.value = {};
  let pid = row.id.toString();
  if (pid) {
    menusDetailsApi(row.id)
      .then(async (res) => {
        formValidate.value.path = res.data.path;
        formValidate.value.path.push(row.id);
        formValidate.value.pid = pid;
        menusFromRef.value.modals = true;
        menusFromRef.value.valids = false;
        titleFrom.value = title;
        formValidate.value.auth_type = 1;
        formValidate.value.is_show = 0;
        formValidate.value.is_show_path = 0;
      })
      .catch((res) => {
        ElMessage.error(res.msg);
      });
  } else {
    formValidate.value.pid = pid;
    menusFromRef.value.modals = true;
    menusFromRef.value.valids = false;
    titleFrom.value = title;
    formValidate.value.auth_type = 1;
    formValidate.value.is_show = 0;
    formValidate.value.is_show_path = 0;
  }
  // formValidate.value.pid = row.id.toString();
  // menusFromRef.value.modals = true;
  // menusFromRef.value.valids = false;
  // titleFrom.value = title;
  // formValidate.value.auth_type = 1;
  // formValidate.value.is_show = '0';
}
// 删除
function del(row, tit) {
  let delfromData = {
    title: tit,
    url: `/setting/menus/${row.id}`,
    method: 'DELETE',
    ids: '',
  };

  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getData();
      getMenusUnique();
      // menusStore.getMenusNavList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 规则详情
function menusDetails(id) {
  menusDetailsApi(id)
    .then(async (res) => {
      formValidate.value = res.data;
      menusFromRef.value.modals = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(row, title, index) {
  openId.value = row.id;
  formValidate.value = {};
  menusDetails(row.id);
  titleFrom.value = title;
  menusFromRef.value.valids = false;
  menusFromRef.value.getAddFrom(row.id);
}
// 添加
function menusAdd(title) {
  formValidate.value = {};
  menusFromRef.value.modals = true;
  menusFromRef.value.valids = false;
  // formValidate.value = Object.assign(this.$data, this.$options.formValidate());
  titleFrom.value = title;
  formValidate.value.auth_type = 1;
  formValidate.value.is_show = 0;
  formValidate.value.is_show_path = 0;
}
// 新增页面表单
// getAddFrom () {
//     spinShow.value = true;
//     addMenus(roleData).then(async res => {
//         FromData.value = res.data;
//         menusFromRef.value.modals = true;
//         spinShow.value = false;
//     }).catch(res => {
//         spinShow.value = false;
//         ElMessage.error(res.msg);
//     })
// },
// 列表
function getData() {
  loading.value = true;
  getTable(roleData)
    .then(async (res) => {
      tableData.value = res.data;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function changeMenu(data) {
  changeData(tableData.value, data);
  getMenusUnique();
}
function changeData(arr, data) {
  let arrKey = Object.keys(data);
  arr.map((e) => {
    if (e.id == openId.value) {
      arrKey.map((el) => {
        e[el] = data[el];
      });
    } else if (e.children) {
      changeData(e.children, data);
    }
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
// 关闭按钮
function cancel() {
  emit('onCancel');
}

onMounted(() => {
  getData();
});
</script>

<style scoped lang="scss">
.vxeTable {
  > .vxe-table--header-wrapper {
    background: var(--prev-bg-white) !important;
  }

  .icon {
    font-size: 20px;
  }
}
:deep(.vxe-table--render-default .vxe-table--border-line) {
  z-index: 2 !important;
}
.rule {
  display: flex;
  flex-wrap: wrap;
  overflow-y: scroll;
  height: max-content;
  max-height: 600px;
  flex: 1;
}
.tree::-webkit-scrollbar {
  width: 2px;
  background-color: #f5f5f5;
}
/*定义滚动条高宽及背景 高宽分别对应横竖滚动条的尺寸*/
.rule::-webkit-scrollbar {
  width: 10px;
  height: 10px;
  background-color: #f5f5f5;
}

/*定义滚动条轨道 内阴影+圆角*/
.rule::-webkit-scrollbar-track {
  border-radius: 4px;
  background-color: #f5f5f5;
}

/*定义滑块 内阴影+圆角*/
.rule::-webkit-scrollbar-thumb {
  border-radius: 4px;
  background-color: #ccc;
}

.rule-list {
  background-color: #f2f2f2;
  width: 48.5%;
  height: max-content;
  margin: 5px;
  border-radius: 3px;
  padding: 10px;
  color: var(--prev-color-text-regular);
  cursor: pointer;
  transition: all 0.1s;
}

.rule-list:hover {
  background-color: #badbfb;
}

.rule-list div {
  white-space: nowrap;
}

.select-rule {
  background-color: #badbfb;
}
.route-list {
  display: flex;
  margin-top: 10px;

  .tree {
    width: 200px;
    overflow-y: scroll;
    max-height: 600px;
    :deep(.el-tree-node__children .el-tree-node .el-tree-node__content) {
      padding-left: 14px !important;
    }
  }
}
</style>
