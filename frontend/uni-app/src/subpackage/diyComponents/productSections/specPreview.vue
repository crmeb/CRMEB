<template>
  <!--
    售卖形态展示位（容器组件）
    数据来源：后端 display_sections 中 key=spec 的单个 section
    渲染：遍历 section.data.blocks，按 block.type 分发到 6 个通用子渲染器
    设计原则：新增产品类型时此容器与 6 个子渲染器全部零改动
  -->
  <view v-if="section && visibleBlocks.length">
    <common-wrapper :config="dataConfig">
      <view class="spec-preview">
        <view
          class="spec-preview__block"
          v-for="(block, idx) in visibleBlocks"
          :key="idx"
        >
          <view class="spec-preview__block-title" v-if="block.title">
            <text>{{ block.title }}</text>
          </view>
          <!-- 按 block.type 分发到对应子渲染器 -->
          <block-chips
            v-if="block.type === 'chips'"
            :block="block"
          ></block-chips>
          <block-matrix
            v-else-if="block.type === 'matrix'"
            :block="block"
            :productData="productData"
            :attr="attr"
            :attrTxt="attrTxt"
            :attrValue="attrValue"
            @changeSpec="onChangeSpec"
            @showSpecModal="onShowSpecModal"
          ></block-matrix>
          <block-price-list
            v-else-if="block.type === 'price_list'"
            :block="block"
            @blockSelect="onBlockSelect"
          ></block-price-list>
          <block-time-slots
            v-else-if="block.type === 'time_slots'"
            :block="block"
            @blockSelect="onBlockSelect"
          ></block-time-slots>
          <block-calendar
            v-else-if="block.type === 'calendar'"
            :block="block"
            :selectedSkuId="selectedSkuId"
            :calendarStyle="calendarLayout"
            @blockSelect="onBlockSelect"
          ></block-calendar>
          <block-policy
            v-else-if="block.type === 'policy'"
            :block="block"
          ></block-policy>
          <!-- 未知 block.type 降级为键值对展示，避免渲染中断 -->
          <view v-else class="spec-preview__fallback">
            <text class="spec-preview__fallback-tip">
              {{ $t(`未支持的展示类型：`) }}{{ block.type }}
            </text>
          </view>
        </view>

        <!-- 日历 range 模式：未选完日期时提示 -->
        <view class="spec-preview__range-hint" v-if="hasRangeCalendar && !dateRangeCompleted">
          <text class="spec-preview__range-hint-text">{{ $t(`请先选择入住和离店日期`) }}</text>
        </view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
/**
 * 售卖形态容器组件
 * 通过 block.type 静态映射到 6 个封闭子渲染器
 * 新增产品类型时无需改此文件，后端输出对应 blocks 组合即可自动渲染
 */
import { ref, computed } from "vue";
import BlockChips from "./blocks/BlockChips.vue";
import BlockMatrix from "./blocks/BlockMatrix.vue";
import BlockPriceList from "./blocks/BlockPriceList.vue";
import BlockTimeSlots from "./blocks/BlockTimeSlots.vue";
import BlockCalendar from "./blocks/BlockCalendar.vue";
import BlockPolicy from "./blocks/BlockPolicy.vue";
import commonWrapper from "./commonWrapper.vue";

defineOptions({ name: "specPreview" });

const props = defineProps({
  // DIY 配置项
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  // 后端 display_sections 中 key=spec 的 section 对象
  section: {
    type: Object,
    default: () => null,
  },
  // 商品主数据（透传给 BlockMatrix 用于复用现有多规格交互）
  productData: {
    type: Object,
    default: () => ({}),
  },
  // 规格选择器状态（透传给 BlockMatrix）
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

const emit = defineEmits(["changeSpec", "showSpecModal", "blockSelect"]);

// 日历 range 模式：入住/离店日期是否已选完
const dateRangeCompleted = ref(false);
// 当前选中的票种 id（price_list 选中后记录，用于日历价格/状态联动）
const selectedSkuId = ref(null);

/**
 * 提取 section.data.blocks 数组
 * @return {Array}
 */
const blocks = computed(() => {
  if (!props.section || !props.section.data) return [];
  return props.section.data.blocks || [];
});
/**
 * 日历风格：从 DIY 配置读取，0=全月历，1=单行日历，默认全月历
 * 存量模板无该配置时回退到全月历，保证向后兼容
 */
const calendarLayout = computed(() => {
  const tabVal = props.dataConfig && props.dataConfig.calendarStyle && props.dataConfig.calendarStyle.tabVal;
  return tabVal === 1 ? "week" : "month";
});
/**
 * 是否存在 range 模式的日历 block（酒店场景）
 * @return {boolean}
 */
const hasRangeCalendar = computed(() => {
  return blocks.value.some(
    (b) => b.type === "calendar" && b.config && b.config.mode === "range"
  );
});
/**
 * 可见 blocks：range 模式下未选完日期时只展示日历，选完后展示全部
 * @return {Array}
 */
const visibleBlocks = computed(() => {
  if (!hasRangeCalendar.value || dateRangeCompleted.value) {
    return blocks.value;
  }
  // 未选完日期：只展示 calendar block，隐藏 matrix 等后续 block
  return blocks.value.filter((b) => b.type === "calendar");
});

function onChangeSpec(item) {
  emit("changeSpec", item);
}
function onShowSpecModal() {
  emit("showSpecModal");
}
/**
 * block 子渲染器选中（票种/场次/日期），透传选中数据给父组件
 * 父组件链路：specPreview → pageDesign → goods_details
 * goods_details 在 goCat 跳转下单页时携带选中数据
 * @param {Object} payload {type, item}
 */
function onBlockSelect(payload) {
  // 记录选中的票种（price_list），日历据此联动展示该票种当日价格/状态
  if (payload.type === "price_list" && payload.item && payload.item.id) {
    selectedSkuId.value = payload.item.id;
  }
  // 日历 range 模式：检测入住/离店是否都选完
  if (payload.type === "calendar" && payload.item) {
    const item = payload.item;
    if (item.check_in && item.check_out) {
      // 入住+离店都选完，解锁后续 block（房型矩阵）
      dateRangeCompleted.value = true;
    } else {
      dateRangeCompleted.value = false;
    }
  }
  emit("blockSelect", payload);
}
</script>

<style lang="scss" scoped>
.spec-preview {
  /* margin/background/border-radius 由 commonWrapper 控制 */

  &__block {
    padding: 24rpx;

    & + & {
      border-top: 1rpx solid #f5f5f5;
    }
  }

  &__block-title {
    padding-bottom: 16rpx;
    font-size: 28rpx;
    font-weight: 600;
    color: #333;
  }

  &__fallback {
    padding: 24rpx;
    text-align: center;

    &-tip {
      font-size: 24rpx;
      color: #999;
    }
  }

  /* range 模式：未选完日期时的提示条 */
  &__range-hint {
    padding: 20rpx 24rpx;
    text-align: center;
    border-top: 1rpx solid #f5f5f5;

    &-text {
      font-size: 26rpx;
      color: #999;
    }
  }
}
</style>
