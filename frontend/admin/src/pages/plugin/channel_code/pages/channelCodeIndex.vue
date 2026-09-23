<template>
  <div>
    <el-row class="ivu-mt box-wrapper">
      <el-col :span="4" class="left-wrapper">
        <div class="tree_tit" v-db-click @click="addSort">
          <i class="el-icon-circle-plus"></i>
          添加分组
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
                  <img v-if="!data.pid" class="icon" src="../../../../assets/images/file.jpg" />
                  <el-tooltip class="item" effect="dark" :content="data.cate_name" placement="top">
                    <div class="text line1">
                      {{ data.cate_name }}
                    </div>
                  </el-tooltip>
                </div>
                <span v-if="data.id">
                  <el-dropdown @command="(command) => clickMenu(data, command)">
                    <i class="el-icon-more el-icon--right"></i>
                    <template #dropdown>
                      <el-dropdown-menu>
                        <el-dropdown-item command="1">编辑分组</el-dropdown-item>
                        <el-dropdown-item v-if="data.id" command="2">删除分组</el-dropdown-item>
                      </el-dropdown-menu>
                    </template>
                  </el-dropdown>
                </span>
              </span></template
            >
          </el-tree>
        </div>
      </el-col>
      <el-col :span="20">
        <el-card :bordered="false" shadow="never" class="left-radius-none">
          <el-row class="action-row mb14">
            <el-col :span="18">
              <el-button v-auth="['marketing-channel_code-create']" type="primary" v-db-click @click="add"
                >新建渠道码</el-button
              >
              <!-- <el-button v-auth="['marketing-channel_code-create']" type="success" v-db-click @click="addSort">添加分组</el-button> -->
            </el-col>
            <el-col :span="6">
              <div class="flex">
                <el-input class="mr10" v-model="tableFrom.name" search placeholder="请输入渠道码名称"> </el-input>
                <el-button type="primary" v-db-click @click="userSearchs">搜索</el-button>
              </div>
            </el-col>
          </el-row>
          <el-table
            :data="tableList"
            v-loading="loading"
            highlight-current-row
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <el-table-column label="渠道码" width="80">
              <template #default="scope">
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row.image" />
                </div>
              </template>
            </el-table-column>
            <el-table-column label="渠道码名称" min-width="80">
              <template #default="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="总关注数" min-width="80">
              <template #default="scope">
                <span>{{ scope.row.follow }}</span>
              </template>
            </el-table-column>
            <el-table-column label="昨日新增关注" min-width="80">
              <template #default="scope">
                <span>{{ scope.row.y_follow }}</span>
              </template>
            </el-table-column>
            <el-table-column label="用户标签" min-width="80">
              <template #default="scope">
                <el-tag class="label-name" v-for="(item, index) in scope.row.label_name" :key="index">{{
                  item
                }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="时间" min-width="80">
              <template #default="scope">
                <span v-if="scope.row.stop === 0"> 永久 </span>
                <span v-if="scope.row.stop === 1">
                  <div>{{ scope.row.add_time }}</div>
                  <div>-</div>
                  <div>{{ scope.row.end_time }}</div>
                </span>
                <span v-if="scope.row.stop === -1">已过期</span>
              </template>
            </el-table-column>
            <el-table-column label="关联推广员" min-width="80">
              <template #default="scope">
                <div class="tabBox_img" v-viewer>
                  <img v-lazy="scope.row.avatar" />
                </div>
              </template>
            </el-table-column>
            <el-table-column label="状态" min-width="80">
              <template #default="scope">
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="scope.row.status"
                  :value="scope.row.status"
                  :disabled="scope.row.lottery_status == 2 ? true : false"
                  @change="onchangeIsShow(scope.row)"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="170">
              <template #default="scope">
                <a v-db-click @click="edit(scope.row)">编辑</a>
                <el-divider direction="vertical"></el-divider>
                <a v-db-click @click="del(scope.row, '删除二维码', scope.$index)">删除</a>
                <el-divider direction="vertical"></el-divider>
                <el-dropdown @command="changeMenu(scope.row, $event)" :transfer="true">
                  <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
                  <template #dropdown
                    ><el-dropdown-menu>
                      <el-dropdown-item command="1">下载</el-dropdown-item>
                      <el-dropdown-item command="2">统计</el-dropdown-item>
                      <el-dropdown-item command="3">用户列表</el-dropdown-item>
                    </el-dropdown-menu></template
                  >
                </el-dropdown>
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
    <el-dialog v-model="modals" title="渠道码用户列表" :close-on-click-modal="false" width="900px">
      <el-table :data="tabList" empty-text="暂无数据" highlight-current-row max-height="400">
        <el-table-column label="UID" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户头像" min-width="120">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户昵称" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total2"
          :total="total2"
          v-model:page="userData.page"
          v-model:limit="userData.limit"
          @pagination="getUserList"
        />
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, onActivated, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import {
  wechatQrcodeList,
  wechatQrcodeCreate,
  wechatQrcodeTree,
  wechatQrcodeStatusApi,
  getUserList as getUserListApi,
  wechatQrcodeCateDelApi,
  wechatQrcodeDelApi,
} from './../api';
import { scrollTop } from '@/libs/util';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import setting from '@/setting';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'marketing_channel_code' });

