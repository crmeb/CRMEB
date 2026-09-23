<template>
  <!--
    门店/场馆卡
    数据来源：后端 display_sections 中 component=store_card 的所有 section
    渲染：默认只展示一个门店，样式与后台 DIY 预览对齐——
          浅灰圆角卡片（红色图标块 + 名称/地址 + 电话/导航 + 底部标签行）
    多门店时点击卡片或切换条弹出门店选择器，可点击切换（类似"到店自提"）
    适用：自提门店、演出场馆、酒店地址、核销门店
  -->
  <view class="store-card" v-if="currentSection">
    <common-wrapper :config="dataConfig">
      <!-- 区块标题（后台配置"显示标题"控制，样式跟随标题配置） -->
      <view class="store-card__title" v-if="titleShow">
        <text class="store-card__title-text" :style="titleStyle">{{ currentSection.title }}</text>
      </view>

      <!-- 当前门店卡片 -->
      <view class="store-card__box">
        <view class="store-card__header">
          <!-- 门店图标块 -->
          <view class="store-card__icon">
            <text class="iconfont icon-mendian store-card__icon-text"></text>
          </view>
          <!-- 门店信息：点击主体弹出切换（多门店时） -->
          <view class="store-card__main" @click="onOpenPicker">
            <view class="store-card__name">{{ getName(currentSection) }}</view>
            <view class="store-card__address" v-if="getAddress(currentSection)">
              <text class="store-card__address-text">{{ getAddress(currentSection) }}</text>
            </view>
          </view>
          <!-- 操作区 -->
          <view class="store-card__actions">
            <!-- 拨打电话 -->
            <view
              class="store-card__action"
              v-if="getPhone(currentSection)"
              @click.stop="onCall(getPhone(currentSection))"
            >
              <text class="iconfont icon-dianhua"></text>
            </view>
            <!-- 地图导航 -->
            <view
              class="store-card__action store-card__action--nav"
              @click.stop="onNavigate(currentSection)"
            >
              <text class="iconfont icon-daohangdaodian"></text>
            </view>
          </view>
        </view>
        <!-- 标签行：距离/类型 -->
        <view class="store-card__tags" v-if="getTags(currentSection).length">
          <text
            class="store-card__tag"
            v-for="(tag, tIdx) in getTags(currentSection)"
            :key="tIdx"
          >{{ tag }}</text>
        </view>
      </view>

      <!-- 多门店时展示"切换门店"入口条（文字 + 右箭头） -->
      <view class="store-card__switch" v-if="sections.length > 1" @click="onOpenPicker">
        <text class="store-card__switch-text">{{ $t('切换门店') }}</text>
        <text class="iconfont icon-xiangyou store-card__switch-icon"></text>
      </view>
    </common-wrapper>

    <!-- 门店选择弹窗（底部弹出） -->
    <view class="store-picker__mask" v-if="pickerShow" @click="onClosePicker">
      <view class="store-picker__panel" @click.stop>
        <view class="store-picker__header">
          <text class="store-picker__title">{{ $t('选择门店') }}</text>
          <text class="iconfont icon-guanbi1 store-picker__close" @click="onClosePicker"></text>
        </view>
        <scroll-view class="store-picker__list" scroll-y>
          <view
            class="store-picker__item"
            :class="{ 'store-picker__item--active': idx === currentIndex }"
            v-for="(section, idx) in sections"
            :key="idx"
            @click="onSelect(idx)"
          >
            <view class="store-picker__item-main">
              <view class="store-picker__item-name">
                <text>{{ getName(section) }}</text>
                <text class="iconfont icon-duihao store-picker__item-check" v-if="idx === currentIndex"></text>
              </view>
              <view class="store-picker__item-address" v-if="getAddress(section)">
                {{ getAddress(section) }}
              </view>
            </view>
          </view>
        </scroll-view>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * 门店/场馆卡组件
 * 接收 PageDesign 透传的同类型 sections 数组，默认展示第一个门店
 * 样式与后台 DIY 预览（home_store_card）对齐：灰底圆角卡片 + 图标块 + 标签行
 * 多门店时支持点击弹窗切换，电话按钮触发拨号，导航按钮打开地图
 */
import { ref, computed, onMounted } from "vue";
import commonWrapper from "./commonWrapper.vue";
import i18n from "@/utils/lang.js";

defineOptions({ name: "storeCard" });

