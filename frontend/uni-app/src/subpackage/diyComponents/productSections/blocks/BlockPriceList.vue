<template>
  <!--
    BlockPriceList 起价列表
    渲染：选项列表（名称 + 起价 + 标签 + 库存状态）
    适用：门票票种、酒店房型、套餐份量、服务项目
    交互：点击选中高亮 + 提示"请点击底部立即购买"，详情页只读预览不采集下单数据
  -->
  <view class="block-price-list" v-if="items.length">
    <view
      class="block-price-list__item"
      v-for="(item, idx) in items"
      :key="idx"
      :class="{
        'block-price-list__item--sold-out': isSoldOut(item),
        'block-price-list__item--active': isSelected(item),
      }"
      @click="onSelect(item)"
    >
      <image
        v-if="item.image"
        class="block-price-list__image"
        :src="item.image"
        mode="aspectFill"
      />
      <view class="block-price-list__main">
        <view class="block-price-list__name">{{ item.name }}</view>
        <view class="block-price-list__tags" v-if="item.tags && item.tags.length">
          <text
            class="block-price-list__tag"
            v-for="(tag, tIdx) in item.tags"
            :key="tIdx"
          >
            {{ tag }}
          </text>
        </view>
      </view>
      <view class="block-price-list__right">
        <view class="block-price-list__price" v-if="item.price_from !== undefined && item.price_from !== null">
          <text class="block-price-list__price-symbol">¥</text>
          <text class="block-price-list__price-from">{{ item.price_from }}</text>
          <text class="block-price-list__price-suffix">{{ $t(`起`) }}</text>
        </view>
        <view class="block-price-list__stock" v-if="item.stock_status">
          <text :class="stockClass(item.stock_status)">{{ stockText(item.stock_status, item.stock_count) }}</text>
        </view>
      </view>
      <!-- 选中指示 -->
      <view class="block-price-list__check" v-if="isSelected(item)">
        <view class="iconfont icon-gou"></view>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * 起价列表子渲染器
 * block.items 结构：[{name, price_from, tags, image, stock_status, stock_count}]
 * stock_status 枚举：sufficient 充足 / tight 紧张 / sold_out 售罄
 *
 * 交互设计（符合设计文档 11.5 节职责边界）：
 * - 详情页只读预览，不采集下单数据
 * - 点击可购项 → 高亮选中 + 提示"请点击底部立即购买"
 * - 点击售罄项 → 提示"已售罄"
 * - 选中状态仅为视觉反馈，不参与下单采集
 */
import { ref, computed } from "vue";
import i18n from "@/utils/lang.js";

defineOptions({ name: "BlockPriceList" });

const props = defineProps({
  block: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(["blockSelect"]);

// 当前选中项的 name（唯一标识）
const selectedName = ref("");

const items = computed(() => {
  return (props.block && props.block.items) || [];
});

function isSoldOut(item) {
  return item.stock_status === "sold_out";
}
/**
 * 判断是否选中
 * @param {Object} item
 * @return {boolean}
 */
function isSelected(item) {
  return selectedName.value && selectedName.value === item.name;
}
/**
 * 点击票种/房型
 * 售罄拦截，可购项高亮选中 + emit 选中数据给父组件
 * 父组件（goods_details）会在跳转下单页时携带选中数据
 * @param {Object} item 票种/房型数据
 */
function onSelect(item) {
  if (isSoldOut(item)) {
    return uni.showToast({ title: i18n.t("已售罄"), icon: "none" });
  }
  selectedName.value = item.name;
  // 通知父组件当前选中项，跳转下单页时携带
  emit("blockSelect", { type: "price_list", item });
}
/**
 * 库存状态文案
 */
function stockText(status, count) {
  if (status === "sold_out") return "已售罄";
  if (status === "tight") {
    return count !== undefined && count !== null ? `仅剩 ${count}` : "紧张";
  }
  if (status === "sufficient") return "充足";
  return "";
}
/**
 * 库存状态样式类
 */
function stockClass(status) {
  const map = {
    sufficient: "block-price-list__stock--sufficient",
    tight: "block-price-list__stock--tight",
    sold_out: "block-price-list__stock--sold-out",
  };
  return map[status] || "";
}
</script>

<style lang="scss" scoped>
.block-price-list {
  &__item {
    position: relative;
    display: flex;
    align-items: center;
    padding: 20rpx;
    margin-bottom: 16rpx;
    background: #f7f8fa;
    border: 1rpx solid transparent;
    border-radius: 12rpx;
    transition: all 0.2s;

    &--sold-out {
      opacity: 0.5;
    }

    // 选中高亮样式：整行框选（对齐场次 BlockTimeSlots 的选中风格）
    &--active {
      background: var(--view-op-point-four);
      border-color: var(--view-theme, #e93323);
    }
  }

  &__image {
    width: 96rpx;
    height: 96rpx;
    border-radius: 8rpx;
    margin-right: 20rpx;
    flex-shrink: 0;
  }

  &__main {
    flex: 1;
    min-width: 0;
  }

  &__name {
    font-size: 28rpx;
    color: #333;
    margin-bottom: 8rpx;

    // 选中时名称高亮
    .block-price-list__item--active & {
      color: var(--view-theme, #e93323);
      font-weight: 600;
    }
  }

  &__tags {
    display: flex;
    flex-wrap: wrap;
  }

  &__tag {
    display: inline-block;
    padding: 2rpx 12rpx;
    margin-right: 8rpx;
    margin-bottom: 4rpx;
    font-size: 20rpx;
    color: #ff7a00;
    background: rgba(255, 122, 0, 0.08);
    border-radius: 4rpx;
  }

  &__right {
    text-align: right;
    padding-left: 20rpx;
    flex-shrink: 0;
  }

  &__price {
    margin-bottom: 6rpx;

    &-symbol {
      font-size: 22rpx;
      color: var(--view-theme, #e93323);
    }

    &-from {
      font-size: 34rpx;
      font-weight: 600;
      color: var(--view-theme, #e93323);
    }

    &-suffix {
      font-size: 22rpx;
      color: var(--view-theme, #e93323);
    }
  }

  &__stock {
    font-size: 22rpx;

    &--sufficient {
      color: #52c41a;
    }

    &--tight {
      color: #ff7a00;
    }

    &--sold-out {
      color: #999;
    }
  }

  // 选中勾选图标：右上角扇形角标，勾选整行（不再遮挡右侧价格/库存）
  &__check {
    position: absolute;
    right: 0;
    top: 0;
    width: 36rpx;
    height: 36rpx;
    /* 左下 100% 圆角构成扇形，右上 12rpx 与卡片圆角贴合 */
    border-radius: 0 12rpx 0 100%;
    background: var(--view-theme, #e93323);
    display: flex;
    align-items: center;
    justify-content: center;

    .icon-gou {
      color: #fff;
      font-size: 20rpx;
      /* 图标朝扇形直角侧微移，避免贴到弧边 */
      transform: translate(2rpx, -2rpx);
    }
  }
}
</style>
