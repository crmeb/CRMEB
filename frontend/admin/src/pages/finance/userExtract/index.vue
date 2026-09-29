<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formRef"
          :model="formValidate"
          :label-width="labelWidth"
          label-position="right"
          inline
          @submit.prevent
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
          <el-form-item label="提现状态：">
            <el-select
              clearable
              v-model="formValidate.status"
              placeholder="请选择状态"
              @change="selChange"
              class="form_content_width"
            >
              <el-option
                v-for="(item, index) in treeData.withdrawal"
                :key="index"
                :value="item.value"
                :label="item.title"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="提现方式：">
            <el-select
              clearable
              v-model="formValidate.extract_type"
              placeholder="请选择状态"
              @change="selChange"
              class="form_content_width"
            >
              <el-option
                v-for="(item, index) in treeData.payment"
                :key="index"
                :value="item.value"
                :label="item.title"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：">
            <el-input
              clearable
              placeholder="微信昵称/姓名/支付宝账号/银行卡号"
              v-model="formValidate.nireid"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="selChange">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <cards-data :cardLists="cardLists" v-if="extractStatistics"></cards-data>
    <el-card :bordered="false" shadow="never">
      <el-table ref="table" :data="tabList" v-loading="loading" empty-text="暂无数据" class="mt14">
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户信息" min-width="130">
          <template #default="scope">
            <div>
              用户昵称: {{ scope.row.nickname }} <br />
              用户id:{{ scope.row.uid }}
            </div>
          </template>
        </el-table-column>
        <el-table-column label="提现金额" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.extract_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="提现手续费" min-width="100">
          <template #default="scope">
            <div>{{ scope.row.extract_fee }}</div>
          </template>
        </el-table-column>
        <el-table-column label="到账金额" min-width="100">
          <template #default="scope">
            <div class="f-price">{{ scope.row.receive_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="提现方式" min-width="130">
          <template #default="scope">
            <div class="type" v-if="scope.row.extract_type === 'bank'">
              <div class="item">姓名:{{ scope.row.real_name }}</div>
              <div class="item">银行卡号:{{ scope.row.bank_code }}</div>
              <div class="item">银行开户地址:{{ scope.row.bank_address }}</div>
            </div>
            <div class="type" v-if="scope.row.extract_type === 'weixin'">
              <div class="item">昵称:{{ scope.row.nickname }}</div>
              <div class="item">微信号:{{ scope.row.wechat }}</div>
            </div>
            <div class="type" v-if="scope.row.extract_type === 'alipay'">
              <div class="item">姓名:{{ scope.row.real_name }}</div>
              <div class="item">支付宝号:{{ scope.row.alipay_code }}</div>
            </div>
            <div class="type" v-if="scope.row.extract_type === 'balance'">
              <div class="item">姓名:{{ scope.row.real_name }}</div>
              <div class="item">提现方式：佣金转入余额</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="收款码" min-width="90">
          <template #default="scope">
            <div
              class="tabBox_img"
              v-viewer
              v-if="scope.row.extract_type === 'weixin' || scope.row.extract_type === 'alipay'"
            >
              <img v-lazy="scope.row.qrcode_url" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="申请时间" min-width="130">
          <template #default="scope">
            <span>{{ $filters.formatDate(scope.row.add_time) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.mark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" min-width="130">
          <template #default="scope">
            <div class="status" v-if="scope.row.status === 0">
              <div class="statusVal">申请中</div>
              <div></div>
            </div>
            <div class="statusVal" v-if="scope.row.status === 1">提现通过</div>
            <div class="statusVal" v-if="scope.row.status === -1">
              提现未通过<br />未通过原因：{{ scope.row.fail_msg }}
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <!-- v-if 须在插槽作用域内判断：scope 是插槽 prop，写在 template 外层时
               不存在（Vue2 slot-scope 可行，Vue3 #default 编译后 v-if 落在作用域外） -->
          <template #default="scope">
            <template v-if="scope.row.status == 0">
              <a href="javascript:void(0);" v-db-click @click="edit(scope.row)">编辑</a>
              <el-divider direction="vertical"></el-divider>
              <a class="item" v-db-click @click="adopt(scope.row, '审核通过', scope.$index)">通过</a>
              <el-divider direction="vertical"></el-divider>
              <a class="item" v-db-click @click="invalid(scope.row)">驳回</a>
            </template>
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

    <!-- 编辑表单-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
    <!-- 拒绝通过-->
    <el-dialog v-model="modals" title="未通过原因" :close-on-click-modal="false" width="540px">
      <el-input v-model="fail_msg.message" type="textarea" :rows="4" placeholder="请输入未通过原因" />
      <template #footer
        ><div>
          <el-button type="primary" v-db-click @click="oks">确定</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>
<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import { storeToRefs } from 'pinia';
import { useMediaStore } from '@/store/module/media';
import cardsData from '@/components/cards/cards';
import editFrom from '@/components/from/from';
import { cashListApi, cashEditApi, refuseApi } from '@/api/finance';
import { modalSure } from '@/utils/public';
import { authLapse } from '@/utils/authLapse';
import Setting from '@/setting';

defineOptions({ name: 'cashApply' });

const route = useRoute();
const { isMobile } = storeToRefs(useMediaStore());

const edits = ref(null);
const images = ref(['1.jpg', '2.jpg']);
const modal_loading = ref(false);
const fail_msg = ref({
  message: '输入信息不完整或有误!',
});
const modals = ref(false);
const total = ref(0);
const cardLists = ref([]);
const loading = ref(false);
const tabList = ref([]);
const treeData = ref({
  withdrawal: [
    {
      title: '全部',
      value: '',
    },
    {
      title: '未通过',
      value: -1,
    },
    {
      title: '申请中',
      value: 0,
    },
    {
      title: '已通过',
      value: 1,
    },
  ],
  payment: [
    {
      title: '全部',
      value: '',
    },
    {
      title: '微信',
      value: 'wx',
    },
    {
      title: '支付宝',
      value: 'alipay',
    },
    {
      title: '银行卡',
      value: 'bank',
    },
  ],
});
const formValidate = ref({
  status: '',
  extract_type: '',
  nireid: '',
  data: '',
  page: 1,
  limit: 15,
});
const extractStatistics = ref({});
const timeVal = ref([]);
const FromData = ref(null);
const extractId = ref(0);

watch(
  () => route.fullPath,
  () => {
    if (route.fullPath === Setting.routePre + '/finance/user_extract/index?status=0') {
      getPath();
    }
  },
);

const labelWidth = computed(() => {
  return isMobile.value ? undefined : '80px';
});
const labelPosition = computed(() => {
  return isMobile.value ? 'top' : 'right';
});

onMounted(() => {
  if (route.fullPath === Setting.routePre + '/finance/user_extract/index?status=0') {
    getPath();
  } else {
    getList();
  }
});

function getPath() {
  formValidate.value.page = 1;
  formValidate.value.status = parseInt(route.query.status);
  getList();
}
// 无效
function invalid(row) {
  extractId.value = row.id;
  modals.value = true;
}
// 确定
function oks() {
  modal_loading.value = true;
  refuseApi(extractId.value, fail_msg.value)
    .then(async (res) => {
      ElMessage.success(res.msg);
      modal_loading.value = false;
      modals.value = false;
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 通过
function adopt(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `finance/extract/adopt/${row.id}`,
    method: 'put',
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
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.value.data = timeVal.value ? timeVal.value.join('-') : '';
  formValidate.value.page = 1;
  getList();
}
// 选择时间
function selectChange(tab) {
  formValidate.value.page = 1;
  formValidate.value.data = tab;
  timeVal.value = [];
  getList();
}
// 选择
function selChange() {
  formValidate.value.page = 1;
  getList();
}
// 列表
function getList() {
  loading.value = true;
  cashListApi(formValidate.value)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list.list;
      total.value = data.list.count;
      extractStatistics.value = data.extract_statistics;
      cardLists.value = [
        {
          col: 6,
          count: extractStatistics.value.brokerage_count,
          name: '佣金总金额',
          className: 'iconyuezhifujine',
        },
        { col: 6, count: extractStatistics.value.price, name: '待提现金额', className: 'iconfufeihuiyuanjine' },
        { col: 6, count: extractStatistics.value.priced, name: '已提现金额', className: 'iconzhifuyongjinjine' },
        {
          col: 6,
          count: extractStatistics.value.brokerage_not,
          name: '未提现金额',
          className: 'iconshangpintuikuanjine',
        },
      ];
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 编辑
function edit(row) {
  cashEditApi(row.id)
    .then(async (res) => {
      if (res.data.status === false) {
        return authLapse(res.data);
      }
      FromData.value = res.data;
      edits.value.modals = true;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 编辑提交成功
function submitFail() {
  // getList();
}
</script>
<style lang="scss" scoped>
.ivu-mt .type .item {
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
.status :deep(.item ~ .item) {
  margin-left: 6px;
}
.status :deep(.statusVal) {
  margin-bottom: 7px;
}
/*.ivu-mt :deep(.ivu-table-header*/
/*    border-top:1px dashed #ddd!important*/
:deep(.type) {
  padding: 3px 0;
  box-sizing: border-box;
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
.z-price {
  color: red;
}
.f-price {
  color: green;
}
</style>
