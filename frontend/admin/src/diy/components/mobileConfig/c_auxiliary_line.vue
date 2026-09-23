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

defineOptions({ name: 'c_auxiliary_line', componentsName: 'home_auxiliary_line' });
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
watch(
  () => props.num,
  (nVal) => {
    // debugger;
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
    var arr = [rCom.value[0]];
    if (nVal == 0) {
      let tempArr = [
        {
          components: toolCom.c_title,
          configNme: 'titleLeft',
        },
        {
          components: toolCom.c_radio,
          configNme: 'lineStyle',
        },
      ];
      rCom.value = arr.concat(tempArr);
    } else {
      let tempArr = [
        {
          components: toolCom.c_title,
          configNme: 'titleRight',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'lineColor',
        },
        {
          components: toolCom.c_slider,
          configNme: 'heightConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleCurrent',
        },
        {
          components: toolCom.c_common_style,
          configNme: 'c_common_style',
        },
      ];
      rCom.value = arr.concat(tempArr);
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
function patchConfig(config) {
  if (!config.paddingConfig) {
    config.paddingConfig = {
      isAll: false,
      title: '内边距',
      val: 0,
      min: 0,
      max: 100,
      valList: [
        { val: config.topConfig ? config.topConfig.val : 0 },
        { val: config.lrEdge ? config.lrEdge.val : 0 },
        { val: config.bottomConfig ? config.bottomConfig.val : 0 },
        { val: config.lrEdge ? config.lrEdge.val : 0 },
      ],
    };
  }
  if (!config.marginConfig) {
    config.marginConfig = {
      isAll: false,
      title: '外边距',
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: config.mbConfig ? config.mbConfig.val : 0 }, { val: 0 }],
    };
  }
  if (!config.c_common_style) {
    config.c_common_style = {
      color: {
        title: '背景颜色',
        val: '',
        name: 'bgColor',
      },
      color2: {
        title: '线条颜色',
        val: '',
        name: 'lineColor',
      },
      lr: {
        title: '左右边距',
        val: 0,
        min: 0,
        max: 100,
      },
      type: 0,
    };
  }
  return config;
}
// 获取组件参数
function getConfig(data) {}
</script>

<style scoped></style>
