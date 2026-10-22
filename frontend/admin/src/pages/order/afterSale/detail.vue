<template>
  <div v-loading="loading">
    <div v-if="detail" class="after-sale-detail">
      <!-- 头部信息 -->
      <el-card :bordered="false" shadow="never" class="head-card">
        <div class="head">
          <div class="full">
            <div class="order_icon" :class="getTypeClass(detail.type)">
              <i class="el-icon-s-promotion"></i>
            </div>
            <div class="text">
              <div class="title">{{ detail.type_text }} · {{ detail.status_text }}</div>
              <div class="sub">售后单号：{{ detail.after_sale_no }} ｜ 订单号：{{ detail.order_no }}</div>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">售后类型</div>
              <div>
                <el-tag :type="getTypeClass(detail.type)" effect="plain">{{ detail.type_text }}</el-tag>
              </div>
            </li>
            <li class="item">
              <div class="title">售后状态</div>
              <div>
                <el-tag :type="getStatusClass(detail.status)">{{ detail.status_text }}</el-tag>
              </div>
            </li>
            <li class="item">
              <div class="title">申请退款金额</div>
              <div class="value1">¥{{ detail.refund_price || '0.00' }}</div>
            </li>
            <li class="item" v-if="detail.actual_refund_price > 0">
              <div class="title">实际退款金额</div>
              <div class="value1">¥{{ detail.actual_refund_price || '0.00' }}</div>
            </li>
            <li class="item">
              <div class="title">申请时间</div>
              <div>{{ detail.add_time ? formatDate(detail.add_time) : '--' }}</div>
            </li>
          </ul>
        </div>
      </el-card>

      <el-tabs type="border-card" v-model="activeTab" class="mt16">
        <!-- 处理时间线 -->
        <el-tab-pane label="处理进度" name="timeline">
          <div class="tab-content">
            <el-timeline v-if="detail.timeline && detail.timeline.length">
              <el-timeline-item
                v-for="(item, index) in detail.timeline"
                :key="index"
                :timestamp="item.time ? formatDate(item.time) : '待处理'"
                :color="item.done ? '#52c41a' : '#d9d9d9'"
                placement="top"
              >
                {{ item.label }}
              </el-timeline-item>
            </el-timeline>
            <el-empty v-else description="暂无进度数据" :image-size="80"></el-empty>
          </div>
        </el-tab-pane>

        <!-- 售后详情信息 -->
        <el-tab-pane label="售后信息" name="info">
          <div class="tab-content">
            <el-descriptions :column="2" border size="default">
              <el-descriptions-item label="售后单号">{{ detail.after_sale_no }}</el-descriptions-item>
              <el-descriptions-item label="订单号">
                <a v-db-click @click="goOrderDetail">{{ detail.order_no }}</a>
              </el-descriptions-item>
              <el-descriptions-item label="售后类型">{{ detail.type_text }}</el-descriptions-item>
              <el-descriptions-item label="售后状态">{{ detail.status_text }}</el-descriptions-item>
              <el-descriptions-item label="产品类型">{{
                getProductTypeLabel(detail.product_type)
              }}</el-descriptions-item>
              <el-descriptions-item label="订单大类">{{
                getCategoryLabel(detail.order_category)
              }}</el-descriptions-item>
              <el-descriptions-item label="申请退款金额"
                ><span class="price">¥{{ detail.refund_price || '0.00' }}</span></el-descriptions-item
              >
              <el-descriptions-item label="实际退款金额"
                ><span class="price">¥{{ detail.actual_refund_price || '0.00' }}</span></el-descriptions-item
              >
              <el-descriptions-item label="退还积分" v-if="detail.refund_integral > 0">{{
                detail.refund_integral
              }}</el-descriptions-item>
              <el-descriptions-item label="退货运费" v-if="detail.return_postage > 0"
                >¥{{ detail.return_postage }}</el-descriptions-item
              >
              <el-descriptions-item label="退款方式" v-if="detail.refund_type">{{
                getRefundTypeLabel(detail.refund_type)
              }}</el-descriptions-item>
              <el-descriptions-item label="退款流水号" v-if="detail.refund_trade_no">{{
                detail.refund_trade_no
              }}</el-descriptions-item>
              <el-descriptions-item label="售后前订单状态" v-if="detail.before_status_text">{{
                detail.before_status_text
              }}</el-descriptions-item>
              <el-descriptions-item label="申请时间" :span="2">{{
                detail.add_time ? formatDate(detail.add_time) : '--'
              }}</el-descriptions-item>
              <el-descriptions-item label="申请原因" :span="2">{{ detail.reason || '--' }}</el-descriptions-item>
              <el-descriptions-item
                label="凭证图片"
                :span="2"
                v-if="detail.evidence_images && detail.evidence_images.length"
              >
                <div class="image-list">
                  <el-image
                    v-for="(img, i) in detail.evidence_images"
                    :key="i"
                    :src="img"
                    :preview-src-list="detail.evidence_images"
                    fit="cover"
                    class="evidence-img"
                  ></el-image>
                </div>
              </el-descriptions-item>
              <el-descriptions-item label="拒绝原因" :span="2" v-if="detail.reject_reason">
                <span class="danger-text">{{ detail.reject_reason }}</span>
              </el-descriptions-item>
            </el-descriptions>

            <!-- 退货物流 -->
            <div class="section-title" v-if="hasReturnDelivery">退货物流</div>
            <el-descriptions v-if="hasReturnDelivery" :column="2" border size="default">
              <el-descriptions-item label="快递公司">{{ detail.return_delivery.type || '--' }}</el-descriptions-item>
              <el-descriptions-item label="快递单号">
                {{ detail.return_delivery.no || '--' }}
                <el-button
                  v-if="detail.return_delivery.no"
                  type="text"
                  @click="openLogistics('return')"
                  style="margin-left: 8px"
                  >查看物流</el-button
                >
              </el-descriptions-item>
              <el-descriptions-item
                label="退货凭证"
                :span="2"
                v-if="detail.return_delivery.images && detail.return_delivery.images.length"
              >
                <div class="image-list">
                  <el-image
                    v-for="(img, i) in detail.return_delivery.images"
                    :key="i"
                    :src="img"
                    :preview-src-list="detail.return_delivery.images"
                    fit="cover"
                    class="evidence-img"
                  ></el-image>
                </div>
              </el-descriptions-item>
            </el-descriptions>

            <!-- 补发物流 -->
            <div class="section-title" v-if="hasResendDelivery">补发物流</div>
            <el-descriptions v-if="hasResendDelivery" :column="2" border size="default">
              <el-descriptions-item label="快递公司">{{ detail.resend_delivery.type || '--' }}</el-descriptions-item>
              <el-descriptions-item label="快递单号">
                {{ detail.resend_delivery.no || '--' }}
                <el-button
                  v-if="detail.resend_delivery.no"
                  type="text"
                  @click="openLogistics('resend')"
                  style="margin-left: 8px"
                  >查看物流</el-button
                >
              </el-descriptions-item>
            </el-descriptions>

            <!-- 审核信息 -->
            <div class="section-title" v-if="detail.audit_time > 0">审核信息</div>
            <el-descriptions v-if="detail.audit_time > 0" :column="2" border size="default">
              <el-descriptions-item label="审核结果">{{
                detail.status === 'audit_rejected' ? '审核拒绝' : '审核通过'
              }}</el-descriptions-item>
              <el-descriptions-item label="审核时间">{{ formatDate(detail.audit_time) }}</el-descriptions-item>
              <el-descriptions-item label="审核备注" :span="2">{{ detail.audit_remark || '--' }}</el-descriptions-item>
            </el-descriptions>

            <!-- 维修信息 -->
            <div class="section-title" v-if="detail.repair_remark">维修记录</div>
            <el-descriptions v-if="detail.repair_remark" :column="1" border size="default">
              <el-descriptions-item label="维修备注">{{ detail.repair_remark }}</el-descriptions-item>
            </el-descriptions>
          </div>
        </el-tab-pane>

        <!-- 商品快照 -->
        <el-tab-pane label="商品信息" name="product">
          <div class="tab-content">
            <el-table v-if="productList.length" :data="productList" border>
              <el-table-column label="商品图片" width="90">
                <template #default="scope">
                  <el-image v-if="scope.row.image" :src="scope.row.image" fit="cover" class="product-img"></el-image>
                  <span v-else>--</span>
                </template>
              </el-table-column>
              <el-table-column label="商品名称" prop="name" min-width="200"></el-table-column>
              <el-table-column label="规格" prop="sku" min-width="120"></el-table-column>
              <el-table-column label="数量" prop="num" width="80"></el-table-column>
              <el-table-column label="单价" width="100">
                <template #default="scope">¥{{ scope.row.price || '0.00' }}</template>
              </el-table-column>
            </el-table>
            <el-empty v-else description="暂无商品快照" :image-size="80"></el-empty>
          </div>
        </el-tab-pane>

        <!-- 管理备注 -->
        <el-tab-pane label="管理备注" name="remark">
          <div class="tab-content">
            <el-input
              type="textarea"
              v-model="remarkText"
              :rows="6"
              placeholder="请输入管理备注内容"
              maxlength="500"
              show-word-limit
            ></el-input>
            <div class="mt16">
              <el-button type="primary" v-db-click @click="saveRemark" v-auth="['admin-order-afterSale-remark']"
                >保存备注</el-button
              >
            </div>
            <div class="remark-history mt16" v-if="detail.remark">
              <div class="section-title">当前备注</div>
              <div class="remark-content">{{ detail.remark }}</div>
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>

      <!-- 底部操作区 -->
      <el-card :bordered="false" shadow="never" class="mt16 action-card">
        <div class="action-bar">
          <div class="action-tip">
            <i class="el-icon-info"></i>
            <span>当前状态：{{ detail.status_text }}</span>
          </div>
          <div class="action-buttons">
            <!-- 待审核：审核通过 / 审核拒绝 -->
            <template v-if="detail.status === 'pending_audit'">
              <el-button type="primary" v-db-click @click="openAudit('pass')" v-auth="['admin-order-afterSale-audit']"
                >审核通过</el-button
              >
              <el-button type="danger" v-db-click @click="openAudit('reject')" v-auth="['admin-order-afterSale-audit']"
                >审核拒绝</el-button
              >
            </template>
            <!-- 仅退款审核通过：执行退款 -->
            <el-button
              v-if="canRefund"
              type="warning"
              v-db-click
              @click="openRefund"
              v-auth="['admin-order-afterSale-refund']"
              >执行退款</el-button
            >
            <!-- 待收货（退货退款/换货）：确认收货 -->
            <el-button
              v-if="canReceive"
              type="primary"
              v-db-click
              @click="handleReceive"
              v-auth="['admin-order-afterSale-receive']"
              >确认收货</el-button
            >
            <!-- 待收货（维修）：开始维修 -->
            <el-button
              v-if="canStartRepair"
              type="primary"
              v-db-click
              @click="handleStartRepair"
              v-auth="['admin-order-afterSale-start_repair']"
              >开始维修</el-button
            >
            <!-- 维修中：维修完成 -->
            <el-button
              v-if="detail.status === 'repairing'"
              type="success"
              v-db-click
              @click="openRepairComplete"
              v-auth="['admin-order-afterSale-repair_complete']"
              >维修完成</el-button
            >
            <!-- 补发商品（补发/换货收货后/维修完成）：填写补发物流 -->
            <el-button
              v-if="canResend"
              type="primary"
              v-db-click
              @click="openResend"
              v-auth="['admin-order-afterSale-resend']"
              >补发商品</el-button
            >
            <el-button v-db-click @click="goBack">返回列表</el-button>
          </div>
        </div>
      </el-card>
    </div>
    <el-empty v-else-if="!loading" description="售后单不存在或已删除" class="empty-tip"></el-empty>

    <!-- 审核弹窗 -->
    <el-dialog
      v-model="auditModal"
      :title="auditForm.result === 'pass' ? '审核通过' : '审核拒绝'"
      width="500px"
      @close="auditForm = { result: 'pass', reject_reason: '', audit_remark: '' }"
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
          <el-button type="primary" @click="submitAudit">确定</el-button>
        </div></template
      >
    </el-dialog>

    <!-- 退款弹窗 -->
    <el-dialog
      v-model="refundModal"
      title="执行退款"
      width="500px"
      @close="refundForm = { amount: 0, refund_type: 'original', refund_trade_no: '' }"
    >
      <el-form :model="refundForm" label-width="100px">
        <el-form-item label="可退金额">
          <span class="price">¥{{ detail.refund_price || '0.00' }}</span>
        </el-form-item>
        <el-form-item label="退款金额" required>
          <el-input-number
            v-model="refundForm.amount"
            :min="0.01"
            :max="Number(detail.refund_price) || 0"
            :precision="2"
            style="width: 200px"
          ></el-input-number>
        </el-form-item>
        <el-form-item label="退款方式">
          <el-radio-group v-model="refundForm.refund_type">
            <el-radio label="original" value="original">原路退回</el-radio>
            <el-radio label="balance" value="balance">退到余额</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="退款流水号">
          <el-input v-model="refundForm.refund_trade_no" maxlength="64" placeholder="退款交易流水号（选填）"></el-input>
        </el-form-item>
      </el-form>
      <template #footer
        ><div>
          <el-button @click="refundModal = false">取消</el-button>
          <el-button type="primary" @click="submitRefund">确认退款</el-button>
        </div></template
      >
    </el-dialog>

    <!-- 补发弹窗 -->
    <el-dialog
      v-model="resendModal"
      title="补发商品"
      width="500px"
      @close="resendForm = { delivery_type: '', delivery_no: '' }"
    >
      <el-form :model="resendForm" label-width="100px">
        <el-form-item label="快递公司" required>
          <el-input v-model="resendForm.delivery_type" placeholder="请输入快递公司名称"></el-input>
        </el-form-item>
        <el-form-item label="快递单号" required>
          <el-input v-model="resendForm.delivery_no" placeholder="请输入快递单号"></el-input>
        </el-form-item>
      </el-form>
      <template #footer
        ><div>
          <el-button @click="resendModal = false">取消</el-button>
          <el-button type="primary" @click="submitResend">确认补发</el-button>
        </div></template
      >
    </el-dialog>

    <!-- 维修完成弹窗 -->
    <el-dialog v-model="repairModal" title="维修完成" width="500px" @close="repairForm = { repair_remark: '' }">
      <el-form :model="repairForm" label-width="100px">
        <el-form-item label="维修备注">
          <el-input
            type="textarea"
            v-model="repairForm.repair_remark"
            :rows="4"
            placeholder="维修过程记录（选填）"
          ></el-input>
        </el-form-item>
        <div class="repair-tip">维修完成后将进入补发流程，请填写补发物流寄回用户。</div>
      </el-form>
      <template #footer
        ><div>
          <el-button @click="repairModal = false">取消</el-button>
          <el-button type="primary" @click="submitRepairComplete">确认完成</el-button>
        </div></template
      >
    </el-dialog>

    <!-- 物流进度抽屉（复用订单详情物流查询组件） -->
    <DetailLogistics v-model:visible="logisticsVisible" :data="logisticsData" :logisticsList="logisticsList" />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage, ElMessageBox } from 'element-plus';
