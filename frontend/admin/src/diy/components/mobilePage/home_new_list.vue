<template>
  <common_wrapper :config="configObj">
    <div class="wrapper" v-if="styleConfig == 0">
      <div
        class="item"
        v-for="(item, index) in list"
        :key="index"
        :style="{
          background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
          borderRadius: fillet
            ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
            : filletVal + 'px',
        }"
      >
        <div class="name line1" :style="{ color: nameColor, fontWeight: nameConfig ? '' : 'bold' }">
          {{ item.title || '文章标题文章标题文章标题文章' }}
        </div>
        <div class="pictrue">
          <img
            :src="item.image_input[0]"
            v-if="item.image_input"
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
            <img src="@diy/assets/images/shan.png" />
          </div>
        </div>
        <div class="bottom acea-row" :class="checkboxList.indexOf(0) != -1 ? 'row-between-wrapper' : 'row-right'">
          <div v-if="checkboxList.indexOf(0) != -1" :style="{ color: timeColor }">
            {{ $filters.formatDate(item.add_time || '1621474811') }}
          </div>
          <div class="right">
            <div class="acea-row row-center-wrapper mr5" v-if="checkboxList.indexOf(1) != -1">
              <span class="iconfont iconfangwenliang" :style="{ color: `${browseColor}` }"></span>
              <span class="num" :style="{ color: `${statisticColor}` }">{{ item.visit || 0 }}</span>
            </div>
            <!-- <div class="like acea-row row-center-wrapper" v-if="checkboxList.indexOf(2) != -1">
              <span
                v-if="item.isLike"
                class="iconfont iconic_Like"
                :style="{ color: toneConfig ? `${likeSuccessColor}` : `${colorStyle.theme}` }"
              ></span>
              <span v-else class="iconfont iconic_Like" :style="{ color: `${likeColor}` }"></span>
              <span class="num" :style="{ color: `${statisticColor}` }">{{ item.like || 0 }}</span>
            </div> -->
          </div>
        </div>
      </div>
    </div>
    <div class="list-wrapper" v-else-if="styleConfig == 1">
      <div
        class="item"
        v-for="(item, index) in list"
        :key="index"
        :style="{
          background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
          borderRadius: fillet
            ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
            : filletVal + 'px',
        }"
      >
        <div class="pictrue" v-if="item.image_input">
          <img
            :src="item.image_input[0]"
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
        </div>
        <div
          class="empty-box on"
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
        <div class="info">
          <div class="titleCon">
            <div class="title line2" :style="{ color: nameColor, fontWeight: nameConfig ? '' : 'bold' }">
              {{ item.title || '文章标题文章标题文章标题文章' }}
            </div>
          </div>
          <div class="bottom acea-row" :class="checkboxList.indexOf(0) != -1 ? 'row-between-wrapper' : 'row-right'">
            <div class="time" v-if="checkboxList.indexOf(0) != -1" :style="{ color: timeColor }">
              {{ $filters.formatDate(item.add_time || '1621474811') }}
            </div>
            <div class="right">
              <div class="acea-row row-center-wrapper mr5" v-if="checkboxList.indexOf(1) != -1">
                <span class="iconfont iconfangwenliang" :style="{ color: `${browseColor}` }"></span>
                <span :style="{ color: `${statisticColor}` }">{{ item.visit || 0 }}</span>
              </div>
              <!-- <div class="like acea-row row-center-wrapper" v-if="checkboxList.indexOf(2) != -1">
                <span
                  v-if="item.isLike"
                  class="iconfont iconic_Like"
                  :style="{ color: toneConfig ? `${likeSuccessColor}` : `${colorStyle.theme}` }"
                ></span>
                <span v-else class="iconfont iconic_Like" :style="{ color: `${likeColor}` }"></span>
                <span class="likeNum" :style="{ color: `${statisticColor}` }">{{ item.like || 0 }}</span>
              </div> -->
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="wrapper2" v-else>
      <div
        class="item"
        :class="list.length % 2 == 0 ? 'on2' : 'on1'"
        v-for="(item, index) in list"
        :key="index"
        :style="{
          background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
          borderRadius: fillet
            ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
            : filletVal + 'px',
        }"
      >
        <div class="pictrue">
          <img
            :src="item.image_input[0]"
            v-if="item.image_input"
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
            <img src="@diy/assets/images/shan.png" />
          </div>
        </div>
        <div class="text">
          <div class="name line1" :style="{ color: `${nameColor}`, fontWeight: nameConfig ? '' : 'bold' }">
            {{ item.title || '文章标题文章标题文章标题文章' }}
          </div>
          <div class="bottom acea-row" :class="checkboxList.indexOf(0) != -1 ? 'row-between-wrapper' : 'row-right'">
            <div class="time" v-if="checkboxList.indexOf(0) != -1" :style="{ color: `${timeColor}` }">
              {{ $filters.formatDate(item.add_time || '1621474811') }}
            </div>
            <div class="acea-row row-center-wrapper mr5" v-if="checkboxList.indexOf(1) != -1">
              <span class="iconfont iconfangwenliang" :style="{ color: `${browseColor}` }"></span>
              <span :style="{ color: `${statisticColor}` }">{{ item.visit || 0 }}</span>
            </div>
            <!-- <div
              class="like acea-row row-center-wrapper"
              v-if="checkboxList.indexOf(1) == -1 && checkboxList.indexOf(2) != -1"
            >
              <span
                v-if="item.isLike"
                class="iconfont iconic_Like"
                :style="{ color: toneConfig ? `${likeSuccessColor}` : `${colorStyle.theme}` }"
              ></span>
              <span v-else class="iconfont iconic_Like" :style="{ color: `${likeColor}` }"></span>
              <span class="likeNum" :style="{ color: `${statisticColor}` }">{{ item.like || 0 }}</span>
            </div> -->
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
  cname: '文章列表',
  desc: '文章列表介绍',
  name: 'articleList',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleRight: '列表样式',
  titleArticle: '文章设置',
  titleList: '商品列表',
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
  styleConfig: {
    title: '选择风格',
    tabVal: 1,
    tabList: [
      {
        name: '大图展示',
      },
      {
        name: '单列纵向',
      },
      {
        name: '两列横向',
      },
    ],
  },
  numConfig: {
    val: 3,
    title: '文章数量',
  },
  selectConfig: {
    title: '文章分类',
    activeValue: '',
    list: [
      {
        activeValue: '',
        title: '',
      },
      {
        activeValue: '',
        title: '',
      },
    ],
  },
  goodsList: {
    max: 20,
    list: [],
  },
  selectList: {
    title: '文章列表',
    list: [],
  },
  checkboxList: {
    title: '是否显示',
    type: [0, 1, 2],
    list: [
      {
        id: 0,
        name: '时间日期',
      },
      {
        id: 1,
        name: '浏览量',
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
  nameConfig: {
    title: '文章标题',
    tabVal: 1,
    tabList: [
      {
        name: '加粗',
      },
      {
        name: '常规',
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
  likeSuccessColor: {
    title: '点赞成功',
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
  nameColor: {
    title: '文章标题',
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
  timeColor: {
    title: '时间日期',
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
  browseColor: {
    title: '浏览元素',
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
  statisticColor: {
    title: '数字统计',
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
  bgColor: {
    title: '组件背景',
    isAlpha: false,
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
    val: 10,
    min: 0,
  },
  mbConfig: {
    title: '页面间距',
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
    val: 6,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';
import { categoryList as categoryListApi } from '@diy/api';
import { formatDate as formatDateUtil } from '@/utils/validate';

defineOptions({
  name: 'home_new_list',
  cname: '文章列表',
  icon: '#iconzujian-wenzhangliebiao',
  configName: 'c_new_list',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'articleList',
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

const bgColorLeft = ref('');
const bgColorRight = ref('');
const itemStyle = ref(0);
const prConfig = ref(0);
const styleConfig = ref(0);
const checkboxList = ref([]);
const filletImg = ref(0);
const filletValImg = ref(0);
const valListImg = ref([]);
const fillet = ref(0);
const filletVal = ref(0);
const valList = ref([]);
const nameConfig = ref(0);
const nameColor = ref('');
const timeColor = ref('');
const browseColor = ref('');
// likeColor: '',
const statisticColor = ref('');
const bottomBgColor = ref('');
const configObj = ref({});
const pageData = ref({});
const topConfig = ref(0);
const bottomConfig = ref(0);
const toneConfig = ref(0);
const likeSuccessColor = ref('');
const list = ref([]);

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
    const data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  (nVal, oVal) => {
    const data = mobildConfigStore.defaultArray[props.num];
    setConfig(data);
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    pageData.value = mobildConfigStore.defaultArray[props.num];
    setConfig(pageData.value);
    // categoryList()
  });
});

function categoryList() {
  categoryListApi().then((res) => {
    pageData.value.selectConfig.list = res.data;
    pageData.value.selectConfig.list.map((item) => {
      item.id.toString();
      // return item;
    });
    mobildConfigStore.UPDATEARR({ num: props.num, val: pageData.value });
  });
}

function setConfig(data) {
  if (!data) return;
  for (let key in defaultConfig) {
    if (data[key] == undefined) {
      data[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
  styleConfig.value = data.styleConfig.tabVal;
  checkboxList.value = data.checkboxList.type;
  filletImg.value = data.filletImg.type;
  filletValImg.value = data.filletImg.val;
  valListImg.value = data.filletImg.valList;
  fillet.value = data.fillet.type;
  filletVal.value = data.fillet.val;
  valList.value = data.fillet.valList;
  bgColorLeft.value = data.bgColor.color[0].item;
  bgColorRight.value = data.bgColor.color[1].item;
  nameConfig.value = data.nameConfig.tabVal;
  nameColor.value = data.nameColor.color[0].item;
  timeColor.value = data.timeColor.color[0].item;
  browseColor.value = data.browseColor.color[0].item;
  // this.likeColor = data.likeColor.color[0].item;
  likeSuccessColor.value = data.likeSuccessColor.color[0].item;
  statisticColor.value = data.statisticColor.color[0].item;
  bottomBgColor.value = data.bottomBgColor.color[0].item;
  topConfig.value = data.topConfig.val;
  bottomConfig.value = data.bottomConfig.val;
  toneConfig.value = data.toneConfig.tabVal;
  prConfig.value = data.prConfig.val;

  configObj.value = data;

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
    marginConfig.valList[0].val = data.mbConfig ? data.mbConfig.val : 0;
    configObj.value['marginConfig'] = marginConfig;
  }

  const selectList = data.selectList.list || [];
  if (selectList.length) {
    list.value = selectList;
  } else {
    list.value = data.numConfig.val;
  }
}

function formatDate(time) {
  if (time !== 0) {
    const date = new Date(time * 1000);
    return formatDateUtil(date, 'yyyy-MM-dd hh:mm');
  }
}
</script>

<style scoped lang="scss">
.mobile-page {
  display: inline-block;
  width: -webkit-fill-available;
}
.empty-box {
  background: #f3f9ff;
  img {
    width: 65px !important;
    height: 50px !important;
  }
}
.wrapper2 {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  width: 100%;

  .item {
    width: calc(50% - 5px);
    margin-bottom: 10px;
    box-sizing: border-box;

    &.on1 {
      &:nth-last-child(1) {
        margin-bottom: 0;
      }
    }
    &.on2 {
      &:nth-last-child(1) {
        margin-bottom: 0;
      }
      &:nth-last-child(2) {
        margin-bottom: 0;
      }
    }
    .pictrue {
      width: 100%;
      height: 108px;
      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    }
    .text {
      padding: 8px 10px 4px 10px;
      box-sizing: border-box;
      .name {
        color: #333333;
        font-size: 14px;
      }
      .time {
        font-size: 12px;
        color: #999999;
      }
      .bottom {
        font-size: 12px;
        color: #999999;
        margin-top: 2px;
        .likeNum {
          padding-top: 2px;
        }
        .iconfont {
          font-size: 15px;
          margin-right: 4px;
          padding-top: 2px;
        }
      }
    }
  }
}
.wrapper {
  .item {
    padding: 16px 12px 12px 12px;
    margin-bottom: 10px;
    &:nth-last-child(1) {
      margin-bottom: 0;
    }
    .name {
      font-size: 14px;
      color: #333333;
    }
    .pictrue {
      width: 100%;
      height: 160px;
      margin-top: 12px;
      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    }
    .bottom {
      color: #999999;
      font-size: 12px;
      margin-top: 12px;
      .right {
        display: flex;
        align-items: center;
        .iconfont {
          font-size: 16px;
          margin-right: 4px;
        }
        .iconic_Like {
          font-size: 14px;
        }
        .like {
          margin-left: 16px;
        }
        .num {
          margin-top: 2px;
        }
      }
    }
  }
}
.list-wrapper {
  .item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #fff;
    padding: 8px;
    margin-bottom: 10px;
    &:nth-last-child(1) {
      margin-bottom: 0 !important;
    }
    .info {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      margin-left: 10px;
      flex: 1;
      .titleCon {
        height: 52px;
      }
      .title {
        color: #333;
        font-size: 14px;
      }
      .bottom {
        color: #999999;
        font-size: 12px;
        .right {
          display: flex;
          align-items: center;
          .iconfont {
            font-size: 16px;
            margin-right: 4px;
          }
          .like {
            margin-left: 16px;
            .likeNum {
              padding-top: 2px;
            }
          }
        }
      }
    }
    .empty-box {
      width: 120px;
      height: 76px;
      background: #f3f9ff img {
        width: 50px !important;
        height: 38px !important;
      }
    }
    .pictrue {
      width: 120px;
      height: 76px;
      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    }
  }
}
</style>
