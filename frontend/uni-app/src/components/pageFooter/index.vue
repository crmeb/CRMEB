<template>
  <!-- 底部导航 -->
  <view v-if="showTabBar">
    <view class="fixed-lb w-full pb-safe z-999" :style="[bgColor]">
      <view class="page-footer-wrapper">
        <view
          class="page-footer"
          :class="{
            'page-footer2': newData.navStyleConfig.tabVal == 1,
            'page-footer3': newData.navStyleConfig.tabVal == 2,
          }"
          id="target"
          :style="[componentStyle]"
        >
          <view
            class="foot-item flex-1 flex-col flex-center h-96 relative"
            v-for="(item, index) in newData.menuList"
            :key="index"
            @click="goRouter(item)"
          >
            <template v-if="item.link.split('?')[0] == activeRouter">
              <image
                v-if="newData.navStyleConfig.tabVal != 1"
                :src="item.imgList[0]"
              ></image>
              <view
                v-if="newData.navStyleConfig.tabVal != 2"
                class="txt active"
                :style="[txtActiveColor]"
                >{{ item.name }}</view
              >
            </template>
            <template v-else>
              <image
                v-if="newData.navStyleConfig.tabVal != 1"
                :src="item.imgList[1]"
              ></image>
              <view
                v-if="newData.navStyleConfig.tabVal != 2"
                class="txt"
                :style="[txtColor]"
                >{{ item.name }}</view
              >
            </template>
            <BaseBadge
              v-if="
                item.link === '/pages/order_addcart/order_addcart' &&
                cartNum > 0
              "
              class="uni-badge-left-margin"
              :text="cartNum"
              absolute="rightTop"
            ></BaseBadge>
          </view>
        </view>
      </view>
    </view>
    <view :style="{ height: `${footerHeight}px` }"></view>
    <view class="safe-area-inset-bottom"></view>
  </view>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useAppStore } from "@/store/modules/app";
import { useIndexDataStore } from "@/store/modules/indexData";
import { getNavigation } from "@/api/public.js";
// import {getCartCounts} from '@/api/order.js';
import { getDiyVersion } from "@/api/api.js";
import BaseBadge from "@/components/BaseBadge/index.vue";

defineOptions({ name: "pageFooter" });

const props = defineProps({
  isTabBar: {
    type: Boolean,
    default: true,
  },
  configData: {
    type: Object,
    default: () => {},
  },
});

const emit = defineEmits(["newDataStatus"]);

// 原 computed: mapGetters(["isLogin", "cartNum"])
const appStore = useAppStore();
const indexDataStore = useIndexDataStore();
const { isLogin } = storeToRefs(appStore);
const { cartNum } = storeToRefs(indexDataStore);

// ---------- data ----------
const newData = ref({});
const activeRouter = ref("");
const showTabBar = ref(false);
const footerHeight = ref(0);

// ---------- computed ----------
const txtActiveColor = computed(() => {
  let styleObject = {};
  if (newData.value.toneConfig && newData.value.toneConfig.tabVal) {
    styleObject["color"] = newData.value.activeTxtColor.color[0].item;
  }
  return styleObject;
});
const txtColor = computed(() => {
  let styleObject = {};
  if (newData.value.toneConfig && newData.value.toneConfig.tabVal) {
    styleObject["color"] = newData.value.txtColor.color[0].item;
  }
  return styleObject;
});
const bgColor = computed(() => {
  let styleObject = {};
  if (!newData.value.name) {
    return styleObject;
  }
  if (!newData.value.navConfig.tabVal) {
    styleObject["background"] = newData.value.bgColor.color[0].item;
  }
  return styleObject;
});
const componentStyle = computed(() => {
  let styleObject = {};
  let borderRadius = ``;
  if (!newData.value.name) {
    return styleObject;
  }
  if (newData.value.navConfig.tabVal) {
    borderRadius = `${newData.value.fillet.val * 2}rpx`;
    if (newData.value.fillet.type) {
      borderRadius = `${newData.value.fillet.valList[0].val * 2}rpx ${newData.value.fillet.valList[1].val * 2}rpx ${newData.value.fillet.valList[3].val * 2}rpx ${
        newData.value.fillet.valList[2].val * 2
      }rpx`;
    }
    styleObject["right"] = `${newData.value.prConfig.val * 2}rpx`;
    styleObject["bottom"] = `${newData.value.mbConfig.val * 2}rpx`;
    styleObject["left"] = `${newData.value.prConfig.val * 2}rpx`;
    styleObject["padding-top"] = `${newData.value.topConfig.val * 2}rpx`;
    styleObject["padding-bottom"] =
      `${newData.value.bottomConfig.val * 2}rpx`;
    styleObject["border-radius"] = borderRadius;
    styleObject["background"] = newData.value.bgColor2.color[0].item;
  } else {
    styleObject["padding-top"] = `${newData.value.topConfig.val * 2}rpx`;
    styleObject["padding-bottom"] =
      `${newData.value.bottomConfig.val * 2}rpx`;
    styleObject["background"] = newData.value.bgColor.color[0].item;
  }
  return styleObject;
});

