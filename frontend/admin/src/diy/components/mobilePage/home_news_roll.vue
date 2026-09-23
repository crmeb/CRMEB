<template>
  <common_wrapper :config="configObj" v-if="list.length">
    <div class="news-box">
      <div
        class="item"
        :style="{
          background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
          borderRadius: bgRadius,
        }"
        v-if="styleConfig == 0"
      >
        <div class="img-box" v-if="titleConfig == 0"><img :src="imgUrl" alt="" /></div>
        <div
          class="top"
          v-else
          :style="{
            color: toneConfig ? titleColor : '#fff',
            background: toneConfig
              ? `linear-gradient(90deg,${titleBgColorLeft} 0%,${titleBgColorRight} 100%)`
              : colorStyle.theme,
          }"
        >
          {{ titleTxtConfig || '商城头条' }}
        </div>
        <div
          class="right-box"
          :style="{
            color: newsColor,
          }"
        >
          {{ list[0].chiild[0].val }}
        </div>
        <span
          class="iconfont iconjinru"
          :style="{
            color: bntColor,
          }"
          v-if="!buttonConfig"
        ></span>
      </div>
      <div
        class="list"
        v-else
        :style="{
          background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
          borderRadius: bgRadius,
        }"
      >
        <div class="title acea-row row-between-wrapper">
          <div class="pictrue" v-if="titleConfig == 0">
            <img :src="imgUrl" alt="" />
          </div>
          <div
            class="top"
            v-else
            :style="{
              color: !toneConfig ? titleColor : colorStyle.theme,
            }"
          >
            {{ titleTxtConfig || '商城头条' }}
          </div>
          <div
            v-if="!buttonConfig"
            :style="{
              color: bntColor,
            }"
          >
            {{ textConfig }}<span class="iconfont iconjinru"></span>
          </div>
        </div>
        <div
          class="text line1"
          v-for="(item, index) in list"
          :key="index"
          :style="{
            color: newsColor,
          }"
        >
          <span class="num" :class="index == 0 ? 'on' : index == 1 ? 'on2' : index == 2 ? 'on3' : ''">{{
            index + 1
          }}</span
          >{{ item.chiild[0].val }}
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '新闻公告',
  desc: '新闻公告',
  name: 'news',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleStyle: '公告风格',
  titleButton: '按钮设置',
  titleContent: '公告内容',
  titleRight: '标题样式',
  titleCurrency: '通用样式',
  styleConfig: {
    title: '选择风格',
    tabVal: 0,
    tabList: [
      {
        name: '样式一',
      },
      {
        name: '样式二',
      },
    ],
  },
  titleConfig: {
    title: '标题类型',
    tabVal: 0,
    tabList: [
      {
        name: '图片',
      },
      {
        name: '文字',
      },
    ],
  },
  imgConfig: {
    url: __asset_news2_0,
    type: 'code',
    delType: 1,
    name: '上传图片',
  },
  titleTxtConfig: {
    title: '标题文字',
    value: '商城头条',
    place: '请输入标题文字',
    max: 4,
  },
  rollConfig: {
    title: '滚动方式',
    tabVal: 0,
    tabList: [
      {
        name: '上下滚动',
      },
      {
        name: '左右滚动',
      },
    ],
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
  textConfig: {
    title: '右侧文字',
    value: '更多',
    place: '请输入右侧文字',
    max: 4,
  },
  linkConfig: {
    title: '链接',
    value: '',
    place: '选择跳转链接',
    max: 100,
    type: 'link',
  },
  listConfig: {
    max: 10,
    type: 1,
    list: [
      {
        chiild: [
          {
            title: '标题',
            val: '标题',
            max: 20,
            pla: '输入标题',
          },
          {
            title: '链接',
            val: '',
            max: 200,
            pla: '输入连接',
          },
        ],
        show: true,
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
  titleBgColor: {
    title: '标题背景',
    default: [
      {
        item: '#FCEAE9',
      },
      {
        item: '#FCEAE9',
      },
    ],
    color: [
      {
        item: '#FCEAE9',
      },
      {
        item: '#FCEAE9',
      },
    ],
  },
  titleColor: {
    title: '标题文字',
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
  newsColor: {
    title: '新闻标题',
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
  bntColor: {
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
  bottomBgColor: {
    title: '底部背景',
    default: [
      {
        item: '#f5f5f5',
      },
    ],
    color: [
      {
        item: '#f5f5f5',
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
  componentBgConfig: {
    title: '组件背景',
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
  // logoConfig: {
  //     header: '图标设置',
  //     title: '最多可添加1张图片，建议宽度130 * 36px',
  //     url: __asset_news_1
  // }
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';
import __asset_news2_0 from '@/assets/images/news2.png';
import __asset_news_1 from '@/assets/images/news.png';

defineOptions({
  name: 'home_news_roll',
  cname: '新闻公告',
  configName: 'c_news_roll',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'news',
  // 外面匹配名称
  icon: '#iconzujian-xinwenbobao',
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

const tabVal = ref('');
const rollStyle = ref('');
const txtPosition = ref('');
const pageData = ref({});
const configObj = ref({});
const list = ref([]);
const slider = ref(0);
const styleConfig = ref(0);
const imgUrl = ref('');
const buttonConfig = ref(0);
const textConfig = ref('');
const toneConfig = ref(0);
const newsColor = ref('');
const bntColor = ref('');
const bgColorLeft = ref('');
const bgColorRight = ref('');
const bgRadius = ref(0);
const titleConfig = ref(0);
const titleBgColorLeft = ref('');
const titleBgColorRight = ref('');
const titleColor = ref('');
const titleTxtConfig = ref('');

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
  if (!configObj.value.paddingConfig) {
    configObj.value['paddingConfig'] = {
      title: '内边距',
      isAll: false,
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.topConfig) configObj.value.paddingConfig.valList[0].val = data.topConfig.val;
    if (data.bottomConfig) configObj.value.paddingConfig.valList[2].val = data.bottomConfig.val;
    if (data.prConfig) {
      configObj.value.paddingConfig.valList[1].val = data.prConfig.val;
      configObj.value.paddingConfig.valList[3].val = data.prConfig.val;
    }
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
      configObj.value[key] = defaultConfig[key];
    }
  }
  rollStyle.value = data.rollConfig.tabVal;
  txtPosition.value = data.textConfig.tabVal;
  // slider.value = this.marginConfig.valList[0].val;
  styleConfig.value = data.styleConfig.tabVal;
  imgUrl.value = data.imgConfig.url;
  buttonConfig.value = data.buttonConfig.tabVal;
  textConfig.value = data.textConfig.value;
  let lists = data.listConfig.list;
  let showList = [];
  lists.forEach((item) => {
    if (item.show) {
      showList.push(item);
    }
  });
  list.value = showList;
  toneConfig.value = data.toneConfig.tabVal;
  newsColor.value = data.newsColor.color[0].item;
  bntColor.value = data.bntColor.color[0].item;
  bgColorLeft.value = data.moduleColor.color[0].item;
  bgColorRight.value = data.moduleColor.color[1].item;
  // this.prConfig = this.paddingConfig.valList[1].val;
  // this.topConfig = this.paddingConfig.valList[0].val;
  // this.bottomConfig = this.paddingConfig.valList[2].val;
  titleConfig.value = data.titleConfig.tabVal;
  titleBgColorLeft.value = data.titleBgColor.color[0].item;
  titleBgColorRight.value = data.titleBgColor.color[1].item;
  titleColor.value = data.titleColor.color[0].item;
  titleTxtConfig.value = data.titleTxtConfig.value;
  let fillet = data.fillet.type;
  let filletVal = data.fillet.val;
  let valList = data.fillet.valList;
  bgRadius.value = fillet
    ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
    : filletVal + 'px';
}
</script>

<style scoped lang="scss">
.pageOn {
  border-radius: 6px !important;
}
.news-box {
  display: inline-block;
  width: -webkit-fill-available;
  .list {
    padding: 0 12px 16px 12px;
    .title {
      color: #999999;
      font-size: 12px;
      padding: 16px 0;

      .top {
        font-size: 16px;
      }

      .pictrue {
        height: 18px;
        img {
          height: 100%;
          width: 100%;
        }
      }
      .iconfont {
        font-size: 12px;
      }
    }
    .text {
      color: #282828;
      font-size: 14px;

      .num {
        font-size: 15px;
        margin-right: 7px;
        color: #999999;
        &.on {
          color: #e93323;
        }
        &.on2 {
          color: #ff7300;
        }
        &.on3 {
          color: #ffc300;
        }
      }

      & ~ .text {
        margin-top: 12px;
      }
    }
  }
  .item {
    display: flex;
    align-items: center;
    height: 44px;
    padding: 0 10px;
    font-size: 13px;
    color: #333;
    position: relative;
    .top {
      max-width: 64px;
      height: 20px;
      font-size: 13px;
      text-align: center;
      line-height: 20px;
      padding: 0 6px;
      border-radius: 4px;
      margin-right: 8px;
    }
    .iconfont {
      color: #999;
      font-size: 14px;
    }
    .img-box {
      height: 24px;
      img {
        height: 100%;
        margin-right: 8px;
      }
    }
    .right-box {
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }
}
</style>
