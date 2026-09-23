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
import { categoryList as categoryListApi } from '@diy/api';
import { cmsListApi } from '@/api/cms';
import { useMobildConfigStore } from '@diy/store/mobildConfig';
import { ElMessage } from 'element-plus';

defineOptions({ name: 'c_home_bargain', componentsName: 'home_bargain' });
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
    configNme: 'styleConfig',
  },
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
]);
const oneStyle = [
  {
    components: toolCom.c_title,
    configNme: 'titleRight',
  },
  {
    components: toolCom.c_fillet,
    configNme: 'filletImg',
  },
  {
    components: toolCom.c_radio,
    configNme: 'nameConfig',
  },
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const twoStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'likeSuccessColor',
  },
];
const threeStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'nameColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'timeColor',
  },
  {
    components: toolCom.c_bg_color,
    configNme: 'browseColor',
  },
  // {
  //   components: toolCom.c_bg_color,
  //   configNme: 'likeColor',
  // },
  {
    components: toolCom.c_bg_color,
    configNme: 'statisticColor',
  },
  {
    components: toolCom.c_common_style,
    configNme: 'c_common_style',
  },
];
let setUp = 0;
let type = 0;
watch(
  () => props.num,
  (nVal) => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[nVal]));
    configObj.value = value;
    categoryList();
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
    // 取前两个
    var arr = [
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
      let tempArr = [
        {
          components: toolCom.c_title,
          configNme: 'titleArticle',
        },
        {
          components: toolCom.c_select,
          configNme: 'selectConfig',
        },
        {
          components: toolCom.c_input_number,
          configNme: 'numConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleList',
        },
        {
          components: toolCom.c_checkbox,
          configNme: 'checkboxList',
        },
      ];
      rCom.value = arr.concat(tempArr);
    } else {
      if (type) {
        rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle];
      } else {
        rCom.value = [...arr, ...oneStyle, ...threeStyle];
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type = nVal;
    var arr = [
      {
        components: toolCom.c_card_select,
        configNme: 'styleConfig',
      },
      {
        components: toolCom.c_set_up,
        configNme: 'setUp',
      },
    ];
    if (setUp) {
      if (nVal) {
        rCom.value = [...arr, ...oneStyle, ...twoStyle, ...threeStyle];
      } else {
        rCom.value = [...arr, ...oneStyle, ...threeStyle];
      }
    }
  },
);
onMounted(() => {
  nextTick(() => {
    let value = JSON.parse(JSON.stringify(mobildConfigStore.defaultArray[props.num]));
    configObj.value = patchConfig(value);
    categoryList();
  });
});
function patchConfig(data) {
  if (!data) return data;
  if (!data.paddingConfig) {
    data['paddingConfig'] = {
      isAll: false,
      title: '内边距',
      val: 0,
      min: 0,
      max: 100,
      valList: [
        { val: data.topConfig ? data.topConfig.val : 0 },
        { val: data.prConfig ? data.prConfig.val : 0 },
        { val: data.bottomConfig ? data.bottomConfig.val : 0 },
        { val: data.prConfig ? data.prConfig.val : 0 },
      ],
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
  return data;
}
function categoryList() {
  categoryListApi()
    .then((res) => {
      let data = [];
      res.data.map((item) => {
        data.push({ title: item.title, activeValue: item.id.toString() });
      });
      configObj.value.selectConfig.list = data;
      // mobildConfigStore.UPDATEARR({ num: props.num, val: this.pageData })
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
// 获取组件参数
function getConfig(data) {
  if (data.name == 'radio') {
    return;
  }
  let val = {
    pid: parseInt(configObj.value.selectConfig.activeValue),
    page: 1,
    limit: parseInt(configObj.value.numConfig.val),
  };
  cmsListApi(val)
    .then((res) => {
      configObj.value.selectList.list = res.data.list;
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
function handleSubmit(name) {
  let obj = {};
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
