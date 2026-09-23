<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-alert type="warning" :closable="false" class="alert-info">
        <template #title>
          获取访问 Token 的接口:<br />
          请求 URL: /outapi/access_token 请求方式: POST 请求参数: appid和appsecret 返回数据: access_token: 访问令牌
          exp_time: 令牌过期时间 auth_info: 授权信息<br />
          使用获取到的 Token 访问对外接口:<br />
          在 HTTP 请求头中添加 Authorization 字段 字段值为 Bearer access_token(注意 Bearer 后有一个空格)
        </template>
      </el-alert>
      <el-form :model="formValidate" :label-width="labelWidth" :label-position="labelPosition" @submit.prevent>
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
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="账号" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.appid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="描述" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="添加时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="最后登录时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.last_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="最后登录ip" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.ip }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="130">
          <template #default="scope">
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
          <template #default="scope">
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
          v-model:page="formValidate.page"
          v-model:limit="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <el-dialog
      v-model="modals"
      :title="type == 0 ? '添加账号' : '编辑账号'"
      :close-on-click-modal="false"
      :show-close="true"
      width="720px"
    >
      <el-form
        ref="modalsdateRef"
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
            :props="treeProps"
            multiple
            show-checkbox
            ref="tree"
            node-key="id"
            :default-checked-keys="selectIds"
            @check-change="selectTree"
          ></el-tree>
        </el-form-item>
      </el-form>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="cancel">取 消</el-button>
          <el-button type="primary" v-db-click @click="ok">确 定</el-button>
        </span></template
      >
    </el-dialog>
    <el-dialog
      v-model="settingModals"
      scrollable
      title="设置推送"
      width="1000px"
      :close-on-click-modal="false"
      :show-close="true"
    >
      <el-form
        class="setting-style"
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
            <span class="tip">接受推送方获取token的账号</span>
          </div>
        </el-form-item>
        <el-form-item label="推送密码：" prop="push_password">
          <div class="form-content">
            <el-input type="text" v-model="settingData.push_password" placeholder="请输入推送密码"></el-input>
            <span class="tip">接受推送方获取token的密码</span>
          </div>
        </el-form-item>
        <el-form-item label="获取TOKEN接口：" prop="push_token_url">
          <div class="form-content">
            <div class="input-button">
              <el-input type="text" v-model="settingData.push_token_url" placeholder="请输入获取TOKEN接口"></el-input>
              <el-button class="ml10" type="primary" v-db-click @click="textOutUrl()">测试链接</el-button>
            </div>
            <span class="tip"
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
            <span class="tip">用户修改积分，余额，经验等将用户信息推送至该地址，POST方法</span>
          </div>
        </el-form-item>
        <el-form-item label="订单创建推送接口：" prop="order_create_push">
          <div class="form-content">
            <el-input
              type="text"
              v-model="settingData.order_create_push"
              placeholder="请输入订单创建推送接口"
            ></el-input>
            <span class="tip">订单创建时推送订单信息至该地址，POST方法</span>
          </div>
        </el-form-item>
        <el-form-item label="订单支付推送接口：" prop="order_pay_push">
          <div class="form-content">
            <el-input type="text" v-model="settingData.order_pay_push" placeholder="请输入订单支付推送接口"></el-input>
            <span class="tip">订单完成支付时推送订单已支付信息至该地址，POST方法</span>
          </div>
        </el-form-item>
        <el-form-item label="售后订单创建推送接口：" prop="refund_create_push">
          <div class="form-content">
            <el-input
              type="text"
              v-model="settingData.refund_create_push"
              placeholder="请输入售后订单创建推送接口"
            ></el-input>
            <span class="tip">售后订单生成时推送售后单信息至该地址，POST方法</span>
          </div>
        </el-form-item>
        <el-form-item label="售后订单取消推送接口：" prop="refund_cancel_push">
          <div class="form-content">
            <el-input
              type="text"
              v-model="settingData.refund_cancel_push"
              placeholder="请输入售后订单取消推送接口"
            ></el-input>
            <span class="tip">售后订单取消时推送售后单取消信息至该地址，POST方法</span>
          </div>
        </el-form-item>
      </el-form>
      <template #footer
        ><div>
          <el-button type="primary" v-db-click @click="submit">确定</el-button>
          <el-button v-db-click @click="settingModals = false">取消</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import {
  accountListApi,
  outSaveApi,
  outSavesApi,
  setShowApi,
  outSetUp,
  interfaceList,
  setUpPush,
  textOutUrl as textOutUrlApi,
} from './api';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'systemOut' });

