<template>
  <div class="message">
    <Card :bordered="false" dis-hover class="ivu-mt">
      <div class="mb20">
        <Tabs v-model="currentTab" @on-click="changeTab">
          <TabPane :label="item.label" :name="item.value.toString()" v-for="(item, index) in headerList" :key="index" />
        </Tabs>
      </div>
      <Row type="flex" class="mb20" v-if="currentTab == 1">
        <Col v-bind="grid">
          <Button
            v-auth="['app-wechat-template-sync']"
            icon="md-list"
            type="success"
            @click="routineTemplate"
            style="margin-left: 20px"
            >同步小程序订阅消息</Button
          >
          <Button
            v-auth="['app-wechat-template-sync']"
            icon="md-list"
            type="success"
            @click="wechatTemplate"
            style="margin-left: 20px"
            >同步微信模版消息</Button
          >
        </Col>
      </Row>
      <Alert v-if="currentTab == 1">
        <template slot="desc">
          登录微信公众号后台，选择模版消息，将模版消息的所在行业修改副行业为《其他/其他》<br />登录微信小程序后台，基本设置，服务类目增加《生活服务
          > 百货/超市/便利店》
        </template>
      </Alert>
      <Table
        :columns="currentTab == 1 ? columns : columns2"
        :data="levelLists"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="name">
          <span class="table">
            {{ row.name }}
          </span>
        </template>
        <template slot-scope="{ row, index }" slot="title">
          <span class="table">{{ row.title }}</span>
        </template>
        <template
          slot-scope="{ row }"
          v-for="item in ['is_system', 'is_wechat', 'is_routine', 'is_sms', 'is_ent_wechat']"
          :slot="item"
        >
          <div v-if="item === 'is_ent_wechat' && currentTab == 1" :key="index">--</div>
          <i-switch
            v-model="row[item]"
            :value="row[item]"
            :true-value="1"
            :false-value="2"
            @on-change="changeSwitch(row, item)"
            size="large"
            v-if="row[item] > 0 && currentTab !== 1"
          >
            <span slot="open">开启</span>
            <span slot="close">关闭</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="setting">
          <span class="setting btn" @click="setting(item, row)">设置</span>
        </template>
      </Table>
    </Card>
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
      columns: [
        {
          title: 'ID',
          key: 'id',
          align: 'center',
          width: 60,
        },
        {
          title: '通知类型',
          slot: 'name',
          align: 'center',
          width: 200,
        },
        {
          title: '通知场景说明',
          slot: 'title',
          align: 'center',
          minWidth: 200,
        },
        {
          title: '站内信',
          slot: 'is_system',
          align: 'center',
          minWidth: 100,
        },
        {
          title: '公众号模板',
          slot: 'is_wechat',
          align: 'center',
          minWidth: 100,
        },
        {
          title: '小程序订阅',
          slot: 'is_routine',
          align: 'center',
          minWidth: 100,
        },
        {
          title: '发送短信',
          slot: 'is_sms',
          align: 'center',
          minWidth: 100,
        },
        {
          title: '设置',
          slot: 'setting',
          width: 150,
          align: 'center',
        },
      ],
      columns2: [
        {
          title: 'ID',
          key: 'id',
          align: 'center',
          width: 60,
        },
        {
          title: '通知类型',
          slot: 'name',
          align: 'center',
          width: 200,
        },
        {
          title: '通知场景说明',
          slot: 'title',
          align: 'center',
          minWidth: 200,
        },
        {
          title: '站内信',
          slot: 'is_system',
          align: 'center',
          minWidth: 100,
        },
        {
          title: '公众号模板',
          slot: 'is_wechat',
          align: 'center',
          minWidth: 100,
        },
        {
          title: '发送短信',
          slot: 'is_sms',
          align: 'center',
          minWidth: 100,
        },
        {
          title: '企业微信',
          slot: 'is_ent_wechat',
          align: 'center',
          minWidth: 100,
        },
        {
          title: '设置',
          slot: 'setting',
          width: 150,
          align: 'center',
        },
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
    changeSwitch(row, item) {
      noticeStatus(item, row[item], row.id)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    changeTab(data) {
      getNotificationList(data).then((res) => {
        this.levelLists = res.data;
      });
    },
    // 同步订阅消息
    routineTemplate() {
      routineSyncTemplate()
        .then((res) => {
          this.$Message.success(res.msg);
          this.changeTab(this.currentTab);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 同步微信模版消息
    wechatTemplate() {
      wechatSyncTemplate()
        .then((res) => {
          this.$Message.success(res.msg);
          this.changeTab(this.currentTab);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 开启关闭
    changeStatus() {},
    // 列表
    notice() {},
    // 设置
    setting(item, row) {
      this.$router.push({
        path: '/admin/setting/notification/notificationEdit?id=' + row.id,
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

<style scoped>
.message /deep/ .ivu-table-header table {
  /* border-top: 1px solid #e8eaec !important;
  border-left: 1px solid #e8eaec !important; */
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
  padding: 6px 12px;
  cursor: pointer;
  color: #2d8cf0;
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
