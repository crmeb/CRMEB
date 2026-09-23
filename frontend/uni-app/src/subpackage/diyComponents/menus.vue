<template>
  <view v-show="!isSortType && menus.length">
    <common-wrapper :config="configData">
      <view :style="[boxContentStyle]">
        <view
          class="menu-header"
          v-if="
            headerConfig.enable && (leftTopText.enable || rightTopText.enable)
          "
          :style="[headerBoxStyle]"
        >
          <text
            class="left-text"
            v-if="leftTopText.enable"
            :style="[leftTextStyle]"
          >
            {{ leftTopText.text }}
          </text>
          <view
            class="right-text"
            v-if="rightTopText.enable"
            :style="[rightTextStyle]"
            @click="goMenuPage(rightTopText.link, item)"
          >
            {{ rightTopText.text }}
            <text
              class="iconfont icon-xiangyou"
              v-if="rightTopText.text && rightTopText.link"
              :style="[rightIconStyle]"
            ></text>
          </view>
        </view>

        <!-- 列表样式 -->
        <view class="list-menu" v-if="menuStyleConfig === 2">
          <view class="list-item" v-for="(item, index) in menus" :key="index">
            <view
              v-if="
                !item.routine_contact_type ||
                item.info[1].value !== '/pages/extension/customer_list/chat'
              "
              class="list-item-content"
              @click="goMenuPage(item.info[1].value)"
            >
              <view class="list-img-box" v-if="navDisplayStyle !== 2">
                <block v-if="listStyle == 0">
                  <easy-loadimage
                    v-if="item.img"
                    :image-src="item.img"
                    width="90rpx"
                    height="90rpx"
                    :borderRadius="imgStyle"
                  ></easy-loadimage>
                  <view class="list-empty-box" v-else>
                    <image
                      src="/static/images/shan.png"
                      mode="aspectFill"
                    ></image>
                  </view>
                  <text v-if="item.num" class="num">{{ item.num }}</text>
                </block>
                <block v-else>
                  <view class="icon-box" :style="[iconBoxStyle]">
                    <view
                      :class="['iconfont', item.icon]"
                      :style="[iconContentStyle]"
                    ></view>
                    <text v-if="item.num" class="num">{{ item.num }}</text>
                  </view>
                </block>
              </view>
              <view
                class="list-text"
                :style="[textColor]"
                v-if="navDisplayStyle !== 1"
              >
                {{ item.info[0].value }}
              </view>
            </view>
            <template v-else>
              <!-- #ifdef MP -->
              <button
                class="list-item-content"
                open-type="contact"
                v-if="item.routine_contact_type"
              >
                <view class="list-img-box" v-if="navDisplayStyle !== 2">
                  <block v-if="listStyle == 0">
                    <easy-loadimage
                      v-if="item.img"
                      :image-src="item.img"
                      width="90rpx"
                      height="90rpx"
                      :borderRadius="imgStyle"
                    ></easy-loadimage>
                    <view class="list-empty-box" v-else>
                      <image
                        src="/static/images/shan.png"
                        mode="aspectFill"
                      ></image>
                    </view>
                    <text v-if="item.num" class="num">{{ item.num }}</text>
                  </block>
                  <block v-else>
                    <view class="icon-box" :style="[iconBoxStyle]">
                      <view
                        :class="['iconfont', item.icon]"
                        :style="[iconContentStyle]"
                      ></view>
                      <text v-if="item.num" class="num">{{ item.num }}</text>
                    </view>
                  </block>
                </view>
                <view
                  class="list-text"
                  :style="[textColor]"
                  v-if="navDisplayStyle !== 1"
                >
                  {{ item.info[0].value }}
                </view>
              </button>
              <!-- #endif -->
            </template>
            <text class="iconfont icon-xiangyou list-arrow"></text>
          </view>
        </view>

        <!-- 宫格/排列样式 -->
        <view v-else>
          <view class="swiper" v-if="isShowConfig">
            <swiper
              :interval="3000"
              :duration="500"
              :style="'height:' + navHigh + 'px;'"
              @change="bannerfun"
            >
              <block>
                <swiper-item v-for="(item, indexw) in menuList" :key="indexw">
                  <view
                    class="menu"
                    :id="'nav' + indexw"
                    :class="{
                      'grid-three': menuStyleConfig === 1 && gridStyle === 0,
                      'grid-four': menuStyleConfig === 1 && gridStyle === 1,
                    }"
                    :style="[gridContainerStyle]"
                  >
                    <template v-for="(itemn, indexn) in item.list">
                      <view
                        v-if="
                          !itemn.routine_contact_type ||
                          itemn.info[1].value !==
                            '/pages/extension/customer_list/chat'
                        "
                        class="flex-col flex-center"
                        :class="{
                          'grid-item': menuStyleConfig === 1,
                        }"
                        :key="indexn"
                        @click="goMenuPage(itemn.info[1].value)"
                        :style="[gridItemContentStyle]"
                      >
                        <view
                          class="pictrue skeleton-radius"
                          v-if="navDisplayStyle !== 2"
                        >
                          <block v-if="listStyle == 0">
                            <easy-loadimage
                              :image-src="itemn.img"
                              width="90rpx"
                              height="90rpx"
                              :borderRadius="imgStyle"
                            ></easy-loadimage>
                            <text v-if="itemn.num" class="num">{{
                              itemn.num
                            }}</text>
                          </block>
                          <block v-else>
                            <view class="icon-box" :style="[iconBoxStyle]">
                              <view
                                :class="['iconfont', itemn.icon]"
                                :style="[iconContentStyle]"
                              ></view>
                              <text v-if="itemn.num" class="num">{{
                                itemn.num
                              }}</text>
                            </view>
                          </block>
                        </view>
                        <view
                          class="fs-24"
                          :class="{ 'mt-10': listStyle === 0 }"
                          :style="[textColor]"
                          v-if="navDisplayStyle !== 1"
                          >{{ itemn.info[0].value }}</view
                        >
                      </view>
                      <template v-else>
                        <!-- #ifdef MP -->
                        <button
                          class="flex-col flex-center reset-btn"
                          :class="{
                            'grid-item': menuStyleConfig === 1,
                          }"
                          :key="'btn_' + indexn"
                          open-type="contact"
                          v-if="itemn.routine_contact_type"
                          :style="[gridItemContentStyle]"
                        >
                          <view
                            class="pictrue skeleton-radius"
                            v-if="navDisplayStyle !== 2"
                          >
                            <block v-if="listStyle == 0">
                              <easy-loadimage
                                :image-src="itemn.img"
                                width="90rpx"
                                height="90rpx"
                                :borderRadius="imgStyle"
                              ></easy-loadimage>
                              <text v-if="itemn.num" class="num">{{
                                itemn.num
                              }}</text>
                            </block>
                            <block v-else>
                              <view class="icon-box" :style="[iconBoxStyle]">
                                <view
                                  :class="['iconfont', itemn.icon]"
                                  :style="[iconContentStyle]"
                                ></view>
                                <text v-if="itemn.num" class="num">{{
                                  itemn.num
                                }}</text>
                              </view>
                            </block>
                          </view>
                          <view
                            class="fs-24"
                            :class="{ 'mt-10': listStyle === 0 }"
                            :style="[textColor]"
                            v-if="navDisplayStyle !== 1"
                            >{{ itemn.info[0].value }}</view
                          >
                        </button>
                        <!-- #endif -->
                      </template>
                    </template>
                  </view>
                </swiper-item>
              </block>
            </swiper>
          </view>
          <view v-else>
            <view
              class="menu"
              :class="{
                'grid-three': menuStyleConfig === 1 && gridStyle === 0,
                'grid-four': menuStyleConfig === 1 && gridStyle === 1,
              }"
              :style="[gridContainerStyle]"
            >
              <template v-for="(item, index) in menus">
                <view
                  v-if="
                    !item.routine_contact_type ||
                    item.info[1].value !== '/pages/extension/customer_list/chat'
                  "
                  class="flex-col flex-center"
                  :class="{
                    'grid-item': menuStyleConfig === 1,
                  }"
                  :key="index"
                  @click="goMenuPage(item.info[1].value)"
                  :style="[gridItemContentStyle]"
                >
                  <view
                    class="pictrue skeleton-radius"
                    v-if="navDisplayStyle !== 2"
                  >
                    <block v-if="listStyle == 0">
                      <easy-loadimage
                        :image-src="item.img"
                        width="90rpx"
                        height="90rpx"
                        :borderRadius="imgStyle"
                      ></easy-loadimage>
                      <text v-if="item.num" class="num">{{ item.num }}</text>
                    </block>
                    <block v-else>
                      <view class="icon-box" :style="[iconBoxStyle]">
                        <view
                          :class="['iconfont', item.icon]"
                          :style="[iconContentStyle]"
                        ></view>
                        <!-- 角标数量 -->
                        <text v-if="item.num" class="num">{{ item.num }}</text>
                      </view>
                    </block>
                  </view>
                  <view
                    class="fs-24"
                    :class="{ 'mt-10': listStyle === 0 }"
                    :style="[textColor]"
                    v-if="navDisplayStyle !== 1"
                    >{{ item.info[0].value }}</view
                  >
                </view>
                <template v-else>
                  <!-- #ifdef MP -->
                  <button
                    class="flex-col flex-center reset-btn"
                    :class="{
                      'grid-item': menuStyleConfig === 1,
                    }"
                    :key="'btn_' + index"
                    open-type="contact"
                    v-if="item.routine_contact_type"
                    :style="[gridItemContentStyle]"
                  >
                    <view
                      class="pictrue skeleton-radius"
                      v-if="navDisplayStyle !== 2"
                    >
                      <block v-if="listStyle == 0">
                        <easy-loadimage
                          :image-src="item.img"
                          width="90rpx"
                          height="90rpx"
                          :borderRadius="imgStyle"
                        ></easy-loadimage>
                        <text v-if="item.num" class="num">{{
                          item.num
                        }}</text>
                      </block>
                      <block v-else>
                        <view class="icon-box" :style="[iconBoxStyle]">
                          <view
                            :class="['iconfont', item.icon]"
                            :style="[iconContentStyle]"
                          ></view>
                          <!-- 角标数量 -->
                          <text v-if="item.num" class="num">{{
                            item.num
                          }}</text>
                        </view>
                      </block>
                    </view>
                    <view
                      class="fs-24"
                      :class="{ 'mt-10': listStyle === 0 }"
                      :style="[textColor]"
                      v-if="navDisplayStyle !== 1"
                      >{{ item.info[0].value }}</view
                    >
                  </button>
                  <!-- #endif -->
                </template>
              </template>
            </view>
          </view>
          <view
            class="dot acea-row row-center-wrapper"
            v-if="menuList.length > 1 && isShowConfig"
          >
            <view
              class="dot-item line_dot-item"
              :style="[getDotStyle(index)]"
              v-for="(item, index) in menuList"
              :key="index"
            ></view>
          </view>
        </view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed, nextTick, getCurrentInstance, onMounted } from "vue";
