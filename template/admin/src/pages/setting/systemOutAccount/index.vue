<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-alert type="warning" :closable="false" class="alert-info">
        <template slot="title">
          获取访问 Token 的接口:<br />
          请求 URL: /outapi/access_token 请求方式: POST 请求参数: appid和appsecret 返回数据: access_token: 访问令牌
          exp_time: 令牌过期时间 auth_info: 授权信息<br />
          使用获取到的 Token 访问对外接口:<br />
          在 HTTP 请求头中添加 Authorization 字段 字段值为 Bearer access_token(注意 Bearer 后有一个空格)
        </template>
      </el-alert>
      <el-form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <el-row>
          <el-col v-bind="grid">
            <el-button v-auth="['setting-system_admin-add']" type="primary" v-db-click @click="add">添加账号</el-button>
          </el-col>
        </el-row>
      </el-form>
      <el-table
        :data="list"
        class="mt14"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        v-loading="loading"
        highlight-current-row
      >
        <el-table-column label="编号" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="账号" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.appid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="描述" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="最后登录时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.last_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="最后登录ip" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.ip }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="130">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="140">
          <template slot-scope="scope">
            <a v-db-click @click="setUp(scope.row)">设置</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '删除账号', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="formValidate.page"
          :limit.sync="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <el-dialog
      :visible.sync="modals"
      :title="type == 0 ? '添加账号' : '编辑账号'"
      :close-on-click-modal="false"
      :show-close="true"
      width="720px"
    >
      <el-form
        ref="modalsdate"
        :model="modalsdate"
        :rules="type == 0 ? ruleValidate : editValidate"
        label-width="80px"
        label-position="right"
      >
        <el-form-item label="账号：" prop="appid">
          <div style="display: flex">
            <el-input type="text" v-model="modalsdate.appid" :disabled="type != 0"></el-input>
          </div>
        </el-form-item>
        <el-form-item label="密码：" prop="appsecret">
          <div style="display: flex">
            <el-input type="text" v-model="modalsdate.appsecret" class="input"></el-input>
            <el-button type="primary" v-db-click @click="reset" class="reset">随机</el-button>
          </div>
        </el-form-item>
        <el-form-item label="描述：" prop="title">
          <div style="display: flex">
            <el-input type="textarea" v-model="modalsdate.title"></el-input>
          </div>
        </el-form-item>
        <el-form-item label="接口权限：" prop="title">
          <!-- <el-checkbox-group v-model="modalsdate.rules">
            <el-checkbox
              :disabled="[2, 3].includes(item.id)"
              style="width: 30%"
              v-for="item in intList"
              :key="item.id"
              :label="item.id"
              >{{ item.name }}</el-checkbox
            >
          </el-checkbox-group> -->
          <el-tree
            :data="intList"
            :props="props"
            multiple
            show-checkbox
            ref="tree"
            node-key="id"
            :default-checked-keys="selectIds"
            @check-change="selectTree"
          ></el-tree>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button v-db-click @click="cancel">取 消</el-button>
        <el-button type="primary" v-db-click @click="ok('modalsdate')">确 定</el-button>
      </span>
    </el-dialog>
    <el-dialog
      :visible.sync="settingModals"
      scrollable
      title="设置推送"
      width="1000px"
      :close-on-click-modal="false"
      :show-close="true"
    >
      <el-form
        class="setting-style"
        ref="settingData"
        :model="settingData"
        :rules="type == 0 ? ruleValidate : editValidate"
        label-width="155px"
        label-position="right"
      >
        <el-form-item label="推送开关：" prop="switch">
          <el-switch v-model="settingData.push_open" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="推送账号：" prop="push_account">
          <div class="form-content">
            <el-input type="text" v-model="settingData.push_account" placeholder="请输入推送账号"></el-input>
            <span class="tips-info">接受推送方获取token的账号</span>
          </div>
        </el-form-item>
        <el-form-item label="推送密码：" prop="push_password">
          <div class="form-content">
            <el-input type="text" v-model="settingData.push_password" placeholder="请输入推送密码"></el-input>
            <span class="tips-info">接受推送方获取token的密码</span>
          </div>
        </el-form-item>
        <el-form-item label="获取TOKEN接口：" prop="push_token_url">
          <div class="form-content">
            <div class="input-button">
              <el-input type="text" v-model="settingData.push_token_url" placeholder="请输入获取TOKEN接口"></el-input>
              <el-button class="ml10" type="primary" v-db-click @click="textOutUrl(settingData.id)">测试链接</el-button>
            </div>
            <span class="tips-info"
              >接受推送方获取token的URL地址，POST方法，传入push_account和push_password，返回token和有效时间time(秒)</span
            >
          </div>
        </el-form-item>
        <el-form-item label="用户数据修改推送接口：" prop="user_update_push">
          <div class="form-content">
            <el-input
              type="text"
              v-model="settingData.user_update_push"
              placeholder="请输入用户数据修改推送接口"
            ></el-input>
            <span class="tips-info">用户修改积分，余额，经验等将用户信息推送至该地址，POST方法</span>
          </div>
        </el-form-item>
        <el-form-item label="订单创建推送接口：" prop="order_create_push">
          <div class="form-content">
            <el-input
              type="text"
              v-model="settingData.order_create_push"
              placeholder="请输入订单创建推送接口"
            ></el-input>
            <span class="tips-info">订单创建时推送订单信息至该地址，POST方法</span>
          </div>
        </el-form-item>
        <el-form-item label="订单支付推送接口：" prop="order_pay_push">
          <div class="form-content">
            <el-input type="text" v-model="settingData.order_pay_push" placeholder="请输入订单支付推送接口"></el-input>
            <span class="tips-info">订单完成支付时推送订单已支付信息至该地址，POST方法</span>
          </div>
        </el-form-item>
        <el-form-item label="售后订单创建推送接口：" prop="refund_create_push">
          <div class="form-content">
            <el-input
              type="text"
              v-model="settingData.refund_create_push"
              placeholder="请输入售后订单创建推送接口"
            ></el-input>
            <span class="tips-info">售后订单生成时推送售后单信息至该地址，POST方法</span>
          </div>
        </el-form-item>
        <el-form-item label="售后订单取消推送接口：" prop="refund_cancel_push">
          <div class="form-content">
            <el-input
              type="text"
              v-model="settingData.refund_cancel_push"
              placeholder="请输入售后订单取消推送接口"
            ></el-input>
            <span class="tips-info">售后订单取消时推送售后单取消信息至该地址，POST方法</span>
          </div>
        </el-form-item>
      </el-form>
      <div slot="footer">
        <el-button type="primary" v-db-click @click="submit('settingData')">确定</el-button>
        <el-button v-db-click @click="settingModals = false">取消</el-button>
      </div>
    </el-dialog>
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
      props: {
        label: 'title',
        disabled: 'disableCheckbox',
      },
      selectIds: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '50px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
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
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
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
          this.$message.error(res.msg);
        });
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
    selectTree(e, i) {},
    getIntList(type, list) {
      let arr = [];
      interfaceList().then((res) => {
        this.intList = res.data;
        if (!type) {
          this.intList.map((item) => {
            if (item.id === 1) {
              item.checked = true;
              item.disableCheckbox = true;
              arr.push(item.id);
              if (item.children.length) {
                item.children.map((v) => {
                  v.checked = true;
                  v.disableCheckbox = true;
                  arr.push(v.id);
                });
              }
            }
          });
          this.$nextTick((e) => {
            this.selectIds = arr;
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
          this.selectIds = list;
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
      this.modalsdate.appsecret = row.apppwd;
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
          this.$message.success(res.msg);
          this.list.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
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
        this.$message.success(res.msg);
        this.settingModals = false;
        this.getList();
      });
    },
    textOutUrl() {
      textOutUrl(this.settingData)
        .then((res) => {
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    ok(name) {
      let fuc = this.modalsid ? outSavesApi : outSaveApi;
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.modalsdate.rules = [];
          this.$refs.tree.getCheckedNodes().map((node) => {
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
              (this.modals = false), this.$message.success(res.msg);
              this.modalsid = '';
              this.getList();
            })
            .catch((err) => {
              this.$message.error(err.msg);
            });
        } else {
          this.$message.warning('请完善数据');
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
.setting-style ::v-deep .ivu-form-item {
  margin-bottom: 14px;
}
.alert-info {
  margin-bottom: 14px;
}
</style>
