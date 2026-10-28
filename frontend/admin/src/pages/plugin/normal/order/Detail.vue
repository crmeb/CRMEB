<template>
  <div>
    <div v-loading="detailLoading">
      <div v-if="detailData" class="order-detail">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderIcon" alt="" />
            <div class="text">
              <div class="title">实物商品订单</div>
              <div>订单号：{{ detailData.order_no }}</div>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">订单状态</div>
              <div>
                <el-tag :type="businessStatus.type">{{ businessStatus.text }}</el-tag>
                <span v-if="detailData.refund_status == 1" style="color: #ed4014; margin-left: 4px">
                  退款中
                </span>
              </div>
            </li>
            <li class="item">
              <div class="title">订单金额</div>
              <div class="value1">¥{{ detailData.total_price || '0.00' }}</div>
            </li>
            <li class="item" v-if="detailData.refund_status == 2">
              <div class="title">实际退款</div>
              <div>¥{{ detailData.refunded_price || '0.00' }}</div>
            </li>
            <li class="item">
              <div class="title">支付方式</div>
              <div>{{ detailData.pay_type_name || detailData.pay_type || '--' }}</div>
            </li>
            <li class="item">
              <div class="title">支付时间</div>
              <div>{{ detailData.pay_time ? formatDate(detailData.pay_time) : '--' }}</div>
            </li>
          </ul>
        </div>
        <el-tabs type="border-card" v-model="activeTab">
          <el-tab-pane label="订单信息" name="detail">
            <!-- 商品信息 -->
            <div class="section" v-if="orderItems.length">
              <div class="section-title">商品信息</div>
              <el-table :data="orderItems" stripe class="goods-table">
                <el-table-column label="商品" min-width="300">
                  <template #default="{ row }">
                    <div class="goods-cell">
                      <el-image :src="row.image" class="goods-img" />
                      <div class="goods-meta">
                        <p class="goods-name">{{ row.store_name }}</p>
                        <p v-if="row.sku" class="goods-sku">{{ row.sku }}</p>
                      </div>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column label="单价" width="100">
                  <template #default="{ row }">¥{{ row.true_price || row.price }}</template>
                </el-table-column>
                <el-table-column label="数量" width="80" prop="cart_num" />
                <el-table-column label="小计" width="120">
                  <template #default="{ row }"
                    >¥{{ (parseFloat(row.true_price || row.price) * row.cart_num).toFixed(2) }}</template
                  >
                </el-table-column>
              </el-table>
            </div>

            <!-- 用户信息 -->
            <div class="section">
              <div class="section-title">用户信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">用户名称：</span><span class="value">{{ detailData.nickname || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">联系电话：</span><span class="value">{{ detailData.user_phone || '--' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 配送方式（下单时 express/pickup/door） -->
            <div class="section">
              <div class="section-title">配送方式</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">配送方式：</span
                    ><span class="value">{{ deliveryTypeText }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 发货/物流信息（发货后 express/send/fictitious） -->
            <div class="section" v-if="detailData.delivery_name">
              <div class="section-title">{{ shipInfoSectionTitle }}</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">{{ shipInfoNameLabel }}</span
                    ><span class="value">{{ detailData.delivery_name || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="12" v-if="shipDeliveryType !== 'fictitious' || detailData.delivery_id"
                  ><div class="detail-item">
                    <span class="label">{{ shipInfoIdLabel }}</span
                    ><span class="value"
                      >{{ detailData.delivery_id || '--'
                      }}<a
                        v-if="shipDeliveryType === 'express'"
                        v-db-click
                        @click="toggleLogistics"
                        class="track-link"
                        >{{ logisticsVisible ? '收起轨迹' : '查看轨迹' }}</a
                      ></span
                    >
                  </div></el-col
                >
              </el-row>
              <!-- 物流轨迹展开区域（仅真实快递） -->
              <div v-if="logisticsVisible && shipDeliveryType === 'express'" class="logistics-panel">
                <div class="logistics-header">
                  <img class="logistics-icon" src="../../../../assets/images/expressi.jpg" />
                  <div class="logistics-info">
                    <div class="logistics-company">{{ detailData.delivery_name }}</div>
                    <div class="logistics-num">
                      <span class="label">运单号</span>
                      <span class="value">{{ detailData.delivery_id }}</span>
                      <i class="el-icon-document-copy copy-btn" title="复制" @click="copyNum"></i>
                    </div>
                  </div>
                </div>
                <div class="logistics-timeline">
                  <el-timeline v-if="logisticsData.length">
                    <el-timeline-item
                      v-for="(item, i) in logisticsData"
                      :key="i"
                      :color="i === 0 ? '#2d8cf0' : '#c0c4cc'"
                    >
                      <div class="track-item">
                        <div class="track-status" :class="{ latest: i === 0 }">{{ item.status }}</div>
                        <div class="track-time">{{ item.time }}</div>
                      </div>
                    </el-timeline-item>
                  </el-timeline>
                  <div v-else class="empty-logistics">暂无物流信息</div>
                </div>
              </div>
            </div>

            <!-- 收货信息（仅快递配送展示） -->
            <div
              class="section"
              v-if="
                detailData.extra_info?.delivery_type !== 'pickup' && (detailData.contact || detailData.user_address)
              "
            >
              <div class="section-title">收货信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">收货人：</span
                    ><span class="value">{{ detailData.contact?.name || detailData.real_name || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">收货电话：</span
                    ><span class="value">{{ detailData.contact?.phone || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">收货地址：</span
                    ><span class="value">{{
                      formatAddress(detailData.user_address || detailData.contact?.address) || '--'
                    }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 订单信息 -->
            <div class="section">
              <div class="section-title">订单信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">创建时间：</span
                    ><span class="value">{{ formatDate(detailData.add_time) }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">商品总数：</span><span class="value">{{ detailData.total_num }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">商品总价：</span><span class="value">¥{{ detailData.total_price }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">优惠券金额：</span
                    ><span class="value">-¥{{ detailData.coupon_price || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">积分抵扣：</span
                    ><span class="value">-¥{{ detailData.deduction_price || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">交付邮费：</span
                    ><span class="value">¥{{ detailData.pay_postage || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">会员等级优惠：</span
                    ><span class="value">-¥{{ detailData.member_level_discount || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">付费会员优惠：</span
                    ><span class="value">-¥{{ detailData.vip_discount || '0.00' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label price-label">实际支付：</span
                    ><span class="value price-value">¥{{ detailData.pay_price || '0.00' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 返佣信息 -->
            <div
              class="section"
              v-if="
                detailData.one_brokerage ||
                detailData.two_brokerage ||
                detailData.staff_brokerage ||
                detailData.agent_brokerage ||
                detailData.division_brokerage
              "
            >
              <div class="section-title">返佣信息（金额|用户UID）</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">一级佣金：</span
                    ><span class="value"
                      >{{ detailData.one_brokerage || '0.00' }} | {{ detailData.spread_uid || '0' }}</span
                    >
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">二级佣金：</span
                    ><span class="value"
                      >{{ detailData.two_brokerage || '0.00' }} | {{ detailData.spread_two_uid || '0' }}</span
                    >
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">员工返佣：</span
                    ><span class="value"
                      >{{ detailData.staff_brokerage || '0.00' }} | {{ detailData.staff_id || '0' }}</span
                    >
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">代理商佣金：</span
                    ><span class="value"
                      >{{ detailData.agent_brokerage || '0.00' }} | {{ detailData.agent_id || '0' }}</span
                    >
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">事业部佣金：</span
                    ><span class="value"
                      >{{ detailData.division_brokerage || '0.00' }} | {{ detailData.division_id || '0' }}</span
                    >
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 发票信息 -->
            <div class="section" v-if="detailData.invoice && Object.keys(detailData.invoice).length">
              <div class="section-title">发票信息</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">发票抬头：</span
                    ><span class="value">{{ detailData.invoice.name || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="detailData.invoice.header_type === 2 && detailData.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">企业税号：</span
                    ><span class="value">{{ detailData.invoice.duty_number || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="detailData.invoice.header_type === 2 && detailData.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">发票类型：</span><span class="value">电子普通发票</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="detailData.invoice.header_type === 2 && detailData.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">发票抬头类型：</span><span class="value">企业</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="detailData.invoice.header_type === 1 && detailData.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">真实姓名：</span
                    ><span class="value">{{ detailData.invoice.name || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="detailData.invoice.header_type === 1 && detailData.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">联系电话：</span
                    ><span class="value">{{ detailData.invoice.drawer_phone || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="detailData.invoice.header_type === 2 && detailData.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">联系电话：</span
                    ><span class="value">{{ detailData.invoice.user_phone || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8" v-if="detailData.invoice.header_type === 2 && detailData.invoice.type === 1"
                  ><div class="detail-item">
                    <span class="label">联系邮箱：</span
                    ><span class="value">{{ detailData.invoice.email || '--' }}</span>
                  </div></el-col
                >
                <el-col :span="8"
                  ><div class="detail-item">
                    <span class="label">开票状态：</span
                    ><span class="value">{{ detailData.invoice.is_invoice ? '已开票' : '未开票' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 买家留言 -->
            <div class="section">
              <div class="section-title">买家留言</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="24"
                  ><div class="detail-item">
                    <span class="value">{{ detailData.mark || '--' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>

            <!-- 表单信息（图片字段按 type=img 判断，与订单中台 DetailOrderInfo 同口径） -->
            <div class="section" v-if="detailData.custom_form && detailData.custom_form.length">
              <div class="section-title">表单信息</div>
              <el-row :gutter="24" class="detail-grid">
                <template v-for="(item, idx) in detailData.custom_form" :key="idx">
                  <el-col :span="8" v-if="!isImageItem(item)">
                    <div class="detail-item">
                      <span class="label">{{ item.title }}：</span><span class="value">{{ item.value }}</span>
                    </div>
                  </el-col>
                  <el-col :span="24" v-else>
                    <div class="detail-item">
                      <span class="label">{{ item.title }}：</span>
                      <span class="value">
                        <img
                          v-for="(img, i) in formImageUrls(item)"
                          :key="i"
                          :src="img"
                          class="form-img"
                          v-viewer
                        />
                      </span>
                    </div>
                  </el-col>
                </template>
              </el-row>
            </div>

            <!-- 订单备注 -->
            <div class="section">
              <div class="section-title">订单备注</div>
              <el-row :gutter="24" class="detail-grid">
                <el-col :span="24"
                  ><div class="detail-item">
                    <span class="value">{{ detailData.remark || '--' }}</span>
                  </div></el-col
                >
              </el-row>
            </div>
          </el-tab-pane>

          <el-tab-pane label="订单记录" name="timeline">
            <div class="tl-section" v-if="mergedTimeline.length">
              <el-timeline>
                <el-timeline-item
                  v-for="(item, index) in mergedTimeline"
                  :key="index"
                  :timestamp="item.time"
                  :color="item.source === 'after_sale' ? '#e6a23c' : index === 0 ? '#409eff' : '#e4e7ed'"
                >
                  <span class="tl-title">
                    <el-tag v-if="item.source === 'after_sale'" type="warning" effect="plain" class="source-tag"
                      >售后</el-tag
                    >
                    {{ item.title }}
                  </span>
                  <span class="tl-desc" v-if="item.desc">{{ item.desc }}</span>
                </el-timeline-item>
              </el-timeline>
            </div>
            <div v-else class="empty-tips">暂无订单记录</div>
          </el-tab-pane>
        </el-tabs>
      </div>
      <div v-else-if="!detailLoading" class="empty-tip">
        <el-empty description="暂无订单数据"></el-empty>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import orderIcon from '@/assets/images/order_icon.png';
import request from '@/libs/request';
import { getOrderExpress, getOrderBusinessStatus } from '../normalOrder';

defineOptions({ name: 'normalOrderDetail' });

const props = defineProps({
  /** 订单号，非 prop 时从 $route 读取 */
  orderNo: { type: String, default: '' },
});

const route = useRoute();

const detailData = ref(null);
const detailLoading = ref(false);
const activeTab = ref('detail');
const logisticsVisible = ref(false);
const logisticsData = ref([]);

const orderItems = computed(() => {
  if (!detailData.value) return [];
  return detailData.value.order_items || detailData.value.cartInfo || [];
});

/** 订单状态与列表同口径（待发货/待收货/待到店等业务状态） */
const businessStatus = computed(() => getOrderBusinessStatus(detailData.value || {}));

/** 下单配送方式（express/pickup/door），用于「配送方式」区块 */
const orderDeliveryType = computed(() => {
  const t = String(detailData.value?.extra_info?.delivery_type || '');
  if (['express', 'pickup', 'door'].includes(t)) return t;
  const shippingType = Number(detailData.value?.shipping_type || 0);
  if (shippingType === 2) return 'pickup';
  if (shippingType === 3) return 'door';
  return 'express';
});
const deliveryTypeText = computed(
  () =>
    ({
      express: '快递配送',
      pickup: '到店自提',
      door: '上门服务',
    })[orderDeliveryType.value] || '快递配送',
);

/** 发货方式（express/send/fictitious）；未发货为空 */
const shipDeliveryType = computed(() => {
  const t = String(detailData.value?.delivery_type || '');
  return ['express', 'send', 'fictitious'].includes(t) && detailData.value?.delivery_name ? t : '';
});

const shipInfoSectionTitle = computed(() => {
  if (shipDeliveryType.value === 'fictitious') return '发货信息';
  if (shipDeliveryType.value === 'send') return '送货人信息';
  return '物流信息';
});

const shipInfoNameLabel = computed(() => {
  if (shipDeliveryType.value === 'fictitious') return '发货方式：';
  if (shipDeliveryType.value === 'send') return '送货人姓名：';
  return '物流公司：';
});

const shipInfoIdLabel = computed(() => {
  if (shipDeliveryType.value === 'fictitious') return '发货备注：';
  if (shipDeliveryType.value === 'send') return '送货人电话：';
  return '物流单号：';
});

/** 售后进度数据（后端注入到 detail.extend.after_sale） */
const afterSaleInfo = computed(() => {
  if (!detailData.value) return null;
  const ext = detailData.value.extend || {};
  const afterSale = ext.after_sale;
  // 无售后单号视为无售后记录
  if (!afterSale || !afterSale.after_sale_no) return null;
  return afterSale;
});

/** 合并订单操作记录与售后时间线，按时间倒序排列（最新在前） */
const mergedTimeline = computed(() => {
  if (!detailData.value) return [];
  // 订单操作记录（time 格式：Y-m-d H:i:s）
  const orderList = (detailData.value.timeline || []).map((item) => ({
    title: item.title || '',
    desc: item.desc || '',
    time: item.time || '',
    source: 'order',
    sortTime: parseTimeToTimestamp(item.time),
  }));
  // 售后时间线（time 格式：Y-m-d H:i）
  const afterSaleList = ((afterSaleInfo.value && afterSaleInfo.value.timeline) || []).map((item) => ({
    title: item.title || '',
    desc: item.desc || '',
    time: item.time || '',
    source: 'after_sale',
    sortTime: parseTimeToTimestamp(item.time),
  }));
  // 合并后按时间倒序排列（sortTime 大的在前）
  return [...orderList, ...afterSaleList].sort((a, b) => b.sortTime - a.sortTime);
});

function loadDetail(orderNo) {
  const no = orderNo || props.orderNo || route.params?.orderNo || '';
  if (!no) return;
  detailLoading.value = true;
  request
    .get('normal/order/' + no)
    .then((res) => {
      detailData.value = res.data;
      detailLoading.value = false;
    })
    .catch((res) => {
      detailLoading.value = false;
      ElMessage.error(res.msg || '加载订单详情失败');
    });
}

function toggleLogistics() {
  logisticsVisible.value = !logisticsVisible.value;
  if (logisticsVisible.value && !logisticsData.value.length) {
    getLogisticsData();
  }
}

function copyNum() {
  const trackingNum = (detailData.value && detailData.value.delivery_id) || '';
  if (!trackingNum) return;
  const input = document.createElement('input');
  input.value = trackingNum;
  document.body.appendChild(input);
  input.select();
  document.execCommand('copy');
  document.body.removeChild(input);
  ElMessage.success('已复制');
}

function getLogisticsData() {
  if (!detailData.value) return;
  getOrderExpress(detailData.value.order_no)
    .then((res) => {
      logisticsData.value = res.data.result || [];
    })
    .catch((res) => {
      ElMessage.error(res.msg || '物流查询失败');
    });
}

function formatDate(ts) {
  if (!ts) return '--';
  const d = new Date(ts * 1000);
  return `${d.getFullYear()}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d
    .getDate()
    .toString()
    .padStart(2, '0')} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d
    .getSeconds()
    .toString()
    .padStart(2, '0')}`;
}

// 图片扩展名判断
const IMAGE_EXT_RE = /\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i;

/** 自定义表单字段值规范化为图片 URL 数组（兼容字符串单图 / 数组多图） */
function formImageUrls(item) {
  if (!item || !item.value) return [];
  const list = Array.isArray(item.value) ? item.value : [item.value];
  return list.filter((v) => typeof v === 'string' && v.indexOf('http') === 0);
}

/**
 * 图片字段判断：新订单 type=img，或存量订单无类型但值是图片链接
 * （新数据 label 是 custom_field_N，不能再按 label == 'img' 判断）
 */
function isImageItem(item) {
  if (!item) return false;
  if (item.type === 'img') return true;
  if (item.type) return false;
  const urls = formImageUrls(item);
  return urls.length > 0 && urls.every((v) => IMAGE_EXT_RE.test(v) || v.indexOf('/uploads/') !== -1);
}

/** 将 "Y-m-d H:i:s" 或 "Y-m-d H:i" 字符串转为时间戳，用于时间线排序 */
function parseTimeToTimestamp(timeStr) {
  if (!timeStr) return 0;
  // iOS/Safari 不支持 "-" 分隔的 Date 解析，统一替换为 "/"
  const ts = new Date(String(timeStr).replace(/-/g, '/')).getTime();
  return isNaN(ts) ? 0 : ts;
}

function formatAddress(addr) {
  if (!addr) return '';
  if (typeof addr === 'string') {
    try {
      const parsed = JSON.parse(addr);
      return buildAddress(parsed);
    } catch (e) {
      return addr;
    }
  }
  if (typeof addr === 'object') {
    return buildAddress(addr);
  }
  return '';
}

function buildAddress(addr) {
  const parts = [];
  if (addr.province) parts.push(addr.province);
  if (addr.city) parts.push(addr.city);
  if (addr.district) parts.push(addr.district);
  if (addr.detail) parts.push(addr.detail);
  return parts.join('');
}

onMounted(() => {
  // 只要有 orderNo（prop 或 $route 参数）就自动加载
  const no = props.orderNo || route.params?.orderNo || '';
  if (no) {
    loadDetail(no);
  }
});

// 抽屉模式下 prop 变化时重新加载
watch(
  () => props.orderNo,
  (val) => {
    if (val) {
      loadDetail(val);
    }
  },
);

defineExpose({ loadDetail });
</script>

<style lang="scss" scoped>
:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item.is-active) {
  border-bottom: none;
}

:deep(.el-tabs__item) {
  height: 40px !important;
  line-height: 40px !important;
}

.head {
  padding: 0 30px 24px;

  .full {
    display: flex;
    align-items: center;

    .order_icon {
      width: 60px;
      height: 60px;
    }

    .text {
      align-self: center;
      flex: 1;
      min-width: 0;
      padding-left: 12px;
      font-size: 13px;
      color: var(--prev-color-text-primary);

      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        color: var(--prev-color-text-regular);
      }
    }
  }

  .list {
    display: flex;
    flex-wrap: wrap;
    row-gap: 16px;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;

    .item {
      flex: 1 1 auto;
      min-width: 0;
      padding-right: 16px;
      font-size: 14px;
      line-height: 14px;
      color: var(--prev-color-text-regular);

      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: var(--prev-color-text-primary);
      }

      .value1 {
        color: #f56022;
        font-weight: bold;
      }
    }
  }
}

:deep(.el-tabs--border-card > .el-tabs__content) {
  /* 与 .head 的 30px 左右缩进对齐（body 左右已由列表页归零） */
  padding: 0 30px;
}

:deep(.el-tabs--border-card > .el-tabs__header),
:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item:active) {
  border: none;
  height: 40px;
}

:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item.is-active) {
  border: none;
  border-top: 2px solid var(--prev-color-primary);
  font-size: 13px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
  line-height: 16px;
}

:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item) {
  border: none;
}

:deep(.el-tabs--border-card > .el-tabs__header .el-tabs__item) {
  margin-top: 0;
  transition: none;
  height: 40px !important;
  line-height: 40px !important;
  width: 92px !important;
  font-size: 13px;
  font-weight: 400;
  color: var(--prev-color-text-regular);
  line-height: 16px;
}

:deep(.el-tabs--border-card) {
  border: none;
  box-shadow: none;
}

/* section 通用 */
.section {
  padding: 20px 0;
  border-bottom: 1px dashed var(--prev-border-color-lighter);
}
.section:last-child {
  border-bottom: none;
}
.section-title {
  padding-left: 10px;
  border-left: 3px solid var(--prev-color-primary, #1890ff);
  font-size: 15px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
  margin-bottom: 16px;
}

.detail-grid {
  padding: 0;
}
.detail-item {
  display: flex;
  padding: 8px 0;
  font-size: 13px;
  color: var(--prev-color-text-primary);
}
.detail-item .label {
  flex-shrink: 0;
  color: var(--prev-color-text-secondary);
  margin-right: 4px;
}
.detail-item .value {
  flex: 1;
  word-break: break-all;
  min-width: 0;

  .track-link {
    display: inline-block;
    margin-left: 8px;
    color: #2d8cf0;
    cursor: pointer;
    white-space: nowrap;
    word-break: keep-all;
  }
}
.price-label {
  color: #ed4014 !important;
}
.price-value {
  color: #ed4014;
  font-weight: bold;
}

/* 商品表格 */
.goods-table {
  border: 1px solid var(--prev-border-color-lighter);
  border-radius: 4px;
}
.goods-cell {
  display: flex;
  align-items: center;
}
.goods-img {
  width: 50px;
  height: 50px;
  border-radius: 4px;
  margin-right: 12px;
  flex-shrink: 0;
}
.goods-meta {
  min-width: 0;
}
.goods-name {
  margin: 0;
  font-size: 13px;
  color: var(--prev-color-text-regular);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.goods-sku {
  margin: 2px 0 0;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}

/* 表单图片 */
.form-img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  margin-right: 8px;
  cursor: pointer;
}

/* 时间线 */
.tl-section {
  padding: 16px 0;

  /* EP 时间线默认 padding-left 40px，叠加 tabs 缩进后过深，收齐到内容左缘 */
  :deep(.el-timeline) {
    padding-left: 4px;
  }
}
:deep(.el-timeline-item__content .tl-title) {
  font-size: 13px;
  font-weight: 500;
  color: var(--prev-color-text-regular);
}
:deep(.el-timeline-item__content .tl-desc) {
  margin-left: 8px;
  font-size: 12px;
  color: var(--prev-color-text-secondary);
}
/* 售后来源标签 */
.source-tag {
  margin-right: 6px;
  vertical-align: middle;
}

.empty-tip {
  padding: 40px;
}
.empty-tips {
  padding: 20px 0;
  color: var(--prev-color-text-secondary);
  font-size: 13px;
}

/* 物流轨迹面板 */
.logistics-panel {
  margin-top: 16px;
  padding: 16px;
  background: var(--prev-bg-color);
  border-radius: 8px;

  .logistics-header {
    display: flex;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--prev-border-color-lighter);
    margin-bottom: 16px;

    .logistics-icon {
      width: 40px;
      height: 40px;
      border-radius: 6px;
      margin-right: 12px;
      flex-shrink: 0;
    }

    .logistics-info {
      flex: 1;
      min-width: 0;
    }

    .logistics-company {
      font-size: 14px;
      font-weight: 600;
      color: var(--prev-color-text-regular);
      margin-bottom: 4px;
    }

    .logistics-num {
      display: flex;
      align-items: center;
      font-size: 12px;

      .label {
        color: var(--prev-color-text-secondary);
        margin-right: 6px;
        flex-shrink: 0;
      }

      .value {
        color: var(--prev-color-text-primary);
        word-break: break-all;
      }

      .copy-btn {
        color: var(--prev-color-text-secondary);
        cursor: pointer;
        margin-left: 6px;
        font-size: 13px;
        flex-shrink: 0;

        &:hover {
          color: #2d8cf0;
        }
      }
    }
  }

  .logistics-timeline {
    max-height: 300px;
    overflow-y: auto;
    padding-left: 8px;

    :deep(.el-timeline-item) {
      padding-bottom: 16px;

      &:last-child {
        padding-bottom: 0;
      }
    }

    :deep(.el-timeline-item__node) {
      width: 12px;
      height: 12px;
    }

    :deep(.el-timeline-item__wrapper) {
      padding-left: 20px;
    }
  }

  .track-item {
    .track-status {
      font-size: 13px;
      color: var(--prev-color-text-primary);
      line-height: 1.5;
      margin-bottom: 2px;

      &.latest {
        color: #2d8cf0;
        font-weight: 500;
      }
    }

    .track-time {
      font-size: 11px;
      color: #c0c4cc;
    }
  }

  .empty-logistics {
    text-align: center;
    color: var(--prev-color-text-secondary);
    padding: 30px 0;
    font-size: 13px;
  }
}
</style>