const router = useRouter();
const routePre = setting.routePre;
const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const treeId = ref('');
const isChat = true;
const formValidate3 = ref({
  page: 1,
  limit: 15,
});
const total3 = ref(0);
const loading3 = ref(false);
const modals3 = ref(false);
const tabList = ref([]);
const formValidate5 = ref({
  page: 1,
  limit: 15,
  uid: 0,
  to_uid: 0,
  id: 0,
});
const tableList5 = ref([]);
const FromData = ref(null);
const formValidate = ref({
  page: 1,
  limit: 15,
  data: '',
  type: '',
  nickname: '',
});
const tableList2 = ref([]);
const modals = ref(false);
const total = ref(0);
const tableFrom = ref({
  page: 1,
  limit: 15,
  cate_id: 0,
  name: '',
});
const userData = ref({
  id: 0,
  page: 1,
  limit: 15,
});
const timeVal = ref([]);
const loading = ref(false);
const tableList = ref([]);
const loading2 = ref(false);
const total2 = ref(0);
const addFrom = ref({
  uids: [],
});
const selections = ref([]);
const rows = ref({});
const rowRecord = ref({});
const theme3 = 'light';
const labelSort = ref([]);
const sortName = ref('');
const current = ref(0);
const uid = ref(0);
const orderId = ref(0);

// 原 filters：转为普通 function
function typeFilter(status) {
  const statusMap = {
    wechat: '微信用户',
    routine: '小程序用户',
  };
  return statusMap[status];
}

onActivated(() => {
  getUserLabelAll();
});

onMounted(() => {
  getUserLabelAll();
});

function changeMenu(row, name) {
  orderId.value = row.id;
  switch (name) {
    case '1':
      downLoadCode(row.image);
      break;
    case '2':
      // vue-router 4 中 path 内拼接的 query 会被丢弃，必须用 query 对象传递参数
      router.push({
        path: routePre + '/marketing/channel_code/code_statistic',
        query: { id: row.id },
      });
      break;
    case '3':
      modals.value = true;
      userData.value.id = row.id;
      getUserList();
      break;
    default:
  }
}
function downLoadCode(url) {
  if (!url) return ElMessage.warning('暂无二维码');
  var image = new Image();
  image.src = url;
  // 解决跨域 Canvas 污染问题
  image.setAttribute('crossOrigin', 'anonymous');
  image.onload = function () {
    var canvas = document.createElement('canvas');
    canvas.width = image.width;
    canvas.height = image.height;
    var context = canvas.getContext('2d');
    context.drawImage(image, 0, 0, image.width, image.height);

    var url = canvas.toDataURL(); //得到图片的base64编码数据
    var a = document.createElement('a'); // 生成一个a元素
    var event = new MouseEvent('click'); // 创建一个单击事件
    a.download = name || 'photo'; // 设置图片名称
    a.href = url; // 将生成的URL设置为a.href属性
    a.dispatchEvent(event); // 触发a的单击事件
  };
}
// 获取渠道码用户列表
function getUserList() {
  getUserListApi(userData.value)
    .then(async (res) => {
      let data = res.data;
      let arr = [];
      data.list.map((i) => {
        arr.push(i.user);
      });
      tabList.value = arr;
      total2.value = data.count;
      loading2.value = false;
    })
    .catch((res) => {
      loading.value = false;
      tabList.value = [];
      ElMessage.error(res.msg);
    });
}

// 获取分组
function getUserLabelAll(key) {
  wechatQrcodeTree().then((res) => {
    let data = res.data.data;
    let obj = {
      cate_name: '全部',
      id: '',
    };
    data.unshift(obj);
    data.forEach((el) => {
      el.status = false;
    });
    if (!key) {
      sortName.value = data[0].id;
      tableFrom.value.cate_id = data[0].id;
      getList();
    }
    labelSort.value = data;
  });
}
// 添加分类
function addSort() {
  modalForm(wechatQrcodeCreate(0)).then(() => getUserLabelAll());
}
//编辑标签
function labelEdit(item) {
  modalForm(wechatQrcodeCreate(item.id)).then(() => getUserLabelAll(1));
}
function deleteSort(row, tit) {
  let num = labelSort.value.findIndex((e) => {
    return e.id == row.id;
  });
  let delfromData = {
    title: tit,
    num: num,
    url: `marketing/channel_code/cate/del/${row.id}`,
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
  tableFrom.value.page = 1;
  current.value = index;
  labelSort.value.forEach((el) => {
    el.status = false;
  });
  tableFrom.value.cate_id = name.id;
  getList();
}
function cancel() {
  formValidate.value = {
    page: 1,
    limit: 15,
    data: '',
    type: '',
    nickname: '',
  };
}
// 编辑
function edit(row) {
  // vue-router 4 中 path 内拼接的 query 会被丢弃，必须用 query 对象传递参数
  router.push({
    path: routePre + '/marketing/channel_code/create',
    query: { id: row.id },
  });
}
// 添加
function add() {
  router.push({
    path: routePre + '/marketing/channel_code/create',
  });
}
//
function getListService() {
  loading2.value = true;
  kefucreateApi(formValidate.value)
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
// 搜索
function userSearchs() {
  formValidate.value.page = 1;
  getList();
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `/marketing/channel_code/del/${row.id}`,
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
  wechatQrcodeList(tableFrom.value)
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
// 修改是否显示
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  wechatQrcodeStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
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
.label-name {
  margin: 2px 2px;
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
