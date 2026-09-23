<template>
  <div class="coupon-select">
    <el-form :inline="true" @submit.prevent>
      <el-form-item label="优惠券名称：">
        <el-input
          v-model="tableFrom.coupon_title"
          placeholder="请输入优惠券名称"
          clearable
          class="form_content_width"
          @keyup.enter="userSearchs"
        ></el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
      </el-form-item>
    </el-form>
    <el-table
      ref="couponTable"
      v-loading="loading"
      class="mt15"
      :data="couponList"
      :row-key="getRowKey"
      highlight-current-row
      no-userFrom-text="暂无数据"
      no-filtered-userFrom-text="暂无筛选结果"
      height="420"
      @selection-change="changeCheckbox"
    >
      <el-table-column type="selection" width="55" :selectable="checkSelectable" :reserve-selection="true" />
      <el-table-column label="ID" width="70" prop="id" />
      <el-table-column label="优惠券名称" min-width="140">
        <template #default="scope">
          <span>{{ scope.row.title }}</span>
        </template>
      </el-table-column>
      <el-table-column label="优惠券类型" min-width="90">
        <template #default="scope">
          <span v-if="scope.row.type === 1">品类券</span>
          <span v-else-if="scope.row.type === 2">商品券</span>
          <span v-else-if="scope.row.type === 3">会员券</span>
          <span v-else>通用券</span>
        </template>
      </el-table-column>
      <el-table-column label="面值" min-width="80">
        <template #default="scope">
          <span>{{ scope.row.coupon_price }}元</span>
        </template>
      </el-table-column>
      <el-table-column label="最低消费" min-width="90">
        <template #default="scope">
          <span>{{ scope.row.use_min_price == '0.00' ? '无门槛' : `满${scope.row.use_min_price}元` }}</span>
        </template>
      </el-table-column>
      <el-table-column label="发布数量" min-width="130">
        <template #default="scope">
          <span v-if="scope.row.is_permanent">不限量</span>
          <div v-else>
            <span>发布：{{ scope.row.total_count }}</span>
            <span class="ml10">剩余：{{ scope.row.remain_count }}</span>
          </div>
        </template>
      </el-table-column>
      <el-table-column label="领取期限" min-width="150">
        <template #default="scope">
          <div v-if="scope.row.start_time || scope.row.end_time">
            {{ $filters.formatDate(scope.row.start_time) }} ~ {{ $filters.formatDate(scope.row.end_time) }}
          </div>
          <span v-else>不限时</span>
        </template>
      </el-table-column>
      <el-table-column label="使用有效期" min-width="150">
        <template #default="scope">
          <span>{{ scope.row.use_time }}</span>
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
    <div class="footer">
      <el-button v-db-click @click="cancel">取 消</el-button>
      <el-button type="primary" v-db-click @click="confirm"
        >确认选择{{ selectedRows.length ? `（${selectedRows.length}）` : '' }}</el-button
      >
    </div>
  </div>
</template>

<script setup>
import { nextTick, onMounted, reactive, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { useRoute } from 'vue-router';
import { releasedListApi } from '@/api/marketing';

defineOptions({ name: 'CouponSelect' });

const route = useRoute();

const loading = ref(false);
const total = ref(0);
const couponList = ref([]);
const couponTable = ref(null);
const tableFrom = reactive({
  coupon_title: '',
  receive_type: '',
  page: 1,
  limit: 15,
});
/** 已选行完整数据（[{id,title,coupon_price,use_min_price}]，经 helper.set 回传展示券名） */
const selectedRows = ref([]);

/**
 * 余量充足且在领取期内的券才允许勾选（与后端发券查询 getGiveCoupon 条件一致）
 */
function checkSelectable(row) {
  const hasStock = row.is_permanent || row.remain_count > 0;
  const now = Math.floor(Date.now() / 1000);
  const inPeriod =
    (!row.start_time && !row.end_time) ||
    (!row.start_time && row.end_time > now) ||
    (row.start_time < now && !row.end_time) ||
    (row.start_time < now && row.end_time > now);
  return hasStock && inPeriod;
}

function getRowKey(row) {
  return row.id;
}

function getList() {
  loading.value = true;
  releasedListApi(tableFrom)
    .then((res) => {
      couponList.value = res.data.list;
      total.value = res.data.count;
      nextTick(() => setCheckedRows());
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
    });
}

/**
 * 回显当前表单已选中的券（跨页勾选由 reserve-selection 保持）
 */
function setCheckedRows() {
  if (!selectedRows.value.length || !couponTable.value) return;
  const ids = selectedRows.value.map((item) => Number(item.id ?? item));
  couponList.value.forEach((row) => {
    couponTable.value.toggleRowSelection(row, ids.includes(row.id));
  });
}

function changeCheckbox(selection) {
  selectedRows.value = selection.map((item) => ({
    id: item.id,
    title: item.title,
    coupon_price: item.coupon_price,
    use_min_price: item.use_min_price,
  }));
}

function userSearchs() {
  tableFrom.page = 1;
  getList();
}

function cancel() {
  if (window.form_create_helper) {
    window.form_create_helper.close(route.query.fodder);
  }
}

/**
 * 确认选择：回传选中行完整数据并关闭弹窗
 * （弹窗页脚"确定"经 helper.onOk 注册同样触发本方法）
 */
function confirm() {
  if (!window.form_create_helper) {
    ElMessage.error('请在表单弹窗中使用本页面');
    return;
  }
  window.form_create_helper.set(route.query.fodder, selectedRows.value);
  window.form_create_helper.close(route.query.fodder);
}

/**
 * 等待宿主注入 form_create_helper（iframe onload 后注入，与本页面 mounted 存在竞态）
 * @param {number} timeout 最长等待毫秒
 * @returns {Promise<object|null>}
 */
function waitHelper(timeout = 5000) {
  return new Promise((resolve) => {
    const started = Date.now();
    const timer = setInterval(() => {
      if (window.form_create_helper) {
        clearInterval(timer);
        resolve(window.form_create_helper);
      } else if (Date.now() - started > timeout) {
        clearInterval(timer);
        resolve(null);
      }
    }, 50);
  });
}

onMounted(async () => {
  const helper = await waitHelper();
  if (helper) {
    // 回显当前表单已选值（对象数组或历史纯 id 数组）
    const current = helper.get(route.query.fodder);
    if (Array.isArray(current)) {
      selectedRows.value = current.map((item) =>
        typeof item === 'object' && item !== null ? item : { id: Number(item) },
      );
    }
    // 注册弹窗页脚"确定"回调
    helper.onOk(confirm);
  }
  getList();
});
</script>

<style lang="scss" scoped>
.coupon-select {
  padding: 15px 15px 0;
  background-color: #fff;
}
.footer {
  padding: 15px 0;
  text-align: right;
}
</style>
