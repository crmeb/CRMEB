<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidateRef"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="时间选择：">
            <date-range-picker
              clearable
              v-model="timeVal"
              :editable="false"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY/MM/DD"
              class="mr20"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="搜索：" label-for="status">
            <el-input
              clearable
              placeholder="请输入姓名、电话、UID"
              v-model="formValidate.nickname"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="cardLists.length >= 0"></cards-data>
    <el-card :bordered="false" shadow="never">
      <el-button v-auth="['export-userAgent']" class="export" v-db-click @click="exports">导出</el-button>
      <el-table
        ref="selection"
        :data="tableList"
        class="mt14"
        v-loading="loading"
        empty-text="暂无数据"
        highlight-current-row
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="头像" min-width="90">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.headimgurl ? scope.row.headimgurl : moren" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户信息" width="150">
          <template #default="scope">
            <div class="name">
              <div class="item">昵称:{{ scope.row.nickname }}</div>
              <div class="item">姓名:{{ scope.row.real_name }}</div>
              <div class="item">电话:{{ scope.row.phone }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="分销等级" min-width="120">
          <template #default="scope">
            <div>{{ scope.row.agentLevel ? scope.row.agentLevel.name : '--' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="推广用户数量" min-width="120">
          <template #default="scope">
            <span>{{ scope.row.spread_count }}</span>
          </template>
        </el-table-column>
        <el-table-column label="推广订单数量" min-width="120">
          <template #default="scope">
            <div>{{ scope.row.spread_order.order_count }}</div>
          </template>
        </el-table-column>
        <el-table-column label="推广订单金额" min-width="120">
          <template #default="scope">
            <div>{{ scope.row.spread_order.order_price || '0.00' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="佣金总金额" min-width="120">
          <template #default="scope">
            <div>{{ scope.row.brokerage_money }}</div>
          </template>
        </el-table-column>
        <el-table-column label="已提现金额" min-width="120">
          <template #default="scope">
            <div>{{ scope.row.extract_count_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="提现次数" min-width="120">
          <template #default="scope">
            <div>{{ scope.row.extract_count_num }}</div>
          </template>
        </el-table-column>
        <el-table-column label="未提现金额" min-width="120">
          <template #default="scope">
            <div>{{ scope.row.new_money }}</div>
          </template>
        </el-table-column>
        <el-table-column label="上级推广人" min-width="120">
          <template #default="scope">
            <div>{{ scope.row.spread_name }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template #default="scope">
            <a v-db-click @click="promoters(scope.row, 'man')">推广人</a>
            <el-divider direction="vertical"></el-divider>
            <el-dropdown @command="changeMenu(scope.row, $event, scope.$index)" :transfer="true">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
              <template #dropdown
                ><el-dropdown-menu>
                  <el-dropdown-item command="1">推广订单</el-dropdown-item>
                  <el-dropdown-item command="2">推广二维码</el-dropdown-item>
                  <el-dropdown-item command="3">修改上级推广人</el-dropdown-item>
                  <el-dropdown-item command="4" v-if="scope.row.spread_uid">清除上级推广人</el-dropdown-item>
                  <el-dropdown-item command="5">取消推广资格</el-dropdown-item>
                  <el-dropdown-item command="6">修改分销等级</el-dropdown-item>
                </el-dropdown-menu></template
              >
            </el-dropdown>
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
    <!-- 推广人列表-->
    <promoters-list ref="promotersLists"></promoters-list>
    <!-- 推广二维码-->
    <el-dialog v-model="modals" title="推广二维码" :close-on-click-modal="false" width="540px">
      <div class="acea-row row-around" v-loading="spinShow">
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" v-if="code_src"><img v-lazy="code_src" /></div>
          <span class="QRpic_sp1 mt10" v-db-click @click="getWeChat">公众号推广二维码</span>
        </div>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" v-if="code_xcx"><img v-lazy="code_xcx" /></div>
          <span class="QRpic_sp2 mt10" v-db-click @click="getXcx">小程序推广二维码</span>
        </div>
        <div class="acea-row row-column-around row-between-wrapper">
          <div class="QRpic" v-if="code_h5"><img v-lazy="code_h5" /></div>
          <span class="QRpic_sp2 mt10" v-db-click @click="getH5">H5推广二维码</span>
        </div>
      </div>
    </el-dialog>
    <!--修改推广人-->
    <el-dialog v-model="promoterShow" title="修改推广人" width="540px" :show-close="true">
      <el-form ref="formInlineRef" :model="formInline" label-width="100px" @submit.prevent>
        <el-form-item label="用户头像：" prop="image">
          <div class="picBox" v-db-click @click="customer">
            <div class="pictrue" v-if="formInline.image">
              <img v-lazy="formInline.image" />
            </div>
            <div class="upLoad acea-row row-center-wrapper" v-else>
              <i class="el-icon-picture-outline" style="font-size: 24px"></i>
            </div>
          </div>
        </el-form-item>
      </el-form>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="cancel('formInline')">取 消</el-button>
          <el-button type="primary" v-db-click @click="putSend('formInline')">提交</el-button>
        </span></template
      >
    </el-dialog>
    <el-dialog v-model="customerShow" title="请选择商城用户" :show-close="true" width="1000px">
      <customerInfo v-if="customerShow" @imageObject="imageObject"></customerInfo>
    </el-dialog>
  </div>
</template>

<script setup>
import moren from '@/assets/images/moren.jpg';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import cardsData from '@/components/cards/cards';
import searchFrom from '@/components/publicSearchFrom';
import { useMediaStore } from '@/store/module/media';
import {
  agentListApi,
  statisticsApi,
  lookCodeApi,
  lookxcxCodeApi,
  lookh5CodeApi,
  userAgentApi,
  agentSpreadApi,
} from './api';
import promotersList from './handle/promotersList';
import customerInfo from '@/components/customerInfo';
import { membershipDataAddApi } from '@/api/membershipLevel';
import modalForm from '@/utils/modalForm';
import { modalSure } from '@/utils/public';

defineOptions({ name: 'agentManage' });

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const customerShow = ref(false);
const promoterShow = ref(false);
const modals = ref(false);
const spinShow = ref(false);
const rows = ref({});
const formValidate = ref({
  nickname: '',
  data: '',
  page: 1,
  limit: 15,
});
const date = ref('all');
const total = ref(0);
const cardLists = ref([]);
const loading = ref(false);
const tableList = ref([]);
const timeVal = ref([]);
const code_src = ref('');
const code_xcx = ref('');
const code_h5 = ref('');
const formInline = ref({
  uid: 0,
  spread_uid: 0,
  image: '',
});

const formValidateRef = ref(null);
const formInlineRef = ref(null);
const selection = ref(null);
const promotersLists = ref(null);

// 提交
function putSend(name) {
  formInlineRef.value.validate((valid) => {
    if (valid) {
      if (!formInline.value.spread_uid) {
        return ElMessage.error('请上传用户');
      }
      agentSpreadApi(formInline.value)
        .then((res) => {
          promoterShow.value = false;
          ElMessage.success(res.msg);
          getList();
          formInlineRef.value.resetFields();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    }
  });
}
// 导出
function exports() {
  let data = {
    data: formValidate.value.data,
    nickname: formValidate.value.nickname,
  };
  userAgentApi(data)
    .then((res) => {
      location.href = res.data[0];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 操作
function changeMenu(row, name, index) {
  switch (name) {
    case '1':
      promoters(row, 'order'); //推广人订单
      break;
    case '2':
      spreadQR(row); //推广方式二维码
      break;
    case '3':
      editS(row); //修改上级推广人
      break;
    case '4': //清除上级推广人
      del_parent(row, '清除【 ' + row.nickname + ' 】的上级推广人', index);
      break;
    case '5': //取消推广资格
      del_agent(row, '取消【 ' + row.nickname + ' 】的推广资格', index);
      break;
    case '6': //修改推广等级
      modalForm(membershipDataAddApi({ uid: row.uid }, '/distribution/get_level_form')).then(() => getList());
      break;
    default:
      break;
  }
}
function editS(row) {
  promoterShow.value = true;
  formInline.value.uid = row.uid;
}
function customer() {
  customerShow.value = true;
}
function imageObject(e) {
  customerShow.value = false;
  formInline.value.spread_uid = e.uid;
  formInline.value.image = e.image;
}
// 清除上级关系
function del_parent(rows2, titile, num) {
  let delfromDatap = {
    title: titile,
    num: num,
    url: `distribution/stair/delete_spread/${rows2.uid}`,
    method: 'PUT',
    ids: '',
  };
  modalSure(delfromDatap)
    .then((res) => {
      ElMessage.success(res.msg);
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 取消自己推广资格
function del_agent(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `distribution/stair/delete_system_spread/${row.uid}`,
    method: 'PUT',
    ids: '',
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
function edit(row) {
  promoterShow.value = true;
  formInline.value.uid = row.uid;
}
function cancel(name) {
  promoterShow.value = false;
  formInlineRef.value.resetFields();
}
// 推广人列表 订单
function promoters(row, tit) {
  promotersLists.value.modals = true;
  promotersLists.value.getList(row, tit);
}
// 统计
function getStatistics() {
  let data = {
    nickname: formValidate.value.nickname,
    data: formValidate.value.data,
  };
  statisticsApi(data)
    .then(async (res) => {
      let data = res.data;
      cardLists.value = data.res;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.value.data = timeVal.value ? timeVal.value.join('-') : '';
  formValidate.value.page = 1;
  if (!e[0]) {
    formValidate.value.data = '';
  }
  getList();
  getStatistics();
}
// 选择时间
function selectChange(tab) {
  formValidate.value.page = 1;
  formValidate.value.data = tab;
  timeVal.value = [];
  getList();
  getStatistics();
}
// 列表
function getList() {
  loading.value = true;
  agentListApi(formValidate.value)
    .then(async (res) => {
      let data = res.data;
      tableList.value = data.list;
      total.value = res.data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 表格搜索
function userSearchs() {
  formValidate.value.page = 1;
  getList();
  getStatistics();
}
// 二维码
function spreadQR(row) {
  modals.value = true;
  rows.value = row;
  getWeChat();
  getXcx();
  getH5();
}
// 公众号推广二维码
function getWeChat() {
  spinShow.value = true;
  let data = {
    uid: rows.value.uid,
    action: 'wechant_code',
  };
  lookCodeApi(data)
    .then(async (res) => {
      let data = res.data;
      code_src.value = data.code_src;
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
// 小程序推广二维码
function getXcx() {
  spinShow.value = true;
  let data = {
    uid: rows.value.uid,
  };
  lookxcxCodeApi(data)
    .then(async (res) => {
      let data = res.data;
      code_xcx.value = data.code_src;
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}
function getH5() {
  spinShow.value = true;
  let data = {
    uid: rows.value.uid,
  };
  lookh5CodeApi(data)
    .then(async (res) => {
      let data = res.data;
      code_h5.value = data.code_src;
      spinShow.value = false;
    })
    .catch((res) => {
      spinShow.value = false;
      ElMessage.error(res.msg);
    });
}

getList();
getStatistics();
</script>
<style lang="scss" scoped>
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
  .iconfont {
    color: #898989;
  }
}
.QRpic {
  width: 180px;
  height: 180px;

  img {
    width: 100%;
    height: 100%;
  }
}
.QRpic_sp1 {
  font-size: 13px;
  color: #19be6b;
  cursor: pointer;
}
.QRpic_sp2 {
  font-size: 13px;
  color: #2d8cf0;
  cursor: pointer;
}

img {
  height: 36px;
  display: block;
}
.ivu-mt .name .item {
  margin: 3px 0;
}
.tabform {
  margin-bottom: 10px;
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
}
.ivu-form-item {
  margin-bottom: 10px;
}

/* .ivu-mt :deep(.ivu-table-header) */
/* border-top:1px dashed #ddd!important */
</style>
