<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-row class="action-row mb14">
        <el-col :span="24">
          <el-button v-auth="['setting-store_service-add']" type="primary" v-db-click @click="add" class="mr10"
            >添加客服</el-button
          >
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
        <el-table-column label="客服头像" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-if="scope.row.avatar" v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="客服名称" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.wx_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="客服状态" min-width="130">
          <template #default="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template #default="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除客服', scope.$index)">删除</a>
            <el-divider direction="vertical" v-if="scope.row.status" />
            <a v-db-click @click="goChat(scope.row)" v-if="scope.row.status">进入工作台</a>
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

    <!--聊天记录-->
    <el-dialog v-model="modals3" title="聊天记录" width="720px">
      <div v-if="isChat" class="modelBox">
        <el-table
          v-loading="loading3"
          highlight-current-row
          no-userFrom-text="暂无数据"
          no-filtered-userFrom-text="暂无筛选结果"
          :data="tableList3"
        >
          <el-table-column label="用户名称" width="200">
            <template #default="scope">
              <span>{{ scope.row.nickname }}</span>
            </template>
          </el-table-column>
          <el-table-column label="用户头像" min-width="90">
            <template #default="scope">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="scope.row.headimgurl" />
              </div>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right" width="170">
            <template #default="scope">
              <a v-db-click @click="look(scope.row)">查看对话</a>
            </template>
          </el-table-column>
        </el-table>
        <div class="acea-row row-right page">
          <pagination
            v-if="total3"
            :total="total3"
            v-model:page="formValidate3.page"
            v-model:limit="formValidate3.limit"
            @pagination="getListRecord"
          />
        </div>
      </div>
      <div v-if="!isChat">
        <el-button type="primary" v-db-click @click="isChat = true">返回聊天记录</el-button>
        <el-table
          v-loading="loading5"
          highlight-current-row
          no-userFrom-text="暂无数据"
          class="mt14"
          no-filtered-userFrom-text="暂无筛选结果"
          :data="tableList5"
        >
          <el-table-column label="用户名称" min-width="200">
            <template #default="scope">
              <span>{{ scope.row.nickname }}</span>
            </template>
          </el-table-column>
          <el-table-column label="用户头像" min-width="90">
            <template #default="scope">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="scope.row.avatar" />
              </div>
            </template>
          </el-table-column>
          <el-table-column label="发送消息" min-width="200">
            <template #default="scope">
              <span>{{ scope.row.msn }}</span>
            </template>
          </el-table-column>
          <el-table-column label="发送时间" min-width="200">
            <template #default="scope">
              <span>{{ scope.row.add_time }}</span>
            </template>
          </el-table-column>
        </el-table>
        <div class="acea-row row-right page">
          <pagination
            v-if="total5"
            :total="total5"
            v-model:page="formValidate5.page"
            v-model:limit="formValidate5.limit"
            @pagination="getChatlist"
          />
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { ElMessage } from 'element-plus';
import { setCookies } from '@/libs/util';
import Setting from '@/setting';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import {
  kefuListApi,
  kefucreateApi,
  kefuaddApi,
  kefuAddApi,
  kefusetStatusApi,
  kefuEditApi,
  kefuRecordApi,
  kefuChatlistApi,
  kefuLogin,
} from '@/api/setting';
import { useMediaStore } from '@/store/module/media';
import { useUserLevelStore } from '@/store/module/userLevel';

defineOptions({ name: 'index' });

const mediaStore = useMediaStore();
const userLevelStore = useUserLevelStore();

// 原 filters.typeFilter
function typeFilter(status) {
  const statusMap = {
    wechat: '微信用户',
    routine: '小程序用户',
  };
  return statusMap[status];
}

const isMobile = computed(() => mediaStore.isMobile);
const categoryId = computed(() => userLevelStore.categoryId);
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const isChat = ref(true);
const formValidate3 = reactive({
  page: 1,
  limit: 15,
});
const total3 = ref(0);
const loading3 = ref(false);
const modals3 = ref(false);
const tableList3 = ref([]);
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
const eidtLoading = ref(false);

