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

defineOptions({ name: 'c_product_service', cname: '商品服务', componentsName: 'home_product_service' });
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
let setUp = 0;
const rCom = ref([
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]);
watch(
  () => props.num,
  (nVal, oVal) => {
    let data = mobildConfigStore.defaultArray[nVal];
    setConfig(data);
  },
  { deep: true },
);
watch(
  () => configObj.value?.setUp?.tabVal,
  (nVal, oVal) => {
    setUp = nVal;
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
onMounted(() => {
  nextTick(() => {
    let data = mobildConfigStore.defaultArray[props.num];
    setConfig(data);
  });
});
function setConfig(data) {
  if (!data) return;
  data = patchConfig(data);
  configObj.value = data;
  setUp = data.setUp.tabVal;
  updateRCom();
}
function patchConfig(data) {
  if (!data) return data;
  if (!data.paddingConfig) {
    data['paddingConfig'] = {
      title: '内边距',
      val: 10,
      min: 0,
      max: 100,
      isAll: false,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.marginConfig) {
    data['marginConfig'] = {
      title: '外边距',
      isAll: false,
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
  }
  return data;
}
function updateRCom() {
  const arr = [
    {
      components: toolCom.c_set_up,
      configNme: 'setUp',
    },
  ];
  if (setUp == 0) {
    // Content Settings
    rCom.value = arr.concat([
      {
        components: toolCom.c_title,
        configNme: 'openService',
      },
      {
        components: toolCom.c_checkbox,
        configNme: 'checkBoxConfig',
      },
    ]);
  } else {
    // Style Settings
    let styleArr = [
      {
        components: toolCom.c_title,
        configNme: 'serviceStyleTitle',
      },
      {
        components: toolCom.c_bg_color,
        configNme: 'titleColor',
      },
      {
        components: toolCom.c_bg_color,
        configNme: 'contentColor',
      },
      {
        components: toolCom.c_radio,
        configNme: 'toneConfig',
      },
    ];

    if (configObj.value.toneConfig && configObj.value.toneConfig.tabVal == 1) {
      styleArr = styleArr.concat([
        {
          components: toolCom.c_bg_color,
          configNme: 'activityColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'activityBgColor',
        },
      ]);
    }

    styleArr = styleArr.concat([
      {
        components: toolCom.c_title,
        configNme: 'generalStyleTitle',
      },
      {
        components: toolCom.c_bg_color,
        configNme: 'componentBgColor',
      },
      {
        components: toolCom.c_common_style,
        configNme: 'c_common_style',
      },
    ]);

    rCom.value = arr.concat(styleArr);
  }
}
</script>

<style scoped lang="scss">
.mobile-config {
  padding-bottom: 20px;
}
</style>
