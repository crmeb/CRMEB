<template>
  <div>
    <div class="i-layout-page-header header-title">
      <div class="header-main">
        <span class="ivu-page-header-title mr20">{{ $route.meta.title }}</span>
        <span class="header-description">
          <el-icon class="header-description-icon"><InfoFilled /></el-icon>
          切换移动端商城主题色系，保存后前台立即生效。
        </span>
      </div>
      <div>
        <div style="float: right">
          <el-button class="bnt" type="primary" v-db-click @click="submit">保存</el-button>
        </div>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt p20 h100">
      <div class="acea-row">
        <div
          class="tab_color"
          v-for="(item, index) in tabList"
          :key="index"
          :class="current === index + 1 ? 'active' : ''"
          v-db-click
          @click="selected(index)"
        >
          <div class="color_cont flex align-center">
            <div class="main_c mr-2" :class="item.class">
              <span class="iconfont iconxuanzhong6" v-show="current == index + 1"></span>
            </div>
            <div style="line-height: 24px">{{ item.tit }}</div>
          </div>
        </div>
      </div>
      <div class="acea-row row-top position-relative">
        <div
          class="pictrue position-absolute"
          :class="{ sel: current == index + 1 }"
          v-for="(item, index) in picList"
          :key="index"
        >
          <img :src="item.image" />
        </div>
      </div>
    </el-card>
    <!--<div class="footer acea-row row-center-wrapper">-->
    <!--<el-button type="primary" v-db-click @click="submit">保存</el-button>-->
    <!--</div>-->
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { ElMessage } from 'element-plus';
import __asset_bule_0 from '@/assets/images/bule.jpg';
import __asset_green_1 from '@/assets/images/green.jpg';
import __asset_red_2 from '@/assets/images/red.jpg';
import __asset_pink_3 from '@/assets/images/pink.jpg';
import __asset_orange_4 from '@/assets/images/orange.jpg';

import { colorChange, getColorChange } from '@diy/api';
import { useMediaStore } from '@/store/module/media';

defineOptions({ name: 'themeStyle' });

// 原 mapState('admin/layout') 模块不存在；按语义映射到现有 media store
const mediaStore = useMediaStore();

const grid = {
  xl: 7,
  lg: 7,
  md: 12,
  sm: 24,
  xs: 24,
};
const tabList = [
  { tit: '天空蓝', class: 'blue' },
  { tit: '生鲜绿', class: 'green' },
  { tit: '热情红', class: 'red' },
  { tit: '魅力粉', class: 'pink' },
  { tit: '活力橙', class: 'orange' },
];
const picList = [
  { image: __asset_bule_0 },
  { image: __asset_green_1 },
  { image: __asset_red_2 },
  { image: __asset_pink_3 },
  { image: __asset_orange_4 },
];
const current = ref('');
const clientHeight = ref(0);
const loadingExist = ref(false);

const isMobile = computed(() => mediaStore.isMobile);
const labelWidth = computed(() => (isMobile.value ? undefined : '110px'));
const labelPosition = computed(() => (isMobile.value ? 'top' : 'right'));

// created
// picList = picListBule;
getInfo();

onMounted(() => {
  nextTick(() => {
    clientHeight.value = `${document.documentElement.clientHeight}` - 250; //获取浏览器可视区域高度
    window.onresize = function () {
      clientHeight.value = `${document.documentElement.clientHeight}` - 250;
    };
  });
});

function selected(index) {
  current.value = index + 1;
}

function getInfo() {
  getColorChange('color_change')
    .then((res) => {
      current.value = res.data.status ? res.data.status : 3;
      changeColor(current.value);
    })
    .catch((err) => {
      ElMessage.error(err.msg);
    });
}

function submit() {
  loadingExist.value = true;
  colorChange(current.value, 'color_change')
    .then((res) => {
      loadingExist.value = false;
      ElMessage.success(res.msg);
    })
    .catch(() => {
      loadingExist.value = false;
    });
}

function changeColor(e) {
  current.value = e;
}
</script>

<style scoped lang="scss">
.box {
  width: 100px;
  text-align: center;
}

.bnt {
  // width 10px!important;
}

.pictrue {
  top: 0;
  left: 0;
  max-width: 1000px;
  margin: 10px 24px 0 0;
  img {
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease;
  }
  &.sel img {
    opacity: 1;
  }
}

.footer {
  width: 100%;
  height: 70px;
  box-shadow: 0px -2px 4px rgba(0, 0, 0, 0.03);
  background-color: #fff;
  position: fixed;
  bottom: 0;
  left: 0;
  z-index: 9;
}
.main_c {
  width: 25px;
  height: 25px;
  border-radius: 5px;
  text-align: center;
  line-height: 25px;
  font-size: 14px;
}
.tab_color {
  width: 114px;
  height: 45px;
  border: 1px solid #e5e5e5;
  margin-bottom: 10px;
  margin-right: 20px;
  border-radius: 5px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
}
.color_cont {
  font-size: 14px;
}
.mr-2 {
  margin-right: 10px;
}
.color_bdg {
  display: block;
  width: 18px;
  height: 18px;
  border-top: 1px solid #fff;
  border-bottom: 1px solid #fff;
}
.blue {
  background-color: #1ca5e9;
}

.green {
  background-color: #42ca4d;
}

.red {
  background-color: #e93323;
}

.pink {
  background-color: #ff448f;
}

.orange {
  background-color: #fe5c2d;
}
.active {
  border: 1px solid var(--prev-color-primary);
}

:deep(.ivu-radio-border) {
  position: relative;
}

.iconfont {
  font-size: 12px;
  color: #fff;
}

:deep(.ivu-radio-inner:after) {
  background-color: unset;
  transform: unset;
}

:deep(.i-layout-page-header) {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
</style>
