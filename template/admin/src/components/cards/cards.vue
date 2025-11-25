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

<script>
export default {
  name: 'cards',
  data() {
    return {
      colsize: {
        xl: 4,
        lg: 8,
        md: 12,
      },
    };
  },
  props: {
    cardLists: Array,
  },
  methods: {},
  created() {
    let cardlength = this.cardLists.length;
    switch (cardlength) {
      case 1:
        this.colsize.xl = 24;
        this.colsize.lg = 24;
        this.colsize.md = 24;
        break;
      case 2:
        this.colsize.xl = 12;
        this.colsize.lg = 12;
        this.colsize.md = 12;
        break;
      case 3:
        this.colsize.xl = 8;
        this.colsize.lg = 8;
        this.colsize.md = 8;
        break;
      case 4:
        this.colsize.xl = 6;
        this.colsize.lg = 6;
        this.colsize.md = 12;
        break;
      case 5:
        this.colsize.xl = 8;
        this.colsize.lg = 8;
        this.colsize.md = 12;
        break;
      default:
        this.colsize.xl = 4;
        this.colsize.lg = 8;
        this.colsize.md = 12;
        break;
    }
    if (this.cardLists.length == 4) {
      this.colsize.lg = 6;
    } else if (this.cardLists.length == 4) {
      this.colsize.lg = 8;
    }
  },
};
</script>

<style lang="scss" scoped>
.card_box_cir1 ::v-deep .iconfont {
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
