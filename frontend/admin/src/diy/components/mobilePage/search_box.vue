<template>
  <common_wrapper :config="configObj">
    <div class="search-box" :style="[searchBoxStyle]">
      <div class="search acea-row row-middle" :style="[txtPosition]">
        <img :src="logoUrl" alt="" v-if="logoUrl && styleConfig == 0 && styleTypeConfig == 1" />
        <div
          class="title"
          :style="[txtStyle]"
          v-if="titleConfig && (styleConfig == 1 || (styleConfig == 0 && styleTypeConfig == 0))"
        >
          {{ titleConfig }}
        </div>
        <div v-if="styleConfig === 0" class="box" :style="[searchStyle]">
          <span
            class="iconfont iconsousuo1"
            :style="{
              color: tipColor,
            }"
          ></span>
          <span
            class="hotWords"
            :style="{
              color: hotWordsColor,
            }"
            v-if="hotWords"
            >{{ hotWords }}</span
          >
          <span
            v-else
            :style="{
              color: tipColor,
            }"
            >{{ tipConfig }}</span
          >
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '搜索框',
  name: 'headerSerch',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleSearch: '搜索内容',
  titleHotWords: '搜索热词',
  titleRight: '搜索框',
  titleCurrency: '通用样式',
  titleTxt: '文字设置',
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  styleConfig: {
    title: '选择风格',
    tabVal: 0,
    tabList: [
      {
        name: '搜索',
      },
      {
        name: '标题',
      },
    ],
  },
  styleTypeConfig: {
    title: '样式类型',
    tabVal: 1,
    tabList: [
      {
        name: '标题',
      },
      {
        name: 'logo',
      },
    ],
  },
  logoConfig: {
    info: '建议：144px * 44px',
    url: '',
    type: 'code',
    delType: 1,
    name: 'logo图',
  },
  titleConfig: {
    title: '标题',
    value: '标题',
    place: '请输入标题',
    max: 6,
  },
  linkConfig: {
    title: '链接',
    value: '',
    place: '请选择链接',
    max: 100,
    type: 'link',
  },
  tipConfig: {
    title: '提示文字',
    value: '搜索商品',
    place: '填写内容',
    max: 20,
  },
  hotWords: {
    list: [
      {
        val: '',
      },
    ],
  },
  numConfig: {
    placeholder: '设置搜索热词显示时间',
    title: '显示时间',
    val: 3,
    type: 'words',
  },
  txtFixConfig: {
    title: '文字位置',
    tabVal: 0,
    tabList: [
      {
        name: '左对齐',
      },
      {
        name: '居中对齐',
      },
      {
        name: '右对齐',
      },
    ],
  },
  txtStyleConfig: {
    title: '文字样式',
    tabVal: 0,
    tabList: [
      {
        name: '正常',
        style: 'normal',
      },
      {
        name: '倾斜',
        style: 'italic',
      },
      {
        name: '加粗',
        style: 'bold',
      },
    ],
  },
  txtColor: {
    title: '文字颜色',
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
  txtSize: {
    title: '文字大小',
    val: 15,
    min: 0,
  },
  searchBoxColor: {
    title: '搜索框',
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
  tipColor: {
    title: '提示文字',
    default: [
      {
        item: '#CCCCCC',
      },
    ],
    color: [
      {
        item: '#CCCCCC',
      },
    ],
  },
  hotWordsColor: {
    title: '热词文字',
    default: [
      {
        item: '#888',
      },
    ],
    color: [
      {
        item: '#888',
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
  paddingConfig: {
    title: '内边距',
    val: 0,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
});
</script>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'search_box',
  cname: '搜索框',
  icon: '#iconzujian-sousuokuang',
  configName: 'c_search_box',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'headerSerch',
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

const txtStyle = computed(() => {
  let num = 0;
  if (styleConfig.value == 0 && styleTypeConfig.value != 1) {
    num = 15;
  }
  return {
    color: `${txtColor.value}`,
    fontStyle: `${txtStyleConfig.value != 'bold' ? txtStyleConfig.value : ''}`,
    fontWeight: `${txtStyleConfig.value == 'bold' ? txtStyleConfig.value : ''}`,
    fontSize: `${txtSize.value}px`,
    marginRight: `${num}px`,
  };
});

const txtPosition = computed(() => {
  return {
    justifyContent:
      styleConfig.value != 0 && txtFixConfig.value === 1
        ? 'center'
        : styleConfig.value != 0 && txtFixConfig.value === 2
        ? 'flex-end'
        : 'flex-start',
  };
});

const searchStyle = computed(() => {
  return {
    textAlign: txtFixConfig.value == 0 ? 'left' : txtFixConfig.value == 2 ? 'right' : 'center',
    background: searchBoxColor.value,
  };
});

const searchBoxStyle = computed(() => {
  if (configObj.value && configObj.value.moduleColor) {
    return {
      background: `linear-gradient(90deg, ${configObj.value.moduleColor.color[0].item} 0%, ${configObj.value.moduleColor.color[1].item} 100%)`,
    };
  }
});

// 默认初始化数据禁止修改

const pageData = ref({});
const logoUrl = ref('');
const styleConfig = ref(0);
const titleConfig = ref('');
const searchBoxColor = ref('');
const tipConfig = ref('');
const hotWords = ref('');
const tipColor = ref('');
const hotWordsColor = ref('');
const styleTypeConfig = ref(0);
const fixConfig = ref(0);
const txtFixConfig = ref(0);
const txtColor = ref('');
const txtStyleConfig = ref('');
const txtSize = ref(0);
const paddingConfig = ref(null);
const marginConfig = ref(null);
const borderConfig = ref(null);
const shadowConfig = ref(null);
const componentBgConfig = ref(null);
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

function setConfig(data) {
  if (!data) return;
  let dataClone = JSON.parse(JSON.stringify(data));
  for (let key in defaultConfig) {
    if (dataClone[key] === undefined) {
      dataClone[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
  configObj.value = dataClone;

  paddingConfig.value = dataClone.paddingConfig;
  marginConfig.value = dataClone.marginConfig;
  borderConfig.value = dataClone.borderConfig;
  shadowConfig.value = dataClone.shadowConfig;
  componentBgConfig.value = dataClone.componentBgConfig;

  logoUrl.value = dataClone.logoConfig.url;
  styleConfig.value = dataClone.styleConfig.tabVal;
  styleTypeConfig.value = dataClone.styleTypeConfig.tabVal;
  txtFixConfig.value = dataClone.txtFixConfig.tabVal;
  txtStyleConfig.value = dataClone.txtStyleConfig.tabList[dataClone.txtStyleConfig.tabVal].style;
  txtSize.value = dataClone.txtSize.val;
  txtColor.value = dataClone.txtColor.color[0].item;
  titleConfig.value = dataClone.titleConfig.value;
  searchBoxColor.value = dataClone.searchBoxColor.color[0].item;
  tipConfig.value = dataClone.tipConfig.value;
  hotWords.value = dataClone.hotWords.list.length ? dataClone.hotWords.list[0].val : '';
  tipColor.value = dataClone.tipColor.color[0].item;
  hotWordsColor.value = dataClone.hotWordsColor.color[0].item;
}
</script>

<style scoped lang="scss">
.mobile-page {
  display: inline-block;
  width: -webkit-fill-available;
}
.search-box {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 48px;
  padding: 9px 15px;
  cursor: pointer;
  .search {
    width: 100%;
    &.center {
      justify-content: center;
    }
    &.right {
      justify-content: right;
    }
    .hotWords {
      color: rgba(255, 255, 255, 0.8);
    }
  }
  .title {
    font-size: 15px;
    color: #333;
  }
  .map {
    color: #333;
    font-size: 14px;
    .iconfont {
      font-size: 16px;
    }
    .iconyou {
      font-size: 12px;
      opacity: 0.8;
    }
    .icondingwei {
      margin-right: 3px;
    }
  }
  img {
    width: 76px;
    height: 30px;
    margin-right: 11px;
  }
  .box {
    flex: 1;
    height: 30px;
    line-height: 30px;
    color: #ccc;
    font-size: 14px;
    background: #fff;
    border-radius: 15px;
    padding: 0 16px;

    .iconfont {
      margin-right: 5px;
      margin-top: -3px;
      display: inline-block;
      vertical-align: middle;
    }
  }
}
</style>
