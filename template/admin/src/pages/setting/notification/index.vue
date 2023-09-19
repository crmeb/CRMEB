<template>
  <div class="message">
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '0 20px 20px' }">
      <div>
        <el-tabs v-model="currentTab" @tab-click="changeTab">
          <el-tab-pane
            :label="item.label"
            :name="item.value.toString()"
            v-for="(item, index) in headerList"
            :key="index"
          />
        </el-tabs>
      </div>
      <el-row class="mb14" v-if="currentTab == 1">
        <el-col>
          <el-button v-auth="['app-wechat-template-sync']" type="primary" @click="routineTemplate"
            >同步小程序订阅消息</el-button
          >
          <el-button v-auth="['app-wechat-template-sync']" type="primary" @click="wechatTemplate"
            >同步微信模版消息</el-button
          >
        </el-col>
      </el-row>
      <el-alert v-if="currentTab == 1" closable>
        <template slot="title">
          公众号：登录微信公众号后台，基本设置，服务类目增加《生活服务> 百货/超市/便利店》<br />
          小程序：登录微信小程序后台，基本设置，服务类目增加《生活服务> 百货/超市/便利店》
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
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="通知类型" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="通知场景说明" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="站内信" min-width="130">
          <template slot-scope="scope">
            <el-switch
              v-if="scope.row.is_system !== 0"
              :active-value="1"
              :inactive-value="2"
              v-model="scope.row.is_system"
              :value="scope.row.is_system"
              @change="changeSwitch($event, scope.row, 'is_system')"
              size="large"
              :disabled="scope.row.is_system == 0"
            >
            </el-switch>
            <div v-else>-</div>
          </template>
        </el-table-column>
        <el-table-column label="公众号模板" min-width="130">
          <template slot-scope="scope">
            <el-switch
              v-if="scope.row.is_wechat !== 0"
              :active-value="1"
              :inactive-value="2"
              v-model="scope.row.is_wechat"
              :value="scope.row.is_wechat"
              @change="changeSwitch($event, scope.row, 'is_wechat')"
              size="large"
              :disabled="scope.row.is_wechat == 0"
            >
            </el-switch>
            <div v-else>-</div>
          </template>
        </el-table-column>

        <el-table-column label="发送短信" min-width="130">
          <template slot-scope="scope">
            <el-switch
              v-if="scope.row.is_sms !== 0"
              :active-value="1"
              :inactive-value="2"
              v-model="scope.row.is_sms"
              :value="scope.row.is_sms"
              @change="changeSwitch($event, scope.row, 'is_sms')"
              size="large"
            >
            </el-switch>
            <div v-else>-</div>
          </template>
        </el-table-column>
        <el-table-column label="企业微信" min-width="130" v-if="currentTab != 1">
          <template slot-scope="scope">
            <el-switch
              v-if="scope.row.is_ent_wechat !== 0"
              :active-value="1"
              :inactive-value="2"
              v-model="scope.row.is_ent_wechat"
              :value="scope.row.is_ent_wechat"
              @change="changeSwitch($event, scope.row, 'is_ent_wechat')"
              size="large"
            >
            </el-switch>
            <div v-else>-</div>
          </template>
        </el-table-column>
        <el-table-column label="小程序订阅" min-width="130" v-if="currentTab == 1">
          <template slot-scope="scope">
            <el-switch
              v-if="scope.row.is_routine !== 0"
              :active-value="1"
              :inactive-value="2"
              v-model="scope.row.is_routine"
              :value="scope.row.is_routine"
              @change="changeSwitch($event, scope.row, 'is_routine')"
              size="large"
              :disabled="scope.row.is_routine == 0"
            >
            </el-switch>
            <div v-else>-</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="70">
          <template slot-scope="scope">
            <a class="setting btn" @click="setting(item, scope.row)">设置</a>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script>
import { getNotificationList, getNotificationInfo, noticeStatus } from '@/api/notification.js';
import { routineSyncTemplate, wechatSyncTemplate } from '@/api/app';
export default {
  data() {
    return {
      modalTitle: '',
      notificationModal: false,
      headerList: [
        { label: '通知会员', value: '1' },
        { label: '通知平台', value: '2' },
      ],
      levelLists: [],
      currentTab: '1',
      loading: false,
      formData: {},
    };
  },
  created() {
    this.changeTab(this.currentTab);
  },
  methods: {
    changeSwitch(e, row, type) {
      noticeStatus(type, row[type], row.id)
        .then((res) => {
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    changeTab() {
      getNotificationList(this.currentTab).then((res) => {
        this.levelLists = res.data;
      });
    },
    // 同步订阅消息
    routineTemplate() {
      routineSyncTemplate()
        .then((res) => {
          this.$message.success(res.msg);
          this.changeTab(this.currentTab);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 同步微信模版消息
    wechatTemplate() {
      wechatSyncTemplate()
        .then((res) => {
          this.$message.success(res.msg);
          this.changeTab(this.currentTab);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 开启关闭
    changeStatus() {},
    // 列表
    notice() {},
    // 设置
    setting(item, row) {
      this.$router.push({
        path: this.$routeProStr + '/setting/notification/notificationEdit?id=' + row.id,
      });
    },
    getData(keys, row, item) {
      this.formData = {};
      getNotificationInfo(row.id, item).then((res) => {
        keys.map((i, v) => {
          this.formData[i] = res.data[i];
        });
        this.formData.type = item;
        this.notificationModal = true;
      });
    },
  },
};
</script>

<style lang="scss" scoped>
/deep/ .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
.message /deep/ .ivu-table-header thead tr th {
  padding: 8px 16px;
}
.message /deep/ .ivu-tabs-tab {
  border-radius: 0 !important;
}
.table-box {
  padding: 20px;
}
.is-table {
  display: flex;
  /* justify-content: space-around; */
  justify-content: center;
}
.btn {
  padding: 6px 0px;
  cursor: pointer;
  font-size: 10px;
  border-radius: 3px;
}
.is-switch-close {
  background-color: #504444;
}
.is-switch {
  background-color: #eb5252;
}
.notice-list {
  background-color: #308cf5;
  margin: 0 15px;
}
.table {
  padding: 0 18px;
}
</style>
