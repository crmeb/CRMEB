<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="userFrom" label-width="80px" label-position="right" @submit.prevent inline>
          <el-form-item label="用户搜索：" label-for="nickname">
            <el-input v-model="userFrom.nickname" placeholder="请输入用户" clearable class="form_content_width">
              <template #prepend
                ><el-select v-model="field_key" style="width: 100px">
                  <el-option value="all" label="全部"></el-option>
                  <el-option value="uid" label="UID"></el-option>
                  <el-option value="phone" label="手机号"></el-option>
                  <el-option value="nickname" label="用户昵称"></el-option> </el-select
              ></template>
            </el-input>
          </el-form-item>
          <el-form-item label="用户等级：" label-for="level">
            <el-select v-model="level" placeholder="请选择用户等级" clearable class="form_content_width">
              <el-option value="all" label="全部">全部</el-option>
              <el-option
                :value="item.id"
                v-for="(item, index) in levelList"
                :key="index"
                :label="item.name"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="用户分组：">
            <el-select v-model="group_id" placeholder="请选择用户分组" clearable class="form_content_width">
              <el-option value="all" label="全部"></el-option>
              <el-option
                :value="item.id"
                v-for="(item, index) in groupList"
                :key="index"
                :label="item.group_name"
              ></el-option>
            </el-select>
          </el-form-item>
          <template v-if="collapse">
            <el-form-item v-if="distributionEnabled" label="分销等级：">
              <el-select v-model="agent_level" placeholder="请选择分销等级" clearable class="form_content_width">
                <el-option value="all" label="全部"></el-option>
                <el-option
                  :value="item.grade"
                  v-for="(item, index) in membershipList"
                  :key="index"
                  :label="item.name"
                ></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="用户标签：" label-for="label_id">
              <div class="labelInput acea-row row-between-wrapper" v-db-click @click="openSelectLabel">
                <div style="width: 222px">
                  <div v-if="selectDataLabel.length">
                    <el-tag :closable="false" v-for="(item, index) in selectDataLabel" :key="index" class="mr10">{{
                      item.label_name
                    }}</el-tag>
                  </div>
                  <span class="span" v-else>选择用户关联标签</span>
                </div>
                <div class="ivu-icon ivu-icon-ios-arrow-down"></div>
              </div>
            </el-form-item>
            <el-form-item label="付费会员：" label-for="isMember">
              <el-select v-model="userFrom.isMember" placeholder="请选择" clearable class="form_content_width">
                <el-option value="" label="全部"></el-option>
                <el-option value="1" label="是"></el-option>
                <el-option value="0" label="否"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="储值余额：" label-for="balance">
              <el-input clearable placeholder="最小值" v-model="userFrom.balance[0]" class="form_range_content_width" />
              <span class="ml5 mr5">~</span>
              <el-input clearable placeholder="最大值" v-model="userFrom.balance[1]" class="form_range_content_width" />
            </el-form-item>
            <el-form-item label="积分剩余：" label-for="integral">
              <el-input
                clearable
                placeholder="最小值"
                v-model="userFrom.integral[0]"
                class="form_range_content_width"
              />
              <span class="ml5 mr5">~</span>
              <el-input
                clearable
                placeholder="最大值"
                v-model="userFrom.integral[1]"
                class="form_range_content_width"
              />
            </el-form-item>
            <el-form-item label="上次消费：" label-for="before_pay_time">
              <date-range-picker
                clearable
                width="250px"
                v-model="before_pay_time"
                :editable="false"
                @change="(e) => onchangeTime(e, 'before_pay_time')"
                format="YYYY/MM/DD"
                value-format="YYYY/MM/DD"
              ></date-range-picker>
            </el-form-item>
            <el-form-item label="下单次数：" label-for="pay_count">
              <el-input
                clearable
                placeholder="最小值"
                v-model="userFrom.pay_count_num[0]"
                class="form_range_content_width"
              />
              <span class="ml5 mr5">~</span>
              <el-input
                clearable
                placeholder="最大值"
                v-model="userFrom.pay_count_num[1]"
                class="form_range_content_width"
              />
            </el-form-item>
            <el-form-item label="消费金额：" label-for="store_name">
              <el-input
                clearable
                placeholder="最小值"
                v-model="userFrom.pay_count_money[0]"
                class="form_range_content_width"
              />
              <span class="ml5 mr5">~</span>
              <el-input
                clearable
                placeholder="最大值"
                v-model="userFrom.pay_count_money[1]"
                class="form_range_content_width"
              />
            </el-form-item>
            <el-form-item label="访问情况：" label-for="user_time_type">
              <el-select v-model="user_time_type" placeholder="请选择访问情况" clearable class="form_content_width">
                <el-option value="" label="全部"></el-option>
                <el-option value="visitno" label="时间段未访问"></el-option>
                <el-option value="visit" label="时间段访问过"></el-option>
                <el-option value="add_time" label="首次访问"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="访问时间：" label-for="user_time" v-if="user_time_type">
              <date-range-picker
                clearable
                width="250px"
                v-model="timeVal"
                :editable="false"
                @change="(e) => onchangeTime(e, 'user_time')"
                format="YYYY/MM/DD"
                value-format="YYYY/MM/DD"
              ></date-range-picker>
            </el-form-item>
            <!-- <el-form-item label="地区：" label-for="country">
                <el-select
                  v-model="userFrom.country"
                  placeholder="请选择国家"
                  clearable
                  @change="changeCountry"
                  class="form_content_width"
                >
                  <el-option value="domestic" label="中国"></el-option>
                  <el-option value="abroad" label="外国"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="省份：" v-if="userFrom.country === 'domestic'">
                <el-cascader
                  :options="addresData"
                  :value="address"
                  v-model="address"
                  @change="handleChange"
                  clearable
                  style="width: 250px"
                ></el-cascader>
              </el-form-item> -->
          </template>

          <el-form-item class="search-form-sub">
            <el-button type="primary" label="default" v-db-click @click="userSearchs">查询</el-button>
            <el-button class="ResetSearch" v-db-click @click="reset('userFrom')">重置</el-button>
            <a class="ivu-ml-8 font12 ml10" v-db-click @click="collapse = !collapse">
              <template v-if="!collapse"> 展开 <i class="el-icon-arrow-down" /> </template>
              <template v-else> 收起 <i class="el-icon-arrow-up" /> </template>
            </a>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16" :body-style="{ padding: '0 20px 20px' }">
      <!-- tab-click 时 v-model 尚未同步新值，getList 会带上上一个 tab 的 user_type；tab-change 在同步后触发 -->
      <el-tabs v-model="userFrom.user_type" @tab-change="onClickTab">
        <el-tab-pane :label="item.name" :name="item.type" v-for="(item, index) in headeNum" :key="index" />
      </el-tabs>
      <el-row :gutter="24" justify="space-between">
        <el-col :span="24">
          <el-button v-auth="['admin-user-save']" type="primary" v-db-click @click="edit({ uid: 0 })"
            >添加用户</el-button
          >
          <el-button v-auth="['admin-user-coupon']" v-db-click @click="onSend">发送优惠券</el-button>
          <el-button
            v-auth="['admin-wechat-news']"
            class="greens mr10"
            v-db-click
            @click="onSendPic"
            v-if="userFrom.user_type === 'wechat'"
          >
            发送图文消息
          </el-button>
          <el-button v-auth="['admin-user-group_set']" v-db-click @click="setGroup">批量设置分组</el-button>
          <el-button v-auth="['admin-user-set_label']" v-db-click @click="setLabel">批量设置标签</el-button>
          <el-button class="mr10" v-db-click @click="exportList">导出</el-button>

          <!-- <el-button v-auth="['admin-user-synchro']" class="mr20" v-db-click @click="synchro">同步公众号用户</el-button> -->
        </el-col>
        <el-col :span="24" class="userAlert" v-if="selectionList.length">
          <el-alert show-icon>
            <template #title>
              已选择<i class="userI"> {{ selectionList.length }} </i>项
            </template>
          </el-alert>
        </el-col>
      </el-row>
      <el-table
        :data="userLists"
        class="mt16"
        ref="table"
        highlight-current-row
        v-loading="loading"
        empty-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
        @sort-change="sortChanged"
        @select="handleSelectRow"
        @select-all="handleSelectAll"
      >
        <el-table-column type="expand">
          <template #default="scope">
            <expandRow :row="scope.row"></expandRow>
          </template>
        </el-table-column>
        <el-table-column type="selection" :selectable="isSel" width="55"> </el-table-column>
        <el-table-column label="用户ID" min-width="80">
          <template #default="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="头像" min-width="60">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="昵称" min-width="150">
          <template #default="scope">
            <div class="acea-row">
              <i class="el-icon-male" v-show="scope.row.sex === '男'" style="color: #2db7f5; font-size: 15px"></i>
              <i class="el-icon-female" v-show="scope.row.sex === '女'" style="color: #ed4014; font-size: 15px"></i>
              <div v-text="scope.row.nickname" class=""></div>
            </div>
            <div v-if="scope.row.is_del == 1" style="color: red">用户已注销</div>
          </template>
        </el-table-column>
        <el-table-column label="付费会员" min-width="90">
          <template #default="scope">
            <div>{{ scope.row.isMember ? '是' : '否' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="用户等级" min-width="90">
          <template #default="scope">
            <div>{{ scope.row.level }}</div>
          </template>
        </el-table-column>
        <el-table-column label="分组" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.group_id }}</div>
          </template>
        </el-table-column>
        <el-table-column label="手机号" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.phone }}</div>
          </template>
        </el-table-column>
        <el-table-column label="用户类型" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.user_type }}</div>
          </template>
        </el-table-column>
        <el-table-column label="余额" prop="now_money" min-width="100" :sortable="true">
          <template #default="scope">
            <div>{{ scope.row.now_money }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <template v-if="scope.row.is_del != 1">
              <a v-db-click @click="userDetail(scope.row)">详情</a>

              <el-divider direction="vertical"></el-divider>
              <el-dropdown @command="changeMenu(scope.row, $event, scope.$index)" :transfer="true">
                <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
                <template #dropdown
                  ><el-dropdown-menu>
                    <!-- <el-dropdown-item command="1">编辑</el-dropdown-item> -->
                    <el-dropdown-item command="2">修改余额</el-dropdown-item>
                    <el-dropdown-item command="8">修改积分</el-dropdown-item>
                    <el-dropdown-item command="3">赠送会员</el-dropdown-item>
                    <!--                                <el-dropdown-item command="4" v-if="row.vip_name">清除等级</el-dropdown-item>-->
                    <el-dropdown-item command="5">设置分组</el-dropdown-item>
                    <el-dropdown-item command="6">设置标签</el-dropdown-item>
                    <el-dropdown-item command="99" v-if="scope.row.spread_uid">清除上级推广人</el-dropdown-item>
                  </el-dropdown-menu></template
                >
              </el-dropdown>
            </template>
            <template v-else>
              <a v-db-click @click="userDetail(scope.row)">详情</a>
            </template>
          </template>
        </el-table-column>
      </el-table>

      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="userFrom.page"
          v-model:limit="userFrom.limit"
          @pagination="pageChange"
        />
      </div>
    </el-card>
    <!-- 编辑表单 积分余额-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
    <!-- 发送优惠券-->
    <send-from ref="sends" :userIds="ids.toString()"></send-from>
    <!-- 会员详情-->
    <user-details ref="userDetailsRef"></user-details>
    <!--发送图文消息 -->
    <el-dialog v-model="modal13" title="发送消息" width="1200px" class="modelBox">
      <news-category
        v-if="modal13"
        :isShowSend="isShowSend"
        :userIds="ids.toString()"
        :scrollerHeight="scrollerHeight"
        :contentTop="contentTop"
        :contentWidth="contentWidth"
        :maxCols="maxCols"
      ></news-category>
    </el-dialog>
    <el-dialog v-model="labelShow" append-to-body title="请选择用户标签" width="540px" :show-close="true">
      <userLabel
        v-if="labelShow"
        :uid="labelActive.uid"
        :only_get="!labelActive.uid"
        :is_batch="is_batch"
        @close="labelClose"
        @activeData="activeData"
        @onceGetList="onceGetList"
      ></userLabel>
    </el-dialog>
    <el-drawer class="demo-drawer" v-model="modals" :wrapperClosable="false" size="720" title="用户信息填写">
      <div class="demo-drawer__content">
        <UserEdit ref="userEditRef" v-if="modals" :userData="userData"></UserEdit>
        <div class="fix_footer acea-row row-center">
          <el-button v-db-click @click="modals = false">取消</el-button>
          <el-button type="primary" v-db-click @click="setUser">提交</el-button>
        </div>
      </div>
    </el-drawer>
    <!-- 用户标签 -->
    <el-dialog
      v-model="selectLabelShow"
      append-to-body
      title="请选择用户标签"
      width="540px"
      :show-close="true"
      :close-on-click-modal="false"
    >
      <userLabel
        v-if="selectLabelShow"
        :uid="0"
        :only_get="true"
        :selectDataLabel="selectDataLabel"
        @activeData="activeSelectData"
        @close="labelClose"
      ></userLabel>
    </el-dialog>
  </div>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import userLabel from '@/components/userLabel';
import expandRow from './tableExpand.vue';
import UserEdit from './handle/userEdit.vue';
import {
  userList,
  getUserData,
  editOtherApi,
  giveLevelApi,
  userSetGroup,
  userGroupApi,
  levelListApi,
  userSetLabelApi,
  userLabelApi,
  userSynchro,
  getUserSaveForm,
  giveLevelTimeApi,
  getUserInfo,
  setUser as setUserApi,
  editUser,
  saveSetLabel,
} from '@/api/user';
import { exportUserList } from '@/api/export';
import editFrom from '../../../components/from/from';
import sendFrom from '@/components/sendCoupons/index';
import UserDetails from './handle/userDetails';
import newsCategory from '@/components/newsCategory/index';
import { cityList } from '@/api/app';
import { pluginListApi } from '@/api/plugin';
import { membershipDataListApi } from '@/api/membershipLevel';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';
import exportExcel from '@/utils/newToExcel.js';

defineOptions({ name: 'user_list' });

const dataLabel = ref([]);
const selectDataLabel = ref([]);
const userData = ref({});
const modals = ref(false);
const selectLabelShow = ref(false);
const labelShow = ref(false);
const is_batch = ref(false);
const labelActive = ref({
  uid: 0,
});
const collapse = ref(false);
// 渠道筛选 Tab 由后端按已安装且已启用的渠道模块返回，此处仅兜底"全部"
const headeNum = ref([{ type: '', name: '全部' }]);
const address = ref([]);
const addresData = ref([]);
const isShowSend = ref(true);
const modal13 = ref(false);
const maxCols = ref(4);
const scrollerHeight = ref('600');
const contentTop = ref('130');
const contentWidth = ref('98%');
const grid = {
  xl: 6,
  lg: 6,
  md: 8,
  sm: 12,
  xs: 24,
};
const grid2 = {
  xl: 8,
  lg: 8,
  md: 8,
  sm: 12,
  xs: 24,
};
const loading = ref(false);
const total = ref(0);
const userFrom = ref({
  label_id: '',
  user_type: '',
  status: '',
  sex: '',
  country: '',
  isMember: '',
  pay_count_num: ['', ''],
  balance: ['', ''],
  integral: ['', ''],
  pay_count_money: ['', ''],
  user_time_type: '',
  user_time: '',
  before_pay_time: '',
  nickname: '',
  province: '',
  city: '',
  page: 1,
  limit: 15,
  level: '',
  group_id: '',
  agent_level: '',
  field_key: '',
});
const before_pay_time = ref([]);
const field_key = ref('');
const level = ref('');
const group_id = ref('');
const agent_level = ref('');
const label_id = ref('');
const user_time_type = ref('');
const pay_count = ref('');
const userLists = ref([]);
const FromData = ref(null);
const selectionList = ref([]);
const user_ids = ref('');
const selectedData = ref([]);
const timeVal = ref([]);
const groupList = ref([]);
const levelList = ref([]);
const distributionEnabled = ref(false);
const membershipList = ref([]);
const labelFrom = ref({
  page: 1,
  limit: '',
});
const labelLists = ref([]);
const selectedIds = ref([]); // 选中合并项的id
const ids = ref([]);
// 模板 ref
const table = ref(null);
const sends = ref(null);
const userDetailsRef = ref(null);
const userEditRef = ref(null);

// created
getList();
getCityList();

onMounted(() => {
  userGroup();
  levelLists();
  loadDistributionState();
});

function getCityList() {
  cityList().then((res) => {
    addresData.value = res.data;
  });
}

function setUser() {
  let data = userEditRef.value.formItem;
  let ids = [];
  userEditRef.value.dataLabel.map((i) => {
    ids.push(i.id);
  });
  data.label_id = ids;
  // if (!data.real_name) return ElMessage.warning("请输入真实姓名");
  // if (!data.phone) return ElMessage.warning("请输入手机号");
  // if (!data.pwd) return ElMessage.warning("请输入密码");
  // if (!data.true_pwd) return ElMessage.warning("请输入确认密码");
  if (data.uid) {
    editUser(data)
      .then((res) => {
        modals.value = false;
        ElMessage.success(res.msg);
        getList();
      })
      .catch((err) => {
        ElMessage.error(err);
      });
  } else {
    setUserApi(data)
      .then((res) => {
        modals.value = false;
        ElMessage.success(res.msg);
        getList();
      })
      .catch((err) => {
        ElMessage.error(err.msg);
      });
  }
}

function onceGetList() {
  labelActive.value.uid = 0;
  getList();
}

// 标签弹窗关闭
function labelClose() {
  labelActive.value.uid = 0;
  labelShow.value = false;
  selectLabelShow.value = false;
}

function save() {
  modals.value = true;

  // modalForm(getUserSaveForm())
  //   .then(() => {
  //     userFrom.value.page = 1;
  //     getList();
  //   })
  //   .catch((res) => {
  //     ElMessage.error(res.msg);
  //   });
}

function synchro() {
  userSynchro()
    .then((res) => {
      ElMessage.success(res.msg);
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

function isSel(row) {
  return !!!row.is_del;
}

// 分组列表
function groupLists() {
  loading.value = true;
  userLabelApi(labelFrom.value)
    .then(async (res) => {
      let data = res.data;
      labelLists.value = data.list;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

function onClickTab() {
  userFrom.value.page = 1;
  getList();
}

function userGroup() {
  let data = {
    page: 1,
    limit: '',
  };
  userGroupApi(data).then((res) => {
    groupList.value = res.data.list;
  });
}

function levelLists() {
  let data = {
    page: 1,
    limit: '',
    title: '',
    is_show: 1,
  };
  levelListApi(data).then((res) => {
    levelList.value = res.data.list;
  });
}

function loadDistributionState() {
  pluginListApi({ type: 'distribution' })
    .then((res) => {
      const list = Array.isArray(res.data) ? res.data : [];
      const plugin = list.find((item) => item.key === 'distribution' || item.id === 'distribution');
      distributionEnabled.value = Boolean(plugin && Number(plugin.installed) === 1 && Number(plugin.enabled) === 1);
      if (distributionEnabled.value) membershipDataList();
    })
    .catch(() => {
      distributionEnabled.value = false;
    });
}

function membershipDataList() {
  const data = {
    page: 1,
    limit: 0,
    staus: 1,
  };
  membershipDataListApi(data).then((res) => {
    membershipList.value = res.data.list;
  });
}

// 批量设置分组；
function setGroup() {
  if (ids.value.length === 0) {
    ElMessage.warning('请选择要设置分组的用户');
  } else {
    let uids = { uids: ids.value };
    modalForm(userSetGroup(uids)).then(() => {
      ids.value = [];
      selectedIds.value = [];
      getList();
    });
  }
}

// 批量设置标签；
function setLabel() {
  if (ids.value.length === 0) {
    ElMessage.warning('请选择要设置标签的用户');
  } else {
    is_batch.value = true;
    let uids = { uids: ids.value };
    labelActive.value.uid = 0;
    labelShow.value = true;
    // modalForm(userSetLabelApi(uids)).then(() =>
    //   sends.value.getList()
    // );
  }
}

function activeSelectData(data) {
  selectLabelShow.value = false;
  selectDataLabel.value = data || [];
  if (selectDataLabel.value.length) {
    let activeIds = [];
    selectDataLabel.value.map((item) => {
      activeIds.push(item.id);
    });
    userFrom.value.label_id = activeIds.join(',');
    getList();
  } else {
    userFrom.value.label_id = '';
  }
}

function handleClose(tag) {
  let i = selectDataLabel.value.findIndex((item) => item.id === tag.id);
  if (i !== -1) {
    selectDataLabel.value.splice(i, 1);
  }
  nextTick(() => {
    if (selectDataLabel.value.length) {
      let activeIds = [];
      selectDataLabel.value.map((item) => {
        activeIds.push(item.id);
      });
      userFrom.value.label_id = activeIds.join(',');
    } else {
      userFrom.value.label_id = '';
    }
  });
  // userSearchs();
}

// 批量设置标签
function activeData(data, type) {
  let labels = [];
  if (!data.length) return;
  data.map((i) => {
    labels.push(i.id);
  });
  saveSetLabel({
    uids: ids.value.join(','),
    label_id: labels,
    label_type: type,
  }).then((res) => {
    labelShow.value = false;
    selectedIds.value = new Set();
    getList();
    ElMessage.success(res.msg);
  });
}

// 是否为付费会员；
function changeMember() {
  userFrom.value.page = 1;
  getList();
}

// 选择国家
function changeCountry() {
  if (userFrom.value.country === 'abroad' || !userFrom.value.country) {
    selectedData.value = [];
    userFrom.value.province = '';
    userFrom.value.city = '';
    address.value = [];
  }
}

// 选择地址
function handleChange(selectedDataVal) {
  const labels = selectedDataVal.map((o) => o.label);
  selectedData.value = labels;
  userFrom.value.province = labels[0];
  userFrom.value.city = labels[1];
}

// 具体日期
function onchangeTime(e, type) {
  userFrom.value[type] = e ? e.join('-') : '';
}

function userDetail(row) {
  userDetailsRef.value.modals = true;
  userDetailsRef.value.getDetails(row.uid);
}

// 操作
function changeMenu(row, name, index) {
  let uid = [];
  uid.push(row.uid);
  let uids = { uids: uid };
  switch (name) {
    case '1':
      edit(row);
      break;
    case '2':
      getOtherFrom(row.uid, 'money');
      break;
    case '3':
      giveLevelTime(row.uid);
      break;
    case '4':
      del(row, '清除 【 ' + tenText(row.nickname) + ' 】的会员等级', index, 'user');
      break;
    case '5':
      modalForm(userSetGroup(uids)).then(() => getList());
      break;
    case '6':
      openLabel(row);
      break;
    case '8':
      getOtherFrom(row.uid, 'point');
      break;
    case '99':
      del(row, '解除【 ' + tenText(row.nickname) + ' 】的上级推广人', index, 'tuiguang');
      break;
    default:
      break;
  }
}

function tenText(str) {
  if (str.length > 10) {
    //如果字符长度超过10，后面的字符就变成...可自行调整长度和代替字符
    str = str.substr(0, 10) + '...'; //截取从第一个字符开始，往后取10个字符，剩余的用...代替
  }
  return str;
}

function openLabel(row) {
  is_batch.value = false;
  labelShow.value = true;
  labelActive.value.uid = row.uid;
}

function openSelectLabel() {
  selectLabelShow.value = true;
}

// 赠送会员等级
function giveLevel(id) {
  modalForm(giveLevelApi(id)).then(() => getList(1));
}

// 赠送会员等级
function giveLevelTime(id) {
  modalForm(giveLevelTimeApi(id)).then(() => getList(1));
}

// 删除
function del(row, tit, num, name) {
  let delfromData = {
    title: tit,
    num: num,
    url: name === 'user' ? `user/del_level/${row.uid}` : `distribution/stair/delete_spread/${row.uid}`,
    method: name === 'user' ? 'DELETE' : 'PUT',
    ids: '',
    width: 600,
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

// 清除会员删除成功
function submitModel() {
  getList();
}

// 会员列表
function getList() {
  // if (selectDataLabel.value.length) {
  //   let activeIds = [];
  //   selectDataLabel.value.forEach((item) => {
  //     activeIds.push(item.id);
  //   });
  //   userFrom.value.label_id = activeIds.join(',');
  // }
  userFrom.value.user_type = userFrom.value.user_type || '';
  userFrom.value.status = userFrom.value.status || '';
  userFrom.value.sex = userFrom.value.sex || '';
  userFrom.value.country = userFrom.value.country || '';
  userFrom.value.pay_count = pay_count.value === 'all' ? '' : pay_count.value;
  userFrom.value.user_time_type = user_time_type.value === 'all' ? '' : user_time_type.value;
  userFrom.value.field_key = field_key.value === 'all' ? '' : field_key.value;
  userFrom.value.level = level.value === 'all' ? '' : level.value;
  userFrom.value.group_id = group_id.value === 'all' ? '' : group_id.value;
  userFrom.value.agent_level = agent_level.value === 'all' ? '' : agent_level.value;
  loading.value = true;
  userList(userFrom.value)
    .then(async (res) => {
      let data = res.data;
      userLists.value = data.list;
      // 渠道筛选 Tab 以后端已安装且已启用的渠道模块为准
      if (data.tabs) headeNum.value = data.tabs;
      total.value = data.count;
      loading.value = false;
      nextTick(() => {
        setChecked();
      });
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

// 用户导出
async function exportList() {
  if (ids.value.length) {
    userFrom.value.ids = ids.value;
  }
  userFrom.value.user_type = userFrom.value.user_type || '';
  userFrom.value.status = userFrom.value.status || '';
  userFrom.value.sex = userFrom.value.sex || '';
  userFrom.value.country = userFrom.value.country || '';
  userFrom.value.pay_count = pay_count.value === 'all' ? '' : pay_count.value;
  userFrom.value.user_time_type = user_time_type.value === 'all' ? '' : user_time_type.value;
  userFrom.value.field_key = field_key.value === 'all' ? '' : field_key.value;
  userFrom.value.level = level.value === 'all' ? '' : level.value;
  userFrom.value.group_id = group_id.value === 'all' ? '' : group_id.value;
  userFrom.value.agent_level = agent_level.value === 'all' ? '' : agent_level.value;
  let [th, filekey, data, fileName] = [[], [], [], ''];
  //   let fileName = "";
  let excelData = JSON.parse(JSON.stringify(userFrom.value));
  excelData.page = 1;
  for (let i = 0; i < excelData.page + 1; i++) {
    let lebData = await getExcelData(excelData);
    if (!fileName) fileName = lebData.filename;
    if (!filekey.length) {
      filekey = lebData.fileKey;
    }
    if (!th.length) th = lebData.header;
    if (lebData.export.length) {
      data = data.concat(lebData.export);
      excelData.page++;
    } else {
      exportExcel(th, filekey, fileName, data);
      return;
    }
  }
}

function getExcelData(excelData) {
  return new Promise((resolve, reject) => {
    exportUserList(excelData).then((res) => {
      resolve(res.data);
    });
  });
}

function pageChange() {
  selectionList.value = [];
  getList();
}

// 搜索
function userSearchs() {
  // 清除已选用户
  ids.value = [];
  selectedIds.value = [];
  selectionList.value = [];
  userFrom.value.page = 1;
  getList();
}

// 重置
function reset(name) {
  userFrom.value = {
    label_id: '',
    status: '',
    sex: '',
    country: '',
    isMember: '',
    pay_count_num: ['', ''],
    balance: ['', ''],
    integral: ['', ''],
    pay_count_money: ['', ''],
    user_time_type: '',
    user_time: '',
    before_pay_time: '',
    nickname: '',
    province: '',
    city: '',
    page: 1,
    limit: 15,
    level: '',
    group_id: '',
    agent_level: '',
    field_key: '',
  };
  field_key.value = '';
  level.value = '';
  group_id.value = '';
  agent_level.value = '';
  dataLabel.value = [];
  selectDataLabel.value = [];
  user_time_type.value = '';
  pay_count.value = '';
  timeVal.value = [];
  before_pay_time.value = [];
  selectedIds.value = new Set();
  getList();
}

// 获取编辑表单数据
function getUserFrom(id) {
  getUserInfo(id)
    .then(async (res) => {
      modals.value = true;
      userData.value = res.data;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

// 获取积分余额表单
function getOtherFrom(id, type) {
  modalForm(editOtherApi(id, type)).then(() => getList(1));
}

// 点击发送优惠券
function onSend() {
  if (ids.value.length === 0) {
    ElMessage.warning('请选择要发送优惠券的用户');
  } else {
    sends.value.modals = true;
    sends.value.getList();
  }
}

// 发送图文消息
function onSendPic() {
  if (ids.value.length === 0) {
    ElMessage.warning('请选择要发送图文消息的用户');
  } else {
    modal13.value = true;
  }
}

// 编辑
function edit(row) {
  getUserFrom(row.uid);
}

// 修改成功
function submitFail() {
  // getList();
}

// 排序
function sortChanged(e, props, order) {
  userFrom.value[e.prop] = e.order;
  getList();
}

// 全选和取消全选时触发
function handleSelectAll(selection) {
  let ids = [];
  selection.map((e) => {
    ids.push(e.uid);
  });
  selectedIds.value = ids;
  nextTick(() => {
    //确保dom加载完毕
    setChecked();
  });
}

//  选中某一行
function handleSelectRow(selection, row) {
  let ids = [];
  selection.map((e) => {
    ids.push(e.uid);
  });
  selectedIds.value = ids;
  nextTick(() => {
    //确保dom加载完毕
    setChecked();
  });
}

function setChecked() {
  //将new Set()转化为数组
  ids.value = [...selectedIds.value];
  // 找到绑定的table的ref对应的dom，找到table的objData对象，objData保存的是当前页的数据
  let objData = table.value?.objData;
  if (!objData) return;
  for (let index in objData) {
    if (selectedIds.value.has(objData[index].uid)) {
      objData[index]._isChecked = true;
    }
  }
}
</script>

<style scoped lang="scss">
:deep(.el-tabs__item) {
  height: 54px !important;
  line-height: 54px !important;
}

.picBox {
  display: inline-block;
  cursor: pointer;

  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
    font-size: 24px;
    font-weight: 500;
  }

  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }
}
.fix_footer {
  position: fixed;
  bottom: 0;
  width: -webkit-fill-available;
  background: var(--prev-bg-white);
  padding: 20px 0px;
  box-sizing: border-box;
  z-index: 100;
}
.userFrom {
  :deep(.ivu-form-item-content) {
    margin-left: 0px !important;
  }
}

.userAlert {
  margin-top: 20px;
}

.userI {
  color: var(--prev-color-primary);
  font-style: normal;
}

img {
  height: 36px;
  display: block;
}

.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}

.tabBox_tit {
  width: 60%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}

.modelBox {
  :deep(.ivu-modal-body) {
    padding: 0 16px 16px 16px !important;
  }
}

.vipName {
  color: #dab176;
}

.listbox {
  :deep(.ivu-divider-horizontal) {
    margin: 0 !important;
  }
}

.labelInput {
  width: 250px;
  border: 1px solid #dcdee2;
  padding: 0 15px;
  border-radius: 5px;
  min-height: 30px;
  cursor: pointer;
  font-size: 12px;

  .span {
    color: #c5c8ce;
  }

  .ivu-icon-ios-arrow-down {
    font-size: 14px;
    color: #808695;
  }
}

.demo-drawer-footer {
  width: 100%;
  position: absolute;
  bottom: 0;
  left: 0;
  border-top: 1px solid var(--prev-border-color-lighter);
  padding: 10px 16px;
  text-align: right;
  background: var(--prev-bg-white);
}
</style>
