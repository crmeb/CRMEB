<template>
  <div ref="rootRef" class="coupon-select" :class="{ 'is-embed': isEmbed }">
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
      class="mt15 coupon-select__table"
      :data="couponList"
      :row-key="getRowKey"
      highlight-current-row
      no-userFrom-text="暂无数据"
      no-filtered-userFrom-text="暂无筛选结果"
      :max-height="tableMaxHeight"
      @selection-change="changeCheckbox"
    >
      <el-table-column type="selection" width="42" :selectable="checkSelectable" :reserve-selection="true" />
      <el-table-column label="ID" width="54" prop="id" />
      <el-table-column label="优惠券名称" min-width="96" show-overflow-tooltip>
        <template #default="scope">
          <span>{{ scope.row.title }}</span>
        </template>
      </el-table-column>
      <el-table-column label="类型" width="64">
        <template #default="scope">
          <span v-if="scope.row.type === 1">品类券</span>
          <span v-else-if="scope.row.type === 2">商品券</span>
          <span v-else-if="scope.row.type === 3">会员券</span>
          <span v-else>通用券</span>
        </template>
      </el-table-column>
      <el-table-column label="面值" width="64">
        <template #default="scope">
          <span>{{ scope.row.coupon_price }}元</span>
        </template>
      </el-table-column>
      <el-table-column label="门槛" width="72">
        <template #default="scope">
          <span>{{ scope.row.use_min_price == '0.00' ? '无门槛' : `满${scope.row.use_min_price}` }}</span>
        </template>
      </el-table-column>
      <el-table-column label="数量" width="88">
        <template #default="scope">
          <span v-if="scope.row.is_permanent">不限量</span>
          <span v-else class="cell-nowrap">{{ scope.row.total_count }}/{{ scope.row.remain_count }}</span>
        </template>
      </el-table-column>
      <el-table-column label="领取期限" width="178">
        <template #default="scope">
          <span v-if="scope.row.start_time || scope.row.end_time" class="cell-nowrap">
            {{ formatDay(scope.row.start_time) }}~{{ formatDay(scope.row.end_time) }}
          </span>
          <span v-else>不限时</span>
        </template>
      </el-table-column>
      <el-table-column label="使用有效期" width="178">
        <template #default="scope">
          <!-- 有固定使用起止时间用日期（仅年月日）；否则按领取后天数展示 -->
          <span v-if="scope.row.start_use_time" class="cell-nowrap">
            {{ formatDay(scope.row.start_use_time) }}~{{ formatDay(scope.row.end_use_time) }}
          </span>
          <span v-else>{{ Number(scope.row.coupon_time || 0) }}天</span>
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
    <!-- 嵌入 SchemaCoupon 弹窗时由外层页脚确认，避免重复按钮与高度浪费 -->
    <div v-if="!isEmbed" class="footer">
      <el-button v-db-click @click="cancel">取 消</el-button>
      <el-button type="primary" v-db-click @click="confirm"
        >确认选择{{ selectedRows.length ? `（${selectedRows.length}）` : '' }}</el-button
      >
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { ElMessage } from 'element-plus';
import { useRoute } from 'vue-router';
import { releasedListApi } from '@/api/marketing';

defineOptions({ name: 'CouponSelect' });

const route = useRoute();

/** 是否嵌入 SchemaCoupon 弹窗（由 iframe query embed=1 标识） */
const isEmbed = computed(() => String(route.query.embed || '') === '1');

/** 表头约 40px + 单行约 48px，最多展示 6 行后出滚动条 */
const TABLE_HEADER_HEIGHT = 40;
const TABLE_ROW_HEIGHT = 48;
const TABLE_MAX_ROWS = 6;
const tableMaxHeight = TABLE_HEADER_HEIGHT + TABLE_ROW_HEIGHT * TABLE_MAX_ROWS;

const loading = ref(false);
const total = ref(0);
const couponList = ref([]);
const couponTable = ref(null);
const rootRef = ref(null);
const tableFrom = reactive({
  coupon_title: '',
  // 支持 URL receive_type：新人礼传 3（系统赠送）；缺省不限，保持历史行为
  receive_type: route.query.receive_type !== undefined && route.query.receive_type !== '' ? route.query.receive_type : '',
  page: 1,
  limit: 15,
});
/** 已选行完整数据（[{id,title,coupon_price,use_min_price}]，经 helper.set 回传展示券名） */
const selectedRows = ref([]);

/**
 * 时间戳格式化为仅年月日（兼容秒/毫秒），用于期限列完整单行展示
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

/** 通知外层弹窗按内容高度收缩 iframe，避免大片空白 */
function reportHeight() {
  if (!isEmbed.value || !window.parent || window.parent === window) return;
  nextTick(() => {
    const el = rootRef.value;
    if (!el) return;
    const height = Math.ceil(el.getBoundingClientRect().height);
    window.parent.postMessage({ type: 'schema-coupon-resize', height }, '*');
  });
}

function getList() {
  loading.value = true;
  releasedListApi(tableFrom)
    .then((res) => {
      couponList.value = res.data.list;
      total.value = res.data.count;
      nextTick(() => {
        setCheckedRows();
        reportHeight();
      });
      loading.value = false;
    })
    .catch(() => {
      loading.value = false;
      reportHeight();
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

watch(loading, () => reportHeight());

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
  window.addEventListener('resize', reportHeight);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', reportHeight);
});
</script>

<style lang="scss" scoped>
.coupon-select {
  padding: 15px 15px 0;
  background-color: var(--prev-bg-white);

  &.is-embed {
    padding: 4px 4px 0;
    min-height: 0;
  }
}
.coupon-select__table {
  width: 100%;
}
.cell-nowrap {
  white-space: nowrap;
}
.page {
  margin-top: 8px;
}
.footer {
  padding: 15px 0;
  text-align: right;
}
</style>