import { storeToRefs } from "pinia";
import commonWrapper from "./commonWrapper.vue";
import { getCustomer } from "@/utils/index.js";
import { toLogin } from "@/libs/login.js";
import { useAppStore } from "@/store/modules/app";
import util from "@/utils/util.js";

defineOptions({ name: "menus" });

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
  isSortType: {
    type: [String, Number],
    default: 0,
  },
});

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

const navHigh = ref(0);
const menuList = ref([]);
const active = ref(0);

const rowsNum = computed(() => {
  return props.dataConfig.rowsNum.tabVal;
});
const number = computed(() => {
  return props.dataConfig.number.tabVal;
});
const configData = computed(() => {
  return {
    ...props.dataConfig,
  };
});
const menus = computed(() => {
  let list = props.dataConfig.menuConfig.list || [];
  let menuList = [];
  list.forEach((item) => {
    if (item.show) {
      menuList.push(item);
    }
  });
  return menuList;
});
const isShowConfig = computed(() => {
  return props.dataConfig.showConfig.tabVal;
});
const textColor = computed(() => {
  return {
    color: props.dataConfig.textColor
      ? props.dataConfig.textColor.color[0].item
      : "#333",
  };
});
const boxContentStyle = computed(() => {
  let borderRadius = `${props.dataConfig.fillet.val * 2}rpx`;
  if (props.dataConfig.fillet.type) {
    borderRadius = `${props.dataConfig.fillet.valList[0].val * 2}rpx ${
      props.dataConfig.fillet.valList[1].val * 2
    }rpx ${props.dataConfig.fillet.valList[3].val * 2}rpx ${
      props.dataConfig.fillet.valList[2].val * 2
    }rpx`;
  }
  return {
    background: `linear-gradient(90deg, ${props.dataConfig.bgColor.color[0].item} 0%, ${props.dataConfig.bgColor.color[1].item} 100%)`,
    borderRadius,
  };
});
const imgStyle = computed(() => {
  let borderRadius = `${props.dataConfig.filletImg.val * 2}rpx`;
  if (props.dataConfig.filletImg.type) {
    borderRadius = `${props.dataConfig.filletImg.valList[0].val * 2}rpx ${
      props.dataConfig.filletImg.valList[1].val * 2
    }rpx ${props.dataConfig.filletImg.valList[3].val * 2}rpx ${
      props.dataConfig.filletImg.valList[2].val * 2
    }rpx`;
  }
  return borderRadius;
});
//分几行展示，一行展示多少个
const gridColumns = computed(() => {
  if (props.dataConfig.number.tabVal == 0) {
    return {
      gridTemplateColumns: "repeat(3, 1fr)",
    };
  } else if (props.dataConfig.number.tabVal == 1) {
    return {
      gridTemplateColumns: "repeat(4, 1fr)",
    };
  } else {
    return {
      gridTemplateColumns: "repeat(5, 1fr)",
    };
  }
});
const menuStyleConfig = computed(() => {
  return props.dataConfig.menuStyleConfig.tabVal;
});
const dotColor = computed(() => {
  return props.dataConfig.pointerBgColor.color[0].item;
});
const dotSelectColor = computed(() => {
  return props.dataConfig.pointerColor.color[0].item;
});
const headerConfig = computed(() => {
  return props.dataConfig.headerConfig || {};
});
const leftTopText = computed(() => {
  return props.dataConfig.leftTopText || {};
});
const rightTopText = computed(() => {
  return props.dataConfig.rightTopText || {};
});
const headerStyle = computed(() => {
  return props.dataConfig.headerStyle || {};
});
const navDisplayStyle = computed(() => {
  return props.dataConfig.navDisplayStyle
    ? props.dataConfig.navDisplayStyle.tabVal
    : 0;
});
const gridStyle = computed(() => {
  return props.dataConfig.gridStyle ? props.dataConfig.gridStyle.tabVal : 0;
});
const gridItemStyle = computed(() => {
  return Object.assign(
    {
      itemPadding: 8,
      itemBgColor: "#ffffff",
      itemRadius: 0,
      itemPaddingTop: 0,
    },
    props.dataConfig.gridItemStyle,
  );
});
const listStyle = computed(() => {
  return props.dataConfig.menuConfig.listStyle || 0;
});
const iconStyleConfig = computed(() => {
  let iconConfig = props.dataConfig.iconStyleConfig || {};
  return {
    color: iconConfig.color ? iconConfig.color.color[0].item : "#333",
    size: iconConfig.size ? iconConfig.size.val : 24,
    position: iconConfig.position ? iconConfig.position.tabVal : 1,
    padding: iconConfig.padding ? iconConfig.padding.val : 0,
    rotate: iconConfig.rotate ? iconConfig.rotate.val : 0,
    shadow: iconConfig.shadow ? iconConfig.shadow.tabVal : 0,
  };
});
const iconBoxStyle = computed(() => {
  const position = iconStyleConfig.value.position;
  return {
    justifyContent:
      position === 0
        ? "flex-start"
        : position === 1
          ? "center"
          : "flex-end",
  };
});
const headerBoxStyle = computed(() => {
  return {
    paddingTop: headerStyle.value.topPadding * 2 + "rpx",
    paddingBottom: headerStyle.value.bottomPadding * 2 + "rpx",
    paddingLeft: headerStyle.value.leftRightPadding * 2 + "rpx",
    paddingRight: headerStyle.value.leftRightPadding * 2 + "rpx",
  };
});
const leftTextStyle = computed(() => {
  return {
    color: headerStyle.value.leftColor,
    fontSize: headerStyle.value.fontSize * 2 + "rpx",
    fontWeight: headerStyle.value.leftWeight,
  };
});
const rightTextStyle = computed(() => {
  return {
    color: headerStyle.value.rightColor,
    fontSize: (headerStyle.value.rightFontSize || 12) * 2 + "rpx",
    fontWeight: headerStyle.value.rightWeight,
  };
});
const rightIconStyle = computed(() => {
  return {
    fontSize: (headerStyle.value.rightFontSize || 12) * 2 + "rpx",
  };
});
const iconContentStyle = computed(() => {
  const config = iconStyleConfig.value;
  return {
    color: config.color,
    fontSize: config.size * 2 + "rpx",
    padding: config.padding * 2 + "rpx",
    transform: "rotate(" + config.rotate + "deg)",
    textShadow: config.shadow ? "0px 2px 4px rgba(0,0,0,0.2)" : "none",
  };
});
const gridContainerStyle = computed(() => {
  if (menuStyleConfig.value === 1) {
    return {
      columnGap: gridItemStyle.value.itemPadding * 2 + "rpx",
      rowGap: gridItemStyle.value.itemPaddingTop * 2 + "rpx",
    };
  } else {
    return gridColumns.value;
  }
});
const gridItemContentStyle = computed(() => {
  if (menuStyleConfig.value === 1) {
    return {
      backgroundColor: gridItemStyle.value.itemBgColor,
      borderRadius: gridItemStyle.value.itemRadius * 2 + "rpx",
      paddingTop: gridItemStyle.value.itemPaddingTop * 2 + "rpx",
      paddingBottom: gridItemStyle.value.itemPaddingTop * 2 + "rpx",
    };
  } else {
    return {};
  }
});