import {
  afterSaleDetail,
  auditAfterSale,
  receiveAfterSale,
  refundAfterSale,
  resendAfterSale,
  startRepairAfterSale,
  repairCompleteAfterSale,
  remarkAfterSale,
  getAfterSaleExpress,
} from '@/api/afterSale';
import setting from '@/setting';
import DetailLogistics from '@/pages/order/orderCenter/components/DetailLogistics';

defineOptions({ name: 'afterSaleDetail' });

const route = useRoute();
const router = useRouter();
const routePre = setting.routePre;

const detail = ref(null);
const loading = ref(false);
const activeTab = ref('timeline');
const remarkText = ref('');
// 审核弹窗
const auditModal = ref(false);
const auditForm = ref({ result: 'pass', reject_reason: '', audit_remark: '' });
// 退款弹窗
const refundModal = ref(false);
const refundForm = ref({ amount: 0, refund_type: 'original', refund_trade_no: '' });
// 补发弹窗
const resendModal = ref(false);
const resendForm = ref({ delivery_type: '', delivery_no: '' });
// 维修完成弹窗
const repairModal = ref(false);
const repairForm = ref({ repair_remark: '' });
// 产品类型文案映射（与列表页保持一致）
const productTypeOptions = [
  { key: 'normal', label: '实物商品' },
  { key: 'virtual', label: '虚拟商品' },
  { key: 'cardkey', label: '卡密商品' },
  { key: 'coupon', label: '优惠券商品' },
];
const categoryOptions = [
  { key: 'goods', label: '商品订单' },
  { key: 'points', label: '积分订单' },
  { key: 'member', label: '会员订单' },
  { key: 'recharge', label: '充值订单' },
];
// 物流进度抽屉
const logisticsVisible = ref(false);
const logisticsData = ref({});
const logisticsList = ref([]);

