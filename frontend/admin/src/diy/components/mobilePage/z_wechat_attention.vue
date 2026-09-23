<template>
  <common_wrapper :config="wrapperConfig">
    <div class="flex-box" v-if="configObj">
      <div class="left">
        <div class="img-box">
          <div class="empty-box on">
            <img :src="imgUrl" alt="" v-if="imgUrl" />
            <img src="@diy/assets/images/noPictrue.png" v-else />
          </div>
        </div>
        <div class="name">{{ txt }}</div>
      </div>
      <div class="right">
        <div class="btn" :style="{ borderColor: themeColor, color: themeColor }">关注</div>
        <div class="iconfont iconguanbi5"></div>
      </div>
    </div>
  </common_wrapper>
</template>

<script>
// 模块级具名导出：DIY 编辑器新增组件时经聚合层取默认配置工厂
// （原 Vue2 时代通过组件选项 data().defaultConfig 暴露；timestamp 依赖实例 props，故为工厂函数）
export const getDefaultConfig = (num) => ({
  cname: '关注公众号',
  name: 'follow',
  timestamp: num,
  isHide: false,
  setUp: {
    tabVal: 0,
  },
  titleLeft: '标题设置',
  positionTitle: '位置设置',
  pictrueTitle: '图片设置',
  codeTitle: '关注二维码',
  titleRight: '关注按钮',
  titleCurrency: '通用样式',
  positionConfig: {
    title: '展示位置',
    tabVal: 0,
    tabList: [
      {
        name: '顶部',
      },
      {
        name: '底部',
      },
    ],
  },
  titleConfig: {
    title: '标题名称',
    value: '标题',
    place: '请输入标题',
    max: 10,
  },
  imgConfig: {
    info: '建议：图片尺寸92px * 92px',
    url: '',
    type: 'code',
    name: '上传图片',
  },
  codeConfig: {
    url: '',
    type: 'code',
    name: '上传二维码',
  },
  themeColor: {
    title: '按钮颜色',
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
  bgColor: {
    title: '背景颜色',
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
  paddingConfig: {
    title: '内边距',
    isAll: false,
    val: 0,
    min: 0,
    max: 100,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
  marginConfig: {
    title: '外边距',
    isAll: false,
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
    val: 0,
    min: 0,
    valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
  },
});
</script>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({
  name: 'z_wechat_attention',
  cname: '关注公众号',
  configName: 'c_wechat_attention',
  icon: '#iconzujian-gongzhonghao',
  type: 2,
  // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'follow',
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

const wrapperConfig = computed(() => {
  return {
    ...configObj.value,
  };
});

const configObj = ref(null);
// 默认初始化数据禁止修改

const cSlider = ref('');
const bgColor = ref('');
const confObj = ref({});
const pageData = ref({});
const themeColor = ref('');
const imgUrl = ref('');
const txt = ref('');
const fillet = ref(0);
const filletVal = ref(0);
const valList = ref([]);
const bgRadius = ref(0);

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
  bgColor.value = data.bgColor.color;
  themeColor.value = data.themeColor.color[0].item;
  imgUrl.value = data.imgConfig.url;
  txt.value = data.titleConfig.value;
  fillet.value = data.fillet.type;
  filletVal.value = data.fillet.val;
  valList.value = data.fillet.valList;
  bgRadius.value = fillet.value
    ? valList.value[0].val +
      'px ' +
      valList.value[1].val +
      'px ' +
      valList.value[3].val +
      'px ' +
      valList.value[2].val +
      'px'
    : filletVal.value + 'px';

  if (!configObj.value.paddingConfig) {
    configObj.value['paddingConfig'] = {
      title: '内边距',
      isAll: false,
      val: 0,
      min: 0,
      max: 100,
      valList: [
        { val: 0 },
        { val: data.prConfig ? data.prConfig.val : 0 },
        { val: 0 },
        { val: data.prConfig ? data.prConfig.val : 0 },
      ],
    };
  }
  if (!configObj.value.marginConfig) {
    configObj.value['marginConfig'] = {
      title: '外边距',
      isAll: false,
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: data.mbConfig ? data.mbConfig.val : 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  for (let key in defaultConfig) {
    if (configObj.value[key] === undefined) {
      configObj.value[key] = defaultConfig[key];
    }
  }
}
</script>

<style scoped lang="scss">
.flex-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 10px;
  height: 60px;

  .iconfont {
    color: #999;
    font-size: 15px;
    margin-left: 10px;
  }

  .right {
    display: flex;
    align-items: center;
    margin-left: 10px;
  }

  .left {
    display: flex;
    align-items: center;

    .img-box,
    .empty-box {
      width: 46px;
      height: 46px;
      border-radius: 50%;

      img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
      }
    }

    .name {
      margin-left: 10px;
      font-size: 15px;
      color: #333;
    }
  }

  .btn {
    width: 56px;
    height: 28px;
    border: 1px solid #e93323;
    opacity: 1;
    border-radius: 25px;
    color: #e93323;
    font-size: 12px;
    text-align: center;
    line-height: 28px;
  }

  .iconfont {
    font-size: 20px;
  }
}
</style>
