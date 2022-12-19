<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <!-- <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="状态：" label-for="status1">
              <Select v-model="status" placeholder="请选择" @on-change="userSearchs" clearable>
                <Option value="all">全部</Option>
                <Option value="1">开启</Option>
                <Option value="0">关闭</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="搜索：" label-for="status2">
              <Input
                search
                enter-button
                placeholder="请输入账号"
                v-model="formValidate.name"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row> -->
        <Row type="flex">
          <Col v-bind="grid">
            <Button v-auth="['setting-system_admin-add']" type="primary" @click="add" icon="md-add">添加账号</Button>
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="list"
        class="mt25"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        :loading="loading"
        highlight-row
      >
        <template slot-scope="{ row }" slot="roles">
          <div v-if="row.roles.length !== 0">
            <Tag color="blue" v-for="(item, index) in row.roles.split(',')" :key="index" v-text="item"></Tag>
          </div>
        </template>
        <template slot-scope="{ row }" slot="status">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :false-value="0"
            @on-change="onchangeIsShow(row)"
            size="large"
          >
            <span slot="open">开启</span>
            <span slot="close">关闭</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="setUp(row)">设置</a>
          <Divider type="vertical" />
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除账号', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="formValidate.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="formValidate.limit"
        />
      </div>
    </Card>
    <Modal
      v-model="modals"
      scrollable
      :title="type == 0 ? '添加账号' : '编辑账号'"
      :mask-closable="false"
      width="700"
      :closable="false"
    >
      <Form
        ref="modalsdate"
        :model="modalsdate"
        :rules="type == 0 ? ruleValidate : editValidate"
        :label-width="70"
        label-position="right"
      >
        <FormItem label="账号" prop="appid">
          <div style="display: flex">
            <Input type="text" v-model="modalsdate.appid" :disabled="type != 0"></Input>
          </div>
        </FormItem>
        <FormItem label="密码" prop="appsecret">
          <div style="display: flex">
            <Input type="text" v-model="modalsdate.appsecret" class="input"></Input>
            <Button type="primary" @click="reset" class="reset">重置</Button>
          </div>
        </FormItem>
        <FormItem label="描述" prop="title">
          <div style="display: flex">
            <Input type="textarea" v-model="modalsdate.title"></Input>
          </div>
        </FormItem>
        <FormItem label="接口权限" prop="title">
          <!-- <CheckboxGroup v-model="modalsdate.rules">
            <Checkbox
              :disabled="[2, 3].includes(item.id)"
              style="width: 30%"
              v-for="item in intList"
              :key="item.id"
              :label="item.id"
              >{{ item.name }}</Checkbox
            >
          </CheckboxGroup> -->
          <Tree :data="intList" multiple show-checkbox ref="tree" @on-check-change="selectTree"></Tree>
        </FormItem>
      </Form>
      <div slot="footer">
        <Button type="primary" @click="ok('modalsdate')">确定</Button>
        <Button @click="cancel">取消</Button>
      </div>
    </Modal>
    <Modal v-model="settingModals" scrollable title="设置推送" :mask-closable="false" width="900" :closable="false">
      <Form
        class="setting-style"
        ref="settingData"
        :model="settingData"
        :rules="type == 0 ? ruleValidate : editValidate"
        :label-width="140"
        label-position="right"
      >
        <FormItem label="推送开关" prop="switch">
          <Switch v-model="settingData.push_open" :true-value="1" :false-value="0" />
        </FormItem>
        <FormItem label="推送账号" prop="push_account">
          <div class="form-content">
            <Input type="text" v-model="settingData.push_account" placeholder="请输入推送账号"></Input>
            <span class="trip">接受推送方获取token的账号</span>
          </div>
        </FormItem>
        <FormItem label="推送密码" prop="push_password">
          <div class="form-content">
            <Input type="text" v-model="settingData.push_password" placeholder="请输入推送密码"></Input>
            <span class="trip">接受推送方获取token的密码</span>
          </div>
        </FormItem>
        <FormItem label="获取TOKEN接口" prop="push_token_url">
          <div class="form-content">
            <div class="input-button">
              <Input type="text" v-model="settingData.push_token_url" placeholder="请输入获取TOKEN接口"></Input>
              <Button class="ml10" type="primary" @click="textOutUrl(settingData.id)">测试链接</Button>
            </div>
            <span class="trip"
              >接受推送方获取token的URL地址，POST方法，传入push_account和push_password，返回token和有效时间time(秒)</span
            >
          </div>
        </FormItem>
        <FormItem label="用户数据修改推送接口" prop="user_update_push">
          <div class="form-content">
            <Input type="text" v-model="settingData.user_update_push" placeholder="请输入用户数据修改推送接口"></Input>
            <span class="trip">用户修改积分，余额，经验等将用户信息推送至该地址，POST方法</span>
          </div>
        </FormItem>
        <FormItem label="订单创建推送接口" prop="order_create_push">
          <div class="form-content">
            <Input type="text" v-model="settingData.order_create_push" placeholder="请输入订单创建推送接口"></Input>
            <span class="trip">订单创建时推送订单信息至该地址，POST方法</span>
          </div>
        </FormItem>
        <FormItem label="订单支付推送接口" prop="order_pay_push">
          <div class="form-content">
            <Input type="text" v-model="settingData.order_pay_push" placeholder="请输入订单支付推送接口"></Input>
            <span class="trip">订单完成支付时推送订单已支付信息至该地址，POST方法</span>
          </div>
        </FormItem>
        <FormItem label="售后订单创建推送接口" prop="refund_create_push">
          <div class="form-content">
            <Input
              type="text"
              v-model="settingData.refund_create_push"
              placeholder="请输入售后订单创建推送接口"
            ></Input>
            <span class="trip">售后订单生成时推送售后单信息至该地址，POST方法</span>
          </div>
        </FormItem>
        <FormItem label="售后订单取消推送接口" prop="refund_cancel_push">
          <div class="form-content">
            <Input
              type="text"
              v-model="settingData.refund_cancel_push"
              placeholder="请输入售后订单取消推送接口"
            ></Input>
            <span class="trip">售后订单取消时推送售后单取消信息至该地址，POST方法</span>
          </div>
        </FormItem>
      </Form>
      <div slot="footer">
        <Button type="primary" @click="submit('settingData')">确定</Button>
        <Button @click="settingModals = false">取消</Button>
      </div>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import {
  accountListApi,
  outSaveApi,
  outSavesApi,
  setShowApi,
  outSetUp,
  interfaceList,
  setUpPush,
  textOutUrl,
} from '@/api/systemOutAccount';
export default {
  name: 'systemOut',
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      total: 0,
      loading: false,
      roleData: {
        status1: '',
      },
      formValidate: {
        roles: '',
        status: '',
        name: '',
        page: 1, // 当前页
        limit: 20, // 每页显示条数
      },
      status: '',
      list: [],
      intList: [],
      columns: [
        {
          type: 'selection',
          width: 60,
          align: 'center',
        },
        {
          title: '接口名称',
          key: 'name',
        },
      ],
      columns1: [
        {
          title: '编号',
          key: 'id',
          minWidth: 80,
        },
        {
          title: '账号',
          key: 'appid',
          minWidth: 150,
        },
        {
          title: '描述',
          key: 'title',
          minWidth: 250,
        },
        {
          title: '添加时间',
          key: 'add_time',
          minWidth: 180,
        },
        {
          title: '最后登录时间',
          key: 'last_time',
          minWidth: 180,
        },
        {
          title: '最后登录ip',
          key: 'ip',
          minWidth: 180,
        },
        {
          title: '状态',
          slot: 'status',
          minWidth: 90,
        },
        {
          title: '操作',
          key: 'action',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      FromData: null,
      modalTitleSs: '',
      ids: Number,
      modals: false,
      modalsid: '',
      type: 0,
      modalsdate: {
        appid: '',
        appsecret: '',
        title: '',
        rules: [],
      },
      settingModals: false,
      settingData: {
        switch: 1,
        name: '',
      },
      ruleValidate: {
        appid: [{ required: true, message: '请输入正确的账号 (4到30位之间)', trigger: 'blur', min: 4, max: 30 }],
        appsecret: [{ required: true, message: '请输入正确的密码 (6到32位之间)', trigger: 'blur', min: 6, max: 32 }],
        title: [{ message: '请输入正确的描述 (不能多于200位数)', trigger: 'blur', max: 200 }],
      },
      editValidate: {
        appsecret: [{ required: false, message: '请输入正确的密码 (6到32位之间)', trigger: 'blur', min: 6, max: 32 }],
      },
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 50;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 开启状态
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      setShowApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 请求列表
    submitFail() {
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.roles = this.formValidate.roles || '';
      accountListApi(this.formValidate)
        .then(async (res) => {
          this.total = res.data.count;
          this.list = res.data.list;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    // 添加
    add() {
      this.modals = true;
      this.type = 0;
      this.modalsdate = {
        appid: '',
        appsecret: '',
        title: '',
        rules: [],
      };
      this.getIntList();
    },
    selectTree(e, i) {
      console.log(e, i);
    },
    getIntList(type, list) {
      interfaceList().then((res) => {
        this.intList = res.data;
        if (!type) {
          this.intList.map((item) => {
            if (item.id === 1) {
              item.checked = true;
              item.disableCheckbox = true;
              if (item.children.length) {
                item.children.map((v) => {
                  v.checked = true;
                  v.disableCheckbox = true;
                });
              }
            }
          });
        } else {
          list.map((item) => {
            this.intList.map((e) => {
              if (e.id === 1) {
                e.checked = true;
                e.disableCheckbox = true;
                if (e.children.length) {
                  e.children.map((v) => {
                    v.checked = true;
                    v.disableCheckbox = true;
                  });
                }
              }
              listData(e.children || [], item);
            });
          });
        }
        function listData(list, id) {
          if (list.length) {
            list.map((v) => {
              if (v.id == id) {
                v.checked = true;
              }
              if (v.children) {
                listData(v.children);
              }
            });
          }
        }
      });
    },
    // 编辑
    edit(row) {
      this.modals = true;
      this.modalsdate.appid = row.appid;
      this.modalsdate.title = row.title;
      this.modalsdate.rules = row.rules.map((e) => {
        return Number(e);
      });
      this.modalsid = row.id;
      this.type = 1;
      this.getIntList('edit', this.modalsdate.rules);
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/system_out_account/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.list.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 编辑
    setUp(row) {
      this.settingModals = true;
      this.settingData = row;
    },
    // 搜索
    userSearchs() {
      this.formValidate.status = this.status === 'all' ? '' : this.status;
      this.formValidate.page = 1;
      this.list = [];
      this.getList();
    },
    submit(name) {
      setUpPush(this.settingData).then((res) => {
        this.$Message.success(res.msg);
        this.settingModals = false;
        this.getList();
      });
    },
    textOutUrl() {
      textOutUrl(this.settingData)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    ok(name) {
      console.log(this.$refs.tree.getCheckedAndIndeterminateNodes());
      let fuc = this.modalsid ? outSavesApi : outSaveApi;
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.modalsdate.rules = [];
          this.$refs.tree.getCheckedAndIndeterminateNodes().map((node) => {
            this.modalsdate.rules.push(node.id);
          });
          if (this.modalsid) this.modalsdate.id = this.modalsid;
          fuc(this.modalsdate)
            .then((res) => {
              this.modalsdate = {
                appid: '',
                appsecret: '',
                title: '',
                rules: [],
              };
              (this.modals = false), this.$Message.success(res.msg);
              this.modalsid = '';
              this.getList();
            })
            .catch((err) => {
              this.$Message.error(err.msg);
            });
        } else {
          this.$Message.warning('请完善数据');
        }
      });
    },
    cancel() {
      this.modalsid = '';
      this.modalsdate = {
        appid: '',
        appsecret: '',
        title: '',
      };
      this.modals = false;
    },
    reset() {
      let len = 16;
      let chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
      let maxPos = chars.length;
      let pwd = '';
      for (let i = 0; i < len; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * maxPos));
      }
      this.modalsdate.appsecret = pwd;
    },
  },
};
</script>

<style scoped>
.reset {
  margin-left: 10px;
}
.form-content {
  display: flex;
  flex-direction: column;
}
.input-button {
  display: flex;
}
w .trip {
  color: #aaa;
  line-height: 20px;
}
.setting-style /deep/ .ivu-form-item {
  margin-bottom: 14px;
}
</style>
