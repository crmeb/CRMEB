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

defineOptions({ name: 'c_picture_cube', cname: '图片魔方', componentsName: 'picture_cube' });
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
// 当前页面组件
let setUp = 0;
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
          components: toolCom.c_button_style,
          configNme: 'styleConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleShow',
        },
        {
          components: toolCom.c_pictrue,
          configNme: 'picStyle',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleContent',
        },
        {
          components: toolCom.c_menu_list,
          configNme: 'menuConfig',
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
          components: toolCom.c_slider,
          configNme: 'imgConfig',
        },
        {
          components: toolCom.c_fillet,
          configNme: 'filletImg',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
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
        { val: config.prConfig ? config.prConfig.val : 0 },
        { val: config.bottomConfig ? config.bottomConfig.val : 0 },
        { val: config.prConfig ? config.prConfig.val : 0 },
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
      valList: [{ val: config.mbConfig ? config.mbConfig.val : 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!config.c_common_style) {
    config.c_common_style = {
      color: 'rgba(255,255,255,1)',
      color2: 'rgba(255,255,255,1)',
      lr: 0,
      type: 0,
    };
  }
  // 兼容旧版「隐藏/显示」语义，统一为「颜色/图片」语义
  if (config.componentBgConfig) {
    let bg = config.componentBgConfig;
    let isLegacy =
      bg.tabList && bg.tabList.length >= 2 && bg.tabList[0].name === '隐藏' && bg.tabList[1].name === '显示';
    if (isLegacy) {
      bg.tabList = [{ name: '颜色' }, { name: '图片' }];
      // 旧版「显示」(tabVal=1) 即展示背景颜色，映射为「颜色」(tabVal=0)
      bg.tabVal = 0;
      if (bg.val !== undefined) delete bg.val;
    }
    if (!bg.colorDirection) {
      bg.colorDirection = {
        title: '渐变方向',
        tabVal: 0,
        tabList: [{ name: '横向' }, { name: '纵向' }, { name: '左斜' }, { name: '右斜' }],
      };
    }
    if (!bg.imageConfig) {
      bg.imageConfig = {
        header: '背景图片',
        title: '',
        name: '上传图片',
        type: 'code',
        url: '',
        info: '建议尺寸：750px * 400px',
      };
    }
  }
  return config;
}
</script>

<style scoped lang="scss">
:deep(.ivu-radio-group-button.ivu-radio-group-large .ivu-radio-wrapper) {
  width: 52px !important;
  margin-bottom: 10px;
  margin-right: 9px !important;
}
.pro {
  padding: 15px 15px 0;
  .tips {
    height: 50px;
    line-height: 50px;
    color: #999;
    font-size: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }
}
.btn-box {
  padding-bottom: 20px;
}
</style>
