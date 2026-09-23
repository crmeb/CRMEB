<template>
  <common_wrapper :config="configObj">
    <div class="mobile-page">
      <div
        class="title"
        :style="{
          background: `linear-gradient(90deg,${titleColorLeft} 0%,${titleColorRight} 100%)`,
          borderRadius: fillet
            ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
            : filletVal + 'px',
        }"
      >
        <div
          class="title-box"
          :class="buttonConfig ? 'on' : ''"
          :style="{
            color: themeColor,
            fontSize: fontSize + 'px',
            fontStyle: txtStyle != 'bold' ? txtStyle : '',
            fontWeight: txtStyle == 'bold' ? txtStyle : '',
            textAlign: txtPosition,
          }"
        >
          {{ titleTxt }}
        </div>
        <div
          v-if="!buttonConfig"
          :style="{
            color: buttonColor,
            fontSize: buttonSize + 'px',
          }"
        >
          {{ buttonTitle }}<span class="iconfont iconjinru"></span>
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '文本标题',
  name: 'titles',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '标题设置',
  titleRight: '文字设置',
  titleCurrency: '通用样式',
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  titleConfig: {
    title: '标题名称',
    value: '标题',
    place: '请输入标题',
    max: 10,
  },
  titleConfigRight: {
    title: '右侧文字',
    value: '更多',
    place: '请输入右侧文字',
    max: 5,
  },
  buttonConfig: {
    title: '右侧按钮',
    tabVal: 0,
    tabList: [
      {
        name: '显示',
      },
      {
        name: '隐藏',
      },
    ],
  },
  linkConfig: {
    title: '链接',
    value: '',
    place: '请输入链接地址',
    max: 100,
    type: 'link',
  },
  themeColor: {
    title: '标题颜色',
    name: 'themeColor',
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
  buttonColor: {
    title: '按钮颜色',
    default: [
      {
        item: '#999999',
      },
    ],
    color: [
      {
        item: '#999999',
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
  buttonText: {
    title: '按钮文字',
    val: 12,
    min: 6,
  },
  textPosition: {
    title: '标题位置',
    tabVal: 0,
    tabList: [
      {
        name: '左对齐',
        style: 'left',
        icon: 'icondoc_left',
      },
      {
        name: '居中对齐',
        style: 'center',
        icon: 'icondoc_center',
      },
      {
        name: '右对齐',
        style: 'right',
        icon: 'icondoc_right',
      },
    ],
  },
  textStyle: {
    title: '标题样式',
    tabVal: 0,
    tabList: [
      {
        name: '正常',
        style: 'normal',
        icon: 'icondoc_general',
      },
      {
        name: '倾斜',
        style: 'italic',
        icon: 'icondoc_skew',
      },
      {
        name: '加粗',
        style: 'bold',
        icon: 'icondoc_bold',
      },
    ],
  },
  fontSize: {
    title: '标题文字',
    val: 16,
    min: 8,
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
    val: 0,
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
  componentBgConfig: {
    title: '背景设置',
    tabVal: 0,
    tabList: [{ name: '颜色' }, { name: '图片' }],
    colorConfig: {
      title: '背景颜色',
      default: [{ item: '#F5F5F5' }, { item: '#F5F5F5' }],
      color: [{ item: '#F5F5F5' }, { item: '#F5F5F5' }],
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
  paddingConfig: {
    title: '内边距',
    val: 0,
    min: 0,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    isAll: false,
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
  bottomBgColor: {
    title: '底部背景',
    default: [
      {
        item: '#F5F5F5',
      },
    ],
    color: [
      {
        item: '#F5F5F5',
      },
    ],
  },
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_title',
  cname: '文本标题',
  icon: '#iconzujian-biaoti',
  configName: 'c_home_title',
  type: 2,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'titles',
});

const props = defineProps({
  index: {
    type: null,
  },
  num: {
    type: null,
  },
});
// 实例级默认配置（timestamp 取当前实例 num）
const defaultConfig = getDefaultConfig(props.num);

const mobildConfigStore = useMobildConfigStore();

const configObj = ref(null);

const titleTxt = ref('');
const link = ref('');
const txtPosition = ref('');
const txtStyle = ref('');
const fontSize = ref(0);
const titleColorLeft = ref('');
const titleColorRight = ref('');
const themeColor = ref('');
const paddingConfig = ref({
  title: '内边距',
  val: 0,
  max: 100,
  isAll: false,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const marginConfig = ref({
  title: '外边距',
  val: 0,
  max: 100,
  isAll: false,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const pageData = ref({});
const bottomBgColor = ref('');
const buttonConfig = ref(0);
const buttonTitle = ref('');
const buttonColor = ref('');
const buttonSize = ref(0);
const fillet = ref(0);
const filletVal = ref(0);
const valList = ref([]);

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
    if (data[key] == undefined) {
      data[key] = defaultConfig[key];
    }
  }
  if (data.id) {
    titleTxt.value = data.titleConfig.value;
    link.value = data.linkConfig.value;
    txtPosition.value = data.textPosition.tabList[data.textPosition.tabVal].style;
    txtStyle.value = data.textStyle.tabList[data.textStyle.tabVal].style;
    themeColor.value = data.themeColor.color[0].item;
    fontSize.value = data.fontSize.val;
    titleColorLeft.value = data.moduleColor.color[0].item;
    titleColorRight.value = data.moduleColor.color[1].item;
    bottomBgColor.value = data.bottomBgColor.color[0].item;
    buttonConfig.value = data.buttonConfig.tabVal;
    buttonTitle.value = data.titleConfigRight.value;
    buttonColor.value = data.buttonColor.color[0].item;
    buttonSize.value = data.buttonText.val;
    fillet.value = data.fillet.type;
    filletVal.value = data.fillet.val;
    valList.value = data.fillet.valList;
    marginConfig.value = data.marginConfig || {
      val: 0,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };

    if (!data.marginConfig) {
      if (data.mbConfig) marginConfig.value.valList[0].val = data.mbConfig.val;
    }
  }
}
</script>

<style scoped lang="scss">
.title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.titleOn {
  border-radius: 10px !important;
}
.title {
  padding: 13px 12px;
  .title-box {
    &.on {
      width: 100%;
    }
  }
}
.iconfont {
  font-size: 14px;
}
</style>