/**
 * 商品快照统一为数组
 * 用户端 buildSnapshot 写入结构为 { product_type, order_no, order_amount, items: [...] }，
 * 表格需要扁平的 items 数组，且字段名需从 store_name/cart_num/true_price 映射为 name/num/price
 */
const productList = computed(() => {
  const snap = detail.value && detail.value.product_snapshot;
  if (!snap) return [];
  // 兼容三种结构：直接数组 / 含 items 数组的对象 / 单商品对象
  let items = [];
  if (Array.isArray(snap)) {
    items = snap;
  } else if (snap && Array.isArray(snap.items)) {
    items = snap.items;
  } else if (snap && typeof snap === 'object') {
    items = [snap];
  }
  // 字段名映射统一：用户端写入字段 → 表格展示字段
  return items.map((item) => ({
    image: item.image || '',
    name: item.name || item.store_name || item.title || '',
    sku: item.sku || '',
    num: item.num || item.cart_num || 1,
    price: item.price || item.true_price || '0.00',
  }));
});

/**
 * 是否有退货物流信息
 */
const hasReturnDelivery = computed(() => {
  const d = detail.value && detail.value.return_delivery;
  return d && (d.type || d.no);
});

/**
 * 是否有补发物流信息
 */
const hasResendDelivery = computed(() => {
  const d = detail.value && detail.value.resend_delivery;
  return d && (d.type || d.no);
});

