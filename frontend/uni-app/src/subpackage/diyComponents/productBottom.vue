<template>
  <commonWrapper
    :config="wrapperConfig"
    class="footer"
    :class="{ eject: storeInfo.id }"
    :style="bagStyle"
  >
    <view class="acea-row row-between-wrapper px-20 py-14" style="height: 100%">
      <div class="acea-row">
        <block v-if="!isCustomEntry">
          <block v-for="(item_id, index) in displayIcons" :key="index">
            <navigator :render-link="false"
              v-if="item_id === 3"
              hover-class="none"
              class="item"
              open-type="reLaunch"
              url="/pages/index/index"
            >
              <view class="iconfont icon-shouye6"></view>
              <view class="p_center">{{ $t(`首页`) }}</view>
            </navigator>
            <view v-if="item_id === 1" @click="setCollect" class="item">
              <view
                class="iconfont icon-shoucang1"
                v-if="storeInfo.userCollect"
              ></view>
              <view class="iconfont icon-shoucang4" v-else></view>
              <view class="p_center">{{ $t(`收藏`) }}</view>
            </view>
            <view
              v-if="item_id === 2"
              class="animated item"
              :class="animated == true ? 'bounceIn' : ''"
              @click="goCart"
            >
              <view class="iconfont icon-gouwuche">
                <text class="num bg-color" v-if="parseFloat(CartCount) > 0">{{
                  CartCount || 0
                }}</text>
              </view>
              <view class="p_center">{{ $t(`购物车`) }}</view>
            </view>
            <!-- #ifdef APP-PLUS || H5 -->
            <view
              v-if="item_id === 0"
              class="item"
              @click="goCustomer"
            >
              <view class="iconfont icon-kefu"></view>
              <view class="p_center">{{ $t(`客服`) }}</view>
            </view>
            <!-- #endif -->
            <!-- #ifdef MP -->
            <view
              v-if="item_id === 0 && routineContact == 0"
              class="item"
              @click="goCustomer"
            >
              <view class="iconfont icon-kefu"></view>
              <view class="p_center">{{ $t(`客服`) }}</view>
            </view>
            <button
              v-else-if="item_id === 0 && routineContact == 1"
              class="item"
              open-type="contact"
              :send-message-title="storeInfo.store_name"
              :send-message-img="storeInfo.image"
              :send-message-path="`/pages/goods_details/index?id=${storeInfo.id}`"
              show-message-card
              hover-class="none"
            >
              <view class="iconfont icon-kefu"></view>
              <view class="p_center">{{ $t(`客服`) }}</view>
            </button>
            <!-- #endif -->
            <view v-if="item_id === 4" class="item" @click="goShare">
              <view class="iconfont icon-fenxiang4"></view>
              <view class="p_center">{{ $t(`分享`) }}</view>
            </view>
          </block>
          <view v-if="is_gift" @click="goGift()" class="item">
            <image
              class="gift-icon"
              src="@/static/images/gift-icon.png"
              mode=""
            ></image>
            <view class="p_center">{{ $t(`送礼物`) }}</view>
          </view>
        </block>
        <block v-else>
          <view
            class="item"
            v-for="(item, index) in customMenuList"
            :key="index"
            @click="goPage(item.url)"
          >
            <view
              v-if="isCustomIcon"
              class="iconfont"
              :class="item.icon"
              :style="customIconStyle"
            ></view>
            <image
              v-else
              :src="item.img"
              class="menu-img"
              :style="customImageStyle"
              mode="aspectFit"
            ></image>
            <view class="p_center">{{ item.name }}</view>
          </view>
          <view v-if="is_gift" @click="goGift()" class="item">
            <image
              class="gift-icon"
              src="@/static/images/gift-icon.png"
              mode=""
            ></image>
            <view class="p_center">{{ $t(`送礼物`) }}</view>
          </view>
        </block>
      </div>
      <view v-if="noGoods" class="presale">
        <view class="acea-row">
          <form class="bnts bg-color-hui">
            <button class="bnts bg-color-hui" form-type="submit">
              {{ $t(`暂无产品`) }}
            </button>
          </form>
        </view>
      </view>
      <view class="btn-box" v-else>
        <!-- 会员专享商品（vip_product=2 非会员）：引导开通会员 -->
        <view class="bnt acea-row virbnt" v-if="isVipExclusive">
          <form @submit="goOpenVip" class="buy bnts">
            <button class="buy bnts" form-type="submit" :style="buyBtnStyle">
              {{ $t(`开通会员后可购买`) }}
            </button>
          </form>
        </view>
        <view v-else-if="!storeInfo.presale">
          <!-- 售罄 + direct 模式：单按钮已售罄 -->
          <view
            class="bnt acea-row virbnt"
            v-if="attr.productSelect.stock <= 0 && isDirectMode"
          >
            <form class="bnts bg-color-hui">
              <button class="bnts bg-color-hui" form-type="submit">
                {{ $t(`已售罄`) }}
              </button>
            </form>
          </view>
          <!-- 售罄 + cart 模式：双按钮已售罄 -->
          <view
            class="bnt acea-row"
            :class="!isCartButtonVisible ? 'virbnt' : ''"
            v-else-if="attr.productSelect.stock <= 0"
          >
            <form
              v-if="isCartButtonVisible"
              @submit="joinCart"
              class="joinCart bnts"
              :class="!isCartButtonVisible ? 'virbnt' : ''"
            >
              <button
                class="joinCart bnts"
                form-type="submit"
                :style="cartBtnStyle"
              >
                {{ $t(`加入购物车`) }}
              </button>
            </form>
            <form class="buy bnts bg-color-hui">
              <button
                class="buy bnts bg-color-hui"
                form-type="submit"
                :class="!isCartButtonVisible ? 'virbnt' : ''"
              >
                {{ $t(`已售罄`) }}
              </button>
            </form>
          </view>
          <!-- 有库存 + direct 模式：单按钮直接下单（补 buy 类兜底背景色，与立即购买样式一致） -->
          <view class="bnt acea-row virbnt" v-else-if="isDirectMode">
            <form @submit="goBuy" class="buy bnts">
              <button class="buy bnts" form-type="submit" :style="buyBtnStyle">
                {{ buyButtonText }}
              </button>
            </form>
          </view>
          <!-- 有库存 + cart 模式：双按钮加入购物车+立即购买 -->
          <view class="bnt acea-row" v-else>
            <form
              v-if="isCartButtonVisible"
              @submit="joinCart"
              class="joinCart bnts"
            >
              <button
                class="joinCart bnts"
                form-type="submit"
                :style="cartBtnStyle"
              >
                {{ $t(`加入购物车`) }}
              </button>
            </form>
            <form
              @submit="goBuy"
              class="buy bnts"
              :class="!isCartButtonVisible ? 'virbnt' : ''"
            >
              <button
                class="buy bnts"
                :class="!isCartButtonVisible ? 'virbnt' : ''"
                form-type="submit"
                :style="buyBtnStyle"
              >
                {{ buyButtonText }}
              </button>
            </form>
          </view>
        </view>
        <view class="presale" v-else>
          <view
            class="acea-row"
            v-if="presale_pay_status === 1 || presale_pay_status === 3"
          >
            <form class="bnts bg-color-hui">
              <button class="bnts bg-color-hui" form-type="submit">
                {{ presale_pay_status === 1 ? $t(`未开始`) : $t(`已结束`) }}
              </button>
            </form>
          </view>
          <view
            class="acea-row"
            v-else-if="
              attr.productSelect.quota <= 0 ||
              attr.productSelect.quota < attr.productSelect.cart_num
            "
          >
            <form class="bnts bg-color-hui">
              <button class="bnts bg-color-hui" form-type="submit">
                {{ $t(`已售罄`) }}
              </button>
            </form>
          </view>
          <view class="bnts acea-row" v-else-if="presale_pay_status === 2">
            <form @submit="goBuy" class="bnts">
              <button class="bnts" form-type="submit" :style="buyBtnStyle">
                {{ $t(`立即购买`) }}
              </button>
            </form>
          </view>
        </view>
      </view>
    </view>
  </commonWrapper>
