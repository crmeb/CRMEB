<template>
  <div class="upgrade">
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <div class="header">
        <div>
          当前版本<span class="v"></span><span class="num">{{ userInfoStore.version }}</span>
        </div>
        <div class="info title">
          更新说明：
          <span v-if="upgradeStatus.status != 1">已升级至最新版本，无需更新</span>
          <ul v-if="upgradeStatus.status == 1">
            <li>{{ upgradeStatus.title }}</li>
          </ul>
        </div>
        <el-button
          v-if="currentTab == 1 && upgradeStatus.status == 1"
          type="primary"
          class="primary btn update"
          v-db-click
          @click="update()"
          >立即更新</el-button
        >
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-tabs v-model="currentTab" @tab-change="handleClick">
        <el-tab-pane
          :label="item.label"
          :name="item.value.toString()"
          v-for="item in headerList"
          :key="item.id"
        ></el-tab-pane>
      </el-tabs>
      <div class="contentTime" v-if="currentTab == 1">
        <div
          class="acea-row row-top on"
          @mouseenter="quearyEvear(item.id, index)"
          v-for="(item, index) in upgradeList"
          :key="index"
          :class="{ active: index == dynamic }"
        >
          <div class="time">{{ item.release_time }}</div>
          <el-timeline class="list">
            <el-timeline-item>
              <!-- <template #dot><Icon :type="index==0 ? 'md-radio-button-on' : 'md-radio-button-off'"/></template> -->
              <el-collapse>
                <el-collapse-item>
                  <template #title>
                    {{ item.title }} v{{ item.first_version }}.{{ item.second_version }}.{{ item.third_version }}.{{
                      item.fourth_version
                    }}<i class="el-icon-arrow-down" />
                  </template>
                  <div class="info">
                    <ul style="white-space: pre-wrap">
                      <li v-html="item.content"></li>
                    </ul>
                  </div>
                </el-collapse-item>
              </el-collapse>
            </el-timeline-item>
            <el-button
              v-if="item.client_package_link"
              type="success"
              class="primary btn"
              v-db-click
              @click="downloadFile(item.client_package_link)"
              >移动端源码</el-button
            >
          </el-timeline>
        </div>
      </div>
      <div v-if="currentTab == 2" height="550">
        <div class="contentTime">
          <div
            class="acea-row row-top off"
            @mouseenter="quearyEvear(item.id, index)"
            v-for="(item, index) in upgradeLogList"
            :key="index"
            :class="{ active: index == dynamic }"
          >
            <div class="time">
              <div v-if="index == 0">最近更新</div>
              <div>{{ item.upgrade_time }}</div>
            </div>
            <el-timeline class="list">
              <el-timeline-item>
                <template #dot><Icon :type="index == 0 ? 'md-radio-button-on' : 'md-radio-button-off'" /></template>
                <el-collapse>
                  <el-collapse-item>
                    <template #title>
                      {{ item.title }} v{{ item.first_version }}.{{ item.second_version }}.{{ item.third_version }}.{{
                        item.fourth_version
                      }}<i type="el-icon-arrow-down" />
                    </template>
                    <div class="info">
                      <ul style="white-space: pre-wrap">
                        <li v-html="item.content"></li>
                      </ul>
                    </div>
                  </el-collapse-item>
                </el-collapse>
              </el-timeline-item>
            </el-timeline>
          </div>
        </div>
      </div>
    </el-card>
    <!-- 免责声明 -->
    <el-dialog
      v-model="declaration"
      width="470px"
      custom-clas="vertical-center-modal"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <template #header
        ><p class="header-modal">
          <span>{{ upgradeAgreement.title }}</span>
        </p></template
      >
      <div class="describe">
        <p v-html="upgradeAgreement.content"></p>
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="reject">拒 绝</el-button>
          <el-button type="primary" v-db-click @click="agree">同 意</el-button>
        </span></template
      >
    </el-dialog>
    <!-- 升级 -->
    <el-dialog
      v-model="updateModal"
      width="470px"
      custom-clas="vertical-center-modal"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <template #header
        ><p class="header-modal2">
          <span>升级至v{{ forceVersion }}</span>
        </p></template
      >
      <div class="describe" v-if="upgradeProgress.speed != '100.0' || downloadStatus != '200'">
        <i-circle
          :size="140"
          :trail-width="4"
          :stroke-width="5"
          :percent="Number(upgradeProgress.speed)"
          stroke-linecap="square"
          stroke-color="#43a3fb"
        >
          <div class="demo-i-circle-custom">
            <p>{{ upgradeProgress.tip || '升级中' }}</p>
          </div>
        </i-circle>
        <div class="proportion">{{ !upgradeProgress.speed ? '' : upgradeProgress.speed + '%' }}</div>
        <div class="wait">正在更新，请耐心等候～</div>
      </div>
      <!-- Vue2 下同名 footer 插槽后者覆盖前者，此处仅保留原最后一个生效插槽 -->
      <div v-if="upgradeProgress.speed == '100.0'" class="describe">
        <el-progress type="circle" :percentage="100" status="success"></el-progress>
        <div class="success">升级成功</div>
      </div>
      <template #footer
        ><span v-if="upgradeProgress.speed == '100.0'" class="dialog-footer">
          <el-button v-db-click @click="cancel">取 消</el-button>
          <el-button type="primary" v-db-click @click="back">确 认</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import {
  upgradeListApi,
  upgradeProgressApi,
  upgradeAgreementApi,
  upgradeStatusApi,
  upgradeLogListApi,
  upgradeableListApi,
  downloadApi,
} from '@/api/system';
import axios from 'axios';
import { AccountLogout } from '@/api/account';
import { getCookies, removeCookies } from '@/libs/util';

