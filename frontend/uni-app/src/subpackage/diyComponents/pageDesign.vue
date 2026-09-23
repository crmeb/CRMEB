<template>
  <view class="page-design" :class="bgClass">
    <view v-if="!errorNetwork" :style="colorStyle">
      <!-- 轮播搜索 -->
      <homeComb
        v-if="showHomeComb"
        :dataConfig="homeCombData"
        :belongIndex="belongIndex"
        @bindSortId="bindSortId"
        :isScrolled="isScrolled"
        @storeTap="storeTap"
      ></homeComb>

      <!-- 顶部搜索框 -->
      <headerSearch
        v-if="isHeaderSerch"
        :dataConfig="headerSearchCombData"
        :belongIndex="belongIndex"
        @storeTap="storeTap"
      ></headerSearch>

      <tabNav
        v-if="showCateNav"
        :dataConfig="cateNavData"
        @bindHeight="bindHeight"
        @bindSortId="bindSortId"
        :isFixed="isFixed && !cateNavData.stickyConfig.tabVal"
        :isFirst="tabNavIsFirst"
      ></tabNav>

      <view class="index">
        <!-- 自定义样式 -->
        <block v-for="(item, index) in styleConfig" :key="index">
          <view :id="item.id">
            <userInfor
              v-if="item.name == 'userInfor'"
              :dataConfig="item"
              @changeLogin="changeLogin"
            ></userInfor>
            <homeUserInfor
              v-else-if="item.name == 'member'"
              :dataConfig="item"
              @changeLogin="changeLogin"
            ></homeUserInfor>
            <newVip
              v-else-if="item.name == 'newVip'"
              :dataConfig="item"
            ></newVip>
            <!-- 文章列表 -->
            <articleList
              v-else-if="item.name == 'articleList'"
              :dataConfig="item"
            ></articleList>
            <bargain
              v-else-if="item.name == 'bargain'"
              :dataConfig="item"
              @changeBarg="changeBarg"
            ></bargain>
            <blankPage
              v-else-if="item.name == 'blankPage'"
              :dataConfig="item"
            ></blankPage>
            <combination
              v-else-if="item.name == 'combination'"
              :dataConfig="item"
            ></combination>
            <!-- 优惠券 -->
            <coupon
              v-else-if="item.name == 'coupon'"
              :dataConfig="item"
              @changeLogin="changeLogin"
            ></coupon>
            <!-- 客户服务 -->
            <customerService
              v-else-if="item.name == 'customerService'"
              :dataConfig="item"
            ></customerService>
            <!-- 商品列表 -->
            <goodList
              ref="goodLists"
              v-else-if="
                item.name == 'goodList' || item.name == 'goodRecommend'
              "
              :dataConfig="item"
              :list="goodListData"
            ></goodList>
            <!-- <homeGoodRecommend
              v-else-if="
                item.name == 'goodList' || item.name == 'goodRecommend'
              "
              :dataConfig="item"
            ></homeGoodRecommend> -->
            <guide v-else-if="item.name == 'guide'" :dataConfig="item"></guide>
            <!-- 直播模块 -->
            <!-- #ifdef  MP-WEIXIN -->
            <liveBroadcast
              v-else-if="item.name == 'liveBroadcast'"
              :dataConfig="item"
            ></liveBroadcast>
            <!-- #endif -->
            <menus v-else-if="item.name == 'menus'" :dataConfig="item"></menus>
            <!-- 实时消息 -->
            <news v-else-if="item.name == 'news'" :dataConfig="item"></news>
            <!-- 图片库 -->
            <pictureCube
              v-else-if="item.name == 'pictureCube'"
              :dataConfig="item"
            ></pictureCube>
            <!-- 促销列表 -->
            <promotionList
              ref="promotionLists"
              v-else-if="item.name == 'promotionList'"
              :dataConfig="item"
              :productVideoStatus="productVideoStatus"
              :positionTop="positionTop"
            ></promotionList>
            <seckill
              v-else-if="item.name == 'seckill'"
              :dataConfig="item"
            ></seckill>
            <!-- 轮播图-->
            <swiperBg
              v-else-if="item.name == 'swiperBg'"
              :dataConfig="item"
            ></swiperBg>
            <swipers
              v-else-if="item.name == 'swipers'"
              :dataConfig="item"
            ></swipers>

            <!-- 标题 -->
            <titles
              v-else-if="item.name == 'titles'"
              :dataConfig="item"
            ></titles>
            <presale
              v-else-if="item.name == 'presale'"
              :dataConfig="item"
            ></presale>
            <pointsMall
              v-else-if="item.name == 'pointsMall'"
              :dataConfig="item"
            ></pointsMall>
            <!-- #ifndef APP -->
            <richText
              v-else-if="item.name == 'richText'"
              :dataConfig="item"
            ></richText>
            <videos
              v-else-if="item.name == 'videos'"
              :dataConfig="item"
            ></videos>
            <!-- #endif -->
            <signIn
              v-else-if="item.name == 'signIn'"
              :dataConfig="item"
              :isPageVisible="isPageVisible"
            ></signIn>
            <hotspot
              v-else-if="item.name == 'hotspot'"
              :dataConfig="item"
            ></hotspot>
            <follow
              v-else-if="item.name == 'follow'"
              :dataConfig="item"
            ></follow>
            <!-- 商品详情 -->
            <productInfo
              v-else-if="item.name == 'productInfo'"
              :dataConfig="item"
              :productData="productData"
              :priceData="priceData"
              :skuList="skuList"
              :attrValue="attrValue"
              @changeSpec="onChangeSpec"
              @showSpecModal="onShowSpecModal"
              @share="onShare"
              @goActivity="onGoActivity"
            ></productInfo>
            <homePaidVip
              v-else-if="item.name == 'home_paid_vip'"
              :dataConfig="item"
              :productData="productData"
              :isShowPaidVip="isShowPaidVip"
              :priceData="priceData"
            ></homePaidVip>
            <homeProductService
              v-else-if="item.name == 'productService'"
              :dataConfig="item"
              :productData="productData"
              :couponList="couponList"
              :activity="activity"
              :attr="attr"
              :attrTxt="attrTxt"
              :attrValue="attrValue"
              @showCoupon="onShowCoupon"
              @showSpecModal="onShowSpecModal"
              @openModal="onOpenModal"
              @goActivity="onGoActivity"
            ></homeProductService>
            <homeReviews
              v-else-if="item.name == 'reviews'"
              :dataConfig="item"
              :reply="reply"
              :replyCount="replyCount"
              :replyChance="replyChance"
              :productId="productId"
            ></homeReviews>
            <productDesc
              v-else-if="item.name == 'productDesc'"
              :dataConfig="item"
              :productData="productData"
            ></productDesc>
            <!-- ===== 产品类型扩展组件（display_sections 数据驱动，新增产品类型前端零改动）===== -->
            <!-- 万能信息卡：履约/须知/规则等键值对列表 -->
            <infoCard
              v-else-if="item.name == 'infoCard'"
              :dataConfig="item"
              :sections="displaySectionsByType('info_card')"
            ></infoCard>
            <!-- 门店/场馆卡：自提店/演出场馆/酒店地址 -->
            <storeCard
              v-else-if="item.name == 'storeCard'"
              :dataConfig="item"
              :sections="displaySectionsByType('store_card')"
            ></storeCard>
            <!-- 时间线：预售期/有效期/场次时间 -->
            <timeCard
              v-else-if="item.name == 'timeCard'"
              :dataConfig="item"
              :sections="displaySectionsByType('time_card')"
            ></timeCard>
            <!-- 售卖形态展示位：按 block.type 分发到 6 个通用子渲染器 -->
            <specPreview
              v-else-if="item.name == 'specPreview'"
              :dataConfig="item"
              :section="displaySectionByKey('spec')"
              :productData="productData"
              :attr="attr"
              :attrTxt="attrTxt"
              :attrValue="attrValue"
              @changeSpec="onChangeSpec"
              @showSpecModal="onShowSpecModal"
              @blockSelect="onBlockSelect"
            ></specPreview>
            <customComponent
              v-else-if="item.name == 'customComponent'"
              :dataConfig="item"
              @changeLogin="changeLogin"
            ></customComponent>
          </view>
        </block>

        <!-- 插槽：用于展示分类商品列表或其他底部内容 -->
        <slot name="bottom"></slot>

        <view class="pb-safe" :style="[pdHeights]" v-if="isFooter"></view>
        <pageFooter
          v-if="footerConfigData"
          :configData="footerConfigData"
          @newDataStatus="newDataStatus"
        ></pageFooter>
      </view>
    </view>

    <view v-else>
      <view class="error-network">
        <image :src="imgHost + '/statics/images/error-network.gif'"></image>
        <view class="title">{{ $t(`网络连接断开`) }}</view>
        <view class="btn" @click="reconnect">{{ $t(`重新连接`) }}</view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import pageFooter from "@/components/pageFooter/index.vue";
