<template>
  <div class="article-manager">
    <Card :bordered="false" dis-hover class="ivu-mt fromBox">
      <Tabs v-model="currentTab" @on-click="changeTab" v-if="headerList.length">
        <TabPane
          :icon="item.icon"
          :label="item.label"
          :name="item.value.toString()"
          v-for="(item, index) in headerList"
          :key="index"
        />
      </Tabs>
      <Tabs type="card" v-model="childrenId" v-if="headerChildrenList.length" @on-click="changeChildrenTab">
        <TabPane
          :label="item.label"
          :name="item.id.toString()"
          v-for="(item, index) in headerChildrenList"
          :key="index"
        ></TabPane>
      </Tabs>
      <form-create :option="option" :rule="rules" @submit="onSubmit" v-if="rules.length !== 0"></form-create>
      <Spin size="large" fix v-if="spinShow"></Spin>
    </Card>
  </div>
</template>

<script>
import formCreate from '@form-create/iview';
import { headerListApi, dataFromApi } from '@/api/setting';
import request from '@/libs/request';
import { getLogo } from '@/api/common';
export default {
  name: 'setting_setSystem',
  components: { formCreate: formCreate.$form() },
  data() {
    return {
      rules: [],
      option: {
        form: {
          labelWidth: 185,
        },
        submitBtn: {
          col: {
            span: 3,
            push: 3,
          },
        },
        global: {
          upload: {
            props: {
              onSuccess(res, file) {
                if (res.status === 200) {
                  file.url = res.data.src;
                } else {
                  this.$Message.error(res.msg);
                }
              },
            },
          },
          frame: {
            props: {
              closeBtn: false,
              okBtn: false,
            },
          },
        },
      },
      spinShow: false,
      FromData: null,
      currentTab: '',
      headerList: [],
      headerChildrenList: [],
      childrenId: '',
      title: '',
    };
  },
  created() {
    this.getAllData();
  },
  watch: {
    $route(to, from) {
      this.headerChildrenList = [];
      this.getAllData();
    },
    childrenId() {
      this.getFrom();
    },
  },
  methods: {
    childrenList(index) {
      let that = this;
      that.headerList.forEach(function (item) {
        if (item.value.toString() === that.currentTab) {
          if (item.children === undefined) {
            that.childrenId = item.id;
            that.headerChildrenList = [];
          } else {
            that.headerChildrenList = item.children;
            that.childrenId = item.children.length ? item.children[index ? index : 0].id.toString() : '';
          }
        }
      });
    },
    // 头部tab
    getHeader(index) {
      this.spinShow = true;
      return new Promise((resolve, reject) => {
        let tab_id = this.$route.params.tab_id;
        let data = {
          type: this.$route.params.type ? this.$route.params.type : 0,
          pid: tab_id ? tab_id : 0,
        };
        headerListApi(data)
          .then(async (res) => {
            let config = res.data.config_tab;
            this.headerList = config;
            this.currentTab = config[index ? index : 0].value.toString();
            this.childrenList(index ? 1 : 0);
            resolve(this.currentTab);
            this.spinShow = false;
          })
          .catch((err) => {
            this.spinShow = false;
            this.$Message.error(err);
          });
      });
    },
    // 表单
    getFrom() {
      this.spinShow = true;
      return new Promise((resolve, reject) => {
        let ids = '';
        if (this.$route.params.type === '3') {
          ids = this.$route.params.tab_id;
        } else {
          if (this.childrenId) {
            ids = this.childrenId;
          } else {
            ids = this.currentTab;
          }
        }
        let data = {
          tab_id: Number(ids),
        };
        let logistics = 'freight/config/edit_basics',
          agent = 'agent/config/edit_basics',
          integral = 'marketing/integral_config/edit_basics',
          sms = 'serve/sms_config/edit_basics',
          config = 'setting/config/edit_basics';
        let url =
          this.$route.name === 'setting_logistics'
            ? logistics
            : this.$route.name === 'setting_distributionSet'
            ? agent
            : this.$route.name === 'setting_message'
            ? sms
            : this.$route.name === 'setting_setSystem'
            ? config
            : integral;
        dataFromApi(data, url)
          .then(async (res) => {
            this.spinShow = false;
            if (res.data.status === false) {
              return this.$authLapse(res.data);
            }
            this.FromData = res.data;
            this.rules = res.data.rules;
            this.title = res.data.title;
          })
          .catch((res) => {
            this.spinShow = false;
            this.$Message.error(res.msg);
          });
      });
    },
    async getAllData() {
      if (this.$route.query.from === 'download') {
        await this.getHeader(2);
      } else if (this.$route.params.type !== '3') {
        this.childrenId = '';
        await this.getHeader();
      } else {
        this.headerList = [];
        this.getFrom();
      }
    },
    // 选择
    changeTab() {
      this.childrenList();
    },
    // 二级选择
    changeChildrenTab(name) {
      this.childrenId = name;
    },
    // 提交表单 group
    onSubmit(formData) {
      request({
        url: this.FromData.action,
        method: this.FromData.method,
        data: formData,
      })
        .then((res) => {
          this.getAdminTitle();
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    getAdminTitle() {
      getLogo().then((res) => {
        localStorage.setItem('ADMIN_TITLE', res.data.site_name);
      });
    },
  },
};
</script>

<style scoped lang="stylus">
.ivu-tabs {
  margin-bottom: 18px;
}

.fromBox {
  min-height: 600px;
}
</style>