import Setting from '@/setting';
import { useUpgradeStore } from '@/store/module/upgrade';
import { useUserInfoStore } from '@/store/module/userInfo';

defineOptions({ name: 'systemUpgradeclient' });

const router = useRouter();
const upgradeStore = useUpgradeStore();
const userInfoStore = useUserInfoStore();

const Panel = ref('1');
const currentTab = ref('1');
const declaration = ref(false);
const updateModal = ref(false);
const modal_loading = ref(false);
const percent = ref(0);
const params = ref([]);
const arr = ref([]);
const dynamic = ref(false);
const status = ref(false);
const version = ref('');
const forceVersion = ref('');
const headerList = [
  {
    label: '系统升级',
    value: 1,
  },
  {
    label: '升级记录',
    value: 2,
  },
];
const upgradeList = ref([]);
const upgradeLogList = ref([]);
const upgradeableList = ref([]);
const upgradeProgress = ref({
  speed: 0,
});
const upgradeAgreement = ref([]);
const upgradeStatus = ref({});
const downloadStatus = ref(null);
const page = ref(1);
const limit = ref(15);
// 定时器
let timer = null;
const params_key = ref(undefined);
const newKey = ref(null);
const servionStatus = ref(false);
const count = ref(0);

watch(() => upgradeProgress.value.speed, {
  handler(newVal, oldVal) {
    if (newVal === '100.0') {
      clearInterval(timer);
    }
  },
});

