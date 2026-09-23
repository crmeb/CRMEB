<template>
  <common_wrapper v-if="configObj" :config="configObj">
    <div class="pictrue">
      <img
        :src="imgUrl"
        v-if="imgUrl"
        :style="{
          borderRadius: bgRadius,
        }"
      />
      <div
        class="empty-box"
        v-else
        :style="{
          borderRadius: bgRadius,
        }"
      >
        <img src="@diy/assets/images/shan.png" />
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '热区',
  name: 'hotspot',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '内容设置',
  titleRight: '通用样式',
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
  },
  picStyle: {
    url: '',
    list: [],
  },
  bottomBgColor: {
    title: '底部背景',
    name: 'bottomBgColor',
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
  topConfig: {
    title: '上边距',
    val: 0,
    min: 0,
  },
  bottomConfig: {
    title: '下边距',
    val: 0,
    min: 0,
  },
  prConfig: {
    title: '左右边距',
    val: 0,
    min: 0,
  },
  paddingConfig: {
    title: '内边距',
    val: 0,
    min: 0,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  mbConfig: {
    title: '页面上间距',
    val: 0,
    min: 0,
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
  name: 'home_hotspot',
  cname: '热区',
  configName: 'c_hotspot',
  icon: '#iconzujian-requ',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'hotspot',
});

const props = defineProps({
  index: {
    type: null,
    default: -1,
  },
  num: {
    type: null,
  },
});
// 实例级默认配置（timestamp 取当前实例 num）
const defaultConfig = getDefaultConfig(props.num);

const mobildConfigStore = useMobildConfigStore();

// 默认初始化数据禁止修改

const configObj = ref(null);
const confObj = ref({});
const pageData = ref({});
const bgRadius = ref(0);
const imgUrl = ref('');

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
    if (configObj.value[key] === undefined) {
      configObj.value[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
  if (data.mbConfig) {
    imgUrl.value = data.picStyle.url;

    if (!data.paddingConfig) {
      let paddingConfig = {
        title: '内边距',
        val: 0,
        min: 0,
        isAll: false,
        valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
      };
      paddingConfig.valList[0].val = data.topConfig.val;
      paddingConfig.valList[2].val = data.bottomConfig.val;
      paddingConfig.valList[1].val = data.prConfig.val;
      paddingConfig.valList[3].val = data.prConfig.val;
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
      marginConfig.valList[0].val = data.mbConfig.val;
      configObj.value['marginConfig'] = marginConfig;
    }

    let fillet = data.fillet.type;
    let filletVal = data.fillet.val;
    let valList = data.fillet.valList;
    bgRadius.value = fillet
      ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
      : filletVal + 'px';
  }
}
</script>

<style scoped lang="scss">
.mobile-page {
  display: inline-block;
  width: -webkit-fill-available;
}
.pictrue {
  width: 100%;
  height: 100%;
  .empty-box {
    width: 100%;
    height: 375px;
    border-radius: 0;
    background: #f3f9ff;

    img {
      width: 65px;
      height: 50px;
    }
  }
  img {
    width: 100%;
    height: 100%;
  }
}
</style>
