<template>
  <div v-loading="spinShow">
    <div class="i-layout-page-header header-title" v-if="!headerList.length">
      <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
    </div>
    <div class="article-manager">
      <el-card :bordered="false" shadow="never" class="ivu-mt fromBox" :body-style="{ padding: '0 20px 20px' }">
        <el-tabs v-model="currentTab" @tab-click="changeTab" v-if="headerList.length">
          <el-tab-pane
            :icon="item.icon"
            :label="item.label"
            :name="item.value.toString()"
            v-for="(item, index) in headerList"
            :key="index"
          />
        </el-tabs>
        <el-tabs v-model="childrenId" v-if="headerChildrenList.length">
          <el-tab-pane
            :label="item.label"
            :name="item.id.toString()"
            v-for="(item, index) in headerChildrenList"
            :key="index"
          ></el-tab-pane>
        </el-tabs>
        <form-create
          :option="option"
          :rule="rules"
          @submit="onSubmit"
          v-if="rules.length"
          style="padding-top: 20px"
        ></form-create>
      </el-card>
    </div>
  </div>
</template>

<script>
import formCreate from '@form-create/element-ui';
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
          labelWidth: '120px',
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
                  this.$message.error(res.msg);
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
        if (this.$route.query.tab_id) {
          this.currentTab = this.$route.query.tab_id;
        }
        let tab_id = this.$route.params.tab_id ? this.$route.params.tab_id : this.$route.query.tab_id;
        let data = {
          type: this.$route.params.type ? this.$route.params.type : 0,
          pid: tab_id ? tab_id : 0,
        };
        headerListApi(data)
          .then(async (res) => {
            let config = res.data.config_tab;
            this.headerList = config;
            if (!this.currentTab) {
            }
            if (this.$route.query.tab_id) {
              this.currentTab = this.$route.query.tab_id;
            } else {
              this.currentTab = config[index ? index : 0].value.toString();
            }
            this.childrenList(index ? 1 : 0);
            resolve(this.currentTab);
            this.spinShow = false;
          })
          .catch((err) => {
            this.spinShow = false;
            this.$message.error(err);
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
            // res.data.rules.forEach((e) => {
            //   e.title += ':';
            //   if (e.control) {
            //   }
            // });
            this.addColon(res.data.rules);
            this.rules = res.data.rules;
            this.title = res.data.title;
          })
          .catch((res) => {
            this.spinShow = false;
            this.$message.error(res.msg);
          });
      });
    },
    addColon(arr) {
      for (let i = 0; i < arr.length; i++) {
        const c = arr[i];
        c.title += ':';
        if (c.control) {
          for (let j = 0; j < c.control.length; j++) {
            const e = c.control[j];
            if (e.rule.length) {
              this.addColon(e.rule);
            }
          }
        }
      }
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
    // 提交表单 group
    onSubmit(formData) {
      request({
        url: this.FromData.action,
        method: this.FromData.method,
        data: formData,
      })
        .then((res) => {
          this.$message.success(res.msg);
          if (formData.site_name) {
            localStorage.setItem('ADMIN_TITLE', formData.site_name);
            this.$store.commit('setAdminTitle', formData.site_name);
            window.document.title = `${formData.site_name} - 系统设置`;
          }
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .el-tabs__header {
  margin: unset;
}
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
::v-deep .el-input-number {
  width: 414px;
}
::v-deep .el-input {
  width: 414px;
}
::v-deep .el-input-number .el-input__inner {
  text-align: unset;
}
.ivu-tabs {
  margin-bottom: 18px;
}

.fromBox {
  min-height: calc(100vh - 200px);
  margin-top: 0px !important;
}

.article-manager ::v-deep .ivu-form-item {
  margin-bottom: 20px !important;
}
// ::v-deep .form-create .el-button{
//   float: right;
// }
body ::v-deep .el-dialog .el-dialog__header {
  border: none !important;
}
::v-deep .el-form-item--small .el-form-item__label {
  line-height: 14px;
  // margin-top: 10px;
}
</style>
