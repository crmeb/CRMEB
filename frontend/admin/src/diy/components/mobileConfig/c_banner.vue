<template>
  <div class="mobile-config">
    <div v-for="(item, key) in rCom" :key="key">
      <component
        :is="item.components"
        :configObj="configObj"
        ref="childData"
        :configNme="item.configNme"
        :key="key"
        :index="activeIndex"
        :num="item.num"
      ></component>
    </div>
    <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
  </div>
</template>
<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import rightBtn from '@diy/components/rightBtn/index.vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_banner', componentsName: 'home_banner' });
const props = defineProps({
  activeIndex: {
    type: null,
  },
  num: {
    type: null,
  },
  index: {
    type: null,
  },
});
const mobildConfigStore = useMobildConfigStore();
const configObj = ref({});
const rCom = ref([
  // {
  //   components: toolCom.c_title,
  //   configNme: 'titleLeft',
  // },
  {
    components: toolCom.c_card_select,
    configNme: 'styleConfig',
  },
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]);
// 内容设置部分
const contentStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleContent',
  },
  {
    components: toolCom.c_swipers_list,
    configNme: 'swiperConfig',
  },
];
// 样式设置 - 指示器部分
const oneStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleRight',
  },
  {
    components: toolCom.c_radio,
    configNme: 'docConfig',
  },
  {
    components: toolCom.c_radio,
    configNme: 'docPosition',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const twoStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'dotColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'dotBgColor',
  },
];
const threeStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleImg',
  },
  {
    components: toolCom.c_fillet,
    configNme: 'filletImg',
  },
];
const fourStyle = [
  {
    components: toolCom.c_slider,
    configNme: 'imgConfig',
  },
];
const oneCurrencyStyle = [
  {
    components: toolCom.c_common_style,
    configNme: 'commonStyle',
  },
];
const twoCurrencyStyle = [];
const threeCurrencyStyle = [
  {
    components: toolCom.c_fillet,
    configNme: 'fillet',
  },
];
let setUp = 0;
let type = 0;
let type2 = 0;
watch(
  () => props.num,
  (nVal) => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[nVal]));
    configObj.value = patchConfig(value);
  },
);
watch(
  configObj,
  (nVal, oVal) => {
    mobildConfigStore.UPDATEARR({ num: props.num, val: nVal });
  },
  { deep: true },
);
watch(
  () => configObj.value?.setUp?.tabVal,
  (nVal, oVal) => {
    setUp = nVal;
    var arr = [
      // {
      //   components: toolCom.c_title,
      //   configNme: 'titleLeft',
      // },
      {
        components: toolCom.c_card_select,
        configNme: 'styleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (nVal == 0) {
      // 内容设置
      rCom.value = arr.concat(contentStyle);
    } else {
      // 样式设置
      if (type2 == 2) {
        if (type) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle,
            ...fourStyle,
            ...oneCurrencyStyle,
            ...twoCurrencyStyle,
          ];
        } else {
          rCom.value = [...arr, ...oneStyle, ...threeStyle, ...fourStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        }
      } else if (type2 == 1) {
        if (type) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...oneCurrencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...threeStyle, ...oneCurrencyStyle];
        }
      } else {
        if (type) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...threeStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        }
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.styleConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    var arr = [
      // {
      //   components: toolCom.c_title,
      //   configNme: 'titleLeft',
      // },
      {
        components: toolCom.c_card_select,
        configNme: 'styleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (setUp) {
      if (nVal == 2) {
        if (type) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle,
            ...fourStyle,
            ...oneCurrencyStyle,
            ...twoCurrencyStyle,
          ];
        } else {
          rCom.value = [...arr, ...oneStyle, ...threeStyle, ...fourStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        }
      } else if (nVal == 1) {
        if (type) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...oneCurrencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...threeStyle, ...oneCurrencyStyle];
        }
      } else {
        if (type) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...threeStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        }
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type = nVal;
    var arr = [
      // {
      //   components: toolCom.c_title,
      //   configNme: 'titleLeft',
      // },
      {
        components: toolCom.c_card_select,
        configNme: 'styleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (setUp) {
      if (type2 == 2) {
        if (nVal) {
          rCom.value = [
            ...arr,
            ...oneStyle,
            ...twoStyle,
            ...threeStyle,
            ...fourStyle,
            ...oneCurrencyStyle,
            ...twoCurrencyStyle,
          ];
        } else {
          rCom.value = [...arr, ...oneStyle, ...threeStyle, ...fourStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        }
      } else if (type2 == 1) {
        if (nVal) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...threeStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        }
      } else {
        if (nVal) {
          rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        } else {
          rCom.value = [...arr, ...oneStyle, ...threeStyle, ...oneCurrencyStyle, ...twoCurrencyStyle];
        }
      }
    }
  },
  { deep: true },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    configObj.value = patchConfig(value);
  });
});
function patchConfig(data) {
  if (!data) return data;
  // 兼容旧数据：底部背景
  if (data.bgColor && !data.bottomBgColor) {
    data['bottomBgColor'] = {
      title: '底部背景',
      default: [{ item: data.bgColor.color[0].item }],
      color: [{ item: data.bgColor.color[0].item }],
    };
  }
  if (!data.paddingConfig) {
    data['paddingConfig'] = {
      title: '内边距',
      isAll: false,
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.topConfig) data.paddingConfig.valList[0].val = data.topConfig.val;
    if (data.prConfig) {
      data.paddingConfig.valList[1].val = data.prConfig.val;
      data.paddingConfig.valList[3].val = data.prConfig.val;
    }
    if (data.bottomConfig) data.paddingConfig.valList[2].val = data.bottomConfig.val;
  }
  if (!data.marginConfig) {
    data['marginConfig'] = {
      title: '外边距',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
  }
  return data;
}
// 提交
function handleSubmit(name) {
  let obj = {};
  obj.activeIndex = props.activeIndex;
  obj.data = configObj.value;
  mobildConfigStore.UPDATEARR(obj);
}
</script>

<style scoped lang="scss">
.title-tips {
  padding-bottom: 10px;
  font-size: 14px;
  color: #333;
  span {
    margin-right: 14px;
    color: #999;
  }
}
</style>
