<template>
  <div class="edit">
    <div class="i-layout-page-header">
      <router-link :to="{ path: '/admin/setting/notification/index' }"
        ><Button icon="ios-arrow-back" size="small" class="mr20"
          >返回</Button
        ></router-link
      >
      <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
    </div>
    <div class="tabs">
      <Row :gutter="32">
        <Col span="32" class="demo-tabs-style1" style="padding: 16px">
          <Tabs @on-click="changeTabs">
            <TabPane
              v-for="(item, index) in tabsList"
              :key="index"
              :name="item.slot"
              :label="item.title"
            >
              <Form
                class="form-sty"
                ref="formData"
                :model="formData"
                :rules="ruleValidate"
                :label-width="80"
              >
                <div v-if="item.slot === 'is_system' && !loading">
                  <FormItem label="通知标题">
                    <Input
                      v-model="formData.system_title"
                      placeholder="请输入通知标题"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="通知内容">
                    <div class="content">
                      <Input
                        v-model="formData.system_text"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      ></Input>
                      <div class="trip">
                        <div>
                          请输入模板消息详细内容对应的变量。关键字个数需与已添加的模板一致。
                          可以使用如下变量：
                        </div>
                        <div
                          v-for="(item, index) in formData.variable.split(',')"
                          :key="index"
                        >
                          {{ item }}
                        </div>
                      </div>
                    </div>
                  </FormItem>
                  <FormItem label="状态" prop="is_system">
                    <RadioGroup v-model="formData.is_system">
                      <Radio :label="1">开启</Radio>
                      <Radio :label="2">关闭</Radio>
                    </RadioGroup>
                  </FormItem>
                </div>
                <div v-if="item.slot === 'is_sms' && !loading">
                  <FormItem label="短信模版ID">
                    <Input
                      v-model="formData.sms_id"
                      placeholder="短信模版ID"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="通知内容">
                    <div class="content">
                      <Input
                        v-model="formData.content"
                        type="textarea"
                        disabled
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      ></Input>
                    </div>
                  </FormItem>
                  <FormItem label="状态" prop="is_sms">
                    <RadioGroup v-model="formData.is_sms">
                      <Radio :label="1">开启</Radio>
                      <Radio :label="2">关闭</Radio>
                    </RadioGroup>
                  </FormItem>
                </div>
                <div v-else-if="item.slot === 'is_wechat' && !loading">
                  <FormItem label="ID">
                    <Input
                      v-model="formData.templage_message_id"
                      disabled
                      placeholder="请输入通模板编号"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="模板编号">
                    <Input
                      v-model="formData.tempkey"
                      disabled
                      placeholder="请输入通模板编号"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="模板">
                    <Input
                      disabled
                      v-model="formData.content"
                      type="textarea"
                      :autosize="{ minRows: 5, maxRows: 8 }"
                      placeholder="请输入模板"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="模板ID">
                    <Input
                      v-model="formData.tempid"
                      placeholder="请输入模板ID"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="状态" prop="is_wechat">
                    <RadioGroup v-model="formData.is_wechat">
                      <Radio :label="1">开启</Radio>
                      <Radio :label="2">关闭</Radio>
                    </RadioGroup>
                  </FormItem>
                </div>
                <div v-else-if="item.slot === 'is_routine' && !loading">
                  <FormItem label="ID">
                    <Input
                      v-model="formData.templage_message_id"
                      disabled
                      placeholder="请输入通模板编号"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="模板编号">
                    <Input
                      v-model="formData.tempkey"
                      disabled
                      placeholder="请输入通模板编号"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="模板">
                    <Input
                      disabled
                      v-model="formData.content"
                      type="textarea"
                      :autosize="{ minRows: 5, maxRows: 8 }"
                      placeholder="请输入模板"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="模板ID">
                    <Input
                      v-model="formData.tempid"
                      placeholder="请输入模板ID"
                      style="width: 500px"
                    ></Input>
                  </FormItem>
                  <FormItem label="状态" prop="is_routine">
                    <RadioGroup v-model="formData.is_routine">
                      <Radio :label="1">开启</Radio>
                      <Radio :label="2">关闭</Radio>
                    </RadioGroup>
                  </FormItem>
                </div>
                
                <div v-else-if="item.slot === 'is_ent_wechat' && !loading">
                  <FormItem label="通知内容">
                    <div class="content">
                      <Input
                        v-model="formData.ent_wechat_text"
                        type="textarea"
                        :autosize="{ minRows: 5, maxRows: 8 }"
                        placeholder="请输入通知内容"
                        style="width: 500px"
                      ></Input>
                      <div class="trip">
                        <div>
                          请输入模板消息详细内容对应的变量。关键字个数需与已添加的模板一致。
                          可以使用如下变量：
                        </div>
                        <div
                          v-for="(item, index) in formData.variable.split(',')"
                          :key="index"
                        >
                          {{ item }}
                        </div>
                      </div>
                    </div>
                  </FormItem>
                  <FormItem label="机器人链接">
                    <div class="content">
                      <Input
                        v-model="formData.url"
                        placeholder="请输入机器人链接"
                        style="width: 500px"
                      ></Input>
                      <div class="trip">企业微信群机器人链接</div>
                    </div>
                  </FormItem>
                  <FormItem label="状态" prop="is_ent_wechat">
                    <RadioGroup v-model="formData.is_ent_wechat">
                      <Radio :label="1">开启</Radio>
                      <Radio :label="2">关闭</Radio>
                    </RadioGroup>
                  </FormItem>
                </div>
                <FormItem>
                  <Button type="primary" @click="handleSubmit('formData')"
                    >提交</Button
                  >
                </FormItem>
              </Form>
            </TabPane>
          </Tabs>
        </Col>
      </Row>
    </div>
  </div>
</template>

<script>
import {
  getNotificationInfo,
  getNotificationSave,
} from "@/api/notification.js";
export default {
  data() {
    return {
      tabs: [
        {
          title: "系统通知",
          slot: "is_system",
        },
        {
          title: "短信通知",
          slot: "is_sms",
        },
        {
          title: "微信模板消息",
          slot: "is_wechat",
        },
        {
          title: "微信小程序提醒",
          slot: "is_routine",
        },
        {
          title: "企业微信",
          slot: "is_ent_wechat",
        },
      ],
      tabsList: [],
      formData: {},
      id: 0,
      loading: true,
      ruleValidate: {
        name: [
          {
            required: true,
            message: "请输入通知场景",
            trigger: "blur",
          },
        ],
        title: [
          {
            required: true,
            message: "请输入通知场景",
            trigger: "blur",
          },
        ],
        content: [
          {
            required: true,
            message: "请输入通知内容",
            trigger: "blur",
          },
        ],
      },
    };
  },
  created() {
    this.id = this.$route.query.id;
    this.changeTabs("is_system");
  },
  methods: {
    changeTabs(name) {
      this.getData(this.id, name);
    },
    getData(id, name) {
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
          this.formData = res.data;
          this.formData.type = name;
          this.formData.id = id;
          this.loading = false;
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    handleSubmit(name) {
      getNotificationSave(this.formData)
        .then((res) => {
          this.$Message.success("设置成功");
        })
        .catch((err) => {
          this.$Message.error(err);
        });
    },
    handleReset(name) {
      this.$emit("close");
    },
  },
};
</script>

<style scoped>
.edit {
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
