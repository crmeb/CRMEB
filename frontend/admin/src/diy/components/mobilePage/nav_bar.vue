<template>
  <common_wrapper :config="configObj">
    <div
      class="menusCon"
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: bgRadius,
      }"
    >
      <div class="menus">
        <div
          class="item on"
          v-if="styleConfig == 0"
          :style="{
            color: toneConfig ? textColor : '#333',
          }"
        >
          首页<span
            :style="{
              background: toneConfig
                ? `linear-gradient(90deg,${decorateColorLeft} 0%,${decorateColorRight} 100%)`
                : themeColor,
            }"
          ></span>
        </div>
        <div
          class="item on3"
          v-else-if="styleConfig == 1"
          :style="{
            color: toneConfig ? textColor2 : colorStyle.theme,
          }"
        >
          首页<span
            :style="{
              borderColor: toneConfig ? decorateColor : colorStyle.theme,
            }"
          ></span>
        </div>
        <div
          class="item on2"
          v-else
          :style="{
            color: toneConfig ? textColor3 : '#fff',
            background: toneConfig
              ? `linear-gradient(90deg,${decorateColorLeft} 0%,${decorateColorRight} 100%)`
              : themeColor,
          }"
        >
          首页
        </div>
        <div class="item" v-for="(item, index) in navList.slice(0, 20)" :key="index">
          {{ item.text.val }}
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '选项卡',
  desc: '选项卡介绍',
  name: 'tabNav',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleTab: '选项卡设置',
  titleRight: '选项卡样式',
  titleCurrency: '通用样式',
  styleConfig: {
    title: '选择风格',
    tabVal: 0,
    tabList: [
      {
        name: '风格一',
      },
      {
        name: '风格二',
      },
      {
        name: '风格三',
      },
    ],
  },
  stickyConfig: {
    title: '滑动置顶',
    tabVal: 0,
    tabList: [
      {
        name: '启用',
      },
      {
        name: '不启用',
      },
    ],
  },
  tabListConfig: {
    title: '鼠标拖拽版块可调整选项卡顺序',
    max: 100,
    list: [
      {
        text: {
          title: '显示文字',
          val: '首页',
          max: 6,
          pla: '请输入分类名称',
        },
        dataType: {
          title: '数据类型',
          tabVal: 0,
          tabList: [
            {
              name: '微页面',
            },
            {
              name: '商品分类',
            },
          ],
        },
        microPage: {
          name: '',
          id: 0,
        },
        classPage: {
          name: '',
          id: 0,
        },
      },
      {
        text: {
          title: '显示文字',
          val: '标题标题',
          max: 6,
          pla: '请输入分类名称',
        },
        dataType: {
          title: '数据类型',
          tabVal: 0,
          tabList: [
            {
              name: '微页面',
            },
            {
              name: '商品分类',
            },
          ],
        },
        microPage: {
          name: '',
          id: 0,
        },
        classPage: {
          name: '',
          id: 0,
        },
      },
      {
        text: {
          title: '显示文字',
          val: '标题标题',
          max: 6,
          pla: '请输入分类名称',
        },
        dataType: {
          title: '数据类型',
          tabVal: 0,
          tabList: [
            {
              name: '微页面',
            },
            {
              name: '商品分类',
            },
          ],
        },
        microPage: {
          name: '',
          id: 0,
        },
        classPage: {
          name: '',
          id: 0,
        },
      },
      {
        text: {
          title: '显示文字',
          val: '标题标题',
          max: 6,
          pla: '请输入分类名称',
        },
        dataType: {
          title: '数据类型',
          tabVal: 0,
          tabList: [
            {
              name: '微页面',
            },
            {
              name: '商品分类',
            },
          ],
        },
        microPage: {
          name: '',
          id: 0,
        },
        classPage: {
          name: '',
          id: 0,
        },
      },
    ],
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
  decorateColor: {
    title: '装饰元素',
    default: [
      {
        item: '#E93323',
      },
      {
        item: '#FF7931',
      },
    ],
    color: [
      {
        item: '#E93323',
      },
      {
        item: '#FF7931',
      },
    ],
  },
  decorateColor2: {
    title: '装饰元素',
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
  textColor: {
    title: '选中文字',
    default: [
      {
        item: '#333333',
      },
    ],
    color: [
      {
        item: '#333333',
      },
    ],
  },
  textColor2: {
    title: '选中文字',
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
  textColor3: {
    title: '选中文字',
    default: [
      {
        item: '#FFFFFF',
      },
    ],
    color: [
      {
        item: '#FFFFFF',
      },
    ],
  },
  moduleColor: {
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
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  borderConfig: {
    title: '边框设置',
    tabVal: 0,
    tabList: [{ name: '隐藏' }, { name: '显示' }],
    val: 0,
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
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'nav_bar',
  configName: 'c_nav_bar',
  cname: '选项卡',
  icon: '#iconzujian-xuanxiangka1',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'tabNav',
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

const configObj = ref(null);
const pageData = ref({});
const navList = ref([]);
const toneConfig = ref(0);
const decorateColorLeft = ref('');
const decorateColorRight = ref('');
const decorateColor = ref('');
const bgColorLeft = ref('');
const bgColorRight = ref('');
const textColor = ref('');
const textColor2 = ref('');
const textColor3 = ref('');
const cSlider = ref(0);
const bgRadius = ref(0);
const bottomBgColor = ref('');
const topConfig = ref(0);
const bottomConfig = ref(0);
const prConfig = ref(0);
const styleConfig = ref(0);
const themeColor = ref('');

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

function setConfig(data) {
  if (!data) return;
  configObj.value = data;
  for (let key in defaultConfig) {
    if (data[key] === undefined) {
      data[key] = defaultConfig[key];
    }
  }

  if (!data.paddingConfig) {
    let paddingConfig = {
      title: '内边距',
      val: 0,
      min: 0,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.topConfig) paddingConfig.valList[0].val = data.topConfig.val;
    if (data.prConfig) {
      paddingConfig.valList[1].val = data.prConfig.val;
      paddingConfig.valList[3].val = data.prConfig.val;
    }
    if (data.bottomConfig) paddingConfig.valList[2].val = data.bottomConfig.val;
    configObj.value['paddingConfig'] = paddingConfig;
  }

  if (!data.marginConfig) {
    let marginConfig = {
      title: '外边距',
      val: 0,
      min: 0,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) marginConfig.valList[0].val = data.mbConfig.val;
    configObj.value['marginConfig'] = marginConfig;
  }

  navList.value = data.tabListConfig.list;
  toneConfig.value = data.toneConfig.tabVal;
  decorateColorLeft.value = data.decorateColor.color[0].item;
  decorateColorRight.value = data.decorateColor.color[1].item;
  decorateColor.value = data.decorateColor2.color[0].item;
  textColor.value = data.textColor.color[0].item;
  textColor2.value = data.textColor2.color[0].item;
  textColor3.value = data.textColor3.color[0].item;
  bgColorLeft.value = data.moduleColor.color[0].item;
  bgColorRight.value = data.moduleColor.color[1].item;
  themeColor.value = `linear-gradient(90deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
  bottomBgColor.value = data.bottomBgColor.color[0].item;
  styleConfig.value = data.styleConfig.tabVal;
  let fillet = data.fillet.type;
  let filletVal = data.fillet.val;
  let valList = data.fillet.valList;
  bgRadius.value = fillet
    ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
    : filletVal + 'px';
}
</script>

<style scoped lang="scss">
.menusCon {
  overflow: hidden;
}
.menus {
  display: flex;
  align-items: center;
  width: 10000%;
  height: 40px;
  cursor: pointer;
  padding-left: 12px;

  .item {
    position: relative;
    color: #333;
    font-size: 14px;
    margin-right: 28px;
    z-index: 9;

    &.on {
      font-size: 16px;
      font-weight: 600;

      span {
        display: block;
        position: absolute;
        left: 50%;
        bottom: 4px;
        width: 100%;
        height: 4px;
        border-radius: 100px;
        transform: translateX(-50%);
        background: #fff;
        background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
        z-index: -1;
      }
    }

    &.on2 {
      width: 46px;
      height: 24px;
      text-align: center;
      line-height: 24px;
      color: #fff;
      background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
      border-radius: 50px;
    }

    &.on3 {
      font-size: 16px;
      font-weight: 600;
      color: #e93323;

      span {
        position: absolute;
        width: 30px;
        height: 30px;
        border: 3px solid #e93323;
        border-left: 3px solid transparent !important;
        border-top: 3px solid transparent !important;
        border-right: 3px solid transparent !important;
        border-radius: 50%;
        bottom: -4px;
        left: 50%;
        transform: translateX(-50%);
      }
    }
  }
}
</style>
