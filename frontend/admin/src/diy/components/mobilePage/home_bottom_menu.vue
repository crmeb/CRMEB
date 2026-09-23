<template>
  <common_wrapper :config="configObj">
    <div class="bottom-menu acea-row row-middle">
      <!-- Icons Section -->
      <div class="icons-box acea-row row-middle">
        <div class="item" v-for="(item, index) in showIconsList" :key="index" :class="{ 'gift-item': item.id === 5 }">
          <div
            class="mb-iconfont"
            :class="item.icon"
            v-if="!isCustomImage && !item.url"
            :style="isCustomIcon ? customIconStyle : ''"
          ></div>
          <div v-else style="position: relative">
            <img class="menu-img" :src="item.img" :style="customImageStyle" alt="" />
            <span
              class="num"
              v-if="item.id === 2"
              style="
                position: absolute;
                top: -5px;
                right: -5px;
                background-color: #f5222d;
                color: #fff;
                font-size: 10px;
                padding: 0 4px;
                border-radius: 10px;
                line-height: 14px;
              "
              >0</span
            >
          </div>
          <div class="text">{{ item.name }}</div>
        </div>
      </div>

      <!-- Buttons Section -->
      <div class="buttons-box acea-row row-middle">
        <div
          class="btn cart-btn"
          v-if="showCartBtn"
          :style="{
            background: toneConfig ? cartBtnColor : themeColor2,
          }"
        >
          加入购物车
        </div>
        <div
          class="btn buy-btn"
          :style="{
            background: toneConfig ? buyBtnColor : themeColor,
          }"
        >
          立即购买
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '底部菜单',
  name: 'bottomMenu',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  entryConfig: {
    title: '入口内容',
    tabVal: 0,
    tabList: [{ name: '默认' }, { name: '自定义' }],
  },
  styleTitle: '样式设置',

  iconColor: {
    title: '图标颜色',
    default: [{ item: '#333' }],
    color: [{ item: '#333' }],
  },
  iconSize: {
    title: '图标大小',
    val: 20,
    min: 10,
    max: 50,
  },
  iconRotate: {
    title: '旋转角度',
    val: 0,
    min: 0,
    max: 360,
  },
  padding: {
    title: '内边距',
    val: 0,
    min: 0,
    max: 50,
  },
  contentConfigTitle: '内容设置',
  showContent: {
    title: '显示内容',
    name: 'showContent',
    type: [3, 1, 2], // Default: Service, Collect, Cart
    list: [
      { id: 3, name: '首页', icon: 'icon-shouye6' },
      { id: 1, name: '收藏', icon: 'icon-shoucang4' },
      { id: 2, name: '购物车', icon: 'icon-gouwuche' },
      { id: 0, name: '客服', icon: 'icon-kefu' },
      { id: 4, name: '分享', icon: 'icon-fenxiang4' },
    ],
  },
  cartButton: {
    title: '购物车按钮',
    tabVal: 0,
    tabList: [{ name: '显示' }, { name: '隐藏' }],
  },

  menuConfig: {
    title: '最多可添加1张图片，建议宽度90 * 90px',
    bnt: '添加',
    type: 1,
    listStyle: 0,
    maxList: 100,
    list: [
      {
        img: '',
        type: 0,
        show: true,
        icon: '',
        info: [
          {
            title: '标题',
            value: '标题',
            tips: '选填，不超过4个字',
            max: 4,
          },
          {
            title: '链接',
            value: '',
            tips: '请输入链接',
            max: 100,
          },
        ],
      },
    ],
  },
  buttonStyleTitle: '按钮设置',
  toneConfig: {
    title: '按钮色调',
    tabVal: 0, // 0: Follow Theme, 1: Custom
    tabList: [{ name: '跟随主题风格' }, { name: '自定义' }],
  },
  cartColor: {
    title: '购物车按钮',
    default: [{ item: '#FAAD14' }, { item: '#FAAD14' }],
    color: [{ item: '#FAAD14' }, { item: '#FAAD14' }],
  },
  buyColor: {
    title: '购买按钮',
    default: [{ item: '#E93323' }, { item: '#E93323' }],
    color: [{ item: '#E93323' }, { item: '#E93323' }],
  },
  generalStyleTitle: '通用样式',
  moduleColor: {
    title: '组件背景',
    default: [{ item: '#fff' }, { item: '#fff' }],
    color: [{ item: '#fff' }, { item: '#fff' }],
  },
  bottomBgColor: {
    title: '底部背景',
    default: [{ item: '#F5F5F5' }],
    color: [{ item: '#F5F5F5' }],
  },
  componentBgConfig: {
    title: '背景设置',
    tabVal: 0,
    tabList: [{ name: '颜色' }, { name: '图片' }],
    colorConfig: {
      title: '背景颜色',
      default: [{ item: '#fff' }, { item: '#fff' }],
      color: [{ item: '#fff' }, { item: '#fff' }],
    },
    colorDirection: {
      title: '渐变方向',
      tabVal: 0,
      tabList: [{ name: '横向' }, { name: '纵向' }, { name: '左斜' }, { name: '右斜' }],
    },
    imageConfig: {
      header: '背景图片',
      title: '',
      name: '上传图片',
      type: 'code',
      url: '',
      info: '建议尺寸：750px * 400px',
    },
  },
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  borderConfig: {
    title: '边框设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Hide, 1: Show
    styleConfig: {
      title: '边框样式',
      tabVal: 0,
      tabList: [
        { name: '实线', style: 'solid' },
        { name: '虚线', style: 'dashed' },
        { name: '点状', style: 'dotted' },
      ],
    },
    widthConfig: {
      title: '边框粗细',
      val: 1,
      min: 1,
    },
    colorConfig: {
      title: '边框颜色',
      default: [{ item: '#e5e5e5' }],
      color: [{ item: '#e5e5e5' }],
    },
  },
  shadowConfig: {
    title: '阴影设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0, // 0: Off, 1: On
    colorConfig: {
      title: '阴影颜色',
      default: [{ item: 'rgba(0,0,0,0.1)' }],
      color: [{ item: 'rgba(0,0,0,0.1)' }],
    },
    xConfig: {
      title: 'X轴偏移',
      val: 0,
      min: -50,
    },
    yConfig: {
      title: 'Y轴偏移',
      val: 0,
      min: -50,
    },
    blurConfig: {
      title: '模糊半径',
      val: 10,
      min: 0,
    },
    spreadConfig: {
      title: '扩展半径',
      val: 0,
      min: -50,
    },
  },
  paddingConfig: {
    title: '内边距',
    isAll: false,
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  marginConfig: {
    title: '外边距',
    isAll: false,
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  fillet: {
    title: '背景圆角',
    type: 0,
    list: [
      {
        val: '全部',
        icon: 'iconcaozuo-zhengti',
      },
      {
        val: '单个',
        icon: 'iconcaozuo-bianjiao',
      },
    ],
    valName: '圆角值',
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  menuPcFillet: {
    title: '圆角设置',
    type: 0,
    list: [
      {
        val: '全部',
        icon: 'iconcaozuo-zhengti',
      },
      {
        val: '单个',
        icon: 'iconcaozuo-bianjiao',
      },
    ],
    valName: '圆角值',
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
});
</script>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_bottom_menu',
  cname: '底部菜单',
  configName: 'c_bottom_menu',
  icon: '#iconzujian-dibucaidan',
  // Placeholder icon
  type: -1,
  // 0 基础组件 1 营销组件 2工具组件 3 商品组件 4 个人中心组件
  defaultName: 'bottomMenu',
});

