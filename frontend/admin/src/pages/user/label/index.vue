<template>
  <div>
    <el-row class="ivu-mt box-wrapper">
      <el-col v-bind="grid1" class="left-wrapper">
        <div class="tree_tit" v-db-click @click="addSort">
          <i class="el-icon-circle-plus"></i>
          添加分类
        </div>
        <div class="tree">
          <el-tree
            :data="labelSort"
            node-key="id"
            default-expand-all
            highlight-current
            :expand-on-click-node="false"
            @node-click="bindMenuItem"
            :current-node-key="treeId"
          >
            <template #default="{ data }"
              ><span class="custom-tree-node">
                <div class="file-name">
                  <img v-if="!data.pid" class="icon" src="@/assets/images/file.jpg" />
                  <el-tooltip class="item" effect="dark" :content="data.name" placement="top">
                    <div class="text line1">
                      {{ data.name }}
                    </div>
                  </el-tooltip>
                </div>
                <span v-if="data.id">
                  <el-dropdown @command="(command) => clickMenu(data, command)">
                    <i class="el-icon-more el-icon--right"></i>
                    <template #dropdown>
                      <el-dropdown-menu>
                        <el-dropdown-item command="1">编辑分类</el-dropdown-item>
                        <el-dropdown-item v-if="data.id" command="2">删除分类</el-dropdown-item>
                      </el-dropdown-menu>
                    </template>
                  </el-dropdown>
                </span>
              </span></template
            >
          </el-tree>
        </div>
      </el-col>
      <el-col v-bind="grid2" ref="rightBox">
        <el-card :bordered="false" shadow="never" class="left-radius-none">
          <el-row class="action-row">
            <el-col>
              <el-button v-auth="['admin-user-label_add']" type="primary" v-db-click @click="add">添加标签</el-button>
              <!-- <el-button v-auth="['admin-user-label_add']" type="success" v-db-click @click="addSort">添加分类</el-button> -->
            </el-col>
          </el-row>
          <el-table
            :data="labelLists"
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
            <el-table-column label="标签名称" width="80">
              <template #default="scope">
                <span>{{ scope.row.label_name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="分类名称" min-width="80">
              <template #default="scope">
                <span>{{ scope.row.cate_name }}</span>
              </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作" width="100">
              <template #default="scope">
                <a v-db-click @click="edit(scope.row.id)">修改</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除分类', scope.$index)">删除</a>
              </template>
            </el-table-column>
          </el-table>
          <div class="acea-row row-right page">
            <pagination
              v-if="total"
              :total="total"
              v-model:page="labelFrom.page"
              v-model:limit="labelFrom.limit"
              @pagination="getList"
            />
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { ElMessage } from 'element-plus';
import { userLabelAll, userLabelApi, userLabelAddApi, userLabelEdit, userLabelCreate } from '@/api/user';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'user_label' });

const treeId = ref('');
const grid1 = {
  xl: 4,
  lg: 4,
  md: 6,
  sm: 8,
  xs: 0,
};
const grid2 = {
  xl: 20,
  lg: 20,
  md: 18,
  sm: 16,
  xs: 24,
};

const loading = ref(false);
const labelFrom = ref({
  page: 1,
  limit: 15,
  label_cate: '',
});
const labelLists = ref([]);
const total = ref(0);
const theme3 = ref('light');
const labelSort = ref([]);
const sortName = ref('');
const current = ref(0);

// created
getUserLabelAll();

// 添加
function add() {
  modalForm(userLabelAddApi(0, labelFrom.value.label_cate)).then(() => getList());
}

// 分组列表
function getList() {
  loading.value = true;
  userLabelApi(labelFrom.value)
    .then(async (res) => {
      let data = res.data;
      labelLists.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

// 修改
function edit(id) {
  modalForm(userLabelAddApi(id)).then(() => getList());
}

// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `user/user_label/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      labelLists.value.splice(num, 1);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

// 标签分类
function getUserLabelAll(key) {
  userLabelAll().then((res) => {
    let obj = {
      name: '全部',
      id: '',
    };
    res.data.unshift(obj);
    res.data.forEach((el) => {
      el.status = false;
    });
    if (!key) {
      sortName.value = res.data[0].id;
      labelFrom.value.label_cate = res.data[0].id;
      getList();
    }
    labelSort.value = res.data;
  });
}

// 显示标签小菜单
function showMenu(item) {
  labelSort.value.forEach((el) => {
    if (el.id == item.id) {
      el.status = item.status ? false : true;
    } else {
      el.status = false;
    }
  });
}

// 编辑标签
function labelEdit(item) {
  modalForm(userLabelEdit(item.id)).then(() => getUserLabelAll(1));
}

// 添加分类
function addSort() {
  modalForm(userLabelCreate()).then(() => getUserLabelAll());
}

function deleteSort(row, tit) {
  let num = labelSort.value.findIndex((e) => {
    return e.id == row.id;
  });
  let delfromData = {
    title: tit,
    num: num,
    url: `user/user_label_cate/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      labelSort.value.splice(num, 1);
      labelSort.value = [];
      getUserLabelAll();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

function clickMenu(data, name) {
  if (name == 1) {
    labelEdit(data);
  } else if (name == 2) {
    deleteSort(data, '删除分类');
  }
}

function bindMenuItem(name, index) {
  labelFrom.value.page = 1;
  current.value = index;
  labelSort.value.forEach((el) => {
    el.status = false;
  });
  labelFrom.value.label_cate = name.id;
  getList();
}
</script>

<style lang="scss" scoped>
.showOn {
  color: #2d8cf0;
  background: #f0faff;
  z-index: 2;
}
:deep(.ivu-menu-vertical .ivu-menu-item-group-title) {
  display: none;
}
:deep(.ivu-menu-vertical.ivu-menu-light:after) {
  display: none;
}
.left-wrapper {
  background: #fff;
  border-right: 1px solid #f2f2f2;
  .tree {
    // height: 100%;
    height: calc(-220px + 100vh);
    overflow-y: scroll;
  }
  .tree::-webkit-scrollbar {
    display: none;
  }
}

.menu-item {
  z-index: 50;
  position: relative;
  display: flex;
  justify-content: space-between;
  word-break: break-all;
  .icon-box {
    z-index: 3;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
  }
  &:hover .icon-box {
    display: block;
  }
  .right-menu {
    z-index: 10;
    position: absolute;
    right: -106px;
    top: -11px;
    width: auto;
    min-width: 121px;
  }
}
</style>
