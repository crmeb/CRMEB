<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="tableFromRef"
          :model="tableFrom"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.prevent
          inline
        >
          <el-form-item label="优惠券名称：" label-for="coupon_title">
            <el-input
              v-model="tableFrom.coupon_title"
              placeholder="请输入优惠券名称"
              class="form_content_width"
              show-word-limit
              clearable
            />
          </el-form-item>
          <el-form-item label="优惠券类型：" label-for="coupon_type">
            <el-select
              v-model="tableFrom.coupon_type"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="0" label="通用券"></el-option>
              <el-option value="1" label="品类券"></el-option>
              <el-option value="2" label="商品券"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="是否开启：" label-for="status">
            <el-select
              v-model="tableFrom.status"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="1" label="正常"></el-option>
              <el-option value="0" label="未开启"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="发放方式：" label-for="status">
            <el-select
              v-model="receive_type"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="all" label="全部"></el-option>
              <el-option value="1" label="用户领取"></el-option>
              <el-option value="2" label="系统赠送"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt mt16">
      <el-button v-auth="['admin-marketing-store_coupon-add']" type="primary" v-db-click @click="add" class="action-row"
        >添加优惠券</el-button
      >
      <el-table
        :data="tableList"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template #default="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠券名称" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.coupon_title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="优惠券类型" min-width="80">
          <template #default="scope">
            <span v-if="scope.row.type === 1">品类券</span>
            <span v-else-if="scope.row.type === 2">商品券</span>
            <span v-else-if="scope.row.type === 3">会员券</span>
            <span v-else>通用券</span>
          </template>
        </el-table-column>
        <el-table-column label="面值" min-width="100">
          <template #default="scope">
            <span>{{ scope.row.coupon_price }}</span>
          </template>
        </el-table-column>
        <el-table-column label="领取方式" min-width="150">
          <template #default="scope">
            <span v-if="scope.row.receive_type === 1 || scope.row.receive_type === 4">用户领取</span>
            <span v-else>系统赠送</span>
          </template>
        </el-table-column>
        <el-table-column label="领取日期" min-width="100">
          <template #default="scope">
            <div v-if="scope.row.start_time">
              {{ $filters.formatDate(scope.row.start_time) }} - {{ $filters.formatDate(scope.row.end_time) }}
            </div>
            <span v-else>不限时</span>
          </template>
        </el-table-column>
        <el-table-column label="使用时间" width="200">
          <template #default="scope">
            <span v-if="scope.row.start_use_time" class="cell-nowrap">
              {{ formatDay(scope.row.start_use_time) }}~{{ formatDay(scope.row.end_use_time) }}
            </span>
            <span v-else>{{ scope.row.coupon_time }}天</span>
          </template>
        </el-table-column>
        <el-table-column label="发布数量" min-width="100">
          <template #default="scope">
            <span v-if="scope.row.is_permanent">不限量</span>
            <div v-else>
              <span class="fa">发布：{{ scope.row.total_count }}</span>
              <span class="sheng">剩余：{{ scope.row.remain_count }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="是否开启" min-width="100">
          <template #default="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              size="large"
              @change="openChange(scope.row)"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="200">
          <template #default="scope">
            <a v-db-click @click="receive(scope.row)">领取记录</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="copy(scope.row)">复制</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="couponDel(scope.row, '删除发布的优惠券', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          v-model:page="tableFrom.page"
          v-model:limit="tableFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <!-- 领取记录 -->
    <el-dialog v-model="modals2" title="领取记录" :close-on-click-modal="false" width="720px">
      <el-table
        :data="receiveList"
        ref="table"
        v-loading="loading2"
        highlight-current-row
        height="500"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户名" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="用户头像" min-width="150">
          <template #default="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.avatar" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="领取时间" min-width="150">
          <template #default="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onActivated, getCurrentInstance } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import {
  releasedListApi,
  releasedissueLogApi,
  releaseStatusApi,
  delCouponReleased,
  couponStatusApi,
} from '@/api/marketing';
import { modalSure } from '@/utils/public';
import { useMediaStore } from '@/store/module/media';
import Setting from '@/setting';

