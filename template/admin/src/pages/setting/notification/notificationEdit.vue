<template>
  <div class="edit">
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/setting/notification/index'"
    ></pages-header>
    <div class="tabs mt16">
      <el-row :gutter="32">
        <el-col :span="32" class="demo-tabs-style1" style="padding: 16px">
          <el-tabs v-model="tagName" @tab-click="changeTabs">
            <el-tab-pane v-for="(item, index) in tabsList" :key="index" :name="item.slot" :label="item.title">
              <el-form class="form-sty" ref="formData" :model="formData" :rules="ruleValidate" label-width="85px">
                <div v-if="item.slot === 'is_system' && !loading">
                  <el-form-item label="通知标题：">
                    <el-input
                      v-model="formData.system_title"
                      placeholder="请输入通知标题"
                      style="width: 500px"
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="通知内容：">
                    <div class="content">
                      <el-input
                        v-model="formData.system_text"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      ></el-input>
                      <div class="trip">
                        <div>请输入模板消息详细内容对应的变量。关键字个数需与已添加的模板一致。 可以使用如下变量：</div>
                        <div v-for="(i, index) in formData.variable.split(',')" :key="index">
                          {{ i }}
                        </div>
                      </div>
                    </div>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_system">
                    <el-radio-group v-model="formData.is_system">
                      <el-radio :label="1">开启</el-radio>
                      <el-radio :label="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <div v-if="item.slot === 'is_sms' && !loading">
                  <el-form-item label="短信模版ID：">
                    <el-input v-model="formData.sms_id" placeholder="短信模版ID" style="width: 500px"></el-input>
                  </el-form-item>
                  <el-form-item label="通知内容：">
                    <div class="content">
                      <el-input
                        v-model="formData.content"
                        type="textarea"
                        disabled
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      ></el-input>
                    </div>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_sms">
                    <el-radio-group v-model="formData.is_sms">
                      <el-radio :label="1">开启</el-radio>
                      <el-radio :label="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <div v-else-if="item.slot === 'is_wechat' && !loading">
                  <el-form-item label="模板编号：">
                    <el-input
                      v-model="formData.tempkey"
                      disabled
                      placeholder="请输入通模板编号"
                      style="width: 500px"
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="模板：">
                    <el-input
                      disabled
                      v-model="formData.content"
                      type="textarea"
                      :autosize="{ minRows: 5, maxRows: 8 }"
                      placeholder="请输入模板"
                      style="width: 500px"
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="模板ID：">
                    <el-input v-model="formData.tempid" placeholder="请输入模板ID" style="width: 500px"></el-input>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_wechat">
                    <el-radio-group v-model="formData.is_wechat">
                      <el-radio :label="1">开启</el-radio>
                      <el-radio :label="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <div v-else-if="item.slot === 'is_routine' && !loading">
                  <el-form-item label="模板编号：">
                    <el-input
                      v-model="formData.tempkey"
                      disabled
                      placeholder="请输入通模板编号"
                      style="width: 500px"
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="模板：">
                    <el-input
                      disabled
                      v-model="formData.content"
                      type="textarea"
                      :autosize="{ minRows: 5, maxRows: 8 }"
                      placeholder="请输入模板"
                      style="width: 500px"
                    ></el-input>
                  </el-form-item>
                  <el-form-item label="模板ID：">
                    <el-input v-model="formData.tempid" placeholder="请输入模板ID" style="width: 500px"></el-input>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_routine">
                    <el-radio-group v-model="formData.is_routine">
                      <el-radio :label="1">开启</el-radio>
                      <el-radio :label="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>

                <div v-else-if="item.slot === 'is_ent_wechat' && !loading">
                  <el-form-item label="通知内容：">
                    <div class="content">
                      <el-input
                        v-model="formData.ent_wechat_text"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      ></el-input>
                      <div class="trip">
                        <div>请输入模板消息详细内容对应的变量。关键字个数需与已添加的模板一致。 可以使用如下变量：</div>
                        <div v-for="(i, index) in formData.variable.split(',')" :key="index">
                          {{ i }}
                        </div>
                      </div>
                    </div>
                  </el-form-item>
                  <el-form-item label="机器人链接：">
                    <div class="content">
                      <el-input v-model="formData.url" placeholder="请输入机器人链接" style="width: 500px"></el-input>
                      <div class="trip">企业微信群机器人链接</div>
                    </div>
                  </el-form-item>
                  <el-form-item label="状态：" prop="is_ent_wechat">
                    <el-radio-group v-model="formData.is_ent_wechat">
                      <el-radio :label="1">开启</el-radio>
                      <el-radio :label="2">关闭</el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <el-form-item>
                  <el-button type="primary" @click="handleSubmit('formData')">提交</el-button>
                </el-form-item>
              </el-form>
            </el-tab-pane>
          </el-tabs>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script>
import { getNotificationInfo, getNotificationSave } from '@/api/notification.js';
export default {
  data() {
    return {
      tabs: [
        {
          title: '系统通知',
          slot: 'is_system',
        },
        {
          title: '短信通知',
          slot: 'is_sms',
        },
        {
          title: '微信模板消息',
          slot: 'is_wechat',
        },
        {
          title: '微信小程序提醒',
          slot: 'is_routine',
        },
        {
          title: '企业微信',
          slot: 'is_ent_wechat',
        },
      ],
      tabsList: [],
      formData: {},
      id: 0,
      loading: true,
      tagName: 'is_system',
      ruleValidate: {
        name: [
          {
            required: true,
            message: '请输入通知场景',
            trigger: 'blur',
          },
        ],
        title: [
          {
            required: true,
            message: '请输入通知场景',
            trigger: 'blur',
          },
        ],
        content: [
          {
            required: true,
            message: '请输入通知内容',
            trigger: 'blur',
          },
        ],
      },
    };
  },
  created() {
    this.id = this.$route.query.id;
    this.getData(this.id, this.tagName, 1);
  },
  methods: {
    changeTabs() {
      this.getData(this.id, this.tagName);
    },
    getData(id, name, init) {
      this.loading = true;
      this.formData = {};
      getNotificationInfo(id, name)
        .then((res) => {
          if (!this.tabsList.length) {
            this.tabs.map((v) => {
              if (res.data[v.slot]) {
                this.tabsList.push(v);
              }
            });
          }
          if (init) this.tagName = this.tabsList[0].slot;
          this.formData = res.data;
          this.formData.type = name;
          this.formData.id = id;
          this.loading = false;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    handleSubmit(name) {
      getNotificationSave(this.formData)
        .then((res) => {
          this.$message.success('设置成功');
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    handleReset(name) {
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
.edit {
}
.header_top {
  margin-bottom: 10px;
}
.demo-tabs-style1 > .ivu-tabs-card > .ivu-tabs-content {
  height: 120px;
  margin-top: -16px;
}

.demo-tabs-style1 > .ivu-tabs-card > .ivu-tabs-content > .ivu-tabs-tabpane {
  background: #fff;
  padding: 16px;
}

.demo-tabs-style1 > .ivu-tabs.ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-tab {
  border-color: transparent;
}

.demo-tabs-style1 > .ivu-tabs-card > .ivu-tabs-bar .ivu-tabs-tab-active {
  border-color: #fff;
}

.tabs {
  padding: 0 30px;
  background-color: #fff;
}

.trip {
  color: rgb(146, 139, 139);
  background-color: #f2f2f2;
  margin-left: 80px;
  border-radius: 4px;
  padding: 15px;
}

.content {
  display: flex;
}

.form-sty {
  margin-top: 20px;
}
</style>
