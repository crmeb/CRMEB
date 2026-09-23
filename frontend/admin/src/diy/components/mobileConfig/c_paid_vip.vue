<template>
  <div class="mobile-config pro">
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
import __asset_goods_vip_0 from '@/assets/images/goods_vip.png';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import rightBtn from '@diy/components/rightBtn/index.vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_paid_vip', cname: '付费会员', componentsName: 'home_paid_vip' });
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
// 内容设置配置项
const contentConfig = [
  {
    components: toolCom.c_title,
    configNme: 'titleContent',
  },
  {
    components: toolCom.c_upload_img,
    configNme: 'imgConfig',
  },
  {
    components: toolCom.c_input_item,
    configNme: 'rightBntConfig',
  },
];
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
    updateRCom();
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    if (setUp === 1) {
      // Only update if currently in style tab
      updateRCom();
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
  if (!data.borderConfig) {
    data['borderConfig'] = {
      title: '边框设置',
      tabVal: 0,
      tabList: [{ name: '隐藏' }, { name: '显示' }],
      val: 0, // 0: Hide, 1: Show
      styleConfig: {
        title: '边框样式',
        tabVal: 0,
        tabList: [
          { name: '实线', style: 'solid' },
          { name: '虚线', style: 'dashed' },
          { name: '点状', style: 'dotted' },
        ],
      },
      widthConfig: {
        title: '边框粗细',
        val: 1,
        min: 1,
      },
      colorConfig: {
        title: '边框颜色',
        default: [{ item: '#e5e5e5' }],
        color: [{ item: '#e5e5e5' }],
      },
    };
  }
  if (!data.shadowConfig) {
    data['shadowConfig'] = {
      title: '阴影设置',
      tabVal: 0,
      tabList: [{ name: '隐藏' }, { name: '显示' }],
      val: 0, // 0: Hide, 1: Show
      colorConfig: {
        title: '阴影颜色',
        default: [{ item: '#e5e5e5' }],
        color: [{ item: '#e5e5e5' }],
      },
    };
  }
  if (!data.componentBgConfig) {
    data['componentBgConfig'] = {
      title: '组件背景',
      tabVal: 0,
      tabList: [{ name: '颜色' }, { name: '图片' }],
      colorConfig: {
        title: '背景颜色',
        default: [{ item: '#fff' }],
        color: [{ item: '#fff' }],
      },
      imageConfig: {
        url: '',
        type: 'code',
        name: '背景图片',
      },
    };
  }
  if (!data.fillet) {
    data['fillet'] = {
      title: '背景圆角',
      type: 0,
      list: [
        { val: '全部', icon: 'iconcaozuo-zhengti' },
        { val: '单个', icon: 'iconcaozuo-bianjiao' },
      ],
      valName: '圆角值',
      val: 8,
      min: 0,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.imgConfig) {
    data['imgConfig'] = {
      info: '建议：36px * 36px',
      url: __asset_goods_vip_0,
      type: 'code',
      delType: 1,
      name: '会员图片',
    };
  }
  if (!data.rightBntConfig) {
    data['rightBntConfig'] = {
      title: '右侧按钮',
      value: '立即开通',
      place: '请输入按钮文字',
      max: 6,
    };
  }
  if (!data.c_common_style) {
    data['c_common_style'] = {
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
  return data;
}
// 更新配置组件列表
function updateRCom() {
  var arr = [rCom.value[0]]; // 保留第一个 setUp 组件

  if (setUp == 0) {
    // 内容设置
    rCom.value = arr.concat(contentConfig);
  } else {
    // 样式设置
    let styleArr = [
      {
        components: toolCom.c_title,
        configNme: 'titleStyle',
      },
      {
        components: toolCom.c_radio,
        configNme: 'toneConfig',
      },
    ];

    // Show color settings if Custom Tone (tabVal == 1)
    if (configObj.value.toneConfig && configObj.value.toneConfig.tabVal == 1) {
      styleArr = styleArr.concat([
        {
          components: toolCom.c_bg_color,
          configNme: 'tipsColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'moneyColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'btnColor',
        },
        {
          components: toolCom.c_slider,
          configNme: 'btnConfig',
        },
      ]);
    }

    // Common Styles
    styleArr = styleArr.concat([
      {
        components: toolCom.c_common_style,
        configNme: 'c_common_style',
      },
    ]);

    rCom.value = arr.concat(styleArr);
  }
}
function handleSubmit(name) {
  let obj = {};
  obj.activeIndex = props.activeIndex;
  obj.data = configObj.value;
  mobildConfigStore.UPDATEARR(obj);
}
</script>

<style scoped></style>
