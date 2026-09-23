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
        :number="num"
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

defineOptions({ name: 'c_bottom_menu', cname: '底部菜单', componentsName: 'home_bottom_menu' });
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
const rCom = ref([]);
let setUp = 0;
watch(
  () => props.num,
  (nVal) => {
    let source = nVal ? mobildConfigStore.defaultArray[nVal] : mobildConfigStore.bottomMenu;
    let value = JSON.parse(JSON.stringify(source || {}));
    value = patchConfig(value);
    configObj.value = value;
    setUp = value.setUp ? value.setUp.tabVal : 0;
    updateRCom();
  },
);
watch(
  configObj,
  (nVal, oVal) => {
    if (props.num) {
      mobildConfigStore.UPDATEARR({ num: props.num, val: nVal });
    } else {
      mobildConfigStore.UPBOTTOMMENU(nVal);
    }
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
  () => configObj.value?.entryConfig?.tabVal,
  (nVal, oVal) => {
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.menuConfig?.listStyle,
  (nVal, oVal) => {
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
    let value;
    if (props.num) {
      value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    } else {
      value = JSON.parse(JSON.stringify(mobildConfigStore.bottomMenu));
    }
    configObj.value = patchConfig(value);
    setUp = configObj.value.setUp ? configObj.value.setUp.tabVal : 0;
    updateRCom();
  });
});
function getDefaultConfig() {
  return {
    cname: '底部菜单',
    name: 'bottomMenu',
    isHide: false,
    setUp: { tabVal: 0 },
    entryConfig: {
      title: '入口内容',
      tabVal: 0,
      tabList: [{ name: '默认' }, { name: '自定义' }],
    },
    styleTitle: '样式设置',
    contentConfigTitle: '内容设置',
    showContent: {
      title: '显示内容',
      name: 'showContent',
      type: [3, 1, 2],
      list: [
        { id: 3, name: '首页', icon: 'icon-shouye6' },
        { id: 1, name: '收藏', icon: 'icon-shoucang4' },
        { id: 2, name: '购物车', icon: 'icon-gouwuche' },
        { id: 0, name: '客服', icon: 'icon-kefu' },
        { id: 4, name: '分享', icon: 'icon-fenxiang4' },
      ],
    },
    cartButton: {
      title: '购物车按钮',
      tabVal: 0,
      tabList: [{ name: '显示' }, { name: '隐藏' }],
    },
    menuConfig: {
      title: '最多可添加1张图片，建议宽度90 * 90px',
      bnt: '添加',
      type: 1,
      listStyle: 0,
      maxList: 100,
      list: [
        {
          img: '',
          type: 0,
          show: true,
          icon: '',
          info: [
            { title: '标题', value: '标题', tips: '选填，不超过4个字', max: 4 },
            { title: '链接', value: '', tips: '请输入链接', max: 100 },
          ],
        },
      ],
    },
    buttonStyleTitle: '按钮设置',
    toneConfig: {
      title: '按钮色调',
      tabVal: 0,
      tabList: [{ name: '跟随主题风格' }, { name: '自定义' }],
    },
    cartColor: {
      title: '购物车按钮',
      default: [{ item: '#FAAD14' }, { item: '#FAAD14' }],
      color: [{ item: '#FAAD14' }, { item: '#FAAD14' }],
    },
    buyColor: {
      title: '购买按钮',
      default: [{ item: '#E93323' }, { item: '#E93323' }],
      color: [{ item: '#E93323' }, { item: '#E93323' }],
    },
    generalStyleTitle: '通用样式',
    moduleColor: {
      title: '组件背景',
      default: [{ item: '#fff' }, { item: '#fff' }],
      color: [{ item: '#fff' }, { item: '#fff' }],
    },
    bottomBgColor: {
      title: '底部背景',
      default: [{ item: '#F5F5F5' }],
      color: [{ item: '#F5F5F5' }],
    },
    c_common_style: {
      color: [{ item: '#fff' }, { item: '#fff' }],
      color2: [{ item: '#F5F5F5' }],
      lr: 0,
      type: 0,
    },
    iconColor: {
      title: '图标颜色',
      default: [{ item: '#333' }],
      color: [{ item: '#333' }],
    },
    iconSize: { title: '图标大小', val: 20, min: 10, max: 50 },
    iconRotate: { title: '旋转角度', val: 0, min: 0, max: 360 },
    padding: { title: '内边距', val: 0, min: 0, max: 50 },
    fillet: {
      title: '背景圆角',
      type: 0,
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    },
    menuPcFillet: {
      title: '圆角设置',
      type: 0,
      val: 0,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    },
  };
}
function mergeConfig(defaultConfig, data) {
  if (Array.isArray(defaultConfig)) {
    return Array.isArray(data) ? data : JSON.parse(JSON.stringify(defaultConfig));
  }
  if (!defaultConfig || typeof defaultConfig !== 'object') {
    return data === undefined ? defaultConfig : data;
  }
  const result = JSON.parse(JSON.stringify(defaultConfig));
  if (!data || typeof data !== 'object') return result;
  Object.keys(data).forEach((key) => {
    if (
      result[key] &&
      typeof result[key] === 'object' &&
      !Array.isArray(result[key]) &&
      data[key] &&
      typeof data[key] === 'object' &&
      !Array.isArray(data[key])
    ) {
      result[key] = mergeConfig(result[key], data[key]);
    } else {
      result[key] = data[key];
    }
  });
  return result;
}
function patchConfig(config) {
  config = mergeConfig(getDefaultConfig(), config || {});
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
      valList: [{ val: 0 }, { val: 0 }, { val: config.mbConfig ? config.mbConfig.val : 0 }, { val: 0 }],
    };
  }
  if (!config.c_common_style && config.moduleColor) {
    config.c_common_style = {
      color: config.moduleColor.color,
      color2: config.bottomBgColor.color,
      lr: config.fillet.type ? config.fillet.val : 0,
      type: config.fillet.type,
    };
  }
  return config;
}
function updateRCom() {
  if (!configObj.value.setUp) return;

  let arr = [
    {
      components: toolCom.c_set_up,
      configNme: 'setUp',
    },
  ];

  if (setUp == 0) {
    // Content Config
    let contentArr = [
      {
        components: toolCom.c_title,
        configNme: 'contentConfigTitle',
      },
      {
        components: toolCom.c_radio,
        configNme: 'entryConfig',
      },
    ];

    if (configObj.value.entryConfig && configObj.value.entryConfig.tabVal == 1) {
      contentArr.push(
        {
          components: toolCom.c_menu_list,
          configNme: 'menuConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'cartButton',
        },
      );
    } else {
      // Default Mode
      contentArr = contentArr.concat([
        {
          components: toolCom.c_checkbox,
          configNme: 'showContent',
        },
        {
          components: toolCom.c_radio,
          configNme: 'cartButton',
        },
      ]);
    }
    rCom.value = arr.concat(contentArr);
  } else {
    // Style Config
    let styleArr = [];
    if (configObj.value.entryConfig && configObj.value.entryConfig.tabVal == 1) {
      if (configObj.value.menuConfig && configObj.value.menuConfig.listStyle == 0) {
        styleArr.push({
          components: toolCom.c_title,
          configNme: 'styleTitle',
        });
        styleArr.push({
          components: toolCom.c_fillet,
          configNme: 'menuPcFillet',
        });
      } else {
        styleArr.push({
          components: toolCom.c_title,
          configNme: 'styleTitle',
        });
        styleArr.push({
          components: toolCom.c_bg_color,
          configNme: 'iconColor',
        });
        styleArr.push({
          components: toolCom.c_slider,
          configNme: 'iconSize',
        });
        styleArr.push({
          components: toolCom.c_slider,
          configNme: 'iconRotate',
        });
      }
    }

    styleArr = styleArr.concat([
      {
        components: toolCom.c_title,
        configNme: 'buttonStyleTitle',
      },
      {
        components: toolCom.c_radio,
        configNme: 'toneConfig',
      },
    ]);

    if (configObj.value.toneConfig.tabVal == 1) {
      styleArr.push({
        components: toolCom.c_bg_color,
        configNme: 'cartColor',
      });
      styleArr.push({
        components: toolCom.c_bg_color,
        configNme: 'buyColor',
      });
    }

    styleArr = styleArr.concat([
      {
        components: toolCom.c_title,
        configNme: 'generalStyleTitle',
      },
      {
        components: toolCom.c_common_style,
        configNme: 'c_common_style',
      },
    ]);

    rCom.value = arr.concat(styleArr);
  }
}
function getConfig(data, name) {
  // No external API calls needed for this component usually
}
</script>
