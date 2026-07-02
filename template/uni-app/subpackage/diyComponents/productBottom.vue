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
          <block v-for="(item_id, index) in showIcons" :key="index">
            <navigator
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
        <view v-if="!storeInfo.presale">
          <view
            class="bnt acea-row"
            :class="!isCartButtonVisible ? 'virbnt' : ''"
            v-if="attr.productSelect.stock <= 0"
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
                {{ $t(`立即购买`) }}
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

<script>
import commonWrapper from "./commonWrapper.vue";
import { getCustomer } from "@/utils/index.js";
export default {
  name: "productBottom",
  components: {
    commonWrapper,
  },
  props: {
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
    animated: {
      type: Boolean,
      default: false,
    },
    routineContact: {
      type: Number,
      default: 0,
    },
  },
  computed: {
    bottomConfig() {
      if (!this.diyData || !this.diyData.value) return null;
      const values = Object.values(this.diyData.value);
      return values.find((item) => item.name === "bottomMenu");
    },
    wrapperConfig() {
      let config = this.bottomConfig;
      if (!config) return null;
      let newConfig = { ...config };
      return newConfig;
    },
    toneConfig() {
      if (!this.bottomConfig || !this.bottomConfig.toneConfig) return 0;
      return this.bottomConfig.toneConfig.tabVal;
    },
    bagStyle() {
      if (this.bottomConfig) {
        //   const color = this.bottomConfig.bottomBgColor.color[0].item || this.bottomConfig.bottomBgColor.default[0].item;
        //   return `background-color: ${color}`;
        const color = this.bottomConfig.componentBgConfig.colorConfig.color;
        const c1 = color[0].item;
        const c2 = color[1].item;
        return `background: linear-gradient(90deg, ${c1} 0%, ${c2} 100%);`;
      }
    },
    cartBtnStyle() {
      if (this.toneConfig && this.bottomConfig.cartColor) {
        const color = this.bottomConfig.cartColor.color;
        const c1 = color[0].item;
        const c2 = color[1].item;
        return `background: linear-gradient(90deg, ${c1} 0%, ${c2} 100%);`;
      }
      return ""; // Fallback to CSS default
    },
    buyBtnStyle() {
      if (this.toneConfig && this.bottomConfig.buyColor) {
        const color = this.bottomConfig.buyColor.color;
        const c1 = color[0].item;
        const c2 = color[1].item;
        return `background: linear-gradient(90deg, ${c1} 0%, ${c2} 100%);`;
      }
      return ""; // Fallback to CSS default
    },
    showIcons() {
      if (!this.bottomConfig) return [3, 1, 2, 0, 4, 5];
      return this.bottomConfig.showContent.type;
    },
    showCartButton() {
      if (!this.bottomConfig) return true;
      return this.bottomConfig.cartButton.tabVal === 0;
    },
    isCartButtonVisible() {
      return !!this.storeInfo.cart_button && this.showCartButton;
    },
    entryConfig() {
      return this.bottomConfig && this.bottomConfig.entryConfig;
    },
    menuConfig() {
      return this.bottomConfig && this.bottomConfig.menuConfig;
    },
    isCustomEntry() {
      return this.entryConfig && this.entryConfig.tabVal === 1;
    },
    isCustomImage() {
      return this.isCustomEntry && this.menuConfig.listStyle === 0;
    },
    isCustomIcon() {
      return this.isCustomEntry && this.menuConfig.listStyle === 1;
    },
    customMenuList() {
      if (!this.isCustomEntry || !this.menuConfig) return [];
      return this.menuConfig.list
        .filter((item) => item.show)
        .map((item) => ({
          name: item.info[0].value,
          url: item.info[1].value,
          icon: item.icon,
          img: item.img,
        }));
    },
    customImageStyle() {
      const fillet = this.bottomConfig?.fillet;
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
    },
    customIconStyle() {
      const config = this.bottomConfig;
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
    },
  },
  methods: {
    goCustomer() {
      getCustomer(`/pages/extension/customer_list/chat?productId=${this.storeInfo.id}`);
    },
    goPage(url) {
      if (!url) return;
      uni.navigateTo({
        url: url,
        fail: () => {
          uni.switchTab({
            url: url,
          });
        },
      });
    },
    setCollect() {
      this.$emit("setCollect");
    },
    goCart() {
      this.$emit("goCart");
    },
    goGift() {
      this.$emit("goGift");
    },
    goShare() {
      this.$emit("share");
    },
    joinCart() {
      this.$emit("joinCart");
    },
    goBuy() {
      this.$emit("goBuy");
    },
  },
};
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
