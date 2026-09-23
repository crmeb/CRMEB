<template>
  <common_wrapper :config="configObj" v-if="!isHide">
    <div class="reviews-box">
      <!-- Header -->
      <div class="header">
        <div class="left">
          <span class="title" :style="{ color: titleColor }">评价</span>
          <span class="count" :style="{ color: countColor }" v-if="checkList.includes(0)">(2.3k)</span>
        </div>
        <div class="right" v-if="checkList.includes(1)">
          <span class="rate"><span :style="{ color: rateColor }">99.0% </span>好评率</span>
          <span class="iconfont iconyou" :style="{ color: rateColor }"></span>
        </div>
      </div>

      <!-- Review List -->
      <div class="list" :class="{ 'is-slide': isSlide }">
        <div class="item" v-for="(item, index) in showList" :key="index">
          <div class="user-info">
            <div class="avatar">
              <img :src="item.avatar" alt="" />
            </div>
            <div class="info">
              <div class="name">{{ item.name }}</div>
              <div class="stars">
                <span
                  class="mb-iconfont icon-pingjia"
                  v-for="i in 5"
                  :key="i"
                  :style="{ color: i <= item.star ? starColor : '#eee' }"
                ></span>
              </div>
            </div>
          </div>
          <div class="content">{{ item.content }}</div>
          <div class="images" v-if="item.images && item.images.length">
            <div class="img-box" v-for="(img, imgIndex) in item.images.slice(0, 4)" :key="imgIndex">
              <img :src="img" alt="" />
              <div class="more" v-if="imgIndex === 3 && item.images.length > 4">+{{ item.images.length - 4 }}</div>
            </div>
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
  cname: '商品评价',
  name: 'reviews',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  headTitle: '头部设置',
  checkBoxConfig: {
    title: '展示信息',
    type: [0, 1],
    list: [
      { id: 0, name: '评价数' },
      { id: 1, name: '好评率' },
    ],
  },
  listTitle: '评价列表',
  layoutConfig: {
    title: '选择风格',
    tabVal: 0,
    tabList: [{ name: '单列展示' }, { name: '左右滑动' }],
  },
  numConfig: {
    title: '评价数量',
    val: 2,
    min: 1,
    max: 10,
  },
  // Style Config
  reviewStyleTitle: '评价样式',
  generalStyleTitle: '通用样式',
  titleColor: {
    title: '标题文字',
    default: [{ item: '#333333' }],
    color: [{ item: '#333333' }],
  },
  countColor: {
    title: '评价数量',
    default: [{ item: '#999999' }],
    color: [{ item: '#999999' }],
  },
  toneConfig: {
    title: '色调',
    tabVal: 0, // 0: Follow Theme, 1: Custom
    tabList: [{ name: '跟随主题风格' }, { name: '自定义' }],
  },
  rateColor: {
    title: '好评百分比',
    default: [{ item: '#E93323' }],
    color: [{ item: '#E93323' }],
  },
  starColor: {
    title: '星级',
    default: [{ item: '#E93323' }],
    color: [{ item: '#E93323' }],
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
  bottomBgColor: {
    title: '底部背景',
    default: [{ item: '#F5F5F5' }],
    color: [{ item: '#F5F5F5' }],
  },
  paddingConfig: {
    title: '内边距',
    val: 10,
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
  mbConfig: {
    title: '页面间距',
    val: 10,
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
    val: 8,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
});
</script>

<script setup>
import { ref, watch } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';
import __asset_yonghu_0 from '@/assets/images/yonghu.png';
import __asset_videoBg_1 from '@/assets/images/videoBg.png';

defineOptions({
  name: 'home_reviews',
  cname: '商品评价',
  configName: 'c_reviews',
  icon: '#iconzujian-shangpinpingjia',
  type: 3,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'reviews',
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

const configObj = ref(null);
const pageData = ref({}); // watch(pageData) 引用，缺声明会在 setup 阶段抛 ReferenceError
const isHide = ref(false);
const checkList = ref([]);
const isSlide = ref(false);
const showList = ref([]);
const titleColor = ref('');
const countColor = ref('');
const isCustomTone = ref(false);
const rateColor = ref('');
const starColor = ref('');

const mockList = ref([
  {
    avatar: __asset_yonghu_0, // Use placeholder if available
    name: '用户昵称',
    star: 5,
    content: '小小的很轻便，喜欢的无以复加！柔柔滑滑特别细腻...',
    images: [__asset_videoBg_1, __asset_videoBg_1, __asset_videoBg_1, __asset_videoBg_1, __asset_videoBg_1],
  },
  {
    avatar: __asset_yonghu_0,
    name: '用户昵称',
    star: 4,
    content: '非常不错，物超所值，下次还会再来购买的。',
    images: [__asset_videoBg_1, __asset_videoBg_1],
  },
  {
    avatar: __asset_yonghu_0,
    name: '用户昵称',
    star: 5,
    content: '非常不错，物超所值，下次还会再来购买的。',
    images: [],
  },
]);

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

{
  const data = mobildConfigStore.defaultArray[props.num];
  setConfig(data);
}

function setConfig(data) {
  configObj.value = data ? data : defaultConfig;
  if (!configObj.value) return;
  isHide.value = configObj.value.isHide;
  checkList.value = configObj.value.checkBoxConfig ? configObj.value.checkBoxConfig.type : [];
  isSlide.value = configObj.value.layoutConfig ? configObj.value.layoutConfig.tabVal === 1 : false;
  const num = configObj.value.numConfig ? configObj.value.numConfig.val : 2;
  showList.value = mockList.value.slice(0, num);
  titleColor.value = configObj.value.titleColor ? configObj.value.titleColor.color[0].item : '#333333';
  countColor.value = configObj.value.countColor ? configObj.value.countColor.color[0].item : '#999999';
  isCustomTone.value = configObj.value.toneConfig && configObj.value.toneConfig.tabVal === 1;
  if (isCustomTone.value) {
    rateColor.value = configObj.value.rateColor ? configObj.value.rateColor.color[0].item : '#E93323';
    starColor.value = configObj.value.starColor ? configObj.value.starColor.color[0].item : '#E93323';
  } else {
    rateColor.value = '#E93323';
    starColor.value = '#E93323';
  }
}
</script>

<style scoped lang="scss">
.mobile-page {
  display: inline-block;
  width: -webkit-fill-available;
}
.reviews-box {
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    .left {
      .title {
        font-size: 15px;
        font-weight: bold;
        margin-right: 5px;
      }
      .count {
        font-size: 12px;
      }
    }
    .right {
      display: flex;
      align-items: center;
      .rate {
        font-size: 12px;
      }
      .iconyou {
        font-size: 12px;
        margin-left: 2px;
      }
    }
  }

  .list {
    &.is-slide {
      display: flex;
      overflow-x: auto;
      // Hide scrollbar
      &::-webkit-scrollbar {
        display: none;
      }
      .item {
        flex-shrink: 0;
        width: 85%;
        margin-bottom: 0;
        margin-right: 10px;
        // marginRight handled by inline style
      }
    }

    .item {
      background: #f9f9f9;
      border-radius: 6px;
      padding: 10px;
      margin-bottom: 10px; // Handled by inline style

      .user-info {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        .avatar {
          width: 32px;
          height: 32px;
          border-radius: 50%;
          overflow: hidden;
          margin-right: 8px;
          img {
            width: 100%;
            height: 100%;
            object-fit: cover;
          }
        }
        .info {
          .name {
            font-size: 13px;
            color: #333;
            margin-bottom: 2px;
          }
          .stars {
            display: flex;
            .icon-pingjia {
              font-size: 10px;
              margin-right: 2px;
            }
          }
        }
      }

      .content {
        font-size: 13px;
        color: #333;
        line-height: 1.4;
        margin-bottom: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
      }

      .images {
        display: flex;
        .img-box {
          width: 60px;
          height: 60px;
          border-radius: 4px;
          overflow: hidden;
          margin-right: 6px;
          position: relative;
          img {
            width: 100%;
            height: 100%;
            object-fit: cover;
          }
          .more {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
          }
        }
      }
    }
  }
}
</style>
