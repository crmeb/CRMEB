<template>
  <div>
    <!--v-if="item.count && item.count!='0.00'"-->
    <el-row align="middle" :gutter="16" class="ivu-mt">
      <el-col
        :xl="item.col || colsize.xl"
        :lg="colsize.lg"
        :md="colsize.md"
        :sm="24"
        :xs="24"
        class="ivu-mb"
        v-for="(item, index) in cardLists"
        :key="index"
      >
        <el-card shadow="never" class="card_cent">
          <div class="card_box">
            <div
              class="card_box_cir"
              :class="{
                one: index % 5 == 0,
                two: index % 5 == 1,
                three: index % 5 == 2,
                four: index % 5 == 3,
                five: index % 5 == 4,
              }"
            >
              <div
                class="card_box_cir1"
                :class="{
                  one1: index % 5 == 0,
                  two1: index % 5 == 1,
                  three1: index % 5 == 2,
                  four1: index % 5 == 3,
                  five1: index % 5 == 4,
                }"
              >
                <span class="iconfont" :class="item.className"></span>
              </div>
            </div>
            <div class="card_box_txt">
              <span class="sp1" v-text="item.count || 0"></span>
              <span class="sp2" v-text="item.name"></span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { reactive } from 'vue';

defineOptions({ name: 'cards' });

const props = defineProps({
  cardLists: Array,
});

const colsize = reactive({
  xl: 4,
  lg: 8,
  md: 12,
});

// created
let cardlength = props.cardLists.length;
switch (cardlength) {
  case 1:
    colsize.xl = 24;
    colsize.lg = 24;
    colsize.md = 24;
    break;
  case 2:
    colsize.xl = 12;
    colsize.lg = 12;
    colsize.md = 12;
    break;
  case 3:
    colsize.xl = 8;
    colsize.lg = 8;
    colsize.md = 8;
    break;
  case 4:
    colsize.xl = 6;
    colsize.lg = 6;
    colsize.md = 12;
    break;
  case 5:
    colsize.xl = 8;
    colsize.lg = 8;
    colsize.md = 12;
    break;
  default:
    colsize.xl = 4;
    colsize.lg = 8;
    colsize.md = 12;
    break;
}
if (props.cardLists.length == 4) {
  colsize.lg = 6;
} else if (props.cardLists.length == 4) {
  colsize.lg = 8;
}
</script>

<style lang="scss" scoped>
.card_box_cir1 :deep(.iconfont) {
  font-size: 26px;
  color: #fff;
}
.one {
  background: #e4ecff;
}
.two {
  background: #fff3e0;
}
.three {
  background: #eaf9e1;
}
.four {
  background: #ffeaf4;
}
.five {
  background: #f1e4ff;
}
.one1 {
  background: #4d7cfe;
}
.two1 {
  background: #ffab2b;
}
.three1 {
  background: #6dd230;
}
.four1 {
  background: #ff85c0;
}
.five1 {
  background: #b37feb;
}
.card_box {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  /*justify-content: center*/

  box-sizing: border-box;
  border-radius: 4px;
  .card_box_cir {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    .card_box_cir1 {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
    }
  }
  .card_box_txt {
    .sp1 {
      display: block;
      color: #252631;
      font-size: 24px;
    }
    .sp2 {
      display: block;
      color: #98a9bc;
      font-size: 12px;
    }
  }
}
</style>
