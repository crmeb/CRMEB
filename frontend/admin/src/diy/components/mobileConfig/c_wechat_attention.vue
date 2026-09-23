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

defineOptions({ name: 'c_wechat_attention', componentsName: 'z_wechat_attention' });
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
          components: toolCom.c_input_item,
          configNme: 'titleConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'positionTitle',
        },
        {
          components: toolCom.c_radio,
          configNme: 'positionConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'pictrueTitle',
        },
        {
          components: toolCom.c_upload_img,
          configNme: 'imgConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'codeTitle',
        },
        {
          components: toolCom.c_upload_img,
          configNme: 'codeConfig',
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
          configNme: 'themeColor',
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
function patchConfig(data) {
  if (!data.paddingConfig) {
    data['paddingConfig'] = {
      title: '内边距',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
      valList: [
        { val: 0 },
        { val: data.prConfig ? data.prConfig.val : 0 },
        { val: 0 },
        { val: data.prConfig ? data.prConfig.val : 0 },
      ],
    };
  }
  if (!data.marginConfig) {
    data['marginConfig'] = {
      title: '外边距',
      val: 0,
      min: 0,
      max: 100,
      isAll: false,
      valList: [{ val: data.mbConfig ? data.mbConfig.val : 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.c_common_style) {
    data['c_common_style'] = {
      color: 'rgba(255,255,255,1)',
      color2: 'rgba(255,255,255,1)',
      lr: 0,
      type: 0,
    };
  }
  if (!data.themeColor) {
    data['themeColor'] = {
      title: '按钮颜色',
      default: [
        {
          item: '#E93323',
        },
      ],
      color: [
        {
          item: '#E93323',
        },
      ],
    };
  }
  return data;
}
// 获取组件参数
function getConfig(data) {}
</script>

<style scoped></style>
