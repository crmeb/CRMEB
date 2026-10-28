<template>
  <view class="custom-box">
    <common-wrapper :config="configObj">
      <!-- Unified List Mode -->
      <view
        v-if="
          ['article', 'coupon', 'goods'].includes(selectTypeValue) &&
          dataList.length > 0
        "
      >
        <view class="custom-box-list" :style="[getListContainerStyle]">
          <view
            v-for="(dataItem, dataIndex) in dataList"
            :key="dataIndex"
            :style="[getListItemStyle]"
            class="list-item"
          >
            <view class="home_custom_component" :style="[getDataWrapperStyle]">
              <view :style="[itemWrapperStyle]">
                <view
                  v-for="item in visibleComponents"
                  :key="item.id"
                  :style="[getComponentStyle(item.style)]"
                  @click="goDetail(dataItem, item)"
                >
                  <!-- Picture -->
                  <image
                    v-if="item.component === 'Picture'"
                    :src="getPictureUrl(item, dataItem)"
                    :style="[getPictureStyle(item.propValue)]"
                    mode="aspectFill"
                  ></image>

                  <!-- Text -->
                  <view
                    v-else-if="item.component === 'Text'"
                    :style="[getTextBgStyle(item.propValue)]"
                  >
                    <view
                      :style="[getTextStyle(item.propValue)]"
                      :class="
                        item.propValue.ellipsis == 1
                          ? 'line1'
                          : item.propValue.ellipsis == 2
                            ? 'line2'
                            : item.propValue.ellipsis == 3
                              ? 'line3'
                              : ''
                      "
                    >
                      {{ getDisplayText(item, dataItem) }}
                    </view>
                  </view>

                  <!-- Icon -->
                  <view
                    v-else-if="item.component === 'Icon'"
                    :style="[getIconStyle(item.propValue)]"
                    class="flex-center"
                  >
                    <text
                      class="iconfont"
                      :class="item.propValue.class"
                      :style="{
                        fontSize:
                          item.propValue.size * 2 * contentScale + 'rpx',
                        color: item.propValue.color,
                      }"
                    ></text>
                  </view>

                  <!-- Line -->
                  <view
                    v-else-if="item.component === 'Line'"
                    class="flex-center"
                    style="width: 100%; height: 100%"
                  >
                    <view :style="[getLineStyle(item.propValue)]"></view>
                  </view>

                  <!-- Panel -->
                  <view
                    v-else-if="item.component === 'Panel'"
                    :style="[getPanelStyle(item.propValue)]"
                  ></view>
                </view>
              </view>
            </view>
          </view>
        </view>
      </view>

      <!-- Default Mode (User/Single) -->
      <view v-else style="width: 100%">
        <view class="home_custom_component" :style="[getDataWrapperStyle]">
          <view :style="[itemWrapperStyle]">
            <view
              v-for="item in visibleComponents"
              :key="item.id"
              :style="[getComponentStyle(item.style)]"
            >
              <!-- Picture -->
              <image
                v-if="item.component === 'Picture'"
                :src="getPictureUrl(item, userInfo)"
                :style="[getPictureStyle(item.propValue)]"
                mode="aspectFill"
                @click="handleUserClick(item)"
              ></image>

              <!-- Text -->
              <view
                v-else-if="item.component === 'Text'"
                :style="[getTextBgStyle(item.propValue)]"
              >
                <view
                  :style="[getTextStyle(item.propValue)]"
                  :class="
                    item.propValue.ellipsis == 1
                      ? 'line1'
                      : item.propValue.ellipsis == 2
                        ? 'line2'
                        : item.propValue.ellipsis == 3
                          ? 'line3'
                          : ''
                  "
                  @click="handleUserClick(item)"
                >
                  {{ getDisplayText(item, userInfo) }}
                </view>
              </view>

              <!-- Icon -->
              <view
                v-else-if="item.component === 'Icon'"
                :style="[getIconStyle(item.propValue)]"
                class="flex-center"
                @click="handleUserClick(item)"
              >
                <text
                  class="iconfont"
                  :class="item.propValue.class"
                  :style="{
                    fontSize: item.propValue.size * 2 * contentScale + 'rpx',
                    color: item.propValue.color,
                  }"
                ></text>
              </view>

              <!-- Line -->
              <view
                v-else-if="item.component === 'Line'"
                class="flex-center"
                style="width: 100%; height: 100%"
              >
                <view :style="[getLineStyle(item.propValue)]"></view>
              </view>

              <!-- Panel -->
              <view
                v-else-if="item.component === 'Panel'"
                :style="[getPanelStyle(item.propValue)]"
                @click="handleUserClick(item)"
              ></view>
            </view>
          </view>
        </view>
      </view>
    </common-wrapper>
  </view>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { storeToRefs } from "pinia";
