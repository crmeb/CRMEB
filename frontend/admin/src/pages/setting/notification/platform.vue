<template>
  <div class="message">
    <pages-header
      class="mb16"
      :title="$route.meta.title"
      description="按场景配置平台端消息通知的渠道开关与模板，管理员接收通知从这里开启。"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '0 20px 20px' }">
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
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { getNotificationList, noticeStatus, noticeChannelOptions } from '@/api/notification.js';
import Setting from '@/setting';

defineOptions({ name: 'NotificationPlatform' });

const router = useRouter();

const levelLists = ref([]);
const loading = ref(false);
const channelList = ref([]);

// 获取渠道选项列表（平台端支持范围）
function getChannelOptions() {
  noticeChannelOptions({ type: 2 }).then((res) => {
    channelList.value = res.data;
  });
}
// 获取平台通知列表（type=2）
function getList() {
  loading.value = true;
  getNotificationList(2)
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
// 跳转编辑页（vue-router 4 中 path 内拼接的 query 会被丢弃，必须用 query 对象传递参数）
function setting(row) {
  router.push({
    path: Setting.routePre + '/setting/notification/notificationEdit',
    query: { id: row.id, from: 'platform' },
  });
}

getChannelOptions();
getList();
</script>

<style lang="scss" scoped>
.message :deep(.ivu-table-header thead tr th) {
  padding: 8px 16px;
}
</style>