const instance = getCurrentInstance();
onMounted(() => {
  getSwiperCount();
  nextTick(() => {
    if (menuList.value.length && isShowConfig.value) {
      // #ifdef H5
      menuHeight();
      // #endif
      // #ifndef H5
      setTimeout(() => {
        menuHeight();
      }, 150);
      // #endif
    }
  });
});

function getSwiperCount() {
  /* rowsNum 显示行数  0: 1行  1: 2行 2: 3行 3 4行 */
  /* number  单行显示  0: 3个  1: 4个 2: 5个 */
  const columns =
    menuStyleConfig.value === 1 ? gridStyle.value + 3 : number.value + 3;
  pageNum((rowsNum.value + 1) * columns);
}
function bannerfun(e) {
  active.value = e.detail.current;
}
function menuHeight() {
  const query = uni.createSelectorQuery().in(instance.proxy);
  query
    .select("#nav0")
    .boundingClientRect((data) => {
      navHigh.value = data.height;
    })
    .exec();
}
function pageNum(num) {
  let count = Math.ceil(menus.value.length / num);
  let goodArray = new Array();
  for (let i = 0; i < count; i++) {
    let list = menus.value.slice(i * num, i * num + num);
    if (list.length)
      goodArray.push({
        list: list,
      });
  }
  menuList.value = goodArray;
}

