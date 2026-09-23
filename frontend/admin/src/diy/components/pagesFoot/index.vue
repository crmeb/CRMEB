<template>
  <div
    class="page-footer"
    :style="{ background: bgColor, paddingTop: topConfig + 'px', paddingBottom: bottomConfig + 'px' }"
    v-if="navConfig == 0"
  >
    <div class="foot-item" :class="navStyleConfig == 1 ? 'on' : ''" v-for="(item, index) in menuList" :key="index">
      <div v-if="navStyleConfig != 1">
        <img :src="item.imgList ? item.imgList[0] : noPic" alt="" v-if="index == isSpecial" />
        <img :src="item.imgList ? item.imgList[1] : noPic" alt="" v-else />
      </div>
      <div v-if="navStyleConfig != 2">
        <p v-if="index == isSpecial" :style="{ color: toneConfig ? activeTxtColor : colorStyle.theme }">
          {{ item.name || '自定义' }}
        </p>
        <p v-else :style="{ color: toneConfig ? txtColor : '#1A1A1A' }">{{ item.name || '自定义' }}</p>
      </div>
    </div>
  </div>
  <div
    class="page-footer page-footer2"
    v-else
    :style="{
      background: bgColor2,
      paddingTop: topConfig + 'px',
      paddingBottom: bottomConfig + 'px',
      marginLeft: prConfig + 'px',
      marginRight: prConfig + 'px',
      borderRadius: bgRadius,
    }"
  >
    <div class="list">
      <div class="foot-item" :class="navStyleConfig == 1 ? 'on' : ''" v-for="(item, index) in menuList" :key="index">
        <div v-if="navStyleConfig != 1">
          <img :src="item.imgList ? item.imgList[0] : noPic" alt="" v-if="index == isSpecial" />
          <img :src="item.imgList ? item.imgList[1] : noPic" alt="" v-else />
        </div>
        <div v-if="navStyleConfig != 2">
          <p v-if="index == isSpecial" :style="{ color: toneConfig ? activeTxtColor : colorStyle.theme }">
            {{ item.name || '自定义' }}
          </p>
          <p v-else :style="{ color: toneConfig ? txtColor : '#1A1A1A' }">{{ item.name || '自定义' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { ElMessage } from 'element-plus';
import __asset_noPictrue_0 from '@diy/assets/images/noPictrue.png';

import { useMobildConfigStore } from '@diy/store/mobildConfig';
import { getColorChange } from '@diy/api';

defineOptions({ name: 'index.vue' });

const props = defineProps({
  configObj: {
    type: Object,
    default: function () {
      return {};
    },
  },
  configNme: {
    type: String,
    default: '',
  },
});

const mobildConfigStore = useMobildConfigStore();
const { pageFooter } = storeToRefs(mobildConfigStore);

const txtColor = ref('');
const activeTxtColor = ref('');
const bgColor = ref('');
const bgColor2 = ref('');
const menuList = ref([]);
const isSpecial = ref(2);
const toneConfig = ref(0);
const topConfig = ref(0);
const bottomConfig = ref(0);
const navStyleConfig = ref(0);
const navConfig = ref(0);
const prConfig = ref(0);
const mTop = ref(0);
const bgRadius = ref(0);
const noPic = __asset_noPictrue_0;

// ---- @diy/mixins/theme 内联（created 时机拉取主题色） ----
const current = ref(3);
const colorStyle = ref({});

function getInfo() {
  getColorChange('color_change')
    .then((res) => {
      current.value = res.data.status ? res.data.status : 3;
      let colorList = [
        {
          theme: '#42CA4D',
          priceColor: '#FF7600',
          minorColor: 'rgba(108, 198, 94, 0.5)',
          minorColorT: 'rgba(66, 202, 77, 0.1)',
          bntColor: '#FE960F',
          gradient: '#4DEA4D',
        },
        {
          theme: '#e93323',
          priceColor: '#e93323',
          minorColor: 'rgba(233, 51, 35, 0.5)',
          minorColorT: 'rgba(233, 51, 35, 0.1)',
          bntColor: '#FE960F',
          gradient: '#FF7931',
        },
        {
          theme: '#1DB0FC',
          priceColor: '#FD502F',
          minorColor: 'rgba(58, 139, 236, 0.5)',
          minorColorT: 'rgba(9, 139, 243, 0.1)',
          bntColor: '#22CAFD',
          gradient: '#5ACBFF',
        },
        {
          theme: '#FF448F',
          priceColor: '#FF448F',
          minorColor: 'rgba(255, 68, 143, 0.5)',
          minorColorT: 'rgba(255, 68, 143, 0.1)',
          bntColor: '#282828',
          gradient: '#FF67AD',
        },
        {
          theme: '#FE5C2D',
          priceColor: '#FE5C2D',
          minorColor: 'rgba(254, 92, 45, 0.5)',
          minorColorT: 'rgba(254, 92, 45, 0.1)',
          bntColor: '#FDB000',
          gradient: '#FF9451',
        },
        {
          theme: '#E0A558',
          priceColor: '#DA8C18',
          minorColor: 'rgba(224, 165, 88, 0.5)',
          minorColorT: 'rgba(224, 165, 88, 0.1)',
          bntColor: '#1A1A1A',
          gradient: '#FFCD8C',
        },
      ];
      switch (res.data.status) {
        case 1:
          colorStyle.value = colorList[2];
          break;
        case 2:
          colorStyle.value = colorList[0];
          break;
        case 3:
          colorStyle.value = colorList[1];
          break;
        case 4:
          colorStyle.value = colorList[3];
          break;
        case 5:
          colorStyle.value = colorList[4];
          break;
        case 6:
          colorStyle.value = colorList[5];
          break;
        default:
          colorStyle.value = colorList[1];
          break;
      }
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}
getInfo();
// ---- theme mixin 内联结束 ----

watch(
  pageFooter,
  (nVal, oVal) => {
    setConfig(nVal);
  },
  { deep: true },
);

onMounted(() => {
  let data = mobildConfigStore.pageFooter;
  setConfig(data);
});

function setConfig(data) {
  txtColor.value = data.txtColor.color[0].item;
  activeTxtColor.value = data.activeTxtColor.color[0].item;
  bgColor.value = data.bgColor.color[0].item;
  bgColor2.value = data.bgColor2.color[0].item;
  navStyleConfig.value = data.navStyleConfig.tabVal;
  toneConfig.value = data.toneConfig.tabVal;
  navConfig.value = data.navConfig.tabVal;
  topConfig.value = data.topConfig.val;
  bottomConfig.value = data.bottomConfig.val;
  prConfig.value = data.prConfig.val;
  mTop.value = data.mbConfig.val;
  let fillet = data.fillet.type;
  let filletVal = data.fillet.val;
  let valList = data.fillet.valList;
  bgRadius.value = fillet
    ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
    : filletVal + 'px';
  mobildConfigStore.footType(navConfig.value);
  mobildConfigStore.footBottom(mTop.value);
  menuList.value = [];
  menuList.value = data.menuList.length ? data.menuList : 5;
  if (data.status.title == '是否显示') {
    isSpecial.value = 2;
  } else {
    isSpecial.value = 0;
  }
}
</script>

<style lang="scss" scoped>
.page-footer2 {
  backdrop-filter: blur(10px);
  .list {
    display: contents;
  }
}
.page-footer {
  display: flex;
  background: #fff;
  .foot-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 50px;
    &.on {
      p {
        font-size: 16px;
      }
    }
    img {
      width: 24px;
      height: 24px;
    }
    p {
      font-size: 12px;
      color: #282828;
      margin-top: 1px;
      &.on {
        color: #00a4f8;
      }
    }
  }
}
</style>
