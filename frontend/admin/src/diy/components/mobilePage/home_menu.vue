<template>
  <common_wrapper v-if="configObj" :config="configObj">
    <div
      class="mobile-page"
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
          : filletVal + 'px',
      }"
    >
      <div>
        <!-- 左上角和右上角文字 -->
        <div
          class="menu-header"
          v-if="headerConfig.enable && (leftTopText.enable || rightTopText.enable)"
          :style="{
            paddingTop: headerStyle.topPadding + 'px',
            paddingBottom: headerStyle.bottomPadding + 'px',
            paddingLeft: headerStyle.leftRightPadding + 'px',
            paddingRight: headerStyle.leftRightPadding + 'px',
          }"
        >
          <span
            class="left-text"
            v-show="leftTopText.enable"
            :style="{
              color: headerStyle.leftColor,
              fontSize: headerStyle.fontSize + 'px',
              fontWeight: headerStyle.leftWeight,
            }"
          >
            {{ leftTopText.text }}
          </span>
          <span
            class="right-text"
            v-show="rightTopText.enable"
            :style="{
              color: headerStyle.rightColor,
              fontSize: headerStyle.rightFontSize + 'px',
              fontWeight: headerStyle.rightWeight,
            }"
          >
            {{ rightTopText.text }}
            <span
              v-if="rightTopText.text && rightTopText.link"
              class="mb-iconfont icon-xiangyou"
              :style="{ fontSize: headerStyle.rightFontSize + 'px' }"
            ></span>
          </span>
        </div>

        <!-- 列表样式 -->
        <div class="mobile-page" v-if="menuStyleConfig === 2">
          <div class="list-menu">
            <div class="list-item" v-for="(item, index) in vuexMenu" :key="index">
              <div class="list-item-content">
                <div
                  class="list-img-box"
                  v-if="navDisplayStyle !== 2"
                  :style="{
                    borderRadius: filletImg
                      ? valListImg[0].val +
                        'px ' +
                        valListImg[1].val +
                        'px ' +
                        valListImg[3].val +
                        'px ' +
                        valListImg[2].val +
                        'px'
                      : filletValImg + 'px',
                  }"
                >
                  <template v-if="listStyle == 0">
                    <img :src="item.img" v-if="item.img" alt="" />
                    <div
                      class="list-empty-box"
                      v-else
                      :style="{
                        borderRadius: filletImg
                          ? valListImg[0].val +
                            'px ' +
                            valListImg[1].val +
                            'px ' +
                            valListImg[3].val +
                            'px ' +
                            valListImg[2].val +
                            'px'
                          : filletValImg + 'px',
                      }"
                    >
                      <img src="@diy/assets/images/shan.png" />
                    </div>
                  </template>
                  <template v-else>
                    <div
                      class="icon-box"
                      style="display: flex; align-items: center; width: 100%; height: 100%"
                      :style="{
                        justifyContent:
                          iconStyleConfig.position === 0
                            ? 'flex-start'
                            : iconStyleConfig.position === 1
                            ? 'center'
                            : 'flex-end',
                      }"
                    >
                      <span
                        :class="['mb-iconfont', item.icon]"
                        :style="{
                          color: iconStyleConfig.color,
                          fontSize: iconStyleConfig.size + 'px',
                          lineHeight: iconStyleConfig.size + 'px',
                          padding: iconStyleConfig.padding + 'px',
                          transform: 'rotate(' + iconStyleConfig.rotate + 'deg)',
                          textShadow: iconStyleConfig.shadow ? '0px 2px 4px rgba(0,0,0,0.2)' : 'none',
                        }"
                      ></span>
                    </div>
                  </template>
                </div>
                <p class="list-text" :style="'color:' + textColor" v-if="navDisplayStyle !== 1">
                  {{ item.info[0].value }}
                </p>
              </div>
              <span class="list-arrow">›</span>
            </div>
          </div>
        </div>

        <!-- 宫格样式和排列样式 -->
        <div class="mobile-page" v-else>
          <div
            class="list_menu"
            :class="[menuStyleConfig === 2 ? 'list-style' : '', menuStyleConfig === 1 ? 'grid-layout' : '']"
            :style="
              menuStyleConfig === 1
                ? {
                    gridTemplateColumns: gridStyle === 0 ? 'repeat(3, 1fr)' : 'repeat(4, 1fr)',
                    columnGap: gridItemStyle.itemPadding + 'px',
                    rowGap: gridItemStyle.itemPaddingTop + 'px',
                  }
                : {}
            "
          >
            <div
              class="item"
              :class="[
                menuStyleConfig === 0 ? (number === 1 ? 'four' : number === 2 ? 'five' : '') : '',
                menuStyleConfig === 1 ? (gridStyle === 0 ? 'grid-three' : 'grid-four') : '',
              ]"
              v-for="(item, index) in vuexMenu"
              :key="index"
            >
              <div
                class="item-content"
                :style="
                  menuStyleConfig === 1
                    ? {
                        backgroundColor: gridItemStyle.itemBgColor,
                        borderRadius: gridItemStyle.itemRadius + 'px',
                        paddingTop: gridItemStyle.itemPaddingTop + 'px',
                        paddingBottom: gridItemStyle.itemPaddingTop + 'px',
                      }
                    : {}
                "
              >
                <div class="img-box-gird" :class="navDisplayStyle == 1 ? 'on' : ''" v-if="navDisplayStyle !== 2">
                  <template v-if="listStyle == 0">
                    <div v-if="item.img">
                      <img
                        :src="item.img"
                        alt=""
                        v-if="item.img"
                        :style="{
                          borderRadius: filletImg
                            ? valListImg[0].val +
                              'px ' +
                              valListImg[1].val +
                              'px ' +
                              valListImg[3].val +
                              'px ' +
                              valListImg[2].val +
                              'px'
                            : filletValImg + 'px',
                        }"
                      />
                      <div
                        class="empty-box on"
                        :style="{
                          borderRadius: filletImg
                            ? valListImg[0].val +
                              'px ' +
                              valListImg[1].val +
                              'px ' +
                              valListImg[3].val +
                              'px ' +
                              valListImg[2].val +
                              'px'
                            : filletValImg + 'px',
                        }"
                        v-else
                      >
                        <img src="@diy/assets/images/shan.png" />
                      </div>
                    </div>
                  </template>
                  <template v-else>
                    <div
                      style="width: 100%; height: 100%; display: flex; align-items: center"
                      :style="{
                        justifyContent:
                          iconStyleConfig.position === 0
                            ? 'flex-start'
                            : iconStyleConfig.position === 1
                            ? 'center'
                            : 'flex-end',
                      }"
                    >
                      <span
                        :class="['mb-iconfont', item.icon]"
                        :style="{
                          color: iconStyleConfig.color,
                          fontSize: iconStyleConfig.size + 'px',
                          lineHeight: iconStyleConfig.size + 'px',
                          padding: iconStyleConfig.padding + 'px',
                          transform: 'rotate(' + iconStyleConfig.rotate + 'deg)',
                          textShadow: iconStyleConfig.shadow ? '0px 2px 4px rgba(0,0,0,0.2)' : 'none',
                        }"
                      ></span>
                    </div>
                  </template>
                </div>
                <p
                  v-if="navDisplayStyle !== 1"
                  :style="{
                    color: textColor,
                    marginTop: listStyle === 0 ? '10px' : '0px',
                  }"
                >
                  {{ item.info[0].value }}
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="dot" v-if="showConfig && menuStyleConfig !== 2">
          <div class="dot-item" :style="{ background: toneConfig ? `${pointerColor}` : `${colorStyle.theme}` }"></div>
          <div
            class="dot-item"
            :style="{ background: toneConfig ? `${pointerBgColor}` : '' }"
            v-for="n in 2"
            :key="n"
          ></div>
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '导航组',
  name: 'menus',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleContent: '内容设置',
  titleRight: '图片样式',
  titlePointer: '指示器设置',
  titleCurrency: '通用样式',
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  menuStyleConfig: {
    title: '展示样式',
    tabVal: 0,
    tabList: [
      {
        name: '排列展示',
      },
      {
        name: '宫格展示',
      },
      {
        name: '列表展示',
      },
    ],
  },
  navDisplayStyle: {
    title: '导航样式',
    tabVal: 0,
    tabList: [
      {
        name: '图文展示',
      },
      {
        name: '纯图片',
      },
      {
        name: '纯文字',
      },
    ],
  },
  number: {
    title: '单行显示',
    tabVal: 1,
    tabList: [
      {
        name: '3个',
      },
      {
        name: '4个',
      },
      {
        name: '5个',
      },
    ],
  },
  gridStyle: {
    title: '宫格样式',
    tabVal: 0,
    tabList: [
      {
        name: '3个/行',
      },
      {
        name: '4个/行',
      },
    ],
  },
  gridItemStyle: {
    title: '宫格项样式',
    itemPadding: 8,
    itemBgColor: '#ffffff',
    itemRadius: 8,
    itemPaddingTop: 0,
  },
  headerConfig: {
    title: '头部设置',
    enable: false,
  },
  headerStyle: {
    title: '头部样式',
    fontSize: 14,
    rightFontSize: 14,
    leftColor: '#333333',
    rightColor: '#333333',
    topPadding: 10,
    bottomPadding: 10,
    leftRightPadding: 12,
    leftWeight: 'normal',
    rightWeight: 'normal',
  },
  leftTopText: {
    title: '左上角文字',
    enable: false,
    text: '左上角文字',
    link: '',
  },
  rightTopText: {
    title: '右上角文字',
    enable: false,
    text: '右上角文字',
    link: '',
  },
  showConfig: {
    title: '展示样式',
    tabVal: 0,
    tabList: [
      {
        name: '固定显示',
      },
      {
        name: '分页滑动',
      },
    ],
  },
  rowsNum: {
    title: '显示行数',
    tabVal: 0,
    tabList: [
      {
        name: '1行',
      },
      {
        name: '2行',
      },
      {
        name: '3行',
      },
      {
        name: '4行',
      },
    ],
  },
  filletImg: {
    title: '图片圆角',
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
  toneConfig: {
    title: '色调',
    tabVal: 0,
    tabList: [
      {
        name: '跟随主题风格',
      },
      {
        name: '自定义',
      },
    ],
  },
  pointerBgColor: {
    title: '常规样式',
    default: [
      {
        item: '#DDDDDD',
      },
    ],
    color: [
      {
        item: '#DDDDDD',
      },
    ],
  },
  pointerColor: {
    title: '选中样式',
    default: [
      {
        item: '#E93323',
      },
    ],
    color: [
      {
        item: '#E93323',
      },
    ],
  },
  bgColor: {
    title: '组件背景',
    default: [
      {
        item: '#fff',
      },
      {
        item: '#fff',
      },
    ],
    color: [
      {
        item: '#fff',
      },
      {
        item: '#fff',
      },
    ],
  },
  textColor: {
    title: '文字颜色',
    default: [
      {
        item: '#333',
      },
    ],
    color: [
      {
        item: '#333',
      },
    ],
  },
  bottomBgColor: {
    title: '底部背景',
    default: [
      {
        item: '#fff',
      },
    ],
    color: [
      {
        item: '#fff',
      },
    ],
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
    tabList: [
      {
        name: '隐藏',
      },
      {
        name: '显示',
      },
    ],
    val: 0,
    colorConfig: {
      title: '阴影颜色',
      default: [
        {
          item: 'rgba(0,0,0,0.1)',
        },
      ],
      color: [
        {
          item: 'rgba(0,0,0,0.1)',
        },
      ],
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
    val: 0,
    min: 0,
    max: 100,
    isAll: true,
    valList: [{ val: 10 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 20 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  // 页面间距
  // mbConfig: {
  //   title: '页面上间距',
  //   val: 0,
  //   min: 0,
  // },
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
  iconStyleConfig: {
    color: {
      title: '图标颜色',
      default: [{ item: '#333' }],
      color: [{ item: '#333' }],
    },
    size: {
      title: '图标大小',
      val: 24,
      min: 12,
      max: 100,
    },
    position: {
      title: '图标位置',
      tabVal: 1,
      tabList: [{ icon: 'iconzuoduiqi' }, { icon: 'iconjuzhongduiqi' }, { icon: 'iconyouduiqi' }],
    },
    // padding: {
    //   title: '内边距',
    //   val: 0,
    //   min: 0,
    //   max: 50,
    // },
    rotate: {
      title: '旋转角度',
      val: 0,
      min: 0,
      max: 360,
    },
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
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_menu',
  cname: '导航组',
  icon: '#iconzujian-daohangzu',
  configName: 'c_home_menu',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'menus',
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

// 默认初始化数据禁止修改

const vuexMenu = ref([]);
const bgColorLeft = ref('');
const bgColorRight = ref('');
const bottomBgColor = ref('');
const number = ref(0);
const rowsNum = ref(0);
const textColor = ref('');
const pointerColor = ref('');
const pointerBgColor = ref('');
const pageData = ref({});
const menuStyleConfig = ref(0);
const navDisplayStyle = ref(0);
const showConfig = ref(0);
const filletImg = ref(0);
const filletValImg = ref(0);
const valListImg = ref([]);
const fillet = ref(0);
const filletVal = ref(0);
const valList = ref([]);
const toneConfig = ref(0);
const gridStyle = ref(0);
const leftTopText = ref({ enable: false, text: '' });
const rightTopText = ref({ enable: false, text: '' });
const headerConfig = ref({ enable: false });
const headerStyle = ref({
  fontSize: 14,
  leftColor: '#333333',
  rightColor: '#333333',
  topPadding: 10,
  bottomPadding: 10,
  leftRightPadding: 12,
});
const gridItemStyle = ref({ itemPadding: 8, itemBgColor: '#ffffff', itemRadius: 8, itemPaddingTop: 0 });
const listStyle = ref(0);
const iconStyleConfig = ref({
  color: '#333',
  size: 24,
  position: 1,
  padding: 0,
  rotate: 0,
  shadow: 0,
});
const configObj = ref(null);

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
    let data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  (nVal, oVal) => {
    let data = mobildConfigStore.defaultArray[props.num];
    setConfig(data);
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    pageData.value = mobildConfigStore.defaultArray[props.num];
    setConfig(pageData.value);
  });
});

// 对象转数组
function objToArr(data) {
  let obj = Object.keys(data);
  let m = obj.map((key) => data[key]);
  return m;
}

function setConfig(data) {
  if (!data) return;
  configObj.value = data;
  for (let key in defaultConfig) {
    if (data[key] == undefined) {
      data[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
  if (data.menuConfig) {
    menuStyleConfig.value = data.menuStyleConfig.tabVal;
    navDisplayStyle.value = data.navDisplayStyle ? data.navDisplayStyle.tabVal : 0;
    showConfig.value = data.showConfig.tabVal;
    filletImg.value = data.filletImg.type;
    filletValImg.value = data.filletImg.val;
    valListImg.value = data.filletImg.valList;
    fillet.value = data.fillet.type;
    filletVal.value = data.fillet.val;
    valList.value = data.fillet.valList;
    toneConfig.value = data.toneConfig.tabVal;
    pointerColor.value = data.pointerColor.color[0].item;
    pointerBgColor.value = data.pointerBgColor.color[0].item;
    bottomBgColor.value =
      data.bottomBgColor && data.bottomBgColor.color && data.bottomBgColor.color[0]
        ? data.bottomBgColor.color[0].item
        : '';
    textColor.value = data.textColor.color[0].item;

    if (!data.paddingConfig) {
      data.paddingConfig = {
        title: '内边距',
        isAll: false,
        val: 0,
        min: 0,
        max: 100,
        valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
      };
      if (data.topConfig) data.paddingConfig.valList[0].val = data.topConfig.val;
      if (data.prConfig) {
        data.paddingConfig.valList[1].val = data.prConfig.val;
        data.paddingConfig.valList[3].val = data.prConfig.val;
      }
      if (data.bottomConfig) data.paddingConfig.valList[2].val = data.bottomConfig.val;
    }

    if (!data.marginConfig) {
      data.marginConfig = {
        title: '外边距',
        isAll: true,
        val: 0,
        min: 0,
        max: 100,
        valList: [{ val: 20 }, { val: 0 }, { val: 0 }, { val: 0 }],
      };
      if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
    }

    bgColorLeft.value = data.bgColor.color[0].item;
    bgColorRight.value = data.bgColor.color[1].item;
    // 新增配置
    gridStyle.value = data.gridStyle ? data.gridStyle.tabVal : 0;
    leftTopText.value = data.leftTopText || { enable: false, text: '左上角文字', link: '' };
    rightTopText.value = data.rightTopText || { enable: false, text: '右上角文字', link: '' };
    headerConfig.value = data.headerConfig || { enable: false };
    headerStyle.value = data.headerStyle || {
      fontSize: 14,
      leftColor: '#333333',
      rightColor: '#333333',
      topPadding: 10,
      bottomPadding: 10,
      leftRightPadding: 12,
    };
    gridItemStyle.value = Object.assign(
      {
        itemPadding: 8,
        itemBgColor: '#ffffff',
        itemRadius: 8,
        itemPaddingTop: 0,
      },
      data.gridItemStyle,
    );
    listStyle.value = data.menuConfig.listStyle || 0;
    let iconConfig = data.iconStyleConfig || {};
    iconStyleConfig.value = {
      color: iconConfig.color ? iconConfig.color.color[0].item : '#333',
      size: iconConfig.size ? iconConfig.size.val : 24,
      position: iconConfig.position ? iconConfig.position.tabVal : 1,
      padding: iconConfig.padding ? iconConfig.padding.val : 0,
      rotate: iconConfig.rotate ? iconConfig.rotate.val : 0,
      shadow: iconConfig.shadow ? iconConfig.shadow.tabVal : 0,
    };

    let lists = objToArr(data.menuConfig.list);
    let list = [];
    lists.forEach((item) => {
      if (item.show) {
        list.push(item);
      }
    });
    number.value = data.number.tabVal;
    rowsNum.value = data.rowsNum.tabVal;
    if (showConfig.value) {
      const columns = menuStyleConfig.value === 1 ? gridStyle.value + 3 : number.value + 3;
      vuexMenu.value = list.splice(0, (rowsNum.value + 1) * columns);
    } else {
      vuexMenu.value = lists.filter((item) => item.show);
    }
  }
}
</script>

<style scoped lang="scss">
.mobile-page {
  display: inline-block;
  width: -webkit-fill-available;
}
.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;

  .left-text {
    flex: 1;
  }

  .right-text {
    flex-shrink: 0;
  }
}

.list_menu {
  display: flex;
  flex-wrap: wrap;

  .item {
    font-size: 12px;
    color: #333;
    text-align: center;
    width: 33.3333%;

    &.four {
      width: 25%;
    }

    &.five {
      width: 20%;
    }

    &.grid-three {
      width: 33.3333%;
    }

    &.grid-four {
      width: 25%;
    }

    .item-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      height: 100%;
      box-sizing: border-box;
      margin-bottom: 12px;
    }

    .img-box {
      width: 45px;
      height: 45px;
      margin: 0 auto 8px auto;

      &.on {
        width: 25px;
        height: 25px;
        margin-bottom: 0;
      }

      img {
        width: 100%;
        height: 100%;
      }
    }
    .img-box-gird {
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;

      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      &.on {
        margin-bottom: 10px;
      }
    }
  }

  .icontupian {
    font-size: 16px;
  }
}

.list_menu.grid-layout {
  display: grid;
  flex-wrap: nowrap;

  .item {
    width: 100%;

    .item-content {
      margin-bottom: 0;
    }
  }
}

// 列表样式
.list-menu {
  padding: 0 12px;

  .list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px solid #f5f5f5;

    &:last-child {
      border-bottom: none;
    }

    .list-item-content {
      display: flex;
      align-items: center;
      flex: 1;
    }

    .list-img-box {
      width: 40px;
      height: 40px;
      margin-right: 12px;
      flex-shrink: 0;
      border-radius: 4px;
      overflow: hidden;

      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    }

    .list-empty-box {
      width: 45px;
      height: 45px;
      margin-right: 12px;
      flex-shrink: 0;
      background: #f3f9ff;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;

      img {
        width: 20px !important;
        height: 16px !important;
      }
    }

    .list-text {
      flex: 1;
      font-size: 14px;
      margin: 0;
    }

    .list-arrow {
      font-size: 18px;
      color: #999;
      margin-left: 8px;
    }
  }
}

.home_menu {
  padding: 0 12px 12px;
  display: flex;
  overflow: hidden;

  .menu-item {
    margin-top: 12px;
    font-size: 11px;
    color: #282828;
    text-align: center;
    margin-right: 27px;

    .img-box {
      width: 50px;
      height: 50px;

      &.on {
        border-radius: 50%;

        img,
        .empty-box {
          border-radius: 50%;
        }
      }
    }

    .box,
    img {
      width: 100%;
      height: 100%;
    }

    .box {
      background: #d8d8d8;
    }

    p {
      margin-top: 5px;
    }
  }

  &.on {
    .menu-item {
      margin-right: 51px;

      &:nth-child(5n) {
        margin-right: 51px;
      }

      &:nth-child(4n) {
        margin-right: 0;
      }
    }
  }

  .icontupian {
    font-size: 16px;
  }
}

.dot {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-bottom: 10px;

  &.number {
    bottom: 15px;
  }

  .num {
    width: 25px;
    height: 18px;
    line-height: 18px;
    background-color: #000;
    color: #fff;
    opacity: 0.3;
    border-radius: 8px;
    font-size: 12px;
    text-align: center;
  }

  .dot-item {
    width: 10px;
    height: 3px;
    background: #dddddd;
    border-radius: 50%;
    margin: 0 3px;
  }
}

.empty-box {
  background: #f3f9ff;

  img {
    width: 26px !important;
    height: 20px !important;
  }
}
</style>
