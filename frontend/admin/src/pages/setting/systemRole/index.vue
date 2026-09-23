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
          <el-form-item label="状态：" label-for="status">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              @change="userSearchs"
              clearable
              class="form_content_width"
            >
              <el-option value="1" label="显示"></el-option>
              <el-option value="0" label="不显示"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="身份昵称：" label-for="role_name">
            <el-input
              clearable
              placeholder="请输入身份昵称"
              v-model="formValidate.role_name"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" v-loading="spinShow">
      <el-button v-auth="['setting-system_role-add']" class="action-row" type="primary" v-db-click @click="add('添加')"
        >添加身份</el-button
      >
      <el-table
        :data="tableList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="身份昵称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.role_name }}</span>
          </template>
        </el-table-column>
        <!-- <el-table-column label="权限" min-width="1000">
          <template #default="scope">
            <span class="line1">{{ scope.row.rules }}</span>
          </template>
        </el-table-column> -->
        <el-table-column label="状态" min-width="120">
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
            <a v-db-click @click="edit(scope.row, '编辑')">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除', scope.$index)">删除</a>
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
    <!-- 新增编辑-->
    <el-dialog
      v-model="modals"
      :title="`${modelTit}身份`"
      :close-on-click-modal="false"
      :show-close="true"
      width="540px"
      @closed="closed"
    >
      <el-form
        ref="formInlineRef"
        :model="formInline"
        :rules="ruleValidate"
        label-width="100px"
        :label-position="labelPosition2"
        @submit.prevent
      >
        <el-form-item label="身份名称：" label-for="role_name" prop="role_name">
          <el-input placeholder="请输入身份昵称" v-model="formInline.role_name" />
        </el-form-item>
        <el-form-item label="是否开启：" prop="status">
          <el-radio-group v-model="formInline.status">
            <el-radio :label="1" :value="1">开启</el-radio>
            <el-radio :label="0" :value="0">关闭</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="权限：">
          <div class="trees-coadd">
            <div class="scollhide">
              <div class="iconlist">
                <el-tree
                  :data="menusList"
                  node-key="id"
                  check-strictly
                  show-checkbox
                  highlight-current
                  ref="tree"
                  :default-checked-keys="selectIds"
                  :props="defaultProps"
                  @check="clickDeal"
                  :default-expand-all="defaultExpandAll"
                ></el-tree>
              </div>
            </div>
            <span class="iconlist-btn" @click="changeExpandAll">{{ defaultExpandAll ? '折叠' : '展开' }}</span>
          </div>
        </el-form-item>
      </el-form>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="onCancel">取 消</el-button>
          <el-button type="primary" v-db-click @click="handleSubmit()">提 交</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>
<script setup>
import { ref, reactive, computed, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import { roleListApi, roleSetStatusApi, menusListApi, roleCreateApi, roleInfoApi } from '@/api/setting';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'systemrRole' });

const mediaStore = useMediaStore();

const table = ref(null);
const tree = ref(null);
const formValidateRef = ref(null);
const formInlineRef = ref(null);

const spinShow = ref(false);
const modals = ref(false);
const total = ref(0);
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);
const defaultExpandAll = ref(false);
const formValidate = reactive({
  status: '',
  role_name: '',
  page: 1,
  limit: 15,
});
const tableList = ref([]);
const formInline = ref({
  role_name: '',
  status: 0,
  checked_menus: [],
  id: 0,
});
const menusList = ref([]);
const selectIds = ref([]);
const modelTit = ref('');
const ruleValidate = {
  role_name: [{ required: true, message: '请输入身份昵称', trigger: 'blur' }],
  status: [{ required: true, type: 'number', message: '请选择是否开启', trigger: 'change' }],
  // checked_menus: [
  //     { required: true, validator: validateStatus, trigger: 'change' }
  // ]
};
const defaultProps = {
  children: 'children',
  label: 'title',
};

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));
const labelPosition2 = computed(() => (isMobile.value ? 'top' : 'right'));