import { HTTP_REQUEST_URL } from "@/config/app";
import { useColorTheme } from "@/composables/useColorTheme";
// diyComponents - 同目录使用相对路径
import homeComb from "./homeComb.vue";
import headerSearch from "./headerSearch.vue";
import tabNav from "./tabNav.vue";
import userInfor from "./userInfor.vue";
import homeUserInfor from "./homeUserInfor.vue";
import newVip from "./newVip.vue";
import articleList from "./articleList.vue";
import bargain from "./bargain.vue";
import blankPage from "./blankPage.vue";
import combination from "./combination.vue";
import coupon from "./coupon.vue";
import customerService from "./customerService.vue";
import goodList from "./goodList.vue";
import guide from "./guide.vue";
import liveBroadcast from "./liveBroadcast.vue";
import menus from "./menus.vue";
import news from "./news.vue";
import pictureCube from "./pictureCube.vue";
import promotionList from "./promotionList.vue";
import seckill from "./seckill.vue";
import swiperBg from "./swiperBg.vue";
import swipers from "./swipers.vue";
import titles from "./titles.vue";
import presale from "./presale.vue";
import pointsMall from "./pointsMall.vue";
import richText from "./richText.vue";
import videos from "./videos.vue";
import signIn from "./signIn.vue";
import hotspot from "./hotspot.vue";
import follow from "./follow.vue";
import productInfo from "./productInfo.vue";
import homePaidVip from "./homePaidVip.vue";
import homeProductService from "./homeProductService.vue";
import homeReviews from "./homeReviews.vue";
import productDesc from "./productDesc.vue";
import customComponent from "./customComponent.vue";
// 产品类型扩展组件（display_sections 数据驱动）
import infoCard from "./productSections/infoCard.vue";
import storeCard from "./productSections/storeCard.vue";
import timeCard from "./productSections/timeCard.vue";
import specPreview from "./productSections/specPreview.vue";

