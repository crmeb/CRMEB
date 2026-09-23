<template>
  <!--
    万能信息卡
    数据来源：后端 display_sections 中 component=info_card 的所有 section
    渲染：键值对列表（label + value），支持可选 icon 和点击行为（map/link）
    适用：配送信息、演出信息、卡密使用说明、酒店设施、退改政策等
  -->
  <view class="info-card" v-if="sections.length">
    <common-wrapper :config="dataConfig">
      <view
        class="info-card__section"
        v-for="(section, sIdx) in sections"
        :key="sIdx"
      >
        <!-- 区块标题（section.title 为空则不显示） -->
        <view class="info-card__title" v-if="section.title">
          <text class="info-card__title-text">{{ section.title }}</text>
        </view>
        <!-- 键值对列表 -->
        <view class="info-card__items">
          <view
            class="info-card__item"
            v-for="(item, idx) in getItems(section)"
            :key="idx"
            @click="onItemTap(item)"
          >
            <view class="info-card__item-label">
              <text
                v-if="item.icon"
                class="iconfont"
                :class="iconClass(item.icon)"
              ></text>
              <text>{{ item.label }}</text>
            </view>
            <view class="info-card__item-value">
              <text>{{ item.value }}</text>
              <text
                v-if="item.action"
                class="iconfont icon-xiangyou info-card__arrow"
              ></text>
            </view>
          </view>
        </view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
/**
 * 万能信息卡组件
 * 接收 PageDesign 透传的同类型 sections 数组，按 section.title 分组渲染
 * 单个 item 支持 action（type=map 打开地图 / type=link 跳转页面）
 */
import commonWrapper from "./commonWrapper.vue";

defineOptions({ name: "infoCard" });

defineProps({
  // DIY 配置项（含 title、样式等，运营可调）
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  // 后端 display_sections 中 component=info_card 的所有项
  sections: {
    type: Array,
    default: () => [],
  },
});

/**
 * 提取 section 的 items，兼容 data.items / data.list 两种字段
 * @param {Object} section
 * @return {Array}
 */
function getItems(section) {
  if (!section || !section.data) return [];
  return section.data.items || section.data.list || [];
}
/**
 * 图标标识 → iconfont 类名映射
 * 后端只下发 icon 标识（express/clock 等），前端负责映射到具体图标
 * @param {string} key
 * @return {string}
 */
function iconClass(key) {
  const map = {
    express: "icon-kuaidi",
    clock: "icon-shijian",
    truck: "icon-huoche",
    location: "icon-dingwei",
    qrcode: "icon-erweima",
    info: "icon-tishi",
    phone: "icon-dianhua",
  };
  return map[key] || "icon-tishi";
}
/**
 * item 点击行为
 * action.type=map：打开地图导航
 * action.type=link：跳转页面
 * @param {Object} item
 */
function onItemTap(item) {
  if (!item.action) return;
  const action = item.action;
  if (action.type === "map" && action.lat && action.lng) {
    uni.openLocation({
      latitude: parseFloat(action.lat),
      longitude: parseFloat(action.lng),
      name: action.name || "",
      scale: 18,
    });
  } else if (action.type === "link" && action.url) {
    uni.navigateTo({ url: action.url });
  }
}
</script>

<style lang="scss" scoped>
.info-card {
  /* margin/background/border-radius 由 commonWrapper 控制 */

  &__section {
    padding: 24rpx 24rpx 8rpx;

    & + & {
      border-top: 1rpx solid #f5f5f5;
    }
  }

  &__title {
    padding-bottom: 16rpx;

    &-text {
      font-size: 28rpx;
      font-weight: 600;
      color: #333;
    }
  }

  &__items {
    /* 键值对列表容器 */
  }

  &__item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16rpx 0;
    font-size: 26rpx;

    &-label {
      display: flex;
      align-items: center;
      color: #999;

      .iconfont {
        margin-right: 8rpx;
        font-size: 28rpx;
        color: #999;
      }
    }

    &-value {
      display: flex;
      align-items: center;
      color: #333;
      text-align: right;
      flex: 1;
      justify-content: flex-end;
      padding-left: 24rpx;
    }
  }

  &__arrow {
    margin-left: 8rpx;
    font-size: 24rpx;
    color: #ccc;
  }
}
</style>