const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => (isMobile.value ? undefined : '50px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const tree = ref(null);
const modalsdateRef = ref(null);

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const total = ref(0);
const loading = ref(false);
const roleData = ref({
  status1: '',
});
const formValidate = ref({
  roles: '',
  status: '',
  name: '',
  page: 1, // 当前页
  limit: 15, // 每页显示条数
});
const status = ref('');
const list = ref([]);
const intList = ref([]);
const FromData = ref(null);
const modalTitleSs = ref('');
const ids = ref(Number);
const modals = ref(false);
const modalsid = ref('');
const type = ref(0);
const modalsdate = ref({
  appid: '',
  appsecret: '',
  title: '',
  rules: [],
});
const settingModals = ref(false);
const settingData = ref({
  switch: 1,
  name: '',
});
const ruleValidate = {
  appid: [{ required: true, message: '请输入正确的账号 (4到30位之间)', trigger: 'blur', min: 4, max: 30 }],
  appsecret: [{ required: true, message: '请输入正确的密码 (6到32位之间)', trigger: 'blur', min: 6, max: 32 }],
  title: [{ message: '请输入正确的描述 (不能多于200位数)', trigger: 'blur', max: 200 }],
};
const editValidate = {
  appsecret: [{ required: false, message: '请输入正确的密码 (6到32位之间)', trigger: 'blur', min: 6, max: 32 }],
};
const treeProps = {
  label: 'title',
  disabled: 'disableCheckbox',
};
const selectIds = ref([]);

getList();

// 开启状态
function onchangeIsShow(row) {
  let data = {
    id: row.id,
    status: row.status,
  };
  setShowApi(data)
    .then(async (res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 请求列表
function submitFail() {
  getList();
}
// 列表
function getList() {
  loading.value = true;
  formValidate.value.roles = formValidate.value.roles || '';
  accountListApi(formValidate.value)
    .then(async (res) => {
      total.value = res.data.count;
      list.value = res.data.list;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 添加
function add() {
  modals.value = true;
  type.value = 0;
  modalsdate.value = {
    appid: '',
    appsecret: '',
    title: '',
    rules: [],
  };
  getIntList();
}
function selectTree(e, i) {}
function getIntList(t, list) {
  let arr = [];
  interfaceList().then((res) => {
    intList.value = res.data;
    if (!t) {
      intList.value.map((item) => {
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
      nextTick((e) => {
        selectIds.value = arr;
      });
    } else {
      list.map((item) => {
        intList.value.map((e) => {
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
      selectIds.value = list;
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
}
// 编辑
function edit(row) {
  modals.value = true;
  modalsdate.value.appid = row.appid;
  modalsdate.value.title = row.title;
  modalsdate.value.appsecret = row.apppwd;
  modalsdate.value.rules = row.rules.map((e) => {
    return Number(e);
  });
  modalsid.value = row.id;
  type.value = 1;
  getIntList('edit', modalsdate.value.rules);
}
// 删除
function del(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `setting/system_out_account/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      list.value.splice(num, 1);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑
function setUp(row) {
  settingModals.value = true;
  settingData.value = row;
}
// 搜索
function userSearchs() {
  formValidate.value.status = status.value === 'all' ? '' : status.value;
  formValidate.value.page = 1;
  list.value = [];
  getList();
}
function submit() {
  setUpPush(settingData.value).then((res) => {
    ElMessage.success(res.msg);
    settingModals.value = false;
    getList();
  });
}
function textOutUrl() {
  textOutUrlApi(settingData.value)
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function ok() {
  let fuc = modalsid.value ? outSavesApi : outSaveApi;
  modalsdateRef.value.validate((valid) => {
    if (valid) {
      modalsdate.value.rules = [];
      tree.value.getCheckedNodes().map((node) => {
        modalsdate.value.rules.push(node.id);
      });
      if (modalsid.value) modalsdate.value.id = modalsid.value;
      fuc(modalsdate.value)
        .then((res) => {
          modalsdate.value = {
            appid: '',
            appsecret: '',
            title: '',
            rules: [],
          };
          (modals.value = false), ElMessage.success(res.msg);
          modalsid.value = '';
          getList();
        })
        .catch((err) => {
          ElMessage.error(err.msg);
        });
    } else {
      ElMessage.warning('请完善数据');
    }
  });
}
function cancel() {
  modalsid.value = '';
  modalsdate.value = {
    appid: '',
    appsecret: '',
    title: '',
  };
  modals.value = false;
}
function reset() {
  let len = 16;
  let chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
  let maxPos = chars.length;
  let pwd = '';
  for (let i = 0; i < len; i++) {
    pwd += chars.charAt(Math.floor(Math.random() * maxPos));
  }
  modalsdate.value.appsecret = pwd;
}
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
.setting-style :deep(.ivu-form-item) {
  margin-bottom: 14px;
}
.alert-info {
  margin-bottom: 14px;
}
</style>
