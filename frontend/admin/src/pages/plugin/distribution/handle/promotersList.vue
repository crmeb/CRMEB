<template>
  <div>
    <el-dialog
      v-model="modals"
      :title="listTitle === 'man' ? '统计推广人列表' : '推广订单'"
      :close-on-click-modal="false"
      width="1000px"
      @closed="onCancel"
    >
      <div class="table_box">
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
              :editable="false"
              @change="onchangeTime"
              v-model="timeVal"
              value-format="YYYY/MM/DD"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="用户类型：">
            <el-select v-model="formValidate.type" clearable class="form_content_width">
              <el-option
                v-for="(item, i) in listTitle === 'man' ? fromList.fromTxt2 : fromList.fromTxt3"
                :key="i"
                :value="item.val"
                :label="item.text"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="搜索：" v-if="listTitle === 'man'">
            <el-input
              clearable
              placeholder="请输入请姓名、电话、UID"
              v-model="formValidate.nickname"
              class="form_content_width"
            ></el-input>
          </el-form-item>
          <el-form-item label="订单号：" v-if="listTitle === 'order'">
            <el-input
              clearable
              placeholder="请输入请订单号"
              v-model="formValidate.order_id"
              class="form_content_width"
            ></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
      <el-table
        ref="selection"
        :data="tabList"
        v-loading="loading"
        empty-text="暂无数据"
        highlight-current-row
        max-height="400"
      >
        <template v-if="listTitle === 'man'">
          <el-table-column label="UID" width="80">
            <template #default="scope">
              <span>{{ scope.row.uid }}</span>
            </template>
          </el-table-column>
          <el-table-column label="头像" min-width="90">
            <template #default="scope">
              <div class="tabBox_img" v-viewer>
                <img v-lazy="scope.row.avatar ? scope.row.avatar : moren" />
              </div>
            </template>
          </el-table-column>
          <el-table-column label="用户信息" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.nickname }}</span>
            </template>
          </el-table-column>
          <el-table-column label="是否推广员" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.promoter_name }}</span>
            </template>
          </el-table-column>
          <el-table-column label="推广人数" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.spread_count }}</span>
            </template>
          </el-table-column>
          <el-table-column label="订单数" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.order_count }}</span>
            </template>
          </el-table-column>
          <el-table-column label="绑定时间" min-width="130">
            <template #default="scope">
              <span>{{ $filters.formatDate(scope.row.spread_time) }}</span>
            </template>
          </el-table-column>
        </template>
        <template v-else>
          <el-table-column label="订单ID" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.order_id }}</span>
            </template>
          </el-table-column>
          <el-table-column label="用户信息" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.user_info }}</span>
            </template>
          </el-table-column>
          <el-table-column label="时间" min-width="130">
            <template #default="scope">
              <span>{{ scope.row._add_time }}</span>
            </template>
          </el-table-column>
          <el-table-column label="返佣金额" min-width="130">
            <template #default="scope">
              <span>{{ scope.row.brokerage_price || 0 }}</span>
            </template>
          </el-table-column>
          <el-table-column label="事业部返佣金额" min-width="130" v-if="rowsList.division_type == 1">
            <template #default="scope">
              <span>{{ scope.row.division_brokerage || 0 }}</span>
            </template>
          </el-table-column>
          <el-table-column label="代理商返佣金额" min-width="130" v-if="rowsList.division_type == 2">
            <template #default="scope">
              <span>{{ scope.row.agent_brokerage || 0 }}</span>
            </template>
          </el-table-column>
          <el-table-column label="员工返佣金额" min-width="130" v-if="rowsList.division_type == 3">
            <template #default="scope">
              <span>{{ scope.row.staff_brokerage || 0 }}</span>
            </template>
          </el-table-column>
        </template>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="formValidate.page"
          v-model:limit="formValidate.limit"
          @pagination="pageChange"
        />
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import moren from '@/assets/images/moren.jpg';
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { useMediaStore } from '@/store/module/media';
import { stairListApi } from '../api';

defineOptions({ name: 'promotersList' });

const { isMobile } = storeToRefs(useMediaStore());
const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const modals = ref(false);
const fromList = ref({
  title: '选择时间',
  custom: true,
  fromTxt: [
    { text: '全部', val: '' },
    { text: '今天', val: 'today' },
    { text: '昨天', val: 'yesterday' },
    { text: '最近7天', val: 'lately7' },
    { text: '最近30天', val: 'lately30' },
    { text: '本月', val: 'month' },
    { text: '本年', val: 'year' },
  ],
  fromTxt2: [
    { text: '全部', val: '' },
    { text: '一级推广人', val: 1 },
    { text: '二级推广人', val: 2 },
  ],
  fromTxt3: [
    { text: '全部', val: '' },
    { text: '一级推广人订单', val: 1 },
    { text: '二级推广人订单', val: 2 },
    { text: '事业部推广订单', val: 3 },
    { text: '代理商推广订单', val: 4 },
  ],
});
const formValidate = ref({
  limit: 15,
  page: 1,
  nickname: '',
  data: '',
  type: '',
  order_id: '',
  uid: 0,
});
const loading = ref(false);
const tabList = ref([]);
const total = ref(0);
const timeVal = ref([]);
const columns4 = ref([]);
const listTitle = ref('');
const rowsList = ref({
  division_type: 0,
});

const formValidateRef = ref(null);
const selection = ref(null);

function onCancel() {
  formValidate.value = {
    limit: 15,
    page: 1,
    nickname: '',
    data: '',
    type: '',
    order_id: '',
    uid: 0,
  };
  timeVal.value = [];
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e;
  formValidate.value.data = timeVal.value ? timeVal.value.join('-') : '';
  getList(rowsList.value, listTitle.value);
}
// 选择时间
function selectChange(tab) {
  formValidate.value.data = tab;
  timeVal.value = [];
  getList(rowsList.value, listTitle.value);
}
// 列表
function getList(row, tit) {
  listTitle.value = tit;
  rowsList.value = row;
  loading.value = true;
  let url = '';
  if (listTitle.value === 'man') {
    url = 'distribution/stair';
  } else {
    url = 'distribution/stair/order';
  }
  formValidate.value.uid = row.uid;
  stairListApi(url, formValidate.value)
    .then(async (res) => {
      let data = res.data;
      tabList.value = data.list;
      total.value = data.count;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      tabList.value = [];
      ElMessage.error(res.msg);
    });
}
function pageChange() {
  getList(rowsList.value, listTitle.value);
}
// 搜索
function userSearchs() {
  formValidate.value.page = 1;
  getList(rowsList.value, listTitle.value);
}

// 页面通过 $refs.promotersLists 控制弹窗并调用 getList
defineExpose({ modals, getList });
</script>

<style scoped></style>