defineOptions({ name: "PageDesign" });

const emit = defineEmits([
  "reconnect",
  "changeSpec",
  "showSpecModal",
  "blockSelect",
  "showCoupon",
  "openModal",
  "goActivity",
  "share",
  "bindSortId",
  "bindHeight",
  "storeTap",
  "changeLogin",
  "changeBarg",
  "newDataStatus",
]);

const props = defineProps({
  // DIY配置数据
  diyData: {
    type: Object,
    default: () => ({}),
  },
  // 是否为首页（用于控制小程序添加到我的小程序提示等）
  isHome: {
    type: Boolean,
    default: false,
  },
  isPageVisible: {
    type: Boolean,
    default: true,
  },
  // 页面滚动状态
  isScrolled: {
    type: Boolean,
    default: false,
  },
  // 是否固定（用于吸顶）
  isFixed: {
    type: Boolean,
    default: false,
  },
  productData: {
    type: Object,
    default: () => ({}),
  },
  priceData: {
    type: Object,
    default: () => ({}),
  },
  skuList: {
    type: Array,
    default: () => [],
  },
  reply: {
    type: Array,
    default: () => [],
  },
  replyCount: {
    type: Number,
    default: 0,
  },
  replyChance: {
    type: [String, Number],
    default: 0,
  },
  productId: {
    type: [Number, String],
    default: 0,
  },
  goodList: {
    type: Array,
    default: () => [],
  },
  // 视频播放状态
  productVideoStatus: {
    type: Boolean,
    default: false,
  },
  // 进店规则归属门店排序位置
  belongIndex: {
    type: Number,
    default: 0,
  },
  // 网络错误状态
  errorNetwork: {
    type: Boolean,
    default: false,
  },
  couponList: {
    type: Array,
    default: () => [],
  },
  activity: {
    type: Array,
    default: () => [],
  },
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
  // 微页面
  microPage: {
    type: Boolean,
    default: false,
  },
  // 商品 vip 模块
  isShowPaidVip: {
    type: Boolean,
    default: false,
  },
  // 后端 display_sections（产品类型/营销监听器输出的展示块数组）
  // 结构：[{key, component, title, visible, order, data}]
  // component 枚举：spec_preview / info_card / store_card / time_card
  displaySections: {
    type: Array,
    default: () => [],
  },
});