import {
  getThemeArticle,
  getThemeCoupon,
  getThemeProduct,
  getThemeUser,
  setCouponReceive,
} from "@/api/api.js";
import { useAppStore } from "@/store/modules/app";
import CommonWrapper from "./commonWrapper.vue";
import i18n from "@/utils/lang.js";

defineOptions({ name: "customComponent" });

const emit = defineEmits(["changeLogin"]);

const props = defineProps({
  dataConfig: {
    type: Object,
    default: () => ({}),
  },
});

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

const dataList = ref([]);
const articleList = ref([]);
const couponList = ref([]);
const goodsList = ref([]);
const userInfo = ref({});

const configObj = computed(() => {
  return props.dataConfig;
});
const customComponents = computed(() => {
  return configObj.value.customComponents || {};
});
const visibleComponents = computed(() => {
  if (!customComponents.value || !customComponents.value.list) return [];
  return customComponents.value.list.filter((item) => !item.isHidden);
});
const contentScale = computed(() => {
  const {
    marginConfig,
    paddingConfig,
    borderConfig,
    paddingDataConfig,
    borderDataConfig,
    marginDataConfig,
  } = configObj.value;
  let width = 375;

  // Wrapper Margin
  if (marginConfig) {
    if (!marginConfig.isAll) {
      width -= (marginConfig.val || 0) * 2;
    } else {
      width -=
        (marginConfig.valList[1].val || 0) +
        (marginConfig.valList[3].val || 0);
    }
  }

  // Wrapper Padding
  if (paddingConfig) {
    if (!paddingConfig.isAll) {
      width -= (paddingConfig.val || 0) * 2;
    } else {
      width -=
        (paddingConfig.valList[1].val || 0) +
        (paddingConfig.valList[3].val || 0);
    }
  }

  // Wrapper Border
  if (borderConfig && borderConfig.tabVal) {
    const borderWidth = (borderConfig.widthConfig.val || 0) * 2;
    width -= borderWidth;
  }

  // Data Margin
  if (marginDataConfig) {
    if (!marginDataConfig.isAll) {
      width -= (marginDataConfig.val || 0) * 2;
    } else {
      width -=
        (marginDataConfig.valList[1].val || 0) +
        (marginDataConfig.valList[3].val || 0);
    }
  }

  // Data Padding
  if (paddingDataConfig) {
    if (!paddingDataConfig.isAll) {
      width -= (paddingDataConfig.val || 0) * 2;
    } else {
      width -=
        (paddingDataConfig.valList[1].val || 0) +
        (paddingDataConfig.valList[3].val || 0);
    }
  }

  // Data Border
  if (borderDataConfig && borderDataConfig.tabVal) {
    const borderWidth = (borderDataConfig.widthConfig.val || 0) * 2;
    width -= borderWidth;
  }

  return width / 375;
});
const currentDisplayMode = computed(() => {
  if (selectTypeValue.value === "article")
    return configObj.value.articleDisplayMode;
  if (selectTypeValue.value === "coupon")
    return configObj.value.couponDisplayMode;
  if (selectTypeValue.value === "goods")
    return configObj.value.goodsDisplayMode;
  return null;
});
const currentColumnStyle = computed(() => {
  if (selectTypeValue.value === "article")
    return configObj.value.articleColumnStyle;
  if (selectTypeValue.value === "coupon")
    return configObj.value.couponColumnStyle;
  if (selectTypeValue.value === "goods")
    return configObj.value.goodsColumnStyle;
  return null;
});
const selectTypeValue = computed(() => {
  return configObj.value.selectType
    ? configObj.value.selectType.activeValue
    : "user";
});
// Background Gradient
const getDataWrapperStyle = computed(() => {
  const {
    filletDataConfig,
    marginDataConfig,
    paddingDataConfig,
    componentBgDataConfig,
    borderDataConfig,
    shadowDataConfig,
  } = configObj.value;
  const style = {};

  // Border Radius
  if (filletDataConfig) {
    if (filletDataConfig.type) {
      style.borderRadius = `${filletDataConfig.valList[0].val}px ${filletDataConfig.valList[1].val}px ${filletDataConfig.valList[3].val}px ${filletDataConfig.valList[2].val}px`;
    } else {
      style.borderRadius = `${filletDataConfig.val}px`;
    }
  }

  // Margin
  if (marginDataConfig) {
    if (marginDataConfig.isAll) {
      style.marginTop = `${marginDataConfig.valList[0].val}px`;
      style.marginRight = `${marginDataConfig.valList[1].val}px`;
      style.marginBottom = `${marginDataConfig.valList[2].val}px`;
      style.marginLeft = `${marginDataConfig.valList[3].val}px`;
    } else {
      style.margin = `${marginDataConfig.val}px`;
    }
  }

  // Padding
  if (paddingDataConfig) {
    if (paddingDataConfig.isAll) {
      style.paddingTop = `${paddingDataConfig.valList[0].val}px`;
      style.paddingRight = `${paddingDataConfig.valList[1].val}px`;
      style.paddingBottom = `${paddingDataConfig.valList[2].val}px`;
      style.paddingLeft = `${paddingDataConfig.valList[3].val}px`;
    } else {
      style.padding = `${paddingDataConfig.val}px`;
    }
  }

  // Background
  if (componentBgDataConfig) {
    if (componentBgDataConfig.tabVal === 0) {
      // Color
      const colorList = componentBgDataConfig.colorConfig.color;
      if (colorList && colorList.length > 0) {
        style.background = `linear-gradient(${
          componentBgDataConfig.colorDirection.tabVal === 0
            ? "90deg"
            : componentBgDataConfig.colorDirection.tabVal === 1
              ? "180deg"
              : componentBgDataConfig.colorDirection.tabVal === 2
                ? "135deg"
                : "45deg"
        }, ${colorList[0].item} 0%, ${colorList[1] ? colorList[1].item : colorList[0].item} 100%)`;
      }
    } else {
      // Image
      if (componentBgDataConfig.imageConfig.url) {
        style.backgroundImage = `url(${componentBgDataConfig.imageConfig.url})`;
        style.backgroundSize = "100% 100%";
        style.backgroundRepeat = "no-repeat";
      }
    }
  }

  // Border
  if (borderDataConfig && borderDataConfig.tabVal) {
    style.borderStyle =
      borderDataConfig.styleConfig.tabVal === 0
        ? "solid"
        : borderDataConfig.styleConfig.tabVal === 1
          ? "dashed"
          : "dotted";
    style.borderWidth = `${borderDataConfig.widthConfig.val}px`;
    style.borderColor = borderDataConfig.colorConfig.color[0].item;
  }

  // Shadow
  if (shadowDataConfig && shadowDataConfig.tabVal) {
    style.boxShadow = `${shadowDataConfig.xConfig.val}px ${shadowDataConfig.yConfig.val}px ${shadowDataConfig.blurConfig.val}px ${shadowDataConfig.spreadConfig.val}px ${shadowDataConfig.colorConfig.color[0].item}`;
  }

  return style;
});
const itemWrapperStyle = computed(() => {
  // let bgColorLeft = configObj.value.moduleColor.color[0].item;
  // let bgColorRight = configObj.value.moduleColor.color[1].item;

  const {
    marginConfig,
    paddingConfig,
    borderConfig,
    marginDataConfig,
    paddingDataConfig,
    borderDataConfig,
  } = configObj.value;

  let marginHeight = 0;
  let paddingHeight = 0;
  let borderWidth = 0;

  // Wrapper Margin
  if (marginConfig) {
    marginHeight += marginConfig.isAll
      ? marginConfig.valList[0].val + marginConfig.valList[2].val || 0
      : marginConfig.val || 0;
  }

  // Wrapper Padding
  if (paddingConfig) {
    paddingHeight += paddingConfig.isAll
      ? paddingConfig.valList[0].val + paddingConfig.valList[2].val || 0
      : paddingConfig.val || 0;
  }

  // Wrapper Border
  if (borderConfig && borderConfig.tabVal) {
    borderWidth += borderConfig.widthConfig.val / 2;
  }

  // Data Margin
  if (marginDataConfig) {
    marginHeight += marginDataConfig.isAll
      ? marginDataConfig.valList[0].val + marginDataConfig.valList[2].val ||
        0
      : marginDataConfig.val || 0;
  }

  // Data Padding
  if (paddingDataConfig) {
    paddingHeight += paddingDataConfig.isAll
      ? paddingDataConfig.valList[0].val +
          paddingDataConfig.valList[2].val || 0
      : paddingDataConfig.val || 0;
  }

  // Data Border
  if (borderDataConfig && borderDataConfig.tabVal) {
    borderWidth += borderDataConfig.widthConfig.val / 2;
  }

  const scale = contentScale.value;
  const canvasHeight =
    (customComponents.value.canvasHeight * scale || 0) +
    borderWidth * scale +
    marginHeight * scale +
    paddingHeight * scale;

  return {
    height: customComponents.value.canvasHeight * scale * 2 + "rpx",
    position: "relative",
    width: "100%",
    // background: `linear-gradient(90deg,${bgColorLeft || "#fff"} 0%,${
    //   bgColorRight || "#fff"
    // } 100%)`,
  };
});
// List Styles
const getListContainerStyle = computed(() => {
  let displayMode, columnStyle;
  if (selectTypeValue.value === "article") {
    displayMode = configObj.value.articleDisplayMode;
    columnStyle = configObj.value.articleColumnStyle;
  } else if (selectTypeValue.value === "coupon") {
    displayMode = configObj.value.couponDisplayMode;
    columnStyle = configObj.value.couponColumnStyle;
  } else if (selectTypeValue.value === "goods") {
    displayMode = configObj.value.goodsDisplayMode;
    columnStyle = configObj.value.goodsColumnStyle;
  }

  const style = {
    position: "relative",
    width: "100%",
  };
  if (displayMode && displayMode.tabVal === 1) {
    // Horizontal Scroll
    style.display = "flex";
    style.overflowX = "auto";
    style.overflowY = "hidden";
    style.flexWrap = "nowrap";
  } else {
    style.display = "flex";
    style.flexWrap = "wrap";
  }
  return style;
});
const getListItemStyle = computed(() => {
  let displayMode, columnStyle;
  if (selectTypeValue.value === "article") {
    displayMode = configObj.value.articleDisplayMode;
    columnStyle = configObj.value.articleColumnStyle;
  } else if (selectTypeValue.value === "coupon") {
    displayMode = configObj.value.couponDisplayMode;
    columnStyle = configObj.value.couponColumnStyle;
  } else if (selectTypeValue.value === "goods") {
    displayMode = configObj.value.goodsDisplayMode;
    columnStyle = configObj.value.goodsColumnStyle;
  }

  const style = {
    width: "100%",
  };
  if (displayMode && displayMode.tabVal === 1) {
    style.flexShrink = 0;
    if (columnStyle) {
      const colCount = columnStyle.tabVal + 1;
      style.width = `${100 / colCount}%`;
    } else {
      style.width = "400rpx";
    }
  } else {
    if (columnStyle) {
      const colCount = columnStyle.tabVal + 1;
      style.width = `${100 / colCount}%`;
    }
  }
  return style;
});
// Add flex-basis for multi-column layouts to ensure wrapping works correctly if needed,
// though width usually suffices.

