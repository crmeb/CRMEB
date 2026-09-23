<template>
  <div class="mobile-config hot">
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

defineOptions({ name: 'c_home_hot', cname: '超值爆款', componentsName: 'home_hot' });
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
          components: toolCom.c_input_item,
          configNme: 'titleConfig',
        },
        {
          components: toolCom.c_input_item,
          configNme: 'desConfig',
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
          components: toolCom.c_bg_color,
          configNme: 'themeColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'boxColor',
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
      isAll: false,
      title: '内边距',
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.marginConfig) {
    data['marginConfig'] = {
      isAll: false,
      title: '外边距',
      val: 0,
      min: 0,
      max: 100,
      valList: [{ val: data.mbConfig ? data.mbConfig.val : 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
    };
  }
  if (!data.bottomBgColor) {
    data['bottomBgColor'] = {
      title: '底部背景',
      default: [
        {
          item: '#F5F5F5',
        },
      ],
      color: [
        {
          item: '#F5F5F5',
        },
      ],
    };
  }
  return data;
}
function getConfig(data) {}
function handleSubmit(name) {
  let obj = {};
  obj.activeIndex = props.activeIndex;
  obj.data = configObj.value;
  mobildConfigStore.UPDATEARR(obj);
}
</script>

<style scoped lang="scss">
.hot {
  padding-right: 14px;
  padding-left: 14px;
}
</style>
