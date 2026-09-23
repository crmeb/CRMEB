<template>
  <div class="mobile-config">
    <div v-for="(item, key) in rCom" :key="key">
      <component
        :is="item.components"
        :configObj="configObj"
        ref="childData"
        :configNme="item.configNme"
        :key="key"
        @getConfig="getConfig"
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

defineOptions({ name: 'c_userInfor', componentsName: 'home_userInfor' });
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
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]);
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
    configNme: 'progressColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'progressBgColor',
  },
];
const currencyStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleCurrency',
  },
  {
    components: toolCom.c_common_style,
    configNme: 'c_common_style',
  },
];
let setUp = 0;
let type = 0;
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
    var arr = [rCom.value[0]];
    if (nVal == 0) {
      let tempArr = [
        {
          components: toolCom.c_title,
          configNme: 'titleLeft',
        },
        {
          components: toolCom.c_radio,
          configNme: 'styleConfig',
        },
        {
          components: toolCom.c_checkbox,
          configNme: 'checkboxInfo',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleImg',
        },
        {
          components: toolCom.c_upload_img,
          configNme: 'logoConfig',
        },
      ];
      rCom.value = arr.concat(tempArr);
    } else {
      if (type == 0) {
        rCom.value = [...arr, ...oneStyle, ...currencyStyle];
      } else {
        rCom.value = [...arr, ...oneStyle, ...twoStyle, ...currencyStyle];
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type = nVal;
    var arr = [rCom.value[0]];
    if (setUp) {
      if (nVal == 0) {
        rCom.value = [...arr, ...oneStyle, ...currencyStyle];
      } else {
        rCom.value = [...arr, ...oneStyle, ...twoStyle, ...currencyStyle];
      }
    }
  },
  { deep: true },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    configObj.value = value;
    configObj.value = patchConfig(configObj.value);
  });
});
function patchConfig(data) {
  if (!data) return data;
  if (!data.paddingConfig) {
    data['paddingConfig'] = {
      title: '内边距',
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
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
  }
  if (!data.c_common_style) {
    data['c_common_style'] = {
      color: 'rgba(255,255,255,1)',
      color2: 'rgba(255,255,255,1)',
      lr: 0,
      type: 0,
    };
  }
  return data;
}
// 获取组件参数
function getConfig(data) {}
function handleSubmit(name) {
  let obj = {};
  obj.activeIndex = props.activeIndex;
  obj.data = configObj.value;
  mobildConfigStore.UPDATEARR(obj);
}
</script>

<style scoped></style>
