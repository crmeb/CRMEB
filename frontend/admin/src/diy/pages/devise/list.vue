<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title mr20">{{ $route.meta.title }}</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          装修页面与模板列表，支持新增页面、导入模板、编辑设计与设为默认。
        </span>
      </div>
      <div>
        <div style="float: right" v-if="cardShow == 1 || cardShow == 2">
          <el-button class="bnt" type="primary" v-db-click @click="submit" :loading="loadingExist">保存</el-button>
          <el-button v-if="cardShow == 1" class="bnt ml20" v-db-click @click="reast">重置</el-button>
        </div>
      </div>
    </div>
    <el-card class="h100" :bordered="false" shadow="never" v-if="cardShow == 0">
      <div class="acea-row no-warp">
        <div class="iframe-col">
          <iframe class="iframe-box" :src="iframeUrl" frameborder="0" ref="iframe"></iframe>
          <div class="mask"></div>
        </div>
        <div class="table-box">
          <div class="acea-row row-between-wrapper">
            <div class="button acea-row row-middle">
              <el-button class="m-r-10" type="primary" @click="createdPage">添加页面</el-button>
              <el-upload
                :action="UploadPath"
                :before-upload="beforeUpload"
                :on-success="handleSuccess"
                :on-error="handleError"
                :limit="1"
                :show-file-list="false"
                accept=".txt"
                :headers="header"
              >
                <el-button type="primary">导入模板</el-button>
              </el-upload>
            </div>
          </div>
          <el-table
            :data="list"
            ref="table"
            class="mt14"
            v-loading="loading"
            highlight-current-row
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <el-table-column label="页面ID" width="80">
              <template #default="scope">
                <span>{{ scope.row.id }}</span>
              </template>
            </el-table-column>
            <el-table-column label="模板名称" min-width="130">
              <template #default="scope">
                <span>{{ scope.row.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="模板类型" min-width="130">
              <template #default="scope">
                <el-tag type="success" size="default" v-if="scope.row.status == 1">首页</el-tag>
                <el-tag type="info" size="default" v-else class="mr10">专题页</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="添加时间" min-width="130">
              <template #default="scope">
                <span>{{ scope.row.add_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="更新时间" min-width="130">
              <template #default="scope">
                <span>{{ scope.row.update_time }}</span>
              </template>
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="210">
              <template #default="scope">
                <div
                  style="display: inline-block"
                  v-if="scope.row.status || scope.row.is_diy"
                  v-db-click
                  @click="edit(scope.row)"
                >
                  <a
                    v-if="scope.row.is_diy === 1"
                    class="target"
                    ref="target"
                    :href="`${url}${$routeProStr}/setting/pages/diy_index?id=${scope.row.id}&name=${
                      scope.row.template_name || 'moren'
                    }`"
                  >
                    编辑</a
                  >
                  <a v-else class="target">编辑</a>
                </div>
                <el-divider
                  direction="vertical"
                  v-if="(scope.row.status || scope.row.is_diy) && scope.row.id != 1 && scope.row.status != 1"
                />

                <div style="display: inline-block" v-if="scope.row.id != 1 && scope.row.status != 1">
                  <a v-db-click @click="del(scope.row, '删除此模板', scope.$index)">删除</a>
                </div>
                <el-divider
                  direction="vertical"
                  v-if="(scope.row.id != 1 && scope.row.status != 1) || scope.row.is_diy"
                />
                <div style="display: inline-block" v-if="scope.row.is_diy">
                  <a v-db-click @click="preview(scope.row, scope.$index)">预览</a>
                </div>
                <el-divider direction="vertical" v-if="scope.row.is_diy && scope.row.status != 1" />
                <div style="display: inline-block" v-if="scope.row.status != 1">
                  <a v-db-click @click="setStatus(scope.row, scope.$index)">设为首页</a>
                </div>
                <el-divider direction="vertical" />
                <div style="display: inline-block">
                  <a v-db-click @click="exportView(scope.row.id)">导出模版</a>
                </div>
              </template>
            </el-table-column>
          </el-table>
          <div class="acea-row row-right page">
            <pagination
              v-if="total"
              :total="total"
              v-model:page="diyFrom.page"
              v-model:limit="diyFrom.limit"
              @pagination="diyProList"
            />
          </div>
        </div>
      </div>
    </el-card>
    <goodClass v-else-if="cardShow == 1" ref="category" @parentFun="getChildData"></goodClass>
    <users v-else ref="usersRef" @parentFun="getChildData"></users>
    <el-dialog v-model="isTemplate" title="开发移动端链接" :z-index="1" width="540px" @closed="cancel">
      <div class="article-manager">
        <el-card :bordered="false" shadow="never" class="ivu-mt">
          <el-form
            ref="formItemRef"
            :model="formItem"
            label-width="120px"
            label-position="right"
            :rules="ruleValidate"
            @submit.prevent
          >
            <el-row :gutter="24">
              <el-col :span="24">
                <el-col>
                  <el-form-item label="开发移动端链接：" prop="link" label-for="link">
                    <el-input v-model="formItem.link" placeholder="http://localhost:8080" />
                  </el-form-item>
                </el-col>
              </el-col>
            </el-row>
          </el-form>
        </el-card>
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button type="primary" v-db-click @click="handleSubmit">提交</el-button>
        </span></template
      >
    </el-dialog>
    <el-dialog v-model="modal" width="540px" title="预览">
      <div>
        <div v-viewer class="acea-row row-around code">
          <div class="acea-row row-column-around row-between-wrapper">
            <div class="QRpic" ref="qrCodeUrl"></div>
            <span class="mt10">公众号二维码</span>
          </div>
          <div class="acea-row row-column-around row-between-wrapper">
            <div class="QRpic">
              <img v-lazy="qrcodeImg" />
            </div>
            <span class="mt10">小程序二维码</span>
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import Setting from '@/setting';
import {
  diyProList as diyProListApi,
  diyDel,
  setStatus as setStatusApi,
  recovery as recoveryApi,
  getRoutineCode,
  setDefault as setDefaultApi,
  exportDiyDataApi,
} from '@diy/api';
import QRCode from '@/vendor/qrcodejs2';
import goodClass from './goodClass';
import users from './users';
import { getCookies, setCookies } from '@/libs/util';
import { modalSure } from '@/utils/public';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'devise_list' });

const route = useRoute();
const router = useRouter();
const mobildConfigStore = useMobildConfigStore();

const category = ref(null);
const usersRef = ref(null);
const formItemRef = ref(null);
const qrCodeUrl = ref(null);

const loading = ref(false);
const theme3 = ref('light');
const menuList = ref([
  {
    name: '商城首页',
    id: 1,
  },
  {
    name: '商品分类',
    id: 2,
  },
  {
    name: '个人中心',
    id: 3,
  },
]);
const list = ref([]);
const iframeUrl = ref('');
const modal = ref(false);
const UploadPath = Setting.apiBaseURL + '/diy_pro/import/data';
const BaseURL = Setting.apiBaseURL.replace(/adminapi/, '');
const cardShow = ref(0);
const loadingExist = ref(false);
const isDiy = ref(1);
const qrcodeImg = ref('');
const diyFrom = reactive({
  type: '',
  page: 1,
  limit: 15,
});
const total = ref(0);
const formItem = reactive({
  id: 0,
  link: '',
});
const isTemplate = ref(false);
const ruleValidate = {
  link: [{ required: true, message: '请输入移动端链接', trigger: 'blur' }],
};
const url = window.location.origin;
const header = ref({});

watch(
  () => route.params.type,
  () => {
    cardShow.value = route.params.type;
  },
);

// created
cardShow.value = route.params.type;
diyProList();
iframeUrl.value = `${Setting.h5BaseURL || location.origin}/pages/index/index?mdType=iframeWindow`;
getToken();

onMounted(() => {
  mobildConfigStore.SETEMPTY();
});

function getToken() {
  header.value['Authori-zation'] = 'Bearer ' + getCookies('token');
}
function beforeUpload(file) {
  const isTXT = file.type === 'text/plain';
  if (!isTXT) {
    ElMessage.error('只能上传TXT文件');
  }
  return isTXT;
}
function handleSuccess(response, file) {
  if (response.status == 200) {
    ElMessage.success(response.msg);
    diyProList();
  } else {
    ElMessage.error(response.msg);
  }
}
function handleError(err, file) {
  ElMessage.error('文件上传失败');
}
function exportView(id) {
  exportDiyDataApi(id)
    .then((res) => {
      const textToSave = res.data.value;
      const blob = new Blob([textToSave], { type: 'text/plain;charset=utf-8' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = res.data.filename; // 设置下载文件的名称
      document.body.appendChild(a);
      a.click(); // 模拟点击触发下载
      document.body.removeChild(a); // 清理DOM
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function createdPage() {
  router.push({
    path: Setting.routePre + '/setting/pages/diy_index',
    query: { id: 0, name: '首页', type: 1 },
  });
  // nextTick(() => {
  //   window.open(`${url}${Setting.routePre}/setting/pages/diy_index?id=0&name=首页&type=0`);
  // });
}
function cancel() {
  formItemRef.value.resetFields();
}
function refreshFrame() {
  iframeUrl.value = '';
  setTimeout((e) => {
    iframeUrl.value = `${Setting.h5BaseURL || location.origin}/pages/index/index?mdType=iframeWindow`;
  }, 200);
}
function getChildData(e) {
  loadingExist.value = e;
}
function submit() {
  if (cardShow.value == 1) {
    category.value.onSubmit();
  } else {
    usersRef.value.onSubmit();
  }
}
function reast() {
  if (cardShow.value == 1) {
    category.value.onSubmit(1);
  } else {
    usersRef.value.getInfo();
  }
}
function bindMenuItem(index) {
  cardShow.value = index;
}
function onCopy() {
  ElMessage.success('复制预览链接成功');
}
function onError() {
  ElMessage.error('复制预览链接失败');
}
//生成二维码
function creatQrCode(id) {
  qrCodeUrl.value.innerHTML = '';
  let url = `${BaseURL}pages/annex/special/index?id=${id}`;
  var qrcode = new QRCode(qrCodeUrl.value, {
    text: url, // 需要转换为二维码的内容
    width: 160,
    height: 160,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
  });
}
//小程序二维码
function routineCode(id) {
  getRoutineCode(id)
    .then((res) => {
      qrcodeImg.value = res.data.image;
    })
    .catch((err) => {
      ElMessage.error(err);
    });
}
function preview(row) {
  modal.value = true;
  nextTick((e) => {
    creatQrCode(row.id);
    routineCode(row.id);
  });
}
function handleSubmit() {
  formItemRef.value.validate((valid) => {
    if (valid) {
      setCookies('moveLink', formItem.link);
      router.push({
        path: Setting.routePre + '/setting/pages/diy',
        query: { id: formItem.id, type: 1 },
      });
    } else {
      return false;
    }
  });
}
function changeMenu(row, index, name) {
  switch (name) {
    case '1':
      setDefault(row);
      break;
    case '2':
      recovery(row);
      break;
    case '3':
      del(row, '删除此模板', index);
      break;
    default:
  }
}
//设置默认数据
function setDefault(row) {
  setDefaultApi(row.id)
    .then((res) => {
      ElMessage.success(res.msg);
      diyProList();
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
// 获取列表
function diyProList() {
  // let storage = window.localStorage;
  // iframeUrl = storage.getItem("iframeUrl");
  loading.value = true;
  diyProListApi(diyFrom).then((res) => {
    loading.value = false;
    let data = res.data;
    list.value = data.list;
    total.value = data.count;
  });
}
// 编辑
function edit(row) {
  formItem.id = row.id;
  if (!row.is_diy) {
    if (!row.status) {
      ElMessage.error('请先设为首页在进行编辑');
    } else {
      router.push({
        path: Setting.routePre + '/setting/pages/diy',
        query: { id: row.id, type: 0 },
      });
    }
  }
}
// 添加
// add() {
//   modalForm(getDiyCreate()).then(() => diyProList());
// }
// 添加
function add() {
  // router.push({
  //   path: Setting.routePre + '/setting/pages/diy_index',
  //   query: { id: 0, name: '首页', type: 1 },
  // });
}
// 删除
function del(row) {
  let delfromData = {
    title: '删除',
    num: 2000,
    url: 'diy/del/' + row.id,
    method: 'DELETE',
    data: {
      type: 1,
    },
  };
  modalSure(delfromData)
    .then((res) => {
      diyProList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 使用模板
async function setStatus(row) {
  ElMessageBox({
    title: '提示',
    message: '是否把该模板设为首页',
    showCancelButton: true,
    cancelButtonText: '取消',
    confirmButtonText: '确定',
    iconClass: 'el-icon-warning',
    confirmButtonClass: 'btn-custom-cancel',
  })
    .then(() => {
      setStatusApi(row.id, {
        type: 1,
      })
        .then((res) => {
          refreshFrame();
          ElMessage.success(res.msg);
          diyProList();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    })
    .catch(() => {});
}
function recovery(row) {
  recoveryApi(row.id).then((res) => {
    ElMessage.success(res.msg);
    diyProList();
  });
}
</script>

<style lang="scss" scoped>
.ivu-mt {
  background-color: #fff;
  padding-bottom: 50px;
}
.no-warp {
  flex-wrap: nowrap !important;
}
:deep(.el-card__body) {
  padding: 40px;
}
.bnt {
  width: 80px !important;
}
.iframe-col {
  width: 375px;
  min-width: 375px;
  height: 650px;
  margin-right: 30px;
  position: relative;
}
.iframe-box {
  width: 100%;
  height: 100%;
  border-radius: 10px;
  border: 1px solid #eee;
}
.target-add {
  text-decoration: none;
  color: #fff;
}
.mask {
  position: absolute;
  left: 0;
  width: 100%;
  top: 0;
  height: 100%;
  background-color: rgba(0, 0, 0, 0);
}
:deep(.ivu-menu-vertical .ivu-menu-item),
.ivu-menu-vertical .ivu-menu-submenu-title {
  text-align: center;
}
:deep(.i-layout-page-header) {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
:deep(.ivu-page-header) {
  border-bottom: unset;
  position: fixed;
  z-index: 9;
  width: 100%;
}
:deep(.ivu-menu-vertical .ivu-menu-item-group-title) {
  display: none;
}
:deep(.ivu-menu-vertical.ivu-menu-light:after) {
  display: none;
}
:deep(.ivu-menu) {
  z-index: 0 !important;
}
:deep(.ivu-row) {
  display: flex;
}
.table-box {
  flex: 1;
}
.code {
  position: relative;
}
.QRpic {
  width: 160px;
  height: 160px;

  img {
    width: 100%;
    height: 100%;
  }
}
.left-wrapper {
  padding: 20px 0 0 20px;
  border-right: unset;
}
.tree_tit {
  height: 50px;
  line-height: 50px;
  font-size: 14px;
  color: #333;
  font-weight: 500;
  text-align: center;
  border-bottom: 1px solid #ebeef5;
}
.picCon {
  width: 280px;
  height: 510px;
  background: #ffffff;
  border: 1px solid #eeeeee;
  border-radius: 25px;
  .pictrue {
    width: 250px;
    height: 417px;
    border: 1px solid #eeeeee;
    opacity: 1;
    border-radius: 10px;
    margin: 30px auto 0 auto;

    img {
      width: 100%;
      height: 100%;
      border-radius: 10px;
    }
  }
  .circle {
    width: 36px;
    height: 36px;
    background: #ffffff;
    border: 1px solid #eeeeee;
    border-radius: 50%;
    margin: 13px auto 0 auto;
  }
}
.tree-vis {
  display: flex;
  flex-direction: column;
  .tab-item {
    padding: 15px 20px;
    cursor: pointer;
  }
  .active {
    background-color: var(--prev-bg-main-color);
    color: var(--prev-color-primary);
    border-right: 2px solid var(--prev-color-primary);
  }
}
</style>
