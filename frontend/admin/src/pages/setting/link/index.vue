<template>
  <el-card :bordered="false" shadow="never" class="ivu-mt">
    <div class="Modal">
      <div class="colLeft">
        <div class="Nav">
          <div class="trees-coadd">
            <div class="tree_tit" v-db-click @click="append">
              <i class="el-icon-circle-plus"></i>
              添加分类
            </div>
            <div class="scollhide">
              <div class="tree" v-if="treeData.length">
                <el-tree
                  ref="tree"
                  :data="treeData"
                  node-key="id"
                  default-expand-all
                  :highlight-current="highlightCurrent"
                  :expand-on-click-node="true"
                  :check-on-click-node="false"
                  @node-click="appendBtn"
                  @node-expand="nodeExpand"
                  :current-node-key="treeId"
                >
                  <template #default="{ data }"
                    ><span class="custom-tree-node">
                      <div class="file-name">
                        <img v-if="data.pid == 1" class="icon" src="@/assets/images/file.jpg" />
                        <el-tooltip class="item" effect="dark" :content="data.title" placement="top">
                          <div class="text line1">
                            {{ data.title }}
                          </div>
                        </el-tooltip>
                      </div>
                      <span>
                        <el-dropdown @command="(command) => clickMenu(data, command)">
                          <i class="el-icon-more el-icon--right"></i>
                          <template #dropdown>
                            <el-dropdown-menu>
                              <el-dropdown-item v-if="data.pid == 1" command="1">新增分类</el-dropdown-item>
                              <el-dropdown-item v-if="data.id" command="2">编辑分类</el-dropdown-item>
                              <el-dropdown-item v-if="data.id" command="3">删除</el-dropdown-item>
                            </el-dropdown-menu>
                          </template>
                        </el-dropdown>
                      </span>
                    </span></template
                  >
                </el-tree>
              </div>
            </div>
          </div>
        </div>
        <div class="conter">
          <el-button type="primary" @click="addLink">添加链接</el-button>
          <el-table
            :data="linkList"
            ref="couponTable"
            class="mt14"
            v-loading="loading"
            highlight-current-row
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <el-table-column label="ID" width="70">
              <template #default="scope">
                <span>{{ scope.row.id }}</span>
              </template>
            </el-table-column>
            <el-table-column label="名称" width="150">
              <template #default="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="H5链接" minWidth="170">
              <template #default="scope">
                <el-tooltip class="item pointer" content="点击复制">
                  <span v-db-click @click="onCopy(scope.row.h5_url)">{{ scope.row.h5_url }}</span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="小程序链接" minWidth="140">
              <template #default="scope">
                <el-tooltip class="item pointer" content="点击复制">
                  <span v-db-click @click="onCopy(scope.row.url)">{{ scope.row.url }}</span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="添加时间" minWidth="90">
              <template #default="scope">
                <span>{{ scope.row.add_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="90">
              <template #default="scope">
                <a v-db-click @click="edit(scope.row)">编辑</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除链接', scope.$index)">删除</a>
              </template>
            </el-table-column>
          </el-table>
          <div class="acea-row row-right page">
            <pagination
              v-if="total"
              :total="total"
              v-model:page="fileData.page"
              v-model:limit="fileData.limit"
              @pagination="getLinkList"
            />
          </div>
        </div>
      </div>
    </div>
  </el-card>
</template>

<script setup>
import { ref, reactive, getCurrentInstance, onMounted, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import { diyLinkCategoryListApi, linkCategoryFormApi, linkListApi, linkFormApi } from '@/api/setting';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'linkLisk' });

const { proxy } = getCurrentInstance();

const tree = ref(null);
const couponTable = ref(null);

const treeData = ref([]);
const linkList = ref([]);
const uploadName = {
  name: '',
  all: 1,
};
const treeId = ref(0);
const fileData = reactive({
  page: 1,
  limit: 15,
});
const total = ref(0);
const pids = ref(0);
const loading = ref(false);
const multipleSelection = ref([]);
const highlightCurrent = ref(true);
let tits = '';
let ids = [];
let checkPicList = [];

function onCopy(copyData) {
  proxy
    .$copyText(copyData)
    .then((message) => {
      ElMessage.success('复制成功');
    })
    .catch((err) => {
      ElMessage.error('复制失败');
    });
}
function edit(row) {
  modalForm(linkFormApi(row.id, row.cate_id)).then(() => getLinkList());
}
// 点击菜单
function clickMenu(data, name) {
  if (name == 1) {
    editLinkCategory(data, 0);
  } else if (name == 2) {
    editLinkCategory(data, 1);
  } else if (name == 3) {
    remove(data, '分类');
  }
}
function delImg(id) {
  let idsData = {
    ids: id,
  };
  let delfromData = {
    title: '删除选中图片',
    url: `file/file/delete`,
    method: 'POST',
    ids: idsData,
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getLinkList();
      checkPicList = [];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 删除图片
function editPicList(id) {
  let idsData = {
    ids: id || ids.toString(),
  };
  let delfromData = {
    title: '删除选中图片',
    url: `file/file/delete`,
    method: 'POST',
    ids: idsData,
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getLinkList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 鼠标移入 移出
function onMouseOver(root, node, data) {
  event.preventDefault();
  data.flag = !data.flag;
  if (data.flag2) {
    data.flag2 = false;
  }
}
function addLink() {
  modalForm(linkFormApi(0, treeId.value)).then(() => getLinkList());
}
// 点击树
function appendBtn(data, n, d) {
  if (data.pid == 1 && !n.expanded) {
    highlightCurrent.value = false;
    return;
  } else if (data.pid != 1) {
    treeId.value = data.id;
    fileData.page = 1;
    getLinkList();
  }
}
function nodeExpand(data, n, d) {
  if (n.expanded) {
    if (data.children.length) {
      highlightCurrent.value = true;
      nextTick(() => {
        fileData.page = 1;
        treeId.value = data.children[0].id || '';
        tree.value.setCurrentKey(treeId.value);
        getLinkList();
      });
    }
  }
}
// 点击添加
function append() {
  getFrom();
}
// 删除分类
function remove(data, tit) {
  tits = tit;
  let delfromData = {
    title: '删除 [ ' + data.title + ' ] ' + '分类',
    url: `diy/link/category/del/${data.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
      checkPicList = [];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑树表单
function editLinkCategory(data, type) {
  modalForm(linkCategoryFormApi(type ? data.id : 0, data.id ? data.id : 1)).then(() => getList());
}
// 分类列表树
function getList(type) {
  diyLinkCategoryListApi(uploadName)
    .then((res) => {
      treeId.value = res.data.length > 0 ? res.data[0]?.children[0]?.id : '';
      treeData.value = res.data;
      getLinkList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 文件列表
function getLinkList() {
  fileData.id = treeId.value;
  loading.value = true;
  linkListApi(fileData)
    .then(async (res) => {
      linkList.value = res.data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `diy/link/del/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      linkList.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function addFlag(treedata) {
  treedata.map((item) => {
    item['flag'] = false;
    item['flag2'] = false;
    item.children && addFlag(item.children);
  });
}
function pageChange(index) {
  fileData.page = index;
  getLinkList();
  checkPicList = [];
}
// 新建分类表单
function getFrom() {
  modalForm(linkCategoryFormApi(0, 1)).then((res) => {
    getList();
  });
}

onMounted(() => {
  getList();
});
</script>

<style scoped lang="scss">
.box {
  width: 100%;
  height: 100%;
  background: #fff;
}
:deep(.el-card__body) {
  min-height: 700px;
  padding: 16px 16px 16px 0;
}
:deep(.conter .linkList) {
  max-width: 100%;
}
.nameStyle {
  position: absolute;
  white-space: nowrap;
  z-index: 9;
  background: #eee;
  height: 20px;
  line-height: 20px;
  color: #555;
  border: 1px solid #ebebeb;
  padding: 0 5px;
  left: 56px;
  bottom: -18px;
}

.tree_tit {
  padding-top: 7px;
  padding-bottom: 22px;
}
.treeBox {
  width: 100%;
  height: 100%;
  max-width: 180px;
}
.Nav {
  width: 100%;
  border-right: 1px solid #eee;
  min-width: 220px;
  max-width: max-content;
}
:deep(.tree .is-current) {
  background-color: #fff !important;
}
.trees-coadd {
  width: 100%;
  border-radius: 4px;
  overflow: hidden;
  position: relative;

  .scollhide {
    overflow-x: hidden;
    overflow-y: scroll;
    padding: 0px 0 10px 0;
    box-sizing: border-box;

    .isTree {
      min-height: 374px;
      max-height: 550px;
      :deep(.file-name) {
        display: flex;
        align-items: center;
        .name {
          max-width: 7em;
        }
        .icon {
          width: 12px;
          height: 12px;
          margin-right: 8px;
        }
      }
      :deep(.el-tree-node) {
        margin-right: 16px;
      }
      :deep(.el-tree-node__children .el-tree-node) {
        margin-right: 0;
      }
      :deep(.el-tree-node__content) {
        width: 100%;
        height: 36px;
      }
      :deep(.custom-tree-node) {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-right: 20px;
        font-size: 13px;
        font-weight: 400;
        color: rgba(0, 0, 0, 0.6);
        line-height: 13px;
      }
      :deep(.is-current) {
        background: #f1f9ff !important;
        color: var(--prev-color-primary) !important;
      }
      :deep(.is-current .custom-tree-node) {
        color: var(--prev-color-primary) !important;
      }
    }
  }

  .scollhide::-webkit-scrollbar {
    display: none;
  }
}

.treeSel :deep(.ivu-select-dropdown-list) {
  padding: 0 5px !important;
  box-sizing: border-box;
  width: 200px;
}
.imagesNo {
  display: flex;
  justify-content: center;
  flex-direction: column;
  align-items: center;
  margin: 65px 0;

  .imagesNo_sp {
    font-size: 13px;
    color: #dbdbdb;
    line-height: 3;
  }
}

.Modal {
  width: 100%;
  height: 100%;
  background: #fff !important;
}
.fill-window {
  height: 100vh;
}
.colLeft {
  padding-right: 0 !important;
  height: 100%;
  display: flex;
  flex-wrap: nowrap;
}

.conter {
  width: 100%;
  height: 100%;
  margin-left: 20px !important;
  .iconliebiao {
    font-size: 12px;
  }
}

.conter .bnt {
  width: 100%;
  padding: 0 0px 20px 0px;
  box-sizing: border-box;
}

.conter .linkList {
  // width: 100%;
  overflow-x: hidden;
  overflow-y: auto;
  min-height: 463px;
}
.conter .linkList.is-modal {
  max-height: 480px;
}
.conter .linkList img {
  max-width: 100%;
}
.conter .linkList .img.on {
  border: 2px solid var(--prev-color-primary);
}

.conter .footer {
  padding: 0 20px 10px 20px;
}

.card-tree {
  background: #fff;
  height: 72px;
  box-sizing: border-box;
  overflow-x: scroll; /* 设置溢出滚动 */
  white-space: nowrap;
  overflow-y: hidden;
  /* 隐藏滚动条 */
  border-radius: 4px;
  scrollbar-width: none; /* firefox */
  -ms-overflow-style: none; /* IE 10+ */
}
.card-tree::-webkit-scrollbar {
  display: none; /* Chrome Safari */
}
</style>