const { colorStyle } = useColorTheme();

// prop goodList 与 import 进来的 goodList 组件同名，模板里直接写 goodList 会解析到组件对象
// （script setup 的 setup 绑定优先于 prop），必须经别名桥接，否则传给子组件的是 Object
const goodListData = computed(() => props.goodList);

const styleConfig = ref([]);
const homeCombData = ref({});
const headerSearchCombData = ref({});
const cateNavData = ref({});
const footerConfigData = ref(null);
const showHomeComb = ref(false);
const isHeaderSerch = ref(false);
const showCateNav = ref(false);
const bgColor = ref("");
const bgPic = ref("");
const bgTabVal = ref("");
const positionTop = ref(0);
const isFooter = ref(false);
const pdHeight = ref(0);
const imgHost = ref(HTTP_REQUEST_URL);

const pageStyle = computed(() => {
  return {
    backgroundColor: bgColor.value,
    backgroundImage: bgPic.value ? `url(${bgPic.value})` : "",
    minHeight: "100vh", // 确保背景铺满
  };
});
const bgClass = computed(() => {
  if (bgTabVal.value == 2) return "fullsize noRepeat";
  if (bgTabVal.value == 1) return "repeat ysize";
  return "noRepeat ysize";
});
const pdHeights = computed(() => {
  let H = `${pdHeight.value * 2 + 100}rpx`;
  return {
    height: isFooter.value ? H : "100rpx",
  };
});
// tabNav 是否为第一个顶部组件（前面无轮播搜索、顶部搜索框）
const tabNavIsFirst = computed(() => {
  return !showHomeComb.value && !isHeaderSerch.value;
});

watch(
  () => props.diyData,
  (val) => {
    if (val && Object.keys(val).length > 0) {
      setDiyData(val);
    } else {
      // 重置数据
      styleConfig.value = [];
      homeCombData.value = {};
      headerSearchCombData.value = {};
      cateNavData.value = {};
      footerConfigData.value = null;
      showHomeComb.value = false;
      isHeaderSerch.value = false;
      showCateNav.value = false;
      bgColor.value = "";
      bgPic.value = "";
      bgTabVal.value = "";
    }
  },
  { deep: true, immediate: true }
);

function reconnect() {
  emit("reconnect");
}
function onChangeSpec(item) {
  emit("changeSpec", item);
}
function onShowSpecModal() {
  emit("showSpecModal");
}
/**
 * specPreview 内 block 选中（票种/场次/日期），透传选中数据给 goods_details
 * goods_details 在 goCat 跳转下单页时携带选中数据
 * @param {Object} payload {type, item}
 */
function onBlockSelect(payload) {
  emit("blockSelect", payload);
}
/**
 * 按 component 类型过滤 display_sections
 * 用于 infoCard/storeCard/timeCard 三个通用组件，支持同类型多 section 渲染
 * @param {string} type component 类型枚举：info_card / store_card / time_card
 * @return {Array} 过滤并排序后的 section 数组（仅 visible=true）
 */
