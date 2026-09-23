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
                <span v-show="data.id !== '' && data.id !== 0">
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
        <el-card :bordered="false" shadow="never">
          <el-row class="action-row mb14">
            <el-col :span="24">
              <el-button v-auth="['setting-store_service-add']" type="primary" v-db-click @click="add"
                >添加话术</el-button
              >
              <!-- <el-button v-auth="['setting-store_service-add']" type="success" v-db-click @click="addSort">添加分类</el-button> -->
            </el-col>
          </el-row>
          <el-table
            :data="tableList"
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
            <el-table-column label="分类" min-width="120">
              <template #default="scope">
                <span>{{ scope.row.cate_name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="标题" min-width="120">
              <template #default="scope">
                <el-tooltip placement="top" :open-delay="600">
                  <template #content
                    ><div>{{ scope.row.title }}</div></template
                  >
                  <span class="line2">{{ scope.row.title }}</span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="详情" min-width="120">
              <template #default="scope">
                <el-tooltip placement="top" :open-delay="600">
                  <template #content
                    ><div>{{ scope.row.message }}</div></template
                  >
                  <span class="line2">{{ scope.row.message }}</span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="排序" min-width="120">
              <template #default="scope">
                <span>{{ scope.row.sort }}</span>
              </template>
            </el-table-column>
            <el-table-column label="添加时间" min-width="150">
              <template #default="scope">
                <span>{{ scope.row.add_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="170">
              <template #default="scope">
                <a v-db-click @click="edit(scope.row)">编辑</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除客服', scope.$index)">删除</a>
              </template>
            </el-table-column>
          </el-table>
          <div class="acea-row row-right page">
            <pagination
              v-if="total"
              :total="total"
              v-model:page="tableFrom.page"
              v-model:limit="tableFrom.limit"
              @pagination="getList"
            />
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { ElMessage } from 'element-plus';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import {
  wechatSpeechcraft,
  speechcraftCreate,
  speechcraftEdit,
  speechcraftcate,
  speechcraftcateCreate,
  speechcraftcateEdit,
} from '@/api/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'index' });

const mediaStore = useMediaStore();

// 原 filters.typeFilter
function typeFilter(status) {
  const statusMap = {
    wechat: '微信用户',
    routine: '小程序用户',
  };
  return statusMap[status];
}

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

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
const isChat = ref(true);
const formValidate3 = reactive({
  page: 1,
  limit: 15,
});
const total3 = ref(0);
const loading3 = ref(false);
const modals3 = ref(false);
const tableList3 = ref([]);
const columns3 = [
  {
    title: '用户名称',
    key: 'nickname',
    width: 200,
  },
  {
    title: '客服头像',
    slot: 'headimgurl',
  },
  {
    title: '操作',
    slot: 'action',
  },
];
const formValidate5 = reactive({
  page: 1,
  limit: 15,
  uid: 0,
  to_uid: 0,
  id: 0,
});
const total5 = ref(0);
const loading5 = ref(false);
const tableList5 = ref([]);
const FromData = ref(null);
const formValidate = reactive({
  page: 1,
  limit: 15,
  data: '',
  type: '',
  nickname: '',
});
const tableList2 = ref([]);
const modals = ref(false);
const total = ref(0);
const tableFrom = reactive({
  page: 1,
  limit: 15,
  cate_id: 0,
});
const timeVal = ref([]);
const fromList = ref({
  title: '选择时间',
  custom: true,
  fromTxt: [
    { text: '全部', val: '' },
    { text: '今天', val: 'today' },
    { text: '昨天', val: 'yesterday' },
    { text: '最近7天', val: 'lately7' },
    { text: '最近30天', val: 'lately30' },
    { text: '本月', val: 'month' },
    { text: '本年', val: 'year' },
  ],
});
const loading = ref(false);
const tableList = ref([]);

const loading2 = ref(false);
const total2 = ref(0);
const addFrom = reactive({
  uids: [],
});
const selections = ref([]);
const rows = ref({});
const rowRecord = ref({});
const theme3 = ref('light');
const labelSort = ref([]);
const sortName = ref('');
const current = ref(0);

// created
getUserLabelAll();

function getUserLabelAll(key) {
  speechcraftcate().then((res) => {
    let data = res.data.data;
    let obj = {
      name: '全部',
      id: '',
    };
    data.unshift(obj);
    data.forEach((el) => {
      el.status = false;
    });
    if (!key) {
      sortName.value = data[0].id;
      tableFrom.cate_id = data[0].id;
      getList();
    }
    labelSort.value = data;
  });
}
// 添加分类
function addSort() {
  modalForm(speechcraftcateCreate()).then(() => getUserLabelAll());
}
//编辑标签
function labelEdit(item) {
  modalForm(speechcraftcateEdit(item.id)).then(() => getUserLabelAll(1));
}
function deleteSort(row, tit) {
  let num = labelSort.value.findIndex((e) => {
    return e.id == row.id;
  });
  let delfromData = {
    title: tit,
    num: num,
    url: `app/wechat/speechcraftcate/${row.id}`,
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
// 点击菜单
function clickMenu(data, name) {
  if (name == 1) {
    labelEdit(data);
  } else if (name == 2) {
    deleteSort(data, '删除分类');
  }
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
function bindMenuItem(name, index) {
  tableFrom.page = 1;
  current.value = index;
  labelSort.value.forEach((el) => {
    el.status = false;
  });
  tableFrom.cate_id = name.id;
  getList();
}
function cancel() {
  Object.assign(formValidate, {
    page: 1,
    limit: 15,
    data: '',
    type: '',
    nickname: '',
  });
}
// 查看对话
function look(row) {
  isChat.value = false;
  rowRecord.value = row;
  getChatlist();
}
// 查看对话列表（死代码：原文件即未导入 kefuChatlistApi，保持原状）
function getChatlist() {
  loading5.value = true;
  formValidate5.uid = rows.value.uid;
  formValidate5.to_uid = rowRecord.value.uid;
  formValidate5.id = rows.value.id;
  kefuChatlistApi(formValidate5)
    .then(async (res) => {
      let data = res.data;
      tableList5.value = data.list;
      total5.value = data.count;
      loading5.value = false;
    })
    .catch((res) => {
      loading5.value = false;
      ElMessage.error(res.msg);
    });
}
function pageChange5(index) {
  formValidate5.page = index;
  getChatlist();
}
// 修改成功
function submitFail() {
  getList();
}
// 聊天记录
function record(row) {
  rows.value = row;
  modals3.value = true;
  isChat.value = true;
  getListRecord();
}
// 聊天记录列表（死代码：原文件即未导入 kefuRecordApi，保持原状）
function getListRecord() {
  loading3.value = true;
  kefuRecordApi(formValidate3, rows.value.id)
    .then(async (res) => {
      let data = res.data;
      tableList3.value = data.list ? data.list : [];
      total3.value = data.count;
      loading3.value = false;
    })
    .catch((res) => {
      loading3.value = false;
      ElMessage.error(res.msg);
    });
}
function pageChange3(index) {
  formValidate3.page = index;
  getListRecord();
}
// 编辑
function edit(row) {
  modalForm(speechcraftEdit(row.id)).then(() => getList());
}
// 添加
function add() {
  modalForm(speechcraftCreate()).then(() => getList());
}
// 全选
function onSelectTab(selection) {
  selections.value = selection;
  let data = [];
  selections.value.map((item) => {
    data.push(item.uid);
  });
  addFrom.uids = data;
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.data = timeVal.value ? timeVal.value.join('-') : '';
  formValidate.page = 1;
  getListService();
}
// 选择时间
function selectChange(tab) {
  formValidate.data = tab;
  timeVal.value = [];
  formValidate.page = 1;
  getListService();
}
// 客服列表（死代码：原文件即未导入 kefucreateApi，保持原状）
function getListService() {
  loading2.value = true;
  kefucreateApi(formValidate)
    .then(async (res) => {
      let data = res.data;
      tableList2.value = data.list;
      total2.value = data.count;
      tableList2.value.map((item) => {
        item._isChecked = false;
      });
      loading2.value = false;
    })
    .catch((res) => {
      loading2.value = false;
      ElMessage.error(res.msg);
    });
}
function pageChange2(pageIndex) {
  formValidate.page = pageIndex;
  getListService();
  addFrom.uids = [];
}
// 搜索
function userSearchs() {
  formValidate.page = 1;
  getListService();
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `/app/wechat/speechcraft/${row.id}`,
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
// 列表
function getList() {
  loading.value = true;
  wechatSpeechcraft(tableFrom)
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
// 修改是否显示（死代码：原文件即未导入 kefusetStatusApi，保持原状）
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  kefusetStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 添加客服（死代码：原文件即未导入 kefuAddApi，保持原状）
function putRemark() {
  if (addFrom.uids.length === 0) {
    return ElMessage.warning('请选择要添加的客服');
  }
  kefuAddApi(addFrom)
    .then(async (res) => {
      ElMessage.success(res.msg);
      modals.value = false;
      getList();
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
</script>

<style lang="scss" scoped>
.showOn {
  color: #2d8cf0;
  background: #f0faff;
  z-index: 2;
}
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
.modelBox {
  :deep(.ivu-table-header) {
    width: 100% !important;
  }
}
.trees-coadd {
  width: 100%;
  height: 385px;
  .scollhide {
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: scroll;
  }
}
.scollhide::-webkit-scrollbar {
  display: none;
}
:deep(.ivu-menu-vertical .ivu-menu-item-group-title) {
  display: none;
}
:deep(.ivu-menu-vertical.ivu-menu-light:after) {
  display: none;
}
.left-wrapper {
  height: 904px;
  background: #fff;
  border-right: 1px solid #f2f2f2;
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
