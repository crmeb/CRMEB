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

defineOptions({ name: 'c_info_card', componentsName: 'home_info_card' });
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
const contentList = [
  {
    components: toolCom.c_title,
    configNme: 'contentTitle',
  },
  {
    components: toolCom.c_radio,
    configNme: 'isShow',
  },
];
const styleList = [
  {
    components: toolCom.c_title,
    configNme: 'titleStyle',
  },
  {
    components: toolCom.c_align,
    configNme: 'textPosition',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'textColor',
  },
  {
    components: toolCom.c_slider,
    configNme: 'fontSize',
  },
  {
    components: toolCom.c_common_style,
    configNme: 'currencyStyle',
  },
];
let setUp = 0;
watch(
  () => props.num,
  (nVal) => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[nVal]));
    configObj.value = value;
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
      rCom.value = arr.concat(contentList);
    } else {
      rCom.value = arr.concat(styleList);
    }
  },
  { deep: true },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    configObj.value = value;
  });
});
function getConfig(data) {}
</script>