</template>

<script setup>
import { computed } from "vue";
import commonWrapper from "./commonWrapper.vue";
import { getCustomer } from "@/utils/index.js";
import i18n from "@/utils/lang.js";

defineOptions({ name: "productBottom" });

const emit = defineEmits(["setCollect", "goCart", "goGift", "share", "joinCart", "goBuy"]);

const props = defineProps({
  diyData: {
    type: Object,
    default: () => ({}),
  },
  storeInfo: {
    type: Object,
    default: () => ({}),
  },
  is_gift: {
    type: [Boolean, Number],
    default: 0,
  },
  CartCount: {
    type: [Number, String],
    default: 0,
  },
  noGoods: {
    type: Boolean,
    default: false,
  },
  attr: {
    type: Object,
    default: () => ({
      productSelect: {},
    }),
  },
  presale_pay_status: {
    type: [Number, String],
    default: 1,
  },
  // 加购策略契约（从 storeInfo.cart_strategy 透传）
  cart_strategy: {
    type: Object,
    default: () => ({}),
  },
  animated: {
    type: Boolean,
    default: false,
  },
  routineContact: {
    type: Number,
    default: 0,
  },
});

const bottomConfig = computed(() => {
  if (!props.diyData || !props.diyData.value) return null;
  const values = Object.values(props.diyData.value);
  return values.find((item) => item.name === "bottomMenu");
});
const wrapperConfig = computed(() => {
  let config = bottomConfig.value;
  if (!config) return null;
  let newConfig = { ...config };
  return newConfig;
});
const toneConfig = computed(() => {
  if (!bottomConfig.value || !bottomConfig.value.toneConfig) return 0;
  return bottomConfig.value.toneConfig.tabVal;
});
const bagStyle = computed(() => {
  if (bottomConfig.value) {
    //   const color = this.bottomConfig.bottomBgColor.color[0].item || this.bottomConfig.bottomBgColor.default[0].item;
    //   return `background-color: ${color}`;
    const color = bottomConfig.value.componentBgConfig.colorConfig.color;
    const c1 = color[0].item;
    const c2 = color[1].item;
    return `background: linear-gradient(90deg, ${c1} 0%, ${c2} 100%);`;
  }
});
const cartBtnStyle = computed(() => {
  if (toneConfig.value && bottomConfig.value.cartColor) {
    const color = bottomConfig.value.cartColor.color;
    const c1 = color[0].item;
    const c2 = color[1].item;
    return `background: linear-gradient(90deg, ${c1} 0%, ${c2} 100%);`;
  }
  return ""; // Fallback to CSS default
});
const buyBtnStyle = computed(() => {
  if (toneConfig.value && bottomConfig.value.buyColor) {
    const color = bottomConfig.value.buyColor.color;
    const c1 = color[0].item;
    const c2 = color[1].item;
    return `background: linear-gradient(90deg, ${c1} 0%, ${c2} 100%);`;
  }
  return ""; // Fallback to CSS default
});
const showIcons = computed(() => {
  if (!bottomConfig.value) return [3, 1, 2, 0, 4, 5];
  return bottomConfig.value.showContent.type;
});
// 送礼入口会占一席，左侧最多只保留 3 个按钮（含送礼）。
// 超出时按 首页(3) → 客服(0) → 购物车(2) 的优先级隐藏，收藏/分享保留。
// 模板仅渲染 0/1/2/3/4，占位 id 不参与配额，避免误判按钮数量。
const RENDERABLE_ICON_IDS = [3, 1, 2, 0, 4];
const displayIcons = computed(() => {
  const icons = (showIcons.value || []).filter((id) => RENDERABLE_ICON_IDS.includes(id));
  if (!props.is_gift) return (showIcons.value || []).slice();
  const hideOrder = [3, 0, 2];
  // 含送礼共 3 个 => 常规图标最多 2 个
  for (const id of hideOrder) {
    if (icons.length <= 2) break;
    const idx = icons.indexOf(id);
    if (idx > -1) icons.splice(idx, 1);
  }
  return icons;
});
const showCartButton = computed(() => {
  if (!bottomConfig.value) return true;
  return bottomConfig.value.cartButton.tabVal === 0;
});
// 加购模式：cart（双按钮）| direct（单按钮直接下单），缺失时 fallback 到 cart
const cartMode = computed(() => {
  return props.storeInfo.cart_strategy?.cart_mode || 'cart';
});
// 是否为直接下单模式（预售/租赁/服务等）
const isDirectMode = computed(() => {
  return cartMode.value === 'direct';
});
// 主按钮文案：优先读 cart_strategy.button_text，空则用默认"立即购买"
const buyButtonText = computed(() => {
  const text = props.storeInfo.cart_strategy?.button_text;
  return text ? i18n.t(text) : i18n.t(`立即购买`);
});
const isCartButtonVisible = computed(() => {
  // direct 模式不显示加入购物车按钮
  if (isDirectMode.value) return false;
  return !!props.storeInfo.cart_button && showCartButton.value;
});
// 会员专享商品（vip_product=2 非会员）：后端下发 vip_exclusive 标识，渲染"开通会员"引导按钮
const isVipExclusive = computed(() => {
  return !!props.storeInfo.vip_exclusive;
});
const entryConfig = computed(() => {
  return bottomConfig.value && bottomConfig.value.entryConfig;
});
const menuConfig = computed(() => {
  return bottomConfig.value && bottomConfig.value.menuConfig;
});
const isCustomEntry = computed(() => {
  return entryConfig.value && entryConfig.value.tabVal === 1;
});
const isCustomImage = computed(() => {
  return isCustomEntry.value && menuConfig.value.listStyle === 0;
});
const isCustomIcon = computed(() => {
  return isCustomEntry.value && menuConfig.value.listStyle === 1;
});
const customMenuList = computed(() => {
  if (!isCustomEntry.value || !menuConfig.value) return [];
  return menuConfig.value.list
    .filter((item) => item.show)
    .map((item) => ({
      name: item.info[0].value,
      url: item.info[1].value,
      icon: item.icon,
      img: item.img,
    }));
});
const customImageStyle = computed(() => {
  const fillet = bottomConfig.value?.fillet;
  if (!fillet) return { width: "40rpx", height: "40rpx" };
  let radius;
  if (fillet.type) {
    radius = `${fillet.valList[0].val}px ${fillet.valList[1].val}px ${fillet.valList[3].val}px ${fillet.valList[2].val}px`;
  } else {
    radius = `${fillet.val}px`;
  }
  return {
    borderRadius: radius,
    width: "40rpx",
    height: "40rpx",
    display: "block",
  };
});
const customIconStyle = computed(() => {
  const config = bottomConfig.value;
  if (!config) return {};
  const color = config.iconColor?.color?.[0]?.item || "#333";
  const size = config.iconSize?.val || 20;
  const rotate = config.iconRotate?.val || 0;
  const padding = config.padding?.val || 0;
  const shadow =
    config.shadow?.tabVal === 1 ? "0px 2px 4px rgba(0,0,0,0.2)" : "none";
  return {
    color: color,
    fontSize: `${size}px`,
    transform: `rotate(${rotate}deg)`,
    padding: `${padding}px`,
    textShadow: shadow,
    display: "inline-block",
  };
});

