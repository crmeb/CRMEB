<template>
  <common_wrapper :config="configObj">
    <div
      v-if="styleConfig"
      class="userInfor acea-row row-between-wrapper"
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: bgRadius,
      }"
    >
      <div class="left acea-row row-middle">
        <div class="pictrue acea-row row-center-wrapper">
          <img :src="logoConfig" v-if="logoConfig" />
          <div class="empty-box" v-else>
            <img src="@diy/assets/images/shan.png" />
          </div>
        </div>
        <div class="text">
          <div class="name acea-row row-middle">用户名称<img src="@diy/assets/images/vip-diy.png" /></div>
          <div class="acea-row row-middle">
            <div
              class="progress"
              :style="{
                background: toneConfig ? progressBgColor : '#eee',
              }"
            >
              <div
                class="bgReds"
                :style="{
                  background: toneConfig
                    ? `linear-gradient(90deg,${progressLeft} 0%,${progressRight} 100%)`
                    : themeColor,
                }"
              ></div>
            </div>
            <div class="percent">3000/1000000</div>
          </div>
          <!--<div class="phone"><span class="iconfont iconshouji"></span>13000000000</div>-->
        </div>
      </div>
      <div class="right acea-row row-bottom">
        <div class="item" v-if="checkType.slice(0, 3).indexOf(1) != -1">
          <div class="num">20</div>
          <div>积分</div>
        </div>
        <div class="item" v-if="checkType.slice(0, 3).indexOf(2) != -1">
          <div class="num">200</div>
          <div>余额</div>
        </div>
        <div class="item" v-if="checkType.slice(0, 3).indexOf(0) != -1">
          <div class="num">2</div>
          <div>优惠券</div>
        </div>
        <div class="item" v-if="checkType.slice(0, 3).indexOf(4) != -1">
          <div class="num">80</div>
          <div>收藏</div>
        </div>
        <div class="item" v-if="checkType.slice(0, 3).indexOf(5) != -1">
          <div class="num">80</div>
          <div>浏览</div>
        </div>
      </div>
    </div>
    <div
      v-else
      class="userInfor"
      :style="{
        background: `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`,
        borderRadius: bgRadius,
      }"
    >
      <div class="acea-row row-between-wrapper">
        <div class="left acea-row row-middle">
          <div class="pictrue acea-row row-center-wrapper">
            <img :src="logoConfig" v-if="logoConfig" />
            <div class="empty-box" v-else>
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
          <div class="text">
            <div class="name acea-row row-middle">用户名称<img src="@diy/assets/images/vip-diy.png" /></div>
            <div class="acea-row row-middle">
              <div
                class="progress"
                :style="{
                  background: toneConfig ? progressBgColor : '#eee',
                }"
              >
                <div
                  class="bgReds"
                  :style="{
                    background: toneConfig
                      ? `linear-gradient(90deg,${progressLeft} 0%,${progressRight} 100%)`
                      : themeColor,
                  }"
                ></div>
              </div>
              <div class="percent">3000/1000000</div>
            </div>
            <!--<div class="phone"><span class="iconfont iconshouji"></span>13000000000</div>-->
          </div>
        </div>
      </div>
      <div class="list acea-row row-around">
        <div class="item" v-if="checkType.indexOf(1) != -1">
          <div>积分<span class="num">20000</span></div>
        </div>
        <div class="item" v-if="checkType.indexOf(2) != -1">
          <div>余额<span class="num">200</span></div>
        </div>
        <div class="item" v-if="checkType.indexOf(0) != -1">
          <div>优惠券<span class="num">2</span></div>
        </div>
        <div class="item" v-if="checkType.indexOf(4) != -1">
          <div>收藏<span class="num">80</span></div>
        </div>
        <div class="item" v-if="checkType.indexOf(5) != -1">
          <div>浏览<span class="num">80</span></div>
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '用户信息',
  name: 'userInfor',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
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
  titleLeft: '展示设置',
  titleImg: '默认头像',
  titleRight: '进度条',
  titleCurrency: '通用样式',
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
  checkboxInfo: {
    title: '是否展示',
    name: 'checkboxInfo',
    userType: 1,
    type: [1, 2],
    list: [
      {
        id: 1,
        name: '积分',
      },
      {
        id: 2,
        name: '余额',
      },
      {
        id: 4,
        name: '收藏',
      },
      {
        id: 0,
        name: '优惠券',
      },
      {
        id: 5,
        name: '浏览',
      },
    ],
  },
  logoConfig: {
    info: '建议：图片尺寸90px * 90px',
    url: '',
    type: 'code',
    delType: 1,
    name: '上传图片',
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
  progressColor: {
    title: '进度条',
    default: [
      {
        item: '#e93323',
      },
      {
        item: '#ff8933',
      },
    ],
    color: [
      {
        item: '#e93323',
      },
      {
        item: '#ff8933',
      },
    ],
  },
  progressBgColor: {
    title: '进度条背景',
    default: [
      {
        item: '#EEEEEE',
      },
    ],
    color: [
      {
        item: '#EEEEEE',
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
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_userInfor',
  cname: '用户信息',
  configName: 'c_userInfor',
  icon: '#iconzujian-yonghuxinxi',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'userInfor',
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

const configObj = ref(null);
// 默认初始化数据禁止修改

const pageData = ref({});
const styleConfig = ref(0);
const checkType = ref([]);
const logoConfig = ref('');
const progressLeft = ref('');
const progressRight = ref('');
const progressBgColor = ref('');
const bgColorLeft = ref('');
const bgColorRight = ref('');
const bottomBgColor = ref('');
const topConfig = ref(0);
const bottomConfig = ref(0);
const prConfig = ref(0);
const mTop = ref('');
const bgRadius = ref(0);
const toneConfig = ref(0);
const themeColor = ref('');
const paddingConfig = ref({
  title: '内边距',
  val: 0,
  min: 0,
  max: 100,
  isAll: false,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const marginConfig = ref({
  title: '外边距',
  val: 0,
  min: 0,
  max: 100,
  isAll: false,
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
  configObj.value = data;
  for (let key in defaultConfig) {
    if (data[key] === undefined) {
      data[key] = JSON.parse(JSON.stringify(defaultConfig[key]));
    }
  }
  paddingConfig.value = data.paddingConfig || {
    title: '内边距',
    isAll: false,
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  };
  marginConfig.value = data.marginConfig || {
    title: '外边距',
    isAll: false,
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  };
  if (!data.paddingConfig) {
    if (data.topConfig) paddingConfig.value.valList[0].val = data.topConfig.val;
    if (data.bottomConfig) paddingConfig.value.valList[2].val = data.bottomConfig.val;
    if (data.prConfig) {
      paddingConfig.value.valList[1].val = data.prConfig.val;
      paddingConfig.value.valList[3].val = data.prConfig.val;
    }
  }
  if (!data.marginConfig) {
    if (data.mbConfig) marginConfig.value.valList[0].val = data.mbConfig.val;
  }
  styleConfig.value = data.styleConfig.tabVal;
  checkType.value = data.checkboxInfo.type;
  logoConfig.value = data.logoConfig.url;
  toneConfig.value = data.toneConfig.tabVal;
  progressLeft.value = data.progressColor.color[0].item;
  progressRight.value = data.progressColor.color[1].item;
  progressBgColor.value = data.progressBgColor.color[0].item;
  bgColorLeft.value = data.moduleColor.color[0].item;
  bgColorRight.value = data.moduleColor.color[1].item;
  themeColor.value = `linear-gradient(90deg,${props.colorStyle.theme} 0%,${props.colorStyle.gradient} 100%)`;
  // bottomBgColor.value = data.bottomBgColor.color[0].item;
  // topConfig.value = data.topConfig.val;
  // bottomConfig.value = data.bottomConfig.val;
  // prConfig.value = data.prConfig.val;
  // mTop.value = data.mbConfig.val;
  let fillet = data.fillet.type;
  let filletVal = data.fillet.val;
  let valList = data.fillet.valList;
  bgRadius.value = fillet
    ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
    : filletVal + 'px';
}
</script>

<style scoped lang="scss">
.userInfor {
  padding: 14px 10px;
  .list {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #eee;
    .item {
      font-size: 13px;
      color: #999;
      .num {
        font-size: 14px;
        font-weight: 600;
        color: #333333;
        margin-left: 4px;
      }
    }
  }
  .right {
    &.on {
      .item {
        color: #666666;
      }
    }
    .item {
      text-align: center;
      font-weight: 400;
      color: #999;
      font-size: 11px;
      margin-left: 16px;
      .num {
        font-size: 16px;
        margin-bottom: 2px;
        font-weight: 600;
        color: #333333;
      }
    }
    .iconfont {
      font-weight: 400;
      //color: #333333;
      font-size: 15px;
      margin-bottom: 2px;
    }
  }
  .left {
    .pictrue {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      margin-right: 10px;
      img {
        border-radius: 50%;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      .empty-box {
        border-radius: 50%;
        background-color: #f3f9ff;
        border: 1px solid #eeeeee;
        .iconfont {
          font-size: 20px;
        }
        img {
          width: 26px;
          height: 20px;
        }
      }
    }
    .text {
      font-weight: 400;
      color: #333333;
      font-size: 16px;
      .name {
        img {
          width: 38px;
          height: 16px;
          margin-left: 5px;
        }
      }
      .progress {
        overflow: hidden;
        background-color: #eeeeee;
        width: 60px;
        height: 7px;
        border-radius: 3px;
        position: relative;
        .bgReds {
          width: 80%;
          height: 100%;
          transition: width 0.6s ease;
          border-radius: 3px;
        }
      }
      .percent {
        font-size: 10px;
        margin-left: 4px;
      }
      .phone {
        font-weight: 400;
        //color: #333;
        font-size: 10px;
        margin-top: 3px;
        .iconshouji {
          margin-right: 2px;
          font-size: 12px;
        }
      }
    }
  }
}
</style>
