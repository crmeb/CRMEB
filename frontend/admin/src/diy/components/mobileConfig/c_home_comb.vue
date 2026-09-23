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

defineOptions({ name: 'c_home_comb', componentsName: 'home_comb' });
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
const oneContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleSearch',
  },
  {
    components: toolCom.c_radio,
    configNme: 'classConfig',
  },
  {
    components: toolCom.c_radio,
    configNme: 'searchConfig',
  },
  {
    components: toolCom.c_radio,
    configNme: 'searchBox',
  },
  // {
  //   components: toolCom.c_title,
  //   configNme: 'titleSearch',
  // },
];
const fixContent = [
  {
    components: toolCom.c_radio,
    configNme: 'searchFix',
  },
];
const txtContent = [
  {
    components: toolCom.c_input_item,
    configNme: 'titleConfig',
  },
];
const logoContent = [
  {
    components: toolCom.c_upload_img,
    configNme: 'logoConfig',
  },
];
const logoUpContent = [
  {
    components: toolCom.c_upload_img,
    configNme: 'logoConfig',
  },
  {
    components: toolCom.c_upload_img,
    configNme: 'logoUpConfig',
  },
];
const twoContent = [
  {
    components: toolCom.c_input_item,
    configNme: 'inputConfig',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleHotWords',
  },
  {
    components: toolCom.c_hot_word,
    configNme: 'hotWords',
  },
  {
    components: toolCom.c_input_number,
    configNme: 'numConfig',
  },
];
const threeContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleTab',
  },
  {
    components: toolCom.c_tab_list,
    configNme: 'tabListConfig',
  },
];
const rComContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleImg',
  },
  {
    components: toolCom.c_menu_list,
    configNme: 'swiperConfig',
  },
];
const oneStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleRight',
  },
  {
    components: toolCom.c_slider,
    configNme: 'contentConfig',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'classColor',
  },
];
const twoStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titlePointer',
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
const threeStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'dotColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'dotBgColor',
  },
];
const fourStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleImg',
  },
  {
    components: toolCom.c_fillet,
    configNme: 'filletImg',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleGradient',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'gradientColor',
  },
];
const bgColor = [
  {
    components: toolCom.c_title,
    configNme: 'titleGradient',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'gradientColor',
  },
];
const commonStyle = [
  {
    components: toolCom.c_common_style,
    configNme: 'commonStyle',
  },
];
let setUp = 0;
let type = 0;
let type2 = 0;
let type3 = 0;
let type4 = 0;
watch(
  () => props.num,
  (nVal) => {
    const value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[nVal]));
    if (!value.paddingConfig) {
      value['paddingConfig'] = {
        isAll: false,
        title: '内边距',
        val: 0,
        min: 0,
        max: 100,
        valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
      };
      if (value.topConfig) value.paddingConfig.valList[0].val = value.topConfig.val;
      if (value.prConfig) {
        value.paddingConfig.valList[1].val = value.prConfig.val;
        value.paddingConfig.valList[3].val = value.prConfig.val;
      }
      if (value.bottomConfig) value.paddingConfig.valList[2].val = value.bottomConfig.val;
    }
    if (!value.marginConfig) {
      value['marginConfig'] = {
        isAll: false,
        title: '外边距',
        val: 0,
        min: 0,
        max: 100,
        valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
      };
      if (value.mbConfig) value.marginConfig.valList[0].val = value.mbConfig.val;
    }
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
      getRComContent(arr);
    } else {
      getRComStyle(arr);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.classConfig?.tabVal,
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
    if (setUp == 0) {
      getRComContent(arr);
    } else {
      getRComStyle(arr);
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.searchBox?.tabVal,
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
    if (setUp == 0) {
      getRComContent(arr);
    } else {
      getRComStyle(arr);
    }
  },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type3 = nVal;
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
    if (setUp == 0) {
      getRComContent(arr);
    } else {
      getRComStyle(arr);
    }
  },
);
watch(
  () => configObj.value?.searchConfig?.tabVal,
  (nVal, oVal) => {
    type4 = nVal;
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
    if (setUp == 0) {
      getRComContent(arr);
    } else {
      getRComStyle(arr);
    }
  },
);
onMounted(() => {
  nextTick(() => {
    const value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    if (!value.paddingConfig) {
      value['paddingConfig'] = {
        isAll: false,
        title: '内边距',
        val: 0,
        min: 0,
        max: 100,
        valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
      };
      if (value.topConfig) value.paddingConfig.valList[0].val = value.topConfig.val;
      if (value.prConfig) {
        value.paddingConfig.valList[1].val = value.prConfig.val;
        value.paddingConfig.valList[3].val = value.prConfig.val;
      }
      if (value.bottomConfig) value.paddingConfig.valList[2].val = value.bottomConfig.val;
    }
    if (!value.marginConfig) {
      value['marginConfig'] = {
        isAll: false,
        title: '外边距',
        val: 0,
        min: 0,
        max: 100,
        valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
      };
      if (value.mbConfig) value.marginConfig.valList[0].val = value.mbConfig.val;
    }
    configObj.value = value;
  });
});
function getRComContent(arr) {
  let logoConfig = logoUpContent;
  if (type == 0) {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneContent, ...txtContent, ...twoContent, ...threeContent, ...rComContent];
    } else {
      rCom.value = [...arr, ...oneContent, ...logoConfig, ...twoContent, ...threeContent, ...rComContent];
    }
  } else {
    if (type2 == 0) {
      rCom.value = [...arr, ...oneContent, ...txtContent, ...twoContent, ...rComContent];
    } else {
      rCom.value = [...arr, ...oneContent, ...logoConfig, ...twoContent, ...rComContent];
    }
  }
}
function getRComStyle(arr) {
  if (type == 0) {
    if (type3 == 0) {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...fourStyle, ...commonStyle];
    } else {
      rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle, ...fourStyle, ...commonStyle];
    }
  } else {
    if (type3 == 0) {
      rCom.value = [...arr, ...twoStyle, ...fourStyle, ...commonStyle];
    } else {
      rCom.value = [...arr, ...twoStyle, ...threeStyle, ...fourStyle, ...commonStyle];
    }
  }
}
function handleSubmit(name) {
  const obj = {};
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