function goCustomer() {
  getCustomer(`/pages/extension/customer_list/chat?productId=${props.storeInfo.id}`);
}
function goPage(url) {
  if (!url) return;
  uni.navigateTo({
    url: url,
    fail: () => {
      uni.switchTab({
        url: url,
      });
    },
  });
}
function setCollect() {
  emit("setCollect");
}
function goCart() {
  emit("goCart");
}
function goGift() {
  emit("goGift");
}
function goShare() {
  emit("share");
}
function joinCart() {
  emit("joinCart");
}
function goBuy() {
  emit("goBuy");
}
// 会员专享商品：引导跳转会员开通页
function goOpenVip() {
  uni.navigateTo({ url: '/pages/user_vip/index' });
}
</script>

<style scoped lang="scss">
.footer {
  position: fixed;
  bottom: 0;
  width: 100%;
  box-sizing: border-box;
  z-index: 277;
  border-top: 1rpx solid #f0f0f0; // 保留默认边框作为兜底
  height: 100rpx;
  height: calc(100rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
  height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
  transform: translate3d(0, 100%, 0);
  transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);

  &.eject {
    transform: translate3d(0, 0, 0);
  }

  .gift-icon {
    width: 40rpx;
    height: 40rpx;
    margin: 5rpx 0 0rpx;
  }

  .item {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    font-size: 18rpx;
    margin-right: 30rpx;
    color: #666;

    .iconfont {
      text-align: center;
      font-size: 34rpx;

      &.icon-shoucang1 {
        color: var(--view-theme);
      }

      &.icon-gouwuche {
        position: relative;

        .num {
          color: #fff;
          position: absolute;
          font-size: 18rpx;
          padding: 2rpx 11rpx 3rpx;
          border-radius: 200rpx;
          top: -10rpx;
          right: -14rpx;
        }
      }
    }
  }

  uni-button {
    background-color: transparent;
  }
  .bnt {
    flex: 1;
    height: 76rpx;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: nowrap;
    .bnts {
      width: 100%;
      text-align: center;
      line-height: 76rpx;
      color: #fff;
      font-size: 28rpx;
      border-radius: 50rpx;
    }

    .joinCart {
      background-color: var(--view-bntColor);
      margin-right: 20rpx;
    }

    .buy {
      background-color: var(--view-theme);
    }
  }
}

.virbnt {
  // width: 444rpx !important;
  height: 76rpx !important;
  border-radius: 50rpx !important;
  overflow: hidden;
}

.virbnts {
  width: 444rpx !important;
  text-align: center;
  line-height: 76rpx;
  color: #fff;
  font-size: 28rpx;
  background-color: var(--view-bntColor);
  border-radius: 50rpx !important;
}
.btn-box {
  flex: 1;
}
.presale .bnts {
  width: 100%;
  height: 76rpx;
  border-radius: 50rpx 50rpx;
  background-color: var(--view-theme);
  text-align: center;
  line-height: 76rpx;
  color: #fff;
  font-size: 28rpx;
}
</style>
