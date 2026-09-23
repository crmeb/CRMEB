<template>
  <div>
    <div class="button-style acea-row row-middle">
      <div class="title-tips" v-if="configData">
        <span>{{ configData.title }}</span>
      </div>
      <div class="style-box acea-row row-middle">
        <div class="bnt" @click="styleTap">修改风格</div>
        <div class="name">当前：样式{{ configData.tabVal + 1 }}</div>
      </div>
    </div>
    <el-dialog
      v-model="modals"
      title="风格选择器"
      height="500"
      :width="configData.type == 'signIn' || configData.type == 'ranking' ? '630px' : '910px'"
    >
      <div class="list acea-row row-middle">
        <div
          class="item"
          :class="current == index ? 'on' : ''"
          v-for="(item, index) in list"
          :key="index"
          @click="tap(index)"
        >
          <div class="pictrue acea-row row-center-wrapper">
            <img
              :src="item.url"
              :style="{
                width: item.width + 'px',
                height: item.height + 'px',
              }"
            />
            <span class="iconfont icona-zu80222" v-if="current == index"></span>
          </div>
          <div class="name">风格{{ index + 1 }}</div>
        </div>
      </div>
      <template #footer
        ><span class="dialog-footer">
          <el-button v-db-click @click="cancel">取 消</el-button>
          <el-button type="primary" v-db-click @click="ok">确 定</el-button>
        </span></template
      >
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';
import __asset_tab02_0 from '@/assets/images/tab02.png';
import __asset_tab03_1 from '@/assets/images/tab03.png';
import __asset_tab01_2 from '@/assets/images/tab01.png';
import __asset_signIn01_3 from '@diy/assets/images/signIn01.png';
import __asset_signIn02_4 from '@diy/assets/images/signIn02.png';
import __asset_ranking01_5 from '@/assets/images/ranking01.png';
import __asset_ranking02_6 from '@/assets/images/ranking02.png';
import __asset_coupon01_7 from '@/assets/images/coupon01.png';
import __asset_coupon02_8 from '@/assets/images/coupon02.png';
import __asset_coupon03_9 from '@/assets/images/coupon03.png';
import __asset_coupon04_10 from '@/assets/images/coupon04.png';
import __asset_coupon05_11 from '@/assets/images/coupon05.png';
import __asset_cube2_12 from '@/assets/images/cube2.png';
import __asset_cube3_13 from '@/assets/images/cube3.png';
import __asset_cube4_14 from '@/assets/images/cube4.png';
import __asset_cube5_15 from '@/assets/images/cube5.png';
import __asset_cube6_16 from '@/assets/images/cube6.png';
import __asset_cube7_17 from '@/assets/images/cube7.png';
import __asset_cube8_18 from '@/assets/images/cube8.png';
import __asset_cube9_19 from '@/assets/images/cube9.png';
import __asset_cube10_20 from '@/assets/images/cube10.png';
import __asset_cube11_21 from '@/assets/images/cube11.png';
import __asset_cube12_22 from '@/assets/images/cube12.png';
import __asset_cube1_23 from '@/assets/images/cube1.png';

