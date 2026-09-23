<template>
  <div>
    <div class="button-style acea-row row-middle">
      <div class="title-tips" v-if="configData">
        <span>{{ configData.title }}</span>
      </div>
      <div class="style-box acea-row row-middle" v-for="(item, index) in list" :key="index">
        <div class="pictrue acea-row row-center-wrapper" :class="current == index ? 'on' : ''" @click="tap(index)">
          <img
            :src="item.url"
            :style="{
              width: item.width + 'px',
              height: item.height + 'px',
            }"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import __asset_cart2_0 from '@/assets/images/cart2.png';
import __asset_cart3_1 from '@/assets/images/cart3.png';
import __asset_cart1_2 from '@/assets/images/cart1.png';

defineOptions({ name: 'c_button_img' });

const props = defineProps({
  configObj: {
    type: Object,
  },
  configNme: {
    type: String,
  },
});

const defaults = ref({});
const configData = ref({});
const current = ref(0);
const list = ref([]);

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
    getBnt(nVal);
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
    getBnt(defaults.value);
  });
});

function tap(index) {
  current.value = index;
  configData.value.tabVal = index;
}
function getBnt(nVal) {
  let obj = [
    {
      url: __asset_cart2_0,
      width: 24,
      height: 24,
    },
    {
      url: __asset_cart3_1,
      width: 24,
      height: 24,
    },
  ];
  if (nVal.bntStyleConfig.typeFrom == 'bnt') {
    list.value = obj;
  } else {
    if (nVal.styleConfig.tabVal == 0 || nVal.styleConfig.tabVal == 4) {
      list.value = [
        {
          url: __asset_cart1_2,
          width: 42,
          height: 24,
        },
        {
          url: __asset_cart2_0,
          width: 24,
          height: 24,
        },
        {
          url: __asset_cart3_1,
          width: 24,
          height: 24,
        },
      ];
    } else {
      current.value = current.value == 2 ? 1 : current.value;
      list.value = obj;
    }
    nVal.bntStyleConfig.tabVal = current.value;
  }
}
</script>

<style scoped lang="scss">
.button-style {
  padding: 0 15px;
  margin-bottom: 20px;
  .title-tips {
    color: #999999;
    font-size: 12px;
    width: 75px;
    margin-right: 16px;
  }
  .style-box {
    .pictrue {
      width: 54px;
      height: 36px;
      border-radius: 3px;
      border: 1px solid #fff;
      margin-right: 10px;
      &.on {
        border: 1px solid var(--prev-color-primary);
      }
      img {
        display: block;
      }
    }
  }
}
</style>