function changeExpandAll() {
  // 控制按钮点击之后失焦
  if (defaultExpandAll.value) {
    defaultExpandAll.value = false;
    for (let key in tree.value.store.nodesMap) {
      tree.value.store.nodesMap[key].expanded = false;
    }
  } else {
    defaultExpandAll.value = true;
    for (let key in tree.value.store.nodesMap) {
      tree.value.store.nodesMap[key].expanded = true;
    }
  }
}
function closed() {
  formInline.value = {
    role_name: '',
    status: 0,
    checked_menus: [],
    id: 0,
  };
  selectIds.value = [];
}
// 添加
function add(name) {
  formInline.value.id = 0;
  modelTit.value = name;
  modals.value = true;
  getmenusList();
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `setting/role/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  roleSetStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  formValidate.status = formValidate.status || '';
  roleListApi(formValidate)
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
// 表格搜索
function userSearchs() {
  formValidate.page = 1;
  getList();
}
// 编辑
function edit(row, name) {
  modelTit.value = name;
  formInline.value.id = row.id;
  modals.value = true;
  rows = row;
  getIofo(row);
}
// 菜单列表
function getmenusList() {
  spinShow.value = true;
  menusListApi()
    .then(async (res) => {
      let data = res.data;
      menusList.value = data.menus;
      menusList.value.map((item, index) => {
        if (item.title === '主页') {
          // item.checked = true;
          // item.disableCheckbox = true;
          if (item.children.length) {
            item.children.map((v) => {
              // v.checked = true;
              // v.disableCheckbox = true;
            });
          }
        }
        item.expand = false;
      });
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
// 详情
function getIofo(row) {
  spinShow.value = true;
  roleInfoApi(row.id)
    .then(async (res) => {
      let data = res.data;
      formInline.value = data.role || formInline.value;
      formInline.value.checked_menus = formInline.value.rules;
      nextTick((e) => {
        selectIds.value = formInline.value.rules.split(',');
        tidyRes(data.menus);
        // tree.value.setCheckedKeys(Array(arr));
      });
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
function forChildrenChecked(arr, status, pid) {
  if (arr.length) {
    let len = arr.length;
    for (var j = 0; j < len; j++) {
      var childNode = tree.value.getNode(arr[j].id).data;
      if (status) {
        tree.value.setChecked(childNode.id, true);
        childNode.checked = true;
      }
      if (!status) {
        tree.value.setChecked(childNode.id, false);
        childNode.checked = false;
      }
      if (childNode.children.length) {
        forChildrenChecked(childNode.children, status);
      }
    }
  }
}

function clickDeal(currentObj, treeStatus, ccc) {
  // 用于：父子节点严格互不关联时，父节点勾选变化时通知子节点同步变化，实现单向关联。
  let selected = treeStatus.checkedKeys.indexOf(currentObj.id); // -1未选中
  // 选中
  if (selected !== -1) {
    // 子节点只要被选中父节点就被选中
    selectedParent(currentObj);
    // 统一处理子节点为相同的勾选状态
    uniteChildSame(currentObj, true);
  } else {
    // 未选中 处理子节点全部未选中
    if (currentObj.children.length !== 0) {
      uniteChildSame(currentObj, false);
    }
    let selParent = false;
    let parentNode = currentObj.pid ? tree.value.getNode(currentObj.pid).data : undefined;
    if (parentNode && parentNode.children.length) {
      for (let i = 0; i < parentNode.children.length; i++) {
        if (treeStatus.checkedKeys.includes(parentNode.children[i].id)) {
          selParent = true;
        }
      }
    }
    if (!selParent && currentObj.pid) tree.value.setChecked(currentObj.pid, false);
  }
}
// 统一处理子节点为相同的勾选状态
function uniteChildSame(treeList, isSelected) {
  tree.value.setChecked(treeList.id, isSelected);
  for (let i = 0; i < treeList.children.length; i++) {
    uniteChildSame(treeList.children[i], isSelected);
  }
}
// 统一处理父节点为选中
function selectedParent(currentObj) {
  let currentNode = tree.value.getNode(currentObj);
  if (currentNode.parent.key !== undefined) {
    tree.value.setChecked(currentNode.parent, true);
    selectedParent(currentNode.parent);
  }
}
function tidyRes(menus) {
  let data = [];
  menus.map((menu) => {
    if (menu.title === '主页') {
      menu.checked = true;
      // menu.disabled = true;
      if (menu.children.length) {
        menu.children.map((v) => {
          v.checked = true;
        });
      }
      data.push(menu);
    } else {
      data.push(initMenu(menu));
    }
  });
  menusList.value = data;
}
function initMenu(menu) {
  let data = {},
    checkMenus = ',' + formInline.value.checked_menus + ',';
  data.title = menu.title;
  data.id = menu.id;
  data.pid = menu.pid;
  data.children = menu.children;
  data.checked = menu.checked;

  if (menu.children && menu.children.length > 0) {
    data.children = [];
    menu.children.map((child) => {
      data.children.push(initMenu(child));
    });
  } else {
    data.checked = checkMenus.indexOf(String(',' + data.id + ',')) !== -1;
    data.expand = !data.checked;
  }
  return data;
}
// 提交
function handleSubmit() {
  formInlineRef.value.validate((valid) => {
    if (valid) {
      formInline.value.checked_menus = [...tree.value.getCheckedKeys(), ...tree.value.getHalfCheckedKeys()];
      if (formInline.value.checked_menus.length === 0) return ElMessage.warning('请至少选择一个权限');
      roleCreateApi(formInline.value)
        .then(async (res) => {
          ElMessage.success(res.msg);
          modals.value = false;
          getList();
          formInlineRef.value.resetFields();
          formInline.value.checked_menus = [];
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    } else {
      return false;
    }
  });
}
function onCancel() {
  formInlineRef.value.resetFields();
  formInline.value.checked_menus = [];
  selectIds.value = [];
  modals.value = false;
}

let rows = null;

getList();
</script>

<style scoped lang="scss">
.trees-coadd {
  width: 100%;
  height: 385px;
  display: flex;
  .scollhide {
    position: relative;
    width: 100%;
    height: 100%;
    margin-top: 4px;
    overflow-y: scroll;
  }
  .iconlist-btn {
    white-space: nowrap;
    cursor: pointer;
    color: var(--prev-color-primary);
  }
}
// margin-left: 18px;
.scollhide::-webkit-scrollbar {
  display: none;
}
</style>