watch(
  () => props.dataConfig,
  (val) => {
    fetchData();
  },
  { deep: true, immediate: true }
);

function fetchData() {
  if (selectTypeValue.value === "user") {
    fetchUserInfo();
    dataList.value = [];
    return;
  }
  if (selectTypeValue.value === "article") {
    fetchArticleData();
  } else if (selectTypeValue.value === "coupon") {
    fetchCouponData();
  } else if (selectTypeValue.value === "goods") {
    fetchGoodsData();
  }
}
function fetchArticleData() {
  if (!configObj.value.articleDataSource) return;
  let params = {
    limit: configObj.value.articleNum.val,
    order: configObj.value.articleSort.tabVal,
    sort: configObj.value.articleSortRule.tabVal,
  };
  if (configObj.value.articleDataSource.tabVal === 0) {
    // Specific Data
    if (!configObj.value.articleList || !configObj.value.articleList.list)
      return;
    params.ids = configObj.value.articleList.list
      .map((item) => item.id)
      .join(",");
    if (params.ids.length === 0) {
      dataList.value = [];
      return;
    }
  } else {
    // Filter Data
    params.cid = Array.isArray(configObj.value.articleClass.activeValue)
      ? configObj.value.articleClass.activeValue.join(",")
      : configObj.value.articleClass.activeValue;
  }
  getThemeArticle(params).then((res) => {
    dataList.value = res.data;
  });
}
function fetchCouponData() {
  if (!configObj.value.couponDataSource) return;
  let params = {
    limit: configObj.value.couponNum.val,
    order: configObj.value.couponSort.tabVal,
    sort: configObj.value.couponSortRule.tabVal,
  };
  if (configObj.value.couponDataSource.tabVal === 0) {
    // Specific Data
    if (!configObj.value.couponList || !configObj.value.couponList.list)
      return;
    params.ids = configObj.value.couponList.list
      .map((item) => item.id)
      .join(",");
    if (params.ids.length === 0) {
      dataList.value = [];
      return;
    }
  } else {
    // Filter Data
    params.type = configObj.value.couponType.activeValue;
    params.user_type = configObj.value.couponUserType.activeValue;
    if (configObj.value.couponUserType.activeValue != 2) {
      params.send_type = configObj.value.couponSendType.activeValue;
    }
    params.is_min_price = configObj.value.couponThreshold.tabVal;
    if (params.is_min_price == 1) {
      params.min_price = configObj.value.couponThresholdValue.val;
    }
    if (
      configObj.value.couponTime.val &&
      configObj.value.couponTime.val.length
    ) {
      params.start_time = configObj.value.couponTime.val[0];
      params.end_time = configObj.value.couponTime.val[1];
    }
  }
  getThemeCoupon(params).then((res) => {
    dataList.value = res.data;
  });
}
function fetchGoodsData() {
  if (!configObj.value.goodsDataSource) return;
  let params = {
    limit: configObj.value.goodsNum.val,
    order: configObj.value.goodsSort.tabVal,
    sort: configObj.value.goodsSortRule.tabVal,
  };
  if (configObj.value.goodsDataSource.tabVal === 0) {
    // Specific Data
    if (!configObj.value.goodsList || !configObj.value.goodsList.list) return;
    params.ids = configObj.value.goodsList.list
      .map((item) => item.id)
      .join(",");
    if (params.ids.length === 0) {
      dataList.value = [];
      return;
    }
  } else {
    // Filter Data
    params.cate_ids = Array.isArray(configObj.value.goodsClass.activeValue)
      ? configObj.value.goodsClass.activeValue.join(",")
      : configObj.value.goodsClass.activeValue;
  }
  getThemeProduct(params).then((res) => {
    dataList.value = res.data;
  });
}
function fetchUserInfo() {
  getThemeUser().then((res) => {
    userInfo.value = res.data || {};
  });
}
function getWrapperStyle(displayMode, columnStyle) {
  const style = {
    width: "100%",
  };
  if (displayMode && displayMode.tabVal === 1) {
    style.flexShrink = 0;
    if (columnStyle) {
      const colCount = columnStyle.tabVal + 1;
      style.width = `${100 / colCount}%`;
    } else {
      style.width = "200px";
    }
  } else {
    if (columnStyle) {
      const colCount = columnStyle.tabVal + 1;
      style.width = `${100 / colCount}%`;
    }
  }
  return style;
}
function getComponentStyle(style) {
  const scale = contentScale.value;
  return {
    position: "absolute",
    left: style.left * scale * 2 + "rpx",
    top: style.top * scale * 2 + "rpx",
    width: style.width * scale * 2 + "rpx",
    maxWidth: "100%",
    height: style.height * scale * 2 + "rpx",
    zIndex: style.zIndex,
    transform: `rotate(${style.rotate || 0}deg)`,
  };
}
function getPictureStyle(propValue) {
  let borderRadius = 0;
  if (propValue.isRadiusAll) {
    borderRadius = propValue.borderRadius * 2 + "rpx";
  } else {
    borderRadius = `${propValue.borderRadiusTopLeft * 2}rpx ${
      propValue.borderRadiusTopRight * 2
    }rpx ${propValue.borderRadiusBottomRight * 2}rpx ${
      propValue.borderRadiusBottomLeft * 2
    }rpx`;
  }
  const style = {
    width: "100%",
    height: "100%",
    borderRadius: borderRadius,
  };
  if (propValue.showShadow) {
    style.boxShadow = `${propValue.shadowX * 2}rpx ${
      propValue.shadowY * 2
    }rpx ${propValue.shadowBlur * 2}rpx ${propValue.shadowSpread * 2}rpx ${
      propValue.shadowColor
    }`;
  }
  if (propValue.showBorder) {
    style.border = `${propValue.borderWidth * 2}rpx ${
      propValue.borderStyle
    } ${propValue.borderColor}`;
  }
  return style;
}
function getTextStyle(propValue) {
  const scale = contentScale.value;
  const style = {
    width: "100%",
    fontSize: propValue.fontSize * scale * 2 + "rpx",
    color: propValue.color,
    lineHeight: propValue.lineHeight,
    letterSpacing: (propValue.letterSpacing || 0) * scale * 2 + "rpx",
    fontWeight: propValue.fontWeight || "normal",
    fontStyle: propValue.fontStyle || "normal",
    textDecoration: propValue.textDecoration || "none",
    textAlign: propValue.textAlign || "left",

    boxSizing: "border-box",
    wordBreak: "break-all",
  };

  if (propValue.ellipsis == 0) {
    style.whiteSpace = "normal";
  }
  if (propValue.showTextShadow) {
    style.textShadow = `${(propValue.shadowX || 0) * scale * 2}rpx ${(propValue.shadowY || 0) * scale * 2}rpx ${(propValue.shadowBlur || 0) * scale * 2}rpx ${
      propValue.shadowColor || "rgba(0,0,0,0.5)"
    }`;
  }
  return style;
}
function getTextBgStyle(propValue) {
  const scale = contentScale.value;
  const style = {
    height: "100%",
    padding: `${(propValue.paddingTop || 0) * scale * 2}rpx ${(propValue.paddingRight || 0) * scale * 2}rpx ${
      (propValue.paddingBottom || 0) * scale * 2
    }rpx ${(propValue.paddingLeft || 0) * scale * 2}rpx`,
    borderWidth:
      (propValue.showBorder ? propValue.borderWidth * scale * 2 : 0) + "rpx",
    borderColor: propValue.borderColor || "transparent",
    borderStyle: propValue.borderStyle || "solid",
  };
  // Background
  if (propValue.bgColor2) {
    const directionMap = {
      horizontal: "90deg",
      vertical: "180deg",
      "left-diagonal": "135deg",
      "right-diagonal": "45deg",
    };
    const deg = directionMap[propValue.bgDirection] || "180deg";
    style.backgroundImage = `linear-gradient(${deg}, ${propValue.backgroundColor || "transparent"}, ${
      propValue.bgColor2
    })`;
  } else {
    style.backgroundColor = propValue.backgroundColor || "transparent";
  }
  if (propValue.showBorder && propValue.borderWidth) {
    style.backgroundSize = `100% calc(100% + ${propValue.borderWidth * scale * 4 || 0}rpx)`;
    style.backgroundPosition = `0rpx -${propValue.borderWidth * scale * 2 || 0}rpx`;
  }
  // Border Radius
  if (propValue.isRadiusAll) {
    style.borderRadius = (propValue.borderRadius || 0) * scale * 2 + "rpx";
  } else {
    style.borderRadius = `${(propValue.borderRadiusTopLeft || 0) * scale * 2}rpx ${(propValue.borderRadiusTopRight || 0) * scale * 2}rpx ${
      (propValue.borderRadiusBottomRight || 0) * scale * 2
    }rpx ${(propValue.borderRadiusBottomLeft || 0) * scale * 2}rpx`;
  }
  return style;
}
function getIconStyle(propValue) {
  const scale = contentScale.value;
  const style = {
    display: "flex",
    justifyContent: propValue.iconAlign || "center",
    alignItems: "center",
    width: "100%",
    height: "100%",
    boxSizing: "border-box",
    borderWidth:
      (propValue.showBorder ? propValue.borderWidth * scale * 2 : 0) + "rpx",
    borderColor: propValue.borderColor || "transparent",
    borderStyle: propValue.borderStyle || "solid",
    padding: `${(propValue.paddingTop || 0) * scale * 2}rpx ${(propValue.paddingRight || 0) * scale * 2}rpx ${
      (propValue.paddingBottom || 0) * scale * 2
    }rpx ${(propValue.paddingLeft || 0) * scale * 2}rpx`,
  };

  // Background
  if (propValue.bgColor2) {
    const directionMap = {
      horizontal: "90deg",
      vertical: "180deg",
      "left-diagonal": "135deg",
      "right-diagonal": "45deg",
    };
    const deg = directionMap[propValue.bgDirection] || "180deg";
    style.background = `linear-gradient(${deg}, ${propValue.backgroundColor || "transparent"}, ${
      propValue.bgColor2
    })`;
  } else {
    style.backgroundColor = propValue.backgroundColor || "transparent";
  }
  if (propValue.showBorder && propValue.borderWidth) {
    style.backgroundSize = `100% calc(100% + ${propValue.borderWidth * scale * 4 || 0}rpx)`;
    style.backgroundPosition = `0rpx -${propValue.borderWidth * scale * 2 || 0}rpx`;
  }
  // Border Radius
  if (propValue.isRadiusAll) {
    style.borderRadius = (propValue.borderRadius || 0) * scale + "px";
  } else {
    style.borderRadius = `${(propValue.borderRadiusTopLeft || 0) * scale}px ${
      (propValue.borderRadiusTopRight || 0) * scale
    }px ${(propValue.borderRadiusBottomRight || 0) * scale}px ${(propValue.borderRadiusBottomLeft || 0) * scale}px`;
  }

  return style;
}
function getLineStyle(propValue) {
  const style = {};
  if (propValue.direction === "vertical") {
    style.width = "0rpx";
    style.height = "100%";
    style.borderLeft = `${propValue.height * 2}rpx ${propValue.style} ${
      propValue.color
    }`;
  } else {
    style.width = "100%";
    style.height = "0rpx";
    style.borderTop = `${propValue.height * 2}rpx ${propValue.style} ${
      propValue.color
    }`;
  }
  return style;
}
function getPanelStyle(propValue) {
  const scale = contentScale.value;
  let borderRadius = 0;
  if (propValue.isRadiusAll) {
    borderRadius = propValue.borderRadius * 2 + "rpx";
  } else {
    borderRadius = `${propValue.borderRadiusTopLeft * 2}rpx ${
      propValue.borderRadiusTopRight * 2
    }rpx ${propValue.borderRadiusBottomRight * 2}rpx ${
      propValue.borderRadiusBottomLeft * 2
    }rpx`;
  }
  const style = {
    width: "100%",
    height: "100%",
    borderRadius: borderRadius,
    borderWidth:
      (propValue.showBorder ? propValue.borderWidth * scale : 0) + "px",
    borderColor: propValue.borderColor || "transparent",
    borderStyle: propValue.borderStyle || "solid",
  };
  if (propValue.bgColor2) {
    const directionMap = {
      horizontal: "90deg",
      vertical: "180deg",
      "left-diagonal": "135deg",
      "right-diagonal": "45deg",
    };
    const deg = directionMap[propValue.bgDirection] || "180deg";
    style.backgroundImage = `linear-gradient(${deg}, ${
      propValue.backgroundColor || "transparent"
    }, ${propValue.bgColor2})`;
  } else {
    style.backgroundColor = propValue.backgroundColor || "transparent";
  }
  if (propValue.showBorder && propValue.borderWidth) {
    style.backgroundSize = `100% calc(100% + ${propValue.borderWidth * scale * 4 || 0}rpx)`;
    style.backgroundPosition = `0rpx -${propValue.borderWidth * scale * 2 || 0}rpx`;
  }
  if (propValue.showShadow) {
    style.boxShadow = `${(propValue.shadowX || 0) * scale * 2}rpx ${
      (propValue.shadowY || 0) * scale * 2
    }rpx ${(propValue.shadowBlur || 0) * scale * 2}rpx ${(propValue.shadowSpread || 0) * scale * 2}rpx ${
      propValue.shadowColor || "#000000"
    }`;
  }
  if (propValue.showBorder) {
    style.border = `${propValue.borderWidth * 2}rpx ${
      propValue.borderStyle
    } ${propValue.borderColor}`;
  }
  return style;
}
function getPictureUrl(item, dataItem) {
  const field = item.propValue.fieldType;
  if (selectTypeValue.value === "user" && field === "image") {
    if (!isLogin.value) return item.propValue.url || "/static/images/f.png";
    return (
      userInfo.value.image || item.propValue.url || "/static/images/f.png"
    );
  }
  if (dataItem && field) {
    if (field === "image" && dataItem.image_input)
      return dataItem.image_input[0] || item.propValue.url;
    if (field === "image" && dataItem.image)
      return dataItem.image || item.propValue.url;
  }

  return item.propValue.url;
}
function getDisplayText(item, dataItem) {
  const field = item.propValue.fieldType;
  if (selectTypeValue.value === "article") {
    // Article mapping
    if (field === "title") return dataItem.title;
    if (field === "visit") return dataItem.visit;
    if (field === "add_time") return dataItem.add_time;
    if (field === "synopsis") return dataItem.synopsis;
  } else if (selectTypeValue.value === "coupon") {
    // Coupon mapping
    const field = item.propValue.fieldType;
    if (field === "coupon_title")
      return dataItem.coupon_title || dataItem.title;
    if (field === "coupon_price") return dataItem.coupon_price;
    if (field === "use_min_price") return dataItem.use_min_price;
    if (field === "coupon_time") return dataItem.coupon_time;
    if (field === "type")
      return dataItem.type === 1
        ? "品类券"
        : dataItem.type === 2
          ? "商品券"
          : "通用券";
    if (field === "status") return dataItem.status === 1 ? "开启" : "关闭";
    if (field === "receive_time") return dataItem.receive_time;
    if (field === "use_time") return dataItem.use_time;
    if (field === "receive_count") return dataItem.receive_count;
    if (field === "add_time") return dataItem.add_time;
  } else if (selectTypeValue.value === "goods") {
    // Goods mapping
    if (field === "store_name") return dataItem.store_name;
    if (field === "id") return dataItem.id;
    if (field === "image") return dataItem.image;
    if (field === "store_info") return dataItem.store_info;
    if (field === "unit_name") return dataItem.unit_name;
    if (field === "cate_name") return dataItem.cate_name;
    if (field === "label_name") return dataItem.label_name;
    if (field === "stock") return dataItem.stock;
    if (field === "price") return dataItem.price;
    if (field === "max_price") return dataItem.max_price;
    if (field === "min_price") return dataItem.min_price;
    if (field === "ot_price") return dataItem.ot_price;
    if (field === "max_ot_price") return dataItem.max_ot_price;
    if (field === "min_ot_price") return dataItem.min_ot_price;
    if (field === "min_qty") return dataItem.min_qty;
    if (field === "sales") return dataItem.sales;
    if (field === "browse") return dataItem.browse;
    if (field === "add_time") return dataItem.add_time;
  } else if (selectTypeValue.value === "user") {
    // User mapping
    if (field === "nickname") return dataItem.nickname;
    if (field === "uid") return dataItem.uid;
    if (field === "image") return dataItem.avatar;
    if (field === "collection_num") return dataItem.collection_num;
    if (field === "cart_num") return dataItem.cart_num;
    if (field === "order_num") return dataItem.order_num;
    if (field === "integral") return dataItem.integral;
    if (field === "now_money") return dataItem.now_money;
    if (field === "unread_msg_num") return dataItem.unread_msg_num;
    if (field === "mobile") return dataItem.mobile;
    if (field === "add_time") return dataItem.add_time;
  }
  return item.propValue.text;
}
function goDetail(dataItem, item) {
  if (item.propValue.linkType === "detail") {
    if (selectTypeValue.value === "goods") {
      uni.navigateTo({
        url: "/pages/goods_details/index?id=" + dataItem.id,
      });
    } else if (selectTypeValue.value === "coupon") {
      receiveCoupon(dataItem);
    } else if (selectTypeValue.value === "article") {
      uni.navigateTo({
        url: "/pages/extension/news_details/index?id=" + dataItem.id,
      });
    }
  } else {
    uni.navigateTo({
      url: item.propValue.link,
      fail: () => {
        uni.switchTab({ url: item.propValue.link });
      },
    });
  }
}
function receiveCoupon(item) {
  if (!isLogin.value) {
    emit("changeLogin");
    return;
  }
  setCouponReceive(item.id)
    .then(() => {
      uni.showToast({ title: i18n.t(`领取成功`), icon: "success" });
    })
    .catch((err) => {
      uni.showToast({ title: err, icon: "none" });
    });
}
function handleUserClick(item) {
  if (item.propValue.link) {
    if (item.propValue.linkType === "url") {
      uni.navigateTo({
        url: item.propValue.link,
        fail: () => {
          uni.switchTab({ url: item.propValue.link });
        },
      })
    }
  } else if (!isLogin.value) {
    emit("changeLogin"); // Trigger login modal via parent
  }
}
</script>

<style scoped>
.home_custom_component {
  position: relative;
}

.component-wrapper {
  position: relative;
}

.flex-center {
  display: flex;
  align-items: center;
  justify-content: center;
}

.ellipsis-text {
  /* #ifdef MP */
  display: -webkit-box;
  -webkit-box-orient: vertical;
  /* #endif */
  /* #ifndef MP */
  display: -webkit-box;
  -webkit-box-orient: vertical;
  /* #endif */
  overflow: hidden;
  white-space: normal;
  word-break: break-all;
}
</style>
