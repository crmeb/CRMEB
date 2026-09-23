<template>
  <common_wrapper :config="configObj" v-if="bgColor.length > 0">
    <div
      class="home-hot"
      :style="{
        background: boxColor,
      }"
    >
      <div class="hd">
        <p class="txt" :style="{ color: txtColor }">{{ titleTxt }}</p>
        <p class="color-txt" :style="`background: linear-gradient(90deg,${bgColor[0].item},${bgColor[1].item});`">
          {{ msgTxt }}
        </p>
      </div>
      <div class="bd">
        <div class="item" v-for="(item, index) in hotList" :key="index">
          <div class="left">
            <div class="title">{{ item.info[0].value }}</div>
            <div class="des">{{ item.info[1].value }}</div>
            <div class="link">GO！</div>
          </div>
          <div class="img-box">
            <img :src="item.img" alt="" v-if="item.img" />
            <div class="empty-box on" v-else><span class="iconfont icontupian"></span></div>
          </div>
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  name: 'activeParty',
  timestamp: num,
  setUp: {
    tabVal: 0,
  },
  titleConfig: {
    title: '促销标题',
    value: '超值爆款',
    place: '请输入标题',
    max: 10,
  },
  desConfig: {
    title: '促销简介',
    value: '美好生活由此开始',
    place: '请输入简介',
    max: 8,
  },
  menuConfig: {
    title: '最多可添加4个版块，图片建议尺寸140 * 140px；鼠标拖拽左侧圆点可 调整版块顺序',
    maxList: 4,
    list: [
      {
        img: '',
        info: [
          {
            title: '标题',
            value: '今日推荐',
            tips: '选填，不超过4个字',
            max: 4,
          },
          {
            title: '简介',
            value: '店主诚意推荐 品质商品',
            tips: '选填，不超过20个字',
            max: 20,
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
        info: [
          {
            title: '标题',
            value: '热卖推荐',
            tips: '选填，不超过4个字',
            max: 4,
          },
          {
            title: '简介',
            value: '店主诚意推荐 品质商品',
            tips: '选填，不超过20个字',
            max: 20,
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
        info: [
          {
            title: '标题',
            value: '新品推荐',
            tips: '选填，不超过4个字',
            max: 4,
          },
          {
            title: '简介',
            value: '新品上架等 你来拿',
            tips: '选填，不超过20个字',
            max: 20,
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
        info: [
          {
            title: '标题',
            value: '超值推荐',
            tips: '选填，不超过4个字',
            max: 4,
          },
          {
            title: '简介',
            value: '综合评选好 产品',
            tips: '选填，不超过20个字',
            max: 20,
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
  themeColor: {
    title: '主题颜色',
    name: 'themeColor',
    default: [
      {
        item: '#fc3c3e',
      },
    ],
    color: [
      {
        item: '#fc3c3e',
      },
    ],
  },
  bgColor: {
    title: '标签背景颜色',
    name: 'bgColor',
    default: [
      {
        item: '#F62C2C',
      },
      {
        item: '#F96E29',
      },
    ],
    color: [
      {
        item: '#F62C2C',
      },
      {
        item: '#F96E29',
      },
    ],
  },
  boxColor: {
    title: '背景颜色',
    name: 'boxColor',
    default: [
      {
        item: '#ffe5e3',
      },
    ],
    color: [
      {
        item: '#ffe5e3',
      },
    ],
  },
  titleCurrency: '通用样式',
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
      default: [{ item: '#FFFFFF' }, { item: '#FFFFFF' }],
      color: [{ item: '#FFFFFF' }, { item: '#FFFFFF' }],
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
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_hot',
  cname: '活动魔方',
  icon: 'iconhuodongmofang1',
  configName: 'c_home_hot',
  type: -1,
  // -1 doing 0 基础组件 1 营销组件 2工具组件
  defaultName: 'activeParty',
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

// 默认初始化数据禁止修改

const titleTxt = ref('');
const msgTxt = ref('');
const slider = ref('');
const hotList = ref([]);
const txtColor = ref('');
const bgColor = ref([]);
const pageData = ref({});
const configObj = ref({});
const boxColor = ref('');

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
      data[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
  titleTxt.value = data.titleConfig.value;
  msgTxt.value = data.desConfig.value;
  slider.value = data.mbConfig ? data.mbConfig.val : 0;
  hotList.value = data.menuConfig.list;
  txtColor.value = data.themeColor.color[0].item;
  bgColor.value = data.bgColor.color;
  boxColor.value = data.boxColor.color[0].item;
  // this.bottomBgColor = data.bottomBgColor ? data.bottomBgColor.color[0].item : '#F5F5F5';
  if (!configObj.value.paddingConfig) {
    configObj.value['paddingConfig'] = {
      title: '内边距',
      isAll: false,
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!configObj.value.marginConfig) {
    configObj.value['marginConfig'] = {
      title: '外边距',
      isAll: false,
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) {
      configObj.value.marginConfig.valList[0].val = data.mbConfig.val;
    }
  }
  for (let key in defaultConfig) {
    if (configObj.value[key] === undefined) {
      configObj.value[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
}
</script>

<style scoped lang="scss">
.paddingBox {
  padding: 0 10px !important;
}

.home-hot {
  padding: 15px 10px;
  background: #ffe5e3;
  border-radius: 12px;

  .hd {
    display: flex;
    align-items: center;

    .txt {
      margin-right: 10px;
      color: #fc3c3e;
      font-size: 16px;
      font-weight: bold;
    }

    .color-txt {
      width: 110px;
      height: 18px;
      border-radius: 13px 0 13px 0;
      color: #fff;
      text-align: center;
      font-size: 11px;
      box-shadow: 3px 1px 1px 1px rgba(255, 203, 199, 0.8);
    }
  }

  .bd {
    display: flex;
    flex-wrap: wrap;

    .item {
      display: flex;
      width: 158px;
      margin-top: 10px;
      margin-right: 13px;
      padding: 10px;
      background: #fff;
      border-radius: 8px;

      &:nth-child(2n) {
        margin-right: 0;
      }

      .left {
        width: 69px;

        .title {
          font-size: 14px;
        }

        .des {
          font-size: 12px;
          color: #999999;
        }

        .link {
          width: 56px;
          height: 18px;
          padding: 0 10px;
          margin-top: 3px;
          background: linear-gradient(90deg, #4bc4ff, #207eff 100%);
          border-radius: 9px;
          color: #fff;
          font-size: 13px;
        }
      }

      .img-box {
        flex: 1;

        img {
          width: 100%;
          height: 100%;
        }

        .box {
          width: 100%;
          height: 100%;
          background: #d8d8d8;
        }
      }

      &:nth-child(2) .left .link {
        background: linear-gradient(90deg, #ff9043, #ff531d 100%);
      }

      &:nth-child(3) .left .link {
        background: linear-gradient(90deg, #96e187, #48ce2c 100%);
      }

      &:nth-child(4) .left .link {
        background: linear-gradient(90deg, #ffc560, #ff9c00 100%);
      }
    }
  }
}
</style>
