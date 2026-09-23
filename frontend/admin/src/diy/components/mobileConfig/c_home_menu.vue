<template>
  <div class="mobile-config pro">
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
    <el-dialog v-model="modals" title="设计组件" width="60%">
      <!-- Placeholder for custom content -->
      <div>这里是设计组件内容</div>
    </el-dialog>
  </div>
</template>
<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import rightBtn from '@diy/components/rightBtn/index.vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_home_menu', cname: '导航组', componentsName: 'home_menu' });
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
    components: toolCom.c_card_select,
    configNme: 'menuStyleConfig',
  },
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]);
const rComContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleLeft',
  },
  {
    components: toolCom.c_radio,
    configNme: 'navDisplayStyle',
  },
  {
    components: toolCom.c_header_switch,
    configNme: 'headerConfig',
  },
  {
    components: toolCom.c_text_config,
    configNme: 'leftTopText',
  },
  {
    components: toolCom.c_text_config,
    configNme: 'rightTopText',
  },
];
const oneContent = [
  {
    components: toolCom.c_radio,
    configNme: 'rowsNum',
  },
];
const twoContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleContent',
  },
  {
    components: toolCom.c_menu_list,
    configNme: 'menuConfig',
  },
];
const oneStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleRight',
  },
  {
    components: toolCom.c_fillet,
    configNme: 'filletImg',
  },
];
const iconStyle = [
  {
    components: toolCom.c_icon_style,
    configNme: 'iconStyleConfig',
  },
];
const gridItemStyleConfig = [
  {
    components: toolCom.c_grid_item_style,
    configNme: 'gridItemStyle',
  },
];
const twoStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titlePointer',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const threeStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'pointerColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'pointerBgColor',
  },
];
const fourStyle = [
  {
    components: toolCom.c_common_style,
    configNme: 'commonStyle',
  },
];
let type = 0;
//展示样式索引
let setUp = 0;
//0：内容；1：样式
let type2 = 0;
//导航样式索引
let type3 = 0;
//色调索引
let headerEnable = false;
//头部开关状态
const modals = ref(false);
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
  () => configObj.value?.headerConfig?.enable,
  (nVal, oVal) => {
    headerEnable = nVal;
    updateContentConfig();
  },
  { deep: true },
);
watch(
  () => configObj.value?.setUp?.tabVal,
  (nVal, oVal) => {
    setUp = nVal;
    updateContentConfig();
  },
  { deep: true },
);
watch(
  () => configObj.value?.menuStyleConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    updateContentConfig();
  },
  { deep: true },
);
watch(
  () => configObj.value?.showConfig?.tabVal,
  (nVal, oVal) => {
    type = nVal;
    updateContentConfig();
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type3 = nVal;
    updateContentConfig();
  },
  { deep: true },
);
watch(
  () => configObj.value?.menuConfig?.listStyle,
  (nVal, oVal) => {
    updateContentConfig();
  },
  { deep: true },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    configObj.value = patchConfig(value);
  });
});
function getConfig(data) {
  if (data.name && data.name == 'custom_btn_click') {
    // Handle custom button click
    // Since there's no specific API provided for "custom component operations",
    // we can trigger a placeholder action or emit an event.
    // The user mentioned "click open popup, perform custom component operations".
    // I'll set modals to true to open a dialog.
    modals.value = true;
  }
}
function patchConfig(data) {
  if (!data) return data;
  if (!data.paddingConfig) {
    data['paddingConfig'] = {
      isAll: false,
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
      isAll: false,
      title: '外边距',
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
    if (data.mbConfig) data.marginConfig.valList[0].val = data.mbConfig.val;
  }
  if (!data.customBtnConfig) {
    data['customBtnConfig'] = {
      title: '设计组件',
    };
  }
  if (!data.fillet) {
    data['fillet'] = {
      title: '圆角设置',
      type: 0,
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.bgColor) {
    data['bgColor'] = {
      title: '背景颜色',
      color: [
        {
          item: '#fff',
        },
      ],
      default: [
        {
          item: '#fff',
        },
      ],
    };
  }
  if (!data.headerStyle) {
    data['headerStyle'] = {
      title: '头部样式',
      fontSize: 16,
      rightFontSize: 12,
      leftColor: '#333',
      rightColor: '#999',
      leftWeight: 'normal',
      rightWeight: 'normal',
      topPadding: 10,
      bottomPadding: 10,
      leftRightPadding: 12,
    };
  }
  if (data.headerStyle && !data.headerStyle.rightFontSize) {
    data.headerStyle['rightFontSize'] = 12;
  }
  return data;
}
// 统一生成配置方法
function generateContentConfig() {
  let config = [
    {
      components: toolCom.c_title,
      configNme: 'titleLeft',
    },
    {
      components: toolCom.c_radio,
      configNme: 'navDisplayStyle',
    },
    {
      components: toolCom.c_header_switch,
      configNme: 'headerConfig',
    },
  ];

  // 列表展示固定为列表布局，不显示固定/分页滑动的展示样式配置
  if (type2 !== 2) {
    config.splice(2, 0, {
      components: toolCom.c_radio,
      configNme: 'showConfig',
    });
  }

  // 头部开启时显示头部文字配置
  if (headerEnable) {
    config.push(
      {
        components: toolCom.c_text_config,
        configNme: 'leftTopText',
      },
      {
        components: toolCom.c_text_config,
        configNme: 'rightTopText',
      },
    );
  }

  // 列表样式不显示单行数量和宫格样式
  if (type2 !== 2) {
    // 排列展示显示单行数量
    if (type2 === 0) {
      config.splice(3, 0, {
        components: toolCom.c_radio,
        configNme: 'number',
      });
    }
    // 宫格展示显示宫格样式
    if (type2 === 1) {
      config.splice(3, 0, {
        components: toolCom.c_radio,
        configNme: 'gridStyle',
      });
    }
  }

  // config.push({
  //   components: toolCom.c_radio,
  //   configNme: 'showConfig',
  // });

  return config;
}
// 生成样式设置配置
function generateStyleConfig() {
  let config = [];

  // 头部开启时显示头部样式配置
  if (headerEnable) {
    config.push({
      components: toolCom.c_header_style,
      configNme: 'headerStyle',
    });
  }

  // 宫格展示时显示宫格项样式配置
  if (type2 === 1) {
    config = config.concat(gridItemStyleConfig);
  }

  return config;
}
// 更新内容配置
function updateContentConfig() {
  var arr = [
    {
      components: toolCom.c_card_select,
      configNme: 'menuStyleConfig',
    },
    {
      components: toolCom.c_set_up,
      configNme: 'setUp',
    },
  ];

  if (setUp == 0) {
    let contentConfig = generateContentConfig();
    let rComArr = arr.concat(contentConfig);

    if (type == 0) {
      rCom.value = rComArr.concat(twoContent);
    } else {
      let rCom2 = type2 === 2 ? rComArr : rComArr.concat(oneContent);
      rCom.value = rCom2.concat(twoContent);
    }
  } else {
    // 样式设置
    let styleConfig = generateStyleConfig();
    let listStyle = configObj.value.menuConfig ? configObj.value.menuConfig.listStyle : 0;
    let middleStyle = [];
    if (listStyle === 1) {
      middleStyle = iconStyle;
    }
    middleStyle = oneStyle;

    let base = arr.concat(styleConfig).concat(middleStyle);

    if (type == 0) {
      rCom.value = base.concat(fourStyle);
    } else {
      if (type3 == 0) {
        rCom.value = base.concat(twoStyle).concat(fourStyle);
      } else {
        rCom.value = base.concat(twoStyle).concat(threeStyle).concat(fourStyle);
      }
    }
  }
}
</script>

<style scoped></style>
