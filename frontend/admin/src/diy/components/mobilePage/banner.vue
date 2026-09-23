<template>
  <common_wrapper :config="configObj">
    <div style="position: relative">
      <div v-if="styleConfig == 0 || styleConfig == 1">
        <div class="banner" v-if="styleConfig == 0">
          <img
            :src="imgSrc[0].img"
            alt=""
            v-if="imgSrc.length && imgSrc[0].img"
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
            class="empty-box"
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
            <img class="shan" src="@diy/assets/images/shan.png" />
          </div>
        </div>
        <div class="banner" v-else>
          <div class="acea-row row-middle">
            <div
              class="empty-box style3"
              :style="{
                borderRadius: filletImg
                  ? '0 ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + '0'
                  : '0 ' + filletValImg + 'px ' + filletValImg + 'px ' + '0',
              }"
            >
              <img
                :src="imgSrc[1].img"
                alt=""
                v-if="imgSrc.length > 1 && imgSrc[1].img"
                :style="{
                  borderRadius: filletImg
                    ? '0 ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + '0'
                    : '0 ' + filletValImg + 'px ' + filletValImg + 'px ' + '0',
                }"
              />
            </div>
            <div
              class="empty-box style3 on"
              :style="{
                marginLeft: imgConfig + 'px',
                marginRight: imgConfig + 'px',
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
              <img
                :src="imgSrc[0].img"
                alt=""
                v-if="imgSrc.length && imgSrc[0].img"
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
              <img class="shan" src="@diy/assets/images/shan.png" v-else />
            </div>
            <div
              class="empty-box style3"
              :style="{
                borderRadius: filletImg
                  ? valListImg[1].val + 'px 0 0 ' + valListImg[3].val + 'px'
                  : filletValImg + 'px 0 0 ' + filletValImg + 'px',
              }"
            >
              <img
                :src="imgSrc[2].img"
                alt=""
                v-if="imgSrc.length > 2 && imgSrc[2].img"
                :style="{
                  borderRadius: filletImg
                    ? valListImg[1].val + 'px 0 0 ' + valListImg[3].val + 'px'
                    : filletValImg + 'px 0 0 ' + filletValImg + 'px',
                }"
              />
            </div>
          </div>
        </div>
      </div>

      <div>
        <!-- <div class="dot number acea-row " v-if="docStyle == 0">
		  <div class="num">2</div>
		  <div class="numCon">8</div>
	  </div> -->
        <div
          class="dot"
          v-if="docStyle == 2"
          :style="{
            paddingLeft:
              styleConfig == 1
                ? paddingConfig.valList[3].val + imgConfig * 2 + 26 + 'px'
                : paddingConfig.valList[3].val + 13 + 'px',
            paddingRight:
              styleConfig == 1
                ? paddingConfig.valList[1].val + imgConfig * 2 + 26 + 'px'
                : paddingConfig.valList[1].val + 13 + 'px',
            paddingBottom: paddingConfig.valList[2].val + 10 + 'px',
            justifyContent: docPosition === 1 ? 'center' : docPosition === 2 ? 'flex-end' : 'flex-start',
          }"
        >
          <div
            class="line-dot"
            :style="{
              background: toneConfig ? dotBgColor : '#ddd',
            }"
          >
            <div
              class="item"
              :style="{
                background: toneConfig ? `${dotColor}` : `${colorStyle.theme}`,
              }"
            ></div>
          </div>
        </div>
        <div
          class="dot"
          :class="docStyle == 1 ? 'on' : docStyle == 3 ? 'on2' : ''"
          v-else
          :style="{
            paddingLeft:
              styleConfig == 1
                ? paddingConfig.valList[3].val + imgConfig * 2 + 26 + 'px'
                : paddingConfig.valList[3].val + 10 + 'px',
            paddingRight:
              styleConfig == 1
                ? paddingConfig.valList[1].val + imgConfig * 2 + 26 + 'px'
                : paddingConfig.valList[1].val + 10 + 'px',
            paddingBottom: paddingConfig.valList[2].val + 10 + 'px',
            justifyContent: docPosition === 1 ? 'center' : docPosition === 2 ? 'flex-end' : 'flex-start',
          }"
        >
          <div
            class="dot-item"
            :class="docStyle == 1 ? 'ons' : ''"
            :style="{ background: toneConfig ? `${dotColor}` : `${colorStyle.theme}` }"
          ></div>
          <div
            class="dot-item"
            :style="{ background: toneConfig ? dotBgColor : '#ddd' }"
            v-for="(item, index) in 2"
            :key="index"
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
  cname: '轮播图',
  name: 'swiperBg',
  // 简介
  desc: '轮播图以动态播放的形式为用户呈现多张图片素材，通过轮播图组件可以让每张图片素材都得到较好的曝光，提高商品、内容转化。',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleContent: '内容设置',
  titleRight: '指示器设置',
  titleImg: '图片设置',
  titleCurrency: '通用样式',
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
    val: 0,
    colorConfig: {
      title: '阴影颜色',
      default: [{ item: '#e5e5e5' }],
      color: [{ item: '#e5e5e5' }],
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
  styleConfig: {
    title: '选择风格',
    tabVal: 1,
    tabList: [
      {
        name: '样式一',
      },
      {
        name: '样式二',
      },
    ],
  },
  // 图片列表
  swiperConfig: {
    maxList: 10,
    list: [
      {
        img: '',
        imgTitle: '图片',
        info: [
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
  docConfig: {
    title: '指示器样式',
    tabVal: 0,
    tabList: [
      {
        name: '样式一',
      },
      {
        name: '样式二',
      },
      {
        name: '样式三',
      },
      {
        name: '样式四',
      },
    ],
  },
  docPosition: {
    title: '指示器位置',
    tabVal: 1,
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
  dotColor: {
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
  dotBgColor: {
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
    val: 10,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  imgConfig: {
    title: '图片间距',
    val: 1,
    min: 0,
  },
  componentBgConfig: {
    title: '组件背景',
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
    valList: [{ val: 10 }, { val: 10 }, { val: 10 }, { val: 10 }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    max: 100,
    isAll: false,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  fillet: {
    title: '背景圆角',
    type: 0,
    list: [
      { val: '全部', icon: 'iconcaozuo-zhengti' },
      { val: '单个', icon: 'iconcaozuo-bianjiao' },
    ],
    valName: '圆角值',
    val: 8,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  txtStyle: {
    title: '指示器位置',
    type: 0,
    list: [
      {
        val: '居左',
        icon: 'icondoc_left',
      },
      {
        val: '居中',
        icon: 'icondoc_center',
      },
      {
        val: '居右',
        icon: 'icondoc_right',
      },
    ],
  },
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'banner',
  // 组件名称
  cname: '轮播图',
  // 标题名称
  icon: '#iconzujian-lunbotu',
  defaultName: 'swiperBg',
  // 外面匹配名称
  configName: 'c_banner',
  // 右侧配置名称
  type: 0,
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
const vuexMenu = ref([]);
const styleConfig = ref(0);
const bottomBgColor = ref('');
const filletImg = ref(0);
const filletValImg = ref(0);
const valListImg = ref([]);
const docPosition = ref(0);
const toneConfig = ref(0);
const dotBgColor = ref('');
const dotColor = ref('');
const imgConfig = ref(0);
const pageData = ref({});
const edge = ref(0);
const imgSrc = ref([]);
const docStyle = ref(0);
const paddingConfig = ref({
  val: 0,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const marginConfig = ref({
  val: 0,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});

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
    if (dataClone[key] == undefined) {
      dataClone[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }

  // 兼容旧数据：底部背景
  if (data.bgColor && !dataClone.bottomBgColor) {
    dataClone.bottomBgColor.color[0].item = data.bgColor.color[0].item;
  }

  // 兼容旧数据：内边距
  if (!data.paddingConfig) {
    if (dataClone.topConfig) dataClone.paddingConfig.valList[0].val = dataClone.topConfig.val;
    if (dataClone.bottomConfig) dataClone.paddingConfig.valList[2].val = dataClone.bottomConfig.val;
    if (dataClone.prConfig) {
      dataClone.paddingConfig.valList[1].val = dataClone.prConfig.val;
      dataClone.paddingConfig.valList[3].val = dataClone.prConfig.val;
    }
  }
  // 兼容旧数据：外边距
  if (!data.marginConfig) {
    if (dataClone.mbConfig) dataClone.marginConfig.valList[0].val = dataClone.mbConfig.val;
  }

  configObj.value = dataClone;

  styleConfig.value = dataClone.styleConfig.tabVal || 0;
  bottomBgColor.value = dataClone.bottomBgColor ? dataClone.bottomBgColor.color[0].item : '';
  filletImg.value = dataClone.filletImg.type;
  filletValImg.value = dataClone.filletImg.val;
  valListImg.value = dataClone.filletImg.valList;
  docPosition.value = dataClone.docPosition.tabVal;
  toneConfig.value = dataClone.toneConfig.tabVal;
  dotColor.value = dataClone.dotColor.color[0].item;
  dotBgColor.value = dataClone.dotBgColor.color[0].item;
  imgConfig.value = dataClone.imgConfig.val;
  imgSrc.value = dataClone.swiperConfig.list;
  docStyle.value = dataClone.docConfig.tabVal;
  paddingConfig.value = dataClone.paddingConfig;
  marginConfig.value = dataClone.marginConfig;
}
</script>

<style scoped lang="scss">
.empty-box {
  height: 170px;
  background-color: #f3f9ff;
  .shan {
    width: 65px !important;
    height: 50px !important;
  }
  &.style3 {
    width: 16px;
    height: 134px;
    border-radius: 0;
    img {
      height: 100%;
    }
  }
  &.on {
    flex: 1;
    height: 170px;
  }
}

.banner {
  width: 100%;
  margin-top: 0;
  &.on {
    margin-top: -96px;
  }

  img {
    width: 100%;
    height: 100%;
    border-radius: 6px;
  }
}

.bg {
  width: 100%;
  height: 96px;
}

.dot {
  position: absolute;
  left: 0;
  bottom: 0;
  width: 100%;
  display: flex;
  align-items: center;

  &.on {
    .dot-item {
      width: 5px;
      height: 5px;
      &.ons {
        width: 9px;
        height: 5px;
        border-radius: 4px;
      }
    }
  }

  &.on2 {
    .dot-item {
      width: 10px;
      height: 3px;
      border-radius: 4px;
    }
  }

  .dot-item {
    width: 6px;
    height: 6px;
    background: #dddddd;
    border-radius: 50%;
    margin: 0 3px;
  }

  .line-dot {
    width: 30px;
    height: 3px;
    border-radius: 4px;
    background-color: #dddddd;
    .item {
      width: 10px;
      height: 100%;
      border-radius: 4px;
      background-color: #e93323;
    }
  }

  &.number {
    width: 40px;
    height: 18px;
    border-radius: 100px;
    background: rgba(0, 0, 0, 0.3);
    color: #fff;
    font-size: 8px;
    .num {
      width: 22px;
      height: 100%;
      border-radius: 20px 0 20px 20px;
      background: rgba(0, 0, 0, 0.1);
      font-size: 10px;
      text-align: center;
      line-height: 18px;
    }
    .numCon {
      width: 18px;
      text-align: center;
      line-height: 18px;
    }
  }
}
</style>
