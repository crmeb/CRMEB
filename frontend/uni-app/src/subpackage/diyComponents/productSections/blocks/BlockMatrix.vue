<template>
  <!--
    BlockMatrix 规格矩阵
    渲染：规格选择交互（选规格看价/图/库存），不采集下单数据
    设计原则：保留现有多规格体验，仅展示，不动下单采集逻辑
    适用：实物/虚拟商品多规格 SKU 矩阵预览
  -->
  <view class="block-matrix">
    <!-- 规格属性列表 -->
    <view
      class="block-matrix__attr"
      v-for="(attr, aIdx) in attrs"
      :key="aIdx"
    >
      <view class="block-matrix__attr-name">{{ attr.name }}</view>
      <view class="block-matrix__attr-values">
        <view
          class="block-matrix__attr-value"
          :class="{
            'block-matrix__attr-value--active': isActive(attr, value),
            'block-matrix__attr-value--disabled': isDisabled(attr, value),
          }"
          v-for="(value, vIdx) in attr.values"
          :key="vIdx"
          @click="onSelectAttr(attr, value)"
        >
          <text>{{ value }}</text>
        </view>
      </view>
    </view>

    <!-- 当前选中规格信息 -->
    <view class="block-matrix__current" v-if="currentSku">
      <view class="block-matrix__current-info">
        <image
          v-if="currentSku.image"
          class="block-matrix__current-image"
          :src="currentSku.image"
          mode="aspectFill"
        />
        <view class="block-matrix__current-detail">
          <view class="block-matrix__current-price" v-if="currentSku.price !== undefined">
            <text class="block-matrix__price-symbol">¥</text>
            <text class="block-matrix__price-num">{{ currentSku.price }}</text>
          </view>
          <view class="block-matrix__current-stock" v-if="currentSku.stock !== undefined">
            <text>{{ stockText(currentSku.stock) }}</text>
          </view>
          <view class="block-matrix__current-suk" v-if="currentSku.suk">
            <text>已选：{{ currentSku.suk }}</text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * 规格矩阵子渲染器
 * 复用现有 productWindow 的规格选择交互逻辑（简化版），仅展示不下单
 * 通过 emit changeSpec 通知父组件更新 attr 状态，emit showSpecModal 打开完整规格弹窗
 */
import { computed } from "vue";

defineOptions({ name: "BlockMatrix" });

const props = defineProps({
  // block 对象（含 attrs/skus/current_sku）
  block: {
    type: Object,
    default: () => ({}),
  },
  // 商品主数据
  productData: {
    type: Object,
    default: () => ({}),
  },
  // 规格选择器状态（来自 PageDesign 透传的 attr）
  attr: {
    type: Object,
    default: () => ({}),
  },
  attrTxt: {
    type: String,
    default: "",
  },
  attrValue: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["changeSpec", "showSpecModal"]);

/**
 * 规格属性列表
 * @return {Array}
 */
const attrs = computed(() => {
  return (props.block && props.block.attrs) || [];
});
/**
 * SKU 列表（对象或数组均兼容）
 * @return {Array}
 */
const skus = computed(() => {
  const skus = (props.block && props.block.skus) || [];
  if (Array.isArray(skus)) return skus;
  if (skus && typeof skus === "object") return Object.values(skus);
  return [];
});
/**
 * 当前选中 SKU
 * 优先用 attr.productSelect（来自父组件规格状态），否则取第一个 SKU
 * @return {Object|null}
 */
const currentSku = computed(() => {
  if (props.attr && props.attr.productSelect && props.attr.productSelect.suk) {
    return props.attr.productSelect;
  }
  return skus.value[0] || null;
});

/**
 * 判断某规格值是否为当前选中
 * 注：参数 attr 为模板 v-for 的规格项，与同名 prop 经 props.attr 访问互不冲突
 */
function isActive(attr, value) {
  if (!props.attr || !props.attr.productAttr) return false;
  const target = props.attr.productAttr.find((a) => a.name === attr.name);
  return target && target.index === value;
}
/**
 * 判断某规格值是否禁用（无对应 SKU）
 */
function isDisabled(attr, value) {
  // 简化判断：若没有任何 SKU 包含该值则禁用
  return !skus.value.some((s) => s.suk && s.suk.includes(value));
}
/**
 * 点击规格值，打开现有规格选择弹窗（productWindow）
 * 设计原则：不动现有多规格体验，统一走 productWindow 弹窗采集
 * BlockMatrix 仅负责展示选中规格的价/图/库存，不自行维护规格状态
 * @param {Object} attr 规格属性对象
 * @param {string} value 规格值
 */
function onSelectAttr(attr, value) {
  if (isDisabled(attr, value)) return;
  // 触发父组件打开 productWindow 规格弹窗，由弹窗统一处理规格选择
  emit("showSpecModal");
}
/**
 * 库存文案
 */
function stockText(stock) {
  const num = parseInt(stock, 10);
  if (isNaN(num) || num <= 0) return "已售罄";
  if (num < 10) return `仅剩 ${num} 件`;
  return `库存 ${num} 件`;
}
</script>

<style lang="scss" scoped>
.block-matrix {
  &__attr {
    margin-bottom: 24rpx;

    &-name {
      font-size: 26rpx;
      color: #666;
      margin-bottom: 12rpx;
    }

    &-values {
      display: flex;
      flex-wrap: wrap;
    }

    &-value {
      padding: 12rpx 24rpx;
      margin: 0 16rpx 16rpx 0;
      background: #f7f8fa;
      border: 1rpx solid transparent;
      border-radius: 8rpx;
      font-size: 26rpx;
      color: #333;

      &--active {
        background: var(--view-op-point-four);
        border-color: var(--view-theme, #e93323);
        color: var(--view-theme, #e93323);
      }

      &--disabled {
        color: #ccc;
        background: #fafafa;
        text-decoration: line-through;
      }
    }
  }

  &__current {
    margin-top: 16rpx;
    padding: 20rpx;
    background: #fafafa;
    border-radius: 12rpx;

    &-info {
      display: flex;
      align-items: center;
    }

    &-image {
      width: 120rpx;
      height: 120rpx;
      border-radius: 8rpx;
      margin-right: 20rpx;
      flex-shrink: 0;
    }

    &-detail {
      flex: 1;
      min-width: 0;
    }

    &-price {
      margin-bottom: 8rpx;
    }

    &-stock {
      font-size: 24rpx;
      color: #999;
      margin-bottom: 4rpx;
    }

    &-suk {
      font-size: 24rpx;
      color: #666;
    }
  }

  &__price-symbol {
    font-size: 24rpx;
    color: var(--view-theme, #e93323);
  }

  &__price-num {
    font-size: 36rpx;
    font-weight: 600;
    color: var(--view-theme, #e93323);
  }
}
</style>