function getDotStyle(index) {
  return {
    "background-color":
      active.value === index ? dotSelectColor.value : dotColor.value,
  };
}
function menusTap(url) {
  util.JumpPath(url);
}
function goMenuPage(url) {
  if (url.indexOf("http") === -1) {
    if (url == "/kefu/mobile_list") {
      return uni.navigateTo({
        url: `/pages/annex/web_view/index?url=${location.origin}${url}`,
      });
    } else if (url == "/pages/extension/customer_list/chat") {
      return getCustomer(url);
    }
    util.JumpPath(url);
  } else {
    util.JumpPath(url);
  }
}
</script>

<style lang="scss" scoped>
.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;

  .right-text {
    display: flex;
    align-items: center;
  }
}

.dot {
  width: 100%;
  padding-bottom: 20rpx;

  .instruct {
    width: 50rpx;
    height: 36rpx;
    line-height: 36rpx;
    background-color: rgba(0, 0, 0, 0.8);
    color: #fff;
    border-radius: 16rpx;
    font-size: 24rpx;
    text-align: center;
  }

  .dot-item {
    width: 10rpx;
    height: 10rpx;
    background: rgba(0, 0, 0, 0.4);
    border-radius: 50%;
    margin: 0 4px;

    &.line_dot-item {
      width: 20rpx;
      height: 6rpx;
      border-radius: 3rpx;
    }
  }
}
.nav {
  .item {
    margin-right: 42rpx;
    width: 90rpx;
    text-align: center;
    font-size: 24rpx;
    display: inline-block;
  }
}
.menu {
  display: grid;
  grid-template-rows: auto;
  width: 100%;
  padding: 0 0 20rpx 0;
}
.swiper {
  z-index: 20;
  position: relative;
  overflow: hidden;
  swiper,
  .swiper-item {
    width: 100%;
    display: block;
  }
}

.list-menu {
  .list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 0 24rpx;
    padding: 24rpx 0;
    border-bottom: 1rpx solid #eee;
    &:first-child {
      margin-top: 0;
    }
    &:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }

    .list-item-content {
      display: flex;
      align-items: center;

      .list-img-box {
        margin-right: 20rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        width: 90rpx;
        height: 90rpx;
      }

      .list-text {
        font-size: 28rpx;
      }
    }

    .list-arrow {
      color: #999;
      font-size: 24rpx;
    }
  }
}
.icon-box {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  width: 90rpx;
  height: 90rpx;
}
.pictrue {
  position: relative;
  width: 90rpx;
  height: 90rpx;
}
.num {
  position: absolute;
  top: 10rpx;
  right: 8rpx;
  height: 28rpx;
  min-width: 20rpx;
  padding: 4rpx 8rpx;
  line-height: 20rpx;
  text-align: center;
  font-size: 20rpx;
  color: #fff;
  background-color: #ff4444;
  border-radius: 50%;
}

.grid-three {
  grid-template-columns: repeat(3, 1fr) !important;
}

.grid-four {
  grid-template-columns: repeat(4, 1fr) !important;
}

.grid-item {
  width: 100%;
}
</style>