const props = defineProps({
  index: {
    type: null,
  },
  num: {
    type: null,
  },
  colorStyle: {
    type: null,
  },
});
// 实例级默认配置（timestamp 取当前实例 num）
const defaultConfig = getDefaultConfig(props.num);

const mobildConfigStore = useMobildConfigStore();

const entryConfig = computed(() => {
  return configObj.value?.entryConfig;
});

const isCustomEntry = computed(() => {
  return entryConfig.value?.tabVal === 1;
});

const isCustomImage = computed(() => {
  return isCustomEntry.value && configObj.value?.menuConfig?.listStyle === 0;
});

const isCustomIcon = computed(() => {
  return isCustomEntry.value && configObj.value?.menuConfig?.listStyle === 1;
});

const customImageStyle = computed(() => {
  const fillet = configObj.value?.menuPcFillet;
  if (!fillet) return { width: '20px', height: '20px' };
  let radius;
  if (fillet.type) {
    radius = `${fillet.valList[0].val}px ${fillet.valList[1].val}px ${fillet.valList[3].val}px ${fillet.valList[2].val}px`;
  } else {
    radius = `${fillet.val}px`;
  }
  return {
    borderRadius: radius,
    width: '20px',
    height: '20px',
    display: 'block',
  };
});

const customIconStyle = computed(() => {
  const config = configObj.value;
  if (!config) return {};
  const color = config.iconColor?.color?.[0]?.item || '#333';
  const size = config.iconSize?.val || 20;
  const rotate = config.iconRotate?.val || 0;
  const padding = config.padding?.val || 0;
  const shadow = config.shadow?.tabVal === 1 ? '0px 2px 4px rgba(0,0,0,0.2)' : 'none';
  return {
    width: '20px',
    height: '20px',
    color: color,
    fontSize: `${size}px`,
    transform: `rotate(${rotate}deg)`,
    padding: `${padding}px`,
    textShadow: shadow,
    display: 'inline-block',
  };
});