const props = defineProps({
  // DIY 配置项（标题显隐/颜色/字号/对齐）
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  // 后端 display_sections 中 component=store_card 的所有项
  sections: {
    type: Array,
    default: () => [],
  },
});

// 当前选中的门店索引（默认第一个）
const currentIndex = ref(0);
// 门店选择弹窗显隐
const pickerShow = ref(false);
// 用户定位坐标（用于计算门店距离，获取失败则为 null）
const userLocation = ref(null);

onMounted(() => {
  // 获取用户定位用于计算门店距离；失败/拒绝授权时静默隐藏距离标签，仅留调试日志
  uni.getLocation({
    type: "wgs84",
    success: (res) => {
      userLocation.value = {
        latitude: res.latitude,
        longitude: res.longitude,
      };
    },
    fail: (err) => {
      console.warn("[storeCard] 获取定位失败，距离不显示", err);
    },
  });
});

// 当前展示的门店 section，索引越界时回退到第一个
const currentSection = computed(() => {
  return props.sections[currentIndex.value] || props.sections[0] || null;
});
// 标题是否显示：后台"显示标题"配置 + section 携带 title
const titleShow = computed(() => {
  const isShow = props.dataConfig.isShow;
  const show = isShow ? isShow.tabVal === 0 : true;
  return show && !!(currentSection.value && currentSection.value.title);
});
// 标题样式：跟随后台标题配置（颜色/字号/对齐）
const titleStyle = computed(() => {
  const color =
    (props.dataConfig.textColor &&
      props.dataConfig.textColor.color &&
      props.dataConfig.textColor.color[0].item) ||
    "#333";
  const fontSize = (props.dataConfig.fontSize && props.dataConfig.fontSize.val) || 16;
  const textAlign = (props.dataConfig.textPosition && props.dataConfig.textPosition.val) || "left";
  return {
    color,
    fontSize: fontSize * 2 + "rpx",
    textAlign,
  };
});

function getName(section) {
  return (section.data && section.data.name) || "";
}
function getAddress(section) {
  return (section.data && section.data.address) || "";
}
function getPhone(section) {
  return (section.data && section.data.phone) || "";
}
/**
 * 提取标签行数据：距离 / 营业时间 / 类型
 * 距离优先取后端下发的 distance，否则按用户定位实时计算
 * @param {Object} section
 * @return {Array}
 */
function getTags(section) {
  const data = (section && section.data) || {};
  const tags = [];
  const distance = data.distance || formatDistance(data);
  if (distance) tags.push(distance);
  // 营业时间带前缀展示
  if (data.business_hours) tags.push(i18n.t("营业时间") + "：" + data.business_hours);
  if (data.store_type_label) tags.push(data.store_type_label);
  return tags;
}
/**
 * 根据用户定位与门店坐标格式化距离（"距您 2.3km" / "距您 800m"）
 * @param {Object} data 门店数据（lat/lng）
 * @return {string}
 */
function formatDistance(data) {
  if (!userLocation.value || !data.lat || !data.lng) return "";
  const meters = calcDistance(
    userLocation.value.latitude,
    userLocation.value.longitude,
    parseFloat(data.lat),
    parseFloat(data.lng)
  );
  if (meters < 1000) {
    return i18n.t("距您") + " " + meters + "m";
  }
  return i18n.t("距您") + " " + (meters / 1000).toFixed(1) + "km";
}
/**
 * Haversine 公式计算两坐标间球面距离（米）
 * @param {number} lat1
 * @param {number} lng1
 * @param {number} lat2
 * @param {number} lng2
 * @return {number}
 */
function calcDistance(lat1, lng1, lat2, lng2) {
  const rad = (d) => (d * Math.PI) / 180;
  const R = 6371000;
  const dLat = rad(lat2 - lat1);
  const dLng = rad(lng2 - lng1);
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(dLng / 2) ** 2;
  return Math.round(R * 2 * Math.asin(Math.sqrt(a)));
}
/**
 * 打开门店选择弹窗（仅多门店时可切换）
 */
function onOpenPicker() {
  if (props.sections.length > 1) {
    pickerShow.value = true;
  }
}
/**
 * 关闭门店选择弹窗
 */
function onClosePicker() {
  pickerShow.value = false;
}
/**
 * 选中门店并关闭弹窗
 * @param {number} idx 门店索引
 */
function onSelect(idx) {
  currentIndex.value = idx;
  pickerShow.value = false;
}
/**
 * 打开地图导航
 * 经纬度存在时调用 uni.openLocation，否则提示
 */