// ---------- watch ----------
watch(
  () => props.configData,
  (newVal) => {
    if (newVal) {
      let configData = newVal;
      newData.value = configData;
      showTabBar.value = configData.effectConfig.tabVal;
    }
  },
  {
    deep: true,
    immediate: true,
  }
);

// ---------- created（顶层直写） ----------
{
  let routes = getCurrentPages(); //获取当前打开过的页面路由数组
  let curRoute = routes[routes.length - 1].route; //获取当前页面路由
  activeRouter.value = "/" + curRoute;
}

// ---------- mounted ----------
onMounted(() => {
  navigationInfo();
  // if (this.isLogin) {
  // 	this.getCartNum()
  // }
});

// ---------- methods ----------
function setNavigationInfo(data) {
  if (props.isTabBar) {
    newData.value = data;
    showTabBar.value = data.effectConfig.tabVal;
    let pdHeight = data.topConfig.val + data.bottomConfig.val;
    emit(
      "newDataStatus",
      data.effectConfig.tabVal,
      pdHeight,
      data.mbConfig.val,
      data.navConfig.tabVal
    );
    if (data.effectConfig.tabVal) {
      uni.hideTabBar();
    } else {
      uni.showTabBar();
    }
  }
}
function getNavigationInfo() {
  let data = {};
  // 主题预览时传入预览主题 id，获取预览主题的底部菜单数据
  let previewThemeId = uni.getStorageSync("previewThemeId");
  if (previewThemeId) data.theme_id = previewThemeId;
  getNavigation(data).then((res) => {
    uni.setStorageSync("diyVersionNav", res.data);
    setNavigationInfo(res.data);
  });
}
function navigationInfo() {
  let previewThemeId = uni.getStorageSync("previewThemeId");
  // 预览主题时不使用缓存，直接获取预览主题的底部菜单数据
  let footerNavigation = previewThemeId ? null : uni.getStorageSync("footerNavigation");
  if (footerNavigation) {
    getDiyVersion(0).then((res) => {
      let diyVersion = uni.getStorageSync("diyVersionNav");
      if (res.data.version + "0" === diyVersion) {
        setNavigationInfo(footerNavigation);
      } else {
        uni.setStorageSync("diyVersionNav", res.data.version + "0");
        getNavigationInfo();
      }
    });
  } else {
    getNavigationInfo();
  }
}
function goRouter(item) {
  var pages = getCurrentPages();
  var page = pages[pages.length - 1].$page.fullPath;
  if (item.link == page) return;
  if (
    item.link == "/pages/short_video/appSwiper/index" ||
    item.link == "/pages/short_video/nvueSwiper/index"
  ) {
    //#ifdef APP
    item.link = "/pages/short_video/appSwiper/index";
    //#endif
    //#ifndef APP
    item.link = "/pages/short_video/nvueSwiper/index";
    //#endif
  }
  uni.switchTab({
    url: item.link,
    fail(err) {
      uni.redirectTo({
        url: item.link,
      });
    },
  });
}
// getCartNum: function() {
// 	getCartCounts().then(res => {
// 		this.$store.commit('indexData/setCartNum', res.data.count + '')
// 	}).catch(err=>{
// 		return this.$util.Tips({
// 			title: err.msg
// 		});
// 	})
// },
</script>

<style scoped lang="scss">
.safe-area-inset-bottom {
  height: 0;
  height: constant(safe-area-inset-bottom);
  height: env(safe-area-inset-bottom);
}

.page-footer-wrapper {
  position: relative;
}

.page-footer {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  display: flex;

  .foot-item image {
    display: block;
    height: 48rpx;
    width: 48rpx;
    margin: 0 auto;
  }

  .foot-item .txt {
    margin-top: 4rpx;
    font-size: 20rpx;
    line-height: 28rpx;
    color: #333333;

    &.active {
      color: var(--view-theme);
    }
  }
}
.page-footer :deep(.uni-badge--x){
  position: absolute !important;
  top: 0rpx;
}
.page-footer .uni-badge-left-margin {
  position: absolute;
  /* #ifdef MP */
  margin-left: 40rpx;
  top: -10rpx;
  /* #endif */
}
.page-footer :deep(.uni-badge-left-margin .uni-badge--error){
  color: #fff !important;
  background-color: var(--view-theme) !important;
  z-index: 8;
}
.page-footer :deep(.uni-badge){
  right: unset !important;
  top: unset !important;
}
.page-footer2 .foot-item .txt {
  margin-top: 0;
  font-size: 32rpx;
  line-height: 44rpx;
  color: #333333;

  &.active {
    color: var(--view-theme);
  }
}

.page-footer2.float .foot-item::before,
.page-footer3.float .foot-item::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 0;
  width: 2rpx;
  height: 32rpx;
  background: #cccccc;
  transform: translateY(-50%);
}

.page-footer2.float .foot-item:first-child::before,
.page-footer3.float .foot-item:first-child::before {
  display: none;
}
</style>