/**
 * 商家是否已确认收到退货
 * 退货退款/换货确认收货后仍沿用 receiving 状态，用 receive_time 区分收货前后动作
 */
const isReceived = computed(() => Number((detail.value && detail.value.receive_time) || 0) > 0);

/**
 * 是否可执行退款
 * - 仅退款：audit_passed 状态可退款
 * - 退货退款：receiving 状态且已确认收货后可退款
 */
const canRefund = computed(() => {
  if (!detail.value) return false;
  const { status, type } = detail.value;
  if (type === 'refund_only' && status === 'audit_passed') return true;
  if (type === 'return_refund' && status === 'receiving' && isReceived.value) return true;
  return false;
});

/**
 * 是否可确认收货（退货退款/换货类型，receiving 状态且尚未确认收货）
 */
const canReceive = computed(() => {
  if (!detail.value) return false;
  const { status, type } = detail.value;
  return status === 'receiving' && ['return_refund', 'exchange'].includes(type) && !isReceived.value;
});

/**
 * 是否可开始维修（维修类型，receiving 状态）
 */
const canStartRepair = computed(() => {
  if (!detail.value) return false;
  const { status, type } = detail.value;
  return status === 'receiving' && type === 'repair';
});

/**
 * 是否可补发商品
 * - 补发类型：audit_passed 状态可补发
 * - 换货类型：receiving 状态且已确认收货后可补发
 * - 维修类型：repair_complete 状态可补发
 */
