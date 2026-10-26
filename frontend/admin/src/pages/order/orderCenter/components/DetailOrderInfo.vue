<template>
  <div>
    <!-- 商品信息 -->
    <div class="section" v-if="orderItems.length">
      <div class="title">商品信息</div>
      <div class="goods-table">
        <div class="goods-header">
          <span class="col-goods">商品</span>
          <span class="col-spec">规格</span>
          <span class="col-price">单价</span>
          <span class="col-num">数量</span>
          <span class="col-subtotal">小计</span>
        </div>
        <div class="goods-row" v-for="(item, idx) in orderItems" :key="idx">
          <div class="col-goods">
            <el-image :src="item.image" />
            <span class="goods-name">{{ item.store_name }}</span>
          </div>
          <div class="col-spec">{{ item.sku || '默认' }}</div>
          <div class="col-price">¥{{ item.true_price || item.price }}</div>
          <div class="col-num">{{ item.cart_num }}</div>
          <div class="col-subtotal">¥{{ ((item.true_price || item.price) * item.cart_num).toFixed(2) }}</div>
        </div>
      </div>
    </div>
    <div class="section">
      <div class="title">用户信息</div>
      <ul class="list">
        <li class="item">
          <div>用户名称：</div>
          <div class="value">{{ data.nickname || '--' }}</div>
        </li>
        <li class="item">
          <div>绑定电话：</div>
          <div class="value">{{ data.user_phone || '--' }}</div>
        </li>
      </ul>
    </div>
    <!-- 收货信息（快递配送）/ 自提门店信息（到店自提） -->
    <div class="section">
      <div class="title">{{ data.order_delivery_type === 'pickup' ? '自提门店' : '收货信息' }}</div>
      <ul class="list" v-if="data.order_delivery_type === 'pickup' && data.store_info">
        <li class="item">
          <div>门店名称：</div>
          <div class="value">{{ data.store_info.store_name || '--' }}</div>
        </li>
        <li class="item">
          <div>门店电话：</div>
          <div class="value">{{ data.store_info.store_phone || '--' }}</div>
        </li>
        <li class="item">
          <div>门店地址：</div>
          <div class="value">{{ data.store_info.detailed_address || data.store_info.address || '--' }}</div>
        </li>
      </ul>
      <ul class="list" v-else>
        <li class="item">
          <div>收货人：</div>
          <div class="value">{{ data.contact?.name || data.real_name || '--' }}</div>
        </li>
        <li class="item">
          <div>收货电话：</div>
          <div class="value">{{ data.contact?.phone || '--' }}</div>
        </li>
        <li class="item">
          <div>收货地址：</div>
          <div class="value">{{ formatAddress(data.user_address) || data.contact?.address || '--' }}</div>
        </li>
      </ul>
    </div>
    <div class="section">
      <div class="title">订单信息</div>
      <ul class="list">
        <li class="item">
          <div>创建时间：</div>
          <div class="value">{{ formatDate(data.add_time) }}</div>
        </li>
        <li class="item">
          <div>商品总数：</div>
          <div class="value">{{ data.total_num }}</div>
        </li>
        <li class="item">
          <div>商品总价：</div>
          <div class="value">¥{{ data.total_price }}</div>
        </li>
        <li class="item">
          <div>优惠券金额：</div>
          <div class="value">¥{{ data.coupon_price || '0.00' }}</div>
        </li>
        <li class="item">
          <div>积分抵扣：</div>
          <div class="value">¥{{ data.deduction_price || '0.00' }}</div>
        </li>
        <li class="item" v-if="data.order_delivery_type !== 'pickup'">
          <div>交付邮费：</div>
          <div class="value">¥{{ data.pay_postage || '0.00' }}</div>
        </li>
        <li class="item">
          <div>用户等级优惠：</div>
          <div class="value">¥{{ data.member_level_discount || '0.00' }}</div>
        </li>
        <li class="item">
          <div>付费会员优惠：</div>
          <div class="value">¥{{ data.vip_discount || '0.00' }}</div>
        </li>
        <li class="item">
          <div>实际支付：</div>
          <div class="value price">¥{{ data.pay_price || '0.00' }}</div>
        </li>
      </ul>
    </div>
    <div
      class="section"
      v-if="
        data.one_brokerage ||
        data.two_brokerage ||
        data.staff_brokerage ||
        data.agent_brokerage ||
        data.division_brokerage
      "
    >
      <div class="title">返佣信息(金额｜用户UID)</div>
      <ul class="list">
        <li class="item">
          <div>一级佣金：</div>
          <div class="value">{{ data.one_brokerage || '0.00' }} | {{ data.spread_uid || '0' }}</div>
        </li>
        <li class="item">
          <div>二级佣金：</div>
          <div class="value">{{ data.two_brokerage || '0.00' }} | {{ data.spread_two_uid || '0' }}</div>
        </li>
        <li class="item">
          <div>员工返佣：</div>
          <div class="value">{{ data.staff_brokerage || '0.00' }} | {{ data.staff_id || '0' }}</div>
        </li>
        <li class="item">
          <div>代理商佣金：</div>
          <div class="value">{{ data.agent_brokerage || '0.00' }} | {{ data.agent_id || '0' }}</div>
        </li>
        <li class="item">
          <div>事业部佣金：</div>
          <div class="value">{{ data.division_brokerage || '0.00' }} | {{ data.division_id || '0' }}</div>
        </li>
      </ul>
    </div>
    <div class="section" v-if="data.delivery_name">
      <div class="title">{{ data.delivery_type == 'express' ? '物流信息' : '送货人信息' }}</div>
      <ul class="list">
        <li class="item">
          <div>{{ data.delivery_type == 'express' ? '物流公司：' : '送货人姓名：' }}</div>
          <div class="value">{{ data.delivery_name || '--' }}</div>
        </li>
        <li class="item">
          <div>{{ data.delivery_type == 'express' ? '物流单号：' : '送货人电话：' }}</div>
          <div class="value">
            {{ data.delivery_id || '--' }}
            <a v-if="data.delivery_type == 'express'" v-db-click @click="$emit('openLogistics')">物流查询</a>
          </div>
        </li>
      </ul>
    </div>
    <div class="section" v-if="data.invoice && Object.keys(data.invoice).length">
      <div class="title">发票信息</div>
      <ul class="list">
        <li class="item">
          <div>发票抬头：</div>
          <div class="value">{{ data.invoice.name || '--' }}</div>
        </li>
        <li class="item" v-if="data.invoice.header_type === 2 && data.invoice.type === 1">
          <div>企业税号：</div>
          <div class="value">{{ data.invoice.duty_number || '--' }}</div>
        </li>
        <li class="item" v-if="data.invoice.header_type === 2 && data.invoice.type === 1">
          <div>发票类型：</div>
          <div class="value">电子普通发票</div>
        </li>
        <li class="item" v-if="data.invoice.header_type === 2 && data.invoice.type === 1">
          <div>发票抬头类型：</div>
          <div class="value">企业</div>
        </li>
        <li class="item" v-if="data.invoice.header_type === 1 && data.invoice.type === 1">
          <div>真实姓名：</div>
          <div class="value">{{ data.invoice.name || '--' }}</div>
        </li>
        <li class="item" v-if="data.invoice.header_type === 1 && data.invoice.type === 1">
          <div>联系电话：</div>
          <div class="value">{{ data.invoice.drawer_phone || '--' }}</div>
        </li>
        <li class="item" v-if="data.invoice.header_type === 1 && data.invoice.type === 1">
          <div>联系邮箱：</div>
          <div class="value">{{ data.invoice.email || '--' }}</div>
        </li>
        <li class="item" v-if="data.invoice.header_type === 2 && data.invoice.type === 1">
          <div>联系电话：</div>
          <div class="value">{{ data.invoice.user_phone || '--' }}</div>
        </li>
        <li class="item" v-if="data.invoice.header_type === 2 && data.invoice.type === 1">
          <div>联系邮箱：</div>
          <div class="value">{{ data.invoice.email || '--' }}</div>
        </li>
        <li class="item">
          <div>开票状态：</div>
          <div class="value">{{ data.invoice.is_invoice ? '已开票' : '未开票' }}</div>
        </li>
      </ul>
    </div>
    <div class="section">
      <div class="title">买家留言</div>
      <ul class="list">
        <li class="item item-full">
          <div class="full-text">{{ data.mark || '--' }}</div>
        </li>
      </ul>
    </div>
    <div class="section" v-if="data.custom_form && data.custom_form.length">
      <div class="title">表单信息</div>
      <ul class="list">
        <li class="item" :class="{ pic: isImageItem(item) }" v-for="(item, index) in data.custom_form" :key="index">
          <template v-if="!isImageItem(item)">
            <div>{{ item.title }}：{{ item.value }}</div>
          </template>
          <template v-else>
            <div>{{ item.title }}：</div>
            <div v-for="(img, i) in formImageUrls(item)" :key="i" class="img">
              <img v-viewer :src="img" alt="" />
            </div>
          </template>
        </li>
      </ul>
    </div>
    <div class="section">
      <div class="title">订单备注</div>
      <ul class="list">
        <li class="item item-full">
          <div class="full-text">{{ data.remark || '--' }}</div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

