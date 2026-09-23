<template>
  <common_wrapper :config="configObj" v-if="!isHide">
    <div class="product-service">
      <!-- 活动 -->
      <div class="item" v-if="checkList.includes(0)">
        <div class="label" :style="{ color: titleColor }">活动</div>
        <div class="content">
          <div class="tags">
            <span class="tag" :style="tagStyle"
              ><span class="mb-iconfont icon-ic_user1"></span>二人拼团<span
                class="iconfont iconyou"
                :style="{ color: activityColor }"
              ></span
            ></span>
            <span class="tag" :style="tagStyle"
              ><span class="mb-iconfont icon-miaosha1"></span>限时秒杀<span
                class="iconfont iconyou"
                :style="{ color: activityColor }"
              ></span
            ></span>
            <span class="tag" :style="tagStyle"
              ><span class="mb-iconfont icon-ic_sale"></span>参与砍价<span
                class="iconfont iconyou"
                :style="{ color: activityColor }"
              ></span
            ></span>
          </div>
          <span class="iconfont iconyou" :style="{ color: contentColor }"></span>
        </div>
      </div>
      <!-- 选择 -->
      <div class="item" v-if="checkList.includes(1)">
        <div class="label" :style="{ color: titleColor }">选择</div>
        <div class="content">
          <span :style="{ color: contentColor }">黑色,80ml</span>
          <span class="iconfont iconyou" :style="{ color: contentColor }"></span>
        </div>
      </div>
      <!-- 参数 -->
      <div class="item" v-if="checkList.includes(2)">
        <div class="label" :style="{ color: titleColor }">参数</div>
        <div class="content">
          <span :style="{ color: contentColor }">充绒量85% · 聚酯纤维面料</span>
          <span class="iconfont iconyou" :style="{ color: contentColor }"></span>
        </div>
      </div>
      <!-- 服务 -->
      <div class="item" v-if="checkList.includes(3)">
        <div class="label" :style="{ color: titleColor }">服务</div>
        <div class="content">
          <span :style="{ color: contentColor }">正品保障 · 七天无理由退换货 · 退货运费险...</span>
          <span class="iconfont iconyou" :style="{ color: contentColor }"></span>
        </div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '商品服务',
  name: 'productService',
  timestamp: num,
  openService: '开启服务',
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  checkBoxConfig: {
    title: '展示信息',
    type: [0, 1, 2, 3],
    list: [
      { id: 0, name: '活动' },
      { id: 1, name: '选择' },
      { id: 2, name: '参数' },
      { id: 3, name: '服务' },
    ],
  },
  serviceStyleTitle: '服务样式',
  generalStyleTitle: '通用样式',
  titleColor: {
    title: '标题文字',
    default: [{ item: '#999999' }],
    color: [{ item: '#999999' }],
  },
  contentColor: {
    title: '内容文字',
    default: [{ item: '#333333' }],
    color: [{ item: '#333333' }],
  },
  toneConfig: {
    title: '色调',
    tabVal: 0,
    tabList: [{ name: '跟随主题风格' }, { name: '自定义' }],
  },
  activityColor: {
    title: '活动内容',
    default: [{ item: '#E93323' }],
    color: [{ item: '#E93323' }],
  },
  activityBgColor: {
    title: '活动背景',
    default: [{ item: '#FDEBE9' }],
    color: [{ item: '#FDEBE9' }],
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
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'home_product_service',
  cname: '商品服务',
  configName: 'c_product_service',
  icon: '#iconzujian-shangpinfuwu',
  // Need a suitable icon, using placeholder
  type: 3,
  // 0 基础组件 1 营销组件 2工具组件 3 商品组件 4 用户组件
  defaultName: 'productService',
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

const isHide = computed(() => {
  return configObj.value ? configObj.value.isHide : true;
});

const checkList = computed(() => {
  return configObj.value && configObj.value.checkBoxConfig ? configObj.value.checkBoxConfig.type : [];
});

const titleColor = computed(() => {
  return configObj.value && configObj.value.titleColor ? configObj.value.titleColor.color[0].item : '#999999';
});

const contentColor = computed(() => {
  return configObj.value && configObj.value.contentColor ? configObj.value.contentColor.color[0].item : '#333333';
});

const isCustomTone = computed(() => {
  return configObj.value && configObj.value.toneConfig && configObj.value.toneConfig.tabVal === 1;
});

const tagStyle = computed(() => {
  if (isCustomTone.value) {
    return {
      color: configObj.value.activityColor ? configObj.value.activityColor.color[0].item : '#E93323',
      background: configObj.value.activityBgColor ? configObj.value.activityBgColor.color[0].item : '#FDEBE9',
    };
  }
  // Follow theme - assuming standard theme colors or hardcoded for now if theme var not available easily
  return {
    color: '#E93323',
    background: '#FDEBE9',
  };
});

const activityColor = computed(() => {
  return configObj.value && configObj.value.activityColor ? configObj.value.activityColor.color[0].item : '#E93323';
});

const configObj = ref(null);
const pageData = ref({});

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

  // 兼容旧数据：组件背景
  if (!data.componentBgConfig && data.componentBgColor) {
    dataClone.componentBgConfig.colorConfig.color[0].item = data.componentBgColor.color[0].item;
    if (data.componentBgColor.color[1]) {
      dataClone.componentBgConfig.colorConfig.color[1].item = data.componentBgColor.color[1].item;
    }
  }
  configObj.value = dataClone;
}
</script>

<style scoped lang="scss">
.product-service {
  overflow: hidden;
  .item {
    display: flex;
    justify-content: space-between;
    align-items: center; // Align items vertically center
    padding: 12px 0;
    &:last-child {
      border-bottom: none;
    }
    .label {
      width: 40px;
      font-size: 14px;
      color: #999;
      margin-right: 10px;
      flex-shrink: 0; // Prevent label from shrinking
    }
    .content {
      flex: 1;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 14px;
      color: #333;
      overflow: hidden; // Prevent overflow

      // Make sure text truncates if too long
      > span:first-child {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
      }

      .tags {
        display: flex;
        flex-wrap: wrap;
        .tag {
          font-size: 10px;
          padding: 2px 5px;
          border-radius: 10px;
          margin-right: 5px;
          margin-bottom: 0;
          display: flex;
          align-items: center;
          justify-content: center;
          .iconfont {
            font-size: 8px;
            margin-left: 2px;
            line-height: 12px;
          }
          .mb-iconfont {
            font-size: 12px;
            margin-right: 2px;
            line-height: 12px;
          }
        }
      }
      .iconfont {
        font-size: 12px;
        color: #333;
        margin-left: 5px;
      }
    }
  }
}
</style>
