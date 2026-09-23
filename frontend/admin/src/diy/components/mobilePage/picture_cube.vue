<template>
  <common_wrapper :config="configObj">
    <div class="mobile-page">
      <div
        class="advert"
        :style="
          style === 10
            ? 'height:100%'
            : 'height:' + (379 - paddingConfig.valList[1].val - paddingConfig.valList[3].val) + 'px'
        "
      >
        <div class="advertItem07" v-if="style === 0">
          <div
            class="item"
            v-for="(item, index) in picList"
            :key="index"
            :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
          >
            <img
              class="img"
              :src="item.image"
              v-if="item.image"
              :style="{
                borderRadius: imgRadius,
              }"
            />
            <div
              class="empty-box"
              v-else
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
        </div>
        <div class="advertItem02 acea-row" v-if="style === 1">
          <div
            class="item"
            v-for="(item, index) in picList"
            :key="index"
            :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
          >
            <img
              class="img"
              :src="item.image"
              v-if="item.image"
              :style="{
                borderRadius: imgRadius,
              }"
            />
            <div
              class="empty-box"
              v-else
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
        </div>
        <div class="advertItem02 advertItem03 acea-row" v-if="style === 2">
          <div
            class="item"
            v-for="(item, index) in picList"
            :key="index"
            :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
          >
            <img
              class="img"
              :src="item.image"
              v-if="item.image"
              :style="{
                borderRadius: imgRadius,
              }"
            />
            <div
              class="empty-box"
              v-else
              :style="{
                borderRadius: imgRadius,
              }"
            >
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
        </div>
        <div class="advertItem07 advertItem08" v-if="style === 3">
          <div class="item acea-row">
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img
                class="img"
                :src="picList[0].image"
                v-if="picList[0].image"
                :style="{
                  borderRadius: imgRadius,
                }"
              />
              <div
                class="empty-box"
                v-else
                :style="{
                  borderRadius: imgRadius,
                }"
              >
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img
                class="img"
                :src="picList[1].image"
                v-if="picList[1].image"
                :style="{
                  borderRadius: imgRadius,
                }"
              />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
          </div>
          <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
        </div>
        <div class="advertItem07 advertItem08" v-if="style === 4">
          <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
          <div class="item acea-row">
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
          </div>
        </div>
        <div class="advertItem04 acea-row" v-if="style === 5">
          <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
          <div class="item">
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
          </div>
        </div>
        <div class="advertItem04 acea-row" v-if="style === 6">
          <div class="item">
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
          </div>
          <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
        </div>
        <div class="advertItem06 acea-row" v-if="style === 7">
          <div
            class="item"
            v-for="(item, index) in picList"
            :key="index"
            :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
          >
            <img class="img" :src="item.image" v-if="item.image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
        </div>
        <div class="advertItem07 advertItem08" v-if="style === 8">
          <div class="item acea-row">
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
          </div>
          <div class="items acea-row">
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[3].image" v-if="picList[3].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[4].image" v-if="picList[4].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
          </div>
        </div>
        <div class="advertItem04 acea-row" v-if="style === 9">
          <div class="item" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
            <img class="img" :src="picList[0].image" v-if="picList[0].image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
          <div class="item">
            <div class="pic" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
              <img class="img" :src="picList[1].image" v-if="picList[1].image" :style="{ borderRadius: imgRadius }" />
              <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                <img src="@diy/assets/images/shan.png" />
              </div>
            </div>
            <div class="pic acea-row">
              <div class="picItem" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
                <img class="img" :src="picList[2].image" v-if="picList[2].image" :style="{ borderRadius: imgRadius }" />
                <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                  <img src="@diy/assets/images/shan.png" />
                </div>
              </div>
              <div class="picItem" :style="{ border: imgConfig + 'px solid ' + bottomBgColor }">
                <img class="img" :src="picList[3].image" v-if="picList[3].image" :style="{ borderRadius: imgRadius }" />
                <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
                  <img src="@diy/assets/images/shan.png" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <template v-if="style === 10">
          <div
            class="advertItem01 acea-row"
            v-for="(item, index) in picList"
            :key="index"
            :style="{ border: imgConfig + 'px solid ' + bottomBgColor }"
          >
            <img class="img" :src="item.image" v-if="item.image" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
        </template>
        <template v-if="style === 11">
          <div
            class="advertItem11 acea-row"
            v-for="(item, index) in docPicList"
            :key="index"
            :style="{
              border: imgConfig + 'px solid ' + bottomBgColor,
              width: (item.doc.w / 375) * 100 + '%',
              height: (item.doc.h / 375) * 100 + '%',
              left: (item.doc.startX / 375) * 100 + '%',
              top: (item.doc.startY / 375) * 100 + '%',
            }"
          >
            <img class="img" :src="item.img" v-if="item.img" :style="{ borderRadius: imgRadius }" />
            <div class="empty-box" v-else :style="{ borderRadius: imgRadius }">
              <img src="@diy/assets/images/shan.png" />
            </div>
          </div>
        </template>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '图片魔方',
  name: 'pictureCube',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '展示设置',
  titleShow: '展示设置',
  titleContent: '内容设置',
  titleRight: '图片魔方',
  titleCurrency: '通用样式',
  styleConfig: {
    title: '选择风格',
    tabVal: 0,
    count: 2,
    type: 'pictureCube',
  },
  picStyle: {
    tabVal: 0,
    picList: [],
    docPicList: [],
  },

  menuConfig: {
    title: '',
    maxList: 1,
    isCube: 1,
    listStyle: -1,
    list: [
      {
        img: '',
        info: [
          {
            title: '链接',
            tips: '请输入链接',
            value: '',
            max: 100,
          },
        ],
      },
    ],
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
    val: 0, // 0: Hide, 1: Show
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
      default: [{ item: '#fff' }],
      color: [{ item: '#fff' }],
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
  imgConfig: {
    title: '图片间距',
    val: 0,
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
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
  name: 'picture_cube',
  cname: '图片魔方',
  configName: 'c_picture_cube',
  icon: '#iconzujian-tupianmofang',
  type: 0,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'pictureCube',
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

const pageData = ref({});
const style = ref(0);
const imgConfig = ref(0);
const picList = ref([]);
const configObj = ref(null);
const bottomBgColor = ref('');
const paddingConfig = ref({
  title: '内边距',
  val: 0,
  min: 0,
  max: 100,
  isAll: false,
  valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
});
const imgRadius = ref(0);
const docPicList = ref([]);

watch(
  pageData,
  (nVal) => {
    setConfig(nVal);
  },
  { deep: true },
);
watch(
  () => props.num,
  (nVal) => {
    let data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => mobildConfigStore.defaultArray,
  () => {
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
  configObj.value = dataClone;

  let isLegacyPadding = !dataClone.paddingConfig;
  let isLegacyMargin = !dataClone.marginConfig;

  if (isLegacyPadding) {
    dataClone.paddingConfig = {
      title: '边距设置',
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (dataClone.topConfig) dataClone.paddingConfig.valList[0].val = dataClone.topConfig.val;
    if (dataClone.bottomConfig) dataClone.paddingConfig.valList[2].val = dataClone.bottomConfig.val;
    if (dataClone.prConfig) {
      dataClone.paddingConfig.valList[1].val = dataClone.prConfig.val;
      dataClone.paddingConfig.valList[3].val = dataClone.prConfig.val;
    }
  }
  if (isLegacyMargin) {
    dataClone.marginConfig = {
      title: '间距设置',
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (dataClone.mbConfig) dataClone.marginConfig.valList[0].val = dataClone.mbConfig.val;
  }
  paddingConfig.value = dataClone.paddingConfig;

  style.value = dataClone.styleConfig.tabVal;
  bottomBgColor.value = dataClone.bottomBgColor.color[0].item;
  let filletImg = dataClone.filletImg.type;
  let filletValImg = dataClone.filletImg.val;
  let valListImg = dataClone.filletImg.valList;
  imgRadius.value = filletImg
    ? valListImg[0].val + 'px ' + valListImg[1].val + 'px ' + valListImg[3].val + 'px ' + valListImg[2].val + 'px'
    : filletValImg + 'px';
  imgConfig.value = dataClone.imgConfig.val;
  if (dataClone.styleConfig.tabVal !== 11) {
    if (!dataClone.picStyle.picList.length) {
      picList.value = [
        {
          image: '',
          link: '',
        },
      ];
    } else {
      picList.value = dataClone.picStyle.picList;
    }
  } else {
    dataClone.picStyle.docPicList.map((e, i) => {
      dataClone.picStyle.docPicList[i].img = dataClone.picStyle.picList[i].image;
      dataClone.picStyle.docPicList[i].link = dataClone.picStyle.picList[i].link;
    });
    docPicList.value = dataClone.picStyle.docPicList;
  }
}
</script>
<style scoped lang="scss">
.empty-box {
  background: #f3f9ff;
  img {
    width: 65px;
    height: 50px;
  }
}

.img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.advertItem07 {
  width: 100%;
  height: 100%;

  .item {
    width: 100%;
    height: 50%;
  }

  .items {
    width: 100%;
    height: 50%;

    .pic {
      width: 33.3333%;
      height: 100%;
    }
  }
}

.advertItem08 {
  .item {
    .pic {
      width: 50%;
      height: 100%;
    }
  }
}

.mobile-page {
  display: inline-block;
  width: -webkit-fill-available;
  .advert {
    position: relative;

    .advertItem11 {
      position: absolute;
    }

    .advertItem01 {
      width: 100%;
      height: 100%;

      .empty-box {
        width: 100%;
        height: 375px;

        .icontupian {
          font-size: 50px;
          color: #999;
        }
      }
    }

    .advertItem02 {
      width: 100%;
      height: 100%;

      .item {
        width: 50%;
        height: 100%;

        .empty-box {
          width: 100%;
          height: 100%;
        }
      }
    }

    .advertItem03 {
      .item {
        width: 33.3333%;
        height: 100%;

        .empty-box {
          width: 100%;
          height: 100%;
        }
      }
    }

    .advertItem04 {
      width: 100%;
      height: 100%;

      .item {
        width: 50%;
        height: 100%;

        .empty-box {
          width: 100%;
          height: 100%;
        }

        .pic {
          width: 100%;
          height: 50%;

          .picItem {
            width: 50%;
            height: 100%;
          }
        }
      }
    }

    .advertItem05 {
      .item {
        width: 25%;

        .empty-box {
          width: 100%;
          height: 94.75px;
        }
      }
    }

    .advertItem06 {
      width: 100%;
      height: 100%;

      .item {
        width: 50%;
        height: 50%;

        .empty-box {
          width: 100%;
          height: 100%;
        }
      }
    }
  }
}
</style>
