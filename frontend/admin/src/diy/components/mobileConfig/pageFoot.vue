<template>
  <div class="mobile-config">
    <div v-for="(item, key) in rCom" :key="key">
      <component :is="item.components" ref="childData" :configObj="configObj" :configNme="item.configNme"></component>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'pageFoot', cname: '底部导航' });

const mobildConfigStore = useMobildConfigStore();

const hotIndex = ref(1);
const configObj = ref({}); // 配置对象
const rCom = ref([
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]); // 当前页面组件
const oneStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleRight',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const twoStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'activeTxtColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'txtColor',
  },
];
const currencyTitle = [
  {
    components: toolCom.c_title,
    configNme: 'titleCurrency',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'bgColor',
  },
];
const currencyTitle2 = [
  {
    components: toolCom.c_title,
    configNme: 'titleCurrency',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'bgColor2',
  },
];
const currencyStyle = [
  {
    components: toolCom.c_slider,
    configNme: 'topConfig',
  },
  {
    components: toolCom.c_slider,
    configNme: 'bottomConfig',
  },
];
const currencyStyle2 = [
  {
    components: toolCom.c_slider,
    configNme: 'prConfig',
  },
  {
    components: toolCom.c_slider,
    configNme: 'mbConfig',
  },
  {
    components: toolCom.c_fillet,
    configNme: 'fillet',
  },
];
const setUp = ref(0);
const type = ref(0);
const type2 = ref(0);
const type3 = ref(0);

onMounted(() => {
  configObj.value = patchConfig(mobildConfigStore.pageFooter);
});

watch(
  configObj,
  (nVal) => {
    mobildConfigStore.footPageUpdata(nVal);
  },
  { deep: true },
);
// 原字符串路径 watch；configObj 挂载后才填充，用可选链避免空对象取值报错
watch(
  () => configObj.value.setUp?.tabVal,
  (nVal) => {
    if (nVal === undefined) return;
    setUp.value = nVal;
    const arr = [rCom.value[0]];
    if (nVal == 0) {
      let tempArr = [
        {
          components: toolCom.c_title,
          configNme: 'titleLeft',
        },
        // {
        //   components: toolCom.c_radio,
        //   configNme: 'effectConfig',
        // },
        {
          components: toolCom.c_radio,
          configNme: 'navConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'navStyleConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleNav',
        },
        {
          components: toolCom.c_foot,
          configNme: 'menuList',
        },
      ];
      rCom.value = arr.concat(tempArr);
    } else {
      getRComStyle(arr, type.value, type2.value, type3.value);
    }
  },
);
watch(
  () => configObj.value.navConfig?.tabVal,
  (nVal) => {
    if (nVal === undefined) return;
    type.value = nVal;
    const arr = [rCom.value[0]];
    if (setUp.value) {
      getRComStyle(arr, nVal, type2.value, type3.value);
    }
  },
);
watch(
  () => configObj.value.navStyleConfig?.tabVal,
  (nVal) => {
    if (nVal === undefined) return;
    type2.value = nVal;
    const arr = [rCom.value[0]];
    if (setUp.value) {
      getRComStyle(arr, type.value, nVal, type3.value);
    }
  },
);
watch(
  () => configObj.value.toneConfig?.tabVal,
  (nVal) => {
    if (nVal === undefined) return;
    type3.value = nVal;
    const arr = [rCom.value[0]];
    if (setUp.value) {
      getRComStyle(arr, type.value, type2.value, nVal);
    }
  },
);

function patchConfig(config) {
  if (!config.c_common_style) {
    config.c_common_style = {
      color: 'rgba(255,255,255,1)',
      color2: 'rgba(255,255,255,1)',
      lr: 0,
      type: 0,
    };
  }
  return config;
}
function getRComStyle(arr, typeVal, type2Val, type3Val) {
  if (typeVal == 0) {
    if (type2Val == 2) {
      rCom.value = [...arr, ...currencyTitle, ...currencyStyle];
    } else {
      if (type3Val == 0) {
        rCom.value = [...arr, ...oneStyle, ...currencyTitle, ...currencyStyle];
      } else {
        rCom.value = [...arr, ...oneStyle, ...twoStyle, ...currencyTitle, ...currencyStyle];
      }
    }
  } else {
    if (type2Val == 2) {
      rCom.value = [...arr, ...currencyTitle2, ...currencyStyle, ...currencyStyle2];
    } else {
      if (type3Val == 0) {
        rCom.value = [...arr, ...oneStyle, ...currencyTitle2, ...currencyStyle, ...currencyStyle2];
      } else {
        rCom.value = [...arr, ...oneStyle, ...twoStyle, ...currencyTitle2, ...currencyStyle, ...currencyStyle2];
      }
    }
  }
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
