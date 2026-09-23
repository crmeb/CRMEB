<template>
  <common_wrapper :config="configObj">
    <div class="live-broadcast" :class="styleConfig == 2 ? 'on' : ''">
      <div class="live-wrapper-c" v-if="styleConfig == 0">
        <div
          class="live-item-a"
          :style="{
            marginBottom: liveConfig + 'px',
          }"
          v-for="(item, index) in live"
          :key="index"
        >
          <div class="img-box">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
            <div class="label bgblue" v-if="item.type == 1">
              <span class="txt">预告</span>
              <span class="msg">08月08日 20:00</span>
            </div>
            <div class="label bggary" v-if="item.type == 0">回放</div>
            <div class="label bgred" v-if="item.type == 2"><span class="iconfont iconzhibozhong"></span>直播中</div>
          </div>
          <div class="info">
            <div class="title" v-if="checkboxInfo.indexOf(0) != -1">这里是直播标题这里是直播标题这里是直播标题...</div>
            <div class="people" v-if="checkboxInfo.indexOf(1) != -1">
              <img src="@/assets/images/ren.png" alt="" />
              <span>主播：王小丫</span>
            </div>
          </div>
        </div>
      </div>
      <div class="live-wrapper-b" v-else-if="styleConfig == 2">
        <div
          class="live-item-b"
          :style="{
            marginRight: liveConfig + 'px',
          }"
          v-for="(item, index) in live"
          :key="index"
        >
          <div class="img-box">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
            <div
              class="label bgblue"
              :style="{
                borderRadius: imgRadius2,
              }"
              v-if="item.type == 1"
            >
              <span
                class="txt"
                :style="{
                  borderRadius: imgRadius2,
                }"
                >预告</span
              >
              <span class="msg">08月08日 20:00</span>
            </div>
            <div
              class="label bggary"
              :style="{
                borderRadius: imgRadius2,
              }"
              v-if="item.type == 0"
            >
              回放
            </div>
            <div
              class="label bgred"
              :style="{
                borderRadius: imgRadius2,
              }"
              v-if="item.type == 2"
            >
              <span class="iconfont iconzhibozhong"></span>直播中
            </div>
          </div>
          <div class="info">
            <div class="title line1" v-if="checkboxInfo.indexOf(0) != -1">直播标题直播标题直播标 题直播标题</div>
            <div class="people" v-if="checkboxInfo.indexOf(1) != -1">
              <img src="@/assets/images/ren.png" alt="" />
              <span>主播：王小丫</span>
            </div>
          </div>
        </div>
      </div>
      <div class="live-wrapper-a" v-else-if="styleConfig == 1">
        <div
          class="live-item-a"
          :style="{
            marginBottom: liveConfig + 'px',
          }"
          v-for="(item, index) in live"
          :key="index"
        >
          <div class="img-box">
            <div
              class="empty-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
            <div class="label bgblue" v-if="item.type == 1">
              <span class="txt">预告</span>
              <span class="msg">08月08日 20:00</span>
            </div>
            <div class="label bggary" v-if="item.type == 0">回放</div>
            <div class="label bgred" v-if="item.type == 2"><span class="iconfont iconzhibozhong"></span>直播中</div>
          </div>
          <div class="info">
            <div class="left">
              <div class="title line2" v-if="checkboxInfo.indexOf(0) != -1">直播标题直播标题直播标 题直播标题</div>
            </div>
            <div class="goods-wrapper">
              <template v-if="item.goods.length > 0">
                <div class="goods-item" v-for="(goods, index) in item.goods" :key="index">
                  <img src="@diy/assets/images/shan.png" alt="" />
                  <span v-if="index < 2">￥{{ goods.price }}</span>
                  <span class="num" v-else>+5</span>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
      <div class="live-wrapper-d" v-else>
        <div v-for="(item, index) in live" :key="index">
          <div class="live-item-d" v-if="index == 0">
            <div
              class="img-box"
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <div class="empty-box">
                <img src="@diy/assets/images/shan.png" />
              </div>
              <div class="label bgred" v-if="item.type == 2"><span class="iconfont iconzhibozhong"></span>直播中</div>
              <div
                class="info"
                v-if="checkboxInfo.indexOf(0) != -1 || checkboxInfo.indexOf(1) != -1"
                :style="{
                  borderRadius: imgRadius3,
                }"
              >
                <div class="title line1" v-if="checkboxInfo.indexOf(0) != -1">
                  这里是直播标题这里是直播标题这里是直播标题...
                </div>
                <div class="people" v-if="checkboxInfo.indexOf(1) != -1">
                  <img src="@/assets/images/ren.png" alt="" />
                  <span>主播：王小丫</span>
                </div>
              </div>
            </div>
          </div>
          <div
            class="live-item-d on"
            v-else
            :style="{
              marginTop: liveConfig + 'px',
            }"
          >
            <div class="name line1">
              <div class="label" v-if="item.type == 1">预告</div>
              <div class="label bggary" v-if="item.type == 0">回放</div>
              <div v-if="checkboxInfo.indexOf(0) != -1">这里是直播标题这里...</div>
            </div>
            <div class="people acea-row row-middle" v-if="checkboxInfo.indexOf(1) != -1">
              <img src="@/assets/images/ren.png" alt="" />
              <div class="acea-row row-middle">
                <span>主播：王小丫</span>
                <div class="line"></div>
                <span>08月28日 18:00</span>
              </div>
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
  cname: '小程序直播',
  name: 'liveBroadcast',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleContent: '内容展示',
  titleRight: '直播样式',
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
      {
        name: '样式三',
      },
      {
        name: '样式四',
      },
    ],
  },
  numberConfig: {
    title: '直播数量',
    val: 3,
    min: 1,
  },
  checkboxInfo: {
    title: '展示信息',
    name: 'checkboxInfo',
    type: [0, 1],
    list: [
      {
        id: 0,
        name: '直播标题',
      },
      {
        id: 1,
        name: '用户名称',
      },
    ],
  },
  liveConfig: {
    title: '直播间距',
    val: 10,
    min: 0,
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
    val: 8,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  zIndexConfig: {
    title: '组件上浮',
    val: 0,
    min: 0,
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
  bottomBgColor: {
    title: '底部背景',
    name: 'bgColor',
    default: [{ item: '#f5f5f5' }],
    color: [{ item: '#f5f5f5' }],
  },
  paddingConfig: {
    title: '内边距',
    val: 10,
    min: 0,
    max: 100,
    valList: [{ val: 10 }, { val: 10 }, { val: 10 }, { val: 10 }],
  },
  marginConfig: {
    title: '外边距',
    val: 0,
    min: 0,
    max: 100,
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
    val: 8,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
});
</script>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'wechat_live',
  cname: '小程序直播',
  configName: 'c_wechat_live',
  type: 1,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'liveBroadcast',
  // 插件归属组件：经 diy/component_status 白名单放行（配合清单 diy.components 声明）
  pluginComponent: true,
  // 外面匹配名称
  icon: '#iconzujian-xiaochengxuzhibo',
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

const live = ref([
  {
    title: '直播中',
    name: 'playBg',
    type: 2,
    color: '',
    icon: 'iconzhibozhong',
    goods: [
      {
        img: '',
        price: '199',
      },
    ],
  },
  {
    title: '预告',
    name: 'notBg',
    type: 1,
    color: '',
    icon: 'iconweikaishi',
    goods: [
      {
        img: '',
        price: '199',
      },
      {
        img: '',
        price: '199',
      },
      {
        img: '',
        price: '199',
      },
    ],
  },
  {
    title: '回放',
    name: 'endBg',
    type: 0,
    color: '',
    icon: 'iconyijieshu',
    goods: [
      {
        img: '',
        price: '199',
      },
      {
        img: '',
        price: '199',
      },
    ],
  },
]);
const confObj = ref({});
const configObj = ref(null);
const pageData = ref({});
const styleConfig = ref(0);
const checkboxInfo = ref([]);
const liveConfig = ref(0);
const imgRadius = ref(0);
const bgColor = ref('');
const bottomBgColor = ref('');
const paddingConfig = ref({
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const marginConfig = ref({
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const mTop = ref(0);
const topConfig = ref(0);
const bottomConfig = ref(0);
const prConfig = ref(0);
const bgRadius = ref('');
const imgRadius2 = ref(0);
const imgRadius3 = ref(0);

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

  if (!data.componentBgConfig && data.moduleColor) {
    dataClone.componentBgConfig.colorConfig.color[0].item = data.moduleColor.color[0].item;
    dataClone.componentBgConfig.colorConfig.color[1].item = data.moduleColor.color[1].item;
  }

  if (!data.paddingConfig) {
    if (dataClone.topConfig) dataClone.paddingConfig.valList[0].val = dataClone.topConfig.val;
    if (dataClone.bottomConfig) dataClone.paddingConfig.valList[2].val = dataClone.bottomConfig.val;
    if (dataClone.prConfig) {
      dataClone.paddingConfig.valList[1].val = dataClone.prConfig.val;
      dataClone.paddingConfig.valList[3].val = dataClone.prConfig.val;
    }
  }
  if (!data.marginConfig) {
    if (dataClone.mbConfig) dataClone.marginConfig.valList[0].val = dataClone.mbConfig.val;
  }

  configObj.value = dataClone;

  styleConfig.value = dataClone.styleConfig.tabVal;
  checkboxInfo.value = dataClone.checkboxInfo.type;
  liveConfig.value = dataClone.liveConfig.val;
  let filletImg = dataClone.filletImg.type;
  let filletValImg = dataClone.filletImg.val;
  let valListImg = dataClone.filletImg.valList;
  imgRadius.value = filletImg
    ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
    : filletValImg + 'px';
  imgRadius2.value = filletImg ? valListImg[0].val + 'px 0 10px 0' : filletValImg + 'px 0 10px 0';
  imgRadius3.value = filletImg
    ? '0 0 ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
    : '0 0 ' + filletValImg + 'px ' + filletValImg + 'px';
}
</script>

<style scoped lang="scss">
.live-broadcast {
  width: 100%;
  &.on {
    padding-right: 0;
  }
}

.live-wrapper-d {
  width: 100%;

  .live-item-d {
    .img-box {
      width: 100%;
      height: 335px;
      position: relative;
      background: #f3f9ff;

      .empty-box {
        width: 100%;
        height: 265px;
        background-color: unset;

        img {
          width: 88px;
          height: 68px;
        }
      }

      .label {
        top: 12px;
        left: 0;
        border-radius: 0 50px 50px 0;
        height: 20px;
      }

      .info {
        position: absolute;
        width: 100%;
        background-color: rgba(0, 0, 0, 0.2);
        left: 0;
        bottom: 0;
        padding: 12px;

        .title {
          color: #ffffff;
          font-size: 14px;
        }

        .people {
          display: flex;
          align-items: center;
          margin-top: 6px;

          img {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 5px;
          }

          span {
            font-size: 12px;
            color: #ffffff;
          }
        }
      }
    }

    &.on {
      width: 100%;
      border-radius: 8px;
      border: 1px solid #eee;
      padding: 10px;
      margin-top: 10px;

      .name {
        color: #333333;
        font-size: 14px;
        display: flex;
        align-items: center;

        .label {
          position: unset;
          width: 32px;
          height: 16px;
          text-align: center;
          line-height: 16px;
          background: linear-gradient(90deg, #208ff2 0%, #3fa6ff 98%);
          border-radius: 3px;
          margin-right: 8px;

          &.bggary {
            background: linear-gradient(90deg, #6d80ac 0%, #889ebd 100%);
          }
        }
      }

      .people {
        font-size: 12px;
        color: #999999;
        display: flex;
        align-items: center;
        margin-top: 6px;

        img {
          width: 20px;
          height: 20px;
          border-radius: 50%;
          margin-right: 6px;
        }

        .line {
          width: 1px;
          height: 11px;
          background: #eee;
          margin: 0 6px;
        }
      }
    }
  }
}

.live-wrapper-c {
  width: 100%;

  .live-item-a {
    position: relative;

    &:nth-last-child(1) {
      margin-bottom: 0 !important;
    }

    .empty-box {
      width: 100%;
      height: 200px;
      background: #f3f9ff;

      img {
        width: 88px;
        height: 68px;
      }
    }

    .img-box {
      .label {
        height: 20px;
        line-height: 20px;
        border-radius: 0 50px 50px 0;
        left: 0;
        top: 12px;

        &.bgred {
          width: 65px;
          background: linear-gradient(90deg, #ff2851 0%, #ff2851 100%);
        }

        &.bgblue {
          .txt {
            border-radius: 0 50px 50px 0;
            line-height: 20px;
          }

          .msg {
            height: 100%;
          }
        }

        &.bggary {
          width: 38px;
        }
      }
    }

    .info {
      .title {
        color: #333333;
        font-size: 14px;
        margin-top: 12px;
      }

      .people {
        display: flex;
        align-items: center;
        font-size: 12px;
        color: #999999;
        margin-top: 6px;

        img {
          width: 20px;
          height: 20px;
          background: #f3f9ff;
          border-radius: 50%;
          margin-right: 6px;
        }
      }
    }
  }
}

.live-wrapper-a {
  .live-item-a {
    display: flex;
    align-items: center;
    position: relative;
    margin-bottom: 10px;
    overflow: hidden;

    &:nth-last-child(1) {
      margin-bottom: 0 !important;
    }

    .img-box {
      position: relative;
      width: 166px;
      height: 118px;
      overflow: hidden;

      .empty-box {
        background-color: #f3f9ff;

        img {
          width: 65px;
          height: 50px;
        }
      }

      .bggary.label {
        width: 34px;
        line-height: 18px;
      }
    }

    .info {
      flex: 1;
      overflow: hidden;
      margin-left: 10px;

      .title {
        color: #333333;
        font-size: 14px;
      }
    }

    .goods-wrapper {
      margin-top: 8px;
      display: flex;

      .goods-item {
        position: relative;
        width: 48px;
        height: 48px;
        border-radius: 4px;
        margin-right: 8px;
        background: #f3f9ff;
        display: flex;
        align-items: center;
        justify-content: center;

        &:nth-child(3n) {
          margin-right: 0;
        }

        img {
          width: 36px;
          height: 28px;
        }

        span {
          position: absolute;
          left: 0;
          bottom: 0;
          color: #fff;
          font-size: 12px;
          width: 100%;
          height: 13px;
          background: rgba(0, 0, 0, 0.3);
          border-radius: 0 0 4px 4px;
          font-size: 11px;
          text-align: center;
          line-height: 14px;

          &.num {
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.35);
            font-size: 14px;
            color: #fefefe;
            border-radius: 4px;
            line-height: 48px;
          }
        }
      }
    }
  }
}

.live-wrapper-b {
  display: flex;
  overflow: hidden;

  .live-item-b {
    width: 140px;
    margin-right: 10px;

    &:nth-last-child(1) {
      margin-bottom: 0;
    }

    .img-box {
      position: relative;
      height: 100px;
      width: 140px;

      .empty-box {
        background: #f3f9ff;

        img {
          width: 65px;
          height: 50px;
        }
      }
    }

    .label {
      top: 0;
      left: 0;

      &.bgred {
        width: 61px;
      }

      &.bgblue {
        width: 128px;

        .txt {
          border-radius: 0;
        }
      }

      &.bggary {
        width: 34px;
      }
    }

    .info {
      width: 100%;

      .title {
        font-size: 14px;
        color: #333;
        margin-top: 6px;
      }

      .people {
        display: flex;
        align-items: center;
        margin-top: 6px;
        font-size: 12px;
        color: #999;

        img {
          width: 20px;
          height: 20px;
          margin-right: 5px;
          border-radius: 50%;
        }
      }
    }
  }
}

.bggary {
  background: linear-gradient(90deg, #6d80ac 0%, #889ebd 100%);
}

.bgred {
  background: linear-gradient(90deg, #ff2851 0%, #ff2851 100%);
}

.label {
  display: flex;
  align-items: center;
  justify-content: center;
  position: absolute;
  left: 6px;
  top: 6px;
  width: 61px;
  height: 18px;
  line-height: 18px;
  border-radius: 50px;
  color: #fff;
  font-size: 11px;

  .iconfont {
    font-size: 12px;
    margin-right: 5px;
  }

  &.bgblue {
    justify-content: inherit;
    width: 128px;
    background: rgba(0, 0, 0, 0.2);
    overflow: hidden;

    .txt {
      width: 34px;
      height: 100%;
      text-align: center;
      margin-right: 7px;
      background: linear-gradient(90deg, #208ff2 0%, #3fa6ff 98%);
      border-radius: 50px;
      line-height: 18px;
    }
  }

  &.bggary {
    width: 54px;
  }
}
</style>
