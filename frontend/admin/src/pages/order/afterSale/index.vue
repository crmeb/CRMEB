<template>
  <div>
    <el-card :bordered="false" shadow="never" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form :model="searchForm" label-width="80px" label-position="right" inline @submit.prevent>
          <el-form-item label="售后状态：">
            <el-select
              v-model="searchForm.status"
              clearable
              @change="getList(1)"
              placeholder="全部"
              class="form_content_width"
            >
              <el-option label="全部" value="" />
              <el-option v-for="item in statusOptions" :key="item.key" :value="item.key" :label="item.label" />
            </el-select>
          </el-form-item>
          <el-form-item label="售后类型：">
            <el-select
              v-model="searchForm.type"
              clearable
              @change="getList(1)"
              placeholder="全部"
              class="form_content_width"
            >
              <el-option label="全部" value="" />
              <el-option v-for="item in typeOptions" :key="item.key" :value="item.key" :label="item.label" />
            </el-select>
          </el-form-item>
          <el-form-item label="产品类型：">
            <el-select
              v-model="searchForm.product_type"
              clearable
              @change="getList(1)"
              placeholder="全部"
              class="form_content_width"
            >
              <el-option label="全部" value="" />
              <el-option v-for="item in productTypeOptions" :key="item.key" :value="item.key" :label="item.label" />
            </el-select>
          </el-form-item>
          <el-form-item label="订单大类：">
            <el-select
              v-model="searchForm.order_category"
              clearable
              @change="getList(1)"
              placeholder="全部"
              class="form_content_width"
            >
              <el-option label="全部" value="" />
              <el-option v-for="item in categoryOptions" :key="item.key" :value="item.key" :label="item.label" />
            </el-select>
          </el-form-item>
          <el-form-item label="申请时间：">
            <date-range-picker
              width="250px"
              clearable
              v-model="timeVal"
              @change="onchangeTime"
              format="YYYY/MM/DD"
              value-format="YYYY-MM-DD"
            ></date-range-picker>
          </el-form-item>
          <el-form-item label="单号搜索：">
            <el-input
              clearable
              v-model="searchForm.keyword"
              placeholder="请输入售后单号/订单号"
              class="form_content_width"
              @keyup.enter="getList(1)"
            >
              <template #prepend
                ><el-select v-model="searchForm.search_field" style="width: 110px">
                  <el-option value="after_sale_no" label="售后单号"></el-option>
                  <el-option value="order_no" label="订单号"></el-option> </el-select
              ></template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="getList(1)">查询</el-button>
            <el-button v-db-click @click="handleReset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="mt16" :body-style="{ padding: '0 0 20px' }">
      <el-tabs v-model="statusTab" @tab-change="handleStatusTabClick">
        <el-tab-pane label="全部" name=""></el-tab-pane>
        <el-tab-pane v-for="item in statusOptions" :key="item.key" :label="item.label" :name="item.key"></el-tab-pane>
      </el-tabs>
      <div class="table-content">
        <el-table :data="list" ref="table" v-loading="loading" empty-text="暂无数据" class="afterSaleData">
          <el-table-column label="售后单号" min-width="180">
            <template #default="scope">
              <span>{{ scope.row.after_sale_no }}</span>
            </template>
          </el-table-column>
          <el-table-column label="订单号" min-width="160">
            <template #default="scope">
              <span>{{ scope.row.order_no }}</span>
            </template>
          </el-table-column>
          <el-table-column label="售后类型" width="110">
            <template #default="scope">
              <el-tag :type="getTypeClass(scope.row.type)" effect="plain">{{
                scope.row.type_text || scope.row.type
              }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="售后状态" width="110">
            <template #default="scope">
              <el-tag :type="getStatusClass(scope.row.status)">{{ scope.row.status_text || scope.row.status }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="产品类型" width="110">
            <template #default="scope">
              <span>{{ getProductTypeLabel(scope.row.product_type) }}</span>
            </template>
          </el-table-column>
          <el-table-column label="退款金额" min-width="120">
            <template #default="scope">
              <span class="price">¥{{ scope.row.refund_price || '0.00' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="申请时间" min-width="160">
            <template #default="scope">
              <span>{{ scope.row.add_time ? formatDate(scope.row.add_time) : '--' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right" width="140">
            <template #default="scope">
              <a v-db-click @click="viewDetail(scope.row)">详情</a>
              <template v-if="scope.row.status === 'pending_audit'">
                <el-divider direction="vertical" />
                <a v-db-click @click="goAudit(scope.row)" v-auth="['admin-order-afterSale-audit']">审核</a>
              </template>
              <template v-if="canRemark(scope.row.status)">
                <el-divider direction="vertical" />
                <a v-db-click @click="editRemark(scope.row)" v-auth="['admin-order-afterSale-remark']">备注</a>
              </template>
            </template>
          </el-table-column>
        </el-table>
        <div class="acea-row row-right page">
          <pagination
            v-if="total"
            :total="total"
            v-model:page="page.page"
            v-model:limit="page.limit"
            @pagination="getList"
          />
        </div>
      </div>
    </el-card>
    <!-- 备注弹窗 -->
    <el-dialog v-model="remarkModal" title="售后管理备注" width="500px">
      <el-form :model="remarkForm" label-width="80px">
        <el-form-item label="备注内容">
          <el-input
            type="textarea"
            v-model="remarkForm.remark"
            :rows="4"
            placeholder="请输入备注内容"
            maxlength="500"
            show-word-limit
          ></el-input>
        </el-form-item>
      </el-form>
      <template #footer
        ><div>
          <el-button @click="remarkModal = false">取消</el-button>
          <el-button type="primary" @click="saveRemark">保存</el-button>
        </div></template
      >
    </el-dialog>
    <!-- 审核弹窗 -->
    <el-dialog
      v-model="auditModal"
      :title="auditForm.result === 'pass' ? '审核通过' : '审核拒绝'"
      width="500px"
      :close-on-click-modal="false"
      @close="resetAuditForm"
    >
      <el-form :model="auditForm" label-width="100px">
        <el-form-item label="审核结果">
          <el-radio-group v-model="auditForm.result">
            <el-radio label="pass" value="pass">审核通过</el-radio>
            <el-radio label="reject" value="reject">审核拒绝</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="拒绝原因" v-if="auditForm.result === 'reject'" required>
          <el-input type="textarea" v-model="auditForm.reject_reason" :rows="3" placeholder="请填写拒绝原因"></el-input>
        </el-form-item>
        <el-form-item label="审核备注">
          <el-input
            type="textarea"
            v-model="auditForm.audit_remark"
            :rows="3"
            placeholder="审核备注（选填）"
          ></el-input>
        </el-form-item>
      </el-form>
      <template #footer
        ><div>
          <el-button @click="auditModal = false">取消</el-button>
          <el-button type="primary" v-db-click @click="submitAudit">确定</el-button>
        </div></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import { afterSaleList, auditAfterSale, remarkAfterSale } from '@/api/afterSale';
import { orderCenterFilterOptions } from '@/api/orderCenter';
import setting from '@/setting';

defineOptions({ name: 'afterSaleList' });

const router = useRouter();
const routePre = setting.routePre;

const searchForm = ref({
  status: '',
  type: '',
  product_type: '',
  order_category: '',
  keyword: '',
  search_field: 'after_sale_no',
  start_time: '',
  end_time: '',
});
const timeVal = ref([]);
const list = ref([]);
const total = ref(0);
const page = ref({ page: 1, limit: 15 });
const loading = ref(false);
const statusTab = ref('');
const remarkModal = ref(false);
const remarkForm = ref({ id: 0, remark: '' });
const auditModal = ref(false);
const auditId = ref(0);
const auditForm = ref({ result: 'pass', reject_reason: '', audit_remark: '' });
// 售后状态选项（与后端 STATUS_TEXT_MAP 对齐）
const statusOptions = [
  { key: 'pending_audit', label: '待审核' },
  { key: 'audit_passed', label: '审核通过' },
  { key: 'audit_rejected', label: '审核拒绝' },
  { key: 'canceled', label: '已取消' },
  { key: 'refunding', label: '退款中' },
  { key: 'refunded', label: '已退款' },
  { key: 'pending_return', label: '待退货' },
  { key: 'receiving', label: '待收货' },
  { key: 'repairing', label: '维修中' },
  { key: 'resending', label: '补发中' },
  { key: 'completed', label: '已完成' },
];
// 售后类型选项（与后端 TYPE_TEXT_MAP 对齐）
const typeOptions = [
  { key: 'refund_only', label: '仅退款' },
  { key: 'return_refund', label: '退货退款' },
  { key: 'exchange', label: '换货' },
  { key: 'resend', label: '补发' },
  { key: 'repair', label: '维修' },
];
// 产品类型选项（与订单中台一致）
const productTypeOptions = ref([]);
// 订单大类选项
const categoryOptions = [
  { key: 'goods', label: '商品订单' },
  { key: 'points', label: '积分订单' },
  { key: 'member', label: '会员订单' },
  { key: 'recharge', label: '充值订单' },
];

/**
 * 获取售后列表
 * @param {Number} res 传入 1 时重置到第一页
 */
function getList(res) {
  page.value.page = res === 1 ? 1 : page.value.page;
  loading.value = true;
  // 状态 Tab 优先于筛选下拉
  const status = statusTab.value || searchForm.value.status;
  // 单号搜索映射到对应字段
  const params = {
    status,
    type: searchForm.value.type,
    product_type: searchForm.value.product_type,
    order_category: searchForm.value.order_category,
    start_time: searchForm.value.start_time,
    end_time: searchForm.value.end_time,
    page: page.value.page,
    limit: page.value.limit,
  };
  if (searchForm.value.keyword) {
    params[searchForm.value.search_field] = searchForm.value.keyword;
  }
  afterSaleList(params)
    .then((res) => {
      list.value = res.data.list || [];
      total.value = res.data.count || 0;
      loading.value = false;
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

/**
 * 产品类型选项与订单中台保持一致，由产品类型注册中心动态提供
 */
function loadFilterOptions() {
  orderCenterFilterOptions()
    .then((res) => {
      productTypeOptions.value = res.data?.product_types || [];
    })
    .catch(() => {
      productTypeOptions.value = [];
    });
}

/**
 * 申请时间范围变更
 */
function onchangeTime(e) {
  timeVal.value = e || [];
  if (timeVal.value.length === 2) {
    // 转为时间戳，与后端 add_time 字段一致
    searchForm.value.start_time = Math.floor(new Date(timeVal.value[0] + ' 00:00:00').getTime() / 1000);
    searchForm.value.end_time = Math.floor(new Date(timeVal.value[1] + ' 23:59:59').getTime() / 1000);
  } else {
    searchForm.value.start_time = '';
    searchForm.value.end_time = '';
  }
  getList(1);
}

/**
 * 重置筛选条件
 */
function handleReset() {
  searchForm.value = {
    status: '',
    type: '',
    product_type: '',
    order_category: '',
    keyword: '',
    search_field: 'after_sale_no',
    start_time: '',
    end_time: '',
  };
  timeVal.value = [];
  statusTab.value = '';
  getList(1);
}

/**
 * 状态 Tab 切换
 */
function handleStatusTabClick() {
  getList(1);
}

/**
 * 跳转详情页
 */
function viewDetail(row) {
  router.push({ path: routePre + '/order/afterSale/detail/' + row.id });
}

/**
 * 打开审核弹窗
 */
function goAudit(row) {
  auditId.value = row.id;
  auditForm.value = { result: 'pass', reject_reason: '', audit_remark: '' };
  auditModal.value = true;
}

/**
 * 重置审核表单
 */
function resetAuditForm() {
  auditId.value = 0;
  auditForm.value = { result: 'pass', reject_reason: '', audit_remark: '' };
}

/**
 * 提交审核
 */
function submitAudit() {
  if (auditForm.value.result === 'reject' && !auditForm.value.reject_reason) {
    ElMessage.warning('请填写拒绝原因');
    return;
  }
  auditAfterSale(auditId.value, auditForm.value)
    .then((res) => {
      ElMessage.success(res.msg);
      auditModal.value = false;
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

/**
 * 打开备注弹窗
 */
function editRemark(row) {
  remarkForm.value = { id: row.id, remark: row.remark || '' };
  remarkModal.value = true;
}

/**
 * 保存备注
 */
function saveRemark() {
  remarkAfterSale(remarkForm.value.id, { remark: remarkForm.value.remark })
    .then((res) => {
      ElMessage.success(res.msg);
      remarkModal.value = false;
      getList();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

/**
 * 终态不可备注（已完成/已取消/审核拒绝）
 */
function canRemark(status) {
  return !['completed', 'canceled', 'audit_rejected'].includes(status);
}

/**
 * 产品类型文案
 */
function getProductTypeLabel(type) {
  const item = productTypeOptions.value.find((opt) => opt.key === type);
  return item ? item.label : type || '--';
}

/**
 * 售后类型 tag 颜色
 */
function getTypeClass(type) {
  const map = {
    refund_only: 'warning',
    return_refund: 'danger',
    exchange: 'primary',
    resend: 'success',
    repair: 'info',
  };
  return map[type] || 'info';
}

/**
 * 售后状态 tag 颜色
 */
function getStatusClass(status) {
  const map = {
    pending_audit: 'warning',
    audit_passed: 'primary',
    audit_rejected: 'danger',
    canceled: 'info',
    refunding: 'warning',
    refunded: 'success',
    pending_return: 'warning',
    receiving: 'primary',
    repairing: 'primary',
    resending: 'primary',
    completed: 'success',
  };
  return map[status] || 'info';
}

/**
 * 格式化时间戳（秒）
 */
function formatDate(timestamp) {
  const d = new Date(timestamp * 1000);
  return `${d.getFullYear()}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d
    .getDate()
    .toString()
    .padStart(2, '0')} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d
    .getSeconds()
    .toString()
    .padStart(2, '0')}`;
}

onMounted(() => {
  loadFilterOptions();
  getList();
});
</script>

<style lang="scss" scoped>
.form_content_width {
  width: 250px;
}

.table-content {
  padding: 12px 20px 0;
}

.price {
  color: #ed4014;
  font-weight: bold;
}

:deep(.el-tabs__header) {
  margin: 0;
  padding: 0 20px;
}
</style>