const pageData = ref({});
const showIconsList = ref([]);
const showCartBtn = ref(true);
const toneConfig = ref(0);
const configObj = ref(null);
const cartBtnColor = ref('');
const buyBtnColor = ref('');
// bgColor: '',
// bottomBgColor: '',
const mTop = ref(0);
const topConfig = ref(0);
const bottomConfig = ref(0);
const prConfig = ref(0);
// bgRadius: 0,
const themeColor = ref('');
const themeColor2 = ref('');

watch(
  pageData,
  (nVal, oVal) => {
    setConfig(nVal);
  },
  { deep: true },
);
watch(
  () => props.num,
  (nVal, oVal) => {
    let data;
    if (nVal) {
      data = mobildConfigStore.defaultArray[nVal];
    } else {
      data = mobildConfigStore.bottomMenu;
    }
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  (nVal, oVal) => {
    if (props.num) {
      let data = mobildConfigStore.defaultArray[props.num];
      setConfig(data);
    }
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.bottomMenu,
  (nVal, oVal) => {
    if (!props.num) {
      setConfig(nVal);
    }
  },
  { deep: true },
);
watch(
  () => props.colorStyle,
  (nVal, oVal) => {
    themeColor.value = `linear-gradient(90deg,${nVal.theme} 0%,${nVal.gradient} 100%)`;
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    if (props.num) {
      pageData.value = mobildConfigStore.defaultArray[props.num];
    } else {
      pageData.value = mobildConfigStore.bottomMenu;
    }
    setConfig(pageData.value);
  });
});