defineOptions({ name: 'DetailOrderInfo' });

const props = defineProps({
  data: {
    type: Object,
    default: () => ({}),
  },
});

const orderItems = computed(() => {
  return props.data.order_items || props.data.cartInfo || [];
});

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

function formatAddress(address) {
  if (!address) return '';
  if (typeof address === 'string') {
    try {
      const parsed = JSON.parse(address);
      return buildAddress(parsed);
    } catch (e) {
      return address;
    }
  }
  if (typeof address === 'object') {
    return buildAddress(address);
  }
  return '';
}

function buildAddress(addr) {
  const parts = [];
  if (addr.province) parts.push(addr.province);
  if (addr.city) parts.push(addr.city);
  if (addr.district) parts.push(addr.district);
  if (addr.detail) parts.push(addr.detail);
  if (addr.postal_code) parts.push(`(${addr.postal_code})`);
  return parts.join('');
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
</script>

<style lang="scss" scoped>
.section {
  padding: 25px 0;
  border-bottom: 1px dashed #eeeeee;

  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: var(--prev-color-text-regular);
  }

  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
  }

  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: var(--prev-color-text-primary);

    &:nth-child(3n + 1) {
      padding-right: 20px;
    }

    &:nth-child(3n + 2) {
      padding-right: 10px;
      padding-left: 10px;
    }

    &:nth-child(3n + 3) {
      padding-left: 20px;
    }

    // 买家留言 / 订单备注：占满整行，避免按三列宽度提前换行
    &.item-full {
      flex: 0 0 100%;
      padding-left: 0;
      padding-right: 0;
      word-break: break-word;
      white-space: pre-wrap;
    }
  }

  .full-text {
    width: 100%;
    line-height: 1.6;
  }

  .value {
    flex: 1;
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }

  .item.pic {
    display: flex;
    img {
      width: 80px;
      height: 80px;
    }
  }

  .price {
    color: #ed4014;
    font-weight: bold;
  }
}

.goods-table {
  margin-top: 16px;
  border: 1px solid var(--prev-border-color-lighter);
  border-radius: 4px;

  .goods-header,
  .goods-row {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    font-size: 13px;
    color: #666;
  }

  .goods-header {
    background: var(--prev-bg-color);
    color: var(--prev-color-text-secondary);
    font-weight: 500;
    border-bottom: 1px solid var(--prev-border-color-lighter);
  }

  .goods-row + .goods-row {
    border-top: 1px solid var(--prev-border-color-lighter);
  }

  .col-goods {
    flex: 1;
    display: flex;
    align-items: center;
    min-width: 0;

    .el-image {
      width: 50px;
      height: 50px;
      margin-right: 12px;
      border-radius: 4px;
      flex-shrink: 0;
    }

    .goods-name {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      color: var(--prev-color-text-regular);
    }
  }

  .col-spec,
  .col-price,
  .col-num,
  .col-subtotal {
    flex: 0 0 100px;
    text-align: center;
  }

  .col-subtotal {
    color: var(--prev-color-text-regular);
    font-weight: 500;
  }
}
</style>