defineOptions({ name: 'c_button_style' });

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
const modals = ref(false);
const current = ref(0);
const navBar = [
  {
    url: __asset_tab02_0,
    width: 220,
    height: 24,
  },
  {
    url: __asset_tab03_1,
    width: 220,
    height: 24,
  },
  {
    url: __asset_tab01_2,
    width: 220,
    height: 24,
  },
];
const signIn = [
  {
    url: __asset_signIn01_3,
    width: 220,
    height: 64,
  },
  {
    url: __asset_signIn02_4,
    width: 220,
    height: 59,
  },
];
const ranking = [
  {
    url: __asset_ranking01_5,
    width: 200,
    height: 172,
  },
  {
    url: __asset_ranking02_6,
    width: 200,
    height: 167,
  },
];
const coupon = [
  {
    url: __asset_coupon01_7,
    width: 220,
    height: 69,
  },
  {
    url: __asset_coupon02_8,
    width: 220,
    height: 87,
  },
  {
    url: __asset_coupon03_9,
    width: 220,
    height: 62,
  },
  {
    url: __asset_coupon04_10,
    width: 220,
    height: 69,
  },
  {
    url: __asset_coupon05_11,
    width: 220,
    height: 49,
  },
];
const pictureCube = [
  {
    url: __asset_cube2_12,
    width: 130,
    height: 129,
    count: 2,
  },
  {
    url: __asset_cube3_13,
    width: 130,
    height: 129,
    count: 2,
  },
  {
    url: __asset_cube4_14,
    width: 130,
    height: 129,
    count: 3,
  },
  {
    url: __asset_cube5_15,
    width: 130,
    height: 129,
    count: 3,
  },
  {
    url: __asset_cube6_16,
    width: 130,
    height: 129,
    count: 3,
  },
  {
    url: __asset_cube7_17,
    width: 130,
    height: 129,
    count: 3,
  },
  {
    url: __asset_cube8_18,
    width: 130,
    height: 129,
    count: 3,
  },
  {
    url: __asset_cube9_19,
    width: 130,
    height: 129,
    count: 4,
  },
  {
    url: __asset_cube10_20,
    width: 130,
    height: 129,
    count: 5,
  },
  {
    url: __asset_cube11_21,
    width: 130,
    height: 129,
    count: 4,
  },
  {
    url: __asset_cube12_22,
    width: 130,
    height: 129,
    count: 1,
  },
  // {
  //   url: __asset_cube1_23,
  //   width: 130,
  //   height: 130,
  //   count: 16,
  // },
];
const list = ref([]);

watch(
  () => props.configObj,
  (nVal) => {
    defaults.value = nVal;
    configData.value = nVal[props.configNme];
  },
  { deep: true },
);

onMounted(() => {
  nextTick(() => {
    defaults.value = props.configObj;
    configData.value = props.configObj[props.configNme];
    nextTick((e) => {
      current.value = configData.value.tabVal;
    });
    switch (configData.value.type) {
      case 'navBar':
        list.value = navBar;
        break;
      case 'signIn':
        list.value = signIn;
        break;
      case 'ranking':
        list.value = ranking;
        break;
      case 'coupon':
        list.value = coupon;
        break;
      case 'pictureCube':
        list.value = pictureCube;
        break;
    }
  });
});

function tap(index) {
  current.value = index;
}
function styleTap() {
  modals.value = true;
}
function cancel() {
  modals.value = false;
}
function ok() {
  modals.value = false;
  configData.value.tabVal = current.value;
  configData.value.count = list.value[current.value].count;
  if (defaults.value.picStyle) {
    defaults.value.picStyle.tabVal = 0;
  }
}
</script>

<style scoped lang="scss">
:deep(.ivu-modal-body) {
  max-height: 623px;
  overflow: auto;
}
.list {
  padding-left: 8px;
  max-height: 50vh;
  .item {
    margin-right: 11px;
    text-align: center;
    cursor: pointer;

    &.on {
      .pictrue {
        border-color: var(--prev-color-primary);
      }
    }

    .name {
      color: #3d3d3d;
      font-size: 14px;
      margin: 12px 0;
    }

    &:nth-of-type(3n) {
      margin-right: 0;
    }
    .pictrue {
      width: 273px;
      height: 218px;
      background: #f5f5f5;
      border-radius: 4px;
      border: 1px solid #dddddd;
      position: relative;

      img {
        display: block;
      }

      .iconfont {
        position: absolute;
        right: -1px;
        bottom: -4px;
        color: var(--prev-color-primary);
      }
    }
  }
}
.button-style {
  padding: 0 15px;
  margin-bottom: 20px;

  .title-tips {
    margin-right: 14px;
    color: #999;
    font-size: 12px;
    width: 82px;
  }

  .style-box {
    .bnt {
      width: 94px;
      height: 32px;
      background: var(--prev-color-primary);
      border-radius: 4px;
      text-align: center;
      line-height: 32px;
      color: #fff;
      font-size: 12px;
      cursor: pointer;
    }
    .name {
      color: #999999;
      font-size: 12px;
      margin-left: 12px;
    }
  }
}
</style>
