<template>
  <div class="mobile-config">
    <!-- Vue2 时代 iView 的 <Form> 组件迁移后已不存在（控制台报 Failed to resolve component: Form），
         Vue 会回退渲染为原生 form 元素，此处显式改为原生标签以消除警告，渲染结果不变 -->
    <form>
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
    </form>
  </div>
</template>
<script setup>
import { nextTick, onMounted, ref, watch } from 'vue';
import toolCom from '@diy/components/mobileConfigRight/index.js';
import rightBtn from '@diy/components/rightBtn/index.vue';
import { useMobildConfigStore } from '@diy/store/mobildConfig';

defineOptions({ name: 'c_news_roll', cname: '新闻公告', componentsName: 'home_news_roll' });
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
const hotIndex = 1;
const configObj = ref({});
// 配置对象
const rCom = ref([
  {
    components: toolCom.c_card_select,
    configNme: 'styleConfig',
  },
]);
// 当前页面组件
const rComContent = [
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
  // {
  //   components: toolCom.c_radio,
  //   configNme: 'styleConfig',
  // },
  {
    components: toolCom.c_title,
    configNme: 'titleStyle',
  },
  {
    components: toolCom.c_radio,
    configNme: 'titleConfig',
  },
];
const sixContentImg = [
  {
    components: toolCom.c_upload_img,
    configNme: 'imgConfig',
  },
];
const sixContentTxt = [
  {
    components: toolCom.c_input_item,
    configNme: 'titleTxtConfig',
  },
];
const oneContent = [
  {
    components: toolCom.c_radio,
    configNme: 'rollConfig',
  },
];
const twoContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleButton',
  },
  {
    components: toolCom.c_radio,
    configNme: 'buttonConfig',
  },
];
const threeContent = [
  {
    components: toolCom.c_input_item,
    configNme: 'linkConfig',
  },
];
const fourContent = [
  {
    components: toolCom.c_title,
    configNme: 'titleContent',
  },
  {
    components: toolCom.c_product,
    configNme: 'listConfig',
  },
];
const fiveContent = [
  {
    components: toolCom.c_input_item,
    configNme: 'textConfig',
  },
];
const oneStyle = [
  {
    components: toolCom.c_set_up,
    configNme: 'setUp',
  },
  {
    components: toolCom.c_title,
    configNme: 'titleRight',
  },
];
const oneBntStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'bntColor',
  },
];
const fourStyle = [
  {
    components: toolCom.c_radio,
    configNme: 'toneConfig',
  },
];
const twoStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'titleBgColor',
  },
];
const threeStyle = [
  {
    components: toolCom.c_bg_color,
    configNme: 'titleColor',
  },
];
const currencyStyle = [
  {
    components: toolCom.c_common_style,
    configNme: 'c_common_style',
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
    setUp = nVal;
    var arr = [rCom.value[0]];
    if (nVal == 0) {
      if (type == 0) {
        if (type4 == 0) {
          rCom.value = [...arr, ...rComContent, ...sixContentImg, ...oneContent, ...twoContent, ...fourContent];
        } else {
          rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...oneContent, ...twoContent, ...fourContent];
        }
      } else {
        if (type4 == 0) {
          if (type2 == 0) {
            rCom.value = [...arr, ...rComContent, ...sixContentImg, ...twoContent, ...threeContent, ...fourContent];
          } else {
            rCom.value = [...arr, ...rComContent, ...sixContentImg, ...twoContent, ...fourContent];
          }
        } else {
          if (type2 == 0) {
            rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...twoContent, ...threeContent, ...fourContent];
          } else {
            rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...twoContent, ...fourContent];
          }
        }
      }
    } else {
      if (type == 0) {
        if (type4 == 0) {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...currencyStyle];
          }
        } else {
          if (type2 == 0) {
            if (type3) {
              rCom.value = [
                ...arr,
                ...oneStyle,
                ...oneBntStyle,
                ...fourStyle,
                ...twoStyle,
                ...threeStyle,
                ...currencyStyle,
              ];
            } else {
              rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...fourStyle, ...currencyStyle];
            }
          } else {
            if (type3) {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...twoStyle, ...threeStyle, ...currencyStyle];
            } else {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...currencyStyle];
            }
          }
        }
      } else {
        if (type4 == 0) {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...currencyStyle];
          }
        } else {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...threeStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...threeStyle, ...currencyStyle];
          }
        }
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.styleConfig?.tabVal,
  (nVal, oVal) => {
    type = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      if (nVal == 0) {
        if (type4 == 0) {
          rCom.value = [...arr, ...rComContent, ...sixContentImg, ...oneContent, ...twoContent, ...fourContent];
        } else {
          rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...oneContent, ...twoContent, ...fourContent];
        }
      } else {
        if (type4 == 0) {
          if (type2 == 0) {
            rCom.value = [...arr, ...rComContent, ...sixContentImg, ...twoContent, ...threeContent, ...fourContent];
          } else {
            rCom.value = [...arr, ...rComContent, ...sixContentImg, ...twoContent, ...fourContent];
          }
        } else {
          if (type2 == 0) {
            rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...twoContent, ...threeContent, ...fourContent];
          } else {
            rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...twoContent, ...fourContent];
          }
        }
      }
    } else {
      if (nVal == 0) {
        if (type4 == 0) {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...currencyStyle];
          }
        } else {
          if (type2 == 0) {
            if (type3) {
              rCom.value = [
                ...arr,
                ...oneStyle,
                ...oneBntStyle,
                ...fourStyle,
                ...twoStyle,
                ...threeStyle,
                ...currencyStyle,
              ];
            } else {
              rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...fourStyle, ...currencyStyle];
            }
          } else {
            if (type3) {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...twoStyle, ...threeStyle, ...currencyStyle];
            } else {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...currencyStyle];
            }
          }
        }
      } else {
        if (type4 == 0) {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...currencyStyle];
          }
        } else {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...threeStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...threeStyle, ...currencyStyle];
          }
        }
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.titleConfig?.tabVal,
  (nVal, oVal) => {
    type4 = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      if (type == 0) {
        if (nVal == 0) {
          rCom.value = [...arr, ...rComContent, ...sixContentImg, ...oneContent, ...twoContent, ...fourContent];
        } else {
          rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...oneContent, ...twoContent, ...fourContent];
        }
      } else {
        if (nVal == 0) {
          if (type2 == 0) {
            rCom.value = [...arr, ...rComContent, ...sixContentImg, ...twoContent, ...threeContent, ...fourContent];
          } else {
            rCom.value = [...arr, ...rComContent, ...sixContentImg, ...twoContent, ...fourContent];
          }
        } else {
          if (type2 == 0) {
            rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...twoContent, ...threeContent, ...fourContent];
          } else {
            rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...twoContent, ...fourContent];
          }
        }
      }
    } else {
      if (type == 0) {
        if (nVal == 0) {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...currencyStyle];
          }
        } else {
          if (type2 == 0) {
            if (type3) {
              rCom.value = [
                ...arr,
                ...oneStyle,
                ...oneBntStyle,
                ...fourStyle,
                ...twoStyle,
                ...threeStyle,
                ...currencyStyle,
              ];
            } else {
              rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...fourStyle, ...currencyStyle];
            }
          } else {
            if (type3) {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...twoStyle, ...threeStyle, ...currencyStyle];
            } else {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...currencyStyle];
            }
          }
        }
      } else {
        if (nVal == 0) {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...currencyStyle];
          }
        } else {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...threeStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...threeStyle, ...currencyStyle];
          }
        }
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.buttonConfig?.tabVal,
  (nVal, oVal) => {
    type2 = nVal;
    var arr = [rCom.value[0]];
    if (setUp == 0) {
      if (type == 0) {
        if (type4 == 0) {
          rCom.value = [...arr, ...rComContent, ...sixContentImg, ...oneContent, ...twoContent, ...fourContent];
        } else {
          rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...oneContent, ...twoContent, ...fourContent];
        }
      } else {
        if (type4 == 0) {
          if (nVal == 0) {
            rCom.value = [...arr, ...rComContent, ...sixContentImg, ...twoContent, ...threeContent, ...fourContent];
          } else {
            rCom.value = [...arr, ...rComContent, ...sixContentImg, ...twoContent, ...fourContent];
          }
        } else {
          if (nVal == 0) {
            rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...twoContent, ...threeContent, ...fourContent];
          } else {
            rCom.value = [...arr, ...rComContent, ...sixContentTxt, ...twoContent, ...fourContent];
          }
        }
      }
    } else {
      if (type == 0) {
        if (type4 == 0) {
          if (nVal == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...currencyStyle];
          }
        } else {
          if (nVal == 0) {
            if (type3) {
              rCom.value = [
                ...arr,
                ...oneStyle,
                ...oneBntStyle,
                ...fourStyle,
                ...twoStyle,
                ...threeStyle,
                ...currencyStyle,
              ];
            } else {
              rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...fourStyle, ...currencyStyle];
            }
          } else {
            if (type3) {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...twoStyle, ...threeStyle, ...currencyStyle];
            } else {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...currencyStyle];
            }
          }
        }
      } else {
        if (type4 == 0) {
          if (nVal == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...currencyStyle];
          }
        } else {
          if (type2 == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...threeStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...threeStyle, ...currencyStyle];
          }
        }
      }
    }
  },
  { deep: true },
);
watch(
  () => configObj.value?.toneConfig?.tabVal,
  (nVal, oVal) => {
    type3 = nVal;
    var arr = [rCom.value[0]];
    if (setUp) {
      if (type == 0) {
        if (type4 == 0) {
          if (nVal == 0) {
            rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...currencyStyle];
          } else {
            rCom.value = [...arr, ...oneStyle, ...currencyStyle];
          }
        } else {
          if (nVal == 0) {
            if (nVal) {
              rCom.value = [
                ...arr,
                ...oneStyle,
                ...oneBntStyle,
                ...fourStyle,
                ...twoStyle,
                ...threeStyle,
                ...currencyStyle,
              ];
            } else {
              rCom.value = [...arr, ...oneStyle, ...oneBntStyle, ...fourStyle, ...currencyStyle];
            }
          } else {
            if (nVal) {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...twoStyle, ...threeStyle, ...currencyStyle];
            } else {
              rCom.value = [...arr, ...oneStyle, ...fourStyle, ...currencyStyle];
            }
          }
        }
      }
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
  return config;
}
// 获取组件参数
function getConfig(data) {}
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