// created
getList();

// 进入工作台
function goChat(item) {
  kefuLogin(item.id)
    .then((res) => {
      var url = '';
      if (res.data.token) {
        let expires = getExpiresTime(res.data.exp_time);
        setCookies('kefu_token', res.data.token, expires);
        setCookies('kefu_uuid', res.data.kefuInfo.uid, expires);
        setCookies('kefu_expires_time', res.data.exp_time, expires);
        setCookies('kefuInfo', res.data.kefuInfo, expires);
        // 工作台为 admin 站点下的独立全屏路由，必须带 routePre（/admin）前缀，否则 dev/prod 均无法命中
        if (mediaStore.isMobile) {
          url = `${window.location.origin}${Setting.routePre}/kefu/mobile_list`;
        } else {
          url = `${window.location.origin}${Setting.routePre}/kefu/pc_list`;
        }

        window.open(url, '_blank');
      }
    })
    .catch((error) => {
      ElMessage.error(error.msg);
    });
}
function getExpiresTime(expiresTime) {
  let nowTimeNum = Math.round(new Date() / 1000);
  let expiresTimeNum = expiresTime - nowTimeNum;
  return parseFloat(parseFloat(parseFloat(expiresTimeNum / 60) / 60) / 24);
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
function handleReachBottom() {
  return new Promise((resolve) => {
    formValidate.page = formValidate.page + 1;
    setTimeout(() => {
      // loading2.value = true;
      kefucreateApi(formValidate)
        .then(async (res) => {
          let data = res.data;
          // tableList2.value = data.list;
          if (data.list.length > 0) {
            for (let i = 0; i < data.list.length; i++) {
              tableList2.value.push(data.list[i]);
            }
          }
          total2.value = data.count;
          loading2.value = false;
        })
        .catch((res) => {
          loading2.value = false;
          ElMessage.error(res.msg);
        });
      resolve();
    }, 2000);
  });
}
// 查看对话
function look(row) {
  isChat.value = false;
  rowRecord.value = row;
  getChatlist();
}
// 查看对话列表
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
// 聊天记录列表
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
// 编辑
function edit(row) {
  if (eidtLoading.value) return;
  eidtLoading.value = true;
  modalForm(kefuEditApi(row.id), { onMounted: bindServiceAvatar })
    .then(() => {
      getList();
      eidtLoading.value = false;
    })
    .catch(() => {
      eidtLoading.value = false;
    });
}

// 选择关联用户后，客服未手动设置头像时自动带入用户头像。
function bindServiceAvatar(formApi) {
  const getAvatar = (value) => {
    const selected = Array.isArray(value) ? value[0] : value;
    if (!selected || typeof selected !== 'object') return '';
    return selected.image || selected.avatar || selected.headimgurl || '';
  };
  let autoAvatar = getAvatar(formApi.getValue?.('image'));
  let manuallySelected = Boolean(
    formApi.getValue?.('avatar') && autoAvatar && formApi.getValue('avatar') !== autoAvatar,
  );
  const onUserChange = (value) => {
    if (manuallySelected) return;
    const avatar = getAvatar(value);
    if (!avatar) return;
    autoAvatar = avatar;
    formApi.setValue('avatar', avatar);
  };
  const onAvatarChange = (value) => {
    const avatar = Array.isArray(value) ? value[0] : value;
    manuallySelected = Boolean(avatar && avatar !== autoAvatar);
    if (!avatar) autoAvatar = '';
  };
  formApi.bus?.$on('change-image', onUserChange);
  formApi.bus?.$on('change-avatar', onAvatarChange);
}
// 添加
function add() {
  modalForm(kefuaddApi(), { onMounted: bindServiceAvatar }).then(() => getList());
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
// 客服列表（原实现存在 `true(this.formValidate)` 语法错误，按意图修正为 kefucreateApi 调用）
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
    url: `app/wechat/kefu/${row.id}`,
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
  kefuListApi(tableFrom)
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
  kefusetStatusApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 添加客服
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
</style>