defineOptions({ name: 'marketing_storeCouponIssue' });

const { proxy } = getCurrentInstance();
const router = useRouter();
const mediaStore = useMediaStore();

const tableFromRef = ref(null);

const modals2 = ref(false);
const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const loading = ref(false);

const tableFrom = reactive({
  status: '',
  coupon_type: '',
  coupon_title: '',
  receive_type: '',
  page: 1,
  limit: 15,
});
const receive_type = ref('');
const tableList = ref([]);
const total = ref(0);
const FromData = ref(null);
const receiveList = ref([]);
const loading2 = ref(false);
const total2 = ref(0);
const receiveFrom = reactive({
  page: 1,
  limit: 15,
});
const rows = ref({});

onActivated(() => {
  getList();
});

/**
 * 时间戳格式化为仅年月日（YYYY/MM/DD），兼容秒/毫秒
 * @param {number|string} data
 * @returns {string}
 */
function formatDay(data) {
  if (data === null || data === undefined || data === '' || Number(data) === 0) {
    return '';
  }
  let ts = Number(data);
  if (!Number.isFinite(ts)) {
    return '';
  }
  if (ts > 0 && ts < 1e12) {
    ts *= 1000;
  }
  const date = new Date(ts);
  if (Number.isNaN(date.getTime())) {
    return '';
  }
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}/${m}/${d}`;
}

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '90px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// 失效
let delfromData;
function couponInvalid(row, tit, num) {
  delfromData = {
    title: tit,
    num: num,
    url: `marketing/coupon/status/${row.id}`,
    method: 'PUT',
    ids: '',
  };
  proxy.$refs.modelSure.modals = true;
}
// 领取记录
function receive(row) {
  modals2.value = true;
  rows.value = row;
  getReceivelist(row);
}
function getReceivelist(row) {
  loading2.value = true;
  releasedissueLogApi(row.id, receiveFrom)
    .then(async (res) => {
      let data = res.data;
      receiveList.value = data.list;
      total2.value = res.data.count;
      loading2.value = false;
    })
    .catch((res) => {
      loading2.value = false;
      ElMessage.error(res.msg);
    });
}
// 领取记录改变分页
function receivePageChange(index) {
  receiveFrom.page = index;
  getReceivelist(rows.value);
}
// 删除
function couponDel(row, tit, num) {
  let delfromData = {
    title: tit,
    num: num,
    url: `marketing/coupon/released/${row.id}`,
    method: 'DELETE',
    ids: '',
  };
  modalSure(delfromData)
    .then((res) => {
      ElMessage.success(res.msg);
      tableList.value.splice(num, 1);
      total.value--;
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}
// 列表
function getList() {
  loading.value = true;
  tableFrom.receive_type = receive_type.value === 'all' ? '' : receive_type.value;
  tableFrom.status = tableFrom.status || '';
  releasedListApi(tableFrom)
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
  tableFrom.page = 1;
  getList();
}
// 添加优惠券
function add() {
  router.push({ path: Setting.routePre + '/marketing/store_coupon_issue/create' });
}
// 复制
function copy(data) {
  router.push({
    path: Setting.routePre + `/marketing/store_coupon_issue/create/${data.id}`,
  });
}
// 复制
function edit(data) {
  router.push({
    path: Setting.routePre + `/marketing/store_coupon_issue/create/${data.id}/1`,
  });
}
// 是否开启
function openChange(data) {
  couponStatusApi(data).then(() => getList());
}
</script>

<style lang="scss" scoped>
.cell-nowrap {
  white-space: nowrap;
}
.fa {
  color: #0a6aa1;
  display: block;
}
.sheng {
  color: #ff0000;
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
</style>
