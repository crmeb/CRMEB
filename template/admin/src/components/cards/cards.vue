<template>
  <div>
    <!--v-if="item.count && item.count!='0.00'"-->
    <el-row  align="middle" :gutter="24" class="ivu-mt">
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
      colsize:{
        xl:4,
        lg:8,
        md:12
      }
    };
  },
  props: {
    cardLists: Array,
  },
  methods: {  },
  created() {
    let cardlength = this.cardLists.length
    switch(cardlength) {
      case 1:
        this.colsize.xl = 24
        this.colsize.lg = 24
        this.colsize.md = 24
        break;
      case 2:
        this.colsize.xl = 12
        this.colsize.lg = 12
        this.colsize.md = 12
        break;
      case 3:
        this.colsize.xl = 8
        this.colsize.lg = 8
        this.colsize.md = 8
        break;
      case 4:
        this.colsize.xl = 6
        this.colsize.lg = 6
        this.colsize.md = 12
        break;
      case 5:
        this.colsize.xl = 8
        this.colsize.lg = 8
        this.colsize.md = 12
        break;
      default:
        this.colsize.xl = 4
        this.colsize.lg = 8
        this.colsize.md = 12
        break;

    }
    if(this.cardLists.length == 4 ) {
      this.colsize.lg = 6
    } else if(this.cardLists.length == 4 ) {
      this.colsize.lg = 8
    }  
  },
};
</script>

<style scoped lang="stylus">

.card_box_cir1 ::v-deep .iconfont
   font-size: 26px
   color: #fff
 .one
    background #E4ECFF
.two
    background #FFF3E0
.three
    background #EAF9E1
.four
    background #FFEAF4
.five
    background #F1E4FF
.one1
    background #4D7CFE
.two1
    background #FFAB2B
.three1
    background #6DD230
.four1
    background #FF85C0
.five1
    background #B37FEB
.card_box
    width 100%
    height 100%
    display flex
    align-items: center
    /*justify-content: center*/
    // padding: 25px
    box-sizing: border-box
    border-radius: 4px
    .card_box_cir
        width 60px
        height 60px
        border-radius: 50%
        overflow: hidden
        margin-right: 20px
        display: flex
        justify-content: center
        align-items: center
        .card_box_cir1
            width 48px
            height 48px
            border-radius: 50%
            display: flex
            justify-content: center
            align-items: center
    .card_box_txt
        .sp1
            display block
            color #252631
            font-size 24px
        .sp2
            display block
            color #98A9BC
            font-size 12px
</style>