function setConfig(data) {
  if (!data) return;
  // Content
  if (data.entryConfig && data.entryConfig.tabVal === 1) {
    // Custom Mode: Use menuConfig list
    let list = data.menuConfig.list || [];
    showIconsList.value = list
      .filter((item) => item.show)
      .map((item) => ({
        name: item.info[0].value,
        icon: item.icon, // Using icon class if available
        img: item.img, // Or image
        id: 'custom', // Flag for custom
        url: item.url, // Custom URL
      }));
  } else {
    // Default Mode: Use showContent
    let showIds = data.showContent.type;
    let allIcons = data.showContent.list;
    showIconsList.value = showIds.map((id) => allIcons.find((item) => item.id === id)).filter((item) => item);
  }

  showCartBtn.value = data.cartButton.tabVal === 0;

  // Style
  toneConfig.value = data.toneConfig.tabVal;

  let cartC1 = data.cartColor.color[0].item;
  let cartC2 = data.cartColor.color[1].item;
  cartBtnColor.value = `linear-gradient(90deg,${cartC1} 0%,${cartC2} 100%)`;

  let buyC1 = data.buyColor.color[0].item;
  let buyC2 = data.buyColor.color[1].item;
  buyBtnColor.value = `linear-gradient(90deg,${buyC1} 0%,${buyC2} 100%)`;

  if (!data.componentBgConfig) {
    data.componentBgConfig = {
      title: '背景设置',
      tabVal: 0,
      tabList: [{ name: '颜色' }, { name: '图片' }],
      colorConfig: {
        title: '背景颜色',
        default: data.moduleColor.default,
        color: data.moduleColor.color,
      },
      colorDirection: {
        title: '渐变方向',
        tabVal: 0,
        tabList: [{ name: '横向' }, { name: '纵向' }, { name: '左斜' }, { name: '右斜' }],
      },
      imageConfig: {
        header: '背景图片',
        title: '',
        name: '上传图片',
        type: 'code',
        url: '',
        info: '建议尺寸：750px * 400px',
      },
    };
  }
  if (!data.marginConfig) {
    data.marginConfig = {
      isAll: false,
      val: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  configObj.value = data;
  themeColor.value = `linear-gradient(90deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
  themeColor2.value = 'linear-gradient(90deg, #FAAD14 0%, #FAAD14 100%)';
}
</script>

<style scoped lang="scss">
.bottom-menu {
  height: 50px;
  padding: 0 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  .menu-img {
    width: 20px;
    height: 20px;
  }
  .icons-box {
    display: flex;
    margin-right: 10px;
    .item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: #333;

      .iconfont {
        font-size: 20px;
        margin-bottom: 2px;
      }
      .text {
        font-size: 10px;
      }

      &:last-child {
        margin-right: 0;
      }
    }
  }

  .buttons-box {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    flex: 1;
    .btn {
      width: 100%;
      height: 36px;
      border-radius: 18px;
      color: #fff;
      text-align: center;
      line-height: 36px;
      font-size: 14px;
      // margin-left: 10px;

      &:first-child {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        margin-left: 0;
        margin-right: 0; // Stick together?
        border-radius: 18px;
      }

      &:last-child {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-radius: 18px;
      }

      &.cart-btn {
        // Orange handled by inline style
        background: #faad14;
        margin-right: 10px;
      }

      &.buy-btn {
        // Red handled by inline style
      }

      // &.full-width {
      //   width: 200px; // Or flex-grow
      //   border-radius: 18px;
      // }
    }

    // Adjust button style based on design.
    // The image shows buttons are separated or joined depending on style?
    // "Default Style": Joined. Yellow (Add) + Red (Buy).
    // "Hide Cart Button": One big Red button.
    // "2 icons + 2 buttons": Separated buttons in image 3? No, image 3 shows them separated with rounded corners.
    // Image 1 (Default): Joined.
    // Image 3 (2 icons + 2 buttons): Separated.

    // Wait, looking closely at the image:
    // Top Left (Default): Headset, Star, Cart | [Add to Cart][Buy Now] (Joined, pill shape)
    // Top Right (Hide Cart): Headset, Star, Cart | [Buy Now] (Full Pill)
    // Middle Left (2 icons + 2 buttons): Headset, Star | [Add to Cart] [Buy Now] (Separated pills? No, joined pill with gap? Or just two buttons?)
    // Actually, "2 icons + 2 buttons" looks like [Add to Cart] (Yellow Pill) [Buy Now] (Red Pill) -- Separated!
    // "1 icon + 2 buttons": Headset | [Add to Cart] (Yellow Pill) [Buy Now] (Red Pill) -- Separated.

    // BUT "Default Style" shows them JOINED.

    // Why the difference? Maybe it depends on space?
    // Or maybe the user didn't change style, but just removed icons, and the buttons automatically expanded/separated?
    // The image title "2 icons + 2 buttons" shows them separated.
    // The "Default" shows them joined.

    // I will implement "Joined" for default, and if there's enough space, maybe they separate?
    // Or I should just make them separate but margin 0 if joined?
    // The image shows "Add to Cart" is Left half pill, "Buy Now" is Right half pill in Default.
    // In "2 icons + 2 buttons", they are two full pills.

    // I don't see a config for "Button Style" (Joined vs Separated).
    // It might be responsive? Or based on count?
    // Let's assume they are joined by default for the standard e-commerce look.
    // But if I look at the "2 icons + 2 buttons" image, they are clearly separated.
    // Maybe I should just make them separated with small margin for now, or use border-radius manipulation.

    // Actually, looking at the "Default Style" image again:
    // [Add to Cart][Buy Now] -> Left one has left radius, right one has right radius.

    // In "2 icons + 2 buttons":
    // [Add to Cart] -> Full radius.
    // [Buy Now] -> Full radius.

    // This implies if there is space, they might separate?
    // Or maybe it's just a different style choice I missed?
    // I don't see a style choice for "Button Layout".

    // I'll stick to the "Default Style" (Joined) as the base implementation because it's the most complex to get right (radius manipulation).
    // If the user wants them separated, I might need more info or add a config.
    // Wait, looking at "2 icons + 1 button" (Red button), it's a full pill.
    // "1 icon + 1 button" (Red button), full pill.

    // I will implement the "Joined" style for 2 buttons, and "Full Pill" for 1 button.
    // If the user really wants the separated look in the screenshot, I might be missing something.
    // But joined is standard.
  }
}
</style>