const canResend = computed(() => {
  if (!detail.value) return false;
  const { status, type } = detail.value;
  if (type === 'resend' && status === 'audit_passed') return true;
  if (type === 'exchange' && status === 'receiving' && isReceived.value) return true;
  if (type === 'repair' && status === 'repair_complete') return true;
  return false;
});

/**
 * 加载售后详情
 */
function loadDetail() {
  const id = route.params.id;
  if (!id) return;
  loading.value = true;
  afterSaleDetail(id)
    .then((res) => {
      detail.value = res.data || null;
      remarkText.value = detail.value ? detail.value.remark || '' : '';
      loading.value = false;
      // 从列表页跳转时定位审核区块
      if (route.query.action === 'audit' && detail.value && detail.value.status === 'pending_audit') {
        openAudit('pass');
      }
    })
    .catch((res) => {
      loading.value = false;
      ElMessage.error(res.msg);
    });
}

/**
 * 查看物流进度
 * 复用订单详情物流查询抽屉组件，按物流类型查询退货物流或补发物流轨迹
 * @param {String} type 物流类型 return=退货物流 resend=补发物流
 */
function openLogistics(type) {
  const id = route.params.id;
  // 重置抽屉数据并打开，异步加载轨迹
  logisticsData.value = {};
  logisticsList.value = [];
  logisticsVisible.value = true;
  getAfterSaleExpress(id, type)
    .then((res) => {
      const data = res.data || {};
      logisticsData.value = { delivery_name: data.delivery_name, delivery_id: data.delivery_id };
      logisticsList.value = data.result || [];
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

/**
 * 打开审核弹窗
 */
function openAudit(result) {
  auditForm.value = { result, reject_reason: '', audit_remark: '' };
  auditModal.value = true;
}

/**
 * 提交审核
 */
function submitAudit() {
  if (auditForm.value.result === 'reject' && !auditForm.value.reject_reason) {
    ElMessage.warning('请填写拒绝原因');
    return;
  }
  const id = route.params.id;
  auditAfterSale(id, auditForm.value)
    .then((res) => {
      ElMessage.success(res.msg);
      auditModal.value = false;
      loadDetail();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

/**
 * 打开退款弹窗
 */
function openRefund() {
  refundForm.value = {
    amount: Number(detail.value.refund_price) || 0,
    refund_type: 'original',
    refund_trade_no: '',
  };
  refundModal.value = true;
}

/**
 * 提交退款
 */
function submitRefund() {
  if (refundForm.value.amount <= 0) {
    ElMessage.warning('退款金额必须大于0');
    return;
  }
  const id = route.params.id;
  refundAfterSale(id, refundForm.value)
    .then((res) => {
      ElMessage.success(res.msg);
      refundModal.value = false;
      loadDetail();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

/**
 * 确认收货
 */
function handleReceive() {
  ElMessageBox.confirm('确认已收到用户退回的商品吗？确认后将推进后续流程。', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  })
    .then(() => {
      const id = route.params.id;
      receiveAfterSale(id)
        .then((res) => {
          ElMessage.success(res.msg);
          loadDetail();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    })
    .catch(() => {});
}

/**
 * 开始维修
 */
function handleStartRepair() {
  ElMessageBox.confirm('确认开始维修吗？确认后售后状态将变为"维修中"。', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning',
  })
    .then(() => {
      const id = route.params.id;
      startRepairAfterSale(id)
        .then((res) => {
          ElMessage.success(res.msg);
          loadDetail();
        })
        .catch((res) => {
          ElMessage.error(res.msg);
        });
    })
    .catch(() => {});
}

/**
 * 打开维修完成弹窗
 */
function openRepairComplete() {
  repairForm.value = { repair_remark: '' };
  repairModal.value = true;
}

/**
 * 提交维修完成
 */
function submitRepairComplete() {
  const id = route.params.id;
  repairCompleteAfterSale(id, repairForm.value)
    .then((res) => {
      ElMessage.success(res.msg);
      repairModal.value = false;
      loadDetail();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

/**
 * 打开补发弹窗
 */
function openResend() {
  resendForm.value = { delivery_type: '', delivery_no: '' };
  resendModal.value = true;
}

/**
 * 提交补发
 */
function submitResend() {
  if (!resendForm.value.delivery_type || !resendForm.value.delivery_no) {
    ElMessage.warning('请填写快递公司和单号');
    return;
  }
  const id = route.params.id;
  resendAfterSale(id, resendForm.value)
    .then((res) => {
      ElMessage.success(res.msg);
      resendModal.value = false;
      loadDetail();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

/**
 * 保存管理备注
 */
function saveRemark() {
  const id = route.params.id;
  remarkAfterSale(id, { remark: remarkText.value })
    .then((res) => {
      ElMessage.success(res.msg);
      loadDetail();
    })
    .catch((res) => {
      ElMessage.error(res.msg);
    });
}

/**
 * 跳转到订单详情
 * 业态订单详情路由遵循统一规律：/product/{product_type}/order/detail/{order_no}
 */
function goOrderDetail() {
  if (!detail.value) return;
  const { product_type, order_no, order_center_id } = detail.value;
  // 符合 order_no 路由规律的业态，直接拼接业态专属订单详情路径
  if (order_no) {
    router.push({ path: `${routePre}/product/${product_type}/order/detail/${order_no}` });
  } else if (order_center_id) {
    // 无业态 order_no 时，回退订单中台统一详情页
    router.push({ path: `${routePre}/order/center/detail/${order_center_id}` });
  }
}

/**
 * 返回列表
 */
function goBack() {
  router.push({ path: routePre + '/order/afterSale' });
}

/**
 * 产品类型文案
 */
function getProductTypeLabel(type) {
  const item = productTypeOptions.find((opt) => opt.key === type);
  return item ? item.label : type || '--';
}

/**
 * 订单大类文案
 */
function getCategoryLabel(category) {
  const item = categoryOptions.find((opt) => opt.key === category);
  return item ? item.label : category || '--';
}

/**
 * 退款方式文案
 */
function getRefundTypeLabel(type) {
  const map = { original: '原路退回', balance: '退到余额' };
  return map[type] || type || '--';
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
  if (!timestamp) return '--';
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
  loadDetail();
});
</script>

<style lang="scss" scoped>
.after-sale-detail {
  padding: 0;
}

.head-card {
  :deep(.el-card__body) {
    padding: 20px 30px;
  }
}

.head {
  .full {
    display: flex;
    align-items: center;

    .order_icon {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: #fff;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .text {
      flex: 1;
      padding-left: 12px;

      .title {
        font-size: 16px;
        font-weight: 500;
        color: var(--prev-color-text-regular);
        margin-bottom: 6px;
      }

      .sub {
        font-size: 13px;
        color: var(--prev-color-text-primary);
      }
    }
  }

  .list {
    display: flex;
    margin-top: 20px;
    list-style: none;
    padding: 0;
    flex-wrap: wrap;

    .item {
      flex: none;
      width: 180px;
      font-size: 14px;
      margin-bottom: 10px;

      .title {
        margin-bottom: 8px;
        font-size: 13px;
        color: #999;
      }

      .value1 {
        color: #ed4014;
        font-weight: bold;
      }
    }
  }
}

.tab-content {
  padding: 20px 30px;
}

.section-title {
  margin: 24px 0 12px;
  font-size: 14px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
  padding-left: 10px;
  border-left: 3px solid var(--prev-color-primary, #409eff);
}

.image-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.evidence-img {
  width: 80px;
  height: 80px;
  border-radius: 4px;
  border: 1px solid var(--prev-border-color-lighter);
}

.product-img {
  width: 50px;
  height: 50px;
  border-radius: 4px;
}

.price {
  color: #ed4014;
  font-weight: bold;
}

.danger-text {
  color: #ed4014;
}

.remark-content {
  padding: 12px;
  background: #f5f7fa;
  border-radius: 4px;
  font-size: 13px;
  color: var(--prev-color-text-primary);
  white-space: pre-wrap;
  word-break: break-all;
}

.action-card {
  :deep(.el-card__body) {
    padding: 16px 20px;
  }

  .action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .action-tip {
      font-size: 14px;
      color: var(--prev-color-text-primary);

      i {
        margin-right: 6px;
        color: var(--prev-color-primary, #409eff);
      }
    }

    .action-buttons {
      display: flex;
      gap: 10px;
    }
  }
}

.repair-tip {
  margin-top: 8px;
  padding: 10px;
  background: #fff7e6;
  border-radius: 4px;
  color: #fa8c16;
  font-size: 12px;
}

.empty-tip {
  padding: 80px 0;
}

:deep(.el-tabs--border-card) {
  border: none;
  box-shadow: none;

  > .el-tabs__header {
    border: none;
    height: 40px;

    .el-tabs__item {
      border: none;
      height: 40px !important;
      line-height: 40px !important;
      font-size: 13px;

      &.is-active {
        border-top: 2px solid var(--prev-color-primary, #409eff);
        font-weight: 500;
      }
    }
  }

  > .el-tabs__content {
    padding: 0;
  }
}
</style>