function handleReachBottom() {
  if (count.value === upgradeLogList.value.length) {
    ElMessage.warning('暂无更多升级记录');
  } else {
    getUpgradeLogList();
  }
}
function handleClick() {
  page.value = 1;
  if (currentTab.value == 1) {
    getupgradeableList();
  } else {
    upgradeLogList.value = [];
    getUpgradeLogList();
  }
}
// 升级列表
async function getUpgradeList() {
  let data = {
    page: page.value,
    limit: limit.value,
  };
  let res = await upgradeListApi(data);
  upgradeList.value = res.data.list;
  upgradeList.value = res.data.list;
}
// 升级记录
function getUpgradeLogList() {
  let data = {
    page: page.value,
    limit: limit.value,
  };
  upgradeLogListApi(data)
    .then((res) => {
      upgradeLogList.value = [...upgradeLogList.value, ...res.data.list];
      count.value = res.data.count;
      if (upgradeLogList.value.length < count.value) {
        page.value++;
      }
      if (count.value === upgradeLogList.value.length) {
        ElMessage.warning('暂无更多升级记录');
      } else {
        getUpgradeLogList();
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

// 可升级列表
async function getupgradeableList() {
  let res = await upgradeableListApi();
  upgradeableList.value = res.data;
  let firstVer = res.data[0];
  if (upgradeStore.toggleStatus || upgradeStatus.value.force_reminder) {
    const data = res.data.find((item) => item.force_reminder === 1);
    newKey.value = data.package_key;
    forceVersion.value =
      data.first_version + '.' + data.second_version + '.' + data.third_version + '.' + data.fourth_version;
  } else {
    params_key.value = upgradeableList.value[0].package_key;
    forceVersion.value =
      firstVer.first_version +
      '.' +
      firstVer.second_version +
      '.' +
      firstVer.third_version +
      '.' +
      firstVer.fourth_version;
  }

  // arr.forEach((item) => {
  //   item.servion =
  //     item.first_version + '.' + item.second_version + '.' + item.third_version + '.' + item.fourth_version;
  // });

  // let data = [];
  // arr.map((ele) => {
  //   data.push(ele.servion);
  // });
  // // 版本号排序
  // function sortVersion(list) {
  //   return list.sort((version1, version2) => {
  //     const arr1 = version1.split('.').map((e) => e * 1);
  //     const arr2 = version2.split('.').map((e) => e * 1);
  //     const length = Math.max(arr1.length, arr2.length);
  //     for (let i = 0; i < length; i++) {
  //       if ((arr1[i] || 0) > (arr2[i] || 0)) return 1;
  //       if ((arr1[i] || 0) < (arr2[i] || 0)) return -1;
  //     }
  //     return 0;
  //   });
  // }
  // this.forceVersion = data[0];
  // this.params.map((item) => {
  //   if (item.servion == data[data.length - 1]) {
  //     this.newKey = item.package_key;
  //   }
  // });
}
// 下载升级包
function getdownload() {
  if (upgradeStore.toggleStatus || upgradeStatus.value.force_reminder) {
    params_key.value = newKey.value;
  }
  downloadApi(params_key.value)
    .then((res) => {
      // downloadStatus.value = res.status;
      if (res.status == 200) {
        if (upgradeProgress.value.speed !== '100.0') {
          timer = setInterval(() => {
            setTimeout(() => {
              getUpgradeProgress();
            }, 0);
          }, 5000);
        } else {
          clearInterval(timer);
          updateModal.value = false;
        }
      } else {
        updateModal.value = false;
      }
    })
    .catch((err) => {
      clearInterval(timer);
      ElMessage.error('下载终止');
      updateModal.value = false;
    });
}
function downloadFile(url) {
  window.open(url, '_blank');
}

// 升级进度
function getUpgradeProgress() {
  upgradeProgressApi()
    .then((res) => {
      upgradeProgress.value = res.data;
      downloadStatus.value = res.status;
    })
    .catch((res) => {
      clearInterval(timer);
      ElMessage.error(res.msg);
    });
}
// 升级协议
function getUpgradeAgreement() {
  upgradeAgreementApi()
    .then((res) => {
      upgradeAgreement.value = res.data;
      declaration.value = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 升级状态
function getUpgradeStatus() {
  upgradeStatusApi()
    .then((res) => {
      upgradeStatus.value = res.data;
      if (res.data.force_reminder) {
        declaration.value = true;
        getUpgradeAgreement();
      }
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 立即更新
function update(item) {
  declaration.value = true;
  if (item && upgradeStore.toggleStatus == false) {
    params_key.value = item.params_key;
  }
  // this.version = item.first_version + "." + item.second_version + "." + item.third_version + "." + item.fourth_version;
  getUpgradeAgreement();
}
// 比较相同版本号
function compare() {
  upgradeList.value.map((ele, i) => {
    upgradeList.value[i]['mn'] = ele.first_version + ele.second_version + ele.third_version + ele.fourth_version;

    upgradeableList.value.map((item, i) => {
      upgradeableList.value[i]['mn'] =
        item.first_version + item.second_version + item.third_version + item.fourth_version;
    });
    if (upgradeList.value[i].mn == upgradeableList.value[i].mn) {
      upgradeList.value[i]['indexn'] = true;
      upgradeList.value[i]['params_key'] = upgradeableList.value[i].package_key;
      upgradeableList.value[i]['indexn'] = true;
    }
  });
}
// 同意
function agree() {
  declaration.value = false;
  updateModal.value = true;
  getdownload();
  upgradeStore.TOGGLE_STATUS(false);
}
function reject() {
  declaration.value = false;
  upgradeStore.TOGGLE_STATUS(false);
}
function back() {
  updateModal.value = false;
  // this.getUpgradeList();
  clearInterval(timer);
  AccountLogout()
    .then((res) => {
      ElMessage.success('您已成功退出');
      router.replace(Setting.routePre + '/login');
      localStorage.clear();
      removeCookies('token');
      removeCookies('expires_time');
      removeCookies('uuid');
      // window.location.reload()
    })
    .catch((res) => {});
}
function backSure() {
  updateModal.value = false;
  clearInterval(timer);
}
function cancel() {
  updateModal.value = false;
  clearInterval(timer);
}
// 导出备份文件
function exports(item) {
  let name =
    item.title +
    'v' +
    item.first_version +
    '.' +
    item.second_version +
    '.' +
    item.third_version +
    '.' +
    item.fourth_version +
    '.' +
    item.upgrade_time;
  let href = Setting.apiBaseURL + `/system/upgrade_export/${item.id}/file`;
  axios({
    method: 'get',
    url: href,
    responseType: 'blob',
    headers: {
      'Authori-zation': 'Bearer ' + getCookies('token'),
    },
  }).then((res) => {
    var blob = new Blob([res.data], {
      type: 'application/octet-stream;charset=UTF-8',
    });
    var downloadElement = document.createElement('a');
    // 下载的文件名
    downloadElement.download = `${name}.zip`;
    // 创建下载的链接
    downloadElement.href = window.URL.createObjectURL(blob);
    // 点击下载
    downloadElement.click();
    document.body.appendChild(downloadElement);
    // 下载完成移除元素
    document.body.removeChild(downloadElement);
    // 释放掉blob对象
    window.URL.revokeObjectURL(downloadElement.href);
  });
}
function handleDownload(item) {
  let name =
    item.title +
    'v' +
    item.first_version +
    '.' +
    item.second_version +
    '.' +
    item.third_version +
    '.' +
    item.fourth_version +
    '.' +
    item.upgrade_time;
  let href = Setting.apiBaseURL + `/system/upgrade_export/${item.id}/data`;
  axios({
    method: 'get',
    url: href,
    responseType: 'blob',
    headers: {
      'Authori-zation': 'Bearer ' + getCookies('token'),
    },
  }).then((res) => {
    var blob = new Blob([res.data], {
      type: 'application/octet-stream;charset=UTF-8',
    });
    var downloadElement = document.createElement('a');
    // 下载的文件名
    downloadElement.download = `${name}.gz`;
    // 创建下载的链接
    downloadElement.href = window.URL.createObjectURL(blob);
    // 点击下载
    downloadElement.click();
    document.body.appendChild(downloadElement);
    // 下载完成移除元素
    document.body.removeChild(downloadElement);
    // 释放掉blob对象
    window.URL.revokeObjectURL(downloadElement.href);
  });
}
function quearyEvear(id, index) {
  dynamic.value = index;
}

onMounted(async () => {
  getUpgradeStatus();
  await getUpgradeList();
  await getupgradeableList();
  if (upgradeStore.toggleStatus) {
    servionStatus.value = true;
    getUpgradeAgreement();
  }
  // compare();
});

onUnmounted(() => {
  //销毁
  clearInterval(timer);
});
</script>

<style lang="scss" scoped>
.active {
  padding: 6px 0;
  // background-color: #eee !important;
}

.active .ivu-collapse-simple {
  // background-color: #eee !important;
}

.active .ivu-collapse-content {
  // background-color: #eee !important;
}

.active .ivu-timeline-item-head-custom {
  // background-color: #fff !important;
  // z-index 9999
}

.active .btn {
  top: 50px;
}

.primary {
  position: absolute;
  right: 0;
  bottom: 0;
}

.upgrade .header {
  font-size: 12px;
  color: var(--prev-color-text-regular);
  // border-bottom:1px dotted rgba(221,221,221,1);
  // padding-bottom: 25px;
}

.upgrade .header .v {
  color: var(--prev-color-primary);
  margin-left: 10px;
}

.upgrade .header .num {
  color: var(--prev-color-primary);
  font-size: 24px;
}

.upgrade .header .info {
  color: #999999;
}

.upgrade .header .title {
  color: #999999;
  display: flex;
}

.upgrade .header .info ul {
  color: #999999;
  display: flex;
  margin-left: 14px;
}

.upgrade .header .info ul li::marker {
  color: red;
}

.upgrade .header .info ul li + li {
  margin-left: 40px;
}

.upgrade .contentTime .acea-row {
  padding: 5px;
}

.upgrade .contentTime .list {
  position: relative;
  width: 85%;
}

.upgrade .contentTime .info {
  font-size: 12px !important;
  color: #999 !important;
  margin-top: 13px;
}

.upgrade .contentTime .info li {
  list-style-type: disc;
  margin-left: 16px;
  line-height: 26px;
  list-style: none;
}

.upgrade .contentTime .collapse {
  width: 100%;
}

.upgrade .contentTime .ivu-collapse {
  border: 0 !important;
}

.upgrade .contentTime .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header {
  height: unset !important;
  line-height: 20px !important;
  border: 0 !important;
  font-size: 16px !important;
  color: var(--prev-color-text-regular);
  font-weight: 600;
  // background: #eee;
}

.upgrade .contentTime .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header > i {
  color: #bbbbbb !important;
  margin-left: 9px;
}

.upgrade .contentTime .ivu-collapse > .ivu-collapse-item.ivu-collapse-item-active > .ivu-collapse-header > i {
  transform: rotate(180deg);
}

.upgrade .contentTime {
  margin-top: 30px;
  position: relative;
}

.upgrade .contentTime .btn {
  position: absolute;
  top: 5px;
  right: 60px;
  z-index: 99;
  height: max-content;
}

.upgrade .contentTime .btn1 {
  position: absolute;
  top: 0;
  right: -29px;
  z-index: 99;
  margin-left: 14px;
}

.upgrade .contentTime .time {
  font-size: 14px;
  line-height: 14px;
  color: #999;
  text-align: right;
  padding-right: 28px;
  min-width: 150px;
}

.upgrade .contentTime .ivu-timeline-item:after {
  content: ' ';
  position: absolute;
  top: 13px;
  left: 6.5px;
  width: 1px;
  height: calc(100% - 13px);
  background-color: #e8eaec;
}

.upgrade .contentTime:nth-child(2n) .ivu-timeline-item-head-custom {
  z-index: 2;
  font-size: 16px;
  color: #dddcdd;
  // background: #eee;
  margin-left: 12px;
  margin-top: 10px;
}

.ivu-timeline-item-head-custom {
  left: -24px;
}
.upgrade .contentTimed .on {
  display: flex;
  flex-wrap: nowrap;
}
.upgrade .contentTime .on:first-child .ivu-timeline-item-head-custom {
  color: var(--prev-color-primary);
  font-size: 18px;
}
:deep(.ivu-collapse > .ivu-collapse-item > .ivu-collapse-header) {
  line-height: 20px;
}
.upgrade .contentTimed .on .ivu-collapse > .ivu-collapse-item > .ivu-collapse-header {
  color: var(--prev-color-primary);
}

.on:first-child .ivu-collapse-header {
  color: var(--prev-color-primary) !important;
}

.upgrade .contentTime .off:first-child .ivu-timeline-item-head-custom {
  color: var(--prev-color-primary);
  font-size: 18px;
}

.off:first-child .ivu-collapse-header {
  color: var(--prev-color-primary) !important;
}

.off:first-child .time {
  color: var(--prev-color-primary) !important;
}

.header-modal {
  background: url('../../../assets/images/bg.png') no-repeat;
  background-size: 100% 100%;
  text-align: center;
  border-radius: 6px 6px 0 0;
}

.ivu-modal {
  border-radius: 12px;
  overflow: hidden;
}

.header-modal2 {
  background: url('../../../assets/images/bg2.png') no-repeat;
  background-size: 100% 100%;
  text-align: center;
  border-radius: 6px 6px 0 0;
}

.ivu-modal-header {
  padding: 0;
  border-radius: 10px;
  border: none;
}

.ivu-modal-header .header-modal2 {
  height: 74px;
  line-height: 74px;
  font-size: 20px;
  font-weight: 500;
}

.ivu-modal-header p,
.ivu-modal-header-inner {
  height: 96px;
  color: #fff;
  line-height: 96px;
  font-size: 24px;
}

.describe {
  text-align: center;
  padding-top: 30px;

  .success {
    font-size: 20px;
    color: var(--prev-color-text-regular);
    margin-top: 10px;
  }
}

.ivu-modal-footer {
  border: none;
  display: flex;
  justify-content: center;
}

.footer .ivu-btn {
  width: 128px;
  height: 40px;
  border: none;
}

.cancel {
  background: #eeeeee;
}

.vertical-center-modal {
  display: flex;
  align-items: center;
  justify-content: center;

  .ivu-modal {
    top: 0;
  }
}
:deep(.vertical-center-modal .ivu-modal-header) {
  padding: 0 !important;
  border: none;
}
:deep(.ivu-modal-content) {
  border-radius: 14px 14px 0 0;
  overflow: hidden;
}
.footer {
  display: flex;
  justify-content: space-around;
}

.demo-i-circle-custom {
  & h1 {
    color: #cccccc;
    font-size: 12px;
    font-weight: normal;
    line-height: 17px;
  }

  & p {
    color: #2a7efb;
    font-size: 14px;
    line-height: 20px;
    margin: 6px 0;
  }

  & span {
    display: block;
    color: #cccccc;
    font-size: 12px;
    line-height: 17px;
  }

  & span i {
    font-style: normal;
    color: #3f414d;
  }
}

.proportion {
  font-size: 20px;
  font-weight: 600;
  color: #2a7efb;
  margin: 12px 0 6px;
}

.wait {
  font-size: 12px;
  font-weight: 400;
  color: #999999;
}

.footer2 {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  flex-direction: column;

  .confirm,
  .back {
    width: 210px;
    height: 40px;
  }

  .back {
    border: none;
  }
}

.describe {
  padding-top: 0;
}

.describe h2 {
  font-size: 22px;
  font-weight: 400;
  color: var(--prev-color-text-regular);
  margin-bottom: 12px;
}

.acea-row.row-top {
  position: relative;
  overflow: hidden;
}

.update {
  margin: 27px 16px;
}
</style>