function onNavigate(section) {
  const data = (section && section.data) || {};
  if (!data.lat || !data.lng) {
    return uni.showToast({
      title: i18n.t("暂无位置信息"),
      icon: "none",
    });
  }
  uni.openLocation({
    latitude: parseFloat(data.lat),
    longitude: parseFloat(data.lng),
    name: data.name || "",
    address: data.address || "",
    scale: 18,
  });
}
/**
 * 拨打电话
 */
function onCall(phone) {
  if (!phone) return;
  uni.makePhoneCall({ phoneNumber: String(phone) });
}
</script>

<style lang="scss" scoped>
.store-card {
  /* margin/background/border-radius 由 commonWrapper 控制 */

  &__title {
    padding: 24rpx 24rpx 16rpx;

    &-text {
      font-size: 28rpx;
      font-weight: 600;
      color: #333;
      display: block;
    }
  }

  /* 门店卡片：浅灰底圆角，与后台 DIY 预览一致 */
  &__box {
    margin: 0 24rpx;
    padding: 20rpx;
    background: #f7f8fa;
    border-radius: 12rpx;
  }

  &__header {
    display: flex;
    align-items: center;
  }

  /* 左侧红色图标块 */
  &__icon {
    width: 72rpx;
    height: 72rpx;
    background: var(--view-theme, #e93323);
    border-radius: 12rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 16rpx;
    opacity: 0.85;

    &-text {
      font-size: 36rpx;
      color: #fff;
    }
  }

  &__main {
    flex: 1;
    min-width: 0;
  }

  &__name {
    font-size: 28rpx;
    font-weight: 600;
    color: #333;
    line-height: 1.4;
  }

  &__address {
    font-size: 24rpx;
    color: #999;
    margin-top: 6rpx;
    line-height: 1.4;
  }

  &__actions {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    padding-left: 16rpx;
  }

  &__action {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 64rpx;
    height: 64rpx;
    border-radius: 50%;
    background: #fff;
    margin-left: 12rpx;

    .iconfont {
      font-size: 30rpx;
      color: #666;
    }

    &--nav {
      background: var(--view-theme, #e93323);

      .iconfont {
        color: #fff;
      }
    }
  }

  /* 标签行：距离/类型 */
  &__tags {
    display: flex;
    flex-wrap: wrap;
    margin-top: 16rpx;
  }

  &__tag {
    font-size: 20rpx;
    color: var(--view-theme, #e93323);
    background: var(--view-op-ten);
    padding: 4rpx 14rpx;
    border-radius: 6rpx;
    margin-right: 12rpx;
  }

  /* 点击切换门店入口条 */
  &__switch {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20rpx 0;
    margin: 0 24rpx;
    border-top: 1rpx solid #f5f5f5;

    &-text {
      font-size: 24rpx;
      color: var(--view-theme, #e93323);
    }

    &-icon {
      font-size: 24rpx;
      color: var(--view-theme, #e93323);
      margin-left: 4rpx;
    }
  }
}

/* 门店选择弹窗 */
.store-picker {
  &__mask {
    position: fixed;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: flex;
    align-items: flex-end;
  }

  &__panel {
    width: 100%;
    max-height: 60vh;
    background: #fff;
    border-radius: 24rpx 24rpx 0 0;
    display: flex;
    flex-direction: column;
    animation: store-picker-slide-up 0.25s ease;
  }

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 30rpx 32rpx;
    border-bottom: 1rpx solid #f5f5f5;
    flex-shrink: 0;
  }

  &__title {
    font-size: 30rpx;
    font-weight: 600;
    color: #333;
  }

  &__close {
    font-size: 32rpx;
    color: #999;
    padding: 8rpx;
  }

  &__list {
    max-height: 50vh;
  }

  &__item {
    display: flex;
    padding: 28rpx 32rpx;
    border-bottom: 1rpx solid #f5f5f5;

    &--active {
      background: var(--view-op-point-four);
    }

    &-main {
      flex: 1;
      min-width: 0;
    }

    &-name {
      display: flex;
      align-items: center;
      font-size: 28rpx;
      font-weight: 600;
      color: #333;
      margin-bottom: 8rpx;
    }

    &-check {
      margin-left: 10rpx;
      font-size: 28rpx;
      color: var(--view-theme, #e93323);
    }

    &-address {
      font-size: 24rpx;
      color: #999;
      line-height: 1.5;
    }
  }
}

@keyframes store-picker-slide-up {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}
</style>
