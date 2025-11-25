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
          <el-button v-auth="['app-wechat-template-sync']" type="primary" v-db-click @click="routineTemplate"
            >同步小程序订阅消息</el-button
          >
          <el-button v-auth="['app-wechat-template-sync']" type="primary" v-db-click @click="wechatTemplate"
            >同步微信模版消息</el-button
          >
        </el-col>
      </el-row>
      <el-row class="mb14" v-if="currentTab == 3">
        <el-col>
          <el-button type="primary" v-db-click @click="notificationForm(0)">添加通知</el-button>
        </el-col>
      </el-row>
      <el-alert v-if="currentTab == 1" type="warning" :closable="false">
        <template slot="title">
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
        <el-table-column label="小程序订阅" min-width="130" v-if="currentTab == 1 || currentTab == 3">
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
        <el-table-column label="操作" fixed="right" :width="currentTab == 3 ? 130 : 70">
          <template slot-scope="scope">
            <a class="setting btn" v-db-click @click="setting(scope.row)">设置</a>
            <template v-if="currentTab == 3">
              <el-divider direction="vertical"></el-divider>
              <a class="setting btn" v-db-click @click="notificationForm(scope.row.id)">编辑</a>
              <el-divider direction="vertical"></el-divider>
              <a class="setting btn" v-db-click @click="del(scope.row, '删除', scope.$index)">删除</a>
            </template>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script>
import { getNotificationList, getNotificationInfo, noticeStatus, notificationForm } from '@/api/notification.js';
import { routineSyncTemplate, wechatSyncTemplate } from '@/api/app';
export default {
  data() {
    return {
      modalTitle: '',
      notificationModal: false,
      headerList: [
        { label: '会员通知', value: '1' },
        { label: '平台通知', value: '2' },
        { label: '自定义通知', value: '3' },
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
    notificationForm(id) {
      this.$modalForm(notificationForm(id)).then(() => this.changeTab());
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
    setting(row) {
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
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/notification/del_not/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.levelLists.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
.message ::v-deep .ivu-table-header thead tr th {
  padding: 8px 16px;
}
.message ::v-deep .ivu-tabs-tab {
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
  font-size: 12px;
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
.alert_title {
  margin-bottom: 5px;
  font-weight: 700;
}
</style>
