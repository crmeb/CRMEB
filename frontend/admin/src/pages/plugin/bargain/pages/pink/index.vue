<template>
  <div class="article-manager">
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="formValidate" :label-width="labelWidth" label-position="right" @submit.prevent inline>
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
          <el-form-item label="砍价状态：">
            <el-select
              v-model="formValidate.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option :value="1" label="进行中"></el-option>
              <el-option :value="2" label="已失败"></el-option>
              <el-option :value="3" label="已成功"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-table
        :data="tableList"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="头像" width="80">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="发起用户" min-width="100">
          <template #default="scope">
            <span> {{ scope.row.nickname + ' / ' + scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="开启时间" min-width="110">
          <template #default="scope">
            <span> {{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="砍价商品" min-width="300">
          <template #default="scope">
            <span> {{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="最低价" min-width="60">
          <template #default="scope">
            <span> {{ scope.row.bargain_price_min }}</span>
          </template>
        </el-table-column>
        <el-table-column label="当前价" min-width="60">
          <template #default="scope">
            <span> {{ scope.row.now_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="总砍价次数" min-width="70">
          <template #default="scope">
            <span> {{ scope.row.people_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="剩余砍价次数" min-width="100">
          <template #default="scope">
            <span> {{ scope.row.num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="结束时间" min-width="150">
          <template #default="scope">
            <span> {{ scope.row.datatime }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template #default="scope">
            <el-tag size="default" type="info" v-show="scope.row.status === 1">进行中</el-tag>
            <el-tag size="default" type="danger" v-show="scope.row.status === 2">已失败</el-tag>
            <el-tag size="default" v-show="scope.row.status === 3">已成功</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="100">
          <template #default="scope">
            <a v-db-click @click="Info(scope.row)">查看详情</a>
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

    <!-- 详情模态框-->
    <el-dialog v-model="modals" class="tableBox" title="查看详情" :close-on-click-modal="false" width="720px">
      <el-table :data="tabList3" v-loading="loading2" empty-text="暂无数据" highlight-current-row max-height="600">
        <el-table-column label="用户ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户头像" min-width="100">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户名称" min-width="100">
          <template #default="scope">
            <span> {{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="砍价金额" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="砍价时间" min-width="130">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import { formatDate } from '@/utils/validate';
import { bargainUserListApi, bargainUserInfoApi } from '@/api/marketing';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'bargainPink' });

const { isMobile } = storeToRefs(useMediaStore());

const labelWidth = computed(() => (isMobile.value ? undefined : '80px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

const cardLists = ref([]);
const modals = ref(false);
const loading = ref(false);
const formValidate = ref({
  status: '',
  data: '',
  page: 1,
  limit: 15,
});
const tableList = ref([]);
const total = ref(0);
const timeVal = ref([]);
const loading2 = ref(false);
const tabList3 = ref([]);
const rows = ref({});

// 原 filters：转为普通 function
function formatDateFilter(time) {
  if (time !== 0) {
    let date = new Date(time * 1000);
    return formatDate(date, 'yyyy-MM-dd hh:mm');
  }
}

getList();

// 查看详情
function Info(row) {
  modals.value = true;
  rows.value = row;
  bargainUserInfoApi(row.id)
    .then(async (res) => {
      let data = res.data;
      tabList3.value = data.list;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}
// 具体日期
function onchangeTime(e) {
  timeVal.value = e || [];
  formValidate.value.data = timeVal.value[0] ? (timeVal.value ? timeVal.value.join('-') : '') : '';
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
// 列表
function getList() {
  loading.value = true;
  formValidate.value.status = formValidate.value.status || '';
  bargainUserListApi(formValidate.value)
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
}
</script>

<style lang="scss" scoped>
:deep(.ivu-tag-cyan .ivu-tag-text) {
  color: #19be6b !important;
}
.ivu-tag-cyan {
  background: rgba(25, 190, 170, 0.1);
  border-color: #19be6b !important;
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
</style>
