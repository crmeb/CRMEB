<template>
  <div class="message">
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="按场景配置会员端消息通知的渠道开关与模板，支持一键同步微信订阅消息/模板消息。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '20px 20px 20px' }">
      <el-row class="action-row mb14">
        <el-col>
          <el-button
            v-if="isRoutineAvailable"
            v-auth="['app-wechat-template-sync']"
            type="primary"
            v-db-click
            @click="routineTemplate"
            >同步小程序订阅消息</el-button
          >
          <el-button
            v-if="isWechatAvailable"
            v-auth="['app-wechat-template-sync']"
            type="primary"
            v-db-click
            @click="wechatTemplate"
            >同步微信模版消息</el-button
          >
        </el-col>
      </el-row>
      <el-alert type="warning" :closable="false">
        <template #title>
          <p class="alert_title">小程序订阅消息</p>
          登录微信小程序后台，基本设置，服务类目增加《生活服务 > 百货/超市/便利店》 (否则同步小程序订阅消息会报错)<br />
          同步小程序订阅消息，是在小程序后台未添加订阅消息模板的前提下使用的，会新增一个模板消息并把信息同步过来，并新本项目数据库。<br />
          <br />
          <p class="alert_title">微信模板消息</p>
          登录微信公众号后台，选择模板消息，在账号详情下的服务类目中手动设置服务类目，《生活服务 >
          百货/超市/便利店》(否则同步模板消息不成功)<br />
          同步公众号模板消息，同步公众号模板会删除公众号后台现有的模板，并重新添加新的模板，然后同步信息到数据库，如果多个项目使用同一个公众号的模板，请谨慎操作。
        </template>
      </el-alert>
      <el-table
        :data="levelLists"
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
        <el-table-column label="通知类型" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="通知场景说明" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column
          v-for="channel in channelList"
          :key="channel.channel_key"
          :label="channel.channel_name"
          min-width="130"
        >
          <template #default="scope">
            <el-switch
              v-if="getChannelStatus(scope.row, channel.channel_key) !== undefined"
              :active-value="1"
              :inactive-value="2"
              :model-value="getChannelStatus(scope.row, channel.channel_key)"
              :disabled="
                scope.row.mark === 'verify_code' &&
                channel.channel_key === 'is_sms' &&
                getChannelStatus(scope.row, channel.channel_key) === 1
              "
              @change="changeSwitch($event, scope.row, channel.channel_key)"
              size="large"
            >
            </el-switch>
            <div v-else>--</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" :width="70">
          <template #default="scope">
            <a v-db-click @click="setting(scope.row)">设置</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <el-drawer
      v-model="settingVisible"
      title="通知设置"
      size="800px"
      @closed="closeSetting"
      class="notification-edit-drawer"
    >
      <notification-edit
        v-if="settingVisible && selectedNotificationId"
        drawer-mode
        :notification-id="selectedNotificationId"
        from="member"
        @close="closeSetting"
        @saved="handleSettingSaved"
      />
    </el-drawer>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { getNotificationList, noticeStatus, noticeChannelOptions } from '@/api/notification.js';
import { routineSyncTemplate, wechatSyncTemplate } from '@/api/app';
import NotificationEdit from './notificationEdit.vue';

defineOptions({ name: 'NotificationMember' });

const levelLists = ref([]);
const loading = ref(false);
const channelList = ref([]);
const settingVisible = ref(false);
const selectedNotificationId = ref(0);
const isRoutineAvailable = computed(() => channelList.value.some((channel) => channel.channel_key === 'is_routine'));
const isWechatAvailable = computed(() => channelList.value.some((channel) => channel.channel_key === 'is_wechat'));

// 获取渠道选项列表（用户端支持范围）
function getChannelOptions() {
  noticeChannelOptions({ type: 1 }).then((res) => {
    channelList.value = res.data;
  });
}
// 获取会员通知列表（type=1）
function getList() {
  loading.value = true;
  getNotificationList(1)
    .then((res) => {
      levelLists.value = res.data;
    })
    .finally(() => {
      loading.value = false;
    });
}
// 渠道开关状态从 channels 嵌套结构读取
function getChannelStatus(row, channelKey) {
  if (row.channels && row.channels[channelKey] !== undefined) {
    return row.channels[channelKey].status;
  }
  return undefined;
}
// 切换渠道开关
function changeSwitch(e, row, type) {
  noticeStatus(type, e, row.id)
    .then((res) => {
      if (row.channels && row.channels[type] !== undefined) {
        row.channels[type]['status'] = e;
      }
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
// 同步小程序订阅消息
function routineTemplate() {
  routineSyncTemplate()
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 同步微信公众号模板消息
function wechatTemplate() {
  wechatSyncTemplate()
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
function setting(row) {
  selectedNotificationId.value = row.id;
  settingVisible.value = true;
}
function closeSetting() {
  settingVisible.value = false;
  selectedNotificationId.value = 0;
}
function handleSettingSaved() {
  getList();
  closeSetting();
}

getChannelOptions();
getList();
</script>

<style lang="scss" scoped>
.message :deep(.ivu-table-header thead tr th) {
  padding: 8px 16px;
}
.table-box {
  padding: 20px;
}
:deep(.el-modal-drawer) .notification-edit-drawer .el-drawer__body {
  padding: 10px 20px;

  .tabs {
    padding: 0;
  }
}
</style>