function displaySectionsByType(type) {
  if (!Array.isArray(props.displaySections) || !type) return [];
  return props.displaySections
    .filter((s) => s && s.component === type && s.visible !== false)
    .sort((a, b) => (a.order || 0) - (b.order || 0));
}
/**
 * 按 key 精确查找单个 display_section
 * 用于 specPreview 容器（spec section 全商品仅一个）
 * @param {string} key section 的 key 字段
 * @return {Object|null} 匹配的 section 对象，未找到返回 null
 */
function displaySectionByKey(key) {
  if (!Array.isArray(props.displaySections) || !key) return null;
  return (
    props.displaySections.find(
      (s) => s && s.key === key && s.visible !== false
    ) || null
  );
}
function onShowCoupon() {
  emit("showCoupon");
}
function onOpenModal(type) {
  emit("openModal", type);
}
function onGoActivity(item) {
  emit("goActivity", item);
}
function onShare() {
  emit("share");
}
// 对象转数组
function objToArr(data) {
  if (!data) return [];
  let obj = Object.keys(data).sort();
  let m = obj.map((key) => data[key]);
  return m;
}
function setDiyData(data) {
  if (!data) return;
  if (data.is_bg_color) {
    bgColor.value = data.color_picker;
  }
  if (data.is_bg_pic) {
    bgPic.value = data.bg_pic;
    bgTabVal.value = data.bg_tab_val;
  }

  let temp = [];
  // 重置状态
  showHomeComb.value = false;
  isHeaderSerch.value = false;
  showCateNav.value = false;
  footerConfigData.value = null;
  if (data.value) {
    // 不可用组件节点已由后端在数据出口直接剔除，前端拿到的即干净数据，无需过滤判断
    let lastArr = objToArr(data.value);
    lastArr.forEach((item) => {
      if (item.name == "pageFoot" && !props.microPage) {
        footerConfigData.value = item;
      }
      if (item.name === "homeComb" && !item.isHide) {
        showHomeComb.value = true;
        homeCombData.value = item;
        if (item.searchConfig && item.searchConfig.tabVal) {
          positionTop.value = uni.getWindowInfo().statusBarHeight + 43;
        }
      }
      if (item.name == "headerSerch" && !item.isHide) {
        isHeaderSerch.value = true;
        headerSearchCombData.value = item;
      }
      if (item.name == "tabNav" && !item.isHide) {
        showCateNav.value = true;
        cateNavData.value = item;
      }
      if (!item.isHide) {
        temp.push(item);
      }
    });

    // 排序
    temp.sort((a, b) => a.timestamp - b.timestamp);
    styleConfig.value = temp;
  }
}
function bindSortId(item, data) {
  emit("bindSortId", item, data);
}
function bindHeight(data) {
  emit("bindHeight", data);
}
function storeTap(id) {
  emit("storeTap", id);
}
function changeLogin() {
  emit("changeLogin");
}
function changeBarg(item) {
  emit("changeBarg", item);
}
function newDataStatus(val, num) {
  isFooter.value = val ? true : false;
  pdHeight.value = num;
  emit("newDataStatus", { val, num });
}
</script>

<style lang="scss" scoped>
.page-design {
  overflow-y: scroll;
  overflow-x: hidden;
  min-height: 100vh;
}

.ysize {
  background-size: 100%;
}

.fullsize {
  background-size: 100% 100%;
}

.repeat {
  background-repeat: repeat;
}

.noRepeat {
  background-repeat: no-repeat;
}

.error-network {
  position: fixed;
  left: 0;
  top: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  height: 100%;
  padding-top: 40rpx;
  background: #fff;

  image {
    width: 414rpx;
    height: 336rpx;
  }

  .title {
    position: relative;
    top: -40rpx;
    font-size: 32rpx;
    color: #666;
  }

  .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 508rpx;
    height: 86rpx;
    margin-top: 100rpx;
    border: 1px solid #d74432;
    color: #e93323;
    font-size: 30rpx;
    border-radius: 120rpx;
  }
}
</style>
